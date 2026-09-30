<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Builders\MigrationDefinitionBuilder;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;
use Illuminate\Support\Facades\File;

class MigrationGenerator
{
    public function __construct(
        protected MigrationDefinitionBuilder $builder,
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    public function generate(CrudModule $module): string
    {
        $migrationsPath = $this->names->migrationPath($module);
        
        if (!File::isDirectory($migrationsPath)) {
            File::makeDirectory($migrationsPath, 0755, true);
        }
        
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
