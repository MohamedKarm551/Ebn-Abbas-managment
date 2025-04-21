<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Hotel;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\EditLog;
use Illuminate\Support\Facades\Auth;
use App\Models\ArchivedBooking; // <--- 1. نتأكد من إضافة ArchivedBooking
use App\Models\Notification;
use Illuminate\Support\Facades\DB;  // <--- 2. نضيف DB للـ Transactions
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
        // تجاهل الحجوزات المؤرشفة (اللي سعرهم بصفر)
        $query->where('cost_price', '!=', 0)
            ->where('sale_price', '!=', 0);
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
        // 3 & 4. تطبيق فلترة التواريخ (بالمنطق الجديد)
        // --------------------------------------------------
        $startDate = null;
        $endDate = null;
        $startDateFilled = $request->filled('start_date');
        $endDateFilled = $request->filled('end_date');

        // بنحاول نقرا التواريخ لو موجودة
        if ($startDateFilled) {
            try {
                $startDate = Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();
                Log::info('[فلتر التواريخ] تاريخ البداية المطلوب: ' . $startDate->toDateString());
            } catch (\Exception $e) {
                Log::error('[فلتر التواريخ] تنسيق تاريخ البداية غير صحيح: ' . $request->input('start_date') . ' - ' . $e->getMessage());
                $startDateFilled = false; // بنعتبره مش موجود لو التنسيق غلط
            }
        }
        if ($endDateFilled) {
            try {
                $endDate = Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay(); // بنستخدم endOfDay هنا عشان يشمل اليوم كله
                Log::info('[فلتر التواريخ] تاريخ النهاية المطلوب: ' . $endDate->toDateString());
            } catch (\Exception $e) {
                Log::error('[فلتر التواريخ] تنسيق تاريخ النهاية غير صحيح: ' . $request->input('end_date') . ' - ' . $e->getMessage());
                $endDateFilled = false; // بنعتبره مش موجود لو التنسيق غلط
            }
        }

        // بنطبق الفلترة حسب الحالات المختلفة
        if ($startDateFilled && $endDateFilled) {
            // *** الحالة 1: المستخدم دخل تاريخ بداية ونهاية (المنطق الجديد للأوفرلاب) ***
            Log::info('[فلتر التواريخ] تطبيق فلتر الأوفرلاب من ' . $startDate->toDateString() . ' إلى ' . $endDate->toDateString());

            // الشرط الأول: تاريخ بداية الحجز يكون قبل أو يساوي نهاية الفترة
            $query->whereDate('check_in', '<=', $endDate);

            // الشرط الثاني: تاريخ نهاية الحجز يكون بعد أو يساوي بداية الفترة
            $query->whereDate('check_out', '>=', $startDate);
        } elseif ($startDateFilled) { // <--- بداية الحالة التانية (دي متغيرة)
            // *** الحالة 2: المستخدم دخل تاريخ بداية بس (عايزين اللي بدأ في اليوم ده بالظبط) ***
            Log::info('[فلتر التواريخ] تطبيق فلتر تاريخ الدخول = ' . $startDate->toDateString());
            $query->whereDate('check_in', '=', $startDate);
        } elseif ($endDateFilled) { // <--- بداية الحالة التالتة (دي متغيرة)
            // *** الحالة 3: المستخدم دخل تاريخ نهاية بس (عايزين اللي خلص في اليوم ده بالظبط) ***
            Log::info('[فلتر التواريخ] تطبيق فلتر تاريخ الخروج = ' . $endDate->toDateString());
            $query->whereDate('check_out', '=', $endDate->startOfDay()); // بنقارن بتاريخ اليوم بس
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
        // $query->orderBy('created_at', 'desc');



        // 6. الترتيب وتنفيذ الاستعلام مع Pagination
        if ($startDateFilled || $endDateFilled || $request->filled('search') || $request->filled('company_id') || $request->filled('agent_id') || $request->filled('hotel_id') || $request->filled('employee_id')) {
            // لو فيه فلترة، رتب حسب check_in تصاعدي
            $query->orderBy('check_in', 'asc');
        } else {
            // لو مفيش فلترة، رتب حسب created_at تنازلي
            $query->orderBy('created_at', 'desc');
        }

        // ==================================================
        // *** بداية الكود الجديد لحساب الإجماليات الدقيقة ***
        // ==================================================
        // 1. بنعمل نسخة من الاستعلام قبل الـ pagination
        $queryForTotals = clone $query;

        // 2. بنحسب الإجماليات على النسخة دي
        $totalCount = $queryForTotals->count();
        // **مهم:** تأكد إن أسماء الأعمدة دي صح في جدول bookings
        $totalDueFromCompanyAccurate = $queryForTotals->sum('amount_due_from_company') ?? 0;
        $totalPaidByCompanyAccurate = $queryForTotals->sum('amount_paid_by_company') ?? 0;
        $remainingFromCompanyAccurate = $totalDueFromCompanyAccurate - $totalPaidByCompanyAccurate;

        // 3. بنحسب إجماليات الفنادق بس لو مش بنفلتر بشركة
        $totalDueToHotelsAccurate = 0;
        $totalPaidToHotelsAccurate = 0;
        $remainingToHotelsAccurate = 0;
        if (!$request->filled('company_id')) {
            // **مهم:** تأكد إن أسماء الأعمدة دي صح في جدول bookings
            $totalDueToHotelsAccurate = $queryForTotals->sum('amount_due_to_hotel') ?? 0;
            $totalPaidToHotelsAccurate = $queryForTotals->sum('amount_paid_to_hotel') ?? 0;
            $remainingToHotelsAccurate = $totalDueToHotelsAccurate - $totalPaidToHotelsAccurate;
        }

        // 4. (اختياري) بنسجل الإجماليات في الـ log عشان نتأكد
        Log::info('[الإجماليات المحسوبة قبل Paginate] العدد: ' . $totalCount . ', مستحق من الشركة: ' . $totalDueFromCompanyAccurate);
        // ==================================================
        // *** نهاية الكود الجديد لحساب الإجماليات الدقيقة ***
        // ==================================================


        $totalDueToHotelsAll = $queryForTotals->sum('amount_due_to_hotel') ?? 0;
        $totalDueFromCompanyAll = $queryForTotals->sum('amount_due_from_company') ?? 0;
        $bookings = $query->paginate(10)->withQueryString();
        // فحص إذا كان الطلب AJAX
        if ($request->wantsJson() || $request->ajax()) {
            // الحفاظ على معلمات البحث في روابط الصفحات وتحديد قالب bootstrap-4 بشكل صريح
            $paginationLinks = $bookings->appends($request->all())
                ->onEachSide(1)
                ->links('vendor.pagination.bootstrap-4')
                ->toHtml();

            return response()->json([
                'table' => view('bookings._table', ['bookings' => $bookings])->render(),
                'pagination' => $paginationLinks,
                'totals' => [ // بنضيف مفتاح جديد اسمه totals
                    'count' => $totalCount,
                    'due_from_company' => $totalDueFromCompanyAccurate,
                    'paid_by_company' => $totalPaidByCompanyAccurate,
                    'remaining_from_company' => $remainingFromCompanyAccurate,
                    // بنرجع إجماليات الفنادق بس لو اتحسبت (يعني مش بنفلتر بشركة)
                    'due_to_hotels' => $request->filled('company_id') ? null : $totalDueToHotelsAccurate,
                    'paid_to_hotels' => $request->filled('company_id') ? null : $totalPaidToHotelsAccurate,
                    'remaining_to_hotels' => $request->filled('company_id') ? null : $remainingToHotelsAccurate,
                ]
            ]);
        }

        // --------------------------------------------------
        // 7. حساب الإجماليات (ملاحظة: قد يكون غير دقيق مع Pagination)
        // --------------------------------------------------
        // *** تحذير: الكود التالي يحسب الإجماليات بناءً على نتائج الصفحة الحالية فقط (`$bookings`) وليس على كامل نتائج الاستعلام قبل الـ pagination. ***
        // *** للحصول على إجماليات دقيقة لكل النتائج المطابقة للفلاتر، يجب حسابها باستخدام `$query` قبل استدعاء `paginate` أو باستخدام استعلامات aggregate منفصلة. ***

        // عدد الحجوزات النشطة (بعد الفلترة)
        $totalActiveBookingsCount = (clone $query)->count();

        // عدد الحجوزات المؤرشفة (بعد نفس الفلاتر)
        $archivedQuery = Booking::with(['company', 'employee', 'agent', 'hotel'])
            ->where('cost_price', 0)
            ->where('sale_price', 0);

        // طبق نفس الفلاتر على المؤرشفة
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $archivedQuery->where(function ($q) use ($searchTerm) {
                $q->where('client_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('employee', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('company', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('agent', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('hotel', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }
        if ($request->filled('company_id')) {
            $archivedQuery->where('company_id', $request->input('company_id'));
        }
        if ($request->filled('agent_id')) {
            $archivedQuery->where('agent_id', $request->input('agent_id'));
        }
        if ($request->filled('hotel_id')) {
            $archivedQuery->where('hotel_id', $request->input('hotel_id'));
        }
        if ($request->filled('employee_id')) {
            $archivedQuery->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('start_date')) {
            try {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();
                $archivedQuery->whereDate('check_in', '>=', $startDate);
            } catch (\Exception $e) {
            }
        }
        if ($request->filled('end_date')) {
            try {
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay();
                $archivedQuery->whereDate('check_out', '<=', $endDate);
            } catch (\Exception $e) {
            }
        }
        $totalArchivedBookingsCount = $archivedQuery->count(); // عدد الحجوزات المؤرشفة

        // --------------------------------------------------
        // 8. تمرير البيانات إلى الفيو
        // --------------------------------------------------
        // نعيد عرض الفيو 'bookings.index' ونمرر له:
        // - `$bookings`: كائن الـ Paginator الذي يحتوي على حجوزات الصفحة الحالية وروابط الصفحات الأخرى.
        // - الإجماليات المحسوبة (للصفحة الحالية).
        // - `$bookingDetails`: مجموعة التفاصيل المحسوبة (اختياري).
        return view('bookings.index', [
            'bookings' => $bookings, // كائن الـ Paginator
            'totalBookingsCount' => $totalCount, // العدد الكلي
            'totalDueFromCompany' => $totalDueFromCompanyAccurate,
            'totalPaidByCompany' => $totalPaidByCompanyAccurate,
            'remainingFromCompany' => $remainingFromCompanyAccurate,
            'totalDueToHotels' => $totalDueToHotelsAccurate, // هتبقى صفر لو فلترنا بشركة
            'totalPaidToHotels' => $totalPaidToHotelsAccurate, // هتبقى صفر لو فلترنا بشركة
            'remainingToHotels' => $remainingToHotelsAccurate, // هتبقى صفر لو فلترنا بشركة
            'totalActiveBookingsCount' => $totalActiveBookingsCount, // عدد الحجوزات النشطة
            'totalArchivedBookingsCount' => $totalArchivedBookingsCount, // عدد الحجوزات المؤرشفة
            'totalDueToHotelsAll' => $totalDueToHotelsAll,
            'totalDueFromCompanyAll' => $totalDueFromCompanyAll,
            // ممكن تشيل 'bookingDetails' لو مش بتستخدمها في الفيو بعد ما شيلنا الـ map
        ]);
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
        // تعقيم الملاحظات من أي أكواد HTML أو سكريبت
        // تعقيم كل الحقول النصية
        foreach (['notes', 'client_name'] as $field) {
            if (isset($validatedData[$field])) {
                $validatedData[$field] = htmlspecialchars($validatedData[$field], ENT_QUOTES, 'UTF-8');
            }
        }
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
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "حجز جديد: {$booking->client_name}، الفندق: {$booking->hotel->name}، الشركة: {$booking->company->name}",
            'type' => 'إضافة',
        ]);
        // return redirect()->route('bookings.index')->with('success', 'تم إنشاء الحجز بنجاح!');
        return redirect()->route('bookings.voucher', $booking->id)->with('success', 'تم إنشاء الحجز بنجاح! يمكنك طباعة الفاتورة الآن.');
    }
    public function voucher($id)
    {
        $booking = Booking::with(['company', 'agent', 'hotel', 'employee'])->findOrFail($id);
        return view('bookings.voucher', compact('booking'));
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
            'cost_price' => 'required|numeric', // لو الحجز اتكنسل ادخل عدل السعر صفر هيقبل
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
        // بعد حلقة foreach التي تسجل التعديلات في EditLog
        $changedFields = [];
        foreach ($validatedData as $field => $newValue) {
            $oldValue = $booking->getOriginal($field);

            if (in_array($field, ['amount_due_to_hotel', 'amount_due_from_company'])) {
                continue;
            }
            if (in_array($field, ['check_in', 'check_out'])) {
                $oldValueFormatted = \Carbon\Carbon::parse($oldValue)->format('Y-m-d');
                $newValueFormatted = \Carbon\Carbon::parse($newValue)->format('Y-m-d');

                if ($oldValueFormatted != $newValueFormatted) {
                    $changedFields[] = "$field: من $oldValueFormatted إلى $newValueFormatted";
                }
            } else {
                if ($oldValue != $newValue) {
                    $changedFields[] = "$field: من $oldValue إلى $newValue";
                }
            }
        }
         // تعقيم الملاحظات من أي أكواد HTML أو سكريبت
        // تعقيم كل الحقول النصية
        foreach (['notes', 'client_name'] as $field) {
            if (isset($validatedData[$field])) {
                $validatedData[$field] = htmlspecialchars($validatedData[$field], ENT_QUOTES, 'UTF-8');
            }
        }
        $booking->update($validatedData);
        // بعد $booking->update($validatedData);
        // إشعار للادمن
        $isArchived = $booking->cost_price == 0 && $booking->sale_price == 0;
        if ($isArchived) {
            \App\Models\Notification::create([
                'user_id' => Auth::user()->id,
                'message' => "تم أرشفة حجز للعميل: {$booking->client_name}، الفندق: {$booking->hotel->name}، الشركة: {$booking->company->name}",
                'type' => 'أرشفة حجز',
            ]);
        }
        // شوف ايه اللي اتعدل وابعت إشعاره للأدمن : 

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


        // إشعار عام إذا كان هناك تعديلات
        if (count($changedFields)) {
            Notification::create([
                'user_id' => Auth::user()->id,
                'message' => "تم تعديل حجز للعميل: {$booking->client_name}، التعديلات: " . implode(' | ', $changedFields),
                'type' => 'تعديل حجز',
            ]);
        }
        // روح على الرئيسية 
        return redirect()->route('bookings.index')->with('success', 'تم تحديث الحجز بنجاح!');
    }

    public function destroy($id)
    {
        // انا مستخدم أحدث تقنية من لاارافيل 12 عشان أحمل كل العلاقات مرة واحدة لتوفير أداء وسرعة
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
        // إشعار للأدمن 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => 'تم حذف حجز للعميل: ' . $booking->client_name . '، الفندق: ' . $booking->hotel->name . '، الشركة: ' . $booking->company->name,
            'type' => 'عملية حذف',
        ]);
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
