<?php

namespace App\Filament\Pages;

use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthCheckResults extends BaseHealthCheckResults
{
    public static function getNavigationLabel(): string
    {
        return __('Health');
    }

    public function getTitle(): string
    {
        return __('Health');
    }

    public static function getNavigationGroup(): string
    {
        return __('Settings');
    }

    public static function getNavigationSort(): int
    {
        return 11;
    }
}
