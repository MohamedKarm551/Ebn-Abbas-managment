<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandTripBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'land_trip_id',
        'land_trip_room_price_id',
        'client_name',
        'company_id',
        'rooms',
        'cost_price',
        'sale_price',
        'amount_due_to_agent',
        'amount_due_from_company',
        'currency',
        'notes',
        'employee_id',
    ];

    // العلاقات
    public function landTrip()
    {
        return $this->belongsTo(LandTrip::class);
    }

    public function roomPrice()
    {
        return $this->belongsTo(LandTripRoomPrice::class, 'land_trip_room_price_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
