<?php

namespace App\Filament\Resources\Enquiries\Tables;

use App\Filament\Resources\Enquiries\Schemas\EnquiryForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('M j, Y H:i')->sortable(),
                TextColumn::make('name')->searchable()->weight('medium')->description(fn ($record) => $record->email),
                TextColumn::make('service')->limit(30),
                TextColumn::make('budget'),
                TextColumn::make('type')->badge()->color('gray'),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => EnquiryForm::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'new' => 'warning', 'won' => 'success', 'lost' => 'danger', default => 'info' }),
            ])
            ->filters([SelectFilter::make('status')->options(EnquiryForm::STATUSES), SelectFilter::make('type')->options(['contact' => 'Contact', 'audit' => 'Free audit'])])
            ->recordActions([EditAction::make()->label('Open')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}