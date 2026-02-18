<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Категории';
    protected static ?string $modelLabel = 'Категория';
    protected static ?string $pluralModelLabel = 'Категории';
    protected static ?string $navigationGroup = 'Меню';
    protected static ?int $navigationSort = 1;

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

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\TextInput::make('order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('image_url')
                            ->label('URL изображения')
                            ->url()
                            ->maxLength(500),
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

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Товаров')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Сортировка')
                    ->sortable(),

                Tables\Columns\TextColumn::make('iiko_id')
                    ->label('iiko ID')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->filters([])
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
