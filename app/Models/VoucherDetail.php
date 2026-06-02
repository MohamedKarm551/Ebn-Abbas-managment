<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherDetail extends Model
{
    protected $fillable = [
        'journal_entry_id','voucher_type',
        'debit_account_id','credit_account_id',
        'amount','subject','description',
        'payment_method','cheque_number','cheque_date',
        'sig_receiver','sig_accountant','sig_manager',
    ];

    protected $casts = ['cheque_date' => 'date'];

    public function journalEntry()  { return $this->belongsTo(JournalEntry::class); }
    public function debitAccount()  { return $this->belongsTo(Account::class, 'debit_account_id'); }
    public function creditAccount() { return $this->belongsTo(Account::class, 'credit_account_id'); }
}