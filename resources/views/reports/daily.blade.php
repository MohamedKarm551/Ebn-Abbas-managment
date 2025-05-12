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
        {{-- ملخص الأرصدة حسب العملة --}}
        <div class="  mb-4">
            <div class="card-header ">
                <h5 class="mb-2 text-warning"><i class="fas fa-money-bill-wave me-2"></i>ملخص الأرصدة حسب العملة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">إجمالي المطلوب من الشركات:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-dollar-sign me-1"></i>

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

                        </ul>
                        <h6 class="text-success">إجمالي المدفوع من الشركات:</h6>
                        <ul class="list-unstyled">
                            @if (isset($companyPaymentsByCurrency['SAR']))
                                <li><i class="fas fa-dollar-sign me-1"></i>
                                    {{ number_format($companyPaymentsByCurrency['SAR'], 2) }} ريال سعودي</li>
                            @endif
                            @if (isset($companyPaymentsByCurrency['KWD']))
                                <li><i class="fas fa-money-bill-wave me-1"></i>
                                    {{ number_format($companyPaymentsByCurrency['KWD'], 2) }} دينار كويتي</li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger">إجمالي المستحق للجهات:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-dollar-sign me-1"></i>
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
                            </li>
                        </ul>
                        <h6 class="text-success">إجمالي المدفوع للجهات:</h6>
                        <ul class="list-unstyled">
                            @if (isset($agentPaymentsByCurrency['SAR']))
                                <li><i class="fas fa-dollar-sign me-1"></i>
                                    {{ number_format($agentPaymentsByCurrency['SAR'], 2) }} ريال سعودي</li>
                            @endif
                            @if (isset($agentPaymentsByCurrency['KWD']))
                                <li><i class="fas fa-money-bill-wave me-1"></i>
                                    {{ number_format($agentPaymentsByCurrency['KWD'], 2) }} دينار كويتي</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- *** بداية إضافة قسم الرسم البياني لصافي الرصيد *** --}}
        <div class="row mt-4"> {{-- صف جديد للرسم البياني --}}
            <div class="col-md-12 mb-4"> {{-- واخد العرض كله --}}
                <div class=" shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-2 text-info">اتجاه صافي الرصيد مع الوقت</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:350px; width:100%">
                            {{-- Canvas للرسم البياني الجديد --}}
                            <canvas id="netBalanceChart"></canvas>
                        </div>
                        <p class="text-muted small mt-2 text-center">
                            يمثل الخط التغير في صافي الرصيد (الموجب = لك، السالب = عليك) بناءً على العمليات المسجلة.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        {{-- *** نهاية إضافة قسم الرسم البياني لصافي الرصيد *** --}}
<div class="chart-container" style="position: relative; height:350px; width:100%">
    <canvas id="netBalanceKWDChart"></canvas>
