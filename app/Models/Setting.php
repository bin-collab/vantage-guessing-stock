<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'guess_start_date',
        'guess_end_date',
        'daily_deadline',
    ];

    protected function casts(): array
    {
        return [
            'guess_start_date' => 'date',
            'guess_end_date' => 'date',
        ];
    }
}
