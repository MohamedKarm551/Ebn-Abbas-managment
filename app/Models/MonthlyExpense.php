<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;


class MonthlyExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'month_year',
        'salaries',
        'advertising',
        'rent',
        'staff_commissions',
        'other_expenses',

        'total_monthly_profit_SAR',
        'total_monthly_profit_KWD',

        'net_profit_SAR',
        'net_profit_KWD',

        'ismail_share_SAR',
        'ismail_share_KWD',

        'mohamed_share_SAR',
        'mohamed_share_KWD',
        'expenses_currencies',
        'unified_currency',
        'exchange_rate',
        'start_date',
        'end_date',
        'notes'
    ];

    protected $casts = [
        'other_expenses' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'expenses_currencies' => 'array',  

    ];

    // حساب إجمالي المصاريف
    public function getTotalExpensesAttribute()
    {
        $basicExpenses = $this->salaries + $this->advertising + $this->rent + $this->staff_commissions;
        
        $otherExpensesTotal = 0;
        if ($this->other_expenses && is_array($this->other_expenses)) {
            foreach ($this->other_expenses as $expense) {
                $otherExpensesTotal += floatval($expense['amount'] ?? 0);
            }
        }

        return $basicExpenses + $otherExpensesTotal;
    }

    /**
     * علاقة مع جدول سجلات التعديلات
     * كل مصروف شهري يمكن أن يحتوي على عدة سجلات تعديل
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MonthlyExpenseLog::class, 'monthly_expense_id')
                    ->latest(); // ترتيب بالأحدث أولاً
    }

    /**
     * الحصول على آخر تعديل
     */
    public function getLastEditAttribute()
    {
        return $this->logs()
                    ->where('action_type', 'updated')
                    ->with('user')
                    ->first();
    }

    /**
     * عدد مرات التعديل
     */
    public function getEditCountAttribute(): int
    {
        return $this->logs()
                    ->where('action_type', 'updated')
                    ->count();
    }
    /**
     * ✅ الحصول على تاريخ الإنشاء
     */
    public function getCreationLogAttribute()
    {
        return $this->logs()
                    ->where('action_type', 'created')
                    ->with('user')
                    ->first();
    }

    /**
     * ✅ إجمالي عدد السجلات
     */
    public function getTotalLogsCountAttribute(): int
    {
        return $this->logs()->count();
    }

    /**
     * ✅ التحقق من وجود تعديلات
     */
    public function hasLogs(): bool
    {
        return $this->logs()->exists();
    }

    /**
     * ✅ الحصول على المستخدمين الذين عدلوا السجل
     */
    public function getEditorsAttribute()
    {
        return $this->logs()
                    ->with('user')
                    ->get()
                    ->pluck('user')
                    ->unique('id')
                    ->values();
    }

    /**
     * ✅ تحديد لون حالة السجل حسب آخر تعديل
     */
    public function getStatusColorAttribute(): string
    {
        $lastLog = $this->logs()->first();
        
        if (!$lastLog) {
            return 'secondary'; // رمادي إذا لم يكن هناك سجلات
        }

        return match ($lastLog->action_type) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * ✅ الحصول على آخر نشاط
     */
    public function getLastActivityAttribute(): ?string
    {
        $lastLog = $this->logs()->first();
        
        if (!$lastLog) {
            return null;
        }

        return "تم {$lastLog->action_type_display} بواسطة {$lastLog->user->name} في {$lastLog->created_at->diffForHumans()}";
    }
}
