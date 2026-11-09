<?php

namespace App\Filament\Resources\TechGroups\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TechGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->required(),
            TextInput::make('sort_order')->numeric()->default(0),
            TextInput::make('text')->label('Description')->columnSpanFull(),
            Fields::tags('core', 'Core tools (highlighted)'),
            Fields::tags('also', 'Also used'),
        ]);
    }
}