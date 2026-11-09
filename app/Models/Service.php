<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'audience' => 'array',
            'problems' => 'array',
            'features' => 'array',
            'deliverables' => 'array',
            'steps' => 'array',
            'stack' => 'array',
            'faqs' => 'array',
            'related_services' => 'array',
            'is_published' => 'boolean',
            'noindex' => 'boolean',
        ];
    }
}
