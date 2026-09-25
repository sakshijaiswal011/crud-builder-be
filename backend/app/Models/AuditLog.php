<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'module_id',
        'event',
        'auditable_type',
        'auditable_id',
        'table_name',
        'record_identifier',
        'old_values',
        'new_values',
        'changed_fields',
        'description',
        'ip_address',
        'user_agent',
        'request_method',
        'request_url',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'changed_fields' => 'array',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CrudModule::class, 'module_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
