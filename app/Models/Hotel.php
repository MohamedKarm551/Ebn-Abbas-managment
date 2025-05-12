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
        // 'image_path', 
        'description',
        'color',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'hotel_id');
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
    // *** علاقة الفندق بالصور المتعددة ***
    public function images()
    {
        return $this->hasMany(HotelImage::class);
    }
    // Accessor لجلب رابط الصورة الأولى (أو صورة افتراضية)
    public function getFirstImageUrlAttribute()
    {
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_path; // يفترض أن image_path في hotel_images هو URL كامل
        }
        // يمكنك إرجاع رابط صورة افتراضية هنا إذا أردت
        return null; // أو رابط صورة افتراضية
    }

    // *** Accessor لجلب رابط الصورة كامل ***
    // public function getImageUrlAttribute()
    // {
    //     if ($this->image_path) {
    //         // يفترض أن الصور مخزنة في storage/app/public/hotels
    //         // return Storage::url('hotels/' . $this->image_path);
    //         // ممكن أجيب من جوجل درايف  https://drive.google.com/file/d/1yQZDjO-qB6fmo-yAvkUhA70mPp1FjSyl/view?usp=sharing

    //         // return 'https://drive.google.com/uc?export=view&id=' . $this->image_path;
    //         return "";
    //     }
    //     // يمكنك إرجاع رابط صورة افتراضية هنا إذا أردت
    //     return null; // أو رابط صورة افتراضية
    // }
    public function getTotalDueByCurrencyAttribute()
{
    // تجميع الحجوزات حسب العملة وحساب المبالغ المستحقة
    return $this->bookings()
        ->select('currency', \Illuminate\Support\Facades\DB::raw('SUM(amount_due_to_hotel) as total'))
        ->groupBy('currency')
        ->pluck('total', 'currency')
        ->toArray();
}

}
