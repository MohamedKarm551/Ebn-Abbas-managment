<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes'
    ];

    public function operationReports()
    {
        return $this->hasMany(BookingOperationReport::class);
    }

    // جلب آخر حجز للعميل
    public function getLatestBookingAttribute()
    {
        // البحث في الحجوزات العادية
        $regularBooking = \App\Models\Booking::where('client_name', $this->name)
            ->orWhere('client_name', 'LIKE', '%' . $this->name . '%')
            ->with(['company', 'hotel'])
            ->latest()
            ->first();

        // البحث في حجوزات الرحلات البرية
        $landTripBooking = \App\Models\LandTripBooking::where('client_name', $this->name)
            ->orWhere('client_name', 'LIKE', '%' . $this->name . '%')
            ->with(['company', 'landTrip'])
            ->latest()
            ->first();

        // إرجاع الأحدث
        if (!$regularBooking && !$landTripBooking) {
            return null;
        }

        if (!$regularBooking) {
            return (object)[
                'type' => 'land_trip',
                'booking' => $landTripBooking,
                'company' => $landTripBooking->company ?? null,
                'service_name' => $landTripBooking->landTrip->title ?? 'رحلة برية'
            ];
        }

        if (!$landTripBooking) {
            return (object)[
                'type' => 'hotel',
                'booking' => $regularBooking,
                'company' => $regularBooking->company ?? null,
                'service_name' => $regularBooking->hotel->name ?? 'حجز فندق'
            ];
        }

        // المقارنة بين التاريخين
        $regularDate = $regularBooking->created_at;
        $landTripDate = $landTripBooking->created_at;

        if ($regularDate->gt($landTripDate)) {
            return (object)[
                'type' => 'hotel',
                'booking' => $regularBooking,
                'company' => $regularBooking->company ?? null,
                'service_name' => $regularBooking->hotel->name ?? 'حجز فندق'
            ];
        } else {
            return (object)[
                'type' => 'land_trip',
                'booking' => $landTripBooking,
                'company' => $landTripBooking->company ?? null,
                'service_name' => $landTripBooking->landTrip->title ?? 'رحلة برية'
            ];
        }
    }
}
