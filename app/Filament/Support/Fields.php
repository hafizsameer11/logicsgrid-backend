<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/** Reusable form pieces shared by every content resource. */
class Fields
{
    /** Title input that fills the slug while the slug is still empty or untouched. */
    public static function titleWithSlug(string $title = 'title', string $label = 'Title'): array
    {
        return [
            TextInput::make($title)->label($label)->required()->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                    if (blank($get('slug')) || $get('slug') === Str::slug((string) $old)) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)
                ->helperText('Used in the page address. Lowercase words separated by hyphens.'),
        ];
    }

    public static function seo(): Section
    {
        return Section::make('SEO')
            ->description('How this page appears in Google and when shared on social media. Leave blank to use the defaults.')
            ->collapsible()
            ->columns(2)
            ->schema([
                TextInput::make('seo_title')->label('Meta title')->maxLength(70)->helperText('Up to about 60 characters.'),
                TextInput::make('seo_keywords')->label('Keywords')->maxLength(255),
                Textarea::make('seo_description')->label('Meta description')->rows(3)->maxLength(170)->helperText('Up to about 155 characters.')->columnSpanFull(),
                FileUpload::make('og_image')->label('Social share image')->image()->directory('seo')->helperText('1200 × 630 px works best.'),
                TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(255),
                Toggle::make('noindex')->label('Hide from search engines (noindex)'),
            ]);
    }

    /** Repeater of {title, text} pairs. */
    public static function titleTextList(string $name, string $label, string $itemLabel = 'Item'): Repeater
    {
        return Repeater::make($name)->label($label)
            ->schema([
                TextInput::make('title')->required(),
                Textarea::make('text')->rows(2),
            ])
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? $itemLabel)
            ->collapsible()->collapsed()->reorderable()->cloneable()
            ->addActionLabel("Add {$itemLabel}")
            ->columnSpanFull();
    }

    public static function faqs(string $name = 'faqs', string $label = 'FAQs'): Repeater
    {
        return Repeater::make($name)->label($label)
            ->schema([
                TextInput::make('q')->label('Question')->required(),
                Textarea::make('a')->label('Answer')->rows(3)->required(),
            ])
            ->itemLabel(fn (array $state): ?string => $state['q'] ?? 'Question')
            ->collapsible()->collapsed()->reorderable()
            ->addActionLabel('Add question')
            ->columnSpanFull();
    }

    public static function tags(string $name, string $label): TagsInput
    {
        return TagsInput::make($name)->label($label)->reorderable()->columnSpanFull();
    }

    public static function iconSelect(string $name = 'icon'): Select
    {
        return Select::make($name)->label('Icon')->searchable()->options([
            'rocket' => 'Rocket', 'layout-template' => 'Layout', 'smartphone' => 'Smartphone', 'calendar-check' => 'Calendar',
            'bot' => 'AI bot', 'handshake' => 'Handshake', 'shield-check' => 'Shield', 'activity' => 'Activity',
            'archive' => 'Archive', 'bug' => 'Bug', 'gauge' => 'Gauge', 'refresh-cw' => 'Refresh', 'eye-off' => 'Eye off',
            'file-text' => 'Document', 'layers' => 'Layers', 'messages-square' => 'Messages', 'wrench' => 'Wrench',
            'code' => 'Code', 'database' => 'Database', 'cloud' => 'Cloud', 'sparkles' => 'Sparkles', 'users' => 'Users',
        ]);
    }
}
