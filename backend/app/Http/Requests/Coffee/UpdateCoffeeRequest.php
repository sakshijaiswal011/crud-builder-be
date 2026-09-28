<?php

namespace App\Http\Requests\Coffee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoffeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes'],
            'code' => ['sometimes'],
            'desc' => ['sometimes'],
            'color_id' => ['sometimes'],
        ];
    }
}
