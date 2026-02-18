<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OrderKanban extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Канбан заказов';
    protected static ?string $title = 'Канбан-доска заказов';
    protected static ?string $navigationGroup = 'Управление';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.order-kanban';

    /**
     * Статусы для отображения на канбан-доске (без завершённых)
     */
    public function getKanbanStatuses(): array
    {
        return [
            'CREATED'      => ['label' => 'Создан',          'color' => '#f59e0b', 'icon' => '🕐'],
            'PENDING_IIKO' => ['label' => 'Ожидает iiko',    'color' => '#3b82f6', 'icon' => '🔄'],
            'CONFIRMED'    => ['label' => 'Подтверждён',     'color' => '#8b5cf6', 'icon' => '✅'],
            'COOKING'      => ['label' => 'Готовится',       'color' => '#f97316', 'icon' => '🔥'],
            'READY'        => ['label' => 'Готов к выдаче',  'color' => '#22c55e', 'icon' => '📦'],
            'DELIVERING'   => ['label' => 'В доставке',      'color' => '#06b6d4', 'icon' => '🚚'],
        ];
    }

    /**
     * Получить заказы по статусам
     */
    public function getOrdersByStatus(): array
    {
        $statuses = array_keys($this->getKanbanStatuses());

        $orders = Order::whereIn('status', $statuses)
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [];
        foreach ($statuses as $status) {
            $grouped[$status] = $orders->where('status', $status)->values();
        }

        return $grouped;
    }

    /**
     * Сменить статус заказа (вызывается через wire:click)
     */
    public function moveOrder(string $orderId, string $newStatus): void
    {
        $order = Order::find($orderId);

        if (!$order) {
            Notification::make()
                ->title('Ошибка')
                ->body('Заказ не найден')
                ->danger()
                ->send();
            return;
        }

        if (!$order->canTransitionTo($newStatus)) {
            Notification::make()
                ->title('Нельзя сменить статус')
                ->body("Переход из «{$order->status_label}» в «" . (Order::getStatusOptions()[$newStatus] ?? $newStatus) . "» недопустим")
                ->warning()
                ->send();
            return;
        }

        $oldLabel = $order->status_label;
        $order->update(['status' => $newStatus]);
        $newLabel = Order::getStatusOptions()[$newStatus] ?? $newStatus;

        Notification::make()
            ->title('Статус обновлён')
            ->body("Заказ #{$order->iiko_order_number}: {$oldLabel} → {$newLabel}")
            ->success()
            ->send();
    }
}
