@extends('layouts.app')

{{-- *** تأكد من وجود رابط Font Awesome في layout الرئيسي layouts/app.blade.php *** --}}
{{-- مثال: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> --}}

@section('content')
    <div class="container">
        <h1>سجل المدفوعات - {{ $agent->name }}</h1>
                {{-- ✅ إضافة قسم ملخص الحسابات الحالية --}}
        <div class="card mb-4" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 1px solid #dee2e6; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div class="card-header" style="background: linear-gradient(120deg, #10b981 60%, #059669 100%); color: white; border-radius: 12px 12px 0 0;">
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
                    $dueByCurrency = $agent->computed_total_due_by_currency ?? 
                                    ($agent->total_due_by_currency ?? ['SAR' => $agent->total_due ?? 0]);
                    
                    // المدفوع والخصومات حسب العملة
                    $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
                    $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];
                    
                    // إذا لم تكن محسوبة، احسبها من المدفوعات
                    if (empty($paidByCurrency) && $agent->payments) {
                        $agentPaymentsGrouped = $agent->payments->groupBy('currency');
                        
                        foreach ($agentPaymentsGrouped as $currency => $paymentsForCurrency) {
                            $paidByCurrency[$currency] = $paymentsForCurrency->where('amount', '>=', 0)->sum('amount');
                            $discountsByCurrency[$currency] = abs($paymentsForCurrency->where('amount', '<', 0)->sum('amount'));
                        }
                    }
                    
                    // المتبقي حسب العملة
                    $remainingByCurrency = $agent->computed_remaining_by_currency ?? 
                                          ($agent->remaining_by_currency ?? ['SAR' => $agent->remaining_amount ?? 0]);
                @endphp

                <div class="row g-3">
                    {{-- عدد الحجوزات --}}
                    <div class="col-md-3">
                        <div class="text-center p-3" style="background: linear-gradient(135deg, #3b82f6, #60a5fa); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-calendar-check" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $totalBookings }}</h4>
                            <small class="opacity-90">عدد الحجوزات</small>
                        </div>
                    </div>

                    {{-- إجمالي المستحق --}}
                    <div class="col-md-3">
                        <div class="text-center p-3" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); border-radius: 10px; color: white;">
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
                        <div class="text-center p-3" style="background: linear-gradient(135deg, #10b981, #34d399); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas fa-hand-holding-usd" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                @php $hasPaidAmount = false; @endphp
                                @foreach (['SAR', 'KWD'] as $currency)
                                    @if (($paidByCurrency[$currency] ?? 0) > 0 || ($discountsByCurrency[$currency] ?? 0) > 0)
                                        @php $hasPaidAmount = true; @endphp
                                        <div class="mb-1">
                                            <h6 class="mb-0">{{ number_format($paidByCurrency[$currency] ?? 0, 2) }}</h6>
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
                                $isOverpaid = collect($remainingByCurrency)->some(function($amount) {
                                    return $amount < 0;
                                });
                            }
                        @endphp
                        <div class="text-center p-3" style="background: linear-gradient(135deg, {{ $isOverpaid ? '#ef4444, #f87171' : ($hasRemaining ? '#ef4444, #f87171' : '#6b7280, #9ca3af') }}); border-radius: 10px; color: white;">
                            <div class="mb-2">
                                <i class="fas {{ $isOverpaid ? 'fa-arrow-down' : ($hasRemaining ? 'fa-exclamation-triangle' : 'fa-check-circle') }}" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                @if ($hasRemaining)
                                    @foreach ($remainingByCurrency as $currency => $amount)
                                        @if ($amount != 0)
                                            <div class="mb-1">
                                                <h5 class="mb-0">{{ $amount > 0 ? '+' : '' }}{{ number_format($amount, 2) }}</h5>
                                                <small class="opacity-90">{{ $currency === 'SAR' ? 'ريال' : 'دينار' }}</small>
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
        <div id="chartContainer" class="mb-4 shadow-sm" style="display: none; position: relative; height:300px; width:100%;">
            <canvas id="paymentsChart"></canvas>
        </div>
        {{-- *** نهاية إضافة زرار ومكان الرسم البياني *** --}}


        <table class="table table-bordered">
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
    @endpush
@endsection
