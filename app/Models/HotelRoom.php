<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RoomAssignment;
use Carbon\Carbon;

class HotelRoom extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'room_number',
        'floor',
        'type',
        'status',
        'notes',
    ];
    
    // العلاقة مع الفندق
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    
    // العلاقة مع الحجوزات (حجز واحد حالي فقط)
    public function currentBooking()
    {
      return $this->hasOne(RoomAssignment::class)
                 ->where('status', 'active')
                   ->where('check_out', '>', Carbon::now())    // ← أضفنا هذا السطر
                 ->with('booking'); 
   }
     /**
     * كل تخصيصات الغرفة النشطة (قد تكون أكثر من تخصيص واحد إذا الغرفة كبيرة)
     */
    public function activeAssignments()
    {
        return $this->hasMany(RoomAssignment::class, 'hotel_room_id')
                    ->where('status', 'active')
                    // أيضًا نجعل check_out بعد الآن
                    ->where('check_out', '>', Carbon::now())
                    ->with('booking.company', 'booking.agent');
    }
     
    
    // العلاقة مع كل الحجوزات (التاريخية)
    public function allBookings()
    {
        return $this->hasMany(RoomAssignment::class);
    }
    
    // هل الغرفة مشغولة؟
    public function getIsOccupiedAttribute()
    {
        // return $this->currentBooking()->exists();
                return $this->activeAssignments()->exists();

    }
    
    // بيانات النزيل الحالي (إذا وجد)
    public function getCurrentGuestAttribute()
    {
        if ($this->is_occupied && $this->currentBooking && $this->currentBooking->booking) {
            return $this->currentBooking->booking;
        }
        return null;
    }
}
