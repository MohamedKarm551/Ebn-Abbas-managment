<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandTripsCompanyPayment extends Model
{
    protected $table = 'landtrips_company_payments';

    protected $fillable = [
        'company_id','agent_id','employee_id','currency','amount','payment_date','notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
    ];

    public function company() { return $this->belongsTo(\App\Models\Company::class); }
    public function agent()   { return $this->belongsTo(\App\Models\Agent::class); }
    public function employee(){ return $this->belongsTo(\App\Models\Employee::class); }
}
