from typing import Optional, Dict, Any, List
import httpx
import logging
from datetime import datetime
from app.core.config import settings

logger = logging.getLogger(__name__)

class IikoCloudClient:
    def __init__(self):
        self.base_url = settings.IIKO_API_BASE_URL
        self.api_login = settings.IIKO_API_LOGIN
        self.token: Optional[str] = None
        self.client = httpx.AsyncClient(timeout=30.0)

    async def get_token(self) -> str:
        """Authenticates and retrieves a JWT token."""
        try:
            response = await self.client.post(
                f"{self.base_url}/access_token",
                json={"apiLogin": self.api_login}
            )
            response.raise_for_status()
            data = response.json()
            self.token = data.get("token")
            return self.token
        except httpx.HTTPError as e:
            logger.error(f"Failed to get token: {e}")
            raise

    async def _make_request(self, method: str, endpoint: str, data: Optional[Dict] = None) -> Dict:
        """Internal method to make authenticated requests with retry logic."""
        if not self.token:
            await self.get_token()

        headers = {"Authorization": f"Bearer {self.token}"}
        url = f"{self.base_url}{endpoint}"

        try:
            if method == "GET":
                response = await self.client.get(url, headers=headers)
            elif method == "POST":
                response = await self.client.post(url, headers=headers, json=data)
            else:
                raise ValueError(f"Unsupported method: {method}")

            if response.status_code == 401:
                # Token expired, refresh and retry once
                logger.info("Token expired, refreshing...")
                token = await self.get_token()
                headers["Authorization"] = f"Bearer {token}"
                if method == "GET":
                    response = await self.client.get(url, headers=headers)
                elif method == "POST":
                    response = await self.client.post(url, headers=headers, json=data)

            if response.is_error:
                logger.error(f"API Error {response.status_code} for {endpoint}: {response.text}")
            
            response.raise_for_status()
            data = response.json()
            # logger.info(f"API Response for {endpoint}: {data}") # Verbose
            return data

        except httpx.HTTPError as e:
            logger.error(f"Request failed: {e}")
            raise

    async def get_organizations(self, return_additional_info: bool = False, include_disabled: bool = False) -> List[Dict]:
        """
        Returns the list of organizations.
        /api/1/organizations
        """
        payload = {
            "organizationIds": None, # All available
            "returnAdditionalInfo": return_additional_info,
            "includeDisabled": include_disabled
        }

        data = await self._make_request("POST", "/organizations", payload)
        return data.get("organizations", [])

    async def get_terminal_groups(self, organization_ids: List[str]) -> List[Dict]:
        """
        Returns terminal groups for the specified organizations.
        /api/1/terminal_groups
        """
        payload = {"organizationIds": organization_ids}
        return await self._make_request("POST", "/terminal_groups", payload)

    async def get_menu(self, organization_id: str) -> Dict:
        """
        Returns the external menu (nomenclature).
        /api/1/nomenclature
        """
        payload = {"organizationId": organization_id}
        return await self._make_request("POST", "/nomenclature", payload)

    async def create_delivery_order(self, organization_id: str, order_data: Dict) -> Dict:
        """
        Creates a new delivery order.
        /api/1/deliveries/create
        """
        payload = {
            "organizationId": organization_id,
            "order": order_data
        }
        return await self._make_request("POST", "/deliveries/create", payload)

    async def check_delivery_status(self, organization_id: str, order_id: str) -> Dict:
        """
        Checks the status of a delivery order.
        /api/1/deliveries/by_id
        """
        payload = {
            "organizationId": organization_id,
            "orderIds": [order_id]
        }
        return await self._make_request("POST", "/deliveries/by_id", payload)

    async def get_customer_info(self, organization_id: str, phone: str) -> Dict:
        """
        Get customer loyalty info (balance, wallet).
        /api/1/loyalty/iiko/get_customer_info
        """
        # Phone normalization
        if not phone.startswith("+"):
            phone = "+" + phone
            
        payload = {
            "organizationId": organization_id,
            "phone": phone,
            "type": "phone"
        }
        return await self._make_request("POST", "/loyalty/iiko/get_customer_info", payload)

    async def close(self):
        await self.client.aclose()
