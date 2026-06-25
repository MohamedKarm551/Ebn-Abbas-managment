<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference','entry_date','status',
        'source_type','source_id','created_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function lines() {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function editLogs()
    {
        return $this->hasMany(JournalEditLog::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

     public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function reversals()
    {
        return $this->hasMany(JournalEntry::class, 'source_id', 'id')
                    ->where('source_type', 'manual_reversal');
    }

    public function isBalanced(): bool
    {
        $totals = $this->lines()
            ->selectRaw('SUM(debit) as d, SUM(credit) as c')
            ->first();
        return round((float)$totals->d, 2) === round((float)$totals->c, 2);
    }

    public function voucherDetail()
    {
        return $this->hasOne(\App\Models\VoucherDetail::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($entry) {
            if (empty($entry->reference)) {
                $last = static::withTrashed()->max('id') ?? 0;
                $entry->reference = 'JE-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}