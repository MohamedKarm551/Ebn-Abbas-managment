<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'company_id',
        'agent_id',
        'hotel_id',
        'room_type',
        'check_in',
        'check_out',
        'days',
        'rooms',
        'cost_price',
        'amount_due_to_hotel',
        'amount_paid_to_hotel',
        'sale_price',
        'amount_due_from_company',
        'amount_paid_by_company',
        'employee_id',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'cost_price' => 'float',
        'amount_due_to_hotel' => 'float',
        'amount_paid_to_hotel' => 'float',
        'sale_price' => 'float',
        'amount_due_from_company' => 'float',
        'amount_paid_by_company' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}