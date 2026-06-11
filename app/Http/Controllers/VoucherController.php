<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\JournalEditLog;
use App\Models\VoucherDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherController extends Controller
{
    // ══════════════════════════════════════════
    // صفحة ايصال استلام
    // ══════════════════════════════════════════
    public function receipt()
    {
        $debitAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->where('type', 'asset')
            ->whereIn('code', ['1.1.1', '1.1.2'])
            ->orWhere(function ($q) {
                $q->where('is_leaf', true)->where('is_active', true)
                  ->where('type', 'asset')->where('code', 'like', '1.1.2%');
            })
            ->orderBy('code')->get();

        if ($debitAccounts->isEmpty()) {
            $debitAccounts = Account::where('is_leaf', true)
                ->where('is_active', true)->where('type', 'asset')
                ->orderBy('code')->get();
        }

        $creditAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)->orderBy('code')->get();

        $lastEntry = JournalEntry::where('reference', 'like', 'RCV-%')
            ->orderByDesc('id')->first();
        $nextRef = 'RCV-' . str_pad(
            ($lastEntry ? (int)substr($lastEntry->reference, 4) + 1 : 1),
            5, '0', STR_PAD_LEFT
        );

        return view('vouchers.receipt', compact('debitAccounts', 'creditAccounts', 'nextRef'));
    }

    // ══════════════════════════════════════════
    // صفحة ايصال صرف
    // ══════════════════════════════════════════
    public function payment()
    {
        $creditAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)->where('type', 'asset')
            ->orderBy('code')->get();

        if ($creditAccounts->isEmpty()) {
            $creditAccounts = Account::where('is_leaf', true)
                ->where('is_active', true)->where('type', 'asset')
                ->orderBy('code')->get();
        }

        $debitAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)->orderBy('code')->get();

        $lastEntry = JournalEntry::where('reference', 'like', 'PAY-%')
            ->where('reference', 'not like', 'PAY-BK-%')
            ->where('reference', 'not like', 'PAY-CO-%')
            ->where('reference', 'not like', 'PAY-AG-%')
            ->orderByDesc('id')->first();
        $nextRef = 'PAY-' . str_pad(
            ($lastEntry ? (int)substr($lastEntry->reference, 4) + 1 : 1),
            5, '0', STR_PAD_LEFT
        );

        return view('vouchers.payment', compact('debitAccounts', 'creditAccounts', 'nextRef'));
    }

    // ══════════════════════════════════════════
    // حفظ قيد جديد
    // ══════════════════════════════════════════
    public function save(Request $request)
    {
         try {
            $request->validate([
                'voucher_type'      => 'required|in:receipt,payment',
                'entry_date'        => 'required|date',
                'amount'            => 'required|numeric|min:0.01',
                'debit_account_id'  => 'required|exists:accounts,id',
                'credit_account_id' => 'required|exists:accounts,id',
                'subject'           => 'nullable|string|max:500',
                'sig_receiver'      => 'nullable|string|max:100',
                'sig_accountant'    => 'nullable|string|max:100',
                'sig_manager'       => 'nullable|string|max:100',
                'reference'         => 'required|string|unique:journal_entries,reference',
                'booking_id'        => 'nullable|exists:bookings,id',
            ], [
                'reference.unique' => 'الرقم المرجعي :input موجود بالفعل. يرجى تحديث الصفحة والمحاولة مرة أخرى.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = $e->errors()['reference'][0] ?? 'الرقم المرجعي موجود بالفعل.';
            return response()->json(['success' => false, 'message' => $message], 422);
        }
        DB::transaction(function () use ($request) {
            $entry = JournalEntry::create([
                'reference'   => $request->reference,
                'entry_date'  => $request->entry_date,
                'status'      => 'posted',
                'source_type' => $request->voucher_type,
                'source_id'   => null,
                'created_by'  => Auth::id(),
            ]);

            $debitAcc  = Account::findOrFail($request->debit_account_id);
            $creditAcc = Account::findOrFail($request->credit_account_id);
            $amount    = (float) $request->amount;
            $desc      = $request->subject
                ?: ($request->voucher_type === 'receipt'
                    ? "ايصال استلام - {$creditAcc->name}"
                    : "ايصال صرف - {$debitAcc->name}");

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $debitAcc->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => $desc,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $creditAcc->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => $desc,
            ]);

            $debitAcc->debit($amount, $desc, $entry->id);
            $creditAcc->credit($amount, $desc, $entry->id);

            VoucherDetail::create([
                'journal_entry_id'  => $entry->id,
                'voucher_type'      => $request->voucher_type,
                'debit_account_id'  => $debitAcc->id,
                'credit_account_id' => $creditAcc->id,
                'amount'            => $amount,
                'subject'           => $request->subject,
                'description'       => $desc,
                'payment_method'    => $request->payment_method ?? 'cash',
                'cheque_number'     => $request->cheque_number,
                'cheque_date'       => $request->cheque_date,
                'sig_receiver'      => $request->sig_receiver,
                'sig_accountant'    => $request->sig_accountant,
                'sig_manager'       => $request->sig_manager,
            ]);

        });

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════
    // عرض الإيصال للتعديل (نفس الـ view)
    // ══════════════════════════════════════════
    public function showVoucher(JournalEntry $entry)
    {
        abort_unless(in_array($entry->source_type, ['receipt','payment']), 404);

        $detail = $entry->voucherDetail()
            ->with(['debitAccount','creditAccount'])
            ->firstOrFail();

        $debitAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)->where('type', 'asset')
            ->orderBy('code')->get();

        if ($debitAccounts->isEmpty()) {
            $debitAccounts = Account::where('is_leaf', true)
                ->where('is_active', true)->orderBy('code')->get();
        }

        $creditAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)->orderBy('code')->get();

        $nextRef = $entry->reference;
        $isEdit  = true;

        $view = $entry->source_type === 'receipt'
            ? 'vouchers.receipt'
            : 'vouchers.payment';

        return view($view, compact(
            'debitAccounts', 'creditAccounts',
            'nextRef', 'detail', 'entry', 'isEdit'
        ));
    }

   public function updateVoucher(Request $request, JournalEntry $entry)
{
    abort_unless(in_array($entry->source_type, ['receipt','payment']), 403);

    $request->validate([
        'entry_date'        => 'required|date',
        'amount'            => 'required|numeric|min:0.01',
        'debit_account_id'  => 'required|exists:accounts,id',
        'credit_account_id' => 'required|exists:accounts,id',
        'payment_method'    => 'nullable|in:cash,cheque',
        'cheque_number'     => 'nullable|string|max:100',
        'cheque_date'       => 'nullable|date',
        'subject'           => 'nullable|string|max:500',
        'sig_receiver'      => 'nullable|string|max:100',
        'sig_accountant'    => 'nullable|string|max:100',
        'sig_manager'       => 'nullable|string|max:100',
    ]);

    DB::transaction(function () use ($request, $entry) {

        $entry->load('lines');
        $detail = $entry->voucherDetail()->firstOrFail();

        $newAmount       = (float) $request->amount;
        $oldAmount       = (float) $detail->amount;
        $newDebitId      = (int) $request->debit_account_id;
        $newCreditId     = (int) $request->credit_account_id;
        $oldDebitId      = (int) $detail->debit_account_id;
        $oldCreditId     = (int) $detail->credit_account_id;
        $newEntryDate    = $request->entry_date;
        $oldEntryDate    = $entry->entry_date->toDateString();

        // ─── هل في تغيير يمس القيد المحاسبي؟ ───
        $journalChanged =
            $newAmount    !== $oldAmount    ||
            $newDebitId   !== $oldDebitId   ||
            $newCreditId  !== $oldCreditId  ||
            $newEntryDate !== $oldEntryDate;

        if (!$journalChanged) {
            // ══ تغيير في البيانات الإضافية فقط ══

            $oldSnapshot = [
                'subject'        => $detail->subject,
                'description'    => $detail->description,
                'payment_method' => $detail->payment_method,
                'cheque_number'  => $detail->cheque_number,
                'cheque_date'    => $detail->cheque_date,
                'sig_receiver'   => $detail->sig_receiver,
                'sig_accountant' => $detail->sig_accountant,
                'sig_manager'    => $detail->sig_manager,
            ];

            $detail->update([
                'subject'        => $request->subject,
                'description'    =>  $request->subject,
                'payment_method' => $request->payment_method ?? 'cash',
                'cheque_number'  => $request->cheque_number,
                'cheque_date'    => $request->cheque_date,
                'sig_receiver'   => $request->sig_receiver,
                'sig_accountant' => $request->sig_accountant,
                'sig_manager'    => $request->sig_manager,
            ]);
            
            $entry->lines()->update(['description' => $request->subject]);
            AccountLedger::where('journal_entry_id', $entry->id)
                ->update(['description' => $request->subject]);

            $newSnapshot = [
                'subject'        => $request->subject,
                'description'    =>  $request->subject,
                'payment_method' => $request->payment_method ?? 'cash',
                'cheque_number'  => $request->cheque_number,
                'cheque_date'    => $request->cheque_date,
                'sig_receiver'   => $request->sig_receiver,
                'sig_accountant' => $request->sig_accountant,
                'sig_manager'    => $request->sig_manager,
            ];

            JournalEditLog::create([
                'journal_entry_id' => $entry->id,
                'user_id'          => Auth::id(),
                'action'           => 'edit',
                'old_data'         => json_encode($oldSnapshot, JSON_UNESCAPED_UNICODE),
                'new_data'         => json_encode($newSnapshot, JSON_UNESCAPED_UNICODE),
                'notes'            => "تعديل بيانات إضافية للإيصال {$entry->source_type} رقم {$entry->reference}",
            ]);

            Log::info("تعديل بيانات إضافية فقط — القيد #{$entry->id}");

            return; // ✅ خلاص، مش هنمس القيد
        }

        // ══ تغيير يمس القيد المحاسبي → نفس المنطق القديم ══

        $oldSnapshot = $this->entrySnapshot($entry);

        $debitAcc  = Account::findOrFail($newDebitId);
        $creditAcc = Account::findOrFail($newCreditId);
        $desc      = $request->subject
            ?: ($entry->source_type === 'receipt'
                ? "ايصال استلام - {$creditAcc->name}"
                : "ايصال صرف - {$debitAcc->name}");

        $newEntry = JournalEntry::create([
            'reference'   => $entry->reference,
            'entry_date'  => $newEntryDate,
            'status'      => 'posted',
            'source_type' => $entry->source_type,
            'source_id'   => null,
            'created_by'  => Auth::id(),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $newEntry->id,
            'account_id'       => $debitAcc->id,
            'debit'            => $newAmount,
            'credit'           => 0,
            'description'      => $desc,
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $newEntry->id,
            'account_id'       => $creditAcc->id,
            'debit'            => 0,
            'credit'           => $newAmount,
            'description'      => $desc,
        ]);

        $debitAcc->debit($newAmount, $desc, $newEntry->id);
        $creditAcc->credit($newAmount, $desc, $newEntry->id);

        JournalEditLog::where('journal_entry_id', $entry->id)
            ->update(['journal_entry_id' => $newEntry->id]);

        AccountLedger::where('journal_entry_id', $entry->id)->delete();
        $entry->lines()->delete();
        $entry->delete();

        VoucherDetail::updateOrCreate(
            ['journal_entry_id' => $newEntry->id],
            [
                'voucher_type'      => $newEntry->source_type,
                'debit_account_id'  => $debitAcc->id,
                'credit_account_id' => $creditAcc->id,
                'amount'            => $newAmount,
                'subject'           => $request->subject,
                'description'       => $desc,
                'payment_method'    => $request->payment_method ?? 'cash',
                'cheque_number'     => $request->cheque_number,
                'cheque_date'       => $request->cheque_date,
                'sig_receiver'      => $request->sig_receiver,
                'sig_accountant'    => $request->sig_accountant,
                'sig_manager'       => $request->sig_manager,
            ]
        );

        $newEntry->load('lines');
        JournalEditLog::create([
            'journal_entry_id' => $newEntry->id,
            'user_id'          => Auth::id(),
            'action'           => 'edit',
            'old_data'         => json_encode($oldSnapshot, JSON_UNESCAPED_UNICODE),
            'new_data'         => json_encode($this->entrySnapshot($newEntry), JSON_UNESCAPED_UNICODE),
            'notes'            => "تعديل إيصال {$newEntry->source_type} رقم {$newEntry->reference}",
        ]);

        Log::info("تعديل إيصال — القيد القديم #{$entry->id} → الجديد #{$newEntry->id}");
    });

    return response()->json(['success' => true]);
}

    // ══════════════════════════════════════════
    // Helper
    // ══════════════════════════════════════════
    private function entrySnapshot(JournalEntry $entry): array
    {
        return [
            'reference'  => $entry->reference,
            'entry_date' => $entry->entry_date->toDateString(),
            'status'     => $entry->status,
            'lines'      => $entry->lines->map(fn($l) => [
                'account_id'  => $l->account_id,
                'debit'       => $l->debit,
                'credit'      => $l->credit,
                'description' => $l->description,
            ])->toArray(),
        ];
    }
}