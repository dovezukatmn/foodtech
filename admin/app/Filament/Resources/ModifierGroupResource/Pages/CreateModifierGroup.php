<?php

namespace App\Filament\Resources\ModifierGroupResource\Pages;

use App\Filament\Resources\ModifierGroupResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateModifierGroup extends CreateRecord
{
    protected static string $resource = ModifierGroupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id'] = (string) Str::uuid();
        $data['iiko_id'] = (string) Str::uuid(); // Временный UUID для ручного создания
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
