<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // الحقول المسموح بتخصيصها جماعيًا
    protected $fillable = ['name' , 'user_id'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'employee_id');
    }

    // *** علاقة الموظف بالإتاحات ***
    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }
    // *** علاقة الموظف بالمستخدمين ***
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
