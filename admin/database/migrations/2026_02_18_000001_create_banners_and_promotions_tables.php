<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Миграция: Баннеры и Акции
     */
    public function up(): void
    {
        // Баннеры — слайды на главной странице
        Schema::create('banners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');               // Заголовок баннера
            $table->text('description')->nullable(); // Описание
            $table->string('image_url')->nullable(); // URL изображения
            $table->string('link_url')->nullable();  // Ссылка при нажатии
            $table->integer('sort_order')->default(0); // Порядок отображения
            $table->boolean('is_active')->default(true); // Активен ли
            $table->timestamp('starts_at')->nullable();   // Начало показа
            $table->timestamp('ends_at')->nullable();     // Конец показа
            $table->timestamps();
        });

        // Акции — скидки, промокоды
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');                  // Название акции
            $table->text('description')->nullable();  // Описание условий
            $table->string('promo_code')->nullable()->unique(); // Промокод
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent'); // Тип скидки
            $table->decimal('discount_value', 10, 2)->default(0); // Значение скидки
            $table->decimal('min_order_amount', 10, 2)->nullable(); // Мин. сумма заказа
            $table->integer('usage_limit')->nullable();  // Лимит использований
            $table->integer('usage_count')->default(0);  // Сколько раз использовано
            $table->boolean('is_active')->default(true); // Активна ли
            $table->timestamp('starts_at')->nullable();  // Начало действия
            $table->timestamp('ends_at')->nullable();    // Конец действия
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('banners');
    }
};
