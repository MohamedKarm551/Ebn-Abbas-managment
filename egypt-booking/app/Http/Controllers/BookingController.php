<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;
use App\Models\JournalEntry;     
use App\Models\AccountLedger;  
use App\Models\RoomAssignment;
use App\Models\TripPrice;
use App\Models\VoucherDetail;

class BookingController extends Controller
{

    public function create(Trip $trip) {
        $representatives = User::role('Representative')->get();
        return view('bookings.create', compact('trip', 'representatives'));
    }

    public function store(Request $request, Trip $trip) {
        $request->validate([
            'client_name'        => 'required',
            'gender'             => 'required',
            'accommodation_type' => 'required',
            'first_payment'      => 'required|numeric|min:0',
        ]);

        if ($trip->available_seats < 1) {
            return back()->withErrors(['error' => '❌ لا توجد مقاعد متاحة في هذه الرحلة.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $price = $trip->prices
                ->where('room_type', $request->accommodation_type)
                ->first();

            $base_price = $price ? $price->price : 0;

            // رفع الصور
            $passportPath = null;
            $photoPath    = null;
            $receiptPath = null;

            if ($request->hasFile('first_payment_receipt')) {
                $receiptPath = $request->file('first_payment_receipt')->store('receipts', 'public');
            }

            if ($request->hasFile('passport_image')) {
                $passportPath = $request->file('passport_image')->store('passports', 'public');
            }
            if ($request->hasFile('personal_photo')) {
                $photoPath = $request->file('personal_photo')->store('photos', 'public');
            }

            // إنشاء الحجز
            $booking = Booking::create([
                'trip_id'            => $trip->id,
                'client_name'        => $request->client_name,
                'gender'             => $request->gender,
                'passport_image'     => $passportPath,
                'personal_photo'     => $photoPath,
                'accommodation_type' => $request->accommodation_type,
                'base_price'         => $base_price,
                'representative_id' => $request->representative_id,
                'notes'              => $request->notes,
            ]);

            // إضافة أول دفعة
            if ($request->first_payment > 0) {
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount'     => $request->first_payment,
                    'paid_at'    => now(),
                    'notes'      => 'أول دفعة',
                    'receipt_image' => $receiptPath,
                ]);
                AccountingService::onPaymentCreated($payment);
                $journalEntry = JournalEntry::where('source_type', Payment::class)
                    ->where('source_id', $payment->id)
                    ->latest()
                    ->first();

                if ($journalEntry) {
                     $payment->update(['journal_entry_id' => $journalEntry->id]);
                    $lines = $journalEntry->lines;
                    $debitLine  = $lines->where('debit',  '>', 0)->first();
                    $creditLine = $lines->where('credit', '>', 0)->first();

                    VoucherDetail::create([
                        'journal_entry_id'  => $journalEntry->id,
                        'voucher_type'      => 'receipt',
                        'debit_account_id'  => $debitLine?->account_id,
                        'credit_account_id' => $creditLine?->account_id,
                        'amount'            => $payment->amount,
                        'subject'           => 'دفعة حجز - ' . $booking->client_name,
                        'booking_id'        => $booking->id,
                    ]);
                }
            }
            $trip->decrement('available_seats', 1);
            AccountingService::onBookingCreated($booking);

            DB::commit();

            return redirect()->route('bookings.show', $booking)
            ->with('success', '✅ تم إنشاء الحجز');
        }       
        catch (\Exception $e) 
        {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء الحجز: '.$e->getMessage()])->withInput();
        }
    }

    public function show(Booking $booking) {
        $booking->load('payments', 'trip');
        return view('bookings.show', compact('booking'));
    }

