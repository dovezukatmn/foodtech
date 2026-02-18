<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Информация о клиенте')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_name')
                            ->label('Имя клиента'),

                        Infolists\Components\TextEntry::make('customer_phone')
                            ->label('Телефон')
                            ->icon('heroicon-o-phone'),

                        Infolists\Components\TextEntry::make('delivery_address')
                            ->label('Адрес доставки')
                            ->icon('heroicon-o-map-pin')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('comment')
                            ->label('Комментарий')
                            ->placeholder('Нет комментария')
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Детали заказа')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('Статус')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Order::getStatusOptions()[$state] ?? $state)
                            ->color(fn (string $state): string => Order::getStatusColors()[$state] ?? 'gray')
                            ->icon(fn (string $state): string => Order::getStatusIcons()[$state] ?? 'heroicon-o-question-mark-circle'),

                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Сумма')
                            ->money('RUB'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Дата создания')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('iiko_order_number')
                            ->label('Номер в iiko')
                            ->placeholder('Нет'),
                    ])->columns(4),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $order = $this->record;
        $nextStatuses = $order->getNextStatuses();
        $actions = [];

        foreach ($nextStatuses as $status => $label) {
            $color = Order::getStatusColors()[$status] ?? 'gray';
            $icon = Order::getStatusIcons()[$status] ?? 'heroicon-o-arrow-right';

            $actions[] = Actions\Action::make("transition_{$status}")
                ->label($label)
                ->icon($icon)
                ->color($color)
                ->requiresConfirmation()
                ->modalHeading("Сменить статус на \"{$label}\"?")
                ->modalDescription("Вы уверены, что хотите перевести заказ в статус \"{$label}\"?")
                ->modalSubmitActionLabel('Да, сменить')
                ->modalCancelActionLabel('Отмена')
                ->action(function () use ($status) {
                    $this->record->update(['status' => $status]);
                    $this->refreshFormData(['status']);
                });
        }

        $actions[] = Actions\EditAction::make()->label('Редактировать');

        return $actions;
    }
}
