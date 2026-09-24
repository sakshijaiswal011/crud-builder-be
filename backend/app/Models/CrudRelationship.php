<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CrudRelationship extends Model
{
    use SoftDeletes;

    protected $table = 'crud_relationships';

    protected $fillable = [
        'module_id',
        'relation_type',
        'related_module_id',
        'foreign_key',
        'local_key',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CrudModule::class, 'module_id');
    }

    public function relatedModule(): BelongsTo
    {
        return $this->belongsTo(CrudModule::class, 'related_module_id');
    }

    public function getRelationMethodNameAttribute(): string
    {
        $related = $this->relatedModule;
        if (! $related) {
            return 'related';
        }

        $base = Str::camel(Str::singular($related->table_name));

        return in_array($this->relation_type, ['hasMany', 'belongsToMany'], true)
            ? Str::plural($base)
            : $base;
    }
}
