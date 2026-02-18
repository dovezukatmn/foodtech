<?php

namespace App\Filament\Resources\ModifierResource\Pages;

use App\Filament\Resources\ModifierResource;
use Filament\Resources\Pages\ListRecords;

class ListModifiers extends ListRecords
{
    protected static string $resource = ModifierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()->label('Создать модификатор'),
        ];
    }
}
