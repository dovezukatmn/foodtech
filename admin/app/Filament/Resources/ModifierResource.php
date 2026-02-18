<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModifierResource\Pages;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ModifierResource extends Resource
{
    protected static ?string $model = Modifier::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationLabel = 'Модификаторы';
    protected static ?string $modelLabel = 'Модификатор';
    protected static ?string $pluralModelLabel = 'Модификаторы';
    protected static ?string $navigationGroup = 'Меню';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->placeholder('Например: Сырный борт, Кетчуп, Большая')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('group_id')
                            ->label('Группа модификаторов')
                            ->options(
                                ModifierGroup::all()->mapWithKeys(function ($group) {
                                    $productName = $group->product?->name ?? 'Без продукта';
                                    return [$group->id => "{$group->name} ({$productName})"];
                                })
                            )
                            ->searchable()
                            ->required()
                            ->helperText('К какой группе относится этот модификатор'),

                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->prefix('₽')
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Дополнительная стоимость модификатора'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Системная информация')
                    ->schema([
                        Forms\Components\TextInput::make('iiko_id')
                            ->label('iiko ID')
                            ->disabled()
                            ->dehydrated(false),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('Группа')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('group.product.name')
                    ->label('Продукт')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('iiko_id')
                    ->label('iiko ID')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('Группа')
                    ->options(
                        ModifierGroup::all()->pluck('name', 'id')
                    ),
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
            'index' => Pages\ListModifiers::route('/'),
            'create' => Pages\CreateModifier::route('/create'),
            'edit' => Pages\EditModifier::route('/{record}/edit'),
        ];
    }
}
