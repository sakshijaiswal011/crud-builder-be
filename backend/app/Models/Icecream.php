<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Icecream extends Model
{
    use SoftDeletes;

    protected $table = 'icecream';

    protected $fillable = [
        'flavor',
        'name',
    ];

}
