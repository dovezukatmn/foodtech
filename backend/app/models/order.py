from typing import List, Optional
from uuid import UUID, uuid4
from datetime import datetime
from sqlmodel import Field, SQLModel, Relationship

class OrderItemBase(SQLModel):
    product_id: UUID = Field(foreign_key="product.id")
    quantity: int = 1
    price: float = 0.0 # Price at the time of order
    # modifier_ids removed to avoid SQLModel type error
    modifiers_json: Optional[str] = None # Store selected modifiers as JSON string for simplicity

class OrderItem(OrderItemBase, table=True):
    id: Optional[UUID] = Field(default_factory=uuid4, primary_key=True)
    order_id: Optional[UUID] = Field(default=None, foreign_key="order.id")
    order: Optional["Order"] = Relationship(back_populates="items")

class OrderBase(SQLModel):
    customer_name: str
    customer_phone: str
    delivery_address: Optional[str] = None
    comment: Optional[str] = None
    total_amount: float = 0.0
    status: str = "CREATED" # CREATED, PENDING_IIKO, CONFIRMED, CANCELLED
    iiko_order_id: Optional[UUID] = None # ID from iiko
    iiko_order_number: Optional[str] = None
    created_at: datetime = Field(default_factory=datetime.utcnow)

class Order(OrderBase, table=True):
    id: Optional[UUID] = Field(default_factory=uuid4, primary_key=True)
    items: List["OrderItem"] = Relationship(back_populates="order")
