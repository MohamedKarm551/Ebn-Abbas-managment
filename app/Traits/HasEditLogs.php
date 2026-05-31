<?php
namespace App\Traits;

use App\Models\JournalEditLog;
use Illuminate\Support\Facades\Auth;

trait HasEditLogs
{
    public function editLogs()
    {
        return $this->hasMany(JournalEditLog::class, 'journal_entry_id');
    }
    
    public function logEdit(string $action, $oldData = null, $newData = null, $notes = null)
    {
        return JournalEditLog::create([
            'journal_entry_id' => $this->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'notes' => $notes,
        ]);
    }
    
    public function getEditHistoryAttribute()
    {
        return $this->editLogs()->with('user')->latest()->get();
    }
}