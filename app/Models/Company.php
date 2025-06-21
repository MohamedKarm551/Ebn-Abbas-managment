<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
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
        return $this->hasMany(Booking::class, 'company_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function companyPayments()
    {
        return $this->hasMany(CompanyPayment::class, 'company_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ========== الحسابات الإجمالية ==========
    // 1. إجمالي المستحق من الحجوزات (معادلة النظام القديم: sale_price * rooms * days)
    public function getTotalDueAttribute()
    {
        // return $this->bookings->sum(function ($booking) {
        //     return ($booking->sale_price ?? 0) * ($booking->rooms ?? 0) * ($booking->days ?? 0);
        // });
        // المستحق من الحجوزات العادية
        $regularBookingsDue = $this->bookings()->sum(DB::raw('sale_price * rooms * days'));

        // المستحق من الرحلات البرية
        $landTripsDue = $this->landTripBookings()->sum('amount_due_from_company');

        return $regularBookingsDue + $landTripsDue;
    }

    // 2. إجمالي المدفوع (مجموع كل المدفوعات: Payments & CompanyPayments)
    public function getTotalPaidAttribute()
    {
        // $paid1 = $this->payments()->sum('amount') ?? 0;
        // $paid2 = $this->companyPayments()->sum('amount') ?? 0;
        // return $paid1 + $paid2;
        // المدفوعات القديمة (من جدول payments)
        $oldPayments = $this->payments()->sum('amount') ?? 0;

        // المدفوعات الجديدة (من جدول company_payments)
        $newPayments = $this->companyPayments()->sum('amount') ?? 0;

        return $oldPayments + $newPayments;
    }

    // 3. المتبقي = المستحق - المدفوع
    public function getRemainingAmountAttribute()
    {
        return (float) ($this->total_due - $this->total_paid);
    }

    // ========== الإحصائيات حسب العملة ==========
    /**
     * إجمالي المستحق من الحجوزات حسب العملة (يعتمد على حقل جديد "amount_due_from_company")
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
     * إجمالي المدفوع حسب العملة (يدعم كلا Payments وCompanyPayments)
     */
    public function getTotalPaidByCurrencyAttribute()
    {
        // المدفوعات القديمة حسب العملة
        $oldPayments = $this->payments()
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        // المدفوعات الجديدة حسب العملة  
        $newPayments = $this->companyPayments()
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        // دمج المدفوعات
        $result = [];
        $allCurrencies = array_unique(array_merge(array_keys($oldPayments), array_keys($newPayments)));

        foreach ($allCurrencies as $currency) {
            $result[$currency] = ($oldPayments[$currency] ?? 0) + ($newPayments[$currency] ?? 0);
        }

        return $result;
    }

    /**
     * المتبقي على الشركة مصنف حسب العملة
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

    // ========== دوال التوافق القديم ==========
    /**
     * حساب عدد الحجوزات
     */
    public function getBookingsCountAttribute()
    {
        return $this->bookings()->count();
    }
    /**
     * عدد الحجوزات الإجمالي (عادية + رحلات برية)
     */
    public function getTotalBookingsCountAttribute()
    {
        return $this->bookings()->count() + $this->landTripBookings()->count();
    }

    /**
     * @deprecated استخدم remaining_amount بدلاً منه
     */
    public function getRemainingAttribute()
    {
        return $this->remaining_amount;
    }

    public function getTotalsByCurrency()
    {
        // 1. المستحق من الحجوزات العادية حسب العملة
        $regularBookingsDue = $this->bookings()
            ->select('currency', DB::raw('SUM(sale_price * rooms * days) as total_due'))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        // 2. المستحق من الرحلات البرية حسب العملة
        $landTripsDue = $this->landTripBookings()
            ->select('currency', DB::raw('SUM(amount_due_from_company) as total_due'))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');
        // 3. المدفوعات القديمة حسب العملة (من جدول payments) - تعديل لفصل الخصومات
        $oldPaymentsQuery = $this->payments()
            ->select(
                'currency',
                DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as positive_payments'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts')
            )
            ->groupBy('currency');

        $oldPayments = $oldPaymentsQuery->get()->keyBy('currency');


        // 4. ✅ المدفوعات الجديدة حسب العملة (من جدول company_payments)
        // هنا سيتم جمع الدفعات الموجبة والسالبة (الخصومات) معًا
        $newPaymentsQuery = $this->companyPayments()
            ->select(
                'currency',
                DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as positive_payments'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts')
            )
            ->groupBy('currency');

        $newPayments = $newPaymentsQuery->get()->keyBy('currency');





        // اجمع كل العملات المستخدمة فعلياً
        $currencies = collect([$regularBookingsDue, $landTripsDue, $oldPayments, $newPayments])
            ->flatMap(fn($col) => $col->keys())
            ->unique()
            ->values()
            ->all();

        $result = [];

        foreach ($currencies as $currency) {
            $regularDue = isset($regularBookingsDue[$currency]) ? (float)$regularBookingsDue[$currency]->total_due : 0;
            $landTripDue = isset($landTripsDue[$currency]) ? (float)$landTripsDue[$currency]->total_due : 0;
            $totalDue = $regularDue + $landTripDue;

            // المدفوعات الموجبة من الجدولين معاً
            $oldPositivePaid = isset($oldPayments[$currency]) ? (float)$oldPayments[$currency]->positive_payments : 0;
            $newPositivePaid = isset($newPayments[$currency]) ? (float)$newPayments[$currency]->positive_payments : 0;
            $totalPaid = $oldPositivePaid + $newPositivePaid;

            // الخصومات من الجدولين معاً
            $oldDiscounts = isset($oldPayments[$currency]) ? (float)$oldPayments[$currency]->discounts : 0;
            $newDiscounts = isset($newPayments[$currency]) ? (float)$newPayments[$currency]->discounts : 0;
            $totalDiscounts = $oldDiscounts + $newDiscounts;


            $result[$currency] = [
                'due' => $totalDue,
                'paid' => $totalPaid,
                'discounts' => $totalDiscounts,
                'remaining' => $totalDue - $totalPaid - $totalDiscounts // هنا نطرح الخصومات أيضًا
            ];
        }

        return $result;
    }

    // أضف العلاقة مع LandTripBooking
    public function landTripBookings()
    {
        return $this->hasMany(LandTripBooking::class);
    }

    // تعديل getTotalDueAttribute للاستخدام الصحيح
    public function getTotalDueAttributeLandTrip()
    {
        // استخدم LandTripBooking بدلاً من bookings العادية
        $landTripBookings = DB::table('land_trip_bookings')
            ->where('company_id', $this->id)
            ->select('currency', DB::raw('SUM(amount_due_from_company) as total_due'))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        // استخدم الـ payments العادية
        $payments = $this->payments()
            ->select('currency', DB::raw('SUM(amount) as total_paid'))
            ->groupBy('currency')
            ->get()
            ->keyBy('currency');

        $currencies = ['SAR', 'KWD'];
        $result = [];

        foreach ($currencies as $currency) {
            $due = isset($landTripBookings[$currency]) ? (float)$landTripBookings[$currency]->total_due : 0;
            $paid = isset($payments[$currency]) ? (float)$payments[$currency]->total_paid : 0;

            $result[$currency] = [
                'due' => $due,
                'paid' => $paid,
                'remaining' => $due - $paid,
            ];
        }

        return $result;
    }
    public function agents()
    {
        return $this->hasManyThrough(
            Agent::class,
            LandTripBooking::class,
            'company_id', // Foreign key على land_trip_bookings table
            'id', // Foreign key على agents table  
            'id', // Local key على companies table
            'agent_id' // Local key على land_trip_bookings table (عبر land_trip)
        )->distinct();
    }
    // إجمالي المستحق من الحجوزات العادية حسب العملة
    public function getTotalDueBookingsByCurrencyAttribute()
    {
        return $this->bookings()
            ->select('currency', DB::raw('SUM(sale_price * rooms * days) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();
    }

    // إجمالي المدفوع حسب العملة (Payments فقط، تجاهل CompanyPayments)
    public function getTotalPaidBookingsByCurrencyAttribute()
    {
        return $this->payments()
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();
    }

    // المتبقي من الحجوزات فقط (بدون الرحلات البرية)
    public function getRemainingBookingsByCurrencyAttribute()
    {
        $due = $this->total_due_bookings_by_currency;

        // ✅ استخدام المدفوعات من جدول payments فقط (للحجوزات العادية)
        // فصل المدفوعات الموجبة عن الخصومات (السالبة)
        $payments = $this->payments()
            ->select(
                'currency',
                DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as positive_payments'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts')
            )
            ->groupBy('currency')
            ->get();

        // تحويل نتائج الاستعلام إلى مصفوفات
        $paymentsArray = [];
        $discountsArray = [];

        foreach ($payments as $payment) {
            $paymentsArray[$payment->currency] = $payment->positive_payments;
            $discountsArray[$payment->currency] = $payment->discounts;
        }

        // حساب المتبقي لكل عملة بالمعادلة الصحيحة
        $result = [];
        $currencies = array_unique(array_merge(
            array_keys($due),
            array_keys($paymentsArray),
            array_keys($discountsArray)
        ));

        foreach ($currencies as $currency) {
            $dueAmount = $due[$currency] ?? 0;
            $paidAmount = $paymentsArray[$currency] ?? 0;
            $discountAmount = $discountsArray[$currency] ?? 0;

            // المعادلة الصحيحة: المتبقي = المستحق - المدفوع - الخصومات
            $result[$currency] = $dueAmount - $paidAmount - $discountAmount;
        }

        return $result;
    }
}
