<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPayment extends Model
{
    protected $fillable = [
        'agent_id',
        'amount',
        'currency',  
        'payment_date',
        'notes',
        'account_id',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'float'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
        // كل دفعة مرتبطة بجهة حجز واحدة
    }
}
