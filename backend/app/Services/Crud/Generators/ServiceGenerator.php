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
        $module->loadMissing(['fields', 'formLists.field']);
        $model = $this->names->model($module);

        $searchFields = $module->formLists
            ->filter(fn ($form) => $form->search_enabled && $form->field?->field_name)
            ->map(fn ($form) => $form->field->field_name)
            ->unique()
            ->values();

        if ($searchFields->isEmpty()) {
            $searchFields = $module->fields->pluck('field_name');
        }

        $searchConditions = $searchFields
            ->values()
            ->map(function ($name, $index) {
                $method = $index === 0 ? 'where' : 'orWhere';

                return "                \$q->{$method}('{$name}', 'like', \"%{\$search}%\");";
            })
            ->implode("\n");

        if ($searchConditions === '') {
            $searchConditions = '                //';
        }

        return $this->stubs->renderFile(
            $this->stubs->stubPath('service.stub'),
            app_path('Services/'.$this->names->service($module).'.php'),
            [
                'class' => $this->names->service($module),
                'model' => $model,
                'model_fqcn' => $this->names->modelFqcn($module),
                'variable' => $this->names->variable($module),
                'search_conditions' => $searchConditions,
            ]
        );
    }
}
