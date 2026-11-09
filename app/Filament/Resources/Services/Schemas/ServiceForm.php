<?php

namespace App\Filament\Resources\Services\Schemas;

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

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Service')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Basics')->icon('heroicon-o-pencil-square')->schema([
                    Grid::make(2)->schema([
                        ...Fields::titleWithSlug(),
                        TextInput::make('short')->label('Short summary')->required()->maxLength(255)->columnSpanFull()
                            ->helperText('Shown on service cards and lists.'),
                        Textarea::make('intro')->label('Hero introduction')->rows(3)->required()->columnSpanFull(),
                        Fields::iconSelect(),
                        FileUpload::make('image')->image()->directory('services'),
                        Fields::tags('tags', 'Hero labels')->helperText('Short words floated around the 3D cube.'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_published')->label('Published')->default(true),
                    ]),
                ]),
                Tab::make('Who it\'s for')->icon('heroicon-o-users')->schema([
                    Fields::titleTextList('audience', 'Audience', 'audience'),
                ]),
                Tab::make('Problems')->icon('heroicon-o-exclamation-triangle')->schema([
                    Repeater::make('problems')->label('Problems and fixes')
                        ->schema([
                            Textarea::make('problem')->rows(2)->required(),
                            Textarea::make('fix')->label('How we solve it')->rows(2)->required(),
                        ])->columns(2)
                        ->itemLabel(fn (array $state): ?string => $state['problem'] ?? null)
                        ->collapsible()->collapsed()->reorderable()->addActionLabel('Add problem'),
                ]),
                Tab::make('Capabilities')->icon('heroicon-o-sparkles')->schema([
                    Fields::titleTextList('features', 'Capabilities', 'capability'),
                    Fields::tags('deliverables', 'Deliverables'),
                ]),
                Tab::make('Process')->icon('heroicon-o-arrow-path')->schema([
                    Repeater::make('steps')->label('Project steps')
                        ->schema([
                            TextInput::make('title')->required(),
                            Textarea::make('text')->rows(2),
                            TextInput::make('output')->label('You get'),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsible()->collapsed()->reorderable()->addActionLabel('Add step'),
                ]),
                Tab::make('Stack & related')->icon('heroicon-o-cpu-chip')->schema([
                    Fields::tags('stack', 'Technology stack'),
                    Select::make('related_services')->label('Related services')->multiple()
                        ->options(fn () => Service::query()->orderBy('sort_order')->pluck('title', 'slug')),
                ]),
                Tab::make('FAQs')->icon('heroicon-o-question-mark-circle')->schema([Fields::faqs()]),
                Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([Fields::seo()]),
            ]),
        ]);
    }
}
