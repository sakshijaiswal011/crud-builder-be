<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreCrudModuleListRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $moduleId = (int) $this->route('module');

        return [
            'lists' => ['required', 'array', 'min:1'],
            'lists.*.field_id' => [
                'required',
                'integer',
                Rule::exists('crud_fields', 'id')
                    ->where('module_id', $moduleId)
                    ->whereNull('deleted_at'),
            ],
            'lists.*.list_label' => ['required', 'string', 'max:150'],
            'lists.*.search_enabled' => ['sometimes', 'boolean'],
            'lists.*.sorting_enabled' => ['sometimes', 'boolean'],
            'lists.*.filtering_enabled' => ['sometimes', 'boolean'],
            'lists.*.width' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fieldIds = collect($this->input('lists', []))->pluck('field_id');
            if ($fieldIds->count() !== $fieldIds->unique()->count()) {
                $validator->errors()->add('lists', 'field_id must be unique within the lists payload.');
            }
        });
    }
}
