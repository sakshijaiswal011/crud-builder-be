<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SpatiePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'admin', 'guard_name' => $guard]
        );

        $permissions = [
            'manage crud builder',
            'manage modules',
            'view audit logs',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate(
                ['name' => $permissionName, 'guard_name' => $guard]
            );
        }

        $adminRole->syncPermissions($permissions);

        $admin = User::query()->find(1);
        if ($admin && ! $admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}
