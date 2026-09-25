<?php

namespace App\Services\Crud;

use App\Models\CrudModule;
use App\Models\CrudRelationship;
use App\Services\Crud\Support\CrudClassNameResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class CrudModuleDeleterService
{
    public function __construct(
        protected CrudClassNameResolver $resolver
    ) {}

    public function delete(CrudModule $module): void
    {
        $this->validateNoForeignKeyDependencies($module);

        $this->deleteGeneratedFiles($module);
        
        $this->dropTable($module);
        
        // Cascade deletes will handle fields, formLists, permissions, relationships
        $module->delete();
    }

    protected function validateNoForeignKeyDependencies(CrudModule $module): void
    {
        $isReferenced = CrudRelationship::where('related_module_id', $module->id)->exists();
        
        if ($isReferenced) {
            throw new InvalidArgumentException("Cannot delete module '{$module->name}' because it is referenced by other modules as a foreign key relation.");
        }
    }

    protected function dropTable(CrudModule $module): void
    {
        Schema::dropIfExists($module->table_name);
    }

    protected function deleteGeneratedFiles(CrudModule $module): void
    {
        $model = $this->resolver->model($module);
        $controller = $this->resolver->controller($module);
        $service = $this->resolver->service($module);
        $resource = $this->resolver->resource($module);
        $storeRequest = $this->resolver->storeRequest($module);
        $updateRequest = $this->resolver->updateRequest($module);
        $policy = $this->resolver->policy($module);
        $routeFile = $this->resolver->routeFile($module);
        
        $filesToDelete = [
            app_path("Models/{$model}.php"),
            app_path("Http/Controllers/Api/{$controller}.php"),
            app_path("Services/{$service}.php"),
            app_path("Http/Resources/{$resource}.php"),
            app_path("Http/Requests/{$model}/{$storeRequest}.php"),
            app_path("Http/Requests/{$model}/{$updateRequest}.php"),
            app_path("Policies/{$policy}.php"),
            base_path("routes/modules/{$routeFile}.php"),
        ];

        // Find and delete the migration file
        $migrationFiles = glob(database_path("migrations/*_create_{$module->table_name}_table.php"));
        if ($migrationFiles) {
            $filesToDelete = array_merge($filesToDelete, $migrationFiles);
        }

        foreach ($filesToDelete as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }
        
        // Clean up the request directory if empty
        $requestDir = app_path("Http/Requests/{$model}");
        if (File::isDirectory($requestDir) && count(File::files($requestDir)) === 0) {
            File::deleteDirectory($requestDir);
        }
    }
}
