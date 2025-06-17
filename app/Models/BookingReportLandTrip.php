<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReportLandTrip extends Model
{
    protected $fillable = [
        'booking_operation_report_id',
        'trip_type',
        'departure_date',
        'return_date',
        'days',
        'transport_cost',
        'mecca_hotel_cost',
        'medina_hotel_cost',
        'extra_costs',
        'total_cost',
        'selling_price',
        'profit',
        'currency',
        'itinerary',
        'notes'
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    public function operationReport()
    {
        return $this->belongsTo(BookingOperationReport::class, 'booking_operation_report_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // حساب إجمالي التكلفة
            $model->total_cost = 
                ($model->transport_cost ?? 0) +
                ($model->mecca_hotel_cost ?? 0) +
                ($model->medina_hotel_cost ?? 0) +
                ($model->extra_costs ?? 0);
            
            // حساب الربح
            $model->profit = ($model->selling_price ?? 0) - $model->total_cost;
        });
    }
}
