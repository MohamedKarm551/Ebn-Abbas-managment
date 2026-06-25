<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['booking_id','amount','paid_at','notes', 'receipt_image','journal_entry_id'];

    public function booking() {
        return $this->belongsTo(Booking::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}