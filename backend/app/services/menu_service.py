from typing import List, Dict, Optional
from uuid import UUID
from sqlmodel import select
from app.core.database import async_session
from app.models.menu import Category, Product

class MenuService:
    async def get_menu_tree(self) -> List[Dict]:
        """
        Returns the menu hierarchy: Categories -> Sub-categories -> Products.
        """
        async with async_session() as session:
            # Fetch categories ordered by order
            stmt = select(Category).order_by(Category.order)
            result = await session.execute(stmt)
            categories = result.scalars().all()
            
            # Fetch products
            stmt = select(Product).where(Product.is_deleted == False)
            result = await session.execute(stmt)
            products = result.scalars().all()
            
            # Organize products by category_id (internal UUID)
            products_by_category: Dict[UUID, List[Dict]] = {}
            for p in products:
                if p.category_id:
                    if p.category_id not in products_by_category:
                        products_by_category[p.category_id] = []
                    
                    products_by_category[p.category_id].append({
                        "id": str(p.id),
                        "iiko_id": str(p.iiko_id),
                        "name": p.name,
                        "description": p.description,
                        "price": p.price,
                        "weight": p.weight,
                        "image_url": p.image_url
                    })
            
            # Initialize Category Map
            category_map = {}
            for cat in categories:
                category_map[cat.id] = {
                    "id": str(cat.id),
                    "iiko_id": str(cat.iiko_id),
                    "name": cat.name,
                    "description": cat.description,
                    "image_url": cat.image_url,
                    "order": cat.order,
                    "products": products_by_category.get(cat.id, []),
                    "subCategories": []
                }
            
            # Build Tree
            root_categories = []
            orphan_categories = [] # For safety

            for cat in categories:
                cat_dict = category_map[cat.id]
                parent_id = cat.parent_id
                
                if parent_id:
                    if parent_id in category_map:
                         category_map[parent_id]["subCategories"].append(cat_dict)
                    else:
                        # Parent ID exists but not found in map (should not happen if consistent)
                        orphan_categories.append(cat_dict)
                else:
                    root_categories.append(cat_dict)
            
            # Sort subcategories by order if needed? (List append preserves order of `categories` query which was ordered)
            # Yes, categories query was `order_by(Category.order)`. Iteration preserves order.
            
            return root_categories + orphan_categories
            
    async def get_product(self, product_id: UUID) -> Optional[Dict]:
        async with async_session() as session:
            stmt = select(Product).where(Product.id == product_id)
            result = await session.execute(stmt)
            product = result.scalar_one_or_none()
            if product:
                return {
                        "id": str(product.id),
                        "iiko_id": str(product.iiko_id),
                        "name": product.name,
                        "description": product.description,
                        "price": product.price,
                        "weight": product.weight,
                        "image_url": product.image_url
                        # TODO: Modifiers?
                    }
            return None
