<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Health
        Health::checks([
            EnvironmentCheck::new(),
            DebugModeCheck::new(),
            DatabaseCheck::new(),
            HorizonCheck::new(),
            ScheduleCheck::new(),
            UsedDiskSpaceCheck::new(),
        ]);
    }
}
