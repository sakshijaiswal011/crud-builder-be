<?php

namespace App\Http\Requests\Color;

use Illuminate\Foundation\Http\FormRequest;

class UpdateColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes'],
            'desc' => ['sometimes'],
            'code' => ['sometimes', 'min:6', 'max:6'],
            'short_name' => ['sometimes'],
        ];
    }
}
