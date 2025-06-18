<?php

namespace App\Http\Controllers;

use App\Models\MonthlyExpense;
use App\Models\MonthlyExpenseLog;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MonthlyExpenseController extends Controller
{
    // عرض صفحة المصاريف الشهرية
    public function index()
    {
        $expenses = MonthlyExpense::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.monthly_expenses.index', compact('expenses'));
    }

    // حساب الربح في فترة محددة
    public function calculateProfit(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // حساب الأرباح من الحجوزات التي تتقاطع مع الفترة المحددة
        $bookings = Booking::where(function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                // الحجوزات التي تبدأ داخل الفترة
                $q->whereBetween('check_in', [$startDate, $endDate]);
            })->orWhere(function ($q) use ($startDate, $endDate) {
                // الحجوزات التي تنتهي داخل الفترة
                $q->whereBetween('check_out', [$startDate, $endDate]);
            })->orWhere(function ($q) use ($startDate, $endDate) {
                // الحجوزات التي تحتوي الفترة بالكامل
                $q->where('check_in', '<=', $startDate)
                    ->where('check_out', '>=', $endDate);
            });
        })
            ->get();


        // مصفوفة لحفظ الأرباح حسب العملة
        $profitsByCurrency = [
            'SAR' => 0, // الريال السعودي
            'KWD' => 0  // الدينار الكويتي
        ];

        // عدد الحجوزات لكل عملة
        $bookingsCount = [
            'SAR' => 0,
            'KWD' => 0
        ];


        foreach ($bookings as $booking) {
            // الحصول على عملة الحجز
            $currency = $booking->currency ?? 'SAR'; // استخدام SAR كقيمة افتراضية

            // حساب عدد الليالي المتداخلة مع الفترة المحددة
            $bookingStart = Carbon::parse($booking->check_in);
            $bookingEnd = Carbon::parse($booking->check_out);

            // تعديل تواريخ البداية والنهاية إذا كانت خارج الفترة المطلوبة
            // $effectiveStart = $bookingStart->lt($startDate) ? $startDate : $bookingStart;
            // $effectiveEnd = $bookingEnd->gt($endDate) ? $endDate : $bookingEnd;

            // // عدد الليالي = الفرق بين التواريخ

            // $nights = $effectiveStart->diffInDays($effectiveEnd);
            // لو انا عاوز احسب عدد الليالي من تاريخ الحجز لحد الفترة المحددة

            $nights = $bookingStart->diffInDays($bookingEnd);

            // الربح لهذا الحجز = عدد الليالي * عدد الغرف * (سعر البيع - سعر التكلفة)
            $profitPerRoom = $booking->sale_price - $booking->cost_price;
            $bookingProfit = $nights * $booking->rooms * $profitPerRoom;


            // إضافة الربح إلى العملة المناسبة
            if (isset($profitsByCurrency[$currency])) {
                $profitsByCurrency[$currency] += $bookingProfit;
                $bookingsCount[$currency]++;
            } else {
                // إضافة عملة جديدة إذا لم تكن موجودة
                $profitsByCurrency[$currency] = $bookingProfit;
                $bookingsCount[$currency] = 1;
            }
        }

        // تنسيق تاريخ الفترة كاسم شهر
        $monthYearName = $startDate->format('F Y');

        // تحديد العملة الأساسية بناءً على أكبر مبلغ أرباح
        $primaryCurrency = 'SAR'; // القيمة الافتراضية
        if (isset($profitsByCurrency['KWD']) && isset($profitsByCurrency['SAR'])) {
            $primaryCurrency = $profitsByCurrency['KWD'] >= $profitsByCurrency['SAR'] ? 'KWD' : 'SAR';
        } elseif (isset($profitsByCurrency['KWD']) && $profitsByCurrency['KWD'] > 0) {
            $primaryCurrency = 'KWD';
        }

        return response()->json([
            'profits_by_currency' => $profitsByCurrency,
            'total_profit' => array_sum($profitsByCurrency), // المجموع الكلي (للتوافقية مع الكود القديم)
            'month_year' => $monthYearName,
            'bookings_count' => $bookings->count(),
            'bookings_count_by_currency' => $bookingsCount,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'primary_currency' => $primaryCurrency, // ✅ إضافة العملة الأساسية

        ]);
    }

    // حفظ بيانات المصاريف الشهرية
    public function store(Request $request)
    {

        $validatedData =  $request->validate([
            'month_year' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'salaries' => 'nullable|numeric|min:0',
            'advertising' => 'nullable|numeric|min:0',
            'rent' => 'nullable|numeric|min:0',
            'staff_commissions' => 'nullable|numeric|min:0',
            'total_monthly_profit_SAR' => 'required|numeric|min:0',
            'total_monthly_profit_KWD' => 'required|numeric|min:0',
            'net_profit_SAR' => 'required|numeric|min:0',
            'ismail_share_SAR' => 'required|numeric|min:0',
            'mohamed_share_SAR' => 'required|numeric|min:0',
            'net_profit_KWD' => 'required|numeric|min:0',
            'ismail_share_KWD' => 'required|numeric|min:0',
            'mohamed_share_KWD' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'other_expenses' => 'nullable|array',
            'other_expenses.*.name' => 'required|string|max:255',
            'other_expenses.*.amount' => 'required|numeric|min:0',
            'other_expenses.*.currency' => 'nullable|string|in:SAR,KWD', // العملة للمصاريف الإضافية
        ]);
        // جمع معلومات العملات المستخدمة
        $expensesCurrencies = [
            'salaries' => $request->input('salaries_currency', 'SAR'),
            'advertising' => $request->input('advertising_currency', 'SAR'),
            'rent' => $request->input('rent_currency', 'SAR'),
            'staff_commissions' => $request->input('staff_commissions_currency', 'SAR'),
        ];

        // معالجة المصاريف الإضافية وعملاتها
        $formattedExpenses = [];
        $otherExpenses = $request->input('other_expenses', []);

        if (is_array($otherExpenses)) {
            foreach ($otherExpenses as $key => $expense) {
                if (!empty($expense['name']) && !empty($expense['amount'])) {
                    $formattedExpenses[] = [
                        'name' => trim($expense['name']),
                        'amount' => floatval($expense['amount']),
                        'currency' => $expense['currency'] ?? 'SAR'
                    ];

                    // إضافة معلومات العملة إلى مصفوفة العملات
                    $expensesCurrencies["other_expense_{$key}"] = $expense['currency'] ?? 'SAR';
                }
            }
        }

        $validatedData['other_expenses'] = $formattedExpenses;

        // ✅ معالجة بيانات التوحيد - مُحسنة
        $unifiedCurrency = $request->input('unified_currency');
        $exchangeRate = $request->input('exchange_rate');

        // تنظيف البيانات الإضافية
        $validatedData['expenses_currencies'] = $expensesCurrencies;
        $validatedData['unified_currency'] = $unifiedCurrency;
        $validatedData['exchange_rate'] = $exchangeRate ? floatval($exchangeRate) : null;
        // إنشاء السجل
        $expense = MonthlyExpense::create($validatedData);

        // 🔥 تسجيل عملية الإنشاء
        $this->logExpenseCreation($expense);
        // تسجيل التعديلات التي حدثت
        return redirect()->route('admin.monthly-expenses.index')
            ->with('success', 'تم حفظ بيانات المصاريف الشهرية بنجاح');
    }

    /**
     * ✅ دالة جديدة: تسجيل عملية الإنشاء
     */
    private function logExpenseCreation(MonthlyExpense $expense): void
    {
        // تسجيل عملية الإنشاء الرئيسية
        MonthlyExpenseLog::create([
            'monthly_expense_id' => $expense->id,
            'user_id' => Auth::id() ?? 1,
            'action_type' => 'created',
            'field_name' => 'expense_created',
            'field_label' => 'إنشاء سجل مصاريف جديد',
            'old_value' => null,
            'new_value' => "تم إنشاء سجل مصاريف للفترة: {$expense->month_year}",
            'currency' => null,
            'notes' => 'إنشاء سجل مصاريف شهرية جديد',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // تسجيل جميع القيم الأولية
        $initialFields = [
            'month_year' => 'اسم الفترة',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
            'salaries' => 'إجمالي الرواتب',
            'advertising' => 'إجمالي الإعلانات',
            'rent' => 'الإيجار',
            'staff_commissions' => 'عمولات الموظفين',
            'total_monthly_profit_SAR' => 'إجمالي الربح الشهري (ريال)',
            'total_monthly_profit_KWD' => 'إجمالي الربح الشهري (دينار)',
            'net_profit_SAR' => 'صافي الربح (ريال)',
            'ismail_share_SAR' => 'نصيب إسماعيل (ريال)',
            'mohamed_share_SAR' => 'نصيب محمد حسن (ريال)',
            'net_profit_KWD' => 'صافي الربح (دينار)',
            'ismail_share_KWD' => 'نصيب إسماعيل (دينار)',
            'mohamed_share_KWD' => 'نصيب محمد حسن (دينار)',
            'notes' => 'الملاحظات',
        ];

        foreach ($initialFields as $fieldName => $fieldLabel) {
            $value = $expense->$fieldName;

            // تسجيل فقط الحقول التي لها قيم غير فارغة
            if ($value !== null && $value !== '' && $value != 0) {
                $currency = $this->determineCurrency($fieldName, $expense);

                MonthlyExpenseLog::create([
                    'monthly_expense_id' => $expense->id,
                    'user_id' => Auth::id() ?? 1,
                    'action_type' => 'created',
                    'field_name' => $fieldName,
                    'field_label' => $fieldLabel,
                    'old_value' => null,
                    'new_value' => $value,
                    'currency' => $currency,
                    'notes' => 'قيمة أولية عند الإنشاء',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        }

        // ✅ تسجيل المصاريف الإضافية عند الإنشاء
        if (!empty($expense->other_expenses)) {
            MonthlyExpenseLog::create([
                'monthly_expense_id' => $expense->id,
                'user_id' => Auth::id() ?? 1,
                'action_type' => 'created',
                'field_name' => 'other_expenses',
                'field_label' => 'المصاريف الإضافية',
                'old_value' => null,
                'new_value' => json_encode($expense->other_expenses, JSON_UNESCAPED_UNICODE),
                'currency' => null,
                'notes' => 'المصاريف الإضافية عند الإنشاء',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        // ✅ تسجيل معلومات العملات عند الإنشاء
        if (!empty($expense->expenses_currencies)) {
            MonthlyExpenseLog::create([
                'monthly_expense_id' => $expense->id,
                'user_id' => Auth::id() ?? 1,
                'action_type' => 'created',
                'field_name' => 'expenses_currencies',
                'field_label' => 'عملات المصاريف',
                'old_value' => null,
                'new_value' => json_encode($expense->expenses_currencies, JSON_UNESCAPED_UNICODE),
                'currency' => null,
                'notes' => 'تحديد عملات المصاريف عند الإنشاء',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * ✅ دالة مساعدة: تحديد العملة للحقل
     */
    private function determineCurrency(string $fieldName, MonthlyExpense $expense): ?string
    {
        // التحقق من الحقول التي تحتوي على العملة في الاسم
        if (str_contains($fieldName, 'SAR')) {
            return 'SAR';
        }

        if (str_contains($fieldName, 'KWD')) {
            return 'KWD';
        }

        // التحقق من المصاريف الأساسية
        if (in_array($fieldName, ['salaries', 'advertising', 'rent', 'staff_commissions'])) {
            $currencies = $expense->expenses_currencies;
            return is_array($currencies) ? ($currencies[$fieldName] ?? 'SAR') : 'SAR';
        }


        return null;
    }
    // عرض تفاصيل مصروفات شهر معين
    public function show($id)
    {
        $expense = MonthlyExpense::findOrFail($id);
        return view('admin.monthly_expenses.show', compact('expense'));
    }

    // حذف مصروفات شهر معين
    public function destroy($id)
    {
        $expense = MonthlyExpense::findOrFail($id);
        // 🔥 تسجيل عملية الحذف قبل الحذف الفعلي
        MonthlyExpenseLog::create([
            'monthly_expense_id' => $expense->id,
            'user_id' => Auth::id() ?? 1,
            'action_type' => 'deleted',
            'field_name' => 'expense_deleted',
            'field_label' => 'حذف سجل مصاريف',
            'old_value' => "سجل مصاريف للفترة: {$expense->month_year}",
            'new_value' => null,
            'currency' => null,
            'notes' => 'تم حذف سجل المصاريف نهائياً',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);


        $expense->delete();

        return redirect()->route('admin.monthly-expenses.index')
            ->with('success', 'تم حذف البيانات بنجاح');
    }

    /**
     * عرض صفحة تعديل المصاريف الشهرية
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // جلب سجل المصاريف مع التحقق من وجوده
        $expense = MonthlyExpense::findOrFail($id);

        return view('admin.monthly_expenses.edit', compact('expense'));
    }

    /**
     * تحديث بيانات المصاريف الشهرية
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // جلب السجل المراد تعديله
        $expense = MonthlyExpense::findOrFail($id);
        // حفظ القيم القديمة للمقارنة
        $oldValues = [
            'month_year' => $expense->month_year,
            'start_date' => $expense->start_date,
            'end_date' => $expense->end_date,
            'salaries' => $expense->salaries,
            'advertising' => $expense->advertising,
            'rent' => $expense->rent,
            'staff_commissions' => $expense->staff_commissions,
            'total_monthly_profit_SAR' => $expense->total_monthly_profit_SAR,
            'total_monthly_profit_KWD' => $expense->total_monthly_profit_KWD,
            'net_profit_SAR' => $expense->net_profit_SAR,
            'ismail_share_SAR' => $expense->ismail_share_SAR,
            'mohamed_share_SAR' => $expense->mohamed_share_SAR,
            'net_profit_KWD' => $expense->net_profit_KWD,
            'ismail_share_KWD' => $expense->ismail_share_KWD,
            'mohamed_share_KWD' => $expense->mohamed_share_KWD,
            'notes' => $expense->notes,
            'other_expenses' => $expense->other_expenses,
            'expenses_currencies' => $expense->expenses_currencies,
            'unified_currency' => $expense->unified_currency,
            'exchange_rate' => $expense->exchange_rate,
        ];

        // التحقق من صحة البيانات المدخلة
        $validatedData = $request->validate([
            'month_year' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'salaries' => 'nullable|numeric|min:0',
            'advertising' => 'nullable|numeric|min:0',
            'rent' => 'nullable|numeric|min:0',
            'staff_commissions' => 'nullable|numeric|min:0',
            'total_monthly_profit_SAR' => 'required|numeric|min:0',
            'total_monthly_profit_KWD' => 'required|numeric|min:0',
            'net_profit_SAR' => 'required|numeric|min:0',
            'ismail_share_SAR' => 'required|numeric|min:0',
            'mohamed_share_SAR' => 'required|numeric|min:0',
            'net_profit_KWD' => 'required|numeric|min:0',
            'ismail_share_KWD' => 'required|numeric|min:0',
            'mohamed_share_KWD' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            // ✅ إضافة validation للمصاريف الإضافية
            'other_expenses' => 'nullable|array',
            'other_expenses.*.name' => 'required_with:other_expenses.*.amount|string|max:255',
            'other_expenses.*.amount' => 'required_with:other_expenses.*.name|numeric|min:0',
            'other_expenses.*.currency' => 'nullable|string|in:SAR,KWD',
        ]);

        // معالجة معلومات العملات المستخدمة
        $expensesCurrencies = [
            'salaries' => $request->input('salaries_currency', 'SAR'),
            'advertising' => $request->input('advertising_currency', 'SAR'),
            'rent' => $request->input('rent_currency', 'SAR'),
            'staff_commissions' => $request->input('staff_commissions_currency', 'SAR'),
        ];

        // معالجة المصاريف الإضافية وتنظيمها
        $formattedExpenses = [];
        $otherExpenses = $request->input('other_expenses', []);

        if (is_array($otherExpenses)) {
            foreach ($otherExpenses as $key => $expense_item) {
                if (!empty($expense_item['name']) && !empty($expense_item['amount'])) {
                    $formattedExpenses[] = [
                        'name' => $expense_item['name'],
                        'amount' => floatval($expense_item['amount']),
                        'currency' => $expense_item['currency'] ?? 'SAR'
                    ];

                    // إضافة معلومات العملة للمصروف الإضافي
                    $expensesCurrencies["other_expense_{$key}"] = $expense_item['currency'] ?? 'SAR';
                }
            }
        }

        $validatedData['other_expenses'] = $formattedExpenses;

        // معالجة بيانات توحيد العملة إذا تم استخدامها
        $unifiedCurrency = $request->input('unified_currency', null);
        $exchangeRate = null;

        if ($unifiedCurrency) {
            $exchangeRate = $request->input('exchange_rate', null);
        }

        // إضافة البيانات الإضافية للحفظ
        $validatedData['expenses_currencies'] = $expensesCurrencies;
        $validatedData['unified_currency'] = $unifiedCurrency;
        $validatedData['exchange_rate'] = $exchangeRate;

        // تحديث السجل في قاعدة البيانات
        $expense->update($validatedData);
        // تسجيل كل التعديلات التي حدثت
        $this->logExpenseChanges($expense, $oldValues, $validatedData);

        // إعادة التوجيه مع رسالة نجاح
        return redirect()->route('admin.monthly-expenses.index')
            ->with('success', 'تم تحديث بيانات المصاريف الشهرية بنجاح');
    }


    /**
     * تسجيل جميع التعديلات التي حدثت على المصروف الشهري
     */
    private function logExpenseChanges(MonthlyExpense $expense, array $oldValues, array $newValues): void
    {
        // مصفوفة تحتوي على أسماء الحقول وتسمياتها باللغة العربية
        $fieldLabels = [
            'month_year' => 'اسم الفترة',
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
            'salaries' => 'إجمالي الرواتب',
            'advertising' => 'إجمالي الإعلانات',
            'rent' => 'الإيجار',
            'staff_commissions' => 'عمولات الموظفين',
            'total_monthly_profit_SAR' => 'إجمالي الربح الشهري (ريال)',
            'total_monthly_profit_KWD' => 'إجمالي الربح الشهري (دينار)',
            'net_profit_SAR' => 'صافي الربح (ريال)',
            'ismail_share_SAR' => 'نصيب إسماعيل (ريال)',
            'mohamed_share_SAR' => 'نصيب محمد حسن (ريال)',
            'net_profit_KWD' => 'صافي الربح (دينار)',
            'ismail_share_KWD' => 'نصيب إسماعيل (دينار)',
            'mohamed_share_KWD' => 'نصيب محمد حسن (دينار)',
            'notes' => 'الملاحظات',
            'unified_currency' => 'العملة الموحدة',
            'exchange_rate' => 'سعر الصرف',
        ];

        // مقارنة كل حقل وتسجيل التعديلات
        foreach ($fieldLabels as $fieldName => $fieldLabel) {
            $oldValue = $oldValues[$fieldName] ?? null;
            $newValue = $newValues[$fieldName] ?? null;

            // تحويل التواريخ للنص للمقارنة
            if (in_array($fieldName, ['start_date', 'end_date'])) {
                $oldValue = $oldValue ? $oldValue->format('Y-m-d') : null;
                $newValue = $newValue ? \Carbon\Carbon::parse($newValue)->format('Y-m-d') : null;
            }

            // تسجيل التعديل إذا تغيرت القيمة
            if ($oldValue != $newValue) {
                $currency = $this->determineCurrency($fieldName, $expense);

                // ✅ استخدام MonthlyExpenseLog مباشرة بدلاً من logChange()
                MonthlyExpenseLog::create([
                    'monthly_expense_id' => $expense->id,
                    'user_id' => Auth::id() ?? 1,
                    'action_type' => 'updated',
                    'field_name' => $fieldName,
                    'field_label' => $fieldLabel,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'currency' => $currency,
                    'notes' => "تعديل تلقائي بواسطة النظام",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        }

        // تسجيل تعديلات المصاريف الإضافية
        $this->logOtherExpensesChanges($expense, $oldValues['other_expenses'] ?? [], $newValues['other_expenses'] ?? []);

        // تسجيل تعديلات عملات المصاريف
        $this->logCurrencyChanges($expense, $oldValues['expenses_currencies'] ?? [], $newValues['expenses_currencies'] ?? []);
    }

    /**
     * تسجيل تعديلات المصاريف الإضافية - مُحدثة
     */
    private function logOtherExpensesChanges(MonthlyExpense $expense, array $oldExpenses, array $newExpenses): void
    {
        // تحويل المصاريف لنص للمقارنة
        $oldExpensesText = json_encode($oldExpenses, JSON_UNESCAPED_UNICODE);
        $newExpensesText = json_encode($newExpenses, JSON_UNESCAPED_UNICODE);

        if ($oldExpensesText !== $newExpensesText) {
            MonthlyExpenseLog::create([
                'monthly_expense_id' => $expense->id,
                'user_id' => Auth::id() ?? 1,
                'action_type' => 'updated',
                'field_name' => 'other_expenses',
                'field_label' => 'المصاريف الإضافية',
                'old_value' => $oldExpensesText,
                'new_value' => $newExpensesText,
                'currency' => null,
                'notes' => 'تم تعديل المصاريف الإضافية',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * تسجيل تعديلات عملات المصاريف - مُحدثة
     */
    private function logCurrencyChanges(MonthlyExpense $expense, array $oldCurrencies, array $newCurrencies): void
    {
        foreach ($newCurrencies as $expenseType => $newCurrency) {
            $oldCurrency = $oldCurrencies[$expenseType] ?? 'SAR';

            if ($oldCurrency !== $newCurrency) {
                $fieldLabel = match ($expenseType) {
                    'salaries' => 'عملة الرواتب',
                    'advertising' => 'عملة الإعلانات',
                    'rent' => 'عملة الإيجار',
                    'staff_commissions' => 'عملة عمولات الموظفين',
                    default => "عملة {$expenseType}",
                };

                MonthlyExpenseLog::create([
                    'monthly_expense_id' => $expense->id,
                    'user_id' => Auth::id() ?? 1,
                    'action_type' => 'updated',
                    'field_name' => "currency_{$expenseType}",
                    'field_label' => $fieldLabel,
                    'old_value' => $oldCurrency,
                    'new_value' => $newCurrency,
                    'currency' => null,
                    'notes' => 'تغيير عملة المصروف',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        }
    }
   
}
