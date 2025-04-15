<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Hotel;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\EditLog;
use Illuminate\Support\Facades\Log; // تأكد من استيراد Log
use Illuminate\Support\Facades\File;
use Carbon\Carbon; // *** استيراد Carbon لمعالجة التواريخ ***

class BookingsController extends Controller
{
    /**
     * عرض قائمة الحجوزات مع إمكانية البحث والفلترة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // --------------------------------------------------
        // 1. بناء الاستعلام الأساسي وتحميل العلاقات (Eager Loading)
        // --------------------------------------------------
        // نبدأ ببناء استعلام Eloquent لجدول الحجوزات.
        // نستخدم `with` لتحميل العلاقات مسبقًا (Eager Loading). هذا يمنع مشكلة N+1
        // ويحسن الأداء عن طريق تقليل عدد الاستعلامات لقاعدة البيانات عند الوصول للعلاقات لاحقًا.
        $query = Booking::with(['company', 'employee', 'agent', 'hotel']);

        // --------------------------------------------------
        // 2. تطبيق فلتر البحث النصي (إذا كان موجودًا)
        // --------------------------------------------------
        // نتحقق مما إذا كان حقل البحث 'search' يحتوي على قيمة.
        if ($request->filled('search')) {
            // نحصل على قيمة البحث من الطلب.
            $searchTerm = $request->input('search');

            // نستخدم `where` مع دالة Closure لتجميع شروط البحث معًا باستخدام `OR`.
            // هذا يضمن أن البحث يتم في أي من الحقول المحددة.
            $query->where(function ($q) use ($searchTerm) {
                // البحث في اسم العميل مباشرة في جدول الحجوزات.
                $q->where('client_name', 'like', "%{$searchTerm}%")
                  // البحث في اسم الموظف المرتبط (عبر العلاقة 'employee').
                  ->orWhereHas('employee', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  })
                  // البحث في اسم الشركة المرتبطة (عبر العلاقة 'company').
                  ->orWhereHas('company', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  })
                  // البحث في اسم جهة الحجز المرتبطة (عبر العلاقة 'agent').
                  ->orWhereHas('agent', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  })
                  // البحث في اسم الفندق المرتبط (عبر العلاقة 'hotel').
                  ->orWhereHas('hotel', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // --------------------------------------------------
        // 3. تطبيق فلتر تاريخ البدء (إذا كان موجودًا وصحيحًا)
        // --------------------------------------------------
        // نتحقق مما إذا كان حقل 'start_date' يحتوي على قيمة.
        if ($request->filled('start_date')) {
            try {
                // نحاول تحويل التاريخ القادم من الفورم (المتوقع أن يكون بصيغة 'd/m/Y')
                // إلى كائن Carbon ثم إلى صيغة 'Y-m-d' التي تفهمها قاعدة البيانات.
                // نستخدم `startOfDay` لضمان أن المقارنة تشمل بداية اليوم المحدد.
                $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();

                // نضيف شرط `whereDate` إلى الاستعلام الحالي.
                // هذا الشرط يضمن أن تاريخ الدخول 'check_in' أكبر من أو يساوي تاريخ البدء المحدد.
                // `whereDate` يقارن جزء التاريخ فقط من العمود.
                $query->whereDate('check_in', '>=', $startDate);

            } catch (\Exception $e) {
                // في حالة فشل تحويل التاريخ (صيغة غير صحيحة)، نسجل الخطأ.
                // يمكن اختياريًا إعادة المستخدم للخلف مع رسالة خطأ.
                Log::error('Invalid start date format: ' . $request->input('start_date') . ' - ' . $e->getMessage());
                // return redirect()->back()->withErrors(['start_date' => 'صيغة تاريخ البدء غير صحيحة.']);
            }
        }

        // --------------------------------------------------
        // 4. تطبيق فلتر تاريخ الانتهاء (إذا كان موجودًا وصحيحًا)
        // --------------------------------------------------
        // نتحقق مما إذا كان حقل 'end_date' يحتوي على قيمة.
        if ($request->filled('end_date')) {
            try {
                // نحاول تحويل التاريخ القادم من الفورم (المتوقع أن يكون بصيغة 'd/m/Y')
                // إلى كائن Carbon ثم إلى صيغة 'Y-m-d'.
                // نستخدم `endOfDay` لضمان أن المقارنة تشمل نهاية اليوم المحدد.
                $endDate = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay();

                // نضيف شرط `whereDate` إلى الاستعلام الحالي.
                // هذا الشرط يضمن أن تاريخ الدخول 'check_in' (أو 'check_out' حسب المنطق المطلوب)
                // أصغر من أو يساوي تاريخ الانتهاء المحدد.
                // *** ملاحظة: الكود الأصلي كان يستخدم 'check_out'، تم تغييره إلى 'check_in' ليتوافق مع فلتر تاريخ البدء. عدّله إذا كان المقصود فلترة تاريخ الخروج. ***
                $query->whereDate('check_in', '<=', $endDate);

            } catch (\Exception $e) {
                // في حالة فشل تحويل التاريخ، نسجل الخطأ.
                Log::error('Invalid end date format: ' . $request->input('end_date') . ' - ' . $e->getMessage());
                // return redirect()->back()->withErrors(['end_date' => 'صيغة تاريخ الانتهاء غير صحيحة.']);
            }
        }

        // --------------------------------------------------
        // 5. تطبيق فلاتر إضافية (حسب الحاجة)
        // --------------------------------------------------
        // نضيف شروط `where` بسيطة لتطبيق الفلاتر الأخرى إذا كانت موجودة في الطلب.

        // فلترة حسب الشركة
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        // فلترة حسب جهة الحجز
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->input('agent_id'));
        }

        // فلترة حسب الفندق
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }

        // فلترة حسب الموظف
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        // --------------------------------------------------
        // 6. الترتيب وتنفيذ الاستعلام مع Pagination
        // --------------------------------------------------
        // نرتب النتائج حسب تاريخ الدخول تنازليًا (الأحدث أولاً).
        // يمكنك تغيير حقل الترتيب والاتجاه حسب الحاجة (مثل 'created_at').
        $query->orderBy('created_at', 'desc');

        // ننفذ الاستعلام ونجلب النتائج باستخدام `paginate`.
        // `paginate(20)` يجلب 20 نتيجة لكل صفحة.
        // `withQueryString()` يضمن أن روابط الـ pagination تحتفظ بجميع بارامترات الفلترة الحالية (search, start_date, etc.).
        $bookings = $query->paginate(10)->withQueryString();
        // فحص إذا كان الطلب AJAX
    if ($request->wantsJson()) {
        // إرجاع جزء HTML من الجدول وروابط الـ pagination
        return response()->json([
            'table' => view('bookings._table', ['bookings' => $bookings])->render(),
            'pagination' => $bookings->links()->toHtml(),
        ]);
    }

        // --------------------------------------------------
        // 7. حساب الإجماليات (ملاحظة: قد يكون غير دقيق مع Pagination)
        // --------------------------------------------------
        // *** تحذير: الكود التالي يحسب الإجماليات بناءً على نتائج الصفحة الحالية فقط (`$bookings`) وليس على كامل نتائج الاستعلام قبل الـ pagination. ***
        // *** للحصول على إجماليات دقيقة لكل النتائج المطابقة للفلاتر، يجب حسابها باستخدام `$query` قبل استدعاء `paginate` أو باستخدام استعلامات aggregate منفصلة. ***
        $totalDueFromCompany = 0;
        $totalPaidByCompany = 0;
        $remainingFromCompany = 0;

        $totalDueToHotels = 0;
        $totalPaidToHotels = 0;
        $remainingToHotels = 0;

        // يتم استخدام `map` للمرور على حجوزات الصفحة الحالية وحساب التفاصيل والإجماليات (للصفحة الحالية فقط).
        $bookingDetails = $bookings->map(function ($booking) use (&$totalDueFromCompany, &$totalPaidByCompany, &$remainingFromCompany, &$totalDueToHotels, &$totalPaidToHotels, &$remainingToHotels) {
            // حساب المستحق من الشركة لهذا الحجز
            $dueFromCompany = $booking->days * $booking->rooms * $booking->sale_price;
            $paidByCompany = $booking->amount_paid_by_company;
            $remainingFromCompany += $dueFromCompany - $paidByCompany;

            // حساب المستحق للفندق لهذا الحجز
            $dueToHotel = $booking->days * $booking->rooms * $booking->cost_price;
            $paidToHotel = $booking->amount_paid_to_hotel;
            $remainingToHotels += $dueToHotel - $paidToHotel;

            // تجميع الإجماليات (للصفحة الحالية)
            $totalDueFromCompany += $dueFromCompany;
            $totalPaidByCompany += $paidByCompany;

            $totalDueToHotels += $dueToHotel;
            $totalPaidToHotels += $paidToHotel;

            // إرجاع مصفوفة بتفاصيل الحجز المحسوبة (اختياري، حسب حاجة الفيو)
            return [
                'id' => $booking->id, // إضافة ID للاستخدام في الفيو
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

        // --------------------------------------------------
        // 8. تمرير البيانات إلى الفيو
        // --------------------------------------------------
        // نعيد عرض الفيو 'bookings.index' ونمرر له:
        // - `$bookings`: كائن الـ Paginator الذي يحتوي على حجوزات الصفحة الحالية وروابط الصفحات الأخرى.
        // - الإجماليات المحسوبة (للصفحة الحالية).
        // - `$bookingDetails`: مجموعة التفاصيل المحسوبة (اختياري).
        return view('bookings.index', compact(
            'bookings', // كائن الـ Paginator
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
        // ترتيب الشركات تصاعديًا حسب الاسم
        $companies = Company::orderBy('name', 'asc')->get();

        // ترتيب جهات الحجز تصاعديًا حسب الاسم
        $agents = Agent::orderBy('name', 'asc')->get();

        // ترتيب الفنادق تصاعديًا حسب الاسم
        $hotels = Hotel::orderBy('name', 'asc')->get();

        // ترتيب الموظفين تصاعديًا حسب الاسم
        $employees = Employee::orderBy('name', 'asc')->get();

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

        // حساب القيم المالية
        // التحقق من القيم قبل الحساب
        if ($validatedData['days'] <= 0 || $validatedData['rooms'] <= 0 || $validatedData['cost_price'] <= 0 || $validatedData['sale_price'] <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['calculation_error' => 'تأكد من إدخال قيم صحيحة للحساب']);
        }
        // حساب المبالغ
        $validatedData['amount_due_to_hotel'] = $validatedData['cost_price'] * $validatedData['rooms'] * $validatedData['days'];
        $validatedData['amount_due_from_company'] = $validatedData['sale_price'] * $validatedData['rooms'] * $validatedData['days'];

        // تتبع القيم المحسوبة
        Log::info('القيم المحسوبة:', [
            'amount_due_to_hotel' => $validatedData['amount_due_to_hotel'],
            'amount_due_from_company' => $validatedData['amount_due_from_company'],
        ]);
        // dd($validatedData);
        // إنشاء الحجز
        $booking = Booking::create($validatedData);
        // تتبع القيم المحفوظة
        Log::info('القيم المحفوظة في قاعدة البيانات:', $booking->toArray());
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
            'cost_price' => 'required|numeric',// لو الحجز اتكنسل ادخل عدل السعر صفر هيقبل
            'sale_price' => 'required|numeric', //لو الحجز اتكنسل دلوقت تعمل تعديل السعر بصفر
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
        $validatedData['amount_due_to_hotel'] = $validatedData['cost_price'] * $validatedData['rooms'] * $validatedData['days'];
        $validatedData['amount_due_from_company'] = $validatedData['sale_price'] * $validatedData['rooms'] * $validatedData['days'];
        // تسجيل التعديلات
        foreach ($validatedData as $field => $newValue) {
            $oldValue = $booking->$field;

            // تجاهل الحقول المحسوبة ديناميكيًا

            if (in_array($field, ['amount_due_to_hotel', 'amount_due_from_company'])) {
                continue;
            }
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
