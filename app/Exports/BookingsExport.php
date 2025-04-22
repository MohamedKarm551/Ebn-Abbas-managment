<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingsExport implements FromQuery, WithHeadings, WithMapping
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
        // --- نفس منطق الاستعلام الموجود في BookingsController@index ---
        $query = Booking::with(['company', 'agent', 'hotel', 'employee']); // جلب النشط فقط (بدون onlyTrashed)

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
        // فلترة تاريخ البدء
        if ($this->request->filled('start_date')) {
            try {
                $startDate = Carbon::createFromFormat('d/m/Y', $this->request->input('start_date'))->startOfDay();
                $query->where('check_in', '>=', $startDate);
            } catch (\Exception $e) {
                // تجاهل التاريخ غير الصحيح أو سجل خطأ
            }
        }
        // فلترة تاريخ الانتهاء
        if ($this->request->filled('end_date')) {
            try {
                $endDate = Carbon::createFromFormat('d/m/Y', $this->request->input('end_date'))->endOfDay();
                $query->where('check_out', '<=', $endDate);
            } catch (\Exception $e) {
                // تجاهل التاريخ غير الصحيح أو سجل خطأ
            }
        }
        // فلترة البحث العام
        if ($this->request->filled('search')) {
            $searchTerm = $this->request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('client_name', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%")
                  ->orWhereHas('company', fn($sq) => $sq->where('name', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('agent', fn($sq) => $sq->where('name', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('hotel', fn($sq) => $sq->where('name', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('employee', fn($sq) => $sq->where('name', 'like', "%{$searchTerm}%"));
            });
        }

        return $query->orderBy('check_in', 'desc'); // ترتيب حسب تاريخ الدخول أو أي ترتيب تفضله
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
            'المستحق للفندق',
            'مطلوب من الشركة',
            'الموظف المسؤول',
            'الملاحظات',
            'تاريخ الإنشاء',
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
            $booking->amount_due_to_hotel ?? '0', // تأكد من وجود هذه الأعمدة المحسوبة في الموديل أو احسبها هنا
            $booking->amount_due_from_company ?? '0', // تأكد من وجود هذه الأعمدة المحسوبة في الموديل أو احسبها هنا
            $booking->employee->name ?? '-',
            $booking->notes ?? '-',
            $booking->created_at ? $booking->created_at->format('Y-m-d H:i') : '-',
        ];
    }
}