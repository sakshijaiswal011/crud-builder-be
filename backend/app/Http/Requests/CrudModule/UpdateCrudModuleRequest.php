<?php

namespace App\Http\Requests\CrudModule;

use Illuminate\Validation\Rule;

class UpdateCrudModuleRequest extends CreateCrudModuleRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = ['required', 'string', 'max:100', 'alpha_dash'];
        $rules['table_name'] = ['required', 'string', 'max:150', 'regex:/^[a-z][a-z0-9_]*$/'];
        $rules['fields.*.id'] = ['sometimes', 'integer', Rule::exists('crud_fields', 'id')];
        $rules['permissions.*.id'] = ['required', 'integer', Rule::exists('crud_module_permissions', 'id')];

        return $rules;
    }
}
