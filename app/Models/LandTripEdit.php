<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandTripEdit extends Model
{
    protected $fillable = [
        'land_trip_id', 'user_id', 'field', 'old_value', 'new_value'
    ];

    public function landTrip()
    {
        return $this->belongsTo(LandTrip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
