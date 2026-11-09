<?php

namespace App\Filament\Resources\CarePlans\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CarePlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required(),
            TextInput::make('label')->label('Small label')->placeholder('Most flexible'),
            Textarea::make('for')->label('Who it is for')->rows(2)->columnSpanFull(),
            Fields::tags('points', 'What is included'),
            Toggle::make('is_highlighted')->label('Highlight this plan'),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }
}