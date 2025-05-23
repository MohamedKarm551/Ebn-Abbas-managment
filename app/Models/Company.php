<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
use Illuminate\Notifications\Notifiable; // إذا كنت تستخدم الإشعارات
use App\Models\User;
use Illuminate\Support\Facades\DB; // تأكد من إضافة هذا السطر

class Company extends Model
{
    use HasFactory;

    // الحقول المسموح بتخصيصها جماعيًا
    protected $fillable = ['name'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'company_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalPaidAttribute()
    {
        // التأكد من أن هناك دفعات قبل الحساب
        return $this->payments()->sum('amount') ?? 0;
    }
    public function getTotalDueAttribute()
    {
        // حساب إجمالي المستحق من الحجوزات
        return $this->bookings->sum(function ($booking) {
            return $booking->sale_price * $booking->rooms * $booking->days;
        });
    }
    public function getRemainingAttribute()
    {
        // التأكد من أن هناك حجوزات قبل الحساب
        $totalDue = $this->bookings->sum(function ($booking) {
            // تأكد أن هذه القيم دائمًا أرقام في قاعدة البيانات
            return ($booking->sale_price ?? 0) * ($booking->rooms ?? 0) * ($booking->days ?? 0);
        });

        $totalPaid = $this->total_paid; // تستخدم الـ accessor الآخر

        // *** التأكد من أن القيمة المرتجعة هي رقم عشري (float) ***
        return (float) ($totalDue - $totalPaid);
    }

    /**
     * Get the users associated with the company.
     * علاقة الشركة بالمستخدمين (الشركة لديها عدة مستخدمين)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * حساب إجمالي المستحق من الشركة مصنف حسب العملة
     */
    public function getTotalDueByCurrencyAttribute()
    {
        return $this->bookings()
            ->select('currency', DB::raw('SUM(amount_due_from_company) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();
    }

    /**
     * حساب المدفوع من الشركة مصنف حسب العملة
     */
    public function getTotalPaidByCurrencyAttribute()
    {
        return $this->payments()
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();
    }

    /**
     * حساب المتبقي على الشركة مصنف حسب العملة
     */
    public function getRemainingByCurrencyAttribute()
    {
        $dueByCurrency = $this->total_due_by_currency;
        $paidByCurrency = $this->total_paid_by_currency;
        $remainingByCurrency = [];
        
        foreach ($dueByCurrency as $currency => $due) {
            $paid = $paidByCurrency[$currency] ?? 0;
            $remainingByCurrency[$currency] = $due - $paid;
        }
        
        return $remainingByCurrency;
    }
}
