<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Client;
use App\Models\Company;
use App\Models\BookingReportVisa;
use App\Models\BookingReportFlight;
use App\Models\BookingReportTransport;
use App\Models\BookingReportHotel;
use App\Models\BookingReportLandTrip;

class BookingOperationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'report_date',
        'client_id',
        'client_name',
        'client_phone',
        'company_id',
        'company_name',
        'company_phone',
        'booking_type',
        'booking_id',
        'booking_reference',
        'total_visa_profit',
        'total_flight_profit',
        'total_transport_profit',
        'total_hotel_profit',
        'total_land_trip_profit',
        'grand_total_profit',
        'employee_profit',       
        'employee_profit_currency', 
        'currency',
        'status',
        'notes'
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    // العلاقات
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function visas()
    {
        return $this->hasMany(BookingReportVisa::class, 'booking_operation_report_id');
    }

    public function flights()
    {
        return $this->hasMany(BookingReportFlight::class, 'booking_operation_report_id');
    }

    public function transports()
    {
        return $this->hasMany(BookingReportTransport::class, 'booking_operation_report_id');
    }

    public function hotels()
    {
        return $this->hasMany(BookingReportHotel::class, 'booking_operation_report_id');
    }

    public function landTrips()
    {
        return $this->hasMany(BookingReportLandTrip::class, 'booking_operation_report_id');
    }

    // حساب الإجماليات تلقائياً
    public function calculateTotals()
    {
        $this->total_visa_profit = $this->visas()->sum('profit') ?? 0;
        $this->total_flight_profit = $this->flights()->sum('profit') ?? 0;
        $this->total_transport_profit = $this->transports()->sum('profit') ?? 0;
        $this->total_hotel_profit = $this->hotels()->sum('profit') ?? 0;
        $this->total_land_trip_profit = $this->landTrips()->sum('profit') ?? 0;

        $this->grand_total_profit =
            $this->total_visa_profit +
            $this->total_flight_profit +
            $this->total_transport_profit +
            $this->total_hotel_profit +
            $this->total_land_trip_profit;

        return $this->save();
    }

    // ✅ تحسين accessor الأرباح حسب العملة
    public function getProfitsByCurrencyAttribute()
    {
        $profits = [];

        // جمع أرباح التأشيرات
        foreach ($this->visas as $visa) {
            $currency = $visa->currency ?? 'KWD';
            if (!isset($profits[$currency])) {
                $profits[$currency] = 0;
            }
            $profits[$currency] += $visa->profit ?? 0;
        }

        // جمع أرباح الطيران
        foreach ($this->flights as $flight) {
            $currency = $flight->currency ?? 'KWD';
            if (!isset($profits[$currency])) {
                $profits[$currency] = 0;
            }
            $profits[$currency] += $flight->profit ?? 0;
        }

        // جمع أرباح النقل
        foreach ($this->transports as $transport) {
            $currency = $transport->currency ?? 'KWD';
            if (!isset($profits[$currency])) {
                $profits[$currency] = 0;
            }
            $profits[$currency] += $transport->profit ?? 0;
        }

        // جمع أرباح الفنادق
        foreach ($this->hotels as $hotel) {
            $currency = $hotel->currency ?? 'KWD';
            if (!isset($profits[$currency])) {
                $profits[$currency] = 0;
            }
            $profits[$currency] += $hotel->profit ?? 0;
        }

        // جمع أرباح الرحلات البرية
        foreach ($this->landTrips as $landTrip) {
            $currency = $landTrip->currency ?? 'KWD';
            if (!isset($profits[$currency])) {
                $profits[$currency] = 0;
            }
            $profits[$currency] += $landTrip->profit ?? 0;
        }

        // إرجاع العملات التي تحتوي على أرباح فقط
        return array_filter($profits, function ($value) {
            return $value > 0;
        });
    }


    // أكسيسور للربح الإجمالي
    public function getTotalProfitAttribute()
    {
        return $this->grand_total_profit ?: (
            $this->visas()->sum('profit') +
            $this->flights()->sum('profit') +
            $this->transports()->sum('profit') +
            $this->hotels()->sum('profit') +
            $this->landTrips()->sum('profit')
        );
    }

    // جلب الحجز المرتبط
    public function getRelatedBookingAttribute()
    {
        if ($this->booking_type === 'hotel' && $this->booking_id) {
            return \App\Models\Booking::find($this->booking_id);
        } elseif ($this->booking_type === 'land_trip' && $this->booking_id) {
            return \App\Models\LandTripBooking::find($this->booking_id);
        }
        return null;
    }
    // public function getProfitsByCurrencyAttribute()
    // {
    //     $profits = [
    //         'KWD' => 0,
    //         'SAR' => 0,
    //         'USD' => 0,
    //         'EUR' => 0
    //     ];

    //     // جمع أرباح التأشيرات
    //     foreach ($this->visas as $visa) {
    //         if (isset($profits[$visa->currency])) {
    //             $profits[$visa->currency] += $visa->profit;
    //         }
    //     }

    //     // جمع أرباح الطيران
    //     foreach ($this->flights as $flight) {
    //         if (isset($profits[$flight->currency])) {
    //             $profits[$flight->currency] += $flight->profit;
    //         }
    //     }

    //     // جمع أرباح النقل
    //     foreach ($this->transports as $transport) {
    //         if (isset($profits[$transport->currency])) {
    //             $profits[$transport->currency] += $transport->profit;
    //         }
    //     }

    //     // جمع أرباح الفنادق
    //     foreach ($this->hotels as $hotel) {
    //         if (isset($profits[$hotel->currency])) {
    //             $profits[$hotel->currency] += $hotel->profit;
    //         }
    //     }

    //     // جمع أرباح الرحلات البرية
    //     foreach ($this->landTrips as $landTrip) {
    //         if (isset($profits[$landTrip->currency])) {
    //             $profits[$landTrip->currency] += $landTrip->profit;
    //         }
    //     }

    //     // إرجاع العملات التي تحتوي على أرباح فقط
    //     return array_filter($profits, function ($value) {
    //         return $value > 0;
    //     });
    // }

    /**
     * الحصول على الأرباح مفصلة حسب النوع والعملة
     */
    public function getProfitsByCurrencyDetailedAttribute()
    {
        // ✅ التركيز على العملتين الأساسيتين فقط
        $profits = [
            'visa' => ['KWD' => 0, 'SAR' => 0],
            'flight' => ['KWD' => 0, 'SAR' => 0],
            'transport' => ['KWD' => 0, 'SAR' => 0],
            'hotel' => ['KWD' => 0, 'SAR' => 0],
            'land_trip' => ['KWD' => 0, 'SAR' => 0],
        ];

        // جمع أرباح التأشيرات
        foreach ($this->visas as $visa) {
            $currency = $visa->currency ?? 'KWD';
            if (in_array($currency, ['KWD', 'SAR']) && isset($profits['visa'][$currency])) {
                $profits['visa'][$currency] += $visa->profit ?? 0;
            }
        }

        // جمع أرباح الطيران
        foreach ($this->flights as $flight) {
            $currency = $flight->currency ?? 'KWD';
            if (in_array($currency, ['KWD', 'SAR']) && isset($profits['flight'][$currency])) {
                $profits['flight'][$currency] += $flight->profit ?? 0;
            }
        }

        // جمع أرباح النقل
        foreach ($this->transports as $transport) {
            $currency = $transport->currency ?? 'KWD';
            if (in_array($currency, ['KWD', 'SAR']) && isset($profits['transport'][$currency])) {
                $profits['transport'][$currency] += $transport->profit ?? 0;
            }
        }

        // جمع أرباح الفنادق
        foreach ($this->hotels as $hotel) {
            $currency = $hotel->currency ?? 'KWD';
            if (in_array($currency, ['KWD', 'SAR']) && isset($profits['hotel'][$currency])) {
                $profits['hotel'][$currency] += $hotel->profit ?? 0;
            }
        }

        // جمع أرباح الرحلات البرية
        foreach ($this->landTrips as $landTrip) {
            $currency = $landTrip->currency ?? 'KWD';
            if (in_array($currency, ['KWD', 'SAR']) && isset($profits['land_trip'][$currency])) {
                $profits['land_trip'][$currency] += $landTrip->profit ?? 0;
            }
        }

        return $profits;
    }
}
