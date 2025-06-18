<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * نموذج سجل تعديلات المصاريف الشهرية
 * يحفظ كل تعديل يتم على أي حقل في المصاريف الشهرية
 */
class MonthlyExpenseLog extends Model
{
    use HasFactory;

    /**
     * اسم الجدول في قاعدة البيانات
     */
    protected $table = 'monthly_expense_logs';

    /**
     * الحقول القابلة للملء بشكل جماعي
     */
    protected $fillable = [
        'monthly_expense_id', // معرف المصروف الشهري
        'user_id',           // معرف المستخدم الذي قام بالتعديل
        'action_type',       // نوع العملية (إنشاء/تعديل/حذف)
        'field_name',        // اسم الحقل المُعدَّل
        'field_label',       // التسمية الواضحة للحقل
        'old_value',         // القيمة القديمة
        'new_value',         // القيمة الجديدة
        'currency',          // العملة
        'notes',             // ملاحظات
        'ip_address',        // عنوان IP
        'user_agent',        // معلومات المتصفح
    ];

    /**
     * الحقول التي يجب تحويلها إلى تواريخ
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * قيم افتراضية للحقول
     */
    protected $attributes = [
        'action_type' => 'updated',
    ];

    /**
     * علاقة مع جدول المصاريف الشهرية
     * كل سجل تعديل ينتمي لمصروف شهري واحد
     */
    public function monthlyExpense(): BelongsTo
    {
        return $this->belongsTo(MonthlyExpense::class, 'monthly_expense_id');
    }

    /**
     * علاقة مع جدول المستخدمين
     * كل سجل تعديل تم بواسطة مستخدم واحد
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * تنسيق نوع العملية لعرض أفضل
     */
    public function getActionTypeDisplayAttribute(): string
    {
        return match ($this->action_type) {
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            default => 'غير محدد',
        };
    }

    /**
     * تنسيق القيمة القديمة للعرض
     */
    public function getFormattedOldValueAttribute(): string
    {
        if (is_null($this->old_value)) {
            return '--';
        }

        // تحسين عرض التواريخ
        if ($this->isDateField()) {
            return $this->formatDateValue($this->old_value);
        }

        // إذا كان الحقل رقمي، نضيف العملة
        if (is_numeric($this->old_value) && $this->currency) {
            return number_format($this->old_value, 2) . ' ' . $this->getCurrencySymbol();
        }

        // معالجة JSON للمصاريف الإضافية
        if ($this->isJsonField()) {
            return $this->formatJsonValue($this->old_value);
        }

        return $this->old_value;
    }

    /**
     * تنسيق القيمة الجديدة للعرض
     */
    public function getFormattedNewValueAttribute(): string
    {
        if (is_null($this->new_value)) {
            return '--';
        }

        // تحسين عرض التواريخ
        if ($this->isDateField()) {
            return $this->formatDateValue($this->new_value);
        }

        // إذا كان الحقل رقمي، نضيف العملة
        if (is_numeric($this->new_value) && $this->currency) {
            return number_format($this->new_value, 2) . ' ' . $this->getCurrencySymbol();
        }

        // معالجة JSON للمصاريف الإضافية
        if ($this->isJsonField()) {
            return $this->formatJsonValue($this->new_value);
        }

        return $this->new_value;
    }

    /**
     * التحقق من أن الحقل يحتوي على تاريخ
     */
    private function isDateField(): bool
    {
        return in_array($this->field_name, ['start_date', 'end_date']);
    }

    /**
     * التحقق من أن الحقل يحتوي على JSON
     */
    private function isJsonField(): bool
    {
        return in_array($this->field_name, ['other_expenses', 'expenses_currencies']);
    }

    /**
     * تنسيق قيم التاريخ
     */
    private function formatDateValue(?string $dateValue): string
    {
        if (!$dateValue) return '--';
        
        try {
            return \Carbon\Carbon::parse($dateValue)->format('Y/m/d');
        } catch (\Exception $e) {
            return $dateValue;
        }
    }

    /**
     * تنسيق قيم JSON
     */
    private function formatJsonValue(?string $jsonValue): string
    {
        if (!$jsonValue) return '--';
        
        try {
            $data = json_decode($jsonValue, true);
            
            if ($this->field_name === 'other_expenses') {
                return $this->formatOtherExpenses($data);
            } elseif ($this->field_name === 'expenses_currencies') {
                return $this->formatCurrencies($data);
            }
            
            return $jsonValue;
        } catch (\Exception $e) {
            return $jsonValue;
        }
    }

