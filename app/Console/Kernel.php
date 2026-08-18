<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tracking:send-tracking-reminders')
            ->days([
                Schedule::MONDAY,
                Schedule::TUESDAY,
                Schedule::WEDNESDAY,
                Schedule::THURSDAY,
                Schedule::FRIDAY,
                Schedule::SATURDAY,
            ])
            ->at('09:05');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
