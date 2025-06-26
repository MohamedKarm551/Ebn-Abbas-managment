@extends('layouts.app')

{{-- *** تأكد من وجود رابط Font Awesome في layout الرئيسي layouts/app.blade.php *** --}}
{{-- مثال: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> --}}

@section('content')
    <div class="container">
        <h1>سجل المدفوعات - {{ $company->name }}</h1>
        {{-- ✅ إضافة قسم ملخص الحسابات الحالية --}}
        <div class="card mb-4"
            style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 1px solid #dee2e6; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div class="card-header"
                style="background: linear-gradient(120deg, #3b82f6 60%, #2563eb 100%); color: white; border-radius: 12px 12px 0 0;">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-building me-2"></i>
                    ملخص الحسابات الحالية
                </h5>
            </div>
            <div class="card-body">
                @php
                    // ✅ استخدام الحسابات المحسوبة مسبقاً في الشركة
                    $company->calculateTotals();
                    // dd($company->toArray());

                    // عدد الحجوزات
                    $totalBookings = $company->bookings_count ?? $company->bookings()->count();

                    // المستحق حسب العملة
                    $dueByCurrency =
                        $company->computed_total_due_by_currency ??
                        ($company->total_due_by_currency ?? ['SAR' => $company->total_due ?? 0]);

                    // المدفوع والخصومات حسب العملة
                    $paidByCurrency = $company->computed_total_paid_by_currency ?? [];
                    $discountsByCurrency = $company->computed_total_discounts_by_currency ?? [];

                    // إذا لم تكن محسوبة، احسبها من المدفوعات
                    if (empty($paidByCurrency) && $company->payments) {
                        $companyPaymentsGrouped = $company->payments->groupBy('currency');

                        foreach ($companyPaymentsGrouped as $currency => $paymentsForCurrency) {
                            $paidByCurrency[$currency] = $paymentsForCurrency->where('amount', '>=', 0)->sum('amount');
                            $discountsByCurrency[$currency] = abs(
                                $paymentsForCurrency->where('amount', '<', 0)->sum('amount'),
                            );
                        }
                    }

                    // المتبقي حسب العملة
                    $remainingByCurrency =
                        $company->computed_remaining_by_currency ??
                        ($company->remaining_by_currency ?? ['SAR' => $company->remaining_amount ?? 0]);
                @endphp

                <div class="row g-3">
                    {{-- عدد الحجوزات --}}
                    <div class="col-12 col-md-3">
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, #8b5cf6, #a78bfa); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-calendar-alt" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $totalBookings }}</h4>
                            <small class="opacity-90">عدد الحجوزات</small>
                        </div>
                    </div>

                    {{-- إجمالي المستحق --}}
                    <div class="col-12 col-md-3">
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, #06b6d4, #67e8f9); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-hand-holding-usd" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                @php
                                    $dueHotels = $company->computed_total_due_bookings_by_currency ?? [];
                                    $dueTrips = $company->computed_total_due_land_trips_by_currency ?? [];
                                    $hasHotels = collect($dueHotels)->filter(fn($v) => $v > 0)->isNotEmpty();
                                    $hasTrips = collect($dueTrips)->filter(fn($v) => $v > 0)->isNotEmpty();
                                @endphp

                                @if ($hasHotels)
                                    <div class="mb-1">
                                        <span class="badge bg-success text-white" style="font-size: 0.9em;">
                                            <i class="fas fa-hotel me-1"></i>
                                            مستحقات الفنادق:
                                        </span>
                                    </div>
                                    @foreach (['SAR', 'KWD'] as $currency)
                                        @if (($dueHotels[$currency] ?? 0) > 0)
                                            <div class="mb-1">
                                                <h6 class="mb-0">{{ number_format($dueHotels[$currency], 2) }}</h6>
                                                <small
                                                    class="opacity-90">{{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</small>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                @if ($hasTrips)
                                    <div class="mb-1">
                                        <span class="badge bg-info text-dark" style="font-size: 0.9em;border: 1px solid #000;">
                                            <i class="fas fa-bus me-1"></i>
                                            <a href="{{ route('admin.company-payments.show', $company->id) }}"
                                                class="text-dark text-decoration-none">
                                                مستحقات الرحلات البرية:
                                            </a>
                                        </span>
                                    </div>
                                    @foreach (['SAR', 'KWD'] as $currency)
                                        @if (($dueTrips[$currency] ?? 0) > 0)
                                            <div class="mb-1">
                                                <h6 class="mb-0">{{ number_format($dueTrips[$currency], 2) }}</h6>
                                                <small
                                                    class="opacity-90">{{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</small>
                                            </div>
                                        @endif
                                    @endforeach
                                    <div class="bg-warning">
                                        إجمالي المستحق بالريال : {{ $company->computed_total_due_by_currency['SAR'] }}
                                        <br>
                                        إجمالي المستحق بالدينار : {{ $company->computed_total_due_by_currency['KWD'] ?? 0 }}
                                    </div>
                                @endif

                                @if (!$hasHotels && !$hasTrips)
                                    <h5 class="mb-0">0.00</h5>
                                    <small class="opacity-90">ريال</small>
                                @endif
                            </div>
                            <small class="opacity-90 d-block">إجمالي المستحق - راجع مدفوعات الشركة للرحلات البرية لتعرف هل تم دفع المستحق </small>
                        </div>
                    </div>

                    {{-- المدفوع --}}
                    <div class="col-12 col-md-3">
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, #10b981, #34d399); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-credit-card" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                @php $hasPaidAmount = false; @endphp
                                @foreach (['SAR', 'KWD'] as $currency)
                                    @if (
                                        ($company->computed_total_paid_by_currency[$currency] ?? 0) > 0 ||
                                            ($company->computed_total_discounts_by_currency[$currency] ?? 0) > 0)
                                        @php $hasPaidAmount = true; @endphp
                                        <div class="mb-1">
                                            <h6 class="mb-0">
                                                {{ number_format($company->computed_total_paid_by_currency[$currency] ?? 0, 2) }}
                                            </h6>
                                            <small class="opacity-90">{{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</small>
                                            @if (($company->computed_total_discounts_by_currency[$currency] ?? 0) > 0)
                                                <div style="font-size: 0.7rem; opacity: 0.8;">
                                                    خصومات:
                                                    {{ number_format($company->computed_total_discounts_by_currency[$currency], 2) }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                                @if (!$hasPaidAmount)
                                    <h5 class="mb-0">0.00</h5>
                                    <small class="opacity-90">ريال</small>
                                @endif
                            </div>
                            <small class="opacity-90 d-block">المدفوع</small>
                        </div>
                    </div>

                    {{-- المتبقي --}}
                    <div class="col-12 col-md-3">
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, #f59e0b, #fcd34d); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-wallet" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <div class="mb-1">
                                    <h6 class="mb-0">
                                        {{ ($company->computed_total_due_bookings_by_currency['SAR'] ?? 0) -
                                            ($company->computed_total_paid_by_currency['SAR'] ?? 0) -
                                            ($company->computed_total_discounts_by_currency['SAR'] ?? 0) }}

                                    </h6>
                                    <small class="opacity-90">ريال</small>
                                </div>
                                <div class="mb-1">
                                    <h6 class="mb-0">
                                        @php
                                            if (
                                                isset($company->computed_total_due_bookings_by_currency['KWD']) &&
                                                isset($company->computed_total_paid_by_currency['KWD']) &&
                                                isset($company->computed_total_discounts_by_currency['KWD'])
                                            ) {
                                                $remainingKWD =
                                                    $company->computed_total_due_bookings_by_currency['KWD'] -
                                                    $company->computed_total_paid_by_currency['KWD'] -
                                                    $company->computed_total_discounts_by_currency['KWD'];
                                            } else {
                                                $remainingKWD = 0;
                                            }
                                        @endphp
                                        {{ $remainingKWD }}
                                    </h6>
                                    <small class="opacity-90">دينار</small>
                                </div>
                            </div>
                            <small class="opacity-90 d-block">المتبقي من الشركة</small>
                        </div>
                    </div>
                </div>

                {{-- مؤشر الحالة --}}
                <div class="row mt-3">
                    <div class="col-12">
                        @php
                            $totalRemaining = collect($remainingByCurrency)->sum();
                            if ($totalRemaining == 0) {
                                $statusClass = 'success';
                                $statusIcon = 'fa-check-circle';
                                $statusText = 'الحساب مكتمل ومتوازن - الشركة دفعت جميع مستحقاتها';
                            } elseif ($totalRemaining < 0) {
                                $statusClass = 'info';
                                $statusIcon = 'fa-info-circle';
                                $statusText = 'الشركة دفعت مبلغ زائد عن المطلوب';
                            } else {
                                $statusClass = 'warning';
                                $statusIcon = 'fa-clock';
                                $statusText = 'يوجد مبلغ مستحق على الشركة';
                            }
                        @endphp
                        <div class="alert alert-{{ $statusClass }} mb-0 d-flex align-items-center">
                            <i class="fas {{ $statusIcon }} me-2"></i>
                            <strong>{{ $statusText }}</strong>
                            @if ($payments->count() > 0)
                                <span class="ms-auto">
                                    <small>آخر دفعة: {{ $payments->first()->payment_date->format('d/m/Y') }}</small>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
            <div class="mb-3">
                <button id="showChartBtn" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-chart-line me-1"></i> عرض الرسم البياني للدفعات
                </button>
            </div>

            <div id="chartContainer" class="mb-4 shadow-sm"
                style="display: none; position: relative; height:300px; width:100%;">
                <canvas id="paymentsChart"></canvas>
            </div>
            {{-- *** نهاية إضافة زرار ومكان الرسم البياني *** --}}


            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th> {{-- *** إضافة رأس عمود الترقيم *** --}}
                        <th>التاريخ</th>
                        <th>المبلغ</th>
                        <th>الملاحظات</th>
                        <th>الإيصال</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        @php
                            // --- معالجة الملاحظات والإيصال مرة واحدة ---
                            $receiptUrl = null;
                            $isUploaded = false;
                            $displayNotes = $payment->notes; // الملاحظات الأصلية للعرض

                            // أولاً: تحقق من الملف المرفوع
                            if ($payment->receipt_path) {
                                $isUploaded = true;
                            }
                            // ثانياً: إذا لا يوجد ملف مرفوع، ابحث عن رابط في الملاحظات
                            elseif ($payment->notes) {
                                // ابحث عن أول رابط http/https
                                $pattern = '/(https?:\/\/[^\s]+)/';
                                if (preg_match($pattern, $payment->notes, $matches)) {
                                    $receiptUrl = $matches[0]; // استخراج الرابط
                                    // استبدل الرابط في النص المعروض
                                    $displayNotes = preg_replace($pattern, '"رابط صورة الإيصال"', $payment->notes, 1); // استبدال مرة واحدة فقط
                                }
                            }
                            // --- نهاية المعالجة ---
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td> {{-- *** إضافة خلية الترقيم *** --}}
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td>
                                {{ number_format($payment->amount, 2) }}
                                {{ $payment->currency === 'SAR' ? 'ريال' : 'دينار كويتي' }}
                            </td> {{-- *** عرض الملاحظات بعد استبدال الرابط (إن وجد) *** --}}
                            <td>{!! nl2br(e($displayNotes)) !!}</td> {{-- استخدم nl2br للحفاظ على الأسطر الجديدة و e للحماية --}}

                            <td> {{-- *** خلية الإيصال *** --}}
                                @if ($isUploaded)
                                    {{-- عرض أيقونة تشير لوجود ملف مرفوع --}}
                                    <span title="تم إرفاق إيصال (لا يمكن عرضه مباشرة)">
                                        <i class="fas fa-file-invoice text-success"></i>
                                    </span>
                                @elseif ($receiptUrl)
                                    {{-- عرض أيقونة كرابط للـ URL الموجود في الملاحظات --}}
                                    <a href="{{ $receiptUrl }}" target="_blank" title="فتح رابط الإيصال من الملاحظات">
                                        <i class="fas fa-external-link-alt"></i> {{-- *** تغيير هنا: عرض أيقونة الرابط *** --}}
                                    </a>
                                @else
                                    {{-- لا يوجد إيصال أو رابط --}}
                                    -
                                @endif
                            </td>
                            <td class="d-flex gap-1">
                                <a href="{{ route('reports.company.payment.edit', $payment->id) }}"
                                    class="btn btn-warning btn-sm">تعديل</a>
                                <form action="{{ route('reports.company.payment.destroy', $payment->id) }}" method="POST"
                                    onsubmit="return confirm('هل أنت متأكد من حذف هذه الدفعة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @push('scripts')
            {{-- مكتبات Chart.js الأساسية --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            {{-- محول التاريخ لـ Chart.js --}}
            <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
            </script>
            {{-- مكتبة الزووم --}}
            <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/2.0.1/chartjs-plugin-zoom.min.js"></script>
            {{-- Include Str facade if needed for limiting notes, or do it in controller --}}
            @php use Illuminate\Support\Str; @endphp

            <script>
                function formatCurrency(value) {
                    return new Intl.NumberFormat('ar-SA', {
                        style: 'currency',
                        currency: 'SAR'
                    }).format(value);
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const showChartBtn = document.getElementById('showChartBtn');
                    const chartContainer = document.getElementById('chartContainer');
                    const ctx = document.getElementById('paymentsChart');
                    let paymentsChartInstance = null;

                    // --- 1. Get Timeline Data from Controller ---
                    const timelineEvents = @json($timelineEvents ?? []); // Use the new variable

                    if (showChartBtn && chartContainer && ctx && timelineEvents.length > 0) {
                        showChartBtn.addEventListener('click', function() {
                            chartContainer.style.display = 'block';

                            if (!paymentsChartInstance) {
                                // --- 2. Prepare Chart Data ---
                                const balanceData = timelineEvents.map(event => ({
                                    x: event.chart_date, // Use pre-formatted date
                                    y: event.running_balance
                                }));

                                // Create points for scatter plot - place them ON the balance line
                                const eventPoints = timelineEvents.map(event => ({
                                    x: event.chart_date,
                                    y: event.running_balance, // Y value is the balance at that time
                                    type: event.type,
                                    amount: event.amount, // Original amount of the event
                                    balance_change: event.balance_change, // How balance changed
                                    details: event.details
                                }));

                                paymentsChartInstance = new Chart(ctx, {
                                    type: 'line', // Base type is line for the balance
                                    data: {
                                        datasets: [
                                            // --- Dataset 1: Running Balance Line ---
                                            {
                                                label: 'الرصيد المستحق على الشركة',
                                                data: balanceData,
                                                borderColor: 'rgb(0, 123, 255)', // Blue line
                                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                                tension: 0, // Straight lines between points
                                                pointRadius: 0, // Hide points on the line itself
                                                fill: true,
                                                order: 2 // Draw line behind points
                                            },
                                            // --- Dataset 2: Event Markers (Scatter) ---
                                            {
                                                type: 'scatter', // Overlay scatter points
                                                label: 'الأحداث (حجز/دفعة)', // Combined label
                                                data: eventPoints,
                                                pointRadius: 6,
                                                pointHoverRadius: 8,
                                                pointBackgroundColor: function(context) {
                                                    // Green for payment, Red for booking
                                                    return context.raw.type === 'payment' ?
                                                        'rgb(40, 167, 69)' : 'rgb(220, 53, 69)';
                                                },
                                                pointStyle: function(context) {
                                                    // Circle for payment, Triangle for booking
                                                    return context.raw.type === 'payment' ?
                                                        'circle' : 'triangle';
                                                },
                                                order: 1 // Draw points on top of line
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        interaction: {
                                            mode: 'index',
                                            intersect: false
                                        }, // Show tooltip for nearest points on x-axis
                                        scales: {
                                            x: {
                                                type: 'time',
                                                time: {
                                                    unit: 'day',
                                                    tooltipFormat: 'dd/MM/yyyy',
                                                    displayFormats: {
                                                        day: 'dd/MM',
                                                        month: 'MMM yyyy'
                                                    }
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'التاريخ'
                                                }
                                            },
                                            y: { // Single Y-axis for Balance
                                                type: 'linear',
                                                position: 'left',
                                                beginAtZero: false, // Start axis based on data range
                                                title: {
                                                    display: true,
                                                    text: 'الرصيد المستحق على الشركة (ريال)'
                                                },
                                                ticks: {
                                                    callback: formatCurrency
                                                }
                                            }
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    title: function(tooltipItems) {
                                                        // Show date from the first item
                                                        return tooltipItems[0]?.label || '';
                                                    },
                                                    label: function(context) {
                                                        const event = context
                                                            .raw; // Get the raw data point
                                                        let label = '';

                                                        if (context.dataset.type === 'scatter' &&
                                                            event) {
                                                            // Scatter point tooltip (Booking or Payment)
                                                            if (event.type === 'booking') {
                                                                label =
                                                                    `⬆️ حجز: +${formatCurrency(event.amount)}`;
                                                            } else if (event.type === 'payment') {
                                                                label =
                                                                    `⬇️ دفعة: -${formatCurrency(event.amount)}`;
                                                            }
                                                            label += ` (${event.details})`;
                                                            label +=
                                                                `\nالرصيد بعد الحدث: ${formatCurrency(event.y)}`; // event.y is the running_balance
                                                        } else if (context.dataset.type === 'line') {
                                                            // Optional: Tooltip for the line itself if needed
                                                            // label = `الرصيد: ${formatCurrency(context.parsed.y)}`;
                                                            return null; // Hide tooltip for the line itself, focus on events
                                                        }
                                                        return label;
                                                    }
                                                }
                                            },
                                            legend: {
                                                display: true,
                                                position: 'top',
                                                labels: {
                                                    // Custom legend items for clarity
                                                    generateLabels: function(chart) {
                                                        return [{
                                                                text: 'الرصيد المستحق',
                                                                fillStyle: 'rgb(0, 123, 255)',
                                                                strokeStyle: 'rgb(0, 123, 255)',
                                                                lineWidth: 2,
                                                                hidden: false,
                                                                index: 0
                                                            },
                                                            {
                                                                text: '▲ حجز جديد (+)',
                                                                pointStyle: 'triangle',
                                                                fillStyle: 'rgb(220, 53, 69)',
                                                                strokeStyle: 'rgb(220, 53, 69)',
                                                                hidden: false,
                                                                index: 1
                                                            },
                                                            {
                                                                text: '● دفعة مستلمة (-)',
                                                                pointStyle: 'circle',
                                                                fillStyle: 'rgb(40, 167, 69)',
                                                                strokeStyle: 'rgb(40, 167, 69)',
                                                                hidden: false,
                                                                index: 1
                                                            }
                                                        ];
                                                    }
                                                },
                                                // Handle clicking custom legend
                                                onClick: (e, legendItem, legend) => {
                                                    const index = legendItem.index;
                                                    const type = legendItem.text.includes('حجز') ?
                                                        'booking' : (legendItem.text.includes('دفعة') ?
                                                            'payment' : 'balance');
                                                    const ci = legend.chart;

                                                    if (type === 'balance') {
                                                        const balanceDataset = ci.data.datasets[0];
                                                        balanceDataset.hidden = !balanceDataset.hidden;
                                                    } else {
                                                        // Toggle visibility of points based on type
                                                        const scatterDataset = ci.data.datasets[1];
                                                        // This basic toggle hides/shows the whole scatter dataset.
                                                        // More complex logic needed to hide only specific point types.
                                                        // For simplicity, we'll toggle the whole scatter set for now.
                                                        scatterDataset.hidden = !scatterDataset.hidden;

                                                        // A better approach would filter the data points, but that's more involved.
                                                    }
                                                    ci.update();
                                                }
                                            },
                                            zoom: {
                                                pan: {
                                                    enabled: true,
                                                    mode: 'x',
                                                    threshold: 5
                                                },
                                                zoom: {
                                                    wheel: {
                                                        enabled: true
                                                    },
                                                    pinch: {
                                                        enabled: true
                                                    },
                                                    mode: 'x'
                                                }
                                            }
                                        } // end plugins
                                    } // end options
                                }); // end new Chart
                            } // end if !paymentsChartInstance
                        }); // end addEventListener('click')
                    } else if (showChartBtn) {
                        showChartBtn.textContent = 'لا توجد بيانات كافية لعرض الرسم البياني';
                        showChartBtn.disabled = true;
                    } // end if/else check data

                }); // end DOMContentLoaded
            </script>
        @endpush
    @endsection
