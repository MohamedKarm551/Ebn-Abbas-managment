<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyPayment;
use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CompanyPaymentController extends Controller
{
    /**
     * عرض قائمة الشركات مع الإحصائيات المالية
     */
    public function index(Request $request)
    {
        $query = Company::with(['companyPayments', 'landTripBookings']);

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $companies = $query->get()->map(function ($company) {
            $totals = $company->getTotalsByCurrency();

            // دايمًا جهز كل العملات (حتى لو صفر)
            foreach (['SAR', 'KWD'] as $currency) {
                if (!isset($totals[$currency])) {
                    $totals[$currency] = ['due' => 0, 'paid' => 0, 'remaining' => 0];
                }
            }

            return [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'bookings_count' => $company->landTripBookings()->count(),
                'totals_by_currency' => $totals,
                'last_payment' => $company->companyPayments()->latest()->first(),
            ];
        });

        // حساب الإحصائيات العامة
        $totalStats = [
            'companies_count' => $companies->count(),
            'total_due_sar' => $companies->sum(fn($c) => $c['totals_by_currency']['SAR']['due'] ?? 0),
            'total_paid_sar' => $companies->sum(fn($c) => $c['totals_by_currency']['SAR']['paid'] ?? 0),
            'total_due_kwd' => $companies->sum(fn($c) => $c['totals_by_currency']['KWD']['due'] ?? 0),
            'total_paid_kwd' => $companies->sum(fn($c) => $c['totals_by_currency']['KWD']['paid'] ?? 0),
        ];

        return view('admin.company-payments.index', compact('companies', 'totalStats'));
    }

    public function show(Company $company)
    {
        $company->load(['companyPayments.employee', 'landTripBookings']);

        $totals = $company->getTotalsByCurrency();
        foreach (['SAR', 'KWD'] as $currency) {
            if (!isset($totals[$currency])) {
                $totals[$currency] = ['due' => 0, 'paid' => 0, 'remaining' => 0];
            }
        }

        $payments = $company->companyPayments()
            ->with('employee')
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        $recentBookings = $company->landTripBookings()
            ->with(['landTrip.agent', 'landTrip.hotel'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.company-payments.show', compact('company', 'totals', 'payments', 'recentBookings'));
    }

    public function create(Company $company)
    {
        $totals = $company->getTotalsByCurrency();
        foreach (['SAR', 'KWD'] as $currency) {
            if (!isset($totals[$currency])) {
                $totals[$currency] = ['due' => 0, 'paid' => 0, 'remaining' => 0];
            }
        }
        $employees = Employee::orderBy('name')->get();

        return view('admin.company-payments.create', compact('company', 'totals', 'employees'));
    }

    /**
     * حفظ دفعة جديدة
     */
    public function store(Request $request, Company $company)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'receipt_image_url' => 'nullable|url|max:500',
        ], [
            'amount.required' => 'المبلغ مطلوب',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
            'currency.required' => 'العملة مطلوبة',
            'currency.in' => 'العملة يجب أن تكون ريال سعودي أو دينار كويتي',
            'payment_date.required' => 'تاريخ الدفع مطلوب',
            'payment_date.before_or_equal' => 'تاريخ الدفع لا يمكن أن يكون في المستقبل',
            'receipt_image_url.url' => 'رابط الصورة غير صحيح',
        ]);

        // البحث عن الموظف المرتبط بالمستخدم الحالي
        $employee = Employee::where('user_id', Auth::id())->first();

        DB::beginTransaction();

        try {
            $payment = CompanyPayment::create([
                'company_id' => $company->id,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'],
                'receipt_image_url' => $validated['receipt_image_url'],
                'employee_id' => $employee?->id,
            ]);

            // إنشاء إشعار
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم تسجيل دفعة جديدة من شركة {$company->name} بمبلغ {$payment->amount} {$payment->currency}",
                'type' => 'دفعة جديدة',
            ]);

            DB::commit();

            return redirect()->route('admin.company-payments.show', $company)
                ->with('success', 'تم تسجيل الدفعة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تسجيل الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * عرض نموذج تعديل دفعة
     */
    public function edit(Company $company, CompanyPayment $payment)
    {
        // التأكد أن الدفعة تخص الشركة المحددة
        if ($payment->company_id !== $company->id) {
            abort(404);
        }

        $employees = Employee::orderBy('name')->get();

        return view('admin.company-payments.edit', compact('company', 'payment', 'employees'));
    }

    /**
     * تحديث دفعة موجودة
     */
    public function update(Request $request, Company $company, CompanyPayment $payment)
    {
        // التأكد أن الدفعة تخص الشركة المحددة
        if ($payment->company_id !== $company->id) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'receipt_image_url' => 'nullable|url|max:500',
        ]);

        $oldAmount = $payment->amount;
        $oldCurrency = $payment->currency;

        $payment->update($validated);

        // إنشاء إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "تم تعديل دفعة شركة {$company->name} من {$oldAmount} {$oldCurrency} إلى {$payment->amount} {$payment->currency}",
            'type' => 'تعديل دفعة',
        ]);

        return redirect()->route('admin.company-payments.show', $company)
            ->with('success', 'تم تحديث الدفعة بنجاح');
    }

    /**
     * حذف دفعة
     */
    public function destroy(Company $company, CompanyPayment $payment)
    {
        // التأكد أن الدفعة تخص الشركة المحددة
        if ($payment->company_id !== $company->id) {
            abort(404);
        }

        $paymentInfo = [
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'date' => $payment->payment_date->format('Y-m-d')
        ];

        $payment->delete();

        // إنشاء إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "تم حذف دفعة شركة {$company->name} بمبلغ {$paymentInfo['amount']} {$paymentInfo['currency']} بتاريخ {$paymentInfo['date']}",
            'type' => 'حذف دفعة',
        ]);

        return redirect()->route('admin.company-payments.show', $company)
            ->with('success', 'تم حذف الدفعة بنجاح');
    }
    /**
     * تطبيق خصم كدفعة سالبة (الطريقة البسيطة)
     */
    public function applyDiscount(Request $request, Company $company)
    {
        // 1. التحقق من صحة البيانات المدخلة
        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'reason' => 'nullable|string|max:500'
        ], [
            'discount_amount.required' => 'مبلغ الخصم مطلوب',
            'discount_amount.min' => 'مبلغ الخصم يجب أن يكون أكبر من صفر',
            'currency.required' => 'العملة مطلوبة',
            'currency.in' => 'العملة يجب أن تكون ريال سعودي أو دينار كويتي'
        ]);

        // 2. الحصول على المجاميع الحالية للشركة
        $totals = $company->getTotalsByCurrency();
        $currentTotals = $totals[$validated['currency']] ?? ['due' => 0, 'paid' => 0, 'remaining' => 0];

        // 3. التحقق من أن الخصم لا يتجاوز المبلغ المتبقي
        if ($validated['discount_amount'] > $currentTotals['remaining']) {
            return redirect()->back()
                ->with('error', "مبلغ الخصم ({$validated['discount_amount']} {$validated['currency']}) أكبر من المبلغ المتبقي ({$currentTotals['remaining']} {$validated['currency']})");
        }

        // 4. الحصول على بيانات الموظف الحالي
        $employee = Employee::where('user_id', Auth::id())->first();

        // 5. بدء معاملة قاعدة البيانات لضمان الأمان
        DB::beginTransaction();
        try {
            // 6. إنشاء دفعة بقيمة سالبة (هذا هو السر!)
            $discountPayment = CompanyPayment::create([
                'company_id' => $company->id,
                'amount' => -$validated['discount_amount'], // 🔥 قيمة سالبة للخصم
                'currency' => $validated['currency'],
                'payment_date' => now()->format('Y-m-d'),
                'notes' => 'خصم مطبق: ' . ($validated['reason'] ?: 'خصم'),
                'employee_id' => $employee?->id,
            ]);

            // 7. إنشاء إشعار للمدراء
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم تطبيق خصم {$validated['discount_amount']} {$validated['currency']} على شركة {$company->name}",
                'type' => 'خصم مطبق',
            ]);

            // 8. تأكيد المعاملة
            DB::commit();

            return redirect()->route('admin.company-payments.show', $company)
                ->with('success', "تم تطبيق خصم {$validated['discount_amount']} {$validated['currency']} بنجاح");
        } catch (\Exception $e) {
            // 9. في حالة حدوث خطأ، إلغاء المعاملة
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تطبيق الخصم: ' . $e->getMessage());
        }
    }

    /**
     * ✅ صفحة التقارير المالية الرئيسية
     */
    public function reports()
    {
        return view('admin.company-payments.reports');
    }

    /**
     * 📊 إرجاع بيانات التقارير كـ JSON
     */
    public function data(Request $request)
{
    try {
        Log::info('📊 بدء تحميل بيانات التقارير', $request->all());

        $period = $request->get('period', 'daily');
        $currency = $request->get('currency', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // 📅 تحديد نطاق التواريخ
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        Log::info('📅 نطاق التواريخ:', $dateRange);

        // 🔍 بناء الاستعلام الأساسي
        $query = CompanyPayment::with(['company', 'employee'])
            ->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']]);

        if ($currency !== 'all') {
            $query->where('currency', $currency);
        }

        $payments = $query->get();
        Log::info('💰 عدد المدفوعات المسترجعة: ' . $payments->count());

        // 📈 إعداد البيانات للاستجابة مع إضافة بيانات الربح
        $response = [
            'success' => true,
            'period' => $period,
            'currency' => $currency,
            'date_range' => [
                'start' => $dateRange['start']->format('Y-m-d'),
                'end' => $dateRange['end']->format('Y-m-d'),
            ],
            'total_payments' => $this->calculateTotalPayments($payments),
            'profit_data' => $this->calculateProfitData($currency), // ✅ إضافة بيانات الربح
            'chart_data' => $this->getChartData($payments, $period),
            'currency_distribution' => $this->getCurrencyDistribution($payments),
            'top_companies' => $this->getTopCompanies($payments),
            'comparison' => $this->getComparison($period, $dateRange, $currency),
            'collection_targets' => $this->getCollectionTargets(),
            'risk_analysis' => $this->getRiskAnalysis($payments)
        ];

        Log::info('✅ تم إعداد البيانات بنجاح');
        return response()->json($response);

    } catch (\Exception $e) {
        Log::error('❌ خطأ في تحميل تقارير مدفوعات الشركات: ' . $e->getMessage());
        Log::error('🔍 تفاصيل الخطأ: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => 'حدث خطأ في تحميل البيانات',
            'message' => config('app.debug') ? $e->getMessage() : 'خطأ داخلي في الخادم',
            'debug_info' => config('app.debug') ? [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ] : null
        ], 500);
    }
}

    /**
     * 📅 تحديد نطاق التواريخ حسب الفترة
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        if ($period === 'custom' && $startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay()
            ];
        }

        $now = Carbon::now();

        switch ($period) {
            case 'weekly':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'monthly':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            default: // daily
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
        }
    }

  /**
 * 💰 حساب إجمالي المدفوعات والأرباح حسب العملة
 */
