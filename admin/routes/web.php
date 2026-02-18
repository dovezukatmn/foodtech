<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Пользователи
    Route::resource('users', UserController::class);

    // Категории
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

    // Продукты
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);

    // Модификаторы
    Route::resource('modifier-groups', \App\Http\Controllers\Admin\ModifierGroupController::class);
    Route::resource('modifiers', \App\Http\Controllers\Admin\ModifierController::class);

    // Заказы
    Route::get('orders/kanban', [\App\Http\Controllers\Admin\OrderController::class, 'kanban'])->name('orders.kanban');
    Route::post('orders/{order}/update-status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->only(['index', 'show']);
});

Auth::routes(); // Включаем стандартные маршруты аутентификации
