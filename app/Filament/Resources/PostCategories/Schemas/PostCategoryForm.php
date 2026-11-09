<?php

namespace App\Filament\Resources\PostCategories\Schemas;

use App\Filament\Support\Fields;
use Filament\Schemas\Schema;

class PostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components(Fields::titleWithSlug('name', 'Name'));
    }
}