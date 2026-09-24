<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;

class StoreCrudModulePermissionsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*.permission_name' => ['required', 'string', 'max:150'],
            'permissions.*.action' => ['required', 'string', 'max:50'],
            'permissions.*.enabled' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $names = collect($this->input('permissions', []))->pluck('permission_name');
            if ($names->count() !== $names->unique()->count()) {
                $validator->errors()->add('permissions', 'permission_name must be unique within the module.');
            }

            $actions = collect($this->input('permissions', []))->pluck('action');
            if ($actions->count() !== $actions->unique()->count()) {
                $validator->errors()->add('permissions', 'action must be unique within the module.');
            }
        });
    }
}
