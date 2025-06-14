<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPayment extends Model
{
    use HasFactory;
        protected $table = 'company_payments';  

    protected $fillable = [
        'company_id',
        'amount',
        'currency',
        'payment_date',
        'notes',
        'receipt_image_url',
        'employee_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // العلاقات
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    // // فلترة على payments الخاصة بالشركات فقط
    // protected static function booted()
    // {
    //     static::addGlobalScope('company_payments', function ($builder) {
    //         $builder->whereNotNull('company_id');
    //     });
    // }
}
