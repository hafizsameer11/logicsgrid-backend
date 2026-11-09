<?php

namespace App\Filament\Resources\CarePlans\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarePlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->weight('medium')->description(fn ($record) => $record->for),
                IconColumn::make('is_highlighted')->label('Highlighted')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}