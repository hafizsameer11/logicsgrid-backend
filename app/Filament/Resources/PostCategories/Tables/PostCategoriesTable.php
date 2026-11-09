<?php

namespace App\Filament\Resources\PostCategories\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('posts_count')->counts('posts')->label('Posts'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}