<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Agent;
use App\Models\AgentPayment;
use App\Models\Payment;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        // تقرير الشركات: كل شركة وعدد حجوزاتها
        $companiesReport = Company::withCount('bookings')->get();

        // تقرير الوكلاء: كل وكيل وعدد حجوزاته
        $agentsReport = Agent::withCount('bookings')->get();

        // إجمالي المستحق من الشركات (كل اللي المفروض الشركات تدفعه بناءً على كل حجوزاتها)
        $totalDueFromCompanies = $companiesReport->sum('total_due');

        // إجمالي اللي اتدفع للفنادق (كل اللي اتدفع فعلاً للفنادق عن كل الحجوزات)
        $totalPaidToHotels = Booking::all()->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });

        // تقرير الفنادق: كل فندق وعدد حجوزاته (قائمة الفنادق مع عدد الحجوزات لكل فندق)
        $hotelsReport = Hotel::withCount('bookings')->get();

        // تقرير الشركات: كل شركة وعدد حجوزاتها (قائمة الشركات مع عدد الحجوزات لكل شركة)
        $companiesReport = Company::withCount('bookings')->get();

        // إجمالي المتبقي من الشركات (كل اللي لسه الشركات ما دفعتهوش فعلاً = المستحق - المدفوع لكل شركة)
        $totalRemainingFromCompanies = $companiesReport->sum('remaining');

        // إجمالي المتبقي للفنادق (كل اللي لسه عليك تدفعه للفنادق = المستحق للفنادق - اللي اتدفع فعلاً)
        $totalRemainingToHotels = Booking::sum('amount_due_to_hotel') - AgentPayment::sum('amount');
        // إجمالي اللي علينا لجهات الحجز أو الفنادقdd(Booking::sum('amount_due_to_hotel')); 

        // صافي الربح (الفرق بين اللي لسه الشركات هتدفعه لك واللي لسه عليك تدفعه للفنادق)
        $netProfit = $totalRemainingFromCompanies - $totalRemainingToHotels;

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
            'netProfit'
        ));
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



      
        // رجع البيانات للواجهة
        return view('reports.company_bookings', compact(
            'company',
            'bookings',
            'dueCount',
            'totalDue',
            'totalPaid',
            'totalRemaining',
           
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

        // رجع البيانات للواجهة
        return view('reports.agent_bookings', compact(
            'agent',
            'bookings',
            'dueCount',
            'totalDue',
            'totalPaid',
            'totalRemaining'
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

        // رجع البيانات للواجهة
        return view('reports.hotel_bookings', [
            'hotel'   => $hotel,
            'bookings' => $bookings
        ]);
    }

    // إضافة دفعة جديدة لشركة
    public function storePayment(Request $request)
    {
        // تحقق من البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'amount'           => 'required|numeric|min:0',
            'payment_date'     => 'nullable|date',
            'notes'            => 'nullable|string',
            'bookings_covered' => 'nullable|array',
            'bookings_covered.*' => 'exists:bookings,id',
        ]);

        // سجل الدفعة في جدول payments
        $payment = Payment::create([
            'company_id'       => $validated['company_id'],
            'amount'           => $validated['amount'],
            'payment_date'     => $validated['payment_date'] ?? now(),
            'notes'            => $validated['notes'] ?? null,
            'bookings_covered' => json_encode($validated['bookings_covered'] ?? []),
        ]);

        // وزع المبلغ على الحجوزات المفتوحة
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
            'notes'    => 'nullable|string',
        ]);

        // سجل الدفعة في جدول agent_payments
        $payment = AgentPayment::create([
            'agent_id' => $validated['agent_id'],
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'notes' => $validated['notes'],
        ]);

        // رجع للصفحة مع رسالة نجاح
        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    // سجل الدفعات لشركة معينة
    public function companyPayments($id)
    {
        // هات الشركة المطلوبة
        $company = Company::findOrFail($id);

        // هات كل الدفعات بتاعتها
        $payments = Payment::where('company_id', $id)->orderBy('payment_date', 'desc')->get();

        // رجع البيانات للواجهة
        return view('reports.company_payments', compact('company', 'payments'));
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

        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $payment->company_id)
            ->with('success', 'تم تعديل دفعة الشركة بنجاح!');
    }

    // حذف دفعة شركة مع إعادة توزيع المبالغ على الحجوزات
    public function destroyCompanyPayment($id)
    {
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

        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $companyId)
            ->with('success', 'تم حذف الدفعة وإرجاع المبالغ المرتبطة بها.');
    }

    // حذف دفعة وكيل
    public function destroyAgentPayment($id)
    {
        // هات الدفعة المطلوبة
        $payment = AgentPayment::findOrFail($id);
        $agentId = $payment->agent_id;

        // احذف الدفعة
        $payment->delete();

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
}
