<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityDailyStatus extends Model
{
    use HasFactory;

    protected $table = 'availability_daily_status';

    protected $fillable = [
        'availability_room_type_id',
        'date',
        'available_rooms',
        'booked_rooms',
    ];

    protected $casts = [
        'date' => 'date',
        'available_rooms' => 'integer',
        'booked_rooms' => 'integer',
    ];

    /**
     * العلاقة مع نوع الغرفة في الإتاحة
     */
    public function availabilityRoomType()
    {
        return $this->belongsTo(AvailabilityRoomType::class);
    }

    /**
     * الغرف المتاحة الفعلية (المتاحة - المحجوزة)
     */
    public function getRemainingRoomsAttribute()
    {
        return $this->available_rooms - $this->booked_rooms;
    }

    /**
     * هل اليوم ده ممتلئ؟
     */
    public function getIsFullyBookedAttribute()
    {
        return $this->booked_rooms >= $this->available_rooms;
    }
}