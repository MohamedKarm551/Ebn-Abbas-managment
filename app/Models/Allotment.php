<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Allotment extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'start_date',
        'end_date',
        'rooms_count',
        'rate_per_room',
        'currency',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function sales()
    {
        return $this->hasMany(AllotmentSale::class);
    }

    // حساب عدد الأيام في الألوتمنت
    public function getDaysCountAttribute()
    {
        return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
    }
    
    // حساب عدد الغرف المباعة لهذا الألوتمنت
    public function getSoldRoomsAttribute()
    {
        return $this->sales->sum('rooms_sold');
    }
    
    // حساب عدد الغرف المتبقية
    public function getRemainingRoomsAttribute()
    {
        return $this->rooms_count - $this->sold_rooms;
    }
    
    // حساب إجمالي قيمة الألوتمنت
    public function getTotalValueAttribute()
    {
        return $this->rooms_count * $this->rate_per_room * $this->days_count;
    }
}