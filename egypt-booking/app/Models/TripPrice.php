<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TripPrice extends Model
{
    use SoftDeletes;
    protected $fillable = ['trip_id', 'room_type', 'price'];
}
