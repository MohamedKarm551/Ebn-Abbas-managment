<?php

namespace App\Http\Controllers;

use App\Models\LandTrip;
use App\Models\TripType;
use App\Models\RoomType;
use App\Models\Employee;
use App\Models\Agent;
use App\Models\Hotel;
use App\Models\Notification;
use App\Models\User;
use App\Models\LandTripRoomPrice;
use App\Models\LandTripBooking;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LandTripController extends Controller
{
    public function index(Request $request)
    {
        // تحديث حالة الرحلات المنتهية تلقائياً
        try {
            $expiredCount = LandTrip::where('status', '!=', 'expired')
                ->whereDate('return_date', '<', Carbon::today())
                ->update(['status' => 'expired']);

            if ($expiredCount > 0) {
                Log::info("تم تحديث {$expiredCount} رحلة منتهية إلى 'expired'.");

                Notification::create([
                    'type' => 'تحديث_تلقائي',
                    'message' => "تم تحديث {$expiredCount} رحلة منتهية تلقائياً إلى 'expired'.",
                ]);
            }
        } catch (\Exception $e) {
            Log::error("خطأ أثناء تحديث الرحلات المنتهية: " . $e->getMessage());
        }

        // بناء الاستعلام
        $query = LandTrip::with(['tripType', 'agent', 'employee'])->latest();

        // تطبيق الفلاتر
        if ($request->filled('trip_type_id')) {
            $query->where('trip_type_id', $request->input('trip_type_id'));
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->input('agent_id'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // البحث حسب التاريخ
        if ($request->filled('start_date')) {
            try {
                $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();
                $query->whereDate('departure_date', '>=', $startDate);
            } catch (\Exception $e) {
                Log::error("خطأ في تنسيق تاريخ البحث: " . $e->getMessage());
            }
        }

        if ($request->filled('end_date')) {
            try {
                $endDate = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay();
                $query->whereDate('return_date', '<=', $endDate);
            } catch (\Exception $e) {
                Log::error("خطأ في تنسيق تاريخ البحث: " . $e->getMessage());
            }
        }

        // تنفيذ الاستعلام مع التقسيم إلى صفحات
        $landTrips = $query->paginate(15)->withQueryString();

        // جلب البيانات اللازمة للفلاتر
        $tripTypes = TripType::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $hotels = Hotel::orderBy('name')->get(); // إضافة الفنادق
            // إضافة استعلام الحجوزات
    $allBookings = LandTripBooking::with(['landTrip', 'company', 'roomPrice'])->paginate(10)->withQueryString();

// SELECT * FROM `land_trip_bookings` WHERE  `id`, `land_trip_id`, `land_trip_room_price_id`, `client_name`, `company_id`, `rooms`, `cost_price`, `sale_price`, `amount_due_to_agent`, `amount_due_from_company`, `currency`, `notes`, `employee_id`, `created_at`, `updated_at`, `deleted_at`
        // حساب الإحصائيات بطريقة أكثر كفاءة
        $stats = [
            'totalTrips' => LandTrip::count(),
            'activeTrips' => LandTrip::where('status', 'active')->count(),
            'currentMonthTrips' => LandTrip::whereYear('departure_date', now()->year)
                ->whereMonth('departure_date', now()->month)
                ->count(),
            'totalBookings' => DB::table('land_trip_bookings')->count()
           
        ];

        return view('admin.land-trips.index', compact(
            'landTrips',
            'tripTypes',
            'agents',
            'employees',
           'allBookings',
            'hotels'
        ))->with($stats);
    }

    public function create()
    {
        $tripTypes = TripType::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $roomTypes = RoomType::orderBy('room_type_name')->get();
        $hotels = Hotel::orderBy('name')->get(); // إضافة الفنادق

        return view('admin.land-trips.create', compact('tripTypes', 'agents', 'employees', 'roomTypes', 'hotels'));
    }

    public function store(Request $request)
    {
        // التحقق من البيانات
        $validatedData = $request->validate([
            'trip_type_id' => 'required|exists:trip_types,id',
            'agent_id' => 'required|exists:agents,id',
            'hotel_id' => 'required|exists:hotels,id',
            'employee_id' => 'required|exists:employees,id',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:5000',

            // التحقق من البيانات المتعلقة بالغرف
            'room_types' => 'required|array|min:1',
            'room_types.*.room_type_id' => 'required|exists:room_types,id|distinct',
            'room_types.*.cost_price' => 'required|numeric|min:0',
            'room_types.*.sale_price' => 'required|numeric|min:0|gte:room_types.*.cost_price',
            'room_types.*.currency' => 'required|string|in:SAR,KWD',
            'room_types.*.allotment' => 'nullable|integer|min:1',
        ], [
            'trip_type_id.required' => 'يجب اختيار نوع الرحلة',
            'agent_id.required' => 'يجب اختيار جهة الحجز',
            'hotel_id.exists' => 'الفندق المحدد غير موجود', // رسالة الخطأ للفندق

            'employee_id.required' => 'يجب اختيار الموظف المسؤول',
            'departure_date.required' => 'تاريخ المغادرة مطلوب',
            'return_date.required' => 'تاريخ العودة مطلوب',
            'return_date.after_or_equal' => 'تاريخ العودة يجب أن يكون بعد أو يساوي تاريخ المغادرة',
            'status.required' => 'حالة الرحلة مطلوبة',
            'room_types.required' => 'يجب إضافة نوع غرفة واحد على الأقل',
            'room_types.min' => 'يجب إضافة نوع غرفة واحد على الأقل',
            'room_types.*.room_type_id.required' => 'يجب اختيار نوع الغرفة',
            'room_types.*.room_type_id.distinct' => 'لا يمكن تكرار نفس نوع الغرفة',
            'room_types.*.cost_price.required' => 'سعر التكلفة مطلوب',
            'room_types.*.cost_price.min' => 'سعر التكلفة يجب ألا يكون سالباً',
            'room_types.*.sale_price.required' => 'سعر البيع مطلوب',
            'room_types.*.sale_price.min' => 'سعر البيع يجب ألا يكون سالباً',
            'room_types.*.sale_price.gte' => 'سعر البيع يجب أن يكون أكبر من أو يساوي سعر التكلفة',
            'room_types.*.currency.required' => 'العملة مطلوبة',
            'room_types.*.currency.in' => 'العملة يجب أن تكون واحدة من: SAR, KWD',
            'room_types.*.allotment.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً',
            'room_types.*.allotment.min' => 'عدد الغرف يجب ألا يكون سالباً',
        ]);

        // تنسيق التواريخ
        try {
            $departureDate = self::parseDateFlexible($validatedData['departure_date']);
            $returnDate = self::parseDateFlexible($validatedData['return_date']);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'departure_date' => 'حدث خطأ في تنسيق التاريخ. يجب استخدام صيغة dd/mm/yyyy أو yyyy-mm-dd',
            ]);
        }

        // حساب عدد الأيام
        $daysCount = LandTrip::calculateDaysCount($departureDate, $returnDate);

        DB::beginTransaction();

        try {
            // إنشاء الرحلة
            $landTrip = LandTrip::create([
                'trip_type_id' => $validatedData['trip_type_id'],
                'agent_id' => $validatedData['agent_id'],
                'hotel_id' => $validatedData['hotel_id'], // إضافة حقل الفندق
                'employee_id' => $validatedData['employee_id'],
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
                'days_count' => $daysCount,
                'status' => $validatedData['status'],
                'notes' => $validatedData['notes'],
            ]);

            // إضافة أسعار الغرف
            if (isset($validatedData['room_types'])) {
                $roomPrices = [];
                foreach ($validatedData['room_types'] as $roomData) {
                    if (isset($roomData['room_type_id'], $roomData['cost_price'], $roomData['sale_price'])) {
                        $roomPrices[] = [
                            'land_trip_id' => $landTrip->id,
                            'room_type_id' => $roomData['room_type_id'],
                            'cost_price' => $roomData['cost_price'],
                            'sale_price' => $roomData['sale_price'],
                            'currency' => $roomData['currency'],
                            'allotment' => $roomData['allotment'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($roomPrices)) {
                    LandTripRoomPrice::insert($roomPrices);
                }
            }

            DB::commit();

            // إنشاء إشعار
            $landTrip->refresh(); // إعادة تحميل العلاقات
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "إضافة رحلة برية جديدة من " .
                    $landTrip->departure_date->format('d/m/Y') .
                    " إلى " .
                    $landTrip->return_date->format('d/m/Y') .
                    " (" .
                    $landTrip->tripType->name .
                    ")",
                'type' => 'إضافة رحلة',
            ]);

            return redirect()->route('admin.land-trips.index')
                ->with('success', 'تم إضافة الرحلة وأسعار الغرف بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("خطأ في إنشاء الرحلة: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->withInput()
                ->withErrors(['db_error' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage()]);
        }
    }

    public function show(LandTrip $landTrip)
    {
        // تحميل العلاقات
        $landTrip->loadMissing(['tripType', 'agent', 'employee', 'roomPrices.roomType', 'bookings.company']);

        // حساب إجماليات الحجوزات حسب نوع الغرفة
        $bookingSummary = [];
        foreach ($landTrip->roomPrices as $roomPrice) {
            $bookings = $roomPrice->bookings;
            $totalRooms = $bookings->sum('rooms');
            $totalAmount = $bookings->sum('amount_due_from_company');

            $bookingSummary[$roomPrice->id] = [
                'booked' => $totalRooms,
                'available' => $roomPrice->allotment ? $roomPrice->allotment - $totalRooms : null,
                'total_amount' => $totalAmount,
            ];
        }

        return view('admin.land-trips.show', compact('landTrip', 'bookingSummary'));
    }

    public function edit(LandTrip $landTrip)
    {
        // تحميل العلاقات
        $landTrip->loadMissing(['tripType', 'agent', 'employee', 'roomPrices.roomType']);

        $tripTypes = TripType::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $roomTypes = RoomType::orderBy('room_type_name')->get();
        $hotels = Hotel::orderBy('name')->get(); // إضافة الفنادق
        return view('admin.land-trips.edit', compact('landTrip', 'tripTypes', 'agents', 'hotels', 'employees', 'roomTypes'));
    }

    public function update(Request $request, LandTrip $landTrip)
    {
        // الاحتفاظ بالبيانات الأصلية قبل التحديث
        $originalData = $landTrip->getOriginal();
        $originalRoomPrices = $landTrip->roomPrices()->get()->keyBy('id')->toArray();

        // التحقق من البيانات
        $validatedData = $request->validate([
            'trip_type_id' => 'required|exists:trip_types,id',
            'agent_id' => 'required|exists:agents,id',
            'hotel_id' => 'required|exists:hotels,id', // إضافة حقل الفندق
            'employee_id' => 'required|exists:employees,id',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'status' => 'required|in:active,inactive' . ($landTrip->status === 'expired' ? ',expired' : ''),
            'notes' => 'nullable|string|max:5000',

            // التحقق من البيانات المتعلقة بالغرف
            'room_types' => 'required|array|min:1',
            'room_types.*.id' => 'sometimes|nullable|integer|exists:land_trip_room_prices,id,land_trip_id,' . $landTrip->id,
            'room_types.*.room_type_id' => 'required|exists:room_types,id|distinct',
            'room_types.*.cost_price' => 'required|numeric|min:0',
            'room_types.*.sale_price' => 'required|numeric|min:0|gte:room_types.*.cost_price',
            'room_types.*.currency' => 'required|string|in:SAR,KWD',
            'room_types.*.allotment' => 'nullable|integer|min:1',
        ], [
            'trip_type_id.required' => 'يجب اختيار نوع الرحلة',
            'agent_id.required' => 'يجب اختيار جهة الحجز',
            'hotel_id.exists' => 'الفندق المحدد غير موجود', // رسالة الخطأ للفندق
            'employee_id.required' => 'يجب اختيار الموظف المسؤول',
            'departure_date.required' => 'تاريخ المغادرة مطلوب',
            'return_date.required' => 'تاريخ العودة مطلوب',
            'return_date.after_or_equal' => 'تاريخ العودة يجب أن يكون بعد أو يساوي تاريخ المغادرة',
            'status.required' => 'حالة الرحلة مطلوبة',
            'room_types.required' => 'يجب إضافة نوع غرفة واحد على الأقل',
            'room_types.min' => 'يجب إضافة نوع غرفة واحد على الأقل',
            'room_types.*.room_type_id.required' => 'يجب اختيار نوع الغرفة',
            'room_types.*.room_type_id.distinct' => 'لا يمكن تكرار نفس نوع الغرفة',
            'room_types.*.cost_price.required' => 'سعر التكلفة مطلوب',
            'room_types.*.cost_price.min' => 'سعر التكلفة يجب ألا يكون سالباً',
            'room_types.*.sale_price.required' => 'سعر البيع مطلوب',
            'room_types.*.sale_price.min' => 'سعر البيع يجب ألا يكون سالباً',
            'room_types.*.sale_price.gte' => 'سعر البيع يجب أن يكون أكبر من أو يساوي سعر التكلفة',
            'room_types.*.currency.required' => 'العملة مطلوبة',
            'room_types.*.currency.in' => 'العملة يجب أن تكون واحدة من: SAR, KWD',
            'room_types.*.allotment.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً',
            'room_types.*.allotment.min' => 'عدد الغرف يجب ألا يكون سالباً',
        ]);

        // تنسيق التواريخ
        try {
            $departureDate = self::parseDateFlexible($validatedData['departure_date']);
            $returnDate = self::parseDateFlexible($validatedData['return_date']);
            // dd('صيغة التورايخ الآن', $departureDate, $returnDate);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'departure_date' => 'حدث خطأ في تنسيق التاريخ. يجب استخدام صيغة dd/mm/yyyy أو yyyy-mm-dd',
            ]);
        }

        // تحقق من حالة الرحلة المنتهية
        if ($landTrip->status === 'expired' && $validatedData['status'] !== 'expired') {
            // يمكن تغيير حالة 'expired' فقط إذا تم تمديد تاريخ العودة إلى المستقبل
            $returnDateObj = Carbon::parse($returnDate);
            if ($returnDateObj->isPast()) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تغيير حالة الرحلة المنتهية إلا إذا تم تعديل تاريخ العودة ليكون في المستقبل.',
                ]);
            }
        }

        // حساب عدد الأيام
        $daysCount = LandTrip::calculateDaysCount($departureDate, $returnDate);

        DB::beginTransaction();

        try {
            // تحديث الرحلة
            $landTrip->update([
                'trip_type_id' => $validatedData['trip_type_id'],
                'agent_id' => $validatedData['agent_id'],
                'hotel_id' => $validatedData['hotel_id'], // إضافة حقل الفندق
                'employee_id' => $validatedData['employee_id'],
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
                'days_count' => $daysCount,
                'status' => $validatedData['status'],
                'notes' => $validatedData['notes'],
            ]);

            // مزامنة أسعار الغرف
            $submittedRoomTypes = collect($validatedData['room_types'] ?? []);
            $existingRoomPriceIds = $landTrip->roomPrices()->pluck('id')->all();
            $submittedIds = $submittedRoomTypes->pluck('id')->filter()->all(); // فلتر القيم الفارغة (null)

            // الأسعار التي يجب حذفها
            $idsToDelete = array_diff($existingRoomPriceIds, $submittedIds);
            if (!empty($idsToDelete)) {
                LandTripRoomPrice::whereIn('id', $idsToDelete)
                    ->where('land_trip_id', $landTrip->id)
                    ->delete();
            }

            // تحديث أو إنشاء الأسعار
            foreach ($submittedRoomTypes as $roomData) {
                $priceData = [
                    'room_type_id' => $roomData['room_type_id'],
                    'cost_price' => $roomData['cost_price'],
                    'sale_price' => $roomData['sale_price'],
                    'currency' => $roomData['currency'],
                    'allotment' => $roomData['allotment'] ?? null,
                ];

                if (isset($roomData['id']) && !empty($roomData['id'])) {
                    // تحديث السعر الموجود
                    LandTripRoomPrice::where('id', $roomData['id'])
                        ->where('land_trip_id', $landTrip->id)
                        ->update($priceData);
                } else {
                    // البحث عن سعر موجود بنفس نوع الغرفة
                    $existing = $landTrip->roomPrices()
                        ->where('room_type_id', $roomData['room_type_id'])
                        ->first();

                    if (!$existing) {
                        // إنشاء سعر جديد
                        $priceData['land_trip_id'] = $landTrip->id;
                        LandTripRoomPrice::create($priceData);
                    } else {
                        // تحديث السعر الموجود
                        $existing->update($priceData);
                    }
                }
            }

            DB::commit();

            // إنشاء إشعار مع تفاصيل التغييرات
            $this->createUpdateNotification($landTrip, $originalData);

            return redirect()->route('admin.land-trips.index')
                ->with('success', 'تم تحديث الرحلة وأسعار الغرف بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("خطأ في تحديث الرحلة: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->withInput()
                ->withErrors(['db_error' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage()]);
        }
    }

    public function destroy(LandTrip $landTrip)
    {
        // التحقق من وجود حجوزات مرتبطة
        if ($landTrip->bookings()->exists()) {
            return redirect()->route('admin.land-trips.index')
                ->with('error', 'لا يمكن حذف الرحلة لوجود حجوزات مرتبطة بها. يمكنك تعديل حالتها إلى غير نشطة بدلاً من ذلك.');
        }

        // الإحتفاظ بمعلومات الرحلة للإشعار
        $tripInfo = [
            'id' => $landTrip->id,
            'departure_date' => $landTrip->departure_date->format('d/m/Y'),
            'return_date' => $landTrip->return_date->format('d/m/Y'),
            'trip_type' => $landTrip->tripType->name ?? 'غير معروف',
        ];

        DB::beginTransaction();

        try {
            // حذف أسعار الغرف أولاً (أو يمكن الاعتماد على onDelete cascade)
            $landTrip->roomPrices()->delete();

            // حذف الرحلة
            $landTrip->delete();

            DB::commit();

            // إنشاء إشعار
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "حذف رحلة برية (#{$tripInfo['id']}) من {$tripInfo['departure_date']} إلى {$tripInfo['return_date']} ({$tripInfo['trip_type']})",
                'type' => 'حذف رحلة',
            ]);

            return redirect()->route('admin.land-trips.index')
                ->with('success', 'تم حذف الرحلة بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("خطأ في حذف الرحلة: " . $e->getMessage());
            return redirect()->route('admin.land-trips.index')
                ->with('error', 'حدث خطأ أثناء محاولة حذف الرحلة: ' . $e->getMessage());
        }
    }

    public function showBookings(LandTrip $landTrip)
    {
        // جلب الحجوزات المرتبطة بالرحلة
        $bookings = $landTrip->bookings()
            ->with(['company', 'employee', 'roomPrice.roomType'])
            ->latest()
            ->paginate(20);

        return view('admin.land-trips.bookings', compact('landTrip', 'bookings'));
    }

    // دالة مساعدة لتنسيق التاريخ
    private static function parseDateFlexible($date)
    {
        // إذا كان التاريخ فارغ
        if (empty($date)) {
            throw new \Exception("التاريخ فارغ");
        }

        // إذا كان التاريخ بالفعل بالتنسيق yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // صيغة yyyy-mm-dd (مثل 2025-05-01)
            $parts = explode('-', $date);
            $year = (int)$parts[0];
            $month = (int)$parts[1];
            $day = (int)$parts[2];

            if (checkdate($month, $day, $year)) {
                return $date; // التاريخ بالصيغة الصحيحة بالفعل
            }
        }

        // محاولة تحويل التاريخ باستخدام Carbon
        try {
            // إضافة طباعة تشخيصية لفهم التنسيق
            Log::info("محاولة تحليل التاريخ: $date");

            $carbonDate = Carbon::parse($date);
            // التأكد من التنسيق
            $result = $carbonDate->format('Y-m-d');
            Log::info("تم تحليل التاريخ إلى: $result");
            return $result;
        } catch (\Exception $e) {
            Log::error("فشل في تحليل التاريخ: $date", ['error' => $e->getMessage()]);
            throw new \Exception("تنسيق التاريخ غير معتمد: $date. استخدم الصيغة YYYY-MM-DD فقط.");
        }
    }       // دالة لإنشاء إشعار مفصل بالتغييرات
    private function createUpdateNotification(LandTrip $landTrip, array $originalData)
    {
        $landTrip->refresh();
        $changes = [];

        // قاموس الحقول بالعربي
        $fieldNames = [
            'trip_type_id' => 'نوع الرحلة',
            'agent_id' => 'جهة الحجز',
            'hotel_id' => 'الفندق', // إضافة حقل الفندق
            'employee_id' => 'الموظف المسؤول',
            'departure_date' => 'تاريخ المغادرة',
            'return_date' => 'تاريخ العودة',
            'days_count' => 'عدد الأيام',
            'status' => 'الحالة',
            'notes' => 'الملاحظات',
        ];

        // قاموس الحالات بالعربي
        $statusMap = [
            'active' => 'نشطة',
            'inactive' => 'غير نشطة',
            'expired' => 'منتهية',
        ];

        // تتبع التغييرات في الحقول الأساسية
        foreach ($originalData as $key => $oldValue) {
            if (array_key_exists($key, $fieldNames) && $landTrip->$key != $oldValue) {
                $fieldLabel = $fieldNames[$key];
                $newValue = $landTrip->$key;

                // تنسيق القيم للعرض
                if ($key === 'trip_type_id') {
                    $oldTypeName = TripType::find($oldValue)?->name ?? $oldValue;
                    $newTypeName = $landTrip->tripType->name ?? $newValue;
                    $changes[] = "{$fieldLabel}: من \"{$oldTypeName}\" إلى \"{$newTypeName}\"";
                } elseif ($key === 'agent_id') {
                    $oldAgentName = Agent::find($oldValue)?->name ?? $oldValue;
                    $newAgentName = $landTrip->agent->name ?? $newValue;
                    $changes[] = "{$fieldLabel}: من \"{$oldAgentName}\" إلى \"{$newAgentName}\"";
                } elseif ($key === 'hotel_id') {
                    $oldHotelName = Hotel::find($oldValue)?->name ?? $oldValue;
                    $newHotelName = $landTrip->hotel->name ?? $newValue;
                    $changes[] = "{$fieldLabel}: من \"{$oldHotelName}\" إلى \"{$newHotelName}\"";
                } elseif ($key === 'employee_id') {
                    $oldEmployeeName = Employee::find($oldValue)?->name ?? $oldValue;
                    $newEmployeeName = $landTrip->employee->name ?? $newValue;
                    $changes[] = "{$fieldLabel}: من \"{$oldEmployeeName}\" إلى \"{$newEmployeeName}\"";
                } elseif (in_array($key, ['departure_date', 'return_date'])) {
                    $oldDate = Carbon::parse($oldValue)->format('d/m/Y');
                    $newDate = Carbon::parse($newValue)->format('d/m/Y');
                    $changes[] = "{$fieldLabel}: من \"{$oldDate}\" إلى \"{$newDate}\"";
                } elseif ($key === 'status') {
                    $oldStatus = $statusMap[$oldValue] ?? $oldValue;
                    $newStatus = $statusMap[$newValue] ?? $newValue;
                    $changes[] = "{$fieldLabel}: من \"{$oldStatus}\" إلى \"{$newStatus}\"";
                } else {
                    $changes[] = "{$fieldLabel}: تم التعديل";
                }
            }
        }

        // إذا لم تكن هناك تغييرات
        if (empty($changes)) {
            $changesList = "لم يتم تسجيل تغييرات في البيانات الأساسية.";
        } else {
            $changesList = implode("\n", $changes);
        }

        $message = "تعديل رحلة برية (#{$landTrip->id}) - {$landTrip->departure_date->format('d/m/Y')} إلى {$landTrip->return_date->format('d/m/Y')} بواسطة: " . Auth::user()->name . "\n\n" . $changesList;

        Notification::create([
            'user_id' => Auth::id(),
            'message' => $message,
            'type' => 'تعديل رحلة',
        ]);
    }

    public function createBooking(LandTrip $landTrip)
    {
        // التحقق من أن الرحلة نشطة
        if ($landTrip->status !== 'active') {
            return redirect()->route('admin.land-trips.show', $landTrip->id)
                ->with('error', 'لا يمكن إنشاء حجز لرحلة غير نشطة');
        }

        // تحميل العلاقات اللازمة
        $landTrip->loadMissing(['tripType', 'agent', 'hotel', 'employee', 'roomPrices.roomType']);

        // استعلام عن الشركات لعرضها في القائمة المنسدلة
        $companies = \App\Models\Company::orderBy('name')->get();

        // حساب الغرف المتاحة لكل نوع غرفة
        $roomAvailability = [];
        foreach ($landTrip->roomPrices as $roomPrice) {
            $bookedRooms = DB::table('land_trip_booking_room_price')
                ->where('land_trip_room_price_id', $roomPrice->id)
                ->sum('rooms');

            $availableRooms = $roomPrice->allotment ? $roomPrice->allotment - $bookedRooms : null;

            $roomAvailability[$roomPrice->id] = [
                'booked' => $bookedRooms,
                'available' => $availableRooms,
            ];
        }

        return view('admin.land-trips.create-booking', compact('landTrip', 'companies', 'roomAvailability'));
    }

    public function storeBooking(Request $request, LandTrip $landTrip)
    {
        // التحقق من أن الرحلة نشطة
        if ($landTrip->status !== 'active') {
            return redirect()->route('admin.land-trips.show', $landTrip->id)
                ->with('error', 'لا يمكن إنشاء حجز لرحلة غير نشطة');
        }

        // التحقق من البيانات
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'client_name' => 'required|string|max:255',
            'land_trip_room_price_id' => 'required|exists:land_trip_room_prices,id',
            'rooms' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ], [
            'company_id.required' => 'يرجى تحديد الشركة',
            'client_name.required' => 'اسم العميل مطلوب',
            'land_trip_room_price_id.required' => 'يرجى اختيار نوع الغرفة',
            'rooms.required' => 'يرجى تحديد عدد الغرف',
            'rooms.min' => 'عدد الغرف يجب أن يكون على الأقل 1',
        ]);

        // جلب سعر الغرفة المختارة
        $roomPrice = \App\Models\LandTripRoomPrice::findOrFail($validatedData['land_trip_room_price_id']);

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

        // حساب المبالغ
        $costPrice = $roomPrice->cost_price;
        $salePrice = $roomPrice->sale_price;
        $totalDueToAgent = $validatedData['rooms'] * $costPrice * $landTrip->days_count;
        $totalDueFromCompany = $validatedData['rooms'] * $salePrice * $landTrip->days_count;

        DB::beginTransaction();

        try {
            // إنشاء الحجز
            $booking = \App\Models\LandTripBooking::create([
                'land_trip_id' => $landTrip->id,
                'land_trip_room_price_id' => $roomPrice->id,
                'client_name' => strip_tags($validatedData['client_name']),
                'company_id' => $validatedData['company_id'],
                'employee_id' => Auth::id(), // الموظف الحالي هو من يقوم بالحجز
                'rooms' => $validatedData['rooms'],
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
                'amount_due_to_agent' => $totalDueToAgent,
                'amount_due_from_company' => $totalDueFromCompany,
                'currency' => $roomPrice->currency,
                'notes' => strip_tags($validatedData['notes'] ?? ''),
            ]);

            // إضافة السجل في الجدول الوسيط
            DB::table('land_trip_booking_room_price')->insert([
                'land_trip_room_price_id' => $roomPrice->id,
                'booking_id' => $booking->id,
                'rooms' => $validatedData['rooms'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // إنشاء إشعار
            $company = \App\Models\Company::find($validatedData['company_id']);
            $notificationMessage = "تم إضافة حجز جديد للرحلة رقم: {$landTrip->id} للعميل: {$validatedData['client_name']} من شركة: {$company->name}. عدد الغرف: {$validatedData['rooms']}";

            \App\Models\Notification::create([
                'user_id' => Auth::id(),
                'message' => $notificationMessage,
                'type' => 'حجز رحلة',
            ]);

            DB::commit();

            return redirect()->route('admin.land-trips.bookings', $landTrip->id)
                ->with('success', 'تم إنشاء الحجز بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("خطأ في إنشاء الحجز: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الحجز: ' . $e->getMessage());
        }
    }
}
