<?php

namespace App\Services\Crud;

use App\Models\CrudModule;
use App\Models\CrudRelationship;
use App\Services\Crud\Support\CrudClassNameResolver;
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
        
        // Hard delete handles everything via database-level cascadeOnDelete and removes unique key conflicts
        $module->forceDelete();
    }

    protected function validateNoForeignKeyDependencies(CrudModule $module): void
    {
        // Only belongsTo creates a real database foreign key constraint on the other table
        // hasOne/hasMany don't create physical constraints, so they should not block deletion
        $isReferenced = CrudRelationship::where('related_module_id', $module->id)
            ->where('relation_type', 'belongsTo')
            ->whereHas('module') // Ignore ghost relationships from soft-deleted modules
            ->exists();
        
        if ($isReferenced) {
            throw new InvalidArgumentException("Cannot delete module '{$module->name}' because it is referenced by other active modules as a foreign key relation.");
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
        
        $modelsPath = $this->resolver->modelPath($module);
        $controllersPath = $this->resolver->controllerPath($module);
        $servicesPath = $this->resolver->servicePath($module);
        $resourcesPath = $this->resolver->resourcePath($module);
        $requestsPath = $this->resolver->requestPath($module);
        $policiesPath = $this->resolver->policyPath($module);
        $routesPath = $this->resolver->routePath($module);
        $migrationsPath = $this->resolver->migrationPath($module);
        
        $filesToDelete = [
            "{$modelsPath}/{$model}.php",
            "{$controllersPath}/{$controller}.php",
            "{$servicesPath}/{$service}.php",
            "{$resourcesPath}/{$resource}.php",
            "{$requestsPath}/{$model}/{$storeRequest}.php",
            "{$requestsPath}/{$model}/{$updateRequest}.php",
            "{$policiesPath}/{$policy}.php",
            "{$routesPath}/{$routeFile}.php",
        ];

        // Find and delete the migration file
        $migrationFiles = glob("{$migrationsPath}/*_create_{$module->table_name}_table.php");
        if ($migrationFiles) {
            $filesToDelete = array_merge($filesToDelete, $migrationFiles);
        }

        foreach ($filesToDelete as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }
        // Collect all directories that might be empty now
        $directoriesToCheck = array_unique([
            $requestsPath . '/' . $model,
            $modelsPath,
            $controllersPath,
            $servicesPath,
            $resourcesPath,
            $requestsPath,
            $policiesPath,
            $routesPath,
            $migrationsPath,
        ]);

        // Add the root domain folder to be checked last
        if ($module->target_project_path) {
            $domain = $module->domain_folder ?: $module->slug;
            $directoriesToCheck[] = rtrim($module->target_project_path, '/\\') . '/' . $domain;
        } elseif ($module->domain_folder) {
            $directoriesToCheck[] = base_path('app/Modules/' . $module->domain_folder);
        }

        // Sort directories by length descending, so deeper folders (like Requests/Model) are deleted before their parents (like Requests)
        usort($directoriesToCheck, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($directoriesToCheck as $dir) {
            if (File::isDirectory($dir)) {
                // If the directory is completely empty, delete it
                $files = array_diff(scandir($dir), ['.', '..']);
                if (empty($files)) {
                    rmdir($dir);
                }
            }
        }
    }
}
