<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private $logFilePath = __DIR__ . '/log/laravel.log';

    protected function schedule(Schedule $schedule): void
    {
        $schedule
                ->command('fetch:newsdataio')
                ->everyFifteenMinutes()
                ->appendOutputTo($this->logFilePath);
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
