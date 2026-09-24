<?php

namespace App\Services\Crud\Builders;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;

class ModelDefinitionBuilder
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected RelationshipDefinitionBuilder $relationships
    ) {}

    /**
     * @return array{namespace: string, class: string, table: string, imports: string, traits: string, fillable: string, casts: string, relationships: string}
     */
    public function build(CrudModule $module): array
    {
        $module->loadMissing(['fields', 'relationships.relatedModule']);

        $imports = [
            'use Illuminate\\Database\\Eloquent\\Model;',
        ];
        $traits = [];

        if ($module->soft_delete) {
            $imports[] = 'use Illuminate\\Database\\Eloquent\\SoftDeletes;';
            $traits[] = 'SoftDeletes';
        }

        $relation = $this->relationships->build($module);
        $imports = array_values(array_unique(array_merge($imports, $relation['imports'])));
        sort($imports);

        $fillable = $module->fields
            ->pluck('field_name')
            ->map(fn ($name) => "        '{$name}',")
            ->implode("\n");

        if ($fillable === '') {
            $fillable = '        //';
        }

        $casts = $this->buildCasts($module);

        return [
            'namespace' => $this->names->modelNamespace(),
            'class' => $this->names->model($module),
            'table' => $module->table_name,
            'imports' => implode("\n", $imports),
            'traits' => empty($traits) ? '' : '    use '.implode(', ', $traits).";\n",
            'fillable' => $fillable,
            'casts' => $casts,
            'relationships' => $relation['methods'],
        ];
    }

    protected function buildCasts(CrudModule $module): string
    {
        $casts = [];

        foreach ($module->fields as $field) {
            $type = strtolower((string) $field->type);
            $cast = match ($type) {
                'boolean', 'bool' => 'boolean',
                'integer', 'int', 'biginteger', 'big_integer', 'bigint',
                'unsignedbiginteger', 'unsigned_big_integer',
                'smallinteger', 'small_integer', 'tinyinteger', 'tiny_integer' => 'integer',
                'decimal', 'float', 'double' => 'float',
                'json' => 'array',
                'date' => 'date',
                'datetime', 'timestamp' => 'datetime',
                default => null,
            };

            if ($cast) {
                $casts[] = "            '{$field->field_name}' => '{$cast}',";
            }
        }

        if (empty($casts)) {
            return '';
        }

        $body = implode("\n", $casts);

        return <<<PHP

    protected function casts(): array
    {
        return [
{$body}
        ];
    }

PHP;
    }
}
