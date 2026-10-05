<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use App\Services\Crud\Support\StubRenderer;
use Illuminate\Support\Facades\File;

class ActionGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names,
        protected StubRenderer $stubs
    ) {}

    /**
     * Generate Create, Update, and Delete Actions for the module.
     *
     * Each Action is a single-responsibility class.
     * Actions receive validated data (array) or Model instances — no DTOs needed
     * for simple CRUD.
     */
    public function generate(CrudModule $module): array
    {
        $path = $this->names->actionPath($module);

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return [
            'Create' => $this->generateCreateAction($module, $path),
            'Update' => $this->generateUpdateAction($module, $path),
            'Delete' => $this->generateDeleteAction($module, $path),
        ];
    }

    protected function generateCreateAction(CrudModule $module, string $path): string
    {
        $className = $this->names->action($module, 'Create');
        $model = $this->names->model($module);

        $body = <<<PHP
        return {$model}::query()->create(\$data);
PHP;

        return $this->stubs->renderFile(
            $this->stubs->stubPath('action.stub'),
            $path . '/' . $className . '.php',
            [
                'namespace' => $this->names->actionNamespace($module),
                'class' => $className,
                'model_fqcn' => $this->names->modelFqcn($module),
                'params' => 'array $data',
                'return_type' => $model,
                'body' => $body,
            ]
        );
    }

    protected function generateUpdateAction(CrudModule $module, string $path): string
    {
        $className = $this->names->action($module, 'Update');
        $model = $this->names->model($module);
        $variable = $this->names->variable($module);

        $body = <<<PHP
        \${$variable}->update(\$data);

        return \${$variable}->fresh();
PHP;

        return $this->stubs->renderFile(
            $this->stubs->stubPath('action.stub'),
            $path . '/' . $className . '.php',
            [
                'namespace' => $this->names->actionNamespace($module),
                'class' => $className,
                'model_fqcn' => $this->names->modelFqcn($module),
                'params' => "{$model} \${$variable}, array \$data",
                'return_type' => $model,
                'body' => $body,
            ]
        );
    }

    protected function generateDeleteAction(CrudModule $module, string $path): string
    {
        $className = $this->names->action($module, 'Delete');
        $model = $this->names->model($module);
        $variable = $this->names->variable($module);

        $body = <<<PHP
        return (bool) \${$variable}->delete();
PHP;

        return $this->stubs->renderFile(
            $this->stubs->stubPath('action.stub'),
            $path . '/' . $className . '.php',
            [
                'namespace' => $this->names->actionNamespace($module),
                'class' => $className,
                'model_fqcn' => $this->names->modelFqcn($module),
                'params' => "{$model} \${$variable}",
                'return_type' => 'bool',
                'body' => $body,
            ]
        );
    }
}
