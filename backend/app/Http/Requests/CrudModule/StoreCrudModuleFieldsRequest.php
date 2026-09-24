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
        });
    }
}
