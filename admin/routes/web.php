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

    // Здесь добавятся другие routes: orders
});

Auth::routes(); // Включаем стандартные маршруты аутентификации
