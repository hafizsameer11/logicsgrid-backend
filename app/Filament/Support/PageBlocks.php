<?php

namespace App\Filament\Support;

use App\Models\CaseStudy;
use App\Models\Service;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

/**
 * Page builder blocks. Each block type maps 1:1 to a section component on the website
 * (see src/components/site/CmsSections.tsx in the site), so editors can add, remove,
 * reorder and edit every section of every page.
 */
class PageBlocks
{
    /** Heading fields every section shares. */
    private static function heading(bool $withText = true): array
    {
        return [
            Grid::make(2)->schema(array_filter([
                TextInput::make('eyebrow')->label('Small label above the heading'),
                TextInput::make('title')->label('Heading'),
                $withText ? Textarea::make('text')->label('Intro text')->rows(2)->columnSpanFull() : null,
                Toggle::make('alt')->label('Shaded background'),
            ])),
        ];
    }

    private static function items(string $name = 'items', string $label = 'Items', bool $icon = false): Repeater
    {
        return Repeater::make($name)->label($label)
            ->schema(array_filter([
                TextInput::make('title')->required(),
                Textarea::make('text')->rows(2),
                $icon ? Fields::iconSelect() : null,
            ]))
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
            ->collapsible()->collapsed()->reorderable()->cloneable();
    }

    /** Block list with item labels that show each section's heading in the collapsed builder. */
    public static function all(): array
    {
        return array_map(function (Block $block) {
            $name = $block->getLabel();

            return $block->label(fn (?array $state): string => filled($state['title'] ?? null) ? "{$name}: {$state['title']}" : $name);
        }, self::definitions());
    }

