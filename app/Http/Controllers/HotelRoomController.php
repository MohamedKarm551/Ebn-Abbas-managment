<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\RoomAssignment;
use App\Models\Booking;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotelRoomController extends Controller
{
    /**
     * عرض حالة الغرف لجميع الفنادق
     */
    public function index()
    {
        $hotels = Hotel::with(['rooms.currentBooking.booking.company'])->get();

        // حساب إحصائيات لكل فندق
        foreach ($hotels as $hotel) {
            $hotel->total_rooms = $hotel->rooms->count();
            $hotel->occupied_rooms = $hotel->rooms->where('is_occupied', true)->count();
            $hotel->available_rooms = $hotel->total_rooms - $hotel->occupied_rooms;

            if ($hotel->total_rooms > 0) {
                $hotel->occupancy_rate = round(($hotel->occupied_rooms / $hotel->total_rooms) * 100);
            } else {
                $hotel->occupancy_rate = 0;
            }
        }

        // جلب الحجوزات النشطة التي لم تخصص لها غرف بعد
        $unassignedBookings = Booking::whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>', now())
            ->whereDoesntHave('roomAssignment', function ($q) {
                $q->where('status', 'active');
            })
            ->with(['company', 'agent', 'hotel', 'roomAssignments'])
            ->orderBy('check_in')
            ->get();

        // تنظيم الحجوزات غير المخصصة حسب الفندق لسهولة عرضها
        $unassignedBookingsByHotel = $unassignedBookings->groupBy('hotel_id');

        // النزلاء الحاليين (مع تفاصيل الحجز)
        $currentGuests = RoomAssignment::where('status', 'active')
            ->with(['booking.company', 'booking.agent', 'room.hotel'])
            ->orderBy('check_in')
            ->get();

        return view('admin.hotels.rooms.index', compact('hotels', 'currentGuests', 'unassignedBookings', 'unassignedBookingsByHotel'));
    }

    /**
     * عرض تفاصيل فندق معين مع غرفه
     */
    public function showHotel($id)
    {
        $hotel = Hotel::with(['rooms.currentBooking.booking.company'])->findOrFail($id);

        // حساب إحصائيات للفندق
        $hotel->total_rooms = $hotel->rooms->count();
        $hotel->occupied_rooms = $hotel->rooms->where('is_occupied', true)->count();
        $hotel->available_rooms = $hotel->total_rooms - $hotel->occupied_rooms;
        $hotel->occupancy_rate = $hotel->total_rooms > 0
            ? round(($hotel->occupied_rooms / $hotel->total_rooms) * 100)
            : 0;

        // تجميع الغرف حسب الطابق للعرض المنظم
        $roomsByFloor = $hotel->rooms->groupBy('floor');

        // الغرف المتاحة حاليًا
        $availableRooms = $hotel->rooms->where('is_occupied', false);

        // النزلاء الحاليين في هذا الفندق
        $currentGuests = RoomAssignment::where('status', 'active')
            ->whereHas('room', function ($q) use ($id) {
                $q->where('hotel_id', $id);
            })
            ->with(['booking.company', 'booking.agent', 'room'])
            ->orderBy('check_in')
            ->get();

        // الحجوزات التي تحتاج لتخصيص غرف في هذا الفندق
        $unassignedBookings = Booking::whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>', now())
            ->where('hotel_id', $id)
            ->whereDoesntHave('roomAssignment', function ($q) {
                $q->where('status', 'active');
            })
            ->with(['company', 'agent', 'roomAssignments' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('check_in')
            ->get();

        return view('admin.hotels.rooms.hotel', compact(
            'hotel',
            'roomsByFloor',
            'currentGuests',
            'unassignedBookings',
            'availableRooms'
        ));
    }

    /**
     * عرض تفاصيل غرفة معينة
     */
    public function showRoom($id)
    {
        $room = HotelRoom::with(['hotel', 'currentBooking.booking.company', 'allBookings.booking'])->findOrFail($id);

        // التحقق من وجود علاقة hotel
        if (!$room->hotel) {
            return redirect()->route('hotel.rooms.index')
                ->with('error', 'لم يتم العثور على معلومات الفندق المرتبط بهذه الغرفة');
        }
        // كل حجز خرج غير حالته إلى completed
        $room->allBookings->each(function ($booking) {
            if ($booking->check_out < now()) {
                $booking->update(['status' => 'completed']);
            }
        });

        // قائمة الحجوزات النشطة المتاحة للتخصيص - فقط لنفس فندق الغرفة
        $availableBookings = Booking::whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>', now())
            ->where('hotel_id', $room->hotel_id)
            ->whereDoesntHave('roomAssignment', function ($q) {
                $q->where('status', 'active');
            })
            ->with(['company', 'agent'])
            ->orderBy('check_in', 'desc')
            ->get();

        // حساب سعة الغرفة والسعة المتبقية
        $roomType = $room->type;
        $maxGuests = $this->getRoomBedsCount($roomType);
        // حساب عدد النزلاء الحاليين في الغرفة
        $currentGuestsCount = RoomAssignment::where('hotel_room_id', $room->id)
            ->where('status', 'active')
            ->count();
        $remainingCapacity = $maxGuests - $currentGuestsCount;

        return view('admin.hotels.rooms.show', compact(
            'room',
            'availableBookings',
            'maxGuests',
            'currentGuestsCount',
            'remainingCapacity'
        ));
    }

    /**
     * إنشاء غرف متعددة لفندق
     */
    public function createRooms(Request $request)
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|min:1|gte:start_number',
            'floor' => 'nullable|string|max:10',
            'type' => 'required|in:single,double,triple,quad,quint,standard,suite,deluxe,family',
        ]);

        $hotel = Hotel::findOrFail($validated['hotel_id']);
        $count = 0;

        DB::beginTransaction();

        try {
            // إنشاء الغرف بالأرقام المحددة
            for ($i = $validated['start_number']; $i <= $validated['end_number']; $i++) {
                $roomNumber = str_pad($i, 3, '0', STR_PAD_LEFT);

                // التحقق من عدم وجود غرفة بنفس الرقم
                $exists = HotelRoom::where('hotel_id', $hotel->id)
                    ->where('room_number', $roomNumber)
                    ->exists();

                if (!$exists) {
                    HotelRoom::create([
                        'hotel_id' => $hotel->id,
                        'room_number' => $roomNumber,
                        'floor' => $validated['floor'],
                        'type' => $validated['type'],
                        'status' => 'available',
                    ]);
                    $count++;
                }
            }

            // إنشاء إشعار
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم إضافة {$count} غرفة جديدة لفندق {$hotel->name}",
                'type' => 'إضافة غرف',
            ]);

            DB::commit();
            // تحديث قيمة purchased_rooms_count تلقائيًا
            $totalRoomsCount = $hotel->rooms()->count();
            $hotel->update([
                'purchased_rooms_count' => $totalRoomsCount
            ]);
            return redirect()->route('hotel.rooms.hotel', $hotel->id)
                ->with('success', "تم إضافة {$count} غرفة بنجاح");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'حدث خطأ أثناء إضافة الغرف: ' . $e->getMessage());
        }
    }
    public function edit(HotelRoom $room)
    {
        // نجلب الفندق المرتبط بهذه الغرفة للعرض والعودة للقائمة
        $hotel = $room->hotel;

        return view('admin.hotels.rooms.edit', compact('room', 'hotel'));
    }

    /**
     * 2. استقبال عملية الحفظ بعد التعديل.
     *    Route::patch('/hotels/rooms/{room}', [HotelRoomController::class, 'update'])->name('hotel.rooms.update');
     */
    public function update(Request $request, HotelRoom $room)
    {
        // 2.1. التحقق من صحة البيانات الواردة
        $validated = $request->validate([
            // unique: نتأكد من عدم وجود رقم غرفة مكرر لنفس الفندق باستثناء السجل الحالي
            'room_number' => 'required|string|max:3|unique:hotel_rooms,room_number,'
                . $room->id . ',id,hotel_id,' . $room->hotel_id,
            'floor'       => 'nullable|string|max:10',
            'type'        => 'required|in:single,double,triple,quad,quint,standard,suite,deluxe,family',
            'status'      => 'required|in:available,occupied,maintenance',
            'notes'       => 'nullable|string',
        ]);

        // 2.2. تحديث بيانات الغرفة
        $room->update([
            'room_number' => $validated['room_number'],
            'floor'       => $validated['floor'],
            'type'        => $validated['type'],
            'status'      => $validated['status'],
            'notes'       => $validated['notes'] ?? $room->notes,
        ]);

        // 2.3. إنشاء إشعار (اختياري)
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "تم تعديل بيانات الغرفة رقم {$room->room_number} في فندق {$room->hotel->name}",
            'type'    => 'تعديل غرفة',
        ]);

        // 2.4. إعادة التوجيه إلى صفحة قائمة غرف الفندق مع رسالة نجاح
        return redirect()->route('hotel.rooms.hotel', $room->hotel_id)
            ->with('success', "تم حفظ التعديلات على الغرفة {$room->room_number} بنجاح");
    }

    /**
     * 3. حذف غرفة نهائيًا.
     *    Route::delete('/hotels/rooms/{room}', [HotelRoomController::class, 'destroy'])->name('hotel.rooms.destroy');
     */
    public function destroy(HotelRoom $room)
    {
        $hotelName  = $room->hotel->name;
        $roomNumber = $room->room_number;
        $hotelId    = $room->hotel_id;

        // 3.1. حذف الغرفة من قاعدة البيانات
        $room->delete();

        // 3.2. إنشاء إشعار (اختياري)
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "تم حذف الغرفة رقم {$roomNumber} من فندق {$hotelName}",
            'type'    => 'حذف غرفة',
        ]);

        // 3.3. إعادة التوجيه مع رسالة نجاح
        return redirect()->route('hotel.rooms.hotel', $hotelId)
            ->with('success', "تم حذف الغرفة {$roomNumber} بنجاح");
    }
    /**
     * تخصيص حجز لغرفة
     */
    public function assignRoom(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:hotel_rooms,id',
            'booking_id' => 'required|exists:bookings,id',
            'notes' => 'nullable|string',
        ]);

        $room = HotelRoom::findOrFail($validated['room_id']);
        $booking = Booking::findOrFail($validated['booking_id']);

        // التحقق من أن الغرفة غير مشغولة
        // استخدام الدالة الجديدة للتحقق من التوفر
        if (!$this->isRoomAvailableForBooking($room)) {
            if ($room->status === 'maintenance') {
                return back()->with('error', "الغرفة {$room->room_number} في حالة صيانة");
            } else {
                return back()->with('error', "الغرفة {$room->room_number} ممتلئة بالكامل");
            }
        }

        DB::beginTransaction();

        try {
            // إنشاء تخصيص جديد
            $assignment = RoomAssignment::create([
                'hotel_room_id' => $room->id,
                'booking_id' => $booking->id,
                'check_in' => $booking->check_in,
                'check_out' => $booking->check_out,
                'status' => 'active',
                'notes' => $validated['notes'],
                'assigned_by' => Auth::id(),
            ]);

            // تحديث حالة الغرفة بناءً على السعة المتبقية بعد الإضافة
            $bedsCount = $this->getRoomBedsCount($room->type);
            $occupiedBeds = RoomAssignment::where('hotel_room_id', $room->id)
                ->where('status', 'active')
                ->count();

            if ($occupiedBeds >= $bedsCount) {
                $room->update(['status' => 'occupied']);
            }

            // إنشاء إشعار
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم تخصيص الغرفة {$room->room_number} بفندق {$room->hotel->name} للعميل {$booking->client_name}",
                'type' => 'تخصيص غرفة',
            ]);

            DB::commit();
            return redirect()->route('hotel.rooms.hotel', $room->hotel_id)
                ->with('success', "تم تخصيص الغرفة للعميل {$booking->client_name} بنجاح");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'حدث خطأ أثناء تخصيص الغرفة: ' . $e->getMessage());
        }
    }

    /**
     * تخصيص غرف متعددة لحجز
     */
    public function assignMultipleRooms(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'room_ids' => 'required|array',
            'room_ids.*' => 'required|exists:hotel_rooms,id',
            'notes' => 'nullable|string',
        ]);

        $booking = Booking::with('roomAssignments')->findOrFail($validated['booking_id']);

        // التحقق من أن عدد الغرف المحددة لا يتجاوز العدد المطلوب في الحجز
        $currentAssignedRoomsCount = $booking->roomAssignments()->where('status', 'active')->count();
        $selectedRoomsCount = count($validated['room_ids']);

        if ($currentAssignedRoomsCount + $selectedRoomsCount > $booking->rooms) {
            return back()->with('error', 'عدد الغرف المحددة يتجاوز العدد المطلوب في الحجز');
        }

        // التحقق من أن كل الغرف المحددة متاحة
        foreach ($validated['room_ids'] as $roomId) {
            $room = HotelRoom::findOrFail($roomId);
            // بدلاً من التحقق من الحالة فقط، نتحقق من السعة المتبقية
            if ($room->status === 'maintenance') {
                return back()->with('error', "الغرفة {$room->room_number} في حالة صيانة");
            }

            // حساب السعة المتبقية في الغرفة
            $bedsCount = $this->getRoomBedsCount($room->type);
            $occupiedBeds = RoomAssignment::where('hotel_room_id', $room->id)
                ->where('status', 'active')
                ->count();
            $remainingCapacity = $bedsCount - $occupiedBeds;

            if ($remainingCapacity <= 0) {
                return back()->with('error', "الغرفة {$room->room_number} ممتلئة بالكامل");
            }
        }

        DB::beginTransaction();

        try {
            $assignedRooms = [];
            $hotelId = null;

            // تخصيص كل غرفة من الغرف المحددة
            foreach ($validated['room_ids'] as $roomId) {
                $room = HotelRoom::findOrFail($roomId);
                $hotelId = $room->hotel_id;

                // إنشاء تخصيص جديد
                $assignment = RoomAssignment::create([
                    'hotel_room_id' => $room->id,
                    'booking_id' => $booking->id,
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                    'status' => 'active',
                    'notes' => $validated['notes'],
                    'assigned_by' => Auth::id(),
                ]);

                // تحديث حالة الغرفة بناءً على السعة المتبقية
                $bedsCount = $this->getRoomBedsCount($room->type);
                $occupiedBeds = RoomAssignment::where('hotel_room_id', $room->id)
                    ->where('status', 'active')
                    ->count();

                // إذا امتلأت الغرفة بالكامل، غيّر حالتها إلى "occupied"
                if ($occupiedBeds >= $bedsCount) {
                    $room->update(['status' => 'occupied']);
                }

                // إذا لم تمتلئ بعد، لا نغير الحالة (تبقى كما هي)


                $assignedRooms[] = $room->room_number;
            }

            // إنشاء إشعار
            $roomNumbers = implode(', ', $assignedRooms);
            $notificationMessage = "تم تخصيص {$selectedRoomsCount} غرفة ({$roomNumbers}) للنزيل {$booking->client_name}";

            Notification::create([
                'user_id' => Auth::id(),
                'message' => $notificationMessage,
                'type' => 'تخصيص غرف',
            ]);

            DB::commit();
            return redirect()->route('hotel.rooms.hotel', $hotelId)
                ->with('success', "تم تخصيص {$selectedRoomsCount} غرفة للنزيل {$booking->client_name} بنجاح");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'حدث خطأ أثناء تخصيص الغرف: ' . $e->getMessage());
        }
    }

    /**
     * الحصول على الغرف المتاحة لفندق معين عبر API
     */
    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $hotelId = $request->hotel_id;

        $rooms = HotelRoom::where('hotel_id', $hotelId)
            ->where('status', '!=', 'maintenance')
            ->get()
            ->filter(function ($room) {
                return $this->isRoomAvailableForBooking($room);
            })
            ->map(function ($room) {
                $bedsCount = $this->getRoomBedsCount($room->type);
                $occupiedBeds = RoomAssignment::where('hotel_room_id', $room->id)
                    ->where('status', 'active')
                    ->count();

                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'floor' => $room->floor,
                    'type' => $room->type,
                    'type_ar' => $this->getArabicRoomTypeName($room->type),
                    'beds_count' => $bedsCount,
                    'occupied_beds' => $occupiedBeds,
                    'remaining_capacity' => $bedsCount - $occupiedBeds
                ];
            });

        return response()->json([
            'success' => true,
            'rooms' => $rooms
        ]);
    }

    /**
     * إنهاء تخصيص غرفة (خروج مبكر)
     */
    public function endAssignment($id)
    {
        $assignment = RoomAssignment::findOrFail($id);
        $room = $assignment->room;

        DB::beginTransaction();

        try {
            // تحديث حالة التخصيص
            $assignment->update([
                'status' => 'completed',
                'check_out' => now(),
            ]);

            // التحقق من السعة المتبقية بعد الإخلاء
            $bedsCount = $this->getRoomBedsCount($room->type);
            $remainingOccupiedBeds = RoomAssignment::where('hotel_room_id', $room->id)
                ->where('status', 'active')
                ->count();

            if ($remainingOccupiedBeds == 0) {
                $room->update(['status' => 'available']);
            } else {
                $room->update(['status' => 'occupied']); // لا تزال مشغولة جزئياً
            }
            // إنشاء إشعار
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم إخلاء الغرفة {$room->room_number} بفندق {$room->hotel->name} (خروج مبكر)",
                'type' => 'إخلاء غرفة',
            ]);

            DB::commit();
            return redirect()->route('hotel.rooms.hotel', $room->hotel_id)
                ->with('success', "تم إخلاء الغرفة بنجاح");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'حدث خطأ أثناء إخلاء الغرفة: ' . $e->getMessage());
        }
    }

    /**
     * إضافة نزيل إضافي لغرفة مشتركة
     */
    public function addGuest(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:hotel_rooms,id',
            'booking_id' => 'required|exists:bookings,id',
            'notes' => 'nullable|string',
        ]);

        $room = HotelRoom::with('currentBooking.booking')->findOrFail($validated['room_id']);
        $booking = Booking::findOrFail($validated['booking_id']);

        // التحقق من أن الغرفة مشغولة ولديها تخصيص نشط
        if (!$room->is_occupied || !$room->currentBooking) {
            return back()->with('error', 'يجب أن تكون الغرفة مشغولة بالفعل لإضافة نزلاء إضافيين');
        }

        // التحقق من سعة الغرفة
        $roomType = $room->type;
        $maxGuests = $this->getRoomBedsCount($roomType);
        $currentGuestsCount = $room->currentBooking->booking->guests_count ?? 1;

        if ($currentGuestsCount >= $maxGuests) {
            return back()->with('error', 'لا يمكن إضافة نزلاء آخرين، الغرفة ممتلئة بالفعل');
        }

        DB::beginTransaction();

        try {
            // تحديث عدد النزلاء في الحجز الحالي للغرفة
            $room->currentBooking->booking->update([
                'guests_count' => $currentGuestsCount + 1,
            ]);
            // إنشاء تخصيص جديد للنزيل الإضافي في نفس الغرفة
            $newAssignment = RoomAssignment::create([
                'hotel_room_id' => $room->id,
                'booking_id' => $booking->id,
                'check_in' => $room->currentBooking->check_in,
                'check_out' => $room->currentBooking->check_out,
                'status' => 'active',
                'notes' => $validated['notes'] ? $validated['notes'] . ' (نزيل إضافي)' : 'نزيل إضافي في غرفة مشتركة',
                'assigned_by' => Auth::id(),
            ]);

            // تحديث حالة الحجز المختار للإشارة أنه تم استخدامه
            $booking->update([
                'status' => 'checked_in',
            ]);

            // إضافة ملاحظة بالنزيل الجديد في تخصيص الغرفة الحالية
            $originalNotes = $room->currentBooking->notes ?? '';
            $additionalNotes = "تم إضافة نزيل إضافي من الحجز {$booking->id} ({$booking->client_name}) بتاريخ " . now()->format('Y-m-d H:i');

            $room->currentBooking->update([
                'notes' => $originalNotes . "\n" . $additionalNotes
            ]);


            // إنشاء إشعار
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم إضافة نزيل إضافي من الحجز {$booking->id} ({$booking->client_name}) للغرفة {$room->room_number} بفندق {$room->hotel->name}",
                'type' => 'إضافة نزيل',
            ]);

            DB::commit();
            return redirect()->route('hotel.rooms.show', $room->id)
                ->with('success', "تم إضافة النزيل {$booking->client_name} كنزيل إضافي بنجاح");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'حدث خطأ أثناء إضافة النزيل: ' . $e->getMessage());
        }
    }

    /**
     * الحصول على الاسم العربي لنوع الغرفة
     */
    private function getArabicRoomTypeName($type)
    {
        $namesMapping = [
            'single' => 'فردية',
            'double' => 'زوجية',
            'triple' => 'ثلاثية',
            'quad' => 'رباعية',
            'quint' => 'خماسية',
            // دعم القيم القديمة للتوافق
            'standard' => 'قياسية',
            'deluxe' => 'ديلوكس',
            'suite' => 'جناح',
            'family' => 'عائلية',
        ];
        return $namesMapping[$type] ?? $type;
    }

    /**
     * الحصول على عدد الأسرة بناءً على نوع الغرفة
     */
    private function getRoomBedsCount($type)
    {
        $bedsMapping = [
            'single' => 1,
            'double' => 2,
            'triple' => 3,
            'quad' => 4,
            'quint' => 5,
            // دعم القيم القديمة للتوافق
            'standard' => 2,
            'deluxe' => 3,
            'suite' => 2,
            'family' => 4,
        ];
        return $bedsMapping[$type] ?? 2;
    }
    private function isRoomAvailableForBooking($room)
    {
        // إذا كانت الغرفة في صيانة، فهي غير متاحة
        if ($room->status === 'maintenance') {
            return false;
        }

        // حساب السعة المتبقية
        $bedsCount = $this->getRoomBedsCount($room->type);
        $occupiedBeds = RoomAssignment::where('hotel_room_id', $room->id)
            ->where('status', 'active')
            ->count();

        return ($bedsCount - $occupiedBeds) > 0;
    }
}
