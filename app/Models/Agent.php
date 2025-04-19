<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'agent_id');
        // جهة الحجز يمكن أن يكون لها العديد من الحجوزات
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
        // حساب المتبقي
        return max($this->total_due - $this->total_paid, 0); // التأكد من أن المتبقي لا يكون أقل من صفر
    }
}