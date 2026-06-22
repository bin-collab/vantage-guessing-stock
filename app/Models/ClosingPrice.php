<?php

namespace App\Models;

use App\Services\GuessService;
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
            app(GuessService::class)->validateGuesses($closingPrice);
        });
    }
}
