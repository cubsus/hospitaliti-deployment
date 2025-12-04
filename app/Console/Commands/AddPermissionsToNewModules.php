<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;

class AddPermissionsToNewModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-permissions-to-new-modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelNames = [
            'User',
        ];

        $permissionsTypes = [
            '.view',
            '.create',
            '.update',
            '.delete'
        ];

        foreach ($modelNames as $modelName) {
            foreach ($permissionsTypes as $permissionsType) {
                Permission::firstOrCreate(['name' => $modelName . $permissionsType], ['guard_name' => 'web']);
            }
        }
    }
}
