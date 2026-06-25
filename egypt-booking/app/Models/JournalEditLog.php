<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class JournalEditLog extends Model
{
    use HasFactory;

    protected $table = 'journal_edit_logs';
    
    protected $fillable = [
        'journal_entry_id', 'user_id', 'action', 'old_data', 'new_data', 'notes'
    ];
    
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];
    
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}