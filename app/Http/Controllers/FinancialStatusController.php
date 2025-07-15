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

class FinancialStatusController extends Controller
{
    /**
     * عرض صفحة مخطط الحالة المالية
     */
    public function index()
    {
        return view('reports.financial');
    }

    /**
     * الحصول على بيانات الحالة المالية للرسم البياني
     */
    public function getFinancialData(Request $request)
    {
        // معالجة فلاتر التاريخ
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start_date'];
        $endDate = $dateRange['end_date'];

        // الحصول على الحجوزات بناءً على المعايير
        $bookings = $this->getFilteredBookings($request, $startDate, $endDate);

        // إعداد بيانات المخطط الشبكي
        $graphData = $this->prepareGraphData($bookings);

        // إعداد الإحصائيات
        $statistics = $this->prepareStatistics($bookings);

        // إضافة التدفقات المالية (جديد)
        $financialFlows = $this->prepareFinancialFlows($startDate, $endDate);

        return response()->json([
            'graph' => $graphData,
            'statistics' => $statistics,
            'financial_flows' => $financialFlows,
            'date_range' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * الحصول على تفاصيل الحالة المالية لحجز محدد
     */
    public function getBookingFinancialDetails($id)
    {
        $booking = Booking::with(['company', 'agent', 'hotel', 'financialTracking', 'payments', 'agentPayments'])->findOrFail($id);

        $financialTracking = $booking->financialTracking ?: new BookingFinancialTracking();

        // بيانات الشركة
        $companyPaymentStatus = $financialTracking->company_payment_status ?? 'not_paid';
        $companyAmountDue = $booking->amount_due_from_company ?: 0;
        $companyAmountPaid = $booking->company_amount_paid ?: 0;
        $companyAmountRemaining = max(0, $companyAmountDue - $companyAmountPaid);

        // بيانات جهة الحجز
        $agentPaymentStatus = $financialTracking->agent_payment_status ?? 'not_paid';
        $agentAmountDue = $booking->amount_due_to_hotel ?: 0;
        $agentAmountPaid = $booking->agent_amount_paid ?: 0;
        $agentAmountRemaining = max(0, $agentAmountDue - $agentAmountPaid);

        // الإجماليات
        $totalDue = $companyAmountDue + $agentAmountDue;
        $totalPaid = $companyAmountPaid + $agentAmountPaid;
        $totalRemaining = $companyAmountRemaining + $agentAmountRemaining;

        // تحديد حالة الدفع الإجمالية
        $paymentStatus = $this->determinePaymentStatus($totalPaid, $totalDue);

        // حساب النسبة المئوية للدفع
        $paymentPercentage = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0;

        // تفاصيل الدفعات (جديد)
        $companyPayments = $booking->payments()->orderBy('payment_date', 'desc')->take(5)->get();
        $agentPayments = $booking->agent ? $booking->agent->agentPayments()->orderBy('payment_date', 'desc')->take(5)->get() : [];

        // تاريخ المتابعة المالية (جديد)
        $followUpDate = $financialTracking->follow_up_date;
        $paymentDeadline = $financialTracking->payment_deadline;
        $priorityLevel = $financialTracking->priority_level ?? 'medium';

        return response()->json([
            'booking_id' => $booking->id,
            'client_name' => $booking->client_name,
            'voucher_number' => $booking->voucher_number,
            'check_in' => $booking->check_in,
            'check_out' => $booking->check_out,
            'rooms' => $booking->rooms,
            'currency' => $booking->currency,
            'days' => $booking->days,
            'cost_price' => $booking->cost_price,
            'sale_price' => $booking->sale_price,
            'payment_status' => $paymentStatus,
            'payment_percentage' => $paymentPercentage,
            'amount_due' => $totalDue,
            'amount_paid' => $totalPaid,
            'amount_remaining' => $totalRemaining,
            'profit_per_night' => round(($booking->sale_price - $booking->cost_price) * $booking->rooms, 2),
            'total_profit' => round(($booking->sale_price - $booking->cost_price) * $booking->rooms * $booking->days, 2),
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
        ]);
    }

    /**
     * الحصول على تدفقات الأموال (جديد)
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
                $flows[$currency]['net'][$dateStr] -= $payment->total; // نطرح المصروفات من صافي الربح
            }
        }

        // تحويل مصفوفات الإيرادات والمصروفات إلى مصفوفات مرتبة
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
     * الحصول على الحجوزات المفلترة
     */
    private function getFilteredBookings(Request $request, $startDate, $endDate)
    {
        $query = Booking::with(['company', 'agent', 'hotel', 'financialTracking'])
            ->where(function ($q) use ($startDate, $endDate) {
                // الحجوزات التي تم إنشاؤها في الفترة المحددة
                $q->whereBetween('created_at', [$startDate, $endDate])
                    // أو الحجوزات التي تبدأ أو تنتهي في الفترة المحددة
                    ->orWhereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate])
                    // أو الحجوزات النشطة خلال الفترة المحددة
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('check_in', '<=', $startDate)
                            ->where('check_out', '>=', $endDate);
                    });
            });

