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

    public function action(CrudModule $module, string $prefix = ''): string
    {
        return $prefix . $this->model($module) . 'Action';
    }

    public function query(CrudModule $module, string $suffix = 'List'): string
    {
        return $this->model($module) . $suffix . 'Query';
    }

    public function dto(CrudModule $module, string $prefix = ''): string
    {
        return $prefix . $this->model($module) . 'DTO';
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

    public function getModuleDomainName(CrudModule $module): string
    {
        return Str::studly(Str::plural($module->slug)); // e.g., 'customers' -> 'Customers'
    }

    public function basePath(CrudModule $module): string
    {
        return base_path();
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
        $domain = $this->getModuleDomainName($module);
        return $this->appPath($module, 'Modules/' . $domain . '/' . basename($defaultAppSubPath));
    }

    public function getModuleNamespace(CrudModule $module, string $defaultNamespace): string
    {
        $domain = $this->getModuleDomainName($module);
        $baseName = class_basename($defaultNamespace);
        return 'App\\Modules\\' . $domain . '\\' . $baseName;
    }

    public function modelNamespace(CrudModule $module = null): string
    {
        if ($module) {
            return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Models';
        }
        return config('crud-builder.namespaces.models', 'App\\Models');
    }

    public function controllerNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Controllers';
    }

    public function serviceNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Services';
    }

    public function resourceNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Resources';
    }

    public function requestNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Requests';
    }

    public function policyNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Policies';
    }

    public function actionNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Actions';
    }

    public function queryNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\Queries';
    }

    public function dtoNamespace(CrudModule $module): string
    {
        return 'App\\Modules\\' . $this->getModuleDomainName($module) . '\\DTOs';
    }

    public function modelPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Models');
    }

    public function controllerPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Controllers');
    }

    public function servicePath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Services');
    }

    public function resourcePath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Resources');
    }

    public function requestPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Requests');
    }

    public function policyPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Policies');
    }

    public function actionPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Actions');
    }

    public function queryPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'Queries');
    }

    public function dtoPath(CrudModule $module): string
    {
        return $this->getModulePath($module, 'DTOs');
    }

    public function routePath(CrudModule $module): string
    {
        return $this->appPath($module, 'Modules/' . $this->getModuleDomainName($module) . '/Routes');
    }

    public function migrationPath(CrudModule $module): string
    {
        return $this->appPath($module, 'Modules/' . $this->getModuleDomainName($module) . '/Migrations');
    }
}
