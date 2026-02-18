<?php

namespace App\Filament\Resources\ModifierGroupResource\Pages;

use App\Filament\Resources\ModifierGroupResource;
use Filament\Resources\Pages\ListRecords;

class ListModifierGroups extends ListRecords
{
    protected static string $resource = ModifierGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()->label('Создать группу'),
        ];
    }
}
