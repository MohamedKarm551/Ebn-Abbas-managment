<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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


    /**
     * العلاقة مع مدفوعات الرحلات البرية
     */
    public function landTripsPayments()
    {
        return $this->hasMany(LandTripsAgentPayment::class);
    }

    /**
     * العلاقة مع حجوزات الرحلات البرية عبر LandTrip
     */
    public function landTripBookings()
    {
        return $this->hasManyThrough(
            LandTripBooking::class,
            LandTrip::class,
            'agent_id', // Foreign key on land_trips table
            'land_trip_id', // Foreign key on land_trip_bookings table
            'id', // Local key on agents table
            'id' // Local key on land_trips table
        );
    }

    /**
     * حساب الإجماليات المالية للرحلات البرية فقط حسب العملة
     */
    public function getLandTripTotalsByCurrency()
    {
        $totals = [];

        // ✅ إصلاح: استخدام join مباشر بدلاً من hasManyThrough
        $bookingsDue = DB::table('land_trip_bookings')
            ->join('land_trips', 'land_trips.id', '=', 'land_trip_bookings.land_trip_id')
            ->where('land_trips.agent_id', $this->id)
            ->whereNull('land_trip_bookings.deleted_at')
            ->selectRaw('land_trip_bookings.currency, SUM(land_trip_bookings.amount_due_to_agent) as total_due')
            ->groupBy('land_trip_bookings.currency')
            ->get()
            ->keyBy('currency');

        // المدفوع للوكيل من مدفوعات الرحلات البرية
        $paymentsPaid = $this->landTripsPayments()
            ->selectRaw('currency, SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as total_paid, SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts')
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        foreach (['SAR', 'KWD'] as $currency) {
            $due = $bookingsDue->get($currency)?->total_due ?? 0;
            $paid = $paymentsPaid->get($currency)?->total_paid ?? 0;
            $discounts = $paymentsPaid->get($currency)?->discounts ?? 0;

            $totals[$currency] = [
                'due' => (float) $due,
                'paid' => (float) $paid,
                'discounts' => (float) $discounts,
                'remaining' => (float) ($due - $paid - $discounts)
            ];
        }

        return $totals;
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
    public function financialTracking()
    {
        return $this->hasOne(BookingFinancialTracking::class, 'booking_id');
    }
    // الرصيد الحالي balance
     /**
     * رصيد الوكيل حتى تاريخ (افتراضي اليوم)
     * balance > 0 يعني ما زلنا مدينين للوكيل
     * balance < 0 يعني دُفع له أكثر من المستحق حتى ذلك التاريخ
     */
    public function currentBalance(Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();

        // الحجوزات التي بدأتها (دخلت) حتى التاريخ
        $enteredBookings = $this->bookings()
            ->whereDate('check_in', '<=', $date)
            ->get();

        // إجمالي المستحق (نستخدم due_to_agent إن وُجد)
        $enteredDue = $enteredBookings->sum(function ($b) {
            return $b->due_to_agent
                ?? ($b->amount_due_to_hotel
                    ?? ($b->cost_price * $b->rooms * ($b->days ?? $b->total_nights ?? 1)));
        });

        // المدفوعات والخصومات حتى التاريخ
        $paymentsAgg = $this->payments()
            ->whereDate('payment_date', '<=', $date)
            ->selectRaw("
                SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as paid,
                SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts
            ")
            ->first();

        $paid = (float)($paymentsAgg->paid ?? 0);
        $discounts = (float)($paymentsAgg->discounts ?? 0);
        $effectivePaid = $paid + $discounts;

        // الرصيد = المستحق - (المدفوع + الخصومات)
        $balance = $enteredDue - $effectivePaid;

        return [
            'entered_due'    => round($enteredDue, 2),
            'paid'           => round($paid, 2),
            'discounts'      => round($discounts, 2),
            'effective_paid' => round($effectivePaid, 2),
            'balance'        => round($balance, 2),
        ];
    }
}
