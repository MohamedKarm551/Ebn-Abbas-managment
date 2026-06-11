<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines.account', 'creator'])
            ->withSum('lines', 'debit')
            ->withSum('lines', 'credit')
            ->withCount('reversals');

        // تحديد نوع القيد المراد عرضه (افتراضي: يدوي)
        $type = $request->get('type', 'all');   // manual (يدوي), auto (غير يدوي), all (الكل)
    
        if ($type === 'manual') {
            $query->where(function ($q) {
                $q->where('source_type', 'manual')->orWhereNull('source_type');
            });
        } elseif ($type === 'auto') {
            $query->whereNotNull('source_type')
                  ->where('source_type', '!=', 'manual');
        }

        $searchBy = $request->search_by;
        $searchValue = $request->search_value;

        if ($searchBy && $searchValue) {
            switch ($searchBy) {
                case 'id':
                    $query->where('id', $searchValue);
                    break;
                case 'reference':
                    $query->where('reference', 'like', '%' . $searchValue . '%');
                    break;
                case 'status':
                    $statusValue = match ($searchValue) {
                        'معتمد' => 'posted',
                        'غير معتمد' => 'draft',
                        default => $searchValue,
                    };
                    $query->where('status', $statusValue);
                    break;
                case 'created_by':
                    $query->whereHas('creator', fn($q) => $q->where('name', 'like', "%{$searchValue}%"));
                    break;
                case 'created_at':
                    try {
                        $date = \Carbon\Carbon::createFromFormat('d/m/Y', $searchValue)->format('Y-m-d');
                        $query->whereDate('created_at', $date);
                    } catch (\Exception $e) {
                        $query->whereDate('created_at', $searchValue);
                    }
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }

        $entries = $query->latest()->paginate(20)->withQueryString();
        return view('journal.index', compact('entries'));
    }

    public function create()
    {
        $accounts = Account::where('is_leaf', true)->where('is_active', true)->orderBy('code')->get();
        return view('journal.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference'               => 'required|string|max:50',
            'entry_date'              => 'required|date',
            'lines'                   => 'required|array|min:2',
            'lines.*.account_id'      => 'required|exists:accounts,id',
            'lines.*.debit'           => 'nullable|numeric|min:0',
            'lines.*.credit'          => 'nullable|numeric|min:0',
            'lines.*.description'     => 'required|string|max:255',
        ]);

        $totalDebit = collect($request->lines)->sum(fn($l) => (float)($l['debit'] ?? 0));
        $totalCredit = collect($request->lines)->sum(fn($l) => (float)($l['credit'] ?? 0));
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withInput()->withErrors(['balance' => "القيد غير متوازن!"]);
        }

        DB::transaction(function () use ($request) {
            $entry = JournalEntry::create([
                'reference'   => $request->reference,
                'entry_date'  => $request->entry_date,
                'status'      => $request->status ?? 'draft',
                'source_type' => 'manual',
                'source_id'   => null,
                'created_by'  => Auth::id(),
            ]);

            // 1. إنشاء أسطر القيد في journal_entry_lines
            foreach ($request->lines as $line) {
                $debit  = (float)($line['debit'] ?? 0);
                $credit = (float)($line['credit'] ?? 0);
                if ($debit > 0 || $credit > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id'       => $line['account_id'],
                        'debit'            => $debit,
                        'credit'           => $credit,
                        'description'      => $line['description'],
                    ]);
                }
            }

            // 2. إذا كان القيد معتمداً، نضيف سجلات الـ Ledger
            if ($entry->status === 'posted') {
                foreach ($request->lines as $line) {
                    $debit  = (float)($line['debit'] ?? 0);
                    $credit = (float)($line['credit'] ?? 0);
                    if ($debit > 0 || $credit > 0) {
                        $account = Account::findOrFail($line['account_id']);
                        if ($debit > 0) {
                            $account->debit($debit, $line['description'], $entry->id);
                        } elseif ($credit > 0) {
                            $account->credit($credit, $line['description'], $entry->id);
                        }
                    }
                }
            }
        });

        return redirect()->route('journal.index')->with('success', 'تم حفظ القيد المحاسبي بنجاح ✅');
    }

    public function approve($id)
    {
        $entry = JournalEntry::with('lines')->findOrFail($id);
        if ($entry->status === 'posted') {
            return back()->with('warning', 'القيد معتمد بالفعل');
        }

        DB::transaction(function () use ($entry) {
            foreach ($entry->lines as $line) {
                $account = Account::find($line->account_id);
                if ($line->debit > 0) {
                    $account->debit($line->debit, $line->description, $entry->id);
                } elseif ($line->credit > 0) {
                    $account->credit($line->credit, $line->description, $entry->id);
                }
            }
            $entry->update(['status' => 'posted']);
            $entry->logEdit('approve', null, null, 'تم اعتماد القيد');
        });

        return back()->with('success', 'تم اعتماد القيد ✅');
    }

    public function reverse($id)
    {
    $originalEntry = JournalEntry::with('lines')->findOrFail($id);

    // منع عكس القيد إذا كان غير معتمد (draft)
    if ($originalEntry->status !== 'posted') {
        return back()->with('error', 'لا يمكن عكس قيد غير معتمد.');
    }

     if ($originalEntry->source_type !== 'manual' && !is_null($originalEntry->source_type)) {
    return back()->with('error', 'لا يمكن عكس القيود غير اليدوية.');
    }

    $existingReverse = JournalEntry::where('source_type', 'manual_reversal')
        ->where('source_id', $originalEntry->id)
        ->exists();

    if ($existingReverse) {
        return back()->with('error', 'لا يمكن عكس هذا القيد أكثر من مرة. تم إنشاء قيد عكسي له مسبقاً.');
    }

    $oldData = [
        'reference'  => $originalEntry->reference,
        'entry_date' => $originalEntry->entry_date,
        'lines'      => $originalEntry->lines->toArray(),
    ];

    $reverseReference = null;

    DB::transaction(function () use ($originalEntry, $oldData,&$reverseReference) {
        // إنشاء القيد العكسي
        $reverseReference = 'REV-' . $originalEntry->reference;
        $reverseEntry = JournalEntry::create([
            'reference'   => $reverseReference,
            'entry_date'  => now()->toDateString(),
            'status'      => 'posted',   // العكس يكون معتمداً فوراً
            'source_type' => 'manual_reversal',
            'source_id'   => $originalEntry->id, // ربط بالقيد الأصلي
            'created_by'  => Auth::id(),
        ]);

        // إنشاء أسطر القيد العكسي (تبديل المدين/الدائن)
        foreach ($originalEntry->lines as $line) {
            $newLine = JournalEntryLine::create([
                'journal_entry_id' => $reverseEntry->id,
                'account_id'       => $line->account_id,
                'debit'            => $line->credit, // swap
                'credit'           => $line->debit,  // swap
                'description'      => 'عكس القيد: ' . $line->description,
            ]);

            // تحديث Ledger لكل حساب (مدين/دائن حسب القيم الجديدة)
            $account = Account::findOrFail($line->account_id);
            if ($newLine->debit > 0) {
                $account->debit($newLine->debit, $newLine->description, $reverseEntry->id);
            } elseif ($newLine->credit > 0) {
                $account->credit($newLine->credit, $newLine->description, $reverseEntry->id);
            }
        }

        // تسجيل عملية العكس في edit logs
        $originalEntry->logEdit('reverse', $oldData, [
            'reverse_id' => $reverseEntry->id,
            'reverse_reference' => $reverseReference,
        ], 'تم عكس القيد');
    });

    return redirect()->route('journal.index')->with('success', 'تم عكس القيد بنجاح. تم إنشاء قيد عكسي: ' . ($reverseReference ?? 'غير معروف'));
    }


    public function history($id)
    {
        $entry = JournalEntry::with(['editLogs.user'])->findOrFail($id);
        return view('journal.history', compact('entry'));
    }
}