<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnquiryForm
{
    public const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'proposal' => 'Proposal sent', 'won' => 'Won', 'lost' => 'Lost'];

    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Enquiry')->columnSpan(2)->columns(2)->schema([
                TextInput::make('name')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('phone')->disabled(),
                TextInput::make('company')->disabled(),
                TextInput::make('website')->disabled(),
                TextInput::make('service')->disabled(),
                TextInput::make('budget')->disabled(),
                TextInput::make('type')->disabled(),
                Textarea::make('details')->rows(6)->disabled()->columnSpanFull(),
            ]),
            Section::make('Follow-up')->columnSpan(1)->schema([
                Select::make('status')->options(self::STATUSES)->required(),
                Textarea::make('notes')->label('Internal notes')->rows(8),
            ]),
        ]);
    }
}