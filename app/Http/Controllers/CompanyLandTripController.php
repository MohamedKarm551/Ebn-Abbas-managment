<?php

namespace App\Http\Controllers;

use App\Models\LandTrip;
use App\Models\TripType;
use App\Models\LandTripRoomPrice;
use App\Models\LandTripBooking;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\User;
use App\Models\Company;

class CompanyLandTripController extends Controller
{
    public function index(Request $request)
    {
        // استعلام الرحلات النشطة فقط
        $query = LandTrip::with(['tripType', 'agent', 'employee', 'hotel'])
            ->where('status', 'active')
            ->whereDate('return_date', '>=', Carbon::today());

        // تطبيق الفلاتر
        if ($request->filled('trip_type_id')) {
            $query->where('trip_type_id', $request->input('trip_type_id'));
        }

        // البحث حسب التاريخ - تحويل التنسيق من Y-m-d إلى Carbon
        if ($request->filled('start_date')) {
            try {
                // تجربة تنسيق yyyy-mm-dd أولا
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay();
                $query->whereDate('departure_date', '>=', $startDate);
            } catch (\Exception $e) {
                try {
                    // إذا فشل، جرب تنسيق dd/mm/yyyy
                    $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();
                    $query->whereDate('departure_date', '>=', $startDate);
                } catch (\Exception $e2) {
                    Log::error("خطأ في تنسيق تاريخ البحث: " . $e2->getMessage());
                }
            }
        }

        if ($request->filled('end_date')) {
            try {
                // تجربة تنسيق yyyy-mm-dd أولا
                $endDate = Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay();
                $query->whereDate('return_date', '<=', $endDate);
            } catch (\Exception $e) {
                try {
                    // إذا فشل، جرب تنسيق dd/mm/yyyy
                    $endDate = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay();
                    $query->whereDate('return_date', '<=', $endDate);
                } catch (\Exception $e2) {
                    Log::error("خطأ في تنسيق تاريخ البحث: " . $e2->getMessage());
                }
            }
        }

        // تنفيذ الاستعلام مع التقسيم إلى صفحات
        $landTrips = $query->latest()->paginate(15)->withQueryString();

        // جلب البيانات اللازمة للفلاتر
        $tripTypes = \App\Models\TripType::orderBy('name')->get();

        return view('company.land-trips.index', compact('landTrips', 'tripTypes'));
    }

    public function show(LandTrip $landTrip)
    {
        // التحقق من أن الرحلة نشطة
        if ($landTrip->status !== 'active') {
            return redirect()->route('company.land-trips.index')
                ->with('error', 'هذه الرحلة غير متاحة للحجز');
        }

        // تحميل العلاقات
        $landTrip->loadMissing(['tripType', 'agent', 'hotel', 'employee', 'roomPrices.roomType']);

        // تجهيز معلومات الغرف
        $roomInfo = [];
        foreach ($landTrip->roomPrices as $roomPrice) {
            $booked = $roomPrice->bookings()->sum('rooms');
            $available = $roomPrice->allotment ? max(0, $roomPrice->allotment - $booked) : null;

            $roomInfo[] = [
                'id' => $roomPrice->id,
                'room_type' => $roomPrice->roomType->room_type_name,
                'price' => $roomPrice->sale_price,
                'available' => $available,
                'disabled' => $available === 0 && $roomPrice->allotment !== null,
            ];
        }

        // جلب الحجوزات السابقة للشركة في هذه الرحلة
        $companyBookings = LandTripBooking::where('land_trip_id', $landTrip->id)
            ->where('company_id', Auth::user()->company_id)
            ->with(['roomPrice.roomType'])
            ->get();

        return view('company.land-trips.show', compact('landTrip', 'roomInfo', 'companyBookings'));
    }

