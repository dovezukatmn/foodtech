<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Акции';
    protected static ?string $modelLabel = 'Акция';
    protected static ?string $pluralModelLabel = 'Акции';
    protected static ?string $navigationGroup = 'Маркетинг';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация об акции')
                    ->icon('heroicon-o-gift')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название акции')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Например: Скидка на первый заказ'),

                        Forms\Components\TextInput::make('promo_code')
                            ->label('Промокод')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('WELCOME20')
                            ->helperText('Уникальный код для активации скидки'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание условий')
                            ->rows(3)
                            ->maxLength(2000)
                            ->placeholder('Подробные условия акции')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Параметры скидки')
                    ->icon('heroicon-o-receipt-percent')
                    ->schema([
                        Forms\Components\Select::make('discount_type')
                            ->label('Тип скидки')
                            ->options(Promotion::getDiscountTypeOptions())
                            ->required()
                            ->default('percent')
                            ->live(),

                        Forms\Components\TextInput::make('discount_value')
                            ->label('Значение скидки')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percent' ? '%' : '₽'),

                        Forms\Components\TextInput::make('min_order_amount')
                            ->label('Минимальная сумма заказа')
                            ->numeric()
                            ->prefix('₽')
                            ->placeholder('Без ограничения')
                            ->helperText('Оставьте пустым, если нет минимума'),

                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Лимит использований')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Без лимита')
                            ->helperText('Сколько раз можно использовать промокод'),
                    ])->columns(2),

                Forms\Components\Section::make('Период действия')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->helperText('Отключите, чтобы приостановить акцию'),

                        Forms\Components\TextInput::make('usage_count')
                            ->label('Использовано раз')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Начало действия')
                            ->placeholder('Не ограничено'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Конец действия')
                            ->placeholder('Не ограничено')
                            ->after('starts_at'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('promo_code')
                    ->label('Промокод')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Скидка')
                    ->formatStateUsing(fn ($record) => $record->formatted_discount),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Использовано')
                    ->formatStateUsing(function ($record) {
                        $count = $record->usage_count;
                        $limit = $record->usage_limit;
                        return $limit ? "{$count} / {$limit}" : $count;
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Начало')
                    ->dateTime('d.m.Y')
                    ->placeholder('Сразу')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Конец')
                    ->dateTime('d.m.Y')
                    ->placeholder('Бессрочно')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные')
                    ->placeholder('Все'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Изменить'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
