<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LandTripsAgentPayment extends Model
{
        // ✅ تحديد اسم الجدول بوضوح
    protected $table = 'landtrips_agent_payments';
    

    protected $fillable = [
        'agent_id',
        'amount', 
        'currency',
        'payment_date',
        'payment_method', // نقد، تحويل، شيك
        'reference_number', // رقم المرجع
        'notes',
        'employee_id', // من سجل الدفعة
        'receipt_image_url'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}