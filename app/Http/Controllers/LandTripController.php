<?php

namespace App\Http\Controllers;

use App\Models\LandTrip;
use App\Models\TripType;
use App\Models\RoomType;
use App\Models\Employee;
use App\Models\Agent;
use App\Models\Hotel;
use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use App\Models\LandTripRoomPrice;
use App\Models\LandTripBooking;
use App\Models\LandTripEdit;
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
        // إضافة استعلام الحجوزات وترتيب حسب الأحدث
        $allBookings = LandTripBooking::with(['landTrip', 'company', 'roomPrice'])->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

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

        DB::beginTransaction(); // بدء المعاملة

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

        $edits = LandTripEdit::where('land_trip_id', $landTrip->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.land-trips.show', compact('landTrip', 'bookingSummary', 'edits'));
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
        // dd($request->all()); // <--- أضف هذا السطر هنا

        // الاحتفاظ بالبيانات الأصلية قبل التحديث
        $originalData = $landTrip->getOriginal();
        $originalRoomPrices = $landTrip->roomPrices()->get()->keyBy('id')->toArray();

        // التحقق من البيانات
        $validatedData = $request->validate([
            'trip_type_id' => 'required|exists:trip_types,id',
            'agent_id' => 'required|exists:agents,id',
            'hotel_id' => 'required|exists:hotels,id',
            'employee_id' => 'required|exists:employees,id',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'status' => 'required|in:active,inactive' . ($landTrip->status === 'expired' ? ',expired' : ''),
            'notes' => 'nullable|string|max:5000',

            'room_types' => 'required|array|min:1',
            'room_types.*.action' => 'sometimes|string|in:new,update,delete,keep',
            'room_types.*.id' => 'sometimes|nullable|integer|exists:land_trip_room_prices,id,land_trip_id,' . $landTrip->id,
            'room_types.*.room_type_id' => 'required|exists:room_types,id', // distinct معالج أدناه
            'room_types.*.cost_price' => 'required|numeric|min:0',
            'room_types.*.sale_price' => 'required|numeric|min:0|gte:room_types.*.cost_price',
            'room_types.*.currency' => 'required|string|in:SAR,KWD',
            'room_types.*.allotment' => 'nullable|integer|min:1',
        ], [
            'trip_type_id.required' => 'يجب اختيار نوع الرحلة',
            'agent_id.required' => 'يجب اختيار جهة الحجز',
            'hotel_id.exists' => 'الفندق المحدد غير موجود',
            'employee_id.required' => 'يجب اختيار الموظف المسؤول',
            'departure_date.required' => 'تاريخ المغادرة مطلوب',
            'return_date.required' => 'تاريخ العودة مطلوب',
            'return_date.after_or_equal' => 'تاريخ العودة يجب أن يكون بعد أو يساوي تاريخ المغادرة',
            'status.required' => 'حالة الرحلة مطلوبة',
            'room_types.required' => 'يجب إضافة نوع غرفة واحد على الأقل',
            'room_types.min' => 'يجب إضافة نوع غرفة واحد على الأقل',
            'room_types.*.room_type_id.required' => 'يجب اختيار نوع الغرفة',
            'room_types.*.cost_price.required' => 'سعر التكلفة مطلوب',
            'room_types.*.cost_price.min' => 'سعر التكلفة يجب ألا يكون سالباً',
            'room_types.*.sale_price.required' => 'سعر البيع مطلوب',
            'room_types.*.sale_price.min' => 'سعر البيع يجب ألا يكون سالباً',
            'room_types.*.sale_price.gte' => 'سعر البيع يجب أن يكون أكبر من أو يساوي سعر التكلفة',
            'room_types.*.currency.required' => 'العملة مطلوبة',
            'room_types.*.currency.in' => 'العملة يجب أن تكون واحدة من: SAR, KWD',
            'room_types.*.allotment.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً',
            'room_types.*.allotment.min' => 'عدد الغرف يجب ألا يكون سالباً',
            'room_types.*.action.in' => 'قيمة الإجراء للغرفة غير صالحة.',
        ]);

        // تنسيق التواريخ
        try {
            $departureDate = self::parseDateFlexible($validatedData['departure_date']);
            $returnDate = self::parseDateFlexible($validatedData['return_date']);
        } catch (\Exception $e) {
            // استخدام ValidationException لعرض الخطأ بشكل صحيح في الفورم
            throw ValidationException::withMessages([
                'departure_date' => 'حدث خطأ في تنسيق التاريخ. يجب استخدام صيغة yyyy-mm-dd.',
            ]);
        }

        // حساب عدد الأيام
        $daysCount = LandTrip::calculateDaysCount($departureDate, $returnDate);

        // تسجيل التغييرات للحقول الأساسية للرحلة
        $fieldsToTrack = [
            'trip_type_id',
            'agent_id',
            'hotel_id',
            'employee_id',
            'status',
            'notes'
        ];
        foreach ($fieldsToTrack as $field) {
            if ($landTrip->getOriginal($field) != ($validatedData[$field] ?? $landTrip->$field)) {
                LandTripEdit::create([
                    'land_trip_id' => $landTrip->id,
                    'user_id' => Auth::id(),
                    'field' => $field,
                    'old_value' => $landTrip->getOriginal($field),
                    'new_value' => $validatedData[$field] ?? $landTrip->$field,
                ]);
            }
        }
        // تسجيل تغييرات التواريخ وعدد الأيام بشكل منفصل بعد التحويل والحساب
        if ($landTrip->getOriginal('departure_date') != $departureDate) {
            LandTripEdit::create([
                'land_trip_id' => $landTrip->id,
                'user_id' => Auth::id(),
                'field' => 'departure_date',
                'old_value' => $landTrip->getOriginal('departure_date'),
                'new_value' => $departureDate,
            ]);
        }
        if ($landTrip->getOriginal('return_date') != $returnDate) {
            LandTripEdit::create([
                'land_trip_id' => $landTrip->id,
                'user_id' => Auth::id(),
                'field' => 'return_date',
                'old_value' => $landTrip->getOriginal('return_date'),
                'new_value' => $returnDate,
            ]);
        }
        if ($landTrip->getOriginal('days_count') != $daysCount) {
            LandTripEdit::create([
                'land_trip_id' => $landTrip->id,
                'user_id' => Auth::id(),
                'field' => 'days_count',
                'old_value' => $landTrip->getOriginal('days_count'),
                'new_value' => $daysCount,
            ]);
        }


        // تحقق من حالة الرحلة المنتهية
        if ($landTrip->status === 'expired' && $validatedData['status'] !== 'expired') {
            $returnDateObj = Carbon::parse($returnDate); // استخدم $returnDate المحولة
            if ($returnDateObj->isPast()) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تغيير حالة الرحلة المنتهية إلا إذا تم تعديل تاريخ العودة ليكون في المستقبل.',
                ]);
            }
        }


        DB::beginTransaction();

        try {
            // تحديث الرحلة
            $landTrip->update([
                'trip_type_id' => $validatedData['trip_type_id'],
                'agent_id' => $validatedData['agent_id'],
                'hotel_id' => $validatedData['hotel_id'],
                'employee_id' => $validatedData['employee_id'],
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
                'days_count' => $daysCount,
                'status' => $validatedData['status'],
                'notes' => $validatedData['notes'],
            ]);

            $submittedRoomTypesOriginal = collect($validatedData['room_types'] ?? []);
            $idsToDeleteDueToTypeChange = [];

            // 1. معالجة الحذف الصريح أولاً
            foreach ($submittedRoomTypesOriginal as $index => $roomData) {
                if (isset($roomData['action']) && $roomData['action'] === 'delete' && !empty($roomData['id'])) {
                    $roomPriceToDelete = LandTripRoomPrice::find($roomData['id']);
                    if ($roomPriceToDelete) {
                        if ($roomPriceToDelete->land_trip_id != $landTrip->id) {
                            DB::rollBack();
                            return redirect()->back()->withInput()->withErrors(['room_types' => "محاولة حذف سعر غرفة لا يخص هذه الرحلة (ID: {$roomData['id']})."]);
                        }
                        if ($roomPriceToDelete->bookings()->count() > 0) {
                            DB::rollBack();
                            return redirect()->back()->withInput()
                                ->withErrors(['room_types' => "لا يمكن حذف نوع الغرفة '{$roomPriceToDelete->roomType->room_type_name}' (ID: {$roomData['id']}) لوجود حجوزات مرتبطة به."]);
                        }

                        $originalRoomForEdit = $originalRoomPrices[$roomData['id']] ?? null;
                        if ($originalRoomForEdit) {
                            foreach (['room_type_id', 'cost_price', 'sale_price', 'currency', 'allotment'] as $field) {
                                LandTripEdit::create([
                                    'land_trip_id' => $landTrip->id,
                                    'user_id' => Auth::id(),
                                    'field' => "room_price_deleted:$field:{$roomData['id']}",
                                    'old_value' => $originalRoomForEdit[$field],
                                    'new_value' => null,
                                ]);
                            }
                        }
                        $roomPriceToDelete->delete();
                    }
                }
            }

            $submittedRoomTypesForProcessing = $submittedRoomTypesOriginal->filter(function ($roomData) {
                return !(isset($roomData['action']) && $roomData['action'] === 'delete' && !empty($roomData['id']));
            })->values();


            // 2. معالجة تغيير نوع الغرفة والتحقق من التكرار
            $processedRoomTypeIdsInRequest = []; // لتتبع أنواع الغرف في الطلب الحالي لمنع التكرار داخل الطلب
            $roomTypeChanges = []; // مصفوفة جديدة لتخزين التغييرات المراد تطبيقها لاحقًا

            foreach ($submittedRoomTypesForProcessing as $i => $roomData) {
                $currentId = $roomData['id'] ?? null;
                $action = $roomData['action'] ?? ($currentId ? 'update' : 'new'); // استنتاج الأكشن إذا لم يرسل
                $roomTypeId = $roomData['room_type_id'];

                // التحقق من التكرار داخل الطلب الحالي
                if (in_array($roomTypeId, $processedRoomTypeIdsInRequest)) {
                    // إذا كان هذا السطر هو نفسه الذي أضاف النوع المكرر (في حالة تغيير نوع غرفة موجودة إلى نوع مكرر في الطلب)
                    // وكان هذا السطر هو الذي سيتم اعتباره "جديد" بعد تغيير النوع، نسمح به مؤقتًا هنا
                    // وسيتم التحقق منه لاحقًا مقابل قاعدة البيانات.
                    // أما إذا كان سطرًا جديدًا تمامًا أو تحديثًا لسطر آخر بنفس النوع المكرر، فهذا خطأ.
                    $isSelfDuplicateOnChange = ($action === 'new_after_type_change' || (empty($currentId) && $action === 'new'));
                    if (!$isSelfDuplicateOnChange) {
                        DB::rollBack(); // التراجع عن المعاملات
                        $tempRoomTypeName = RoomType::find($roomTypeId)->room_type_name ?? $roomTypeId;
                        return redirect()->back()->withInput()->withErrors(['room_types' => "لا يمكن تكرار نوع الغرفة '{$tempRoomTypeName}' في نفس الطلب."]);
                    }
                }
                $processedRoomTypeIdsInRequest[] = $roomTypeId;


                if (!empty($currentId) && $action !== 'new' && $action !== 'delete') { // عنصر موجود قد يتم تغيير نوعه
                    $existingRoomPriceInDb = LandTripRoomPrice::find($currentId);

                    if ($existingRoomPriceInDb && $existingRoomPriceInDb->room_type_id != $roomData['room_type_id']) {
                        // تم تغيير room_type_id
                        if ($existingRoomPriceInDb->bookings()->count() > 0) {
                            DB::rollBack();
                            return redirect()->back()->withInput()
                                ->withErrors(['room_types' => "لا يمكن تغيير نوع الغرفة '{$existingRoomPriceInDb->roomType->room_type_name}' (ID: {$currentId}) إلى نوع آخر لوجود حجوزات. يرجى حذف السطر وإضافة سطر جديد بالنوع المطلوب."]);
                        }
                        // التحقق من أن النوع الجديد ليس مكررًا مع أي غرفة أخرى موجودة بالفعل في قاعدة البيانات
                        $isNewTypeDuplicateInDb = LandTripRoomPrice::where('land_trip_id', $landTrip->id)
                            ->where('room_type_id', $roomData['room_type_id'])
                            ->where('id', '!=', $currentId) // استبعد السطر الحالي من المقارنة
                            ->exists();
                        if ($isNewTypeDuplicateInDb) {
                            DB::rollBack();
                            $tempRoomTypeName = RoomType::find($roomData['room_type_id'])->room_type_name ?? $roomData['room_type_id'];
                            return redirect()->back()->withInput()->withErrors(['room_types' => "نوع الغرفة الجديد '{$tempRoomTypeName}' موجود بالفعل في غرفة أخرى لهذه الرحلة."]);
                        }

                        $idsToDeleteDueToTypeChange[] = $existingRoomPriceInDb->id;
                        // $roomDataRef['original_id_before_type_change'] = $existingRoomPriceInDb->id;
                        // $roomDataRef['id'] = null;
                        // $roomDataRef['action'] = 'new_after_type_change';
                        // تخزين التغييرات في المصفوفة المؤقتة بدلاً من تعديل $roomDataRef مباشرة
                        $roomTypeChanges[$i] = [
                            'original_id_before_type_change' => $existingRoomPriceInDb->id,
                            'id' => null,
                            'action' => 'new_after_type_change'
                        ];
                    }
                }
            }
            // unset($roomDataRef);
            // ** مهم جدًا: تطبيق التغييرات من $roomTypeChanges على $submittedRoomTypesForProcessing **
            foreach ($roomTypeChanges as $index => $changes) {
                $submittedRoomTypesForProcessing[$index] = array_merge(
                    $submittedRoomTypesForProcessing[$index],
                    $changes
                );
            }

            // إضافة تسجيل لفهم البيانات بعد التغييرات
            Log::info('Room data after changes applied:', [
                'processed_data' => $submittedRoomTypesForProcessing->toArray(),
                'ids_to_delete' => $idsToDeleteDueToTypeChange
            ]);

            // 3. معالجة التحديثات والإضافات الجديدة
            foreach ($submittedRoomTypesForProcessing as $roomData) {
                $currentId = $roomData['id'] ?? null;
                $action = $roomData['action'] ?? ($currentId ? 'update' : 'new');

                $priceDataPayload = [
                    'room_type_id' => $roomData['room_type_id'],
                    'cost_price' => $roomData['cost_price'],
                    'sale_price' => $roomData['sale_price'],
                    'currency' => $roomData['currency'],
                    'allotment' => $roomData['allotment'] ?? null,
                ];

                if (($action === 'update' || $action === 'keep') && !empty($currentId)) {
                    $roomToUpdate = LandTripRoomPrice::where('id', $currentId)
                        ->where('land_trip_id', $landTrip->id)
                        ->first();
                    if ($roomToUpdate) {
                        // قبل التحديث، تأكد أن room_type_id لم يتغير إلى نوع مكرر (إذا لم يتم التعامل معه كـ new_after_type_change)
                        if ($roomToUpdate->room_type_id != $priceDataPayload['room_type_id']) {
                            // هذا السيناريو يجب أن يكون قد تم التعامل معه في الخطوة 2 وأصبح action = new_after_type_change
                            // لكن كإجراء احترازي
                            $checkDuplicate = LandTripRoomPrice::where('land_trip_id', $landTrip->id)
                                ->where('room_type_id', $priceDataPayload['room_type_id'])
                                ->where('id', '!=', $currentId)
                                ->exists();
                            if ($checkDuplicate) {
                                DB::rollBack();
                                $tempRoomTypeName = RoomType::find($priceDataPayload['room_type_id'])->room_type_name ?? $priceDataPayload['room_type_id'];
                                return redirect()->back()->withInput()->withErrors(['room_types' => "عند تحديث الغرفة (ID: {$currentId})، النوع الجديد '{$tempRoomTypeName}' مكرر."]);
                            }
                        }

                        $roomToUpdate->update($priceDataPayload);
                        $originalRoomForEdit = $originalRoomPrices[$currentId] ?? null;
                        if ($originalRoomForEdit) {
                            foreach (['room_type_id', 'cost_price', 'sale_price', 'currency', 'allotment'] as $field) {
                                if (($originalRoomForEdit[$field] ?? null) != ($priceDataPayload[$field] ?? null)) {
                                    LandTripEdit::create([
                                        'land_trip_id' => $landTrip->id,
                                        'user_id' => Auth::id(),
                                        'field' => "room_price_updated:$field:{$currentId}",
                                        'old_value' => $originalRoomForEdit[$field],
                                        'new_value' => $priceDataPayload[$field] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                } elseif ($action === 'new' || $action === 'new_after_type_change') {
                    // التحقق من التكرار مرة أخرى قبل الإضافة النهائية (خاصة للـ action 'new')
                    $isDuplicateInDb = LandTripRoomPrice::where('land_trip_id', $landTrip->id)
                        ->where('room_type_id', $priceDataPayload['room_type_id'])
                        ->exists();
                    if ($isDuplicateInDb && $action !== 'new_after_type_change') {
                        DB::rollBack();
                        $tempRoomTypeName = RoomType::find($priceDataPayload['room_type_id'])->room_type_name ?? $priceDataPayload['room_type_id'];
                        return redirect()->back()->withInput()->withErrors(['room_types' => "نوع الغرفة '{$tempRoomTypeName}' موجود بالفعل لهذه الرحلة ولا يمكن إضافته مرة أخرى."]);
                    }

                    $priceDataPayload['land_trip_id'] = $landTrip->id;
                    $newRoomPrice = LandTripRoomPrice::create($priceDataPayload);

                    $fieldPrefix = ($action === 'new_after_type_change' && isset($roomData['original_id_before_type_change'])) ?
                        "room_price_type_changed_to_new:{$roomData['original_id_before_type_change']}_to_{$newRoomPrice->id}" :
                        "room_price_created:{$newRoomPrice->id}";

                    foreach (['room_type_id', 'cost_price', 'sale_price', 'currency', 'allotment'] as $field) {
                        LandTripEdit::create([
                            'land_trip_id' => $landTrip->id,
                            'user_id' => Auth::id(),
                            'field' => "$fieldPrefix:$field",
                            'old_value' => ($action === 'new_after_type_change' && isset($roomData['original_id_before_type_change'])) ? ($originalRoomPrices[$roomData['original_id_before_type_change']][$field] ?? null) : null,
                            'new_value' => $newRoomPrice->$field ?? ($priceDataPayload[$field] ?? null),
                        ]);
                    }
                }
            }

            // 4. حذف أسعار الغرف القديمة التي تم تغيير نوعها
            if (!empty($idsToDeleteDueToTypeChange)) {
                foreach ($idsToDeleteDueToTypeChange as $idToDelete) {
                    $roomPriceInstance = LandTripRoomPrice::find($idToDelete);
                    if ($roomPriceInstance) {
                        $originalRoomForEdit = $originalRoomPrices[$idToDelete] ?? null;
                        if ($originalRoomForEdit) {
                            foreach (['room_type_id', 'cost_price', 'sale_price', 'currency', 'allotment'] as $field) {
                                LandTripEdit::create([
                                    'land_trip_id' => $landTrip->id,
                                    'user_id' => Auth::id(),
                                    'field' => "room_price_type_change_old_deleted:$field:{$idToDelete}",
                                    'old_value' => $originalRoomForEdit[$field],
                                    'new_value' => null,
                                ]);
                            }
                        }
                        $roomPriceInstance->delete();
                    }
                }
            }

            DB::commit();

            $this->createUpdateNotification($landTrip, $originalData); // تأكد أن هذه الدالة موجودة وتعمل بشكل صحيح

            return redirect()->route('admin.land-trips.index')
                ->with('success', 'تم تحديث الرحلة وأسعار الغرف بنجاح!');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning("LandTrip Update Validation Error: ", $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("خطأ في تحديث الرحلة: " . $e->getMessage() . " في الملف " . $e->getFile() . " السطر " . $e->getLine(), ['exception' => $e]);
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
    // دالة لإنشاء حجز جديد لرحلة برية
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
    // دالة لتخزين الحجز 
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
        // حساب المبالغ المستحقة للوكيل والشركة عدد الغرف في السعر وليس السعر الموجود سعر ليلة بل السعر الكلي
        $totalDueToAgent = $validatedData['rooms'] * $costPrice ;
        $totalDueFromCompany = $validatedData['rooms'] * $salePrice ;


        // البحث عن الموظف المرتبط بالمستخدم الحالي
        $employee = Employee::where('user_id', Auth::id())->first();

        if (!$employee) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'لا يمكنك إنشاء الحجز: حسابك غير مرتبط بسجل موظف في النظام.');
        }
        DB::beginTransaction(); // بدء المعاملة
        // dd($validatedData);
        try {
            // إنشاء الحجز
            $booking =  LandTripBooking::create([
                'land_trip_id' => $landTrip->id,
                'land_trip_room_price_id' => $roomPrice->id,
                'client_name' => strip_tags($validatedData['client_name']),
                'company_id' => $validatedData['company_id'],
                'employee_id' => $employee->id, // الموظف الحالي هو من يقوم بالحجز
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
            $company = Company::find($validatedData['company_id']);
            $notificationMessage = "تم إضافة حجز جديد للرحلة رقم: {$landTrip->id} للعميل: {$validatedData['client_name']} من شركة: {$company->name}. عدد الغرف: {$validatedData['rooms']}";
            // إشعار للموظف
            Notification::create([
                'user_id' => Auth::id(),
                'message' => $notificationMessage,
                'type' => 'حجز رحلة',
            ]);
            // إشعار للادمن 
            
            // إرسال إشعار للأدمن أيضاً
            $adminUsers = User::where('role', 'Admin')->get(); // أو أي طريقة تحدد بها الأدمن
            foreach ($adminUsers as $admin) {
                // تجنب إرسال إشعار مكرر إذا كان الأدمن هو نفسه من قام بالحجز
                if ($admin->id !== Auth::id()) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'message' => $notificationMessage . " - بواسطة: " . Auth::user()->name,
                        'type' => 'حجز رحلة',
                    ]);
                }
            }
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
    // دالة لتعديل الحجز 
    public function editBooking(LandTripBooking $booking)
    {
        // التحقق من أن الحجز ينتمي للرحلة البرية
        $landTrip = $booking->landTrip;
        if (!$landTrip) {
            return redirect()->route('admin.land-trips.index')
                ->with('error', 'الحجز غير مرتبط برحلة برية.');
        }

        // تحميل العلاقات اللازمة
        $landTrip->loadMissing(['tripType', 'agent', 'hotel', 'employee', 'roomPrices.roomType']);
        $companies = Company::orderBy('name')->get();

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

        return view('admin.land-trips.edit-booking', compact('booking', 'landTrip', 'companies', 'roomAvailability'));
    }
    public function updateBooking(Request $request, LandTripBooking $booking)
{
    // التحقق من أن الحجز ينتمي للرحلة البرية
    $landTrip = $booking->landTrip;
    if (!$landTrip) {
        return redirect()->route('admin.land-trips.index')
            ->with('error', 'الحجز غير مرتبط برحلة برية.');
    }

    // التحقق من البيانات
    $validatedData = $request->validate([
        'company_id' => 'required|exists:companies,id',
        'client_name' => 'required|string|max:255',
        'land_trip_room_price_id' => 'required|exists:land_trip_room_prices,id',
        'rooms' => 'required|integer|min:1',
        'notes' => 'nullable|string|max:1000',
    ], [
        'company_id.required' => 'يرجى تحديد الشركة',
        'company_id.exists' => 'الشركة المختارة غير موجودة',
        'client_name.required' => 'اسم العميل مطلوب',
        'client_name.max' => 'اسم العميل لا يجب أن يزيد عن 255 حرف',
        'land_trip_room_price_id.required' => 'يرجى اختيار نوع الغرفة',
        'land_trip_room_price_id.exists' => 'نوع الغرفة المختار غير موجود',
        'rooms.required' => 'يرجى تحديد عدد الغرف',
        'rooms.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً',
        'rooms.min' => 'عدد الغرف يجب أن يكون على الأقل 1',
        'notes.max' => 'الملاحظات لا يجب أن تزيد عن 1000 حرف',
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
            ->where('booking_id', '!=', $booking->id) // استبعاد الحجز الحالي
            ->sum('rooms');
        $availableRooms = max(0, $roomPrice->allotment - $bookedRooms);

        if ($validatedData['rooms'] > $availableRooms) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rooms' => "لا يوجد عدد كافٍ من الغرف المتاحة. العدد المتاح: {$availableRooms}"]);
        }
    }

    // حساب التكاليف الجديدة
    $costPrice = $roomPrice->cost_price;
    $salePrice = $roomPrice->sale_price;
    $totalDueToAgent = $validatedData['rooms'] * $costPrice;
    $totalDueFromCompany = $validatedData['rooms'] * $salePrice;

    DB::beginTransaction();

    try {
        // تحديث بيانات الحجز
        $booking->update([
            'company_id' => $validatedData['company_id'],
            'client_name' => strip_tags($validatedData['client_name']),
            'land_trip_room_price_id' => $validatedData['land_trip_room_price_id'],
            'rooms' => $validatedData['rooms'],
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'amount_due_to_agent' => $totalDueToAgent,
            'amount_due_from_company' => $totalDueFromCompany,
            'currency' => $roomPrice->currency,
            'notes' => strip_tags($validatedData['notes'] ?? ''),
        ]);

        // تحديث السجل في الجدول الوسيط
        DB::table('land_trip_booking_room_price')
            ->where('booking_id', $booking->id)
            ->update([
                'land_trip_room_price_id' => $roomPrice->id,
                'rooms' => $validatedData['rooms'],
                'updated_at' => now(),
            ]);

        // إنشاء إشعار بالتحديث
        $company = Company::find($validatedData['company_id']);
        $notificationMessage = "تم تحديث حجز الرحلة رقم: {$landTrip->id} للعميل: {$validatedData['client_name']} من شركة: {$company->name}. عدد الغرف: {$validatedData['rooms']}";
        
        Notification::create([
            'user_id' => Auth::id(),
            'message' => $notificationMessage,
            'type' => 'تحديث حجز رحلة',
        ]);

        // إرسال إشعار للأدمن إذا لم يكن هو من قام بالتحديث
        $adminUsers = User::where('role', 'Admin')->get();
        foreach ($adminUsers as $admin) {
            if ($admin->id !== Auth::id()) {
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => $notificationMessage . " - بواسطة: " . Auth::user()->name,
                    'type' => 'تحديث حجز رحلة',
                ]);
            }
        }

        DB::commit();

        return redirect()->route('admin.land-trips.bookings', $landTrip->id)
            ->with('success', 'تم تحديث الحجز بنجاح');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("خطأ في تحديث الحجز: " . $e->getMessage());

        return redirect()->back()
            ->withInput()
            ->with('error', 'حدث خطأ أثناء تحديث الحجز: ' . $e->getMessage());
    }
}
}
