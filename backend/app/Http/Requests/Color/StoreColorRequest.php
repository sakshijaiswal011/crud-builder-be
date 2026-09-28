<?php

namespace App\Http\Requests\Color;

use Illuminate\Foundation\Http\FormRequest;

class StoreColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'desc' => ['required'],
            'code' => ['required', 'min:6', 'max:6'],
            'short_name' => ['required'],
        ];
    }
}
