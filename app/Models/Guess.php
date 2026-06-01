<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guess extends Model
{
    protected $fillable = [
        'opt_in_user_id',
        'stock_id',
        'guess_date',
        'guessed_price',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'guess_date' => 'date',
            'is_correct' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(OptInUser::class, 'opt_in_user_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
