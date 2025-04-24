<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;
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

    public function getRemainingAttribute()
    {
        // التأكد من أن هناك حجوزات قبل الحساب
        $totalDue = $this->bookings->sum(function ($booking) {
            return $booking->sale_price * $booking->rooms * $booking->days;
        });

        // return max($totalDue - $this->total_paid, 0); // التأكد من أن المتبقي لا يكون أقل من صفر
        // حساب المتبقي بشكل صحيح (يسمح بالسالب)
        return $totalDue - $this->total_paid;
    }

    public function getTotalDueAttribute()
    {
        // حساب إجمالي المستحق من الحجوزات
        return $this->bookings->sum(function ($booking) {
            return $booking->sale_price * $booking->rooms * $booking->days;
        });
    }
}