    public function addPayment(Request $request, Booking $booking) {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $receiptImagePath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptImagePath = $request->file('receipt_image')->store('receipts', 'public');
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'booking_id'    => $booking->id,
                'amount'        => $request->amount,
                'paid_at'       => $request->paid_at ?? now(),
                'notes'         => $request->notes,
                'receipt_image' => $receiptImagePath,
            ]);

            AccountingService::onPaymentCreated($payment);

             $journalEntry = JournalEntry::where('source_type', Payment::class)
                    ->where('source_id', $payment->id)
                    ->latest()
                    ->first();

                if ($journalEntry) {
                    $payment->update(['journal_entry_id' => $journalEntry->id]);

                    $lines = $journalEntry->lines;
                    $debitLine  = $lines->where('debit',  '>', 0)->first();
                    $creditLine = $lines->where('credit', '>', 0)->first();
                
                    VoucherDetail::create([
                        'journal_entry_id'  => $journalEntry->id,
                        'voucher_type'      => 'receipt',
                        'debit_account_id'  => $debitLine?->account_id,
                        'credit_account_id' => $creditLine?->account_id,
                        'amount'            => $payment->amount,
                        'subject'           => $request->notes,
                         'booking_id'        => $booking->id,
                    ]);
                }

            DB::commit();
            return back()->with('success', '✅ تم إضافة الدفعة');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء إضافة الدفعة: ' . $e->getMessage()]);
        }
    }


    public function tripBookings(Trip $trip, Request $request) 
    {
        $query = $trip->bookings()->with('payments', 'representative', 'roomAssignment', 'journalEntry');

        // ========== البحث الموحد (الاسم أو رقم الحجز) ==========
        if ($request->filled('search')) {
            $search = $request->search;

            if (is_numeric($search)) {
                $query->where('id', $search);
            } else {
                $query->where('client_name', 'LIKE', '%' . $search . '%');
            }
        }
        
        if ($request->has_remaining == '1') {
            $query->hasRemaining();
        }

        // التعامل مع صلاحيات المستخدم
        $user = auth()->user();
        if ($user->hasRole('representative')) {
            // الممثل يرى حجوزاته فقط
            $query->where('representative_id', $user->id);
        } else {
        // الأدمن: يطبق فلتر المندوب إذا أرسل في الطلب
        if ($request->filled('representative_id'))
            $query->where('representative_id', $request->representative_id);
        }

        if ($request->filled('gender'))
            $query->where('gender', $request->gender);

        if ($request->filled('accommodation_type'))
            $query->where('accommodation_type', $request->accommodation_type);

        if ($request->filled('room_status')) {
            if ($request->room_status === 'assigned')
                $query->whereHas('roomAssignment');
            else
                $query->whereDoesntHave('roomAssignment');
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();
        $representatives = [];
        if ($user->hasRole('admin')) {
            $representatives = User::role('Representative')->get();
        }

        return view('bookings.index', compact('trip', 'bookings', 'representatives'));
    }

    public function edit(Booking $booking) {
        $booking->load('trip.prices', 'payments');
       $representatives = User::role('Representative')->get();

        return view('bookings.edit', compact('booking', 'representatives'));
    }

    public function update(Request $request, Booking $booking) 
    {
        $request->validate([
            'client_name'        => 'required',
            'gender'             => 'required',
            'accommodation_type' => 'required',
            'representative_id'  => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $oldBasePrice = $booking->base_price;
        
            
            if ($request->hasFile('passport_image')) {
                $booking->deleteFileIfExists($booking->passport_image);
                $booking->passport_image = $request->file('passport_image')->store('passports', 'public');
            }
            if ($request->hasFile('personal_photo')) {
                $booking->deleteFileIfExists($booking->personal_photo);
                $booking->personal_photo = $request->file('personal_photo')->store('photos', 'public');
            }


            // تحديث السعر لو اتغير نوع التسكين
            $price = $booking->trip->prices
                ->where('room_type', $request->accommodation_type)
                ->first();
        
            $booking->client_name = $request->client_name;
            $booking->gender = $request->gender;
            $booking->accommodation_type = $request->accommodation_type;
            $booking->base_price = $price ? $price->price : $booking->base_price;
            $booking->notes = $request->notes;
            $booking->representative_id = $request->representative_id;

            $booking->save();

            if ($booking->wasChanged('client_name') && $booking->account_id) {
                $account = \App\Models\Account::find($booking->account_id);
                if ($account) {
                    $account->update([
                        'name'        => $booking->id . ' - ' . trim($booking->client_name),
                    ]);
                }
            }
    
            AccountingService::onBookingUpdated($booking, $oldBasePrice);
            DB::commit();
            return redirect()->route('trips.bookings', $booking->trip)
                ->with('success', '✅ تم تعديل الحجز');
        } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Booking $booking) {
        DB::transaction(function () use ($booking) {
            //$booking->deleteAssociatedFiles();
            $trip = $booking->trip;

            $roomAssignment = $booking->roomAssignment;
            if ($roomAssignment) {
                $booking->old_room_assignment_id = $roomAssignment->id;
                $booking->room_assignment_id = null;
                $booking->save();

                if ($roomAssignment->bookings()->count() == 1) {
                    $roomAssignment->delete(); // Soft Delete
                }
            }

            AccountingService::onBookingDeleted($booking);
            $booking->discounts()->delete();
            $booking->delete();
            $trip->increment('available_seats', 1);
        });
        return redirect()->route('trips.bookings', $booking->trip)
            ->with('success', '🗑️ تم حذف الحجز و استعادة مقعد');
    }

    public function restore($id) {
        $booking = Booking::withTrashed()->findOrFail($id);
        $trip = $booking->trip;
        $warningMessage = null;
           if ($trip->available_seats <= 0) {
                return redirect()->back()
                    ->with('error', '❌ لا توجد مقاعد متاحة، لا يمكن استعادة الحجز');
            }
    
        DB::transaction(function () use ($booking, $trip) {
            

            JournalEntry::withTrashed()
                ->where('source_type', Booking::class)
                ->where('source_id', $booking->id)
                ->each(fn($e) => AccountingService::restoreEntry($e));

            $booking->discounts()->withTrashed()->each(function ($discount) {
                JournalEntry::withTrashed()
                    ->where('source_type', 'App\Models\Discount')
                    ->where('source_id', $discount->id)
                    ->each(fn($e) => AccountingService::restoreEntry($e));
                $discount->restore();
            });

            $booking->restore();

            $assigned = false;

            if ($booking->old_room_assignment_id) {
                $originalRoom = RoomAssignment::withTrashed()
                    ->find($booking->old_room_assignment_id);

                if ($originalRoom && !$originalRoom->trashed()) {
                    $currentOccupancy = $originalRoom->bookings()->count();
                    if ($currentOccupancy < $originalRoom->capacity) {
                        $booking->room_assignment_id = $originalRoom->id;
                        $booking->old_room_assignment_id = null;
                        $booking->save();
                        $assigned = true;
                    }
                }
            }

             //  إذا لم يتم التسكين، حاول البحث عن غرفة بديلة من نفس النوع
            if (!$assigned) {
                $roomType = $booking->roomAssignment ? $booking->roomAssignment->room_type : null;

                $availableRoom = RoomAssignment::where('trip_id', $trip->id)
                    ->where('capacity', '>', function ($query) {
                        $query->selectRaw('COUNT(*)')
                            ->from('bookings')
                            ->whereColumn('bookings.room_assignment_id', 'room_assignments.id')
                            ->whereNull('bookings.deleted_at'); 
                    })
                    ->when($roomType, function ($query) use ($roomType) {
                        return $query->where('room_type', $roomType);
                    })
                    ->orderBy('capacity') 
                    ->first();

                if ($availableRoom) {
                    $booking->room_assignment_id = $availableRoom->id;
                    $booking->old_room_assignment_id = null;
                    $booking->save();
                    $assigned = true;
                }
            }

            // 6. إذا لم نجد أي غرفة، نترك الحجز بدون تسكين (مع إشعار)
           
            if (!$assigned) {
                $booking->room_assignment_id = null;
                $booking->old_room_assignment_id = null;
                $booking->save();
               $warningMessage = '⚠️ تم استعادة الحجز ولكن لم يتوفر تسكين للغرفة، يرجى تسكينه يدوياً.';
            }


            $trip->decrement('available_seats', 1);
        });

        $redirect = redirect()->back()->with('success', '✅ تم استعادة الحجز');

        if ($warningMessage) {
            $redirect->with('warning', $warningMessage);
        }

        return $redirect;
    }

    public function trashed(Trip $trip) {
        $bookings = Booking::onlyTrashed()
            ->where('trip_id', $trip->id)
            ->latest('deleted_at')
            ->paginate(20);
        return view('bookings.trashed', compact('bookings', 'trip'));
    }
}