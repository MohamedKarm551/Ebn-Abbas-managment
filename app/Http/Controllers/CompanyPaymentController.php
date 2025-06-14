<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyPayment;
use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
}
