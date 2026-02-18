<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Заказы';
    protected static ?string $modelLabel = 'Заказ';
    protected static ?string $pluralModelLabel = 'Заказы';
    protected static ?string $navigationGroup = 'Управление';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о клиенте')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Имя клиента')
                            ->required(),

                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Телефон')
                            ->tel()
                            ->required(),

                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->rows(2),

                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий')
                            ->rows(2),
                    ])->columns(2),

                Forms\Components\Section::make('Детали заказа')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(Order::getStatusOptions())
                            ->required(),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Сумма')
                            ->numeric()
                            ->prefix('₽')
                            ->disabled(),

                        Forms\Components\TextInput::make('iiko_order_number')
                            ->label('Номер заказа в iiko')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable(),

                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Адрес')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::getStatusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => Order::getStatusColors()[$state] ?? 'gray')
                    ->icon(fn (string $state): string => Order::getStatusIcons()[$state] ?? 'heroicon-o-question-mark-circle'),

                Tables\Columns\TextColumn::make('iiko_order_number')
                    ->label('iiko №')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Позиции')
                    ->counts('items'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::getStatusOptions())
                    ->multiple()
                    ->placeholder('Все статусы'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Просмотр'),
                Tables\Actions\EditAction::make()->label('Изменить'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Заказы создаются через API, не вручную
    }
}
