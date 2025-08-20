                {{-- 📈 ملخص تفصيلي سريع --}}
                {{-- 📈 ملخص تفصيلي متقدم قابل للطي --}}
                <div class="collapse-card mt-4">
                    <button class="collapse-btn" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseDetailedSummary" aria-expanded="false">
                        <div class="collapse-btn-content">
                            <div class="collapse-icon-wrapper">
                                <i class="fas fa-chart-line collapse-icon"></i>
                            </div>
                            <div class="collapse-text-content">
                                <span class="collapse-title">📊 التحليل المالي التفصيلي</span>
                                <span class="collapse-subtitle">إحصائيات شاملة وتحليل الأداء المالي</span>
                            </div>
                        </div>
                        <div class="collapse-arrow">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </button>

                    <div class="collapse" id="collapseDetailedSummary">
                        <div class="collapse-content">
                            <div class="row">
                                {{-- القسم الأول: تحليل الشركات --}}
                                {{-- @php
                                    // ✅ إجمالي المستحق لكل عملة (من companiesReport)
                                    $totalDueFromCompaniesByCurrency = ['SAR' => 0, 'KWD' => 0];
                                    foreach ($companiesReport as $company) {
                                        foreach (
                                            $company->total_due_by_currency ?? ['SAR' => $company->total_due]
                                            as $cur => $amt
                                        ) {
                                            $totalDueFromCompaniesByCurrency[$cur] =
                                                ($totalDueFromCompaniesByCurrency[$cur] ?? 0) + $amt;
                                        }
                                    }
                                    // ✅ إجمالي المدفوع والخصومات من companyPaymentsByCurrency (مفترض موجود من الكنترولر)
                                    //     $companyPaymentsByCurrency = ['SAR'=>['paid'=>..,'discounts'=>..], 'KWD'=>[...] ];
                                @endphp --}}
                                {{-- تحليل الشركات  --}}
                                @php
                                    //   dd($totalDueFromCompaniesByCurrency['SAR'], $totalDueFromCompaniesByCurrency['KWD']);
                                    // في الكنترولر : 
                                    //'totalDueFromCompaniesByCurrency'       => $companyTotals['by_currency']['due'],
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="analysis-section">
                                        <h6 class="section-title text-primary">
                                            <i class="fas fa-building me-2"></i>تحليل أداء الشركات
                                        </h6>

                                        @foreach (['SAR', 'KWD'] as $currency)
                                            @php
                                                $due = $totalDueFromCompaniesByCurrency[$currency] ?? 0;
                                                $paid = $companyPaymentsByCurrency[$currency]['paid'] ?? 0;
                                                $discounts = $companyPaymentsByCurrency[$currency]['discounts'] ?? 0;
                                                $netPaid = $paid + $discounts;
                                                // نسبة التحصيل
                                                if ($due == 0 && $netPaid > 0) {
                                                    $percentage = 100;
                                                    $remaining = 0;
                                                } elseif ($due > 0) {
                                                    $percentage = round(($netPaid / $due) * 100, 1);
                                                    $remaining = $due - $netPaid;
                                                } else {
                                                    $percentage = 0;
                                                    $remaining = 0;
                                                }
                                                $currencyName = $currency == 'SAR' ? 'ريال سعودي' : 'دينار كويتي';
                                                $symbol = $currency == 'SAR' ? 'ر.س' : 'د.ك';
                                                $hasData = $due > 0 || $paid > 0 || $discounts > 0;
                                            @endphp

                                            @if ($hasData)
                                                <div class="currency-analysis mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <strong class="currency-label">{{ $currencyName }}:</strong>
                                                        <span
                                                            class="percentage-badge {{ $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                            {{ $percentage }}%
                                                        </span>
                                                    </div>
                                                    <div class="progress-container mb-2">
                                                        <div class="progress" style="height:12px">
                                                            <div class="progress-bar {{ $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                                style="width:{{ $percentage }}%"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $percentage }}% تم تحصيله">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="financial-details">
                                                        <div class="detail-row">
                                                            <span class="detail-label">💰 إجمالي المستحق:</span>
                                                            <span
                                                                class="detail-value text-info">{{ number_format($due, 2) }}
                                                                {{ $symbol }}</span>
                                                        </div>
                                                        <div class="detail-row">
                                                            <span class="detail-label">✅ المدفوع:</span>
                                                            <span
                                                                class="detail-value text-success">{{ number_format($paid, 2) }}
                                                                {{ $symbol }}</span>
                                                        </div>
                                                        @if ($discounts > 0)
                                                            <div class="detail-row">
                                                                <span class="detail-label">🎁 الخصومات:</span>
                                                                <span
                                                                    class="detail-value text-warning">{{ number_format($discounts, 2) }}
                                                                    {{ $symbol }}</span>
                                                            </div>
                                                        @endif
                                                        <div class="detail-row">
                                                            <span class="detail-label">⏳ المتبقي:</span>
                                                            <span
                                                                class="detail-value {{ $remaining > 0 ? 'text-warning' : 'text-success' }}">
                                                                {{ number_format($remaining, 2) }} {{ $symbol }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                        {{-- في حالة عدم وجود بيانات --}}



                                    </div>
                                </div>

                                {{-- القسم الثاني: تحليل الجهات --}}
                                <div class="col-md-6 mb-4">
                                    @php
                                        // ✅ 1. استخدام البيانات الكاملة بدلاً من pagination
                                        // الأولوية الأولى: البيانات المحسوبة من الكنترولر
                                        if (isset($agentsTotalCalculations['total_due_by_currency'])) {
                                            $totalDueToAgentsByCurrency =
                                                $agentsTotalCalculations['total_due_by_currency'];
                                        }
                                        // الأولوية الثانية: البيانات الكاملة للوكلاء
                                        elseif (isset($allAgentsData)) {
                                            $totalDueToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];
                                            foreach ($allAgentsData as $agent) {
                                                $dueByCurrency =
                                                    $agent->computed_total_due_by_currency ??
                                                    ($agent->total_due_by_currency ?? [
                                                        'SAR' => $agent->total_due ?? 0,
                                                    ]);
                                                foreach ($dueByCurrency as $cur => $amt) {
                                                    if (!isset($totalDueToAgentsByCurrency[$cur])) {
                                                        $totalDueToAgentsByCurrency[$cur] = 0;
                                                    }
                                                    $totalDueToAgentsByCurrency[$cur] += $amt;
                                                }
                                            }
                                        }
                                        // الأولوية الثالثة: fallback - استعلام مباشر من قاعدة البيانات
                                        else {
                                            $totalDueToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];

                                            // جلب جميع الوكلاء (بدون pagination) للحسابات
                                            $allAgentsForCalculation = \App\Models\Agent::with(['bookings', 'payments'])
                                                ->withCount('bookings')
                                                ->get()
                                                ->map(function ($agent) {
                                                    $agent->calculateTotals();
                                                    return $agent;
                                                });

                                            foreach ($allAgentsForCalculation as $agent) {
                                                $dueByCurrency =
                                                    $agent->computed_total_due_by_currency ??
                                                    ($agent->total_due_by_currency ?? [
                                                        'SAR' => $agent->total_due ?? 0,
                                                    ]);
                                                foreach ($dueByCurrency as $cur => $amt) {
                                                    if (!isset($totalDueToAgentsByCurrency[$cur])) {
                                                        $totalDueToAgentsByCurrency[$cur] = 0;
                                                    }
                                                    $totalDueToAgentsByCurrency[$cur] += $amt;
                                                }
                                            }
                                        }

                                        // ✅ إجمالي المدفوع والخصومات من agentPaymentsByCurrency (صحيح ومُمرر من الكنترولر)

                                    @endphp

                                    <div class="analysis-section">
                                        <h6 class="section-title text-warning">
                                            <i class="fas fa-handshake me-2"></i>تحليل أداء جهات الحجز
                                        </h6>
                                        @foreach (['SAR', 'KWD'] as $currency)
                                            @php
                                                // ✅ 2. استخدام البيانات الصحيحة المحسوبة من جميع الوكلاء
                                                $due = $totalDueToAgentsByCurrency[$currency] ?? 0;

                                                // ✅ 3. المدفوع والخصومات من البيانات المُمررة من الكنترولر (صحيحة)
                                                $paidAmount = isset($agentPaymentsByCurrency)
                                                    ? ($agentPaymentsByCurrency[$currency] ?? [])['paid'] ?? 0
                                                    : 0;
                                                $discounts = isset($agentPaymentsByCurrency)
                                                    ? ($agentPaymentsByCurrency[$currency] ?? [])['discounts'] ?? 0
                                                    : 0;

                                                $netPaid = $paidAmount + $discounts;
                                                $remaining = $due - $netPaid;

                                                // 🔧 إصلاح حساب النسبة المئوية
                                                if ($due == 0 && $netPaid > 0) {
                                                    $percentage = 100;
                                                    $due = $netPaid; // للعرض فقط
                                                    $remaining = 0;
                                                } elseif ($due > 0) {
                                                    $percentage = round(($netPaid / $due) * 100, 1);
                                                } else {
                                                    $percentage = 0;
                                                }

                                                $currencyName = $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي';
                                                $symbol = $currency === 'SAR' ? 'ر.س' : 'د.ك';

                                                $hasData = $due > 0 || $paidAmount > 0 || $discounts > 0;
                                            @endphp

                                            @if ($hasData)
                                                <div class="currency-analysis mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <strong class="currency-label">{{ $currencyName }}:</strong>
                                                        <span
                                                            class="percentage-badge {{ $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                            {{ $percentage }}%
                                                        </span>
                                                    </div>

                                                    {{-- شريط التقدم المحسن --}}
                                                    <div class="progress-container mb-2">
                                                        <div class="progress" style="height: 12px;">
                                                            <div class="progress-bar {{ $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                                style="width: {{ $percentage }}%"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $percentage }}% تم دفعه من إجمالي المستحق">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- التفاصيل المالية --}}
                                                    <div class="financial-details">
                                                        <div class="detail-row">
                                                            <span class="detail-label">💰 إجمالي المستحق:</span>
                                                            <span
                                                                class="detail-value text-info">{{ number_format($due, 2) }}
                                                                {{ $symbol }}</span>
                                                        </div>
                                                        <div class="detail-row">
                                                            <span class="detail-label">✅ المدفوع:</span>
                                                            <span
                                                                class="detail-value text-success">{{ number_format($paidAmount, 2) }}
                                                                {{ $symbol }}</span>
                                                        </div>
                                                        @if ($discounts > 0)
                                                            <div class="detail-row">
                                                                <span class="detail-label">🎁 الخصومات:</span>
                                                                <span
                                                                    class="detail-value text-warning">{{ number_format($discounts, 2) }}
                                                                    {{ $symbol }}</span>
                                                            </div>
                                                        @endif
                                                        <div class="detail-row">
                                                            <span class="detail-label">⏳ المتبقي:</span>
                                                            <span
                                                                class="detail-value {{ $remaining > 0 ? 'text-warning' : 'text-success' }}">
                                                                {{ number_format($remaining, 2) }} {{ $symbol }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                        {{-- في حالة عدم وجود بيانات --}}
                                        @if (empty(array_filter($totalDueToAgentsByCurrency ?? [])))
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                لا توجد بيانات مالية للوكلاء حالياً
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>

                            {{-- القسم الثالث: صافي الربح والتحليلات المتقدمة --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="profit-analysis-section"
                                        style="background: linear-gradient(145deg, #ffffff, #f8fafc); border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.1);">
                                        <h6 class="section-title text-success mb-3"
                                            style="font-size: 1.1em; font-weight: bold; padding-bottom: 10px; border-bottom: 2px solid #10b981; margin-bottom: 15px; color: #1f2937;">
                                            <i class="fas fa-chart-pie me-2"></i>تحليل الربحية وصافي الرصيد
                                        </h6>

                                        <div class="row">
                                            {{-- صافي الربح الحالي --}}
                                            <div class="col-md-3 mb-3">
                                                @php
                                                    // حساب إجمالي المتبقي للشركات
                                                    $totalRemainingByCurrency = ['SAR' => 0, 'KWD' => 0];
                                                    foreach ($companiesReport as $company) {
                                                        foreach (
                                                            $company->remaining_by_currency ?? [
                                                                'SAR' => $company->remaining,
                                                            ]
                                                            as $cur => $amt
                                                        ) {
                                                            $totalRemainingByCurrency[$cur] += $amt;
                                                        }
                                                    }
                                                    // حساب إجمالي المتبقي للجهات
                                                    // ✅ حساب إجمالي المتبقي للجهات من البيانات الكاملة
                                                    $totalRemainingToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];

                                                    // الأولوية الأولى: البيانات المحسوبة من الكنترولر
                                                    if (
                                                        isset($agentsTotalCalculations['total_remaining_by_currency'])
                                                    ) {
                                                        $totalRemainingToAgentsByCurrency =
                                                            $agentsTotalCalculations['total_remaining_by_currency'];
                                                    }
                                                    // الأولوية الثانية: البيانات الكاملة للوكلاء
                                                    elseif (isset($allAgentsData)) {
                                                        foreach ($allAgentsData as $agent) {
                                                            foreach (
                                                                $agent->computed_remaining_by_currency ??
                                                                    ($agent->remaining_by_currency ?? [
                                                                        'SAR' => $agent->remaining_amount ?? 0,
                                                                    ])
                                                                as $cur => $amt
                                                            ) {
                                                                $totalRemainingToAgentsByCurrency[$cur] += $amt;
                                                            }
                                                        }
                                                    }
                                                    // الأولوية الثالثة: fallback - حساب من البيانات المتاحة
                                                    else {
                                                        // حساب المتبقي = المستحق - (المدفوع + الخصومات)
                                                        foreach (['SAR', 'KWD'] as $currency) {
                                                            $totalDue = $totalDueToAgentsByCurrency[$currency] ?? 0;
                                                            $totalPaid =
                                                                $agentPaymentsByCurrency[$currency]['paid'] ?? 0;
                                                            $totalDiscounts =
                                                                $agentPaymentsByCurrency[$currency]['discounts'] ?? 0;
                                                            $netPaid = $totalPaid + $totalDiscounts;
                                                            $remaining = $totalDue - $netPaid;

                                                            if ($remaining != 0) {
                                                                $totalRemainingToAgentsByCurrency[
                                                                    $currency
                                                                ] = $remaining;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <div style="background: linear-gradient(135deg, #ffffff, #f0fdf4); border-radius: 12px; padding: 20px; height: 100%; border: 1px solid rgba(16, 185, 129, 0.2); transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);"
                                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.2)'"
                                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(16, 185, 129, 0.1)'">

                                                    <h6
                                                        style="font-size: 1em; font-weight: bold; margin-bottom: 15px; text-align: center; background: linear-gradient(120deg, #10b981 60%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                                        💹 صافي الربح الحالي
                                                    </h6>

                                                    @foreach (['SAR', 'KWD'] as $currency)
                                                        @php
                                                            // ✅ 1. حساب متبقي الشركات (ما نستحقه)
                                                            $companyDue =
                                                                $totalDueFromCompaniesByCurrency[$currency] ?? 0;
                                                            $companyPaid =
                                                                $companyPaymentsByCurrency[$currency]['paid'] ?? 0;
                                                            $companyDiscounts =
                                                                $companyPaymentsByCurrency[$currency]['discounts'] ?? 0;
                                                            $companyRemaining =
                                                                $companyDue - ($companyPaid + $companyDiscounts);

                                                            // ✅ 2. حساب متبقي الجهات (ما ندين به)
                                                            $agentDue = $totalDueToAgentsByCurrency[$currency] ?? 0;
                                                            $agentPaid =
                                                                $agentPaymentsByCurrency[$currency]['paid'] ?? 0;
                                                            $agentDiscounts =
                                                                $agentPaymentsByCurrency[$currency]['discounts'] ?? 0;
                                                            $agentRemaining =
                                                                $agentDue - ($agentPaid + $agentDiscounts);

                                                            // ✅ 3. صافي الربح = ما نستحقه - ما ندين به
                                                            $netProfit = $companyRemaining - $agentRemaining;
                                                            $symbol = $currency === 'SAR' ? 'ر.س' : 'د.ك';

                                                        @endphp

                                                        @if ($netProfit !== 0)
                                                            <div
                                                                style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid rgba(16,185,129,0.1);background:linear-gradient(135deg,rgba(16,185,129,0.03),rgba(5,150,105,0.03));border-radius:6px;margin-bottom:8px;">
                                                                <span
                                                                    style="font-weight:600;color:#374151;">{{ $currency }}:</span>
                                                                <span
                                                                    style="font-weight:bold;font-size:1.1em;color:{{ $netProfit > 0 ? '#10b981' : '#ef4444' }};">
                                                                    {{ $netProfit > 0 ? '+' : '' }}{{ number_format($netProfit, 2) }}
                                                                    {{ $symbol }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach


                                                    <div style="margin-top: 10px;">
                                                        <small
                                                            style="color: #6b7280; display: flex; align-items: center; font-size: 0.8em;">
                                                            <i class="fas fa-calculator me-1"
                                                                style="color: #10b981;"></i>
                                                            بناءً على الحالة الحالية <br>
                                                        </small>
                                                        <small> معادلة صافي الربح الحالي : صافي الربح الحالي = مجموع
                                                            (المستحق – (المدفوع + الخصومات)) للشركات
                                                            – مجموع (المستحق – (المدفوع + الخصومات)) للجهات

                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- صافي الربح المتوقع الكلي --}}
                                            <div class="col-md-3 mb-3">
                                                <div style="background: linear-gradient(135deg, #ffffff, #eff6ff); border-radius: 12px; padding: 20px; height: 100%; border: 1px solid rgba(37, 99, 235, 0.2); transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(37, 99, 235, 0.1);"
                                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(37, 99, 235, 0.2)'"
                                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(37, 99, 235, 0.1)'">

                                                    <h6
                                                        style="font-size: 1em; font-weight: bold; margin-bottom: 15px; text-align: center; background: linear-gradient(120deg, #2563eb 60%, #1d4ed8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                                        🎯 صافي الربح المتوقع الكلي
                                                    </h6>

                                                    @foreach (['SAR', 'KWD'] as $currency)
                                                        @php
                                                            $totalDueFromCompanies =
                                                                $totalDueFromCompaniesByCurrency[$currency] ?? 0;
                                                            $totalDueToAgents =
                                                                $totalDueToAgentsByCurrency[$currency] ?? 0;
                                                            $expectedNetProfit =
                                                                $totalDueFromCompanies - $totalDueToAgents;
                                                            $symbol = $currency === 'SAR' ? 'ر.س' : 'د.ك';
                                                        @endphp

                                                        @if ($expectedNetProfit != 0)
                                                            <div
                                                                style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(37, 99, 235, 0.1); background: linear-gradient(135deg, rgba(37, 99, 235, 0.03), rgba(29, 78, 216, 0.03)); border-radius: 6px; margin-bottom: 8px; padding: 12px;">
                                                                <span
                                                                    style="font-weight: 600; color: #374151;">{{ $currency }}:</span>
                                                                <span
                                                                    style="font-weight: bold; font-size: 1.1em; color: {{ $expectedNetProfit > 0 ? '#2563eb' : '#ef4444' }};">
                                                                    {{ $expectedNetProfit > 0 ? '+' : '' }}{{ number_format($expectedNetProfit, 2) }}
                                                                    {{ $symbol }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach

                                                    <div style="margin-top: 10px;">
                                                        <small
                                                            style="color: #6b7280; display: flex; align-items: center; font-size: 0.8em;">
                                                            <i class="fas fa-info-circle me-1"
                                                                style="color: #2563eb;"></i>
                                                            الربح لو تم تحصيل كل المستحقات
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- نسب التحصيل --}}
                                            {{-- <div class="col-md-3 mb-3">
                                                <div style="background: linear-gradient(135deg, #ffffff, #fefce8); border-radius: 12px; padding: 20px; height: 100%; border: 1px solid rgba(245, 158, 11, 0.2); transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(245, 158, 11, 0.1);"
                                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(245, 158, 11, 0.2)'"
                                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(245, 158, 11, 0.1)'">

                                                    <h6
                                                        style="font-size: 1em; font-weight: bold; margin-bottom: 15px; text-align: center; background: linear-gradient(120deg, #f59e0b 60%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                                        📊 نسب التحصيل
                                                    </h6>

                                                    @foreach (['SAR', 'KWD'] as $currency)
                                                        @php
                                                            $totalDue =
                                                                ($totalDueFromCompaniesByCurrency[$currency] ?? 0) +
                                                                ($totalDueToAgentsByCurrency[$currency] ?? 0);
                                                            $totalPaid =
                                                                ($companyPaymentsByCurrency[$currency]['paid'] ?? 0) +
                                                                ($agentPaymentsByCurrency[$currency]['paid'] ?? 0);
                                                            $collectionRate =
                                                                $totalDue > 0
                                                                    ? round(($totalPaid / $totalDue) * 100, 1)
                                                                    : 0;
                                                        @endphp

                                                        @if ($totalDue > 0)
                                                            <div
                                                                style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(245, 158, 11, 0.1); background: linear-gradient(135deg, rgba(245, 158, 11, 0.03), rgba(217, 119, 6, 0.03)); border-radius: 6px; margin-bottom: 8px; padding: 12px;">
                                                                <span
                                                                    style="font-weight: 600; color: #374151;">{{ $currency }}:</span>
                                                                <span
                                                                    style="font-weight: bold; font-size: 1.1em; color: {{ $collectionRate >= 70 ? '#10b981' : ($collectionRate >= 40 ? '#f59e0b' : '#ef4444') }};">
                                                                    {{ $collectionRate }}%
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach

                                                    <div style="margin-top: 10px;">
                                                        <small
                                                            style="color: #6b7280; display: flex; align-items: center; font-size: 0.8em;">
                                                            <i class="fas fa-percentage me-1"
                                                                style="color: #f59e0b;"></i>
                                                            نسبة المدفوع من الإجمالي
                                                        </small>
                                                    </div>
                                                </div>
                                            </div> --}}

                                            {{-- إحصائيات العمليات --}}
                                            <div class="col-md-3 mb-3">
                                                <div style="background: linear-gradient(135deg, #ffffff, #f3e8ff); border-radius: 12px; padding: 20px; height: 100%; border: 1px solid rgba(139, 92, 246, 0.2); transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(139, 92, 246, 0.1);"
                                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(139, 92, 246, 0.2)'"
                                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(139, 92, 246, 0.1)'">

                                                    <h6
                                                        style="font-size: 1em; font-weight: bold; margin-bottom: 15px; text-align: center; background: linear-gradient(120deg, #8b5cf6 60%, #7c3aed 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                                        ⚡ إحصائيات العمليات
                                                    </h6>

                                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                                        {{-- عدد الشركات النشطة --}}
                                                        <div
                                                            style="display: flex; align-items: center; padding: 8px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.03), rgba(124, 58, 237, 0.03)); border-radius: 6px; border-left: 3px solid #8b5cf6;">
                                                            <div
                                                                style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(120deg, #3b82f6, #1d4ed8); display: flex; align-items: center; justify-content: center; margin-left: 8px; font-size: 12px; color: white;">
                                                                <i class="fas fa-building"></i>
                                                            </div>
                                                            <div style="flex: 1;">
                                                                <div
                                                                    style="font-size: 0.75em; color: #6b7280; font-weight: 500;">
                                                                    الشركات النشطة</div>
                                                                <div
                                                                    style="font-size: 1.1em; font-weight: bold; color: #1f2937;">
                                                                    {{ $companiesReport->where('bookings_count', '>', 0)->count() }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- عدد الجهات النشطة --}}
                                                        <div
                                                            style="display: flex; align-items: center; padding: 8px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.03), rgba(124, 58, 237, 0.03)); border-radius: 6px; border-left: 3px solid #8b5cf6;">
                                                            <div
                                                                style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(120deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; margin-left: 8px; font-size: 12px; color: white;">
                                                                <i class="fas fa-handshake"></i>
                                                            </div>
                                                            <div style="flex: 1;">
                                                                <div
                                                                    style="font-size: 0.75em; color: #6b7280; font-weight: 500;">
                                                                    الجهات النشطة</div>
                                                                <div
                                                                    style="font-size: 1.1em; font-weight: bold; color: #1f2937;">
                                                                    {{ $agentsReport->where('bookings_count', '>', 0)->count() }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- إجمالي الحجوزات --}}
                                                        <div
                                                            style="display: flex; align-items: center; padding: 8px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.03), rgba(124, 58, 237, 0.03)); border-radius: 6px; border-left: 3px solid #8b5cf6;">
                                                            <div
                                                                style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(120deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center; margin-left: 8px; font-size: 12px; color: white;">
                                                                <i class="fas fa-calendar-check"></i>
                                                            </div>
                                                            <div style="flex: 1;">
                                                                <div
                                                                    style="font-size: 0.75em; color: #6b7280; font-weight: 500;">
                                                                    إجمالي الحجوزات</div>
                                                                <div
                                                                    style="font-size: 1.1em; font-weight: bold; color: #1f2937;">
                                                                    {{ number_format($companiesReport->sum('bookings_count')) }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- عدد المعاملات اليوم --}}
                                                        <div
                                                            style="display: flex; align-items: center; padding: 8px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.03), rgba(124, 58, 237, 0.03)); border-radius: 6px; border-left: 3px solid #8b5cf6;">
                                                            <div
                                                                style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(120deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center; margin-left: 8px; font-size: 12px; color: white;">
                                                                <i class="fas fa-credit-card"></i>
                                                            </div>
                                                            <div style="flex: 1;">
                                                                <div
                                                                    style="font-size: 0.75em; color: #6b7280; font-weight: 500;">
                                                                    معاملات اليوم</div>
                                                                <div
                                                                    style="font-size: 1.1em; font-weight: bold; color: #1f2937;">
                                                                    {{ \App\Models\Payment::whereDate('payment_date', today())->count() + \App\Models\AgentPayment::whereDate('payment_date', today())->count() }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- القسم الرابع: تحليل المخاطر والتنبيهات --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="risk-analysis-section">
                                        <h6 class="section-title text-danger mb-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>تحليل المخاطر
                                            والتنبيهات
                                        </h6>

                                        <div class="row">
                                            {{-- شركات عليها مبالغ كبيرة --}}
                                            <div class="col-md-6 mb-3">
                                                <div class="risk-card">
                                                    <h6 class="risk-title text-danger">⚠️ شركات عليها مبالغ كبيرة
                                                    </h6>
                                                    @php
                                                        $highRiskCompanies = $companiesReport
                                                            ->filter(function ($company) {
                                                                $remaining = collect(
                                                                    $company->remaining_by_currency ?? [],
                                                                )->sum();
                                                                return $remaining > 1000; // أكثر من 1000 ريال
                                                            })
                                                            ->take(3);
                                                    @endphp

                                                    @forelse ($highRiskCompanies as $company)
                                                        <div class="risk-item">
                                                            <span class="risk-name">{{ $company->name }}</span>
                                                            <span class="risk-amount text-danger">
                                                                {{ number_format(collect($company->remaining_by_currency ?? [])->sum(), 0) }}
                                                                ر.س
                                                            </span>
                                                        </div>
                                                    @empty
                                                        <div class="risk-item text-success">
                                                            <i class="fas fa-check-circle me-1"></i>لا توجد مخاطر
                                                            عالية حالياً
                                                        </div>
                                                    @endforelse

                                                    {{-- تنبيه الشركات الخاملة مالياً --}}
                                                    @php
                                                        $dormantCompanies = $companiesReport
                                                            ->filter(function ($company) {
                                                                // البحث عن آخر دفعة للشركة
                                                                $lastPayment = \App\Models\Payment::where(
                                                                    'company_id',
                                                                    $company->id,
                                                                )
                                                                    ->latest('payment_date')
                                                                    ->first();

                                                                if (!$lastPayment) {
                                                                    // إذا لم توجد أي دفعات، تحقق من تاريخ إنشاء الشركة
                                                                    return $company->created_at->diffInDays(now()) >= 7;
                                                                }

                                                                // إذا كان آخر دفع منذ أكثر من 7 أيام
                                                                return $lastPayment->payment_date->diffInDays(now()) >=
                                                                    7;
                                                            })
                                                            ->take(2);
                                                    @endphp

                                                    @if ($dormantCompanies->count() > 0)
                                                        <div class="mt-2 pt-2"
                                                            style="border-top: 1px dashed #f59e0b;">
                                                            <small class="text-warning fw-bold mb-1 d-block">
                                                                <i class="fas fa-clock me-1"></i>خاملة مالياً:
                                                            </small>
                                                            @foreach ($dormantCompanies as $company)
                                                                @php
                                                                    $lastPayment = \App\Models\Payment::where(
                                                                        'company_id',
                                                                        $company->id,
                                                                    )
                                                                        ->latest('payment_date')
                                                                        ->first();
                                                                    $daysSince = $lastPayment
                                                                        ? $lastPayment->payment_date->diffInDays(now())
                                                                        : $company->created_at->diffInDays(now());
                                                                    if ($lastPayment) {
                                                                        $daysSince = $lastPayment->payment_date->diffInDays(
                                                                            now(),
                                                                        );
                                                                        $lastDate = $lastPayment->payment_date->format(
                                                                            'd/m/Y',
                                                                        );
                                                                    } else {
                                                                        $daysSince = $company->created_at->diffInDays(
                                                                            now(),
                                                                        );
                                                                        $lastDate = 'لم يتم التعامل مطلقاً';
                                                                    }

                                                                    // تحويل الأيام إلى صيغة عربية مفهومة
                                                                    if ($daysSince < 7) {
                                                                        $periodText = $daysSince . ' أيام';
                                                                    } elseif ($daysSince < 30) {
                                                                        $weeks = floor($daysSince / 7);
                                                                        $remainingDays = $daysSince % 7;
                                                                        $periodText = $weeks . ' أسبوع';
                                                                        if ($remainingDays > 0) {
                                                                            $periodText .=
                                                                                ' و ' . $remainingDays . ' أيام';
                                                                        }
                                                                    } elseif ($daysSince < 365) {
                                                                        $months = floor($daysSince / 30);
                                                                        $remainingDays = $daysSince % 30;
                                                                        $periodText = $months . ' شهر';
                                                                        if ($remainingDays > 0) {
                                                                            $periodText .=
                                                                                ' و ' . $remainingDays . ' يوم';
                                                                        }
                                                                    } else {
                                                                        $years = floor($daysSince / 365);
                                                                        $remainingDays = $daysSince % 365;
                                                                        $periodText = $years . ' سنة';
                                                                        if ($remainingDays > 0) {
                                                                            $periodText .=
                                                                                ' و ' . $remainingDays . ' يوم';
                                                                        }
                                                                    }
                                                                @endphp
                                                                <div class="small text-muted mb-1"
                                                                    style="font-size: 0.75em; line-height: 1.3;">
                                                                    <span
                                                                        class="text-warning fw-bold">{{ $company->name }}</span><br>
                                                                    <span class="text-danger">⏰ منذ
                                                                        {{ $periodText }}</span>
                                                                    @if ($lastPayment)
                                                                        <br><span class="text-muted">آخر دفع:
                                                                            {{ $lastDate }}</span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- جهات لها مبالغ كبيرة --}}
                                            <div class="col-md-6 mb-3">
                                                <div class="risk-card">
                                                    <h6 class="risk-title text-warning">💸 جهات لها مبالغ كبيرة
                                                    </h6>
                                                    @php
                                                        $highPayoutAgents = $agentsReport
                                                            ->filter(function ($agent) {
                                                                $remaining = collect(
                                                                    $agent->remaining_by_currency ?? [],
                                                                )->sum();
                                                                return $remaining > 1000; // أكثر من 1,000 ريال
                                                            })
                                                            ->take(3);
                                                    @endphp

                                                    @forelse ($highPayoutAgents as $agent)
                                                        <div class="risk-item">
                                                            <span class="risk-name">{{ $agent->name }}</span>
                                                            <span class="risk-amount text-warning">
                                                                {{ number_format(collect($agent->remaining_by_currency ?? [])->sum(), 0) }}
                                                                ر.س
                                                            </span>
                                                        </div>
                                                    @empty
                                                        <div class="risk-item text-success">
                                                            <i class="fas fa-check-circle me-1"></i>لا توجد مبالغ
                                                            كبيرة مستحقة
                                                        </div>
                                                    @endforelse

                                                    {{-- تنبيه الجهات الخاملة مالياً --}}
                                                    @php
                                                        $dormantAgents = $agentsReport
                                                            ->filter(function ($agent) {
                                                                // البحث عن آخر دفعة للجهة
                                                                $lastPayment = \App\Models\AgentPayment::where(
                                                                    'agent_id',
                                                                    $agent->id,
                                                                )
                                                                    ->latest('payment_date')
                                                                    ->first();

                                                                if (!$lastPayment) {
                                                                    // إذا لم توجد أي دفعات، تحقق من تاريخ إنشاء الجهة
                                                                    return $agent->created_at->diffInDays(now()) >= 7;
                                                                }

                                                                // إذا كان آخر دفع منذ أكثر من 7 أيام
                                                                return $lastPayment->payment_date->diffInDays(now()) >=
                                                                    7;
                                                            })
                                                            ->take(2);
                                                    @endphp

                                                    @if ($dormantAgents->count() > 0)
                                                        <div class="mt-2 pt-2"
                                                            style="border-top: 1px dashed #f59e0b;">
                                                            <small class="text-warning fw-bold mb-1 d-block">
                                                                <i class="fas fa-clock me-1"></i>خاملة مالياً:
                                                            </small>
                                                            @foreach ($dormantAgents as $agent)
                                                                @php
                                                                    $lastPayment = \App\Models\AgentPayment::where(
                                                                        'agent_id',
                                                                        $agent->id,
                                                                    )
                                                                        ->latest('payment_date')
                                                                        ->first();
                                                                    $daysSince = $lastPayment
                                                                        ? $lastPayment->payment_date->diffInDays(now())
                                                                        : $agent->created_at->diffInDays(now());
                                                                    // تحويل شكل الأيام إلى صيغة مفهومة بدلا من (41.033943715937 أيام)
                                                                    // تحويل الأيام إلى صيغة عربية مفهومة
                                                                    if ($lastPayment) {
                                                                        $daysSince = $lastPayment->payment_date->diffInDays(
                                                                            now(),
                                                                        );
                                                                        $lastDate = $lastPayment->payment_date->format(
                                                                            'd/m/Y',
                                                                        );
                                                                    } else {
                                                                        $daysSince = $agent->created_at->diffInDays(
                                                                            now(),
                                                                        );
                                                                        $lastDate = 'لم يتم التعامل مطلقاً';
                                                                    }

                                                                    // تحويل الأيام إلى صيغة عربية مفهومة
                                                                    if ($daysSince < 7) {
                                                                        $periodText = $daysSince . ' أيام';
                                                                    } elseif ($daysSince < 30) {
                                                                        $weeks = floor($daysSince / 7);
                                                                        $remainingDays = $daysSince % 7;
                                                                        $periodText = $weeks . ' أسبوع';
                                                                        if ($remainingDays > 0) {
                                                                            $periodText .=
                                                                                ' و ' . $remainingDays . ' أيام';
                                                                        }
                                                                    } elseif ($daysSince < 365) {
                                                                        $months = floor($daysSince / 30);
                                                                        $remainingDays = $daysSince % 30;
                                                                        $periodText = $months . ' شهر';
                                                                        if ($remainingDays > 0) {
                                                                            $periodText .=
                                                                                ' و ' . $remainingDays . ' يوم';
                                                                        }
                                                                    } else {
                                                                        $years = floor($daysSince / 365);
                                                                        $remainingDays = $daysSince % 365;
                                                                        $periodText = $years . ' سنة';
                                                                        if ($remainingDays > 0) {
                                                                            $periodText .=
                                                                                ' و ' . $remainingDays . ' يوم';
                                                                        }
                                                                    }

                                                                @endphp
                                                                <div class="small text-muted mb-1"
                                                                    style="font-size: 0.75em; line-height: 1.3;">
                                                                    <span
                                                                        class="text-warning fw-bold">{{ $agent->name }}</span><br>
                                                                    <span class="text-danger">⏰ منذ
                                                                        {{ $periodText }}</span>
                                                                    @if ($lastPayment)
                                                                        <br><span class="text-muted">آخر دفع:
                                                                            {{ $lastDate }}</span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- تنبيه عام للجهات الخاملة (إضافي) --}}
                                        @php
                                            $allDormantEntities = collect();

                                            // جمع الشركات الخاملة
                                            $dormantCompaniesAll = $companiesReport
                                                ->filter(function ($company) {
                                                    $lastPayment = \App\Models\Payment::where(
                                                        'company_id',
                                                        $company->id,
                                                    )
                                                        ->latest('payment_date')
                                                        ->first();

                                                    if (!$lastPayment) {
                                                        return $company->created_at->diffInDays(now()) >= 14; // أسبوعين للشركات الجديدة
                                                    }

                                                    return $lastPayment->payment_date->diffInDays(now()) >= 14; // أسبوعين
                                                })
                                                ->map(function ($company) {
                                                    $lastPayment = \App\Models\Payment::where(
                                                        'company_id',
                                                        $company->id,
                                                    )
                                                        ->latest('payment_date')
                                                        ->first();

                                                    return [
                                                        'name' => $company->name,
                                                        'type' => 'شركة',
                                                        'days' => $lastPayment
                                                            ? floor($lastPayment->payment_date->diffInDays(now()))
                                                            : floor($company->created_at->diffInDays(now())),
                                                    ];
                                                });

                                            // جمع الجهات الخاملة
                                            $dormantAgentsAll = $agentsReport
                                                ->filter(function ($agent) {
                                                    $lastPayment = \App\Models\AgentPayment::where(
                                                        'agent_id',
                                                        $agent->id,
                                                    )
                                                        ->latest('payment_date')
                                                        ->first();

                                                    if (!$lastPayment) {
                                                        return $agent->created_at->diffInDays(now()) >= 14;
                                                    }

                                                    return $lastPayment->payment_date->diffInDays(now()) >= 14;
                                                })
                                                ->map(function ($agent) {
                                                    $lastPayment = \App\Models\AgentPayment::where(
                                                        'agent_id',
                                                        $agent->id,
                                                    )
                                                        ->latest('payment_date')
                                                        ->first();

                                                    return [
                                                        'name' => $agent->name,
                                                        'type' => 'جهة',
                                                        'days' => $lastPayment
                                                            ? floor($lastPayment->payment_date->diffInDays(now()))
                                                            : floor($agent->created_at->diffInDays(now())),
                                                    ];
                                                });

                                            $allDormantEntities = $dormantCompaniesAll
                                                ->concat($dormantAgentsAll)
                                                ->sortByDesc('days')
                                                ->take(5);
                                        @endphp

                                        @if ($allDormantEntities->count() > 0)
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <div class="alert alert-warning py-2 mb-0"
                                                        style="border-left: 4px solid #f59e0b;">
                                                        <small class="fw-bold text-warning mb-1 d-block">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            تنبيه: خاملة مالياً لأكثر من أسبوعين
                                                        </small>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach ($allDormantEntities as $entity)
                                                                <span class="badge bg-light text-dark"
                                                                    style="font-size: 0.7em;">
                                                                    {{ $entity['name'] }} ({{ $entity['type'] }} -
                                                                    {{ $entity['days'] }} يوم)
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                </div>
                </div>
                </div>
