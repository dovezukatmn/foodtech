<?php

namespace App\Filament\Resources\ModifierResource\Pages;

use App\Filament\Resources\ModifierResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateModifier extends CreateRecord
{
    protected static string $resource = ModifierResource::class;

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
