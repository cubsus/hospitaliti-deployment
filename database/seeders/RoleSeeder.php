<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleNames = ['Super Admin', 'Developer'];

        foreach ($roleNames as $roleName) {
            Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }
}
