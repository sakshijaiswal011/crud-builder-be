<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;

class ServiceGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        return $this->stubs->renderFile(
            $this->stubs->stubPath('service.stub'),
            $this->names->servicePath($module) . '/' . $this->names->service($module) . '.php',
            [
                'namespace' => $this->names->serviceNamespace($module),
                'class' => $this->names->service($module),
                'model' => $this->names->model($module),
                'model_fqcn' => $this->names->modelFqcn($module),
                'variable' => $this->names->variable($module),
            ]
        );
    }
}
