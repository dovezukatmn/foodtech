from aiogram import Router, F
from aiogram.filters import Command
from aiogram.types import Message, CallbackQuery, InlineKeyboardMarkup, InlineKeyboardButton
import httpx
import os

router = Router()
BACKEND_URL = os.getenv("BACKEND_URL", "http://backend:8000/api/v1")

@router.message(Command("start"))
async def cmd_start(message: Message):
    await message.answer(
        "Welcome to FoodTech Delivery! 🍣🍕\nWhat would you like to order?",
        reply_markup=InlineKeyboardMarkup(inline_keyboard=[[
            InlineKeyboardButton(text="📋 Menu", callback_data="show_menu"),
            InlineKeyboardButton(text="🛒 Cart", callback_data="show_cart")
        ], [
            InlineKeyboardButton(text="🎁 My Points", callback_data="check_loyalty")
        ]])
    )

@router.callback_query(F.data == "show_menu")
async def show_menu(callback: CallbackQuery):
    async with httpx.AsyncClient() as client:
        try:
            response = await client.get(f"{BACKEND_URL}/menu/tree")
            response.raise_for_status()
            menu_tree = response.json()
            
            keyboard = []
            for cat in menu_tree:
                keyboard.append([InlineKeyboardButton(text=cat['name'], callback_data=f"cat_{cat['id']}")])
            
            # Add Cart button at bottom
            keyboard.append([InlineKeyboardButton(text="🛒 Cart", callback_data="show_cart")])
            
            await callback.message.edit_text("Select a category:", reply_markup=InlineKeyboardMarkup(inline_keyboard=keyboard))
        except Exception as e:
            await callback.message.answer(f"Failed to load menu: {e}")
            await callback.answer()

@router.callback_query(F.data.startswith("cat_"))
async def show_category(callback: CallbackQuery):
    cat_id = callback.data.split("_")[1]
    # We need to fetch products for this category.
    # Our menu/tree endpoint returns nested structure.
    # To optimize, we could either traverse the tree here (cached?) or have a dedicated backend endpoint for category products.
    # For MVP, let's fetch tree again and find the category.
    async with httpx.AsyncClient() as client:
        try:
            response = await client.get(f"{BACKEND_URL}/menu/tree") # Inefficient for large menus, but simple.
            menu_tree = response.json()
            
            # Find category
            category = None
            for cat in menu_tree:
                if cat['id'] == cat_id:
                    category = cat
                    break
                # Check subcats if any (recursive search needed if 2+ levels)
            
            if category:
                keyboard = []
                for product in category.get('products', []):
                     keyboard.append([InlineKeyboardButton(text=f"{product['name']} - {product['price']}₽", callback_data=f"prod_{product['id']}")])
                
                keyboard.append([
                    InlineKeyboardButton(text="🔙 Back", callback_data="show_menu"),
                    InlineKeyboardButton(text="🛒 Cart", callback_data="show_cart")
                ])
                await callback.message.edit_text(f"Category: {category['name']}", reply_markup=InlineKeyboardMarkup(inline_keyboard=keyboard))
            else:
                await callback.answer("Category not found")

        except Exception as e:
            await callback.message.answer(f"Error: {e}")
