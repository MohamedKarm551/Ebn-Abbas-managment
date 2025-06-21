@extends('layouts.app')
@section('title', 'التقارير اليومية')
@section('favicon')
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/daily_reports.css') }}">
@endpush




@section('content')
    <div class="container">
        {{-- خلي العنوان جمبه الصورة تظهر بشكل مناسب وريسبونسف --}}

        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4">
            {{-- العنوان --}}
            <h1 class="mb-3 mb-md-0">التقرير اليومي</h1> {{-- شيلنا التاريخ من هنا --}}
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
                            @php
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
                                    <li><i class="fas fa-arrow-up me-1 text-info"></i>
                                        <strong>{{ number_format($amount, 2) }}</strong>
                                        {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        {{-- 💰 إجمالي المدفوع من الشركات --}}
                        <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>إجمالي المدفوع من الشركات:</h6>
                        <ul class="list-unstyled">

                            {{-- عرض المدفوعات والخصومات من البيانات المُمررة من الكنترولر --}}
                            @if (isset($companyPaymentsByCurrency['SAR']))
                                <li>
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
                                <li>
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
                                    <li>
                                        <i
                                            class="fas {{ $remaining > 0 ? 'fa-exclamation-triangle text-danger' : 'fa-check-double text-success' }} me-1"></i>
                                        <span
                                            class="{{ $remaining > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
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
                                <li><i class="fas fa-check-circle me-1 text-success"></i>
                                    <span class="text-success fw-bold">جميع مستحقات الشركات مدفوعة! 🎉</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="col-md-6">
                        {{-- 📋 إجمالي المستحق للجهات --}}
                        <h6 class="text-warning"><i class="fas fa-hand-holding-usd me-2"></i>إجمالي المستحق للجهات:</h6>
                        <ul class="list-unstyled">
                            @php
                                // ✅ حساب إجمالي المستحق للجهات حسب العملة
                                $totalDueToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];
                                foreach ($agentsReport as $agent) {
                                    $dueByCurrency = $agent->total_due_by_currency ?? ['SAR' => $agent->total_due];
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
                                    <li><i class="fas fa-arrow-down me-1 text-warning"></i>
                                        <strong>{{ number_format($amount, 2) }}</strong>
                                        {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        {{-- 💳 إجمالي المدفوع للجهات --}}
                        <h6 class="text-success"><i class="fas fa-credit-card me-2"></i>إجمالي المدفوع للجهات:</h6>
                        <ul class="list-unstyled">
                            @php
                                // ✅ استخدام البيانات الصحيحة من الكنترولر
                                $displayPaidToAgents = $totalPaidToAgentsByCurrency ?? [];

                                // في حالة عدم وجود البيانات، نحسبها من agentPaymentsByCurrency
                                if (empty($displayPaidToAgents) && isset($agentPaymentsByCurrency)) {
                                    foreach ($agentPaymentsByCurrency as $currency => $data) {
                                        if (is_array($data) && isset($data['paid'])) {
                                            $displayPaidToAgents[$currency] = $data['paid'];
                                        }
                                    }
                                }
                            @endphp

                            @foreach ($displayPaidToAgents as $currency => $amount)
                                @if ($amount > 0)
                                    <li><i class="fas fa-check-circle me-1 text-success"></i>
                                        <strong>{{ number_format((float) $amount, 2) }}</strong>
                                        {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                    </li>
                                @endif
                            @endforeach

                            @if (empty($displayPaidToAgents) || array_sum($displayPaidToAgents) == 0)
                                <li><i class="fas fa-info-circle me-1 text-muted"></i>
                                    لا توجد مدفوعات مسجلة للجهات حتى الآن
                                </li>
                            @endif
                        </ul>

                        {{-- ⚠️ الباقي المطلوب للجهات --}}
                        <h6 class="text-warning"><i class="fas fa-hourglass-half me-2"></i>الباقي المطلوب للجهات:</h6>
                        <ul class="list-unstyled">
                            @php
                                // ✅ حساب المتبقي للجهات = المستحق - المدفوع
                                $totalRemainingToAgentsByCurrency = [];
                                $allAgentCurrencies = array_unique(
                                    array_merge(
                                        array_keys($totalDueToAgentsByCurrency),
                                        array_keys($totalPaidToAgentsByCurrency),
                                    ),
                                );

                                foreach ($allAgentCurrencies as $currency) {
                                    $due = $totalDueToAgentsByCurrency[$currency] ?? 0;
                                    $paid = $totalPaidToAgentsByCurrency[$currency] ?? 0;
                                    $remaining = $due - $paid;

                                    if ($remaining > 0) {
                                        // عرض فقط الموجب
                                        $totalRemainingToAgentsByCurrency[$currency] = $remaining;
                                    }
                                }
                            @endphp
                            @foreach ($totalRemainingToAgentsByCurrency as $currency => $remaining)
                                <li><i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                    <span class="text-warning fw-bold">{{ number_format($remaining, 2) }}</span>
                                    {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                </li>
                            @endforeach
                            @if (empty($totalRemainingToAgentsByCurrency))
                                <li><i class="fas fa-check-circle me-1 text-success"></i>
                                    <span class="text-success fw-bold">جميع مستحقات الجهات مدفوعة! 🎉</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- ⚖️ صافي الرصيد الإجمالي --}}
                <hr class="my-4">
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-center mb-3">
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

                        {{-- 📈 ملخص تفصيلي سريع --}}
                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading"><i class="fas fa-chart-line me-2"></i>ملخص تفصيلي:</h6>
                            @foreach ($allCompanyCurrencies ?? [] as $currency)
                                @php
                                    $due = $totalDueFromCompaniesByCurrency[$currency] ?? 0;
                                    $paid = $totalPaidByCompaniesByCurrency[$currency] ?? 0;
                                    $remaining = $due - $paid;
                                    $percentage = $due > 0 ? round(($paid / $due) * 100, 1) : 0;
                                    $currencyName = $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي';
                                @endphp
                                @if ($due > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>{{ $currencyName }}:</strong>
                                        <div class="text-end">
                                            <small class="d-block">
                                                {{ number_format($due, 2) }} مطلوب -
                                                {{ number_format($paid, 2) }} مدفوع =
                                                <span class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($remaining, 2) }} متبقي
                                                </span>
                                            </small>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar {{ $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                    style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $percentage }}% مدفوع</small>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- 📊 الرسوم البيانية المحسنة --}}
        <div class="row mt-5">
            <div class="col-md-12 mb-4">
                <div class="chart-card position-relative overflow-hidden">
                    {{-- خلفية متدرجة متحركة --}}
                    <div class="chart-bg-gradient"></div>

                    {{-- Header محسن --}}
                    <div class="chart-header position-relative">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="chart-title-section">
                                <div class="chart-icon-wrapper">
                                    <i class="fas fa-chart-line chart-main-icon"></i>
                                </div>
                                <div class="chart-title-content">
                                    <h5 class="chart-title mb-1">اتجاه صافي الرصيد مع الوقت</h5>
                                    <p class="chart-subtitle mb-0">تتبع ديناميكي لحركة الأرصدة المالية</p>
                                </div>
                            </div>

                            {{-- أزرار التحكم --}}
                            <div class="chart-controls d-flex gap-2">
                                <button class="chart-control-btn" id="fullscreenBtn" title="شاشة كاملة">
                                    <i class="fas fa-expand-alt"></i>
                                </button>
                                <button class="chart-control-btn" id="downloadBtn" title="تحميل كصورة">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="chart-control-btn" id="refreshBtn" title="تحديث">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        {{-- مؤشرات الحالة --}}
                        <div class="chart-indicators mt-3">
                            <div class="indicator-item">
                                <div class="indicator-dot positive"></div>
                                <span class="indicator-text">رصيد موجب (لك)</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-dot negative"></div>
                                <span class="indicator-text">رصيد سالب (عليك)</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-dot neutral"></div>
                                <span class="indicator-text">نقطة التوازن</span>
                            </div>
                        </div>
                    </div>

                    {{-- Chart Container محسن --}}
                    <div class="chart-body position-relative">
                        <div class="chart-container-enhanced">
                            <canvas id="netBalanceChart" class="main-chart"></canvas>

                            {{-- Loading Animation --}}
                            <div class="chart-loading" id="chartLoading">
                                <div class="loading-spinner">
                                    <div class="spinner-ring"></div>
                                    <div class="spinner-ring"></div>
                                    <div class="spinner-ring"></div>
                                </div>
                                <p class="loading-text">جاري تحميل البيانات...</p>
                            </div>
                        </div>

                        {{-- Chart Info Panel --}}
                        <div class="chart-info-panel">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">آخر تحديث</span>
                                    <span class="info-value" id="lastUpdate">{{ now()->format('H:i d/m/Y') }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-chart-bar info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">نقاط البيانات</span>
                                    <span class="info-value" id="dataPoints">--</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-trending-up info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">الاتجاه</span>
                                    <span class="info-value trend-indicator" id="trendIndicator">--</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Chart Footer --}}
                    <div class="chart-footer">
                        <div class="chart-description">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>يمثل الخط التغير في صافي الرصيد (الموجب = لك، السالب = عليك) بناءً على العمليات
                                المسجلة</span>
                        </div>

                        {{-- Quick Stats --}}
                        <div class="quick-stats mt-3">
                            <div class="stat-card positive">
                                <div class="stat-icon">
                                    <i class="fas fa-arrow-trend-up"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-label">أعلى رصيد</span>
                                    <span class="stat-value" id="maxBalance">--</span>
                                </div>
                            </div>

                            <div class="stat-card negative">
                                <div class="stat-icon">
                                    <i class="fas fa-arrow-trend-down"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-label">أقل رصيد</span>
                                    <span class="stat-value" id="minBalance">--</span>
                                </div>
                            </div>

                            <div class="stat-card neutral">
                                <div class="stat-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-label">المتوسط</span>
                                    <span class="stat-value" id="avgBalance">--</span>
                                </div>
                            </div>

                            <div class="stat-card info">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-label">الحالي</span>
                                    <span class="stat-value" id="currentBalance">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📈 رسم بياني إضافي للدينار محسن --}}
        <div class="mb-4">
            <div class="collapse-card">
                <button class="collapse-btn" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseNetBalanceKWD" aria-expanded="false">
                    <div class="collapse-btn-content">
                        <div class="collapse-icon-wrapper">
                            <i class="fas fa-chart-area collapse-icon"></i>
                        </div>
                        <div class="collapse-text-content">
                            <span class="collapse-title">صافي الرصيد بالدينار الكويتي</span>
                            <span class="collapse-subtitle">عرض تفصيلي للعملة الكويتية</span>
                        </div>
                    </div>
                    <div class="collapse-arrow">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </button>

                <div class="collapse" id="collapseNetBalanceKWD">
                    <div class="collapse-content">
                        <div class="chart-container-secondary">
                            <canvas id="netBalanceKWDChart" class="secondary-chart"></canvas>
                        </div>

                        {{-- KWD Stats --}}
                        <div class="kwd-stats mt-3">
                            <div class="kwd-stat-item">
                                <i class="fas fa-coins"></i>
                                <span>إجمالي بالدينار: <strong id="kwdTotal">0.00 د.ك</strong></span>
                            </div>
                            <div class="kwd-stat-item">
                                <i class="fas fa-percentage"></i>
                                <span>نسبة التغيير: <strong id="kwdChange">0.0%</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- 📋 قوائم أعلى الشركات والجهات --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            أعلى 5 شركات عليها مبالغ
                        </h5>
                        @php
                            $topCompanies = $companiesReport->sortByDesc('remaining_amount')->take(5);
                        @endphp
                        <ul class="list-unstyled mb-2 small">
                            @forelse ($topCompanies as $company)
                                @php
                                    $remainingByCurrency = $company->remaining_by_currency ?? [
                                        'SAR' => $company->remaining_amount,
                                    ];
                                    $hasPositiveRemaining = collect($remainingByCurrency)
                                        ->filter(fn($amount) => $amount > 0)
                                        ->isNotEmpty();
                                @endphp
                                @if ($hasPositiveRemaining)
                                    <li class="mb-1">
                                        <strong>{{ $company->name }}:</strong>
                                        @foreach ($remainingByCurrency as $currency => $amount)
                                            @if ($amount > 0)
                                                <span class="badge bg-danger">
                                                    {{ number_format($amount, 0) }}
                                                    {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </li>
                                @endif
                            @empty
                                <li class="text-muted">لا توجد شركات عليها مبالغ متبقية حاليًا.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="h-100">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-money-check-alt me-1"></i>
                            أعلى 5 جهات لها مبالغ
                        </h5>
                        @php
                            $topAgents = $agentsReport->sortByDesc('remaining_amount')->take(5);
                        @endphp
                        <ul class="list-unstyled mb-2 small">
                            @forelse ($topAgents as $agent)
                                @php
                                    $remainingByCurrency = $agent->remaining_by_currency ?? [
                                        'SAR' => $agent->remaining_amount,
                                    ];
                                    $hasPositiveRemaining = collect($remainingByCurrency)
                                        ->filter(fn($amount) => $amount > 0)
                                        ->isNotEmpty();
                                @endphp
                                @if ($hasPositiveRemaining)
                                    <li class="mb-1">
                                        <strong>{{ $agent->name }}:</strong>
                                        @foreach ($remainingByCurrency as $currency => $amount)
                                            @if ($amount > 0)
                                                <span class="badge bg-warning">
                                                    {{ number_format($amount, 0) }}
                                                    {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </li>
                                @endif
                            @empty
                                <li class="text-muted">لا توجد جهات لها مبالغ متبقية حاليًا.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        {{-- *** نهاية الرسم البياني الجديد *** --}}

        {{-- *** نهاية قسم لوحة المعلومات المصغرة *** --}}



        {{-- <div class=" mb-4">
            <div class="card-header">
                <h3>ملخص اليوم</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled" style="padding: 1%;margin: 1%;
">
                            <li>
                                <a href="{{ route('bookings.index', ['start_date' => now()->format('d/m/Y')]) }}"
                                    class="fw-bold text-decoration-none text-primary">
                                    عدد الحجوزات اليوم: {{ $todayBookings->count() }}
                                </a>
                            </li>

                            <li class="fw-bold">إجمالي المتبقي من الشركات:
                                {{ number_format($totalRemainingFromCompanies) }}
                                ريال</li>
                            <li class="fw-bold">إجمالي المتبقي للفنادق (جهات الحجز):
                                {{ number_format($totalRemainingToHotels) }} ريال</li>
                            <li class="fw-bold">صافي الربح: {{ number_format($netProfit) }} ريال</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
 --}}
        <!-- جدول الشركات -->
        <div class="  mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>حساب المطلوب من الشركات</h3>
                <button class="btn btn-secondary btn-sm" onclick="copyTable('companiesTable')">نسخ الجدول</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-bordered table-striped" id="companiesTable">
                        <thead>
                            <tr>
                                <th>الشركة</th>
                                <th>عدد الحجوزات</th>
                                <th>إجمالي المستحق</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($companiesReport as $company)
                                <tr>
                                    <td>{{ $loop->iteration }}. {{ $company->name }}
                                        @php
                                            $hasEdit =
                                                $recentCompanyEdits
                                                    ->filter(function ($n) use ($company) {
                                                        return str_contains($n->first()->message, $company->name);
                                                    })
                                                    ->count() > 0;
                                        @endphp
                                        @if ($hasEdit)
                                            <span class="badge bg-success" style="font-size: 0.7em;">edit</span>
                                        @endif
                                    </td>
                                    <td>{{ $company->bookings_count }}</td>
                                    <td>
                                        @php
                                            $dueByCurrency = $company->total_due_by_currency ?? [
                                                'SAR' => $company->total_due,
                                            ];
                                        @endphp
                                        @foreach ($dueByCurrency as $currency => $amount)
                                            {{ number_format($amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endforeach
                                    </td>
                                    <td
                                        @if ($company->total_paid > $company->total_due) style="color: red !important; font-weight: bold;" title="المبلغ المدفوع أكثر من المستحق" @endif>
                                        @php
                                            $paymentsByCurrency = $company->payments
                                                ? $company->payments->groupBy('currency')
                                                : collect();
                                        @endphp
                                        @forelse ($paymentsByCurrency as $currency => $payments)
                                            @php
                                                $positivePaid = $payments->where('amount', '>=', 0)->sum('amount');
                                                $discounts = $payments->where('amount', '<', 0)->sum('amount');
                                                $discountsAbsolute = abs($discounts);
                                            @endphp
                                            <div class="mb-1">
                                                <strong
                                                    class="text-success">{{ number_format($positivePaid, 2) }}</strong>
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                @if ($discountsAbsolute > 0)
                                                    <br><small class="text-warning">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        خصومات: {{ number_format($discountsAbsolute, 2) }}
                                                        {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                    </small>
                                                @endif
                                            </div>
                                        @empty
                                            0 ريال
                                        @endforelse
                                    </td>
                                    <td>
                                        @php
                                            // حساب المتبقي حسب العملة
                                            //  $company->remaining_bookings_by_currency : يعني المبلغ المتبقي لكل عملة
                                            $remainingBookingsByCurrency = $company->remaining_bookings_by_currency;
                                            //
                                        @endphp

                                        @foreach ($remainingBookingsByCurrency as $currency => $amount)
                                            {{-- print  --}}
                                            {{ number_format($amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endforeach

                                    </td>
                                    <td>
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                            <a href="{{ route('reports.company.bookings', $company->id) }}"
                                                class="btn btn-info btn-sm">عرض الحجوزات</a>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#paymentModal{{ $company->id }}">
                                                تسجيل دفعة
                                            </button>
                                            <a href="{{ route('reports.company.payments', $company->id) }}"
                                                class="btn btn-primary btn-sm">كشف حساب </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td class="text-center">الإجمالي</td>
                                <td class="text-center">
                                    @php
                                        $totalBookingsCount = $companiesReport->sum('bookings_count');
                                    @endphp
                                    {{ $totalBookingsCount }}
                                </td>
                                <td>
                                    @php
                                        $totalDueByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];
                                        foreach ($companiesReport as $company) {
                                            $dueByCurrency = $company->total_due_by_currency ?? [
                                                'SAR' => $company->total_due,
                                            ];
                                            foreach ($dueByCurrency as $currency => $amount) {
                                                $totalDueByCurrency[$currency] += (float) $amount; // ✅ إصلاح: $amount هو رقم مباشرة
                                            }
                                        }
                                    @endphp
                                    @foreach ($totalDueByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            {{ number_format((float) $amount, 2) }} {{-- ✅ إصلاح: استخدام $amount مباشرة --}}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    {{-- عرض المدفوعات مع فصل الخصومات --}}
                                    @if (isset($companyPaymentsByCurrency['SAR']))
                                        <div class="mb-1">
                                            <strong
                                                class="text-success">{{ number_format((float) ($companyPaymentsByCurrency['SAR']['paid'] ?? 0), 2) }}</strong>
                                            ريال
                                            @if (($companyPaymentsByCurrency['SAR']['discounts'] ?? 0) > 0)
                                                <br><small class="text-warning">
                                                    <i class="fas fa-minus-circle me-1"></i>
                                                    خصومات:
                                                    {{ number_format((float) $companyPaymentsByCurrency['SAR']['discounts'], 2) }}
                                                    ريال
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                    @if (isset($companyPaymentsByCurrency['KWD']))
                                        <div>
                                            <strong
                                                class="text-success">{{ number_format((float) ($companyPaymentsByCurrency['KWD']['paid'] ?? 0), 2) }}</strong>
                                            دينار
                                            @if (($companyPaymentsByCurrency['KWD']['discounts'] ?? 0) > 0)
                                                <br><small class="text-warning">
                                                    <i class="fas fa-minus-circle me-1"></i>
                                                    خصومات:
                                                    {{ number_format((float) $companyPaymentsByCurrency['KWD']['discounts'], 2) }}
                                                    دينار
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>

                                    {{-- المتبقي للشركات - حساب مباشر من المستحق والمدفوع --}}
                                    @php
                                        $totalCompanyRemainingByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];

                                        // حساب المتبقي الصحيح = إجمالي المستحق - إجمالي المدفوع
                                        foreach (['SAR', 'KWD'] as $currency) {
                                            // 1. إجمالي المستحق حسب العملة (من المتغير المحسوب مسبقاً)
                                            $totalDue = $totalDueByCurrency[$currency] ?? 0;

                                            // 2. إجمالي المدفوع حسب العملة (من المتغير المحسوب مسبقاً)
                                            $totalPaid = $companyPaymentsByCurrency[$currency]['paid'] ?? 0;
                                            $totalDiscounts = $companyPaymentsByCurrency[$currency]['discounts'] ?? 0;

                                            // 3. حساب المتبقي = المستحق - (المدفوع - الخصومات)
                                            // ملاحظة: الخصومات موجبة في المتغير لكنها تقلل من المدفوع
                                            $netPaid = $totalPaid + $totalDiscounts; // الخصومات تضاف للمدفوع الفعلي
                                            $remaining = $totalDue - $netPaid;

                                            if ($remaining != 0) {
                                                $totalCompanyRemainingByCurrency[$currency] = $remaining;
                                            }
                                        }
                                    @endphp

                                    @foreach ($totalCompanyRemainingByCurrency as $currency => $amount)
                                        @if ($amount != 0)
                                            <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $amount > 0 ? '+' : '' }}{{ number_format((float) $amount, 2) }}
                                            </span>
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                            @if ($amount < 0)
                                                <small class="text-muted">(دفعوا زيادة)</small>
                                            @endif
                                        @endif
                                    @endforeach

                                    {{-- إذا كان المجموع صفر في كل العملات --}}
                                    @if (empty(array_filter($totalCompanyRemainingByCurrency)))
                                        <span class="text-success">0.00 ريال</span><br>
                                        <small class="text-muted">(متوازن)</small>
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- جدول جهات الحجز -->
        <div class="mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>حساب المستحق إلى جهات الحجز</h3>
                <button class="btn btn-secondary btn-sm" onclick="copyTable('agentsTable')">نسخ الجدول</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="agentsTable">
                        <thead>
                            <tr>
                                <th>جهة الحجز</th>
                                <th>عدد الحجوزات</th>
                                <th>إجمالي المستحق</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agentsReport as $agent)
                                <tr>
                                    <td>{{ $loop->iteration }}.{{ $agent->name }}
                                        @php
                                            $hasEdit =
                                                $resentAgentEdits
                                                    ->filter(function ($n) use ($agent) {
                                                        return str_contains($n->first()->message, $agent->name);
                                                    })
                                                    ->count() > 0;
                                        @endphp
                                        @if ($hasEdit)
                                            <span class="badge bg-success" style="font-size: 0.7em;">edit</span>
                                        @endif
                                    </td>
                                    <td>{{ $agent->bookings_count }}</td>
                                    <td>
                                        @php
                                            $dueByCurrency = $agent->total_due_by_currency ?? [
                                                'SAR' => $agent->total_due ?? 0,
                                            ];
                                        @endphp
                                        @foreach ($dueByCurrency as $currency => $amount)
                                            @if ($amount > 0)
                                                {{ number_format((float) $amount, 2) }}
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @php
                                            $paymentsByCurrency = $agent->payments
                                                ? $agent->payments->groupBy('currency')
                                                : collect();
                                        @endphp
                                        @forelse ($paymentsByCurrency as $currency => $payments)
                                            @php
                                                $positivePaid = $payments->where('amount', '>=', 0)->sum('amount');
                                                $discounts = $payments->where('amount', '<', 0)->sum('amount');
                                                $discountsAbsolute = abs($discounts);
                                            @endphp
                                            <div class="mb-1">
                                                <strong
                                                    class="text-success">{{ number_format((float) $positivePaid, 2) }}</strong>
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                @if ($discountsAbsolute > 0)
                                                    <br><small class="text-warning">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        خصومات: {{ number_format((float) $discountsAbsolute, 2) }}
                                                        {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                    </small>
                                                @endif
                                            </div>
                                        @empty
                                            0 ريال
                                        @endforelse
                                    </td>
                                    <td>
                                        @php
                                            // حساب المتبقي حسب العملة (نفس طريقة الشركات)
                                            $remainingAgentByCurrency = $agent->remaining_by_currency ?? [
                                                'SAR' => $agent->remaining_amount ?? 0,
                                            ];
                                        @endphp
                                        @foreach ($remainingAgentByCurrency as $currency => $amount)
                                            @if ($amount != 0)
                                                <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ $amount > 0 ? '+' : '' }}{{ number_format((float) $amount, 2) }}
                                                </span>
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                                @if ($amount < 0)
                                                    <small class="text-muted">(دفعنا زيادة)</small>
                                                @endif
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                            <a href="{{ route('reports.agent.bookings', $agent->id) }}"
                                                class="btn btn-info btn-sm">عرض الحجوزات</a>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#agentPaymentModal{{ $agent->id }}">
                                                تسجيل دفعة
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#agentDiscountModal{{ $agent->id }}">
                                                تطبيق خصم
                                            </button>
                                            <a href="{{ route('reports.agent.payments', $agent->id) }}"
                                                class="btn btn-primary btn-sm">كشف حساب</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td class="text-center">الإجمالي</td>
                                <td class="text-center">
                                    @php
                                        $totalAgentBookingsCount = $agentsReport->sum('bookings_count');
                                    @endphp
                                    {{ $totalAgentBookingsCount }}
                                </td>
                                <td>
                                    @php
                                        $totalAgentDueByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];
                                        foreach ($agentsReport as $agent) {
                                            $dueByCurrency = $agent->total_due_by_currency ?? [
                                                'SAR' => $agent->total_due ?? 0,
                                            ];
                                            foreach ($dueByCurrency as $currency => $amount) {
                                                $totalAgentDueByCurrency[$currency] += (float) $amount;
                                            }
                                        }
                                    @endphp
                                    @foreach ($totalAgentDueByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            {{ number_format((float) $amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    {{-- عرض المدفوعات مع فصل الخصومات (نفس طريقة الشركات) --}}
                                    @if (isset($agentPaymentsByCurrency['SAR']))
                                        <div class="mb-1">
                                            <strong
                                                class="text-success">{{ number_format((float) ($agentPaymentsByCurrency['SAR']['paid'] ?? 0), 2) }}</strong>
                                            ريال
                                            @if (($agentPaymentsByCurrency['SAR']['discounts'] ?? 0) > 0)
                                                <br><small class="text-warning">
                                                    <i class="fas fa-minus-circle me-1"></i>
                                                    خصومات:
                                                    {{ number_format((float) $agentPaymentsByCurrency['SAR']['discounts'], 2) }}
                                                    ريال
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                    @if (isset($agentPaymentsByCurrency['KWD']))
                                        <div>
                                            <strong
                                                class="text-success">{{ number_format((float) ($agentPaymentsByCurrency['KWD']['paid'] ?? 0), 2) }}</strong>
                                            دينار
                                            @if (($agentPaymentsByCurrency['KWD']['discounts'] ?? 0) > 0)
                                                <br><small class="text-warning">
                                                    <i class="fas fa-minus-circle me-1"></i>
                                                    خصومات:
                                                    {{ number_format((float) $agentPaymentsByCurrency['KWD']['discounts'], 2) }}
                                                    دينار
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $totalAgentRemainingByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];
                                        foreach ($agentsReport as $agent) {
                                            $remainingByCurrency = $agent->remaining_by_currency ?? [
                                                'SAR' => $agent->remaining_amount ?? 0,
                                            ];
                                            foreach ($remainingByCurrency as $currency => $amount) {
                                                $totalAgentRemainingByCurrency[$currency] += (float) $amount;
                                            }
                                        }
                                    @endphp
                                    @foreach ($totalAgentRemainingByCurrency as $currency => $amount)
                                        @if ($amount != 0)
                                            <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $amount > 0 ? '+' : '' }}{{ number_format((float) $amount, 2) }}
                                            </span>
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>


        <!-- نماذج تسجيل الدفعات لجهات الحجز -->
        @foreach ($agentsReport as $agent)
            <!-- نموذج الدفعة العادية -->
            <div class="modal fade" id="agentPaymentModal{{ $agent->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('reports.agent.payment') }}" method="POST">
                            @csrf
                            <input type="hidden" name="agent_id" value="{{ $agent->id }}">

                            <div class="modal-header">
                                <h5 class="modal-title">تسجيل دفعة - {{ $agent->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">المبلغ المدفوع والعملة</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" name="amount"
                                            required>
                                        <select class="form-select" name="currency" style="max-width: 120px;">
                                            <option value="SAR" selected>ريال سعودي</option>
                                            <option value="KWD">دينار كويتي</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea class="form-control" name="notes"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                <button type="submit" class="btn btn-primary">تسجيل الدفعة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- نموذج الخصم المنفصل -->
            <div class="modal fade" id="agentDiscountModal{{ $agent->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('reports.agent.discount', $agent->id) }}" method="POST">
                            @csrf

                            <div class="modal-header">
                                <h5 class="modal-title">تطبيق خصم - {{ $agent->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">مبلغ الخصم والعملة</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" name="discount_amount"
                                            required>
                                        <select class="form-select" name="currency" style="max-width: 120px;">
                                            <option value="SAR" selected>ريال سعودي</option>
                                            <option value="KWD">دينار كويتي</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">سبب الخصم</label>
                                    <textarea class="form-control" name="reason" placeholder="اختياري - سبب تطبيق الخصم"></textarea>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    تأكد من مبلغ الخصم قبل المتابعة. هذا الإجراء سيؤثر على الحساب النهائي للوكيل.
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                <button type="submit" class="btn btn-warning">تطبيق الخصم</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- إضافة سكريبت  النسخ والخصم -->
        @push('scripts')
            <script>
                function copyTable(tableId) {
                    const table = document.getElementById(tableId);
                    const range = document.createRange();
                    range.selectNode(table);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    document.execCommand('copy');
                    window.getSelection().removeAllRanges();
                    alert('تم نسخ الجدول');
                }

                function toggleAgentDiscountMode(agentId) {
                    const isDiscountField = document.getElementById('is-discount-' + agentId);
                    const submitBtn = document.getElementById('agentSubmitBtn-' + agentId);
                    const toggleBtn = document.getElementById('toggleAgentDiscountBtn-' + agentId);
                    const modalTitle = document.querySelector('#agentPaymentModalTitle' + agentId);
                    const agentName = modalTitle.textContent.split('-')[1].trim();

                    if (isDiscountField.value === "0") {
                        // تحويل إلى وضع الخصم
                        isDiscountField.value = "1";
                        submitBtn.textContent = "تطبيق الخصم";
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-warning');
                        toggleBtn.textContent = "تسجيل دفعة";
                        modalTitle.textContent = "تسجيل خصم - " + agentName;
                    } else {
                        // العودة إلى وضع الدفع
                        isDiscountField.value = "0";
                        submitBtn.textContent = "تسجيل الدفعة";
                        submitBtn.classList.remove('btn-warning');
                        submitBtn.classList.add('btn-primary');
                        toggleBtn.textContent = "تسجيل خصم";
                        modalTitle.textContent = "تسجيل دفعة - " + agentName;
                    }
                }
            </script>
        @endpush

        <!-- نموذج تسجيل الدفعات -->
        @foreach ($companiesReport as $company)
            <div class="modal fade" id="paymentModal{{ $company->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('reports.company.payment') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                            <input type="hidden" name="is_discount" id="is-discount-{{ $company->id }}"
                                value="0">

                            <div class="modal-header">
                                <h5 class="modal-title">تسجيل دفعة - {{ $company->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">المبلغ المدفوع والعملة</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" name="amount"
                                            required>
                                        <select class="form-select" name="currency" style="max-width: 120px;">
                                            <option value="SAR" selected>ريال سعودي</option>
                                            <option value="KWD">دينار كويتي</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- *** أضف حقل رفع الملف مشكلة مع جوجل درايف لسه هتتحل  *** --}}
                                {{-- <div class="mb-3">
                                    <label for="receipt_file_company_{{ $company->id }}" class="form-label">إرفاق إيصال
                                        (اختياري)
                                    </label>
                                    <input class="form-control" type="file"
                                        id="receipt_file_company_{{ $company->id }}" name="receipt_file">
                                  
                                <small class="form-text text-muted">الملفات المسموحة: JPG, PNG, PDF (بحد أقصى
                                    5MB)</small>
                            </div> --}}
                                {{-- *** نهاية حقل رفع الملف *** --}}
                                <div class="mb-3">
                                    <label class="form-label">ملاحظات <br>
                                        (إن كانت معك صورة من التحويل ارفعها على درايف وضع الرابط هنا)
                                    </label>
                                    <textarea class="form-control" name="notes"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                <button type="button" class="btn btn-warning"
                                    id="toggleDiscountBtn-{{ $company->id }}"
                                    onclick="toggleDiscountMode({{ $company->id }})">تسجيل خصم</button>
                                <button type="submit" class="btn btn-primary" id="submitBtn-{{ $company->id }}">تسجيل
                                    الدفعة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- جدول الفنادق -->
        <div class="mb-4">
            <div class="card-header">
                <h3>حسابات الفنادق</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>الفندق</th>
                                <th>عدد الحجوزات</th>
                                <th>إجمالي المستحق</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hotelsReport as $hotel)
                                <tr>
                                    <td>{{ $loop->iteration }}. {{ $hotel->name }}</td>
                                    <td>{{ $hotel->bookings_count }}</td>
                                    <td>
                                        @php
                                            $dueByCurrency = $hotel->total_due_by_currency ?? [
                                                'SAR' => $hotel->total_due,
                                            ];
                                        @endphp
                                        @foreach ($dueByCurrency as $currency => $amount)
                                            {{ number_format($amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a href="{{ route('reports.hotel.bookings', $hotel->id) }}"
                                            class="btn btn-info btn-sm">عرض الحجوزات</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td class="text-center">الإجمالي</td>
                                <td class="text-center">
                                    @php
                                        $totalHotelBookingsCount = $hotelsReport->sum('bookings_count');
                                    @endphp
                                    {{ $totalHotelBookingsCount }}
                                </td>
                                <td>
                                    @php
                                        $totalDueByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];
                                        foreach ($hotelsReport as $hotel) {
                                            $dueByCurrency = $hotel->total_due_by_currency ?? [
                                                'SAR' => $hotel->total_due,
                                            ];
                                            foreach ($dueByCurrency as $currency => $amount) {
                                                $totalDueByCurrency[$currency] += $amount;
                                            }
                                        }
                                    @endphp
                                    @foreach ($totalDueByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            {{ number_format($amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- إضافة تنسيقات CSS في القسم الخاص بالستيلات -->

    {{-- *** الخطوة 5: JavaScript لإنشاء الرسوم البيانية *** --}}
    {{-- C:\xampp\htdocs\Ebn-Abbas-managment\public\js\daily.js --}}
    @push('scripts')
        {{-- 1. تضمين Chart.js (إذا لم يكن مضمنًا في app.blade.php) --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        {{-- حفظ الصفحة صورة أو بي دي اف  --}}
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

        {{-- 2. تمرير البيانات من PHP إلى JavaScript --}}
        <script>
            // نضع البيانات في كائن window لسهولة الوصول إليها من الملف الخارجي
            window.chartData = {
                // بيانات الرسم البياني للحجوزات اليومية
                dailyLabels: @json($chartDates ?? []),
                dailyData: @json($bookingCounts ?? []),

                // بيانات الرسم البياني للمستحقات والالتزامات
                receivableBalances: @json($receivableBalances ?? []),
                payableBalances: @json($payableBalances ?? []),
                dailyEventDetails: @json($dailyEventDetails ?? []),
                // بيانات الرسم البياني لصافي الرصيد
                // بيانات الرسم البياني لصافي الرصيد
                netBalanceDates: @json($netBalanceDates ?? []),
                netBalances: @json($netBalances ?? []), // للريال
                netBalancesKWD: @json($netBalancesKWD ?? []), // للدينار
                dailyEventDetails: @json($dailyEventDetails ?? []), // الاحتفاظ بهذا

                // بيانات الرسم البياني للشركات والجهات
                topCompaniesLabels: @json($topCompanies->pluck('name') ?? []),
                topCompaniesRemaining: @json($topCompanies->pluck('remaining') ?? []),
                topCompaniesBookingCounts: @json($topCompanies->pluck('bookings_count') ?? []),
                topAgentsLabels: @json($topAgents->pluck('name') ?? []),
                topAgentsRemaining: @json($topAgents->pluck('remaining') ?? []),

                // // بيانات مقارنة المتبقي (القديمة - للتوافق مع الكود القديم)
                // totalRemainingFromCompanies: {{ $totalRemainingFromCompanies ?? 0 }},
                // totalRemainingToHotels: {{ $totalRemainingToHotels ?? 0 }},

                // بيانات حجوزات الشركات
                totalCompanyBookings: {{ $companiesReport->sum('bookings_count') ?? 0 }},

                totalDueFromCompaniesByCurrency: @json($totalDueFromCompaniesByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                totalPaidByCompaniesByCurrency: @json($totalPaidByCompaniesByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                totalRemainingFromCompaniesByCurrency: @json($totalRemainingFromCompaniesByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                totalDueToAgentsByCurrency: @json($totalDueToAgentsByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                totalPaidToAgentsByCurrency: @json($totalPaidToAgentsByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                totalRemainingToAgentsByCurrency: @json($totalRemainingToAgentsByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                netBalanceByCurrency: @json($netBalanceByCurrency ?? ['SAR' => 0, 'KWD' => 0]),

                // بيانات الرسم البياني الأساسية
                netBalanceDates: @json($netBalanceDates ?? []),
                netBalances: @json($netBalances ?? []), // للريال
                netBalancesKWD: @json($netBalancesKWD ?? []), // للدينار
                dailyEventDetails: @json($dailyEventDetails ?? []),

                // إعدادات التصميم
                chartTheme: {
                    primaryGradient: ['#667eea', '#764ba2'],
                    secondaryGradient: ['#f093fb', '#f5576c'],
                    positiveColor: '#10b981',
                    negativeColor: '#ef4444',
                    neutralColor: '#6b7280'
                }
            };
        </script>

        {{-- 3. استدعاء ملف JavaScript الخارجي --}}
        <script src="{{ asset('js/daily.js') }}"></script>

        {{-- 4. تعريف دالة النسخ --}}
        <script>
            function copyTable(tableId) {
                const table = document.getElementById(tableId);
                if (!table) return; // تأكد من وجود الجدول
                const range = document.createRange();
                range.selectNode(table);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                try {
                    document.execCommand('copy');
                    alert('تم نسخ الجدول');
                } catch (err) {
                    alert('فشل نسخ الجدول. حاول مرة أخرى.');
                }
                window.getSelection().removeAllRanges();
            }
        </script>
        <script>
            // حفظ صورة الصفحة كل دقيقة  وتخزينها في ملف باك أب 
            // function savePageScreenshot() {
            //     html2canvas(document.body).then(function(canvas) {
            //         // حول الصورة لـ base64
            //         var imageData = canvas.toDataURL('image/png');
            //         // ابعت الصورة للسيرفر
            //         fetch('/save-screenshot', {
            //                 method: 'POST',
            //                 headers: {
            //                     'Content-Type': 'application/json',
            //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //                 },
            //                 body: JSON.stringify({
            //                     image: imageData
            //                 })
            //             }).then(res => res.json())
            //             .then(data => {
            //                 console.log('تم حفظ الصورة:', data.path);
            //             }).catch(err => {
            //                 console.error('خطأ في رفع الصورة:', err);
            //             });
            //     });
            // }

            // // شغل الدالة أول مرة
            // savePageScreenshot();
            // // وجدولها كل 1 دقائق (60000 ms)
            // setInterval(savePageScreenshot, 60000);
            //  نهاية دالة حفظ الصورة

            // ==============================================================
            // function savePagePDF() {

            //     // وسع الكونتينر مؤقتاً
            //     var container = document.querySelector('.container');
            //     var oldWidth = null,
            //         oldMaxWidth = null;
            //     if (container) {
            //         oldWidth = container.style.width;
            //         oldMaxWidth = container.style.maxWidth;
            //         container.style.width = '100vw';
            //         container.style.maxWidth = '100vw';
            //     }

            //     // حدد العنصر اللي عايز تصوره PDF (ممكن document.body أو div معين)
            //     var element = document.body;
            //     // إعدادات pdf
            //     var opt = {
            //         margin: 0.2,
            //         filename: 'daily_report_' + new Date().toISOString().replace(/[:.]/g, '-') + '.pdf',
            //         image: {
            //             type: 'jpeg',
            //             quality: 0.98
            //         },
            //         html2canvas: {
            //             scale: 1
            //         },
            //         jsPDF: {
            //             unit: 'in',
            //             format: 'a4',
            //             orientation: 'portrait'
            //         }
            //     };
            //     // حول الصفحة لـ PDF (Blob)
            //     html2pdf().from(element).set(opt).outputPdf('blob').then(function(pdfBlob) {
            //         // حول الـ Blob لبيانات base64

            //         // رجع الكونتينر زي ما كان
            //         if (container) {
            //             container.style.width = oldWidth || '';
            //             container.style.maxWidth = oldMaxWidth || '';
            //         }

            //         var reader = new FileReader();
            //         reader.onloadend = function() {
            //             var base64data = reader.result.split(',')[1];
            //             // ابعت الـ PDF للسيرفر
            //             fetch('/save-pdf', {
            //                     method: 'POST',
            //                     headers: {
            //                         'Content-Type': 'application/json',
            //                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //                     },
            //                     body: JSON.stringify({
            //                         pdf: base64data
            //                     })
            //                 }).then(res => res.json())
            //                 .then(data => {
            //                     console.log('تم حفظ الـ PDF:', data.path);
            //                 }).catch(err => {
            //                     console.error('خطأ في رفع الـ PDF:', err);
            //                 });
            //         };
            //         reader.readAsDataURL(pdfBlob);
            //     });
            // }

            // // شغل الدالة أول مرة
            // savePagePDF();
            // // وجدولها كل دقيقة (60000 ms)
            // setInterval(savePagePDF, 60000);
            // =====================================================
            function saveDailyScreenshotIfNeeded() {
                var today = new Date().toISOString().slice(0, 10); // yyyy-mm-dd
                var lastSaved = localStorage.getItem('dailyScreenshotDate');
                if (lastSaved === today) {
                    // الصورة محفوظة النهاردة بالفعل
                    return;
                }
                html2canvas(document.body).then(function(canvas) {
                    var imageData = canvas.toDataURL('image/png');
                    fetch('/save-screenshot', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                image: imageData
                            })
                        }).then(res => res.json())
                        .then(data => {
                            console.log('تم حفظ صورة اليوم:', data.path);
                            localStorage.setItem('dailyScreenshotDate', today);
                        }).catch(err => {
                            console.error('خطأ في رفع الصورة:', err);
                        });
                });
            }


            window.addEventListener('load', function() {
                setTimeout(saveDailyScreenshotIfNeeded, 3000); // انتظر 3 ثواني بعد تحميل الصفحة
            });
            // دالة التبديل وضع الخصم
            function toggleDiscountMode(companyId) {
                const isDiscountField = document.getElementById('is-discount-' + companyId);
                const submitBtn = document.getElementById('submitBtn-' + companyId);
                const toggleBtn = document.getElementById('toggleDiscountBtn-' + companyId);
                const modalTitle = document.querySelector('#paymentModal' + companyId + ' .modal-title');
                const companyName = modalTitle.textContent.split('-')[1].trim();

                if (isDiscountField.value === "0") {
                    // تحويل إلى وضع الخصم
                    isDiscountField.value = "1";
                    submitBtn.textContent = "تطبيق الخصم";
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-warning');
                    toggleBtn.textContent = "تسجيل دفعة";
                    modalTitle.textContent = "تسجيل خصم - " + companyName;
                } else {
                    // العودة إلى وضع الدفع
                    isDiscountField.value = "0";
                    submitBtn.textContent = "تسجيل الدفعة";
                    submitBtn.classList.remove('btn-warning');
                    submitBtn.classList.add('btn-primary');
                    toggleBtn.textContent = "تسجيل خصم";
                    modalTitle.textContent = "تسجيل دفعة - " + companyName;
                }
            }
        </script>
    @endpush


@endsection
