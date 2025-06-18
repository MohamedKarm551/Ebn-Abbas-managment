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
                            @php
                                // ✅ حساب إجمالي المدفوع من جميع الشركات حسب العملة (من جدولي payments و company_payments)
                                $totalPaidByCompaniesByCurrency = ['SAR' => 0, 'KWD' => 0];
                                foreach ($companiesReport as $company) {
                                    $paidByCurrency = $company->total_paid_by_currency ?? [];
                                    foreach ($paidByCurrency as $currency => $amount) {
                                        if (!isset($totalPaidByCompaniesByCurrency[$currency])) {
                                            $totalPaidByCompaniesByCurrency[$currency] = 0;
                                        }
                                        $totalPaidByCompaniesByCurrency[$currency] += $amount;
                                    }
                                }
                            @endphp
                            @foreach ($totalPaidByCompaniesByCurrency as $currency => $amount)
                                @if ($amount > 0)
                                    <li><i class="fas fa-dollar-sign me-1 text-success"></i>
                                        <strong>{{ number_format($amount, 2) }}</strong>
                                        {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        {{-- 🔥 الباقي المطلوب من الشركات --}}
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>الباقي المطلوب من الشركات:
                        </h6>
                        <ul class="list-unstyled">
                            @php
                                // ✅ حساب المتبقي = المستحق - المدفوع لكل عملة
                                $totalRemainingFromCompaniesByCurrency = [];
                                $allCompanyCurrencies = array_unique(
                                    array_merge(
                                        array_keys($totalDueFromCompaniesByCurrency),
                                        array_keys($totalPaidByCompaniesByCurrency),
                                    ),
                                );

                                foreach ($allCompanyCurrencies as $currency) {
                                    $due = $totalDueFromCompaniesByCurrency[$currency] ?? 0;
                                    $paid = $totalPaidByCompaniesByCurrency[$currency] ?? 0;
                                    $remaining = $due - $paid;

                                    if ($remaining != 0) {
                                        // عرض حتى لو سالب
                                        $totalRemainingFromCompaniesByCurrency[$currency] = $remaining;
                                    }
                                }
                            @endphp
                            @foreach ($totalRemainingFromCompaniesByCurrency as $currency => $remaining)
                                <li>
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
                            @endforeach
                            @if (empty($totalRemainingFromCompaniesByCurrency))
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
                                // ✅ حساب إجمالي المدفوع للجهات حسب العملة
                                $totalPaidToAgentsByCurrency = ['SAR' => 0, 'KWD' => 0];

                                // استخدام البيانات المُمررة من الـ Controller
                                if (isset($agentPaymentsByCurrency)) {
                                    foreach ($agentPaymentsByCurrency as $currency => $amount) {
                                        $totalPaidToAgentsByCurrency[$currency] = $amount;
                                    }
                                }
                            @endphp
                            @foreach ($totalPaidToAgentsByCurrency as $currency => $amount)
                                @if ($amount > 0)
                                    <li><i class="fas fa-check-circle me-1 text-success"></i>
                                        <strong>{{ number_format($amount, 2) }}</strong>
                                        {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                    </li>
                                @endif
                            @endforeach
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

        {{-- 📊 الرسوم البيانية --}}
        <div class="row mt-4">
            <div class="col-md-12 mb-4">
                <div class="shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-2 text-info"><i class="fas fa-chart-line me-2"></i>اتجاه صافي الرصيد مع الوقت</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:350px; width:100%">
                            <canvas id="netBalanceChart"></canvas>
                        </div>
                        <p class="text-muted small mt-2 text-center">
                            يمثل الخط التغير في صافي الرصيد (الموجب = لك، السالب = عليك) بناءً على العمليات المسجلة.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📈 رسم بياني إضافي للدينار --}}
        <div class="mb-3">
            <button class="btn btn-outline-info mb-2" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseNetBalanceKWD">
                <i class="fas fa-chart-area me-1"></i>
                صافي الرصيد بالدينار الكويتي
            </button>
            <div class="collapse" id="collapseNetBalanceKWD">
                <div class="chart-container" style="position: relative; height:350px; width:100%">
                    <canvas id="netBalanceKWDChart"></canvas>
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
                                            {{ number_format($payments->sum('amount'), 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @empty
                                            0 ريال
                                        @endforelse
                                    </td>
                                    <td>
                                        @php
                                            $remainingBookingsByCurrency = $company->remaining_bookings_by_currency;
                                        @endphp

                                        @foreach ($remainingBookingsByCurrency as $currency => $amount)
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
                                <td>
                                    @if (isset($companyPaymentsByCurrency['SAR']))
                                        {{ number_format($companyPaymentsByCurrency['SAR']) }} ريال<br>
                                    @endif
                                    @if (isset($companyPaymentsByCurrency['KWD']))
                                        {{ number_format($companyPaymentsByCurrency['KWD']) }} دينار
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $totalRemainingByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];
                                        $companyRemainingByCurrency = $totalRemainingByCurrency; // إنشاء نسخة من المتغير

                                        foreach ($companiesReport as $company) {
                                            $remainingByCurrency = $company->remaining_by_currency ?? [
                                                'SAR' => $company->remaining,
                                            ];
                                            foreach ($remainingByCurrency as $currency => $amount) {
                                                $totalRemainingByCurrency[$currency] += $amount;
                                            }
                                        }
                                        // حفظ قيم الشركات قبل استخدام المتغير مرة أخرى
                                        $companyRemainingByCurrency = $totalRemainingByCurrency;
                                    @endphp
                                    @foreach ($totalRemainingByCurrency as $currency => $amount)
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

        <!-- جدول جهات الحجز -->
        <div class="  mb-4">
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
                                <th>إجمالي المبالغ</th>
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
                                                'SAR' => $agent->total_due,
                                            ];
                                        @endphp
                                        @foreach ($dueByCurrency as $currency => $amount)
                                            {{ number_format($amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endforeach
                                    </td>
                                    <td
                                        @if ($agent->total_paid > $agent->total_due) style="color: red !important; font-weight: bold;" title="المبلغ المدفوع أكثر من المستحق" @endif>
                                        @php
                                            $paymentsByCurrency = $agent->payments
                                                ? $agent->payments->groupBy('currency')
                                                : collect();
                                        @endphp
                                        @forelse ($paymentsByCurrency as $currency => $payments)
                                            {{ number_format($payments->sum('amount'), 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @empty
                                            0 ريال
                                        @endforelse
                                    </td>
                                    <td>
                                        @php
                                            $remainingByCurrency = $agent->remaining_bookings_by_currency ?? [
                                                'SAR' => $agent->remaining,
                                            ];
                                        @endphp
                                        @foreach ($remainingByCurrency as $currency => $amount)
                                            {{ number_format($amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
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
                                        $totalDueByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];
                                        foreach ($agentsReport as $agent) {
                                            $dueByCurrency = $agent->total_due_by_currency ?? [
                                                'SAR' => $agent->total_due,
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
                                <td>
                                    @if (isset($agentPaymentsByCurrency['SAR']))
                                        {{ number_format($agentPaymentsByCurrency['SAR']) }} ريال<br>
                                    @endif
                                    @if (isset($agentPaymentsByCurrency['KWD']))
                                        {{ number_format($agentPaymentsByCurrency['KWD']) }} دينار
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $totalRemainingByCurrency = [
                                            'SAR' => 0,
                                            'KWD' => 0,
                                        ];
                                        foreach ($agentsReport as $agent) {
                                            $remainingByCurrency = $agent->remaining_by_currency ?? [
                                                'SAR' => $agent->remaining,
                                            ];
                                            foreach ($remainingByCurrency as $currency => $amount) {
                                                $totalRemainingByCurrency[$currency] += $amount;
                                            }
                                        }
                                    @endphp
                                    @foreach ($totalRemainingByCurrency as $currency => $amount)
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

        <!-- نماذج تسجيل الدفعات لجهات الحجز -->
        @foreach ($agentsReport as $agent)
            <div class="modal fade" id="agentPaymentModal{{ $agent->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('reports.agent.payment') }}" method="POST"
                            enctype="multipart/form-data">
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
                                {{-- ***   حقل رفع الملف في مشكلة مع الكومبوسر وجوجل درايف  *** --}}
                                {{-- <div class="mb-3">
                                    <label for="receipt_file_agent_{{ $agent->id }}" class="form-label">إرفاق إيصال
                                        (اختياري)</label>
                                    <input class="form-control" type="file" id="receipt_file_agent_{{ $agent->id }}"
                                        name="receipt_file">
                                    <small class="form-text text-muted">الملفات المسموحة: JPG, PNG, PDF (بحد أقصى
                                        5MB)</small>
                                </div> --}}
                                {{-- *** نهاية حقل رفع الملف *** --}}
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
        @endforeach

        <!-- إضافة سكريبت النسخ -->
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
                                <button type="submit" class="btn btn-primary">تسجيل الدفعة</button>
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

                // بيانات العملات المتعددة (الجديدة)
                totalDueFromCompaniesByCurrency: @json($totalDueFromCompaniesByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                companyPaymentsByCurrency: @json($companyPaymentsByCurrency ?? ['SAR' => 0]),
                totalDueToAgentsByCurrency: @json($totalDueToAgentsByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                agentPaymentsByCurrency: @json($agentPaymentsByCurrency ?? ['SAR' => 0]),
                // إضافة تطابق لأسماء المتغيرات المتوقعة في JavaScript
                companiesRemainingByCurrency: @json($companyRemainingByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                agentsRemainingByCurrency: @json($totalRemainingByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                // إضافة بيانات المتبقي حسب العملة
                totalRemainingByCurrency: @json($totalRemainingByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
                agentRemainingByCurrency: @json($agentRemainingByCurrency ?? ['SAR' => 0, 'KWD' => 0])
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
        </script>
    @endpush


@endsection
