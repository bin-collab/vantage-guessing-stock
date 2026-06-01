<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptInUser extends Model
{
    protected $fillable = [
        'email',
        'uid',
        'name',
    ];

    public function guesses()
    {
        return $this->hasMany(Guess::class, 'opt_in_user_id');
    }
}
