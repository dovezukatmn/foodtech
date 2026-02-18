from aiogram import Router, F
from aiogram.types import Message, CallbackQuery, ReplyKeyboardMarkup, KeyboardButton, ReplyKeyboardRemove
from aiogram.fsm.context import FSMContext
from aiogram.fsm.state import State, StatesGroup
import httpx
import os

router = Router()
BACKEND_URL = os.getenv("BACKEND_URL", "http://backend:8000/api/v1")

class Loyalty(StatesGroup):
    waiting_for_phone = State()

@router.callback_query(F.data == "check_loyalty")
async def check_loyalty_start(callback: CallbackQuery, state: FSMContext):
    await state.set_state(Loyalty.waiting_for_phone)
    await callback.message.answer(
        "Please share your phone number to check points:",
        reply_markup=ReplyKeyboardMarkup(
            keyboard=[[KeyboardButton(text="📱 Share Contact", request_contact=True)]],
            resize_keyboard=True,
            one_time_keyboard=True
        )
    )
    await callback.answer()

@router.message(Loyalty.waiting_for_phone)
async def process_loyalty_phone(message: Message, state: FSMContext):
    if message.contact:
        phone = message.contact.phone_number
    else:
        phone = message.text
        
    # Basic cleanup
    phone = phone.replace(" ", "").replace("-", "").replace("(", "").replace(")", "")
    
    async with httpx.AsyncClient() as client:
        try:
            response = await client.get(f"{BACKEND_URL}/loyalty/balance/{phone}")
            if response.status_code == 200:
                data = response.json()
                balance = data.get("balance", 0.0)
                await message.answer(
                    f"🎁 Your Balance: <b>{balance}</b> points.",
                    reply_markup=ReplyKeyboardRemove()
                )
            else:
                await message.answer("Failed to get balance. You might not be registered in the loyalty program.", reply_markup=ReplyKeyboardRemove())
        except Exception as e:
            await message.answer(f"Error checking balance: {e}", reply_markup=ReplyKeyboardRemove())
            
    await state.clear()
