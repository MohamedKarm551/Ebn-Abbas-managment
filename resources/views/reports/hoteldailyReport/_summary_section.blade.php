<div class="card d-flex flex-column flex-md-row align-items-center justify-content-between mb-4">
    {{-- العنوان --}}
    <h1 class="mb-3 mb-md-0 text-dark">التقرير اليومي</h1> {{-- شيلنا التاريخ من هنا --}}

    {{-- زر التقارير المتقدمة --}}
    <a href="{{ route('reports.advanced') }}" class="btn btn-primary btn-lg mb-3 mb-md-0 ms-md-3">
        <i class="fas fa-chart-line me-2"></i> عرض التقارير المتقدمة
    </a>
    <!-- زر مخطط العلاقات -->
    <a href="{{ route('network.graph') }}" class="btn btn-success btn-lg mb-3 mb-md-0 ms-md-3">
        <i class="fas fa-project-diagram me-2"></i> مخطط العلاقات
    </a>
    {{-- *** بداية التعديل: إضافة التاريخ والوقت فوق الصورة *** --}}
    {{-- حاوية الصورة والنص (Relative Positioning) --}}
    <div style="position: relative;max-width: 200px;filter: drop-shadow(2px 2px 10px #000);"> {{-- نفس العرض الأقصى للصورة --}}
        {{-- الصورة الأصلية --}}
        <img src="{{ asset('images/watch.jpg') }}" alt="تقرير يومي"
            style="display: block; width: 100%; height: auto; border-radius: 8px;">

        {{-- التاريخ (Absolute Positioning) --}}
        <div id="watch-date-display"
            style="position: absolute;top: 23%;left: -6%;transform: translateX(109%);color: #8b22d8;font-size: 0.8em;font-weight: bold;text-shadow: 1px 1px 2px rgba(0,0,0,0.7);width: 30%;text-align: center;background: #000;">
            {{ \Carbon\Carbon::now()->format('d/m') }} {{-- تنسيق التاريخ يوم/شهر --}}
        </div>

        {{-- الوقت (Absolute Positioning) --}}
        <div id="watch-time-display"
            style="position: absolute;top: 31%;left: 38%;transform: translateX(-40%);color: white;font-size: 1.1em;font-weight: bold;text-shadow: 1px 1px 3px rgba(0,0,0,0.8);text-align: center;background: #000;width: 60px;">
            {{ \Carbon\Carbon::now()->format('H:i') }} {{-- تنسيق الوقت ساعة:دقيقة (24 ساعة) --}}
        </div>
    </div>
</div>

{{-- إضافة شريط التاريخ والوقت المتحرك بعد العنوان مباشرة وقبل ملخص العملات --}}
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-2 px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="date-time-item">
                <i class="fas fa-calendar-alt text-primary me-2"></i>
                <span id="gregorian-date">{{ \Carbon\Carbon::now()->locale('ar')->translatedFormat('l j F Y') }}م</span>

            </div>
            <div class="date-time-item">
                <i class="fas fa-moon text-success me-2"></i>
                <span id="hijri-date">جاري تحميل التاريخ الهجري...</span>
            </div>
            <div class="date-time-item">
                <i class="fas fa-clock text-danger me-2"></i>
                <span id="live-clock" class="fw-bold">00:00:00</span>
            </div>
        </div>
    </div>
</div>



@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/ar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.1.2/moment-hijri.min.js"></script>
    <script>
        // تعيين اللغة العربية لـ Moment.js بعد تحميل ملف اللغة
        moment.locale('ar');

        // تحديث الساعة والتاريخ
        function updateDateTime() {
            // تحديث الوقت مع الثواني
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0') + ':' +
                now.getSeconds().toString().padStart(2, '0');
            document.getElementById('live-clock').textContent = timeStr;

            // تحديث التاريخ الميلادي بالعربية
            moment.locale('ar');
            const gregorianDate = moment().format('dddd D MMMM YYYY') + 'م';
            document.getElementById('gregorian-date').textContent = gregorianDate;

            // تحديث التاريخ الهجري
            try {
                const hijriDate = new Intl.DateTimeFormat("ar-SA-islamic", {
                    weekday: "long",
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                    calendar: "islamic"
                }).format(now);
                document.getElementById('hijri-date').textContent = hijriDate.endsWith('هـ') ? hijriDate : hijriDate + 'هـ';
            } catch (e) {
                console.error("Error converting date with Intl.DateTimeFormat:", e);
                const hijri = moment().locale('ar-sa').format('dddd D MMMM iYYYY') + 'هـ';
                document.getElementById('hijri-date').textContent = hijri;
            }
        }

        // تشغيل الوظيفة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof moment !== 'undefined') {
                moment.locale('ar');
                updateDateTime();
                setInterval(updateDateTime, 1000);
            } else {
                console.error('moment.js library not loaded');
            }
        });
    </script>
