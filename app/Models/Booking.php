<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\RoomAssignment;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletes; // <-- أضف SoftDeletes هنا

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
        'currency',
        'availability_room_type_id', // *** إضافة الحقل الجديد ***
        'amount_paid_by_company', // تأكد من وجود هذا الحقل في المايجريشن
        'amount_paid_to_hotel', // تأكد من وجود هذا الحقل في المايجريشن
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'cost_price' => 'float',
        'sale_price' => 'float',
        'days' => 'integer',
        'rooms' => 'integer',
        'amount_due_to_hotel' => 'float',
        'amount_due_from_company' => 'float',
        'amount_paid_by_company' => 'float',
        'amount_paid_to_hotel' => 'float',
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
    // *** علاقة الحجز بصف سعر الإتاحة ***
    public function availabilityRoomType()
    {
        return $this->belongsTo(AvailabilityRoomType::class);
    }

    // *** علاقة مساعدة للوصول للإتاحة مباشرة ***
    public function availability()
    {
        // نستخدم optional() للتعامل مع حالة null بأمان
        return optional($this->availabilityRoomType)->availability();
        // أو الطريقة الأحدث باستخدام Nullsafe operator (PHP 8+)
        // return $this->availabilityRoomType?->availability();
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

    /** المستحق لجهة الحجز (     ) */
    public function getDueToAgentAttribute()
    {
        return $this->rooms * $this->days * $this->cost_price;
    }

    /** المستحق للشركة (الي    ) */
    public function getDueToCompanyAttribute()
    {
        return $this->days * $this->rooms * $this->sale_price;
    }

    /** مجموع ما دفعت جهة الحجز عبر دفعاتها حتى اليوم */
    public function getAgentPaidAttribute()
    {
        // استبدل agentPayments() بعلاقة payments() المعرفة في Agent
        return $this->agent->payments()->sum('amount');
    }

    /** مجموع ما دفعت الشركة عبر دفعاتها حتى اليوم */
    public function getCompanyPaidAttribute()
    {
        return $this->amount_paid_by_company ?? 0;
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
    // *** علاقة الحجز بالغرف المخصصة ***
    /**
     * العلاقة مع تخصيص الغرفة
     * تمثل تخصيص الغرفة لهذا الحجز
     */
    public function roomAssignment()
    {
        return $this->hasOne(RoomAssignment::class);
    }

    /**
     * إذا كان الحجز يمكن أن يكون له أكثر من تخصيص غرفة (تاريخياً)
     */
    public function roomAssignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }
    // ===== علاقة مع المتابعة المالية للحجز =====
    /**
     * علاقة مع المتابعة المالية للحجز
     */
    public function financialTracking()
    {
        return $this->hasOne(BookingFinancialTracking::class);
    }
}
