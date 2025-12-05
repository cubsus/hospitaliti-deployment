<?php

namespace App\Enums;

enum DeploymentStatusEnum: string
{
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
