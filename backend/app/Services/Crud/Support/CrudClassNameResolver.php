<?php

namespace App\Services\Crud\Support;

use App\Models\CrudModule;
use Illuminate\Support\Str;

class CrudClassNameResolver
{
    public function model(CrudModule $module): string
    {
        return Str::studly(Str::singular(str_replace('-', '_', $module->slug)));
    }

    public function controller(CrudModule $module): string
    {
        return $this->model($module).'Controller';
    }

    public function service(CrudModule $module): string
    {
        return $this->model($module).'Service';
    }

    public function resource(CrudModule $module): string
    {
        return $this->model($module).'Resource';
    }

    public function storeRequest(CrudModule $module): string
    {
        return 'Store'.$this->model($module).'Request';
    }

    public function updateRequest(CrudModule $module): string
    {
        return 'Update'.$this->model($module).'Request';
    }

    public function policy(CrudModule $module): string
    {
        return $this->model($module).'Policy';
    }

    public function routeFile(CrudModule $module): string
    {
        return Str::snake(str_replace('-', '_', $module->slug));
    }

    public function apiVersion(CrudModule $module): string
    {
        $version = trim((string) ($module->api_version ?? 'v1'));

        return $version !== '' ? $version : 'v1';
    }

    public function apiPrefix(CrudModule $module): string
    {
        return $module->api_prefix ?: Str::kebab(Str::plural(str_replace('-', '_', $module->slug)));
    }

    /** Full URI segment under /api (e.g. v1/countries). */
    public function apiRoutePath(CrudModule $module): string
    {
        return $this->apiVersion($module).'/'.$this->apiPrefix($module);
    }

    public function variable(CrudModule $module): string
    {
        return Str::camel($this->model($module));
    }

    public function modelFqcn(CrudModule $module): string
    {
        return $this->modelNamespace($module).'\\'.$this->model($module);
    }

    public function basePath(CrudModule $module): string
    {
        return $module->target_project_path ? rtrim($module->target_project_path, '/\\') : base_path();
    }

    public function appPath(CrudModule $module, string $path = ''): string
    {
        return $this->basePath($module) . '/app' . ($path ? '/' . ltrim($path, '/\\') : '');
    }

    public function databasePath(CrudModule $module, string $path = ''): string
    {
        return $this->basePath($module) . '/database' . ($path ? '/' . ltrim($path, '/\\') : '');
    }

    public function getModulePath(CrudModule $module, string $defaultAppSubPath): string
    {
        if ($module->target_project_path) {
            $domain = $module->domain_folder ?: $module->slug;
            $baseName = basename($defaultAppSubPath);
            if (str_contains($defaultAppSubPath, 'Controllers')) $baseName = 'Controllers';
            if (str_contains($defaultAppSubPath, 'Resources')) $baseName = 'Resources';
            if (str_contains($defaultAppSubPath, 'Requests')) $baseName = 'Requests';
            
            return rtrim($module->target_project_path, '/\\') . '/' . $domain . '/' . $baseName;
        }

        if ($module->domain_folder) {
            return $this->appPath($module, 'Modules/' . $module->domain_folder . '/' . basename($defaultAppSubPath));
        }

        return $this->appPath($module, $defaultAppSubPath);
    }

    public function getModuleNamespace(CrudModule $module, string $defaultNamespace): string
    {
        if ($module->domain_folder) {
            $baseName = class_basename($defaultNamespace);
            return 'App\\Modules\\' . $module->domain_folder . '\\' . $baseName;
        }

        return $defaultNamespace;
    }

    public function modelNamespace(CrudModule $module = null): string
    {
        if ($module && $module->domain_folder) {
            return 'App\\Modules\\' . $module->domain_folder . '\\Models';
        }
        return config('crud-builder.namespaces.models', 'App\\Models');
    }

    public function controllerNamespace(CrudModule $module): string
    {
        if ($module->domain_folder) {
            return 'App\\Modules\\' . $module->domain_folder . '\\Controllers';
        }
        return config('crud-builder.namespaces.controllers', 'App\\Http\\Controllers\\Api');
    }

    public function serviceNamespace(CrudModule $module): string
    {
        if ($module->domain_folder) {
            return 'App\\Modules\\' . $module->domain_folder . '\\Services';
        }
        return config('crud-builder.namespaces.services', 'App\\Services');
    }

    public function resourceNamespace(CrudModule $module): string
    {
        if ($module->domain_folder) {
            return 'App\\Modules\\' . $module->domain_folder . '\\Resources';
        }
        return config('crud-builder.namespaces.resources', 'App\\Http\\Resources');
    }

    public function requestNamespace(CrudModule $module): string
    {
        if ($module->domain_folder) {
            return 'App\\Modules\\' . $module->domain_folder . '\\Requests';
        }
        return config('crud-builder.namespaces.requests', 'App\\Http\\Requests');
    }

    public function policyNamespace(CrudModule $module): string
    {
        if ($module->domain_folder) {
            return 'App\\Modules\\' . $module->domain_folder . '\\Policies';
        }
        return config('crud-builder.namespaces.policies', 'App\\Policies');
    }

    public function modelPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Models');
    }

    public function controllerPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Http/Controllers/Api');
    }

    public function servicePath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Services');
    }

    public function resourcePath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Http/Resources');
    }

    public function requestPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Http/Requests');
    }

    public function policyPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Policies');
    }

    public function routePath(CrudModule $module): string
    {
        if ($module->target_project_path) {
            $domain = $module->domain_folder ?: $module->slug;
            return rtrim($module->target_project_path, '/\\') . '/' . $domain . '/Routes';
        }
        if ($module->domain_folder) {
            return $this->appPath($module, 'Modules/' . $module->domain_folder . '/Routes');
        }
        return config('crud-builder.paths.routes', $this->basePath($module) . '/routes/modules');
    }

    public function migrationPath(CrudModule $module): string
    {
        if ($module->target_project_path) {
            $domain = $module->domain_folder ?: $module->slug;
            return rtrim($module->target_project_path, '/\\') . '/' . $domain . '/Migrations';
        }
        return config('crud-builder.paths.migrations', $this->databasePath($module, 'migrations'));
    }
}
