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
    /**
     * daily
     *
     * يعرض تقريراً يومياً بالحجوزات الحالية وإحصائياتها
     *
     * 1. يحصل على تاريخ اليوم
     * 2. يسترجع الحجوزات التي تبدأ اليوم (check_in == اليوم)
     * 3. يجمع إحصائيات عدد الحجوزات لكل شركة ووكيل وفندق
     * 4. يحسب إجمالي المستحق من الشركات وإجمالي المدفوع للفنادق
     * 5. يُرسل البيانات لواجهة reports.daily
     */
    public function daily()
    {
        // 1. تاريخ بداية اليوم
        $today = Carbon::today();

        // 2. حجوزات اليوم
        $todayBookings = Booking::whereDate('check_in', $today)->get();

        // 3.   حتى اليوم إحصائيات عدد الحجوزات لكل شركة
    
        $companiesReport = Company::withCount('bookings')->get();

        // 4. إحصائيات عدد الحجوزات لكل وكيل
        $agentsReport = Agent::withCount('bookings')->get();

        // 5. مجموع المبالغ المستحقة من الشركات
        $totalDueFromCompanies = $companiesReport->sum('total_due');

        // 6. مجموع المدفوع للفنادق (سعر التكلفة × غرف × أيام)
        $totalPaidToHotels = Booking::all()->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });

        // 7. إحصائيات عدد الحجوزات لكل فندق
        $hotelsReport = Hotel::withCount('bookings')->get();

        // 8. عرض الواجهة مع تمرير جميع البيانات المحسوبة
        return view('reports.daily', compact(
            'todayBookings',
            'companiesReport',
            'agentsReport',
            'hotelsReport',
            'totalDueFromCompanies',
            'totalPaidToHotels'
        ));
    }

    /**
     * companyBookings
     *
     * يعرض جميع الحجوزات الخاصة بشركة معيّنة
     *
     * @param int $id  معرّف الشركة
     * 1. يجد الشركة أو يفشل (404) إن لم توجد
     * 2. يسترجع الحجوزات المرتبطة بها مع بيانات الفندق والوكيل
     * 3. يرتب النتائج حسب تاريخ الدخول
     * 4. يعرض view reports.company_bookings
     */
    public function companyBookings($id)
    {
        $company = Company::findOrFail($id);

        // 1) تحميل كل الحجوزات بالشركة وحساب الخصائص
        $bookings = $company->bookings()
            ->with(['hotel','agent'])
            ->orderBy('check_in')
            ->get()
            ->map(function($b) {
                $b->lapsed_days       = $b->lapsed_days;
                $b->due_to_company    = $b->due_to_company;
                $b->company_paid      = $b->company_paid;
                $b->company_remaining = $b->company_remaining;
                return $b;
            });

        // 2) صفّ الحجوزات المستحقة حتى اليوم: دخلت فعلاً (check_in <= اليوم) وما سُدّد رصيدها بالكامل
        $today = Carbon::today();
        $dueBookings = $bookings->filter(function($b) use($today) {
            return $b->check_in->lte($today) && $b->company_remaining != 0;
        });

        // 3) الإحصائيات
        $dueCount      = $dueBookings->count();
        $totalDue      = $dueBookings->sum('due_to_company');
        $totalPaid     = $dueBookings->sum('company_paid');
        $totalRemaining= $dueBookings->sum('company_remaining');

        // 4) عرض الواجهة مع تمرير جميع البيانات
        return view('reports.company_bookings', compact(
            'company','bookings',
            'dueCount','totalDue','totalPaid','totalRemaining'
        ));
    }

    /**
     * agentBookings
     *
     * يعرض جميع الحجوزات الخاصة بوكيل حجز معيّن
     *
     * @param int $id  معرّف الوكيل
     * 1. يجد الوكيل أو يفشل (404)
     * 2. يسترجع الحجوزات المرتبطة به مع بيانات الفندق والشركة
     * 3. يرتب حسب تاريخ الدخول
     * 4. يعيد view reports.agent_bookings
     */
    public function agentBookings($id)
    {
        $agent = Agent::findOrFail($id);

        // 1) تحميل كل الحجوزات للوكيل وحساب الخصائص
        $bookings = $agent->bookings()
            ->with(['hotel','company'])
            ->orderBy('check_in')
            ->get()
            ->map(function($b) {
                $b->lapsed_days     = $b->lapsed_days;
                $b->due_to_agent    = $b->due_to_agent;
                $b->agent_paid      = $b->agent_paid;
                $b->agent_remaining = $b->agent_remaining;
                return $b;
            });

        // 2) صفّ الحجوزات المستحقة حتى اليوم
        $today = Carbon::today();
        $dueBookings = $bookings->filter(function($b) use($today) {
            return $b->check_in->lte($today) && $b->agent_remaining != 0;
        });

        // 3) الإحصائيات
        $dueCount      = $dueBookings->count();
        $totalDue      = $dueBookings->sum('due_to_agent');
        $totalPaid     = $dueBookings->sum('agent_paid');
        $totalRemaining= $dueBookings->sum('agent_remaining');

        // 4) عرض الواجهة
        return view('reports.agent_bookings', compact(
            'agent','bookings',
            'dueCount','totalDue','totalPaid','totalRemaining'
        ));
    }

    /**
     * hotelBookings
     *
     * يعرض جميع الحجوزات الخاصة بفندق معيّن
     *
     * @param int $id  معرّف الفندق
     * 1. يجد الفندق أو يفشل (404)
     * 2. يسترجع الحجوزات التي تخصه مع بيانات الشركة والوكيل
     * 3. يعرض view reports.hotel_bookings
     */
    public function hotelBookings($id)
    {
        $hotel = Hotel::findOrFail($id);

        $bookings = Booking::where('hotel_id', $id)
            ->with(['company', 'agent'])
            ->get();

        return view('reports.hotel_bookings', [
            'hotel'   => $hotel,
            'bookings'=> $bookings
        ]);
    }

    /**
     * storePayment
     *
     * يسجّل دفعة جديدة لشركة في جدول payments
     *
     * @param Request $request  الطلب الوارد من الفورم مع company_id, amount, notes, payment_date
     * 1. يحقّق من صحة البيانات
     * 2. يجد الشركة أو يفشل
     * 3. ينشئ سجل دفعة في جدول payments
     * 4. يعيد التوجيه مع رسالة نجاح
     */
    public function storePayment(Request $request)
    {
        // 1) Validate
        $validated = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'amount'           => 'required|numeric|min:0',
            'payment_date'     => 'nullable|date',
            'notes'            => 'nullable|string',
            'bookings_covered' => 'nullable|array',        // تمرير مصفوفة IDs
            'bookings_covered.*' => 'exists:bookings,id',
        ]);

        // 2) سجل الدفعة
        $payment = Payment::create([
            'company_id'       => $validated['company_id'],
            'amount'           => $validated['amount'],
            'payment_date'     => $validated['payment_date'] ?? now(),
            'notes'            => $validated['notes'] ?? null,
            'bookings_covered' => json_encode($validated['bookings_covered'] ?? []),
        ]);

        // 3) وزّع المبلغ على الحجوزات المفتوحة
        $remaining = $payment->amount;
        Booking::whereIn('id', $validated['bookings_covered'] ?? [])
            ->orderBy('check_in') // أو تحديد الترتيب المناسب
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

        // 4) إعادة التوجيه للـ companyPayments عبر الاسم الصحيح:
        return redirect()
            ->route('reports.company.payments', $validated['company_id'])
            ->with('success', 'تم تسجيل الدفعة وتخصيصها على الحجوزات بنجاح!');
    }

    /**
     * storeAgentPayment
     *
     * يسجّل دفعة جديدة لوكيل في جدول agent_payments
     *
     * @param Request $request  الطلب الوارد مع agent_id, amount, notes
     * 1. يحقّق من صحة البيانات
     * 2. ينشئ سجل دفعة في جدول agent_payments
     * 3. يعيد التوجيه مع رسالة نجاح
     */
    public function storeAgentPayment(Request $request)
    {
        // التحقق من المدخلات
        $validated = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'amount'   => 'required|numeric|min:0',
            'notes'    => 'nullable|string',
        ]);

        // إنشاء دفعة للوكيل
        $payment = AgentPayment::create([
            'agent_id' => $validated['agent_id'], // جهة الحجز المرتبطة
            'amount' => $validated['amount'], // المبلغ المدفوع
            'payment_date' => now(), // تاريخ الدفع
            'notes' => $validated['notes'], // الملاحظات
        ]);

        // العودة مع إشعار نجاح
        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    /**
     * companyPayments
     *
     * يعرض سجل الدفعات لشركة معيّنة
     *
     * @param int $id  معرّف الشركة
     * 1. يجد الشركة
     * 2. يسترجع جميع دفعاتها مرتبة تنازلياً بحسب تاريخ الدفع
     * 3. يعرض view reports.company_payments
     */
    public function companyPayments($id)
    {
        $company = Company::findOrFail($id);
        $payments = Payment::where('company_id', $id)->orderBy('payment_date', 'desc')->get();

        return view('reports.company_payments', compact('company', 'payments'));
    }

    /**
     * agentPayments
     *
     * يعرض سجل الدفعات لوكيل معيّن
     *
     * @param int $id  معرّف الوكيل
     * 1. يجد الوكيل
     * 2. يسترجع جميع دفعاته مرتبة حسب تاريخ الدفع تنازلياً
     * 3. يعرض view reports.agent_payments
     */
    public function agentPayments($id)
    {
        $agent    = Agent::findOrFail($id);
        $payments = AgentPayment::where('agent_id', $id)
                                ->orderBy('payment_date', 'desc')
                                ->get();

        return view('reports.agent_payments', compact('agent', 'payments'));
    }

    /**
     * editPayment
     *
     * يعرض نموذج تعديل دفعة وكيل معينة
     *
     * @param int $id  معرّف دفعة الوكيل
     * 1. يجد الدفعة أو يفشل
     * 2. يعرض view reports.edit_payment مع بيانات الدفعة
     */
    public function editPayment($id)
    {
        $payment = AgentPayment::findOrFail($id);

        return view('reports.edit_payment', compact('payment'));
    }

    /**
     * updatePayment
     *
     * يعالج تحديث دفعة وكيل بعد تعديل البيانات
     *
     * @param Request $request  الطلب يحتوي على amount, notes
     * @param int     $id       معرّف الدفعة
     * 1. يتحقق من صحة المدخلات
     * 2. يجد الدفعة ويحدّثها
     * 3. يعيد تحميل علاقات الوكيل لضمان تحديث الديناميكية
     * 4. يعيد التوجيه إلى صفحة سجل دفعات الوكيل مع رسالة نجاح
     */
    public function updatePayment(Request $request, $id)
    {
        // 1. التحقق من المدخلات
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes'  => 'nullable|string',
        ]);

        // 2. إيجاد وتحديث الدفعة
        $payment = AgentPayment::findOrFail($id);
        $payment->update($validated);

        // إعادة تحميل العلاقات الخاصة بـ Agent
    $agent = $payment->agent;
    $agent->load('payments', 'bookings'); // إعادة تحميل العلاقات للتأكد من تحديث القيم الديناميكية

        // 4. إعادة التوجيه مع إشعار نجاح
        return redirect()->route('reports.agent.payments', $agent->id)
                         ->with('success', 'تم تعديل الدفعة بنجاح!');
    }

    /**
     * editCompanyPayment
     * يعرض نموذج تعديل دفعة شركة
     */
    public function editCompanyPayment($id)
    {
        $payment = Payment::findOrFail($id);
        return view('reports.edit_company_payment', compact('payment'));
    }

    /**
     * updateCompanyPayment
     * يعالج تعديل دفعة شركة
     */
    public function updateCompanyPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $payment = Payment::findOrFail($id);
        $payment->update([
            'amount'       => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'notes'        => $validated['notes'],
        ]);

        return redirect()
            ->route('reports.company.payments', $payment->company_id)
            ->with('success', 'تم تعديل دفعة الشركة بنجاح!');
    }

    /**
     * حذف دفعة شركة مع ترجيع المبالغ إلى الحجوزات
     */
    public function destroyCompanyPayment($id)
    {
        $payment = Payment::findOrFail($id);
        $remaining = $payment->amount;
        $bookingIds = is_array($payment->bookings_covered)
    ? $payment->bookings_covered
    : json_decode($payment->bookings_covered, true) ?? [];

        // نعيد توزيع الحذف بنفس المنطق المطبق عند الإضافة
        Booking::whereIn('id', $bookingIds)
            ->orderBy('check_in')
            ->get()
            ->each(function (Booking $b) use (&$remaining, $payment) {
                if ($remaining <= 0) return;
                // ما دفعت الشركة فعلاً لهذا الحجز الآن
                $paid = $b->amount_paid_by_company;
                // نحسب الحصة المسددة من هذه الدفعة
                $due = $b->amount_due_from_company - ($paid - min($payment->amount, $paid));
                $pay = min(min($payment->amount, $paid), $remaining);
                $b->decrement('amount_paid_by_company', $pay);
                $remaining -= $pay;
            });

        // نحذف سجل الدفعة
        $companyId = $payment->company_id;
        $payment->delete();

        return redirect()
            ->route('reports.company.payments', $companyId)
            ->with('success', 'تم حذف الدفعة وإرجاع المبالغ المرتبطة بها.');
    }

    /**
     * حذف دفعة وكيل
     */
    public function destroyAgentPayment($id)
    {
        $payment = AgentPayment::findOrFail($id);
        $agentId = $payment->agent_id;
        // عندنا حسابات الوكيل ديناميكي بناء على السجلات → يكفي حذف السجل
        $payment->delete();

        return redirect()
            ->route('reports.agent.payments', $agentId)
            ->with('success', 'تم حذف دفعة الوكيل بنجاح.');
    }

    /**
     * عرض دفعة شركة
     */
    public function showCompanyPayment($id)
    {
        $payment = Payment::findOrFail($id);
        // bookings_covered صار array بفضل $casts
        return view('reports.show_company_payment', compact('payment'));
    }
}
