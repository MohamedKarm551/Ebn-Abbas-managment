<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\JournalEditLog;
use App\Models\VoucherDetail;
use App\Models\Payment;
use App\Models\Booking;
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
            ->orderByRaw('CAST(SUBSTRING(reference, 5) AS UNSIGNED) DESC')
            ->first();
        $lastNum = $lastEntry ? (int) substr($lastEntry->reference, 4) : 0;
        $nextRef = 'RCV-' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

        $bookings = \App\Models\Booking::with('trip')
            ->orderBy('client_name')
            ->get();

        return view('vouchers.receipt', compact('debitAccounts', 'creditAccounts', 'nextRef', 'bookings'));
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
            ->orderByRaw('CAST(SUBSTRING(reference, 5) AS UNSIGNED) DESC')
            ->first();
        $lastNum = $lastEntry ? (int) substr($lastEntry->reference, 4) : 0;
        $nextRef = 'PAY-' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        $bookings = \App\Models\Booking::with('trip')
            ->whereHas('payments') 
            ->orderBy('client_name')
            ->get();
        return view('vouchers.payment', compact('debitAccounts', 'creditAccounts', 'nextRef', 'bookings'));
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
                'cheque_date'       => 'nullable|date',
                'booking_id'        => 'nullable|exists:bookings,id',
                'reference'         => 'required|string|unique:journal_entries,reference',
            ], [
                'reference.unique' => 'الرقم المرجعي :input موجود بالفعل. يرجى تحديث الصفحة والمحاولة مرة أخرى.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = $e->errors()['reference'][0] ?? 'الرقم المرجعي موجود بالفعل.';
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        if (str_starts_with($request->reference, 'PAY-')) {
            $request->merge(['voucher_type' => 'payment']);
        } elseif (str_starts_with($request->reference, 'RCV-')) {
            $request->merge(['voucher_type' => 'receipt']);
        }

        DB::transaction(function () use ($request) {
            \Log::info('Voucher save request', $request->all());
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
                'sig_receiver'      => $request->sig_receiver,
                'sig_accountant'    => $request->sig_accountant,
                'sig_manager'       => $request->sig_manager,
                'cheque_date' => $request->cheque_date,
                'booking_id'        => $request->booking_id ?? null,
            ]);

            // ══════════════════════════════════════════
            // لو في حجز مرتبط → سجّل دفعة تلقائياً
            // ══════════════════════════════════════════
            $bookingId = $request->input('booking_id');
            if ($bookingId && is_numeric($bookingId) && $bookingId > 0) {
                Payment::create([
                    'booking_id' => $request->booking_id,
                    'amount'     => $amount,
                    'paid_at'    => $request->entry_date,
                    'notes'      => $request->subject,
                    'journal_entry_id'  => $entry->id,
                ]);
            }

        });

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════
    // عرض الإيصال للتعديل
    // ══════════════════════════════════════════
    public function showVoucher(JournalEntry $entry)
    {
        abort_unless(
            in_array($entry->source_type, ['receipt', 'payment', \App\Models\Payment::class]),
            404
        );

        $detail = $entry->voucherDetail()
            ->with(['debitAccount', 'creditAccount'])
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

        $view = (in_array($entry->source_type, ['receipt', \App\Models\Payment::class]))
            ? 'vouchers.receipt'
            : 'vouchers.payment';

        return view($view, compact(
            'debitAccounts', 'creditAccounts',
            'nextRef', 'detail', 'entry', 'isEdit'
        ));
    }

    // ══════════════════════════════════════════
    // تحديث القيد
    // ══════════════════════════════════════════
    public function updateVoucher(Request $request, JournalEntry $entry)
    {

        abort_unless(
            in_array($entry->source_type, ['receipt', 'payment', \App\Models\Payment::class]),
            404
        );

        $request->validate([
            'entry_date'        => 'required|date',
            'amount'            => 'required|numeric|min:0.01',
            'debit_account_id'  => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'subject'           => 'nullable|string|max:500',
            'sig_receiver'      => 'nullable|string|max:100',
            'sig_accountant'    => 'nullable|string|max:100',
            'sig_manager'       => 'nullable|string|max:100',
            'cheque_date'       => 'nullable|date',
            'booking_id'        => 'nullable|exists:bookings,id',
        ]);

        try {
        DB::transaction(function () use ($request, $entry) {

            $entry->load('lines');
            $detail = $entry->voucherDetail()->firstOrFail();

            $newAmount    = (float) $request->amount;
            $oldAmount    = (float) $detail->amount;
            $newDebitId   = (int) $request->debit_account_id;
            $newCreditId  = (int) $request->credit_account_id;
            $oldDebitId   = (int) $detail->debit_account_id;
            $oldCreditId  = (int) $detail->credit_account_id;
            $newEntryDate = $request->entry_date;
            $oldEntryDate = $entry->entry_date->toDateString();
            $oldBookingId = $detail->booking_id;
            if (empty($oldBookingId)) {
                $payment = Payment::where('journal_entry_id', $entry->id)->first();
                $oldBookingId = $payment->booking_id ?? null;
            }
            $newBookingId = $request->booking_id ?? $oldBookingId;

            $journalChanged =
                $newAmount    !== $oldAmount    ||
                $newDebitId   !== $oldDebitId   ||
                $newCreditId  !== $oldCreditId  ||
                $newEntryDate !== $oldEntryDate;

            $entryId = $entry->id;

            $oldPayment = Payment::where('journal_entry_id', $entry->id)->first();
            $oldReceiptImage = $oldPayment ? $oldPayment->receipt_image : null;
            // ══════════════════════════════════════════
            // Helper: حذف الـ Payment القديم المرتبط بالإيصال
            // ══════════════════════════════════════════
            $deleteOldPayment = function () use ($entryId) {
                Payment::where('journal_entry_id', $entryId)->delete();
            };

            // ══════════════════════════════════════════
            // Helper: إنشاء Payment جديد
            // ══════════════════════════════════════════
            $createNewPayment = function ($receiptImage = null) use ($request, $newBookingId, $newAmount, $entry) {
                if ($newBookingId) {
                    Payment::create([
                        'booking_id' => $newBookingId,
                        'amount'     => $newAmount,
                        'paid_at'    => $request->entry_date,
                        'notes'      => $request->subject,
                        'journal_entry_id'  => $entry->id,
                        'receipt_image' => $receiptImage, 
                    ]);
                }
            };

        
            if (!$journalChanged) {
                // تغيير في البيانات الإضافية فقط
                $oldSnapshot = [
                    'subject'        => $detail->subject,
                    'sig_receiver'   => $detail->sig_receiver,
                    'sig_accountant' => $detail->sig_accountant,
                    'sig_manager'    => $detail->sig_manager,
                    'cheque_date' => $detail->cheque_date,
                    'booking_id'     => $oldBookingId,
                ];

                $detail->update([
                    'subject'        => $request->subject,
                    'sig_receiver'   => $request->sig_receiver,
                    'sig_accountant' => $request->sig_accountant,
                    'sig_manager'    => $request->sig_manager,
                    'cheque_date' => $request->cheque_date,
                    'booking_id'     => $newBookingId, 
                ]);

                $entry->lines()->update(['description' => $request->subject]);
                AccountLedger::where('journal_entry_id', $entry->id)
                    ->update(['description' => $request->subject]);

                if ($oldBookingId !== $newBookingId) {
                    $deleteOldPayment();
                    $createNewPayment($oldReceiptImage);
                }

                $newSnapshot = [
                    'subject'        => $request->subject,
                    'sig_receiver'   => $request->sig_receiver,
                    'sig_accountant' => $request->sig_accountant,
                    'sig_manager'    => $request->sig_manager,
                    'cheque_date' => $request->cheque_date,
                    'booking_id'     => $newBookingId,
                ];

                JournalEditLog::create([
                    'journal_entry_id' => $entry->id,
                    'user_id'          => Auth::id(),
                    'action'           => 'edit',
                    'old_data'         => json_encode($oldSnapshot, JSON_UNESCAPED_UNICODE),
                    'new_data'         => json_encode($newSnapshot, JSON_UNESCAPED_UNICODE),
                    'notes'            => "تعديل بيانات إضافية للإيصال {$entry->source_type} رقم {$entry->reference}",
                ]);

                return;
                }

                // تغيير يمس القيد → ننشئ قيداً جديداً ونحذف القديم
                $oldSnapshot = $this->entrySnapshot($entry);

                $deleteOldPayment();

                $debitAcc  = Account::findOrFail($newDebitId);
                $creditAcc = Account::findOrFail($newCreditId);
                $desc      = $request->subject
                    ?: (in_array($entry->source_type, ['receipt', \App\Models\Payment::class])
                        ? "ايصال استلام - {$creditAcc->name}"
                        : "ايصال صرف - {$debitAcc->name}");

                $newEntry = JournalEntry::create([
                    'reference'   => $entry->reference,
                    'entry_date'  => $newEntryDate,
                    'status'      => 'posted',
                    'source_type' => $entry->source_type,
                    'source_id'   => $entry->source_id ?? null,
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
                        'sig_receiver'      => $request->sig_receiver,
                        'sig_accountant'    => $request->sig_accountant,
                        'sig_manager'       => $request->sig_manager,
                        'cheque_date' => $request->cheque_date,
                        'booking_id'        => $newBookingId,
                    ]
                );

                if ($newBookingId) {
                    Payment::create([
                        'booking_id' => $newBookingId,
                        'amount'     => $newAmount,
                        'paid_at'    => $request->entry_date,
                        'notes'      => $request->subject,
                        'journal_entry_id'  => $newEntry->id,
                        'receipt_image' => $oldReceiptImage,
                    ]);
                }

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
            } catch (\Exception $e) {
            Log::error('updateVoucher error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() . ' في السطر ' . $e->getLine()
            ], 500);
        }
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

    public function getBookingByAccount(Account $account)
    {
        $booking = Booking::where('account_id', $account->id)
            ->orderBy('id', 'desc')->first();
        if ($booking) {
            return response()->json([
                'success' => true,
                'booking' => [
                    'id'          => $booking->id,
                    'client_name' => $booking->client_name,
                    'trip_name'   => $booking->trip->name ?? '—',
                    'remaining'   => number_format($booking->remaining(), 2),
                ]
            ]);
        }
        return response()->json(['success' => false, 'message' => 'لا يوجد حجز مرتبط بهذا الحساب']);
    }
}