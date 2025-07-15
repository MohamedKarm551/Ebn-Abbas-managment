<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Strategies\PaymentStatusStrategy;
use App\Factories\PaymentStatusFactory;
use App\Observers\FinancialTrackingObserver;

/**
 * نموذج متابعة المعاملات المالية للحجوزات
 * 
 * يستخدم Strategy Pattern لإدارة حالات السداد المختلفة
 * يستخدم Observer Pattern للإشعارات والتنبيهات
 */
class BookingFinancialTracking extends Model
{
    /**
     * اسم الجدول
     */
    protected $table = 'booking_financial_tracking';

    /**
     * الحقول القابلة للتعبئة
     */
    protected $fillable = [
        'booking_id',
        'agent_payment_status',
        'agent_payment_amount',
        'agent_payment_notes',
        'company_payment_status',
        'company_payment_amount',
        'company_payment_notes',
        'payment_deadline',
        'follow_up_date',
        'priority_level',
        'last_updated_by',
    ];

    /**
     * تحويل أنواع البيانات
     */
    protected $casts = [
        'payment_deadline' => 'date',
        'follow_up_date' => 'date',
        'agent_payment_amount' => 'decimal:2',
        'company_payment_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * تسجيل الـ Observer
     */
    protected static function boot()
    {
        parent::boot();
        
        // تسجيل الـ Observer للإشعارات
        static::observe(FinancialTrackingObserver::class);
    }

    // ===== العلاقات (Relations) =====

    /**
     * علاقة مع جدول الحجوزات
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * علاقة مع المستخدم الذي قام بآخر تحديث
     */
    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // ===== Strategy Pattern Methods =====

    /**
     * الحصول على استراتيجية حالة السداد لجهة الحجز
     */
    public function getAgentPaymentStrategy(): PaymentStatusStrategy
    {
        return PaymentStatusFactory::create($this->agent_payment_status);
    }

    /**
     * الحصول على استراتيجية حالة السداد للشركة
     */
    public function getCompanyPaymentStrategy(): PaymentStatusStrategy
    {
        return PaymentStatusFactory::create($this->company_payment_status);
    }

    // ===== Helper Methods للألوان والأيقونات =====

    /**
     * الحصول على لون حالة السداد لجهة الحجز
     */
    public function getAgentStatusColor(): string
    {
        return $this->getAgentPaymentStrategy()->getStatusColor();
    }

    /**
     * الحصول على لون حالة السداد للشركة
     */
    public function getCompanyStatusColor(): string
    {
        return $this->getCompanyPaymentStrategy()->getStatusColor();
    }

    /**
     * الحصول على أيقونة حالة السداد لجهة الحجز
     */
    public function getAgentStatusIcon(): string
    {
        return $this->getAgentPaymentStrategy()->getStatusIcon();
    }

    /**
     * الحصول على أيقونة حالة السداد للشركة
     */
    public function getCompanyStatusIcon(): string
    {
        return $this->getCompanyPaymentStrategy()->getStatusIcon();
    }

    /**
     * الحصول على تسمية حالة السداد لجهة الحجز
     */
    public function getAgentStatusLabel(): string
    {
        return $this->getAgentPaymentStrategy()->getStatusLabel();
    }

    /**
     * الحصول على تسمية حالة السداد للشركة
     */
    public function getCompanyStatusLabel(): string
    {
        return $this->getCompanyPaymentStrategy()->getStatusLabel();
    }

    // ===== Validation Methods =====

    /**
     * التحقق من إمكانية تغيير حالة السداد لجهة الحجز
     */
    public function canChangeAgentStatusTo(string $newStatus): bool
    {
        return $this->getAgentPaymentStrategy()->canTransitionTo($newStatus);
    }

    /**
     * التحقق من إمكانية تغيير حالة السداد للشركة
     */
    public function canChangeCompanyStatusTo(string $newStatus): bool
    {
        return $this->getCompanyPaymentStrategy()->canTransitionTo($newStatus);
    }

    // ===== Calculated Properties =====

    /**
     * حساب النسبة المئوية للسداد لجهة الحجز
     */
    public function getAgentPaymentPercentage(): float
    {
        if (!$this->booking || $this->booking->amount_due_to_hotel == 0) {
            return 0;
        }

        return round(($this->agent_payment_amount / $this->booking->amount_due_to_hotel) * 100, 2);
    }

    /**
     * حساب النسبة المئوية للسداد للشركة
     */
    public function getCompanyPaymentPercentage(): float
    {
        if (!$this->booking || $this->booking->amount_due_from_company == 0) {
            return 0;
        }
        
        return round(($this->company_payment_amount / $this->booking->amount_due_from_company) * 100, 2);
    }

    /**
     * حساب المبلغ المتبقي لجهة الحجز
     */
    public function getAgentRemainingAmount(): float
    {
        if (!$this->booking) {
            return 0;
        }
        
        return max(0, $this->booking->amount_due_to_hotel - $this->agent_payment_amount);
    }

    /**
     * حساب المبلغ المتبقي للشركة
     */
    public function getCompanyRemainingAmount(): float
    {
        if (!$this->booking) {
            return 0;
        }
        
        return max(0, $this->booking->amount_due_from_company - $this->company_payment_amount);
    }

    // ===== Status Check Methods =====

    /**
     * التحقق من وجود متابعة متأخرة
     */
    public function isOverdue(): bool
    {
        return $this->follow_up_date && $this->follow_up_date->isPast();
    }

    /**
     * التحقق من أولوية المتابعة العالية
     */
    public function isHighPriority(): bool
    {
        return $this->priority_level === 'high';
    }

    /**
     * التحقق من اكتمال السداد من جميع الجهات
     */
    public function isFullyPaid(): bool
    {
        return $this->agent_payment_status === 'fully_paid' && 
               $this->company_payment_status === 'fully_paid';
    }

    // ===== Scopes for Queries =====

    /**
     * البحث عن المتابعات المتأخرة
     */
    public function scopeOverdue($query)
    {
        return $query->where('follow_up_date', '<', now())
                    ->whereNotNull('follow_up_date');
    }

    /**
     * البحث عن المتابعات عالية الأولوية
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority_level', 'high');
    }

    /**
     * البحث عن المتابعات المكتملة السداد
     */
    public function scopeFullyPaid($query)
    {
        return $query->where('agent_payment_status', 'fully_paid')
                    ->where('company_payment_status', 'fully_paid');
    }

    /**
     * البحث عن المتابعات غير المكتملة السداد
     */
    public function scopeIncomplete($query)
    {
        return $query->where(function ($q) {
            $q->where('agent_payment_status', '!=', 'fully_paid')
              ->orWhere('company_payment_status', '!=', 'fully_paid');
        });
    }
}
