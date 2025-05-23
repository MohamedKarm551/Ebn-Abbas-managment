<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Agent;
use App\Models\Hotel;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\EditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\ArchivedBooking; // <--- 1. نتأكد من إضافة ArchivedBooking
use App\Models\AvailabilityRoomType; // Import AvailabilityRoomType model
use Illuminate\Validation\ValidationException; // *** تأكد من وجود هذا السطر ***
use App\Models\RoomType;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;  // <--- 2. نضيف DB للـ Transactions
use Illuminate\Support\Facades\Log; // تأكد من استيراد Log
use Illuminate\Support\Facades\File;
use Carbon\Carbon; // *** استيراد Carbon لمعالجة التواريخ ***
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;

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

        // 1. بناء الاستعلام الأساسي وتحميل العلاقات (Eager Loading)
        // --------------------------------------------------
        // سطر 43: بنبدأ نبني الاستعلام لجدول الحجوزات وبنحمل العلاقات عشان الأداء
        $query = Booking::with(['company', 'employee', 'agent', 'hotel']);

        // سطر 45-46: بنستبعد الحجوزات المؤرشفة (اللي سعرها صفر)
        $query->where('cost_price', '!=', 0)
            ->where('sale_price', '!=', 0);

        // ==================================================
        // *** بداية الجزء المهم: فلترة حسب دور المستخدم ***
        // ==================================================
        // سطر 48: بنجيب بيانات المستخدم اللي عامل تسجيل دخول حالياً
        $user = Auth::user();

        // سطر 49: بنعمل شرط: هل دور المستخدم ده هو 'Company'؟
        if ($user->role === 'Company') {
            // لو الشرط ده صح (المستخدم شركة):

            // سطر 52: بنتحقق لو المستخدم ده مربوط بـ company_id في جدول users
            if ($user->company_id) {
                // لو مربوط:
                // سطر 54: بنضيف شرط للاستعلام ($query): هات بس الحجوزات اللي الـ company_id بتاعها بيساوي الـ company_id بتاع المستخدم ده.
                $query->where('company_id', $user->company_id);
            } else {
                // لو المستخدم شركة بس مش مربوط بـ company_id (حالة مش مفروض تحصل):
                // سطر 58: بنضيف شرط مستحيل يتحقق عشان نرجع نتيجة فاضية (أمان زيادة)
                $query->whereRaw('1 = 0');
                // سطر 59: بنسجل تحذير في الـ log عشان نعرف لو المشكلة دي حصلت
                Log::warning("المستخدم {$user->name} (ID: {$user->id}) دوره شركة لكن ليس لديه company_id.");
            }
        }

        // 2. تطبيق فلتر البحث النصي (إذا كان موجودًا)
        // --------------------------------------------------
        // نتحقق مما إذا كان حقل البحث 'search' يحتوي على قيمة.
        if ($request->filled('search')) {
            // نحصل على قيمة البحث من الطلب.
            $searchTerm = $request->input('search');
            $searchTerm = preg_replace('/[^\p{L}\p{N}\s\-_.,]/u', '', $searchTerm); // يزيل الأحرف الخاصة مع الحفاظ على النص الأساسي

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
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->startOfDay();
                Log::info('[فلتر التواريخ] تاريخ البداية المطلوب: ' . $startDate->toDateString());
            } catch (\Exception $e) {
                Log::error('[فلتر التواريخ] تنسيق تاريخ البداية غير صحيح: ' . $request->input('start_date') . ' - ' . $e->getMessage());
                $startDateFilled = false; // بنعتبره مش موجود لو التنسيق غلط
            }
        }
        if ($endDateFilled) {
            try {
                $endDate = Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->endOfDay(); // بنستخدم endOfDay هنا عشان يشمل اليوم كله
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
        // 3. حساب الإجماليات مفصلة حسب العملة
        $allBookings = $queryForTotals->get();

        // تجميع المستحق من الشركات حسب العملة
        $totalDueFromCompanyByCurrency = $allBookings->groupBy('currency')
            ->map(function ($currencyGroup) {
                return [
                    'currency' => $currencyGroup->first()->currency,
                    'amount' => $currencyGroup->sum('amount_due_from_company')
                ];
            })->values()->toArray();

        // تجميع المدفوع من الشركات حسب العملة
        $totalPaidByCompanyByCurrency = $allBookings->groupBy('currency')
            ->map(function ($currencyGroup) {
                return [
                    'currency' => $currencyGroup->first()->currency,
                    'amount' => $currencyGroup->sum('amount_paid_by_company')
                ];
            })->values()->toArray();

        // حساب المتبقي من الشركات حسب العملة
        $remainingFromCompanyByCurrency = [];
        foreach ($totalDueFromCompanyByCurrency as $dueItem) {
            $paid = collect($totalPaidByCompanyByCurrency)
                ->where('currency', $dueItem['currency'])
                ->first()['amount'] ?? 0;

            $remainingFromCompanyByCurrency[] = [
                'currency' => $dueItem['currency'],
                'amount' => $dueItem['amount'] - $paid
            ];
        }

        // للتوافق مع الكود القديم - المستحق من الشركات الإجمالي
        $totalDueFromCompanyAccurate = array_sum(array_column($totalDueFromCompanyByCurrency, 'amount'));
        $totalPaidByCompanyAccurate = array_sum(array_column($totalPaidByCompanyByCurrency, 'amount'));
        $remainingFromCompanyAccurate = $totalDueFromCompanyAccurate - $totalPaidByCompanyAccurate;

        // المستحق للفنادق بالعملات المختلفة
        $totalDueToHotelsByCurrency = [];
        $totalPaidToHotelsByCurrency = [];
        $remainingToHotelsByCurrency = [];


        if (!$request->filled('company_id')) {
            // تجميع المستحق للفنادق حسب العملة
            $totalDueToHotelsByCurrency = $allBookings->groupBy('currency')
                ->map(function ($currencyGroup) {
                    return [
                        'currency' => $currencyGroup->first()->currency,
                        'amount' => $currencyGroup->sum('amount_due_to_hotel')
                    ];
                })->values()->toArray();
            // تجميع المدفوع للفنادق حسب العملة
            $totalPaidToHotelsByCurrency = $allBookings->groupBy('currency')
                ->map(function ($currencyGroup) {
                    return [
                        'currency' => $currencyGroup->first()->currency,
                        'amount' => $currencyGroup->sum('amount_paid_to_hotel')
                    ];
                })->values()->toArray();

            // حساب المتبقي للفنادق حسب العملة
            foreach ($totalDueToHotelsByCurrency as $dueItem) {
                $paid = collect($totalPaidToHotelsByCurrency)
                    ->where('currency', $dueItem['currency'])
                    ->first()['amount'] ?? 0;

                $remainingToHotelsByCurrency[] = [
                    'currency' => $dueItem['currency'],
                    'amount' => $dueItem['amount'] - $paid
                ];
            }

            // للتوافق مع الكود القديم - المستحق للفنادق الإجمالي
            $totalDueToHotelsAccurate = array_sum(array_column($totalDueToHotelsByCurrency, 'amount'));
            $totalPaidToHotelsAccurate = array_sum(array_column($totalPaidToHotelsByCurrency, 'amount'));
            $remainingToHotelsAccurate = $totalDueToHotelsAccurate - $totalPaidToHotelsAccurate;
        } else {
            $totalDueToHotelsAccurate = 0;
            $totalPaidToHotelsAccurate = 0;
            $remainingToHotelsAccurate = 0;
        }

        // تجميع المستحق من الشركات في الصفحة الحالية حسب العملة
        $pageDueFromCompanyByCurrency = $allBookings->groupBy('currency')
            ->map(function ($currencyGroup) {
                return [
                    'currency' => $currencyGroup->first()->currency,
                    'amount' => $currencyGroup->sum('amount_due_from_company')
                ];
            })->values()->toArray();

        // تجميع المستحق للفنادق في الصفحة الحالية حسب العملة
        $pageDueToHotelsByCurrency = $allBookings->groupBy('currency')
            ->map(function ($currencyGroup) {
                return [
                    'currency' => $currencyGroup->first()->currency,
                    'amount' => $currencyGroup->sum('amount_due_to_hotel')
                ];
            })->values()->toArray();

        // 4. (اختياري) بنسجل الإجماليات في الـ log عشان نتأكد
        Log::info('[الإجماليات المحسوبة قبل Paginate] العدد: ' . $totalCount . ', مستحق من الشركة: ' . $totalDueFromCompanyAccurate);
        // ==================================================
        // *** نهاية الكود الجديد لحساب الإجماليات الدقيقة ***
        // ==================================================


        $totalDueToHotelsAll = $queryForTotals->sum('amount_due_to_hotel') ?? 0;
        $totalDueFromCompanyAll = $queryForTotals->sum('amount_due_from_company') ?? 0;
        $bookings = $query->paginate(10)->withQueryString();
        $totalDueToHotelsAll = $totalDueToHotelsAccurate;
        $totalDueFromCompanyAll = $totalDueFromCompanyAccurate;

        // فحص إذا كان الطلب AJAX
        if ($request->wantsJson() || $request->ajax()) {
            $paginationLinks = $bookings->appends($request->all())
                ->onEachSide(1)
                ->links('vendor.pagination.bootstrap-4')
                ->toHtml();

            return response()->json([
                'table' => view('bookings._table', [
                    'bookings' => $bookings,
                    'pageDueToHotelsByCurrency' => $pageDueToHotelsByCurrency,
                    'pageDueFromCompanyByCurrency' => $pageDueFromCompanyByCurrency,
                    'totalDueToHotelsByCurrency' => $totalDueToHotelsByCurrency,
                    'totalDueFromCompanyByCurrency' => $totalDueFromCompanyByCurrency
                ])->render(),
                'pagination' => $paginationLinks,
                'totals' => [
                    'count' => $totalCount,
                    'due_from_company_by_currency' => $totalDueFromCompanyByCurrency,
                    'paid_by_company_by_currency' => $totalPaidByCompanyByCurrency,
                    'remaining_from_company_by_currency' => $remainingFromCompanyByCurrency,
                    'due_to_hotels_by_currency' => $totalDueToHotelsByCurrency,
                    'paid_to_hotels_by_currency' => $totalPaidToHotelsByCurrency,
                    'remaining_to_hotels_by_currency' => $remainingToHotelsByCurrency,
                    // للتوافق مع الكود القديم
                    'due_from_company' => $totalDueFromCompanyAccurate,
                    'paid_by_company' => $totalPaidByCompanyAccurate,
                    'remaining_from_company' => $remainingFromCompanyAccurate,
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
            $searchTerm = preg_replace('/[^\p{L}\p{N}\s\-_.,]/u', '', $searchTerm); // يزيل الأحرف الخاصة مع الحفاظ على النص الأساسي
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
            // إضافة المتغيرات الجديدة
            'pageDueToHotelsByCurrency' => $pageDueToHotelsByCurrency,
            'pageDueFromCompanyByCurrency' => $pageDueFromCompanyByCurrency,
            'totalDueToHotelsByCurrency' => $totalDueToHotelsByCurrency,
            'totalDueFromCompanyByCurrency' => $totalDueFromCompanyByCurrency,
            'totalPaidByCompanyByCurrency' => $totalPaidByCompanyByCurrency,
            'totalPaidToHotelsByCurrency' => $totalPaidToHotelsByCurrency,
            'remainingFromCompanyByCurrency' => $remainingFromCompanyByCurrency,
            'remainingToHotelsByCurrency' => $remainingToHotelsByCurrency
        ]);
    }

    public function create(Request $request)
    {
        // ترتيب الشركات تصاعديًا حسب الاسم
        $companies = Company::orderBy('name', 'asc')->get();

        // ترتيب جهات الحجز تصاعديًا حسب الاسم
        $agents = Agent::orderBy('name', 'asc')->get();

        // ترتيب الفنادق تصاعديًا حسب الاسم
        $hotels = Hotel::orderBy('name', 'asc')->get();

        // ترتيب الموظفين تصاعديًا حسب الاسم
        $employees = Employee::orderBy('name', 'asc')->get();

        // *** بداية التحقق الجديد: منع الشركات من إنشاء حجز مباشر ***
        if (Auth::check() && Auth::user()->role === 'Company' && !$request->has('availability_room_type_id')) {
            Log::warning('محاولة وصول مباشر لإنشاء حجز من قبل شركة: ' . Auth::id() . ' من IP: ' . $request->ip());
            // إذا كان المستخدم شركة ويحاول فتح صفحة الإنشاء مباشرة، قم بإعادته لصفحة الإتاحات
            return redirect()->route('company.availabilities.index')
                ->with('error', 'لا يمكنك إنشاء حجز جديد مباشرة. يرجى إنشاء الحجز من خلال صفحة الإتاحات.');
        }
        // *** نهاية التحقق الجديد ***


        // *** التحقق إذا كان الحجز يتم من صفحة الإتاحات ***
        $isBookingFromAvailability = false;
        $bookingData = [];
        // *** بداية التعديل: جلب البيانات من الداتابيز بناءً على الـ ID ***
        if ($request->has('availability_room_type_id')) {
            $availabilityRoomTypeId = $request->input('availability_room_type_id');
            $roomTypeInfo = AvailabilityRoomType::with(['availability.hotel', 'availability.agent', 'roomType', 'availability.employee'])
                ->find($availabilityRoomTypeId);

            // --- هنا نتحقق من صلاحية الإتاحة ---

            if ($roomTypeInfo && $roomTypeInfo->availability) {
                if (Auth::user()->role === 'Company') {
                    // لو الإتاحة دي نشطة اظهرا 
                    if ($roomTypeInfo->availability->status !== 'active') {
                        // لو مش من حقه يشوفها، رجعه لصفحة الإتاحات مع رسالة خطأ
                        return redirect()->route('company.availabilities.index')
                            ->with('error', 'غير مسموح لك بالوصول لهذه الإتاحة.');
                    }
                }

                $isBookingFromAvailability = true;
                $availability = $roomTypeInfo->availability;

                $bookingData = [
                    'availability_room_type_id' => $roomTypeInfo->id,
                    'hotel_id' => $availability->hotel_id,
                    'agent_id' => $availability->agent_id, // قد يكون null
                    'room_type' => $roomTypeInfo->roomType->room_type_name ?? 'غير محدد', // اسم نوع الغرفة
                    'cost_price' => $roomTypeInfo->cost_price, // *** جلب السعر من الداتابيز ***
                    'sale_price' => $roomTypeInfo->sale_price, // *** جلب السعر من الداتابيز ***
                    'currency' => $roomTypeInfo->currency, // العملة
                    // التواريخ الافتراضية (ممكن المستخدم يغيرها)
                    'check_in' => $availability->start_date->format('Y-m-d'),
                    'check_out' => $availability->end_date->format('Y-m-d'),
                    // تواريخ الإتاحة الأصلية للـ min/max في الفيو
                    'availability_start_date' => $availability->start_date->format('Y-m-d'),
                    'availability_end_date' => $availability->end_date->format('Y-m-d'),
                    'rooms' => 1, // قيمة افتراضية لعدد الغرف
                    'max_rooms' => $roomTypeInfo->available_rooms, // الحد الأقصى للغرف
                    'employee_id' => $availability->employee_id, // الموظف المسؤول عن الإتاحة
                    'company_id' => null, // الشركة المستخدم هيختارها
                    'client_name' => null, // العميل المستخدم هيدخله
                    'notes' => null,
                ];
                Log::info('بيانات الحجز المبدئية من الإتاحة:', $bookingData); // للتأكد
            } else {
                // لو الإتاحة مش موجودة أصلاً
                return redirect()->route('company.availabilities.index')
                    ->with('error', 'بيانات الإتاحة المطلوبة غير صحيحة.');
            }
        }


        return view('bookings.create', compact(
            'companies',
            'agents',
            'hotels',
            'employees',
            'isBookingFromAvailability',
            'bookingData'
        ));
    }



    public function store(Request $request)
    {
        // dd($request->all());
        Log::info('بدء عملية حفظ حجز جديد', $request->except(['_token', 'password', 'password_confirmation'])); // تسجيل بيانات الطلب

        $originalRoomTypeInfo = null;
        $availability = null;
        $isBookingFromAvailability = $request->filled('availability_room_type_id');

        // ================================================================
        // 1. التحقق المبدئي والمقارنة لو الحجز من إتاحة (لمنع التلاعب)
        // ================================================================
        if ($isBookingFromAvailability) {
            $availabilityRoomTypeId = $request->input('availability_room_type_id');
            Log::info("الحجز من إتاحة ID: {$availabilityRoomTypeId}");

            // جلب بيانات الإتاحة الأصلية مع العلاقات الضرورية
            $originalRoomTypeInfo = AvailabilityRoomType::with([
                'availability.hotel',
                'availability.agent',
                'roomType', // للتأكد من اسم نوع الغرفة
                'availability.employee' // للتأكد من الموظف المسؤول
            ])->find($availabilityRoomTypeId);

            // التحقق من وجود الإتاحة ونوع الغرفة المرتبط بها
            if (!$originalRoomTypeInfo || !$originalRoomTypeInfo->availability || !$originalRoomTypeInfo->roomType) {
                Log::error("محاولة حجز بإتاحة غير صالحة أو محذوفة أو نوع غرفة غير موجود ID: {$availabilityRoomTypeId}");
                throw ValidationException::withMessages(['availability_room_type_id' => 'بيانات الإتاحة المطلوبة غير صالحة أو تم حذفها.']);
            }

            $availability = $originalRoomTypeInfo->availability; // اختصار لبيانات الإتاحة
            $errors = []; // لتجميع أخطاء المقارنة

            // --- مقارنة البيانات الأساسية ---
            if ($request->input('hotel_id') != $availability->hotel_id) $errors['hotel_id'] = 'فندق الإتاحة المحدد غير صحيح.';
            if ($request->input('agent_id') != $availability->agent_id) $errors['agent_id'] = 'جهة حجز الإتاحة المحددة غير صحيحة.';
            if ($request->input('room_type') != $originalRoomTypeInfo->roomType->room_type_name) $errors['room_type'] = 'نوع غرفة الإتاحة المحدد غير صحيح.';
            if (bccomp((string)$request->input('sale_price'), (string)$originalRoomTypeInfo->sale_price, 2) !== 0) $errors['sale_price'] = 'سعر البيع المحدد لا يطابق سعر الإتاحة الأصلي.';
            // سعر التكلفة مهم حتى لو الشركة لا تراه في الفورم
            if (bccomp((string)$request->input('cost_price'), (string)$originalRoomTypeInfo->cost_price, 2) !== 0) $errors['cost_price'] = 'سعر التكلفة المحدد لا يطابق سعر الإتاحة الأصلي.';
            if ($request->input('employee_id') != $availability->employee_id) $errors['employee_id'] = 'الموظف المسؤول عن الإتاحة غير صحيح.';

            // --- التحقق من التواريخ ضمن فترة الإتاحة ---
            try {
                $checkIn = Carbon::parse($request->input('check_in'));
                $checkOut = Carbon::parse($request->input('check_out'));
                $availabilityStart = $availability->start_date; // تاريخ بداية الإتاحة
                $availabilityEnd = $availability->end_date;     // تاريخ نهاية الإتاحة

                // التحقق من أن التواريخ المطلوبة تقع ضمن فترة الإتاحة وأن تاريخ الدخول قبل الخروج
                if ($checkIn->lt($availabilityStart) || $checkOut->gt($availabilityEnd) || $checkIn->gte($checkOut)) {
                    $errors['check_in'] = 'التواريخ المحددة خارج فترة الإتاحة الأصلية (' . $availabilityStart->format('d/m/Y') . ' - ' . $availabilityEnd->format('d/m/Y') . ') أو غير صحيحة.';
                    $errors['check_out'] = 'التواريخ المحددة خارج فترة الإتاحة الأصلية (' . $availabilityStart->format('d/m/Y') . ' - ' . $availabilityEnd->format('d/m/Y') . ') أو غير صحيحة.';
                }
            } catch (\Exception $e) {
                $errors['check_in'] = 'صيغة تاريخ الدخول أو الخروج غير صحيحة.';
                $errors['check_out'] = 'صيغة تاريخ الدخول أو الخروج غير صحيحة.';
                Log::error("خطأ في صيغة التاريخ أثناء التحقق المبدئي: " . $e->getMessage());
            }

            // --- التحقق المبدئي من عدد الغرف ---
            $requestedRooms = (int)$request->input('rooms');
            if ($requestedRooms <= 0) {
                $errors['rooms'] = 'عدد الغرف المطلوب يجب أن يكون أكبر من صفر.';
            }
            // (سيتم التحقق الدقيق من الـ allotment لاحقاً قبل الحفظ النهائي)

            // --- إرجاع الأخطاء لو وجدت ---
            if (!empty($errors)) {
                Log::warning("فشل التحقق المبدئي من بيانات الإتاحة ID: {$availabilityRoomTypeId}", $errors);
                throw ValidationException::withMessages($errors);
            }
            Log::info("نجح التحقق المبدئي لبيانات الإتاحة ID: {$availabilityRoomTypeId}");
        } // نهاية التحقق لو الحجز من إتاحة

        // ================================================================
        // 2. قواعد الـ Validation الأساسية
        // ================================================================
        $rules = [
            'client_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[^<>{}()\[\];]*$/', // منع الرموز الخاصة بالبرمجة
                function ($attribute, $value, $fail) {
                    if (preg_match('/(script|alert|onerror|onclick|javascript:|eval\()/i', $value)) {
                        $fail('اسم العميل يحتوي على محتوى غير مسموح به.');
                    }
                },
            ],
            'company_id' => 'required|exists:companies,id',
            'agent_id' => 'required|exists:agents,id',
            'hotel_id' => 'required|exists:hotels,id',
            'room_type' => 'required|string|max:255',
            'check_in' => 'required|date_format:Y-m-d', // تأكد من أن الفورم يرسل بهذا التنسيق
            'check_out' => 'required|date_format:Y-m-d|after:check_in', // after وليس after_or_equal
            'rooms' => 'required|integer|min:1',
            'availability_room_type_id' => 'nullable|sometimes|exists:availability_room_types,id', // sometimes يعني يتحقق منه لو موجود بس
            // سعر التكلفة مطلوب لغير الشركة، ويجب أن يكون رقمياً
            'cost_price' => (Auth::user()->role !== 'Company' ? 'required' : 'nullable') . '|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'currency' => 'required|in:SAR,KWD', // إضافة التحقق من العملة
            'employee_id' => 'required|exists:employees,id',
            'notes' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[^<>{}()\[\];]*$/', // منع الرموز الخاصة بالبرمجة
                function ($attribute, $value, $fail) {
                    if ($value && preg_match('/(script|alert|onerror|onclick|javascript:|eval\()/i', $value)) {
                        $fail('الملاحظات تحتوي على محتوى غير مسموح به.');
                    }
                },
            ],
        ];

        $messages = [
            'check_out.after' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
            'cost_price.required' => 'حقل سعر التكلفة مطلوب.',
            'cost_price.numeric' => 'سعر التكلفة يجب أن يكون رقمًا.',
            'cost_price.min' => 'سعر التكلفة يجب أن يكون أكبر من أو يساوي 0.',
            'sale_price.required' => 'حقل سعر البيع مطلوب.',
            'sale_price.numeric' => 'سعر البيع يجب أن يكون رقمًا.',
            'sale_price.min' => 'سعر البيع يجب أن يكون أكبر من أو يساوي 0.',
            'currency.required' => 'حقل العملة مطلوب.',
            'currency.in' => 'العملة يجب أن تكون واحدة من: SAR, KWD.',
            'rooms.required' => 'حقل عدد الغرف مطلوب.',
            'rooms.integer' => 'عدد الغرف يجب أن يكون عدد صحيح.',
            'rooms.min' => 'عدد الغرف يجب أن يكون أكبر من 0.',
            'client_name.required' => 'اسم العميل مطلوب.',
            'client_name.string' => 'اسم العميل يجب أن يكون نصًا.',
            'client_name.max' => 'اسم العميل يجب أن لا يتجاوز 255 حرفًا.',
            'client_name.regex' => 'اسم العميل يحتوي على رموز غير مسموح بها.',
            'notes.regex' => 'الملاحظات تحتوي على رموز غير مسموح بها.',
            // ... أضف رسائل أخرى حسب الحاجة ...
        ];

        // ================================================================
        // 3. تنفيذ الـ Validation
        // ================================================================
        $validatedData = $request->validate($rules, $messages);
        // تنظيف البيانات
        $validatedData['client_name'] = htmlspecialchars(strip_tags($validatedData['client_name']), ENT_QUOTES, 'UTF-8');
        if (!empty($validatedData['notes'])) {
            $validatedData['notes'] = htmlspecialchars(strip_tags($validatedData['notes']), ENT_QUOTES, 'UTF-8');
        }
        Log::info('نجح الـ Validation الأساسي');
        // ====================================
        // *** بداية الإضافة: التحقق من أقل عدد ليالي للحجز ***
        // ================================================================
        // *** بداية الإضافة: التحقق من أقل عدد ليالي للحجز ***
        // ================================================================
        if ($isBookingFromAvailability && $availability) { // نتأكد أن لدينا بيانات الإتاحة
            $checkInDateForMinNights = Carbon::parse($validatedData['check_in']);
            $checkOutDateForMinNights = Carbon::parse($validatedData['check_out']);

            // أولاً، نتأكد أن تاريخ الخروج بعد تاريخ الدخول بشكل صارم
            // هذا الشرط يجب أن يكون قد تم التعامل معه بواسطة الـ validation rule 'after:check_in'
            // لكننا نضيفه هنا كتحقق إضافي لضمان عدم حساب عدد ليالي سالب.
            if ($checkOutDateForMinNights->lte($checkInDateForMinNights)) {
                Log::error("خطأ منطقي: تاريخ الخروج ليس بعد تاريخ الدخول عند حساب أقل عدد ليالي. هذا لا يجب أن يحدث إذا كان الـ validation الأساسي يعمل.", [
                    'check_in' => $validatedData['check_in'],
                    'check_out' => $validatedData['check_out'],
                    'availability_id' => $availability->id
                ]);
                // إيقاف العملية بخطأ واضح لأن حساب عدد الليالي سيكون خاطئًا
                throw ValidationException::withMessages([
                    'check_out' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول بشكل صحيح لحساب مدة الإقامة.'
                ]);
            }

            // الآن نحسب عدد الليالي، ويجب أن تكون القيمة موجبة

            $bookedNights = abs($checkOutDateForMinNights->diffInDays($checkInDateForMinNights, false)); // لاحظ false لجعلها قد ترجع سالب ثم abs


            if ($availability->min_nights && $bookedNights < $availability->min_nights) {
                Log::warning("فشل التحقق من أقل عدد ليالي للإتاحة ID: {$availability->id}. الليالي المحجوزة: {$bookedNights}, الحد الأدنى المطلوب: {$availability->min_nights} ليالٍ.");
                throw ValidationException::withMessages([
                    'check_out' => 'أقل عدد ليالي مسموح به للحجز في هذه الفترة هو ' . $availability->min_nights . ' ليالٍ. عدد الليالي التي اخترتها هو ' . $bookedNights
                ]);
            }
            Log::info("نجح التحقق من أقل عدد ليالي للإتاحة ID: {$availability->id}. عدد الليالي المحجوزة: {$bookedNights}");
        }

        // ================================================================
        // 4. التحقق الدقيق من الـ Allotment (فقط لو الحجز من إتاحة)
        // ================================================================
        if ($isBookingFromAvailability && $originalRoomTypeInfo) {
            $requestedRooms = $validatedData['rooms'];
            // *** تأكد من أن اسم العمود 'allotment' صحيح في جدول availability_room_types ***
            $allotment = $originalRoomTypeInfo->allotment;

            // تأكد أن الـ allotment معرف وليس null
            if ($allotment === null) {
                Log::error("الـ Allotment غير معرف لـ AvailabilityRoomType ID: {$originalRoomTypeInfo->id}");
                throw ValidationException::withMessages(['availability_room_type_id' => 'خطأ: لا يمكن تحديد عدد الغرف المتاح لهذه الإتاحة.']);
            }

            // حساب عدد الغرف المحجوزة حالياً لنفس النوع والتي تتداخل مع الفترة المطلوبة
            // (نستبعد الحجز الحالي لو كنا في عملية تعديل - لكن هنا نحن في store لذا لا نستبعد شيئاً)
            $requestedCheckIn = Carbon::parse($validatedData['check_in']);
            $requestedCheckOut = Carbon::parse($validatedData['check_out']);


            // التحقق الصحيح لو الـ allotment بيعبر عن المتاح الحالي فقط
            Log::info("التحقق من الـ Allotment لـ ID: {$originalRoomTypeInfo->id}", [
                'Allotment المتاح حالياً' => $allotment,
                'المطلوب جديد' => $requestedRooms
            ]);

            if ($requestedRooms > $allotment) {
                Log::warning("فشل التحقق من الـ Allotment لـ ID: {$originalRoomTypeInfo->id}. المطلوب: {$requestedRooms}, المتاح حالياً: {$allotment}");
                throw ValidationException::withMessages([
                    'rooms' => "عدد الغرف المطلوب ({$requestedRooms}) يتجاوز العدد المتاح حالياً ({$allotment} غرف متاحة).",
                ]);
            }
            Log::info("نجح التحقق من الـ Allotment لـ ID: {$originalRoomTypeInfo->id}.");
        } else {
            Log::info("الحجز ليس من إتاحة، لا حاجة للتحقق من الـ Allotment.");
        }
        // ================================================================
        // 5. حساب القيم الإضافية (الأيام والمبالغ)
        // ================================================================
        try {
            $checkInDate = Carbon::parse($validatedData['check_in']);
            $checkOutDate = Carbon::parse($validatedData['check_out']);
            // حساب عدد الليالي (الفرق بين الأيام)
            $days = $checkOutDate->diffInDays($checkInDate);
            // إذا كان منطق العمل يعتمد على عدد الأيام وليس الليالي، يمكنك استخدام:
            // $days = $checkOutDate->diffInDays($checkInDate) + 1;
            // أو إذا كان الحجز بنفس اليوم يعتبر ليلة واحدة:
            // $days = $checkOutDate->diffInDays($checkInDate);
            // if ($days == 0) $days = 1;

            // تأكد من أن عدد الأيام لا يقل عن 1 (أو حسب منطق العمل)
            if ($days <= 0) {
                Log::warning("عدد الأيام المحسوب صفر أو أقل للحجز", $validatedData);
                // يمكنك إما إرجاع خطأ أو تعيين قيمة افتراضية مثل 1
                // throw ValidationException::withMessages(['check_out' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول بيوم واحد على الأقل.']);
                $days = 1; // أو حسب ما تقرر
            }
            $validatedData['days'] = $days;

            // تحديد سعر التكلفة الصحيح (مهم جداً للشركة)
            // إذا كان المستخدم شركة والحجز من إتاحة، نأخذ السعر من الإتاحة الأصلية
            $costPrice = ($isBookingFromAvailability && Auth::user()->role === 'Company')
                ? $originalRoomTypeInfo->cost_price
                : ($validatedData['cost_price'] ?? 0); // وإلا نأخذه من الفورم (أو صفر لو مش موجود/الشركة)

            // حساب المبالغ
            $validatedData['amount_due_to_hotel'] = $costPrice * $validatedData['rooms'] * $days;
            $validatedData['amount_due_from_company'] = $validatedData['sale_price'] * $validatedData['rooms'] * $days;
            // تأكيد حفظ العملة
            $validatedData['currency'] = $request->input('currency');


            Log::info('تم حساب الأيام والمبالغ', [
                'days' => $days,
                'cost_price_used' => $costPrice,
                'amount_due_to_hotel' => $validatedData['amount_due_to_hotel'],
                'amount_due_from_company' => $validatedData['amount_due_from_company']
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في حساب الأيام أو المبالغ: ' . $e->getMessage(), $validatedData);
            // إرجاع المستخدم للفورم مع رسالة خطأ عامة
            return redirect()->back()->withInput()->withErrors(['calculation_error' => 'حدث خطأ أثناء حساب تفاصيل الحجز. يرجى مراجعة التواريخ والأسعار.']);
        }

        // ================================================================
        // 6. تعقيم البيانات النصية (إزالة HTML Tags)
        // ================================================================
        foreach (['notes', 'client_name', 'room_type'] as $field) {
            if (isset($validatedData[$field])) {
                $validatedData[$field] = strip_tags($validatedData[$field]);
            }
        }

        // ================================================================
        // 7. إنشاء الحجز وتحديث الـ Allotment (داخل Transaction)
        // ================================================================
        $booking = null;
        try {
            DB::transaction(function () use ($validatedData, $isBookingFromAvailability, $originalRoomTypeInfo, &$booking) {
                // --- إنشاء الحجز ---
                // إضافة بيانات إضافية قبل الإنشاء إذا لزم الأمر
                // $validatedData['created_by'] = Auth::id(); // مثال
                $booking = Booking::create($validatedData);
                Log::info("تم إنشاء الحجز ID: {$booking->id}");

                // --- تحديث الـ Allotment لو الحجز من إتاحة ---
                if ($isBookingFromAvailability && $originalRoomTypeInfo) {
                    // جلب وتأمين الصف للتحديث (ضروري لمنع race conditions)
                    $currentRoomTypeInfo = AvailabilityRoomType::lockForUpdate()->find($originalRoomTypeInfo->id);
                    if (!$currentRoomTypeInfo) {
                        // هذا لا يجب أن يحدث إذا كان التحقق الأول صحيحاً، لكنه أمان إضافي
                        Log::critical("لم يتم العثور على AvailabilityRoomType داخل الـ transaction للحجز ID: {$booking->id}");
                        throw new \Exception("خطأ داخلي حرج: لم يتم العثور على بيانات الإتاحة لتحديثها.");
                    }

                    // *** تأكد من أن اسم العمود 'allotment' صحيح في جدول availability_room_types ***
                    $currentAllotment = $currentRoomTypeInfo->allotment;
                    $requestedRooms = $validatedData['rooms'];

                    // التحقق الأخير داخل الـ transaction (أمان إضافي ضد race conditions)
                    // هل عدد الغرف المطلوب ما زال أقل من أو يساوي المتاح حالياً؟
                    if ($requestedRooms > $currentAllotment) {
                        Log::error("فشل تحديث الـ Allotment (داخل transaction) للحجز ID: {$booking->id}. المطلوب: {$requestedRooms}, المتاح حالياً: {$currentAllotment}");
                        // إرجاع خطأ validation لإيقاف الـ transaction وإبلاغ المستخدم
                        throw ValidationException::withMessages(['rooms' => 'عفواً، تم حجز الغرف المطلوبة للتو. المتاح حالياً: ' . $currentAllotment]);
                    }

                    // حساب الـ Allotment الجديد وتحديثه
                    $newAllotment = $currentAllotment - $requestedRooms;
                    $currentRoomTypeInfo->update(['allotment' => $newAllotment]); // *** تأكد من اسم العمود هنا ***
                    Log::info("تم تحديث allotment للـ AvailabilityRoomType ID: {$currentRoomTypeInfo->id} إلى {$newAllotment} للحجز ID: {$booking->id}");
                    $parentAvailability = $currentRoomTypeInfo->availability()->lockForUpdate()->first(); // نستخدم lockForUpdate هنا أيضًا للأمان

                    if ($parentAvailability) {
                        // أعد تحميل أنواع الغرف للإتاحة الأم لجلب أحدث بيانات الـ allotment
                        $parentAvailability->load('availabilityRoomTypes');
                        $totalRemainingAllotmentInParent = $parentAvailability->availabilityRoomTypes->sum('allotment');

                        Log::info("التحقق من إجمالي الـ allotment المتبقي للإتاحة الأم ID: {$parentAvailability->id}. الإجمالي: {$totalRemainingAllotmentInParent}");

                        if ($totalRemainingAllotmentInParent <= 0) {
                            // إذا كان مجموع الغرف المتبقية في الإتاحة الأم صفر أو أقل، قم بتغيير حالتها
                            if ($parentAvailability->status !== 'inactive' && $parentAvailability->status !== 'expired') { // تحقق من الحالة الحالية لتجنب التحديث غير الضروري أو تغيير حالة منتهية الصلاحية
                                $parentAvailability->status = 'inactive';
                                $parentAvailability->save();
                                Log::info("تم تغيير حالة الإتاحة الأم ID: {$parentAvailability->id} إلى 'inactive' لأن مجموع الغرف المتبقية أصبح {$totalRemainingAllotmentInParent}.");

                                // (اختياري) إرسال إشعار للإدارة
                                Notification::create([
                                    'message' => "تم تغيير حالة الإتاحة للفندق: {$parentAvailability->hotel->name} (ID: {$parentAvailability->id}) تلقائياً إلى 'غير نشطة' لنفاذ جميع الغرف.",
                                    'type' => 'availability_auto_inactive',
                                    'related_id' => $parentAvailability->id,
                                    'related_type' => \App\Models\Availability::class, // استخدم FQCN للموديل
                                ]);
                            } else {
                                Log::info("الإتاحة الأم ID: {$parentAvailability->id} حالتها بالفعل '{$parentAvailability->status}', لا حاجة للتغيير.");
                            }
                        }
                    } else {
                        Log::warning("لم يتم العثور على الإتاحة الأم لـ AvailabilityRoomType ID: {$currentRoomTypeInfo->id} داخل الـ transaction.");
                    }
                }
            }); // نهاية الـ transaction

        } catch (ValidationException $e) {
            // إعادة رمي أخطاء الـ validation التي حدثت داخل الـ transaction
            Log::warning('ValidationException داخل Transaction الحفظ: ' . $e->getMessage());
            throw $e;
        } catch (\Throwable $e) { // التقاط أي نوع من الأخطاء أو الاستثناءات
            // أي خطأ آخر يحدث أثناء الـ transaction (مثل خطأ في قاعدة البيانات)
            Log::error('حدث خطأ أثناء Transaction حفظ الحجز وتحديث الـ Allotment: ' . $e->getMessage(), [
                'exception' => $e,
                'validated_data' => $validatedData // كن حذراً عند تسجيل بيانات حساسة
            ]);
            // إرجاع المستخدم للفورم مع رسالة خطأ عامة
            return redirect()->back()->withInput()->withErrors(['db_error' => 'حدث خطأ غير متوقع أثناء حفظ الحجز. يرجى المحاولة مرة أخرى أو التواصل مع الدعم.']);
        }

        // ================================================================
        // 8. عمليات ما بعد الحفظ (Backup, Notifications) - تتم فقط إذا نجح الـ Transaction
        // ================================================================
        if ($booking) { // التأكد من أن الحجز تم إنشاؤه بنجاح
            try {
                // --- Backup ---
                // تحميل العلاقات اللازمة للباك اب (إذا لم تكن محملة بالفعل)
                $booking->loadMissing(['company', 'agent', 'hotel', 'employee']);

                // محتوى ملف النصوص
                $textContent = sprintf(
                    "\n=== حجز جديد بتاريخ %s ===\nرقم الحجز: %d\nاسم العميل: %s\nالشركة: %s\nجهة الحجز: %s\nالفندق: %s\nتاريخ الدخول: %s\nتاريخ الخروج: %s\nعدد الغرف: %d\nعدد الأيام/الليالي: %d\nسعر الفندق: %.2f\nسعر البيع: %.2f\nالمبلغ المستحق للفندق: %.2f\nالمبلغ المستحق من الشركة: %.2f\nالموظف: %s\nملاحظات: %s\n=====================================\n",
                    now()->format('d/m/Y H:i:s'),
                    $booking->id,
                    $booking->client_name ?? 'N/A',
                    $booking->company->name ?? 'N/A',
                    $booking->agent->name ?? 'N/A',
                    $booking->hotel->name ?? 'N/A',
                    $booking->check_in ? Carbon::parse($booking->check_in)->format('d/m/Y') : 'N/A',
                    $booking->check_out ? Carbon::parse($booking->check_out)->format('d/m/Y') : 'N/A',
                    $booking->rooms ?? 0,
                    $booking->days ?? 0,
                    $booking->cost_price ?? 0.00,
                    $booking->sale_price ?? 0.00,
                    $booking->amount_due_to_hotel ?? 0.00,
                    $booking->amount_due_from_company ?? 0.00,
                    $booking->employee->name ?? 'N/A',
                    $booking->notes ?? 'لا يوجد'
                );

                // محتوى ملف CSV (استخدم فاصلة منقوطة لتجنب مشاكل الفواصل في الأسماء)
                $csvData = [
                    now()->format('d/m/Y H:i:s'),
                    $booking->client_name ?? '',
                    $booking->company->name ?? '',
                    $booking->agent->name ?? '',
                    $booking->hotel->name ?? '',
                    $booking->check_in ? Carbon::parse($booking->check_in)->format('d/m/Y') : '',
                    $booking->check_out ? Carbon::parse($booking->check_out)->format('d/m/Y') : '',
                    $booking->rooms ?? 0,
                    $booking->days ?? 0,
                    $booking->cost_price ?? 0.00,
                    $booking->sale_price ?? 0.00,
                    $booking->amount_due_to_hotel ?? 0.00,
                    $booking->amount_due_from_company ?? 0.00,
                    $booking->employee->name ?? '',
                    str_replace(["\r", "\n"], ' ', $booking->notes ?? '') // إزالة الأسطر الجديدة من الملاحظات للـ CSV
                ];
                // تحويل المصفوفة إلى سطر CSV مع تضمين القيم بين علامتي اقتباس مزدوجة
                $csvContent = '"' . implode('";"', $csvData) . '"' . "\n";


                // التأكد من وجود المجلدات
                $backupPath = storage_path('app/backups'); // استخدام storage/app أفضل
                if (!File::exists($backupPath)) {
                    File::makeDirectory($backupPath, 0775, true); // إنشاء المجلد الرئيسي
                }
                $txtDir = $backupPath . '/txt';
                $csvDir = $backupPath . '/csv';
                if (!File::exists($txtDir)) File::makeDirectory($txtDir, 0775, true);
                if (!File::exists($csvDir)) File::makeDirectory($csvDir, 0775, true);


                // حفظ الملفات
                $txtPath = $txtDir . '/bookings.txt';
                $csvPath = $csvDir . '/bookings.csv';

                // إضافة العناوين للـ CSV لو الملف مش موجود أو فارغ
                if (!File::exists($csvPath) || File::size($csvPath) === 0) {
                    $csvHeader = '"التاريخ";"العميل";"الشركة";"جهة الحجز";"الفندق";"تاريخ الدخول";"تاريخ الخروج";"عدد الغرف";"عدد الأيام/الليالي";"سعر الفندق";"سعر البيع";"المستحق للفندق";"المستحق من الشركة";"الموظف";"ملاحظات"' . "\n";
                    File::put($csvPath, $csvHeader);
                }

                // حفظ البيانات (إلحاق)
                File::append($txtPath, $textContent);
                File::append($csvPath, $csvContent);
                Log::info("تم إنشاء ملفات الباك اب للحجز ID: {$booking->id}");

                // --- Notifications ---
                // جلب المستخدم المسؤول (نفترض أن employee_id في الحجز هو user_id)
                $responsibleUserId = $booking->employee_id; // أو $booking->employee->user_id حسب العلاقة
                $responsibleUser = User::find($responsibleUserId); // أو Employee::find($booking->employee_id)->user;
                $responsibleUserName = $responsibleUser ? $responsibleUser->name : ($booking->employee->name ?? 'غير معروف');

                // جلب كل المستخدمين الأدمن
                $adminUsers = User::where('role', 'Admin')->get();

                // إنشاء رسالة الإشعار
                // تحديد اسم المسؤول حسب مصدر الحجز
                if ($isBookingFromAvailability && isset($responsibleUserName)) {
                    // لو من إتاحة، المسؤول هو الموظف المرتبط بالإتاحة
                    $notificationMessage = "حجز جديد من شركة: {$booking->company->name} للعميل: {$booking->client_name}، فندق: {$booking->hotel->name}. (مسؤول: {$responsibleUserName})";
                } else {
                    // لو حجز عادي، المسؤول هو المستخدم الحالي
                    $notificationMessage = "حجز جديد من شركة: {$booking->company->name} للعميل: {$booking->client_name}، فندق: {$booking->hotel->name}. (مسؤول: " . (Auth::user()->name ?? 'غير معروف') . ")";
                }
                $notificationType = 'حجز جديد';

                // 1. إرسال إشعار لكل أدمن
                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'message' => $notificationMessage,
                        'type' => $notificationType,
                        'related_id' => $booking->id, // ربط الإشعار بالحجز
                        'related_type' => Booking::class,
                    ]);
                    Log::info("تم إرسال إشعار حجز جديد للأدمن: " . $admin->name);
                }

                // 2. إرسال إشعار للموظف المسؤول (إذا كان موجوداً وليس هو نفسه أدمن)
                if ($responsibleUser && $responsibleUser->role !== 'Admin') {
                    Notification::create([
                        'user_id' => $responsibleUser->id,
                        'message' => $notificationMessage,
                        'type' => $notificationType,
                        'related_id' => $booking->id,
                        'related_type' => Booking::class,
                    ]);
                    Log::info("تم إرسال إشعار حجز جديد للموظف المسؤول: " . $responsibleUser->name);
                } elseif ($responsibleUser && $responsibleUser->role === 'Admin') {
                    Log::info("الموظف المسؤول ({$responsibleUser->name}) هو أدمن بالفعل، تم إشعاره مرة واحدة.");
                } else {
                    Log::warning("لم يتم العثور على مستخدم للموظف المسؤول ID: " . $responsibleUserId . " أو أنه أدمن.");
                }
                Log::info("تم إرسال إشعارات الحجز ID: {$booking->id}");
            } catch (\Exception $e) {
                // تسجيل أي خطأ في عمليات ما بعد الحفظ دون إيقاف المستخدم
                Log::error('حدث خطأ في عمليات ما بعد الحجز (Backup/Notifications): ' . $e->getMessage(), ['booking_id' => $booking->id]);
                // لا تقم بإرجاع خطأ للمستخدم هنا، فقط سجل المشكلة
            }

            // ================================================================
            // 9. إعادة التوجيه للفاوتشر مع رسالة نجاح
            // ================================================================
            return redirect()->route('bookings.voucher', $booking->id)->with('success', 'تم إنشاء الحجز بنجاح! يمكنك طباعة الفاتورة الآن.');
        } else {
            // حالة غير متوقعة: لم يتم إنشاء الحجز رغم نجاح الـ Transaction ظاهرياً
            Log::critical('فشل إنشاء الحجز لسبب غير معروف بعد الـ Transaction.');
            return redirect()->back()->withInput()->withErrors(['unknown_error' => 'حدث خطأ غير معروف ولم يتم إنشاء الحجز. يرجى المحاولة مرة أخرى.']);
        }
    }

    public function voucher($id)
    {
        $booking = Booking::with(['company', 'agent', 'hotel', 'employee'])->findOrFail($id);

        // *** بداية التحقق من صلاحية الشركة لعرض الفاوتشر ***
        $user = Auth::user();
        if ($user && $user->role === 'Company') {
            // إذا كان المستخدم شركة، تحقق مما إذا كان الحجز يخص شركته
            if (!$user->company_id || $booking->company_id != $user->company_id) {
                // إذا لم يكن الحجز يخص شركة المستخدم، أو إذا لم يكن للمستخدم company_id (حالة غير متوقعة)
                Log::warning("محاولة وصول غير مصرح بها لفاوتشر حجز ID: {$id} من قبل شركة: {$user->name} (User ID: {$user->id}, Company ID: {$user->company_id}). الحجز يخص Company ID: {$booking->company_id}.");
                return redirect()->route('company.availabilities.index') // أو أي راوت مناسب للشركة
                    ->with('error', 'غير مصرح لك بعرض هذه الفاتورة.');
            }
        }
        // *** نهاية التحقق ***

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
    // تصدير حجوزات الصفحة فقط أو الحجوزات المفلترة بواسطة المستخدم
    public function exportBookings(Request $request)
    {
        // اسم الملف الذي سيتم تحميله
        $fileName = 'bookings_' . now()->format('Ymd_His') . '.xlsx';

        // استخدم الـ Export class مع تمرير الـ request الحالي للحفاظ على الفلاتر
        return Excel::download(new BookingsExport($request), $fileName);
    }
    /**
     * تصدير كل الحجوزات النشطة بدون أي فلترة باستخدام Anonymous Class.
     */
    public function exportAllBookings()
    {
        $fileName = 'all_active_bookings_' . now()->format('Ymd_His') . '.xlsx';

        // 1. جلب كل الحجوزات النشطة مباشرة
        $allBookings = Booking::with(['company', 'agent', 'hotel', 'employee'])
            ->where('cost_price', '!=', 0) // استبعاد المؤرشفة
            ->where('sale_price', '!=', 0) // استبعاد المؤرشفة
            ->orderBy('created_at', 'desc') // أو أي ترتيب تفضله
            ->get();

        // 2. تعريف رؤوس الأعمدة
        $headings = [
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

        // 3. إنشاء وتمرير Anonymous Class لـ Excel::download
        return Excel::download(new class($allBookings, $headings) implements FromCollection, WithHeadings, WithMapping
        {
            protected $bookings;
            protected $headings;
            protected static $index = 0; // عداد للترقيم التسلسلي

            public function __construct($bookings, $headings)
            {
                $this->bookings = $bookings;
                $this->headings = $headings;
                self::$index = 0; // إعادة تصفير العداد مع كل تصدير جديد
            }

            public function collection()
            {
                return $this->bookings;
            }

            public function headings(): array
            {
                return $this->headings;
            }

            public function map($booking): array
            {
                self::$index++; // زيادة العداد لكل صف
                // نفس منطق تنسيق الصف الموجود في map بالـ Export Class
                return [
                    self::$index, // استخدام العداد
                    $booking->client_name,
                    $booking->company->name ?? '-',
                    $booking->agent->name ?? '-',
                    $booking->hotel->name ?? '-',
                    $booking->check_in ? Carbon::parse($booking->check_in)->format('d/m/Y') : '-',
                    $booking->check_out ? Carbon::parse($booking->check_out)->format('d/m/Y') : '-',
                    $booking->rooms ?? '-',
                    $booking->amount_due_to_hotel ?? '0',
                    $booking->amount_due_from_company ?? '0',
                    $booking->employee->name ?? '-',
                    $booking->notes ?? '-',
                    $booking->created_at ? $booking->created_at->format('Y-m-d H:i') : '-',
                ];
            }
        }, $fileName);
    }

    public function details($hotelId)
    {
        $bookings = Booking::where('hotel_id', $hotelId)->with(['agent', 'hotel'])->get();
        $hotel = Hotel::findOrFail($hotelId);

        return view('bookings.details', compact('bookings', 'hotel'));
    }

    public function edit($id)
    {
        // *** بداية التحقق الجديد: منع الشركات من إنشاء حجز مباشر ***
        if (Auth::check() && Auth::user()->role === 'Company') {
            Log::warning('محاولة وصول مباشر  لتعديل حجز من قبل شركة: ' . Auth::user()->name);
            // إذا كان المستخدم شركة ويحاول فتح صفحة الإنشاء مباشرة، قم بإعادته لصفحة الإتاحات
            return redirect()->route('company.availabilities.index')
                ->with('error',  'لا يمكنك تعديل الحجوزات مباشرة. يرجى استخدام صفحة الإتاحات الخاصة بك.');
        }
        // *** نهاية التحقق الجديد ***

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
            'currency' => 'required|in:SAR,KWD', // إضافة التحقق من العملة
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
        // تأكيد أن العملة تم أخذها من النموذج
        $currency = $request->input('currency');
        $validatedData['currency'] = $currency;

        // تسجيل التعديلات
        foreach ($validatedData as $field => $newValue) {
            $oldValue = $booking->getOriginal($field); // <-- استخدم getOriginal

            // تجاهل الحقول المحسوبة ديناميكيًا مهم 

            if (in_array($field, ['amount_due_to_hotel', 'amount_due_from_company', 'days'])) {
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
                        'old_value' => $oldValue ?? '', // <-- لو null يبقى نص فاضي
                        'new_value' => $newValue ?? '', // <-- لو null يبقى نص فاضي
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


    /**
     * دالة عشان تجيب اقتراحات البحث للإكمال التلقائي
     */
    public function autocomplete(Request $request)
    {
        $term = $request->input('term');
        $suggestions = collect();

        if ($term) {
            $term = '%' . $term . '%';

            $clientSuggestions = Booking::where('client_name', 'LIKE', $term)
                ->distinct()->limit(5)->pluck('client_name');
            $suggestions = $suggestions->merge($clientSuggestions);

            $companySuggestions = Company::where('name', 'LIKE', $term)->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($companySuggestions);

            $agentSuggestions = Agent::where('name', 'LIKE', $term)->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($agentSuggestions);

            $hotelSuggestions = Hotel::where('name', 'LIKE', $term)->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($hotelSuggestions);

            $employeeSuggestions = Employee::where('name', 'LIKE', $term)->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($employeeSuggestions);

            $suggestions = $suggestions->unique()->take(10)->values();
        }

        return response()->json($suggestions);
    }
}
