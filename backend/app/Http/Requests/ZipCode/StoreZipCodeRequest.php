<?php

namespace App\Http\Requests\ZipCode;

use Illuminate\Foundation\Http\FormRequest;

class StoreZipCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'zip_code' => ['required'],
        ];
    }
}
