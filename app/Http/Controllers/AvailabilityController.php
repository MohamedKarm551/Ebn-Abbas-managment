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
            'voucher_number' => 'required|string|max:100',
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
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو تاريخ البداية.',
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

        if (!empty($validatedData['min_nights'])) {
            $start = Carbon::parse($validatedData['start_date']);
            $end   = Carbon::parse($validatedData['end_date']);
            $nights = abs($end->diffInDays($start));

            if ($nights < $validatedData['min_nights']) {
                throw ValidationException::withMessages([
                    'min_nights' => "عدد الليالي بين تاريخ البداية والنهاية هو {$nights} ليلة، بينما أقل عدد ليالي مطلوب هو {$validatedData['min_nights']} ليلة.",
                    'end_date'   => "تاريخ النهاية يجب أن يكون بعد تاريخ البداية بما لا يقل عن {$validatedData['min_nights']} ليلة/ليالي."
                ]);
            }
        }


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
            'voucher_number' => $validatedData['voucher_number'],
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
            'voucher_number' => 'required|string|max:100',
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

        $originalData = $availability->getOriginal();
        $originalRoomTypes = $availability->availabilityRoomTypes()
            ->get(['id', 'cost_price', 'sale_price', 'allotment', 'currency'])
            ->toArray();

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
        $availabilityData['voucher_number'] = $validatedData['voucher_number'];
        if (!empty($validatedData['min_nights'])) {
            $start = Carbon::parse($validatedData['start_date']);
            $end   = Carbon::parse($validatedData['end_date']);
            $nights = abs($end->diffInDays($start));
    
            if ($nights < $validatedData['min_nights']) {
                throw ValidationException::withMessages([
                    'min_nights' => "عدد الليالي بين تاريخ البداية والنهاية هو {$nights} ليلة، بينما أقل عدد ليالي مطلوب هو {$validatedData['min_nights']} ليلة.",
                    'end_date'   => "تاريخ النهاية يجب أن يكون بعد تاريخ البداية بما لا يقل عن {$validatedData['min_nights']} ليلة/ليالي."
                ]);
            }
        }


        $dbNewStart = Carbon::parse($dbStartDate);
        $dbNewEnd   = Carbon::parse($dbEndDate);

        // جلب كل الحجوزات المرتبطة بالإتاحة
        $roomTypeIds = $availability->availabilityRoomTypes()->pluck('id');
        $conflictingBookings = \App\Models\Booking::whereIn('availability_room_type_id', $roomTypeIds)
            ->where(function ($q) use ($dbNewStart, $dbNewEnd) {
                // الحجز خرج كلياً: check_in بعد النهاية الجديدة أو check_out قبل البداية الجديدة
                $q->where('check_in', '>=', $dbNewEnd)
                  ->orWhere('check_out', '<=', $dbNewStart);
            })
            ->get(['id', 'client_name', 'check_in', 'check_out']);

        if ($conflictingBookings->isNotEmpty()) {
            $details = $conflictingBookings->map(fn($b) =>
                "حجز #{$b->id} ({$b->client_name}): " .
                Carbon::parse($b->check_out)->format('d/m/Y'). " → " .
                Carbon::parse($b->check_in)->format('d/m/Y')
            )->implode(' | ');

            throw ValidationException::withMessages([
                'start_date' => "⚠️ لا يمكن تعديل نطاق الإتاحة. الحجوزات التالية ستخرج خارج النطاق الجديد، يرجى تعديلها أو حذفها أولاً:\n{$details}",
            ]);
        }


        // Update availability main data
        $availability->update([
            'hotel_id' => $validatedData['hotel_id'],
            'agent_id' => $validatedData['agent_id'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'status' => $validatedData['status'],
            'notes' => $validatedData['notes'],
            'min_nights' => $validatedData['min_nights'] ?? null,
            'voucher_number' => $validatedData['voucher_number'],
        ]);

        $oldVoucher = $originalData['voucher_number'] ?? null;
        $newVoucher = $availability->voucher_number;
        if ($oldVoucher !== $newVoucher) {
            \App\Models\Booking::whereIn('availability_room_type_id', $roomTypeIds)
                ->get()
                ->each(function ($booking) use ($newVoucher) {
                    $cleanName = trim(preg_replace('/\s+\d+$/', '', $booking->client_name));
                    $booking->update([
                        'client_name' => $cleanName . ' ' .  $newVoucher
                    ]);
                });
        }

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

        $availability->refresh();
        $newRoomTypes = $availability->availabilityRoomTypes()
            ->get(['id', 'cost_price', 'sale_price', 'allotment', 'currency'])
            ->toArray();

        $agentChanged = (int)$originalData['agent_id'] !== (int)$availability->agent_id;
        $datesChanged = 
            Carbon::parse($originalData['start_date'])->format('Y-m-d') 
                !== $availability->start_date->format('Y-m-d') ||
            Carbon::parse($originalData['end_date'])->format('Y-m-d')   
                !== $availability->end_date->format('Y-m-d');
        $normalizeRoomTypes = fn($arr) => collect($arr)->map(fn($r) => [
                'id'         => (int)($r['id'] ?? 0),
                'cost_price' => round((float)($r['cost_price'] ?? 0), 4),
                'sale_price' => round((float)($r['sale_price'] ?? 0), 4),
                'allotment'  => (int)($r['allotment'] ?? 0),
                'currency'   => (string)($r['currency'] ?? 'SAR'),
            ])->sortBy('id')->values()->toArray();
        $pricesChanged = $normalizeRoomTypes($originalRoomTypes) != $normalizeRoomTypes($newRoomTypes);

       if ($agentChanged || $pricesChanged || $datesChanged) {
        
        // تحديث قيد الإتاحة
        try {
            \App\Http\Controllers\AccountController::updateAvailabilityJournalEntry($availability);
        } catch (\Exception $e) {
            Log::error("فشل تحديث القيد المحاسبي للإتاحة ID: {$availability->id} - " . $e->getMessage());
        }

        // تحديث الحجوزات المرتبطة
        $this->updateLinkedBookings($availability, $originalData);

        Log::info("تم تحديث القيود للإتاحة ID: {$availability->id}", [
            'agent_changed'  => $agentChanged,
            'prices_changed' => $pricesChanged,
            'dates_changed'  => $datesChanged,
        ]);
        } else {
            Log::info("لم يتم تحديث القيود - لا يوجد تغيير مؤثر للإتاحة ID: {$availability->id}");
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

private function updateLinkedBookings(Availability $availability, array $originalData): void
{
    $hotelChanged  = $originalData['hotel_id']   != $availability->hotel_id;
    $agentChanged  = $originalData['agent_id']   != $availability->agent_id;
    $datesChanged  = $originalData['start_date'] != $availability->start_date->format('Y-m-d') ||
                     $originalData['end_date']   != $availability->end_date->format('Y-m-d');

    $roomTypeIds = $availability->availabilityRoomTypes()->pluck('id');
    if ($roomTypeIds->isEmpty()) return;

    $bookings = \App\Models\Booking::whereIn('availability_room_type_id', $roomTypeIds)
        ->with(['company', 'agent', 'hotel'])
        ->get();

    if ($bookings->isEmpty()) return;

    $newStart = \Carbon\Carbon::parse($availability->start_date);
    $newEnd   = \Carbon\Carbon::parse($availability->end_date);

    foreach ($bookings as $booking) {
        $updateData      = [];
        $bookingCheckIn  = \Carbon\Carbon::parse($booking->check_in);
        $bookingCheckOut = \Carbon\Carbon::parse($booking->check_out);

        // ✅ تحديث الفندق
        if ($hotelChanged) {
            $updateData['hotel_id'] = $availability->hotel_id;
        }

        // ✅ تحديث الجهة
        if ($agentChanged) {
            $updateData['agent_id'] = $availability->agent_id;
        }

        // ✅ معالجة التواريخ لو اتغيرت
        if ($datesChanged) {
            // الحجز خرج كلياً عن الإتاحة الجديدة
            if ($bookingCheckIn->gte($newEnd) || $bookingCheckOut->lte($newStart)) {
                Log::warning("الحجز ID: {$booking->id} خرج كلياً عن نطاق الإتاحة الجديدة");
                \App\Models\Notification::create([
                    'message' => "⚠️ الحجز #{$booking->id} للعميل {$booking->client_name} خرج عن نطاق الإتاحة بعد تعديل التواريخ. يرجى المراجعة.",
                    'type'    => 'تحذير حجز',
                ]);
                continue; // تخطى هذا الحجز
            }

            // ضبط check_in لو قبل البداية الجديدة
            if ($bookingCheckIn->lt($newStart)) {
                $bookingCheckIn = $newStart->copy();
                $updateData['check_in'] = $bookingCheckIn->format('Y-m-d');
            }

            // ضبط check_out لو بعد النهاية الجديدة
            if ($bookingCheckOut->gt($newEnd)) {
                $bookingCheckOut = $newEnd->copy();
                $updateData['check_out'] = $bookingCheckOut->format('Y-m-d');
            }
        }

        // ✅ تحديث الأسعار والمبالغ دايماً
        $roomTypeInfo = \App\Models\AvailabilityRoomType::find($booking->availability_room_type_id);
        if ($roomTypeInfo) {
            $days = max(1, $bookingCheckIn->diffInDays($bookingCheckOut));
            $newAmountDue = $roomTypeInfo->sale_price * $booking->rooms * $days;
            $newAmountToHotel = $roomTypeInfo->cost_price * $booking->rooms * $days;

            $priceActuallyChanged = 
                (float)$booking->cost_price !== (float)$roomTypeInfo->cost_price ||
                (float)$booking->sale_price !== (float)$roomTypeInfo->sale_price ||
                (float)$booking->amount_due_from_company !== (float)$newAmountDue;

            if ($priceActuallyChanged) {
                $updateData['days']                    = $days;
                $updateData['cost_price']              = $roomTypeInfo->cost_price;
                $updateData['sale_price']              = $roomTypeInfo->sale_price;
                $updateData['currency']                = $roomTypeInfo->currency;
                $updateData['amount_due_to_hotel']     = $newAmountToHotel;
                $updateData['amount_due_from_company'] = $newAmountDue;
            }
        }

        if (!empty($updateData)) {
            $booking->update($updateData);
                    
            $shouldUpdateJournal = $agentChanged 
                || isset($updateData['amount_due_from_company']) 
                || (isset($updateData['check_in']) || isset($updateData['check_out']));
                    
            if ($shouldUpdateJournal) {
                try {
                    \App\Http\Controllers\AccountController::updateBookingJournalEntry($booking);
                } catch (\Exception $e) {
                    Log::error("فشل تحديث قيد الحجز ID: {$booking->id} - " . $e->getMessage());
                }
            }
        }
    }

    Log::info("تم تحديث الحجوزات المرتبطة بالإتاحة ID: {$availability->id}", [
        'hotel_changed' => $hotelChanged,
        'agent_changed' => $agentChanged,
        'dates_changed' => $datesChanged,
        'bookings_count' => $bookings->count(),
    ]);
}

}