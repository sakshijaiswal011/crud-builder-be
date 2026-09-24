<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;

class RouteGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $destination = base_path('routes/modules/'.$this->names->routeFile($module).'.php');

        $path = $this->stubs->renderFile(
            $this->stubs->stubPath('routes.stub'),
            $destination,
            [
                'controller' => $this->names->controller($module),
                'api_prefix' => $this->names->apiPrefix($module),
            ]
        );

        return $path;
    }
}
