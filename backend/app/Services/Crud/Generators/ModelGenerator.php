<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Builders\ModelDefinitionBuilder;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;

class ModelGenerator
{
    public function __construct(
        protected ModelDefinitionBuilder $builder,
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $definition = $this->builder->build($module);
        $destination = app_path('Models/'.$this->names->model($module).'.php');

        return $this->stubs->renderFile(
            $this->stubs->stubPath('model.stub'),
            $destination,
            $definition
        );
    }
}
