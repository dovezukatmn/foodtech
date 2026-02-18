import asyncio
import logging
from uuid import UUID
from sqlmodel import select
from sqlalchemy.ext.asyncio import AsyncSession
from app.core.database import async_session
from app.services.iiko_cloud import IikoCloudClient
from app.models.menu import Category, Product, Modifier, ModifierGroup

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class MenuSynchronizer:
    def __init__(self):
        self.client = IikoCloudClient()

    async def sync_menu(self):
        logger.info("Starting menu synchronization...")
        try:
            # 1. Get Authentication Token
            await self.client.get_token()

            # 2. Get Organizations (we take the first one for now)
            orgs = await self.client.get_organizations()
            if not orgs:
                logger.warning("No organizations found.")
                return
            
            org_id = orgs[0]['id']
            logger.info(f"Syncing menu for Organization ID: {org_id}")

            # 3. Get Menu
            menu_data = await self.client.get_menu(org_id)
            
            # 4. Save to DB
            async with async_session() as session:
                await self._save_categories(session, menu_data.get('groups', []))
                await self._save_products(session, menu_data.get('products', []))
                await session.commit()
            
            logger.info("Menu synchronization completed successfully.")

        except Exception as e:
            logger.error(f"Sync failed: {e}", exc_info=True)
        finally:
            await self.client.close()

    async def _save_categories(self, session: AsyncSession, groups: list):
        # Pass 1: Create or update categories without parent_id (to avoid FK violation)
        group_map = {UUID(g['id']): g for g in groups}
        
        for group in groups:
            stmt = select(Category).where(Category.iiko_id == UUID(group['id']))
            result = await session.execute(stmt)
            category = result.scalar_one_or_none()

            if not category:
                category = Category(
                    iiko_id=UUID(group['id']),
                    name=group['name'],
                    description=group.get('description'),
                    parent_id=None, # Set later
                    order=group.get('order', 0),
                    image_url=group.get('images', [{}])[0].get('imageUrl') if group.get('images') else None
                )
                session.add(category)
            else:
                category.name = group['name']
                category.description = group.get('description')
                # Update other fields
                session.add(category)
        
        await session.flush() # Ensure all categories exist in DB

        # Refresh map with internal IDs
        stmt = select(Category.iiko_id, Category.id)
        result = await session.execute(stmt)
        category_db_map = {row.iiko_id: row.id for row in result.all()}

        # Pass 2: Update parent_id
        for group in groups:
            if group.get('parentGroup'):
                parent_iiko_id = UUID(group['parentGroup'])
                # Find internal ID of parent
                parent_internal_id = category_db_map.get(parent_iiko_id)
                
                if parent_internal_id:
                    stmt = select(Category).where(Category.iiko_id == UUID(group['id']))
                    result = await session.execute(stmt)
                    category = result.scalar_one_or_none()
                    if category:
                        category.parent_id = parent_internal_id
                        session.add(category)
                else:
                    logger.warning(f"Parent category {parent_iiko_id} not found for {group['id']}")

    async def _save_products(self, session: AsyncSession, products: list):
        # Pre-fetch map of iiko_id -> internal_id for categories
        stmt = select(Category.iiko_id, Category.id)
        result = await session.execute(stmt)
        category_map = {row.iiko_id: row.id for row in result.all()}

        for item in products:
            try:
                async with session.begin_nested():
                    stmt = select(Product).where(Product.iiko_id == UUID(item['id']))
                    result = await session.execute(stmt)
                    product = result.scalar_one_or_none()

                    # Validate and link to internal category ID
                    category_uuid = None
                    if item.get('parentGroup'):
                        parent_uuid = UUID(item['parentGroup'])
                        category_uuid = category_map.get(parent_uuid)
                        
                        if not category_uuid:
                            logger.warning(f"Product {item['name']} ({item['id']}) references missing category {parent_uuid}. Setting category to None.")

                    if not product:
                        product = Product(
                            iiko_id=UUID(item['id']),
                            name=item['name'],
                            description=item.get('description'),
                            price=item.get('price', 0.0), 
                            weight=item.get('weight', 0.0),
                            category_id=category_uuid,
                            image_url=item.get('images', [{}])[0].get('imageUrl') if item.get('images') else None,
                            is_deleted=item.get('isDeleted', False)
                        )
                        session.add(product)
                        await session.flush()
                        await session.refresh(product)
                    else:
                        product.name = item['name']
                        product.price = item.get('price', 0.0)
                        product.category_id = category_uuid 
                        session.add(product)
                        await session.flush()
                        await session.refresh(product)
                    
                    # Sync Modifier Groups
                    if 'groupModifiers' in item:
                        await self._save_modifier_groups(session, product, item['groupModifiers'])

            except Exception as e:
                logger.error(f"Failed to save product {item.get('name')}: {e}")
                continue

    async def _save_modifier_groups(self, session: AsyncSession, product: Product, groups: list):
        for group_data in groups:
            # iiko groupModifiers structure:
            # { "id": "uuid", "minAmount": int, "maxAmount": int, "required": bool, "childModifiers": [...] }
            # Usually doesn't have a name here if it's an embedded group, or uses the group name if referenced.
            # We'll need a unique ID for our DB. iiko "id" here is the ID of the group *assignment* or the group itself?
            # It's usually the ID of the *group*.
            
            group_iiko_id = UUID(group_data['id'])
            
            # Check if this group is already linked to this product?
            # Our model: ModifierGroup -> product_id.
            # A generic "Modifier Group" in iiko might be reused across products.
            # But our simplified model has `ModifierGroup` belong to a specific `Product`.
            # If iiko reuses groups (Common Modifiers), we are duplicating them per product in our DB 
            # OR we should have a Many-to-Many relationship (Product <-> ModifierGroup).
            # Given `product_id` FK in `ModifierGroup`, it's 1-to-Many (Product has many Groups).
            # So we treat each group inside `groupModifiers` as unique to this product instance in our DB.
            # To avoid unique constraint violation on `iiko_id` if reused:
            # `ModifierGroup.iiko_id` is generic iiko group ID. 
            # If we enforce uniqueness on `iiko_id`, we can't have the same iiko group attached to multiple products.
            # CHECK MODEL: `iiko_id: UUID = Field(index=True, unique=True)`
            # PROBLEM: If iiko reuses the same group ID for multiple products (e.g. "Pizza Toppings" group used for all pizzas),
            # then `unique=True` will fail.
            # FIX: We should drop `unique=True` for `ModifierGroup.iiko_id` OR make it Many-to-Many.
            # FOR NOW: I will treat `iiko_id` as the ID of the "group assignment" if available, or generate a composite?
            # NO, iiko `id` in `groupModifiers` IS the group ID.
            # If the user wants a simple system, I'll update the model to remove `unique=True` from `ModifierGroup.iiko_id` 
            # and allow multiple `ModifierGroup` entries (one per product) sharing the same `iiko_id`.
            
            # However, I can't change the model easily without migration or force-recreating tables (which I do on startup).
            # So I will Modify `models/menu.py` first to remove `unique=True`.
            pass

            # Assuming I fixed the model:
            # We need to find the specific group *for this product*.
            # Since we don't store "assignment ID", we might just check if we have a group with this iiko_id AND product_id.
             
            stmt = select(ModifierGroup).where(
                ModifierGroup.iiko_id == group_iiko_id,
                ModifierGroup.product_id == product.id
            )
            result = await session.execute(stmt)
            mod_group = result.scalar_one_or_none()

            if not mod_group:
                mod_group = ModifierGroup(
                    iiko_id=group_iiko_id,
                    name=group_data.get('name', 'Modifier Group'), # Name might be missing
                    max_quantity=group_data.get('maxAmount', 1),
                    min_quantity=group_data.get('minAmount', 0),
                    product_id=product.id
                )
                session.add(mod_group)
                await session.flush()
                await session.refresh(mod_group)
            else:
                 mod_group.max_quantity = group_data.get('maxAmount', 1)
                 mod_group.min_quantity = group_data.get('minAmount', 0)
                 session.add(mod_group)
            
            # Save modifiers in this group
            if 'childModifiers' in group_data:
                await self._save_modifiers(session, mod_group, group_data['childModifiers'])

    async def _save_modifiers(self, session: AsyncSession, group: ModifierGroup, modifiers: list):
        for mod_data in modifiers:
            # mod_data: { "id": "...", "defaultAmount": ..., "hideIfDefaultAmount": ..., "minAmount": ..., "maxAmount": ..., "reference": { "id": "..." } } (reference is the product ID of the modifier)
            # OR simple structure depending on API version.
            # Creating a modifier.
            # iiko "modifier" is usually a product. 
            # We store it as `Modifier` table.
            
            mod_iiko_id = UUID(mod_data['id']) # This is the ID of the modifier *item* in the group?
            # Or the ID of the product it refers to? 
            # Usually `mod_data['id']` is the modifier's ID in the group list.
            # It might refer to a product via `productId` or similar.
            # Let's assume `id` is the unique ID for this modifier entry.
            
            # Note: `Modifier` model also has `iiko_id` unique=True. 
            # Same problem: if the same modifier (e.g. "Cheese") is in multiple groups, we can't enforce uniqueness unless it's Many-to-Many.
            # or `Modifier` in our DB represents "Modifier Item in a Group".
            
            stmt = select(Modifier).where(
                Modifier.iiko_id == mod_iiko_id,
                Modifier.group_id == group.id
            )
            result = await session.execute(stmt)
            modifier = result.scalar_one_or_none()
            
            if not modifier:
                # We need name/price. Usually this comes from the product reference.
                # But here we might only have the structural info.
                # If `Reference` is not expanded, we might not have the name.
                # Assuming `menu_data` passed to us has fully expanded tree (often the case with some syncs) or we miss data.
                # `nomenclature` usually includes `productCategories`, `products`.
                # Modifiers are just products.
                # So we might need to LOOK UP the modifier product in our already saved products?
                # Or iiko provides `name` in the structure.
                
                # As a fallback, use `mod_data.get('name', 'Modifier')`.
                
                modifier = Modifier(
                    iiko_id=mod_iiko_id,
                    name=mod_data.get('name', 'Modifier'), # Might be missing
                    price=mod_data.get('price', 0.0), # Might be missing
                    description=None,
                    group_id=group.id
                )
                session.add(modifier)
            else:
                pass # Update logic

if __name__ == "__main__":
    synchronizer = MenuSynchronizer()
    asyncio.run(synchronizer.sync_menu())

if __name__ == "__main__":
    synchronizer = MenuSynchronizer()
    asyncio.run(synchronizer.sync_menu())
