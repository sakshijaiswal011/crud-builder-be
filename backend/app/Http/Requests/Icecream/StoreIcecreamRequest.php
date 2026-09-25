<?php

namespace App\Http\Requests\Icecream;

use Illuminate\Foundation\Http\FormRequest;

class StoreIcecreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'flavor' => ['required'],
        ];
    }
}
