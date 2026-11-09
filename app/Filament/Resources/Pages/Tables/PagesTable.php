<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium')->description(fn ($record) => $record->hero_title),
                TextColumn::make('slug')->color('gray')->formatStateUsing(fn (string $state) => $state === 'home' ? '/' : "/{$state}"),
                TextColumn::make('sections')->label('Sections')->state(fn ($record) => count($record->sections ?? [])),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
