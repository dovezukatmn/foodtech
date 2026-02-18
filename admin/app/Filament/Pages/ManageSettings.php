<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ManageSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Настройки';
    protected static ?string $title = 'Настройки приложения';
    protected static ?string $navigationGroup = 'Управление';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.manage-settings';

    public array $settings = [];

    public function mount(): void
    {
        $allSettings = AppSetting::all();

        foreach ($allSettings as $setting) {
            $this->settings[$setting->key] = $setting->value;
        }
    }

    /**
     * Получить настройки по группе для отображения
     */
    public function getSettingsByGroup(): array
    {
        $groups = AppSetting::getGroupOptions();
        $result = [];

        foreach ($groups as $groupKey => $groupLabel) {
            $settings = AppSetting::getByGroup($groupKey);
            if ($settings->isNotEmpty()) {
                $result[$groupKey] = [
                    'label' => $groupLabel,
                    'settings' => $settings,
                ];
            }
        }

        return $result;
    }

    /**
     * Сохранить все настройки
     */
    public function save(): void
    {
        foreach ($this->settings as $key => $value) {
            AppSetting::set($key, $value);
        }

        Notification::make()
            ->title('Настройки сохранены')
            ->body('Все изменения успешно применены')
            ->success()
            ->send();
    }

    /**
     * Действия в шапке страницы
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить настройки')
                ->icon('heroicon-o-check')
                ->action('save')
                ->color('primary'),
        ];
    }
}
