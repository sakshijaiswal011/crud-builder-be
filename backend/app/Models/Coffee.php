<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coffee extends Model
{
    use SoftDeletes;

    protected $table = 'coffee';

    protected $fillable = [
        'code',
        'color_id',
        'desc',
        'description',
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
