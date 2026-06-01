<?php

namespace App\Models;

/**
 * Ranking model is a proxy for OptInUser to provide a dedicated resource for statistics.
 */
class Ranking extends OptInUser
{
    protected $table = 'opt_in_users';
}
