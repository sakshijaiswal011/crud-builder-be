<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreCrudModuleRelationshipsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'relationships' => ['required', 'array', 'min:1'],
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
        ];
    }
}
