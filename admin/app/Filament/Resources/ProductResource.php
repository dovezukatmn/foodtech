<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Продукты';
    protected static ?string $modelLabel = 'Продукт';
    protected static ?string $pluralModelLabel = 'Продукты';
    protected static ?string $navigationGroup = 'Меню';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('category_id')
                            ->label('Категория')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),

                        Forms\Components\TextInput::make('weight')
                            ->label('Вес (г)')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('image_url')
                            ->label('URL изображения')
                            ->url()
                            ->maxLength(500),

                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Скрыт из меню')
                            ->default(false),
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

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Вес (г)')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Скрыт')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('iiko_id')
                    ->label('iiko ID')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->options(Category::all()->pluck('name', 'id')),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Скрытые')
                    ->trueLabel('Только скрытые')
                    ->falseLabel('Только видимые')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
