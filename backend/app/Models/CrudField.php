<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrudField extends Model
{
    use SoftDeletes;

    protected $table = 'crud_fields';

    protected $fillable = [
        'module_id',
        'field_name',
        'type',
        'length',
        'nullable',
        'default_value',
        'is_unique',
        'is_indexed',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'integer',
            'nullable' => 'boolean',
            'is_unique' => 'boolean',
            'is_indexed' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CrudModule::class, 'module_id');
    }

    public function formList(): HasOne
    {
        return $this->hasOne(CrudFormList::class, 'field_id');
    }
}
