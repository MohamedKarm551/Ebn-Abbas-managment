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
use Illuminate\Support\Facades\Log; // لتسجيل الأخطاء في السجل
use Barryvdh\DomPDF\Facade\Pdf; // تصدير بي دي اف!
use Illuminate\Database\Eloquent\Builder; // لاستخدام Builder في الدوال
use App\Http\Controllers\AccountController;


/**
 * ReportController
 *
 * يتحكم في جميع تقارير ونماذج الدفع المتعلقة بالشركات ووكلاء الحجز والفنادق
 */
class ReportController extends Controller
{

    /**
     * تقرير يومي لكل الحجوزات والإحصائيات
     * دالة محسنة ومنظمة لعرض التقرير اليومي الشامل
     */
    public function daily()
    {
        // ===================================
        // 🗓️ المتغيرات الأساسية والتواريخ
        // ===================================

        // تاريخ اليوم الحالي
        $today = Carbon::today();

        // كل الحجوزات التي تبدأ اليوم
        $todayBookings = Booking::whereDate('check_in', $today)->get();

        // ===================================
        // 🏢 تقرير الشركات مع العلاقات المحسنة
        // ===================================

        // جلب الشركات مع العلاقات المطلوبة فقط (تحسين الأداء)
        // $companiesReport = Company::with([
        //     'bookings' => function ($query) {
        //         $query->select('id', 'company_id', 'sale_price', 'rooms', 'days', 'currency', 'amount_due_from_company');
        //     },
        //     'payments' => function ($query) {
        //         $query->select('id', 'company_id', 'amount', 'currency', 'payment_date');
        //     },
        //     'landTripBookings' => function ($query) {
        //         $query->select('id', 'company_id', 'amount_due_from_company', 'currency');
        //     }
        // ])
        //     ->withCount(['bookings as bookings_count', 'landTripBookings as land_trip_bookings_count'])
        //     ->get()
        //     ->map(function ($company) {
        //         // حساب إجمالي عدد الحجوزات (عادية + رحلات برية)
        //         $company->total_bookings_count = $company->bookings_count + $company->land_trip_bookings_count;

        //         // ✅ استدعاء دالة حساب الإجماليات المحسنة
        //         $company->calculateTotals();

        //         return $company;
        //     })
        //     ->sortByDesc('computed_total_due')  // ترتيب حسب المستحق المحسوب
        //     ->values();
        $perPage = 15; // عدد العناصر في كل صفحة
        $currentPage = request()->get('companies_page', 1);

        // عدل الاستعلام ليشمل فقط الشركات التي لها حجوزات
        $companiesQuery = Company::withCount(['bookings', 'landTripBookings'])
            ->with([
                'bookings' => function ($query) {
                    $query->select('id', 'company_id', 'sale_price', 'rooms', 'days', 'currency', 'amount_due_from_company');
                },
                'payments' => function ($query) {
                    $query->select('id', 'company_id', 'amount', 'currency', 'payment_date');
                },
                'landTripBookings' => function ($query) {
                    $query->select('id', 'company_id', 'amount_due_from_company', 'currency');
                }
            ])
            // فقط الشركات التي لها حجوزات (عادية أو رحلات برية)
            ->having('bookings_count', '>', 0)
            ->orHaving('land_trip_bookings_count', '>', 0);

        // الحصول على إجمالي العدد
        $totalCompanies = $companiesQuery->count();
        $totalPages = ceil($totalCompanies / $perPage);

        // ضبط رقم الصفحة
        if ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
        }
        // 1) احسب الإجماليات من كل الشركات (بدون pagination)
        $companyTotals = $this->computeCompanyTotals(clone $companiesQuery, ['SAR', 'KWD']);
        // 2) خُد الـ Collection الكاملة للعرض والتقسيم لاحقًا
        $companiesFull = $companyTotals['all_companies'];

        // الحصول على الشركات للصفحة الحالية
        $companiesReport = $companiesQuery->get()
            ->map(function ($company) {
                // حساب إجمالي عدد الحجوزات (عادية + رحلات برية)
                $company->total_bookings_count = $company->bookings_count + $company->land_trip_bookings_count;
                // ✅ حساب الرصيد الحالي (الحجوزات دخلت حتى اليوم)
                $company->current_balance = $company->currentBalance();
                // استدعاء دالة حساب الإجماليات المحسنة
                $company->calculateTotals();

                return $company;
            })
            ->sortByDesc('computed_total_due')  // ترتيب حسب المستحق المحسوب
            ->values();

