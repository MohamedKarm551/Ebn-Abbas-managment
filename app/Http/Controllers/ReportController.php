<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Agent;
use App\Models\AgentPayment;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // لرفع الملفات
use Illuminate\Support\Facades\DB; //  لإجراء العمليات على قاعدة البيانات
use Carbon\CarbonPeriod; // لإجراء العمليات على التواريخ
use Illuminate\Support\Str; // لاستخدام دالة Str::limit


/**
 * ReportController
 *
 * يتحكم في جميع تقارير ونماذج الدفع المتعلقة بالشركات ووكلاء الحجز والفنادق
 */
class ReportController extends Controller
{
    // تقرير يومي لكل الحجوزات والإحصائيات
    public function daily()
    {
        // تاريخ النهاردة
        $today = Carbon::today();

        // كل الحجوزات اللي بتبدأ النهاردة
        $todayBookings = Booking::whereDate('check_in', $today)->get();

        // تقرير الشركات: كل شركة وعدد حجوزاتها (قائمة الشركات مع عدد الحجوزات لكل شركة)
        //  كل الشركات وعدد الحجوزاتها 
        $companiesReport = Company::withCount('bookings')->get()
            ->sortByDesc(function ($company) {
                return $company->total_due; // <-- الترتيب الصحيح هنا
            })->values();
        // إجمالي المتبقي من الشركات ...   
        $totalDueFromCompanies = $companiesReport->sum('remaining');

        //  تقرير الوكلاء: كل وكيل وعدد حجوزاته وترتيبهم من الأعلى واحد مطلوب منه فلوس للأقل
        $agentsReport = Agent::withCount('bookings')->get()
            ->sortByDesc(function ($agent) {
                return $agent->remaining;
            })->values();


        // إجمالي اللي اتدفع للفنادق (كل اللي اتدفع فعلاً للفنادق عن كل الحجوزات)
        $totalPaidToHotels = Booking::all()->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });

        // تقرير الفنادق: كل فندق وعدد حجوزاته (قائمة الفنادق مع عدد الحجوزات لكل فندق)
        $hotelsReport = Hotel::withCount('bookings')->get()
            ->sortByDesc(function ($hotel) {
                return $hotel->total_due;
            })->values();


        // إجمالي المتبقي من الشركات (كل اللي لسه الشركات ما دفعتهوش فعلاً = المستحق - المدفوع لكل شركة)
        $totalRemainingFromCompanies = $companiesReport->sum('remaining');

        // إجمالي المتبقي للفنادق (كل اللي لسه عليك تدفعه للفنادق = المستحق للفنادق - اللي اتدفع فعلاً)
        $totalRemainingToHotels = Booking::sum('amount_due_to_hotel') - AgentPayment::sum('amount');
        // إجمالي اللي علينا لجهات الحجز أو الفنادقdd(Booking::sum('amount_due_to_hotel')); 

        // صافي الربح (الفرق بين اللي لسه الشركات هتدفعه لك واللي لسه عليك تدفعه للفنادق)
        // $netProfit = $totalRemainingFromCompanies - $totalRemainingToHotels; // السطر القديم (ممكن تمسحه أو تخليه تعليق)
        $totalDueToAgents = $agentsReport->sum('total_due'); // أو total_due حسب اسم العمود عندك لجهات الحجز
        $netProfit = $totalDueFromCompanies - $totalDueToAgents; // السطر الجديد
        // --- *** بداية: جلب بيانات الحجوزات اليومية لآخر 30 يومًا *** ---
        $days = 30; // عدد الأيام
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        // اختر الحقل الذي تريد تتبع تاريخه: 'created_at' أو 'check_in'
        $dateField = 'created_at'; // أو 'check_in'

        // جلب عدد الحجوزات مجمعة حسب اليوم
        $bookingsData = Booking::select(
            DB::raw("DATE($dateField) as date"),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween($dateField, [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('count', 'date'); // [date => count]

        // إنشاء فترة زمنية كاملة لآخر 30 يومًا
        $period = CarbonPeriod::create($startDate, $endDate);
        $chartDates = [];
        $bookingCounts = [];

        // ملء البيانات مع التأكد من وجود صفر للأيام بدون حجوزات
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartDates[] = $date->format('d/m'); // تنسيق العرض في الرسم البياني (يوم/شهر)
            $bookingCounts[] = $bookingsData[$formattedDate] ?? 0; // نضع صفر إذا لم يكن اليوم موجودًا
        }
        // 2. جلب الحجوزات مع تفاصيلها اللازمة للرسم والتلميح
        $bookingsForChart = Booking::with(['company', 'agent', 'hotel']) // نجيب العلاقات عشان الأسماء
            ->select(
                'check_in', // تاريخ بدء الاستحقاق
                'client_name', // اسم العميل للتفاصيل
                'company_id', // لربط الشركة
                'agent_id',   // لربط الجهة
                'hotel_id',   // لربط الفندق (اختياري في التفاصيل)
                DB::raw('sale_price * rooms * days as company_due'), // المستحق من الشركة
                DB::raw('cost_price * rooms * days as agent_due') // المستحق للجهة
            )
            ->whereBetween('check_in', [$startDate, $endDate]) // نستخدم check_in كتاريخ للحدث
            ->orderBy('check_in', 'asc')
            ->get();

        // 3. جلب دفعات الشركات مع تفاصيلها
        $companyPaymentsForChart = Payment::with('company') // نجيب الشركة عشان اسمها
            ->select('payment_date', 'amount', 'company_id', 'notes')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'asc')
            ->get();

        // 4. جلب دفعات الوكلاء مع تفاصيلها
        $agentPaymentsForChart = AgentPayment::with('agent') // نجيب الجهة عشان اسمها
            ->select('payment_date', 'amount', 'agent_id', 'notes')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'asc')
            ->get();


        // --- *** نهاية: جلب بيانات الحجوزات اليومية *** ---
        // 5. تجميع كل الأحداث (حجوزات ودفعات) في مصفوفة واحدة مع تفاصيلها وتأثيرها
        $allEventsWithDetails = [];
        foreach ($bookingsForChart as $booking) {
            $eventDate = Carbon::parse($booking->check_in)->format('Y-m-d');
            $allEventsWithDetails[$eventDate][] = [
                'type' => 'booking',
                'company_change' => $booking->company_due, // التغيير في رصيد الشركات (موجب)
                'agent_change' => $booking->agent_due,   // التغيير في رصيد الجهات (موجب)
                // نص التفاصيل اللي هيظهر في الـ tooltip
                'details' => "حجز: " . Str::limit($booking->client_name ?? 'N/A', 15) // اسم العميل مختصر
                    . " (+" . number_format($booking->company_due) . " ش)" // تأثيره على رصيد الشركة
                    . " (+" . number_format($booking->agent_due) . " ج)" // تأثيره على رصيد الجهة
            ];
        }
        foreach ($companyPaymentsForChart as $payment) {
            $eventDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
            $allEventsWithDetails[$eventDate][] = [
                'type' => 'company_payment',
                'company_change' => -$payment->amount, // دفعة الشركة تقلل المستحق منها (سالب)
                'agent_change' => 0,
                // نص التفاصيل
                'details' => "دفعة من: " . Str::limit($payment->company->name ?? 'N/A', 10) // اسم الشركة مختصر
                    . " (-" . number_format($payment->amount) . " ش)" // تأثيره على رصيد الشركة
                    . ($payment->notes ? " - " . Str::limit($payment->notes, 10) : "") // ملاحظات مختصرة
            ];
        }
        foreach ($agentPaymentsForChart as $payment) {
            $eventDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
            $allEventsWithDetails[$eventDate][] = [
                'type' => 'agent_payment',
                'company_change' => 0,
                'agent_change' => -$payment->amount, // دفعة للجهة تقلل المستحق لها (سالب)
                // نص التفاصيل
                'details' => "دفعة إلى: " . Str::limit($payment->agent->name ?? 'N/A', 10) // اسم الجهة مختصر
                    . " (-" . number_format($payment->amount) . " ج)" // تأثيره على رصيد الجهة
                    . ($payment->notes ? " - " . Str::limit($payment->notes, 10) : "") // ملاحظات مختصرة
            ];
        }

        // 6. حساب الأرصدة التراكمية يوم بيوم وتجميع تفاصيل الأحداث لكل يوم
        $runningReceivables = 0; // الرصيد التراكمي المستحق من الشركات
        $runningPayables = 0;    // الرصيد التراكمي المستحق للجهات
        $receivableBalances = []; // مصفوفة لتخزين رصيد الشركات لكل يوم
        $payableBalances = [];    // مصفوفة لتخزين رصيد الجهات لكل يوم
        $dailyEventDetails = []; // *** مصفوفة جديدة لتخزين تفاصيل الأحداث لكل يوم ***
        // نستخدم نفس الفترة الزمنية $period المحسوبة لرسم الحجوزات اليومية (السطر 96)
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d'); // تاريخ اليوم YYYY-MM-DD
            $chartLabelDate = $date->format('d/m'); // التاريخ اللي بيظهر تحت في الرسم d/m
            $eventsTodayDetails = []; // لتجميع تفاصيل أحداث اليوم الحالي

            // لو فيه أحداث حصلت في اليوم ده
            if (isset($allEventsWithDetails[$formattedDate])) {
                // (اختياري) ممكن نرتب الأحداث جوه اليوم لو حابب (مثلاً الحجوزات قبل الدفعات)
                // usort($allEventsWithDetails[$formattedDate], function($a, $b) { ... });

                // نمشي على أحداث اليوم ده
                foreach ($allEventsWithDetails[$formattedDate] as $event) {
                    // نحدث الأرصدة التراكمية
                    $runningReceivables += $event['company_change'];
                    $runningPayables += $event['agent_change'];
                    // نضيف تفاصيل الحدث ده لقائمة أحداث اليوم
                    $eventsTodayDetails[] = $event['details'];
                }
            }

            // نخزن الرصيد في نهاية اليوم (حتى لو مفيش أحداث، الرصيد هو نفسه بتاع اليوم اللي قبله)
            $receivableBalances[] = round(max(0, $runningReceivables), 2); // نتأكد إن الرصيد مش سالب
            $payableBalances[] = round(max(0, $runningPayables), 2);    // نتأكد إن الرصيد مش سالب
            // *** نخزن قايمة تفاصيل أحداث اليوم ده في المصفوفة الجديدة ***
            // هنستخدم التاريخ اللي بيظهر في الرسم (d/m) كمفتاح عشان نلاقيها بسهولة في الجافاسكريبت
            $dailyEventDetails[$chartLabelDate] = $eventsTodayDetails;
        }
        // --- *** نهاية: تعديل حساب بيانات الرسم البياني وتفاصيل الأحداث *** ---






        // إشعار خفيف على آخر شيء تم عليه تعديل 
        // في نهاية دالة daily
        $recentCompanyEdits = \App\Models\Notification::whereIn('type', [
            'تعديل',
            'تعديل دفعة',
            'دفعة جديدة',
            'حذف دفعة'
        ])
            ->where('created_at', '>=', now()->subDays(2))
            ->get()
            ->groupBy('message');
        $resentAgentEdits = \App\Models\Notification::whereIn('type', [
            'تعديل',
            'تعديل دفعة',
            'دفعة جديدة',
            'حذف دفعة'
        ])
            ->where('created_at', '>=', now()->subDays(2))
            ->get()
            ->groupBy('message');

        // --- *** بداية: حساب بيانات الرسم البياني لصافي الرصيد *** ---
        // 1. جلب دفعات الشركات بالعملات المختلفة
        $companyPaymentsSAR = Payment::select('payment_date as date', 'amount')
            ->where('currency', 'SAR')
            ->orderBy('date', 'asc')
            ->get();

        $companyPaymentsKWD = Payment::select('payment_date as date', 'amount')
            ->where('currency', 'KWD')
            ->orderBy('date', 'asc')
            ->get();

        // 2. جلب دفعات الوكلاء بالعملات المختلفة
        $agentPaymentsSAR = AgentPayment::select('payment_date as date', DB::raw('-amount as amount'))
            ->where('currency', 'SAR')
            ->orderBy('date', 'asc')
            ->get();

        $agentPaymentsKWD = AgentPayment::select('payment_date as date', DB::raw('-amount as amount'))
            ->where('currency', 'KWD')
            ->orderBy('date', 'asc')
            ->get();

        // 3. حساب المتغيرات لكل عملة
        // --- الريال السعودي (نحافظ على الكود الأصلي للتوافق) ---
        $allTransactions = $companyPaymentsSAR->concat($agentPaymentsSAR);
        $sortedTransactions = $allTransactions->sortBy('date');

        $runningBalance = 0;
        $netBalanceData = []; // مصفوفة للريال السعودي

        foreach ($sortedTransactions as $transaction) {
            $dateString = Carbon::parse($transaction->date)->format('Y-m-d');
            $runningBalance += $transaction->amount;
            $netBalanceData[$dateString] = $runningBalance;
        }

        // --- الدينار الكويتي (إضافة كود جديد) ---
        $allTransactionsKWD = $companyPaymentsKWD->concat($agentPaymentsKWD);
        $sortedTransactionsKWD = $allTransactionsKWD->sortBy('date');

        $runningBalanceKWD = 0;
        $netBalanceDataKWD = []; // مصفوفة جديدة للدينار الكويتي

        foreach ($sortedTransactionsKWD as $transaction) {
            $dateString = Carbon::parse($transaction->date)->format('Y-m-d');
            $runningBalanceKWD += $transaction->amount;
            $netBalanceDataKWD[$dateString] = $runningBalanceKWD;
        }

        // 4. تجهيز المصفوفات النهائية
        $netBalanceDates = []; // مصفوفة التواريخ المشتركة
        $netBalances = [];     // للريال (متوافق مع الكود القديم)
        $netBalancesKWD = [];  // للدينار (جديدة)

        // دمج وترتيب كل التواريخ الفريدة من كلتا العملتين
        $allDates = array_unique(array_merge(array_keys($netBalanceData), array_keys($netBalanceDataKWD)));
        sort($allDates);

        // ملء البيانات بشكل متزامن
        $lastBalanceSAR = 0;
        $lastBalanceKWD = 0;

        foreach ($allDates as $date) {
            $netBalanceDates[] = Carbon::parse($date)->format('d/m');

            // للريال السعودي
            if (isset($netBalanceData[$date])) {
                $lastBalanceSAR = $netBalanceData[$date];
            }
            $netBalances[] = round($lastBalanceSAR, 2);

            // للدينار الكويتي
            if (isset($netBalanceDataKWD[$date])) {
                $lastBalanceKWD = $netBalanceDataKWD[$date];
            }
            $netBalancesKWD[] = round($lastBalanceKWD, 2);
        }

        // --- *** نهاية: حساب بيانات الرسم البياني لصافي الرصيد *** ---

        // حساب المدفوعات حسب العملة للشركات
        $companyPaymentsByCurrency = Payment::select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->pluck('total', 'currency')
            ->toArray();

        // حساب المدفوعات حسب العملة للوكلاء
        $agentPaymentsByCurrency = AgentPayment::select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->pluck('total', 'currency')
            ->toArray();
        // تصنيف الحجوزات حسب العملة للشركات
        $bookingsByCompanyCurrency = Booking::select(
            'company_id',
            'currency',
            DB::raw('SUM(amount_due_from_company) as total_due'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('company_id', 'currency')
            ->get();

        // تصنيف الحجوزات حسب العملة للجهات
        $bookingsByAgentCurrency = Booking::select(
            'agent_id',
            'currency',
            DB::raw('SUM(amount_due_to_hotel) as total_due'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('agent_id', 'currency')
            ->get();

        // تخزين إجمالي المستحقات حسب العملة
        $totalDueFromCompaniesByCurrency = [
            'SAR' => 0,
            'KWD' => 0
        ];

        $totalDueToAgentsByCurrency = [
            'SAR' => 0,
            'KWD' => 0
        ];

        // تجميع إجماليات الحجوزات حسب العملة
        foreach ($bookingsByCompanyCurrency as $booking) {
            $totalDueFromCompaniesByCurrency[$booking->currency] += $booking->total_due;
        }

        foreach ($bookingsByAgentCurrency as $booking) {
            $totalDueToAgentsByCurrency[$booking->currency] += $booking->total_due;
        }

        // حساب المتبقي من الشركات حسب العملة
        $totalRemainingByCurrency = [
            'SAR' => 0,
            'KWD' => 0,
        ];
        foreach ($companiesReport as $company) {
            $remainingByCurrency = $company->remaining_by_currency ?? [
                'SAR' => $company->remaining,
            ];
            foreach ($remainingByCurrency as $currency => $amount) {
                $totalRemainingByCurrency[$currency] += $amount;
            }
        }

        // حساب المتبقي للجهات حسب العملة
        $agentRemainingByCurrency = [
            'SAR' => 0,
            'KWD' => 0,
        ];
        foreach ($agentsReport as $agent) {
            $remainingByCurrency = $agent->remaining_by_currency ?? [
                'SAR' => $agent->remaining,
            ];
            foreach ($remainingByCurrency as $currency => $amount) {
                $agentRemainingByCurrency[$currency] += $amount;
            }
        }

        // حساب صافي الربح حسب العملة
        $netProfitByCurrency = [
            'SAR' => $totalRemainingByCurrency['SAR'] - $agentRemainingByCurrency['SAR'],
            'KWD' => $totalRemainingByCurrency['KWD'] - $agentRemainingByCurrency['KWD'],
        ];

        // رجع كل البيانات للواجهة اليومية
        return view('reports.daily', compact(
            'todayBookings',
            'companiesReport',
            'agentsReport',
            'hotelsReport',
            'totalDueFromCompanies',
            'totalPaidToHotels',
            'totalRemainingFromCompanies',
            'totalRemainingToHotels',
            'netProfit',
            'recentCompanyEdits', // إشعار خفيف على آخر شركة تم عليها تعديل
            'resentAgentEdits', // إشعار خفيف على آخر جهة حجز تم عليه تعديل
            'chartDates',       // <-- *** تمرير مصفوفة التواريخ للرسم ***
            'bookingCounts',    // <-- *** تمرير مصفوفة عدد الحجوزات للرسم ***
            'receivableBalances', // <-- مصفوفة رصيد الشركات (الخط الأخضر)
            'payableBalances',    // <-- مصفوفة رصيد الجهات (الخط الأحمر)
            'dailyEventDetails',
            'companyPaymentsByCurrency',  // المدفوعات حسب العملة للشركات
            'agentPaymentsByCurrency',    // المدفوعات حسب العملة للوكلاء
            'totalDueFromCompaniesByCurrency', // إجمالي المستحقات حسب العملة للشركات
            'totalDueToAgentsByCurrency', // إجمالي المستحقات حسب العملة للجهات
            'totalRemainingByCurrency',
            'agentRemainingByCurrency',
            'netProfitByCurrency',
            'netBalanceDates',
            'netBalances',      // للريال (الحفاظ عليه للتوافق)
            'netBalancesKWD',   // للدينار (جديد)
            'dailyEventDetails',
            // 'netBalanceDates',
        ));
    }
    /**
     * عرض صفحة التقارير المتقدمة
     */
    public function advanced(Request $request)
    {
        // إذا تم تحديد تاريخ، نستخدمه، وإلا نستخدم تاريخ اليوم
        // إذا تم تحديد تاريخ، نستخدمه، وإلا نستخدم تاريخ اليوم
        if ($request->has('date')) {
            try {
                $today = Carbon::createFromFormat('Y-m-d', $request->input('date'));
            } catch (\Exception $e) {
                // إذا كان التاريخ غير صالح، نستخدم اليوم
                $today = Carbon::today();
            }
        } else {
            $today = Carbon::today();
        }

        $tomorrow = (clone $today)->addDay();

        // بنجيب كل الحجوزات النشطة اللي موجودة في التاريخ المحدد
        $activeBookings = Booking::whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->with(['hotel', 'company', 'agent'])
            ->get();

        // بنجيب الحجوزات اللي هتدخل في التاريخ المحدد
        $checkingInToday = Booking::whereDate('check_in', $today)
            ->with(['hotel', 'company', 'agent'])
            ->get();

        // بنجيب الحجوزات اللي هتخرج في اليوم التالي للتاريخ المحدد
        $checkingOutTomorrow = Booking::whereDate('check_out', $tomorrow)
            ->with(['hotel', 'company', 'agent'])
            ->get();
        // ملخص إحصائي عن الفنادق
        $hotelStats = Hotel::withCount(['bookings as active_bookings' => function ($query) use ($today) {
            $query->whereDate('check_in', '<=', $today)
                ->whereDate('check_out', '>', $today);
        }])
            ->withCount(['bookings as checking_in_today' => function ($query) use ($today) {
                $query->whereDate('check_in', $today);
            }])
            ->withCount(['bookings as checking_out_tomorrow' => function ($query) use ($tomorrow) {
                $query->whereDate('check_out', $tomorrow);
            }])
            ->withCount('bookings as total_bookings')
            ->get()
            ->map(function ($hotel) use ($activeBookings) {
                // نستخدم قيمة افتراضية لعدد الغرف (30 غرفة لكل فندق)
                $defaultRooms = 30;

                // بنحسب معدل الإشغال للفندق النهاردة
                $occupiedRooms = $activeBookings->where('hotel_id', $hotel->id)->sum('rooms');
                $hotel->occupancy_rate = $defaultRooms > 0 ? round(($occupiedRooms / $defaultRooms) * 100) : 0;

                // إضافة حقل total_rooms بقيمة افتراضية لكل فندق
                $hotel->total_rooms = $defaultRooms;

                return $hotel;
            });

        // بنجيب بيانات للرسم البياني للإشغال اليومي لمدة أسبوع
        $occupancyData = $this->calculateOccupancyForWeek();

        // بنجيب بيانات تحليل الإيرادات
        $revenueData = $this->calculateRevenueAnalysis();

        // بنجمع كل المعلومات في متغير واحد ونبعتها للفيو
        return view('reports.advanced', compact(
            'today',
            'tomorrow',
            'activeBookings',
            'checkingInToday',
            'checkingOutTomorrow',
            'hotelStats',
            'occupancyData',
            'revenueData'
        ));
    }

    /**
     * دالة لحساب معدل الإشغال اليومي للفنادق لمدة أسبوع
     */
    private function calculateOccupancyForWeek($startDate = null)
    {
        $result = [];
        $startDate = $startDate ?? Carbon::today();
        $endDate = $startDate->addDays(6); // أسبوع كامل

        // جلب قائمة الفنادق (بدون total_rooms لأنه غير موجود)
        $hotels = Hotel::select('id', 'name')->get();

        // نقوم بتعيين عدد غرف افتراضي لكل فندق (يمكنك تغيير هذه القيمة)
        $defaultRoomsPerHotel = 30; // قيمة افتراضية لكل فندق

        // إنشاء مصفوفة تحتوي على عدد الغرف لكل فندق
        $totalRoomsByHotelId = $hotels->mapWithKeys(function ($hotel) use ($defaultRoomsPerHotel) {
            // هنا نستخدم عدد غرف افتراضي بما أن العمود غير موجود
            return [$hotel->id => $defaultRoomsPerHotel];
        });

        $totalRooms = $totalRoomsByHotelId->sum();

        // حساب الإشغال لكل يوم
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $dateLabel = $date->format('d/m');

            // جلب الحجوزات في هذا اليوم مع عدد الغرف
            $bookings = Booking::whereDate('check_in', '<=', $dateString)
                ->whereDate('check_out', '>', $dateString)
                ->select('hotel_id', DB::raw('SUM(rooms) as booked_rooms'))
                ->groupBy('hotel_id')
                ->get()
                ->pluck('booked_rooms', 'hotel_id')
                ->toArray();

            // حساب الغرف المحجوزة والمتاحة لكل فندق
            $occupancyByHotel = [];
            $totalBooked = 0;

            foreach ($hotels as $hotel) {
                // استخدم القيمة الافتراضية التي قمنا بتعيينها
                $hotelTotalRooms = $totalRoomsByHotelId[$hotel->id];
                $booked = $bookings[$hotel->id] ?? 0;
                $available = max(0, $hotelTotalRooms - $booked);
                $occupancyRate = $hotelTotalRooms > 0 ? round(($booked / $hotelTotalRooms) * 100, 1) : 0;

                $occupancyByHotel[$hotel->id] = [
                    'name' => $hotel->name,
                    'booked' => $booked,
                    'available' => $available,
                    'total' => $hotelTotalRooms, // استخدام القيمة الافتراضية
                    'rate' => $occupancyRate
                ];

                $totalBooked += $booked;
            }

            // إضافة البيانات لهذا اليوم
            $overallRate = $totalRooms > 0 ? round(($totalBooked / $totalRooms) * 100, 1) : 0;
            $result[] = [
                'date' => $dateString,
                'label' => $dateLabel,
                'day_name' => $date->locale('ar')->dayName,
                'total_booked' => $totalBooked,
                'total_available' => $totalRooms - $totalBooked,
                'overall_rate' => $overallRate,
                'hotels' => $occupancyByHotel
            ];
        }

        return $result;
    }

    /**
     * دالة لحساب وتحليل الإيرادات (المثال فقط)
     */
    private function calculateRevenueAnalysis($referenceDate = null)
    {
        // إذا لم يتم تمرير تاريخ مرجعي، نستخدم اليوم
        $referenceDate = $referenceDate ?? Carbon::now();

        // بنجيب الأشهر الثلاثة الماضية
        $months = [];
        $revenueData = [];

        for ($i = 2; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $months[] = $month->format('M'); // اسم الشهر مختصر

            // جلب الإيرادات الفعلية (مبالغ البيع للحجوزات في هذا الشهر)
            $actualRevenue = Booking::whereYear('check_in', $month->year)
                ->whereMonth('check_in', $month->month)
                ->sum(DB::raw('sale_price * rooms * days'));

            // جلب المدفوعات الفعلية من الشركات خلال هذا الشهر
            $actualPayments = Payment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');

            // طبعا الإيرادات المتوقعة في المستقبل ممكن تكون تقديرات أو توقعات
            // هنا بنضع قيم افتراضية للتوضيح
            $projectedRevenue = $actualRevenue * 1.1; // مثال: 10% زيادة متوقعة

            $revenueData[] = [
                'month' => $month->format('M Y'),
                'actual' => round($actualRevenue),
                'payments' => round($actualPayments),
                'projected' => round($projectedRevenue),
                'collection_rate' => $actualRevenue > 0 ? round(($actualPayments / $actualRevenue) * 100) : 0
            ];
        }

        // إضافة الشهر الحالي والشهر القادم (توقعات)
        $currentMonth = Carbon::now()->startOfMonth();
        $nextMonth = Carbon::now()->addMonth()->startOfMonth();

        $months[] = $currentMonth->format('M');
        $months[] = $nextMonth->format('M');

        // الإيرادات الفعلية حتى الآن في الشهر الحالي
        $currentMonthRevenue = Booking::whereYear('check_in', $currentMonth->year)
            ->whereMonth('check_in', $currentMonth->month)
            ->sum(DB::raw('sale_price * rooms * days'));

        $currentMonthPayments = Payment::whereYear('payment_date', $currentMonth->year)
            ->whereMonth('payment_date', $currentMonth->month)
            ->sum('amount');

        // توقعات الشهر الحالي (مبني على أنماط سابقة)
        // هنا بنضع قيم افتراضية للتوضيح
        $projectedCurrentMonth = $currentMonthRevenue * 1.5; // افتراض أننا في منتصف الشهر

        $revenueData[] = [
            'month' => $currentMonth->format('M Y'),
            'actual' => round($currentMonthRevenue),
            'payments' => round($currentMonthPayments),
            'projected' => round($projectedCurrentMonth),
            'collection_rate' => $currentMonthRevenue > 0 ? round(($currentMonthPayments / $currentMonthRevenue) * 100) : 0
        ];

        // توقعات الشهر القادم (يمكنك حسابها بناء على الحجوزات المؤكدة مسبقًا للشهر القادم)
        $nextMonthConfirmedBookings = Booking::whereYear('check_in', $nextMonth->year)
            ->whereMonth('check_in', $nextMonth->month)
            ->sum(DB::raw('sale_price * rooms * days'));

        // نفترض أن هناك 30% زيادة متوقعة على الحجوزات المؤكدة حاليًا
        $projectedNextMonth = $nextMonthConfirmedBookings * 1.3;

        $revenueData[] = [
            'month' => $nextMonth->format('M Y'),
            'actual' => 0, // لسه معندناش إيرادات فعلية
            'payments' => 0, // لسه معندناش مدفوعات فعلية
            'projected' => round($projectedNextMonth),
            'collection_rate' => 0
        ];

        return [
            'months' => $months,
            'data' => $revenueData
        ];
    }
    // تقرير حجوزات شركة معينة
    public function companyBookings($id)
    {
        // هات الشركة المطلوبة
        $company = Company::findOrFail($id);

        // هات كل الحجوزات بتاعة الشركة مع بيانات الفندق والوكيل
        $bookings = $company->bookings()
            ->with(['hotel', 'agent'])
            ->orderBy('check_in')
            ->get()
            ->map(function ($b) {
                // احسب المستحق الكلي: كل الليالي × عدد الغرف × سعر البيع
                $b->total_company_due = $b->total_nights * $b->rooms * $b->sale_price;
                return $b;
            });

        // عدد الحجوزات
        $dueCount = $bookings->count();

        // إجمالي المستحق على الشركة
        $totalDue = $bookings->sum('total_company_due');

        // هات كل الدفعات اللي الشركة دفعتها
        $allPayments = $company->payments()->orderBy('payment_date')->get();

        // وزع الدفعات على المستحق (لو فيه دفعات زيادة متحسبهاش مرتين)
        $remaining = $totalDue;
        $totalPaid = 0;
        foreach ($allPayments as $payment) {
            if ($remaining <= 0) break;
            $pay = min($payment->amount, $remaining);
            $totalPaid += $pay;
            $remaining -= $pay;
        }

        // المتبقي على الشركة بعد الدفعات
        $totalRemaining = $totalDue - $totalPaid;



        // إضافة الإجماليات حسب العملة
        $totalDueByCurrency = $company->total_due_by_currency;
        $totalPaidByCurrency = $company->total_paid_by_currency;
        $totalRemainingByCurrency = $company->remaining_by_currency;


        // رجع البيانات للواجهة
        return view('reports.company_bookings', compact(
            'company',
            'bookings',
            'dueCount',
            'totalDue',
            'totalPaid',
            'totalRemaining',
            'totalDueByCurrency',
            'totalPaidByCurrency',
            'totalRemainingByCurrency'

        ));
    }

    // تقرير حجوزات وكيل معين
    public function agentBookings($id)
    {
        // هات الوكيل المطلوب
        $agent = Agent::findOrFail($id);

        // هات كل الحجوزات بتاعة الوكيل مع بيانات الفندق والشركة
        $bookings = $agent->bookings()
            ->with(['hotel', 'company'])
            ->orderBy('check_in')
            ->get()
            ->map(function ($b) {
                // احسب المستحق للوكيل: عدد الليالي × عدد الغرف × سعر الفندق
                $b->due_to_agent = $b->rooms * $b->days * $b->cost_price;
                return $b;
            });

        // فلتر الحجوزات اللي فعلاً دخلت وليها مستحق
        $today = Carbon::today();
        $dueBookings = $bookings->filter(function ($b) use ($today) {
            return $b->check_in->lte($today) && $b->due_to_agent > 0;
        });

        // عدد الحجوزات المستحقة
        $dueCount = $dueBookings->count();

        // إجمالي المستحق للوكيل
        $totalDue = $dueBookings->sum('due_to_agent');

        // هات كل الدفعات اللي اتدفعت للوكيل
        $allPayments = $agent->payments()->orderBy('payment_date')->get();

        // وزع الدفعات على المستحق (لو فيه دفعات زيادة متحسبهاش مرتين)
        $remaining = $totalDue;
        $totalPaid = 0;
        foreach ($allPayments as $payment) {
            if ($remaining <= 0) break;
            $pay = min($payment->amount, $remaining);
            $totalPaid += $pay;
            $remaining -= $pay;
        }

        // المتبقي للوكيل بعد الدفعات
        $totalRemaining = $totalDue - $totalPaid;

        // إضافة الإجماليات حسب العملة
        $totalDueByCurrency = $agent->total_due_by_currency;
        $totalPaidByCurrency = $agent->total_paid_by_currency;
        $totalRemainingByCurrency = $agent->remaining_by_currency;


        // رجع البيانات للواجهة
        return view('reports.agent_bookings', compact(
            'agent',
            'bookings',
            'dueCount',
            'totalDue',
            'totalPaid',
            'totalRemaining',
            'totalDueByCurrency',
            'totalPaidByCurrency',
            'totalRemainingByCurrency'
        ));
    }

    // تقرير حجوزات فندق معين
    public function hotelBookings($id)
    {
        // هات الفندق المطلوب
        $hotel = Hotel::findOrFail($id);

        // هات كل الحجوزات بتاعة الفندق مع بيانات الشركة والوكيل
        $bookings = Booking::where('hotel_id', $id)
            ->with(['company', 'agent'])
            ->get();
        // حساب المستحق والمتبقي حسب العملة
        $totalDueByCurrency = $bookings->groupBy('currency')
            ->map(function ($currencyBookings) {
                return $currencyBookings->sum('amount_due_to_hotel');
            });
        // رجع البيانات للواجهة
        return view('reports.hotel_bookings', [
            'hotel'   => $hotel,
            'bookings' => $bookings,
            'totalDueByCurrency' => $totalDueByCurrency
        ]);
    }

    // إضافة دفعة جديدة لشركة
    public function storePayment(Request $request)
    {
        // تحقق من البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'amount'           => 'required|numeric|min:0',
            'currency' => 'required|in:SAR,KWD',  // التحقق من العملة
            'payment_date'     => 'nullable|date',
            'notes'            => 'nullable|string',
            'bookings_covered' => 'nullable|array',
            'bookings_covered.*' => 'exists:bookings,id',
            // 'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // Optional, file type, max size 5MB
        ]);
        // // *** بداية كود رفع الملف ***
        // $receiptPath = null; // نهيئ متغير المسار

        // // التعامل مع رفع الملف إذا كان موجودًا وصالحًا
        // if ($request->hasFile('receipt_file') && $request->file('receipt_file')->isValid()) {
        //     $file = $request->file('receipt_file');
        //     // إنشاء مسار/اسم ملف فريد داخل مجلد Google Drive
        //     $fileName = time() . '_' . $file->getClientOriginalName();
        //     $filePath = 'company_payments/' . $fileName; // مجلد فرعي داخل المجلد الرئيسي في Drive

        //     try {
        //         // الرفع إلى Google Drive باستخدام الـ disk المحدد
        //         Storage::disk('google')->put($filePath, file_get_contents($file));
        //         $receiptPath = $filePath; // تخزين المسار المستخدم في Google Drive
        //     } catch (\Exception $e) {
        //         // تسجيل الخطأ أو العودة برسالة خطأ
        //         // يمكنك استخدام Log::error(...) هنا لتسجيل تفاصيل الخطأ
        //         return back()->with('error', 'فشل رفع الإيصال: ' . $e->getMessage())->withInput();
        //     }
        // }
        // // *** نهاية كود رفع الملف ***

        // سجل الدفعة في جدول payments
        $payment = Payment::create([
            'company_id'       => $validated['company_id'],
            'amount'           => $validated['amount'],
            'currency' => $validated['currency'],  // حفظ العملة
            'payment_date'     => $validated['payment_date'] ?? now(),
            'notes'            => $validated['notes'] ?? null,
            'bookings_covered' => json_encode($validated['bookings_covered'] ?? []),
            // 'receipt_path'     => $receiptPath, // *** إضافة مسار الإيصال هنا ***
            'employee_id'      => Auth::id(), // إضافة الموظف الذي سجل الدفعة
        ]);

        // وزع المبلغ على الحجوزات المفتوحة
        // فقط إذا كانت العملة ريال سعودي، نخصص المبلغ على الحجوزات
        // لأن الحجوزات مسجلة بالريال السعودي
        if ($payment->currency === 'SAR') {
            $remaining = $payment->amount;
            Booking::whereIn('id', $validated['bookings_covered'] ?? [])
                ->orderBy('check_in')
                ->get()
                ->each(function (Booking $b) use (&$remaining) {
                    $due = $b->amount_due_from_company - $b->amount_paid_by_company;
                    if ($due <= 0 || $remaining <= 0) {
                        return;
                    }
                    $pay = min($due, $remaining);
                    $b->increment('amount_paid_by_company', $pay);
                    $remaining -= $pay;
                });
        }


        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => " تم إضافة دفعة جديدة ({$payment->currency}) لشركة {$payment->company->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'دفعة جديدة',
        ]);
        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $validated['company_id'])
            ->with('success', 'تم تسجيل الدفعة وتخصيصها على الحجوزات بنجاح!');
    }

    // إضافة دفعة جديدة لوكيل
    public function storeAgentPayment(Request $request)
    {
        // تحقق من البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'amount'   => 'required|numeric|min:0',
            'currency' => 'required|in:SAR,KWD',  // التحقق من العملة
            'notes'    => 'nullable|string',
            // 'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // *** إضافة التحقق هنا ***

        ]);
        // // *** بداية كود رفع الملف ***
        // $receiptPath = null; // نهيئ متغير المسار

        // // التعامل مع رفع الملف إذا كان موجودًا وصالحًا
        // if ($request->hasFile('receipt_file') && $request->file('receipt_file')->isValid()) {
        //     $file = $request->file('receipt_file');
        //     // إنشاء مسار/اسم ملف فريد داخل مجلد Google Drive
        //     $fileName = time() . '_' . $file->getClientOriginalName();
        //     $filePath = 'agent_payments/' . $fileName; // مجلد فرعي مختلف

        //     try {
        //         // الرفع إلى Google Drive باستخدام الـ disk المحدد
        //         Storage::disk('google')->put($filePath, file_get_contents($file));
        //         $receiptPath = $filePath; // تخزين المسار المستخدم في Google Drive
        //     } catch (\Exception $e) {
        //         // تسجيل الخطأ أو العودة برسالة خطأ
        //         return back()->with('error', 'فشل رفع الإيصال: ' . $e->getMessage())->withInput();
        //     }
        // }
        // // *** نهاية كود رفع الملف ***


        // سجل الدفعة في جدول agent_payments
        $payment = AgentPayment::create([
            'agent_id' => $validated['agent_id'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'payment_date' => now(),
            'notes' => $validated['notes'],
            // 'receipt_path' => $receiptPath, // *** تأكد من إضافة هذا السطر هنا ***
            'employee_id' => Auth::id(), // إضافة الموظف الذي سجل الدفعة
        ]);
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => " تم إضافة دفعة جديدة ({$payment->currency}) لجهة حجز {$payment->agent->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'دفعة جديدة',
            // 'receipt_path' => $receiptPath, // *** إضافة مسار الإيصال هنا ***
            'employee_id' => Auth::id(), // إضافة الموظف الذي سجل الدفعة
        ]);

        // رجع للصفحة مع رسالة نجاح
        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    // سجل الدفعات لشركة معينة
    public function companyPayments($id)
    {
        // هات الشركة المطلوبة
        $company = Company::findOrFail($id);

        // --- Fetch Payments ---
        $payments = Payment::where('company_id', $id)
            ->orderBy('payment_date', 'asc') // Order ascending for timeline
            ->get();

        // --- Fetch Bookings ---
        // Get bookings where the company owes money, order by check-in date
        $bookings = Booking::where('company_id', $id)
            ->where('amount_due_from_company', '>', 0)
            ->orderBy('check_in', 'asc') // Use check_in as the date the amount becomes due
            ->get();

        // --- Combine into Events ---
        $events = collect();

        foreach ($bookings as $booking) {
            $events->push([
                // Use check_in date and add a time to help with sorting if multiple events on same day
                'date' => Carbon::parse($booking->check_in)->startOfDay()->toDateTimeString(),
                'type' => 'booking',
                // Amount due from company is positive (increases balance)
                'amount' => (float) $booking->amount_due_from_company,
                'balance_change' => (float) $booking->amount_due_from_company,
                'details' => "حجز: " . ($booking->client_name ?? 'N/A') . " (فندق: " . ($booking->hotel->name ?? 'N/A') . ")",
                'id' => 'b_' . $booking->id // Unique ID prefix
            ]);
        }

        foreach ($payments as $payment) {
            $events->push([
                // Use payment date and add a time
                'date' => Carbon::parse($payment->payment_date)->endOfDay()->toDateTimeString(), // Payments happen after bookings on the same day
                'type' => 'payment',
                // Payment amount is positive, but it decreases the balance
                'amount' => (float) $payment->amount,
                'balance_change' => (float) -$payment->amount, // Negative change for balance calculation
                'details' => "دفعة: " . ($payment->notes ? Str::limit($payment->notes, 30) : 'مبلغ ' . $payment->amount),
                'id' => 'p_' . $payment->id // Unique ID prefix
            ]);
        }

        // --- Sort Events Chronologically ---
        $sortedEvents = $events->sortBy('date')->values();

        // --- Calculate Running Balance ---
        $runningBalance = 0;
        $timelineEvents = $sortedEvents->map(function ($event) use (&$runningBalance) {
            $runningBalance += $event['balance_change'];
            $event['running_balance'] = $runningBalance;
            // Re-parse date for chart.js adapter if needed, ensure consistent format
            $event['chart_date'] = Carbon::parse($event['date'])->format('Y-m-d');
            return $event;
        });


        // رجع البيانات للواجهة (pass timelineEvents instead of payments)
        return view('reports.company_payments', compact('company', 'timelineEvents', 'payments')); // Pass timelineEvents
    }

    // سجل الدفعات لوكيل معين
    public function agentPayments($id)
    {
        // هات الوكيل المطلوب
        $agent    = Agent::findOrFail($id);

        // هات كل الدفعات بتاعته
        $payments = AgentPayment::where('agent_id', $id)
            ->orderBy('payment_date', 'desc')
            ->get();

        // رجع البيانات للواجهة
        return view('reports.agent_payments', compact('agent', 'payments'));
    }

    // تعديل دفعة وكيل
    public function editPayment($id)
    {
        // هات الدفعة المطلوبة
        $payment = AgentPayment::findOrFail($id);

        // رجع البيانات للواجهة
        return view('reports.edit_payment', compact('payment'));
    }

    // تحديث دفعة وكيل بعد التعديل
    public function updatePayment(Request $request, $id)
    {
        // تحقق من البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes'  => 'nullable|string',
        ]);

        // هات الدفعة وعدلها
        $payment = AgentPayment::findOrFail($id);
        $payment->update($validated);

        // حدث بيانات الوكيل عشان القيم تتحدث
        $agent = $payment->agent;
        $agent->load('payments', 'bookings');

        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تعديل دفعة لجهة حجز  {$agent->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'تعديل دفعة ',
        ]);

        // رجع للصفحة مع رسالة نجاح
        return redirect()->route('reports.agent.payments', $agent->id)
            ->with('success', 'تم تعديل الدفعة بنجاح!');
    }

    // تعديل دفعة شركة
    public function editCompanyPayment($id)
    {
        // هات الدفعة المطلوبة
        $payment = Payment::findOrFail($id);
        // رجع البيانات للواجهة
        return view('reports.edit_company_payment', compact('payment'));
    }

    // تحديث دفعة شركة بعد التعديل
    public function updateCompanyPayment(Request $request, $id)
    {
        // تحقق من البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        // هات الدفعة وعدلها
        $payment = Payment::findOrFail($id);
        $payment->update([
            'amount'       => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'notes'        => $validated['notes'],
        ]);

        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "  تعديل دفعة  لشركة   {$payment->company->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'تعديل دفعة ',
        ]);
        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $payment->company_id)
            ->with('success', 'تم تعديل دفعة الشركة بنجاح!');
    }

    // حذف دفعة شركة مع إعادة توزيع المبالغ على الحجوزات
    public function destroyCompanyPayment($id)
    {
        // *** إضافة تحقق من صلاحية الأدمن ***
        if (Auth::user()->role !== 'admin') {
            abort(403, 'غير مصرح لك بتنفيذ هذا الإجراء.');
        }
        // *** نهاية التحقق ***

        // هات الدفعة المطلوبة
        $payment = Payment::findOrFail($id);
        $remaining = $payment->amount;
        $bookingIds = is_array($payment->bookings_covered)
            ? $payment->bookings_covered
            : json_decode($payment->bookings_covered, true) ?? [];

        // وزع الحذف زي ما وزعت الإضافة
        Booking::whereIn('id', $bookingIds)
            ->orderBy('check_in')
            ->get()
            ->each(function (Booking $b) use (&$remaining, $payment) {
                if ($remaining <= 0) return;
                $paid = $b->amount_paid_by_company;
                $due = $b->amount_due_from_company - ($paid - min($payment->amount, $paid));
                $pay = min(min($payment->amount, $paid), $remaining);
                $b->decrement('amount_paid_by_company', $pay);
                $remaining -= $pay;
            });

        // احذف سجل الدفعة
        $companyId = $payment->company_id;
        $payment->delete();
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "  حذف دفعة  لشركة   {$payment->company->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'حذف دفعة ',
        ]);
        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $companyId)
            ->with('success', 'تم حذف الدفعة وإرجاع المبالغ المرتبطة بها.');
    }

    // حذف دفعة وكيل
    public function destroyAgentPayment($id)
    {
        // *** إضافة تحقق من صلاحية الأدمن ***
        if (Auth::user()->role !== 'admin') {
            abort(403, 'غير مصرح لك بتنفيذ هذا الإجراء.');
        }
        // *** نهاية التحقق ***

        // هات الدفعة المطلوبة
        $payment = AgentPayment::findOrFail($id);
        $agentId = $payment->agent_id;

        // احذف الدفعة
        $payment->delete();
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => " حذف دفعة  لجهة حجز  {$payment->agent->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'حذف دفعة ',
        ]);
        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.agent.payments', $agentId)
            ->with('success', 'تم حذف دفعة الوكيل بنجاح.');
    }

    // عرض تفاصيل دفعة شركة
    public function showCompanyPayment($id)
    {
        // هات الدفعة المطلوبة
        $payment = Payment::findOrFail($id);
        // رجع البيانات للواجهة
        return view('reports.show_company_payment', compact('payment'));
    }
    // ======================================
    // حفظ الصورة كل دقيقة في ملف باك أب
    // public function saveScreenshot(\Illuminate\Http\Request $request)
    // {
    //     $img = $request->input('image');
    //     if (!$img) {
    //         return response()->json(['error' => 'No image'], 400);
    //     }

    //     // فك التشفير
    //     $img = str_replace('data:image/png;base64,', '', $img);
    //     $img = str_replace(' ', '+', $img);
    //     $imgData = base64_decode($img);

    //     // اسم الملف
    //     $fileName = 'screenshot_صفحة التقرير اليومي_' . now()->format('Y-m-d_H-i-s') . '.png';
    //     $path = storage_path('backups/images/' . $fileName);

    //     // احفظ الصورة
    //     file_put_contents($path, $imgData);

    //     return response()->json(['success' => true, 'path' => $path]);
    // }
    // =====================================
    // حفظ الصفحة كملف pdf  كل دقيقة أو على حسب الاختيار 
    // public function savePDF(\Illuminate\Http\Request $request)
    // {
    //     $pdf = $request->input('pdf');
    //     if (!$pdf) {
    //         return response()->json(['error' => 'No PDF'], 400);
    //     }

    //     $pdfData = base64_decode($pdf);
    //     $fileName = 'pdf_صفحة التقرير اليومي_' . now()->format('Y-m-d_H-i-s') . '.pdf';
    //     $path = storage_path('backups/PDF/' . $fileName);

    //     file_put_contents($path, $pdfData);

    //     return response()->json(['success' => true, 'path' => $path]);
    // }
    // ======================================

public function saveScreenshot(\Illuminate\Http\Request $request)
{
    $img = $request->input('image');
    if (!$img) {
        return response()->json(['error' => 'No image'], 400);
    }
    // فك التشفير
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img); // استبدال الفراغات بـ +
    $imgData = base64_decode($img); // فك تشفير الصورة

    $fileName = 'screenshot_' . now()->format('Y-m-d') . '.png';
    $path = storage_path('backups/images/' . $fileName);

    // لو الصورة موجودة بالفعل لنفس اليوم، متحفظش تاني
    if (file_exists($path)) {
        return response()->json(['success' => true, 'path' => $path, 'message' => 'الصورة محفوظة بالفعل لهذا اليوم.']);
    }

    file_put_contents($path, $imgData);

    return response()->json(['success' => true, 'path' => $path]);
}
}
