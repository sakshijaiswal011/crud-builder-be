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
        $module->loadMissing('fields');

        $attributes = $module->fields
            ->pluck('field_name')
            ->map(fn ($name) => "            '{$name}' => \$this->{$name},")
            ->implode("\n");

        if ($attributes === '') {
            $attributes = '            //';
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
