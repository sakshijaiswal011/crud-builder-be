<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CrudModule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /** Default when auth is not configured yet (e.g. local admin user id 1). */
    public const DEFAULT_USER_ID = 1;

    public const EVENT_CREATED = 'created';

    public const EVENT_UPDATED = 'updated';

    public const EVENT_DELETED = 'deleted';

    /**
     * @param  array<string, mixed>  $context
     */
    public function log(string $event, array $context = []): AuditLog
    {
        $request = request();

        return AuditLog::create([
            'user_id' => $context['user_id'] ?? Auth::id() ?? self::DEFAULT_USER_ID,
            'module_id' => $context['module_id'] ?? null,
            'event' => $event,
            'auditable_type' => $context['auditable_type'] ?? null,
            'auditable_id' => isset($context['auditable_id']) ? (string) $context['auditable_id'] : null,
            'table_name' => $context['table_name'] ?? null,
            'record_identifier' => $context['record_identifier'] ?? null,
            'old_values' => $context['old_values'] ?? null,
            'new_values' => $context['new_values'] ?? null,
            'changed_fields' => $context['changed_fields'] ?? null,
            'description' => $context['description'] ?? null,
            'ip_address' => $context['ip_address'] ?? $this->resolveIp($request),
            'user_agent' => $context['user_agent'] ?? $request?->userAgent(),
            'request_method' => $context['request_method'] ?? $request?->method(),
            'request_url' => $context['request_url'] ?? $request?->fullUrl(),
        ]);
    }

    public function logModuleCreated(CrudModule $module, ?string $description = null): AuditLog
    {
        return $this->log(self::EVENT_CREATED, [
            'module_id' => $module->id,
            'auditable_type' => CrudModule::class,
            'auditable_id' => $module->id,
            'table_name' => 'crud_modules',
            'record_identifier' => $module->name,
            'new_values' => $module->toArray(),
            'description' => $description ?? "CRUD module [{$module->name}] was created.",
        ]);
    }

    public function logModuleUpdated(
        CrudModule $module,
        array $oldValues,
        array $newValues,
        ?string $description = null
    ): AuditLog {
        return $this->log(self::EVENT_UPDATED, [
            'module_id' => $module->id,
            'auditable_type' => CrudModule::class,
            'auditable_id' => $module->id,
            'table_name' => 'crud_modules',
            'record_identifier' => $module->name,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $this->resolveChangedFields($oldValues, $newValues),
            'description' => $description ?? "CRUD module [{$module->name}] was updated.",
        ]);
    }

    public function logModuleDeleted(CrudModule $module, ?string $description = null): AuditLog
    {
        return $this->log(self::EVENT_DELETED, [
            'module_id' => $module->id,
            'auditable_type' => CrudModule::class,
            'auditable_id' => $module->id,
            'table_name' => 'crud_modules',
            'record_identifier' => $module->name,
            'old_values' => $module->toArray(),
            'description' => $description ?? "CRUD module [{$module->name}] was deleted.",
        ]);
    }

    /**
     * Log create/update/delete for a generated module record (call from module services).
     */
    public function logRecordCreated(Model $record, ?CrudModule $module = null): AuditLog
    {
        $module = $module ?? $this->resolveModuleForModel($record);

        return $this->log(self::EVENT_CREATED, [
            'module_id' => $module?->id,
            'auditable_type' => $record::class,
            'auditable_id' => $record->getKey(),
            'table_name' => $record->getTable(),
            'record_identifier' => $this->resolveRecordIdentifier($record),
            'new_values' => $this->modelAttributes($record),
            'description' => $this->buildRecordDescription($module, $record, self::EVENT_CREATED),
        ]);
    }

    public function logRecordUpdated(
        Model $record,
        array $oldValues,
        array $newValues,
        ?CrudModule $module = null
    ): AuditLog {
        $module = $module ?? $this->resolveModuleForModel($record);

        return $this->log(self::EVENT_UPDATED, [
            'module_id' => $module?->id,
            'auditable_type' => $record::class,
            'auditable_id' => $record->getKey(),
            'table_name' => $record->getTable(),
            'record_identifier' => $this->resolveRecordIdentifier($record),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $this->resolveChangedFields($oldValues, $newValues),
            'description' => $this->buildRecordDescription($module, $record, self::EVENT_UPDATED),
        ]);
    }

    public function logRecordDeleted(Model $record, ?CrudModule $module = null): AuditLog
    {
        $module = $module ?? $this->resolveModuleForModel($record);

        return $this->log(self::EVENT_DELETED, [
            'module_id' => $module?->id,
            'auditable_type' => $record::class,
            'auditable_id' => $record->getKey(),
            'table_name' => $record->getTable(),
            'record_identifier' => $this->resolveRecordIdentifier($record),
            'old_values' => $this->modelAttributes($record),
            'description' => $this->buildRecordDescription($module, $record, self::EVENT_DELETED),
        ]);
    }

    /**
     * Only log when the builder module has audit_log enabled.
     */
    public function logRecordCreatedIfEnabled(Model $record, ?CrudModule $module = null): ?AuditLog
    {
        $module = $module ?? $this->resolveModuleForModel($record);
        if (! $module?->audit_log) {
            return null;
        }

        return $this->logRecordCreated($record, $module);
    }

    public function logRecordUpdatedIfEnabled(
        Model $record,
        array $oldValues,
        array $newValues,
        ?CrudModule $module = null
    ): ?AuditLog {
        $module = $module ?? $this->resolveModuleForModel($record);
        if (! $module?->audit_log) {
            return null;
        }

        return $this->logRecordUpdated($record, $oldValues, $newValues, $module);
    }

    public function logRecordDeletedIfEnabled(Model $record, ?CrudModule $module = null): ?AuditLog
    {
        $module = $module ?? $this->resolveModuleForModel($record);
        if (! $module?->audit_log) {
            return null;
        }

        return $this->logRecordDeleted($record, $module);
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @return array<string, mixed>
     */
    public function resolveChangedFields(array $old, array $new): array
    {
        $changed = [];

        foreach ($new as $key => $value) {
            if (! array_key_exists($key, $old) || $old[$key] !== $value) {
                $changed[$key] = [
                    'old' => $old[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $changed;
    }

    protected function resolveModuleForModel(Model $record): ?CrudModule
    {
        return CrudModule::query()
            ->where('table_name', $record->getTable())
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function modelAttributes(Model $record): array
    {
        return $record->attributesToArray();
    }

    protected function resolveRecordIdentifier(Model $record): string
    {
        $candidates = ['name', 'title', 'slug', 'email', 'code'];

        foreach ($candidates as $field) {
            $value = $record->getAttribute($field);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return (string) $record->getKey();
    }

    protected function buildRecordDescription(?CrudModule $module, Model $record, string $event): string
    {
        $moduleLabel = $module?->name ?? $record->getTable();
        $identifier = $this->resolveRecordIdentifier($record);

        return match ($event) {
            self::EVENT_CREATED => "Record [{$identifier}] was created in module [{$moduleLabel}].",
            self::EVENT_UPDATED => "Record [{$identifier}] was updated in module [{$moduleLabel}].",
            self::EVENT_DELETED => "Record [{$identifier}] was deleted from module [{$moduleLabel}].",
            default => "Record [{$identifier}] event [{$event}] in module [{$moduleLabel}].",
        };
    }

    protected function resolveIp(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return $request->ip();
    }
}
