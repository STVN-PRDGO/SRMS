<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\RolePermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RolePermissionCatalog::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['admin', 'teacher', 'student'] as $roleName) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($role->wasRecentlyCreated) {
                $role->syncPermissions(RolePermissionCatalog::forRole($roleName));
            }
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $owner = User::query()->firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'System Owner',
                'password' => 'password',
                'is_active' => true,
                'must_change_password' => true,
            ],
        );

        if (! $owner->hasRole($superAdmin)) {
            $owner->assignRole($superAdmin);
        }

        $admin = User::query()->updateOrCreate(
            ['email' => 'smprodigo@up.edu.ph'],
            [
                'name' => 'School Admin',
                'password' => 'smprodigo@up.edu.ph',
                'is_active' => true,
                'must_change_password' => false,
            ],
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        if (! $admin->hasRole($superAdmin)) {
            $admin->assignRole($superAdmin);
        }
    }
}
