<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;
use Illuminate\Support\Facades\File;

class RouteGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $routesPath = $this->names->routePath($module);
        
        if (!File::isDirectory($routesPath)) {
            File::makeDirectory($routesPath, 0755, true);
        }
        
        $destination = $routesPath . '/' . $this->names->routeFile($module) . '.php';

        $path = $this->stubs->renderFile(
            $this->stubs->stubPath('routes.stub'),
            $destination,
            [
                'controller' => $this->names->controller($module),
                'api_version' => $this->names->apiVersion($module),
                'api_prefix' => $this->names->apiPrefix($module),
            ]
        );

        return $path;
    }
}
