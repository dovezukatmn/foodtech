<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Пользователи';
    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $pluralModelLabel = 'Пользователи';
    protected static ?string $navigationGroup = 'Управление';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Электронная почта')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('role_id')
                            ->label('Роль')
                            ->relationship('role', 'display_name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->helperText('Определяет уровень доступа пользователя'),
                    ])->columns(2),

                Forms\Components\Section::make('Безопасность')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->minLength(8)
                            ->helperText('Минимум 8 символов. При редактировании оставьте пустым, чтобы не менять.'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Подтвердите пароль')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Почта')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Подтверждён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('Не подтверждён'),

                Tables\Columns\TextColumn::make('role.display_name')
                    ->label('Роль')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Без роли'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата регистрации')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->label('Роль')
                    ->relationship('role', 'display_name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Изменить'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранных'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