    public function book(Request $request, LandTrip $landTrip)
    {
        // التحقق من أن الرحلة نشطة
        if ($landTrip->status !== 'active') {
            return redirect()->route('company.land-trips.index')
                ->with('error', 'هذه الرحلة غير متاحة للحجز');
        }

        // التحقق من البيانات
        $validatedData = $request->validate([
            'land_trip_room_price_id' => 'required|exists:land_trip_room_prices,id',
            'client_name' => 'required|string|max:255',
            'rooms' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'employee_id' => 'required|exists:employees,id', // التحقق من employee_id

        ], [
            'land_trip_room_price_id.required' => 'يجب اختيار نوع الغرفة',
            'client_name.required' => 'اسم العميل مطلوب',
            'rooms.required' => 'عدد الغرف مطلوب',
            'rooms.min' => 'عدد الغرف يجب أن يكون على الأقل 1',
        ]);

        // جلب سعر الغرفة المختارة
        $roomPrice = LandTripRoomPrice::findOrFail($validatedData['land_trip_room_price_id']);

        // التحقق من أن سعر الغرفة ينتمي للرحلة المحددة
        if ($roomPrice->land_trip_id !== $landTrip->id) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['land_trip_room_price_id' => 'بيانات الحجز غير صحيحة']);
        }

        // التحقق من توفر الغرف إذا كان هناك حد أقصى
        if ($roomPrice->allotment !== null) {
            $bookedRooms = DB::table('land_trip_booking_room_price')
                ->where('land_trip_room_price_id', $roomPrice->id)
                ->sum('rooms');
            $availableRooms = max(0, $roomPrice->allotment - $bookedRooms);

            if ($validatedData['rooms'] > $availableRooms) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['rooms' => "لا يوجد عدد كافٍ من الغرف المتاحة. العدد المتاح: {$availableRooms}"]);
            }
        }

        // إضافة وقت إضافي للطلب للتأكد من إنهائه
        set_time_limit(120);

        // حساب المبالغ
        $costPrice = $roomPrice->cost_price;
        $salePrice = $roomPrice->sale_price;
        $totalDueToAgent = $validatedData['rooms'] * $costPrice * $landTrip->days_count;
        $totalDueFromCompany = $validatedData['rooms'] * $salePrice * $landTrip->days_count;

        DB::beginTransaction();

        try {
            // إنشاء الحجز
            $booking = LandTripBooking::create([
                'land_trip_id' => $landTrip->id,
                'land_trip_room_price_id' => $roomPrice->id,
                'client_name' => strip_tags($validatedData['client_name']),
                'company_id' => Auth::user()->company_id,
                'employee_id' => $validatedData['employee_id'], // إضافة employee_id
                'rooms' => $validatedData['rooms'],
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
                'amount_due_to_agent' => $totalDueToAgent,
                'amount_due_from_company' => $totalDueFromCompany,
                'currency' => $roomPrice->currency, // إضافة حقل العملة
                'notes' => strip_tags($validatedData['notes']),
            ]);

            // تأكد من إنشاء الجدول الوسيط في قاعدة البيانات
            DB::table('land_trip_booking_room_price')->insert([
                'land_trip_room_price_id' => $roomPrice->id,
                'booking_id' => $booking->id,
                'rooms' => $validatedData['rooms'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // إنشاء إشعار للأدمن والموظف المسؤول
            $notificationMessage = "تم إضافة حجز جديد للرحلة رقم: {$landTrip->id} من شركة: " . Auth::user()->company->name . " القائم بالحجز " . Auth::user()->name .
                " للعميل: {$validatedData['client_name']}. عدد الغرف: {$validatedData['rooms']}";

            // إشعار للمدير
            Notification::create([
                'user_id' => Auth::id(),
                'message' => $notificationMessage,
                'type' => 'حجز رحلة',
            ]);

            // إشعار للموظف المسؤول عن الرحلة
         if ($landTrip->employee_id) {
    // 1. جلب الموظف المسؤول أولاً
    $employee = Employee::find($landTrip->employee_id);
    
    // 2. التحقق من وجود حساب مستخدم مرتبط بالموظف
    if ($employee && $employee->user_id) {
        // إرسال إشعار للمستخدم المرتبط بالموظف
        Notification::create([
            'user_id' => $employee->user_id,
            'message' => $notificationMessage,
            'title' => 'حجز جديد في رحلتك',
            'type' => 'حجز رحلة'
        ]);
        
        Log::info("تم إرسال إشعار للموظف المسؤول: {$employee->name}");
    } else {
        Log::warning("الموظف المسؤول {$employee->name} ليس له حساب مستخدم مرتبط");
        
        // إرسال إشعار للمدراء عن المشكلة
        $adminUsers = User::where('role', 'Admin')->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'موظف بدون حساب مستخدم',
                'message' => "الموظف المسؤول ({$employee->name}) عن الرحلة {$landTrip->id} ليس له حساب مستخدم مرتبط.",
                'type' => 'تنبيه نظام'
            ]);
        }
    }
}


            // توجيه إلى صفحة الفاتورة
            return redirect()->route('company.land-trips.voucher', $booking->id)
                ->with('success', 'تمت إضافة الحجز بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("خطأ في إنشاء الحجز: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الحجز: ' . $e->getMessage());
        }
    }
    public function voucher(LandTripBooking $booking)
    {
        // التأكد من أن الحجز يخص الشركة الحالية
        if ($booking->company_id != Auth::user()->company_id) {
            return redirect()->route('company.land-trips.index')
                ->with('error', 'لا يمكنك الوصول إلى هذا الحجز');
        }

        // تحميل العلاقات اللازمة
        $booking->load(['landTrip.tripType', 'landTrip.agent', 'landTrip.employee', 'roomPrice.roomType']);

        return view('company.land-trips.voucher', compact('booking'));
    }

    public function downloadVoucher(LandTripBooking $booking)
    {
        // التأكد من أن الحجز يخص الشركة الحالية
        if ($booking->company_id != Auth::user()->company_id) {
            return redirect()->route('company.land-trips.index')
                ->with('error', 'لا يمكنك الوصول إلى هذا الحجز');
        }

        // تحميل العلاقات اللازمة
        $booking->load(['landTrip.tripType', 'landTrip.agent', 'landTrip.hotel', 'landTrip.employee', 'roomPrice.roomType', 'company']);

        // عرض صفحة خاصة ستقوم بتحويل HTML إلى PDF في المتصفح
        return view('company.land-trips.voucher-view', compact('booking'));
    }
    public function myBookings(Request $request)
{
    $query = LandTripBooking::with(['landTrip.tripType', 'landTrip.agent', 'landTrip.hotel', 'landTrip.employee', 'roomPrice.roomType'])
        ->where('company_id', Auth::user()->company_id)
        ->latest();
    
    // تطبيق فلاتر البحث
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('client_name', 'like', "%{$search}%")
              ->orWhereHas('landTrip', function($q2) use ($search) {
                  $q2->where('id', 'like', "%{$search}%");
              });
        });
    }
    
    if ($request->filled('start_date')) {
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();
            $query->whereHas('landTrip', function($q) use ($startDate) {
                $q->whereDate('departure_date', '>=', $startDate);
            });
        } catch (\Exception $e) {
            Log::error("خطأ في تنسيق تاريخ البداية: " . $e->getMessage());
        }
    }
    
    if ($request->filled('end_date')) {
        try {
            $endDate = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay();
            $query->whereHas('landTrip', function($q) use ($endDate) {
                $q->whereDate('return_date', '<=', $endDate);
            });
        } catch (\Exception $e) {
            Log::error("خطأ في تنسيق تاريخ النهاية: " . $e->getMessage());
        }
    }
    
    if ($request->filled('trip_type_id')) {
        $query->whereHas('landTrip', function($q) use ($request) {
            $q->where('trip_type_id', $request->input('trip_type_id'));
        });
    }

    if ($request->filled('status')) {
        $today = Carbon::today();
        if ($request->input('status') === 'upcoming') {
            $query->whereHas('landTrip', function($q) use ($today) {
                $q->whereDate('departure_date', '>=', $today);
            });
        } elseif ($request->input('status') === 'current') {
            $query->whereHas('landTrip', function($q) use ($today) {
                $q->whereDate('departure_date', '<=', $today)
                  ->whereDate('return_date', '>=', $today);
            });
        } elseif ($request->input('status') === 'past') {
            $query->whereHas('landTrip', function($q) use ($today) {
                $q->whereDate('return_date', '<', $today);
            });
        }
    }
    
  
    // جلب تفاصيل المدفوعات حسب العملة
    $paymentsByСurrency = LandTripBooking::where('company_id', Auth::user()->company_id)
        ->select('currency', DB::raw('SUM(amount_due_from_company) as total'))
        ->groupBy('currency')
        ->get()
        ->pluck('total', 'currency')
        ->toArray();

    // عمل قاموس لرموز العملات
    $currencySymbols = [
        'SAR' => 'ر.س',
        'KWD' => 'دينار كويتي',
        'USD' => '$',
        'EUR' => '€',
    ];
    
    // حساب إحصائيات 
    $stats = [
        'totalBookings' => LandTripBooking::where('company_id', Auth::user()->company_id)->count(),
        'upcomingBookings' => LandTripBooking::where('company_id', Auth::user()->company_id)
            ->whereHas('landTrip', function($q) {
                $q->whereDate('departure_date', '>=', Carbon::today());
            })->count(),
        'currentMonthBookings' => LandTripBooking::where('company_id', Auth::user()->company_id)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count(),
        'totalSpent' => LandTripBooking::where('company_id', Auth::user()->company_id)
            ->sum('amount_due_from_company'),
        'paymentsByCurrency' => $paymentsByСurrency,
        'currencySymbols' => $currencySymbols
    ];
    
    $bookings = $query->paginate(10)->withQueryString();
    $tripTypes = TripType::orderBy('name')->get();
    return view('company.land-trips.my-bookings', compact('bookings', 'tripTypes', 'stats'));
}
}
