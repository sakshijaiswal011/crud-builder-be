<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreCrudModuleFormRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $moduleId = (int) $this->route('module');

        return [
            'forms' => ['required', 'array', 'min:1'],
            'forms.*.field_id' => [
                'required',
                'integer',
                Rule::exists('crud_fields', 'id')
                    ->where('module_id', $moduleId)
                    ->whereNull('deleted_at'),
            ],
            'forms.*.form_label' => ['required', 'string', 'max:100'],
            'forms.*.form_input_type' => ['required', 'string', Rule::in(['text', 'number', 'select'])],
            'forms.*.form_placeholder' => ['nullable', 'string', 'max:100'],
            'forms.*.is_required' => ['sometimes', 'boolean'],
            'forms.*.validation_rules' => ['nullable'],
            'forms.*.validation_rule' => ['nullable'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fieldIds = collect($this->input('forms', []))->pluck('field_id');
            if ($fieldIds->count() !== $fieldIds->unique()->count()) {
                $validator->errors()->add('forms', 'field_id must be unique within the forms payload.');
            }

            foreach ($this->input('forms', []) as $index => $form) {
                $rules = $form['validation_rules'] ?? $form['validation_rule'] ?? null;
                if ($rules !== null && ! is_array($rules) && ! is_string($rules)) {
                    $validator->errors()->add(
                        "forms.{$index}.validation_rules",
                        'validation_rules must be an array or string.'
                    );
                }
            }
        });
    }
}
