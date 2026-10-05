<?php

namespace App\Services\Crud\Builders;

use App\Models\CrudModule;
use App\Models\CrudRelationship;
use App\Services\Crud\Support\CrudClassNameResolver;
use Illuminate\Support\Str;

class RelationshipDefinitionBuilder
{
    public function __construct(
        protected CrudClassNameResolver $names
    ) {}

    /**
     * @return array{imports: array<int, string>, methods: string}
     */
    public function build(CrudModule $module): array
    {
        $module->loadMissing('relationships.relatedModule');

        $imports = [];
        $methods = [];

        foreach ($module->relationships as $relationship) {
            $built = $this->buildMethod($relationship);
            if ($built === null) {
                continue;
            }

            foreach ($built['imports'] as $import) {
                $imports[] = $import;
            }
            $methods[] = $built['code'];
        }

        return [
            'imports' => array_values(array_unique($imports)),
            'methods' => empty($methods) ? '' : "\n".implode("\n", $methods),
        ];
    }

    /**
     * @return array{imports: array<int, string>, code: string}|null
     */
    protected function buildMethod(CrudRelationship $relationship): ?array
    {
        $related = $relationship->relatedModule;
        if (! $related) {
            return null;
        }

        $relatedClass = $this->names->model($related);
        $relatedFqcn = $this->names->modelFqcn($related);
        $methodName = $this->methodName($relationship, $related);
        $type = $relationship->relation_type;

        $returnType = match ($type) {
            'hasOne' => 'HasOne',
            'hasMany' => 'HasMany',
            'belongsTo' => 'BelongsTo',
            'belongsToMany' => 'BelongsToMany',
            default => null,
        };

        if ($returnType === null) {
            return null;
        }

        $foreignArg = $relationship->foreign_key ? "'{$relationship->foreign_key}'" : null;
        $localArg = $relationship->local_key ? "'{$relationship->local_key}'" : null;

        if ($type === 'belongsToMany') {
            $call = "\$this->belongsToMany({$relatedClass}::class)";
        } elseif ($foreignArg && $localArg) {
            $call = "\$this->{$type}({$relatedClass}::class, {$foreignArg}, {$localArg})";
        } elseif ($foreignArg) {
            $call = "\$this->{$type}({$relatedClass}::class, {$foreignArg})";
        } else {
            $call = "\$this->{$type}({$relatedClass}::class)";
        }

        $code = <<<PHP

    public function {$methodName}(): {$returnType}
    {
        return {$call};
    }

PHP;

        return [
            'imports' => [
                "use Illuminate\\Database\\Eloquent\\Relations\\{$returnType};",
                "use {$relatedFqcn};",
            ],
            'code' => $code,
        ];
    }

    protected function methodName(CrudRelationship $relationship, CrudModule $related): string
    {
        $base = Str::camel(Str::singular(str_replace('-', '_', $related->slug)));

        // If a custom foreign key is provided, use it to derive a unique method name
        // This prevents duplicate method names when multiple relationships point to the same module
        if ($relationship->foreign_key) {
            $prefix = preg_replace('/_id$/', '', $relationship->foreign_key);
            if ($prefix !== '') {
                $base = Str::camel($prefix);
            }
        }

        return in_array($relationship->relation_type, ['hasMany', 'belongsToMany'], true)
            ? Str::plural($base)
            : $base;
    }
}
