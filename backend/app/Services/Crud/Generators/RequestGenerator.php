<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Builders\ValidationDefinitionBuilder;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;

class RequestGenerator
{
    public function __construct(
        protected ValidationDefinitionBuilder $builder,
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    /**
     * @return array{store: string, update: string}
     */
    public function generate(CrudModule $module): array
    {
        $rules = $this->builder->build($module);
        $model = $this->names->model($module);
        $dir = app_path('Http/Requests/'.$model);

        $storeClass = $this->names->storeRequest($module);
        $updateClass = $this->names->updateRequest($module);

        $storePath = $this->stubs->renderFile(
            $this->stubs->stubPath('store-request.stub'),
            $dir.'/'.$storeClass.'.php',
            [
                'model' => $model,
                'class' => $storeClass,
                'rules' => $rules['store_rules'],
            ]
        );

        $updatePath = $this->stubs->renderFile(
            $this->stubs->stubPath('update-request.stub'),
            $dir.'/'.$updateClass.'.php',
            [
                'model' => $model,
                'class' => $updateClass,
                'rules' => $rules['update_rules'],
            ]
        );

        return [
            'store' => $storePath,
            'update' => $updatePath,
        ];
    }
}
