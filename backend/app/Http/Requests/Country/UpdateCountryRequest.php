<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable'],
            'short_name' => ['sometimes', 'nullable'],
            'code' => ['sometimes', 'nullable'],
        ];
    }
}
