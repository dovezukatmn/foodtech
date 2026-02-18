from typing import List, Optional
from uuid import UUID, uuid4
from sqlmodel import Field, SQLModel, Relationship

# --- Modifier Models ---
class ModifierBase(SQLModel):
    iiko_id: UUID = Field(index=True) # Removed unique=True
    name: str
    price: float = 0.0
    description: Optional[str] = None

class Modifier(ModifierBase, table=True):
    id: Optional[UUID] = Field(default_factory=uuid4, primary_key=True)
    group_id: Optional[UUID] = Field(default=None, foreign_key="modifiergroup.id")
    group: Optional["ModifierGroup"] = Relationship(back_populates="modifiers")

class ModifierGroupBase(SQLModel):
    iiko_id: UUID = Field(index=True) # Removed unique=True
    name: str = "Modifier Group" # Default name
    max_quantity: int = 1
    min_quantity: int = 0

class ModifierGroup(ModifierGroupBase, table=True):
    id: Optional[UUID] = Field(default_factory=uuid4, primary_key=True)
    product_id: Optional[UUID] = Field(default=None, foreign_key="product.id")
    product: Optional["Product"] = Relationship(back_populates="modifier_groups")
    modifiers: List["Modifier"] = Relationship(back_populates="group")

# --- Menu Models ---
class CategoryBase(SQLModel):
    iiko_id: UUID = Field(index=True, unique=True)
    name: str
    description: Optional[str] = None
    parent_id: Optional[UUID] = None
    order: int = 0
    image_url: Optional[str] = None

class Category(CategoryBase, table=True):
    id: Optional[UUID] = Field(default_factory=uuid4, primary_key=True)
    products: List["Product"] = Relationship(back_populates="category")

class ProductBase(SQLModel):
    iiko_id: UUID = Field(index=True, unique=True)
    name: str
    description: Optional[str] = None
    price: float = 0.0
    weight: float = 0.0
    image_url: Optional[str] = None
    is_deleted: bool = False
    
class Product(ProductBase, table=True):
    id: Optional[UUID] = Field(default_factory=uuid4, primary_key=True)
    category_id: Optional[UUID] = Field(default=None, foreign_key="category.id")
    category: Optional[Category] = Relationship(back_populates="products")
    modifier_groups: List["ModifierGroup"] = Relationship(back_populates="product")
