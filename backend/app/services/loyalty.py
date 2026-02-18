import logging
from typing import Optional
from app.services.iiko_cloud import IikoCloudClient

logger = logging.getLogger(__name__)

class LoyaltyService:
    def __init__(self):
        self.client = IikoCloudClient()

    async def get_balance(self, phone: str) -> float:
        """
        Get customer's bonus balance.
        """
        # We need an organization ID. 
        # For MVP, we'll fetch the first organization or use a configured one.
        # Ideally, this should come from config or context.
        organizations = await self.client.get_organizations()
        if not organizations:
            logger.error("No organizations found for loyalty check")
            return 0.0
            
        organization_id = organizations[0]['id']
        logger.info(f"Checking loyalty for phone {phone} in org {organization_id}")
        
        customer_info = await self.client.get_customer_info(organization_id, phone)
        
        if not customer_info:
            return 0.0
            
        # Parse wallet balance
        # Structure usually: { "wallet": { "balance": 100.0, ... }, ... }
        # Or list of wallets.
        # Need to verify specific iiko API response structure for 'get_customer_info'.
        # Assuming standard structure:
        wallet = customer_info.get('wallet')
        if wallet:
            return float(wallet.get('balance', 0.0))
            
        # If multiple wallets/programs, logic might be more complex.
        return 0.0
