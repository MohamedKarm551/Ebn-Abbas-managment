<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Bus\Dispatchable; // تأكد من وجود هذا السطر
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\UpdateExpiredAvailabilities; // *** استدعاء الـ Job ***

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // شرح الجدولة : 
        // $schedule->command('inspire')->hourly();
    
        // *** إضافة جدولة الـ Job ***
        $schedule->job(new UpdateExpiredAvailabilities)->daily(); // يمكنك تغيير daily() لأي توقيت تاني
        // مثال: يشتغل كل يوم الساعة 1 صباحاً
        // $schedule->job(new UpdateExpiredAvailabilities)->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Register the application's custom commands.
     */
    protected $commands = [
        \App\Console\Commands\ImportBookings::class, // تسجيل الأمر الخاص بالاستيراد
    ];
}