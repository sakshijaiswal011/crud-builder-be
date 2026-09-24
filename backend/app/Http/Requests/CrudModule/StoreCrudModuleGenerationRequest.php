<?php

namespace App\Http\Requests\CrudModule;

use App\Http\Requests\ApiFormRequest;

class StoreCrudModuleGenerationRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'generate_api_controller_routes' => ['required', 'boolean'],
            'generate_api_resource' => ['required', 'boolean'],
            'generate_policy' => ['required', 'boolean'],
            'generate_frontend_views' => ['required', 'boolean'],
        ];
    }
}
