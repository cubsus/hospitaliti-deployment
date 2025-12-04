<?php

use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

// Clean old backups every Friday at 03:00
Schedule::command('backup:clean')
    ->weekly()
    ->fridays()
    ->at('03:00');

// Run backup every Friday at 03:30
Schedule::command('backup:run')
    ->weekly()
    ->fridays()
    ->at('03:30');

// Check system heartbeat every minute
Schedule::command(ScheduleCheckHeartbeatCommand::class)
    ->everyMinute();