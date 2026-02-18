<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Сидер: начальные настройки приложения
     */
    public function run(): void
    {
        $settings = [
            // === Основные ===
            [
                'key'          => 'app_name',
                'group'        => 'general',
                'display_name' => 'Название приложения',
                'type'         => 'string',
                'value'        => 'FoodTech',
                'description'  => 'Отображается в заголовке и уведомлениях',
            ],
            [
                'key'          => 'company_phone',
                'group'        => 'general',
                'display_name' => 'Телефон компании',
                'type'         => 'string',
                'value'        => '+7 (999) 123-45-67',
                'description'  => 'Основной телефон для связи',
            ],
            [
                'key'          => 'company_address',
                'group'        => 'general',
                'display_name' => 'Адрес компании',
                'type'         => 'text',
                'value'        => '',
                'description'  => 'Юридический или фактический адрес',
            ],
            [
                'key'          => 'work_hours',
                'group'        => 'general',
                'display_name' => 'Время работы',
                'type'         => 'string',
                'value'        => '10:00 - 22:00',
                'description'  => 'Часы приёма заказов',
            ],

            // === Доставка ===
            [
                'key'          => 'min_order_amount',
                'group'        => 'delivery',
                'display_name' => 'Минимальная сумма заказа (₽)',
                'type'         => 'number',
                'value'        => '500',
                'description'  => 'Заказы меньше этой суммы не принимаются',
            ],
            [
                'key'          => 'delivery_price',
                'group'        => 'delivery',
                'display_name' => 'Стоимость доставки (₽)',
                'type'         => 'number',
                'value'        => '200',
                'description'  => '0 = бесплатная доставка',
            ],
            [
                'key'          => 'free_delivery_from',
                'group'        => 'delivery',
                'display_name' => 'Бесплатная доставка от (₽)',
                'type'         => 'number',
                'value'        => '1500',
                'description'  => 'Сумма заказа, при которой доставка бесплатна',
            ],
            [
                'key'          => 'delivery_time_min',
                'group'        => 'delivery',
                'display_name' => 'Время доставки (мин.)',
                'type'         => 'string',
                'value'        => '30-60',
                'description'  => 'Примерное время доставки',
            ],
            [
                'key'          => 'delivery_zone_enabled',
                'group'        => 'delivery',
                'display_name' => 'Ограничение зоны доставки',
                'type'         => 'boolean',
                'value'        => 'false',
                'description'  => 'Включить зону доставки',
            ],

            // === iiko ===
            [
                'key'          => 'iiko_api_url',
                'group'        => 'iiko',
                'display_name' => 'URL API iiko',
                'type'         => 'string',
                'value'        => 'https://api-ru.iiko.services',
                'description'  => 'Базовый URL API iiko Cloud',
            ],
            [
                'key'          => 'iiko_api_key',
                'group'        => 'iiko',
                'display_name' => 'API ключ iiko',
                'type'         => 'string',
                'value'        => '',
                'description'  => 'Ключ для авторизации в API iiko',
            ],
            [
                'key'          => 'iiko_organization_id',
                'group'        => 'iiko',
                'display_name' => 'ID организации iiko',
                'type'         => 'string',
                'value'        => '',
                'description'  => 'UUID организации в iiko',
            ],
            [
                'key'          => 'iiko_terminal_group_id',
                'group'        => 'iiko',
                'display_name' => 'ID группы терминалов',
                'type'         => 'string',
                'value'        => '',
                'description'  => 'UUID группы терминалов для создания заказов',
            ],
            [
                'key'          => 'iiko_sync_enabled',
                'group'        => 'iiko',
                'display_name' => 'Синхронизация с iiko',
                'type'         => 'boolean',
                'value'        => 'false',
                'description'  => 'Автоматическая синхронизация меню',
            ],

            // === Оплата ===
            [
                'key'          => 'payment_cash_enabled',
                'group'        => 'payment',
                'display_name' => 'Оплата наличными',
                'type'         => 'boolean',
                'value'        => 'true',
                'description'  => 'Разрешить оплату при получении',
            ],
            [
                'key'          => 'payment_card_enabled',
                'group'        => 'payment',
                'display_name' => 'Оплата картой онлайн',
                'type'         => 'boolean',
                'value'        => 'false',
                'description'  => 'Разрешить онлайн-оплату',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        echo "✅ Создано/обновлено " . count($settings) . " настроек.\n";
    }
}
