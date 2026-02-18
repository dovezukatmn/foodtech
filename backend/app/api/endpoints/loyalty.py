from fastapi import APIRouter, HTTPException, Depends
from app.services.loyalty import LoyaltyService

router = APIRouter()

@router.get("/balance/{phone}")
async def get_balance(phone: str):
    """
    Get customer loyalty balance by phone.
    """
    service = LoyaltyService()
    try:
        balance = await service.get_balance(phone)
        return {"phone": phone, "balance": balance}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
