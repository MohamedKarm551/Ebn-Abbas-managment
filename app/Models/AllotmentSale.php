<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AllotmentSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'allotment_id',
        'hotel_id',
        'company_name',
        'check_in',
        'check_out',
        'rooms_sold',
        'sale_price',
        'currency',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    public function allotment()
    {
        return $this->belongsTo(Allotment::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    
    // حساب عدد الأيام
    public function getDaysCountAttribute()
    {
        return Carbon::parse($this->check_in)->diffInDays(Carbon::parse($this->check_out));
    }
    
    // حساب إجمالي قيمة البيع
    public function getTotalValueAttribute()
    {
        return $this->rooms_sold * $this->sale_price * $this->days_count;
    }
}