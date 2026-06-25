<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\AccountLedger;
use App\Models\JournalEditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VoucherDetail;
class AccountingService
{
    const CASH        = '1.1.1';
    const BANKS       = '1.1.2';
    const RECEIVABLE  = '1.1.3';
    const CUSTOMERS   = '1.1.3.1';
    const REVENUE     = '4.1';
    const DISCOUNT_EX = '5.4';

    public static function account(string $code): ?Account
    {
        return Account::where('code', $code)->where('is_active', true)->first();
    }

    // ================================================
    // Helper: حفظ بيانات القيد للـ log
    // ================================================
    public static function captureEntryData(JournalEntry $entry): array
    {
        $entry->loadMissing('lines');
        return [
            'reference'  => $entry->reference,
            'entry_date' => $entry->entry_date instanceof \Carbon\Carbon
                ? $entry->entry_date->toDateString()
                : $entry->entry_date,
            'status'     => $entry->status,
            'lines'      => $entry->lines->map(fn($l) => [
                'account_id'  => $l->account_id,
                'debit'       => $l->debit,
                'credit'      => $l->credit,
                'description' => $l->description,
            ])->toArray(),
        ];
    }

    // ================================================
    // Helper: حذف قيد بالكامل (lines + ledger + entry)
    // ================================================
    private static function hardDeleteEntry(JournalEntry $entry): void
    {
        AccountLedger::where('journal_entry_id', $entry->id)->delete();
        $entry->lines()->delete();
        $entry->delete();
    }

    // ================================================
    // Helper: تسجيل في journal_edit_logs
    // ================================================
    public static function logEdit(
        int $journalEntryId,
        string $action,
        ?array $oldData,
        array $newData,
        string $notes = ''
    ): void {
        JournalEditLog::create([
            'journal_entry_id' => $journalEntryId,
            'user_id'          => auth()->id(),
            'action'           => $action,
            'old_data'         => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data'         => json_encode($newData, JSON_UNESCAPED_UNICODE),
            'notes'            => $notes,
        ]);
    }

