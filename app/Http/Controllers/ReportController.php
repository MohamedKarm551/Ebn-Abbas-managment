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
        $today = Carbon::today();
        
        $todayBookings = Booking::whereDate('check_in', $today)->get();
        
        $companiesReport = Company::withCount('bookings')
            ->get()
            ->map(function ($company) {
                // Calculate total due from company's bookings
                $totalDue = $company->bookings->sum(function ($booking) {
                    return $booking->sale_price * $booking->rooms * $booking->days;
                });
                
                // Get total paid from payments
                $totalPaid = $company->payments()->sum('amount');
                
                $company->total_due = $totalDue;
                $company->total_paid = $totalPaid;
                return $company;
            });

        // Add agents report calculation
        $agentsReport = Agent::withCount('bookings')
        ->get()
        ->map(function ($agent) {
            // حساب إجمالي المبالغ
            $totalAmount = $agent->bookings->sum(function ($booking) {
                return $booking->sale_price * $booking->rooms * $booking->days;
            });
            
            // حساب إجمالي المدفوع
            $totalPaid = $agent->payments()->sum('amount');
            
            $agent->total_amount = $totalAmount;
            $agent->total_paid = $totalPaid;
            return $agent;
        });
        $totalDueFromCompanies = $companiesReport->sum('total_due');
        $totalPaidToHotels = Booking::get()->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });
// إضافة حسابات الفنادق
$hotelsReport = Hotel::withCount('bookings')
->get()
->map(function ($hotel) {
    $totalDue = $hotel->bookings->sum(function ($booking) {
        return $booking->cost_price * $booking->rooms * $booking->days;
    });
    
    $hotel->total_due = $totalDue;
    return $hotel;
});

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
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $company = Company::findOrFail($validated['company_id']);
        
        // نجيب كل الحجوزات بتاعت الشركة ونحسب المبالغ المستحقة والمدفوعة
        $bookings = $company->bookings()
            ->orderBy('check_in')
            ->get();

        $remainingAmount = $validated['amount'];
        $coveredBookings = [];

        foreach ($bookings as $booking) {
            // نحسب المبلغ المستحق للحجز ده
            $dueAmount = $booking->sale_price * $booking->rooms * $booking->days;
            
            // نشوف المدفوع قبل كده
            $paidAmount = Payment::whereJsonContains('bookings_covered', $booking->id)
                                ->sum('amount');
            
            // لو فيه مبلغ متبقي
            $unpaidAmount = $dueAmount - $paidAmount;
            if ($unpaidAmount > 0) {
                if ($remainingAmount <= 0) break;

                $paymentForThisBooking = min($remainingAmount, $unpaidAmount);
                $remainingAmount -= $paymentForThisBooking;
                $coveredBookings[] = $booking->id;
            }
        }

        // نسجل الدفعة
        Payment::create([
            'company_id' => $validated['company_id'],
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'notes' => $validated['notes'],
            'bookings_covered' => $coveredBookings
        ]);

        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function storeAgentPayment(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $payment = AgentPayment::create([
            'agent_id' => $validated['agent_id'],
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'notes' => $validated['notes']
        ]);

        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }
}