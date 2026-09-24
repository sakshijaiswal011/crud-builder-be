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

    public function apiPrefix(CrudModule $module): string
    {
        return $module->api_prefix ?: Str::kebab(Str::plural(str_replace('-', '_', $module->slug)));
    }

    public function variable(CrudModule $module): string
    {
        return Str::camel($this->model($module));
    }

    public function modelNamespace(): string
    {
        return 'App\\Models';
    }

    public function modelFqcn(CrudModule $module): string
    {
        return $this->modelNamespace().'\\'.$this->model($module);
    }
}
