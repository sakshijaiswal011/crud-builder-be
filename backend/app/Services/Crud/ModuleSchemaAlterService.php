<?php

namespace App\Services\Crud;

use App\Models\CrudField;
use App\Models\CrudModule;
use App\Services\Crud\Builders\MigrationDefinitionBuilder;
use App\Services\Crud\Support\StubRenderer;
use Illuminate\Support\Facades\Schema;

class ModuleSchemaAlterService
{
    public function __construct(
        protected MigrationDefinitionBuilder $columnBuilder,
        protected StubRenderer $stubs
    ) {}

    /**
     * @param  array<int, CrudField>  $addFields
     * @param  array<int, string>  $dropColumns
     */
    public function apply(
        CrudModule $module,
        array $addFields,
        array $dropColumns,
        bool $addSoftDeletes,
        bool $removeSoftDeletes
    ): ?string {
        $up = [];
        $down = [];

        foreach ($addFields as $field) {
            if (Schema::hasColumn($module->table_name, $field->field_name)) {
                continue;
            }

            $column = trim($this->columnBuilder->buildColumn($field));
            $up[] = "            if (! Schema::hasColumn('{$module->table_name}', '{$field->field_name}')) {";
            $up[] = '                '.$column;
            $up[] = '            }';
            $down[] = "            \$table->dropColumn('{$field->field_name}');";
        }

        foreach ($dropColumns as $column) {
            if (! Schema::hasColumn($module->table_name, $column)) {
                continue;
            }
            $up[] = "            \$table->dropColumn('{$column}');";
            $down[] = '            //';
        }

        if ($addSoftDeletes && ! Schema::hasColumn($module->table_name, 'deleted_at')) {
            $up[] = '            $table->softDeletes();';
            $down[] = '            $table->dropSoftDeletes();';
        }

        if ($removeSoftDeletes && Schema::hasColumn($module->table_name, 'deleted_at')) {
            $up[] = '            $table->dropSoftDeletes();';
            $down[] = '            $table->softDeletes();';
        }

        if ($up === []) {
            return null;
        }

        if ($down === []) {
            $down[] = '            //';
        }

        $migrationsPath = config('crud-builder.paths.migrations', database_path('migrations'));
        
        return $this->stubs->renderFile(
            $this->stubs->stubPath('alter-migration.stub'),
            $migrationsPath . '/' . now()->format('Y_m_d_His') . '_alter_' . $module->table_name . '_table.php',
            [
                'table' => $module->table_name,
                'up_lines' => implode("\n", $up),
                'down_lines' => implode("\n", $down),
            ]
        );
    }
}
