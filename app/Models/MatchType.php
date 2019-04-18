<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchType extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'number_of_sides' => 'integer',
        'number_of_competitors' => 'integer',
     ];
}
