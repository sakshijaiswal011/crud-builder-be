<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;

class PolicyGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $model = $this->names->model($module);

        $destination = config('crud-builder.paths.policies', app_path('Policies')) . '/' . $this->names->policy($module) . '.php';

        return $this->stubs->renderFile(
            $this->stubs->stubPath('policy.stub'),
            $destination,
            [
                'class' => $this->names->policy($module),
                'model' => $model,
                'model_fqcn' => $this->names->modelFqcn($module),
                'variable' => $this->names->variable($module),
                'permission_prefix' => $module->slug,
            ]
        );
    }
}
