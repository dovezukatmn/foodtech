from fastapi import FastAPI
from contextlib import asynccontextmanager
from app.core.config import settings
from app.core.database import init_db
from app.api.endpoints import menu, orders, loyalty

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup: Create DB tables
    await init_db()
    yield
    # Shutdown events if needed

app = FastAPI(
    title=settings.PROJECT_NAME,
    lifespan=lifespan,
    openapi_url=f"{settings.API_V1_STR}/openapi.json"
)

app.include_router(menu.router, prefix=f"{settings.API_V1_STR}/menu", tags=["menu"])
app.include_router(orders.router, prefix=f"{settings.API_V1_STR}/orders", tags=["orders"])
app.include_router(loyalty.router, prefix=f"{settings.API_V1_STR}/loyalty", tags=["loyalty"])

@app.get("/")
def root():
    return {"message": f"Welcome to {settings.PROJECT_NAME} API"}

@app.get("/health")
def health_check():
    return {"status": "ok"}
