<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IikoService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $organizationId;
    protected string $terminalGroupId;

    public function __construct()
    {
        $this->apiUrl          = AppSetting::get('iiko_api_url', 'https://api-ru.iiko.services');
        $this->apiKey          = AppSetting::get('iiko_api_key', '');
        $this->organizationId  = AppSetting::get('iiko_organization_id', '');
        $this->terminalGroupId = AppSetting::get('iiko_terminal_group_id', '');
    }

    /**
     * Получить токен авторизации iiko
     * Токен живёт 15 минут, кэшируем на 14
     */
    public function getToken(): ?string
    {
        return Cache::remember('iiko_token', 840, function () {
            try {
                $response = Http::post("{$this->apiUrl}/api/1/access_token", [
                    'apiLogin' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $token = $response->json('token');
                    Log::info('iiko: Токен получен успешно');
                    return $token;
                }

                Log::error('iiko: Ошибка получения токена', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            } catch (\Exception $e) {
                Log::error('iiko: Исключение при получении токена', [
                    'message' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Проверить подключение к iiko
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'API ключ не указан'];
        }

        // Сбрасываем кэш токена для чистого теста
        Cache::forget('iiko_token');
        $token = $this->getToken();

        if ($token) {
            return ['success' => true, 'message' => 'Подключение установлено'];
        }

        return ['success' => false, 'message' => 'Не удалось получить токен'];
    }

    /**
     * Получить список организаций
     */
    public function getOrganizations(): array
    {
        $token = $this->getToken();
        if (!$token) return [];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->post("{$this->apiUrl}/api/1/organizations", [
                'returnAdditionalInfo' => false,
                'includeDisabled'      => false,
            ]);

            if ($response->successful()) {
                return $response->json('organizations', []);
            }

            Log::error('iiko: Ошибка получения организаций', ['body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('iiko: Исключение при получении организаций', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Получить меню из iiko (номенклатуру)
     */
    public function getMenu(): array
    {
        $token = $this->getToken();
        if (!$token) return [];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->post("{$this->apiUrl}/api/1/nomenclature", [
                'organizationId' => $this->organizationId,
            ]);

            if ($response->successful()) {
                Log::info('iiko: Меню получено', [
                    'groups'   => count($response->json('groups', [])),
                    'products' => count($response->json('products', [])),
                ]);
                return $response->json();
            }

            Log::error('iiko: Ошибка получения меню', ['body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('iiko: Исключение при получении меню', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Синхронизировать категории из iiko
     */
    public function syncCategories(): array
    {
        $menu = $this->getMenu();
        if (empty($menu)) return ['synced' => 0, 'error' => 'Не удалось получить меню'];

        $groups = $menu['groups'] ?? [];
        $synced = 0;

        foreach ($groups as $group) {
            \App\Models\Category::updateOrCreate(
                ['iiko_id' => $group['id']],
                [
                    'name'        => $group['name'],
                    'description' => $group['description'] ?? null,
                    'sort_order'  => $group['order'] ?? 0,
                    'image_url'   => !empty($group['images']) ? ($group['images'][0]['imageUrl'] ?? null) : null,
                ]
            );
            $synced++;
        }

        Log::info("iiko: Синхронизировано категорий: {$synced}");
        return ['synced' => $synced, 'error' => null];
    }

    /**
     * Синхронизировать продукты из iiko
     */
    public function syncProducts(): array
    {
        $menu = $this->getMenu();
        if (empty($menu)) return ['synced' => 0, 'error' => 'Не удалось получить меню'];

        $products = $menu['products'] ?? [];
        $synced = 0;

        foreach ($products as $product) {
            // Ищем категорию по iiko_id
            $category = \App\Models\Category::where('iiko_id', $product['parentGroup'])->first();

            \App\Models\Product::updateOrCreate(
                ['iiko_id' => $product['id']],
                [
                    'name'        => $product['name'],
                    'description' => $product['description'] ?? null,
                    'category_id' => $category?->id,
                    'price'       => $product['sizePrices'][0]['price']['currentPrice'] ?? 0,
                    'weight'      => $product['weight'] ?? null,
                    'image_url'   => !empty($product['images']) ? ($product['images'][0]['imageUrl'] ?? null) : null,
                    'is_hidden'   => !($product['isIncludedInMenu'] ?? true),
                ]
            );
            $synced++;
        }

        Log::info("iiko: Синхронизировано продуктов: {$synced}");
        return ['synced' => $synced, 'error' => null];
    }

    /**
     * Полная синхронизация меню (категории + продукты)
     */
    public function syncAll(): array
    {
        $categories = $this->syncCategories();
        $products = $this->syncProducts();

        return [
            'categories' => $categories,
            'products'   => $products,
        ];
    }

    /**
     * Создать заказ в iiko
     */
    public function createOrder(\App\Models\Order $order): array
    {
        $token = $this->getToken();
        if (!$token) return ['success' => false, 'error' => 'Нет токена iiko'];

        try {
            // Формируем позиции заказа
            $items = [];
            foreach ($order->items as $item) {
                $product = $item->product;
                if (!$product || !$product->iiko_id) continue;

                $orderItem = [
                    'productId' => $product->iiko_id,
                    'type'      => 'Product',
                    'amount'    => $item->quantity,
                ];

                // Модификаторы
                if (!empty($item->modifiers)) {
                    $modifiers = is_string($item->modifiers)
                        ? json_decode($item->modifiers, true)
                        : $item->modifiers;

                    if (is_array($modifiers)) {
                        $orderItem['modifiers'] = array_map(fn($mod) => [
                            'productId'       => $mod['iiko_id'] ?? $mod['id'] ?? '',
                            'amount'          => $mod['quantity'] ?? 1,
                            'productGroupId'  => $mod['group_iiko_id'] ?? $mod['group_id'] ?? '',
                        ], $modifiers);
                    }
                }

                $items[] = $orderItem;
            }

            $payload = [
                'organizationId'  => $this->organizationId,
                'terminalGroupId' => $this->terminalGroupId,
                'order' => [
                    'items' => $items,
                    'customer' => [
                        'name'  => $order->customer_name ?? 'Гость',
                        'phone' => $order->customer_phone,
                    ],
                    'comment' => $order->comment ?? '',
                ],
            ];

            // Если есть адрес — доставка
            if ($order->delivery_address) {
                $payload['order']['orderServiceType'] = 'DeliveryByClient';
                $payload['order']['deliveryPoint'] = [
                    'comment' => $order->delivery_address,
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->post("{$this->apiUrl}/api/1/deliveries/create", $payload);

            if ($response->successful()) {
                $result = $response->json();
                $iikoOrderId = $result['orderInfo']['id'] ?? null;

                if ($iikoOrderId) {
                    $order->update([
                        'iiko_order_id' => $iikoOrderId,
                        'status' => 'PENDING_IIKO',
                    ]);
                }

                Log::info('iiko: Заказ создан', ['order_id' => $order->id, 'iiko_id' => $iikoOrderId]);
                return ['success' => true, 'iiko_order_id' => $iikoOrderId];
            }

            $error = $response->json('errorDescription') ?? $response->body();
            Log::error('iiko: Ошибка создания заказа', ['error' => $error]);
            return ['success' => false, 'error' => $error];

        } catch (\Exception $e) {
            Log::error('iiko: Исключение при создании заказа', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Проверить статус заказа в iiko
     */
    public function checkOrderStatus(string $iikoOrderId): ?array
    {
        $token = $this->getToken();
        if (!$token) return null;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->post("{$this->apiUrl}/api/1/deliveries/by_id", [
                'organizationId' => $this->organizationId,
                'orderIds'       => [$iikoOrderId],
            ]);

            if ($response->successful()) {
                $orders = $response->json('orders', []);
                return $orders[0] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('iiko: Исключение при проверке статуса', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
