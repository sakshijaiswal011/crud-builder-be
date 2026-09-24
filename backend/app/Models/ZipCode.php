<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZipCode extends Model
{
    use SoftDeletes;

    protected $table = 'zip_code';

    protected $fillable = [
        'name',
        'zip_code',
    ];

    protected function casts(): array
    {
        return [
            'zip_code' => 'integer',
        ];
    }


    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

}
