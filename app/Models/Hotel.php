<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // *** استيراد Storage ***

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'image_path', // *** إضافة image_path ***
        'description', // *** إضافة description ***
        'color', // *** إضافة color ***
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class,'hotel_id');
    }

    // *** علاقة الفندق بالإتاحات ***
    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function getTotalDueAttribute()
    {
        // حساب إجمالي المستحق من الحجوزات
        return $this->bookings->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });
    }

    // *** Accessor لجلب رابط الصورة كامل ***
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            // يفترض أن الصور مخزنة في storage/app/public/hotels
            // return Storage::url('hotels/' . $this->image_path);
            // ممكن أجيب من جوجل درايف  https://drive.google.com/file/d/1yQZDjO-qB6fmo-yAvkUhA70mPp1FjSyl/view?usp=sharing
            
            // return 'https://drive.google.com/uc?export=view&id=' . $this->image_path;
            return "";
        }
        // يمكنك إرجاع رابط صورة افتراضية هنا إذا أردت
        return null; // أو رابط صورة افتراضية
    }
}