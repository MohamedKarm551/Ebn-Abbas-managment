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
        {{-- variables --}}
        @include('reports.hoteldailyReport._variabels')
        {{-- Header Section --}}
        @include('reports.hoteldailyReport._summary_section')

        {{-- خلي العنوان جمبه الصورة تظهر بشكل مناسب وريسبونسف --}}

        @include('reports.hoteldailyReport._moneyDetails', [
            'currencyDetails' => $currencyDetails ?? [],
            'totalDueToCompaniesByCurrency' => $totalDueToCompaniesByCurrency ?? [],
            'agentPaymentsByCurrency' => $agentPaymentsByCurrency ?? [],
        ])

        {{-- *** بداية قسم لوحة المعلومات المصغرة *** --}}

        @include('reports.hoteldailyReport._chartSAR')
        @include('reports.hoteldailyReport._chartKWD')
        @include('reports.hoteldailyReport._topdetails')
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
                                            // 1. مستحقات الفنادق حسب العملة
                                            $hotelDueByCurrency = $company->total_due_bookings_by_currency ?? [
                                                'SAR' => 0,
                                                'KWD' => 0,
                                            ];
                                            // 2. مستحقات الرحلات البرية حسب العملة
                                            $tripDueByCurrency = $company->landTripBookings
                                                ->groupBy('currency')
                                                ->map->sum('amount_due_from_company')
                                                ->toArray();
                                            // 3. مدفوعات الرحلات البرية حسب العملة
                                            $tripPayments = $company
                                                ->companyPayments()
                                                ->select(
                                                    'currency',
                                                    DB::raw(
                                                        'SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as paid',
                                                    ),
                                                    DB::raw(
                                                        'SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts',
                                                    ),
                                                )
                                                ->groupBy('currency')
                                                ->get()
                                                ->keyBy('currency');
                                            // 4. المتبقي من الرحلات البرية
                                            $tripRemainingByCurrency = [];
                                            foreach (['SAR', 'KWD'] as $cur) {
                                                $due = $tripDueByCurrency[$cur] ?? 0;
                                                $paid = (float) ($tripPayments[$cur]->paid ?? 0);
                                                $discounts = (float) ($tripPayments[$cur]->discounts ?? 0);
                                                $tripRemainingByCurrency[$cur] = $due - $paid - $discounts;
                                            }
                                            // 5. إجمالي المستحق لكل عملة
                                            $totalDueByCurrency = [];
                                            foreach (['SAR', 'KWD'] as $cur) {
                                                $totalDueByCurrency[$cur] =
                                                    ($hotelDueByCurrency[$cur] ?? 0) + ($tripDueByCurrency[$cur] ?? 0);
                                            }
                                        @endphp

                                        <div class="d-grid gap-2"
                                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));">
                                            {{-- إجمالي المستحق --}}
                                            @foreach ($totalDueByCurrency as $cur => $amt)
                                                @if ($amt > 0)
                                                    <div>
                                                        <strong>{{ number_format($amt, 2) }}</strong>
                                                        {{ $cur === 'SAR' ? 'ريال' : 'دينار' }}
                                                    </div>
                                                @endif
                                            @endforeach

                                            {{-- تفاصيل الفنادق --}}
                                            @foreach ($hotelDueByCurrency as $cur => $amt)
                                                @if ($amt > 0)
                                                    <div>
                                                        <span class="badge bg-success text-white">
                                                            <i class="fas fa-hotel me-1"></i>
                                                            {{ number_format($amt, 2) }} {{ $cur === 'SAR' ? 'ريال' : 'دينار' }}
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach

                                            {{-- المتبقي من الرحلات البرية --}}
                                            @foreach ($tripRemainingByCurrency as $cur => $rem)
                                                @if ($rem != 0)
                                                    <div>
                                                        <span class="badge bg-info text-dark">
                                                            <i class="fas fa-bus me-1"></i>
                                                            {{ $rem > 0 ? number_format($rem, 2) : '-' . number_format(abs($rem), 2) }}
                                                            {{ $cur === 'SAR' ? 'ريال' : 'دينار' }}
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td
                                        @if (($company->computed_total_paid ?? 0) > ($company->computed_total_due ?? $company->total_due)) style="color: red !important; font-weight: bold;" 
             title="المبلغ المدفوع أكثر من المستحق" @endif>

                                        @php
                                            // ✅ استخدام القيم المحسوبة للمدفوعات
                                            $paidByCurrency = $company->computed_total_paid_by_currency ?? [];
                                            $discountsByCurrency = $company->computed_total_discounts_by_currency ?? [];

                                            // إذا لم تكن محسوبة، استخدم الطريقة القديمة كـ fallback
                                            if (empty($paidByCurrency)) {
                                                $paymentsByCurrency = $company->payments
                                                    ? $company->payments->groupBy('currency')
                                                    : collect();
                                            }
                                        @endphp

                                        {{-- عرض المدفوعات المحسوبة --}}
                                        @if (!empty($paidByCurrency))
                                            @foreach ($paidByCurrency as $currency => $paidAmount)
                                                @if ($paidAmount > 0)
                                                    <div class="mb-1">
                                                        <strong
                                                            class="text-success">{{ number_format($paidAmount, 2) }}</strong>
                                                        {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}

                                                        {{-- عرض الخصومات إذا وجدت --}}
                                                        @if (($discountsByCurrency[$currency] ?? 0) > 0)
                                                            <br><small class="text-warning">
                                                                <i class="fas fa-minus-circle me-1"></i>
                                                                خصومات:
                                                                {{ number_format($discountsByCurrency[$currency], 2) }}
                                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            {{-- الطريقة القديمة كـ fallback --}}
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
                                        @endif
                                    </td>
                                    <td>
                                        {{-- المتبقي --}}
                                        @php
                                            // استخدام المتبقي للحجوزات العادية فقط (بدون رحلات برية)
                                            $remainingByCurrency = $company->remaining_bookings_by_currency ?? [
                                                'SAR' => 0,
                                            ];
                                        @endphp

                                        @foreach ($remainingByCurrency as $currency => $amount)
                                            @if ($amount != 0)
                                                <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ $amount > 0 ? '+' : '' }}{{ number_format($amount, 2) }}
                                                </span>
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                                @if ($amount < 0)
                                                    <small class="text-muted">(دفعوا زيادة)</small>
                                                @endif
                                            @endif
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
                            {{-- 🔄 حلقة عرض جميع جهات الحجز/الوكلاء --}}
                            @foreach ($agentsReport as $agent)
                                <tr>
                                    {{-- 📝 عمود اسم جهة الحجز مع رقم ترتيبي وبادج التعديل --}}
                                    <td>
                                        {{ $loop->iteration }}.{{ $agent->name }}

                                        @php
                                            // 🔍 فحص إذا كان هناك تعديلات حديثة على هذا الوكيل
                                            $hasEdit =
                                                $resentAgentEdits
                                                    ->filter(function ($n) use ($agent) {
                                                        return str_contains($n->first()->message, $agent->name);
                                                    })
                                                    ->count() > 0;
                                        @endphp

                                        {{-- 🟢 عرض بادج "edit" إذا كان هناك تعديلات حديثة --}}
                                        @if ($hasEdit)
                                            <span class="badge bg-success" style="font-size: 0.7em;">edit</span>
                                        @endif
                                    </td>

                                    {{-- 📊 عمود عدد الحجوزات --}}
                                    <td>{{ $agent->bookings_count }}</td>

                                    {{-- 💰 عمود إجمالي المستحق حسب العملة --}}
                                    <td>
                                        @php
                                            // ✅ استخدام القيم المحسوبة الجديدة للوكلاء مع fallback للقيم القديمة
                                            $dueByCurrency =
                                                $agent->computed_total_due_by_currency ??
                                                ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);
                                        @endphp

                                        {{-- 🔄 عرض المستحق لكل عملة --}}
                                        @foreach ($dueByCurrency as $currency => $amount)
                                            @if ($amount > 0)
                                                {{ number_format($amount, 2) }}
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                            @endif
                                        @endforeach
                                    </td>

                                    {{-- 💵 عمود المدفوعات والخصومات --}}
                                    <td>
                                        @php
                                            // ✅ استخدام القيم المحسوبة الخاصة بهذا الوكيل تحديدًا
                                            $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
                                            $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];

                                            // إذا لم تكن محسوبة، استخدم الطريقة القديمة كـ fallback
                                            if (empty($paidByCurrency) && $agent->payments) {
                                                $agentPaymentsGrouped = $agent->payments->groupBy('currency');

                                                foreach ($agentPaymentsGrouped as $currency => $payments) {
                                                    $paidByCurrency[$currency] = $payments
                                                        ->where('amount', '>=', 0)
                                                        ->sum('amount');
                                                    $discountsByCurrency[$currency] = abs(
                                                        $payments->where('amount', '<', 0)->sum('amount'),
                                                    );
                                                }
                                            }
                                        @endphp

                                        {{-- عرض المدفوعات بالريال السعودي --}}
                                        @if (isset($paidByCurrency['SAR']) && ($paidByCurrency['SAR'] > 0 || ($discountsByCurrency['SAR'] ?? 0) > 0))
                                            <div class="mb-1">
                                                <strong
                                                    class="text-success">{{ number_format($paidByCurrency['SAR'], 2) }}</strong>
                                                ريال
                                                @if (($discountsByCurrency['SAR'] ?? 0) > 0)
                                                    <br><small class="text-warning">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        خصومات: {{ number_format($discountsByCurrency['SAR'], 2) }} ريال
                                                    </small>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- عرض المدفوعات بالدينار الكويتي --}}
                                        @if (isset($paidByCurrency['KWD']) && ($paidByCurrency['KWD'] > 0 || ($discountsByCurrency['KWD'] ?? 0) > 0))
                                            <div class="mb-1">
                                                <strong
                                                    class="text-success">{{ number_format($paidByCurrency['KWD'], 2) }}</strong>
                                                دينار
                                                @if (($discountsByCurrency['KWD'] ?? 0) > 0)
                                                    <br><small class="text-warning">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        خصومات: {{ number_format($discountsByCurrency['KWD'], 2) }} دينار
                                                    </small>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- إذا لم توجد مدفوعات --}}
                                        @if (empty($paidByCurrency) ||
                                                ((!isset($paidByCurrency['SAR']) || $paidByCurrency['SAR'] == 0) &&
                                                    (!isset($paidByCurrency['KWD']) || $paidByCurrency['KWD'] == 0)))
                                            <span class="text-muted">0 ريال</span>
                                        @endif
                                    </td>

                                    {{-- 📉 عمود المتبقي --}}
                                    <td>
                                        @php
                                            // ✅ استخدام القيم المحسوبة للمتبقي مع fallback
                                            $remainingAgentByCurrency =
                                                $agent->computed_remaining_by_currency ??
                                                ($agent->remaining_by_currency ?? [
                                                    'SAR' => $agent->remaining_amount ?? 0,
                                                ]);
                                        @endphp

                                        {{-- 🔄 عرض المتبقي لكل عملة --}}
                                        @foreach ($remainingAgentByCurrency as $currency => $amount)
                                            @if ($amount != 0)
                                                {{-- 🎨 تلوين المتبقي: أحمر للموجب (مدين لنا)، أبيض مع خلفية حمراء للسالب (دفعنا زيادة) --}}
                                                <span class="{{ $amount > 0 ? '' : 'badge bg-danger text-white' }}">
                                                    {{ number_format($amount, 2) }}
                                                </span>
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>

                                                {{-- 📝 ملاحظة إذا كان المبلغ سالب (دفعنا زيادة) --}}
                                                @if ($amount < 0)
                                                    <small class="text-muted">(دفعنا زيادة)</small>
                                                @endif
                                            @endif
                                        @endforeach
                                    </td>

                                    {{-- ⚙️ عمود العمليات --}}
                                    <td>
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                            {{-- 🔍 رابط عرض الحجوزات --}}
                                            <a href="{{ route('reports.agent.bookings', $agent->id) }}"
                                                class="btn btn-info btn-sm">عرض الحجوزات</a>

                                            {{-- 💰 زر تسجيل دفعة --}}
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#agentPaymentModal{{ $agent->id }}">
                                                تسجيل دفعة
                                            </button>

                                            {{-- 🟡 زر تطبيق خصم --}}
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#agentDiscountModal{{ $agent->id }}">
                                                تطبيق خصم
                                            </button>

                                            {{-- 📋 رابط كشف الحساب --}}
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
                                        // ✅ حساب الإجمالي من القيم المحسوبة للوكلاء
                                        $totalAgentDueByCurrency = ['SAR' => 0, 'KWD' => 0];
                                        foreach ($agentsReport as $agent) {
                                            $dueByCurrency =
                                                $agent->computed_total_due_by_currency ??
                                                ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);

                                            foreach ($dueByCurrency as $currency => $amount) {
                                                $totalAgentDueByCurrency[$currency] += $amount;
                                            }
                                        }
                                    @endphp
                                    @foreach ($totalAgentDueByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            {{ number_format($amount, 2) }}
                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @php
                                        // ✅ حساب إجمالي المدفوعات من القيم المحسوبة للوكلاء
                                        $totalAgentPaidByCurrency = ['SAR' => 0, 'KWD' => 0];
                                        $totalAgentDiscountsByCurrency = ['SAR' => 0, 'KWD' => 0];

                                        foreach ($agentsReport as $agent) {
                                            $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
                                            $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];

                                            foreach ($paidByCurrency as $currency => $amount) {
                                                $totalAgentPaidByCurrency[$currency] += $amount;
                                            }
                                            foreach ($discountsByCurrency as $currency => $amount) {
                                                $totalAgentDiscountsByCurrency[$currency] += $amount;
                                            }
                                        }
                                    @endphp

                                    @foreach (['SAR', 'KWD'] as $currency)
                                        @if (($totalAgentPaidByCurrency[$currency] ?? 0) > 0 || ($totalAgentDiscountsByCurrency[$currency] ?? 0) > 0)
                                            <div class="mb-1">
                                                <strong
                                                    class="text-success">{{ number_format($totalAgentPaidByCurrency[$currency] ?? 0, 2) }}</strong>
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                @if (($totalAgentDiscountsByCurrency[$currency] ?? 0) > 0)
                                                    <br><small class="text-warning">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        خصومات:
                                                        {{ number_format($totalAgentDiscountsByCurrency[$currency], 2) }}
                                                        {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                    </small>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @php
                                        // ✅ حساب إجمالي المتبقي من القيم المحسوبة للوكلاء
                                        $totalAgentRemainingByCurrency = ['SAR' => 0, 'KWD' => 0];

                                        foreach ($agentsReport as $agent) {
                                            $remainingByCurrency =
                                                $agent->computed_remaining_by_currency ??
                                                ($agent->remaining_by_currency ?? [
                                                    'SAR' => $agent->remaining_amount ?? 0,
                                                ]);

                                            foreach ($remainingByCurrency as $currency => $amount) {
                                                $totalAgentRemainingByCurrency[$currency] += $amount;
                                            }
                                        }
                                    @endphp

                                    @foreach ($totalAgentRemainingByCurrency as $currency => $amount)
                                        @if ($amount != 0)
                                            <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $amount > 0 ? '+' : '' }}{{ number_format($amount, 2) }}
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
        {{-- reports\hoteldailyReport\_variabels --}}


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