    /**
     * تنسيق المصاريف الإضافية للعرض
     */
    private function formatOtherExpenses(array $expenses): string
    {
        if (empty($expenses)) return 'لا توجد مصاريف إضافية';
        
        $formatted = [];
        foreach ($expenses as $expense) {
            $name = $expense['name'] ?? 'غير محدد';
            $amount = number_format($expense['amount'] ?? 0, 2);
            $currency = match($expense['currency'] ?? 'SAR') {
                'SAR' => 'ريال',
                'KWD' => 'دينار',
                default => $expense['currency'] ?? 'SAR'
            };
            
            $formatted[] = "{$name}: {$amount} {$currency}";
        }
        
        return implode(' | ', $formatted);
    }

    /**
     * تنسيق العملات للعرض
     */
    private function formatCurrencies(array $currencies): string
    {
        if (empty($currencies)) return 'لا توجد عملات محددة';
        
        $formatted = [];
        foreach ($currencies as $expenseType => $currency) {
            $expenseLabel = match($expenseType) {
                'salaries' => 'الرواتب',
                'advertising' => 'الإعلانات',
                'rent' => 'الإيجار',
                'staff_commissions' => 'العمولات',
                default => $expenseType
            };
            
            $currencyLabel = match($currency) {
                'SAR' => 'ريال',
                'KWD' => 'دينار',
                default => $currency
            };
            
            $formatted[] = "{$expenseLabel}: {$currencyLabel}";
        }
        
        return implode(' | ', $formatted);
    }

    /**
     * الحصول على رمز العملة
     */
    private function getCurrencySymbol(): string
    {
        return match ($this->currency) {
            'SAR' => 'ريال',
            'KWD' => 'دينار',
            'USD' => 'دولار',
            'EUR' => 'يورو',
            default => $this->currency ?? '',
        };
    }

    /**
     * تحديد لون التغيير حسب نوع العملية
     */
    public function getChangeColorAttribute(): string
    {
        return match ($this->action_type) {
            'created' => 'success',   // أخضر للإنشاء
            'updated' => 'warning',   // أصفر للتعديل
            'deleted' => 'danger',    // أحمر للحذف
            default => 'secondary',   // رمادي للافتراضي
        };
    }

    /**
     * تحديد أيقونة التغيير حسب نوع العملية
     */
    public function getChangeIconAttribute(): string
    {
        return match ($this->action_type) {
            'created' => 'fas fa-plus-circle',
            'updated' => 'fas fa-edit',
            'deleted' => 'fas fa-trash-alt',
            default => 'fas fa-question-circle',
        };
    }

    /**
     * تحديد وصف أيقونة مفصل
     */
    public function getChangeDescriptionAttribute(): string
    {
        return match ($this->action_type) {
            'created' => 'تم إنشاء هذا العنصر',
            'updated' => 'تم تعديل هذا العنصر',
            'deleted' => 'تم حذف هذا العنصر',
            default => 'عملية غير محددة',
        };
    }

    /**
     * البحث بالمصروف الشهري
     */
    public function scopeForExpense($query, $expenseId)
    {
        return $query->where('monthly_expense_id', $expenseId);
    }

    /**
     * البحث بالمستخدم
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * البحث بنوع العملية
     */
    public function scopeByAction($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * البحث بفترة زمنية
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * البحث بحقل معين
     */
    public function scopeForField($query, $fieldName)
    {
        return $query->where('field_name', $fieldName);
    }

    /**
     * ترتيب بالأحدث أولاً
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * ترتيب بالأقدم أولاً
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * الحصول على ملخص التغيير
     */
    public function getChangeSummaryAttribute(): string
    {
        $action = $this->action_type_display;
        $field = $this->field_label;
        
        return match ($this->action_type) {
            'created' => "تم إنشاء {$field}",
            'updated' => "تم تعديل {$field}",
            'deleted' => "تم حذف {$field}",
            default => "عملية على {$field}",
        };
    }

    /**
     * التحقق من أن السجل تم بواسطة المستخدم الحالي
     */
    public function isByCurrentUser(): bool
    {
        return $this->user_id === Auth::id();
    }

    /**
     * التحقق من أن السجل حديث (خلال آخر ساعة)
     */
    public function isRecent(): bool
    {
        return $this->created_at->diffInHours(now()) <= 1;
    }
    
}