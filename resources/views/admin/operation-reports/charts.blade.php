@extends('layouts.app')

@section('title', 'تحليلات تقارير العملاء - الربحية والأداء')
@push('styles')
    <style>
        :root {
            --main-green: #1fd18b;
            --main-blue: #378aff;
            --main-purple: #a285f7;
            --main-orange: #ffb967;
            --main-pink: #ff85a1;
            --main-yellow: #ffe66d;
            --main-cyan: #6beffd;
            --main-bg: #f3f6fa;
            --main-dark: #17223b;
            --main-light: #fff;
            --main-muted: #a7b0c2;
            --main-border: #e5e7eb;
            --radius: 1.25rem;
            --shadow: 0 8px 24px -8px rgba(47, 86, 233, 0.10);
            --shadow-lg: 0 14px 32px -10px rgba(47, 86, 233, 0.13);
            --stat-gradient: linear-gradient(120deg, var(--main-green) 60%, var(--main-blue) 100%);
            --card-gradient: linear-gradient(120deg, #fff 60%, #f1f3f9 100%);
        }

        .charts-container {
            background: var(--main-bg);
            min-height: 100vh;
            padding: 2.5rem 1.5rem;
        }

        .page-header {
            background: var(--stat-gradient);
            border-radius: var(--radius);
            padding: 2.2rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow);
            border: none;
            color: var(--main-light);
        }

        .page-title {
            color: var(--main-light);
            font-size: 2.35rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: 1.2px;
            text-shadow: 0 2px 8px rgba(50, 50, 80, 0.12);
        }

        .btn-back {
            background: linear-gradient(90deg, var(--main-blue) 50%, var(--main-green) 100%);
            color: white;
            padding: 0.85rem 2.1rem;
            border-radius: 0.95rem;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 8px -2px rgba(55, 138, 255, 0.14);
            font-size: 1.09rem;
            transition: background 0.2s, transform 0.18s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back:hover {
            background: linear-gradient(90deg, var(--main-green) 20%, var(--main-blue) 100%);
            color: #fff;
            transform: scale(1.045) translateY(-2px);
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2.4rem;
        }

        .stat-box {
            background: var(--stat-gradient);
            border-radius: var(--radius);
            padding: 2.1rem 1.5rem;
            box-shadow: var(--shadow);
            border: none;
            text-align: center;
            color: var(--main-light);
            font-weight: 700;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.9s cubic-bezier(.19, 1, .22, 1) both;
        }

        .stat-box:after {
            content: '';
            position: absolute;
            right: -30px;
            top: -30px;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--main-blue) 30%, var(--main-green) 100%);
            opacity: 0.09;
            border-radius: 50%;
            z-index: 0;
        }

        .stat-value {
            font-size: 2.55rem;
            font-weight: 900;
            color: var(--main-light);
            letter-spacing: 0.5px;
            text-shadow: 0 2px 10px rgba(47, 86, 233, 0.12);
            z-index: 2;
            position: relative;
        }

        .stat-label {
            color: var(--main-bg);
            font-size: 1.08rem;
            font-weight: 600;
            z-index: 2;
            position: relative;
            text-shadow: 0 2px 10px rgba(47, 86, 233, 0.09);
        }

        .chart-card {
            background: var(--card-gradient);
            border-radius: var(--radius);
            padding: 2rem 1.7rem 2rem 1.7rem;
            box-shadow: var(--shadow-lg);
            border: none;
            margin-bottom: 2.2rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.22s cubic-bezier(.19, 1, .22, 1), box-shadow 0.19s;
            will-change: transform, box-shadow;
            animation: fadeInUp 1.1s cubic-bezier(.19, 1, .22, 1) both;
        }

        .chart-card:hover {
            transform: translateY(-4px) scale(1.015);
            box-shadow: 0 24px 48px -10px rgba(47, 86, 233, 0.15);
        }

        .chart-title {
            font-size: 1.36rem;
            font-weight: 700;
            margin-bottom: 1.7rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: .2px;
            background: linear-gradient(90deg, var(--main-blue) 30%, var(--main-green) 70%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        .chart-container.small {
            height: 300px;
        }

        .chart-container.large {
            height: 500px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 768px) {
            .charts-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.2rem;
            }

            .page-title {
                font-size: 1.45rem;
            }

            .chart-container {
                height: 270px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="charts-container">
        <!-- رأس الصفحة -->
        <div class="page-header">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <h1 class="page-title">
                    <i class="fas fa-chart-bar fa-beat"></i>
                    تحليلات تقارير العملاء - الربحية والأداء
                </h1>
                <a href="{{ route('admin.operation-reports.index') }}" class="btn-back"
                    style="
    border: 1px solid;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
    transition: all 0.2s;
">
                    <i class="fas fa-arrow-left"></i>
                    العودة للتقارير
                </a>
            </div>
        </div>

        <!-- إحصائيات شاملة -->
        <div class="stats-summary">
            <div class="stat-box">
                <div class="stat-value">{{ $totalReports }}</div>
                <div class="stat-label">إجمالي التقارير</div>
            </div>
            <div class="stat-box" style="padding-bottom: 1.5rem;">
    <div class="stat-label mb-2">إجمالي الأرباح لكل عملة</div>
    <div style="display: flex; gap: 1.2rem;">
        <div class="currency-row currency-KWD">
            <span>{{ number_format($totalProfitByCurrency['KWD'] ?? 0, 2) }}</span>
            <span class="currency-symbol">د.ك</span>
        </div>
        <div class="currency-row currency-SAR">
            <span>{{ number_format($totalProfitByCurrency['SAR'] ?? 0, 2) }}</span>
            <span class="currency-symbol">ر.س</span>
        </div>
        <div class="currency-row currency-USD">
            <span>{{ number_format($totalProfitByCurrency['USD'] ?? 0, 2) }}</span>
            <span class="currency-symbol">$</span>
        </div>
        <div class="currency-row currency-EUR">
            <span>{{ number_format($totalProfitByCurrency['EUR'] ?? 0, 2) }}</span>
            <span class="currency-symbol">€</span>
        </div>
    </div>
</div>

            <div class="stat-box">
                <div class="stat-value">{{ number_format($avgProfitPerReport, 2) }}</div>
                <div class="stat-label">متوسط ربح التقرير</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $totalClients }}</div>
                <div class="stat-label">إجمالي العملاء</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $totalCompanies }}</div>
                <div class="stat-label">إجمالي الشركات</div>
            </div>
        </div>

        <!-- ✅ إضافة جديدة: إحصائيات الأرباح حسب العملة -->
        @if (!empty($profitsByCurrency))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-card">
                        <h3 class="chart-title">
                            <i class="fas fa-money-bill-wave text-success fa-beat"></i>
                            الأرباح المفصلة حسب العملة
                        </h3>
                        <div class="row">
                            @foreach ($profitsByCurrency as $currency => $total)
                                <div class="col-md-3 mb-3">
                                    <div class="stat-box"
                                        style="margin: 0; background: linear-gradient(120deg, #10b981 40%, #06b6d4 100%);">
                                        <div class="stat-value">{{ number_format($total, 2) }}</div>
                                        <div class="stat-label">{{ $currency }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- ✅ إضافة جديدة: جدول تفصيلي حسب النوع والعملة (إذا كان متوفر) -->
        @if (isset($profitsByTypeAndCurrency))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-card">
                        <h3 class="chart-title">
                            <i class="fas fa-table text-info fa-beat"></i>
                            تفصيل الأرباح حسب نوع العملية والعملة
                        </h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background: linear-gradient(120deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <tr>
                                        <th style="font-weight: 700; color: #495057;">نوع العملية</th>
                                        <th style="font-weight: 700; color: #495057; text-align: center;">KWD</th>
                                        <th style="font-weight: 700; color: #495057; text-align: center;">SAR</th>
                                        <th style="font-weight: 700; color: #495057; text-align: center;">USD</th>
                                        <th style="font-weight: 700; color: #495057; text-align: center;">EUR</th>
                                        <th style="font-weight: 700; color: #495057; text-align: center;">الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $typeNames = [
                                            'visa' => 'تأشيرات',
                                            'flight' => 'طيران',
                                            'transport' => 'نقل',
                                            'hotel' => 'فنادق',
                                            'land_trip' => 'رحلات برية',
                                        ];
                                    @endphp
                                    @foreach ($profitsByTypeAndCurrency as $type => $currencies)
                                        <tr style="transition: all 0.2s;">
                                            <td style="font-weight: 600; color: #374151;">
                                                <i
                                                    class="fas fa-{{ $type == 'visa'
                                                        ? 'passport'
                                                        : ($type == 'flight'
                                                            ? 'plane'
                                                            : ($type == 'transport'
                                                                ? 'bus'
                                                                : ($type == 'hotel'
                                                                    ? 'bed'
                                                                    : 'route'))) }}"></i>
                                                {{ $typeNames[$type] ?? $type }}
                                            </td>
                                            <td style="text-align: center; font-family: 'Courier New', monospace;">
                                                {{ number_format($currencies['KWD'] ?? 0, 2) }}
                                            </td>
                                            <td style="text-align: center; font-family: 'Courier New', monospace;">
                                                {{ number_format($currencies['SAR'] ?? 0, 2) }}
                                            </td>
                                            <td style="text-align: center; font-family: 'Courier New', monospace;">
                                                {{ number_format($currencies['USD'] ?? 0, 2) }}
                                            </td>
                                            <td style="text-align: center; font-family: 'Courier New', monospace;">
                                                {{ number_format($currencies['EUR'] ?? 0, 2) }}
                                            </td>
                                            <td
                                                style="text-align: center; font-weight: 700; color: #059669; font-family: 'Courier New', monospace;">
                                                {{ number_format(array_sum($currencies), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr
                                        style="background: linear-gradient(120deg, #f8f9fa 0%, #e9ecef 100%); font-weight: 700;">
                                        <td style="color: #374151;">المجموع الكلي</td>
                                        @php
                                            $totals = ['KWD' => 0, 'SAR' => 0, 'USD' => 0, 'EUR' => 0];
                                            foreach ($profitsByTypeAndCurrency as $currencies) {
                                                foreach ($totals as $currency => $total) {
                                                    $totals[$currency] += $currencies[$currency] ?? 0;
                                                }
                                            }
                                        @endphp
                                        @foreach ($totals as $currency => $total)
                                            <td
                                                style="text-align: center; color: #059669; font-family: 'Courier New', monospace;">
                                                {{ number_format($total, 2) }}
                                            </td>
                                        @endforeach
                                        <td
                                            style="text-align: center; color: #dc2626; font-weight: 900; font-family: 'Courier New', monospace;">
                                            {{ number_format(array_sum($totals), 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <!-- الرسوم البيانية -->
        <div class="row">
            <!-- 1. توزيع الأرباح حسب نوع العملية -->
            <div class="col-lg-6 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-pie text-primary fa-fade"></i>
                        توزيع الأرباح حسب نوع العملية
                    </h3>
                    <div class="chart-container">
                        <canvas id="profitsByTypeChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- 2. توزيع التقارير حسب فئات الربح -->
            <div class="col-lg-6 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-donut text-warning fa-bounce"></i>
                        توزيع التقارير حسب فئات الربح
                    </h3>
                    <div class="chart-container">
                        <canvas id="profitRangesChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- 3. أعلى العملاء حسب إجمالي الأرباح -->
            <div class="col-12 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-users text-success fa-beat"></i>
                        أعلى 10 عملاء حسب إجمالي الأرباح
                    </h3>
                    <div class="chart-container large">
                        <canvas id="topClientsChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- 4. العملاء الأكثر نشاطاً -->
            <div class="col-lg-6 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-bar text-info fa-beat"></i>
                        العملاء الأكثر نشاطاً (عدد التقارير)
                    </h3>
                    <div class="chart-container">
                        <canvas id="mostActiveClientsChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- 5. متوسط الربح لكل نوع عملية -->
            <div class="col-lg-6 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-line text-purple fa-beat-fade"></i>
                        متوسط الربح لكل نوع عملية
                    </h3>
                    <div class="chart-container">
                        <canvas id="avgProfitByTypeChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- 6. التقارير والأرباح عبر الزمن -->
            <div class="col-12 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-chart-area text-danger fa-bounce"></i>
                        التقارير والأرباح عبر الزمن (آخر 30 يوم)
                    </h3>
                    <div class="chart-container large">
                        <canvas id="reportsOverTimeChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- 7. أعلى الشركات حسب الأرباح -->
            <div class="col-lg-6 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-building text-warning fa-fade"></i>
                        أعلى الشركات حسب الأرباح
                    </h3>
                    <div class="chart-container">
                        <canvas id="topCompaniesChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- 8. توزيع الأرباح حسب العملة -->
            <div class="col-lg-6 mb-4">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-coins text-success fa-bounce"></i>
                        توزيع الأرباح حسب العملة
                    </h3>
                    <div class="chart-container">
                        <canvas id="profitsByCurrencyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // إعدادات عامة لـ Chart.js
            Chart.defaults.font.family = "'Tajawal', 'Arial', sans-serif";
            Chart.defaults.color = '#6b7280';
            Chart.defaults.locale = 'ar';

            // البيانات من الخادم
            const profitsByType = @json($profitsByType ?? []);
            const reportsOverTime = @json($reportsOverTime ?? []);
            const topClients = @json($topClients ?? []);
            const topCompanies = @json($topCompanies ?? []);
            const profitRanges = @json($profitRanges ?? []);
            const avgProfitByType = @json($avgProfitByType ?? []);
            const mostActiveClients = @json($mostActiveClients ?? []);
            const profitsByCurrency = @json($profitsByCurrency ?? []);
            const statusDistribution = @json($statusDistribution ?? []);
            const totalReports = @json($totalReports ?? 0);
            const totalProfitByCurrency = @json($totalProfitByCurrency ?? []);

            console.log('📊 البيانات المحملة من الخادم:', {
                profitsByType,
                reportsOverTime,
                topClients,
                profitRanges,
                profitsByCurrency,
                totalReports
            });

            // مجموعة الألوان
            const colors = {
                primary: '#3b82f6',
                success: '#10b981',
                warning: '#f59e0b',
                danger: '#ef4444',
                info: '#06b6d4',
                purple: '#8b5cf6',
                orange: '#f97316',
                pink: '#ec4899',
                indigo: '#6366f1',
                teal: '#14b8a6'
            };

            // دالة مساعدة لتنسيق الأرقام
            function formatNumber(num) {
                return new Intl.NumberFormat('ar-SA', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                }).format(num);
            }

            // دالة مساعدة لتنسيق التواريخ
            function formatDate(dateString) {
                return new Date(dateString).toLocaleDateString('ar-SA', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            // 1. رسم توزيع الأرباح حسب نوع العملية
            const profitsByTypeCanvas = document.getElementById('profitsByTypeChart');
            if (profitsByTypeCanvas && profitsByType) {
                const typeLabels = ['تأشيرات', 'طيران', 'نقل', 'فنادق', 'رحلات برية'];
                const typeData = [
                    profitsByType.visa || 0,
                    profitsByType.flight || 0,
                    profitsByType.transport || 0,
                    profitsByType.hotel || 0,
                    profitsByType.land_trip || 0
                ];

                const totalAmount = typeData.reduce((a, b) => a + b, 0);

                if (totalAmount > 0) {
                    new Chart(profitsByTypeCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: typeLabels,
                            datasets: [{
                                data: typeData,
                                backgroundColor: [
                                    colors.primary,
                                    colors.success,
                                    colors.warning,
                                    colors.danger,
                                    colors.purple
                                ],
                                borderWidth: 3,
                                borderColor: '#ffffff',
                                hoverBorderWidth: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            size: 12
                                        },
                                        generateLabels: function(chart) {
                                            const data = chart.data;
                                            if (data.labels.length && data.datasets.length) {
                                                return data.labels.map((label, i) => {
                                                    const value = data.datasets[0].data[i];
                                                    const percentage = ((value / totalAmount) *
                                                        100).toFixed(1);
                                                    return {
                                                        text: `${label}: ${formatNumber(value)} (${percentage}%)`,
                                                        fillStyle: data.datasets[0]
                                                            .backgroundColor[i],
                                                        hidden: false,
                                                        index: i
                                                    };
                                                });
                                            }
                                            return [];
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed;
                                            const percentage = ((value / totalAmount) * 100).toFixed(1);
                                            return `${context.label}: ${formatNumber(value)} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    profitsByTypeCanvas.parentElement.innerHTML =
                        '<div class="text-center text-muted p-4"><i class="fas fa-chart-pie fa-3x mb-3"></i><br>لا توجد بيانات أرباح متاحة</div>';
                }
            }

            // 2. رسم فئات الربح
            const profitRangesCanvas = document.getElementById('profitRangesChart');
            if (profitRangesCanvas && profitRanges && Object.values(profitRanges).some(v => v > 0)) {
                new Chart(profitRangesCanvas, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(profitRanges),
                        datasets: [{
                            data: Object.values(profitRanges),
                            backgroundColor: [colors.success, colors.info, colors.warning, colors
                                .danger
                            ],
                            borderWidth: 3,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.label}: ${context.parsed} تقرير`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (profitRangesCanvas) {
                profitRangesCanvas.parentElement.innerHTML =
                    '<div class="text-center text-muted p-4"><i class="fas fa-chart-bar fa-3x mb-3"></i><br>لا توجد بيانات فئات الربح</div>';
            }

            // 3. أعلى العملاء
            const topClientsCanvas = document.getElementById('topClientsChart');
            if (topClientsCanvas && topClients && topClients.length > 0) {
                new Chart(topClientsCanvas, {
                    type: 'bar',
                    data: {
                        labels: topClients.map(client => client.client_name),
                        datasets: [{
                            label: 'إجمالي الأرباح',
                            data: topClients.map(client => client.total_profit),
                            backgroundColor: colors.success,
                            borderRadius: 6,
                            borderSkipped: false,
                            yAxisID: 'y'
                        }, {
                            label: 'عدد التقارير',
                            data: topClients.map(client => client.reports_count),
                            backgroundColor: colors.info,
                            borderRadius: 6,
                            borderSkipped: false,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'الأرباح'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return formatNumber(value);
                                    }
                                }
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'عدد التقارير'
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.datasetIndex === 0) {
                                            return `${context.dataset.label}: ${formatNumber(context.parsed.y)}`;
                                        } else {
                                            return `${context.dataset.label}: ${context.parsed.y}`;
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (topClientsCanvas) {
                topClientsCanvas.parentElement.innerHTML =
                    '<div class="text-center text-muted p-4"><i class="fas fa-users fa-3x mb-3"></i><br>لا توجد بيانات عملاء</div>';
            }

            // 4. العملاء الأكثر نشاطاً
            const mostActiveClientsCanvas = document.getElementById('mostActiveClientsChart');
            if (mostActiveClientsCanvas && mostActiveClients && mostActiveClients.length > 0) {
                new Chart(mostActiveClientsCanvas, {
                    type: 'bar',
                    data: {
                        labels: mostActiveClients.map(client => client.client_name),
                        datasets: [{
                            label: 'عدد التقارير',
                            data: mostActiveClients.map(client => client.reports_count),
                            backgroundColor: colors.info,
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.parsed.x} تقرير`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (mostActiveClientsCanvas) {
                mostActiveClientsCanvas.parentElement.innerHTML =
                    '<div class="text-center text-muted p-4"><i class="fas fa-user-check fa-3x mb-3"></i><br>لا توجد بيانات نشاط العملاء</div>';
            }

            // 5. متوسط الربح لكل نوع عملية
            const avgProfitByTypeCanvas = document.getElementById('avgProfitByTypeChart');
            if (avgProfitByTypeCanvas && avgProfitByType) {
                const avgLabels = ['تأشيرات', 'طيران', 'نقل', 'فنادق', 'رحلات برية'];
                const avgData = [
                    avgProfitByType.visa || 0,
                    avgProfitByType.flight || 0,
                    avgProfitByType.transport || 0,
                    avgProfitByType.hotel || 0,
                    avgProfitByType.land_trip || 0
                ];

                if (avgData.some(v => v > 0)) {
                    new Chart(avgProfitByTypeCanvas, {
                        type: 'radar',
                        data: {
                            labels: avgLabels,
                            datasets: [{
                                label: 'متوسط الربح',
                                data: avgData,
                                backgroundColor: colors.purple + '20',
                                borderColor: colors.purple,
                                borderWidth: 3,
                                pointBackgroundColor: colors.purple,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                r: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return formatNumber(value);
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.dataset.label}: ${formatNumber(context.parsed.r)}`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    avgProfitByTypeCanvas.parentElement.innerHTML =
                        '<div class="text-center text-muted p-4"><i class="fas fa-chart-line fa-3x mb-3"></i><br>لا توجد بيانات متوسط الأرباح</div>';
                }
            }

            // 6. التقارير عبر الزمن
            const reportsOverTimeCanvas = document.getElementById('reportsOverTimeChart');
            if (reportsOverTimeCanvas && reportsOverTime && reportsOverTime.length > 0) {
                new Chart(reportsOverTimeCanvas, {
                    type: 'line',
                    data: {
                        labels: reportsOverTime.map(item => formatDate(item.date)),
                        datasets: [{
                            label: 'عدد التقارير',
                            data: reportsOverTime.map(item => item.reports_count),
                            borderColor: colors.primary,
                            backgroundColor: colors.primary + '20',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: colors.primary,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.parsed.y} تقرير`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (reportsOverTimeCanvas) {
                reportsOverTimeCanvas.parentElement.innerHTML =
                    '<div class="text-center text-muted p-4"><i class="fas fa-calendar-alt fa-3x mb-3"></i><br>لا توجد بيانات زمنية</div>';
            }

            // 7. أعلى الشركات
            const topCompaniesCanvas = document.getElementById('topCompaniesChart');
            if (topCompaniesCanvas && topCompanies && topCompanies.length > 0) {
                new Chart(topCompaniesCanvas, {
                    type: 'bar',
                    data: {
                        labels: topCompanies.map(company => company.company_name),
                        datasets: [{
                            label: 'إجمالي الأرباح',
                            data: topCompanies.map(company => company.total_profit),
                            backgroundColor: colors.warning,
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return formatNumber(value);
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${formatNumber(context.parsed.x)}`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (topCompaniesCanvas) {
                topCompaniesCanvas.parentElement.innerHTML =
                    '<div class="text-center text-muted p-4"><i class="fas fa-building fa-3x mb-3"></i><br>لا توجد بيانات شركات</div>';
            }

            // 8. الأرباح حسب العملة
            const profitsByCurrencyCanvas = document.getElementById('profitsByCurrencyChart');
            if (profitsByCurrencyCanvas && profitsByCurrency && Object.keys(profitsByCurrency).length > 0) {
                // تحويل رموز العملات إلى أسماء
                const currencyNames = {
                    'KWD': 'دينار كويتي',
                    'SAR': 'ريال سعودي',
                    'USD': 'دولار أمريكي',
                    'EUR': 'يورو'
                };

                const currencyLabels = Object.keys(profitsByCurrency).map(currency =>
                    currencyNames[currency] || currency
                );

                new Chart(profitsByCurrencyCanvas, {
                    type: 'bar',
                    data: {
                        labels: currencyLabels,
                        datasets: [{
                            label: 'الأرباح',
                            data: Object.values(profitsByCurrency),
                            backgroundColor: [colors.primary, colors.success, colors.warning, colors
                                .info, colors.purple
                            ],
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return formatNumber(value);
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${formatNumber(context.parsed.y)}`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (profitsByCurrencyCanvas) {
                profitsByCurrencyCanvas.parentElement.innerHTML =
                    '<div class="text-center text-muted p-4"><i class="fas fa-coins fa-3x mb-3"></i><br>لا توجد بيانات عملات</div>';
            }

            // 9. توزيع حالات التقارير (إضافي)
            const statusDistributionCanvas = document.getElementById('statusDistributionChart');
            if (statusDistributionCanvas && statusDistribution && Object.keys(statusDistribution).length > 0) {
                const statusNames = {
                    'completed': 'مكتملة',
                    'draft': 'مسودة',
                    'pending': 'معلقة',
                    'cancelled': 'ملغية'
                };

                const statusLabels = Object.keys(statusDistribution).map(status =>
                    statusNames[status] || status
                );

                new Chart(statusDistributionCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: Object.values(statusDistribution),
                            backgroundColor: [colors.success, colors.warning, colors.info, colors
                                .danger
                            ],
                            borderWidth: 3,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = Object.values(statusDistribution).reduce((a, b) =>
                                            a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return `${context.label}: ${context.parsed} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else if (statusDistributionCanvas) {
                statusDistributionCanvas.parentElement.innerHTML =
                    '<div class="text-center text-muted p-4"><i class="fas fa-tasks fa-3x mb-3"></i><br>لا توجد بيانات حالات</div>';
            }

            // رسالة نجاح التحميل
            console.log('✅ تم تحميل جميع الرسوم البيانية بنجاح');

            // إظهار رسالة للمستخدم
            if (totalReports > 0) {
                console.log(`📈 تم عرض تحليلات ${totalReports} تقرير`);
            } else {
                console.log('📊 يتم عرض بيانات تجريبية - لا توجد تقارير في قاعدة البيانات');
            }
        });
    </script>
                <script src="{{ asset('js/preventClick.js') }}"></script>

@endpush
