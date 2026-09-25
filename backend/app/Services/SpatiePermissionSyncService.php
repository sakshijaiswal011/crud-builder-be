<?php

namespace App\Services;

use App\Models\CrudModule;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SpatiePermissionSyncService
{
    /**
     * Register enabled module permissions (step 6) in Spatie and attach to admin role.
     *
     * @return array<int, string> Spatie permission names synced
     */
    public function syncModulePermissions(CrudModule $module): array
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $module->loadMissing('permissions');

        $guard = config('auth.defaults.guard', 'web');

        $enabled = $module->permissions->filter(fn ($row) => $row->enabled && filled($row->action));

        if ($enabled->isEmpty()) {
            return [];
        }

        $spatieNames = [];

        foreach ($enabled as $row) {
            $name = trim($row->action);

            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);

            $spatieNames[] = $name;
        }

        $this->assignToAdminRole($spatieNames, $guard);

        return $spatieNames;
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    protected function assignToAdminRole(array $permissionNames, string $guard): void
    {
        if ($permissionNames === []) {
            return;
        }

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
        ]);

        $adminRole->givePermissionTo($permissionNames);

        $admin = User::query()->find(1);
        if ($admin && ! $admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }

    /**
     * Remove Spatie permissions for a module (by action names stored in builder).
     *
     * @param  Collection<int, \App\Models\CrudModulePermission>  $modulePermissions
     */
    public function revokeModulePermissions(Collection $modulePermissions): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        foreach ($modulePermissions as $row) {
            if (! filled($row->action)) {
                continue;
            }

            Permission::query()
                ->where('name', $row->action)
                ->where('guard_name', $guard)
                ->delete();
        }
    }
}
