<?php

namespace App\Http\Requests;

use App\Helpers\APIResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json(
            APIResponseHelper::error(
                APIResponseHelper::VALIDATION_ERROR,
                'Validation failed.',
                $validator->errors()
            ),
            APIResponseHelper::VALIDATION_ERROR
        ));
    }
}
