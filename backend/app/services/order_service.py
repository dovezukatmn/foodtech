import logging
import json
from uuid import UUID
from datetime import datetime
from typing import List, Dict, Optional
from sqlmodel import select
from sqlalchemy.ext.asyncio import AsyncSession
from app.core.database import async_session
from app.models.order import Order, OrderItem
from app.models.menu import Product
from app.services.iiko_cloud import IikoCloudClient

logger = logging.getLogger(__name__)

class OrderService:
    def __init__(self):
        self.iiko_client = IikoCloudClient()

    async def create_order(self, order_data: Dict) -> Order:
        """
        Creates an order in the local DB and sends it to iiko.
        order_data expectation:
        {
            "customer": {"name": "...", "phone": "..."},
            "items": [{"productId": "...", "quantity": 1, "modifiers": [...]}]
        }
        """
        async with async_session() as session:
            try:
                # 1. Validate items & Calculate Total
                total_amount = 0.0
                order_items = []
                
                for item in order_data['items']:
                    product_id = UUID(item['productId'])
                    stmt = select(Product).where(Product.id == product_id)
                    result = await session.execute(stmt)
                    product = result.scalar_one_or_none()
                    
                    if not product:
                        raise ValueError(f"Product {product_id} not found")

                    # Calculate price (simple version, ignoring complex modifiers price for now)
                    item_price = product.price 
                    total_amount += item_price * item['quantity']
                    
                    order_items.append(OrderItem(
                        product_id=product_id,
                        quantity=item['quantity'],
                        price=item_price,
                        modifiers_json=json.dumps(item.get('modifiers', []))
                    ))

                # 2. Create Local Order
                customer = order_data['customer']
                order = Order(
                    customer_name=customer['name'],
                    customer_phone=customer['phone'],
                    delivery_address=order_data.get('deliveryAddress'),
                    comment=order_data.get('comment'),
                    total_amount=total_amount,
                    status="CREATED"
                )
                session.add(order)
                await session.flush() # Get Order ID
                
                for oi in order_items:
                    oi.order_id = order.id
                    session.add(oi)
                
                await session.commit()
                await session.refresh(order)
                
                # 3. Send to iiko (Background or Foreground?)
                # For now, foreground to return immediate status
                try:
                    iiko_response = await self._send_to_iiko(order, order_items, session)
                    if iiko_response:
                        order.status = "PENDING_IIKO"
                        order.iiko_order_id = UUID(iiko_response['orderInfo']['id'])
                        order.iiko_order_number = iiko_response['orderInfo'].get('number')
                        session.add(order)
                        await session.commit()
                        await session.refresh(order)
                except Exception as e:
                    logger.error(f"Failed to send order {order.id} to iiko: {e}")
                    # Keep status CREATED or set to ERROR_SENDING
                    # We might want a background task to retry
                    pass

                return order

            except Exception as e:
                await session.rollback()
                logger.error(f"Order creation failed: {e}")
                raise

    async def _send_to_iiko(self, order: Order, items: List[OrderItem], session: AsyncSession) -> Dict:
        """
        Constructs iiko payload and sends it.
        """
        # We need organization ID. Assuming single org for now or fetched from config/DB
        # In a real app, this might come from the order context (which restaurant)
        orgs = await self.iiko_client.get_organizations()
        if not orgs:
            raise ValueError("No organization found in iiko")
        org_id = orgs[0]['id']

        # Construct Items Payload
        iiko_items = []
        for item in items:
            # We need the IIKO ID of the product, not our local ID
            product = await session.get(Product, item.product_id)
            iiko_items.append({
                "productId": str(product.iiko_id),
                "price": item.price,
                "amount": item.quantity,
                "type": "Product"
                # TODO: Modifiers
            })

        payload = {
            "id": str(order.id), # Use our ID as external ID? Or let iiko generate? 
            # iiko requires valid UUID if we pass it, usually for idempotency.
            # But specific field usually is "externalId" or "ids".
            # The 'order' object structure in iiko cloud api create_delivery is complex.
            # Simplified mapping:
            "externalId": str(order.id),
            "date": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "items": iiko_items,
            "phone": order.customer_phone,
            "customer": {"name": order.customer_name},
            "orderServiceType": "DeliveryByClient", # or DeliveryByCourier
        }
        
        # NOTE: This is a simplified payload. iiko API is strict. 
        # We might need "terminalGroupId", "orderType", etc.
        # For this MVP, we will try minimal payload and adjust based on errors.
        
        # We also need terminal Group ID.
        terminals = await self.iiko_client.get_terminal_groups([org_id])
        if terminals and terminals[0].get('items'):
             payload['terminalGroupId'] = terminals[0]['items'][0]['id']

        return await self.iiko_client.create_delivery_order(org_id, payload)
