<?php

namespace App\Models;

use App\Services\Crud\Support\CrudClassNameResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrudModule extends Model
{
    use SoftDeletes;

    protected $table = 'crud_modules';

    protected $fillable = [
        'name',
        'slug',
        'table_name',
        'api_prefix',
        'menu_name',
        'menu_group',
        'menu_icon',
        'soft_delete',
        'audit_log',
        'status',
        'generate_api_controller_routes',
        'generate_api_resource',
        'generate_policy',
        'generate_frontend_views',
        'list_pagination',
        'list_default_per_page',
        'list_max_per_page',
    ];

    protected function casts(): array
    {
        return [
            'soft_delete' => 'boolean',
            'audit_log' => 'boolean',
            'generate_api_controller_routes' => 'boolean',
            'generate_api_resource' => 'boolean',
            'generate_policy' => 'boolean',
            'generate_frontend_views' => 'boolean',
            'list_pagination' => 'boolean',
            'list_default_per_page' => 'integer',
            'list_max_per_page' => 'integer',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CrudField::class, 'module_id');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(CrudRelationship::class, 'module_id');
    }

    public function formLists(): HasMany
    {
        return $this->hasMany(CrudFormList::class, 'module_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(CrudModulePermission::class, 'module_id');
    }

    public function getModelClassNameAttribute(): string
    {
        return app(CrudClassNameResolver::class)->model($this);
    }

    public function getModelFilePathAttribute(): string
    {
        return app_path('Models/'.$this->model_class_name.'.php');
    }

    public function getMigrationFileNamePatternAttribute(): string
    {
        return '*_create_'.$this->table_name.'_table.php';
    }
}
