<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'month_year',
        'salaries',
        'advertising',
        'rent',
        'staff_commissions',
        'other_expenses',

        'total_monthly_profit_SAR',
        'total_monthly_profit_KWD',

        'net_profit_SAR',
        'net_profit_KWD',

        'ismail_share_SAR',
        'ismail_share_KWD',

        'mohamed_share_SAR',
        'mohamed_share_KWD',
        'expenses_currencies',
        'unified_currency',
        'exchange_rate',
        'start_date',
        'end_date',
        'notes'
    ];

    protected $casts = [
        'other_expenses' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'expenses_currencies' => 'array',  

    ];

    // حساب إجمالي المصاريف
    public function getTotalExpensesAttribute()
    {
        $otherExpensesTotal = 0;
        if ($this->other_expenses) {
            foreach ($this->other_expenses as $expense) {
                $otherExpensesTotal += floatval($expense['amount'] ?? 0);
            }
        }

        return $this->salaries + $this->advertising + $this->rent +
            $this->staff_commissions + $otherExpensesTotal;
    }
}
