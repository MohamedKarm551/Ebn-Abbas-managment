<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasrExpense extends Model
{
    protected $fillable = ['title', 'date', 'created_by', 'notes'];

    public function items()
    {
        return $this->hasMany(MasrExpenseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    
}