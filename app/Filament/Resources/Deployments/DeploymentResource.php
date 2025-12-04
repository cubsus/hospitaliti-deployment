<?php

namespace App\Filament\Resources\Deployments;

use App\Filament\Resources\Deployments\Pages\ListDeployments;
use App\Filament\Resources\Deployments\Tables\DeploymentsTable;
use App\Models\Deployment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class DeploymentResource extends Resource
{
    protected static ?string $model = Deployment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return DeploymentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeployments::route('/'),
        ];
    }

    public static function getNavigationGroup(): string
    {
        return __('Deployment');
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
