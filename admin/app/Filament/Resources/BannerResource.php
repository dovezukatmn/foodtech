<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Баннеры';
    protected static ?string $modelLabel = 'Баннер';
    protected static ?string $pluralModelLabel = 'Баннеры';
    protected static ?string $navigationGroup = 'Маркетинг';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Содержимое баннера')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Например: Скидка 20% на пиццу!'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Подробности акции или предложения'),

                        Forms\Components\TextInput::make('image_url')
                            ->label('URL изображения')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://example.com/banner.jpg'),

                        Forms\Components\TextInput::make('link_url')
                            ->label('Ссылка при нажатии')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://example.com/promo')
                            ->helperText('Куда перейдёт пользователь при нажатии на баннер'),
                    ])->columns(2),

                Forms\Components\Section::make('Настройки отображения')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Отключите, чтобы скрыть баннер'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->helperText('Чем меньше число, тем выше в списке'),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Начало показа')
                            ->placeholder('Не ограничено')
                            ->helperText('Оставьте пустым для немедленного показа'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Конец показа')
                            ->placeholder('Не ограничено')
                            ->helperText('Оставьте пустым для бессрочного показа')
                            ->after('starts_at'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Начало')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Сразу')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Конец')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Бессрочно')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
