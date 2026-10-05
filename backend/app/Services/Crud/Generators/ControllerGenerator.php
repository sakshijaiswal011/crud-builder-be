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

        $queryList = $this->names->query($module, 'List');
        $actionCreate = $this->names->action($module, 'Create');
        $actionUpdate = $this->names->action($module, 'Update');
        $actionDelete = $this->names->action($module, 'Delete');
        $filtersDto = $model . 'FiltersDTO';

        $imports = [
            "use " . $this->names->modelFqcn($module) . ";",
            "use " . $this->names->requestNamespace($module) . "\\" . $model . "\\" . $this->names->storeRequest($module) . ";",
            "use " . $this->names->requestNamespace($module) . "\\" . $model . "\\" . $this->names->updateRequest($module) . ";",
            "use " . $this->names->resourceNamespace($module) . "\\" . $this->names->resource($module) . ";",
            "use " . $this->names->queryNamespace($module) . "\\" . $queryList . ";",
            "use " . $this->names->actionNamespace($module) . "\\" . $actionCreate . ";",
            "use " . $this->names->actionNamespace($module) . "\\" . $actionUpdate . ";",
            "use " . $this->names->actionNamespace($module) . "\\" . $actionDelete . ";",
            "use " . $this->names->dtoNamespace($module) . "\\" . $filtersDto . ";",
        ];

        return $this->stubs->renderFile(
            $this->stubs->stubPath('controller.stub'),
            $destination,
            [
                'namespace' => $this->names->controllerNamespace($module),
                'class' => $this->names->controller($module),
                'model' => $model,
                'imports' => implode("\n", $imports),
                'resource' => $this->names->resource($module),
                'store_request' => $this->names->storeRequest($module),
                'update_request' => $this->names->updateRequest($module),
                'query_list' => $queryList,
                'action_create' => $actionCreate,
                'action_update' => $actionUpdate,
                'action_delete' => $actionDelete,
                'filters_dto' => $filtersDto,
            ]
        );
    }
}
