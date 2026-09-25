<?php

namespace App\Services\Crud;

use App\Models\CrudFormList;
use App\Models\CrudModule;
use App\Models\CrudModulePermission;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class CrudModuleCreatorService
{
    public function __construct(
        protected CrudGenerator $generator,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Persist all builder metadata, then generate application files.
     *
     * @return array{module: CrudModule, generated: array<string, mixed>}
     */
    public function create(array $payload): array
    {
        $module = DB::transaction(function () use ($payload) {
            $module = $this->createModule($payload);

            $this->createFields($module, $payload);
            $this->createRelationships($module, $payload);
            $this->createFormsAndLists($module, $payload);
            $this->createPermissions($module, $payload);

            return $module->fresh()->load([
                'fields',
                'relationships.relatedModule',
                'formLists.field',
                'permissions',
            ]);
        });

        $generated = $this->generator->generate($module);

        $module = $module->fresh()->load([
            'fields',
            'relationships.relatedModule',
            'formLists.field',
            'permissions',
        ]);

        $this->auditLogService->logModuleCreated($module);

        return [
            'module' => $module->fresh()->load([
                'fields',
                'relationships.relatedModule',
                'formLists.field',
                'permissions',
            ]),
            'generated' => $generated,
        ];
    }

    protected function createModule(array $payload): CrudModule
    {
        return CrudModule::create([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'table_name' => $payload['table_name'],
            'api_prefix' => $payload['api_prefix'] ?? null,
            'menu_name' => $payload['menu_name'] ?? null,
            'menu_icon' => $payload['menu_icon'] ?? null,
            'menu_group' => $payload['menu_group'] ?? null,
            'soft_delete' => (bool) ($payload['soft_delete'] ?? false),
            'audit_log' => (bool) ($payload['audit_log'] ?? false),
            'status' => $payload['status'] ?? 'draft',
            'generate_api_controller_routes' => (bool) ($payload['generate_api_controller_routes'] ?? true),
            'generate_api_resource' => (bool) ($payload['generate_api_resource'] ?? true),
            'generate_policy' => (bool) ($payload['generate_policy'] ?? true),
            'generate_frontend_views' => (bool) ($payload['generate_frontend_views'] ?? true),
        ]);
    }

    protected function createFields(CrudModule $module, array $payload): void
    {
        foreach ($payload['fields'] as $field) {
            $module->fields()->create([
                'field_name' => $field['field_name'],
                'type' => $field['type'],
                'length' => $field['length'] ?? null,
                'nullable' => (bool) ($field['nullable'] ?? false),
                'default_value' => $field['default_value'] ?? null,
                'is_unique' => (bool) ($field['is_unique'] ?? false),
                'is_indexed' => (bool) ($field['is_indexed'] ?? false),
                'comment' => $field['comment'] ?? null,
            ]);
        }
    }

    protected function createRelationships(CrudModule $module, array $payload): void
    {
        foreach ($payload['relationships'] ?? [] as $relationship) {
            $module->relationships()->create([
                'relation_type' => $relationship['relation_type'],
                'related_module_id' => $relationship['related_module_id'],
                'foreign_key' => $relationship['foreign_key'] ?? null,
                'local_key' => $relationship['local_key'] ?? null,
            ]);
        }
    }

    protected function createFormsAndLists(CrudModule $module, array $payload): void
    {
        $fieldsByName = $module->fields()->get()->keyBy('field_name');

        foreach ($payload['forms_list'] as $row) {
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

            CrudFormList::create([
                'module_id' => $module->id,
                'field_id' => $field->id,
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
            ]);
        }
    }

    protected function createPermissions(CrudModule $module, array $payload): void
    {
        foreach ($payload['permissions'] as $permission) {
            CrudModulePermission::create([
                'module_id' => $module->id,
                'permission_name' => $permission['permission_name'],
                'action' => $permission['action'],
                'enabled' => (bool) ($permission['enabled'] ?? true),
            ]);
        }
    }
}
