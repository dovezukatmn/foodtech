<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Последние заказы';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest('created_at')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Телефон'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::getStatusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => Order::getStatusColors()[$state] ?? 'gray')
                    ->icon(fn (string $state): string => Order::getStatusIcons()[$state] ?? 'heroicon-o-question-mark-circle'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Позиции')
                    ->counts('items'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Просмотр')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record)),
            ])
            ->paginated(false);
    }
}
