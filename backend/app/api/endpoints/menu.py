from fastapi import APIRouter, BackgroundTasks, HTTPException
from app.services.menu_sync import MenuSynchronizer
import logging

router = APIRouter()
logger = logging.getLogger(__name__)

async def run_sync_task():
    try:
        synchronizer = MenuSynchronizer()
        await synchronizer.sync_menu()
    except Exception as e:
        logger.error(f"Background sync failed: {e}", exc_info=True)

from app.services.menu_service import MenuService
from uuid import UUID

@router.get("/tree")
async def get_menu_tree():
    """
    Get the full hierarchical menu tree.
    """
    service = MenuService()
    return await service.get_menu_tree()

@router.get("/products/{product_id}")
async def get_product(product_id: UUID):
    service = MenuService()
    product = await service.get_product(product_id)
    if not product:
        raise HTTPException(status_code=404, detail="Product not found")
    return product

@router.post("/sync")
async def sync_menu(background_tasks: BackgroundTasks):
    """
    Trigger menu synchronization from iiko Cloud.
    """
    background_tasks.add_task(run_sync_task)
    return {"message": "Menu synchronization started in background"}
