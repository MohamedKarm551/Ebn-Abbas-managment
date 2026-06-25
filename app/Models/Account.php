<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasLedger;

class Account extends Model
{
    use SoftDeletes,HasLedger;
    
    protected $fillable = [
        'code', 'name', 'type', 'normal_balance',
        'parent_id', 'level', 'is_leaf',
        'is_active', 'currency', 'description',
    ];

    protected $casts = [
        'is_leaf'   => 'boolean',
        'is_active' => 'boolean',
    ];

    // =====================
    // Relationships
    // =====================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('code');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    // =====================
    // Balance Calculation
    // =====================

    /**
     * الرصيد المباشر من القيود
     */
    public function getDirectBalance(): float
    {
        $lines = $this->journalLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(credit),0) as total_credit')
            ->first();

        $debit  = (float)($lines->total_debit  ?? 0);
        $credit = (float)($lines->total_credit ?? 0);

        return $this->normal_balance === 'debit'
            ? $debit - $credit
            : $credit - $debit;
    }

   
    public function getTotalBalance(): float
    {
        if ($this->is_leaf) {
            $row = $this->ledger()
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();
    
            $debit  = (float) ($row->total_debit  ?? 0);
            $credit = (float) ($row->total_credit ?? 0);
    
            return $this->normal_balance === 'debit'
                ? $debit - $credit
                : $credit - $debit;
        }
    
        $total = 0;
        foreach ($this->allChildren as $child) {
            $total += $child->getTotalBalance();
        }
        return $total;
    }

    public function getBalanceAttribute(): float
    {
        return $this->getTotalBalance();
    }

    // =====================
    // Scopes
    // =====================

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =====================
    // Helpers
    // =====================

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'asset'     => 'أصول',
            'liability' => 'خصوم',
            'equity'    => 'حقوق ملكية',
            'revenue'   => 'إيرادات',
            'expense'   => 'مصروفات',
            default     => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'asset'     => '#f59e0b',
            'liability' => '#ef4444',
            'equity'    => '#8b5cf6',
            'revenue'   => '#10b981',
            'expense'   => '#3b82f6',
            default     => '#6b7280',
        };
    }


    public function ledger()
    {
        return $this->hasMany(AccountLedger::class);
    }

    // =====================
    // Boot
    // =====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Account $account) {
            // تحديد normal_balance تلقائياً
            if (empty($account->normal_balance)) {
                $account->normal_balance = in_array($account->type, ['asset', 'expense'])
                    ? 'debit'
                    : 'credit';
            }
            // تحديد المستوى
            if ($account->parent_id) {
                $parent = Account::find($account->parent_id);
                $account->level = $parent ? $parent->level + 1 : 1;
            }
        });
    }
}
