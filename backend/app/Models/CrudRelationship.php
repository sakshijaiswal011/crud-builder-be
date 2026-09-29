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
    
    protected $appends = ['relation_method_name'];

    protected $fillable = [
        'module_id',
        'relation_type',
        'related_module_id',
        'foreign_key',
        'local_key',
        'display_field',
        'display_name',
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

        // Use the same slug-based naming convention as the generator
        $base = Str::camel(Str::singular(str_replace('-', '_', $related->slug)));

        // If a custom foreign key is provided, use it to derive a unique method name
        if ($this->foreign_key) {
            $prefix = preg_replace('/_id$/', '', $this->foreign_key);
            if ($prefix !== '') {
                $base = Str::camel($prefix);
            }
        }

        return in_array($this->relation_type, ['hasMany', 'belongsToMany'], true)
            ? Str::plural($base)
            : $base;
    }
}
