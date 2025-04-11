<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'field',
        'old_value',
        'new_value',
    ];

    // ربط التعديل بالحجز
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
