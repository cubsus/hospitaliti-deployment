<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleName = 'Super Admin';

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@hospitaliti.com',
            'password' => bcrypt('password'),
        ]);

        if ($superAdmin->roles()) {
            $superAdmin->roles()->detach();
        }

        $superAdmin->assignRole(Role::findByName($roleName));
    }
}
