<?php

namespace App\Filament\Resources\Deployments\Pages;

use App\Filament\Resources\Deployments\DeploymentResource;
use App\Enums\DeploymentStatusEnum;
use App\Models\Deployment;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
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
                    $deployment = Deployment::create([
                        'user_id' => Auth::id(),
                        'status' => DeploymentStatusEnum::RUNNING->value,
                        'started_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Deployment queued')
                        ->success()
                        ->body('The deployment has been queued.')
                        ->send();

                    try {
                        Artisan::call('app:deploy-main-project');

                        $output = Artisan::output();

                        $deployment->update([
                            'status' => DeploymentStatusEnum::COMPLETED->value,
                            'completed_at' => now(),
                            'output' => $output,
                            'exit_code' => 0,
                        ]);

                        Notification::make()
                            ->title('Deployment Completed')
                            ->success()
                            ->body('The deployment has been completed successfully.')
                            ->toDatabase();
                    } catch (\Exception $e) {
                        $deployment->update([
                            'status' => DeploymentStatusEnum::FAILED->value,
                            'completed_at' => now(),
                            'error_output' => $e->getMessage(),
                            'exit_code' => 1,
                        ]);

                        Notification::make()
                            ->title('Deployment Failed')
                            ->danger()
                            ->body('The deployment has failed: ' . $e->getMessage())
                            ->toDatabase();
                    }
                }),
        ];
    }
}
