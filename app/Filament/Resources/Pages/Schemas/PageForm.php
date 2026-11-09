<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Support\Fields;
use App\Filament\Support\PageBlocks;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Page')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Hero')->icon('heroicon-o-sparkles')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->label('Page name (admin only)')->required(),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)
                            ->helperText('"home" is the homepage. Other slugs match the page address, e.g. "about".'),
                        TextInput::make('hero_eyebrow')->label('Small label'),
                        Toggle::make('is_published')->label('Published')->default(true),
                        Textarea::make('hero_title')->label('Headline')->rows(2)->required()->columnSpanFull()
                            ->helperText('On the homepage, put each line of the headline on its own line.'),
                        Textarea::make('hero_text')->label('Intro text')->rows(3)->columnSpanFull(),
                        Fields::tags('hero_labels', 'Labels around the 3D cube'),
                        FileUpload::make('hero_image')->label('Hero image (optional, replaces the cube)')->image()->directory('pages'),
                    ]),
                ]),
                Tab::make('Sections')->icon('heroicon-o-squares-plus')->schema([
                    Builder::make('sections')->label('Page sections')
                        ->blocks(PageBlocks::all())
                        ->blockPickerColumns(3)
                        ->collapsible()->collapsed()->cloneable()->reorderableWithButtons()
                        ->addActionLabel('Add section')
                        ->columnSpanFull(),
                ]),
                Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([Fields::seo()]),
            ]),
        ]);
    }
}
