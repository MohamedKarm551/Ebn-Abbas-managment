@extends('layouts.app')

@section('title', 'تفاصيل تقرير العملية')

@push('styles')
    <style>
        .report-section {
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            overflow: hidden;
        }

        .report-section-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-section-body {
            padding: 20px;
        }

        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }

        .profit-positive {
            color: #10b981;
            font-weight: 600;
        }

        .profit-negative {
            color: #ef4444;
            font-weight: 600;
        }

        .empty-section {
            padding: 20px;
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }

        .summary-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
        }

        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .total-row {
            font-size: 1.2rem;
            font-weight: 700;
            border-top: 2px solid #dee2e6;
            margin-top: 10px;
            padding-top: 10px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .report-section {
                box-shadow: none;
                margin-bottom: 15px;
                break-inside: avoid;
            }

            .container {
                width: 100%;
                max-width: 100%;
            }

            body {
                font-size: 12px;
            }
        }

        .separator {
            height: 2px;
            background-color: #dee2e6;
            margin: 20px 0;
        }

        .badge-section {
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 4px;
            margin-right: 5px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="h3 mb-0">تفاصيل تقرير العملية</h1>
                <p class="text-muted mb-0">التقرير رقم: #{{ $operationReport->id }}</p>
            </div>
            <div>
                <a href="{{ route('admin.operation-reports.edit', $operationReport) }}" class="btn btn-warning btn-sm me-2">
                    <i class="fas fa-edit"></i> تعديل التقرير
                </a>
                <button onclick="window.print()" class="btn btn-info btn-sm me-2">
                    <i class="fas fa-print"></i> طباعة
                </button>
                <a href="{{ route('admin.operation-reports.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> العودة للقائمة
                </a>
            </div>
        </div>

        <!-- معلومات أساسية -->
        <div class="report-section">
            <div class="report-section-header">
                <h2 class="h5 mb-0">المعلومات الأساسية</h2>
                <span
                    class="badge bg-success">{{ $operationReport->status === 'completed' ? 'مكتمل' : 'قيد المعالجة' }}</span>
            </div>
            <div class="report-section-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">تاريخ التقرير:</th>
                                <td>{{ $operationReport->report_date->format('Y-m-d') }} <small
                                        class="d-block text-muted hijri-date"
                                        data-date="{{ $operationReport->report_date->format('Y-m-d') }}"></small></td>
                            </tr>
                            <tr>
                                <th>اسم العميل:</th>
                                <td>{{ $operationReport->client_name }}</td>
                            </tr>
                            <tr>
                                <th>هاتف العميل:</th>
                                <td>{{ $operationReport->client_phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>الموظف المسؤول:</th>
                                <td>{{ $operationReport->employee->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">اسم الشركة:</th>
                                <td>{{ $operationReport->company_name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>نوع الحجز:</th>
                                <td>{{ $operationReport->booking_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>مرجع الحجز:</th>
                                <td>{{ $operationReport->booking_reference ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @php
                    $notes = $operationReport->notes;

                    // Regular expression to detect URLs
                    $pattern = '/(https?:\/\/[^\s]+)/';

                    // Replace URL with icon HTML
                    $notesWithIcons = preg_replace_callback(
                        $pattern,
                        function ($matches) {
                            $url = $matches[0];
                            return '<a href="' .
                                $url .
                                '" target="_blank" style="text-decoration:none;">
                    🔗 لينك :
                </a>';
                        },
                        e($notes),
                    );

                    // Allow the icon HTML while escaping other parts
                    $notesWithIcons = nl2br($notesWithIcons); // Preserve line breaks
                @endphp

                @if ($operationReport->notes)
                    <div class="mt-3">
                        <strong>ملاحظات عامة:</strong>
                        <p class="mt-2 p-3 bg-light rounded">{!! $notesWithIcons !!}</p>
                    </div>
                @endif

            </div>
        </div>

        <!-- ملخص الأرباح حسب العملة -->
        <div class="report-section shadow-sm rounded-4 border border-2 border-primary-subtle mb-5">
            <div
                class="report-section-header bg-gradient bg-primary text-white rounded-top-4 px-4 py-3 d-flex align-items-center">
                <h2 class="h5 mb-0 flex-grow-1">ملخص الأرباح حسب العملة</h2>
                <span class="badge bg-light text-primary fs-6 px-3 py-2">تقرير حديث</span>

            </div>
            <div class="report-section-body bg-white rounded-bottom-4 px-4 py-4">
                <div class="summary-card border-0 p-0">
                    @php
                        // تجميع الأرباح حسب العملة منفصلة تماماً
                        $profitsByCurrency = [
                            'KWD' => [
                                'visas' => 0,
                                'flights' => 0,
                                'transports' => 0,
                                'hotels' => 0,
                                'land_trips' => 0,
                                'total' => 0,
                            ],
                            'SAR' => [
                                'visas' => 0,
                                'flights' => 0,
                                'transports' => 0,
                                'hotels' => 0,
                                'land_trips' => 0,
                                'total' => 0,
                            ],
                            'USD' => [
                                'visas' => 0,
                                'flights' => 0,
                                'transports' => 0,
                                'hotels' => 0,
                                'land_trips' => 0,
                                'total' => 0,
                            ],
                            'EUR' => [
                                'visas' => 0,
                                'flights' => 0,
                                'transports' => 0,
                                'hotels' => 0,
                                'land_trips' => 0,
                                'total' => 0,
                            ],
                        ];

                        // حساب أرباح التأشيرات حسب العملة
                        foreach ($operationReport->visas as $visa) {
                            $currency = $visa->currency ?? 'KWD';
                            $profit = $visa->profit ?? 0;
                            if (isset($profitsByCurrency[$currency])) {
                                $profitsByCurrency[$currency]['visas'] += $profit;
                                $profitsByCurrency[$currency]['total'] += $profit;
                            }
                        }

                        // حساب أرباح الطيران حسب العملة
                        foreach ($operationReport->flights as $flight) {
                            $currency = $flight->currency ?? 'KWD';
                            $profit = $flight->profit ?? 0;
                            if (isset($profitsByCurrency[$currency])) {
                                $profitsByCurrency[$currency]['flights'] += $profit;
                                $profitsByCurrency[$currency]['total'] += $profit;
                            }
                        }

                        // حساب أرباح النقل حسب العملة
                        foreach ($operationReport->transports as $transport) {
                            $currency = $transport->currency ?? 'KWD';
                            $profit = $transport->profit ?? 0;
                            if (isset($profitsByCurrency[$currency])) {
                                $profitsByCurrency[$currency]['transports'] += $profit;
                                $profitsByCurrency[$currency]['total'] += $profit;
                            }
                        }

                        // حساب أرباح الفنادق حسب العملة
                        foreach ($operationReport->hotels as $hotel) {
                            $currency = $hotel->currency ?? 'KWD';
                            $profit = $hotel->profit ?? 0;
                            if (isset($profitsByCurrency[$currency])) {
                                $profitsByCurrency[$currency]['hotels'] += $profit;
                                $profitsByCurrency[$currency]['total'] += $profit;
                            }
                        }

                        // حساب أرباح الرحلات البرية حسب العملة
                        foreach ($operationReport->landTrips as $landTrip) {
                            $currency = $landTrip->currency ?? 'KWD';
                            $profit = $landTrip->profit ?? 0;
                            if (isset($profitsByCurrency[$currency])) {
                                $profitsByCurrency[$currency]['land_trips'] += $profit;
                                $profitsByCurrency[$currency]['total'] += $profit;
                            }
                        }

                        // رموز العملات
                        $currencyLabels = [
                            'KWD' => 'د.ك',
                            'SAR' => 'ر.س',
                            'USD' => '$',
                            'EUR' => '€',
                        ];
                    @endphp

                    {{-- عرض تفصيل كل عملة على حدة }}
                @foreach ($profitsByCurrency as $currency => $profits)
                    @if ($profits['total'] > 0)
                        <div class="currency-section mb-4 p-3 border rounded">
                            <h6 class="mb-3 text-center">
                                <span class="badge bg-{{ $currency == 'KWD' ? 'primary' : ($currency == 'SAR' ? 'success' : 'info') }} fs-6">
                                    {{ $currency == 'KWD' ? 'الدينار الكويتي' : 
                                       ($currency == 'SAR' ? 'الريال السعودي' : 
                                       ($currency == 'USD' ? 'الدولار الأمريكي' : 'اليورو')) }}
                                    ({{ $currencyLabels[$currency] }})
                                </span>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    @if ($profits['visas'] > 0)
                                        <div class="summary-item">
                                            <span>ربح التأشيرات</span>
                                            <span class="profit-positive">
                                                {{ number_format($profits['visas'], 2) }} {{ $currencyLabels[$currency] }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if ($profits['flights'] > 0)
                                        <div class="summary-item">
                                            <span>ربح الطيران</span>
                                            <span class="profit-positive">
                                                {{ number_format($profits['flights'], 2) }} {{ $currencyLabels[$currency] }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if ($profits['transports'] > 0)
                                        <div class="summary-item">
                                            <span>ربح النقل</span>
                                            <span class="profit-positive">
                                                {{ number_format($profits['transports'], 2) }} {{ $currencyLabels[$currency] }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-6">
                                    @if ($profits['hotels'] > 0)
                                        <div class="summary-item">
                                            <span>ربح الفنادق</span>
                                            <span class="profit-positive">
                                                {{ number_format($profits['hotels'], 2) }} {{ $currencyLabels[$currency] }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if ($profits['land_trips'] > 0)
                                        <div class="summary-item">
                                            <span>ربح الرحلات البرية</span>
                                            <span class="profit-positive">
                                                {{ number_format($profits['land_trips'], 2) }} {{ $currencyLabels[$currency] }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="summary-item total-row bg-{{ $currency == 'KWD' ? 'primary' : ($currency == 'SAR' ? 'success' : 'info') }} bg-opacity-10">
                                <span class="fw-bold">إجمالي الأرباح بـ{{ $currency == 'KWD' ? 'الدينار الكويتي' : ($currency == 'SAR' ? 'الريال السعودي' : ($currency == 'USD' ? 'الدولار الأمريكي' : 'اليورو')) }}</span>
                                <span class="profit-positive fw-bold fs-5">
                                    {{ number_format($profits['total'], 2) }} {{ $currencyLabels[$currency] }}
                                </span>
                            </div>
                        </div>
                    @endif
                @endforeach
                
                {{-- إجمالي عام لكل العملات --}}
                    <div class="separator my-4"></div>
                    <div
                        class="summary-item total-row bg-dark text-white rounded-3 px-3 py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="fas fa-calculator me-2"></i> إجمالي عام لكل العملات</span>
                        <div class="d-flex gap-3 flex-wrap">
                            @foreach ($profitsByCurrency as $currency => $profits)
                                @if ($profits['total'] > 0)
                                    <span
                                        class="badge bg-{{ $currency == 'KWD' ? 'primary' : ($currency == 'SAR' ? 'success' : ($currency == 'USD' ? 'info' : 'warning')) }} fs-6 px-3 py-2">
                                        {{ number_format($profits['total'], 2) }} {{ $currencyLabels[$currency] }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    {{-- عرض ربح الموظف --}}
                    @php
                        $baseProfit = $operationReport->grand_total_profit;
                        $currency = $operationReport->currency;
                        $rateToKWD = 1;

                        if ($currency === 'SAR') {
                            $rateToKWD = 0.081;
                        } elseif ($currency === 'USD') {
                            $rateToKWD = 0.31;
                        }

                        $kwdProfit = $baseProfit * $rateToKWD;
                        $finalProfitEGP = $kwdProfit * 10;
                        $equation =
                            "{$baseProfit} {$currency} × {$rateToKWD} × 10 = " .
                            number_format($finalProfitEGP, 2) .
                            ' جنيه مصري';
                    @endphp
                    @if ($operationReport->employee_profit && $operationReport->employee_profit > 0)
                        <div
                            class="summary-item total-row bg-success text-white rounded-3 px-3 py-3 d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold">
                                <i class="fas fa-user-tie me-2"></i>
                                ربح الموظف: {{ $operationReport->employee->name ?? 'غير محدد' }}
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-success fs-5 px-3 py-2" title="{{ $equation }}">
                                    {{ number_format($operationReport->employee_profit) }}
                                    {{ $operationReport->employee_profit_currency ?? 'EGP' }}
                                </span>
                                <small class="text-light opacity-75">
                                    ({{ $operationReport->employee_profit_currency == 'EGP' ? 'جنيه مصري' : $operationReport->employee_profit_currency }})
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- قسم التأشيرات -->
        @if ($operationReport->visas->count() > 0)
            <div class="report-section">
                <div class="report-section-header">
                    <h2 class="h5 mb-0">بيانات التأشيرات</h2>
                    <span class="badge-section">{{ $operationReport->visas->count() }} تأشيرة</span>
                </div>
                <div class="report-section-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th>نوع التأشيرة</th>
                                    <th>الكمية</th>
                                    <th>التكلفة</th>
                                    <th>سعر البيع</th>
                                    <th>العملة</th>
                                    <th>الربح</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operationReport->visas as $index => $visa)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $visa->visa_type }}</td>
                                        <td>{{ $visa->quantity }}</td>
                                        <td>{{ number_format($visa->cost, 2) }}</td>
                                        <td>{{ number_format($visa->selling_price, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $visa->currency == 'KWD' ? 'primary' : 'success' }}">
                                                {{ $visa->currency == 'KWD'
                                                    ? 'د.ك'
                                                    : ($visa->currency == 'SAR'
                                                        ? 'ر.س'
                                                        : ($visa->currency == 'USD'
                                                            ? '$'
                                                            : '€')) }}
                                            </span>
                                        </td>
                                        <td class="{{ $visa->profit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                            {{ number_format($visa->profit, 2) }}
                                        </td>
                                        <td>{{ $visa->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-center bg-light">إجماليات حسب العملة</th>
                                </tr>
                                @php
                                    $visasByCurrency = $operationReport->visas->groupBy('currency');
                                @endphp
                                @foreach ($visasByCurrency as $currency => $visasGroup)
                                    <tr>
                                        <th colspan="3">إجمالي {{ $currency == 'KWD' ? 'الدينار' : 'الريال' }}</th>
                                        <th>{{ number_format($visasGroup->sum('cost'), 2) }}</th>
                                        <th>{{ number_format($visasGroup->sum('selling_price'), 2) }}</th>
                                        <th>
                                            <span class="badge bg-{{ $currency == 'KWD' ? 'primary' : 'success' }}">
                                                {{ $currency == 'KWD' ? 'د.ك' : 'ر.س' }}
                                            </span>
                                        </th>
                                        <th
                                            class="{{ $visasGroup->sum('profit') > 0 ? 'profit-positive' : 'profit-negative' }}">
                                            {{ number_format($visasGroup->sum('profit'), 2) }}
                                        </th>
                                        <th></th>
                                    </tr>
                                @endforeach
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- قسم الطيران -->
        @if ($operationReport->flights->count() > 0)
            <div class="report-section">
                <div class="report-section-header">
                    <h2 class="h5 mb-0">بيانات الطيران</h2>
                    <span class="badge-section">{{ $operationReport->flights->count() }} رحلة</span>
                </div>
                <div class="report-section-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th>تاريخ الرحلة</th>
                                    <th>رقم الرحلة</th>
                                    <th>شركة الطيران</th>
                                    <th>المسار</th>
                                    <th>عدد المسافرين</th>
                                    <th>التكلفة</th>
                                    <th>سعر البيع</th>
                                    <th>العملة</th>
                                    <th>الربح</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operationReport->flights as $index => $flight)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $flight->flight_date ? $flight->flight_date->format('Y-m-d') : '-' }} <small
                                                class="d-block text-muted hijri-date"
                                                data-date="{{ $flight->flight_date->format('Y-m-d') }}"></small></td>
                                        <td>{{ $flight->flight_number ?? '-' }}</td>
                                        <td>{{ $flight->airline ?? '-' }}</td>
                                        <td>{{ $flight->route ?? '-' }}</td>
                                        <td>{{ $flight->passengers }}</td>
                                        <td>{{ number_format($flight->cost, 2) }}</td>
                                        <td>{{ number_format($flight->selling_price, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $flight->currency == 'KWD' ? 'primary' : 'success' }}">
                                                {{ $flight->currency == 'KWD'
                                                    ? 'د.ك'
                                                    : ($flight->currency == 'SAR'
                                                        ? 'ر.س'
                                                        : ($flight->currency == 'USD'
                                                            ? '$'
                                                            : '€')) }}
                                            </span>
                                        </td>
                                        <td class="{{ $flight->profit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                            {{ number_format($flight->profit, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="10" class="text-center bg-light">إجماليات حسب العملة</th>
                                </tr>
                                @php
                                    $flightsByCurrency = $operationReport->flights->groupBy('currency');
                                @endphp
                                @foreach ($flightsByCurrency as $currency => $flightsGroup)
                                    <tr>
                                        <th colspan="6">إجمالي {{ $currency == 'KWD' ? 'الدينار' : 'الريال' }}</th>
                                        <th>{{ number_format($flightsGroup->sum('cost'), 2) }}</th>
                                        <th>{{ number_format($flightsGroup->sum('selling_price'), 2) }}</th>
                                        <th>
                                            <span class="badge bg-{{ $currency == 'KWD' ? 'primary' : 'success' }}">
                                                {{ $currency == 'KWD' ? 'د.ك' : 'ر.س' }}
                                            </span>
                                        </th>
                                        <th
                                            class="{{ $flightsGroup->sum('profit') > 0 ? 'profit-positive' : 'profit-negative' }}">
                                            {{ number_format($flightsGroup->sum('profit'), 2) }}
                                        </th>
                                    </tr>
                                    <div class="row">
                                    </div>

                                    <div class="col-md-4">
                                        <strong>الملاحظات:</strong>
                                        <p class="text-muted"> {{ $flight->notes }}</p>
                                    </div>
                    </div>
        @endforeach
        </tfoot>
        </table>
    </div>
    </div>
    </div>
    @endif

    <!-- قسم النقل -->
    @if ($operationReport->transports->count() > 0)
        <div class="report-section-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>نوع النقل</th>
                            <th>معلومات السائق</th>
                            <th>التكلفة</th>
                            <th>سعر البيع</th>
                            <th>العملة</th>
                            <th>الربح</th>
                            <th>المرفقات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($operationReport->transports as $index => $transport)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $transport->transport_type ?? '-' }}</td>
                                <td>
                                    <strong>{{ $transport->driver_name ?? '-' }}</strong>
                                    @if ($transport->driver_phone)
                                        <br><small class="text-muted">{{ $transport->driver_phone }}</small>
                                    @endif
                                </td>
                                <td>{{ number_format($transport->cost, 2) }}</td>
                                <td>{{ number_format($transport->selling_price, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $transport->currency == 'KWD' ? 'primary' : 'success' }}">
                                        {{ $transport->currency == 'KWD'
                                            ? 'د.ك'
                                            : ($transport->currency == 'SAR'
                                                ? 'ر.س'
                                                : ($transport->currency == 'USD'
                                                    ? '$'
                                                    : '€')) }}
                                    </span>
                                </td>
                                <td class="{{ $transport->profit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                    {{ number_format($transport->profit, 2) }}
                                </td>
                                <td>
                                    @if ($transport->ticket_file_path)
                                        <a href="{{ asset('storage/' . $transport->ticket_file_path) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-alt"></i> عرض
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="bg-light">
                                <td colspan="8" class="small">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>معلومات المركبة:</strong> {{ $transport->vehicle_info ?? '-' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>موعد الانطلاق:</strong>
                                            @if (isset($transport->departure_time))
                                                {{ \Carbon\Carbon::parse($transport->departure_time)->format('d/m/Y H:i') }}
                                                {{-- بالهجري --}}
                                                <small class="d-block text-muted hijri-date"
                                                    data-date="{{ \Carbon\Carbon::parse($transport->departure_time)->format('Y-m-d') }}"></small>
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <strong>موعد الوصول:</strong>
                                            @if (isset($transport->arrival_time))
                                                {{ \Carbon\Carbon::parse($transport->arrival_time)->format('d/m/Y H:i') }}
                                                {{-- بالهجري --}}
                                                <small class="d-block text-muted hijri-date"
                                                    data-date="{{ \Carbon\Carbon::parse($transport->arrival_time)->format('Y-m-d') }}"></small>
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </div>
                                        {{-- الملاحظات --}}
                                        <div class="col-md-4">
                                            <strong>الملاحظات:</strong>
                                            <p class="text-muted">{{ $transport->notes ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="8" class="text-center bg-light">إجماليات حسب العملة</th>
                        </tr>
                        @php
                            $transportsByCurrency = $operationReport->transports->groupBy('currency');
                        @endphp
                        @foreach ($transportsByCurrency as $currency => $transportsGroup)
                            <tr>
                                <th colspan="3">إجمالي {{ $currency == 'KWD' ? 'الدينار' : 'الريال' }}</th>
                                <th>{{ number_format($transportsGroup->sum('cost'), 2) }}</th>
                                <th>{{ number_format($transportsGroup->sum('selling_price'), 2) }}</th>
                                <th>
                                    <span class="badge bg-{{ $currency == 'KWD' ? 'primary' : 'success' }}">
                                        {{ $currency == 'KWD' ? 'د.ك' : 'ر.س' }}
                                    </span>
                                </th>
                                <th
                                    class="{{ $transportsGroup->sum('profit') > 0 ? 'profit-positive' : 'profit-negative' }}">
                                    {{ number_format($transportsGroup->sum('profit'), 2) }}
                                </th>
                                <th></th>
                            </tr>
                        @endforeach
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <!-- قسم الفنادق -->
    @if ($operationReport->hotels->count() > 0)
        <div class="report-section">
            <div class="report-section-header">
                <h2 class="h5 mb-0">بيانات الفنادق</h2>
                <span class="badge-section">{{ $operationReport->hotels->count() }} فندق</span>
            </div>
            <div class="report-section-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>اسم الفندق</th>
                                <th>المدينة</th>
                                <th>تاريخ الدخول</th>
                                <th>تاريخ الخروج</th>
                                <th>عدد الليالي</th>
                                <th>عدد الغرف</th>
                                <th>تكلفة الليلة</th>
                                <th>سعر البيع</th>
                                <th>إجمالي التكلفة</th>
                                <th>إجمالي البيع</th>
                                <th>العملة</th>
                                <th>الربح</th>
                                <th>المرفقات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operationReport->hotels as $index => $hotel)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $hotel->hotel_name ?? '-' }}</td>
                                    <td>{{ $hotel->city ?? '-' }}</td>
                                    <td>{{ $hotel->check_in ? $hotel->check_in->format('Y-m-d') : '-' }}<small
                                            class="d-block text-muted hijri-date"
                                            data-date="{{ $hotel->check_in->format('Y-m-d') }}"></small></td>
                                    <td>{{ $hotel->check_out ? $hotel->check_out->format('Y-m-d') : '-' }}<small
                                            class="d-block text-muted hijri-date"
                                            data-date="{{ $hotel->check_out->format('Y-m-d') }}"></small></td>
                                    <td>{{ $hotel->nights }}</td>
                                    <td>{{ $hotel->rooms }}</td>
                                    <td>{{ number_format($hotel->night_cost, 2) }}</td>
                                    <td>{{ number_format($hotel->night_selling_price, 2) }}</td>
                                    <td>{{ number_format($hotel->total_cost, 2) }}</td>
                                    <td>{{ number_format($hotel->total_selling_price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $hotel->currency == 'KWD' ? 'primary' : 'success' }}">
                                            {{ $hotel->currency == 'KWD'
                                                ? 'د.ك'
                                                : ($hotel->currency == 'SAR'
                                                    ? 'ر.س'
                                                    : ($hotel->currency == 'USD'
                                                        ? '$'
                                                        : '€')) }}
                                        </span>
                                    </td>
                                    <td class="{{ $hotel->profit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ number_format($hotel->profit, 2) }}
                                    </td>
                                    <td>
                                        @if ($hotel->voucher_file_path)
                                            @php
                                                $fileExtension = pathinfo(
                                                    $hotel->voucher_file_path,
                                                    PATHINFO_EXTENSION,
                                                );
                                                $isImage = in_array(strtolower($fileExtension), [
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'gif',
                                                    'webp',
                                                ]);
                                            @endphp
                                            <a href="{{ asset('storage/' . $hotel->voucher_file_path) }}"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                @if ($isImage)
                                                    <i class="fas fa-image"></i> صورة
                                                @else
                                                    <i class="fas fa-file-pdf"></i> ملف
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <div class="col-4 mb-3">
                                    <strong> ملاحظات:</strong> {{ $hotel->notes ?? '-' }}
                                </div>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="14" class="text-center bg-light">إجماليات حسب العملة</th>
                            </tr>
                            @php
                                $hotelsByCurrency = $operationReport->hotels->groupBy('currency');
                            @endphp
                            @foreach ($hotelsByCurrency as $currency => $hotelsGroup)
                                <tr>
                                    <th colspan="9">إجمالي {{ $currency == 'KWD' ? 'الدينار' : 'الريال' }}</th>
                                    <th>{{ number_format($hotelsGroup->sum('total_cost'), 2) }}</th>
                                    <th>{{ number_format($hotelsGroup->sum('total_selling_price'), 2) }}</th>
                                    <th>
                                        <span class="badge bg-{{ $currency == 'KWD' ? 'primary' : 'success' }}">
                                            {{ $currency == 'KWD' ? 'د.ك' : 'ر.س' }}
                                        </span>
                                    </th>
                                    <th
                                        class="{{ $hotelsGroup->sum('profit') > 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ number_format($hotelsGroup->sum('profit'), 2) }}
                                    </th>
                                    <th></th>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- قسم الرحلات البرية -->
    @if ($operationReport->landTrips->count() > 0)
        <div class="report-section">
            <div class="report-section-header">
                <h2 class="h5 mb-0">بيانات الرحلات البرية</h2>
                <span class="badge-section">{{ $operationReport->landTrips->count() }} رحلة</span>
            </div>
            <div class="report-section-body">
                <div class="table-responsive">
                    {{-- الجهة المُصدِرة للرحلة (من الحجز المرتبط بالتقرير) --}}
                    @if (!empty($linkedAgentName))
                        <div class="alert alert-info py-2 px-3 mb-3">
                            <i class="fas fa-building me-1"></i>
                            الجهة المُصدِرة: <strong>{{ $linkedAgentName }}</strong>
                        </div>
                    @endif
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>نوع الرحلة</th>
                                <th>تاريخ المغادرة</th>
                                <th>تاريخ العودة</th>
                                <th>عدد الأيام</th>
                                <th>تكلفة النقل</th>
                                <th>تكلفة فندق مكة</th>
                                <th>تكلفة فندق المدينة</th>
                                <th>تكاليف إضافية</th>
                                <th>إجمالي التكلفة</th>
                                <th>سعر البيع</th>
                                <th>العملة</th>
                                <th>الربح</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operationReport->landTrips as $index => $landTrip)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $landTrip->trip_type ?? '-' }}</td>
                                    <td style="
    font-size: small;
">{{ $landTrip->departure_date ? $landTrip->departure_date->format('Y-m-d') : '-' }}
                                        <small class="d-block text-muted hijri-date"
                                            data-date="{{ $landTrip->departure_date->format('Y-m-d') }}"></small>
                                    </td>
                                    <td style="
    font-size: small;
">{{ $landTrip->return_date ? $landTrip->return_date->format('Y-m-d') : '-' }}
                                        <small class="d-block text-muted hijri-date"
                                            data-date="{{ $landTrip->return_date->format('Y-m-d') }}"></small>
                                    </td>
                                    <td>{{ $landTrip->days }}</td>
                                    <td>{{ number_format($landTrip->transport_cost, 2) }}</td>
                                    <td>{{ number_format($landTrip->mecca_hotel_cost, 2) }}</td>
                                    <td>{{ number_format($landTrip->medina_hotel_cost, 2) }}</td>
                                    <td>{{ number_format($landTrip->extra_costs, 2) }}</td>
                                    <td>{{ number_format($landTrip->total_cost, 2) }}</td>
                                    <td>{{ number_format($landTrip->selling_price, 2) }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $landTrip->currency == 'KWD' ? 'primary' : 'success' }}">
                                            {{ $landTrip->currency == 'KWD'
                                                ? 'د.ك'
                                                : ($landTrip->currency == 'SAR'
                                                    ? 'ر.س'
                                                    : ($landTrip->currency == 'USD'
                                                        ? '$'
                                                        : '€')) }}
                                        </span>
                                    </td>
                                    <td class="{{ $landTrip->profit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ number_format($landTrip->profit, 2) }}
                                    </td>
                                </tr>
                                @if ($landTrip->notes)
                                    <tr>
                                        <td colspan="13">
                                            <small><strong>ملاحظات:</strong> {{ $landTrip->notes }}</small>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="13" class="text-center bg-light">إجماليات حسب العملة</th>
                            </tr>
                            @php
                                $landTripsByCurrency = $operationReport->landTrips->groupBy('currency');
                            @endphp
                            @foreach ($landTripsByCurrency as $currency => $landTripsGroup)
                                <tr>
                                    <th colspan="9">إجمالي {{ $currency == 'KWD' ? 'الدينار' : 'الريال' }}</th>
                                    <th>{{ number_format($landTripsGroup->sum('total_cost'), 2) }}</th>
                                    <th>{{ number_format($landTripsGroup->sum('selling_price'), 2) }}</th>
                                    <th>
                                        <span class="badge bg-{{ $currency == 'KWD' ? 'primary' : 'success' }}">
                                            {{ $currency == 'KWD' ? 'د.ك' : 'ر.س' }}
                                        </span>
                                    </th>
                                    <th
                                        class="{{ $landTripsGroup->sum('profit') > 0 ? 'profit-positive' : 'profit-negative' }}">
                                        {{ number_format($landTripsGroup->sum('profit'), 2) }}
                                    </th>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif



    <!-- رسالة إذا لم توجد بيانات -->
    @if (
        $operationReport->visas->count() == 0 &&
            $operationReport->flights->count() == 0 &&
            $operationReport->transports->count() == 0 &&
            $operationReport->hotels->count() == 0 &&
            $operationReport->landTrips->count() == 0)
        <div class="report-section">
            <div class="empty-section">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>لا توجد بيانات خدمات</h4>
                <p class="text-muted">لم يتم إضافة أي خدمات لهذا التقرير بعد.</p>
                <a href="{{ route('admin.operation-reports.edit', $operationReport) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة خدمات
                </a>
            </div>
        </div>
    @endif
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/preventClick.js') }}"></script>
    <script src="{{ asset('js/hijriDataConvert.js') }}"></script>
@endpush
