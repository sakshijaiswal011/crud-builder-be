<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coffee extends Model
{

    protected $table = 'coffee';

    protected $fillable = [
        'code',
        'color_id',
        'desc',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'code' => 'integer',
            'color_id' => 'integer',
        ];
    }


    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id', 'id');
    }

}