        // تطبيق الفلاتر الإضافية
        if ($request->has('company_id') && $request->input('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->has('agent_id') && $request->input('agent_id')) {
            $query->where('agent_id', $request->input('agent_id'));
        }

        if ($request->has('hotel_id') && $request->input('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }

        if ($request->has('currency') && $request->input('currency')) {
            $query->where('currency', $request->input('currency'));
        }

        // تطبيق فلتر حالة الدفع
        if ($request->has('payment_status') && $request->input('payment_status')) {
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

        // فلتر الأولوية (جديد)
        if ($request->has('priority_level') && $request->input('priority_level')) {
            $query->whereHas('financialTracking', function ($q) use ($request) {
                $q->where('priority_level', $request->input('priority_level'));
            });
        }

        // تحديد حد لعدد النتائج
        $limit = $request->input('limit', 50);

        return $query->take($limit)->get();
    }

    /**
     * تحضير بيانات المخطط الشبكي
     */
    private function prepareGraphData(Collection $bookings)
    {
        $nodes = [];
        $links = [];
        $uniqueIds = [];

        // العقد المركزية للتجميعات (جديد)
        $nodes[] = [
            'id' => 'center_all',
            'name' => 'جميع الحجوزات',
            'type' => 'center',
            'size' => 15,
        ];

        // إضافة العقد والروابط لكل حجز
        foreach ($bookings as $booking) {
            $companyId = $booking->company_id ?? 0;
            $agentId = $booking->agent_id ?? 0;
            $hotelId = $booking->hotel_id ?? 0;

            // حساب المبالغ المالية
            $companyAmountDue = $booking->amount_due_from_company ?: 0;
            $companyAmountPaid = $booking->company_amount_paid ?: 0;
            $companyPaymentStatus = $this->determinePaymentStatus($companyAmountPaid, $companyAmountDue);

            $agentAmountDue = $booking->amount_due_to_hotel ?: 0;
            $agentAmountPaid = $booking->agent_amount_paid ?: 0;
            $agentPaymentStatus = $this->determinePaymentStatus($agentAmountPaid, $agentAmountDue);

            $totalDue = $companyAmountDue + $agentAmountDue;
            $totalPaid = $companyAmountPaid + $agentAmountPaid;
            $totalRemaining = max(0, $totalDue - $totalPaid);

            // تحديد حالة الدفع
            $paymentStatus = $this->determinePaymentStatus($totalPaid, $totalDue);
            $paymentPercentage = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0;

            // تحديد حجم العقدة حسب عدد الغرف
            $nodeSize = min(max($booking->rooms * 1.5, 5), 15);

            // تحديد الأولوية (جديد)
            $priorityLevel = $booking->financialTracking ? $booking->financialTracking->priority_level : 'medium';

            // إضافة عقدة الحجز
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
                'amount_due' => $totalDue,
                'amount_paid' => $totalPaid,
                'amount_remaining' => $totalRemaining,
                'payment_status' => $paymentStatus,
                'payment_percentage' => $paymentPercentage,
                'priority_level' => $priorityLevel,
                'size' => $nodeSize,
                'voucher_number' => $booking->voucher_number,
            ];

            // ربط العقدة بالمركز
            $links[] = [
                'source' => 'center_all',
                'target' => $bookingNodeId,
                'value' => 1,
            ];

            // إضافة عقد الشركات وجهات الحجز والفنادق إذا لم تكن موجودة بالفعل
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

            // إضافة الروابط مع معلومات إضافية
            if ($companyId) {
                $links[] = [
                    'source' => $bookingNodeId,
                    'target' => 'company_' . $companyId,
                    'payment_status' => $companyPaymentStatus,
                    'amount_due' => $companyAmountDue,
                    'amount_paid' => $companyAmountPaid,
                    'amount_remaining' => max(0, $companyAmountDue - $companyAmountPaid),
                    'value' => $companyAmountDue > 0 ? sqrt($companyAmountDue) / 10 + 1 : 1,
                ];
            }

            if ($agentId) {
                $links[] = [
                    'source' => $bookingNodeId,
                    'target' => 'agent_' . $agentId,
                    'payment_status' => $agentPaymentStatus,
                    'amount_due' => $agentAmountDue,
                    'amount_paid' => $agentAmountPaid,
                    'amount_remaining' => max(0, $agentAmountDue - $agentAmountPaid),
                    'value' => $agentAmountDue > 0 ? sqrt($agentAmountDue) / 10 + 1 : 1,
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

        // إضافة تجميعات حسب حالات الدفع (جديد)
        $this->addPaymentStatusClusters($nodes, $links, $bookings);

        return [
            'nodes' => $nodes,
            'links' => $links,
        ];
    }

    /**
     * إضافة تجميعات حسب حالات الدفع (جديد)
     */
    private function addPaymentStatusClusters(&$nodes, &$links, $bookings)
    {
        // إضافة عقد التجميع
        $nodes[] = ['id' => 'cluster_fully_paid', 'name' => 'مدفوع بالكامل', 'type' => 'cluster', 'size' => 12];
        $nodes[] = ['id' => 'cluster_partially_paid', 'name' => 'مدفوع جزئياً', 'type' => 'cluster', 'size' => 12];
        $nodes[] = ['id' => 'cluster_not_paid', 'name' => 'غير مدفوع', 'type' => 'cluster', 'size' => 12];

        // ربط المجموعات بالمركز
        $links[] = ['source' => 'center_all', 'target' => 'cluster_fully_paid', 'value' => 3];
        $links[] = ['source' => 'center_all', 'target' => 'cluster_partially_paid', 'value' => 3];
        $links[] = ['source' => 'center_all', 'target' => 'cluster_not_paid', 'value' => 3];

        // ربط الحجوزات بالمجموعات المناسبة
        foreach ($bookings as $booking) {
            $totalDue = ($booking->amount_due_from_company ?: 0) + ($booking->amount_due_to_hotel ?: 0);
            $totalPaid = ($booking->company_amount_paid ?: 0) + ($booking->agent_amount_paid ?: 0);
            $paymentStatus = $this->determinePaymentStatus($totalPaid, $totalDue);

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
        if ($due <= 0) return 'fully_paid'; // إذا لم يكن هناك مبلغ مستحق

        if ($paid >= $due) return 'fully_paid';
        if ($paid > 0) return 'partially_paid';
        return 'not_paid';
    }

    /**
     * تحضير الإحصائيات
     */
    private function prepareStatistics(Collection $bookings)
    {
        $totalBookings = $bookings->count();
        $fullyPaidBookings = 0;
        $partiallyPaidBookings = 0;
        $notPaidBookings = 0;

        $financialByCurrency = [];
        $priorityStats = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];

        foreach ($bookings as $booking) {
            $companyAmountDue = $booking->amount_due_from_company ?: 0;
            $companyAmountPaid = $booking->company_amount_paid ?: 0;

            $agentAmountDue = $booking->amount_due_to_hotel ?: 0;
            $agentAmountPaid = $booking->agent_amount_paid ?: 0;

            $totalDue = $companyAmountDue + $agentAmountDue;
            $totalPaid = $companyAmountPaid + $agentAmountPaid;
            $totalRemaining = max(0, $totalDue - $totalPaid);

            // تحديد حالة الدفع
            $paymentStatus = $this->determinePaymentStatus($totalPaid, $totalDue);

            // زيادة عداد حالة الدفع
            switch ($paymentStatus) {
                case 'fully_paid':
                    $fullyPaidBookings++;
                    break;
                case 'partially_paid':
                    $partiallyPaidBookings++;
                    break;
                case 'not_paid':
                    $notPaidBookings++;
                    break;
            }

            // تحديد مستوى الأولوية (جديد)
            if ($booking->financialTracking) {
                $priority = $booking->financialTracking->priority_level;
                if (isset($priorityStats[$priority])) {
                    $priorityStats[$priority]++;
                }
            }

            // تجميع الإحصائيات المالية حسب العملة
            $currency = $booking->currency ?: 'SAR';

            if (!isset($financialByCurrency[$currency])) {
                $financialByCurrency[$currency] = [
                    'total_due' => 0,
                    'total_paid' => 0,
                    'total_remaining' => 0,
                ];
            }

            $financialByCurrency[$currency]['total_due'] += $totalDue;
            $financialByCurrency[$currency]['total_paid'] += $totalPaid;
            $financialByCurrency[$currency]['total_remaining'] += $totalRemaining;
        }

        // إضافة إحصائيات الربحية (جديد)
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
     * حساب إحصائيات الربحية (جديد)
     */
    private function calculateProfitStats(Collection $bookings)
    {
        $stats = [
            'SAR' => [
                'total_profit' => 0,
                'realized_profit' => 0,
                'unrealized_profit' => 0,
            ],
            'KWD' => [
                'total_profit' => 0,
                'realized_profit' => 0,
                'unrealized_profit' => 0,
            ]
        ];

        foreach ($bookings as $booking) {
            $currency = $booking->currency ?: 'SAR';
            $profitPerRoom = ($booking->sale_price - $booking->cost_price) * $booking->days;
            $totalProfit = $profitPerRoom * $booking->rooms;

            if (!isset($stats[$currency])) {
                $stats[$currency] = [
                    'total_profit' => 0,
                    'realized_profit' => 0,
                    'unrealized_profit' => 0,
                ];
            }

            $stats[$currency]['total_profit'] += $totalProfit;

            // حساب الأرباح المحققة وغير المحققة
            $companyAmountDue = $booking->amount_due_from_company ?: 0;
            $companyAmountPaid = $booking->company_amount_paid ?: 0;

            $agentAmountDue = $booking->amount_due_to_hotel ?: 0;
            $agentAmountPaid = $booking->agent_amount_paid ?: 0;

            if ($companyAmountDue > 0) {
                $paymentRatio = $companyAmountPaid / $companyAmountDue;
                $realizedProfit = $totalProfit * $paymentRatio;

                $stats[$currency]['realized_profit'] += $realizedProfit;
                $stats[$currency]['unrealized_profit'] += ($totalProfit - $realizedProfit);
            }
        }

        return $stats;
    }

    /**
     * الحصول على المدفوعات المتأخرة (جديد)
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

        return response()->json([
            'overdue_count' => $overdueBookings->count(),
            'bookings' => $overdueBookings->map(function ($booking) use ($today) {
                return [
                    'id' => $booking->id,
                    'client_name' => $booking->client_name,
                    'voucher_number' => $booking->voucher_number,
                    'company_name' => $booking->company->name ?? 'غير معروف',
                    'agent_name' => $booking->agent->name ?? 'غير معروف',
                    'payment_deadline' => $booking->financialTracking->payment_deadline,
                    'days_overdue' => $today->diffInDays($booking->financialTracking->payment_deadline),
                    'priority_level' => $booking->financialTracking->priority_level,
                    'amount_remaining' => $this->calculateRemainingAmount($booking),
                    'currency' => $booking->currency
                ];
            })
        ]);
    }

    /**
     * حساب المبلغ المتبقي (جديد)
     */
    private function calculateRemainingAmount($booking)
    {
        $companyAmountDue = $booking->amount_due_from_company ?: 0;
        $companyAmountPaid = $booking->company_amount_paid ?: 0;

        $agentAmountDue = $booking->amount_due_to_hotel ?: 0;
        $agentAmountPaid = $booking->agent_amount_paid ?: 0;

        $totalDue = $companyAmountDue + $agentAmountDue;
        $totalPaid = $companyAmountPaid + $agentAmountPaid;

        return max(0, $totalDue - $totalPaid);
    }
}
