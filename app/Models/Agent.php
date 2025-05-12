<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'agent_id');
        // جهة الحجز يمكن أن يكون لها العديد من الحجوزات
    }
        // *** علاقة جهة الحجز بالإتاحات ***
        public function availabilities()
        {
            return $this->hasMany(Availability::class);
        }
    
    public function payments()
    {
        return $this->hasMany(AgentPayment::class,'agent_id');
        // جهة الحجز يمكن أن يكون لها العديد من المدفوعات
    }
    public function getTotalPaidAttribute()
    {
        // حساب إجمالي المدفوع من جدول AgentPayment
        return $this->payments()->sum('amount');
    }

    public function getTotalDueAttribute()
    {
        // حساب إجمالي المستحق من الحجوزات كان في هنا غلطة المفروض أضرب في سعر الفندق
        return $this->bookings->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });
    }

    public function getRemainingAttribute()
    {
        //   المتبقي
        // return max($this->total_due - $this->total_paid, 0); // التأكد من أن المتبقي لا يكون أقل من صفر
          // حساب المتبقي بشكل صحيح (يسمح بالسالب)
          return $this->total_due - $this->total_paid;

    }
/**
 * حساب إجمالي المستحق للوكيل مصنف حسب العملة
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
 * حساب المدفوع للوكيل مصنف حسب العملة
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
 * حساب المتبقي للوكيل مصنف حسب العملة
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