<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'features' => 'array',
            'scope' => 'array',
            'stack' => 'array',
            'results' => 'array',
            'services' => 'array',
            'is_published' => 'boolean',
            'noindex' => 'boolean',
        ];
    }
}
