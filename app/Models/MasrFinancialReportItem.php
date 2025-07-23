<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasrFinancialReportItem extends Model
{
    protected $fillable = [
        'report_id', 'title', 'cost_amount', 'cost_currency', 'sale_amount', 'sale_currency'
    ];
    protected $table = 'masr_financial_report_items';

    public function report()
    {
        return $this->belongsTo(MasrFinancialReport::class, 'report_id');
    }
}