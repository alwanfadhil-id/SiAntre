<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the daily queue reset at midnight
        $schedule->command('queue:reset-daily')->dailyAt('00:00');

        // Clean up old queues weekly (every Sunday at 2 AM)
        $schedule->command('queue:cleanup --days=30')->weeklyOn(0, '02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        require base_path('routes/console.php');
    }
}