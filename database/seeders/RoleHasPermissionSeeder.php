<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;

use Illuminate\Database\Seeder;

class RoleHasPermissionSeeder extends Seeder
{
    /**P
     * Run the database seeds.
     */
    public function run(): void
    {
        // Declaring the variables for the main roles
        $adminRole = Role::findByName('Super Admin');
        $developerRole = Role::findByName('Developer');

        // All permissions for Super Admin
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $adminRole->givePermissionTo($permission);
        }

        // All permissions for resources : Employee / Customer
        $specificModels = ['Activity'];
        foreach ($specificModels as $modelName) {
            $modelPermissions = Permission::where('name', 'like', $modelName . '.%')->get();
            foreach ($modelPermissions as $permission) {
                $developerRole->givePermissionTo($permission);
            }
        }
    }
}
