<?php

namespace App\Http\Requests\Coffee;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoffeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'code' => ['required'],
            'desc' => ['required'],
            'color_id' => ['required'],
            'description' => ['required'],
        ];
    }
}
