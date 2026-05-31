<?php

namespace App\Http\Controllers;

use App\Models\AvailabilityDailyStatus;
use App\Models\AvailabilityRoomType;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AvailabilityDailyStatusController extends Controller
{
    /**
     * Get daily status for a specific availability room type
     */
    public function getDailyStatus(int $availabilityRoomTypeId): JsonResponse
{
    $roomType = AvailabilityRoomType::with('availability')->findOrFail($availabilityRoomTypeId);
    
    $dailyStatus = AvailabilityDailyStatus::where('availability_room_type_id', $availabilityRoomTypeId)
        ->orderBy('date')
        ->get()
        ->map(function ($status) {
            return [
                'date' => $status->date->format('Y-m-d'),
                'available_rooms' => $status->available_rooms,
                'booked_rooms' => $status->booked_rooms,
                'remaining_rooms' => $status->available_rooms - $status->booked_rooms,
                'is_fully_booked' => $status->booked_rooms >= $status->available_rooms,
            ];
        });

    $availableDates = $dailyStatus
        ->where('remaining_rooms', '>', 0)
        ->pluck('date')
        ->toArray();

    // *** إضافة: حساب الفترات المتاحة (consecutive available periods) ***
    $availablePeriods = [];
    $currentPeriod = [];
    
    foreach ($dailyStatus as $day) {
        if ($day['remaining_rooms'] > 0) {
            $currentPeriod[] = $day['date'];
        } else {
            if (!empty($currentPeriod)) {
                $availablePeriods[] = [
                    'start' => $currentPeriod[0],
                    'end' => $currentPeriod[count($currentPeriod) - 1],
                    'days' => count($currentPeriod),
                ];
                $currentPeriod = [];
            }
        }
    }
    
    // أضف آخر فترة لو موجودة
    if (!empty($currentPeriod)) {
        $availablePeriods[] = [
            'start' => $currentPeriod[0],
            'end' => $currentPeriod[count($currentPeriod) - 1],
            'days' => count($currentPeriod),
        ];
    }

    return response()->json([
        'availability_room_type_id' => $availabilityRoomTypeId,
        'daily_status' => $dailyStatus,
        'available_dates' => $availableDates,
        'available_periods' => $availablePeriods, // *** الفترات المتاحة ***
        'first_available_date' => $availableDates[0] ?? null,
        'last_available_date' => $availableDates[count($availableDates) - 1] ?? null,
    ]);
}

    /**
     * Check available dates for a specific period
     */
    public function checkAvailableDates(int $availabilityRoomTypeId): JsonResponse
    {
        $request = request();
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');
        $rooms = $request->input('rooms', 1);

        if (!$checkIn || !$checkOut) {
            return response()->json([
                'error' => 'check_in and check_out are required'
            ], 422);
        }

        $startDate = Carbon::parse($checkIn);
        $endDate = Carbon::parse($checkOut);
        $days = $startDate->diffInDays($endDate);
        $requestedRooms = (int) $rooms;

        $availability = [];
        $isAvailable = true;
        $minAvailableRooms = PHP_INT_MAX;

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            
            $dailyStatus = AvailabilityDailyStatus::where('availability_room_type_id', $availabilityRoomTypeId)
                ->whereDate('date', $currentDate)
                ->first();

            $remainingRooms = $dailyStatus ? ($dailyStatus->available_rooms - $dailyStatus->booked_rooms) : 0;
            $dayAvailable = $remainingRooms >= $requestedRooms;
            
            if (!$dayAvailable) {
                $isAvailable = false;
            }
            
            $minAvailableRooms = min($minAvailableRooms, $remainingRooms);

            $availability[] = [
                'date' => $currentDate->format('Y-m-d'),
                'available' => $dayAvailable,
                'remaining_rooms' => $remainingRooms,
                'requested_rooms' => $requestedRooms,
            ];
        }

        return response()->json([
            'availability_room_type_id' => $availabilityRoomTypeId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'requested_rooms' => $requestedRooms,
            'is_available' => $isAvailable,
            'min_available_rooms' => $minAvailableRooms,
            'days' => $availability,
        ]);
    }
}