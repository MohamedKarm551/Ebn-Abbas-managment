<?php
namespace App\Imports;

use App\Models\Booking;
use App\Models\Agent;
use App\Models\Hotel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BookingsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // البحث عن agent_id بناءً على اسم جهة الحجز
        $agent = Agent::where('name', $row['agent'])->first();

        if (!$agent) {
            throw new \Exception("Agent with name '{$row['agent']}' not found.");
        }

        // البحث عن hotel_id بناءً على اسم الفندق
        $hotel = Hotel::find($row['hotel_id']);

        if (!$hotel) {
            throw new \Exception("Hotel with ID {$row['hotel_id']} not found.");
        }

        return new Booking([
            'client_name' => $row['client_name'], // اسم العميل
            'hotel_id' => $hotel->id, // رقم الفندق
            'room_number' => $row['room_number'], // نوع الغرفة
            'check_in' => \Carbon\Carbon::parse($row['check_in']), // تاريخ الدخول
            'check_out' => \Carbon\Carbon::parse($row['check_out']), // تاريخ الخروج
            'days' => $row['days'], // عدد الأيام
            'rooms' => $row['rooms'], // عدد الغرف
            'agent_id' => $agent->id, // البحث عن agent_id
            'cost_price' => $row['cost_price'], // سعر التكلفة
            'sale_price' => $row['sale_price'], // سعر البيع
            'payment_status' => $row['payment_status'], // حالة الدفع
            'notes' => $row['notes'] ?? null, // الملاحظات
        ]);
    }
}