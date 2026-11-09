<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Article')->columnSpan(2)->schema([
                ...Fields::titleWithSlug(),
                Textarea::make('excerpt')->rows(2)->helperText('Shown on the blog list and used as the meta description if none is set.'),
                RichEditor::make('body')
                    ->fileAttachmentsDirectory('blog')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['h2', 'h3', 'blockquote', 'codeBlock'],
                        ['bulletList', 'orderedList', 'table', 'attachFiles'],
                        ['undo', 'redo'],
                    ]),
            ]),
            Grid::make(1)->columnSpan(1)->schema([
                Section::make('Publishing')->schema([
                    Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                    DateTimePicker::make('published_at')->label('Publish date')->default(now())->helperText('Set a future date to schedule.'),
                    Select::make('post_category_id')->label('Category')->relationship('category', 'name')->searchable()->preload()
                        ->createOptionForm([
                            TextInput::make('name')->required(),
                            TextInput::make('slug')->required(),
                        ]),
                    TextInput::make('author_name')->label('Author')->default('LogicsGrid team'),
                    TextInput::make('read_time')->placeholder('5 min read'),
                ]),
                Section::make('Cover')->schema([
                    FileUpload::make('cover_image')->hiddenLabel()->image()->imageEditor()->directory('blog'),
                ]),
            ]),
            Grid::make(1)->columnSpanFull()->schema([Fields::seo()]),
        ]);
    }
}
