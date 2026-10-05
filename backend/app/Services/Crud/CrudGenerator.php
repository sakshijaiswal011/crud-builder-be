<?php

namespace App\Services\Crud;

use App\Models\CrudModule;
use App\Services\Crud\Generators\ControllerGenerator;
use App\Services\Crud\Generators\MigrationGenerator;
use App\Services\Crud\Generators\ModelGenerator;
use App\Services\Crud\Generators\PolicyGenerator;
use App\Services\Crud\Generators\RequestGenerator;
use App\Services\Crud\Generators\ResourceGenerator;
use App\Services\Crud\Generators\RouteGenerator;
use App\Services\Crud\Generators\QueryGenerator;
use App\Services\Crud\Generators\ActionGenerator;
use App\Services\Crud\Generators\DTOGenerator;

class CrudGenerator
{
    public function __construct(
        protected MigrationGenerator $migrationGenerator,
        protected ModelGenerator $modelGenerator,
        protected RequestGenerator $requestGenerator,
        protected ResourceGenerator $resourceGenerator,
        protected QueryGenerator $queryGenerator,
        protected ActionGenerator $actionGenerator,
        protected DTOGenerator $dtoGenerator,
        protected ControllerGenerator $controllerGenerator,
        protected PolicyGenerator $policyGenerator,
        protected RouteGenerator $routeGenerator,
    ) {}

    public function generate(CrudModule $module): array
    {
        $module->loadMissing([
            'fields',
            'relationships.relatedModule',
            'formLists.field',
            'permissions',
        ]);

        $generated = [];

        $generated['migration'] = $this->migrationGenerator->generate($module);
        $generated['model'] = $this->modelGenerator->generate($module);

        if ($module->generate_api_controller_routes || $module->generate_api_resource) {
            $generated['requests'] = $this->requestGenerator->generate($module);
            $generated['dtos'] = $this->dtoGenerator->generate($module);
            $generated['actions'] = $this->actionGenerator->generate($module);
            $generated['queries'] = $this->queryGenerator->generate($module);
            $generated['resource'] = $this->resourceGenerator->generate($module);
        }

        if ($module->generate_api_controller_routes) {
            $generated['controller'] = $this->controllerGenerator->generate($module);
            $generated['routes'] = $this->routeGenerator->generate($module);
        }

        if ($module->generate_policy) {
            $generated['policy'] = $this->policyGenerator->generate($module);
        }

        return $generated;
    }

    public function regenerateCode(CrudModule $module): array
    {
        $module->loadMissing([
            'fields',
            'relationships.relatedModule',
            'formLists.field',
            'permissions',
        ]);

        $generated = [];
        $generated['model'] = $this->modelGenerator->generate($module);

        if ($module->generate_api_controller_routes || $module->generate_api_resource) {
            $generated['requests'] = $this->requestGenerator->generate($module);
            $generated['dtos'] = $this->dtoGenerator->generate($module);
            $generated['actions'] = $this->actionGenerator->generate($module);
            $generated['queries'] = $this->queryGenerator->generate($module);
            $generated['resource'] = $this->resourceGenerator->generate($module);
        }

        if ($module->generate_api_controller_routes) {
            $generated['controller'] = $this->controllerGenerator->generate($module);
            $generated['routes'] = $this->routeGenerator->generate($module);
        }

        if ($module->generate_policy) {
            $generated['policy'] = $this->policyGenerator->generate($module);
        }

        return $generated;
    }
}
