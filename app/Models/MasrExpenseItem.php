<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasrExpenseItem extends Model
{
    protected $fillable = ['masr_expense_id', 'title', 'amount', 'currency'];

    public function expense()
    {
        return $this->belongsTo(MasrExpense::class, 'masr_expense_id');
    }
}