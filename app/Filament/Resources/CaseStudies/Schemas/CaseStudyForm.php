<?php

namespace App\Filament\Resources\CaseStudies\Schemas;

use App\Filament\Support\Fields;
use App\Models\Service;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CaseStudyForm
{
    public const CATEGORIES = ['SaaS' => 'SaaS', 'Web' => 'Web', 'Mobile' => 'Mobile', 'Booking & management' => 'Booking & management', 'AI' => 'AI'];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Project')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Basics')->icon('heroicon-o-pencil-square')->schema([
                    Grid::make(2)->schema([
                        ...Fields::titleWithSlug('name', 'Project name'),
                        TextInput::make('client_type')->label('Client type'),
                        Select::make('category')->options(self::CATEGORIES)->required(),
                        TextInput::make('platform'),
                        TextInput::make('project_url')->label('Live project link')->url(),
                        Textarea::make('summary')->rows(2)->required()->columnSpanFull(),
                        Textarea::make('challenge')->rows(3)->columnSpanFull(),
                        Textarea::make('solution')->rows(3)->columnSpanFull(),
                        Select::make('services')->label('Services used')->multiple()
                            ->options(fn () => Service::query()->orderBy('sort_order')->pluck('title', 'slug')),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_published')->label('Published')->default(true),
                    ]),
                ]),
                Tab::make('Images')->icon('heroicon-o-photo')->schema([
                    FileUpload::make('cover_image')->image()->directory('work')->imageEditor(),
                    FileUpload::make('gallery')->label('Screenshots')->image()->multiple()->reorderable()->directory('work'),
                ]),
                Tab::make('Features & scope')->icon('heroicon-o-list-bullet')->schema([
                    Fields::titleTextList('features', 'Key features', 'feature'),
                    Fields::tags('scope', 'Our scope'),
                    Fields::tags('stack', 'Technology'),
                ]),
                Tab::make('Results & quote')->icon('heroicon-o-chart-bar')->schema([
                    Repeater::make('results')->label('Measured results')
                        ->helperText('Only add numbers the client has confirmed and approved for publishing.')
                        ->schema([TextInput::make('value')->required(), TextInput::make('label')->required()])->columns(2)
                        ->itemLabel(fn (array $state): ?string => trim(($state['value'] ?? '').' '.($state['label'] ?? '')) ?: null),
                    Textarea::make('quote')->label('Client quote')->rows(3)->helperText('Only publish with the client\'s permission.'),
                    TextInput::make('quote_author')->label('Quote author'),
                ]),
                Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([Fields::seo()]),
            ]),
        ]);
    }
}
