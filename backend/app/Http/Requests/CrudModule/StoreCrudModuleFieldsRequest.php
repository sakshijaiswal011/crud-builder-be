<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreCrudModuleFieldsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.field_name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fields.*.type' => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    'string', 'text', 'integer', 'bigInteger', 'decimal', 'boolean',
                    'date', 'datetime', 'timestamp', 'json', 'foreignId', 'enum',
                ]),
            ],
            'fields.*.length' => ['nullable', 'integer', 'min:1'],
            'fields.*.enum_values' => ['nullable', 'string'],
            'fields.*.nullable' => ['sometimes', 'boolean'],
            'fields.*.default_value' => ['nullable', 'string'],
            'fields.*.is_unique' => ['sometimes', 'boolean'],
            'fields.*.is_indexed' => ['sometimes', 'boolean'],
            'fields.*.comment' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $names = collect($this->input('fields', []))->pluck('field_name');
            if ($names->count() !== $names->unique()->count()) {
                $validator->errors()->add('fields', 'Field names must be unique within the module.');
            }

            $reserved = ['id', 'created_at', 'updated_at', 'deleted_at'];
            foreach ($names as $index => $name) {
                if (in_array($name, $reserved, true)) {
                    $validator->errors()->add("fields.{$index}.field_name", "The field name [{$name}] is reserved.");
                }
            }

            foreach ($this->input('fields', []) as $index => $field) {
                if (($field['type'] ?? '') === 'enum' && trim((string) ($field['enum_values'] ?? '')) === '') {
                    $validator->errors()->add(
                        "fields.{$index}.enum_values",
                        'Enum values are required (comma-separated).'
                    );
                }

                $defaultValue = trim((string) ($field['default_value'] ?? ''));
                if (($field['type'] ?? '') === 'enum' && $defaultValue !== '') {
                    $options = $this->parseEnumValuesList((string) ($field['enum_values'] ?? ''));
                    if ($options !== [] && ! in_array($defaultValue, $options, true)) {
                        $validator->errors()->add(
                            "fields.{$index}.default_value",
                            'Default value must be one of the enum options.'
                        );
                    }
                }
            }
        });
    }

    /**
     * @return array<int, string>
     */
    protected function parseEnumValuesList(string $raw): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(',', $raw)
        ), fn (string $part) => $part !== ''));
    }
}
