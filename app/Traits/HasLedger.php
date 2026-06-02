<?php
namespace App\Traits;

use App\Models\AccountLedger;
use Illuminate\Support\Facades\DB;

trait HasLedger {
    public function ledger() {
        return $this->hasMany(AccountLedger::class);
    }

    public function getCurrentBalanceAttribute() {
        return $this->ledger()->latest()->value('running_balance') ?? 0;
    }

    // قيود مدين (Debit): تزيد رصيد الحسابات المدينة (أصول، مصروفات)
    public function debit(float $amount, string $description, $journalEntryId) {
        if ($amount <= 0) return false;
        $lastBalance = $this->current_balance;
        $newBalance = $lastBalance + $amount; // إضافة المبلغ للرصيد

        return $this->ledger()->create([
            'journal_entry_id' => $journalEntryId,
            'debit' => $amount,
            'credit' => 0,
            'running_balance' => $newBalance,
            'description' => $description,
        ]);
    }

    // قيود دائن (Credit): تزيد رصيد الحسابات الدائنة (خصوم، إيرادات، حقوق ملكية)
    public function credit(float $amount, string $description, $journalEntryId) {
        if ($amount <= 0) return false;
        $lastBalance = $this->current_balance;
        $newBalance = $lastBalance - $amount; // طرح المبلغ من الرصيد

        return $this->ledger()->create([
            'journal_entry_id' => $journalEntryId,
            'debit' => 0,
            'credit' => $amount,
            'running_balance' => $newBalance,
            'description' => $description,
        ]);
    }
}