<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModifierGroupResource\Pages;
use App\Models\ModifierGroup;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ModifierGroupResource extends Resource
{
    protected static ?string $model = ModifierGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Группы модификаторов';
    protected static ?string $modelLabel = 'Группа модификаторов';
    protected static ?string $pluralModelLabel = 'Группы модификаторов';
    protected static ?string $navigationGroup = 'Меню';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->placeholder('Например: Размер пиццы, Соусы, Топпинги')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('product_id')
                            ->label('Продукт')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('К какому продукту относится эта группа'),
                    ])->columns(2),

                Forms\Components\Section::make('Ограничения выбора')
                    ->description('Сколько модификаторов из этой группы можно выбрать')
                    ->schema([
                        Forms\Components\TextInput::make('min_quantity')
                            ->label('Минимум')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('0 = необязательный выбор'),

                        Forms\Components\TextInput::make('max_quantity')
                            ->label('Максимум')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('Максимальное количество выбираемых опций'),
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

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Продукт')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('Мин.')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('max_quantity')
                    ->label('Макс.')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('modifiers_count')
                    ->label('Модификаторов')
                    ->counts('modifiers')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('iiko_id')
                    ->label('iiko ID')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Продукт')
                    ->options(Product::all()->pluck('name', 'id')),
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
            'index' => Pages\ListModifierGroups::route('/'),
            'create' => Pages\CreateModifierGroup::route('/create'),
            'edit' => Pages\EditModifierGroup::route('/{record}/edit'),
        ];
    }
}
