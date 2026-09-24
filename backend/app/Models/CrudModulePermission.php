<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrudModulePermission extends Model
{
    use SoftDeletes;

    protected $table = 'crud_module_permissions';

    protected $fillable = [
        'module_id',
        'permission_name',
        'action',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CrudModule::class, 'module_id');
    }
}
