<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'rooms',
        'cost_price',
        'sale_price',
        'employee_id',
        'notes',
        'days',
        'amount_due_to_hotel',
        'amount_due_from_company',
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

    /** 
     * عدد الليالي المنتهية حتى اليوم (أو حتى تاريخ الخروج إن سبق اليوم)
     */
    public function getLapsedDaysAttribute()
    {
        $today    = Carbon::today();
        $checkout = Carbon::parse($this->check_out);
        $end      = $checkout->lessThanOrEqualTo($today) ? $checkout : $today;
        return $end->diffInDays(Carbon::parse($this->check_in));
    }

    /** المستحق لجهة الحجز (الي حتى اليوم) */
    public function getDueToAgentAttribute()
    {
        return $this->lapsed_days * $this->rooms * $this->cost_price;
    }

    /** المستحق للشركة (الي حتى اليوم) */
    public function getDueToCompanyAttribute()
    {
        return $this->lapsed_days * $this->rooms * $this->sale_price;
    }

    /** مجموع ما دفعت جهة الحجز عبر دفعاتها حتى اليوم */
    public function getAgentPaidAttribute()
    {
        // استبدل agentPayments() بعلاقة payments() المعرفة في Agent
        return $this->agent
                    ->payments()                // ⚠️ هنا
                    ->whereDate('payment_date', '<=', now())
                    ->sum('amount');
    }

    /** مجموع ما دفعت الشركة عبر دفعاتها حتى اليوم */
    public function getCompanyPaidAttribute()
    {
        return $this->company->payments()
               ->whereDate('payment_date','<=', now())
               ->sum('amount');
    }

    /** المتبقي على جهة الحجز */
    public function getAgentRemainingAttribute()
    {
        return $this->due_to_agent - $this->agent_paid;
    }

    /** المتبقي على الشركة */
    public function getCompanyRemainingAttribute()
    {
        return $this->due_to_company - $this->company_paid;
    }

    /** مجموع ما دفعت الشركة عبر جميع دفعاتها */
    public function companyPaid()
    {
        return $this->company->payments()->sum('amount');
    }

    /** مجموع ما دفعت جهة الحجز عبر جميع دفعاتها */
    public function agentPaid()
    {
        return $this->agent->agentPayments()->sum('amount');
    }

    public function getTotalDueAttribute()
    {
        $totalNights = Carbon::parse($this->check_in)
                        ->diffInDays(Carbon::parse($this->check_out));
        return $totalNights * $this->rooms * $this->cost_price;
        // عدد الليالي الكلي مضروب في سعر الفندق
    }

    /** عدد الليالي الكلي بين check_in و check_out */
    public function getTotalNightsAttribute()
    {
        return Carbon::parse($this->check_in)
                     ->diffInDays(Carbon::parse($this->check_out));
    }

    /** المستحق الكلي للشركة: ليالي × غرف × سعر البيع */
    public function getTotalCompanyDueAttribute()
    {
        return $this->total_nights * $this->rooms * $this->sale_price;
    }

    /** المستحق الكلي للوكيل: ليالي × غرف × سعر الفندق */
    public function getTotalAgentDueAttribute()
    {
        return $this->total_nights * $this->rooms * $this->cost_price;
    }
}