<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReportTransport extends Model
{
    protected $fillable = [
        'booking_operation_report_id',
        'transport_type',
        'driver_name',
        'driver_phone',
        'vehicle_info',
        'departure_time',
        'arrival_time',
        'schedule_notes',
        'ticket_file_path',
        'cost',
        'selling_price',
        'currency',
        'profit',
        'notes'
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    public function operationReport()
    {
        return $this->belongsTo(BookingOperationReport::class, 'booking_operation_report_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->profit = ($model->selling_price ?? 0) - ($model->cost ?? 0);
        });
    }
}
