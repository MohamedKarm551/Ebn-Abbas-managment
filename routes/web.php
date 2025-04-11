<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HotelController;

Route::get('/bookings', [BookingsController::class, 'index'])->name('bookings.index');
Route::get('/bookings/create', [BookingsController::class, 'create'])->name('bookings.create');
Route::post('/bookings', [BookingsController::class, 'store'])->name('bookings.store');
Route::get('/bookings/details/{hotelId}', [BookingsController::class, 'details'])->name('bookings.details');
Route::get('/bookings/{id}/edit', [BookingsController::class, 'edit'])->name('bookings.edit');

Route::put('/bookings/{id}/update', [BookingsController::class, 'update'])->name('bookings.update');

Route::get('/bookings/{id}/edits', [BookingsController::class, 'getEdits']);


Route::delete('/bookings/{id}', [BookingsController::class, 'destroy'])->name('bookings.destroy');
Route::get('/bookings/{id}', [BookingsController::class, 'show'])->name('bookings.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('storeEmployee');
    Route::delete('/employees/{id}', [AdminController::class, 'deleteEmployee'])->name('deleteEmployee');
    Route::put('/employees/{id}', [AdminController::class, 'updateEmployee'])->name('updateEmployee');

    Route::get('/companies', [AdminController::class, 'companies'])->name('companies');
    Route::post('/companies', [AdminController::class, 'storeCompany'])->name('storeCompany');
    Route::get('/companies/{id}/edit', [AdminController::class, 'editCompany'])->name('editCompany'); // مسار صفحة التعديل
    Route::put('/companies/{id}', [AdminController::class, 'updateCompany'])->name('updateCompany'); // مسار التحديث
    Route::delete('/companies/{id}', [AdminController::class, 'deleteCompany'])->name('deleteCompany');

    Route::get('/agents', [AdminController::class, 'agents'])->name('agents');
    Route::post('/agents', [AdminController::class, 'storeAgent'])->name('storeAgent');
    Route::get('/agents/{id}/edit', [AdminController::class, 'editAgent'])->name('editAgent'); // مسار صفحة التعديل
    Route::put('/agents/{id}', [AdminController::class, 'updateAgent'])->name('updateAgent'); // مسار التحديث
    Route::delete('/agents/{id}', [AdminController::class, 'deleteAgent'])->name('deleteAgent');

    Route::get('/hotels', [HotelController::class, 'index'])->name('hotels');
    Route::post('/hotels', [HotelController::class, 'store'])->name('storeHotel');
    Route::delete('/hotels/{id}', [HotelController::class, 'destroy'])->name('deleteHotel');
    Route::get('/hotels/{id}/edit', [HotelController::class, 'edit'])->name('editHotel'); // مسار صفحة التعديل
    Route::put('/hotels/{id}', [HotelController::class, 'update'])->name('updateHotel'); // مسار التحديث
});

Route::get('/', function () {
    return view('welcome');
});
