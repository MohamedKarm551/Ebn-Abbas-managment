<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Booking;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    // ====================================
    // لوحة المحاسب — القيود المعلقة
    // ====================================
    public function pending()
    {
        $entries = JournalEntry::with(['lines.account', 'creator'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('journal.pending', compact('entries'));
    }

    // ====================================
    // اعتماد قيد
    // ====================================
    public function approve(JournalEntry $entry)
    {
        if ($entry->status !== 'draft') {
            return back()->with('error', 'القيد ليس في حالة انتظار');
        }

        $oldData = AccountingService::captureEntryData($entry);
        AccountingService::postEntry($entry);
        $entry->refresh();
        $newData = AccountingService::captureEntryData($entry);

        AccountingService::logEdit(
            $entry->id,
            'approve',
            $oldData,
            $newData,
            'تم اعتماد القيد وترحيله'
        );

        return back()->with('success', '✅ تم اعتماد القيد وترحيله للشجرة');
    }

    // ====================================
    // إلغاء قيد
    // ====================================
    public function cancel(JournalEntry $entry, Request $request)
    {
        if ($entry->status !== 'draft') {
            return back()->with('error', 'لا يمكن إلغاء قيد معتمد');
        }

        DB::transaction(function () use ($entry, $request) {
            // لو القيد مصدره حجز → احذف الحجز وافرد مكانه
            if ($entry->source_type === Booking::class && $entry->source_id) {
                $booking = Booking::find($entry->source_id);
                if ($booking) {
                    $trip = $booking->trip;

                    // فك التسكين لو موجود
                    $booking->update(['room_assignment_id' => null]);
                    $booking->discounts()->delete();
                    $booking->delete(); // Soft delete
                }

                if ($trip) {
                    $trip->available_seats = $trip->total_seats - $trip->bookings()->count();
                    $trip->save();
                }
            }

            $entry->lines()->delete();
            $entry->delete();
        });

        return back()->with('success', '🗑️ تم إلغاء القيد' .
            ($entry->source_type === Booking::class ? ' وحذف الحجز المرتبط' : ''));
    }

    // ====================================
    // قائمة القيود اليدوية
    // ====================================
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

    // ====================================
    // فورم قيد يدوي
    // ====================================
    public function create()
    {
        $accounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('journal.create', compact('accounts'));
    }

    // ====================================
    // حفظ قيد يدوي
    // ====================================
    public function store(Request $request)
    {
        $request->validate([
            'reference'  => 'required|string|unique:journal_entries,reference',
            'entry_date' => 'required|date',
            'status'     => 'required|in:draft,posted',
            'lines'      => 'required|array|min:2',
            'lines.*.account_id'  => 'required|exists:accounts,id',
            'lines.*.debit'       => 'nullable|numeric|min:0',
            'lines.*.credit'      => 'nullable|numeric|min:0',
            'lines.*.description' => 'required|string',
        ]);

        // التحقق من توازن القيد
        $totalDebit  = collect($request->lines)->sum(fn($l) => (float)($l['debit'] ?? 0));
        $totalCredit = collect($request->lines)->sum(fn($l) => (float)($l['credit'] ?? 0));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()
                ->withErrors(['lines' => 'القيد غير متوازن — المدين لا يساوي الدائن']);
        }

        // التحقق من أن الحسابات غير مجمدة
        foreach ($request->lines as $line) {
            $acc = Account::find($line['account_id']);
            if ($acc && !$acc->is_active) {
                return back()->withInput()
                    ->withErrors(['lines' => "الحساب [{$acc->code}] {$acc->name} مجمد"]);
            }
        }

        DB::transaction(function () use ($request) {
            $entry = JournalEntry::create([
                'reference'   => $request->reference,
                'entry_date'  => $request->entry_date,
                'status'      => 'draft', // دائماً draft أولاً
                'source_type' => null,    // يدوي
                'source_id'   => null,
                'created_by'  => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => (float)($line['debit'] ?? 0),
                    'credit'           => (float)($line['credit'] ?? 0),
                    'description'      => $line['description'],
                ]);
            }

            // لو المستخدم اختار posted مباشرة — اعتمده
            if ($request->status === 'posted') {
                AccountingService::postEntry($entry);
            }
        });

        return redirect()->route('journal.index')
            ->with('success', '✅ تم حفظ القيد');
    }

    // ====================================
    // عكس قيد
    // ====================================
    public function reverse(JournalEntry $entry)
    {
        if ($entry->status !== 'posted') {
            return back()->with('error', 'يمكن عكس القيود المعتمدة فقط');
        }

        if (str_starts_with($entry->reference, 'REV-')) {
            return back()->with('error', 'لا يمكن عكس قيد عكسي مرة أخرى');
        }

        // تحقق مش عنده عكس قبل كده
        $alreadyReversed = JournalEntry::where('reference', 'REV-' . $entry->reference)
            ->exists();
        if ($alreadyReversed) {
            return back()->with('error', 'تم عكس هذا القيد مسبقاً');
        }

        DB::transaction(function () use ($entry) {
            // إنشاء قيد عكسي
            $reversal = JournalEntry::create([
                'reference'   => 'REV-' . $entry->reference,
                'entry_date'  => now()->toDateString(),
                'status'      => 'posted',
                'source_type' => null,
                'source_id'   => null,
                'created_by'  => auth()->id(),
            ]);

            // اعكس كل الأسطر
            foreach ($entry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id'       => $line->account_id,
                    'debit'            => $line->credit, // ← مدين وداين معكوسين
                    'credit'           => $line->debit,
                    'description'      => 'عكس: ' . $line->description,
                ]);
            }

            $reversal->load('lines');
            $oldData = AccountingService::captureEntryData($entry); 
            $newData = AccountingService::captureEntryData($reversal); 

            AccountingService::logEdit(
                $reversal->id,
                'reverse',
                $oldData,
                $newData,
                "تم عكس القيد الأصلي #{$entry->id} (مرجع: {$entry->reference})"
            );

        });

        return back()->with('success', '🔄 تم إنشاء قيد عكسي');
    }

    public function history($id)
    {
        $entry = JournalEntry::with(['editLogs.user'])->findOrFail($id);
        return view('journal.history', compact('entry'));
    }


    /**
    * بحث AJAX في الحسابات النهائية (is_leaf)
    * GET /accounts/search?q=كلمة_البحث
    */
    public function searchAccounts(Request $request)
    {
        $q = trim($request->input('q', ''));
                    
        $accounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('code')
            ->limit(30)
            ->get(['id', 'code', 'name']);
                    
        return response()->json($accounts);
    }

}