<?php

namespace App\Http\Controllers;

use App\Models\MonthlyExpense;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        if (isset($validatedData['other_expenses']) && is_array($validatedData['other_expenses'])) {
            foreach ($validatedData['other_expenses'] as $key => $expense) {
                if (!empty($expense['name']) && !empty($expense['amount'])) {
                    $formattedExpenses[] = [
                        'name' => $expense['name'],
                        'amount' => floatval($expense['amount']),
                        'currency' => $expense['currency'] ?? 'SAR'
                    ];

                    // إضافة معلومات العملة إلى مصفوفة العملات
                    $expensesCurrencies["other_expense_{$key}"] = $expense['currency'] ?? 'SAR';
                }
            }
        }

        $validatedData['other_expenses'] = $formattedExpenses;

        // إضافة معلومات العملة المستخدمة للتوحيد إذا تم استخدامها
        $unifiedCurrency = $request->input('unified_currency', null);
        $exchangeRate = null;

        if ($unifiedCurrency) {
            $exchangeRate = $request->input('exchange_rate', null);
        }

        // إضافة البيانات الإضافية
        $validatedData['expenses_currencies'] = $expensesCurrencies;
        $validatedData['unified_currency'] = $unifiedCurrency;
        $validatedData['exchange_rate'] = $exchangeRate;



        MonthlyExpense::create($validatedData);

        return redirect()->route('admin.monthly-expenses.index')
            ->with('success', 'تم حفظ بيانات المصاريف الشهرية بنجاح');
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
        $expense->delete();

        return redirect()->route('admin.monthly-expenses.index')
            ->with('success', 'تم حذف البيانات بنجاح');
    }
}
