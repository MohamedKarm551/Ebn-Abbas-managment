<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'from', 'to', 'hotels', 'description', 'available_seats', 'total_seats'];

    public function prices() {
        return $this->hasMany(TripPrice::class);
    }

    public function items() {
        return $this->hasMany(Item::class);
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function roomAssignments() {
        return $this->hasMany(RoomAssignment::class);
    }   

}
