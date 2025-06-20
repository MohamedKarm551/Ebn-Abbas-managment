<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'transaction_date',
        'from_to',
        'amount',
        'currency',
        'type',
        'category',
        'link_or_image',
        'notes',
        'exchange_rate',
        'base_currency',
        'converted_amount'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'converted_amount' => 'decimal:2'
    ];

    // العلاقة مع المستخدم (الأدمن)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Accessor للحصول على رمز العملة
    public function getCurrencySymbolAttribute()
    {
        $symbols = [
            'SAR' => 'ر.س',
            'KWD' => 'د.ك',
            'EGP' => 'ج.م',
            'USD' => '$',
            'EUR' => '€'
        ];
        
        return $symbols[$this->currency] ?? $this->currency;
    }

    // Accessor للحصول على نوع العملية بالعربية
    public function getTypeArabicAttribute()
    {
        $types = [
            'deposit' => 'إيداع',
            'withdrawal' => 'سحب',
            'transfer' => 'تحويل',
            'other' => 'أخرى'
        ];
        
        return $types[$this->type] ?? $this->type;
    }

    // دالة لحساب المجموع حسب العملة لفترة معينة
    public static function getTotalsByCurrency($adminId, $startDate = null, $endDate = null)
    {
        $query = self::where('admin_id', $adminId);
        
        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }
        
        return $query->selectRaw('
        currency,
        SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) as total_deposits,
        SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END) as total_withdrawals,
        SUM(CASE WHEN type = "transfer" THEN amount ELSE 0 END) as total_transfers,
        SUM(CASE 
            WHEN type = "deposit" THEN amount 
            WHEN type = "withdrawal" THEN -amount 
            WHEN type = "transfer" THEN -amount 
            ELSE 0 
        END) as net_balance
    ')
    ->groupBy('currency')
    ->get();
    // يعني أن هذه الدالة تقوم بحساب المجموعات حسب العملة لفترة معينة، حيث يتم جمع المبالغ حسب نوع العملية (إيداع، سحب، تحويل) وتقديم الرصيد الصافي.
    }

    // Scope للفلترة حسب النوع
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope للفلترة حسب العملة
    public function scopeOfCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    // Scope للفلترة حسب الفترة الزمنية
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
