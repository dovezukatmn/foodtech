<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $ordersToday = Order::where('created_at', '>=', $today)->count();
        $revenueToday = Order::where('created_at', '>=', $today)
            ->where('status', '!=', 'CANCELLED')
            ->sum('total_amount');
        $activeOrders = Order::whereNotIn('status', ['DELIVERED', 'CANCELLED'])->count();
        $cancelledToday = Order::where('created_at', '>=', $today)
            ->where('status', 'CANCELLED')
            ->count();

        return [
            Stat::make('Заказов сегодня', $ordersToday)
                ->description('Всего за сегодня')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, $ordersToday]),

            Stat::make('Выручка сегодня', number_format($revenueToday, 0, ',', ' ') . ' ₽')
                ->description('Без учёта отменённых')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Активные заказы', $activeOrders)
                ->description('В обработке прямо сейчас')
                ->descriptionIcon('heroicon-o-fire')
                ->color('warning'),

            Stat::make('Отменено сегодня', $cancelledToday)
                ->description('Отменённые за сегодня')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
