<?php

namespace App\Filament\Resources\CaseStudies\Tables;

use App\Filament\Resources\CaseStudies\Schemas\CaseStudyForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CaseStudiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('cover_image')->label('')->square(),
                TextColumn::make('name')->searchable()->weight('medium')->description(fn ($record) => $record->client_type),
                TextColumn::make('category')->badge(),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable(),
            ])
            ->filters([SelectFilter::make('category')->options(CaseStudyForm::CATEGORIES)])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
