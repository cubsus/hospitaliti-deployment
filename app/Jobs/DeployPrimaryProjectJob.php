<?php

namespace App\Jobs;

use App\Enums\DeploymentStatusEnum;
use App\Models\Deployment;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class DeployPrimaryProjectJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public ?int $deploymentId = null;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $authUser) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check for existing running deployment
        $existingDeployment = Deployment::where('status', DeploymentStatusEnum::RUNNING->value)->exists();

        if ($existingDeployment) {

            Notification::make()
                ->title('Deployment Already Running')
                ->warning()
                ->body('A deployment is already in progress. Please wait for it to complete before starting a new one.')
                ->sendToDatabase($this->authUser);

            return;
        }

        // Set activity log causer for deployment operations
        Deployment::setActivityCauser($this->authUser->id);

        // Create a new deployment record
        $deployment = Deployment::create([
            'user_id' => $this->authUser->id,
            'status' => DeploymentStatusEnum::RUNNING->value,
            'started_at' => now(),
        ]);

        // Store deployment ID for failure handling
        $this->deploymentId = $deployment->id;

        // Run the deployment command using Symfony Process
        $deploy_command = 'php vendor/bin/envoy run deploy';

        $process = Process::fromShellCommandline($deploy_command, base_path());
        $process->setTimeout(3600);

        $output = '';
        $error = '';

        // Stream output in real-time
        $process->run(function ($type, $buffer) use ($deployment, &$output, &$error) {
            if ($type === Process::ERR) {
                $error .= $buffer;
            } else {
                $output .= $buffer;
            }

            // Update deployment with live output
            $deployment->update([
                'output' => $output,
                'error_output' => $error,
            ]);
        });

        Deployment::setActivityCauser(null);

        // Update deployment record based on process result
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
                ->sendToDatabase($this->authUser);
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
                ->sendToDatabase($this->authUser);
        }

        // Clean up temporary Envoy files
        $this->cleanupEnvoyFiles();
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        if ($this->deploymentId) {

            // Set activity log causer for deployment operations
            Deployment::setActivityCauser(null);

            $deployment = Deployment::find($this->deploymentId);

            if ($deployment) {

                $deployment->update([
                    'status' => DeploymentStatusEnum::FAILED->value,
                    'completed_at' => now(),
                    'error_output' => $exception?->getMessage() ?? 'Job failed unexpectedly',
                    'exit_code' => 1,
                ]);

                Notification::make()
                    ->title('Deployment Failed')
                    ->danger()
                    ->body('The deployment job failed: ' . ($exception?->getMessage() ?? 'Unknown error'))
                    ->sendToDatabase($this->authUser);
            }
        }

        // Clean up temporary Envoy files even on job failure
        $this->cleanupEnvoyFiles();
    }

    /**
     * Clean up temporary Envoy compiled files.
     */
    protected function cleanupEnvoyFiles(): void
    {
        $basePath = base_path();
        $envoyFiles = File::glob($basePath . '/Envoy*.php');

        foreach ($envoyFiles as $file) {

            // Only delete compiled Envoy files (not Envoy.blade.php)
            if (basename($file) !== 'Envoy.blade.php' && str_starts_with(basename($file), 'Envoy')) {
                File::delete($file);
            }
        }
    }
}
