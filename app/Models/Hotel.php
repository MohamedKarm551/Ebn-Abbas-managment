<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location'];

    public function bookings()
    {
        return $this->hasMany(Booking::class,'hotel_id');
    }
    public function getTotalDueAttribute()
{
    // حساب إجمالي المستحق من الحجوزات
    return $this->bookings->sum(function ($booking) {
        return $booking->cost_price * $booking->rooms * $booking->days;
    });
}
}