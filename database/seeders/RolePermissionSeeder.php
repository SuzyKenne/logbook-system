<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache to avoid conflicts
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define the permission modules and their actions
        $modules = [
            'staffs' => ['create', 'view', 'edit', 'delete'],
            'logbooks' => ['create', 'view', 'edit', 'delete'],
            'permissions' => ['create', 'view', 'edit', 'delete'],
            'roles' => ['create', 'view', 'edit', 'delete'],
        ];

        // Create parent permissions first (e.g., Classes)
        foreach (array_keys($modules) as $module) {
            $displayName = ucfirst($module); // Capitalized name
            $parent = Permission::firstOrCreate([
                'name' => $displayName,
                'identifier' => $module,
                'guard_name' => 'web',
            ]);
            $parentPermissions[$module] = $parent->id;
        }

        // Create child permissions and assign parent_id
        foreach ($modules as $module => $actions) {
            $parentId = $parentPermissions[$module];

            foreach ($actions as $action) {
                $childName = "{$action}-{$module}";

                Permission::firstOrCreate([
                    'name' => $childName,
                    'identifier' => $childName,
                    'guard_name' => 'web',
                    'parent_id' => $parentId,
                ]);
            }
        }
    }
}
