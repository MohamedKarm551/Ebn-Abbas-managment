<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Hotel;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\EditLog; 
use Illuminate\Support\Facades\File;

class BookingsController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['company', 'employee', 'agent', 'hotel']);

        // البحث باستخدام النصوص
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('client_name', 'like', '%' . $search . '%')
                ->orWhereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('company', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('agent', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('hotel', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
        }

        // فلترة بالتاريخ
        if ($request->filled('start_date')) {
            $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
            $query->where('check_in', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d');
            $query->where('check_out', '<=', $endDate);
        }

        // فلترة حسب الشركة
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // فلترة حسب جهة الحجز
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // فلترة حسب الفندق
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        // فلترة حسب الموظف
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // ترتيب تصاعدي
        $query->orderBy('check_in', 'asc');

        $bookings = $query->get();

        // حساب الإجماليات
        $totalDueFromCompany = 0;
        $totalPaidByCompany = 0;
        $remainingFromCompany = 0;

        $totalDueToHotels = 0;
        $totalPaidToHotels = 0;
        $remainingToHotels = 0;

        $bookingDetails = $bookings->map(function ($booking) use (&$totalDueFromCompany, &$totalPaidByCompany, &$remainingFromCompany, &$totalDueToHotels, &$totalPaidToHotels, &$remainingToHotels) {
            $dueFromCompany = $booking->days * $booking->rooms * $booking->sale_price;
            $paidByCompany = $booking->amount_paid_by_company;
            $remainingFromCompany += $dueFromCompany - $paidByCompany;

            $dueToHotel = $booking->days * $booking->rooms * $booking->cost_price;
            $paidToHotel = $booking->amount_paid_to_hotel;
            $remainingToHotels += $dueToHotel - $paidToHotel;

            $totalDueFromCompany += $dueFromCompany;
            $totalPaidByCompany += $paidByCompany;

            $totalDueToHotels += $dueToHotel;
            $totalPaidToHotels += $paidToHotel;

            return [
                'client_name' => $booking->client_name,
                'check_in' => $booking->check_in->format('d/m/Y'),
                'check_out' => $booking->check_out->format('d/m/Y'),
                'days' => $booking->days,
                'rooms' => $booking->rooms,
                'sale_price' => $booking->sale_price,
                'cost_price' => $booking->cost_price,
                'due_from_company' => $dueFromCompany,
                'paid_by_company' => $paidByCompany,
                'remaining_from_company' => $dueFromCompany - $paidByCompany,
                'due_to_hotel' => $dueToHotel,
                'paid_to_hotel' => $paidToHotel,
                'remaining_to_hotel' => $dueToHotel - $paidToHotel,
            ];
        });

        return view('bookings.index', compact(
            'bookings',
            'totalDueFromCompany',
            'totalPaidByCompany',
            'remainingFromCompany',
            'totalDueToHotels',
            'totalPaidToHotels',
            'remainingToHotels',
            'bookingDetails'
        ));
    }

    public function create()
    {
        $companies = Company::all(); // جلب بيانات الشركات
        $agents = Agent::all(); // جلب بيانات جهات الحجز
        $hotels = Hotel::all(); // جلب بيانات الفنادق
        $employees = Employee::all(); // جلب بيانات الموظفين

        return view('bookings.create', compact('companies', 'agents', 'hotels', 'employees'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'client_name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'agent_id' => 'required|exists:agents,id',
            'hotel_id' => 'required|exists:hotels,id',
            'room_type' => 'required|string|max:255',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after_or_equal:check_in',
            'rooms' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        // تحويل التواريخ والحسابات
        try {
            $checkIn = \Carbon\Carbon::createFromFormat('Y-m-d', $request->check_in);
            $checkOut = \Carbon\Carbon::createFromFormat('Y-m-d', $request->check_out);

            $validatedData['check_in'] = $checkIn->format('Y-m-d');
            $validatedData['check_out'] = $checkOut->format('Y-m-d');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['date_error' => 'صيغة التاريخ غير صحيحة']);
        }
        // حساب عدد الأيام



        $validatedData['days'] = $checkIn->diffInDays($checkOut);

        // حساب المبالغ

        $validatedData['amount_due_to_hotel'] = $validatedData['cost_price'] * $validatedData['rooms'];

        // حساب المبلغ المستحق من الشركة

        $validatedData['amount_due_from_company'] = $validatedData['sale_price'] * $validatedData['rooms'];

        // إنشاء الحجز
        $booking = Booking::create($validatedData);

        // تجهيز البيانات للباك اب
        $booking->load(['company', 'agent', 'hotel', 'employee']);

        // محتوى ملف النصوص
        $textContent = sprintf(
            "
    === حجز جديد بتاريخ %s ===
    اسم العميل: %s
    الشركة: %s
    جهة الحجز: %s
    الفندق: %s
    تاريخ الدخول: %s
    تاريخ الخروج: %s
    عدد الغرف: %d
    عدد الأيام: %d
    سعر الفندق: %.2f
    سعر البيع: %.2f
    المبلغ المستحق للفندق: %.2f
    المبلغ المستحق من الشركة: %.2f
    الموظف: %s
    ملاحظات: %s
    =====================================\n\n",
            now()->format('d/m/Y H:i:s'),
            $booking->client_name,
            $booking->company->name,
            $booking->agent->name,
            $booking->hotel->name,
            \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y'),
            \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y'),
            $booking->rooms,
            $booking->days,
            $booking->cost_price,
            $booking->sale_price,
            $booking->amount_due_to_hotel,
            $booking->amount_due_from_company,
            $booking->employee->name,
            $booking->notes ?? 'لا يوجد'
        );

        // محتوى ملف CSV
        $csvContent = implode(',', [
            now()->format('d/m/Y H:i:s'),
            '"' . $booking->client_name . '"',
            '"' . $booking->company->name . '"',
            '"' . $booking->agent->name . '"',
            '"' . $booking->hotel->name . '"',
            '"' . \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') . '"',
            '"' . \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') . '"',
            $booking->rooms,
            $booking->days,
            $booking->cost_price,
            $booking->sale_price,
            $booking->amount_due_to_hotel,
            $booking->amount_due_from_company,
            '"' . $booking->employee->name . '"',
            '"' . ($booking->notes ?? '') . '"'
        ]) . "\n";

        // التأكد من وجود المجلدات
        $backupPath = storage_path('backups');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath);
            File::makeDirectory($backupPath . '/txt');
            File::makeDirectory($backupPath . '/csv');
        }

        // حفظ الملفات
        $txtPath = $backupPath . '/txt/bookings.txt';
        $csvPath = $backupPath . '/csv/bookings.csv';

        // إضافة العناوين للـ CSV لو مش موجود
        if (!File::exists($csvPath)) {
            File::put($csvPath, "التاريخ,العميل,الشركة,جهة الحجز,الفندق,تاريخ الدخول,تاريخ الخروج,عدد الغرف,عدد الأيام,سعر الفندق,سعر البيع,المستحق للفندق,المستحق من الشركة,الموظف,ملاحظات\n");
        }

        // حفظ البيانات
        File::append($txtPath, $textContent);
        File::append($csvPath, $csvContent);

        return redirect()->route('bookings.index')->with('success', 'تم إنشاء الحجز بنجاح!');
    }

    public function import(Request $request)
    {
        // التحقق من رفع ملف CSV
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($data);

        foreach ($data as $row) {
            $row = array_combine($header, $row);

            // التحقق من صحة البيانات
            $validated = [
                'client_name' => $row['client_name'],
                'agent_id' => Agent::where('name', $row['agent'])->first()->id ?? null,
                'hotel_id' => Hotel::where('id', $row['hotel_id'])->first()->id ?? null,
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out'],
                'rooms' => $row['rooms'],
                'cost_price' => $row['cost_price'],
                'sale_price' => $row['sale_price'],
                'payment_status' => $row['payment_status'],
                'notes' => $row['notes'] ?? null,
            ];

            // حساب عدد الأيام
            $checkIn = \Carbon\Carbon::parse($validated['check_in']);
            $checkOut = \Carbon\Carbon::parse($validated['check_out']);
            $validated['days'] = $checkIn->diffInDays($checkOut);

            // إنشاء الحجز
            Booking::create($validated);
        }

        return redirect()->route('bookings.index')->with('success', 'Bookings imported successfully!');
    }

    public function details($hotelId)
    {
        $bookings = Booking::where('hotel_id', $hotelId)->with(['agent', 'hotel'])->get();
        $hotel = Hotel::findOrFail($hotelId);

        return view('bookings.details', compact('bookings', 'hotel'));
    }

    public function edit($id)
    {
        $booking = Booking::findOrFail($id); // جلب بيانات الحجز
        $agents = Agent::all(); // جلب بيانات جهات الحجز
        $hotels = Hotel::all(); // جلب بيانات الفنادق
        $companies = Company::all(); // جلب بيانات الشركات
        $employees = Employee::all(); // جلب بيانات الموظفين

        return view('bookings.edit', compact('booking', 'agents', 'hotels', 'companies', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validatedData = $request->validate([
            'client_name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'agent_id' => 'required|exists:agents,id',
            'hotel_id' => 'required|exists:hotels,id',
            'room_type' => 'nullable|string|max:255',
            'check_in' => 'required|date_format:d/m/Y', // التحقق من صيغة يوم/شهر/سنة
            'check_out' => 'required|date_format:d/m/Y|after_or_equal:check_in',
            'rooms' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        // تحويل التواريخ إلى الصيغة المطلوبة (YYYY-MM-DD)
        $validatedData['check_in'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['check_in'])->format('Y-m-d');
        $validatedData['check_out'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['check_out'])->format('Y-m-d');

        // حساب عدد الأيام
        $checkIn = \Carbon\Carbon::parse($validatedData['check_in']);
        $checkOut = \Carbon\Carbon::parse($validatedData['check_out']);
        $validatedData['days'] = $checkIn->diffInDays($checkOut);

        // حساب المبالغ
        $validatedData['amount_due_to_hotel'] = $validatedData['cost_price'] * $validatedData['rooms'];
        $validatedData['amount_due_from_company'] = $validatedData['sale_price'] * $validatedData['rooms'];
        // تسجيل التعديلات
        foreach ($validatedData as $field => $newValue) {
            $oldValue = $booking->$field;

            //لو الحقل اللي تعدل هو التاريخ ابقا قارنه بعد التنسيق لانه كان بيعتبر كل مرة تغيير في التاريخ حتى لو انا معملتش تعديل
            if (in_array($field, ['check_in', 'check_out'])) {
                $oldValueFormatted = \Carbon\Carbon::parse($oldValue)->format('Y-m-d');
                $newValueFormatted = \Carbon\Carbon::parse($newValue)->format('Y-m-d');

                if ($oldValueFormatted != $newValueFormatted) {
                    \App\Models\EditLog::create([
                        'booking_id' => $booking->id,
                        'field' => $field,
                        'old_value' => $oldValueFormatted,
                        'new_value' => $newValueFormatted,
                    ]);
                }
            } else {
                if ($oldValue != $newValue) {
                    \App\Models\EditLog::create([
                        'booking_id' => $booking->id,
                        'field' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                    ]);
                }
            }
        }
        $booking->update($validatedData);
        // إضافة سجل التحديث للباك اب
    $textContent = sprintf(
        "\n=== تحديث حجز بتاريخ %s ===\nرقم الحجز: %d\n%s\n=====================================\n",
        now()->format('d/m/Y H:i:s'),
        $booking->id,
        json_encode($validatedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    
    File::append(storage_path('backups/txt/bookings_updates.txt'), $textContent);
    
    // تحديث في CSV
    $csvContent = implode(',', [
        now()->format('d/m/Y H:i:s'),
        'تحديث - ' . $booking->client_name,
        $booking->company->name,
        $booking->agent->name,
        $booking->hotel->name,
        $checkIn->format('d/m/Y'),
        $checkOut->format('d/m/Y'),
        $booking->rooms,
        $booking->days,
        $booking->cost_price,
        $booking->sale_price,
        $booking->amount_due_to_hotel,
        $booking->amount_due_from_company,
        $booking->employee->name,
        $booking->notes ?? ''
    ]) . "\n";
    
    File::append(storage_path('backups/csv/bookings.csv'), $csvContent);
        // روح على الرئيسية 
        return redirect()->route('bookings.index')->with('success', 'تم تحديث الحجز بنجاح!');
    }

    public function destroy($id)
    {
        $booking = Booking::with(['company', 'agent', 'hotel', 'employee'])->findOrFail($id);
    
    // تسجيل الحذف في الباك اب
    $textContent = sprintf(
        "\n=== حذف حجز بتاريخ %s ===\nرقم الحجز: %d\nاسم العميل: %s\nالشركة: %s\nالفندق: %s\n=====================================\n",
        now()->format('d/m/Y H:i:s'),
        $booking->id,
        $booking->client_name,
        $booking->company->name,
        $booking->hotel->name
    );
    
    File::append(storage_path('backups/txt/bookings_deleted.txt'), $textContent);
    
    // تسجيل الحذف في CSV
    $csvContent = implode(',', [
        now()->format('d/m/Y H:i:s'),
        'محذوف - ' . $booking->client_name,
        $booking->company->name,
        $booking->agent->name,
        $booking->hotel->name,
        $booking->check_in->format('d/m/Y'),
        $booking->check_out->format('d/m/Y'),
        $booking->rooms,
        $booking->days,
        $booking->cost_price,
        $booking->sale_price,
        $booking->amount_due_to_hotel,
        $booking->amount_due_from_company,
        $booking->employee->name,
        'تم الحذف'
    ]) . "\n";
    
    File::append(storage_path('backups/csv/bookings.csv'), $csvContent);

    $booking->delete();

    return redirect()->route('bookings.index')->with('success', 'تم حذف الحجز بنجاح!');
    }

    public function show($id)
    {
        $booking = Booking::with(['company', 'employee', 'agent', 'hotel'])->findOrFail($id);

        // جلب سجل التعديلات المرتبطة بالحجز
        $editLogs = \App\Models\EditLog::where('booking_id', $id)->orderBy('created_at', 'desc')->get();

        return view('bookings.show', compact('booking', 'editLogs', 'id'));
    }

    public function getEdits($id)
    {
        $edits = EditLog::where('booking_id', $id)->get(); // افترض أن لديك جدول لتسجيل التعديلات
        return response()->json($edits);
    }
}
