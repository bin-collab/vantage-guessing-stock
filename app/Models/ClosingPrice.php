<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosingPrice extends Model
{
    protected $fillable = [
        'stock_id',
        'date',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    protected static function booted(): void
    {
        static::saved(function (ClosingPrice $closingPrice) {
            app(\App\Services\GuessService::class)->validateGuesses($closingPrice);
        });
    }
}
