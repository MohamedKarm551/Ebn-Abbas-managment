<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountLedger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id','journal_entry_id',
        'debit','credit','running_balance','description'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function journalEntry() {
        return $this->belongsTo(JournalEntry::class);
    }
}