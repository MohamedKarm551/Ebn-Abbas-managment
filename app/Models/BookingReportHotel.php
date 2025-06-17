<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReportHotel extends Model
{
    protected $fillable = [
        'booking_operation_report_id',
        'hotel_name',
        'check_in',
        'check_out',
        'nights',
        'rooms',
        'night_selling_price',
        'total_selling_price',
        'night_cost',
        'total_cost',
        'profit',
        'currency',
        'room_type',
         'voucher_file_path',
        'notes'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    public function operationReport()
    {
        return $this->belongsTo(BookingOperationReport::class, 'booking_operation_report_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // حساب الإجماليات
            $model->total_selling_price = ($model->night_selling_price ?? 0) * ($model->nights ?? 0) * ($model->rooms ?? 1);
            $model->total_cost = ($model->night_cost ?? 0) * ($model->nights ?? 0) * ($model->rooms ?? 1);
            $model->profit = $model->total_selling_price - $model->total_cost;
        });
    }
}
