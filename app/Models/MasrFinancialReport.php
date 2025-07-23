<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MasrFinancialReport extends Model
{
    protected $fillable = [
        'date',
        'created_by',
        'notes',
        'title' 
    ];
    protected $table = 'masr_financial_reports';

    public function items()
    {
        return $this->hasMany(MasrFinancialReportItem::class, 'report_id');
    }
    public function creator()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}
}
