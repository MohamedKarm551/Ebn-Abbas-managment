<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = ['trip_id','room_number','room_type','capacity'];

    public function trip() {
        return $this->belongsTo(Trip::class);
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function availableSpots() {
        return $this->capacity - $this->bookings()->count();
    }

    public function dominantGender() {
        $b = $this->bookings()->first();
        return $b ? $b->gender : null;
    }
}