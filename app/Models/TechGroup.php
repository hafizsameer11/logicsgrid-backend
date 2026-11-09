<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechGroup extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'core' => 'array',
            'also' => 'array',
        ];
    }
}
