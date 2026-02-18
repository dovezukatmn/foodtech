from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
from typing import List, Optional
from uuid import UUID
from app.services.order_service import OrderService
from app.models.order import Order

router = APIRouter()

# Pydantic schemas for request/response
class OrderItemCreate(BaseModel):
    productId: UUID
    quantity: int
    modifiers: Optional[List[dict]] = []

class CustomerCreate(BaseModel):
    name: str
    phone: str

class OrderCreate(BaseModel):
    customer: CustomerCreate
    items: List[OrderItemCreate]
    deliveryAddress: Optional[str] = None
    comment: Optional[str] = None

@router.post("/", response_model=dict)
async def create_order(order_in: OrderCreate):
    service = OrderService()
    try:
        # Convert Pydantic to dict for service
        order_data = order_in.model_dump()
        # Ensure UUIDs are strings for JSON serialization/service logic if needed, 
        # but service expects dict with UUID objects or strings? 
        # Service: product_id = UUID(item['productId']) -> expects string or UUID in dict.
        # model_dump() usually converts UUID to UUID object by default.
        # Let's sanitize to be sure.
        for item in order_data['items']:
            item['productId'] = str(item['productId'])
        
        order = await service.create_order(order_data)
        return {"id": order.id, "status": order.status, "total_amount": order.total_amount}
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail="Internal Server Error")

from app.core.database import async_session
from sqlmodel import select
from sqlalchemy.orm import selectinload

@router.get("/{order_id}")
async def get_order(order_id: UUID):
    async with async_session() as session:
        stmt = select(Order).where(Order.id == order_id).options(selectinload(Order.items))
        result = await session.execute(stmt)
        order = result.scalar_one_or_none()
        
        if not order:
             raise HTTPException(status_code=404, detail="Order not found")
        
        return order
