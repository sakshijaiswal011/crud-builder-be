<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Builders\MigrationDefinitionBuilder;
use App\Services\Crud\Support\StubRenderer;
use Illuminate\Support\Facades\File;

class MigrationGenerator
{
    public function __construct(
        protected MigrationDefinitionBuilder $builder,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $migrationsPath = config('crud-builder.paths.migrations', database_path('migrations'));
        $existing = File::glob($migrationsPath . '/*_create_'.$module->table_name.'_table.php');
        
        $destination = !empty($existing)
            ? $existing[0]
            : $migrationsPath . '/' . now()->format('Y_m_d_His') . '_create_' . $module->table_name . '_table.php';

        $definition = $this->builder->build($module);

        return $this->stubs->renderFile(
            $this->stubs->stubPath('migration.stub'),
            $destination,
            $definition
        );
    }
}
