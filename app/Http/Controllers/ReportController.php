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

class ReportController extends Controller
{
    public function daily()
    {
        $today = Carbon::today(); // ده بيجيب تاريخ النهارده عشان نستخدمه في الفلترة

        $todayBookings = Booking::whereDate('check_in', $today)->get(); // بيجيب الحجوزات اللي تاريخ الدخول بتاعها النهارده

        $companiesReport = Company::withCount('bookings') 
            ->get(); // بيجيب الشركات وعدد الحجوزات المرتبطة بكل شركة
           


        // نفس الفكرة مع جهات الحجز
        $agentsReport = Agent::withCount('bookings') // بيجيب جهات الحجز وعدد الحجوزات المرتبطة بكل جهة
            ->get();

             

        // حساب إجمالي المستحق من الشركات
        $totalDueFromCompanies = $companiesReport->sum('total_due');

        // حساب إجمالي المدفوع للفنادق
        $totalPaidToHotels = Booking::get()->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days; // تكلفة الحجز × عدد الغرف × عدد الأيام
        });

        // حساب إجمالي المستحق على الفنادق
        $hotelsReport = Hotel::withCount('bookings') // بيجيب الفنادق وعدد الحجوزات المرتبطة بكل فندق
            ->get();

             

        // رجّع البيانات للـ View
        return view('reports.daily', compact(
            'todayBookings',
            'companiesReport',
            'agentsReport',
            'hotelsReport',
            'totalDueFromCompanies',
            'totalPaidToHotels'
        ));
    }

    public function companyBookings($id)
    {
        $company = Company::findOrFail($id);
        $bookings = $company->bookings()
            ->with(['hotel', 'agent'])
            ->orderBy('check_in')
            ->get();

        return view('reports.company_bookings', compact('company', 'bookings'));
    }

    public function agentBookings($id)
    {
        $agent = Agent::findOrFail($id);
        $bookings = $agent->bookings()
            ->with(['hotel', 'company'])
            ->orderBy('check_in')
            ->get();

        return view('reports.agent_bookings', compact('agent', 'bookings'));
    }

    public function hotelBookings($id)
    {
        $hotel = Hotel::findOrFail($id);
        $bookings = Booking::where('hotel_id', $id)
            ->with(['company', 'agent'])
            ->get();

        return view('reports.hotel_bookings', [
            'hotel' => $hotel,
            'bookings' => $bookings
        ]);
    }

    public function storePayment(Request $request)
    {
        // التحقق من صحة البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id', // لازم الشركة تكون موجودة
            'amount' => 'required|numeric|min:0', // المبلغ لازم يكون رقم ومش أقل من صفر
            'notes' => 'nullable|string', // الملاحظات اختيارية
            'payment_date' => 'nullable|date', // تاريخ الدفع اختياري
        ]);

        // جلب بيانات الشركة
        $company = Company::findOrFail($validated['company_id']); // لو الشركة مش موجودة هيعمل خطأ

        
        // تسجيل الدفعة في جدول المدفوعات
        Payment::create([
            'company_id' => $validated['company_id'], // الشركة المرتبطة
            'amount' => $validated['amount'], // المبلغ المدفوع
            'notes' => $validated['notes'] ?? null, // الملاحظات
            'payment_date' => $validated['payment_date'] ?? now(), // تاريخ الدفع
        ]);

        // رجوع للصفحة مع رسالة نجاح
        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح!');
    }

    public function storeAgentPayment(Request $request)
    {
        // التحقق من صحة البيانات اللي جاية من الفورم
        $validated = $request->validate([
            'agent_id' => 'required|exists:agents,id', // لازم جهة الحجز تكون موجودة
            'amount' => 'required|numeric|min:0', // المبلغ لازم يكون رقم ومش أقل من صفر
            'notes' => 'nullable|string', // الملاحظات اختيارية
        ]);

        // تسجيل الدفعة في جدول AgentPayment
        $payment = AgentPayment::create([
            'agent_id' => $validated['agent_id'], // جهة الحجز المرتبطة
            'amount' => $validated['amount'], // المبلغ المدفوع
            'payment_date' => now(), // تاريخ الدفع
            'notes' => $validated['notes'], // الملاحظات
        ]);

        // رجوع للصفحة مع رسالة نجاح
        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    // عرض سجل المدفوعات للشركات
    public function companyPayments($id)
    {
        $company = Company::findOrFail($id);
        $payments = Payment::where('company_id', $id)->orderBy('payment_date', 'desc')->get();

        return view('reports.company_payments', compact('company', 'payments'));
    }

    // عرض سجل المدفوعات لجهات الحجز
    public function agentPayments($id)
    {
        $agent = Agent::findOrFail($id);
        $payments = AgentPayment::where('agent_id', $id)->orderBy('payment_date', 'desc')->get();

        return view('reports.agent_payments', compact('agent', 'payments'));
    }

    public function editPayment($id)
    {
        $payment = AgentPayment::findOrFail($id);
        return view('reports.edit_payment', compact('payment'));
    }

    public function updatePayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
            // جلب الدفعة وتحديثها
        $payment = AgentPayment::findOrFail($id);
        $payment->update($validated);

        // إعادة تحميل العلاقات الخاصة بـ Agent
    $agent = $payment->agent;
    $agent->load('payments', 'bookings'); // إعادة تحميل العلاقات للتأكد من تحديث القيم الديناميكية

    return redirect()->route('reports.agent.payments', $agent->id)
                     ->with('success', 'تم تعديل الدفعة بنجاح!');
    }
}
