<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'symbol',
        'name',
    ];

    public function closingPrices()
    {
        return $this->hasMany(ClosingPrice::class);
    }

    public function guesses()
    {
        return $this->hasMany(Guess::class);
    }
}
