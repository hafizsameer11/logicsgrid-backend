<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('from_path')->label('Old path')->required()->placeholder('/old-page')->unique(ignoreRecord: true),
            TextInput::make('to_path')->label('New path or URL')->required()->placeholder('/new-page'),
            Select::make('status_code')->options([301 => '301 permanent', 302 => '302 temporary'])->default(301),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }
}