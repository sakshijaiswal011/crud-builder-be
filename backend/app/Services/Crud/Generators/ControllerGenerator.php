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

        $destination = $this->names->controllerPath($module) . '/' . $this->names->controller($module) . '.php';

        return $this->stubs->renderFile(
            $this->stubs->stubPath('controller.stub'),
            $destination,
            [
                'namespace' => $this->names->controllerNamespace($module),
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
