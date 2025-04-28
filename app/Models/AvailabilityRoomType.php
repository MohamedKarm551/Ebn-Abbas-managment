<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityRoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'availability_id',
        'room_type_id',
        'cost_price',
        'sale_price',
        'allotment', // عدد الغرف المتاحة
    ];

    // *** تحويل الأسعار تلقائياً ***
     protected $casts = [
        'cost_price' => 'float',
        'sale_price' => 'float',
        'allotment' => 'integer',
    ];

    // *** العلاقات ***
    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings()
    {
        // الحجوزات التي تمت بناءً على صف السعر هذا
        return $this->hasMany(Booking::class);
    }
}
