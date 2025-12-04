<?php

namespace Database\Seeders;

use App\Models\Permission;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = getModels();
        $now = Carbon::now();

        foreach ($models as $model) {

            $modelName = Str::afterLast($model, '\\');

            Permission::insert([
                [
                    'name' => $modelName.'.view',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => $modelName.'.create',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => $modelName.'.update',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => $modelName.'.delete',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }
}
