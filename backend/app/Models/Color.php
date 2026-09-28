<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use SoftDeletes;

    protected $table = 'color';

    protected $fillable = [
        'code',
        'desc',
        'name',
        'short_name',
    ];

    protected function casts(): array
    {
        return [
            'code' => 'integer',
        ];
    }

}