        // تقسيم النتائج يدوياً لعرض الصفحة الأولى فقط
        $companiesReport = new \Illuminate\Pagination\LengthAwarePaginator(
            $companiesReport->forPage($currentPage, $perPage),
            $totalCompanies,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'companies_page'
            ]
        );

        // ===================================
        // 🤝 تقرير الوكلاء/جهات الحجز
        // ===================================
        // 1. جلب جميع الوكلاء للحسابات (بدون pagination)
        $allAgentsForCalculations = Agent::with(['bookings', 'payments'])
            ->withCount('bookings')
            ->get()
            ->map(function ($agent) {
                // حساب الإجماليات للوكيل
                $agent->calculateTotals();
                $agent->current_balance = $agent->currentBalance();
                return $agent;
            });

        // 2. حساب الإجماليات من جميع الوكلاء (للعرض في الملخص)
        $agentsTotalCalculations = [
            'total_due_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_paid_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_discounts_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_remaining_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_bookings_count' => 0
        ];

        foreach ($allAgentsForCalculations as $agent) {
            // جمع عدد الحجوزات
            $agentsTotalCalculations['total_bookings_count'] += $agent->bookings_count;

            // جمع المستحق حسب العملة
            $dueByCurrency = $agent->computed_total_due_by_currency ??
                ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);
            foreach ($dueByCurrency as $currency => $amount) {
                $agentsTotalCalculations['total_due_by_currency'][$currency] += $amount;
            }

            // جمع المدفوع والخصومات حسب العملة
            $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
            $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];

            foreach (['SAR', 'KWD'] as $currency) {
                $agentsTotalCalculations['total_paid_by_currency'][$currency] += $paidByCurrency[$currency] ?? 0;
                $agentsTotalCalculations['total_discounts_by_currency'][$currency] += $discountsByCurrency[$currency] ?? 0;
            }

            // جمع المتبقي حسب العملة
            $remainingByCurrency = $agent->computed_remaining_by_currency ??
                ($agent->remaining_by_currency ?? ['SAR' => $agent->remaining_amount ?? 0]);
            foreach ($remainingByCurrency as $currency => $amount) {
                $agentsTotalCalculations['total_remaining_by_currency'][$currency] += $amount;
            }
        }

        // 3. إنشاء pagination للعرض فقط
        $perPage = 10;
        $currentPage = request()->get('agents_page', 1);

        $sortedAgents = $allAgentsForCalculations->sortByDesc('computed_total_due');
        $totalItems = $sortedAgents->count();
        $totalPages = ceil($totalItems / $perPage);

        if ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
        }

        $agentsReportPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedAgents->forPage($currentPage, $perPage),
            $totalItems,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'agents_page'
            ]
        );

        // 4. المتغير للعرض (pagination) والمتغير للحسابات (كل البيانات)
        $agentsReport = $agentsReportPaginated;
        $allAgentsData = $allAgentsForCalculations; // للاستخدام في الحسابات

        // ===================================
        // 🏨 تقرير الفنادق
        // ===================================

        // جلب الفنادق مع عدد الحجوزات وترتيبهم مع pagination
        $hotelsQuery = Hotel::withCount('bookings')
            ->with(['bookings' => function ($query) {
                $query->select('hotel_id', 'cost_price', 'rooms', 'days', 'amount_due_to_hotel');
            }]);

        // الحصول على البيانات للترتيب
        $hotelsQuery = Hotel::withCount('bookings')
            ->with(['bookings' => function ($query) {
                $query->select('hotel_id', 'cost_price', 'rooms', 'days', 'amount_due_to_hotel', 'currency'); // ✅ إضافة العملة
            }]);

        // الحصول على البيانات مع حساب المستحق حسب العملة
        $hotelsData = $hotelsQuery->get()->map(function ($hotel) {
            // ✅ حساب المستحق حسب كل عملة
            $totalDueByCurrency = ['SAR' => 0, 'KWD' => 0];

            foreach ($hotel->bookings as $booking) {
                $bookingDue = $booking->amount_due_to_hotel ?? ($booking->cost_price * $booking->rooms * $booking->days);
                $currency = $booking->currency ?? 'SAR'; // العملة الافتراضية ريال سعودي
                $totalDueByCurrency[$currency] += $bookingDue;
            }

            // إضافة البيانات المحسوبة للفندق
            $hotel->total_due_by_currency = $totalDueByCurrency;
            $hotel->total_due = $totalDueByCurrency['SAR'] + ($totalDueByCurrency['KWD'] * 12); // تحويل تقريبي للترتيب

            return $hotel;
        })->sortByDesc('total_due');

        // تحويل إلى pagination يدوياً
        $perPage = 10; // عدد العناصر في الصفحة
        $currentPage = request()->get('page', 1);
        $hotelsReport = new \Illuminate\Pagination\LengthAwarePaginator(
            $hotelsData->forPage($currentPage, $perPage),
            $hotelsData->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );


        // ===================================
        // 💰 الحسابات المالية الأساسية
        // ===================================

        // إجمالي المتبقي من الشركات
        $totalDueFromCompanies = $companiesReport->sum('remaining');

        // إجمالي المدفوع للفنادق (جميع التكاليف الفعلية)
        $totalPaidToHotels = Booking::all()->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });

        // إجمالي المتبقي من الشركات (نسخة مكررة - يمكن حذفها)
        $totalRemainingFromCompanies = $companiesReport->sum('remaining');

        // إجمالي المتبقي للفنادق/الوكلاء
        $totalRemainingToHotels = Booking::sum('amount_due_to_hotel') - AgentPayment::sum('amount');

        // حساب صافي الربح (المحسن)
        $totalDueToAgents = $agentsReport->sum('total_due');
        $netProfit = $totalDueFromCompanies - $totalDueToAgents;

        // ===================================
        // 📊 استدعاء دالة الرسم البياني المنفصلة
        // ===================================

        // جلب جميع بيانات الرسوم البيانية من الدالة المحسنة
        $chartData = $this->getDailyChartData();

        // ===================================
        // 🔔 الإشعارات والتعديلات الأخيرة
        // ===================================

        // إشعارات التعديلات على الشركات (آخر يومين)
        $recentCompanyEdits = \App\Models\Notification::whereIn('type', [
            'تعديل',
            'تعديل دفعة',
            'دفعة جديدة',
            'حذف دفعة'
        ])
            ->where('created_at', '>=', now()->subDays(2))
            ->get()
            ->groupBy('message');

        // إشعارات التعديلات على الوكلاء (آخر يومين)
        $resentAgentEdits = \App\Models\Notification::whereIn('type', [
            'تعديل',
            'تعديل دفعة',
            'دفعة جديدة',
            'حذف دفعة',
            'خصم مطبق',
            'high_priority_tracking',
            'agent_payment_completed',
            'company_payment_completed',
            'agent_payment_partial',
            'company_payment_partial',
            'agent_payment_pending',
            'company_payment_pending',
            'follow_up_date_change',
            'priority_level_change',
            'financial_tracking_created',
            'payment_status_change',
            'payment_amount_change',
            'متابعة مالية عالية الأهمية',
            'تغيير مستوى الأولوية',
        ])
            ->where('created_at', '>=', now()->subDays(2))
            ->get()
            ->groupBy('message');

        // ===================================
        // 💱 حسابات العملات المختلفة
        // ===================================

        // حساب المدفوعات حسب العملة للشركات
        $companyPaymentsByCurrency = [];
        $companyPaymentsData = Payment::select(
            'currency',
            DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as total_paid'),
            DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_discounts')
        )
            ->whereNotNull('company_id')  // فقط المدفوعات المرتبطة بالشركات
            ->groupBy('currency')
            ->get();

        foreach ($companyPaymentsData as $payment) {
            $companyPaymentsByCurrency[$payment->currency] = [
                'paid' => (float) $payment->total_paid,
                'discounts' => (float) $payment->total_discounts
            ];
        }

        // // حساب المدفوعات حسب العملة للوكلاء (بسيط)
        // $agentPaymentsByCurrency = AgentPayment::select('currency', DB::raw('SUM(amount) as total'))
        //     ->groupBy('currency')
        //     ->get()
        //     ->pluck('total', 'currency')
        //     ->toArray();

        // ===================================
        // 📈 تصنيف الحجوزات حسب العملة
        // ===================================

        // تصنيف حجوزات الشركات حسب العملة
        $bookingsByCompanyCurrency = Booking::select(
            'company_id',
            'currency',
            DB::raw('SUM(amount_due_from_company) as total_due'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('company_id', 'currency')
            ->get();

        // تصنيف حجوزات الوكلاء حسب العملة
        $bookingsByAgentCurrency = Booking::select(
            'agent_id',
            'currency',
            DB::raw('SUM(amount_due_to_hotel) as total_due'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('agent_id', 'currency')
            ->get();

        // ===================================
        // 💰 إجماليات المستحقات حسب العملة
        // ===================================

        // تهيئة مصفوفات الإجماليات
        $totalDueFromCompaniesByCurrency = ['SAR' => 0, 'KWD' => 0];
        $totalDueToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];
        $totalRemainingToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];

        // تجميع إجماليات حجوزات الشركات
        foreach ($bookingsByCompanyCurrency as $booking) {
            $totalDueFromCompaniesByCurrency[$booking->currency] += $booking->total_due;
        }

        // تجميع إجماليات حجوزات الوكلاء
        foreach ($bookingsByAgentCurrency as $booking) {
            $totalDueToAgentsByCurrency[$booking->currency] += $booking->total_due;
        }

        // ===================================
        // 🧮 حساب المتبقي حسب العملة
        // ===================================

        // حساب المتبقي من الشركات حسب العملة
        $totalRemainingByCurrency = ['SAR' => 0, 'KWD' => 0];
        foreach ($companiesReport as $company) {
            $remainingByCurrency = $company->remaining_by_currency ?? [
                'SAR' => $company->remaining,
            ];
            foreach ($remainingByCurrency as $currency => $amount) {
                $totalRemainingByCurrency[$currency] += $amount;
            }
        }

        // حساب المتبقي للوكلاء حسب العملة
        $agentRemainingByCurrency = ['SAR' => 0, 'KWD' => 0];
        foreach ($agentsReport as $agent) {
            $agentTotals = $agent->getTotalsByCurrency();
            foreach ($agentTotals as $currency => $data) {
                if (isset($totalDueToAgentsByCurrency[$currency])) {
                    $totalDueToAgentsByCurrency[$currency] += $data['due'];
                    $totalRemainingToAgentsByCurrency[$currency] += $data['remaining'];
                }
            }
        }

        // ===================================
        // 📊 مدفوعات الوكلاء التفصيلية
        // ===================================

        // حساب المدفوعات حسب العملة للوكلاء (للعرض في الجداول)
        $agentPaymentsByCurrency = [];
        $agentPaymentsData = AgentPayment::select(
            'currency',
            DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as total_paid'),
            DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_discounts')
        )
            ->groupBy('currency')
            ->get();

        foreach ($agentPaymentsData as $payment) {
            $agentPaymentsByCurrency[$payment->currency] = [
                'paid' => (float) $payment->total_paid,
                'discounts' => (float) $payment->total_discounts
            ];
        }
        // إضافة العملات الافتراضية لضمان وجود المفاتيح دائمًا
        foreach (['SAR', 'KWD'] as $currency) {
            if (!isset($agentPaymentsByCurrency[$currency])) {
                $agentPaymentsByCurrency[$currency] = [
                    'paid' => 0,
                    'discounts' => 0
                ];
            }
        }
        // متغير منفصل للمدفوعات البسيطة (للملخص)
        $totalPaidToAgentsByCurrency = [];
        foreach ($agentPaymentsData as $payment) {
            $totalPaidToAgentsByCurrency[$payment->currency] = $payment->total_paid;
        }

        // ===================================
        // 💹 حساب صافي الربح حسب العملة
        // ===================================

        $netProfitByCurrency = [
            'SAR' => $totalRemainingByCurrency['SAR'] - $agentRemainingByCurrency['SAR'],
            'KWD' => $totalRemainingByCurrency['KWD'] - $agentRemainingByCurrency['KWD'],
        ];

        // ===================================
        // 📤 إرجاع البيانات للواجهة
        // ===================================

        return view('reports.daily', [
            // البيانات الأساسية
            'todayBookings' => $todayBookings,
            'companiesReport' => $companiesReport,
            'agentsReport' => $agentsReport, // pagination للعرض
            'allAgentsData' => $allAgentsForCalculations, // ✅ البيانات الكاملة للحسابات
            'agentsCurrentBalances' => $allAgentsForCalculations->pluck('current_balance', 'id'),
            'hotelsReport' => $hotelsReport,

            // ✅ الحسابات الخاصة بالوكلاء
            'totalDueToAgentsByCurrency' => $agentsTotalCalculations['total_due_by_currency'] ?? [],
            'totalPaidToAgentsByCurrency' => $agentsTotalCalculations['total_paid_by_currency'] ?? [],
            'totalDiscountsToAgentsByCurrency' => $agentsTotalCalculations['total_discounts_by_currency'] ?? [],
            'totalRemainingToAgentsByCurrency' => $agentsTotalCalculations['total_remaining_by_currency'] ?? [],
            'agentsTotalCalculations' => $agentsTotalCalculations,

            // باقي البيانات...
            'totalDueFromCompanies' => $totalDueFromCompanies,
            'totalPaidToHotels' => $totalPaidToHotels,
            'totalRemainingFromCompanies' => $totalRemainingFromCompanies,
            'totalRemainingToHotels' => $totalRemainingToHotels,
            'netProfit' => $netProfit,

            // الإشعارات
            'recentCompanyEdits' => $recentCompanyEdits,
            'resentAgentEdits' => $resentAgentEdits,

            // بيانات الرسوم البيانية
            'chartDates' => $chartData['chartDates'],
            'bookingCounts' => $chartData['bookingCounts'],
            'receivableBalances' => $chartData['receivableBalances'],
            'payableBalances' => $chartData['payableBalances'],
            'dailyEventDetails' => $chartData['dailyEventDetails'],
            'netBalanceDates' => $chartData['netBalanceDates'],
            'netBalances' => $chartData['netBalances'],
            'netBalancesKWD' => $chartData['netBalancesKWD'],

            // بيانات العملات والمدفوعات
            'companyPaymentsByCurrency' => $companyPaymentsByCurrency,
            'agentPaymentsByCurrency' => $agentPaymentsByCurrency,
            'totalDueFromCompaniesByCurrency' => $totalDueFromCompaniesByCurrency,
            'netProfitByCurrency' => $netProfitByCurrency,

            // ✅ إجماليات الشركات الصحيحة (من كل الشركات، ليست أول صفحة)
            'totalDueFromCompaniesByCurrency'       => $companyTotals['by_currency']['due'],
            'totalPaidByCompaniesByCurrency'        => $companyTotals['by_currency']['paid'],
            'totalDiscountsFromCompaniesByCurrency' => $companyTotals['by_currency']['discounts'],
            'totalRemainingFromCompaniesByCurrency' => $companyTotals['by_currency']['remaining'],

            'totalDueFromCompanies'       => $companyTotals['grand']['due'],
            'totalPaidByCompanies'        => $companyTotals['grand']['paid'],
            'totalDiscountsFromCompanies' => $companyTotals['grand']['discounts'],
            'totalRemainingFromCompanies' => $companyTotals['grand']['remaining'],
        ]);
    }
    // حساب إجمالي المستحق مظبوط مش أول باجيناشن 
    private function computeCompanyTotals(Builder $companiesQuery, array $currencies = ['SAR', 'KWD']): array
    {
        // ✅ نجيب كل الشركات (بدون pagination) ونحسب التوتالات مرة واحدة
        $allCompaniesForCalculations = $companiesQuery->get()
            ->map(function ($company) {
                $company->total_bookings_count = $company->bookings_count + $company->land_trip_bookings_count;
                $company->current_balance = $company->currentBalance();
                $company->calculateTotals(); // لازم تكون بتعبي computed_*_by_currency
                return $company;
            })
            ->sortByDesc('computed_total_due')
            ->values();

        // تهيئة مجاميع حسب العملة
        $totalDueFromCompaniesByCurrency       = array_fill_keys($currencies, 0.0);
        $totalPaidByCompaniesByCurrency        = array_fill_keys($currencies, 0.0);
        $totalDiscountsFromCompaniesByCurrency = array_fill_keys($currencies, 0.0);

        foreach ($allCompaniesForCalculations as $company) {
            foreach (($company->computed_total_due_by_currency ?? []) as $cur => $amt) {
                if (!array_key_exists($cur, $totalDueFromCompaniesByCurrency)) $totalDueFromCompaniesByCurrency[$cur] = 0.0;
                $totalDueFromCompaniesByCurrency[$cur] += (float) $amt;
            }
            foreach (($company->computed_total_paid_by_currency ?? []) as $cur => $amt) {
                if (!array_key_exists($cur, $totalPaidByCompaniesByCurrency)) $totalPaidByCompaniesByCurrency[$cur] = 0.0;
                $totalPaidByCompaniesByCurrency[$cur] += (float) $amt;
            }
            foreach (($company->computed_total_discounts_by_currency ?? []) as $cur => $amt) {
                if (!array_key_exists($cur, $totalDiscountsFromCompaniesByCurrency)) $totalDiscountsFromCompaniesByCurrency[$cur] = 0.0;
                $totalDiscountsFromCompaniesByCurrency[$cur] += (float) $amt;
            }
        }

        // المتبقي = المستحق − (المدفوع + الخصومات) لكل عملة
        $totalRemainingFromCompaniesByCurrency = [];
        foreach ($totalDueFromCompaniesByCurrency as $cur => $due) {
            $paid      = $totalPaidByCompaniesByCurrency[$cur]        ?? 0.0;
            $discounts = $totalDiscountsFromCompaniesByCurrency[$cur] ?? 0.0;
            $totalRemainingFromCompaniesByCurrency[$cur] = $due - ($paid + $discounts);
        }

        // مجاميع كلية عبر كل العملات
        $grandTotalDueFromCompanies       = array_sum($totalDueFromCompaniesByCurrency);
        $grandTotalPaidByCompanies        = array_sum($totalPaidByCompaniesByCurrency);
        $grandTotalDiscountsFromCompanies = array_sum($totalDiscountsFromCompaniesByCurrency);
        $grandTotalRemainingFromCompanies = array_sum($totalRemainingFromCompaniesByCurrency);

        return [
            'all_companies' => $allCompaniesForCalculations, // Collection
            'by_currency' => [
                'due'        => $totalDueFromCompaniesByCurrency,
                'paid'       => $totalPaidByCompaniesByCurrency,
                'discounts'  => $totalDiscountsFromCompaniesByCurrency,
                'remaining'  => $totalRemainingFromCompaniesByCurrency,
            ],
            'grand' => [
                'due'        => $grandTotalDueFromCompanies,
                'paid'       => $grandTotalPaidByCompanies,
                'discounts'  => $grandTotalDiscountsFromCompanies,
                'remaining'  => $grandTotalRemainingFromCompanies,
            ],
        ];
    }
    /**
     * دالة جلب جهات الحجز بـ AJAX مع Pagination
     * نفس طريقة الفنادق تماماً ولكن للوكلاء
     */
    public function getAgentsAjax(Request $request)
    {
        $page = $request->get('agents_page', 1);
        $perPage = 10;

        // 1. جلب جميع الوكلاء للحسابات
        $allAgents = Agent::with(['bookings', 'payments'])
            ->withCount('bookings')
            ->get()
            ->map(function ($agent) {
                $agent->calculateTotals();
                $agent->current_balance = $agent->currentBalance();
                return $agent;
            })
            ->sortByDesc('computed_total_due');

        // 2. حساب الإجماليات من جميع الوكلاء
        $agentsTotalCalculations = [
            'total_due_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_paid_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_discounts_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_remaining_by_currency' => ['SAR' => 0, 'KWD' => 0],
            'total_bookings_count' => 0
        ];

        foreach ($allAgents as $agent) {
            // نفس الحسابات المذكورة في دالة daily()
            $agentsTotalCalculations['total_bookings_count'] += $agent->bookings_count;

            $dueByCurrency = $agent->computed_total_due_by_currency ??
                ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);
            foreach ($dueByCurrency as $currency => $amount) {
                $agentsTotalCalculations['total_due_by_currency'][$currency] += $amount;
            }

            $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
            $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];

            foreach (['SAR', 'KWD'] as $currency) {
                $agentsTotalCalculations['total_paid_by_currency'][$currency] += $paidByCurrency[$currency] ?? 0;
                $agentsTotalCalculations['total_discounts_by_currency'][$currency] += $discountsByCurrency[$currency] ?? 0;
            }

            $remainingByCurrency = $agent->computed_remaining_by_currency ??
                ($agent->remaining_by_currency ?? ['SAR' => $agent->remaining_amount ?? 0]);
            foreach ($remainingByCurrency as $currency => $amount) {
                $agentsTotalCalculations['total_remaining_by_currency'][$currency] += $amount;
            }
        }

        // 3. إنشاء pagination للعرض
        $totalItems = $allAgents->count();
        $totalPages = ceil($totalItems / $perPage);

        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        } elseif ($page < 1) {
            $page = 1;
        }

        $agentsReport = new \Illuminate\Pagination\LengthAwarePaginator(
            $allAgents->forPage($page, $perPage),
            $totalItems,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'agents_page',
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'html' => view('reports.hoteldailyReport.agents-table', [
                    'agentsReport' => $agentsReport,
                    'agentsTotalCalculations' => $agentsTotalCalculations // ✅ تمرير الحسابات الإجمالية
                ])->render(),
                'pagination' => (string) $agentsReport->appends(request()->query())->links('pagination::bootstrap-4'),
                'balances' => $agentsReport->mapWithKeys(fn($a) => [$a->id => $a->current_balance])
            ]);
        }

        return $agentsReport;
    }
    // 
    /**
     * دالة جلب الشركات بـ AJAX مع Pagination
     * نفس طريقة الوكلاء والفنادق
     */
    public function getCompaniesAjax(Request $request)
    {
        try {
            $page = $request->get('companies_page', 1);
            $perPage = 15; // عدد العناصر في كل صفحة

            // جلب الشركات مع العلاقات المطلوبة
            $companiesQuery = Company::withCount(['bookings', 'landTripBookings'])
                ->with([
                    'bookings' => function ($query) {
                        $query->select('id', 'company_id', 'sale_price', 'rooms', 'days', 'currency', 'amount_due_from_company');
                    },
                    'payments' => function ($query) {
                        $query->select('id', 'company_id', 'amount', 'currency', 'payment_date');
                    },
                    'landTripBookings' => function ($query) {
                        $query->select('id', 'company_id', 'amount_due_from_company', 'currency');
                    }
                ])
                // فقط الشركات التي لها حجوزات (عادية أو رحلات برية)
                ->having('bookings_count', '>', 0)
                ->orHaving('land_trip_bookings_count', '>', 0);

            // الحصول على إجمالي العدد
            $totalItems = $companiesQuery->count();
            $totalPages = ceil($totalItems / $perPage);

            // ضبط رقم الصفحة
            if ($page > $totalPages && $totalPages > 0) {
                $page = $totalPages;
            } elseif ($page < 1) {
                $page = 1;
            }

            // الحصول على الشركات ومعالجتها
            $companies = $companiesQuery->get()
                ->map(function ($company) {
                    $company->total_bookings_count = $company->bookings_count + $company->land_trip_bookings_count;
                    $company->calculateTotals();
                    $company->current_balance = $company->currentBalance(); // يرجع array أو رقم حسب تنفيذك

                    return $company;
                })
                ->sortByDesc('computed_total_due')
                ->values();

            // إنشاء pagination
            $companiesReport = new \Illuminate\Pagination\LengthAwarePaginator(
                $companies->forPage($page, $perPage),
                $totalItems,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'pageName' => 'companies_page',
                ]
            );

            // حساب الإجماليات للتقرير الكلي
            $totalDueByCurrency = ['SAR' => 0, 'KWD' => 0];
            $totalPaidByCurrency = ['SAR' => 0, 'KWD' => 0];
            $totalRemainingByCurrency = ['SAR' => 0, 'KWD' => 0];



            foreach ($companies as $company) {
                $dueByCurrency = $company->total_due_by_currency ?? ['SAR' => $company->total_due];
                $paidByCurrency = $company->total_paid_by_currency ?? ['SAR' => $company->total_paid];
                $remainingByCurrency = $company->remaining_by_currency ?? ['SAR' => $company->remaining];

                foreach ($dueByCurrency as $currency => $amount) {
                    $totalDueByCurrency[$currency] += $amount;
                }

                foreach ($paidByCurrency as $currency => $amount) {
                    $totalPaidByCurrency[$currency] += $amount;
                }

                foreach ($remainingByCurrency as $currency => $amount) {
                    $totalRemainingByCurrency[$currency] += $amount;
                }
            }
            // ✅ إضافة متغير recentCompanyEdits
            $recentCompanyEdits = \App\Models\Notification::whereIn('type', [
                'تعديل',
                'تعديل دفعة',
                'دفعة جديدة',
                'حذف دفعة'
            ])
                ->where('created_at', '>=', now()->subDays(2))
                ->get()
                ->groupBy('message');

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('reports.hoteldailyReport.companies-table', [
                        'companiesReport' => $companiesReport,
                        'totalDueByCurrency' => $totalDueByCurrency,
                        'totalPaidByCurrency' => $totalPaidByCurrency,
                        'totalRemainingByCurrency' => $totalRemainingByCurrency,
                        'recentCompanyEdits' => $recentCompanyEdits // ✅ إضافة هنا
                    ])->render(),
                    'pagination' => (string) $companiesReport->appends(request()->query())->links('pagination::bootstrap-4'),
                    'totals' => [
                        'totalDueByCurrency' => $totalDueByCurrency,
                        'totalPaidByCurrency' => $totalPaidByCurrency,
                        'totalRemainingByCurrency' => $totalRemainingByCurrency
                    ]
                ]);
            }

            return $companiesReport;
        } catch (\Exception $e) {
            Log::error('Error in getCompaniesAjax: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'حدث خطأ أثناء تحميل بيانات الشركات',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getHotelsAjax(Request $request)
    {
        $page = $request->get('hotels_page', 1);
        $perPage = 10;

        $hotelsData = Hotel::withCount('bookings')
            ->with(['bookings' => function ($query) {
                $query->select('hotel_id', 'cost_price', 'rooms', 'days', 'amount_due_to_hotel', 'currency'); // ✅ إضافة العملة
            }])
            ->get()
            ->map(function ($hotel) {
                // ✅ حساب المستحق حسب كل عملة
                $totalDueByCurrency = ['SAR' => 0, 'KWD' => 0];

                foreach ($hotel->bookings as $booking) {
                    $bookingDue = $booking->amount_due_to_hotel ?? ($booking->cost_price * $booking->rooms * $booking->days);
                    $currency = $booking->currency ?? 'SAR'; // العملة الافتراضية ريال سعودي
                    $totalDueByCurrency[$currency] += $bookingDue;
                }

                // إضافة البيانات المحسوبة للفندق
                $hotel->total_due_by_currency = $totalDueByCurrency;
                $hotel->total_due = $totalDueByCurrency['SAR'] + $totalDueByCurrency['KWD']; // للترتيب فقط

                return $hotel;
            })
            ->sortByDesc('total_due');

        // إنشاء pagination
        $hotelsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $hotelsData->forPage($page, $perPage),
            $hotelsData->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'hotels_page'
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'html' => view('reports.hoteldailyReport.hotels-table', ['hotelsReport' => $hotelsPaginated])->render(),
                'pagination' => (string) $hotelsPaginated->appends(request()->query())->links('pagination::bootstrap-4')
            ]);
        }

        return $hotelsPaginated;
    }
    /**
     * دالة منفصلة لحساب بيانات الرسم البياني
     */
    private function getDailyChartData()
    {
        // --- بيانات الحجوزات اليومية ---
        $days = 30;
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $dateField = 'created_at';

        // جلب عدد الحجوزات مجمعة حسب اليوم
        $bookingsData = Booking::select(
            DB::raw("DATE($dateField) as date"),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween($dateField, [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('count', 'date');

        // إنشاء فترة زمنية كاملة
        $period = CarbonPeriod::create($startDate, $endDate);
        $chartDates = [];
        $bookingCounts = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartDates[] = $date->format('d/m');
            $bookingCounts[] = $bookingsData[$formattedDate] ?? 0;
        }

        // --- بيانات مفصلة للأحداث ---
        $eventsData = $this->getDetailedEventsData($startDate, $endDate, $period);

        // --- بيانات صافي الرصيد ---
        $netBalanceData = $this->getNetBalanceData();

        return [
            'chartDates' => $chartDates,
            'bookingCounts' => $bookingCounts,
            'receivableBalances' => $eventsData['receivableBalances'],
            'payableBalances' => $eventsData['payableBalances'],
            'dailyEventDetails' => $eventsData['dailyEventDetails'],
            'netBalanceDates' => $netBalanceData['dates'],
            'netBalances' => $netBalanceData['sar'],
            'netBalancesKWD' => $netBalanceData['kwd'],
        ];
    }

    /**
     * دالة لحساب الأحداث المفصلة
     */
    private function getDetailedEventsData($startDate, $endDate, $period)
    {
        // جلب الحجوزات مع تفاصيلها
        $bookingsForChart = Booking::with(['company', 'agent', 'hotel'])
            ->select(
                'check_in',
                'client_name',
                'company_id',
                'agent_id',
                'hotel_id',
                DB::raw('sale_price * rooms * days as company_due'),
                DB::raw('cost_price * rooms * days as agent_due')
            )
            ->whereBetween('check_in', [$startDate, $endDate])
            ->orderBy('check_in', 'asc')
            ->get();

        // جلب دفعات الشركات
        $companyPaymentsForChart = Payment::with('company')
            ->select('payment_date', 'amount', 'company_id', 'notes')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'asc')
            ->get();

        // جلب دفعات الوكلاء
        $agentPaymentsForChart = AgentPayment::with('agent')
            ->select('payment_date', 'amount', 'agent_id', 'notes')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'asc')
            ->get();

        // تجميع الأحداث
        $allEventsWithDetails = [];

        // معالجة الحجوزات
        foreach ($bookingsForChart as $booking) {
            $eventDate = Carbon::parse($booking->check_in)->format('Y-m-d');
            $allEventsWithDetails[$eventDate][] = [
                'type' => 'booking',
                'company_change' => $booking->company_due,
                'agent_change' => $booking->agent_due,
                'details' => "حجز: " . Str::limit($booking->client_name ?? 'N/A', 15)
                    . " (+" . number_format($booking->company_due) . " ش)"
                    . " (+" . number_format($booking->agent_due) . " ج)"
            ];
        }

        // معالجة دفعات الشركات
        foreach ($companyPaymentsForChart as $payment) {
            $eventDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
            $allEventsWithDetails[$eventDate][] = [
                'type' => 'company_payment',
                'company_change' => -$payment->amount,
                'agent_change' => 0,
                'details' => "دفعة من: " . Str::limit($payment->company->name ?? 'N/A', 10)
                    . " (-" . number_format($payment->amount) . " ش)"
                    . ($payment->notes ? " - " . Str::limit($payment->notes, 10) : "")
            ];
        }

        // معالجة دفعات الوكلاء
        foreach ($agentPaymentsForChart as $payment) {
            $eventDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
            $allEventsWithDetails[$eventDate][] = [
                'type' => 'agent_payment',
                'company_change' => 0,
                'agent_change' => -$payment->amount,
                'details' => "دفعة إلى: " . Str::limit($payment->agent->name ?? 'N/A', 10)
                    . " (-" . number_format($payment->amount) . " ج)"
                    . ($payment->notes ? " - " . Str::limit($payment->notes, 10) : "")
            ];
        }

        // حساب الأرصدة التراكمية
        $runningReceivables = 0;
        $runningPayables = 0;
        $receivableBalances = [];
        $payableBalances = [];
        $dailyEventDetails = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartLabelDate = $date->format('d/m');
            $eventsTodayDetails = [];

            if (isset($allEventsWithDetails[$formattedDate])) {
                foreach ($allEventsWithDetails[$formattedDate] as $event) {
                    $runningReceivables += $event['company_change'];
                    $runningPayables += $event['agent_change'];
                    $eventsTodayDetails[] = $event['details'];
                }
            }

            $receivableBalances[] = round(max(0, $runningReceivables), 2);
            $payableBalances[] = round(max(0, $runningPayables), 2);
            $dailyEventDetails[$chartLabelDate] = $eventsTodayDetails;
        }

        return [
            'receivableBalances' => $receivableBalances,
            'payableBalances' => $payableBalances,
            'dailyEventDetails' => $dailyEventDetails
        ];
    }

    /**
     * دالة لحساب بيانات صافي الرصيد
     */
    private function getNetBalanceData()
    {
        // جلب دفعات الشركات بالعملات المختلفة
        $companyPaymentsSAR = Payment::select('payment_date as date', 'amount')
            ->where('currency', 'SAR')
            ->orderBy('date', 'asc')
            ->get();

        $companyPaymentsKWD = Payment::select('payment_date as date', 'amount')
            ->where('currency', 'KWD')
            ->orderBy('date', 'asc')
            ->get();

        // جلب دفعات الوكلاء
        $agentPaymentsSAR = AgentPayment::select('payment_date as date', DB::raw('-amount as amount'))
            ->where('currency', 'SAR')
            ->orderBy('date', 'asc')
            ->get();

        $agentPaymentsKWD = AgentPayment::select('payment_date as date', DB::raw('-amount as amount'))
            ->where('currency', 'KWD')
            ->orderBy('date', 'asc')
            ->get();

        // حساب للريال السعودي
        $allTransactionsSAR = $companyPaymentsSAR->concat($agentPaymentsSAR);
        $sortedTransactionsSAR = $allTransactionsSAR->sortBy('date');

        $runningBalanceSAR = 0;
        $netBalanceDataSAR = [];

        foreach ($sortedTransactionsSAR as $transaction) {
            $dateString = Carbon::parse($transaction->date)->format('Y-m-d');
            $runningBalanceSAR += $transaction->amount;
            $netBalanceDataSAR[$dateString] = $runningBalanceSAR;
        }

        // حساب للدينار الكويتي
        $allTransactionsKWD = $companyPaymentsKWD->concat($agentPaymentsKWD);
        $sortedTransactionsKWD = $allTransactionsKWD->sortBy('date');

        $runningBalanceKWD = 0;
        $netBalanceDataKWD = [];

        foreach ($sortedTransactionsKWD as $transaction) {
            $dateString = Carbon::parse($transaction->date)->format('Y-m-d');
            $runningBalanceKWD += $transaction->amount;
            $netBalanceDataKWD[$dateString] = $runningBalanceKWD;
        }

        // دمج التواريخ وتجهيز البيانات النهائية
        $allDates = array_unique(array_merge(
            array_keys($netBalanceDataSAR),
            array_keys($netBalanceDataKWD)
        ));
        sort($allDates);

        $netBalanceDates = [];
        $netBalancesSAR = [];
        $netBalancesKWD = [];

        $lastBalanceSAR = 0;
        $lastBalanceKWD = 0;

        foreach ($allDates as $date) {
            $netBalanceDates[] = Carbon::parse($date)->format('d/m');

            if (isset($netBalanceDataSAR[$date])) {
                $lastBalanceSAR = $netBalanceDataSAR[$date];
            }
            $netBalancesSAR[] = round($lastBalanceSAR, 2);

            if (isset($netBalanceDataKWD[$date])) {
                $lastBalanceKWD = $netBalanceDataKWD[$date];
            }
            $netBalancesKWD[] = round($lastBalanceKWD, 2);
        }

        return [
            'dates' => $netBalanceDates,
            'sar' => $netBalancesSAR,
            'kwd' => $netBalancesKWD
        ];
    }
    /**
     * عرض صفحة التقارير المتقدمة
     */
    public function advanced(Request $request)
    {
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

        // 1. بنجيب الحجوزات المباشرة النشطة (من جدول bookings)
        $directActiveBookings = Booking::whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->with(['hotel', 'company', 'agent'])
            ->get();

        // 2. بنجيب حجوزات الرحلات البرية النشطة (جديد)
        $landTripActiveBookings = \App\Models\LandTripBooking::with(['landTrip.hotel', 'company'])
            ->whereHas('landTrip', function ($query) use ($today) {
                $query->whereDate('departure_date', '<=', $today)
                    ->whereDate('return_date', '>', $today)
                    ->where('status', 'active');
            })
            ->get()
            ->map(function ($booking) {
                // تحويل حجز الرحلة البرية لصيغة متوافقة مع الحجز العادي
                return (object)[
                    'id' => 'LT-' . $booking->id,
                    'client_name' => $booking->client_name,
                    'hotel' => $booking->landTrip->hotel,
                    'hotel_id' => $booking->landTrip->hotel_id,
                    'company' => $booking->company,
                    'check_in' => \Carbon\Carbon::parse($booking->landTrip->departure_date),
                    'check_out' => \Carbon\Carbon::parse($booking->landTrip->return_date),
                    'rooms' => $booking->rooms,
                    'days' => $booking->landTrip->days_count, // لعرض عدد أيام الرحلة
                    'is_land_trip' => true,
                    // أضف هذا السطر لنسخ كامل كائن الرحلة البرية إذا كنت بحاجة إليه
                    'landTrip' => $booking->landTrip
                ];
            });

        // 3. دمج الحجوزات المباشرة وحجوزات الرحلات البرية
        $activeBookings = $directActiveBookings->concat($landTripActiveBookings);

        // 4. بنجيب الحجوزات المباشرة اللي هتدخل في التاريخ المحدد
        $directCheckingInToday = Booking::whereDate('check_in', $today)
            ->with(['hotel', 'company', 'agent'])
            ->get();

        // 5. بنجيب حجوزات الرحلات البرية اللي هتدخل في التاريخ المحدد (جديد)
        $landTripCheckingInToday = \App\Models\LandTripBooking::with(['landTrip.hotel', 'company'])
            ->whereHas('landTrip', function ($query) use ($today) {
                $query->whereDate('departure_date', $today)
                    ->where('status', 'active');
            })
            ->get()
            ->map(function ($booking) {
                return (object)[
                    'id' => 'LT-' . $booking->id,
                    'client_name' => $booking->client_name,
                    'hotel' => $booking->landTrip->hotel,
                    'hotel_id' => $booking->landTrip->hotel_id,
                    'company' => $booking->company,
                    'check_in' => \Carbon\Carbon::parse($booking->landTrip->departure_date),
                    'check_out' => \Carbon\Carbon::parse($booking->landTrip->return_date),
                    'rooms' => $booking->rooms,
                    'is_land_trip' => true
                ];
            });

        // 6. دمج حجوزات اليوم (الدخول)
        $checkingInToday = $directCheckingInToday->concat($landTripCheckingInToday);

        // 7. بنجيب الحجوزات المباشرة اللي هتخرج في اليوم التالي للتاريخ المحدد
        $directCheckingOutTomorrow = Booking::whereDate('check_out', $tomorrow)
            ->with(['hotel', 'company', 'agent'])
            ->get();

        // 8. بنجيب حجوزات الرحلات البرية اللي هتخرج في اليوم التالي (جديد)
        $landTripCheckingOutTomorrow = \App\Models\LandTripBooking::with(['landTrip.hotel', 'company'])
            ->whereHas('landTrip', function ($query) use ($tomorrow) {
                $query->whereDate('return_date', $tomorrow)
                    ->where('status', 'active');
            })
            ->get()
            ->map(function ($booking) {
                return (object)[
                    'id' => 'LT-' . $booking->id,
                    'client_name' => $booking->client_name,
                    'hotel' => $booking->landTrip->hotel,
                    'hotel_id' => $booking->landTrip->hotel_id,
                    'company' => $booking->company,
                    'check_in' => $booking->landTrip->departure_date,
                    'check_out' => $booking->landTrip->return_date,
                    'rooms' => $booking->rooms,
                    'is_land_trip' => true
                ];
            });

        // 9. دمج حجوزات الغد (الخروج)
        $checkingOutTomorrow = $directCheckingOutTomorrow->concat($landTripCheckingOutTomorrow);

        // 10. ملخص إحصائي عن الفنادق - معدل بليشمل كل الحجوزات
        $hotelStats = Hotel::withCount(['bookings as direct_bookings_count' => function ($query) use ($today) {
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
            ->map(function ($hotel) use ($activeBookings, $checkingInToday, $checkingOutTomorrow) {
                // قيمة افتراضية لعدد الغرف (30 غرفة لكل فندق)
                $defaultRooms = $hotel->purchased_rooms_count ?? 30;

                // بنحسب معدل الإشغال للفندق النهاردة (الآن يشمل الرحلات البرية)
                $occupiedRooms = $activeBookings->where('hotel_id', $hotel->id)->sum('rooms');

                // تحديث عدد الدخول والخروج ليشمل الرحلات البرية
                $hotel->active_bookings = $occupiedRooms;
                $hotel->checking_in_today = $checkingInToday->where('hotel_id', $hotel->id)->count();
                $hotel->checking_out_tomorrow = $checkingOutTomorrow->where('hotel_id', $hotel->id)->count();

                // حساب معدل الإشغال الشامل (الحجوزات المباشرة + الرحلات البرية)
                $hotel->occupancy_rate = $defaultRooms > 0 ? round(($occupiedRooms / $defaultRooms) * 100) : 0;
                $hotel->total_rooms = $defaultRooms;

                return $hotel;
            })
            // ✅ إضافة التصفية هنا: إخفاء الفنادق التي معدل إشغالها صفر
            ->filter(function ($hotel) {
                return $hotel->occupancy_rate > 0;
            })
            // ✅ ترتيب الفنادق النشطة حسب معدل الإشغال (الأعلى أولاً)
            ->sortByDesc('occupancy_rate')
            ->values(); // إعادة فهرسة المجموعة


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
        $endDate = (clone $startDate)->addDays(6); // أسبوع كامل

        // جلب قائمة الفنادق
        $hotels = Hotel::select('id', 'name')->get();

        // نقوم بتعيين عدد غرف افتراضي لكل فندق (يمكنك تغيير هذه القيمة)
        $defaultRoomsPerHotel = 30; // قيمة افتراضية لكل فندق

        // إنشاء مصفوفة تحتوي على عدد الغرف لكل فندق
        $totalRoomsByHotelId = $hotels->mapWithKeys(function ($hotel) use ($defaultRoomsPerHotel) {
            return [$hotel->id => $defaultRoomsPerHotel];
        });

        $totalRooms = $totalRoomsByHotelId->sum();

        // حساب الإشغال لكل يوم
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $dateLabel = $date->format('d/m');

            // 1. جلب الحجوزات المباشرة في هذا اليوم مع عدد الغرف
            $directBookings = Booking::whereDate('check_in', '<=', $dateString)
                ->whereDate('check_out', '>', $dateString)
                ->select('hotel_id', DB::raw('SUM(rooms) as booked_rooms'))
                ->groupBy('hotel_id')
                ->get()
                ->pluck('booked_rooms', 'hotel_id')
                ->toArray();

            // 2. جلب حجوزات الرحلات البرية في هذا اليوم (جديد)
            $landTripBookings = \App\Models\LandTripBooking::select(
                'land_trips.hotel_id',
                DB::raw('SUM(land_trip_bookings.rooms) as booked_rooms')
            )
                ->join('land_trips', 'land_trips.id', '=', 'land_trip_bookings.land_trip_id')
                ->whereDate('land_trips.departure_date', '<=', $dateString)
                ->whereDate('land_trips.return_date', '>', $dateString)
                ->where('land_trips.status', 'active')
                ->groupBy('land_trips.hotel_id')
                ->get()
                ->pluck('booked_rooms', 'hotel_id')
                ->toArray();

            // 3. دمج الحجوزات من كلا المصدرين
            $allBookings = [];
            foreach ($hotels as $hotel) {
                $directBooked = $directBookings[$hotel->id] ?? 0;
                $landTripBooked = $landTripBookings[$hotel->id] ?? 0;
                $allBookings[$hotel->id] = $directBooked + $landTripBooked;
            }

            // 4. حساب الغرف المشغولة والمتاحة لكل فندق
            $occupancyByHotel = [];
            $totalBooked = 0;

            foreach ($hotels as $hotel) {
                $hotelTotalRooms = $totalRoomsByHotelId[$hotel->id];
                $booked = $allBookings[$hotel->id] ?? 0;
                $available = max(0, $hotelTotalRooms - $booked);
                $occupancyRate = $hotelTotalRooms > 0 ? round(($booked / $hotelTotalRooms) * 100, 1) : 0;

                $occupancyByHotel[$hotel->id] = [
                    'name' => $hotel->name,
                    'booked' => $booked,
                    'available' => $available,
                    'total' => $hotelTotalRooms,
                    'rate' => $occupancyRate
                ];

                $totalBooked += $booked;
            }

            // 5. إضافة البيانات لهذا اليوم
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
            ->with(['hotel', 'agent', 'financialTracking'])
            ->orderBy('check_in')
            ->get()
            ->map(function ($b) {
                // احسب المستحق الكلي: كل الليالي × عدد الغرف × سعر البيع
                $b->total_company_due = $b->total_nights * $b->rooms * $b->sale_price;
                // بيانات الدفع من المتابعة المالية (لو موجودة)
                $b->company_payment_amount = $b->financialTracking->company_payment_amount ?? 0;
                $b->company_payment_status = $b->financialTracking->company_payment_status ?? 'غير مدفوع';

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

        // ✅ الرصيد الحالي حتى اليوم
        $currentBalance = $company->currentBalance();


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
            'totalRemainingByCurrency',
            'currentBalance'

        ));
    }
    // طباعة الحجوزات كشف حساب 
    public function exportCompanyBookingsPdf(Company $company)
    {
        // هات كل الحجوزات مع نفس الـ with والـ map!
        $bookings = $company->bookings()
            ->with(['hotel', 'agent', 'financialTracking'])
            ->where('amount_due_from_company', '>', 0)  // استبعاد الحجوزات التي المبلغ المستحق فيها صفر
            ->orderBy('check_in')
            ->get()
            ->map(function ($b) {
                $b->total_company_due = $b->total_nights * $b->rooms * $b->sale_price;
                $b->company_payment_amount = $b->financialTracking->company_payment_amount ?? 0;
                $b->company_payment_status = $b->financialTracking->company_payment_status ?? 'غير مدفوع';
                return $b;
            });

        $dueCount = $bookings->count();
        $totalDue = $bookings->sum('total_company_due');

        $allPayments = $company->payments()->orderBy('payment_date')->get();

        $remaining = $totalDue;
        $totalPaid = 0;
        foreach ($allPayments as $payment) {
            if ($remaining <= 0) break;
            $pay = min($payment->amount, $remaining);
            $totalPaid += $pay;
            $remaining -= $pay;
        }

        $totalRemaining = $totalDue - $totalPaid;

        // إجماليات العملات
        $totalDueByCurrency = $company->total_due_by_currency;
        $totalPaidByCurrency = $company->total_paid_by_currency;
        $totalRemainingByCurrency = $company->remaining_by_currency;
        // ✅ الرصيد الحالي حتى اليوم
        $currentBalance = $company->currentBalance();

        // رجع كل القيم للفيو
        return view('pdf.company_bookings', compact(
            'company',
            'bookings',
            'dueCount',
            'totalDue',
            'totalPaid',
            'totalRemaining',
            'totalDueByCurrency',
            'totalPaidByCurrency',
            'totalRemainingByCurrency',
            'currentBalance'
        ));
    }


    // تقرير حجوزات وكيل معين
    public function agentBookings($id)
    {
        // هات الوكيل المطلوب
        $agent = Agent::findOrFail($id);

        // هات كل الحجوزات بتاعة الوكيل مع بيانات الفندق والشركة
        $bookings = $agent->bookings()
            ->with(['hotel', 'company', 'financialTracking'])
            ->orderBy('check_in')
            ->get()
            ->map(function ($b) {
                // احسب المستحق للوكيل: عدد الليالي × عدد الغرف × سعر الفندق
                $b->due_to_agent = $b->rooms * $b->days * $b->cost_price;
                // بيانات الدفع من المتابعة المالية (لو موجودة)
                $b->agent_payment_amount = $b->financialTracking->agent_payment_amount ??
                    0;
                $b->agent_payment_status = $b->financialTracking->agent_payment_status ??
                    'غير مدفوع';
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

        // ✅ الرصيد الحالي للوكيل
        $currentBalance = $agent->currentBalance();

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
            'totalRemainingByCurrency',
            'currentBalance'
        ));
    }

    // تقرير حجوزات فندق معين
    public function hotelBookings($id)
    {
        // هات الفندق المطلوب
        $hotel = Hotel::findOrFail($id);

        // هات كل الحجوزات بتاعة الفندق مع بيانات الشركة والوكيل
        $bookings = Booking::where('hotel_id', $id)
            ->with(['company', 'agent'])->orderBy('check_in', 'asc')
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
            'is_discount'      => 'nullable|boolean',
            'account_id' => 'required|exists:accounts,id',
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
        // التحقق هل هي عملية خصم
        $isDiscount = $request->input('is_discount') == '1';

        // تعديل القيمة والملاحظات في حالة الخصم
        if ($isDiscount) {
            $validated['amount'] = -abs($validated['amount']);  // قيمة سالبة للخصم
            $validated['notes'] = 'خصم: ' . ($validated['notes'] ?? '');
        }

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
            'account_id' => $validated['account_id'],
        ]);


        // محاسبة الخصم أو الدفعة
        if ($isDiscount) {
            // ✅ الخصم: استخدم حساب الخصم الذي اختاره المستخدم -> مدين الحساب المختار، دائن الشركة
            AccountController::createCompanyDiscountWithChoice(
                $payment->company,
                abs($payment->amount),        // المبلغ موجب
                $validated['account_id'],     // حساب الخصم الذي اختاره المستخدم
                $payment->notes,
                $payment->id  
            );
        } else {
            // ✅ الدفعة العادية: استخدم حساب الدفع المختار
            AccountController::createCompanyPaymentJournalEntry(
                $payment->company,
                $payment->amount,
                $validated['account_id'],
                $payment->id 
            );
        }


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
    
        // إنشاء إشعار مناسب حسب نوع العملية
        $actionType = $isDiscount ? 'تم تطبيق خصم' : 'تم إضافة دفعة جديدة';
        $notificationType = $isDiscount ? 'خصم مطبق' : 'دفعة جديدة';
        $amountDisplay = abs($payment->amount); // استخدام القيمة المطلقة للعرض

        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "{$actionType} ({$payment->currency}) لشركة {$payment->company->name} بمبلغ {$amountDisplay} في تاريخ {$payment->payment_date}",
            'type' => $notificationType,
        ]);

        // رسالة نجاح مناسبة
        $successMsg = $isDiscount ?
            'تم تطبيق الخصم بنجاح' :
            'تم تسجيل الدفعة وتخصيصها على الحجوزات بنجاح!';

        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $validated['company_id'])
            ->with('success', $successMsg);
    }

    // إضافة دفعة جديدة لوكيل
    //public function storeAgentPayment(Request $request)
    //{
        // تحقق من البيانات اللي جاية من الفورم
        //$validated = $request->validate([
        //    'agent_id' => 'required|exists:agents,id',
        //    'amount'   => 'required|numeric|min:0',
        //    'currency' => 'required|in:SAR,KWD',  // التحقق من العملة
        //    'payment_account_id' => 'required|exists:accounts,id',
        //    'notes'    => 'nullable|string',
        //    // 'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // *** إضافة التحقق هنا ***
        //    // 'is_discount' => 'nullable|boolean', // جديد: علامة إذا كان خصم
        //    'booking_id' => 'nullable|exists:bookings,id',
        //]);
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
        //$payment = AgentPayment::create([
        //    'agent_id' => $validated['agent_id'],
        //    'amount' => $validated['amount'],
        //    'currency' => $validated['currency'],
        //    'payment_date' => now(),
        //    'notes' => $validated['notes'],
        //    'account_id' => $validated['payment_account_id'], // ✅ تسجيل الحساب المستخدم
        //    // 'receipt_path' => $receiptPath, // *** تأكد من إضافة هذا السطر هنا ***
        //    'employee_id' => Auth::id(), // إضافة الموظف الذي سجل الدفعة
        //]);
        // حدث بيانات الوكيل عشان القيم تتحدث
        // amount_paid_to_hotel تحديث قيمة الدفعة
        // الحجز نفسه : 
        // تحديث حجز واحد فقط إذا تم تمرير booking_id
        //if ($request->filled('booking_id')) {
        //    $booking = Booking::find($request->input('booking_id'));
        //    if ($booking) {
        //        // 🔥 إضافة القيد المحاسبي لدفعة الفندق
        //         AccountController::createHotelPaymentJournalEntry($booking, $payment->amount, $validated['payment_account_id']);
        //        $booking->increment('amount_paid_to_hotel', $payment->amount);
        //    }
        //}


        // إنشاء إشعار للدفعة العادية
       // Notification::create([
       //     'user_id' => Auth::id(),
       //     'message' => "تم إضافة دفعة جديدة لجهة الحجز {$payment->agent->name} بمبلغ {$payment->amount} {$payment->currency}",
       //     'type' => 'دفعة جديدة',
       // ]);

        // رسالة نجاح
        //$successMsg = "تم تسجيل الدفعة بقيمة {$payment->amount} {$validated['currency']} بنجاح";

        // رجع للصفحة مع رسالة نجاح
       // return redirect()->back()->with('success', $successMsg);
    //}


