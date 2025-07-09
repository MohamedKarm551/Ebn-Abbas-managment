<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReportFlight extends Model
{
    protected $fillable = [
        'booking_operation_report_id',
        'flight_date',
        'flight_number',
        'airline',
        'route',
        'cost',
        'selling_price',
        'profit',
        'currency',
        'passengers',
        'trip_type',
        'notes'
    ];

    protected $casts = [
        'flight_date' => 'date',
    ];

    public function operationReport()
    {
        return $this->belongsTo(BookingOperationReport::class, 'booking_operation_report_id');
    }
    // Automatically calculate profit before saving the model with quantity of passengers
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->profit = (($model->selling_price ?? 0) - ($model->cost ?? 0)) * ($model->passengers ?? 1);
        });
    }
}
