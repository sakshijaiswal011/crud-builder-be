<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class CreateCrudModuleRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            // Step 1 — module
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('crud_modules', 'slug')->whereNull('deleted_at')],
            'table_name' => ['required', 'string', 'max:150', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('crud_modules', 'table_name')->whereNull('deleted_at')],
            'api_prefix' => ['nullable', 'string', 'max:150'],
            'menu_name' => ['nullable', 'string', 'max:100'],
            'menu_icon' => ['nullable', 'string', 'max:100'],
            'menu_group' => ['nullable', 'string', 'max:100'],
            'soft_delete' => ['sometimes', 'boolean'],
            'audit_log' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'inactive'])],

            // Step 7 — generation flags (on module)
            'generate_api_controller_routes' => ['sometimes', 'boolean'],
            'generate_api_resource' => ['sometimes', 'boolean'],
            'generate_policy' => ['sometimes', 'boolean'],
            'generate_frontend_views' => ['sometimes', 'boolean'],

            // Step 2 — fields
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.field_name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fields.*.type' => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    'string', 'varchar', 'char', 'text', 'longText', 'long_text', 'mediumText', 'medium_text',
                    'integer', 'int', 'bigInteger', 'big_integer', 'bigint',
                    'unsignedBigInteger', 'unsigned_big_integer',
                    'smallInteger', 'small_integer', 'tinyInteger', 'tiny_integer',
                    'decimal', 'float', 'double',
                    'boolean', 'bool',
                    'date', 'datetime', 'timestamp', 'time',
                    'json', 'uuid', 'ulid',
                    'foreignId', 'foreign_id',
                ]),
            ],
            'fields.*.length' => ['nullable', 'integer', 'min:1'],
            'fields.*.nullable' => ['sometimes', 'boolean'],
            'fields.*.default_value' => ['nullable', 'string'],
            'fields.*.is_unique' => ['sometimes', 'boolean'],
            'fields.*.is_indexed' => ['sometimes', 'boolean'],
            'fields.*.comment' => ['nullable', 'string'],

            // Step 3 — relationships (optional)
            'relationships' => ['sometimes', 'array'],
            'relationships.*.relation_type' => [
                'required',
                Rule::in(['hasOne', 'hasMany', 'belongsTo', 'belongsToMany']),
            ],
            'relationships.*.related_module_id' => [
                'required',
                'integer',
                Rule::exists('crud_modules', 'id')->whereNull('deleted_at'),
            ],
            'relationships.*.foreign_key' => ['nullable', 'string', 'max:100'],
            'relationships.*.local_key' => ['nullable', 'string', 'max:100'],

            // Steps 4 & 5 — form + list (keyed by field_name)
            'forms_list' => ['required', 'array', 'min:1'],
            'forms_list.*.field_name' => ['required', 'string', 'max:100'],
            'forms_list.*.form_label' => ['required', 'string', 'max:100'],
            'forms_list.*.form_input_type' => ['required', 'string', Rule::in(['text', 'number', 'select'])],
            'forms_list.*.form_placeholder' => ['nullable', 'string', 'max:100'],
            'forms_list.*.is_required' => ['sometimes', 'boolean'],
            'forms_list.*.validation_rules' => ['nullable'],
            'forms_list.*.validation_rule' => ['nullable'],
            'forms_list.*.list_label' => ['required', 'string', 'max:150'],
            'forms_list.*.search_enabled' => ['sometimes', 'boolean'],
            'forms_list.*.sorting_enabled' => ['sometimes', 'boolean'],
            'forms_list.*.filtering_enabled' => ['sometimes', 'boolean'],
            'forms_list.*.width' => ['sometimes', 'integer', 'min:1', 'max:100'],

            // Step 6 — permissions
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*.permission_name' => ['required', 'string', 'max:150'],
            'permissions.*.action' => ['required', 'string', 'max:50'],
            'permissions.*.enabled' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => strtolower((string) $this->input('slug'))]);
        }

        if ($this->has('table_name')) {
            $this->merge(['table_name' => strtolower((string) $this->input('table_name'))]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fieldNames = collect($this->input('fields', []))->pluck('field_name');

            if ($fieldNames->count() !== $fieldNames->unique()->count()) {
                $validator->errors()->add('fields', 'Field names must be unique within the module.');
            }

            $reserved = ['id', 'created_at', 'updated_at', 'deleted_at'];
            foreach ($fieldNames as $index => $name) {
                if (in_array($name, $reserved, true)) {
                    $validator->errors()->add("fields.{$index}.field_name", "The field name [{$name}] is reserved.");
                }
            }

            $formFieldNames = collect($this->input('forms_list', []))->pluck('field_name');
            if ($formFieldNames->count() !== $formFieldNames->unique()->count()) {
                $validator->errors()->add('forms_list', 'field_name must be unique within forms_list.');
            }

            foreach ($formFieldNames as $index => $name) {
                if (! $fieldNames->contains($name)) {
                    $validator->errors()->add(
                        "forms_list.{$index}.field_name",
                        "field_name [{$name}] must match one of the fields[].field_name values."
                    );
                }
            }

            foreach ($this->input('forms_list', []) as $index => $row) {
                $rules = $row['validation_rules'] ?? $row['validation_rule'] ?? null;
                if ($rules !== null && ! is_array($rules) && ! is_string($rules)) {
                    $validator->errors()->add(
                        "forms_list.{$index}.validation_rules",
                        'validation_rules must be an array or string.'
                    );
                }
            }

            $permissionNames = collect($this->input('permissions', []))->pluck('permission_name');
            if ($permissionNames->count() !== $permissionNames->unique()->count()) {
                $validator->errors()->add('permissions', 'permission_name must be unique within the module.');
            }

            $actions = collect($this->input('permissions', []))->pluck('action');
            if ($actions->count() !== $actions->unique()->count()) {
                $validator->errors()->add('permissions', 'action must be unique within the module.');
            }
        });
    }
}
