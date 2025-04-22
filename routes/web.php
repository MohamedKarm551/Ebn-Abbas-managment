<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;

Route::middleware(['auth'])->group(function () {
    // تصدير جدول الحجز
    Route::get('/bookings/export', [BookingsController::class, 'exportBookings'])->name('bookings.export'); // <-- المسار الجديد للتصدير
    Route::get('/bookings/export-all', [BookingsController::class, 'exportAllBookings'])->name('bookings.export.all'); // <-- المسار الجديد لتصدير الكل

    Route::get('/bookings', [BookingsController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingsController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingsController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/details/{hotelId}', [BookingsController::class, 'details'])->name('bookings.details');
    Route::get('/bookings/{id}/edit', [BookingsController::class, 'edit'])->name('bookings.edit');

    Route::put('/bookings/{id}/update', [BookingsController::class, 'update'])->name('bookings.update');

    Route::get('/bookings/{id}/edits', [BookingsController::class, 'getEdits']);
    Route::get('/bookings/{id}/voucher', [BookingsController::class, 'voucher'])->name('bookings.voucher');

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
        // مسار صفحة الأرشيف:
        Route::get('/archived-bookings', [AdminController::class, 'archivedBookings'])->name('archived_bookings');

        Route::get('/archived-bookings/export', [AdminController::class, 'exportArchivedBookings'])->name('archived_bookings.export'); // الاسم النهائي: admin.archived_bookings.export

    });




    // إضافة هذه الروتس
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/company/{id}/bookings', [ReportController::class, 'companyBookings'])->name('reports.company.bookings');
    Route::get('/reports/agent/{id}/bookings', [ReportController::class, 'agentBookings'])->name('reports.agent.bookings');
    Route::get('/reports/hotel/{id}/bookings', [ReportController::class, 'hotelBookings'])->name('reports.hotel.bookings');
    Route::post('/reports/company/payment', [ReportController::class, 'storePayment'])->name('reports.company.payment');
    Route::post('/reports/agent/payment', [ReportController::class, 'storeAgentPayment'])->name('reports.agent.payment');

    // عرض سجل المدفوعات للشركات
    Route::get('reports/company/{id}/payments', [ReportController::class, 'companyPayments'])->name('reports.company.payments');

    // عرض سجل المدفوعات لجهات الحجز
    Route::get('reports/agent/{id}/payments', [ReportController::class, 'agentPayments'])->name('reports.agent.payments');

    // تعديل الدفعات
    Route::get('reports/payment/{id}/edit', [ReportController::class, 'editPayment'])->name('reports.payment.edit');
    Route::put('reports/payment/{id}', [ReportController::class, 'updatePayment'])->name('reports.payment.update');
    Route::get('reports/agent/payment/{id}/edit', [ReportController::class, 'editPayment'])->name('reports.agent.payment.edit');
    Route::put('reports/agent/payment/{id}', [ReportController::class, 'updatePayment'])->name('reports.agent.payment.update');

    // صفحة تعديل دفعة شركة
    Route::get('reports/company/payment/{id}/edit', [ReportController::class, 'editCompanyPayment'])
        ->name('reports.company.payment.edit');
    // معالجة تحديث دفعة شركة
    Route::put('reports/company/payment/{id}', [ReportController::class, 'updateCompanyPayment'])
        ->name('reports.company.payment.update');

    // حذف دفعة شركة
    Route::delete('reports/company/payment/{id}', [ReportController::class, 'destroyCompanyPayment'])
        ->name('reports.company.payment.destroy');

    // حذف دفعة وكيل
    Route::delete('reports/agent/payment/{id}', [ReportController::class, 'destroyAgentPayment'])
        ->name('reports.agent.payment.destroy');

    // عرض دفعة شركة
    Route::get('reports/company/payment/{id}', [ReportController::class, 'showCompanyPayment'])
        ->name('reports.company.payment.show');
});
// end of the auth middleware group
Route::get('/admin/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
Route::post('/admin/notifications/{id}/read', [AdminController::class, 'markNotificationRead'])->name('admin.notifications.markRead');
Route::post('/admin/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('admin.notifications.markAllRead');
Route::get('/api/notifications/unread-count', function () {
    return response()->json([
        'count' => \App\Models\Notification::where('is_read', false)->count()
    ]);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', function () {
    return view('welcome');
});

// مش راضي يشتغل على الهوستنجر عشان هما لسه لم يحدثوا الكومبوسر
// Auth::routes(); 
//  Route::get('/login', function () {
//     return view('welcome');
// })->name('login');
// حل يدوي
// كان في هنا مشكلة لو راح على راوتر اسه لوجين مش هيلاقيه عملته يدوي عشان يويدني صح
Route::get('/login', function () {
    return redirect('/'); // أو أي صفحة أنت عايزها
})->name('login');
Route::post('/manual-login', function (Request $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials, $request->filled('remember'))) {
        return redirect()->intended('/bookings');
    }
    return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة'])->withInput();
})->name('manual.login');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
