<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Позиции заказа';
    protected static ?string $modelLabel = 'Позиция';
    protected static ?string $pluralModelLabel = 'Позиции';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Продукт')
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Итого')
                    ->state(fn ($record) => $record->price * $record->quantity)
                    ->money('RUB'),

                Tables\Columns\TextColumn::make('modifiers_json')
                    ->label('Модификаторы')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->defaultSort('id')
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true; // Позиции нельзя редактировать из админки
    }
}
