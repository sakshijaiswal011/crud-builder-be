<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;
use Illuminate\Support\Collection;

class ServiceGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $module->loadMissing(['fields', 'formLists.field', 'relationships.relatedModule']);

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
            $this->stubs->stubPath('service.stub'),
            app_path('Services/'.$this->names->service($module).'.php'),
            [
                'class' => $this->names->service($module),
                'model' => $this->names->model($module),
                'model_fqcn' => $this->names->modelFqcn($module),
                'variable' => $this->names->variable($module),
                'search_block' => $this->buildSearchBlock($searchFields),
                'sort_block' => $this->buildSortBlock($sortableFields),
                'with_block' => $this->buildWithBlock($module),
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

    protected function buildSearchBlock(Collection $searchFields): string
    {
        if ($searchFields->isEmpty()) {
            return '        // No searchable list columns configured.';
        }

        $list = $this->phpQuotedList($searchFields);

        return <<<PHP
        \$searchableFields = [{$list}];

        if (! empty(\$filters['search']) && is_array(\$filters['search'])) {
            foreach (\$filters['search'] as \$field => \$term) {
                if (! is_string(\$field) || ! in_array(\$field, \$searchableFields, true)) {
                    continue;
                }
                \$term = is_scalar(\$term) ? trim((string) \$term) : '';
                if (\$term === '') {
                    continue;
                }
                \$query->where(\$field, 'like', '%'.\$term.'%');
            }
        }
PHP;
    }

    protected function buildSortBlock(Collection $sortableFields): string
    {
        if ($sortableFields->isEmpty()) {
            return '        $query->latest();';
        }

        $list = $this->phpQuotedList($sortableFields);

        return <<<PHP
        \$sortableFields = [{$list}];
        \$sortBy = \$filters['sort_by'] ?? null;
        \$sortDir = isset(\$filters['sort_dir']) && strtolower((string) \$filters['sort_dir']) === 'desc' ? 'desc' : 'asc';

        if (is_string(\$sortBy) && in_array(\$sortBy, \$sortableFields, true)) {
            \$query->orderBy(\$sortBy, \$sortDir);
        } else {
            \$query->latest();
        }
PHP;
    }

    /**
     * @param  Collection<int, string>  $names
     */
    protected function phpQuotedList(Collection $names): string
    {
        return $names->map(fn ($name) => "'{$name}'")->implode(', ');
    }
}
