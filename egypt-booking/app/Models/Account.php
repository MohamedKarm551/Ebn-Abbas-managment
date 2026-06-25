<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code','name','type','normal_balance',
        'parent_id','level','is_leaf',
        'is_active','description'
    ];

    public function parent() {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function allChildren() {
        return $this->hasMany(Account::class, 'parent_id')
                    ->with('allChildren');
    }

    public function ledger() {
        return $this->hasMany(AccountLedger::class);
    }

    public function journalLines() {
        return $this->hasMany(JournalEntryLine::class);
    }

    // scope للحسابات الجذر
    public function scopeRoots($query) {
        return $query->whereNull('parent_id');
    }

    // الرصيد الإجمالي
    public function getTotalBalance(): float
    {
        if ($this->is_leaf) {
            $debit  = $this->journalLines()
                ->whereHas('journalEntry', fn($q) => $q->where('status','posted'))
                ->sum('debit');
            $credit = $this->journalLines()
                ->whereHas('journalEntry', fn($q) => $q->where('status','posted'))
                ->sum('credit');
            return $debit - $credit;
        }

        return $this->allChildren->sum(fn($child) => $child->getTotalBalance());
    }

    // تسجيل في دفتر الأستاذ
    public function debit(float $amount, string $desc, int $entryId): void
    {
        $lastBalance = $this->ledger()->latest()->value('running_balance') ?? 0;
        $this->ledger()->create([
            'journal_entry_id' => $entryId,
            'debit'            => $amount,
            'credit'           => 0,
            'running_balance'  => $lastBalance + $amount,
            'description'      => $desc,
        ]);
    }

    public function credit(float $amount, string $desc, int $entryId): void
    {
        $lastBalance = $this->ledger()->latest()->value('running_balance') ?? 0;
        $this->ledger()->create([
            'journal_entry_id' => $entryId,
            'debit'            => 0,
            'credit'           => $amount,
            'running_balance'  => $lastBalance - $amount,
            'description'      => $desc,
        ]);
    }
}