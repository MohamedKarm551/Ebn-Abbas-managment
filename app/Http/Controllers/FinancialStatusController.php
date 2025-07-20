<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Booking;
use App\Models\BookingFinancialTracking;
use App\Models\Company;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\AgentPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FinancialStatusController extends Controller
{
    /**
     * عرض صفحة مخطط الحالة المالية
     * هذه الدالة تعيد العرض (View) الخاص بالتقرير المالي
     */
    public function index()
    {
        // إرجاع العرض 'reports.financial' بدون بيانات مباشرة، يتم تحميل البيانات عبر AJAX
        return view('reports.financial');
    }

    /**
     * الحصول على بيانات الحالة المالية للرسم البياني
     * هذه الدالة تُرجع بيانات JSON تحتوي على الإحصائيات والمخططات بناءً على الفلاتر
     */
    public function getFinancialData(Request $request)
    {
        try {
            // معالجة نطاق التاريخ بناءً على الفلتر المحدد من الطلب
            $dateRange = $this->getDateRange($request);
            $startDate = $dateRange['start_date'];
            $endDate = $dateRange['end_date'];
            Log::info('نطاق التاريخ المحدد:', ['start_date' => $startDate, 'end_date' => $endDate]);

            // الحصول على الحجوزات المفلترة بناءً على الشروط
            $bookings = $this->getFilteredBookings($request, $startDate, $endDate);
            Log::info('عدد الحجوزات المسترجعة:', ['count' => $bookings->count(), 'bookings' => $bookings->toArray()]);

            // إعداد بيانات المخطط الشبكي
            $graphData = $this->prepareGraphData($bookings);
            Log::info('بيانات المخطط الشبكي:', ['nodes_count' => count($graphData['nodes']), 'links_count' => count($graphData['links'])]);

            // إعداد الإحصائيات
            $statistics = $this->prepareStatistics($bookings);
            Log::info('الإحصائيات:', $statistics);

            // إضافة التدفقات المالية
            $financialFlows = $this->prepareFinancialFlows($startDate, $endDate);
            Log::info('التدفقات المالية:', ['currencies' => array_keys($financialFlows)]);

            // إرجاع الاستجابة بتنسيق JSON
            return response()->json([
                'graph' => $graphData,
                'statistics' => $statistics,
                'financial_flows' => $financialFlows,
                'date_range' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            // تسجيل الخطأ لتحليل المشكلة
            Log::error('خطأ في الحصول على بيانات الحالة المالية: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            // إرجاع استجابة خطأ بتنسيق JSON
            return response()->json([
                'error' => 'حدث خطأ أثناء معالجة البيانات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على تفاصيل الحالة المالية لحجز محدد
     * هذه الدالة ترجع تفاصيل مالية لحجز معين بناءً على المعرف
     */
    public function getBookingFinancialDetails($id)
    {
        try {
            // البحث عن الحجز مع جميع العلاقات المرتبطة
            $booking = Booking::with(['company', 'agent', 'hotel', 'financialTracking', 'payments', 'agentPayments'])
                ->findOrFail($id);
            Log::info('تفاصيل الحجز المسترجع:', ['booking_id' => $id, 'data' => $booking->toArray()]);

            // استخراج معلومات التتبع المالي أو إنشاء كائن وهمي
            $financialTracking = $booking->financialTracking ?: new BookingFinancialTracking();

            // حساب المبالغ المستحقة والمدفوعة للشركة
            $companyAmountDue = $booking->amount_due_from_company ?: 0;
            $companyAmountPaid = $booking->payments->sum('amount') ?: 0;
            $companyAmountRemaining = max(0, $companyAmountDue - $companyAmountPaid);
            $companyPaymentStatus = $financialTracking->company_payment_status ?? $this->determinePaymentStatus($companyAmountPaid, $companyAmountDue);

            // حساب المبالغ المستحقة والمدفوعة للوكيل
            $agentAmountDue = $booking->amount_due_to_hotel ?: 0;
            $agentAmountPaid = $booking->agentPayments->sum('amount') ?: 0;
            $agentAmountRemaining = max(0, $agentAmountDue - $agentAmountPaid);
            $agentPaymentStatus = $financialTracking->agent_payment_status ?? $this->determinePaymentStatus($agentAmountPaid, $agentAmountDue);

            // حساب الإجماليات
            $totalDue = $companyAmountDue + $agentAmountDue;
            $totalPaid = $companyAmountPaid + $agentAmountPaid;
            $totalRemaining = $companyAmountRemaining + $agentAmountRemaining;

            // تحديد حالة الدفع الإجمالية والنسبة المئوية
            $paymentStatus = $this->combinePaymentStatus($companyPaymentStatus, $agentPaymentStatus);
            $paymentPercentage = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0;

            // حساب الأرباح
            $profitPerNight = round(($booking->sale_price - $booking->cost_price) * $booking->rooms, 2);
            $totalProfit = round(($booking->sale_price - $booking->cost_price) * $booking->rooms * $booking->days, 2);

            // معلومات المتابعة المالية
            $followUpDate = $financialTracking->follow_up_date;
            $paymentDeadline = $financialTracking->payment_deadline;
            $priorityLevel = $financialTracking->priority_level ?? 'medium';

            // جمع دفعات الشركة
            $companyPayments = $booking->payments()
                ->orderBy('payment_date', 'desc')
                ->take(5)
                ->get();

            // جمع دفعات الوكيل
            $agentPayments = $booking->agent
                ? AgentPayment::where('booking_id', $booking->id)
                ->where('agent_id', $booking->agent_id)
                ->orderBy('payment_date', 'desc')
                ->take(5)
                ->get()
                : collect([]);

            // إعداد البيانات للرد
            $response = [
                'booking_id' => $booking->id,
                'client_name' => $booking->client_name,
                'voucher_number' => $booking->voucher_number ?? $booking->id,
                'check_in' => $booking->check_in ? $booking->check_in->format('Y-m-d') : null,
                'check_out' => $booking->check_out ? $booking->check_out->format('Y-m-d') : null,
                'rooms' => $booking->rooms,
                'days' => $booking->days,
                'currency' => $booking->currency,
                'cost_price' => $booking->cost_price,
                'sale_price' => $booking->sale_price,
                'payment_status' => $paymentStatus,
                'payment_percentage' => $paymentPercentage,
                'amount_due' => $totalDue,
                'amount_paid' => $totalPaid,
                'amount_remaining' => $totalRemaining,
                'profit_per_night' => $profitPerNight,
                'total_profit' => $totalProfit,
                'company' => $booking->company,
                'company_payment_status' => $companyPaymentStatus,
                'company_amount_due' => $companyAmountDue,
                'company_amount_paid' => $companyAmountPaid,
                'company_amount_remaining' => $companyAmountRemaining,
                'company_payments' => $companyPayments,
                'agent' => $booking->agent,
                'agent_payment_status' => $agentPaymentStatus,
                'agent_amount_due' => $agentAmountDue,
                'agent_amount_paid' => $agentAmountPaid,
                'agent_amount_remaining' => $agentAmountRemaining,
                'agent_payments' => $agentPayments,
                'follow_up_date' => $followUpDate,
                'payment_deadline' => $paymentDeadline,
                'priority_level' => $priorityLevel,
                'hotel' => $booking->hotel,
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على تفاصيل الحجز: ' . $e->getMessage(), [
                'booking_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'حدث خطأ أثناء تحميل تفاصيل الحجز: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * دمج حالتي دفع الشركة والوكيل للحصول على حالة الدفع الإجمالية
     */
    private function combinePaymentStatus($companyStatus, $agentStatus)
    {
        if ($companyStatus === 'fully_paid' && $agentStatus === 'fully_paid') {
            return 'fully_paid';
        } elseif ($companyStatus === 'not_paid' && $agentStatus === 'not_paid') {
            return 'not_paid';
        } else {
            return 'partially_paid';
        }
    }

    /**
     * الحصول على تدفقات الأموال
     */
    private function prepareFinancialFlows($startDate, $endDate)
    {
        // دفعات الشركات (إيرادات)
        $companyPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->select(
                'payment_date as date',
                'currency',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('payment_date', 'currency')
            ->get();

        // دفعات الوكلاء (مصروفات)
        $agentPayments = AgentPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->select(
                'payment_date as date',
                'currency',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('payment_date', 'currency')
            ->get();

        // تجميع البيانات حسب العملة والتاريخ
        $flows = [
            'SAR' => [
                'dates' => [],
                'income' => [],
                'expenses' => [],
                'net' => [],
            ],
            'KWD' => [
                'dates' => [],
                'income' => [],
                'expenses' => [],
                'net' => [],
            ]
        ];

        // ملء مصفوفة التواريخ بجميع أيام الفترة
        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate->modify('+1 day')
        );

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $flows['SAR']['dates'][] = $dateStr;
            $flows['SAR']['income'][$dateStr] = 0;
            $flows['SAR']['expenses'][$dateStr] = 0;
            $flows['SAR']['net'][$dateStr] = 0;

            $flows['KWD']['dates'][] = $dateStr;
            $flows['KWD']['income'][$dateStr] = 0;
            $flows['KWD']['expenses'][$dateStr] = 0;
            $flows['KWD']['net'][$dateStr] = 0;
        }

        // إضافة دفعات الشركات (الإيرادات)
        foreach ($companyPayments as $payment) {
            $dateStr = Carbon::parse($payment->date)->format('Y-m-d');
            $currency = $payment->currency ?: 'SAR';

            if (isset($flows[$currency]['income'][$dateStr])) {
                $flows[$currency]['income'][$dateStr] += $payment->total;
                $flows[$currency]['net'][$dateStr] += $payment->total;
            }
        }

        // إضافة دفعات الوكلاء (المصروفات)
        foreach ($agentPayments as $payment) {
            $dateStr = Carbon::parse($payment->date)->format('Y-m-d');
            $currency = $payment->currency ?: 'SAR';

            if (isset($flows[$currency]['expenses'][$dateStr])) {
                $flows[$currency]['expenses'][$dateStr] += $payment->total;
                $flows[$currency]['net'][$dateStr] -= $payment->total;
            }
        }

        // تحويل المصفوفات إلى ترتيب زمني
        foreach (['SAR', 'KWD'] as $currency) {
            $orderedDates = $flows[$currency]['dates'];
            $orderedIncome = [];
            $orderedExpenses = [];
            $orderedNet = [];

            foreach ($orderedDates as $date) {
                $orderedIncome[] = $flows[$currency]['income'][$date];
                $orderedExpenses[] = $flows[$currency]['expenses'][$date];
                $orderedNet[] = $flows[$currency]['net'][$date];
            }

            $flows[$currency]['income'] = $orderedIncome;
            $flows[$currency]['expenses'] = $orderedExpenses;
            $flows[$currency]['net'] = $orderedNet;
        }

        return $flows;
    }

    /**
     * الحصول على نطاق التاريخ بناءً على الفلتر المحدد
     */
    private function getDateRange(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'week');
        $now = Carbon::now();

        switch ($dateFilter) {
            case 'today':
                return [
                    'start_date' => $now->copy()->startOfDay(),
                    'end_date' => $now->copy()->endOfDay(),
                ];
            case 'yesterday':
                return [
                    'start_date' => $now->copy()->subDay()->startOfDay(),
                    'end_date' => $now->copy()->subDay()->endOfDay(),
                ];
            case 'week':
                return [
                    'start_date' => $now->copy()->startOfWeek(),
                    'end_date' => $now->copy()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start_date' => $now->copy()->startOfMonth(),
                    'end_date' => $now->copy()->endOfMonth(),
                ];
            case 'custom':
                return [
                    'start_date' => Carbon::parse($request->input('start_date', $now->copy()->subDays(7)->format('Y-m-d'))),
                    'end_date' => Carbon::parse($request->input('end_date', $now->format('Y-m-d'))),
                ];
            default:
                return [
                    'start_date' => $now->copy()->subDays(7),
                    'end_date' => $now,
                ];
        }
    }

    /**
     * الحصول على الحجوزات المفلترة مع معلومات الدفع
     */
    private function getFilteredBookings(Request $request, $startDate, $endDate)
    {
        $query = Booking::with(['company', 'agent', 'hotel', 'financialTracking'])
            ->where(function ($q) use ($startDate, $endDate) {
                // الحجوزات التي تم إنشاؤها في الفترة
                $q->whereBetween('created_at', [$startDate, $endDate])
                    // أو الحجوزات التي تبدأ أو تنتهي في الفترة
                    ->orWhereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate])
                    // أو الحجوزات النشطة خلال الفترة
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('check_in', '<=', $startDate)
                            ->where('check_out', '>=', $endDate);
                    });
            });
        Log::info('استعلام الحجوزات الخام:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        // تطبيق الفلاتر الإضافية
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->input('agent_id'));
        }

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->input('currency'));
        }

        // تطبيق فلتر حالة الدفع
        if ($request->filled('payment_status')) {
            $paymentStatus = $request->input('payment_status');

            $query->whereHas('financialTracking', function ($q) use ($paymentStatus) {
                switch ($paymentStatus) {
                    case 'fully_paid':
                        $q->where('agent_payment_status', 'fully_paid')
                            ->where('company_payment_status', 'fully_paid');
                        break;
                    case 'partially_paid':
                        $q->where(function ($q2) {
                            $q2->where('agent_payment_status', 'partially_paid')
                                ->orWhere('company_payment_status', 'partially_paid')
                                ->orWhere(function ($q3) {
                                    $q3->where('agent_payment_status', 'fully_paid')
                                        ->where('company_payment_status', 'not_paid');
                                })
                                ->orWhere(function ($q3) {
                                    $q3->where('agent_payment_status', 'not_paid')
                                        ->where('company_payment_status', 'fully_paid');
                                });
                        });
                        break;
                    case 'not_paid':
                        $q->where('agent_payment_status', 'not_paid')
                            ->where('company_payment_status', 'not_paid');
                        break;
                }
            });
        }

        // فلتر الأولوية
        if ($request->filled('priority_level')) {
            $query->whereHas('financialTracking', function ($q) use ($request) {
                $q->where('priority_level', $request->input('priority_level'));
            });
        }

        // تحديد حد لعدد النتائج
        $limit = $request->input('limit', 50);
        $bookings = $query->take($limit)->get();
        Log::info('الحجوزات بعد التنفيذ:', ['count' => $bookings->count(), 'data' => $bookings->toArray()]);

        // إضافة معلومات المبالغ المدفوعة والمستحقة
        foreach ($bookings as $booking) {
            $booking->company_amount_paid = $booking->payments->sum('amount');
            $booking->company_amount_remaining = max(0, $booking->amount_due_from_company - $booking->company_amount_paid);
            $booking->agent_amount_paid = $booking->agentPayments->sum('amount');
            $booking->agent_amount_remaining = max(0, $booking->amount_due_to_hotel - $booking->agent_amount_paid);

            if ($booking->financialTracking) {
                $booking->agent_payment_status = $booking->financialTracking->agent_payment_status;
                $booking->company_payment_status = $booking->financialTracking->company_payment_status;
                $booking->priority_level = $booking->financialTracking->priority_level;
            } else {
                $booking->agent_payment_status = $this->determinePaymentStatus(
                    $booking->agent_amount_paid,
                    $booking->amount_due_to_hotel
                );
                $booking->company_payment_status = $this->determinePaymentStatus(
                    $booking->company_amount_paid,
                    $booking->amount_due_from_company
                );
                $booking->priority_level = 'medium';
            }

            $booking->payment_status = $this->combinePaymentStatus(
                $booking->company_payment_status,
                $booking->agent_payment_status
            );

            $totalDue = ($booking->amount_due_from_company ?: 0) + ($booking->amount_due_to_hotel ?: 0);
            $totalPaid = $booking->company_amount_paid + $booking->agent_amount_paid;
            $booking->payment_percentage = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0;
        }

        return $bookings;
    }

    /**
     * تحضير بيانات المخطط الشبكي
     */
    private function prepareGraphData(Collection $bookings)
    {
        $nodes = [];
        $links = [];
        $uniqueIds = [];

        // إضافة العقدة المركزية لجميع الحجوزات
        $nodes[] = [
            'id' => 'center_all',
            'name' => 'جميع الحجوزات',
            'type' => 'center',
            'size' => 15,
        ];

        foreach ($bookings as $booking) {
            $companyId = $booking->company_id ?? 0;
            $agentId = $booking->agent_id ?? 0;
            $hotelId = $booking->hotel_id ?? 0;

            $companyPaymentStatus = $booking->company_payment_status;
            $agentPaymentStatus = $booking->agent_payment_status;
            $paymentStatus = $booking->payment_status;
            $paymentPercentage = $booking->payment_percentage;

            $companyAmountRemaining = $booking->company_amount_remaining;
            $agentAmountRemaining = $booking->agent_amount_remaining;
            $totalRemaining = $companyAmountRemaining + $agentAmountRemaining;

            $nodeSize = min(max($booking->rooms * 1.5, 5), 15);

            $bookingNodeId = 'booking_' . $booking->id;
            $bookingNodeType = 'booking_' . $paymentStatus;
            $nodes[] = [
                'id' => $bookingNodeId,
                'name' => $booking->client_name,
                'type' => $bookingNodeType,
                'booking_id' => $booking->id,
                'check_in' => optional($booking->check_in)->format('Y-m-d'),
                'check_out' => optional($booking->check_out)->format('Y-m-d'),
                'rooms' => $booking->rooms,
                'currency' => $booking->currency,
                'amount_due' => ($booking->amount_due_from_company ?: 0) + ($booking->amount_due_to_hotel ?: 0),
                'amount_paid' => $booking->company_amount_paid + $booking->agent_amount_paid,
                'amount_remaining' => $totalRemaining,
                'payment_status' => $paymentStatus,
                'payment_percentage' => $paymentPercentage,
                'agent_payment_status' => $agentPaymentStatus,
                'company_payment_status' => $companyPaymentStatus,
                'priority_level' => $booking->priority_level,
                'size' => $nodeSize,
                'company_name' => $booking->company->name ?? '-',
                'agent_name' => $booking->agent->name ?? '-',
                'hotel_name' => $booking->hotel->name ?? '-',
            ];

            $links[] = ['source' => 'center_all', 'target' => $bookingNodeId, 'value' => 1];

            if ($companyId && !isset($uniqueIds['company_' . $companyId])) {
                $nodes[] = [
                    'id' => 'company_' . $companyId,
                    'name' => $booking->company->name ?? 'شركة غير معروفة',
                    'type' => 'company',
                    'company_id' => $companyId,
                    'size' => 10,
                ];
                $uniqueIds['company_' . $companyId] = true;
            }

            if ($agentId && !isset($uniqueIds['agent_' . $agentId])) {
                $nodes[] = [
                    'id' => 'agent_' . $agentId,
                    'name' => $booking->agent->name ?? 'جهة حجز غير معروفة',
                    'type' => 'agent',
                    'agent_id' => $agentId,
                    'size' => 10,
                ];
                $uniqueIds['agent_' . $agentId] = true;
            }

            if ($hotelId && !isset($uniqueIds['hotel_' . $hotelId])) {
                $nodes[] = [
                    'id' => 'hotel_' . $hotelId,
                    'name' => $booking->hotel->name ?? 'فندق غير معروف',
                    'type' => 'hotel',
                    'hotel_id' => $hotelId,
                    'size' => 10,
                ];
                $uniqueIds['hotel_' . $hotelId] = true;
            }

            if ($companyId) {
                $links[] = [
                    'source' => $bookingNodeId,
                    'target' => 'company_' . $companyId,
                    'payment_status' => $companyPaymentStatus,
                    'amount_due' => $booking->amount_due_from_company ?: 0,
                    'amount_paid' => $booking->company_amount_paid,
                    'amount_remaining' => $companyAmountRemaining,
                    'value' => ($booking->amount_due_from_company ?: 0) > 0 ? sqrt(($booking->amount_due_from_company ?: 0)) / 10 + 1 : 1,
                ];
            }

            if ($agentId) {
                $links[] = [
                    'source' => $bookingNodeId,
                    'target' => 'agent_' . $agentId,
                    'payment_status' => $agentPaymentStatus,
                    'amount_due' => $booking->amount_due_to_hotel ?: 0,
                    'amount_paid' => $booking->agent_amount_paid,
                    'amount_remaining' => $agentAmountRemaining,
                    'value' => ($booking->amount_due_to_hotel ?: 0) > 0 ? sqrt(($booking->amount_due_to_hotel ?: 0)) / 10 + 1 : 1,
                ];
            }

            if ($hotelId) {
                $links[] = [
                    'source' => $bookingNodeId,
                    'target' => 'hotel_' . $hotelId,
                    'value' => 1,
                ];
            }
        }

        $this->addPaymentStatusClusters($nodes, $links, $bookings);

        return [
            'nodes' => $nodes,
            'links' => $links,
        ];
    }

    /**
     * إضافة تجميعات حسب حالات الدفع
     */
    private function addPaymentStatusClusters(&$nodes, &$links, $bookings)
    {
        $nodes[] = ['id' => 'cluster_fully_paid', 'name' => 'مدفوع بالكامل', 'type' => 'cluster', 'size' => 12];
        $nodes[] = ['id' => 'cluster_partially_paid', 'name' => 'مدفوع جزئياً', 'type' => 'cluster', 'size' => 12];
        $nodes[] = ['id' => 'cluster_not_paid', 'name' => 'غير مدفوع', 'type' => 'cluster', 'size' => 12];

        $links[] = ['source' => 'center_all', 'target' => 'cluster_fully_paid', 'value' => 3];
        $links[] = ['source' => 'center_all', 'target' => 'cluster_partially_paid', 'value' => 3];
        $links[] = ['source' => 'center_all', 'target' => 'cluster_not_paid', 'value' => 3];

        foreach ($bookings as $booking) {
            $paymentStatus = $booking->payment_status;
            $clusterTarget = 'cluster_' . $paymentStatus;

            $links[] = [
                'source' => 'booking_' . $booking->id,
                'target' => $clusterTarget,
                'value' => 2,
                'payment_status' => $paymentStatus
            ];
        }
    }

    /**
     * تحديد حالة الدفع بناءً على المبالغ
     */
    private function determinePaymentStatus($paid, $due)
    {
        if ($due <= 0) return 'fully_paid';

        if ($paid >= $due * 0.99) return 'fully_paid';
        if ($paid > 0) return 'partially_paid';
        return 'not_paid';
    }

    /**
     * تحضير الإحصائيات
     */
    private function prepareStatistics(Collection $bookings)
    {
        $totalBookings = $bookings->count();

        $fullyPaidBookings = $bookings->where('payment_status', 'fully_paid')->count();
        $partiallyPaidBookings = $bookings->where('payment_status', 'partially_paid')->count();
        $notPaidBookings = $bookings->where('payment_status', 'not_paid')->count();

        $financialByCurrency = [];
        $priorityStats = ['high' => 0, 'medium' => 0, 'low' => 0];

        foreach ($bookings as $booking) {
            $priority = $booking->priority_level ?? 'medium';
            if (isset($priorityStats[$priority])) {
                $priorityStats[$priority]++;
            }

            $currency = $booking->currency ?: 'SAR';

            if (!isset($financialByCurrency[$currency])) {
                $financialByCurrency[$currency] = [
                    'total_due' => 0,
                    'total_paid' => 0,
                    'total_remaining' => 0,
                ];
            }

            $totalDue = ($booking->amount_due_from_company ?: 0) + ($booking->amount_due_to_hotel ?: 0);
            $totalPaid = $booking->company_amount_paid + $booking->agent_amount_paid;
            $totalRemaining = $booking->company_amount_remaining + $booking->agent_amount_remaining;

            $financialByCurrency[$currency]['total_due'] += $totalDue;
            $financialByCurrency[$currency]['total_paid'] += $totalPaid;
            $financialByCurrency[$currency]['total_remaining'] += $totalRemaining;
        }

        $profitStats = $this->calculateProfitStats($bookings);

        return [
            'total_bookings' => $totalBookings,
            'fully_paid_bookings' => $fullyPaidBookings,
            'partially_paid_bookings' => $partiallyPaidBookings,
            'not_paid_bookings' => $notPaidBookings,
            'financial' => $financialByCurrency,
            'priority_stats' => $priorityStats,
            'profit_stats' => $profitStats,
        ];
    }

    /**
     * حساب إحصائيات الربحية
     */
    private function calculateProfitStats(Collection $bookings)
    {
        $stats = [
            'SAR' => ['total_profit' => 0, 'realized_profit' => 0, 'unrealized_profit' => 0],
            'KWD' => ['total_profit' => 0, 'realized_profit' => 0, 'unrealized_profit' => 0],
        ];

        foreach ($bookings as $booking) {
            $currency = $booking->currency ?: 'SAR';
            $profitPerRoom = ($booking->sale_price - $booking->cost_price) * $booking->days;
            $totalProfit = $profitPerRoom * $booking->rooms;

            $stats[$currency]['total_profit'] += $totalProfit;

            $companyAmountDue = $booking->amount_due_from_company ?: 0;
            if ($companyAmountDue > 0) {
                $paymentRatio = $booking->company_amount_paid / $companyAmountDue;
                $realizedProfit = $totalProfit * $paymentRatio;

                $stats[$currency]['realized_profit'] += $realizedProfit;
                $stats[$currency]['unrealized_profit'] += ($totalProfit - $realizedProfit);
            }
        }

        return $stats;
    }

    /**
     * الحصول على المدفوعات المتأخرة
     */
    public function getOverduePayments()
    {
        $today = Carbon::today();

        $overdueBookings = Booking::with(['company', 'agent', 'financialTracking'])
            ->whereHas('financialTracking', function ($query) use ($today) {
                $query->where('payment_deadline', '<', $today)
                    ->where(function ($q) {
                        $q->where('agent_payment_status', '!=', 'fully_paid')
                            ->orWhere('company_payment_status', '!=', 'fully_paid');
                    });
            })
            ->get();

        foreach ($overdueBookings as $booking) {
            $companyAmountDue = $booking->amount_due_from_company ?: 0;
            $companyAmountPaid = $booking->payments->sum('amount');
            $companyAmountRemaining = max(0, $companyAmountDue - $companyAmountPaid);

            $agentAmountDue = $booking->amount_due_to_hotel ?: 0;
            $agentAmountPaid = $booking->agentPayments->sum('amount');
            $agentAmountRemaining = max(0, $agentAmountDue - $agentAmountPaid);

            $booking->total_remaining = $companyAmountRemaining + $agentAmountRemaining;
            $booking->days_overdue = $today->diffInDays($booking->financialTracking->payment_deadline);
        }

        return response()->json([
            'overdue_count' => $overdueBookings->count(),
            'bookings' => $overdueBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'client_name' => $booking->client_name,
                    'voucher_number' => $booking->voucher_number ?? $booking->id,
                    'company_name' => $booking->company->name ?? 'غير معروف',
                    'agent_name' => $booking->agent->name ?? 'غير معروف',
                    'payment_deadline' => $booking->financialTracking->payment_deadline,
                    'days_overdue' => $booking->days_overdue,
                    'priority_level' => $booking->financialTracking->priority_level,
                    'amount_remaining' => $booking->total_remaining,
                    'currency' => $booking->currency
                ];
            })
        ]);
    }

    /**
     * الحصول على بيانات المتابعة المالية مباشرة من جدول booking_financial_tracking
     */
    public function getFinancialTrackingData(Request $request)
    {
        try {
            // استخراج نطاق التاريخ بناءً على الفلتر المحدد
            $dateRange = $this->getDateRange($request);
            $startDate = $dateRange['start_date'];
            $endDate = $dateRange['end_date'];

            // بناء الاستعلام الأساسي مع تحميل العلاقات
            $query = BookingFinancialTracking::with(['booking.company', 'booking.agent', 'booking.hotel'])
                ->whereHas('booking', function ($q) use ($startDate, $endDate) {
                    $q->whereNull('deleted_at') // تجاهل السجلات المؤرشفة
                        ->where(function ($q2) use ($startDate, $endDate) {
                            $q2->whereBetween('created_at', [$startDate, $endDate])
                                ->orWhereBetween('check_in', [$startDate, $endDate])
                                ->orWhereBetween('check_out', [$startDate, $endDate]);
                        })
                        ->where('sale_price', '>', 0) // استبعاد الحجوزات بدون سعر بيع
                        ->where('cost_price', '>', 0); // استبعاد الحجوزات بدون تكلفة
                });

            // تطبيق فلتر حالة الدفع إذا تم توفيره
            if ($request->filled('payment_status')) {
                $status = $request->input('payment_status');
                switch ($status) {
                    case 'fully_paid':
                        $query->where('company_payment_status', 'fully_paid')
                            ->where('agent_payment_status', 'fully_paid');
                        break;
                    case 'partially_paid':
                        $query->where(function ($q) {
                            $q->where('company_payment_status', 'partially_paid')
                                ->orWhere('agent_payment_status', 'partially_paid')
                                ->orWhere(function ($q2) {
                                    $q2->where('company_payment_status', 'fully_paid')
                                        ->where('agent_payment_status', '!=', 'fully_paid');
                                })
                                ->orWhere(function ($q2) {
                                    $q2->where('company_payment_status', '!=', 'fully_paid')
                                        ->where('agent_payment_status', 'fully_paid');
                                });
                        });
                        break;
                    case 'not_paid':
                        $query->where('company_payment_status', 'not_paid')
                            ->where('agent_payment_status', 'not_paid');
                        break;
                }
            }

            // تطبيق الفلاتر المتقدمة
            if ($request->filled('client_name')) {
                $query->whereHas('booking', function ($q) use ($request) {
                    $q->where('client_name', 'like', '%' . $request->input('client_name') . '%');
                });
            }

            if ($request->filled('company_name')) {
                $query->whereHas('booking.company', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('company_name') . '%');
                });
            }

            if ($request->filled('agent_name')) {
                $query->whereHas('booking.agent', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('agent_name') . '%');
                });
            }

            if ($request->filled('hotel_name')) {
                $query->whereHas('booking.hotel', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('hotel_name') . '%');
                });
            }

            if ($request->filled('currency')) {
                $query->whereHas('booking', function ($q) use ($request) {
                    $q->where('currency', $request->input('currency'));
                });
            }

            if ($request->filled('priority_level')) {
                $query->where('priority_level', $request->input('priority_level'));
            }

            // تنفيذ الاستعلام مع الترقيم
            $trackingData = $query->paginate(15);

            // حساب الإحصائيات الأساسية
            $stats = [
                'total' => $trackingData->count(),
                'fully_paid' => $trackingData->where('company_payment_status', 'fully_paid')
                    ->where('agent_payment_status', 'fully_paid')
                    ->count(),
                'not_paid' => $trackingData->where('company_payment_status', 'not_paid')
                    ->where('agent_payment_status', 'not_paid')
                    ->count(),
            ];
            $stats['partially_paid'] = $stats['total'] - $stats['fully_paid'] - $stats['not_paid'];

            // تهيئة البيانات النهائية للعرض
            $formattedData = $trackingData->map(function ($item) {
                return [
                    'id' => $item->id,
                    'booking_id' => $item->booking_id,
                    'client_name' => $item->booking->client_name ?? '-',
                    'company_name' => $item->booking->company->name ?? '-',
                    'agent_name' => $item->booking->agent->name ?? '-',
                    'hotel_name' => $item->booking->hotel->name ?? '-',
                    'check_in' => $item->booking->check_in ? $item->booking->check_in->format('Y-m-d') : '-',
                    'check_out' => $item->booking->check_out ? $item->booking->check_out->format('Y-m-d') : '-',
                    'company_payment_status' => $item->company_payment_status,
                    'agent_payment_status' => $item->agent_payment_status,
                    'company_payment_notes' => $item->company_payment_notes,          // ⭐️ أضِف هذا السطر
                    'agent_payment_notes'   => $item->agent_payment_notes,            // ⭐️ أضِف هذا السطر
                    'payment_deadline' => $item->payment_deadline ? $item->payment_deadline->format('Y-m-d') : null,
                    'follow_up_date' => $item->follow_up_date ? $item->follow_up_date->format('Y-m-d') : null,
                    'priority_level' => $item->priority_level ?? 'medium',
                    'notes' => $item->booking->notes ,
                    'updated_at' => $item->updated_at->format('Y-m-d H:i'),
                    'combined_status' => $this->combinePaymentStatus($item->company_payment_status, $item->agent_payment_status),
                ];
            });

            // إرجاع الاستجابة بتنسيق JSON
            return response()->json([
                'success' => true,
                'financial_tracking' => $formattedData,
                'statistics' => $stats,
                'pagination' => [
                    'current_page' => $trackingData->currentPage(),
                    'last_page' => $trackingData->lastPage(),
                    'per_page' => $trackingData->perPage(),
                    'total' => $trackingData->total(),
                    'from' => $trackingData->firstItem(),
                    'to' => $trackingData->lastItem(),
                    'links' => $trackingData->linkCollection(),
                ],
            ]);
        } catch (\Exception $e) {
            // تسجيل الخطأ لأغراض التصحيح
            Log::error('خطأ في الحصول على بيانات المتابعة المالية: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // إرجاع استجابة خطأ
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ أثناء معالجة البيانات: ' . $e->getMessage(),
            ], 500);
        }
    }
}
