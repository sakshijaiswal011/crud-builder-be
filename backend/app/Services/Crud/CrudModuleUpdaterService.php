<?php

namespace App\Services\Crud;

use App\Models\CrudField;
use App\Models\CrudFormList;
use App\Models\CrudModule;
use App\Models\CrudModulePermission;
use App\Models\CrudRelationship;
use App\Services\SpatiePermissionSyncService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CrudModuleUpdaterService
{
    public function __construct(
        protected CrudGenerator $generator,
        protected ModuleSchemaAlterService $schemaAlter,
        protected SpatiePermissionSyncService $spatiePermissionSyncService
    ) {}

    /**
     * @return array{module: CrudModule, generated: array<string, mixed>, spatie_permissions: array<int, string>}
     */
    public function update(CrudModule $module, array $payload): array
    {
        if ($payload['slug'] !== $module->slug || $payload['table_name'] !== $module->table_name) {
            throw new InvalidArgumentException('Slug and table name cannot be changed.');
        }

        $transactionResult = DB::transaction(function () use ($module, $payload) {
            $module->load(['fields', 'relationships', 'formLists.field', 'permissions']);

            $previousSoftDelete = (bool) $module->soft_delete;

            $this->updateModuleMeta($module, $payload);
            $dropColumns = $this->removedFieldNames($module, $payload['fields']);
            $addFields = $this->syncFields($module, $payload['fields']);

            $this->syncRelationships($module, $payload['relationships'] ?? []);
            $this->syncFormsList($module, $payload['forms_list']);
            $this->syncPermissions($module, $payload['permissions']);

            $alterMigrationPath = $this->schemaAlter->apply(
                $module,
                $addFields,
                $dropColumns,
                ! $previousSoftDelete && (bool) $module->soft_delete,
                $previousSoftDelete && ! (bool) $module->soft_delete
            );

            return [
                'module' => $module->fresh()->load([
                'fields',
                'relationships.relatedModule',
                'formLists.field',
                'permissions',
                ]),
                'alter_migration_path' => $alterMigrationPath,
            ];
        });

        $alterMigrationPath = $transactionResult['alter_migration_path'];
        $module = $transactionResult['module'];

        if ($alterMigrationPath) {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/'.basename($alterMigrationPath),
            ]);
        }

        $generated = $this->generator->regenerateCode($module);
        $spatiePermissions = $this->spatiePermissionSyncService->syncModulePermissions($module);

        return [
            'module' => $module->fresh()->load([
                'fields',
                'relationships.relatedModule',
                'formLists.field',
                'permissions',
            ]),
            'generated' => $generated,
            'spatie_permissions' => $spatiePermissions,
        ];
    }

    protected function updateModuleMeta(CrudModule $module, array $payload): void
    {
        $module->update([
            'name' => $payload['name'],
            'api_prefix' => $payload['api_prefix'] ?? null,
            'api_version' => $payload['api_version'] ?? 'v1',
            'menu_name' => $payload['menu_name'] ?? null,
            'menu_icon' => $payload['menu_icon'] ?? null,
            'menu_group' => $payload['menu_group'] ?? null,
            'soft_delete' => (bool) ($payload['soft_delete'] ?? false),
            'audit_log' => (bool) ($payload['audit_log'] ?? false),
            'status' => $payload['status'] ?? $module->status,
            'generate_api_controller_routes' => $module->generate_api_controller_routes
                || (bool) ($payload['generate_api_controller_routes'] ?? false),
            'generate_api_resource' => $module->generate_api_resource
                || (bool) ($payload['generate_api_resource'] ?? false),
            'generate_policy' => $module->generate_policy
                || (bool) ($payload['generate_policy'] ?? false),
            'generate_frontend_views' => $module->generate_frontend_views
                || (bool) ($payload['generate_frontend_views'] ?? false),
        ]);
    }

    /**
     * @return array<int, CrudField>
     */
    protected function syncFields(CrudModule $module, array $fieldsPayload): array
    {
        $existingById = $module->fields->keyBy('id');
        $keepIds = [];
        $newFields = [];

        foreach ($fieldsPayload as $row) {
            $dbId = $row['id'] ?? null;

            if ($dbId && $existingById->has($dbId)) {
                /** @var CrudField $field */
                $field = $existingById->get($dbId);

                if ($field->field_name !== $row['field_name']) {
                    throw new InvalidArgumentException('Existing field names cannot be changed.');
                }

                $field->update([
                    'type' => $row['type'],
                    'length' => $row['length'] ?? null,
                    'nullable' => (bool) ($row['nullable'] ?? false),
                    'default_value' => $row['default_value'] ?? null,
                    'is_unique' => (bool) ($row['is_unique'] ?? false),
                    'is_indexed' => (bool) ($row['is_indexed'] ?? false),
                    'comment' => $row['comment'] ?? null,
                ]);

                $keepIds[] = (int) $dbId;

                continue;
            }

            $created = $module->fields()->create([
                'field_name' => $row['field_name'],
                'type' => $row['type'],
                'length' => $row['length'] ?? null,
                'nullable' => (bool) ($row['nullable'] ?? false),
                'default_value' => $row['default_value'] ?? null,
                'is_unique' => (bool) ($row['is_unique'] ?? false),
                'is_indexed' => (bool) ($row['is_indexed'] ?? false),
                'comment' => $row['comment'] ?? null,
            ]);

            $newFields[] = $created;
            $keepIds[] = $created->id;
        }

        foreach ($module->fields as $field) {
            if (in_array($field->id, $keepIds, true)) {
                continue;
            }

            $this->assertFieldCanBeRemoved($module, $field);
            $field->formList?->delete();
            $field->delete();
        }

        return $newFields;
    }

    /**
     * @return array<int, string>
     */
    protected function removedFieldNames(CrudModule $module, array $fieldsPayload): array
    {
        $keepIds = collect($fieldsPayload)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        return $module->fields
            ->reject(fn (CrudField $field) => in_array($field->id, $keepIds, true))
            ->pluck('field_name')
            ->all();
    }

    protected function assertFieldCanBeRemoved(CrudModule $module, CrudField $field): void
    {
        if ($field->field_name === 'id') {
            throw new InvalidArgumentException('The id column cannot be removed.');
        }

        if (CrudRelationship::query()
            ->where('module_id', $module->id)
            ->where('foreign_key', $field->field_name)
            ->exists()) {
            throw new InvalidArgumentException(
                "Cannot remove field [{$field->field_name}] because it is used as a relationship foreign key."
            );
        }
    }

    protected function syncRelationships(CrudModule $module, array $relationships): void
    {
        $module->relationships()->delete();

        foreach ($relationships as $relationship) {
            $module->relationships()->create([
                'relation_type' => $relationship['relation_type'],
                'related_module_id' => $relationship['related_module_id'],
                'foreign_key' => $relationship['foreign_key'] ?? null,
                'local_key' => $relationship['local_key'] ?? null,
                'display_field' => $relationship['display_field'] ?? null,
                'display_name' => $relationship['display_name'] ?? null,
            ]);
        }
    }

    protected function syncFormsList(CrudModule $module, array $formsList): void
    {
        $fieldsByName = $module->fields()->get()->keyBy('field_name');

        foreach ($formsList as $row) {
            $field = $fieldsByName->get($row['field_name']);
            if (! $field) {
                continue;
            }

            $validationRules = $row['validation_rules'] ?? $row['validation_rule'] ?? [];
            if (is_string($validationRules)) {
                $validationRules = array_values(array_filter(array_map('trim', explode('|', $validationRules))));
            }
            if (! is_array($validationRules)) {
                $validationRules = [];
            }

            CrudFormList::updateOrCreate(
                ['module_id' => $module->id, 'field_id' => $field->id],
                [
                    'form_input_type' => $row['form_input_type'],
                    'form_label' => $row['form_label'],
                    'form_placeholder' => $row['form_placeholder'] ?? '',
                    'is_required' => (bool) ($row['is_required'] ?? false),
                    'validation_rules' => $validationRules,
                    'list_label' => $row['list_label'],
                    'search_enabled' => (bool) ($row['search_enabled'] ?? true),
                    'sorting_enabled' => (bool) ($row['sorting_enabled'] ?? true),
                    'filtering_enabled' => (bool) ($row['filtering_enabled'] ?? true),
                    'width' => (int) ($row['width'] ?? 25),
                ]
            );
        }

        CrudFormList::query()
            ->where('module_id', $module->id)
            ->whereNotIn('field_id', $fieldsByName->pluck('id'))
            ->delete();
    }

    protected function syncPermissions(CrudModule $module, array $permissions): void
    {
        $existing = $module->permissions->keyBy('id');

        foreach ($permissions as $row) {
            $dbId = $row['id'] ?? null;

            if (! $dbId || ! $existing->has($dbId)) {
                throw new InvalidArgumentException('Permissions cannot be added or renamed during edit.');
            }

            /** @var CrudModulePermission $permission */
            $permission = $existing->get($dbId);

            if ($permission->permission_name !== $row['permission_name']
                || $permission->action !== $row['action']) {
                throw new InvalidArgumentException('Permission name and action cannot be changed.');
            }

            $permission->update(['enabled' => (bool) ($row['enabled'] ?? true)]);
        }
    }
}
