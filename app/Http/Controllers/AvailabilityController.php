<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log; // Import Log facade
use App\Models\Availability;
use App\Models\Hotel;
use App\Models\Agent;
use App\Models\Employee;
use App\Models\User; // Assuming you have a User model for admin notifications
use App\Models\RoomType;
use App\Models\AvailabilityRoomType;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException; // Import ValidationException
use Mockery\Matcher\Not;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // ابحث عن الإتاحات اللي مش expired وتاريخ نهايتها قبل النهارده
            $expiredCount = Availability::where('status', '!=', 'expired')
                ->whereDate('end_date', '<=', Carbon::today())
                ->update(['status' => 'expired']); // <-- حدث حالتهم لـ expired

            // (اختياري) سجل في اللوج لو تم تحديث أي حاجة
            if ($expiredCount > 0) {
                Log::info("CompanyAvailabilityController: تم تحديث {$expiredCount} إتاحة منتهية إلى 'expired'.");
                // Notify Admin  
                // هنا ممكن تضيف كود لإرسال إشعار للإدارة لو حبيت
                Notification::create([
                    'type' => 'availability_expired_auto', // ممكن نغير النوع لتمييزه
                    'message' => "تم تحديث {$expiredCount} إتاحة منتهية تلقائياً إلى 'منتهية' بواسطة فحص النظام.",
                    // 'user_id' => null, // ممكن نضيف user_id = null للتأكيد إنه مش مستخدم معين
                ]);
            }
        } catch (\Exception $e) {
            // لو حصل خطأ أثناء التحديث، سجله بس متوقفش الصفحة
            Log::error("AvailabilityController (Admin): خطأ أثناء تحديث الإتاحات المنتهية: " . $e->getMessage());
        }
        $query = Availability::with(['hotel', 'agent', 'employee'])->latest();

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        // Add other filters as needed (e.g., date range)

        $availabilities = $query->paginate(15)->withQueryString(); // Keep filters on pagination
        $hotels = Hotel::orderBy('name')->get(); // For filter dropdown

        return view('admin.availabilities.index', compact('availabilities', 'hotels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $hotels = Hotel::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $roomTypes = RoomType::orderBy('room_type_name')->get(); // Fetch all possible room types

        return view('admin.availabilities.create', compact('hotels', 'agents', 'employees', 'roomTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'agent_id' => 'required|exists:agents,id', // Assuming agent is required
            'employee_id' => 'required|exists:employees,id', // Validate employee
            'start_date' => 'required|date', // Use uppercase Y
            'end_date' => 'required|date|after_or_equal:start_date', // Use uppercase Y
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:5000', // Added max length
            'room_types' => 'required|array|min:1',
            'room_types.*.room_type_id' => 'required|exists:room_types,id|distinct', // Ensure distinct room types
            'room_types.*.cost_price' => 'required|numeric|min:0',
            'room_types.*.sale_price' => 'required|numeric|min:0|gte:room_types.*.cost_price', // Sale price >= cost price
            'room_types.*.allotment' => 'nullable|integer|min:1',
        ], [
            'hotel_id.required' => 'يجب اختيار الفندق.',
            'agent_id.required' => 'يجب اختيار جهة الحجز.',
            'employee_id.required' => 'يجب اختيار الموظف المسؤول.',
            'start_date.required' => 'تاريخ البداية مطلوب.',
            'start_date.date_format' => 'صيغة تاريخ البداية غير صحيحة (dd/mm/yyyy).', // Update format in message
            'end_date.date_format' => 'صيغة تاريخ النهاية غير صحيحة (dd/mm/yyyy).', // Update format in message
            'end_date.required' => 'تاريخ النهاية مطلوب.',

            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو نفس تاريخ البداية.',
            'status.required' => 'حالة الإتاحة مطلوبة.',
            'room_types.required' => 'يجب إضافة نوع غرفة واحد على الأقل.',
            'room_types.min' => 'يجب إضافة نوع غرفة واحد على الأقل.',
            'room_types.*.room_type_id.required' => 'يجب اختيار نوع الغرفة لكل صف.',
            'room_types.*.room_type_id.exists' => 'نوع الغرفة المختار غير صالح.',
            'room_types.*.room_type_id.distinct' => 'لا يمكن تكرار نفس نوع الغرفة.', // Added distinct rule message
            'room_types.*.cost_price.required' => 'سعر التكلفة مطلوب لكل نوع غرفة.',
            'room_types.*.cost_price.min' => 'سعر التكلفة لا يمكن أن يكون سالباً.',
            'room_types.*.sale_price.required' => 'سعر البيع مطلوب لكل نوع غرفة.',
            'room_types.*.sale_price.min' => 'سعر البيع لا يمكن أن يكون سالباً.',
            'room_types.*.sale_price.gte' => 'سعر البيع يجب أن يكون أكبر من أو يساوي سعر التكلفة.', // Added gte rule message
            'room_types.*.allotment.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً.',
            'room_types.*.allotment.min' => 'عدد الغرف لا يمكن أن يكون سالباً.',
        ]);

        // **Crucial: Convert date format before saving**
        try {
            // دعم الصيغتين: d/m/Y و Y-m-d
            $dbStartDate = self::parseDateFlexible($validatedData['start_date']);
            $dbEndDate = self::parseDateFlexible($validatedData['end_date']);
        } catch (\Exception $e) {
            // This should ideally not happen due to validation, but handle just in case
            throw ValidationException::withMessages([
                'start_date' => 'حدث خطأ غير متوقع في تحويل صيغة التاريخ.',
            ]);
        }

        // Prepare data for Availability creation (excluding room_types)
        $availabilityData = $validatedData;
        unset($availabilityData['room_types']); // Remove room_types array
        $availabilityData['start_date'] = $dbStartDate; // Use converted date
        $availabilityData['end_date'] = $dbEndDate;   // Use converted date
        // employee_id is already validated and included

        // Create Availability
        $availability = Availability::create($availabilityData);

        // Save associated room types
        if (isset($validatedData['room_types'])) {
            $roomTypesData = [];
            foreach ($validatedData['room_types'] as $roomData) {
                // Ensure all required keys exist (already validated, but good practice)
                if (isset($roomData['room_type_id'], $roomData['cost_price'], $roomData['sale_price'])) {
                    $roomTypesData[] = [
                        'room_type_id' => $roomData['room_type_id'],
                        'cost_price' => $roomData['cost_price'],
                        'sale_price' => $roomData['sale_price'],
                        'allotment' => $roomData['allotment'] ?? null,
                        // availability_id is added automatically by createMany
                    ];
                }
            }
            if (!empty($roomTypesData)) {
                $availability->availabilityRoomTypes()->createMany($roomTypesData);
            }
        }

        // Create Notification
        Notification::create([
            'user_id' => Auth::id(),
            // Use the model's date objects (already Carbon instances due to casting)
            'message' => "إضافة إتاحة جديدة للفندق: {$availability->hotel->name} من " . $availability->start_date->format('d/m/Y') . " إلى " . $availability->end_date->format('d/m/Y'),
            'type' => 'إتاحة جديدة',
        ]);

        return redirect()->route('admin.availabilities.index')->with('success', 'تم إضافة الإتاحة وأنواع الغرف بنجاح!');
    }
    private static function parseDateFlexible($date)
    {
        // إذا كان التاريخ بالفعل بصيغة Y-m-d (مثلاً 2025-05-01)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        // إذا كان التاريخ بصيغة d/m/Y (مثلاً 01/05/2025)
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }
        // حاول تحويله تلقائياً (قد يرمي استثناء)
        return Carbon::parse($date)->format('Y-m-d');
    }
    /**
     * Display the specified resource.
     */
    // عرض تفاصيل الإتاحة

    public function show(Availability $availability)
    {
        $availability->loadMissing(['hotel', 'agent', 'employee', 'availabilityRoomTypes.roomType']);

        // هات كل الحجوزات المرتبطة بأي RoomType من الإتاحة دي
        $bookings = \App\Models\Booking::with(['company', 'employee'])
            ->whereHas('availabilityRoomType', function ($q) use ($availability) {
                $q->where('availability_id', $availability->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.availabilities.show', compact('availability', 'bookings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Availability $availability)
    {
        // Eager load necessary relations for the form
        $availability->loadMissing(['hotel', 'agent', 'employee', 'availabilityRoomTypes']);
        $hotels = Hotel::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $roomTypes = RoomType::orderBy('room_type_name')->get(); // All possible room types

        return view('admin.availabilities.edit', compact(
            'availability',
            'hotels',
            'agents',
            'employees',
            'roomTypes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Availability $availability)
    {
        // احفظ القيم الأصلية قبل التحديث
        $originalData = $availability->getOriginal();
        $validatedData = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'agent_id' => 'required|exists:agents,id',
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            // Allow 'expired' only if it's already the status
            'status' => 'required|in:active,inactive' . ($availability->status === 'expired' ? ',expired' : ''),
            'notes' => 'nullable|string|max:5000',
            'room_types' => 'required|array|min:1',
            // Validate existing IDs belong to this availability
            'room_types.*.id' => 'sometimes|nullable|integer|exists:availability_room_types,id,availability_id,' . $availability->id,
            'room_types.*.room_type_id' => 'required|exists:room_types,id|distinct', // Ensure distinct
            'room_types.*.cost_price' => 'required|numeric|min:0',
            'room_types.*.sale_price' => 'required|numeric|min:0|gte:room_types.*.cost_price',
            'room_types.*.allotment' => 'nullable|integer|min:1',
        ], [
            // Add specific messages similar to store method
            'hotel_id.required' => 'يجب اختيار الفندق.',
            'agent_id.required' => 'يجب اختيار جهة الحجز.',
            'employee_id.required' => 'يجب اختيار الموظف المسؤول.',
            'start_date.required' => 'تاريخ البداية مطلوب.',
            'start_date.date_format' => 'صيغة تاريخ البداية غير صحيحة (dd/mm/yyyy).', // Update format in message
            'end_date.required' => 'تاريخ النهاية مطلوب.',
            'end_date.date_format' => 'صيغة تاريخ النهاية غير صحيحة (dd/mm/yyyy).', // Update format in message
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو نفس تاريخ البداية.',
            'status.required' => 'حالة الإتاحة مطلوبة.',
            'status.in' => 'الحالة المحددة غير صالحة.',
            'room_types.required' => 'يجب إضافة نوع غرفة واحد على الأقل.',
            'room_types.min' => 'يجب إضافة نوع غرفة واحد على الأقل.',
            'room_types.*.room_type_id.required' => 'يجب اختيار نوع الغرفة لكل صف.',
            'room_types.*.room_type_id.exists' => 'نوع الغرفة المختار غير صالح.',
            'room_types.*.room_type_id.distinct' => 'لا يمكن تكرار نفس نوع الغرفة.',
            'room_types.*.cost_price.required' => 'سعر التكلفة مطلوب لكل نوع غرفة.',
            'room_types.*.cost_price.min' => 'سعر التكلفة لا يمكن أن يكون سالباً.',
            'room_types.*.sale_price.required' => 'سعر البيع مطلوب لكل نوع غرفة.',
            'room_types.*.sale_price.min' => 'سعر البيع لا يمكن أن يكون سالباً.',
            'room_types.*.sale_price.gte' => 'سعر البيع يجب أن يكون أكبر من أو يساوي سعر التكلفة.',
            'room_types.*.allotment.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً.',
            'room_types.*.allotment.min' => 'عدد الغرف لا يمكن أن يكون سالباً.',
        ]);

        // **Crucial: Convert date format before saving**
        try {
            $dbStartDate = self::parseDateFlexible($validatedData['start_date']);
            $dbEndDate = self::parseDateFlexible($validatedData['end_date']);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'start_date' => 'حدث خطأ غير متوقع في تحويل صيغة التاريخ.',
            ]);
        }

        // Prepare data for Availability update
        $availabilityData = $validatedData;
        unset($availabilityData['room_types']);
        $availabilityData['start_date'] = $dbStartDate;
        $availabilityData['end_date'] = $dbEndDate;

        // Prevent changing status from 'expired'
        // تحويل التواريخ أولاً حتى نقدر نقارنها
        $startDate = self::parseDateFlexible($request->input('start_date'));
        $endDate = self::parseDateFlexible($request->input('end_date'));

        // تحقق من الحالة السابقة وهل يمكن تغييرها
        $statusRule = 'required|in:active,inactive';
        $messages = [
            'status.required' => 'حالة الإتاحة مطلوبة.',
        ];

        // إذا كانت الحالة الحالية "expired" لكن تاريخ الانتهاء الجديد في المستقبل، اسمح بالتغيير
        if ($availability->status === 'expired' && Carbon::parse($endDate)->gt(Carbon::today())) {
            // لا تضف "expired" للـ in: rule، فقط active/inactive
            // لا رسالة منع
        } elseif ($availability->status === 'expired') {
            // إذا ظلت منتهية (تاريخ الانتهاء في الماضي)، امنع التغيير
            $statusRule .= ',expired';
            $messages['status.in'] = 'لا يمكن تغيير حالة الإتاحة المنتهية إلا إذا تم تعديل تاريخ الانتهاء ليكون في المستقبل.';
        }



        // Update availability main data
        $availability->update($availabilityData);

        // --- Sync Availability Room Types ---
        $submittedRoomTypes = collect($validatedData['room_types'] ?? []);
        $existingRoomTypeIds = $availability->availabilityRoomTypes()->pluck('id')->all();
        $submittedIds = $submittedRoomTypes->pluck('id')->filter()->all(); // Get IDs from submitted data

        // IDs to delete: Exist in DB but not in submission
        $idsToDelete = array_diff($existingRoomTypeIds, $submittedIds);
        if (!empty($idsToDelete)) {
            AvailabilityRoomType::whereIn('id', $idsToDelete)
                ->where('availability_id', $availability->id) // Ensure they belong to this availability
                ->delete();
        }

        // Update existing or create new ones
        foreach ($submittedRoomTypes as $roomData) {
            $dataToSave = [
                'room_type_id' => $roomData['room_type_id'],
                'cost_price' => $roomData['cost_price'],
                'sale_price' => $roomData['sale_price'],
                'allotment' => $roomData['allotment'] ?? null,
            ];

            if (isset($roomData['id']) && !empty($roomData['id'])) {
                // Update existing record by ID
                AvailabilityRoomType::where('id', $roomData['id'])
                    ->where('availability_id', $availability->id) // Ensure it belongs here
                    ->update($dataToSave);
            } else {
                // Create new record (ID was null or missing)
                // Check if a record for this room_type_id already exists for this availability
                // This prevents creating duplicates if the hidden ID field was somehow lost
                $existing = $availability->availabilityRoomTypes()
                    ->where('room_type_id', $roomData['room_type_id'])
                    ->first();
                if (!$existing) { // Only create if it truly doesn't exist
                    $availability->availabilityRoomTypes()->create($dataToSave);
                } else {
                    // Optionally update the existing one found by room_type_id if ID was missing
                    // $existing->update($dataToSave);
                    // Or log a warning, as this case might indicate a form issue
                    Log::warning("Attempted to create duplicate AvailabilityRoomType.", ['availability_id' => $availability->id, 'room_type_id' => $roomData['room_type_id']]);
                }
            }
        }
        // --- End Sync ---

        // Create Notification
        // بعد تحديث بيانات الإتاحة
        // قاموس الحقول بالعربي

        // *** الخطوة 9: إنشاء الإشعار ***
        try {
            // إعادة تحميل العلاقات المحدثة (خاصة hotel, agent, employee إذا تغيروا)
            $availability->refresh(); // يقوم بتحديث بيانات الموديل والعلاقات المحملة

            // 1. حساب التغييرات للبيانات الأساسية
            $fieldNames = [
                'start_date' => 'تاريخ البداية', 'end_date' => 'تاريخ النهاية', 'status' => 'الحالة',
                'notes' => 'الملاحظات', 'hotel_id' => 'الفندق', 'agent_id' => 'جهة الحجز', 'employee_id' => 'الموظف المسؤول',
            ];
            $statusMap = ['active' => 'نشط', 'inactive' => 'غير نشط', 'expired' => 'منتهي'];
            $mainChangedFields = [];

            foreach ($availabilityData as $key => $newValue) {
                if (array_key_exists($key, $originalData) && array_key_exists($key, $fieldNames) && $originalData[$key] != $newValue) {
                    $fieldLabel = $fieldNames[$key];
                    $oldValueFormatted = $originalData[$key];
                    $newValueFormatted = $newValue;

                    // تنسيق القيم للعرض
                    if ($key === 'hotel_id') {
                        $oldValueFormatted = $originalHotelName ?? $originalData[$key];
                        $newValueFormatted = $availability->hotel?->name ?? $newValue; // الاسم الحالي بعد التحديث
                    } elseif ($key === 'agent_id') {
                        $oldValueFormatted = $originalAgentName ?? $originalData[$key];
                        $newValueFormatted = $availability->agent?->name ?? $newValue; // الاسم الحالي بعد التحديث
                    } elseif ($key === 'employee_id') {
                        $oldValueFormatted = $originalEmployeeName ?? $originalData[$key];
                        $newValueFormatted = $availability->employee?->name ?? $newValue; // الاسم الحالي بعد التحديث
                    } elseif (in_array($key, ['start_date', 'end_date'])) {
                        $oldValueFormatted = $originalData[$key] ? self::formatDateTimeForDisplay($originalData[$key]) : 'فارغ';
                        $newValueFormatted = self::formatDateTimeForDisplay($newValue);
                    } elseif ($key === 'status') {
                        $oldValueFormatted = $statusMap[$originalData[$key] ?? ''] ?? $originalData[$key] ?? 'فارغ';
                        $newValueFormatted = $statusMap[$newValue] ?? $newValue;
                    }

                    $mainChangedFields[] = "- {$fieldLabel}: من \"{$oldValueFormatted}\" إلى \"{$newValueFormatted}\"";
                }
            }

            // 2. حساب التغييرات لأنواع الغرف
            $roomTypeChanges = [];
            $originalRoomTypesById = collect($originalData['availability_room_types'] ?? [])->keyBy('id');
            // جلب الحالة الحالية للغرف بعد المزامنة
            $currentRoomTypes = $availability->availabilityRoomTypes()->with('roomType')->get()->keyBy('id');

            // مقارنة التحديثات والإضافات
            foreach ($currentRoomTypes as $currentId => $currentType) {
                $originalType = $originalRoomTypesById->get($currentId);
                $roomName = $currentType->roomType->room_type_name ?? "ID: {$currentType->room_type_id}";
                $currentChanges = [];
                if ($originalType) { // تحديث
                    if ($originalType['cost_price'] != $currentType->cost_price) $currentChanges[] = "التكلفة: \"{$originalType['cost_price']}\" -> \"{$currentType->cost_price}\"";
                    if ($originalType['sale_price'] != $currentType->sale_price) $currentChanges[] = "البيع: \"{$originalType['sale_price']}\" -> \"{$currentType->sale_price}\"";
                    if ($originalType['allotment'] != $currentType->allotment) $currentChanges[] = "الكمية: \"{$originalType['allotment']}\" -> \"{$currentType->allotment}\"";
                    if (!empty($currentChanges)) $roomTypeChanges[] = "- {$roomName}: " . implode(', ', $currentChanges);
                } else { // إضافة
                    $roomTypeChanges[] = "- إضافة: {$roomName} (تكلفة: {$currentType->cost_price}, بيع: {$currentType->sale_price}, كمية: {$currentType->allotment})";
                }
            }
            // مقارنة الحذف
            $deletedIds = $originalRoomTypesById->keys()->diff($currentRoomTypes->keys());
            foreach($deletedIds as $deletedId) {
                $deletedType = $originalRoomTypesById->get($deletedId);
                if ($deletedType) {
                    $deletedRoomTypeName = RoomType::find($deletedType['room_type_id'])->room_type_name ?? "ID: {$deletedType['room_type_id']}";
                    $roomTypeChanges[] = "- حذف: {$deletedRoomTypeName}";
                }
            }

            // 3. بناء الرسالة النهائية
            $updater = Auth::user();
            $updaterName = $updater->name ?? 'مستخدم';
            $allChanges = array_merge($mainChangedFields, $roomTypeChanges);

            if (empty($allChanges)) {
                $details = 'لم يتم تغيير أي بيانات.';
            } else {
                $details = implode("\n", $mainChangedFields);
                if (!empty($roomTypeChanges)) {
                    $details .= (empty($mainChangedFields) ? '' : "\n") . "--- تغييرات أنواع الغرف ---\n" . implode("\n", $roomTypeChanges);
                }
            }

            $hotelName = $availability->hotel->name ?? 'فندق غير محدد'; // الاسم الحالي
            $notificationMessage = "تعديل إتاحة ({$availability->id}) فندق \"{$hotelName}\" بواسطة \"{$updaterName}\".\n{$details}";
            $notificationType = 'تعديل إتاحة';

            // 4. تحديد المستلمين
            $recipients = collect([$updater]); // ابدأ بالمستخدم الحالي
            if ($availability->employee && $availability->employee->user) {
                $recipients->push($availability->employee->user);
            }
            $adminUsers = User::where('role', 'Admin')->get();
            $uniqueRecipients = $recipients->merge($adminUsers)->unique('id')->filter(); // إزالة القيم الفارغة المحتملة

            // 5. إرسال الإشعارات
            if ($uniqueRecipients->isNotEmpty()) {
                foreach ($uniqueRecipients as $recipient) {
                    Notification::create([
                        'user_id' => $recipient->id,
                        'message' => $notificationMessage,
                        'type' => $notificationType,
                        'related_id' => $availability->id,
                        'related_type' => Availability::class,
                    ]);
                }
                Log::info("تم إرسال إشعارات تحديث الإتاحة ID: {$availability->id} إلى " . $uniqueRecipients->count() . " مستلمين.");
            } else {
                Log::warning("AvailabilityController@update: لم يتم العثور على مستلمين صالحين للإشعار ID: {$availability->id}.");
            }

        } catch (\Exception $e) {
            Log::error('خطأ في إرسال إشعار تحديث الإتاحة: ' . $e->getMessage(), ['availability_id' => $availability->id, 'exception' => $e]);
        }
        // --- نهاية كود الإشعارات ---

    

        return redirect()->route('admin.availabilities.index')->with('success', 'تم تحديث الإتاحة وأنواع الغرف بنجاح!');
    }

    private static function formatDateTimeForDisplay($dateTime)
    {
        if (!$dateTime) return 'فارغ';
        try {
            return Carbon::parse($dateTime)->translatedFormat('d/m/Y');
        } catch (\Exception $e) {
            Log::error("Error formatting date for display: " . $e->getMessage(), ['date' => $dateTime]);
            return (string) $dateTime;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Availability $availability)
    {
        // Optional: Check for related bookings before deleting if needed
        // if ($availability->bookings()->exists()) {
        //     return redirect()->route('admin.availabilities.index')->with('error', 'لا يمكن حذف الإتاحة لوجود حجوزات مرتبطة بها.');
        // }

        $hotelName = $availability->hotel->name ?? 'غير معروف'; // Handle potential missing relation
        $availabilityId = $availability->id;

        // Deleting the availability should trigger deletion of related
        // AvailabilityRoomType records if cascade on delete is set up correctly
        // in the migration's foreign key definition.
        // If not, delete them manually first:
        // $availability->availabilityRoomTypes()->delete();

        $availability->delete();

        // Create Notification فيها تفاصيل الحذف
        Notification::create([
            'user_id' => Auth::id(),
            'message' =>  "حذف إتاحة للفندق: {$hotelName} }) (بتاريخ : {$availability->start_date->format('d/m/Y')} إلى {$availability->end_date->format('d/m/Y')}) (ID: {$availabilityId})",
            'type' => 'حذف إتاحة',
        ]);

        return redirect()->route('admin.availabilities.index')->with('success', 'تم حذف الإتاحة بنجاح!');
    }
}
