<?php

namespace App\Filament\Pages;

use App\Filament\Support\Fields;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** One screen for everything that is shared across the whole website. */
class ManageSiteSettings extends Page
{
    protected string $view = 'filament.pages.manage-site-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?int $navigationSort = 0;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::query()->first()?->attributesToArray() ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Tabs::make('Settings')->persistTabInQueryString()->tabs([
                Tab::make('Company')->icon('heroicon-o-building-office')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('company_name')->required(),
                        TextInput::make('tagline')->columnSpanFull(),
                        FileUpload::make('logo')->image()->directory('brand'),
                        FileUpload::make('logo_icon')->label('Logo icon')->image()->directory('brand'),
                        FileUpload::make('favicon')->image()->directory('brand'),
                    ]),
                    Repeater::make('stats')->label('Company stats')
                        ->schema([
                            TextInput::make('value')->numeric()->required(),
                            TextInput::make('suffix')->placeholder('+'),
                            TextInput::make('label')->required(),
                        ])->columns(3)->reorderable()->itemLabel(fn (array $state): ?string => ($state['value'] ?? '').($state['suffix'] ?? '').' '.($state['label'] ?? '')),
                    Fields::tags('trust_points', 'Trust points (shown under the stats)'),
                ]),
                Tab::make('Contact')->icon('heroicon-o-phone')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('email')->email(),
                        TextInput::make('phone')->helperText('As shown on the site, e.g. +92 329 0112060'),
                        TextInput::make('whatsapp')->label('WhatsApp number')->helperText('Digits only with country code, e.g. 923290112060'),
                        TextInput::make('location'),
                        TextInput::make('hours')->label('Working hours'),
                        TextInput::make('hours_note')->label('Hours note'),
                    ]),
                    Repeater::make('social_links')->label('Social links')
                        ->schema([
                            Select::make('platform')->options(['linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X', 'youtube' => 'YouTube', 'github' => 'GitHub', 'clutch' => 'Clutch'])->required(),
                            TextInput::make('url')->url()->required(),
                        ])->columns(2)->reorderable(),
                    Fields::tags('budget_ranges', 'Budget ranges in the contact form'),
                ]),
                Tab::make('Navigation')->icon('heroicon-o-bars-3')->schema([
                    Repeater::make('header_links')->label('Header menu links')
                        ->helperText('The Services dropdown is built automatically from your services.')
                        ->schema([TextInput::make('label')->required(), TextInput::make('url')->required()])
                        ->columns(2)->reorderable(),
                    Repeater::make('footer_columns')->label('Footer link columns')
                        ->schema([
                            TextInput::make('title')->required(),
                            Repeater::make('links')->schema([TextInput::make('label')->required(), TextInput::make('url')->required()])->columns(2)->reorderable(),
                        ])->reorderable()->collapsible()->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                ]),
                Tab::make('Shared content')->icon('heroicon-o-rectangle-group')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('cta_title')->label('Default call-to-action heading'),
                        TextInput::make('cta_button')->label('Default button label'),
                        Textarea::make('cta_text')->label('Default call-to-action text')->rows(2)->columnSpanFull(),
                    ]),
                    Fields::titleTextList('deployment_included', 'Included in every project (deployment)', 'item'),
                    Fields::faqs('common_faqs', 'Shared FAQs'),
                ]),
                Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('default_seo_title')->label('Default meta title')->maxLength(70),
                        TextInput::make('google_analytics_id')->label('Google Analytics ID')->placeholder('G-XXXXXXX'),
                        Textarea::make('default_seo_description')->label('Default meta description')->rows(3)->maxLength(170)->columnSpanFull(),
                        FileUpload::make('default_og_image')->label('Default social share image')->image()->directory('seo'),
                        TextInput::make('google_site_verification')->label('Google Search Console verification code'),
                        Textarea::make('robots_txt')->label('robots.txt')->rows(5)->columnSpanFull(),
                    ]),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        $settings = SiteSetting::query()->firstOrNew();
        $settings->fill($this->form->getState())->save();

        Notification::make()->success()->title('Settings saved')->send();
    }
}
