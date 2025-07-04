<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address'
    ];

    // ========== العلاقات ==========
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'agent_id');
    }

    public function payments()
    {
        return $this->hasMany(AgentPayment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ========== الحسابات الإجمالية ==========
    public function calculateTotals()
    {
        // حساب المستحق حسب العملة
        $dueByCurrency = $this->bookings()
            ->select('currency', DB::raw('SUM(amount_due_to_hotel) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        // حساب المدفوعات والخصومات حسب العملة
        $payments = $this->payments()
            ->select(
                'currency',
                DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as paid'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts')
            )
            ->groupBy('currency')
            ->get();

        $paidByCurrency = [];
        $discountsByCurrency = [];
        foreach ($payments as $payment) {
            $paidByCurrency[$payment->currency] = (float) $payment->paid;
            $discountsByCurrency[$payment->currency] = (float) $payment->discounts;
        }

        // حساب المتبقي حسب العملة
        $remainingByCurrency = [];
        foreach (['SAR', 'KWD'] as $currency) {
            $due = $dueByCurrency[$currency] ?? 0;
            $paid = $paidByCurrency[$currency] ?? 0;
            $discounts = $discountsByCurrency[$currency] ?? 0;
            $remainingByCurrency[$currency] = $due - $paid - $discounts;
        }
        // لضمان أن كل العملات متاحة ولو بقيم صفرية
foreach (['SAR', 'KWD'] as $currency) {
    if (!isset($dueByCurrency[$currency])) {
        $dueByCurrency[$currency] = 0;
    }
    if (!isset($paidByCurrency[$currency])) {
        $paidByCurrency[$currency] = 0;
    }
    if (!isset($discountsByCurrency[$currency])) {
        $discountsByCurrency[$currency] = 0;
    }
    if (!isset($remainingByCurrency[$currency])) {
        $remainingByCurrency[$currency] = 0;
    }
}


        // حفظ القيم المحسوبة في attributes للوصول إليها لاحقاً
        $this->attributes['computed_total_due_by_currency'] = $dueByCurrency;
        $this->attributes['computed_total_paid_by_currency'] = $paidByCurrency;
        $this->attributes['computed_total_discounts_by_currency'] = $discountsByCurrency;
        $this->attributes['computed_remaining_by_currency'] = $remainingByCurrency;
        $this->attributes['computed_total_due'] = array_sum($dueByCurrency);
        $this->attributes['computed_total_paid'] = array_sum($paidByCurrency);

        return $this; // للسماح بالتسلسل method chaining
    }

    // إضافة getters للوصول إلى البيانات المحسوبة
    public function getComputedTotalPaidByCurrencyAttribute()
    {
        return $this->attributes['computed_total_paid_by_currency'] ?? [];
    }

    public function getComputedTotalDiscountsByCurrencyAttribute()
    {
        return $this->attributes['computed_total_discounts_by_currency'] ?? [];
    }

    /**
     * إجمالي المستحق للوكيل من كل الحجوزات (cost_price * rooms * days)
     */
    public function getTotalDueAttribute()
    {
        return $this->bookings()->sum(DB::raw('cost_price * rooms * days'));
    }

    /**
     * إجمالي المدفوع للوكيل
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    /**
     * المتبقي الإجمالي (المستحق - المدفوع)
     */
    public function getRemainingAmountAttribute()
    {
        return (float)($this->total_due - $this->total_paid);
    }

    /**
     * حساب المستحق من الحجوزات حسب العملة
     */
    public function getTotalDueByCurrencyAttribute()
    {
        return $this->bookings()
            ->select('currency', DB::raw('SUM(amount_due_to_hotel) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();
    }

    /**
     * حساب المدفوعات حسب العملة
     */
    public function getTotalPaidByCurrencyAttribute()
    {
        // نفس الطريقة المستخدمة في Company.php
        $payments = $this->payments()
            ->select(
                'currency',
                DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as positive_payments'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts')
            )
            ->groupBy('currency')
            ->get();

        // تحويل النتائج لمصفوفة مثل Company.php
        $result = [];
        foreach ($payments as $payment) {
            // المجموع الصافي = المدفوع الموجب + الخصومات (لأن الخصومات تقلل من المتبقي)
            $result[$payment->currency] = $payment->positive_payments + $payment->discounts;
        }

        return $result;
    }

    /**
     * حساب المتبقي حسب العملة
     */

    public function getRemainingByCurrencyAttribute()
    {
        $dueByCurrency = $this->total_due_by_currency;
        $paidByCurrency = $this->total_paid_by_currency; // الآن يحسب صح
        $remainingByCurrency = [];

        // استخدم جميع العملات الموجودة في أي من الاثنين
        $currencies = array_unique(array_merge(array_keys($dueByCurrency), array_keys($paidByCurrency)));

        foreach ($currencies as $currency) {
            $due = $dueByCurrency[$currency] ?? 0;
            $paid = $paidByCurrency[$currency] ?? 0;
            $remainingByCurrency[$currency] = $due - $paid;
        }

        return $remainingByCurrency;
    }
    /**
     * نفس الدالة مع اسم مختلف للتوافق مع الكود الموجود
     */
    public function getRemainingBookingsByCurrencyAttribute()
    {
        return $this->remaining_by_currency;
    }

    /**
     * حساب التفاصيل المالية الكاملة حسب العملة (مثل الشركات)
     */
    public function getTotalsByCurrency()
    {
        // 1. المستحق من الحجوزات حسب العملة
        $bookingsDue = $this->bookings()
            ->select('currency', DB::raw('SUM(cost_price * rooms * days) as total_due'))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        // 2. المدفوعات حسب العملة (فصل الموجبة عن السالبة)
        $payments = $this->payments()
            ->select(
                'currency',
                DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as positive_payments'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts')
            )
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        // 3. تجميع كل العملات المستخدمة
        $currencies = collect([$bookingsDue, $payments])
            ->flatMap(fn($col) => $col->keys())
            ->unique()
            ->values()
            ->all();

        $result = [];

        foreach ($currencies as $currency) {
            $due = isset($bookingsDue[$currency]) ? (float)$bookingsDue[$currency]->total_due : 0;
            $paid = isset($payments[$currency]) ? (float)$payments[$currency]->positive_payments : 0;
            $discounts = isset($payments[$currency]) ? (float)$payments[$currency]->discounts : 0;

            $result[$currency] = [
                'due' => $due,
                'paid' => $paid,
                'discounts' => $discounts,
                'remaining' => $due - $paid - $discounts
            ];
        }

        return $result;
    }

    // ========== دوال التوافق القديم ==========

    /**
     * @deprecated استخدم remaining_amount بدلاً منه
     */
    public function getRemainingAttribute()
    {
        return $this->remaining_amount;
    }

    /**
     * حساب عدد الحجوزات
     */
    public function getBookingsCountAttribute()
    {
        return $this->bookings()->count();
    }
}
