<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReportVisa extends Model
{
    protected $fillable = [
        'booking_operation_report_id',
        'visa_type',
        'cost',
        'selling_price',
        'profit',
        'currency',
        'quantity',
        'notes'
    ];

    public function operationReport()
    {
        return $this->belongsTo(BookingOperationReport::class, 'booking_operation_report_id');
    }

    // حساب الربح تلقائياً
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->profit = ($model->selling_price ?? 0) - ($model->cost ?? 0);
        });
    }
}
