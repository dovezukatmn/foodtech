from aiogram import Router, F
from aiogram.filters import Command
from aiogram.types import Message, CallbackQuery, InlineKeyboardMarkup, InlineKeyboardButton, ReplyKeyboardMarkup, KeyboardButton
from aiogram.fsm.context import FSMContext
from aiogram.fsm.state import State, StatesGroup
import httpx
import os

router = Router()
BACKEND_URL = os.getenv("BACKEND_URL", "http://backend:8000/api/v1")

# States for checkout
class Checkout(StatesGroup):
    waiting_for_phone = State()
    waiting_for_name = State()
    waiting_for_address = State()

# Simple in-memory cart (Dictionary: user_id -> items)
# In production, use Redis or Database
carts = {}

@router.callback_query(F.data.startswith("prod_"))
async def add_to_cart(callback: CallbackQuery):
    product_id = callback.data.split("_")[1]
    user_id = callback.from_user.id
    
    if user_id not in carts:
        carts[user_id] = []
    
    # We need product details to show name/price in cart.
    # Ideally, we should fetch product details from backend or store in validation.
    # For now, let's just store ID and fetch on checkout/cart view?
    # Or fetch now.
    
    async with httpx.AsyncClient() as client:
        try:
            # We created this endpoint!
            response = await client.get(f"{BACKEND_URL}/menu/products/{product_id}")
            if response.status_code == 200:
                product = response.json()
                carts[user_id].append(product)
                await callback.answer(f"Added {product['name']} to cart!")
                # Optional: Send a message confirming? or just alert.
                # User asked for buttons. Let's update the reply markup of the message? No, that's the list.
                # Let's just answer with alert for now, but since we added "Cart" button to menu, they can nav there.
                # UPDATE: User said "no cart button". We added it to menu.py.
            else:
                 await callback.answer("Product not found")
        except Exception as e:
            await callback.answer(f"Error adding to cart: {e}")

@router.callback_query(F.data == "show_cart")
async def show_cart_handler(callback: CallbackQuery):
    await view_cart_logic(callback.message, callback.from_user.id)
    await callback.answer()

@router.message(Command("cart"))
async def view_cart_command(message: Message):
    await view_cart_logic(message, message.from_user.id)

async def view_cart_logic(message: Message, user_id: int):
    cart = carts.get(user_id, [])
    
    if not cart:
        # If entered via callback (button), we might want to edit text?
        # But message object is different.
        if isinstance(message, Message): 
             # It's a message (command or callback.message)
             # If it was a callback, we might want to edit.
             # Let's simple send new message for now to be safe across contexts, or try edit.
             await message.answer("Your cart is empty.", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[InlineKeyboardButton(text="🔙 Menu", callback_data="show_menu")]]))
        return

    text = "🛒 <b>Your Cart</b>\n\n"
    total = 0.0
    for item in cart:
        text += f"• {item['name']} - {item['price']}₽\n"
        total += item['price']
    
    text += f"\n<b>Total: {total}₽</b>"
    
    markup = InlineKeyboardMarkup(inline_keyboard=[[
        InlineKeyboardButton(text="✅ Checkout", callback_data="checkout")
    ], [
        InlineKeyboardButton(text="🗑 Clear Cart", callback_data="clear_cart"),
        InlineKeyboardButton(text="🔙 Menu", callback_data="show_menu")
    ]])
    
    # We can try to edit request if it came from a button click (CallbackQuery attached to message)
    # But here we passed `message`.
    # Let's just send a new message to avoid "Message is not modified" errors if content is same
    await message.answer(text, reply_markup=markup)

@router.callback_query(F.data == "clear_cart")
async def clear_cart(callback: CallbackQuery):
    user_id = callback.from_user.id
    carts[user_id] = []
    await callback.message.edit_text("Cart cleared.")

@router.callback_query(F.data == "checkout")
async def start_checkout(callback: CallbackQuery, state: FSMContext):
    user_id = callback.from_user.id
    if not carts.get(user_id):
        await callback.answer("Cart is empty!")
        return
        
    await state.set_state(Checkout.waiting_for_name)
    await callback.message.answer("Please enter your Name:")

@router.message(Checkout.waiting_for_name)
async def process_name(message: Message, state: FSMContext):
    await state.update_data(name=message.text)
    await state.set_state(Checkout.waiting_for_phone)
    await message.answer("Please enter your Phone Number:", reply_markup=ReplyKeyboardMarkup(keyboard=[[KeyboardButton(text="Send Contact", request_contact=True)]], resize_keyboard=True, one_time_keyboard=True))

@router.message(Checkout.waiting_for_phone)
async def process_phone(message: Message, state: FSMContext):
    phone = message.contact.phone_number if message.contact else message.text
    await state.update_data(phone=phone)
    
    await state.set_state(Checkout.waiting_for_address)
    await message.answer("Please enter delivery address:", reply_markup=None) # clear keyboard

@router.message(Checkout.waiting_for_address)
async def process_address(message: Message, state: FSMContext):
    address = message.text
    data = await state.get_data()
    user_id = message.from_user.id
    cart = carts.get(user_id, [])
    
    # Construct Order Payload
    # Group items by ID for quantity
    items_map = {}
    for item in cart:
        pid = item['id']
        if pid in items_map:
            items_map[pid]['quantity'] += 1
        else:
            items_map[pid] = {"productId": pid, "quantity": 1}
    
    order_items = list(items_map.values())
    
    payload = {
        "customer": {
            "name": data['name'],
            "phone": data['phone']
        },
        "items": order_items,
        "deliveryAddress": address,
        "comment": "Order from Telegram"
    }
    
    async with httpx.AsyncClient() as client:
        try:
            response = await client.post(f"{BACKEND_URL}/orders/", json=payload)
            if response.status_code == 200:
                order = response.json()
                await message.answer(f"✅ Order placed successfully!\nOrder ID: {order['id']}\nStatus: {order['status']}")
                carts[user_id] = [] # Clear cart
                await state.clear()
            else:
                await message.answer(f"Failed to place order: {response.text}")
        except Exception as e:
             await message.answer(f"Error submitting order: {e}")
