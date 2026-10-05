<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;
use Illuminate\Support\Facades\File;

class QueryGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    /**
     * Generate the ListQuery for the module.
     *
     * The ListQuery handles complex reads: search, sort, paginate.
     * It accepts a FiltersDTO for typed input.
     */
    public function generate(CrudModule $module): string
    {
        $path = $this->names->queryPath($module);

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $className = $this->names->query($module, 'List');
        $model = $this->names->model($module);
        $filtersDtoName = $model . 'FiltersDTO';
        $filtersDtoFqcn = $this->names->dtoNamespace($module) . '\\' . $filtersDtoName;

        $searchFields = $module->formLists
            ->filter(fn ($form) => $form->search_enabled && $form->field?->field_name)
            ->map(fn ($form) => $form->field->field_name)
            ->unique()
            ->values();

        $sortableFields = $module->formLists
            ->filter(fn ($form) => $form->sorting_enabled && $form->field?->field_name)
            ->map(fn ($form) => $form->field->field_name)
            ->unique()
            ->values();

        return $this->stubs->renderFile(
            $this->stubs->stubPath('query.stub'),
            $path . '/' . $className . '.php',
            [
                'namespace' => $this->names->queryNamespace($module),
                'class' => $className,
                'model' => $model,
                'model_fqcn' => $this->names->modelFqcn($module),
                'dto_import' => "use {$filtersDtoFqcn};\n",
                'params' => "{$filtersDtoName} \$filters",
                'with_block' => $this->buildWithBlock($module),
                'search_block' => $this->buildSearchBlock($searchFields),
                'sort_block' => $this->buildSortBlock($sortableFields),
            ]
        );
    }

    protected function buildWithBlock(CrudModule $module): string
    {
        $relations = $module->relationships
            ->filter(fn ($rel) => in_array($rel->relation_type, ['belongsTo', 'hasOne'], true))
            ->map(fn ($rel) => "'" . $rel->relation_method_name . "'")
            ->values();

        if ($relations->isEmpty()) {
            return '';
        }

        $list = $relations->implode(', ');

        return "        \$query->with([{$list}]);";
    }

    protected function buildSearchBlock($searchFields): string
    {
        if ($searchFields->isEmpty()) {
            return '        // No searchable list columns configured.';
        }

        $list = $searchFields->map(fn ($name) => "'{$name}'")->implode(', ');

        return <<<PHP
        \$searchableFields = [{$list}];

        if (! empty(\$filters->search) && is_array(\$filters->search)) {
            foreach (\$filters->search as \$field => \$term) {
                if (in_array(\$field, \$searchableFields, true) && \$term !== '') {
                    \$query->where(\$field, 'like', '%' . \$term . '%');
                }
            }
        }
    PHP;
    }

    protected function buildSortBlock($sortableFields): string
    {
        if ($sortableFields->isEmpty()) {
            return '        $query->latest();';
        }

        $list = $sortableFields->map(fn ($name) => "'{$name}'")->implode(', ');

        return <<<PHP
        \$sortableFields = [{$list}];

        if (\$filters->sortBy && in_array(\$filters->sortBy, \$sortableFields, true)) {
            \$query->orderBy(\$filters->sortBy, \$filters->sortDir);
        } else {
            \$query->latest();
        }
    PHP;
    }
}
