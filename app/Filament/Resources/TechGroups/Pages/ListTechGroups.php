<?php

namespace App\Filament\Resources\TechGroups\Pages;

use App\Filament\Resources\TechGroups\TechGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTechGroups extends ListRecords
{
    protected static string $resource = TechGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