@endpush

{{-- إضافة ملخص بالعملات في بداية الصفحة --}}
<div class="mb-4">
    <div class="card-header">
        <h5 class="mb-2 text-warning"><i class="fas fa-money-bill-wave me-2"></i>ملخص الأرصدة حسب العملة</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                {{-- 📊 إجمالي المطلوب من الشركات --}}
                <h6 class="text-primary"><i class="fas fa-coins me-2"></i>إجمالي المطلوب من الشركات:</h6>
                <ul class="list-unstyled">
                    {{-- @php
                        // ✅ حساب إجمالي المستحق من جميع الشركات حسب العملة
                        $totalDueFromCompaniesByCurrency = ['SAR' => 0, 'KWD' => 0];
                        foreach ($companiesReport as $company) {
                            $dueByCurrency = $company->total_due_by_currency ?? ['SAR' => $company->total_due];
                            foreach ($dueByCurrency as $currency => $amount) {
                                if (!isset($totalDueFromCompaniesByCurrency[$currency])) {
                                    $totalDueFromCompaniesByCurrency[$currency] = 0;
                                }
                                $totalDueFromCompaniesByCurrency[$currency] += $amount;
                            }
                        }
                    @endphp
                    @foreach ($totalDueFromCompaniesByCurrency as $currency => $amount)
                        @if ($amount > 0)
                            <li class="text-dark"><i class="fas fa-arrow-up me-1 text-info"></i>
                                <strong>{{ number_format($amount, 2) }}</strong>
                                {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                            </li>
                        @endif
                    @endforeach --}}

                    @foreach ($totalDueFromCompaniesByCurrency as $cur => $val)
                        <li class="text-dark">
                            <i class="fas fa-arrow-up me-1 text-info"></i>
                            <strong>{{ number_format($val, 2) }}</strong>
                            {{ $cur === 'SAR' ? 'ريال سعودي' : ($cur === 'KWD' ? 'دينار كويتي' : $cur) }}
                        </li>
                    @endforeach
                </ul>

                {{-- 💰 إجمالي المدفوع من الشركات --}}
                <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>إجمالي المدفوع من الشركات:</h6>
                <ul class="list-unstyled">

                    {{-- عرض المدفوعات والخصومات من البيانات المُمررة من الكنترولر --}}
                    @if (isset($companyPaymentsByCurrency['SAR']))
                        <li class="text-dark">
                            <i class="fas fa-dollar-sign me-1 text-success"></i>
                            <strong>{{ number_format($companyPaymentsByCurrency['SAR']['paid'] ?? 0, 2) }}</strong>
                            ريال سعودي (مدفوع)
                            @if (($companyPaymentsByCurrency['SAR']['discounts'] ?? 0) > 0)
                                <br><small class="text-warning ms-3">
                                    <i class="fas fa-minus-circle me-1"></i>
                                    خصومات: {{ number_format($companyPaymentsByCurrency['SAR']['discounts'], 2) }}
                                    ريال
                                </small>
                            @endif
                        </li>
                    @endif
                    @if (isset($companyPaymentsByCurrency['KWD']))
                        <li class="text-dark">
                            <i class="fas fa-dollar-sign me-1 text-success"></i>
                            <strong>{{ number_format($companyPaymentsByCurrency['KWD']['paid'] ?? 0, 2) }}</strong>
                            دينار كويتي (مدفوع)
                            @if (($companyPaymentsByCurrency['KWD']['discounts'] ?? 0) > 0)
                                <br><small class="text-warning ms-3">
                                    <i class="fas fa-minus-circle me-1"></i>
                                    خصومات: {{ number_format($companyPaymentsByCurrency['KWD']['discounts'], 2) }}
                                    دينار
                                </small>
                            @endif
                        </li>
                    @endif
                </ul>
                </ul>

                {{-- 🔥 الباقي المطلوب من الشركات --}}
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>الباقي المطلوب من الشركات:
                </h6>
                <ul class="list-unstyled">
                    @php
                        // حساب المتبقي بنفس طريقة footer الجدول
                        $totalRemainingByCurrency = [
                            'SAR' => 0,
                            'KWD' => 0,
                        ];

                        // حساب المتبقي الصحيح = إجمالي المستحق - إجمالي المدفوع
                        foreach (['SAR', 'KWD'] as $currency) {
                            // 1. إجمالي المستحق حسب العملة (من المتغير المحسوب مسبقاً)
                            $totalDue = $totalDueFromCompaniesByCurrency[$currency] ?? 0;

                            // 2. إجمالي المدفوع حسب العملة (من المتغير المحسوب مسبقاً)
                            $totalPaid = $companyPaymentsByCurrency[$currency]['paid'] ?? 0;
                            $totalDiscounts = $companyPaymentsByCurrency[$currency]['discounts'] ?? 0;

                            // 3. حساب المتبقي = المستحق - (المدفوع + الخصومات)
                            // ملاحظة: الخصومات موجبة في المتغير لكنها تقلل من المدفوع
                            $netPaid = $totalPaid + $totalDiscounts; // الخصومات تضاف للمدفوع الفعلي
                            $remaining = $totalDue - $netPaid;

                            if ($remaining != 0) {
                                $totalRemainingByCurrency[$currency] = $remaining;
                            }
                        }
                    @endphp

                    @foreach ($totalRemainingByCurrency as $currency => $remaining)
                        @if ($remaining != 0)
                            <li class="text-dark">
                                <i
                                    class="fas {{ $remaining > 0 ? 'fa-exclamation-triangle text-danger' : 'fa-check-double text-success' }} me-1"></i>
                                <span class="{{ $remaining > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                    {{ $remaining > 0 ? '+' : '' }}{{ number_format($remaining, 2) }}
                                </span>
                                {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                @if ($remaining < 0)
                                    <small class="text-muted">(دفعوا زيادة)</small>
                                @endif
                            </li>
                        @endif
                    @endforeach

                    {{-- إذا كان المجموع صفر في كل العملات --}}
                    @if (empty(array_filter($totalRemainingByCurrency)))
                        <li class="text-dark"><i class="fas fa-check-circle me-1 text-success"></i>
                            <span class="text-success fw-bold">جميع مستحقات الشركات مدفوعة! 🎉</span>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="col-md-6">
                {{-- 📋 إجمالي المستحق للجهات --}}
                <h6 class="text-warning"><i class="fas fa-hand-holding-usd me-2"></i>إجمالي المستحق للجهات:</h6>
                <ul class="list-unstyled">
                    {{-- ✅ استخدام البيانات المحسوبة من الكنترولر مباشرة --}}
                    @if (isset($totalDueToAgentsByCurrency))
                        {{-- استخدام البيانات المُمررة من الكنترولر --}}
                        @foreach ($totalDueToAgentsByCurrency as $currency => $amount)
                            @if ($amount > 0)
                                <li class="text-dark"><i class="fas fa-arrow-down me-1 text-warning"></i>
                                    <strong>{{ number_format($amount, 2) }}</strong>
                                    {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                </li>
                            @endif
                        @endforeach
                    @elseif(isset($allAgentsData))
                        {{-- fallback: استخدام البيانات الكاملة للوكلاء --}}
                        @php
                            $totalDueToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];
                            foreach ($allAgentsData as $agent) {
                                $dueByCurrency =
                                    $agent->computed_total_due_by_currency ??
                                    ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);
                                foreach ($dueByCurrency as $currency => $amount) {
                                    if (!isset($totalDueToAgentsByCurrency[$currency])) {
                                        $totalDueToAgentsByCurrency[$currency] = 0;
                                    }
                                    $totalDueToAgentsByCurrency[$currency] += $amount;
                                }
                            }
                        @endphp
                        @foreach ($totalDueToAgentsByCurrency as $currency => $amount)
                            @if ($amount > 0)
                                <li class="text-dark"><i class="fas fa-arrow-down me-1 text-warning"></i>
                                    <strong>{{ number_format($amount, 2) }}</strong>
                                    {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                </li>
                            @endif
                        @endforeach
                    @else
                        {{-- fallback أخير: جمع من جميع الوكلاء في قاعدة البيانات مباشرة --}}
                        @php
                            $totalDueToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];

                            // الحصول على جميع الوكلاء (بدون pagination)
                            $allAgentsForSummary = \App\Models\Agent::with(['bookings', 'payments'])
                                ->withCount('bookings')
                                ->get()
                                ->map(function ($agent) {
                                    $agent->calculateTotals();
                                    return $agent;
                                });

                            foreach ($allAgentsForSummary as $agent) {
                                $dueByCurrency =
                                    $agent->computed_total_due_by_currency ??
                                    ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);
                                foreach ($dueByCurrency as $currency => $amount) {
                                    if (!isset($totalDueToAgentsByCurrency[$currency])) {
                                        $totalDueToAgentsByCurrency[$currency] = 0;
                                    }
                                    $totalDueToAgentsByCurrency[$currency] += $amount;
                                }
                            }
                        @endphp
                        @foreach ($totalDueToAgentsByCurrency as $currency => $amount)
                            @if ($amount > 0)
                                <li class="text-dark"><i class="fas fa-arrow-down me-1 text-warning"></i>
                                    <strong>{{ number_format($amount, 2) }}</strong>
                                    {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                </li>
                            @endif
                        @endforeach
                    @endif

                    {{-- إذا لم توجد مستحقات --}}
                    @if (empty(array_filter($totalDueToAgentsByCurrency ?? [])))
                        <li class="text-dark"><i class="fas fa-info-circle me-1 text-muted"></i>
                            لا توجد مستحقات للجهات حالياً
                        </li>
                    @endif
                </ul>

                {{-- 💳 إجمالي المدفوع للجهات --}}
                <h6 class="text-success"><i class="fas fa-credit-card me-2"></i>إجمالي المدفوع للجهات:</h6>
                <ul class="list-unstyled">
                    {{-- ✅ هذا القسم صحيح لأنه يستخدم البيانات المحسوبة من الكنترولر --}}
                    @if (isset($agentPaymentsByCurrency['SAR']) && ($agentPaymentsByCurrency['SAR']['paid'] ?? 0) > 0)
                        <li class="text-dark">
                            <i class="fas fa-dollar-sign me-1 text-success"></i>
                            <strong>{{ number_format($agentPaymentsByCurrency['SAR']['paid'] ?? 0, 2) }}</strong>
                            ريال سعودي (مدفوع)
                            @if (($agentPaymentsByCurrency['SAR']['discounts'] ?? 0) > 0)
                                <br><small class="text-warning ms-3">
                                    <i class="fas fa-minus-circle me-1"></i>
                                    خصومات: {{ number_format($agentPaymentsByCurrency['SAR']['discounts'], 2) }}
                                    ريال
                                </small>
                            @endif
                        </li>
                    @endif
                    @if (isset($agentPaymentsByCurrency['KWD']) && ($agentPaymentsByCurrency['KWD']['paid'] ?? 0) > 0)
                        <li class="text-dark">
                            <i class="fas fa-dollar-sign me-1 text-success"></i>
                            <strong>{{ number_format($agentPaymentsByCurrency['KWD']['paid'] ?? 0, 2) }}</strong>
                            دينار كويتي (مدفوع)
                            @if (($agentPaymentsByCurrency['KWD']['discounts'] ?? 0) > 0)
                                <br><small class="text-warning ms-3">
                                    <i class="fas fa-minus-circle me-1"></i>
                                    خصومات: {{ number_format($agentPaymentsByCurrency['KWD']['discounts'], 2) }}
                                    دينار
                                </small>
                            @endif
                        </li>
                    @endif

                    {{-- إذا لم توجد مدفوعات --}}
                    @if (empty($agentPaymentsByCurrency) ||
                            (($agentPaymentsByCurrency['SAR']['paid'] ?? 0) == 0 && ($agentPaymentsByCurrency['KWD']['paid'] ?? 0) == 0))
                        <li class="text-dark"><i class="fas fa-info-circle me-1 text-muted"></i>
                            لا توجد مدفوعات مسجلة للجهات حتى الآن
                        </li>
                    @endif
                </ul>

                {{-- ⚠️ الباقي المطلوب للجهات --}}
                <h6 class="text-warning"><i class="fas fa-hourglass-half me-2"></i>الباقي المطلوب للجهات:</h6>
                <ul class="list-unstyled">
                    @php
                        // ✅ استخدام البيانات المحسوبة أو حسابها من البيانات الصحيحة
                        $totalRemainingToAgentsByCurrency = [];

                        // إذا كانت البيانات محسوبة من الكنترولر، استخدمها
                        if (
                            isset($totalRemainingToAgentsByCurrency) &&
                            !empty(array_filter($totalRemainingToAgentsByCurrency))
                        ) {
                            // استخدام البيانات المُمررة من الكنترولر
                            $totalRemainingToAgentsByCurrency = $totalRemainingToAgentsByCurrency;
                        } else {
                            // حساب المتبقي من البيانات المتاحة
                            $totalRemainingToAgentsByCurrency = [
                                'SAR' => 0,
                                'KWD' => 0,
                            ];

                            foreach (['SAR', 'KWD'] as $currency) {
                                // المستحق (من المتغير المحسوب أعلاه)
                                $totalDue = $totalDueToAgentsByCurrency[$currency] ?? 0;

                                // المدفوع والخصومات (من البيانات المُمررة من الكنترولر)
                                $totalPaid = $agentPaymentsByCurrency[$currency]['paid'] ?? 0;
                                $totalDiscounts = $agentPaymentsByCurrency[$currency]['discounts'] ?? 0;

                                // حساب المتبقي = المستحق - (المدفوع + الخصومات)
                                $netPaid = $totalPaid + $totalDiscounts;
                                $remaining = $totalDue - $netPaid;

                                if ($remaining != 0) {
                                    $totalRemainingToAgentsByCurrency[$currency] = $remaining;
                                }
                            }
                        }
                    @endphp

                    @foreach ($totalRemainingToAgentsByCurrency as $currency => $remaining)
                        @if ($remaining != 0)
                            <li class="text-dark">
                                <i
                                    class="fas {{ $remaining > 0 ? 'fa-exclamation-triangle text-warning' : 'fa-check-double text-success' }} me-1"></i>
                                <span class="{{ $remaining > 0 ? 'text-warning fw-bold' : 'text-success fw-bold' }}">
                                    {{ $remaining > 0 ? '+' : '' }}{{ number_format($remaining, 2) }}
                                </span>
                                {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                @if ($remaining < 0)
                                    <small class="text-muted">(دفعنا لهم زيادة)</small>
                                @endif
                            </li>
                        @endif
                    @endforeach

                    {{-- إذا كان المجموع صفر في كل العملات --}}
                    @if (empty(array_filter($totalRemainingToAgentsByCurrency)))
                        <li class="text-dark"><i class="fas fa-check-circle me-1 text-success"></i>
                            <span class="text-success fw-bold">جميع مستحقات الجهات مدفوعة! 🎉</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- ⚖️ صافي الرصيد الإجمالي --}}
        <hr class="my-4">
        <div class="row p-2">
            <div class="col-12">
                <h5 class="text-center mb-3 text-dark">
                    <i class="fas fa-balance-scale me-2"></i>
                    صافي الرصيد الإجمالي
                </h5>
                @php
                    // ✅ حساب صافي الرصيد = ما لك من الشركات - ما عليك للجهات
                    $netBalanceByCurrency = [];
                    $allCurrencies = array_unique(
                        array_merge(
                            array_keys($totalRemainingFromCompaniesByCurrency ?? []),
                            array_keys($totalRemainingToAgentsByCurrency ?? []),
                        ),
                    );

                    foreach ($allCurrencies as $currency) {
                        $fromCompanies = $totalRemainingFromCompaniesByCurrency[$currency] ?? 0; // لك من الشركات
                        $toAgents = $totalRemainingToAgentsByCurrency[$currency] ?? 0; // عليك للجهات
                        $netBalance = $fromCompanies - $toAgents;

                        if ($netBalance != 0) {
                            $netBalanceByCurrency[$currency] = $netBalance;
                        }
                    }
                @endphp

                <div class="text-center">
                    @foreach ($netBalanceByCurrency as $currency => $netBalance)
                        <div class="badge {{ $netBalance > 0 ? 'bg-success' : 'bg-danger' }} fs-6 me-3 p-3">
                            <i class="fas {{ $netBalance > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1"></i>
                            {{ $netBalance > 0 ? '+' : '' }}{{ number_format($netBalance, 2) }}
                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                            <br>
                            <small>{{ $netBalance > 0 ? 'لك' : 'عليك' }}</small>
                        </div>
                    @endforeach

                    @if (empty($netBalanceByCurrency))
                        <div class="badge bg-secondary fs-6 p-3">
                            <i class="fas fa-equals me-1"></i>
                            الرصيد متوازن
                            <br>
                            <small>0.00</small>
                        </div>
                    @endif
                </div>