// إضافة دفعة جديدة لوكيل (تسديد مستحقات الفندق)
public function storeAgentPayment(Request $request)
{
    $validated = $request->validate([
        'agent_id' => 'required|exists:agents,id',
        'amount'   => 'required|numeric|min:0',
        'currency' => 'required|in:SAR,KWD',
        'payment_account_id' => 'required|exists:accounts,id', // ✅ جديد: حساب الدفع
        'notes'    => 'nullable|string',
        'booking_id' => 'nullable|exists:bookings,id', // اختياري لربط الحجز
    ]);

    // تسجيل الدفعة في جدول agent_payments
    $payment = AgentPayment::create([
        'agent_id' => $validated['agent_id'],
        'amount' => $validated['amount'],
        'currency' => $validated['currency'],
        'payment_date' => now(),
        'notes' => $validated['notes'],
        'account_id' => $validated['payment_account_id'], // ✅ تسجيل الحساب المستخدم
        'employee_id' => Auth::id(),
    ]);

    // إذا تم تمرير booking_id (دفعة مرتبطة بحجز معين)
    if ($request->filled('booking_id')) {
        $booking = Booking::find($request->input('booking_id'));
        if ($booking) {
            // ✅ استخدام حساب الدفع الذي اختاره المستخدم
            AccountController::createHotelPaymentJournalEntry($booking, $payment->amount, $validated['payment_account_id']);
            $booking->increment('amount_paid_to_hotel', $payment->amount);
        }
    } else {
        // إذا لم يتم ربط الحجز، قد تحتاج إلى منطق آخر (مثلاً سداد دين عام لجهة الحجز)
        // يمكنك استدعاء دالة أخرى لتسجيل قيد محاسبي عام لجهة الحجز
       AccountController::createAgentPaymentJournalEntry(
            Agent::find($validated['agent_id']),
            $validated['amount'],
            $validated['payment_account_id'],
            $payment->id   // <-- source_id
        );
    }

    Notification::create([
        'user_id' => Auth::id(),
        'message' => "تم إضافة دفعة جديدة لجهة الحجز {$payment->agent->name} بمبلغ {$payment->amount} {$payment->currency} من حساب " . ($payment->account->name ?? ''),
        'type' => 'دفعة جديدة',
    ]);

    return redirect()->back()->with('success', "تم تسجيل الدفعة بقيمة {$payment->amount} {$validated['currency']} بنجاح");
}


    /**
     * تطبيق خصم على وكيل كدفعة سالبة (نفس طريقة الشركات)
     */
    public function applyAgentDiscount(Request $request, $agentId)
    {
        // 1. التحقق من صحة البيانات المدخلة
        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'payment_account_id' => 'required|exists:accounts,id',
            'reason' => 'nullable|string|max:500'
        ], [
            'discount_amount.required' => 'مبلغ الخصم مطلوب',
            'discount_amount.min' => 'مبلغ الخصم يجب أن يكون أكبر من صفر',
            'currency.required' => 'العملة مطلوبة',
            'currency.in' => 'العملة يجب أن تكون ريال سعودي أو دينار كويتي'
        ]);

        try {
            // 2. العثور على الوكيل
            $agent = Agent::findOrFail($agentId);

            // 3. الحصول على المتبقي الحالي للوكيل بنفس العملة
            $remainingByCurrency = $agent->remaining_by_currency ?? [];
            $currentRemaining = $remainingByCurrency[$validated['currency']] ?? 0;

            // 4. التحقق من أن الخصم لا يتجاوز المبلغ المتبقي
            if ($validated['discount_amount'] > $currentRemaining) {
                return redirect()->back()
                    ->with('error', "مبلغ الخصم ({$validated['discount_amount']} {$validated['currency']}) أكبر من المبلغ المتبقي ({$currentRemaining} {$validated['currency']})");
            }

            // 5. بدء معاملة قاعدة البيانات لضمان الأمان
            DB::beginTransaction();

            // 6. إنشاء دفعة بقيمة سالبة (نفس طريقة الشركات)
            $discountPayment = AgentPayment::create([
                'agent_id' => $agent->id,
                'amount' => -$validated['discount_amount'], // 🔥 قيمة سالبة للخصم
                'currency' => $validated['currency'],
                'payment_date' => now(),
                'notes' => 'خصم مطبق: ' . ($validated['reason'] ?: 'خصم'),
                'account_id'    => $validated['payment_account_id'], // تسجيل الحساب المستخدم
                'employee_id' => Auth::id(),
            ]);

            AccountController::createAgentDiscountJournalEntry(
                $agent,
                $validated['discount_amount'],
                $validated['payment_account_id'],
                $validated['reason'] ?? null,
                $discountPayment->id 
            );


            // 7. إنشاء إشعار للمدراء
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم تطبيق خصم {$validated['discount_amount']} {$validated['currency']} على جهة الحجز {$agent->name}",
                'type' => 'خصم مطبق',
            ]);

            // 8. تأكيد المعاملة
            DB::commit();

            return redirect()->back()
                ->with('success', "تم تطبيق خصم {$validated['discount_amount']} {$validated['currency']} بنجاح على {$agent->name}");
        } catch (\Exception $e) {
            // 9. في حالة حدوث خطأ، إلغاء المعاملة
            DB::rollBack();

            // تسجيل الخطأ في اللوجز
            Log::error('خطأ في تطبيق خصم الوكيل: ' . $e->getMessage(), [
                'agent_id' => $agentId,
                'discount_amount' => $validated['discount_amount'] ?? 'غير محدد',
                'currency' => $validated['currency'] ?? 'غير محدد',
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تطبيق الخصم. يرجى المحاولة مرة أخرى.');
        }
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
    public function editAgentPayment($id)
    {
        // هات الدفعة المطلوبة
        $payment = AgentPayment::findOrFail($id);

        // رجع البيانات للواجهة
        return view('reports.edit_payment', compact('payment'));
    }

    // تحديث دفعة وكيل بعد التعديل
    public function updateAgentPayment(Request $request, $id)
    {
        // تحقق من البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes'  => 'nullable|string',
            'account_id' => 'required|exists:accounts,id',
        ]);

        // هات الدفعة وعدلها
        $payment = AgentPayment::findOrFail($id);
        $oldAmount = $payment->amount;
        $oldAccountId = $payment->account_id;

         // 1. حذف القيد المحاسبي القديم
        AccountController::deletePaymentJournalEntry($payment->id, 'agent');

        $payment->update($validated);

        // حدث بيانات الوكيل عشان القيم تتحدث
        $agent = $payment->agent;
        $agent->load('payments', 'bookings');

        // 3. إعادة إنشاء القيد المحاسبي الجديد بالمعلومات المحدثة
    //    لازم نفرق هل هي دفعة عادية (amount موجب) أم خصم (amount سالب)
    if ($payment->amount < 0) {
        // خصم من جهة الحجز (إيراد ليك)
        AccountController::createAgentDiscountJournalEntry(
            $payment->agent,
            abs($payment->amount),
            $payment->account_id,
            $payment->notes,
            $payment->id   // source_id
        );
    } else {
        // دفعة عادية لجهة الحجز (بتسدد مستحقات)
        AccountController::createAgentPaymentJournalEntry(
            $payment->agent,
            $payment->amount,
            $payment->account_id,
            $payment->id   // source_id
        );
    }

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
            'account_id' => 'required|exists:accounts,id'
        ]);

        // هات الدفعة وعدلها
        $payment = Payment::findOrFail($id);
        
        // 1. امسح القيد القديم
        AccountController::deletePaymentJournalEntry($payment->id, 'company');

        $payment->update([
            'amount'       => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'notes'        => $validated['notes'],
            'account_id'   => $validated['account_id'],
        ]);

        // 3. اعمل قيد جديد
        $isDiscount = ($payment->amount < 0);
        if ($isDiscount) {
            AccountController::createCompanyDiscountWithChoice(
                $payment->company,
                abs($payment->amount),
                $payment->account_id,  
                $payment->notes,
                $payment->id
            );
        } else {
            AccountController::createCompanyPaymentJournalEntry(
                $payment->company,
                $payment->amount,
                $payment->account_id,
                $payment->id
            );
        }

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
       if (strtolower(Auth::user()->role) !== 'admin') {
            abort(403, 'غير مصرح لك بتنفيذ هذا الإجراء.');
        }
        // *** نهاية التحقق ***

        // هات الدفعة المطلوبة
        $payment = Payment::findOrFail($id);
        // امسح القيد الأول
        AccountController::deletePaymentJournalEntry($payment->id, 'company');

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
        if (strtolower(Auth::user()->role) !== 'admin') {
            abort(403, 'غير مصرح لك بتنفيذ هذا الإجراء.');
        }
        // *** نهاية التحقق ***

        // هات الدفعة المطلوبة
        $payment = AgentPayment::findOrFail($id);
        $agentId = $payment->agent_id;

         // 1. حذف القيد المحاسبي المرتبط بهذه الدفعة
        AccountController::deletePaymentJournalEntry($payment->id, 'agent');

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
    /**
     * عرض صفحة مخطط العلاقات
     */
    public function networkGraph()
    {
        return view('reports.network_graph');
    }

    /**
     * جلب بيانات الشبكة للمخطط التفاعلي
     */
    public function getNetworkData(Request $request)
    {
        // الحصول على المعاملات من الطلب
        $limit = $request->input('limit', 50); // عدد الحجوزات المراد عرضها
        $agentId = $request->input('agent_id'); // تصفية حسب جهة الحجز
        $hotelId = $request->input('hotel_id'); // تصفية حسب الفندق
        $companyId = $request->input('company_id'); // تصفية حسب الشركة

        // قم بجلب آخر الحجوزات مع العلاقات
        $query = Booking::with(['hotel', 'agent', 'company'])
            ->latest('created_at');

        // تطبيق الفلاتر إذا كانت موجودة
        if ($agentId) {
            $query->where('agent_id', $agentId);
        }

        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $bookings = $query->take($limit)->get();

        // تجهيز بيانات المخطط
        $nodes = [];
        $links = [];
        $nodeIds = [];

        // إضافة عقد للشركات
        $companies = [];
        foreach ($bookings as $booking) {
            if ($booking->company && !isset($companies[$booking->company_id])) {
                $companies[$booking->company_id] = $booking->company;
            }
        }

        foreach ($companies as $company) {
            $nodeId = 'company_' . $company->id;
            if (!in_array($nodeId, $nodeIds)) {
                $nodes[] = [
                    'id' => $nodeId,
                    'name' => $company->name,
                    'type' => 'company',
                    'value' => 15, // حجم العقدة
                ];
                $nodeIds[] = $nodeId;
            }
        }

        // إضافة عقد لجهات الحجز
        $agents = [];
        foreach ($bookings as $booking) {
            if ($booking->agent && !isset($agents[$booking->agent_id])) {
                $agents[$booking->agent_id] = $booking->agent;
            }
        }

        foreach ($agents as $agent) {
            $nodeId = 'agent_' . $agent->id;
            if (!in_array($nodeId, $nodeIds)) {
                $nodes[] = [
                    'id' => $nodeId,
                    'name' => $agent->name,
                    'type' => 'agent',
                    'value' => 12, // حجم العقدة
                ];
                $nodeIds[] = $nodeId;
            }

            // إضافة رابط بين الشركة وجهة الحجز (إذا وجدت)
            foreach ($bookings as $booking) {
                if ($booking->agent_id == $agent->id && $booking->company) {
                    $links[] = [
                        'source' => 'company_' . $booking->company_id,
                        'target' => 'agent_' . $agent->id,
                        'value' => 2, // سمك الخط
                    ];
                }
            }
        }

        // إضافة عقد للفنادق
        $hotels = [];
        foreach ($bookings as $booking) {
            if ($booking->hotel && !isset($hotels[$booking->hotel_id])) {
                $hotels[$booking->hotel_id] = $booking->hotel;
            }
        }

        foreach ($hotels as $hotel) {
            $nodeId = 'hotel_' . $hotel->id;
            if (!in_array($nodeId, $nodeIds)) {
                $nodes[] = [
                    'id' => $nodeId,
                    'name' => $hotel->name,
                    'type' => 'hotel',
                    'value' => 10, // حجم العقدة
                ];
                $nodeIds[] = $nodeId;
            }

            // إضافة روابط بين جهات الحجز والفنادق
            foreach ($bookings as $booking) {
                if ($booking->hotel_id == $hotel->id && $booking->agent) {
                    $links[] = [
                        'source' => 'agent_' . $booking->agent_id,
                        'target' => 'hotel_' . $hotel->id,
                        'value' => 2, // سمك الخط
                    ];
                }
            }
        }

        // إضافة عقد للعملاء/الحجوزات
        foreach ($bookings as $index => $booking) {
            $nodeId = 'booking_' . $booking->id;
            if (!in_array($nodeId, $nodeIds)) {
                $nodes[] = [
                    'id' => $nodeId,
                    'name' => $booking->client_name ?: 'حجز #' . $booking->id,
                    'type' => 'booking',
                    'value' => 8, // حجم العقدة
                    'booking_id' => $booking->id,
                    'check_in' => $booking->check_in ? $booking->check_in->format('Y-m-d') : '',
                    'check_out' => $booking->check_out ? $booking->check_out->format('Y-m-d') : '',
                    'rooms' => $booking->rooms,
                ];
                $nodeIds[] = $nodeId;
            }

            // إضافة رابط بين الفندق والحجز
            if ($booking->hotel) {
                $links[] = [
                    'source' => 'hotel_' . $booking->hotel_id,
                    'target' => 'booking_' . $booking->id,
                    'value' => 1, // سمك الخط
                ];
            }
        }

        return response()->json([
            'nodes' => $nodes,
            'links' => $links
        ]);
    }

    /**
     * عرض صفحة إنشاء سند القبض
     */
    public function receiptVoucher()
    {
        return view('reports.receipt-voucher');
    }

    /**
     * إنشاء وتحميل سند القبض
     */
    public function generateReceiptVoucher(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'subject' => 'required|string|max:500',
            'date_arabic' => 'required|string|max:100',
            'date_english' => 'required|date',
            'payer_name' => 'required|string|max:200',
            'payment_method' => 'required|in:cash,check',
            'check_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'check_date' => 'nullable|date',
            'receiver_signature' => 'required|string|max:100',
            'accountant_signature' => 'required|string|max:100',
        ]);

        return response()->json([
            'success' => true,
            'data' => $validated
        ]);
    }
}
