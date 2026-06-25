<?php
namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Booking;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;

class RoomAssignmentController extends Controller
{
    public function index(Trip $trip)
    {
        $unassigned = $trip->bookings()
            ->whereNull('room_assignment_id')
            ->get();

        $rooms = $trip->roomAssignments()
            ->with('bookings')
            ->orderBy('room_number')
            ->get();

        return view('room-assignments.index', compact('trip', 'unassigned', 'rooms'));
    }

    public function addRoom(Request $request, Trip $trip)
    {
        $request->validate([
            'room_type'   => 'required|in:single,double,triple,quad,quintuple,sextuple',
            'room_number' => 'required',
        ]);

        $capacityMap = [
            'single'    => 1,
            'double'    => 2,
            'triple'    => 3,
            'quad'      => 4,
            'quintuple' => 5,
            'sextuple'  => 6,
        ];

        $roomTypeArabic = [
        'single'    => 'فردية',
        'double'    => 'ثنائية',
        'triple'    => 'ثلاثية',
        'quad'      => 'رباعية',
        'quintuple' => 'خماسية',
        'sextuple'  => 'سداسية',
        ];

        // السعة دايمًا من الـ map، مفيش حاجة يدوية
        $capacity = $capacityMap[$request->room_type];
        $arabicType = $roomTypeArabic[$request->room_type];

        RoomAssignment::create([
            'trip_id'     => $trip->id,
            'room_number' => $request->room_number,
            'room_type'   => $arabicType,
            'capacity'    => $capacity,
        ]);

        return back()->with('success', '✅ تم إضافة الغرفة');
    }

    public function updateRoom(Request $request, RoomAssignment $room)
    {
        $request->validate(['room_number' => 'required']);
        $room->update(['room_number' => $request->room_number]);
        return back()->with('success', '✅ تم تعديل رقم الغرفة');
    }

    public function deleteRoom(RoomAssignment $room)
    {
        $room->bookings()->update(['room_assignment_id' => null]);
        $room->delete();
        return back()->with('success', '🗑️ تم حذف الغرفة');
    }

    public function assign(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'room_id'    => 'required|exists:room_assignments,id',
            'is_family'  => 'boolean',
        ]);

        $booking = Booking::find($request->booking_id);
        $room    = RoomAssignment::with('bookings')->find($request->room_id);

        $isChildOrInfant = in_array($booking->accommodation_type, ['طفل', 'رضيع']);

        // تحقق أن نوع الغرفة يطابق نوع الحجز
        if (!$isChildOrInfant && $room->room_type !== $booking->accommodation_type) {
            $typeLabels = [
                'single'    => 'فردية',
                'double'    => 'ثنائية',
                'triple'    => 'ثلاثية',
                'quad'      => 'رباعية',
                'quintuple' => 'خماسية',
                'sextuple'  => 'سداسية',
            ];
            $bookingLabel = $typeLabels[$booking->accommodation_type] ?? $booking->accommodation_type;
            $roomLabel    = $typeLabels[$room->room_type] ?? $room->room_type;

            return response()->json([
                'type_mismatch' => true,
                'message' => "هذا العميل حاجز <strong>{$bookingLabel}</strong> ولا يمكن تسكينه في غرفة من نوع <strong>{$roomLabel}</strong>.",
            ]);
        }

        // تحقق من الطاقة
        if ($room->availableSpots() <= 0) {
            return response()->json(['error' => '⚠️ الغرفة ممتلئة!']);
        }

        // تحقق من الجنس المختلط
        $existingGenders = $room->bookings->pluck('gender')->unique();
        $hasOppGender = $existingGenders->contains(function ($g) use ($booking) {
            return in_array($g, ['male', 'female']) && $g !== $booking->gender;
        });

        if ($hasOppGender && !$request->is_family) {
            return response()->json([
                'warning' => true,
                'message' => 'أنت بتضيف أنواع مختلفة مع بعض، هل دول أسرة؟',
            ]);
        }

        $booking->update([
            'room_assignment_id' => $room->id,
            'is_family'          => $request->is_family ?? false,
        ]);

        // لو أسرة، خلي كل أهل الغرفة أسرة
        if ($request->is_family) {
            $room->bookings()
                ->where('id', '!=', $booking->id)
                ->update(['is_family' => true]);
        } else {
            // لو في الغرفة بالفعل أسرة، اربط الجديد بيهم تلقائيًا
            $hasFamilyInRoom = $room->bookings()->where('is_family', true)->exists();
            if ($hasFamilyInRoom) {
                $booking->update(['is_family' => true]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function unassign(Booking $booking)
    {
        $booking->update([
            'room_assignment_id' => null,
            'is_family'          => false,
        ]);
        return back()->with('success', '✅ تم إلغاء التسكين');
    }
}