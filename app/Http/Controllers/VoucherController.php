<?php
// app/Http/Controllers/VoucherController.php

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
        // الحسابات المدينة (الخزينة/البنوك) – تظهر في قائمة "الحساب المقابل"
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

        // الحسابات الدائنة (الجهات) – تظهر في قائمة "الجهة"
        $creditAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)->orderBy('code')->get();

        // توليد رقم مرجعي
        $lastEntry = JournalEntry::where('reference', 'like', 'RCV-%')
            ->orderByRaw('CAST(SUBSTRING(reference, 5) AS UNSIGNED) DESC')
            ->first();
        $lastNum = $lastEntry ? (int) substr($lastEntry->reference, 4) : 0;
        $nextRef = 'RCV-' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

        return view('vouchers.receipt', compact('debitAccounts', 'creditAccounts', 'nextRef'));
    }

    // ══════════════════════════════════════════
    // صفحة ايصال صرف
    // ══════════════════════════════════════════
    public function payment()
    {
        // الحسابات الدائنة (الخزينة/البنوك) – تظهر في قائمة "الحساب المقابل"
        $creditAccounts = Account::where('is_leaf', true)
            ->where('is_active', true)->where('type', 'asset')
            ->orderBy('code')->get();

        if ($creditAccounts->isEmpty()) {
            $creditAccounts = Account::where('is_leaf', true)
                ->where('is_active', true)->where('type', 'asset')
                ->orderBy('code')->get();
        }

        // الحسابات المدينة (الجهات) – تظهر في قائمة "الجهة"
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

        return view('vouchers.payment', compact('debitAccounts', 'creditAccounts', 'nextRef'));
    }

    // ══════════════════════════════════════════
    // API: جلب الحجوزات المفتوحة حسب الحساب
    // ══════════════════════════════════════════
    public function getOpenBookings(Request $request)
    {
    try {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'voucher_type' => 'required|in:receipt,payment',
        ]);

        $account = Account::findOrFail($request->account_id);
        $voucherType = $request->voucher_type;
        $bookings = collect();

        // ✅ الشركة المرتبطة بالحساب
        $company = null;
        if ($account->company_id) {
            $company = \App\Models\Company::find($account->company_id);
        }

        // ✅ الوكيل المرتبط بالحساب
        $agent = null;
        if ($account->agent_id) {
            $agent = \App\Models\Agent::find($account->agent_id);
        }

        // تسجيل للتحقق
        Log::info('getOpenBookings - account_id: ' . $account->id . 
                  ', company_id: ' . ($account->company_id ?? 'null') . 
                  ', agent_id: ' . ($account->agent_id ?? 'null'));

        if ($voucherType === 'receipt' && $company) {
            // استلام: حجوزات الشركة التي عليها مستحق
            $bookings = \App\Models\Booking::where('company_id', $company->id)
                ->whereRaw('amount_due_from_company - amount_paid_by_company > 0')
                ->orderBy('check_in', 'asc')
                ->get(['id', 'client_name', 'check_in', 'check_out', 'amount_due_from_company', 'amount_paid_by_company'])
                ->map(function($b) {
                    $remaining = $b->amount_due_from_company - $b->amount_paid_by_company;
                    return [
                        'id' => $b->id,
                        'label' => "{$b->client_name} - " . number_format($remaining, 2) . " متبقي (دخول: {$b->check_in->format('d/m/Y')})",
                        'remaining' => $remaining,
                        'client_name' => $b->client_name,
                        'check_in' => $b->check_in->format('d/m/Y'),
                        'check_out' => $b->check_out->format('d/m/Y'),
                    ];
                });
        } elseif ($voucherType === 'payment' && $agent) {
            // صرف: حجوزات الوكيل التي عليها مستحق للفندق
            $bookings = \App\Models\Booking::where('agent_id', $agent->id)
                ->whereRaw('amount_due_to_hotel - amount_paid_to_hotel > 0')
                ->orderBy('check_in', 'asc')
                ->get(['id', 'client_name', 'check_in', 'check_out', 'amount_due_to_hotel', 'amount_paid_to_hotel'])
                ->map(function($b) {
                    $remaining = $b->amount_due_to_hotel - $b->amount_paid_to_hotel;
                    return [
                        'id' => $b->id,
                        'label' => "{$b->client_name} - " . number_format($remaining, 2) . " متبقي (دخول: {$b->check_in->format('d/m/Y')})",
                        'remaining' => $remaining,
                        'client_name' => $b->client_name,
                        'check_in' => $b->check_in->format('d/m/Y'),
                        'check_out' => $b->check_out->format('d/m/Y'),
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'bookings' => $bookings,
            'has_bookings' => $bookings->isNotEmpty(),
            'entity_name' => $company ? $company->name : ($agent ? $agent->name : null),
            'entity_type' => $company ? 'company' : ($agent ? 'agent' : null),
        ]);

    } catch (\Exception $e) {
        Log::error('خطأ في getOpenBookings: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ: ' . $e->getMessage(),
            'has_bookings' => false,
            'bookings' => [],
        ], 500);
    }
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
            'cheque_date'       => 'nullable|date',
            'booking_id'        => 'nullable|exists:bookings,id', // ✅ جديد
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

    $bookingId = $request->booking_id;
   
    DB::transaction(function () use ($request, $bookingId) {
        $entry = JournalEntry::create([
            'reference'   => $request->reference,
            'entry_date'  => $request->entry_date,
            'status'      => 'posted',
            'source_type' => $request->voucher_type,
            'source_id'   => $bookingId,
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

        // ===========================================
        // ✅ معالجة الحجز المرتبط (إن وجد)
        // ===========================================
        
        if ($bookingId) {
            $booking = \App\Models\Booking::find($bookingId);
            if ($booking) {
                if ($request->voucher_type === 'receipt') {
                    // استلام: دفعة من الشركة
                    $newPaid = $booking->amount_paid_by_company + $amount;
                    $booking->update(['amount_paid_by_company' => $newPaid]);
                    
                    // تحديث المتابعة المالية
                    $financialTracking = $booking->financialTracking()->first();
                    if ($financialTracking) {
                        $financialTracking->update([
                            'company_payment_amount' => $newPaid,
                            'company_payment_status' => $newPaid >= $booking->amount_due_from_company ? 'fully_paid' : 'partially_paid',
                            'last_updated_by' => Auth::id(),
                        ]);
                    }
                    
                } elseif ($request->voucher_type === 'payment') {
                    // صرف: دفعة للوكيل (فندق)
                    $newPaid = $booking->amount_paid_to_hotel + $amount;
                    $booking->update(['amount_paid_to_hotel' => $newPaid]);
                    
                    // تحديث المتابعة المالية
                    $financialTracking = $booking->financialTracking()->first();
                    if ($financialTracking) {
                        $financialTracking->update([
                            'agent_payment_amount' => $newPaid,
                            'agent_payment_status' => $newPaid >= $booking->amount_due_to_hotel ? 'fully_paid' : 'partially_paid',
                            'last_updated_by' => Auth::id(),
                        ]);
                    }
                }
            }
        }

        VoucherDetail::create([
            'journal_entry_id'  => $entry->id,
            'voucher_type'      => $request->voucher_type,
            'debit_account_id'  => $debitAcc->id,
            'credit_account_id' => $creditAcc->id,
            'amount'            => $amount,
            'subject'           => $request->subject,
            'description'       => $desc,
            'cheque_date'       => $request->cheque_date,
            'sig_receiver'      => $request->sig_receiver,
            'sig_accountant'    => $request->sig_accountant,
            'sig_manager'       => $request->sig_manager,
            'booking_id'        => $bookingId, // ✅ حفظ الحجز المرتبط
        ]);
    });

    return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════
    // عرض الإيصال للتعديل
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

    // ══════════════════════════════════════════
    // تحديث القيد (مع دعم تعديل القيد المحاسبي)
    // ══════════════════════════════════════════
    public function updateVoucher(Request $request, JournalEntry $entry)
    {
        abort_unless(in_array($entry->source_type, ['receipt','payment']), 403);

        $request->validate([
            'entry_date'        => 'required|date',
            'amount'            => 'required|numeric|min:0.01',
            'debit_account_id'  => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'cheque_date'       => 'nullable|date',
            'subject'           => 'nullable|string|max:500',
            'sig_receiver'      => 'nullable|string|max:100',
            'sig_accountant'    => 'nullable|string|max:100',
            'sig_manager'       => 'nullable|string|max:100',
            'booking_id'        => 'nullable|exists:bookings,id',
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
            $newBookingId = $request->booking_id ? (int) $request->booking_id : null;
            $oldBookingId = $detail->booking_id ? (int) $detail->booking_id : null;

            // ─── هل في تغيير يمس القيد المحاسبي؟ ───
            $journalChanged =
                $newAmount    !== $oldAmount    ||
                $newDebitId   !== $oldDebitId   ||
                $newCreditId  !== $oldCreditId  ||
                $newEntryDate !== $oldEntryDate ||
                $newBookingId !== $oldBookingId;

            if (!$journalChanged) {
                // ══ تغيير في البيانات الإضافية فقط (لا نمس القيد) ══
            
                $oldSnapshot = [
                    'subject'        => $detail->subject,
                    'description'    => $detail->description,
                    'cheque_date'    => $detail->cheque_date,
                    'sig_receiver'   => $detail->sig_receiver,
                    'sig_accountant' => $detail->sig_accountant,
                    'sig_manager'    => $detail->sig_manager,
                    'booking_id' => $detail->booking_id,
                ];

                $detail->update([
                    'subject'        => $request->subject,
                    'description'    => $request->subject,
                    'cheque_date'    => $request->cheque_date,
                    'sig_receiver'   => $request->sig_receiver,
                    'sig_accountant' => $request->sig_accountant,
                    'sig_manager'    => $request->sig_manager,
                    'booking_id'     => $request->booking_id,
                ]);

                $entry->lines()->update(['description' => $request->subject]);
                AccountLedger::where('journal_entry_id', $entry->id)
                    ->update(['description' => $request->subject]);

                $newSnapshot = [
                    'subject'        => $request->subject,
                    'description'    => $request->subject,
                    'cheque_date'    => $request->cheque_date,
                    'sig_receiver'   => $request->sig_receiver,
                    'sig_accountant' => $request->sig_accountant,
                    'sig_manager'    => $request->sig_manager,
                    'booking_id'     => $request->booking_id,
                ];

                JournalEditLog::create([
                    'journal_entry_id' => $entry->id,
                    'user_id'          => Auth::id(),
                    'action'           => 'edit',
                    'old_data'         => json_encode($oldSnapshot, JSON_UNESCAPED_UNICODE),
                    'new_data'         => json_encode($newSnapshot, JSON_UNESCAPED_UNICODE),
                    'notes'            => "تعديل بيانات إضافية للإيصال {$entry->source_type} رقم {$entry->reference}",
                ]);

                if ($newBookingId !== $oldBookingId) {
                    // إرجاع المبلغ من الحجز القديم
                    if ($oldBookingId) {
                        $this->releaseBookingPayment($oldBookingId, $detail->amount, $entry->source_type);
                    }
                    // تطبيق المبلغ على الحجز الجديد
                    if ($newBookingId) {
                        $this->applyBookingPayment($newBookingId, $detail->amount, $entry->source_type);
                    }
                }

                Log::info("تعديل بيانات إضافية فقط — القيد #{$entry->id}");

                return; // ✅ خلاص، مش هنمس القيد
            }

            // ══ تغيير يمس القيد المحاسبي → ننشئ قيداً جديداً ونحذف القديم ══

            $oldSnapshot = $this->entrySnapshot($entry);

            $debitAcc  = Account::findOrFail($newDebitId);
            $creditAcc = Account::findOrFail($newCreditId);
            $desc      = $request->subject
                ?: ($entry->source_type === 'receipt'
                    ? "ايصال استلام - {$creditAcc->name}"
                    : "ايصال صرف - {$debitAcc->name}");

            if ($oldBookingId) {
                $this->releaseBookingPayment($oldBookingId, $oldAmount, $entry->source_type);
            }
            if ($newBookingId) {
                $this->applyBookingPayment($newBookingId, $newAmount, $entry->source_type);
            }

            $newEntry = JournalEntry::create([
                'reference'   => $entry->reference,
                'entry_date'  => $newEntryDate,
                'status'      => 'posted',
                'source_type' => $entry->source_type,
                'source_id'   => $newBookingId ?: null,
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

            // نقل سجلات التعديل القديمة إلى القيد الجديد
            JournalEditLog::where('journal_entry_id', $entry->id)
                ->update(['journal_entry_id' => $newEntry->id]);

            // حذف أثر القيد القديم
            AccountLedger::where('journal_entry_id', $entry->id)->delete();
            $entry->lines()->delete();
            $entry->delete();

            // إنشاء تفاصيل الإيصال للقيد الجديد
            VoucherDetail::updateOrCreate(
                ['journal_entry_id' => $newEntry->id],
                [
                    'voucher_type'      => $newEntry->source_type,
                    'debit_account_id'  => $debitAcc->id,
                    'credit_account_id' => $creditAcc->id,
                    'amount'            => $newAmount,
                    'subject'           => $request->subject,
                    'description'       => $desc,
                    'cheque_date'       => $request->cheque_date,
                    'sig_receiver'      => $request->sig_receiver,
                    'sig_accountant'    => $request->sig_accountant,
                    'sig_manager'       => $request->sig_manager,
                    'booking_id' => $request->booking_id,
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

/**
 * إرجاع المبلغ من حجز (عند إلغاء الربط أو تغيير الحجز)
 */
private function releaseBookingPayment($bookingId, $amount, $voucherType)
{
    $booking = \App\Models\Booking::find($bookingId);
    if (!$booking) return;

    if ($voucherType === 'receipt') {
        $newPaid = max(0, $booking->amount_paid_by_company - $amount);
        $booking->update(['amount_paid_by_company' => $newPaid]);
        $totalDue = $booking->amount_due_from_company;
        $status = $newPaid >= $totalDue ? 'fully_paid' : ($newPaid > 0 ? 'partially_paid' : 'not_paid');

        $financialTracking = $booking->financialTracking()->first();
        if ($financialTracking) {
            $financialTracking->update([
                'company_payment_amount' => $newPaid,
                'company_payment_status' => $status,
                'last_updated_by' => Auth::id(),
            ]);
        }
    } elseif ($voucherType === 'payment') {
        $newPaid = max(0, $booking->amount_paid_to_hotel - $amount);
        $booking->update(['amount_paid_to_hotel' => $newPaid]);
        $totalDue = $booking->amount_due_to_hotel;
        $status = $newPaid >= $totalDue ? 'fully_paid' : ($newPaid > 0 ? 'partially_paid' : 'not_paid');

        $financialTracking = $booking->financialTracking()->first();
        if ($financialTracking) {
            $financialTracking->update([
                'agent_payment_amount' => $newPaid,
                'agent_payment_status' => $status,
                'last_updated_by' => Auth::id(),
            ]);
        }
    }
}

/**
 * تطبيق المبلغ على حجز (عند الربط بحجز جديد)
 */
private function applyBookingPayment($bookingId, $amount, $voucherType)
{
    $booking = \App\Models\Booking::find($bookingId);
    if (!$booking) return;

    if ($voucherType === 'receipt') {
        $newPaid = $booking->amount_paid_by_company + $amount;
        $booking->update(['amount_paid_by_company' => $newPaid]);
        $totalDue = $booking->amount_due_from_company;
        $status = $newPaid >= $totalDue ? 'fully_paid' : ($newPaid > 0 ? 'partially_paid' : 'not_paid');
        
        $financialTracking = $booking->financialTracking()->first();
        if ($financialTracking) {
            $financialTracking->update([
                'company_payment_amount' => $newPaid,
                'company_payment_status' => $status,
                'last_updated_by' => Auth::id(),
            ]);
        }
    } elseif ($voucherType === 'payment') {
        $newPaid = $booking->amount_paid_to_hotel + $amount;
        $booking->update(['amount_paid_to_hotel' => $newPaid]);
        $totalDue = $booking->amount_due_to_hotel;
        $status = $newPaid >= $totalDue ? 'fully_paid' : ($newPaid > 0 ? 'partially_paid' : 'not_paid');
        $financialTracking = $booking->financialTracking()->first();
        if ($financialTracking) {
            $financialTracking->update([
                'agent_payment_amount' => $newPaid,
                'agent_payment_status' => $status,
                'last_updated_by' => Auth::id(),
            ]);
        }
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
}