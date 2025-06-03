<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAssignment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_room_id',
        'booking_id',
        'check_in',
        'check_out',
        'status', // active, completed, cancelled
        'notes',
        'assigned_by',
    ];
    
    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];
    
    // العلاقة مع الغرفة
    public function room()
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id');
    }
    
    // العلاقة مع الحجز
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    
    // العلاقة مع الموظف الذي قام بالتخصيص
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'assigned_by');
    }
}
