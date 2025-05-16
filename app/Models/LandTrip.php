<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LandTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_date',
        'return_date',
        'days_count',
        'trip_type_id',
        'hotel_id',
        'notes',
        'status',
        'employee_id',
        'agent_id',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'days_count' => 'integer',
    ];

    // العلاقات
    public function tripType()
    {
        return $this->belongsTo(TripType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomPrices()
    {
        return $this->hasMany(LandTripRoomPrice::class);
    }

    public function bookings()
    {
        return $this->hasMany(LandTripBooking::class);
    }

    // سكوب الرحلات النشطة
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->whereDate('return_date', '>=', Carbon::today());
    }

    // سكوب للفلترة حسب جهة الحجز
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    // حساب عدد الأيام تلقائياً
    public static function calculateDaysCount($departureDate, $returnDate)
    {
        return Carbon::parse($departureDate)->diffInDays(Carbon::parse($returnDate)) + 1;
    }
}
