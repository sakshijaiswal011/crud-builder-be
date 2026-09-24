<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;

class ControllerGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $model = $this->names->model($module);

        return $this->stubs->renderFile(
            $this->stubs->stubPath('controller.stub'),
            app_path('Http/Controllers/Api/'.$this->names->controller($module).'.php'),
            [
                'class' => $this->names->controller($module),
                'model' => $model,
                'service' => $this->names->service($module),
                'resource' => $this->names->resource($module),
                'store_request' => $this->names->storeRequest($module),
                'update_request' => $this->names->updateRequest($module),
            ]
        );
    }
}
