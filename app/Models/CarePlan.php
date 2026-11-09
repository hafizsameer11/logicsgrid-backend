<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarePlan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'points' => 'array',
            'is_highlighted' => 'boolean',
        ];
    }
}
