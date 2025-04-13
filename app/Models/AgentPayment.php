<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPayment extends Model
{
    protected $fillable = [
        'agent_id',
        'amount',
        'payment_date',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'float'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
