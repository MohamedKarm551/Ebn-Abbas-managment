<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\LandTripsAgentPayment;
use App\Models\Employee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LandTripsAgentPaymentController extends Controller
{
    /**
     * عرض قائمة الوكلاء مع المستحقات للرحلات البرية
     */
    public function index(Request $request)
    {
        $query = Agent::with(['landTripsPayments', 'landTripBookings'])
            ->whereHas('landTripBookings') // فقط الوكلاء الذين لديهم حجوزات رحلات برية
            ->withCount('landTripBookings')
            ->orderByDesc('land_trip_bookings_count');

        // فلترة حسب البحث
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // فلترة حسب العملة
        if ($request->filled('currency')) {
            $currency = $request->currency;
            $query->whereHas('landTripBookings', function ($bookingQuery) use ($currency) {
                $bookingQuery->where('currency', $currency);
            });
        }

        $agents = $query->get()->map(function ($agent) {
            $totals = $agent->getLandTripTotalsByCurrency();

            // إعداد كل العملات
            foreach (['SAR', 'KWD'] as $currency) {
                if (!isset($totals[$currency])) {
                    $totals[$currency] = ['due' => 0, 'paid' => 0, 'remaining' => 0];
                }
            }

            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'phone' => $agent->phone,
                'bookings_count' => $agent->land_trip_bookings_count,
                'totals_by_currency' => $totals,
                'last_payment' => $agent->landTripsPayments()->latest()->first(),
            ];
        })->filter(function($agent) {
            // فقط الوكلاء الذين لديهم مستحقات
            return $agent['totals_by_currency']['SAR']['due'] > 0 || 
                   $agent['totals_by_currency']['KWD']['due'] > 0;
        })->values();

        // حساب الإحصائيات العامة
        $totalStats = [
            'agents_count' => $agents->count(),
            'total_due_sar' => $agents->sum(fn($a) => $a['totals_by_currency']['SAR']['due']),
            'total_paid_sar' => $agents->sum(fn($a) => $a['totals_by_currency']['SAR']['paid']),
            'total_due_kwd' => $agents->sum(fn($a) => $a['totals_by_currency']['KWD']['due']),
            'total_paid_kwd' => $agents->sum(fn($a) => $a['totals_by_currency']['KWD']['paid']),
            'total_bookings' => $agents->sum('bookings_count'),
        ];

        return view('admin.land-trips-agent-payments.index', compact('agents', 'totalStats'));
    }

    /**
     * عرض تفاصيل مدفوعات وكيل معين للرحلات البرية
     */
    public function show(Agent $agent)
    {
        $agent->load(['landTripsPayments.employee']);

        $totals = $agent->getLandTripTotalsByCurrency();
        
        foreach (['SAR', 'KWD'] as $currency) {
            if (!isset($totals[$currency])) {
                $totals[$currency] = ['due' => 0, 'paid' => 0, 'remaining' => 0];
            }
        }

        $payments = $agent->landTripsPayments()
            ->with('employee')
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        $recentBookings = $agent->landTripBookings()
            ->with(['landTrip', 'company'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.land-trips-agent-payments.show', compact(
            'agent',
            'totals', 
            'payments',
            'recentBookings'
        ));
    }

    /**
     * عرض نموذج إضافة دفعة جديدة
     */
    public function create(Agent $agent)
    {
        $totals = $agent->getLandTripTotalsByCurrency();
        foreach (['SAR', 'KWD'] as $currency) {
            if (!isset($totals[$currency])) {
                $totals[$currency] = ['due' => 0, 'paid' => 0, 'remaining' => 0];
            }
        }
        $employees = Employee::orderBy('name')->get();

        return view('admin.land-trips-agent-payments.create', compact('agent', 'totals', 'employees'));
    }

    /**
     * إنشاء دفعة جديدة للوكيل
     */
    public function store(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'receipt_image_url' => 'nullable|url|max:500',
        ]);

        $employee = Employee::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            $payment = LandTripsAgentPayment::create([
                'agent_id' => $agent->id,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'receipt_image_url' => $validated['receipt_image_url'],
                'employee_id' => $employee?->id,
            ]);

            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم تسجيل دفعة للوكيل {$agent->name} بمبلغ {$payment->amount} {$payment->currency} (رحلات برية)",
                'type' => 'دفعة وكيل - رحلات برية',
            ]);

            DB::commit();

            return redirect()->route('admin.land-trips-agent-payments.show', $agent)
                ->with('success', 'تم تسجيل الدفعة للوكيل بنجاح');
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
    public function edit(Agent $agent, LandTripsAgentPayment $payment)
    {
        if ($payment->agent_id !== $agent->id) {
            abort(404);
        }

        $employees = Employee::orderBy('name')->get();

        return view('admin.land-trips-agent-payments.edit', compact('agent', 'payment', 'employees'));
    }

    /**
     * تحديث دفعة موجودة
     */
    public function update(Request $request, Agent $agent, LandTripsAgentPayment $payment)
    {
        if ($payment->agent_id !== $agent->id) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,transfer,check',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'receipt_image_url' => 'nullable|url|max:500',
        ]);

        $oldAmount = $payment->amount;
        $oldCurrency = $payment->currency;

        $payment->update($validated);

        Notification::create([
            'user_id' => Auth::id(),
            'message' => "تم تعديل دفعة الوكيل {$agent->name} من {$oldAmount} {$oldCurrency} إلى {$payment->amount} {$payment->currency} (رحلات برية)",
            'type' => 'تعديل دفعة وكيل - رحلات برية',
        ]);

        return redirect()->route('admin.land-trips-agent-payments.show', $agent)
            ->with('success', 'تم تحديث الدفعة بنجاح');
    }

    /**
     * حذف دفعة
     */
    public function destroy(Agent $agent, LandTripsAgentPayment $payment)
    {
        if ($payment->agent_id !== $agent->id) {
            abort(404);
        }

        $paymentInfo = [
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'date' => $payment->payment_date->format('Y-m-d')
        ];

        $payment->delete();

        Notification::create([
            'user_id' => Auth::id(),
            'message' => "تم حذف دفعة الوكيل {$agent->name} بمبلغ {$paymentInfo['amount']} {$paymentInfo['currency']} بتاريخ {$paymentInfo['date']} (رحلات برية)",
            'type' => 'حذف دفعة وكيل - رحلات برية',
        ]);

        return redirect()->route('admin.land-trips-agent-payments.show', $agent)
            ->with('success', 'تم حذف الدفعة بنجاح');
    }

    /**
     * تطبيق خصم كدفعة سالبة للرحلات البرية
     */
    public function applyDiscount(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:SAR,KWD',
            'reason' => 'nullable|string|max:500'
        ]);

        $totals = $agent->getLandTripTotalsByCurrency();
        $currentTotals = $totals[$validated['currency']] ?? ['due' => 0, 'paid' => 0, 'remaining' => 0];

        if ($validated['discount_amount'] > $currentTotals['remaining']) {
            return redirect()->back()
                ->with('error', "مبلغ الخصم ({$validated['discount_amount']} {$validated['currency']}) أكبر من المبلغ المتبقي ({$currentTotals['remaining']} {$validated['currency']})");
        }

        $employee = Employee::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            $discountPayment = LandTripsAgentPayment::create([
                'agent_id' => $agent->id,
                'amount' => -$validated['discount_amount'], // قيمة سالبة للخصم
                'currency' => $validated['currency'],
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'cash',
                'notes' => 'خصم مطبق (رحلات برية): ' . ($validated['reason'] ?: 'خصم'),
                'employee_id' => $employee?->id,
            ]);

            Notification::create([
                'user_id' => Auth::id(),
                'message' => "تم تطبيق خصم {$validated['discount_amount']} {$validated['currency']} على الوكيل {$agent->name} (رحلات برية)",
                'type' => 'خصم مطبق - وكيل رحلات برية',
            ]);

            DB::commit();

            return redirect()->route('admin.land-trips-agent-payments.show', $agent)
                ->with('success', "تم تطبيق خصم {$validated['discount_amount']} {$validated['currency']} بنجاح");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تطبيق الخصم: ' . $e->getMessage());
        }
    }
}