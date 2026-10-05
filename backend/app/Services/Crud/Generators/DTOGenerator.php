<?php

namespace App\Services\Crud\Generators;

use App\Models\CrudModule;
use App\Services\Crud\Support\CrudClassNameResolver;
use Illuminate\Support\Facades\File;

class DTOGenerator
{
    public function __construct(
        protected CrudClassNameResolver $names
    ) {}

    /**
     * Generate only a FiltersDTO for the module.
     *
     * The FiltersDTO provides typed input for the ListQuery.
     * Create/Update DTOs are NOT generated because for simple CRUD, passing
     * $request->validated() as an array is sufficient.
     *
     * can manually add Create/Update DTOs when input becomes complex.
     */
    public function generate(CrudModule $module): string
    {
        $path = $this->names->dtoPath($module);

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return $this->generateFiltersDTO($module, $path);
    }

    protected function generateFiltersDTO(CrudModule $module, string $path): string
    {
        $className = $this->names->model($module) . 'FiltersDTO';
        $defaultPerPage = $module->list_default_per_page ?? 15;

        $content = <<<PHP
<?php

namespace {$this->names->dtoNamespace($module)};

class {$className}
{
    public readonly ?array \$search;
    public readonly ?string \$sortBy;
    public readonly string \$sortDir;
    public readonly int \$perPage;
    public readonly bool \$paginate;

    public function __construct(array \$data = [])
    {
        \$this->search = \$data['search'] ?? null;
        \$this->sortBy = \$data['sort_by'] ?? null;
        \$this->sortDir = \$data['sort_dir'] ?? 'desc';
        \$this->perPage = isset(\$data['per_page']) ? (int) \$data['per_page'] : {$defaultPerPage};
        \$this->paginate = ! isset(\$data['paginate']) || filter_var(\$data['paginate'], FILTER_VALIDATE_BOOLEAN);
    }
}
PHP;

        $destination = $path . '/' . $className . '.php';
        File::put($destination, $content);

        return $destination;
    }
}