</div>
        {{-- *** الخطوة 3: قائمة أعلى 5 شركات عليها مبالغ *** --}}
        <div class="col-md-4 mb-3">
            <div class=" h-100">
                <div class="card-body">
                    <h5 class="card-title text-danger"><i class="fas fa-exclamation-triangle me-1"></i> أعلى 5
                        شركات عليها مبالغ</h5>
                    @php
                        // فرز الشركات حسب المتبقي (الأعلى أولاً) في Blade (الأفضل عمله في Controller)
                        $topCompanies = $companiesReport->sortByDesc('remaining')->take(5);
                    @endphp
                    <ul class="list-unstyled mb-2 small">
                        @forelse ($topCompanies as $company)
                            @if ($company->remaining > 0)
                                {{-- عرض فقط إذا كان المتبقي أكبر من صفر --}}
                                <li>{{ $company->name }}:
                                    @php
                                        $remainingByCurrency = $company->remaining_by_currency ?? [
                                            'SAR' => $company->remaining,
                                        ];
                                    @endphp
                                    @foreach ($remainingByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            <span class="fw-bold">{{ number_format($amount) }}
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</span>
                                        @endif
                                    @endforeach
                                </li>
                            @endif
                        @empty
                            <li>لا توجد شركات عليها مبالغ متبقية حاليًا.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        {{-- *** الخطوة 3: قائمة أعلى 5 جهات لها مبالغ *** --}}
        <div class="col-md-4 mb-3">
            <div class=" h-100">
                <div class="card-body">
                    <h5 class="card-title text-warning"><i class="fas fa-money-check-alt me-1"></i> أعلى 5
                        جهات
                        لها مبالغ</h5>
                    @php
                        // فرز الجهات حسب المتبقي (الأعلى أولاً) في Blade (الأفضل عمله في Controller)
                        $topAgents = $agentsReport->sortByDesc('remaining')->take(5);
                    @endphp
                    <ul class="list-unstyled mb-2 small">
                        @forelse ($topAgents as $agent)
                            @if ($agent->remaining > 0)
                                {{-- عرض فقط إذا كان المتبقي أكبر من صفر --}}
                                <li>{{ $agent->name }}:
                                    @php
                                        $remainingByCurrency = $agent->remaining_by_currency ?? [
                                            'SAR' => $agent->remaining,
                                        ];
                                    @endphp
                                    @foreach ($remainingByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            <span class="fw-bold">{{ number_format($amount) }}
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</span>
                                        @endif
                                    @endforeach
                                </li>
                            @endif
                        @empty
                            <li>لا توجد جهات لها مبالغ متبقية حاليًا.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- *** الخطوة 4: إضافة Canvas للرسوم البيانية *** --}}
    <div class="row mt-3">
        <div class="col-md-6 mb-3"> {{-- إضافة mb-3 --}}
            <h5 class="text-center text-danger">المتبقي على الشركات (أعلى 5)</h5>
            <div class="chart-container" style="position: relative; height:250px; width:100%">
                <canvas id="topCompaniesChart"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-3"> {{-- إضافة mb-3 --}}
            <h5 class="text-center text-warning">المتبقي لجهات الحجز (أعلى 5)</h5>
            <div class="chart-container" style="position: relative; height:250px; width:100%">
                <canvas id="topAgentsChart"></canvas>
            </div>
        </div>

        {{-- *** إضافة Canvas للرسوم الجديدة *** --}}
        <div class="col-md-6 mb-3">
            <h5 class="text-center text-info">مقارنة إجمالي المتبقي</h5>
            <div class="chart-container" style="position: relative; height:250px; width:100%">
                <canvas id="remainingComparisonChart"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <h5 class="text-center text-success">توزيع الحجوزات (أعلى 5 شركات)</h5>
            <div class="chart-container" style="position: relative; height:250px; width:100%">
                <canvas id="companyBookingDistributionChart"></canvas>
            </div>
        </div>
        {{-- *** نهاية إضافة Canvas *** --}}
    </div>
    </div>
    </div>
    {{-- *** الرسم البياني الجديد: الحجوزات اليومية *** --}}
    <div class="col-md-12 mb-4"> {{-- ممكن تخليه col-md-6 لو عايزه جنب رسم تاني --}}
        <div class=" shadow-sm">
            <div class="card-header">
                <h5 class="mb-0 text-primary">الحجوزات خلال آخر {{ count($chartDates ?? []) }} يومًا</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:350px; width:100%">
                    {{-- >>>>> السطر ده هو المهم <<<<< --}}
                    <canvas id="dailyBookingsChart"></canvas>
                    {{-- >>>>> تأكد إن السطر ده موجود <<< --}}
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
                                        $remainingByCurrency = $company->remaining_by_currency ?? [
                                            'SAR' => $company->remaining,
                                        ];
                                    @endphp
                                    @foreach ($remainingByCurrency as $currency => $amount)
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
                                        $remainingByCurrency = $agent->remaining_by_currency ?? [
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
                    <form action="{{ route('reports.agent.payment') }}" method="POST" enctype="multipart/form-data">
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
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
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
                    <form action="{{ route('reports.company.payment') }}" method="POST" enctype="multipart/form-data">
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
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
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
    @endpush
    {{-- *** نهاية التعديل *** --}}


@endsection
