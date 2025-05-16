<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandTripRoomPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'land_trip_id',
        'room_type_id',
        'cost_price',
        'sale_price',
        'currency',
        'allotment',
    ];

    // العلاقات
    public function landTrip()
    {
        return $this->belongsTo(LandTrip::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings()
    {
        return $this->hasMany(LandTripBooking::class);
    }

    // عدد المتاح من الغرف
    public function getAvailableAllotmentAttribute()
    {
        if ($this->allotment === null) {
            return null; // غير محدود
        }

        $booked = $this->bookings()->sum('rooms');
        return max(0, $this->allotment - $booked);
    }
}
