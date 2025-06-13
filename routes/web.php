<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\CompanyAvailabilityController;
use App\Http\Controllers\LandTripController;
use App\Http\Controllers\CompanyLandTripController;
use App\Http\Controllers\TripTypeController;
use App\Http\Controllers\HotelRoomController;
use App\Models\User;
use Jenssegers\Agent\Agent;
use App\Models\Notification;

Route::middleware(['auth'])->group(function () {
    // تصدير جدول الحجز
    Route::get('/bookings/export', [BookingsController::class, 'exportBookings'])->name('bookings.export'); // <-- المسار الجديد للتصدير
    Route::get('/bookings/export-all', [BookingsController::class, 'exportAllBookings'])->name('bookings.export.all'); // <-- المسار الجديد لتصدير الكل
    // Route جديد عشان يجيب اقتراحات البحث
    Route::get('/bookings/autocomplete', [BookingsController::class, 'autocomplete'])->name('bookings.autocomplete');

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

    Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\IsNotCompany::class])->group(function () {
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
        Route::get('/archived-bookings/autocomplete', [AdminController::class, 'archivedAutocomplete'])->name('archived_bookings.autocomplete');

        // Download archived bookings
        Route::get('/archived-bookings/export', [AdminController::class, 'exportArchivedBookings'])->name('archived_bookings.export'); // الاسم النهائي: admin.archived_bookings.export
        // إدارة أنواع الغرف
        Route::resource('room_types', RoomTypeController::class); // <-- أضف هذا

        // إدارة الإتاحات
        Route::resource('availabilities', AvailabilityController::class);
    });
    // مسارات إدارة غرف الفنادق
    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {

        Route::get('/hotel-rooms', [HotelRoomController::class, 'index'])->name('hotel.rooms.index');
        Route::get('/hotel-rooms/hotel/{id}', [HotelRoomController::class, 'showHotel'])->name('hotel.rooms.hotel');
        Route::get('/hotel-rooms/{id}', [HotelRoomController::class, 'showRoom'])->name('hotel.rooms.show');
        Route::post('/hotel-rooms/create', [HotelRoomController::class, 'createRooms'])->name('hotel.rooms.create');
        Route::post('/hotel-rooms/assign', [HotelRoomController::class, 'assignRoom'])->name('hotel.rooms.assign');
        // 1. عرض صفحة نموذج تعديل غرفة واحدة
        Route::get('/hotels/rooms/{room}/edit', [HotelRoomController::class, 'edit'])->name('hotel.rooms.edit');

        // 2. استقبال عملية الحفظ بعد التعديل
        Route::patch('/hotels/rooms/{room}', [HotelRoomController::class, 'update'])->name('hotel.rooms.update');

        // 3. حذف غرفة نهائيًا
        Route::delete('/hotels/rooms/{room}', [HotelRoomController::class, 'destroy'])->name('hotel.rooms.destroy');
        Route::patch('/hotel-rooms/end-assignment/{id}', [HotelRoomController::class, 'endAssignment'])->name('hotel.rooms.end-assignment');
        Route::post('/hotel-rooms/assign-multiple', [HotelRoomController::class, 'assignMultipleRooms'])->name('hotel.rooms.assign-multiple');
        Route::post('/hotel-rooms/add-guest', [HotelRoomController::class, 'addGuest'])->name('hotel.rooms.add-guest');
    });
    // *** مجموعة روتات الشركات ***
    // *** هنشيل middleware(['isCompany']) من هنا مؤقتاً ***
    Route::prefix('company')->name('company.')->group(function () {
        // عرض الإتاحات للشركة
        Route::get('/availabilities', [CompanyAvailabilityController::class, 'index'])->name('availabilities.index');
        // يمكنك إضافة روتات أخرى خاصة بالشركات هنا (مثل الداشبورد، عرض الحجوزات الخاصة بهم، إلخ)
        // Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');
    }); // *** نهاية مجموعة روتات الشركات ***





    // تطبيق ميدل وير 'AdminMiddleware' للتحقق من دور المستخدم
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {

        //     عرض الرواتس التالية  أدمن فقط ميدل وير

        Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('/reports/advanced/{date?}', [ReportController::class, 'advanced'])->name('reports.advanced');
        Route::get('/reports/company/{id}/bookings', [ReportController::class, 'companyBookings'])->name('reports.company.bookings');
        Route::get('/reports/agent/{id}/bookings', [ReportController::class, 'agentBookings'])->name('reports.agent.bookings');
        Route::get('/reports/hotel/{id}/bookings', [ReportController::class, 'hotelBookings'])->name('reports.hotel.bookings');
        Route::post('/reports/company/payment', [ReportController::class, 'storePayment'])->name('reports.company.payment');
        Route::post('/reports/agent/payment', [ReportController::class, 'storeAgentPayment'])->name('reports.agent.payment');

        // عرض سجل المدفوعات للشركات
        Route::get('reports/company/{id}/payments', [ReportController::class, 'companyPayments'])->name('reports.company.payments');

        // عرض سجل المدفوعات لجهات الحجز
        Route::get('reports/agent/{id}/payments', [ReportController::class, 'agentPayments'])->name('reports.agent.payments');

        // تعديل الدفعات (المسارات القديمة - قد تحتاج للمراجعة أو الحذف إذا لم تعد مستخدمة)
        // Route::get('reports/payment/{id}/edit', [ReportController::class, 'editPayment'])->name('reports.payment.edit');
        // Route::put('reports/payment/{id}', [ReportController::class, 'updatePayment'])->name('reports.payment.update');
        // Route::get('reports/agent/payment/{id}/edit', [ReportController::class, 'editPayment'])->name('reports.agent.payment.edit');
        // Route::put('reports/agent/payment/{id}', [ReportController::class, 'updatePayment'])->name('reports.agent.payment.update');

        // صفحة تعديل دفعة شركة
        Route::get('reports/company/payment/{id}/edit', [ReportController::class, 'editCompanyPayment'])
            ->name('reports.company.payment.edit');
        // معالجة تحديث دفعة شركة
        Route::put('reports/company/payment/{id}', [ReportController::class, 'updateCompanyPayment'])
            ->name('reports.company.payment.update');

        // حذف دفعة شركة
        Route::delete('reports/company/payment/{id}', [ReportController::class, 'destroyCompanyPayment'])
            ->name('reports.company.payment.destroy');

        // صفحة تعديل دفعة وكيل (إذا كانت مختلفة عن الشركة) - تأكد من وجود هذه الدوال في الكنترولر
        Route::get('reports/agent/payment/{id}/edit', [ReportController::class, 'editAgentPayment']) // تأكد من اسم الدالة
            ->name('reports.agent.payment.edit');
        // معالجة تحديث دفعة وكيل
        Route::put('reports/agent/payment/{id}', [ReportController::class, 'updateAgentPayment']) // تأكد من اسم الدالة
            ->name('reports.agent.payment.update');

        // حذف دفعة وكيل
        Route::delete('reports/agent/payment/{id}', [ReportController::class, 'destroyAgentPayment'])
            ->name('reports.agent.payment.destroy');

        // عرض دفعة شركة (قد لا تحتاجها إذا كان العرض ضمن صفحة السجل)
        // Route::get('reports/company/payment/{id}', [ReportController::class, 'showCompanyPayment'])
        //     ->name('reports.company.payment.show');

        // عرض دفعة وكيل (قد لا تحتاجها إذا كان العرض ضمن صفحة السجل)
        // Route::get('reports/agent/payment/{id}', [ReportController::class, 'showAgentPayment'])
        //     ->name('reports.agent.payment.show');
        // إضافة مسار لعرض مخطط العلاقات
        Route::get('/network-graph', [ReportController::class, 'networkGraph'])->name('network.graph');
        Route::get('/network-data', [ReportController::class, 'getNetworkData'])->name('network.data');
        // روتات إدارة المصاريف الشهرية
        Route::get('/admin/monthly-expenses', [App\Http\Controllers\MonthlyExpenseController::class, 'index'])
            ->name('admin.monthly-expenses.index');
        Route::post('/admin/monthly-expenses', [App\Http\Controllers\MonthlyExpenseController::class, 'store'])
            ->name('admin.monthly-expenses.store');
        Route::post('/admin/calculate-profit', [App\Http\Controllers\MonthlyExpenseController::class, 'calculateProfit'])
            ->name('admin.calculate-profit');
        Route::get('/admin/monthly-expenses/{id}', [App\Http\Controllers\MonthlyExpenseController::class, 'show'])
            ->name('admin.monthly-expenses.show');
        Route::delete('/admin/monthly-expenses/{id}', [App\Http\Controllers\MonthlyExpenseController::class, 'destroy'])
            ->name('admin.monthly-expenses.destroy');
    });
    Route::post('/save-screenshot', [\App\Http\Controllers\ReportController::class, 'saveScreenshot']);
    Route::post('/save-pdf', [\App\Http\Controllers\ReportController::class, 'savePDF']);
    // ==================================================
    // *** نهاية مجموعة روتات التقارير والدفعات (أدمن فقط) ***
    // ==================================================


    // end of the auth middleware group
    // لو انت مش شركة اسمح بدول : 
    Route::middleware([\App\Http\Middleware\IsNotCompany::class])->group(function () {

        Route::get('/admin/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
        Route::post('/admin/notifications/{id}/read', [AdminController::class, 'markNotificationRead'])->name('admin.notifications.markRead');
        Route::post('/admin/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('admin.notifications.markAllRead');
        Route::get('/api/notifications/unread-count', function () {
            return response()->json([
                'count' => \App\Models\Notification::where('is_read', false)->count()
            ]);
        })->name('api.notifications.unread_count'); // اسم مقترح للروت

        // ==================================================
        // *** نهاية مجموعة روتات الإشعارات (لغير الشركات) ***
        // ==================================================


    }); // <--- نهاية المجموعة الرئيسية للمستخدمين المسجلين
}); // <--- نهاية مجموعة روتات الإشعارات (لغير الشركات)
// ==================================================
// روتات إدارة الرحلات (للأدمن والموظفين)
Route::middleware(['auth', \App\Http\Middleware\AdminOrEmployeeMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('land-trips', LandTripController::class);
    Route::resource('trip-types', TripTypeController::class)->except('show');

    Route::get('land-trips/{land_trip}/bookings', [LandTripController::class, 'showBookings'])->name('land-trips.bookings');
    // إضافة مسارات إنشاء الحجوزات
    Route::get('land-trips/{land_trip}/create-booking', [LandTripController::class, 'createBooking'])->name('land-trips.create-booking');
    Route::post('land-trips/{land_trip}/store-booking', [LandTripController::class, 'storeBooking'])->name('land-trips.store-booking');
});
// مسارات ربط الموظفين بالمستخدمين
Route::middleware(['auth', \App\Http\Middleware\AdminOrEmployeeMiddleware::class])->group(function () {
    Route::post('/admin/employees/create-user', [AdminController::class, 'createEmployeeUser'])->name('admin.createEmployeeUser');
    Route::post('/admin/employees/link-user', [AdminController::class, 'linkEmployeeUser'])->name('admin.linkEmployeeUser');
    Route::delete('/admin/employees/{employee}/unlink-user', [AdminController::class, 'unlinkEmployeeUser'])->name('admin.unlinkEmployeeUser');
});
// روتات الشركات للحجز
Route::middleware(['auth', \App\Http\Middleware\IsCompany::class])->prefix('company')->name('company.')->group(function () {
    // الرحلات البرية
    Route::get('land-trips', [CompanyLandTripController::class, 'index'])->name('land-trips.index');
    Route::get('land-trips/{landTrip}', [CompanyLandTripController::class, 'show'])->name('land-trips.show');
    Route::post('land-trips/{landTrip}/book', [CompanyLandTripController::class, 'book'])->name('land-trips.book');
    Route::get('my-bookings', [CompanyLandTripController::class, 'myBookings'])->name('land-trips.my-bookings');
    Route::get('land-trips/booking/{booking}/voucher', [CompanyLandTripController::class, 'voucher'])->name('land-trips.voucher');
    Route::get('land-trips/booking/{booking}/download-voucher', [CompanyLandTripController::class, 'downloadVoucher'])->name('land-trips.downloadVoucher');
});
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', function () {
    return view('welcome');
});

