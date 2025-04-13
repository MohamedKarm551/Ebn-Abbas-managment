<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
// عملت تعديلات كتير لازم أعدل كل المايجريشن دي
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
        'sale_price',
        'employee_id',
        'notes'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'cost_price' => 'float',
        'sale_price' => 'float',
        'days' => 'integer',
        'rooms' => 'integer'
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

    public function editLogs()
    {
        return $this->hasMany(EditLog::class);
    }
}