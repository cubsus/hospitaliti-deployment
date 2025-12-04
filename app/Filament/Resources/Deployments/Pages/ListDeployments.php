<?php

namespace App\Filament\Resources\Deployments\Pages;

use App\Filament\Resources\Deployments\DeploymentResource;
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
                ->label('Trigger Deployment')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Trigger New Deployment')
                ->modalDescription('Are you sure you want to trigger a new deployment? This will run the deployment process.')
                ->modalSubmitActionLabel('Yes, Deploy')
                ->action(function () {
                    $deployment = Deployment::create([
                        'user_id' => Auth::id(),
                        'status' => 'running',
                        'started_at' => now(),
                    ]);

                    try {
                        Artisan::call('app:deploy-main-project');

                        $output = Artisan::output();

                        $deployment->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'output' => $output,
                            'exit_code' => 0,
                        ]);

                        Notification::make()
                            ->title('Deployment Completed')
                            ->success()
                            ->body('The deployment has been completed successfully.')
                            ->send();
                    } catch (\Exception $e) {
                        $deployment->update([
                            'status' => 'failed',
                            'completed_at' => now(),
                            'error_output' => $e->getMessage(),
                            'exit_code' => 1,
                        ]);

                        Notification::make()
                            ->title('Deployment Failed')
                            ->danger()
                            ->body('The deployment has failed: ' . $e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
