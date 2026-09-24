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
        foreach (File::glob(database_path('migrations/*_create_'.$module->table_name.'_table.php')) as $existing) {
            File::delete($existing);
        }

        $definition = $this->builder->build($module);
        $destination = database_path(
            'migrations/'.now()->format('Y_m_d_His').'_create_'.$module->table_name.'_table.php'
        );

        return $this->stubs->renderFile(
            $this->stubs->stubPath('migration.stub'),
            $destination,
            $definition
        );
    }
}
