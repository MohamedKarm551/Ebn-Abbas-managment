<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



use App\Http\Controllers\TripController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomAssignmentController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\FinancialReportsController;

Route::middleware(['auth'])->group(function () {

    Route::get('/trips/{trip}/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/trips/{trip}/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/payments', [BookingController::class, 'addPayment'])->name('bookings.payments.add');

    Route::get('/trips/{trip}/bookings', [BookingController::class, 'tripBookings'])->name('trips.bookings');

    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::get('bookings/trashed/{trip}', [BookingController::class, 'trashed'])->name('bookings.trashed');
    Route::post('bookings/{id}/restore', [BookingController::class, 'restore'])->name('bookings.restore');
    
    Route::prefix('payments')->group(function () {
        Route::delete('{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });


    Route::resource('employees', EmployeeController::class)->except(['show']);

    Route::get('/trips/{trip}/representatives-report',[TripController::class, 'representativesReport'])->name('trips.representatives.report');
    Route::get('trips/trashed', [TripController::class, 'trashed'])->name('trips.trashed');
    Route::post('trips/{id}/restore', [TripController::class, 'restore'])->name('trips.restore');
    Route::resource('trips', TripController::class);

    Route::get('/trips/{trip}/room-assignments',[RoomAssignmentController::class, 'index'])->name('trips.room-assignments');
    Route::post('/trips/{trip}/room-assignments/add-room',[RoomAssignmentController::class, 'addRoom'])->name('room-assignments.add-room');
    Route::patch('/room-assignments/{room}/update-room',[RoomAssignmentController::class, 'updateRoom'])->name('room-assignments.update-room');
    Route::delete('/room-assignments/{room}',[RoomAssignmentController::class, 'deleteRoom'])->name('room-assignments.delete-room');
    Route::post('/room-assignments/assign',[RoomAssignmentController::class, 'assign'])->name('room-assignments.assign');
    Route::patch('/room-assignments/unassign/{booking}',[RoomAssignmentController::class, 'unassign'])->name('room-assignments.unassign');


    Route::post('/bookings/{booking}/discounts',[DiscountController::class, 'store'])->name('discounts.store');
    Route::get('/discounts/pending',[DiscountController::class, 'pendingIndex'])->name('discounts.pending');
    Route::patch('/discounts/{discount}/approve',[DiscountController::class, 'approve'])->name('discounts.approve');
    Route::patch('/discounts/{discount}/reject',[DiscountController::class, 'reject'])->name('discounts.reject');
    Route::prefix('discounts')->group(function () {
        Route::get('{discount}/edit', [DiscountController::class, 'edit'])->name('discounts.edit');
        Route::put('{discount}', [DiscountController::class, 'update'])->name('discounts.update');
        Route::delete('{discount}', [DiscountController::class, 'destroy'])->name('discounts.destroy');
    });


    Route::get('/employees/{employee}/report',[EmployeeController::class, 'report'])->name('employees.report');

    Route::get('/pricing', function () {return view('pricing');})->name('pricing');

    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::patch('/accounts/{account}/toggle-freeze', [AccountController::class, 'toggleFreeze'])->name('accounts.toggle-freeze');
    Route::get('/accounts/{account}/ledger', [AccountController::class, 'ledger'])->name('accounts.ledger');
    Route::get('/accounts/{account}/ledger/export', [AccountController::class, 'ledgerExport'])->name('accounts.ledger.export');
    Route::get('/accounts/search', [AccountController::class, 'search'])->name('accounts.search');
    Route::get('/accounts/export', [AccountController::class, 'export'])->name('accounts.export');

    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/create', [JournalController::class, 'create'])->name('journal.create');
    Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');

    Route::get('/journal/pending', [JournalController::class, 'pending'])->name('journal.pending');
    Route::patch('/journal/{entry}/approve', [JournalController::class, 'approve'])->name('journal.approve');
    Route::delete('/journal/{entry}/cancel', [JournalController::class, 'cancel'])->name('journal.cancel');
    Route::patch('/journal/{entry}/reverse', [JournalController::class, 'reverse'])->name('journal.reverse');
    Route::get('/journal/search', [JournalController::class, 'searchAccounts'])->name('journal.search');
    Route::get('/journal/{id}/history', [JournalController::class, 'history'])->name('journal.history');

    Route::get('/vouchers/receipt', [VoucherController::class, 'receipt'])->name('vouchers.receipt');
    Route::get('/vouchers/payment', [VoucherController::class, 'payment'])->name('vouchers.payment');
    Route::post('/vouchers/save',   [VoucherController::class, 'save'])->name('vouchers.save');
    Route::get('/vouchers/open-bookings', [VoucherController::class, 'getOpenBookings'])->name('vouchers.open-bookings');
    Route::get('/vouchers/booking-by-account/{account}', [VoucherController::class, 'getBookingByAccount'])->name('vouchers.booking-by-account');
    Route::get('/vouchers/{entry}/show',       [VoucherController::class, 'showVoucher'])->name('vouchers.show');
    Route::put('/vouchers/{entry}/update',     [VoucherController::class, 'updateVoucher'])->name('vouchers.update');

// ===== التقارير الختامية =====
Route::prefix('financial-reports')->name('financial-reports.')->middleware('auth')->group(function () {
    Route::get('/trial-balance',    [FinancialReportsController::class, 'trialBalance'])->name('trial-balance');
    Route::get('/income-statement', [FinancialReportsController::class, 'incomeStatement'])->name('income-statement');
    Route::get('/balance-sheet',    [FinancialReportsController::class, 'balanceSheet'])->name('balance-sheet');
});
});



require __DIR__.'/auth.php';
