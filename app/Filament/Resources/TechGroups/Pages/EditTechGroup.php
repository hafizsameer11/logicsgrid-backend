<?php

namespace App\Filament\Resources\TechGroups\Pages;

use App\Filament\Resources\TechGroups\TechGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTechGroup extends EditRecord
{
    protected static string $resource = TechGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
