<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Agent;
use App\Models\AgentPayment;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // لرفع الملفات
use Illuminate\Support\Facades\DB; //  لإجراء العمليات على قاعدة البيانات
use Carbon\CarbonPeriod; // لإجراء العمليات على التواريخ
use Illuminate\Support\Str; // لاستخدام دالة Str::limit


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

        // تقرير الشركات: كل شركة وعدد حجوزاتها (قائمة الشركات مع عدد الحجوزات لكل شركة)
        //  كل الشركات وعدد الحجوزاتها 
        $companiesReport = Company::withCount('bookings')->get()
            ->sortByDesc(function ($company) {
                return $company->total_due; // <-- الترتيب الصحيح هنا
            })->values();
        // إجمالي المتبقي من الشركات ...   
        $totalDueFromCompanies = $companiesReport->sum('remaining');

        //  تقرير الوكلاء: كل وكيل وعدد حجوزاته وترتيبهم من الأعلى واحد مطلوب منه فلوس للأقل
        $agentsReport = Agent::withCount('bookings')->get()
            ->sortByDesc(function ($agent) {
                return $agent->remaining;
            })->values();


        // إجمالي اللي اتدفع للفنادق (كل اللي اتدفع فعلاً للفنادق عن كل الحجوزات)
        $totalPaidToHotels = Booking::all()->sum(function ($booking) {
            return $booking->cost_price * $booking->rooms * $booking->days;
        });

        // تقرير الفنادق: كل فندق وعدد حجوزاته (قائمة الفنادق مع عدد الحجوزات لكل فندق)
        $hotelsReport = Hotel::withCount('bookings')->get()
            ->sortByDesc(function ($hotel) {
                return $hotel->total_due;
            })->values();


        // إجمالي المتبقي من الشركات (كل اللي لسه الشركات ما دفعتهوش فعلاً = المستحق - المدفوع لكل شركة)
        $totalRemainingFromCompanies = $companiesReport->sum('remaining');

        // إجمالي المتبقي للفنادق (كل اللي لسه عليك تدفعه للفنادق = المستحق للفنادق - اللي اتدفع فعلاً)
        $totalRemainingToHotels = Booking::sum('amount_due_to_hotel') - AgentPayment::sum('amount');
        // إجمالي اللي علينا لجهات الحجز أو الفنادقdd(Booking::sum('amount_due_to_hotel')); 

        // صافي الربح (الفرق بين اللي لسه الشركات هتدفعه لك واللي لسه عليك تدفعه للفنادق)
        // $netProfit = $totalRemainingFromCompanies - $totalRemainingToHotels; // السطر القديم (ممكن تمسحه أو تخليه تعليق)
        $totalDueToAgents = $agentsReport->sum('total_due'); // أو total_due حسب اسم العمود عندك لجهات الحجز
        $netProfit = $totalDueFromCompanies - $totalDueToAgents; // السطر الجديد
        // --- *** بداية: جلب بيانات الحجوزات اليومية لآخر 30 يومًا *** ---
        $days = 30; // عدد الأيام
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        // اختر الحقل الذي تريد تتبع تاريخه: 'created_at' أو 'check_in'
        $dateField = 'created_at'; // أو 'check_in'

        // جلب عدد الحجوزات مجمعة حسب اليوم
        $bookingsData = Booking::select(
            DB::raw("DATE($dateField) as date"),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween($dateField, [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('count', 'date'); // [date => count]

        // إنشاء فترة زمنية كاملة لآخر 30 يومًا
        $period = CarbonPeriod::create($startDate, $endDate);
        $chartDates = [];
        $bookingCounts = [];

        // ملء البيانات مع التأكد من وجود صفر للأيام بدون حجوزات
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartDates[] = $date->format('d/m'); // تنسيق العرض في الرسم البياني (يوم/شهر)
            $bookingCounts[] = $bookingsData[$formattedDate] ?? 0; // نضع صفر إذا لم يكن اليوم موجودًا
        }
        // --- *** نهاية: جلب بيانات الحجوزات اليومية *** ---


        // إشعار خفيف على آخر شيء تم عليه تعديل 
        // في نهاية دالة daily
        $recentCompanyEdits = \App\Models\Notification::whereIn('type', [
            'تعديل',
            'تعديل دفعة',
            'دفعة جديدة',
            'حذف دفعة'
        ])
            ->where('created_at', '>=', now()->subDays(2))
            ->get()
            ->groupBy('message');
        $resentAgentEdits = \App\Models\Notification::whereIn('type', [
            'تعديل',
            'تعديل دفعة',
            'دفعة جديدة',
            'حذف دفعة'
        ])
            ->where('created_at', '>=', now()->subDays(2))
            ->get()
            ->groupBy('message');
        // --- *** بداية: حساب بيانات الرسم البياني لصافي الرصيد *** ---

        // 1. جلب دفعات الشركات (فلوس داخلة = موجب) مرتبة بالتاريخ
        $companyPayments = Payment::select('payment_date as date', 'amount')
            ->orderBy('date', 'asc')
            ->get();

        // 2. جلب دفعات الوكلاء (فلوس خارجة = سالب) مرتبة بالتاريخ
        $agentPayments = AgentPayment::select('payment_date as date', DB::raw('-amount as amount')) // لاحظ السالب هنا
            ->orderBy('date', 'asc')
            ->get();

        // 3. دمج العمليتين في مجموعة واحدة
        $allTransactions = $companyPayments->concat($agentPayments);

        // 4. ترتيب كل العمليات حسب التاريخ
        $sortedTransactions = $allTransactions->sortBy('date');

        // 5. حساب الرصيد التراكمي مع الوقت
        $runningBalance = 0;
        $netBalanceData = []; // مصفوفة لتخزين [التاريخ => الرصيد]

        foreach ($sortedTransactions as $transaction) {
            $dateString = Carbon::parse($transaction->date)->format('Y-m-d'); // تاريخ العملية
            $runningBalance += $transaction->amount; // تحديث الرصيد
            // بنخزن آخر رصيد لكل يوم (لو فيه أكتر من عملية في نفس اليوم)
            $netBalanceData[$dateString] = $runningBalance;
        }

        // 6. تجهيز المصفوفات النهائية للرسم البياني
        $netBalanceDates = []; // مصفوفة التواريخ للعرض
        $netBalances = [];   // مصفوفة قيم الرصيد المقابلة

        // لو فيه بيانات، نرتبها حسب التاريخ ونجهزها للـ Chart
        if (!empty($netBalanceData)) {
            ksort($netBalanceData); // نرتب المصفوفة حسب مفتاح التاريخ
            foreach ($netBalanceData as $date => $balance) {
                $netBalanceDates[] = Carbon::parse($date)->format('d/m'); // تنسيق التاريخ للعرض
                $netBalances[] = round($balance, 2); // الرصيد المقابل (ممكن تقريبه)
            }
        }
        // --- *** نهاية: حساب بيانات الرسم البياني لصافي الرصيد *** ---

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
            'netProfit',
            'recentCompanyEdits', // إشعار خفيف على آخر شركة تم عليها تعديل
            'resentAgentEdits', // إشعار خفيف على آخر جهة حجز تم عليه تعديل
            'chartDates',       // <-- *** تمرير مصفوفة التواريخ للرسم ***
            'bookingCounts' ,    // <-- *** تمرير مصفوفة عدد الحجوزات للرسم ***
            'netBalanceDates',  // <-- اسم مصفوفة التواريخ
            'netBalances'   
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
            // 'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // Optional, file type, max size 5MB
        ]);
        // // *** بداية كود رفع الملف ***
        // $receiptPath = null; // نهيئ متغير المسار

        // // التعامل مع رفع الملف إذا كان موجودًا وصالحًا
        // if ($request->hasFile('receipt_file') && $request->file('receipt_file')->isValid()) {
        //     $file = $request->file('receipt_file');
        //     // إنشاء مسار/اسم ملف فريد داخل مجلد Google Drive
        //     $fileName = time() . '_' . $file->getClientOriginalName();
        //     $filePath = 'company_payments/' . $fileName; // مجلد فرعي داخل المجلد الرئيسي في Drive

        //     try {
        //         // الرفع إلى Google Drive باستخدام الـ disk المحدد
        //         Storage::disk('google')->put($filePath, file_get_contents($file));
        //         $receiptPath = $filePath; // تخزين المسار المستخدم في Google Drive
        //     } catch (\Exception $e) {
        //         // تسجيل الخطأ أو العودة برسالة خطأ
        //         // يمكنك استخدام Log::error(...) هنا لتسجيل تفاصيل الخطأ
        //         return back()->with('error', 'فشل رفع الإيصال: ' . $e->getMessage())->withInput();
        //     }
        // }
        // // *** نهاية كود رفع الملف ***

        // سجل الدفعة في جدول payments
        $payment = Payment::create([
            'company_id'       => $validated['company_id'],
            'amount'           => $validated['amount'],
            'payment_date'     => $validated['payment_date'] ?? now(),
            'notes'            => $validated['notes'] ?? null,
            'bookings_covered' => json_encode($validated['bookings_covered'] ?? []),
            // 'receipt_path'     => $receiptPath, // *** إضافة مسار الإيصال هنا ***
            'employee_id'      => Auth::id(), // إضافة الموظف الذي سجل الدفعة
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

        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => " تم إضافة دفعة جديدة لشركة {$payment->company->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'دفعة جديدة',
        ]);
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
            // 'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // *** إضافة التحقق هنا ***

        ]);
        // // *** بداية كود رفع الملف ***
        // $receiptPath = null; // نهيئ متغير المسار

        // // التعامل مع رفع الملف إذا كان موجودًا وصالحًا
        // if ($request->hasFile('receipt_file') && $request->file('receipt_file')->isValid()) {
        //     $file = $request->file('receipt_file');
        //     // إنشاء مسار/اسم ملف فريد داخل مجلد Google Drive
        //     $fileName = time() . '_' . $file->getClientOriginalName();
        //     $filePath = 'agent_payments/' . $fileName; // مجلد فرعي مختلف

        //     try {
        //         // الرفع إلى Google Drive باستخدام الـ disk المحدد
        //         Storage::disk('google')->put($filePath, file_get_contents($file));
        //         $receiptPath = $filePath; // تخزين المسار المستخدم في Google Drive
        //     } catch (\Exception $e) {
        //         // تسجيل الخطأ أو العودة برسالة خطأ
        //         return back()->with('error', 'فشل رفع الإيصال: ' . $e->getMessage())->withInput();
        //     }
        // }
        // // *** نهاية كود رفع الملف ***


        // سجل الدفعة في جدول agent_payments
        $payment = AgentPayment::create([
            'agent_id' => $validated['agent_id'],
            'amount' => $validated['amount'],
            'payment_date' => now(),
            'notes' => $validated['notes'],
            // 'receipt_path' => $receiptPath, // *** تأكد من إضافة هذا السطر هنا ***
            'employee_id' => Auth::id(), // إضافة الموظف الذي سجل الدفعة
        ]);
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => " تم إضافة دفعة جديدة لجهة حجز  {$payment->agent->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'دفعة جديدة',
            // 'receipt_path' => $receiptPath, // *** إضافة مسار الإيصال هنا ***
            'employee_id' => Auth::id(), // إضافة الموظف الذي سجل الدفعة
        ]);

        // رجع للصفحة مع رسالة نجاح
        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    // سجل الدفعات لشركة معينة
    public function companyPayments($id)
    {
        // هات الشركة المطلوبة
        $company = Company::findOrFail($id);

        // --- Fetch Payments ---
        $payments = Payment::where('company_id', $id)
            ->orderBy('payment_date', 'asc') // Order ascending for timeline
            ->get();

        // --- Fetch Bookings ---
        // Get bookings where the company owes money, order by check-in date
        $bookings = Booking::where('company_id', $id)
            ->where('amount_due_from_company', '>', 0)
            ->orderBy('check_in', 'asc') // Use check_in as the date the amount becomes due
            ->get();

        // --- Combine into Events ---
        $events = collect();

        foreach ($bookings as $booking) {
            $events->push([
                // Use check_in date and add a time to help with sorting if multiple events on same day
                'date' => Carbon::parse($booking->check_in)->startOfDay()->toDateTimeString(),
                'type' => 'booking',
                // Amount due from company is positive (increases balance)
                'amount' => (float) $booking->amount_due_from_company,
                'balance_change' => (float) $booking->amount_due_from_company,
                'details' => "حجز: " . ($booking->client_name ?? 'N/A') . " (فندق: " . ($booking->hotel->name ?? 'N/A') . ")",
                'id' => 'b_' . $booking->id // Unique ID prefix
            ]);
        }

        foreach ($payments as $payment) {
            $events->push([
                // Use payment date and add a time
                'date' => Carbon::parse($payment->payment_date)->endOfDay()->toDateTimeString(), // Payments happen after bookings on the same day
                'type' => 'payment',
                // Payment amount is positive, but it decreases the balance
                'amount' => (float) $payment->amount,
                'balance_change' => (float) -$payment->amount, // Negative change for balance calculation
                'details' => "دفعة: " . ($payment->notes ? Str::limit($payment->notes, 30) : 'مبلغ ' . $payment->amount),
                'id' => 'p_' . $payment->id // Unique ID prefix
            ]);
        }

        // --- Sort Events Chronologically ---
        $sortedEvents = $events->sortBy('date')->values();

        // --- Calculate Running Balance ---
        $runningBalance = 0;
        $timelineEvents = $sortedEvents->map(function ($event) use (&$runningBalance) {
            $runningBalance += $event['balance_change'];
            $event['running_balance'] = $runningBalance;
            // Re-parse date for chart.js adapter if needed, ensure consistent format
            $event['chart_date'] = Carbon::parse($event['date'])->format('Y-m-d');
            return $event;
        });


        // رجع البيانات للواجهة (pass timelineEvents instead of payments)
        return view('reports.company_payments', compact('company', 'timelineEvents', 'payments')); // Pass timelineEvents
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

        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تعديل دفعة لجهة حجز  {$agent->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'تعديل دفعة ',
        ]);

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

        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "  تعديل دفعة  لشركة   {$payment->company->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'تعديل دفعة ',
        ]);
        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $payment->company_id)
            ->with('success', 'تم تعديل دفعة الشركة بنجاح!');
    }

    // حذف دفعة شركة مع إعادة توزيع المبالغ على الحجوزات
    public function destroyCompanyPayment($id)
    {
        // *** إضافة تحقق من صلاحية الأدمن ***
        if (Auth::user()->role !== 'admin') {
            abort(403, 'غير مصرح لك بتنفيذ هذا الإجراء.');
        }
        // *** نهاية التحقق ***

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
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "  حذف دفعة  لشركة   {$payment->company->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'حذف دفعة ',
        ]);
        // رجع للصفحة مع رسالة نجاح
        return redirect()
            ->route('reports.company.payments', $companyId)
            ->with('success', 'تم حذف الدفعة وإرجاع المبالغ المرتبطة بها.');
    }

    // حذف دفعة وكيل
    public function destroyAgentPayment($id)
    {
        // *** إضافة تحقق من صلاحية الأدمن ***
        if (Auth::user()->role !== 'admin') {
            abort(403, 'غير مصرح لك بتنفيذ هذا الإجراء.');
        }
        // *** نهاية التحقق ***

        // هات الدفعة المطلوبة
        $payment = AgentPayment::findOrFail($id);
        $agentId = $payment->agent_id;

        // احذف الدفعة
        $payment->delete();
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => " حذف دفعة  لجهة حجز  {$payment->agent->name} بمبلغ {$payment->amount} في تاريخ {$payment->payment_date}",
            'type' => 'حذف دفعة ',
        ]);
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
