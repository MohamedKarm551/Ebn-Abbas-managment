<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // *** استيراد Carbon ***

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'agent_id',
        'employee_id',
        'start_date',
        'end_date',
        'status', // مثلاً: 'active', 'inactive', 'expired'
        'notes',
    ];

    // *** تحويل التواريخ تلقائياً ***
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // *** العلاقات ***
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function availabilityRoomTypes()
    {
        return $this->hasMany(AvailabilityRoomType::class);
    }

    // *** Scopes للفلترة ***

    /**
     * فلترة الإتاحات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->whereDate('end_date', '>=', Carbon::today()); // ولم تنتهِ بعد
    }

    /**
     * فلترة حسب نطاق التواريخ (تداخل)
     */
    public function scopeDateRange($query, $start, $end)
    {
        // التأكد من أن التواريخ هي كائنات Carbon
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->where('start_date', '<=', $endDate)
              ->where('end_date', '>=', $startDate);
        });
    }

    /**
     * فلترة حسب موقع الفندق (عبر العلاقة)
     */
    public function scopeHotelLocation($query, $location)
    {
        return $query->whereHas('hotel', function ($q) use ($location) {
            $q->where('location', 'like', '%' . $location . '%');
        });
    }

    /**
     * فلترة حسب جهة الحجز
     */
    public function scopeAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * فلترة حسب الموظف
     */
    public function scopeEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
