<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'room_type_name',
    ];

    // *** علاقة نوع الغرفة بصفوف الإتاحة ***
    public function availabilityRoomTypes()
    {
        return $this->hasMany(AvailabilityRoomType::class);
    }
}
