@extends('layouts.app')

{{-- *** تأكد من وجود رابط Font Awesome في layout الرئيسي layouts/app.blade.php *** --}}
{{-- مثال: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> --}}

@section('content')
    <div class="container">
        <h1>سجل المدفوعات - {{ $agent->name }}</h1>
        <a href="{{ route('reports.agent.bookings', $agent->id) }}" class="w-25 p-2 mt-2 mb-2 btn btn-primary btn-sm">
            الحجوزات
        </a>

        <button type="button" class="w-25 p-2 mt-2 mb-2 btn btn-success btn-sm" data-bs-toggle="modal"
            data-bs-target="#agentPaymentModal{{ $agent->id }}">
            تسجيل دفعة
        </button>

        <button type="button" class=" w-25 p-2 mt-2 mb-2 btn btn-warning btn-sm" data-bs-toggle="modal"
            data-bs-target="#agentDiscountModal{{ $agent->id }}">
            تطبيق خصم
        </button>
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
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
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
                                تأكد من مبلغ الخصم قبل المتابعة. هذا الإجراء سيؤثر على الحساب النهائي
                                للوكيل.
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
        {{-- ✅ إضافة قسم ملخص الحسابات الحالية --}}
        <div class="card mb-4"
            style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 1px solid #dee2e6; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div class="card-header"
                style="background: linear-gradient(120deg, #10b981 60%, #059669 100%); color: white; border-radius: 12px 12px 0 0;">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-calculator me-2"></i>
                    ملخص الحسابات الحالية
                </h5>
            </div>
            <div class="card-body">
                @php
                    // ✅ استخدام الحسابات المحسوبة مسبقاً في الوكيل
                    $agent->calculateTotals();

                    // عدد الحجوزات
                    $totalBookings = $agent->bookings_count ?? $agent->bookings()->count();

                    // المستحق حسب العملة
                    $dueByCurrency =
                        $agent->computed_total_due_by_currency ??
                        ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);

                    // المدفوع والخصومات حسب العملة
                    $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
                    $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];

                    // إذا لم تكن محسوبة، احسبها من المدفوعات
                    if (empty($paidByCurrency) && $agent->payments) {
                        $agentPaymentsGrouped = $agent->payments->groupBy('currency');

                        foreach ($agentPaymentsGrouped as $currency => $paymentsForCurrency) {
                            $paidByCurrency[$currency] = $paymentsForCurrency->where('amount', '>=', 0)->sum('amount');
                            $discountsByCurrency[$currency] = abs(
                                $paymentsForCurrency->where('amount', '<', 0)->sum('amount'),
                            );
                        }
                    }

                    // المتبقي حسب العملة
                    $remainingByCurrency =
                        $agent->computed_remaining_by_currency ??
                        ($agent->remaining_by_currency ?? ['SAR' => $agent->remaining_amount ?? 0]);
                @endphp

                <div class="row g-3">
                    {{-- عدد الحجوزات --}}
                    <div class="col-md-3">
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, #3b82f6, #60a5fa); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-calendar-check" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $totalBookings }}</h4>
                            <small class="opacity-90">عدد الحجوزات</small>
                        </div>
                    </div>

                    {{-- إجمالي المستحق --}}
                    <div class="col-md-3">
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, #f59e0b, #fbbf24); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-file-invoice-dollar" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                @foreach ($dueByCurrency as $currency => $amount)
                                    @if ($amount > 0)
                                        <div class="mb-1">
                                            <h5 class="mb-0">{{ number_format($amount, 2) }}</h5>
                                            <small class="opacity-90">{{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</small>
                                        </div>
                                    @endif
                                @endforeach
                                @if (empty(array_filter($dueByCurrency)))
                                    <h5 class="mb-0">0.00</h5>
                                    <small class="opacity-90">ريال</small>
                                @endif
                            </div>
                            <small class="opacity-90 d-block">إجمالي المستحق</small>
                        </div>
                    </div>

                    {{-- المدفوع --}}
                    <div class="col-md-3">
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, #10b981, #34d399); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-hand-holding-usd" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                @php $hasPaidAmount = false; @endphp
                                @foreach (['SAR', 'KWD'] as $currency)
                                    @if (($paidByCurrency[$currency] ?? 0) > 0 || ($discountsByCurrency[$currency] ?? 0) > 0)
                                        @php $hasPaidAmount = true; @endphp
                                        <div class="mb-1">
                                            <h6 class="mb-0">{{ number_format($paidByCurrency[$currency] ?? 0, 2) }}
                                            </h6>
                                            <small class="opacity-90">{{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</small>
                                            @if (($discountsByCurrency[$currency] ?? 0) > 0)
                                                <div style="font-size: 0.7rem; opacity: 0.8;">
                                                    خصومات: {{ number_format($discountsByCurrency[$currency], 2) }}
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
                    <div class="col-md-3">
                        @php
                            $hasRemaining = !empty(array_filter($remainingByCurrency));
                            $isOverpaid = false;
                            if ($hasRemaining) {
                                $isOverpaid = collect($remainingByCurrency)->some(function ($amount) {
                                    return $amount < 0;
                                });
                            }
                        @endphp
                        <div class="text-center p-3"
                            style="background: linear-gradient(135deg, {{ $isOverpaid ? '#ef4444, #f87171' : ($hasRemaining ? '#ef4444, #f87171' : '#6b7280, #9ca3af') }}); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas {{ $isOverpaid ? 'fa-arrow-down' : ($hasRemaining ? 'fa-exclamation-triangle' : 'fa-check-circle') }}"
                                    style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                @if ($hasRemaining)
                                    @foreach ($remainingByCurrency as $currency => $amount)
                                        @if ($amount != 0)
                                            <div class="mb-1">
                                                <h5 class="mb-0">
                                                    {{ $amount > 0 ? '+' : '' }}{{ number_format($amount, 2) }}</h5>
                                                <small
                                                    class="opacity-90">{{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</small>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <h5 class="mb-0">0.00</h5>
                                    <small class="opacity-90">ريال</small>
                                @endif
                            </div>
                            <small class="opacity-90 d-block">
                                @if ($isOverpaid)
                                    دفعنا زيادة
                                @elseif ($hasRemaining)
                                    المتبقي
                                @else
                                    مدفوع بالكامل
                                @endif
                            </small>
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
                                $statusText = 'الحساب مكتمل ومتوازن';
                            } elseif ($totalRemaining < 0) {
                                $statusClass = 'info';
                                $statusIcon = 'fa-info-circle';
                                $statusText = 'تم دفع مبلغ زائد للوكيل';
                            } else {
                                $statusClass = 'warning';
                                $statusIcon = 'fa-clock';
                                $statusText = 'يوجد مبلغ مستحق للوكيل';
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
        </div>

        <div class="mb-3">
            <button id="showChartBtn" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i> عرض الرسم البياني للدفعات
            </button>
        </div>

        {{-- المكان اللي هيظهر فيه الرسم (هيكون مخفي في الأول) --}}
        <div id="chartContainer" class="mb-4 shadow-sm"
            style="display: none; position: relative; height:300px; width:100%;">
            <canvas id="paymentsChart"></canvas>
        </div>
        {{-- *** نهاية إضافة زرار ومكان الرسم البياني *** --}}


        <table class="table table-bordered" id="paymentsTable">
            <thead>
                <tr>
                    <th>#</th> {{-- *** إضافة رأس عمود الترقيم *** --}}
                    <th>المبلغ</th>
                    <th>تاريخ الدفع</th>
                    <th>الملاحظات</th>
                    <th>الإيصال</th> {{-- *** إضافة رأس عمود الإيصال *** --}}
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
                        <td>{{ number_format($payment->amount, 2) }} @if ($payment->currency == 'KWD')
                                دينار كويتي
                            @else
                                ريال سعودي
                            @endif
                        </td>
                        <td>{{ $payment->payment_date->format('d/m/Y') }}</td> {{-- *** تغيير تنسيق التاريخ *** --}}
                        {{-- *** عرض الملاحظات بعد استبدال الرابط (إن وجد) *** --}}
                        <td>{!! nl2br(e($displayNotes)) !!}</td> {{-- استخدم nl2br للحفاظ على الأسطر الجديدة و e للحماية --}}

                        <td> {{-- *** إضافة خلية الإيصال *** --}}
                            @if ($isUploaded)
                                {{-- عرض أيقونة تشير لوجود ملف مرفوع --}}
                                <span title="تم إرفاق إيصال (لا يمكن عرضه مباشرة)">
                                    <i class="fas fa-file-invoice text-success"></i>
                                </span>
                            @elseif ($receiptUrl)
                                {{-- عرض أيقونة كرابط للـ URL الموجود في الملاحظات --}}
                                <a href="{{ $receiptUrl }}" target="_blank" title="فتح رابط الإيصال من الملاحظات">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @else
                                {{-- لا يوجد إيصال أو رابط --}}
                                -
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('reports.agent.payment.edit', $payment->id) }}"
                                class="btn btn-warning btn-sm">تعديل</a>
                            <form action="{{ route('reports.agent.payment.destroy', $payment->id) }}" method="POST"
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
        {{-- محول التاريخ لـ Chart.js --}}
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
        </script>
        {{-- مكتبة الزووم --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/2.0.1/chartjs-plugin-zoom.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

        <script>
            // دالة حساب حجم النقطة
            function calculateRadius(amount, minAmount, maxAmount, minRadius, maxRadius) {
                if (maxAmount === minAmount || amount === null || amount === undefined) {
                    return minRadius;
                }
                const radius = minRadius + ((amount - minAmount) / (maxAmount - minAmount)) * (maxRadius - minRadius);
                return Math.max(minRadius, Math.min(maxRadius, radius));
            }

            document.addEventListener('DOMContentLoaded', function() {
                // إعداد العناصر
                const showChartBtn = document.getElementById('showChartBtn');
                const chartContainer = document.getElementById('chartContainer');
                const ctx = document.getElementById('paymentsChart');
                let paymentsChartInstance = null;

                // *** بداية: تحضير البيانات ***
                // هنا نستخدم متغير PHP مجهز مسبقًا (بدون تداخل معقد مع JavaScript)
                const paymentPoints = @json(
                    $payments->map(function ($payment) {
                        return [
                            'x' => $payment->payment_date->format('Y-m-d'),
                            'y' => $payment->amount,
                        ];
                    }));

                // حساب القيم الدنيا والقصوى للمبالغ
                const amounts = paymentPoints.map(p => p.y).filter(Boolean);
                const minAmount = amounts.length ? Math.min(...amounts) : 0;
                const maxAmount = amounts.length ? Math.max(...amounts) : 0;

                // ثوابت حجم النقطة
                const minRadius = 4;
                const maxRadius = 15;

                // حساب أحجام النقاط
                const pointRadii = paymentPoints.map(p => calculateRadius(p.y, minAmount, maxAmount, minRadius,
                    maxRadius));
                const pointHoverRadii = paymentPoints.map(p => calculateRadius(p.y, minAmount, maxAmount, minRadius,
                    maxRadius) + 2);
                // *** نهاية: تحضير البيانات ***

                if (showChartBtn && chartContainer && ctx && paymentPoints.length > 0) {
                    showChartBtn.addEventListener('click', function() {
                        chartContainer.style.display = 'block';

                        if (!paymentsChartInstance) {
                            paymentsChartInstance = new Chart(ctx, {
                                type: 'scatter',
                                data: {
                                    datasets: [{
                                        label: 'دفعة',
                                        data: paymentPoints,
                                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                                        borderColor: 'rgb(0, 123, 255)',
                                        radius: pointRadii,
                                        hoverRadius: pointHoverRadii
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(value) {
                                                    return value.toLocaleString('ar-SA') + ' ريال';
                                                }
                                            },
                                            grid: {
                                                display: true,
                                                color: 'rgba(0, 0, 0, 0.05)'
                                            }
                                        },
                                        x: {
                                            type: 'time',
                                            time: {
                                                unit: 'day',
                                                tooltipFormat: 'dd/MM/yyyy',
                                                displayFormats: {
                                                    day: 'dd/MM',
                                                    week: 'dd/MM',
                                                    month: 'MMM yyyy',
                                                    year: 'yyyy'
                                                }
                                            },
                                            ticks: {
                                                source: 'auto',
                                                autoSkip: true,
                                                maxRotation: 45,
                                                minRotation: 0
                                            },
                                            title: {
                                                display: true,
                                                text: 'تاريخ الدفعة'
                                            },
                                            grid: {
                                                display: true,
                                                color: 'rgba(0, 0, 0, 0.05)'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                title: function(tooltipItems) {
                                                    const date = new Date(tooltipItems[0].raw.x);
                                                    return date.toLocaleDateString('ar-EG', {
                                                        day: 'numeric',
                                                        month: 'long',
                                                        year: 'numeric'
                                                    });
                                                },
                                                label: function(context) {
                                                    if (context.parsed.y !== null) {
                                                        return 'المبلغ: ' + context.parsed.y
                                                            .toLocaleString('ar-SA') + ' ريال';
                                                    }
                                                    return '';
                                                }
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
                                    }
                                }
                            });
                        }
                    });
                } else if (showChartBtn) {
                    showChartBtn.textContent = 'لا توجد بيانات دفعات لعرض الرسم البياني';
                    showChartBtn.disabled = true;
                }
            });
        </script>
        <script>
            const agentName = @json($agent->name ?? '');
        </script>
        <script>
            $(document).ready(function() {

                function stripHtml(data) {
                    return $('<div>').html(data).text().trim();
                }

                function extractNumber(text) {
                    const match = text.match(/-?\d[\d,]*\.\d+|-?\d[\d,]*/);
                    return match ? match[0].replace(/,/g, '') : text;
                }

                $('#paymentsTable').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    pageLength: 20,
                    dom: 'Bfrtip',

                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel"></i> Excel',
                            className: 'btn btn-success btn-sm mb-3',
                            title: `تقرير دفعات ${agentName}`,

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4], // #, amount, date, notes, receipt
                                format: {
                                    body: function(data, row, column, node) {

                                        let text = stripHtml(data);

                                        // ✅ المبلغ: رقم فقط
                                        if (column === 1) {
                                            return extractNumber(text);
                                        }

                                        // ✅ الإيصال: HYPERLINK Formula
                                        if (column === 4) {
                                            let link = $(node).find('a').attr('href');
                                            if (link) {
                                                return `${link}`;
                                            }
                                            return '';
                                        }

                                        return text;
                                    }
                                }
                            },


                        },

                        {
                            extend: 'csvHtml5',
                            text: '<i class="fa fa-file-csv"></i> CSV',
                            className: 'btn btn-info btn-sm mb-3',
                            title: `تقرير دفعات ${agentName}`,
                            bom: true,

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4],
                                format: {
                                    body: function(data, row, column, node) {

                                        // 1️⃣ شيل أي HTML
                                        let text = stripHtml(data);

                                        // 2️⃣ وحّد الأسطر (شيل كسر السطر)
                                        text = text
                                            .replace(/\r?\n|\r/g, ' ') // يشيل new lines
                                            .replace(/\s+/g, ' ') // يشيل مسافات زيادة
                                            .trim(); // trim نهائي

                                        // ✅ المبلغ: رقم فقط
                                        if (column === 1) {
                                            return extractNumber(text);
                                        }

                                        // ✅ الإيصال في CSV: رابط فقط
                                        if (column === 4) {
                                            let link = $(node).find('a').attr('href');
                                            return link ? link.trim() : '';
                                        }

                                        // باقي الأعمدة: نص نظيف
                                        return text;
                                    }
                                }

                            }
                        }
                    ],

                    // ✅ منع ترتيب/بحث الإيصال + الإجراءات
                    columnDefs: [{
                        targets: [4, 5],
                        orderable: false,
                        searchable: false
                    }],

                    language: {
                        search: "بحث:",
                        lengthMenu: "عرض _MENU_ سجل",
                        info: "عرض _START_ إلى _END_ من أصل _TOTAL_ دفعة",
                        paginate: {
                            previous: "السابق",
                            next: "التالي"
                        }
                    }
                });

            });
        </script>
    @endpush
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    @endpush
@endsection