    // ================================================
    // تسجيل في دفتر الأستاذ
    // ================================================
    public static function postEntry(JournalEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $entry->update(['status' => 'posted']);

            foreach ($entry->lines as $line) {
                $account = $line->account;
                if (!$account || !$account->is_active) continue;

                $lastBalance = AccountLedger::where('account_id', $account->id)
                    ->latest()->value('running_balance') ?? 0;

                $newBalance = $lastBalance;
                if ($line->debit > 0) {
                    $newBalance = ($account->normal_balance === 'debit')
                        ? $lastBalance + $line->debit
                        : $lastBalance - $line->debit;
                } elseif ($line->credit > 0) {
                    $newBalance = ($account->normal_balance === 'credit')
                        ? $lastBalance + $line->credit
                        : $lastBalance - $line->credit;
                }

                AccountLedger::create([
                    'account_id'       => $account->id,
                    'journal_entry_id' => $entry->id,
                    'debit'            => $line->debit,
                    'credit'           => $line->credit,
                    'running_balance'  => $newBalance,
                    'description'      => $line->description,
                ]);
            }
        });
    }

    // ================================================
    // BOOKING
    // ================================================
    public static function onBookingCreated(Booking $booking): void
    {
        $existing = JournalEntry::where('source_type', Booking::class)
            ->where('source_id', $booking->id)->exists();
        if ($existing) {
            Log::warning('محاولة إنشاء قيد مكرر للحجز', ['booking_id' => $booking->id]);
            return;
        }

        $clientAccount = Account::find($booking->account_id)
            ?? self::getOrCreateClientAccount($booking);
        $revenue = self::account(self::REVENUE);

        if (!$clientAccount || !$revenue) return;

        $amount = $booking->base_price;

        DB::transaction(function () use ($booking, $clientAccount, $revenue, $amount) {
            $entry = JournalEntry::create([
                'reference'   => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'entry_date'  => now()->toDateString(),
                'status'      => 'draft',
                'source_type' => Booking::class,
                'source_id'   => $booking->id,
                'created_by'  => auth()->id() ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $clientAccount->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => "حجز: {$booking->client_name}",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $revenue->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => "إيراد حجز: {$booking->client_name}",
            ]);

            $entry->load('lines');
            self::logEdit($entry->id, 'create', null, self::captureEntryData($entry),
                "إنشاء قيد حجز #{$booking->id}");
        });
    }

    public static function onBookingUpdated(Booking $booking, $oldBasePrice): void
    {
        if ($booking->base_price == $oldBasePrice) return;

        $oldEntry = JournalEntry::where('source_type', Booking::class)
            ->where('source_id', $booking->id)->first();

        $oldData = $oldEntry ? self::captureEntryData($oldEntry) : null;

        $clientAccount = Account::find($booking->account_id)
            ?? self::getOrCreateClientAccount($booking);
        $revenue = self::account(self::REVENUE);

        if (!$clientAccount || !$revenue) return;

        $amount = $booking->base_price;

        DB::transaction(function () use ($booking, $clientAccount, $revenue, $amount, $oldEntry, $oldData) {
            // إنشاء القيد الجديد
            $newEntry = JournalEntry::create([
                'reference'   => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'entry_date'  => now()->toDateString(),
                'status'      => 'draft',
                'source_type' => Booking::class,
                'source_id'   => $booking->id,
                'created_by'  => auth()->id() ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $newEntry->id,
                'account_id'       => $clientAccount->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => "حجز معدّل: {$booking->client_name}",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $newEntry->id,
                'account_id'       => $revenue->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => "إيراد حجز معدّل: {$booking->client_name}",
            ]);

            $newEntry->load('lines');
            $newData = self::captureEntryData($newEntry);

            // نقل سجلات الـ log القديمة للقيد الجديد
            if ($oldEntry) {
                JournalEditLog::where('journal_entry_id', $oldEntry->id)
                    ->update(['journal_entry_id' => $newEntry->id]);
                self::hardDeleteEntry($oldEntry);
            }

            self::logEdit($newEntry->id, 'edit', $oldData, $newData,
                "تعديل حجز #{$booking->id}");
        });
    }

    public static function onBookingDeleted(Booking $booking): void
    {
        // حذف قيد الحجز
        $entry = JournalEntry::where('source_type', Booking::class)
            ->where('source_id', $booking->id)->first();
        if ($entry) self::hardDeleteEntry($entry);

        // حذف قيود الخصومات
        Discount::where('booking_id', $booking->id)->each(
            fn($d) => self::onDiscountDeleted($d)
        );
    }

    // ================================================
    // PAYMENT
    // ================================================
    public static function onPaymentCreated(Payment $payment): void
    {
        $existing = JournalEntry::where('source_type', Payment::class)
            ->where('source_id', $payment->id)->exists();
        if ($existing) return;

        $booking       = $payment->booking;
        $clientAccount = Account::find($booking->account_id)
            ?? self::getOrCreateClientAccount($booking);
        $cash = self::account(self::CASH);

        if (!$clientAccount || !$cash) return;

        $amount = $payment->amount;

        DB::transaction(function () use ($payment, $booking, $clientAccount, $cash, $amount) {
            $entry = JournalEntry::create([
                'reference'   => 'RCV-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'entry_date'  => now()->toDateString(),
                'status'      => 'draft',
                'source_type' => Payment::class,
                'source_id'   => $payment->id,
                'created_by'  => auth()->id() ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $cash->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => "دفعة من: {$booking->client_name}",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $clientAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => "تسوية ذمم: {$booking->client_name}",
            ]);

            $entry->load('lines');
            self::logEdit($entry->id, 'create', null, self::captureEntryData($entry),
                "إنشاء قيد دفعة #{$payment->id}");
        });
    }

    public static function onPaymentUpdated(Payment $payment, $oldAmount): void
    {
        if ($payment->amount == $oldAmount) return;

        $oldEntry = JournalEntry::where('source_type', Payment::class)
            ->where('source_id', $payment->id)->first();

        $oldData = $oldEntry ? self::captureEntryData($oldEntry) : null;

        $booking       = $payment->booking;
        $clientAccount = Account::find($booking->account_id)
            ?? self::getOrCreateClientAccount($booking);
        $cash = self::account(self::CASH);

        if (!$clientAccount || !$cash) return;

        $amount = $payment->amount;

        DB::transaction(function () use ($payment, $booking, $clientAccount, $cash, $amount, $oldEntry, $oldData) {
            $newEntry = JournalEntry::create([
                'reference'   => 'RCV-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'entry_date'  => now()->toDateString(),
                'status'      => 'draft',
                'source_type' => Payment::class,
                'source_id'   => $payment->id,
                'created_by'  => auth()->id() ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $newEntry->id,
                'account_id'       => $cash->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => "دفعة معدّلة من: {$booking->client_name}",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $newEntry->id,
                'account_id'       => $clientAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => "تسوية ذمم معدّلة: {$booking->client_name}",
            ]);

            $newEntry->load('lines');
            $newData = self::captureEntryData($newEntry);

            if ($oldEntry) {
                JournalEditLog::where('journal_entry_id', $oldEntry->id)
                    ->update(['journal_entry_id' => $newEntry->id]);
                self::hardDeleteEntry($oldEntry);
            }

            self::logEdit($newEntry->id, 'edit', $oldData, $newData,
                "تعديل دفعة #{$payment->id}");
        });
    }

   public static function onPaymentDeleted(Payment $payment): void
{
    $entry = null;
    if ($payment->journal_entry_id) {
        $entry = JournalEntry::find($payment->journal_entry_id);
    }

    if (!$entry) {
        $entry = JournalEntry::where('source_id', $payment->id)
            ->where(function ($query) {
                $query->where('source_type', Payment::class)
                      ->orWhere('source_type', 'payment')
                      ->orWhere('source_type', 'App\Models\Payment')
                      ->orWhere('source_type', 'Payment');
            })->first();
    }

    if ($entry) {
        VoucherDetail::where('journal_entry_id', $entry->id)->delete();
        self::hardDeleteEntry($entry);
    }
}

    // ================================================
    // DISCOUNT
    // ================================================
    public static function onDiscountApproved(Discount $discount): void
    {
        // إذا كان هناك قيد قديم: احذفه أولاً
        $oldEntry = JournalEntry::where('source_type', Discount::class)
            ->where('source_id', $discount->id)->first();
        $oldData = $oldEntry ? self::captureEntryData($oldEntry) : null;
        if ($oldEntry) self::hardDeleteEntry($oldEntry);

        $booking      = $discount->booking;
        $clientAccount = Account::find($booking->account_id)
            ?? self::getOrCreateClientAccount($booking);
        $discountAcct = self::account(self::DISCOUNT_EX);

        if (!$clientAccount || !$discountAcct) return;

        $amount = $discount->amount;

        DB::transaction(function () use ($discount, $booking, $clientAccount, $discountAcct, $amount, $oldData) {
            $entry = JournalEntry::create([
                'reference'   => 'DISC-' . str_pad($discount->id, 6, '0', STR_PAD_LEFT),
                'entry_date'  => now()->toDateString(),
                'status'      => 'draft',
                'source_type' => Discount::class,
                'source_id'   => $discount->id,
                'created_by'  => auth()->id() ?? 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $discountAcct->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => "خصم للعميل: {$booking->client_name}",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $clientAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => "تخفيض ذمة: {$booking->client_name}",
            ]);

            $entry->load('lines');
            $newData = self::captureEntryData($entry);
            $action  = $oldData ? 'edit' : 'create';
            self::logEdit($entry->id, $action, $oldData, $newData,
                $oldData ? "تعديل خصم #{$discount->id}" : "إنشاء قيد خصم #{$discount->id}");
        });
    }

    public static function onDiscountUpdated(Discount $discount, $oldAmount): void
    {
        // نعيد استدعاء onDiscountApproved فهو بحذف القديم وينشئ جديد
        self::onDiscountApproved($discount);
    }

    public static function onDiscountDeleted(Discount $discount): void
    {
        $entry = JournalEntry::where('source_type', 'App\Models\Discount')
            ->where('source_id', $discount->id)->first();
        if ($entry) self::hardDeleteEntry($entry);
    }

    // ================================================
    // جلب أو إنشاء حساب العميل
    // ================================================
    public static function getOrCreateClientAccount(Booking $booking): Account
    {
        if ($booking->account_id) {
            $account = Account::find($booking->account_id);
            if ($account && !$account->trashed()) return $account;
        }

        $customersParent = Account::where('code', self::CUSTOMERS)->first();
        if (!$customersParent) {
            throw new \Exception('حساب العملاء الرئيسي غير موجود');
        }

        $existing = Account::where('parent_id', $customersParent->id)
            ->where('name', 'like', $booking->id . ' - %')
            ->withTrashed()->first();

        if ($existing) {
            if ($existing->trashed()) $existing->restore();
            $booking->account_id = $existing->id;
            $booking->saveQuietly();
            return $existing;
        }

        $lastCode = Account::where('parent_id', $customersParent->id)
            ->withTrashed()->get()
            ->map(fn($a) => (int) last(explode('.', $a->code)))->max() ?? 0;

        $account = Account::create([
            'code'           => $customersParent->code . '.' . ($lastCode + 1),
            'name'           => $booking->id . ' - ' . trim($booking->client_name),
            'type'           => 'asset',
            'normal_balance' => 'debit',
            'parent_id'      => $customersParent->id,
            'is_leaf'        => true,
            'is_active'      => true,
            'description'    => "حساب العميل: {$booking->client_name}",
        ]);

        $booking->account_id = $account->id;
        $booking->saveQuietly();

        return $account;
    }

    public static function restoreEntry(JournalEntry $entry): void
    {
        $entry->lines()->withTrashed()->restore();

        AccountLedger::withTrashed()
            ->where('journal_entry_id', $entry->id)
            ->restore();

        $entry->restore();
    }
    
}