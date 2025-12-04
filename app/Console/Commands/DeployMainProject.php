<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DeployMainProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy-main-project';

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
        $deploy_command = 'php vendor/bin/envoy run deploy';

        $process = Process::fromShellCommandline($deploy_command, base_path());
        $process->setTimeout(3600);

        $process->run();

        echo $process->getOutput();
        echo $process->getErrorOutput();

        Log::info('Error output: '.$process->getErrorOutput());
    }
}
