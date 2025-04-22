<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ArchivedBookingsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        // --- نفس منطق الاستعلام الموجود في AdminController@archivedBookings ---
        $query = Booking::onlyTrashed() // جلب المحذوف فقط
                 ->with(['company', 'agent', 'hotel', 'employee']);

        // فلترة الشركة
        if ($this->request->filled('company_id')) {
            $query->where('company_id', $this->request->input('company_id'));
        }
        // فلترة جهة الحجز
        if ($this->request->filled('agent_id')) {
            $query->where('agent_id', $this->request->input('agent_id'));
        }
        // فلترة الفندق
        if ($this->request->filled('hotel_id')) {
            $query->where('hotel_id', $this->request->input('hotel_id'));
        }
        // فلترة الموظف
        if ($this->request->filled('employee_id')) {
            $query->where('employee_id', $this->request->input('employee_id'));
        }

        // يمكنك إضافة فلترة التواريخ هنا إذا كانت مطلوبة في الأرشيف بنفس طريقة index

        return $query->orderBy('deleted_at', 'desc'); // ترتيب حسب تاريخ الحذف
        // --- نهاية منطق الاستعلام ---
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // رؤوس الأعمدة في ملف Excel
        return [
            'م',
            'العميل',
            'الشركة',
            'جهة حجز',
            'الفندق',
            'الدخول',
            'الخروج',
            'غرف',
            'الموظف المسؤول',
            'الملاحظات',
            'تاريخ الحذف', // أو آخر تحديث لو أردت
        ];
    }

    /**
    * @param mixed $booking
    * @return array
    */
    public function map($booking): array
    {
        // كيفية عرض كل صف في ملف Excel
        static $index = 0;
        $index++;
        return [
            $index,
            $booking->client_name,
            $booking->company->name ?? '-',
            $booking->agent->name ?? '-',
            $booking->hotel->name ?? '-',
            $booking->check_in ? Carbon::parse($booking->check_in)->format('d/m/Y') : '-',
            $booking->check_out ? Carbon::parse($booking->check_out)->format('d/m/Y') : '-',
            $booking->rooms ?? '-',
            $booking->employee->name ?? '-',
            $booking->notes ?? '-',
            $booking->deleted_at ? $booking->deleted_at->format('Y-m-d H:i') : '-', // تاريخ الحذف
        ];
    }
}
