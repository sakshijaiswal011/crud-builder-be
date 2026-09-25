<?php

namespace App\Http\Requests\Icecream;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIcecreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes'],
            'flavor' => ['sometimes'],
        ];
    }
}
