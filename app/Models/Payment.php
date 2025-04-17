<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'amount',
        'payment_date',
        'notes',
        'bookings_covered' // سيحتوي على IDs الحجوزات المغطاة بالدفعة
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'bookings_covered' => 'array'    // ← هنا
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
