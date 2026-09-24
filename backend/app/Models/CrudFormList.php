<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrudFormList extends Model
{
    use SoftDeletes;

    protected $table = 'crud_forms_list';

    protected $fillable = [
        'module_id',
        'field_id',
        'form_input_type',
        'form_label',
        'form_placeholder',
        'is_required',
        'validation_rules',
        'list_label',
        'search_enabled',
        'sorting_enabled',
        'filtering_enabled',
        'width',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'validation_rules' => 'array',
            'search_enabled' => 'boolean',
            'sorting_enabled' => 'boolean',
            'filtering_enabled' => 'boolean',
            'width' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CrudModule::class, 'module_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(CrudField::class, 'field_id');
    }
}
