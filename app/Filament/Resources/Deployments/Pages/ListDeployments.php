<?php

namespace App\Filament\Resources\Deployments\Pages;

use App\Filament\Resources\Deployments\DeploymentResource;
use App\Jobs\DeployPrimaryProjectJob;
use App\Enums\DeploymentStatusEnum;
use App\Models\Deployment;

use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Actions;

use Illuminate\Support\Facades\Auth;

class ListDeployments extends ListRecords
{
    protected static string $resource = DeploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('trigger_deployment')
                ->disabled(fn (): bool => Deployment::where('status', DeploymentStatusEnum::RUNNING->value)->exists())
                ->label('Trigger Deployment')
                ->requiresConfirmation()
                ->modalHeading('Trigger New Deployment')
                ->modalDescription('Are you sure you want to trigger a new deployment? This will run the deployment process.')
                ->modalSubmitActionLabel('Yes, Deploy')
                ->action(function () {

                    DeployPrimaryProjectJob::dispatch(
                        authUser: Auth::user()
                    );

                    Notification::make()
                        ->title('Deployment queued')
                        ->success()
                        ->body('The deployment has been queued.')
                        ->send();
                }),
        ];
    }
}
