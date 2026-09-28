<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;

class ResourceGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $module->loadMissing(['fields', 'relationships.relatedModule']);

        $attributes = $module->fields
            ->pluck('field_name')
            ->map(fn ($name) => "            '{$name}' => \$this->{$name},")
            ->implode("\n");

        if ($attributes === '') {
            $attributes = '            //';
        }

        $relations = $module->relationships
            ->filter(fn ($rel) => $rel->relation_type === 'belongsTo')
            ->map(function ($rel) {
                $method = $rel->relation_method_name;
                return "            '{$method}' => \$this->whenLoaded('{$method}'),";
            })
            ->implode("\n");

        if ($relations !== '') {
            $attributes .= "\n" . $relations;
        }

        $attributes = "            'id' => \$this->id,\n{$attributes}\n            'created_at' => \$this->created_at,\n            'updated_at' => \$this->updated_at,";

        return $this->stubs->renderFile(
            $this->stubs->stubPath('resource.stub'),
            app_path('Http/Resources/'.$this->names->resource($module).'.php'),
            [
                'class' => $this->names->resource($module),
                'attributes' => $attributes,
            ]
        );
    }
}