private function calculateTotalPayments($payments)
{
    $totals = [];
    
    foreach ($payments as $payment) {
        $currency = $payment->currency ?? 'KWD';
        
        if (!isset($totals[$currency])) {
            $totals[$currency] = [
                'total' => 0,
                'count' => 0
            ];
        }
        
        $totals[$currency]['total'] += floatval($payment->amount);
        $totals[$currency]['count']++;
    }

    // إضافة عملات فارغة إذا لم توجد بيانات
    if (empty($totals)) {
        $totals = [
            'KWD' => ['total' => 0, 'count' => 0],
            'SAR' => ['total' => 0, 'count' => 0]
        ];
    }

    return $totals;
}

/**
 * 💹 حساب الأرباح الفعلية والمتوقعة
 */
private function calculateProfitData($currency = 'all')
{
    // جلب جميع الحجوزات النشطة للشركات
    $bookingsQuery = \App\Models\LandTripBooking::with(['company', 'landTrip']);
    
    if ($currency !== 'all') {
        $bookingsQuery->where('currency', $currency);
    }
    
    $bookings = $bookingsQuery->get();
    
    $profitData = [];
    
    foreach ($bookings as $booking) {
        $bookingCurrency = $booking->currency ?? 'KWD';
        
        if (!isset($profitData[$bookingCurrency])) {
            $profitData[$bookingCurrency] = [
                'total_due_from_companies' => 0,  // إجمالي المستحق من الشركات
                'total_due_to_agents' => 0,       // إجمالي المستحق للوكلاء
                'total_paid_by_companies' => 0,   // إجمالي المدفوع من الشركات
                'actual_profit' => 0,             // الربح الفعلي الحالي
                'potential_profit' => 0,          // الربح المتوقع لو تم التحصيل بالكامل
                'profit_percentage' => 0,         // نسبة الربح
                'collection_rate' => 0            // معدل التحصيل
            ];
        }
        
        // حساب المستحق من الشركات والمستحق للوكلاء
        $profitData[$bookingCurrency]['total_due_from_companies'] += floatval($booking->amount_due_from_company);
        $profitData[$bookingCurrency]['total_due_to_agents'] += floatval($booking->amount_due_to_agent);
    }
    
    // حساب المدفوعات الفعلية
    $paymentsQuery = \App\Models\CompanyPayment::selectRaw('currency, SUM(amount) as total_paid')
        ->groupBy('currency');
    
    if ($currency !== 'all') {
        $paymentsQuery->where('currency', $currency);
    }
    
    $payments = $paymentsQuery->get();
    
    foreach ($payments as $payment) {
        $paymentCurrency = $payment->currency ?? 'KWD';
        
        if (!isset($profitData[$paymentCurrency])) {
            $profitData[$paymentCurrency] = [
                'total_due_from_companies' => 0,
                'total_due_to_agents' => 0,
                'total_paid_by_companies' => 0,
                'actual_profit' => 0,
                'potential_profit' => 0,
                'profit_percentage' => 0,
                'collection_rate' => 0
            ];
        }
        
        $profitData[$paymentCurrency]['total_paid_by_companies'] = floatval($payment->total_paid);
    }
    
    // حساب الأرباح والنسب
    foreach ($profitData as $curr => &$data) {
        // الربح المتوقع = إجمالي المستحق من الشركات - إجمالي المستحق للوكلاء
        $data['potential_profit'] = $data['total_due_from_companies'] - $data['total_due_to_agents'];
        
        // الربح الفعلي = المدفوع من الشركات - المستحق للوكلاء (بنسبة التحصيل)
        if ($data['total_due_from_companies'] > 0) {
            $collectionRate = $data['total_paid_by_companies'] / $data['total_due_from_companies'];
            $data['collection_rate'] = $collectionRate * 100;
            $data['actual_profit'] = $data['total_paid_by_companies'] - ($data['total_due_to_agents'] * $collectionRate);
        }
        
        // نسبة الربح
        if ($data['total_due_from_companies'] > 0) {
            $data['profit_percentage'] = ($data['potential_profit'] / $data['total_due_from_companies']) * 100;
        }
    }
    
    return $profitData;
}
   

    /**
     * 📊 بيانات الرسم البياني
     */
    private function getChartData($payments, $period)
    {
        $chartData = [];

        // تجميع البيانات حسب التاريخ
        $groupedPayments = $payments->groupBy(function ($payment) use ($period) {
            $date = Carbon::parse($payment->payment_date);

            switch ($period) {
                case 'weekly':
                    return $date->format('Y-W'); // أسبوع السنة
                case 'monthly':
                    return $date->format('Y-m'); // شهر السنة
                default:
                    return $date->format('Y-m-d'); // يوم
            }
        });

        foreach ($groupedPayments as $dateKey => $dayPayments) {
            $chartData[$dateKey] = [];

            $currencyGroups = $dayPayments->groupBy('currency');

            foreach ($currencyGroups as $currency => $currencyPayments) {
                $chartData[$dateKey][] = [
                    'currency' => $currency ?? 'KWD',
                    'total_amount' => $currencyPayments->sum('amount'),
                    'count' => $currencyPayments->count()
                ];
            }
        }

        // إضافة بيانات فارغة إذا لم توجد بيانات
        if (empty($chartData)) {
            $today = Carbon::now()->format('Y-m-d');
            $chartData[$today] = [
                ['currency' => 'KWD', 'total_amount' => 0, 'count' => 0],
                ['currency' => 'SAR', 'total_amount' => 0, 'count' => 0]
            ];
        }

        return $chartData;
    }

    /**
     * 🥧 توزيع العملات
     */
    private function getCurrencyDistribution($payments)
    {
        $distribution = [];

        $currencyGroups = $payments->groupBy('currency');

        foreach ($currencyGroups as $currency => $currencyPayments) {
            $distribution[$currency ?? 'KWD'] = [
                'total_amount' => $currencyPayments->sum('amount'),
                'payment_count' => $currencyPayments->count(),
                'avg_amount' => $currencyPayments->avg('amount'),
                'min_amount' => $currencyPayments->min('amount'),
                'max_amount' => $currencyPayments->max('amount')
            ];
        }

        // إضافة عملات فارغة إذا لم توجد بيانات
        if (empty($distribution)) {
            $distribution = [
                'KWD' => [
                    'total_amount' => 0,
                    'payment_count' => 0,
                    'avg_amount' => 0,
                    'min_amount' => 0,
                    'max_amount' => 0
                ]
            ];
        }

        return $distribution;
    }

    /**
     * 🏆 أفضل الشركات دفعاً
     */
    private function getTopCompanies($payments, $limit = 5)
    {
        if ($payments->isEmpty()) {
            return collect([]);
        }

        // تجميع المدفوعات حسب الشركة
        $companiesData = $payments->groupBy('company_id')
            ->map(function ($companyPayments) {
                $firstPayment = $companyPayments->first();
                $company = $firstPayment->company;

                return [
                    'id' => $company->id ?? 0,
                    'name' => $company->name ?? "شركة #{$firstPayment->company_id}",
                    'total_paid' => floatval($companyPayments->sum('amount')),
                    'payment_count' => $companyPayments->count(),
                    'avg_payment' => floatval($companyPayments->avg('amount')),
                    'last_payment_date' => $companyPayments->max('payment_date')
                ];
            })
            ->filter(function ($company) {
                return $company['total_paid'] > 0; // فقط الشركات التي دفعت
            })
            ->sortByDesc('total_paid')
            ->take($limit)
            ->values();

        return $companiesData;
    }

    /**
     * ⚖️ مقارنة بالفترة السابقة
     */
    private function getComparison($period, $dateRange, $currency)
    {
        // حساب الفترة السابقة
        $previousRange = $this->getPreviousDateRange($period, $dateRange);

        // بناء الاستعلام للفترة الحالية
        $currentQuery = CompanyPayment::whereBetween('payment_date', [$dateRange['start'], $dateRange['end']]);
        if ($currency !== 'all') {
            $currentQuery->where('currency', $currency);
        }

        // بناء الاستعلام للفترة السابقة
        $previousQuery = CompanyPayment::whereBetween('payment_date', [$previousRange['start'], $previousRange['end']]);
        if ($currency !== 'all') {
            $previousQuery->where('currency', $currency);
        }

        $currentTotal = $currentQuery->sum('amount') ?? 0;
        $currentCount = $currentQuery->count() ?? 0;
        $currentAvg = $currentCount > 0 ? $currentTotal / $currentCount : 0;

        $previousTotal = $previousQuery->sum('amount') ?? 0;
        $previousCount = $previousQuery->count() ?? 0;
        $previousAvg = $previousCount > 0 ? $previousTotal / $previousCount : 0;

        $changePercent = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : 0;

        return [
            'current' => [
                'total' => $currentTotal,
                'count' => $currentCount,
                'average' => $currentAvg
            ],
            'previous' => [
                'total' => $previousTotal,
                'count' => $previousCount,
                'average' => $previousAvg
            ],
            'change_percent' => round($changePercent, 1)
        ];
    }

    /**
     * 📅 حساب الفترة السابقة
     */
    private function getPreviousDateRange($period, $currentRange)
    {
        $start = Carbon::parse($currentRange['start']);
        $end = Carbon::parse($currentRange['end']);

        $diff = $start->diffInDays($end) + 1;

        return [
            'start' => $start->copy()->subDays($diff),
            'end' => $start->copy()->subDay()
        ];
    }

    /**
     * 🎯 أهداف المحصلات الشهرية
     */
    private function getCollectionTargets()
    {
        $currentMonth = Carbon::now()->format('Y-m');

        // جلب المحصلات الفعلية للشهر الحالي
        $monthlyCollections = CompanyPayment::whereRaw("DATE_FORMAT(payment_date, '%Y-%m') = ?", [$currentMonth])
            ->selectRaw('currency, SUM(amount) as collected, COUNT(*) as payment_count')
            ->groupBy('currency')
            ->get();

        // أهداف افتراضية (يمكن ربطها بقاعدة البيانات لاحقاً)
        $monthlyTargets = [
            'KWD' => 500, // 500 دينار شهرياً (هدف واقعي)
            'SAR' => 1000 // 1000 ريال شهرياً (هدف واقعي)
        ];

        $targets = [];
        foreach ($monthlyTargets as $currency => $target) {
            $collectionData = $monthlyCollections->where('currency', $currency)->first();
            $collected = $collectionData ? floatval($collectionData->collected) : 0;
            $paymentCount = $collectionData ? intval($collectionData->payment_count) : 0;

            $percentage = $target > 0 ? ($collected / $target) * 100 : 0;

            $targets[$currency] = [
                'target' => $target,
                'collected' => $collected,
                'percentage' => round($percentage, 1),
                'remaining' => $target - $collected,
                'payment_count' => $paymentCount,
            ];
        }

        return $targets;
    }

    /**
     * ⚠️ تحليل المخاطر والتنبيهات
     */
    private function getRiskAnalysis($payments)
{
    $risks = [];

    // 1. فحص انخفاض المدفوعات في الأسبوع الماضي
    $lastWeek = Carbon::now()->subDays(7);
    $recentPayments = $payments->filter(function($payment) use ($lastWeek) {
        return Carbon::parse($payment->payment_date)->gte($lastWeek);
    });

    if ($recentPayments->count() < 2) {
        $risks[] = [
            'type' => 'low_payments',
            'level' => 'warning',
            'title' => 'انخفاض في المدفوعات الأخيرة',
            'description' => "عدد المدفوعات في آخر 7 أيام: {$recentPayments->count()} فقط"
        ];
    }

    // 2. فحص المدفوعات السالبة (الخصومات)
    $negativePayments = $payments->filter(function($payment) {
        return floatval($payment->amount) < 0;
    });

    if ($negativePayments->count() > 0) {
        $totalDiscounts = abs($negativePayments->sum('amount'));
        $risks[] = [
            'type' => 'discounts_applied',
            'level' => 'info',
            'title' => 'خصومات مطبقة',
            'description' => "تم تطبيق {$negativePayments->count()} خصم بإجمالي {$totalDiscounts}"
        ];
    }

    // 3. فحص تركز المدفوعات في شركة واحدة
    if ($payments->count() > 1) {
        $topCompanies = $this->getTopCompanies($payments, 1);
        if ($topCompanies->count() > 0) {
            $topCompany = $topCompanies->first();
            $totalPayments = abs($payments->sum('amount'));
            
            if ($totalPayments > 0) {
                $concentration = ($topCompany['total_paid'] / $totalPayments) * 100;
                
                if ($concentration > 50) {
                    $risks[] = [
                        'type' => 'payment_concentration',
                        'level' => $concentration > 70 ? 'warning' : 'info',
                        'title' => 'تركز في المدفوعات',
                        'description' => sprintf(
                            "%.1f%% من المدفوعات تأتي من %s", 
                            $concentration, 
                            $topCompany['name']
                        )
                    ];
                }
            }
        }
    }

    // 4. تحليل الشركات النشطة
    $activeCompanies = $payments->groupBy('company_id')->count();
    if ($activeCompanies < 3) {
        $risks[] = [
            'type' => 'few_active_companies',
            'level' => 'info',
            'title' => 'عدد قليل من الشركات النشطة',
            'description' => "فقط {$activeCompanies} شركة قامت بدفعات في هذه الفترة"
        ];
    }

    return $risks;
}
}
