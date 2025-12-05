<?php

namespace App\Jobs;

use App\Enums\DeploymentStatusEnum;
use App\Models\Deployment;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;

use Filament\Notifications\Notification;
use Symfony\Component\Process\Process;

class DeployPrimaryProjectJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $deployment = Deployment::create([
            'user_id' => Auth::id(),
            'status' => DeploymentStatusEnum::RUNNING->value,
            'started_at' => now(),
        ]);

        $deploy_command = 'php vendor/bin/envoy run deploy';

        $process = Process::fromShellCommandline($deploy_command, base_path());
        $process->setTimeout(3600);

        $process->run();

        $output = $process->getOutput();
        $error = $process->getErrorOutput();

        if ($process->isSuccessful()) {
            $deployment->update([
                'status' => DeploymentStatusEnum::COMPLETED->value,
                'completed_at' => now(),
                'output' => $output,
                'error_output' => $error,
                'exit_code' => 0,
            ]);

            Notification::make()
                ->title('Deployment Completed')
                ->success()
                ->body('The deployment has been completed successfully.')
                ->toDatabase();
        } else {
            $deployment->update([
                'status' => DeploymentStatusEnum::FAILED->value,
                'completed_at' => now(),
                'output' => $output,
                'error_output' => $error,
                'exit_code' => 1,
            ]);

            Notification::make()
                ->title('Deployment Failed')
                ->danger()
                ->body('The deployment has failed: ' . $error)
                ->toDatabase();
        }
    }
}
