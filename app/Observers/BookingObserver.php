<?php
namespace App\Observers;

use App\Models\Booking;
use App\Models\BookingFinancialTracking;

class BookingObserver
{
    public function created(Booking $booking)
    {
        // إذا لم يوجد سجل متابعة مالية لهذا الحجز، أنشئه تلقائياً
        if (!$booking->financialTracking) {
            BookingFinancialTracking::create([
                'booking_id' => $booking->id,
                'company_payment_status' => 'not_paid',
                'agent_payment_status' => 'not_paid',
                'company_payment_amount' => 0,
                'agent_payment_amount' => 0,
                'priority_level' => 'medium',
                // يمكنك إضافة أي قيم افتراضية أخرى هنا
            ]);
        }
    }
}