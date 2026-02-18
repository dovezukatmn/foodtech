<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Миграция: Настройки приложения (key-value)
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();         // Ключ настройки
            $table->string('group')->default('general'); // Группа (general, delivery, iiko...)
            $table->string('display_name');            // Название на русском
            $table->string('type')->default('string'); // string, text, number, boolean, json
            $table->text('value')->nullable();          // Значение
            $table->text('description')->nullable();    // Описание для подсказки
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
