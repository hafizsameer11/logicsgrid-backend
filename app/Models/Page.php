<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'hero_labels' => 'array',
            'sections' => 'array',
            'is_published' => 'boolean',
            'noindex' => 'boolean',
        ];
    }
}