// مش راضي يشتغل على الهوستنجر عشان هما لسه لم يحدثوا الكومبوسر
Auth::routes();
//  Route::get('/login', function () {
//     return view('welcome');
// })->name('login');
// حل يدوي
// كان في هنا مشكلة لو راح على راوتر اسه لوجين مش هيلاقيه عملته يدوي عشان يويدني صح
// Route::get('/register', function(){
//      return view('auth.register');
//    // return view('welcome');
// })->name('register');


Route::get('/login', function () {
    return redirect('/');
})->name('login');
Route::post('/manual-login', function (Request $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials, $request->filled('remember'))) {
        $user = Auth::user();
        $agent = new Agent();
        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        // $isMobile = $agent->isMobile();
        // $isTablet = $agent->isTablet();
        // $isDesktop = $agent->isDesktop();
        $ip = $request->ip();
        if ($user) {
            // بدلاً من إنشاء إشعار للمستخدم نفسه، نبحث عن المدراء ونرسل لهم
            $adminUsers = \App\Models\User::where('role', 'Admin')->get();
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'تسجيل دخول',
                    'message' => "تم تسجيل دخول المستخدم {$user->name} من جهاز {$device} ونظام {$platform} ومتصفح {$browser} من IP: {$ip}",
                    'type' => 'login',
                ]);
            }
        }
        return redirect()->intended('/bookings');
    }
    return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة'])->withInput();
})->name('manual.login');

Route::post('/logout', function (Request $request) {
    $user = Auth::user();
    $agent = new Agent();
    $device = $agent->device();
    $platform = $agent->platform();
    $browser = $agent->browser();
    $ip = $request->ip();

    if ($user) {
        // بدلاً من إنشاء إشعار للمستخدم نفسه، نبحث عن المدراء ونرسل لهم
        $adminUsers = User::where('role', 'Admin')->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'تسجيل خروج',
                'message' => "تم تسجيل خروج المستخدم {$user->name} من جهاز {$device} ونظام {$platform} ومتصفح {$browser} من IP: {$ip}",
                'type' => 'logout',
            ]);
        }
    }
    Auth::logout();
    return redirect('/');
})->name('logout');
Route::post('/devtools-logout', function () {
    if (Auth::check()) {
        \App\Models\Notification::create([
            'user_id' => Auth::id(),
            'message' => "محاولة فحص الصفحة أو التعديل عبر أدوات المطور.",
            'type' => 'تنبيه أمني',
        ]);
        Auth::logout();
    }
    return response()->json(['status' => 'ok']);
})->name('devtools.logout');
