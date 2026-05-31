<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Availability;
use App\Models\Hotel;
use App\Models\Agent;
use App\Models\Employee;
use App\Models\User;
use App\Models\RoomType;
use App\Models\AvailabilityRoomType;
use App\Models\AvailabilityDailyStatus;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

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
                ->update(['status' => 'expired']);

            if ($expiredCount > 0) {
                Log::info("AvailabilityController: تم تحديث {$expiredCount} إتاحة منتهية إلى 'expired'.");
                Notification::create([
                    'type' => 'availability_expired_auto',
                    'message' => "تم تحديث {$expiredCount} إتاحة منتهية تلقائياً إلى 'منتهية' بواسطة فحص النظام.",
                ]);
            }
        } catch (\Exception $e) {
            Log::error("AvailabilityController (Admin): خطأ أثناء تحديث الإتاحات المنتهية: " . $e->getMessage());
        }

        $query = Availability::with(['hotel', 'agent', 'employee'])->latest();

        $searchBy = $request->search_by;
        $searchValue = $request->search_value;

         if ($searchBy && $searchValue) {
        switch ($searchBy) {
            case 'hotel':
                $query->whereHas('hotel', function($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%");
                });
                break;
            case 'agent':
                $query->whereHas('agent', function($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%");
                });
                break;
            case 'status':
                $statusMap = [
                    'نشط' => 'active',
                    'غير نشط' => 'inactive',
                    'منتهية' => 'expired'
                ];
                $dbStatus = $statusMap[$searchValue] ?? $searchValue;
                $query->where('status', $dbStatus);
                break;
            case 'employee':
                $query->whereHas('employee', function($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%");
                });
                break;
            }
        }

         // فلترة تاريخ الإنشاء (date_from, date_to)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

         // فلترة إضافية لتاريخ البدء والانتهاء (حسب الطلب)
        if ($request->filled('start_date_from')) {
            $query->whereDate('start_date', '>=', $request->start_date_from);
        }
        if ($request->filled('start_date_to')) {
            $query->whereDate('start_date', '<=', $request->start_date_to);
        }
        if ($request->filled('end_date_from')) {
            $query->whereDate('end_date', '>=', $request->end_date_from);
        }
        if ($request->filled('end_date_to')) {
            $query->whereDate('end_date', '<=', $request->end_date_to);
        }

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $availabilities = $query->paginate(15)->withQueryString();
        $hotels = Hotel::orderBy('name')->get();

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
        $roomTypes = RoomType::orderBy('room_type_name')->get();

        return view('admin.availabilities.create', compact('hotels', 'agents', 'employees', 'roomTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'agent_id' => 'required|exists:agents,id',
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:5000',
            'min_nights' => 'nullable|integer|min:1',

            'room_types' => 'required|array|min:1',
            'room_types.*.room_type_id' => 'required|exists:room_types,id|distinct',
            'room_types.*.cost_price' => 'required|numeric|min:0',
            'room_types.*.sale_price' => 'required|numeric|min:0|gte:room_types.*.cost_price',
            'room_types.*.currency' => 'required|in:SAR,KWD',
            'room_types.*.allotment' => 'nullable|integer|min:1',
        ], [
            'hotel_id.required' => 'يجب اختيار الفندق.',
            'agent_id.required' => 'يجب اختيار جهة الحجز.',
            'employee_id.required' => 'يجب اختيار الموظف المسؤول.',
            'start_date.required' => 'تاريخ البداية مطلوب.',
            'end_date.required' => 'تاريخ النهاية مطلوب.',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو نفس تاريخ البداية.',
            'status.required' => 'حالة الإتاحة مطلوبة.',
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
            'room_types.*.currency.required' => 'يجب تحديد العملة لكل نوع غرفة.',
            'room_types.*.currency.in' => 'العملة المحددة غير مدعومة.',
            'room_types.*.allotment.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً.',
            'room_types.*.allotment.min' => 'عدد الغرف لا يمكن أن يكون سالباً.',
        ]);

        try {
            $dbStartDate = self::parseDateFlexible($validatedData['start_date']);
            $dbEndDate = self::parseDateFlexible($validatedData['end_date']);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'start_date' => 'حدث خطأ غير متوقع في تحويل صيغة التاريخ.',
            ]);
        }

        $availabilityData = $validatedData;
        unset($availabilityData['room_types']);
        $availabilityData['start_date'] = $dbStartDate;
        $availabilityData['end_date'] = $dbEndDate;

        // Create Availability
        $availability = Availability::create([
            'hotel_id' => $validatedData['hotel_id'],
            'agent_id' => $validatedData['agent_id'],
            'employee_id' => $validatedData['employee_id'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'status' => $validatedData['status'],
            'notes' => $validatedData['notes'],
            'min_nights' => $validatedData['min_nights'] ?? null,
        ]);

        // Save associated room types and create daily status records
        if (isset($validatedData['room_types'])) {
            $checkIn = Carbon::parse($availability->start_date);
            $checkOut = Carbon::parse($availability->end_date);
            $days = $checkIn->diffInDays($checkOut);

            foreach ($validatedData['room_types'] as $roomData) {
                if (isset($roomData['room_type_id'], $roomData['cost_price'], $roomData['sale_price'])) {
                    $roomTypeRecord = $availability->availabilityRoomTypes()->create([
                        'room_type_id' => $roomData['room_type_id'],
                        'cost_price' => $roomData['cost_price'],
                        'sale_price' => $roomData['sale_price'],
                        'currency' => $roomData['currency'] ?? 'SAR',
                        'allotment' => $roomData['allotment'] ?? null,
                    ]);

                    // Create daily status records for each day in the availability period
                    $allotment = $roomData['allotment'] ?? 1;
                    for ($i = 0; $i < $days; $i++) {
                        $currentDate = $checkIn->copy()->addDays($i);
                        AvailabilityDailyStatus::create([
                            'availability_room_type_id' => $roomTypeRecord->id,
                            'date' => $currentDate->format('Y-m-d'),
                            'available_rooms' => $allotment,
                            'booked_rooms' => 0,
                        ]);
                    }
                }
            }
        }

        // Create accounting entry
        try {
            \App\Http\Controllers\AccountController::createAvailabilityJournalEntry($availability);
        } catch (\Exception $e) {
            Log::error("فشل تسجيل القيد المحاسبي للإتاحة ID: {$availability->id} - " . $e->getMessage());
        }

        // Create Notification
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "إضافة إتاحة جديدة لجهة الحجز: {$availability->agent->name} من " . $availability->start_date->format('d/m/Y') . " إلى " . $availability->end_date->format('d/m/Y'),
            'type' => 'إتاحة جديدة',
        ]);

        return redirect()->route('admin.availabilities.index')->with('success', 'تم إضافة الإتاحة وأنواع الغرف بنجاح!');
    }

    private static function parseDateFlexible($date)
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }
        return Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * Display the specified resource.
     */
    public function show(Availability $availability)
    {
        $availability->loadMissing(['hotel', 'agent', 'employee', 'availabilityRoomTypes.roomType', 'availabilityRoomTypes.dailyStatus']);

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
        $availability->loadMissing(['hotel', 'agent', 'employee', 'availabilityRoomTypes.dailyStatus']);
        $hotels = Hotel::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $roomTypes = RoomType::orderBy('room_type_name')->get();

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
        $originalData = $availability->getOriginal();
        
        $validatedData = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'agent_id' => 'required|exists:agents,id',
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive' . ($availability->status === 'expired' ? ',expired' : ''),
            'notes' => 'nullable|string|max:5000',
            'min_nights' => 'nullable|integer|min:1',

            'room_types' => 'required|array|min:1',
            'room_types.*.id' => 'sometimes|nullable|integer|exists:availability_room_types,id,availability_id,' . $availability->id,
            'room_types.*.room_type_id' => 'required|exists:room_types,id|distinct',
            'room_types.*.cost_price' => 'required|numeric|min:0',
            'room_types.*.sale_price' => 'required|numeric|min:0|gte:room_types.*.cost_price',
            'room_types.*.currency' => 'required|in:SAR,KWD',
            'room_types.*.allotment' => 'nullable|integer|min:1',
        ], [
            'hotel_id.required' => 'يجب اختيار الفندق.',
            'agent_id.required' => 'يجب اختيار جهة الحجز.',
            'employee_id.required' => 'يجب اختيار الموظف المسؤول.',
            'start_date.required' => 'تاريخ البداية مطلوب.',
            'end_date.required' => 'تاريخ النهاية مطلوب.',
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
            'room_types.*.currency.required' => 'يجب تحديد العملة لكل نوع غرفة.',
            'room_types.*.currency.in' => 'العملة المحددة غير مدعومة.',
            'room_types.*.allotment.integer' => 'عدد الغرف يجب أن يكون رقماً صحيحاً.',
            'room_types.*.allotment.min' => 'عدد الغرف لا يمكن أن يكون سالباً.',
        ]);

        try {
            $dbStartDate = self::parseDateFlexible($validatedData['start_date']);
            $dbEndDate = self::parseDateFlexible($validatedData['end_date']);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'start_date' => 'حدث خطأ غير متوقع في تحويل صيغة التاريخ.',
            ]);
        }

        $availabilityData = $validatedData;
        unset($availabilityData['room_types']);
        $availabilityData['start_date'] = $dbStartDate;
        $availabilityData['end_date'] = $dbEndDate;

        // Update availability main data
        $availability->update([
            'hotel_id' => $validatedData['hotel_id'],
            'agent_id' => $validatedData['agent_id'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'status' => $validatedData['status'],
            'notes' => $validatedData['notes'],
            'min_nights' => $validatedData['min_nights'] ?? null,
        ]);

        // Sync room types and daily status
        $submittedRoomTypes = collect($validatedData['room_types'] ?? []);
        $existingRoomTypeIds = $availability->availabilityRoomTypes()->pluck('id')->all();
        $submittedIds = $submittedRoomTypes->pluck('id')->filter()->all();

        // IDs to delete
        $idsToDelete = array_diff($existingRoomTypeIds, $submittedIds);
        if (!empty($idsToDelete)) {
            AvailabilityRoomType::whereIn('id', $idsToDelete)
                ->where('availability_id', $availability->id)
                ->delete();
        }

        $newStartDate = Carbon::parse($availability->start_date);
        $newEndDate = Carbon::parse($availability->end_date);
        $newDays = $newStartDate->diffInDays($newEndDate);

        foreach ($submittedRoomTypes as $roomData) {
            $dataToSave = [
                'room_type_id' => $roomData['room_type_id'],
                'cost_price' => $roomData['cost_price'],
                'sale_price' => $roomData['sale_price'],
                'currency' => $roomData['currency'] ?? 'SAR',
                'allotment' => $roomData['allotment'] ?? null,
            ];

            if (isset($roomData['id']) && !empty($roomData['id'])) {
                // Update existing
                $roomTypeRecord = AvailabilityRoomType::where('id', $roomData['id'])
                    ->where('availability_id', $availability->id)
                    ->first();
                    
                if ($roomTypeRecord) {
                    $oldAllotment = $roomTypeRecord->allotment;
                    $roomTypeRecord->update($dataToSave);
                    
                    // Update daily status if dates or allotment changed
                    $this->syncDailyStatus($roomTypeRecord, $newStartDate, $newEndDate, $roomData['allotment'] ?? 1, $oldAllotment);
                }
            } else {
                // Create new
                $existing = $availability->availabilityRoomTypes()
                    ->where('room_type_id', $roomData['room_type_id'])
                    ->first();
                    
                if (!$existing) {
                    $roomTypeRecord = $availability->availabilityRoomTypes()->create($dataToSave);
                    
                    // Create daily status for new room type
                    $allotment = $roomData['allotment'] ?? 1;
                    for ($i = 0; $i < $newDays; $i++) {
                        $currentDate = $newStartDate->copy()->addDays($i);
                        AvailabilityDailyStatus::create([
                            'availability_room_type_id' => $roomTypeRecord->id,
                            'date' => $currentDate->format('Y-m-d'),
                            'available_rooms' => $allotment,
                            'booked_rooms' => 0,
                        ]);
                    }
                }
            }
        }

        try {
            \App\Http\Controllers\AccountController::updateAvailabilityJournalEntry($availability);
        } catch (\Exception $e) {
            Log::error("فشل تحديث القيد المحاسبي للإتاحة ID: {$availability->id} - " . $e->getMessage());
        }

        // Create update notification
        try {
            $availability->refresh();
            
            $fieldNames = [
                'start_date' => 'تاريخ البداية',
                'end_date' => 'تاريخ النهاية',
                'status' => 'الحالة',
                'notes' => 'الملاحظات',
                'hotel_id' => 'الفندق',
                'agent_id' => 'جهة الحجز',
                'employee_id' => 'الموظف المسؤول',
            ];
            $statusMap = ['active' => 'نشط', 'inactive' => 'غير نشط', 'expired' => 'منتهي'];
            $mainChangedFields = [];

            foreach ($availabilityData as $key => $newValue) {
                if (array_key_exists($key, $originalData) && array_key_exists($key, $fieldNames) && $originalData[$key] != $newValue) {
                    $fieldLabel = $fieldNames[$key];
                    $oldValueFormatted = $originalData[$key];
                    $newValueFormatted = $newValue;

                    if ($key === 'hotel_id') {
                        $oldValueFormatted = Hotel::find($originalData[$key])->name ?? $originalData[$key];
                        $newValueFormatted = $availability->hotel?->name ?? $newValue;
                    } elseif ($key === 'agent_id') {
                        $oldValueFormatted = Agent::find($originalData[$key])->name ?? $originalData[$key];
                        $newValueFormatted = $availability->agent?->name ?? $newValue;
                    } elseif ($key === 'employee_id') {
                        $oldValueFormatted = Employee::find($originalData[$key])->name ?? $originalData[$key];
                        $newValueFormatted = $availability->employee?->name ?? $newValue;
                    } elseif (in_array($key, ['start_date', 'end_date'])) {
                        $oldValueFormatted = $originalData[$key] ? Carbon::parse($originalData[$key])->format('d/m/Y') : 'فارغ';
                        $newValueFormatted = Carbon::parse($newValue)->format('d/m/Y');
                    } elseif ($key === 'status') {
                        $oldValueFormatted = $statusMap[$originalData[$key] ?? ''] ?? $originalData[$key] ?? 'فارغ';
                        $newValueFormatted = $statusMap[$newValue] ?? $newValue;
                    }

                    $mainChangedFields[] = "- {$fieldLabel}: من {$oldValueFormatted} إلى {$newValueFormatted}";
                }
            }

            $updater = Auth::user();
            $updaterName = $updater->name ?? 'مستخدم';
            
            if (empty($mainChangedFields)) {
                $details = 'لم يتم تغيير أي بيانات.';
            } else {
                $details = implode("\n", $mainChangedFields);
            }

            $hotelName = $availability->hotel->name ?? 'فندق غير محدد';
            $notificationMessage = "تعديل إتاحة ({$availability->id}) فندق {$hotelName} بواسطة {$updaterName} .\n{$details}";
            
            $recipients = collect([$updater]);
            if ($availability->employee && $availability->employee->user) {
                $recipients->push($availability->employee->user);
            }
            $adminUsers = User::where('role', 'Admin')->get();
            $uniqueRecipients = $recipients->merge($adminUsers)->unique('id')->filter();

            if ($uniqueRecipients->isNotEmpty()) {
                foreach ($uniqueRecipients as $recipient) {
                    Notification::create([
                        'user_id' => $recipient->id,
                        'message' => $notificationMessage,
                        'type' => 'تعديل إتاحة',
                        'related_id' => $availability->id,
                        'related_type' => Availability::class,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال إشعار تحديث الإتاحة: ' . $e->getMessage());
        }

        return redirect()->route('admin.availabilities.index')->with('success', 'تم تحديث الإتاحة وأنواع الغرف بنجاح!');
    }

    /**
     * Sync daily status records when availability dates or allotment change
     */
    private function syncDailyStatus(AvailabilityRoomType $roomTypeRecord, Carbon $newStartDate, Carbon $newEndDate, int $newAllotment, ?int $oldAllotment): void
    {
        // ✅ تحقق إن مفيش حجوزات قبل المسح
        $hasBookings = $roomTypeRecord->dailyStatus()
        ->where('booked_rooms', '>', 0)
        ->exists();

         if ($hasBookings) {
        // لو فيه حجوزات → حافظ على booked_rooms وعدل available_rooms بس
        $newDays = $newStartDate->diffInDays($newEndDate);
        
        for ($i = 0; $i < $newDays; $i++) {
            $currentDate = $newStartDate->copy()->addDays($i);
            
            AvailabilityDailyStatus::updateOrCreate(
                [
                    'availability_room_type_id' => $roomTypeRecord->id,
                    'date' => $currentDate->format('Y-m-d'),
                ],
                [
                    'available_rooms' => $newAllotment,
                    // booked_rooms محافظ على قيمته الموجودة
                ]
            );
        }
         // امسح الأيام اللي بره النطاق الجديد بس
        $roomTypeRecord->dailyStatus()
            ->where(function($q) use ($newStartDate, $newEndDate) {
                $q->whereDate('date', '<', $newStartDate)
                  ->orWhereDate('date', '>', $newEndDate);
            })
            ->where('booked_rooms', 0) // امسح بس اللي مش محجوز
            ->delete();
            
        Log::info("تم تحديث daily_status مع الحفاظ على الحجوزات للـ RoomType ID: {$roomTypeRecord->id}");
        return;
        }

        // لو مفيش حجوزات → امسح كل حاجة وابدأ من الأول
        $roomTypeRecord->dailyStatus()->delete();
                
        $newDays = $newStartDate->diffInDays($newEndDate);
        for ($i = 0; $i < $newDays; $i++) {
            $currentDate = $newStartDate->copy()->addDays($i);
            AvailabilityDailyStatus::create([
                'availability_room_type_id' => $roomTypeRecord->id,
                'date' => $currentDate->format('Y-m-d'),
                'available_rooms' => $newAllotment,
                'booked_rooms' => 0,
            ]);
        }
    
    Log::info("تم إعادة إنشاء daily_status للـ RoomType ID: {$roomTypeRecord->id}");
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Availability $availability)
    {
        $hotelName = $availability->hotel->name ?? 'غير معروف';
        $availabilityId = $availability->id;
        
        \App\Http\Controllers\AccountController::deleteAvailabilityJournalEntry($availability);
        $availability->delete();

        Notification::create([
            'user_id' => Auth::id(),
            'message' => "حذف إتاحة للفندق: {$hotelName} (بتاريخ : {$availability->start_date->format('d/m/Y')} إلى {$availability->end_date->format('d/m/Y')}) (ID: {$availabilityId})",
            'type' => 'حذف إتاحة',
        ]);

        return redirect()->route('admin.availabilities.index')->with('success', 'تم حذف الإتاحة بنجاح!');
    }


private function generateDailyStatuses(AvailabilityRoomType $roomType, Availability $availability): void
{
    $startDate = Carbon::parse($availability->start_date);
    $endDate   = Carbon::parse($availability->end_date);
    $allotment = $roomType->allotment ?? 1;

    $statuses = [];
    for ($date = $startDate->copy(); $date->lt($endDate); $date->addDay()) {
        $statuses[] = [
            'availability_room_type_id' => $roomType->id,
            'date'                      => $date->format('Y-m-d'),
            'available_rooms'           => $allotment,
            'booked_rooms'              => 0,
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }

    // إدخال bulk لتحسين الأداء
    AvailabilityDailyStatus::insert($statuses);

    Log::info("تم إنشاء " . count($statuses) . " سجل daily status للـ RoomType ID: {$roomType->id}");
}





// ============================================================
// جلب الإتاحات النشطة لفندق معين (AJAX)
// ============================================================
public function getHotelActive(Request $request)
{
    $request->validate(['hotel_id' => 'required|exists:hotels,id']);

    $availabilities = Availability::where('hotel_id', $request->hotel_id)
        ->where('status', 'active')
        ->with(['availabilityRoomTypes.roomType', 'agent'])
        ->get()
        ->map(fn($av) => [
            'id'              => $av->id,
            'agent_name'      => $av->agent->name ?? '-',
            'start_date'      => Carbon::parse($av->start_date)->format('Y-m-d'),
            'end_date'        => Carbon::parse($av->end_date)->format('Y-m-d'),
            'start_formatted' => Carbon::parse($av->start_date)->format('d/m/Y'),
            'end_formatted'   => Carbon::parse($av->end_date)->format('d/m/Y'),
            'room_types'      => $av->availabilityRoomTypes->map(fn($rt) => [
                'room_type_id'   => $rt->room_type_id,
                'room_type_name' => $rt->roomType->room_type_name ?? '',
                'allotment'      => $rt->allotment,
                'cost_price'     => $rt->cost_price,
            ])->values(),
        ]);

    return response()->json([
        'success'        => true,
        'availabilities' => $availabilities,
    ]);
}

}