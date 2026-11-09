<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'trust_points' => 'array',
            'social_links' => 'array',
            'header_links' => 'array',
            'footer_columns' => 'array',
            'budget_ranges' => 'array',
            'common_faqs' => 'array',
            'deployment_included' => 'array',
        ];
    }
}
