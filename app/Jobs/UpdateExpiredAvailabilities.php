<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Availability; // *** استدعاء موديل الإتاحة ***
use Carbon\Carbon;          // *** استدعاء Carbon ***
use Illuminate\Support\Facades\Log; // *** استدعاء Log (اختياري) ***

class UpdateExpiredAvailabilities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        Log::info('Running UpdateExpiredAvailabilities Job...'); // اختياري: للتأكد إن الـ Job بيشتغل

        // *** بداية التعديل: تغيير علامة المقارنة ***
        // جلب الإتاحات اللي تاريخ نهايتها النهارده أو قبل النهارده وحالتها مش expired بالفعل
        $expiredAvailabilities = Availability::whereDate('end_date', '<=', Carbon::today()) // <-- تغيير هنا من < إلى <=
                                            ->where('status', '!=', 'expired') // تجنب التحديث المتكرر
                                            ->get();
        // *** نهاية التعديل ***

        if ($expiredAvailabilities->count() > 0) {
            Log::info('Found ' . $expiredAvailabilities->count() . ' availabilities to mark as expired.'); // اختياري

            // تحديث حالة الإتاحات دي لـ expired
            foreach ($expiredAvailabilities as $availability) {
                $availability->status = 'expired';
                $availability->save();
                // يمكنك إضافة Log هنا لكل إتاحة لو أردت
            }

            Log::info('Finished updating expired availabilities.'); // اختياري
        } else {
            Log::info('No availabilities found to mark as expired.'); // اختياري
        }
    }
}