    private static function definitions(): array
    {
        return [
            Block::make('numbered_rows')->label('Numbered rows')->icon('heroicon-o-list-bullet')->schema([
                ...self::heading(),
                Select::make('columns')->options([1 => '1 column', 2 => '2 columns', 3 => '3 columns'])->default(2),
                self::items(icon: true),
            ]),
            Block::make('feature_tabs')->label('3D spotlight tabs')->icon('heroicon-o-cube')->schema([
                ...self::heading(),
                TagsInput::make('labels')->label('Labels around the cube'),
                self::items(icon: true),
            ]),
            Block::make('audience_list')->label('Big typographic rows')->icon('heroicon-o-users')->schema([
                ...self::heading(),
                self::items(),
            ]),
            Block::make('steps')->label('Timeline steps')->icon('heroicon-o-arrow-long-right')->schema([
                ...self::heading(),
                Repeater::make('items')->schema([
                    TextInput::make('title')->required(),
                    Textarea::make('text')->rows(2),
                    TextInput::make('output')->label('You get'),
                ])->itemLabel(fn (array $state): ?string => $state['title'] ?? null)->collapsible()->collapsed()->reorderable(),
            ]),
            Block::make('stages')->label('Stages with progress bar')->icon('heroicon-o-chart-bar')->schema([
                ...self::heading(),
                Repeater::make('items')->schema([
                    TextInput::make('title')->required(),
                    Textarea::make('text')->rows(2),
                    TextInput::make('help')->label('How we help'),
                ])->itemLabel(fn (array $state): ?string => $state['title'] ?? null)->collapsible()->collapsed()->reorderable(),
            ]),
            Block::make('problem_fix')->label('Problems and fixes table')->icon('heroicon-o-wrench-screwdriver')->schema([
                ...self::heading(),
                Repeater::make('rows')->schema([
                    Textarea::make('problem')->rows(2)->required(),
                    Textarea::make('fix')->rows(2)->required(),
                ])->columns(2)->itemLabel(fn (array $state): ?string => $state['problem'] ?? null)->collapsible()->collapsed()->reorderable(),
            ]),
            Block::make('cards')->label('Cards')->icon('heroicon-o-squares-2x2')->schema([
                ...self::heading(),
                Repeater::make('items')->schema([
                    TextInput::make('label')->label('Small label'),
                    TextInput::make('title')->required(),
                    Textarea::make('text')->rows(2),
                    TextInput::make('footnote'),
                    Toggle::make('highlight')->label('Highlight this card'),
                ])->itemLabel(fn (array $state): ?string => $state['title'] ?? null)->collapsible()->collapsed()->reorderable(),
                TextInput::make('note')->label('Note under the cards'),
            ]),
            Block::make('compare_table')->label('Comparison table')->icon('heroicon-o-table-cells')->schema([
                ...self::heading(),
                TagsInput::make('columns')->label('Column headings')->required(),
                Repeater::make('rows')->schema([
                    TextInput::make('label')->required(),
                    TagsInput::make('values')->helperText('One value per column, in order. Use "yes" for a dot and "no" for a dash.'),
                ])->itemLabel(fn (array $state): ?string => $state['label'] ?? null)->collapsible()->collapsed()->reorderable(),
            ]),
            Block::make('statement_pair')->label('Two big statements')->icon('heroicon-o-chat-bubble-left-right')->schema([
                Toggle::make('alt')->label('Shaded background'),
                Repeater::make('items')->schema([
                    TextInput::make('eyebrow'),
                    TextInput::make('title')->required(),
                    Textarea::make('text')->rows(3),
                    TextInput::make('link_label'),
                    TextInput::make('link_url')->helperText('For example /care-plans'),
                ])->maxItems(2)->itemLabel(fn (array $state): ?string => $state['title'] ?? null)->collapsible(),
            ]),
            Block::make('checklist')->label('Checklist with 3D layers')->icon('heroicon-o-check-circle')->schema([
                ...self::heading(),
                TagsInput::make('items')->label('Checklist items'),
                Toggle::make('two_columns')->label('Show checklist in two columns'),
                Repeater::make('principles')->label('Points with descriptions (optional)')->schema([
                    TextInput::make('title')->required(),
                    Textarea::make('text')->rows(2),
                ])->itemLabel(fn (array $state): ?string => $state['title'] ?? null)->collapsible()->collapsed()->reorderable(),
                TagsInput::make('layers')->label('3D layer labels')->helperText('Leave empty to hide the 3D stack.'),
            ]),
            Block::make('area_checklists')->label('Numbered checklists')->icon('heroicon-o-clipboard-document-check')->schema([
                ...self::heading(),
                Repeater::make('items')->schema([
                    TextInput::make('title')->required(),
                    TagsInput::make('points'),
                ])->itemLabel(fn (array $state): ?string => $state['title'] ?? null)->collapsible()->collapsed()->reorderable(),
            ]),
            Block::make('timeline')->label('Story timeline')->icon('heroicon-o-clock')->schema([
                ...self::heading(),
                Repeater::make('items')->schema([
                    TextInput::make('label'),
                    TextInput::make('title')->required(),
                    Textarea::make('text')->rows(2),
                ])->itemLabel(fn (array $state): ?string => $state['title'] ?? null)->collapsible()->collapsed()->reorderable(),
            ]),
            Block::make('value_grid')->label('Big numbered values')->icon('heroicon-o-star')->schema([
                ...self::heading(),
                self::items(),
            ]),
            Block::make('fact_grid')->label('Fact grid')->icon('heroicon-o-view-columns')->schema([
                ...self::heading(),
                self::items(),
            ]),
            Block::make('stats')->label('Company stats')->icon('heroicon-o-chart-pie')->schema([
                ...self::heading(),
                Toggle::make('animated')->label('Large animated counters (homepage style)'),
            ]),
            Block::make('problem_merge')->label('Homepage: fragments merge into logo')->icon('heroicon-o-puzzle-piece')->schema([
                ...self::heading(),
                TextInput::make('subtitle')->label('Second heading line'),
                TagsInput::make('fragments')->label('Floating fragments')->helperText('Four short labels that fly in and merge.'),
                TextInput::make('logo_caption'),
                Repeater::make('rows')->label('Comparison rows')->schema([
                    TextInput::make('label')->required(),
                    TextInput::make('pain'),
                    TextInput::make('ours')->label('Our answer'),
                ])->columns(3)->itemLabel(fn (array $state): ?string => $state['label'] ?? null)->collapsible()->collapsed()->reorderable(),
            ]),
            Block::make('why_grid')->label('Homepage: why choose us grid')->icon('heroicon-o-trophy')->schema([
                ...self::heading(),
                self::items(),
            ]),
            Block::make('scroll_process')->label('Homepage: scroll-drawn process')->icon('heroicon-o-arrows-up-down')->schema([
                ...self::heading(),
                self::items(),
            ]),
            Block::make('marquee')->label('Scrolling tags')->icon('heroicon-o-arrows-right-left')->schema([
                ...self::heading(),
                TagsInput::make('items')->helperText('Leave empty to use the technology list.'),
                TextInput::make('link_label'),
                TextInput::make('link_url'),
            ]),
            Block::make('services')->label('Services list')->icon('heroicon-o-briefcase')->schema([
                ...self::heading(),
                Select::make('style')->options(['cards' => 'Cards', 'list' => 'Big rows'])->default('list'),
                Select::make('services')->multiple()->helperText('Leave empty to show all services.')
                    ->options(fn () => Service::query()->orderBy('sort_order')->pluck('title', 'slug')),
            ]),
            Block::make('work')->label('Case studies')->icon('heroicon-o-photo')->schema([
                ...self::heading(),
                Toggle::make('filters')->label('Show category filters'),
                Select::make('case_studies')->multiple()->helperText('Leave empty to show all.')
                    ->options(fn () => CaseStudy::query()->orderBy('sort_order')->pluck('name', 'slug')),
            ]),
            Block::make('care_plans')->label('Care plans and comparison')->icon('heroicon-o-heart')->schema([
                ...self::heading(),
            ]),
            Block::make('tech_groups')->label('Technology groups')->icon('heroicon-o-cpu-chip')->schema([
                ...self::heading(),
            ]),
            Block::make('contact_form')->label('Contact form')->icon('heroicon-o-envelope')->schema([
                ...self::heading(false),
                Select::make('variant')->options(['contact' => 'Project enquiry', 'audit' => 'Free audit request'])->default('contact'),
                TextInput::make('aside_title')->label('Side box heading'),
                TagsInput::make('aside_items')->label('Side box checklist'),
            ]),
            Block::make('contact_channels')->label('Contact channels')->icon('heroicon-o-phone')->schema([
                Toggle::make('alt')->label('Shaded background'),
            ]),
            Block::make('rich_text')->label('Rich text')->icon('heroicon-o-document-text')->schema([
                ...self::heading(false),
                RichEditor::make('content')->columnSpanFull(),
            ]),
            Block::make('faq')->label('FAQs')->icon('heroicon-o-question-mark-circle')->schema([
                TextInput::make('title')->label('Heading')->default('Common questions'),
                Toggle::make('use_common')->label('Use the shared FAQs from Site settings'),
                Fields::faqs('items', 'Questions'),
            ]),
            Block::make('cta')->label('Call to action banner')->icon('heroicon-o-megaphone')->schema([
                TextInput::make('title')->helperText('Leave empty to use the default from Site settings.'),
                Textarea::make('text')->rows(2),
                TextInput::make('button_label'),
            ]),
        ];
    }
}
