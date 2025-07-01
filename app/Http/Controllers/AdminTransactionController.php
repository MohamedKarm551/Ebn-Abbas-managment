<?php


namespace App\Http\Controllers;

use App\Models\AdminTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminTransactionController extends Controller
{
    // عرض صفحة المعاملات المالية
    public function index(Request $request)
    {
        $adminId = Auth::id();

        // بناء الاستعلام الأساسي
        $query = AdminTransaction::where('admin_id', $adminId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // تطبيق الفلاتر المتقدمة
        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->category . '%');
        }

        if ($request->filled('from_to')) {
            $query->where('from_to', 'like', '%' . $request->from_to . '%');
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // الحصول على المعاملات مع التقسيم إلى صفحات
        $transactions = $query->paginate(15)->appends($request->query());

        // حساب الإجماليات حسب العملة
        $totals = AdminTransaction::getTotalsByCurrency(
            $adminId,
            $request->start_date,
            $request->end_date
        );

        // إحصائيات سريعة
        $summary = [
            'total_deposits' => AdminTransaction::where('admin_id', $adminId)
                ->where('type', 'deposit')->count(),
            'total_withdrawals' => AdminTransaction::where('admin_id', $adminId)
                ->where('type', 'withdrawal')->count(),
            'total_transfers' => AdminTransaction::where('admin_id', $adminId)
                ->where('type', 'transfer')->count(), // ✅ إضافة التحويلات
            'this_month' => AdminTransaction::where('admin_id', $adminId)
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->count(),
            'avg_transaction' => AdminTransaction::where('admin_id', $adminId)
                ->avg('amount') ?? 0
        ];

        // الحصول على العملات المستخدمة
        $currencies = AdminTransaction::where('admin_id', $adminId)
            ->distinct()
            ->pluck('currency')
            ->toArray();

        // الحصول على الفئات المستخدمة
        $categories = AdminTransaction::where('admin_id', $adminId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        return view('admin.transactions.index', compact(
            'transactions',
            'totals',
            'currencies',
            'categories',
            'summary'
        ));
    }

    // إضافة معاملة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'nullable|date',
            'from_to' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'type' => 'nullable|in:deposit,withdrawal,transfer,other',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'link_or_image' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120' // 5MB max
        ]);

        $data = $request->all();
        $data['admin_id'] = Auth::id();
        $data['transaction_date'] = $data['transaction_date'] ?? now()->format('Y-m-d');

        // رفع الملف إذا وجد
        if ($request->hasFile('link_or_image')) {
            $file = $request->file('link_or_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('admin_transactions', $filename, 'public');
            $data['link_or_image'] = $path;

            // --- نسخ الملف يدويًا إلى public/storage/admin_transactions ---
            $publicPath = public_path('storage/admin_transactions/' . $filename);
            if (!file_exists(dirname($publicPath))) {
                mkdir(dirname($publicPath), 0775, true);
            }
            copy($file->getRealPath(), $publicPath);
        }

        AdminTransaction::create($data);

        return redirect()->route('admin.transactions.index')
            ->with('success', 'تم إضافة المعاملة بنجاح');
    }

    // تعديل معاملة
    public function update(Request $request, AdminTransaction $transaction)
    {
        // التأكد من أن المعاملة تخص الأدمن الحالي
        if ($transaction->admin_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بتعديل هذه المعاملة');
        }

        $request->validate([
            'transaction_date' => 'nullable|date',
            'from_to' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'type' => 'nullable|in:deposit,withdrawal,transfer,other',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'link_or_image' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120'
        ]);

        $data = $request->all();

        // رفع ملف جديد إذا وجد
        if ($request->hasFile('link_or_image')) {
            // حذف الملف القديم
            if ($transaction->link_or_image && Storage::disk('public')->exists($transaction->link_or_image)) {
                Storage::disk('public')->delete($transaction->link_or_image);
            }

            $file = $request->file('link_or_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('admin_transactions', $filename, 'public');
            $data['link_or_image'] = $path;
        }

        $transaction->update($data);

        return redirect()->route('admin.transactions.index')
            ->with('success', 'تم تعديل المعاملة بنجاح');
    }

    // حذف معاملة
    public function destroy(AdminTransaction $transaction)
    {
        // التأكد من أن المعاملة تخص الأدمن الحالي
        if ($transaction->admin_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بحذف هذه المعاملة');
        }

        // حذف الملف المرفق إذا وجد
        if ($transaction->link_or_image && Storage::disk('public')->exists($transaction->link_or_image)) {
            Storage::disk('public')->delete($transaction->link_or_image);
        }

        $transaction->delete();

        return redirect()->route('admin.transactions.index')
            ->with('success', 'تم حذف المعاملة بنجاح');
    }

    // API لجلب أسعار الصرف محسنة
    public function getExchangeRates(Request $request)
    {
        try {
            // التحقق من صحة البيانات المرسلة
            $request->validate([
                'from' => 'required|string|max:3',
                'to' => 'required|string|max:3',
                'amount' => 'required|numeric|min:0.01'
            ]);

            $fromCurrency = strtoupper($request->get('from'));
            $toCurrency = strtoupper($request->get('to'));
            $amount = floatval($request->get('amount'));

            // التحقق من أن العملتين مختلفتان
            if ($fromCurrency === $toCurrency) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن التحويل من نفس العملة إلى نفسها'
                ], 400);
            }

            // تسجيل المحاولة للتصحيح
            Log::info('Exchange rate request started', [
                'from' => $fromCurrency,
                'to' => $toCurrency,
                'amount' => $amount,
                'user_id' => Auth::id()
            ]);

            // استخدام الأسعار المحلية مباشرة (أكثر موثوقية)
            $rateData = $this->getUpdatedRates($fromCurrency, $toCurrency);

            if ($rateData['rate'] > 0) {
                $convertedAmount = $amount * $rateData['rate'];

                Log::info('Exchange rate calculation successful', [
                    'rate' => $rateData['rate'],
                    'converted_amount' => $convertedAmount,
                    'source' => 'local'
                ]);

                return response()->json([
                    'success' => true,
                    'rate' => round($rateData['rate'], 6),
                    'converted_amount' => round($convertedAmount, 2),
                    'from' => $fromCurrency,
                    'to' => $toCurrency,
                    'original_amount' => $amount,
                    'note' => $rateData['note'],
                    'source' => 'local',
                    'updated_date' => $rateData['updated']
                ]);
            }

            // إذا لم تكن العملة مدعومة
            return response()->json([
                'success' => false,
                'message' => 'عذراً، تحويل هذه العملة غير مدعوم حالياً. العملات المدعومة: SAR, KWD, EGP, USD, EUR'
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Exchange rate calculation failed', [
                'error' => $e->getMessage(),
                'from' => $fromCurrency ?? 'unknown',
                'to' => $toCurrency ?? 'unknown',
                'amount' => $amount ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب التحويل. يرجى المحاولة مرة أخرى.'
            ], 500);
        }
    }

    // أسعار صرف محدثة ودقيقة - ديسمبر 2024
    private function getUpdatedRates($from, $to)
    {
        // أسعار محدثة وموثوقة
        $rates = [
            'SAR' => [
                'KWD' => 0.081,   // 1 ريال سعودي = 0.081 دينار كويتي
                'EGP' => 13.5,    // 1 ريال سعودي = 13.5 جنيه مصري
                'USD' => 0.267,   // 1 ريال سعودي = 0.267 دولار أمريكي
                'EUR' => 0.242    // 1 ريال سعودي = 0.242 يورو
            ],
            'KWD' => [
                'SAR' => 12.35,   // 1 دينار كويتي = 12.35 ريال سعودي
                'EGP' => 166.7,   // 1 دينار كويتي = 166.7 جنيه مصري
                'USD' => 3.29,    // 1 دينار كويتي = 3.29 دولار أمريكي
                'EUR' => 2.99     // 1 دينار كويتي = 2.99 يورو
            ],
            'EGP' => [
                'SAR' => 0.074,   // 1 جنيه مصري = 0.074 ريال سعودي
                'KWD' => 0.006,   // 1 جنيه مصري = 0.006 دينار كويتي
                'USD' => 0.020,   // 1 جنيه مصري = 0.020 دولار أمريكي
                'EUR' => 0.018    // 1 جنيه مصري = 0.018 يورو
            ],
            'USD' => [
                'SAR' => 3.75,    // 1 دولار = 3.75 ريال سعودي (سعر ثابت)
                'KWD' => 0.304,   // 1 دولار = 0.304 دينار كويتي
                'EGP' => 50.5,    // 1 دولار = 50.5 جنيه مصري
                'EUR' => 0.91     // 1 دولار = 0.91 يورو
            ],
            'EUR' => [
                'SAR' => 4.12,    // 1 يورو = 4.12 ريال سعودي
                'KWD' => 0.334,   // 1 يورو = 0.334 دينار كويتي
                'EGP' => 55.4,    // 1 يورو = 55.4 جنيه مصري
                'USD' => 1.10     // 1 يورو = 1.10 دولار أمريكي
            ]
        ];

        $rate = $rates[$from][$to] ?? 0;

        $currencyNames = [
            'SAR' => 'الريال السعودي',
            'KWD' => 'الدينار الكويتي',
            'EGP' => 'الجنيه المصري',
            'USD' => 'الدولار الأمريكي',
            'EUR' => 'اليورو'
        ];

        $note = $rate > 0 ?
            "سعر تقريبي للتحويل من {$currencyNames[$from]} إلى {$currencyNames[$to]} - محدث بتاريخ 19 ديسمبر 2024" :
            "تحويل غير مدعوم";

        return [
            'rate' => $rate,
            'source' => 'local',
            'updated' => '2024-12-19',
            'note' => $note
        ];
    }

    // إضافة دالة create
    public function create()
    {
        return view('admin.transactions.create');
    }

    // إضافة دالة edit
    public function edit(AdminTransaction $transaction)
    {
        // التأكد من أن المعاملة تخص الأدمن الحالي
        if ($transaction->admin_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بتعديل هذه المعاملة');
        }

        return view('admin.transactions.edit', compact('transaction'));
    }

    // إضافة دالة للتصدير
    public function export(Request $request)
    {
        $adminId = Auth::id();

        // التحقق من نوع التصدير المطلوب
        $exportType = $request->get('export_type', 'current');
        $format = $request->get('format', 'excel');

        $query = AdminTransaction::where('admin_id', $adminId);

        // تطبيق الفلاتر إذا كانت موجودة
        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->category . '%');
        }

        if ($request->filled('from_to')) {
            $query->where('from_to', 'like', '%' . $request->from_to . '%');
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // ترتيب البيانات
        $query->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc');

        // جلب البيانات
        $transactions = $query->get();

        // إذا كان المطلوب تنسيق JSON للـ JavaScript
        if ($format === 'json') {
            // تحضير البيانات للتصدير
            $exportData = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
                    'from_to' => $transaction->from_to,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'type' => $transaction->type,
                    'type_arabic' => $transaction->type_arabic,
                    'category' => $transaction->category,
                    'notes' => $transaction->notes,
                    'has_attachment' => !empty($transaction->link_or_image),
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                ];
            });

            // حساب الإحصائيات
            $summary = [
                'total_transactions' => $transactions->count(),
                'total_deposits' => $transactions->where('type', 'deposit')->sum('amount'),
                'total_withdrawals' => $transactions->where('type', 'withdrawal')->sum('amount'),
                'total_transfers' => $transactions->where('type', 'transfer')->sum('amount'), // ✅ إضافة التحويلات
                'net_balance' => $transactions->where('type', 'deposit')->sum('amount') -
                    $transactions->where('type', 'withdrawal')->sum('amount') -
                    $transactions->where('type', 'transfer')->sum('amount'), // ✅ طرح التحويلات
                'this_month' => $transactions->filter(function ($t) {
                    return $t->transaction_date->isCurrentMonth();
                })->count(),
                'avg_transaction' => $transactions->count() > 0 ? $transactions->avg('amount') : 0,
                'currencies_used' => $transactions->pluck('currency')->unique()->values()->toArray(),
            ];

            return response()->json([
                'success' => true,
                'transactions' => $exportData,
                'summary' => $summary,
                'total_count' => $transactions->count()
            ]);
        }

        // للتصدير المباشر (Excel)
        return redirect()->back()->with('info', 'ميزة التصدير المباشر ستكون متاحة قريباً');
    }

    // إضافة دالة show
    public function show(AdminTransaction $transaction)
    {
        // التأكد من أن المعاملة تخص الأدمن الحالي
        if ($transaction->admin_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بعرض هذه المعاملة');
        }

        return view('admin.transactions.show', compact('transaction'));
    }

    // تطوير دالة التقرير الشهري
    public function monthlyReport(Request $request)
    {
        $adminId = Auth::id();

        // الحصول على الشهر المطلوب أو الشهر الحالي
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        // جلب جميع المعاملات للشهر المحدد
        $transactions = AdminTransaction::where('admin_id', $adminId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();

        // إحصائيات أساسية
        $totalTransactions = $transactions->count();
        $totalDeposits = $transactions->where('type', 'deposit')->sum('amount');
        $totalWithdrawals = $transactions->where('type', 'withdrawal')->sum('amount');
        $totalTransfers = $transactions->where('type', 'transfer')->sum('amount');
        $netBalance = $totalDeposits - $totalWithdrawals;

        // تحليل يومي محسن
        $dailyStats = [];
        $runningBalance = 0;
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dayTransactions = $transactions->filter(function ($t) use ($currentDate) {
                return $t->transaction_date->format('Y-m-d') === $currentDate->format('Y-m-d');
            });

            $dayDeposits = $dayTransactions->where('type', 'deposit')->sum('amount');
            $dayWithdrawals = $dayTransactions->where('type', 'withdrawal')->sum('amount');
            $dayTransfers = $dayTransactions->where('type', 'transfer')->sum('amount');
            // ✅ الحساب الصحيح: التحويلات تُطرح من الرصيد
            $dayNet = $dayDeposits - $dayWithdrawals - $dayTransfers;


            $runningBalance += $dayNet;

            $dailyStats[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('l'),
                'day_arabic' => $this->getDayNameArabic($currentDate->format('l')),
                'deposits' => (float)$dayDeposits,
                'withdrawals' => (float)$dayWithdrawals,
                'transfers' => (float)$dayTransfers, // ✅ إضافة التحويلات
                'net' => (float)$dayNet,
                'running_balance' => (float)$runningBalance,
                'transaction_count' => $dayTransactions->count()
            ];

            $currentDate->addDay();
        }

        // تحليل حسب العملة محسن
        $currencyStats = [];
        if ($transactions->count() > 0) {
            $currencyGroups = $transactions->groupBy('currency');
            foreach ($currencyGroups as $currency => $currencyTransactions) {
                if (!$currency) $currency = 'SAR'; // قيمة افتراضية

                $deposits = $currencyTransactions->where('type', 'deposit')->sum('amount');
                $withdrawals = $currencyTransactions->where('type', 'withdrawal')->sum('amount');
                $transfers = $currencyTransactions->where('type', 'transfer')->sum('amount');

                $currencyStats[$currency] = [
                    'currency' => $currency,
                    'deposits' => (float)$deposits,
                    'withdrawals' => (float)$withdrawals,
                    'transfers' => (float)$transfers,
                    'net' => (float)($deposits - $withdrawals),
                    'count' => $currencyTransactions->count(),
                    'symbol' => $this->getCurrencySymbol($currency)
                ];
            }
        }

        // تحليل حسب التصنيف محسن
        $categoryStats = [];
        if ($transactions->count() > 0) {
            $categoryGroups = $transactions->groupBy(function ($transaction) {
                return $transaction->category ?: 'غير محدد';
            });

            foreach ($categoryGroups as $category => $categoryTransactions) {
                $deposits = $categoryTransactions->where('type', 'deposit')->sum('amount');
                $withdrawals = $categoryTransactions->where('type', 'withdrawal')->sum('amount');
                $transfers = $categoryTransactions->where('type', 'transfer')->sum('amount');

                $categoryStats[$category] = [
                    'category' => $category,
                    'deposits' => (float)$deposits,
                    'withdrawals' => (float)$withdrawals,
                    'transfers' => (float)$transfers,
                    'net' => (float)($deposits - $withdrawals),
                    'count' => $categoryTransactions->count()
                ];
            }

            // ترتيب حسب العدد
            $categoryStats = collect($categoryStats)->sortByDesc('count')->toArray();
        }

        // تحليل أسبوعي
        $weeklyStats = $this->getWeeklyStats($transactions, $startDate, $endDate);

        // تحليل الاتجاهات
        $trends = $this->calculateTrends($dailyStats);

        // مقارنة مع الشهر السابق
        $previousMonth = $startDate->copy()->subMonth();
        $comparison = $this->getMonthComparison($adminId, $previousMonth, $startDate);

        // أهم الإحصائيات
        $keyMetrics = [
            'largest_deposit' => $transactions->where('type', 'deposit')->max('amount') ?? 0,
            'largest_withdrawal' => $transactions->where('type', 'withdrawal')->max('amount') ?? 0,
            'average_transaction' => $transactions->avg('amount') ?? 0,
            'most_active_day' => $this->getMostActiveDay($dailyStats),
            'best_balance_day' => $this->getBestBalanceDay($dailyStats),
            'worst_balance_day' => $this->getWorstBalanceDay($dailyStats)
        ];

        // تسجيل للتصحيح
        Log::info('Monthly Report Data Debug', [
            'daily_stats_count' => count($dailyStats),
            'currency_stats_count' => count($currencyStats),
            'category_stats_count' => count($categoryStats),
            'daily_stats_sample' => array_slice($dailyStats, 0, 3),
            'currency_stats_sample' => array_slice($currencyStats, 0, 2),
            'category_stats_sample' => array_slice($categoryStats, 0, 2)
        ]);

        return view('admin.transactions.reports.monthly', compact(
            'month',
            'startDate',
            'endDate',
            'transactions',
            'totalTransactions',
            'totalDeposits',
            'totalWithdrawals',
            'totalTransfers',
            'netBalance',
            'dailyStats',
            'weeklyStats',
            'currencyStats',
            'categoryStats',
            'trends',
            'comparison',
            'keyMetrics'
        ));
    }

    // دوال مساعدة للتحليل
    private function getDayNameArabic($dayName)
    {
        $days = [
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
            'Sunday' => 'الأحد'
        ];

        return $days[$dayName] ?? $dayName;
    }

    private function getWeeklyStats($transactions, $startDate, $endDate)
    {
        $weeks = [];
        $currentWeekStart = $startDate->copy()->startOfWeek();
        $weekNumber = 1;

        while ($currentWeekStart <= $endDate) {
            $weekEnd = $currentWeekStart->copy()->endOfWeek();
            if ($weekEnd > $endDate) {
                $weekEnd = $endDate->copy();
            }

            $weekTransactions = $transactions->filter(function ($t) use ($currentWeekStart, $weekEnd) {
                return $t->transaction_date >= $currentWeekStart && $t->transaction_date <= $weekEnd;
            });

            $deposits = $weekTransactions->where('type', 'deposit')->sum('amount');
            $withdrawals = $weekTransactions->where('type', 'withdrawal')->sum('amount');
            $transfers = $weekTransactions->where('type', 'transfer')->sum('amount');

            $weeks[] = [
                'week_number' => $weekNumber,
                'week_start' => $currentWeekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'deposits' => (float)$deposits,
                'withdrawals' => (float)$withdrawals,
                'transfers' => (float)$transfers,
                'net' => (float)($deposits - $withdrawals - $transfers),
                'count' => $weekTransactions->count()
            ];

            $currentWeekStart->addWeek();
            $weekNumber++;
        }

        return $weeks;
    }

    private function getCurrencySymbol($currency)
    {
        $symbols = [
            'SAR' => 'ر.س',
            'KWD' => 'د.ك',
            'EGP' => 'ج.م',
            'USD' => '$',
            'EUR' => '€'
        ];

        return $symbols[$currency] ?? $currency;
    }

    private function calculateTrends($dailyStats)
    {
        $balances = array_column($dailyStats, 'running_balance');
        $count = count($balances);

        if ($count < 2) {
            return ['trend' => 'stable', 'change' => 0];
        }

        $start = $balances[0];
        $end = end($balances);
        $change = $end - $start;
        $changePercent = $start != 0 ? ($change / abs($start)) * 100 : 0;

        return [
            'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            'change' => $change,
            'change_percent' => round($changePercent, 2),
            'volatility' => $this->calculateVolatility($balances)
        ];
    }

    private function calculateVolatility($balances)
    {
        if (count($balances) < 2) return 0;

        $mean = array_sum($balances) / count($balances);
        $variance = array_sum(array_map(function ($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $balances)) / count($balances);

        return round(sqrt($variance), 2);
    }

    private function getMonthComparison($adminId, $previousMonth, $currentMonth)
    {
        $prevStart = $previousMonth->copy()->startOfMonth();
        $prevEnd = $previousMonth->copy()->endOfMonth();

        $prevTransactions = AdminTransaction::where('admin_id', $adminId)
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->get();

        $prevDeposits = $prevTransactions->where('type', 'deposit')->sum('amount');
        $prevWithdrawals = $prevTransactions->where('type', 'withdrawal')->sum('amount');
        $prevTransfers = $prevTransactions->where('type', 'transfer')->sum('amount');
        $prevNet = $prevDeposits - $prevWithdrawals - $prevTransfers;

        return [
            'previous_deposits' => $prevDeposits,
            'previous_withdrawals' => $prevWithdrawals,
            'previous_transfers' => $prevTransfers,
            'previous_net' => $prevNet,
            'previous_count' => $prevTransactions->count()
        ];
    }

    private function getMostActiveDay($dailyStats)
    {
        return collect($dailyStats)->sortByDesc('transaction_count')->first();
    }

    private function getBestBalanceDay($dailyStats)
    {
        return collect($dailyStats)->sortByDesc('running_balance')->first();
    }

    private function getWorstBalanceDay($dailyStats)
    {
        return collect($dailyStats)->sortBy('running_balance')->first();
    }
}
