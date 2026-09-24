<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreCrudModuleRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
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
}
