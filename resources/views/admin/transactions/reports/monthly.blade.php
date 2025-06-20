@extends('layouts.app')

@section('title', 'التقرير الشهري للمعاملات المالية')

@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
    </script>
    <style>
        .report-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .report-card:hover {
            transform: translateY(-5px);
        }

        .report-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .metric-card {
            background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .metric-card.positive {
            border-left-color: #28a745;
        }

        .metric-card.negative {
            border-left-color: #dc3545;
        }

        .metric-card.neutral {
            border-left-color: #6c757d;
        }

        .metric-card.info {
            border-left-color: #17a2b8;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin: 2rem 0;
        }

        .trend-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .trend-up {
            background: #d4edda;
            color: #155724;
        }

        .trend-down {
            background: #f8d7da;
            color: #721c24;
        }

        .trend-stable {
            background: #e2e3e5;
            color: #383d41;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .day-analysis {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            border: 1px solid #e9ecef;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .day-analysis:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }

        .currency-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4 no-print">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.transactions.index') }}">المعاملات المالية</a>
                </li>
                <li class="breadcrumb-item active">التقرير الشهري</li>
            </ol>
        </nav>

        <!-- Header Section -->
        <div class="card report-card">
            <div class="card-header report-header py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">
                            <i class="fas fa-chart-line me-2"></i>
                            التقرير الشهري للمعاملات المالية
                        </h3>
                        <p class="mb-0 opacity-75">
                            {{ $startDate->format('F Y') }} - تحليل مفصل ودقيق
                        </p>
                    </div>
                    <div class="d-flex gap-2 no-print">
                        <button class="btn btn-light" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> طباعة
                        </button>
                        <button class="btn btn-outline-light" onclick="exportReport()">
                            <i class="fas fa-download me-1"></i> تصدير
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Date Filter -->
                <div class="row mb-4 no-print">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('admin.transactions.reports.monthly') }}">
                            <div class="input-group">
                                <input type="month" name="month" class="form-control" value="{{ $month }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> عرض التقرير
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="showPreviousMonth()">
                                <i class="fas fa-chevron-left me-1"></i> الشهر السابق
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="showNextMonth()">
                                الشهر التالي <i class="fas fa-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics Overview -->
                <div class="stats-grid">
                    <div class="metric-card positive">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-1">إجمالي الإيداعات</h6>
                                <h3 class="text-success mb-2">{{ number_format($totalDeposits, 2) }}</h3>
                                @if (isset($comparison['previous_deposits']))
                                    @php $change = $totalDeposits - $comparison['previous_deposits']; @endphp
                                    <small class="trend-indicator {{ $change >= 0 ? 'trend-up' : 'trend-down' }}">
                                        <i class="fas fa-{{ $change >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }} من الشهر السابق
                                    </small>
                                @endif
                            </div>
                            <div class="text-success">
                                <i class="fas fa-arrow-up fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card negative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-1">إجمالي السحوبات</h6>
                                <h3 class="text-danger mb-2">{{ number_format($totalWithdrawals, 2) }}</h3>
                                @if (isset($comparison['previous_withdrawals']))
                                    @php $change = $totalWithdrawals - $comparison['previous_withdrawals']; @endphp
                                    <small class="trend-indicator {{ $change <= 0 ? 'trend-up' : 'trend-down' }}">
                                        <i class="fas fa-{{ $change <= 0 ? 'arrow-down' : 'arrow-up' }} me-1"></i>
                                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }} من الشهر السابق
                                    </small>
                                @endif
                            </div>
                            <div class="text-danger">
                                <i class="fas fa-arrow-down fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card {{ $netBalance >= 0 ? 'positive' : 'negative' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-1">صافي الرصيد</h6>
                                <h3 class="{{ $netBalance >= 0 ? 'text-success' : 'text-danger' }} mb-2">
                                    {{ $netBalance >= 0 ? '+' : '' }}{{ number_format($netBalance, 2) }}
                                </h3>
                                <div
                                    class="trend-indicator {{ $trends['trend'] === 'up' ? 'trend-up' : ($trends['trend'] === 'down' ? 'trend-down' : 'trend-stable') }}">
                                    <i
                                        class="fas fa-{{ $trends['trend'] === 'up' ? 'trending-up' : ($trends['trend'] === 'down' ? 'trending-down' : 'minus') }} me-1"></i>
                                    اتجاه
                                    {{ $trends['trend'] === 'up' ? 'صاعد' : ($trends['trend'] === 'down' ? 'هابط' : 'مستقر') }}
                                    @if ($trends['change_percent'] != 0)
                                        ({{ $trends['change_percent'] > 0 ? '+' : '' }}{{ $trends['change_percent'] }}%)
                                    @endif
                                </div>
                            </div>
                            <div class="text-{{ $netBalance >= 0 ? 'success' : 'danger' }}">
                                <i class="fas fa-balance-scale fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-1">إجمالي المعاملات</h6>
                                <h3 class="text-info mb-2">{{ $totalTransactions }}</h3>
                                <small class="text-muted">
                                    متوسط {{ number_format($keyMetrics['average_transaction'], 2) }} لكل معاملة
                                </small>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Trend Chart -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line text-primary me-2"></i>
                                    اتجاه الرصيد اليومي
                                </h5>
                                @if (count($dailyStats) > 0)
                                    <div class="btn-group btn-group-sm no-print" role="group">
                                        <button type="button" class="btn btn-outline-primary active"
                                            onclick="showChart('balance')">الرصيد</button>
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="showChart('transactions')">المعاملات</button>
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="showChart('comparison')">مقارنة</button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                @if (count($dailyStats) > 0)
                                    <div class="chart-container">
                                        <canvas id="balanceChart"></canvas>
                                    </div>
                                @else
                                    <div class="text-center p-4">
                                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد بيانات يومية لعرضها في هذا الشهر</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Distribution Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-coins text-warning me-2"></i>
                                    توزيع المعاملات حسب العملة
                                </h5>
                            </div>
                            <div class="card-body">
                                @if (count($currencyStats) > 0)
                                    <canvas id="currencyChart" height="300"></canvas>
                                @else
                                    <div class="text-center p-4">
                                        <i class="fas fa-coins fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد بيانات عملات متعددة</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-tags text-info me-2"></i>
                                    التوزيع حسب التصنيف
                                </h5>
                            </div>
                            <div class="card-body">
                                @if (count($categoryStats) > 0)
                                    <canvas id="categoryChart" height="300"></canvas>
                                @else
                                    <div class="text-center p-4">
                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد بيانات تصنيفات</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Analysis -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-week text-success me-2"></i>
                                    التحليل الأسبوعي
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="weeklyChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Daily Analysis -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day text-primary me-2"></i>
                                    التحليل اليومي التفصيلي
                                </h5>
                                <div class="no-print">
                                    <button class="btn btn-sm btn-outline-primary" onclick="toggleDayDetails()">
                                        <i class="fas fa-eye me-1"></i> إظهار/إخفاء التفاصيل
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="dailyAnalysis" style="display: block;">
                                <div class="row">
                                    @foreach ($dailyStats as $day)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="day-analysis">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">{{ $day['day_arabic'] }}</h6>
                                                    <small
                                                        class="text-muted">{{ Carbon\Carbon::parse($day['date'])->format('d/m') }}</small>
                                                </div>
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <small class="text-success d-block">إيداعات</small>
                                                        <strong
                                                            class="text-success">{{ number_format($day['deposits'], 0) }}</strong>
                                                    </div>
                                                    <div class="col-4">
                                                        <small class="text-danger d-block">سحوبات</small>
                                                        <strong
                                                            class="text-danger">{{ number_format($day['withdrawals'], 0) }}</strong>
                                                    </div>
                                                    <div class="col-4">
                                                        <small class="text-info d-block">صافي</small>
                                                        <strong
                                                            class="text-{{ $day['net'] >= 0 ? 'success' : 'danger' }}">
                                                            {{ $day['net'] >= 0 ? '+' : '' }}{{ number_format($day['net'], 0) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        الرصيد التراكمي: <span
                                                            class="fw-bold">{{ number_format($day['running_balance'], 2) }}</span>
                                                    </small>
                                                </div>
                                                @if ($day['transaction_count'] > 0)
                                                    <div class="mt-1">
                                                        <span class="badge bg-primary">{{ $day['transaction_count'] }}
                                                            معاملة</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Insights -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    نقاط رئيسية ومؤشرات مهمة
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary">الأداء المالي:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success me-2"></i>
                                                أكبر إيداع:
                                                <strong>{{ number_format($keyMetrics['largest_deposit'], 2) }}</strong>
                                            </li>
                                            <li><i class="fas fa-check text-success me-2"></i>
                                                أكبر سحب:
                                                <strong>{{ number_format($keyMetrics['largest_withdrawal'], 2) }}</strong>
                                            </li>
                                            <li><i class="fas fa-check text-success me-2"></i>
                                                متوسط المعاملة:
                                                <strong>{{ number_format($keyMetrics['average_transaction'], 2) }}</strong>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-info">التحليل الزمني:</h6>
                                        <ul class="list-unstyled">
                                            @if ($keyMetrics['most_active_day'])
                                                <li><i class="fas fa-star text-warning me-2"></i>
                                                    أكثر يوم نشاطاً:
                                                    <strong>{{ $keyMetrics['most_active_day']['day_arabic'] }}</strong>
                                                    ({{ $keyMetrics['most_active_day']['transaction_count'] }} معاملة)
                                                </li>
                                            @endif
                                            @if ($keyMetrics['best_balance_day'])
                                                <li><i class="fas fa-arrow-up text-success me-2"></i>
                                                    أفضل رصيد:
                                                    <strong>{{ Carbon\Carbon::parse($keyMetrics['best_balance_day']['date'])->format('d/m') }}</strong>
                                                    ({{ number_format($keyMetrics['best_balance_day']['running_balance'], 2) }})
                                                </li>
                                            @endif
                                            <li><i class="fas fa-chart-line text-info me-2"></i>
                                                التقلبات: <strong>{{ $trends['volatility'] }}</strong>
                                                ({{ $trends['volatility'] < 1000 ? 'منخفضة' : ($trends['volatility'] < 5000 ? 'متوسطة' : 'عالية') }})
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Currency Breakdown -->
                @if (count($currencyStats) > 1)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-globe text-primary me-2"></i>
                                        تفصيل العملات
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="currency-breakdown">
                                        @foreach ($currencyStats as $currency)
                                            <div
                                                class="metric-card {{ $currency['net'] >= 0 ? 'positive' : 'negative' }}">
                                                <h6 class="text-muted">{{ $currency['currency'] }}
                                                    {{ $currency['symbol'] }}</h6>
                                                <div class="row text-center">
                                                    <div class="col-3">
                                                        <small class="text-success d-block">إيداعات</small>
                                                        <strong
                                                            class="text-success">{{ number_format($currency['deposits'], 2) }}</strong>
                                                    </div>
                                                    <div class="col-3">
                                                        <small class="text-danger d-block">سحوبات</small>
                                                        <strong
                                                            class="text-danger">{{ number_format($currency['withdrawals'], 2) }}</strong>
                                                    </div>
                                                    <div class="col-3">
                                                        <small class="text-info d-block">تحويلات</small>
                                                        <strong
                                                            class="text-info">{{ number_format($currency['transfers'], 2) }}</strong>
                                                    </div>
                                                    <div class="col-3">
                                                        <small class="text-primary d-block">صافي</small>
                                                        <strong
                                                            class="text-{{ $currency['net'] >= 0 ? 'success' : 'danger' }}">
                                                            {{ $currency['net'] >= 0 ? '+' : '' }}{{ number_format($currency['net'], 2) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <span class="badge bg-secondary">{{ $currency['count'] }}
                                                        معاملة</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
/* =====================================================
   بداية السكريبت الرئيسي للرسوم البيانية والتقارير
   ===================================================== */

console.log('🚀 بدء تحميل سكريبت التقرير الشهري...');

try {
    /* =====================================================
       تحميل وإعداد البيانات من PHP إلى JavaScript
       ===================================================== */
    
    // تحويل البيانات من PHP إلى JavaScript مع معالجة القيم الفارغة
    const dailyData = @json($dailyStats ?? []);          // البيانات اليومية
    const weeklyData = @json($weeklyStats ?? []);        // البيانات الأسبوعية  
    const currencyData = @json($currencyStats ?? []);    // بيانات العملات
    const categoryData = @json($categoryStats ?? []);    // بيانات التصنيفات
    const trends = @json($trends ?? []);                 // بيانات الاتجاهات

    // طباعة معلومات تشخيصية للتحقق من البيانات
    console.log('📊 تم تحميل البيانات بنجاح:', {
        dailyDataCount: dailyData ? dailyData.length : 0,
        currencyDataCount: currencyData ? Object.keys(currencyData).length : 0,
        categoryDataCount: categoryData ? Object.keys(categoryData).length : 0,
        weeklyDataCount: weeklyData ? weeklyData.length : 0
    });

    /* =====================================================
       متغيرات الرسوم البيانية العامة
       ===================================================== */
    
    // تعريف متغيرات لحفظ مراجع الرسوم البيانية
    let balanceChart, currencyChart, categoryChart, weeklyChart;

    /* =====================================================
       تحميل الصفحة وتهيئة الرسوم البيانية
       ===================================================== */
    
    // انتظار تحميل DOM بالكامل قبل تهيئة الرسوم البيانية
    $(document).ready(function() {
        try {
            console.log('📱 DOM جاهز، بدء تهيئة الرسوم البيانية...');
            initializeCharts();
        } catch (error) {
            console.error('❌ خطأ في تحميل الصفحة:', error);
        }
    });

    /* =====================================================
       دالة التهيئة الرئيسية للرسوم البيانية
       ===================================================== */
    
    function initializeCharts() {
        try {
            console.log('🔧 بدء تهيئة جميع الرسوم البيانية...');

            // تهيئة رسم الرصيد اليومي مع معالجة الأخطاء
            try {
                initializeBalanceChart();
                console.log('✅ تم تهيئة رسم الرصيد اليومي بنجاح');
            } catch (error) {
                console.error('❌ خطأ في تهيئة رسم الرصيد:', error);
            }

            // تهيئة رسم العملات مع معالجة الأخطاء
            try {
                initializeCurrencyChart();
                console.log('✅ تم تهيئة رسم العملات بنجاح');
            } catch (error) {
                console.error('❌ خطأ في تهيئة رسم العملات:', error);
            }

            // تهيئة رسم التصنيفات مع معالجة الأخطاء
            try {
                initializeCategoryChart();
                console.log('✅ تم تهيئة رسم التصنيفات بنجاح');
            } catch (error) {
                console.error('❌ خطأ في تهيئة رسم التصنيفات:', error);
            }

            // تهيئة الرسم الأسبوعي مع معالجة الأخطاء
            try {
                initializeWeeklyChart();
                console.log('✅ تم تهيئة الرسم الأسبوعي بنجاح');
            } catch (error) {
                console.error('❌ خطأ في تهيئة الرسم الأسبوعي:', error);
            }

        } catch (error) {
            console.error('❌ خطأ عام في تهيئة الرسوم البيانية:', error);
        }
    }

    /* =====================================================
       رسم اتجاه الرصيد اليومي (Balance Chart)
       ===================================================== */
    
    function initializeBalanceChart() {
        try {
            console.log('📈 بدء تهيئة رسم الرصيد اليومي...');

            // العثور على عنصر Canvas في DOM
            const balanceCtx = document.getElementById('balanceChart');
            if (!balanceCtx) {
                console.error('❌ لم يتم العثور على عنصر balanceChart');
                return;
            }

            // التحقق من وجود البيانات اليومية
            if (!dailyData || dailyData.length === 0) {
                console.warn('⚠️ لا توجد بيانات يومية متاحة');
                const parentElement = balanceCtx.closest('.chart-container');
                if (parentElement) {
                    parentElement.innerHTML = 
                        '<div class="text-center p-4"><p class="text-muted">لا توجد بيانات يومية</p></div>';
                }
                return;
            }

            // طباعة عينة من البيانات للتشخيص
            console.log('📊 عينة من البيانات اليومية:', dailyData.slice(0, 3));

            // استخراج وتحضير البيانات للرسم البياني
            const dates = dailyData.map(d => d.date);                                    // التواريخ
            const balances = dailyData.map(d => parseFloat(d.running_balance) || 0);     // الأرصدة التراكمية
            const deposits = dailyData.map(d => parseFloat(d.deposits) || 0);           // الإيداعات اليومية
            const withdrawals = dailyData.map(d => parseFloat(d.withdrawals) || 0);     // السحوبات اليومية

            // طباعة إحصائيات البيانات المحضرة
            console.log('📋 إحصائيات البيانات المحضرة:', {
                datesCount: dates.length,
                balancesSum: balances.reduce((a, b) => a + b, 0),
                depositsSum: deposits.reduce((a, b) => a + b, 0),
                withdrawalsSum: withdrawals.reduce((a, b) => a + b, 0)
            });

            // إنشاء الرسم البياني باستخدام Chart.js
            balanceChart = new Chart(balanceCtx, {
                type: 'line',  // نوع الرسم: خطي
                data: {
                    labels: dates,  // التسميات على المحور السيني (التواريخ)
                    datasets: [{
                        // خط الرصيد التراكمي (مرئي افتراضياً)
                        label: 'الرصيد التراكمي',
                        data: balances,
                        borderColor: '#28a745',                        // لون الخط (أخضر)
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',    // لون التعبئة (أخضر شفاف)
                        fill: true,                                    // تعبئة المنطقة تحت الخط
                        tension: 0.4,                                  // انحناء الخط
                        pointBackgroundColor: '#28a745',               // لون النقاط
                        pointBorderColor: '#ffffff',                   // لون حدود النقاط
                        pointBorderWidth: 2,                           // سمك حدود النقاط
                        pointRadius: 4                                 // حجم النقاط
                    }, {
                        // خط الإيداعات اليومية (مخفي افتراضياً)
                        label: 'الإيداعات اليومية',
                        data: deposits,
                        borderColor: '#17a2b8',                        // لون الخط (أزرق)
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',   // لون التعبئة (أزرق شفاف)
                        fill: false,                                   // بدون تعبئة
                        tension: 0.4,
                        hidden: true                                   // مخفي افتراضياً
                    }, {
                        // خط السحوبات اليومية (مخفي افتراضياً)
                        label: 'السحوبات اليومية',
                        data: withdrawals,
                        borderColor: '#dc3545',                        // لون الخط (أحمر)
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',    // لون التعبئة (أحمر شفاف)
                        fill: false,                                   // بدون تعبئة
                        tension: 0.4,
                        hidden: true                                   // مخفي افتراضياً
                    }]
                },
                options: {
                    responsive: true,              // استجابة للشاشات المختلفة
                    maintainAspectRatio: false,    // عدم الحفاظ على نسبة العرض للارتفاع
                    interaction: {
                        intersect: false,          // عدم الحاجة لتقاطع المؤشر مع النقطة
                        mode: 'index'             // عرض جميع القيم عند نفس المؤشر
                    },
                    plugins: {
                        legend: {
                            position: 'top'        // موضع المفتاح في الأعلى
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',  // خلفية التلميح
                            callbacks: {
                                // تنسيق النص في التلميح
                                label: function(context) {
                                    try {
                                        const value = new Intl.NumberFormat('ar-SA', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }).format(context.raw);
                                        return `${context.dataset.label}: ${value}`;
                                    } catch (e) {
                                        return `${context.dataset.label}: ${context.raw}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'التاريخ'      // عنوان المحور السيني
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'المبلغ'       // عنوان المحور الصادي
                            },
                            ticks: {
                                // تنسيق الأرقام على المحور الصادي
                                callback: function(value) {
                                    try {
                                        return new Intl.NumberFormat('ar-SA', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        }).format(value);
                                    } catch (e) {
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                }
            });

            console.log('✅ تم إنشاء رسم الرصيد اليومي بنجاح');

        } catch (error) {
            console.error('❌ خطأ في إنشاء رسم الرصيد:', error);
        }
    }

    /* =====================================================
       رسم توزيع العملات (Currency Chart)
       ===================================================== */
    
    function initializeCurrencyChart() {
        try {
            console.log('🪙 بدء تهيئة رسم العملات...');

            // العثور على عنصر Canvas
            const currencyCtx = document.getElementById('currencyChart');
            if (!currencyCtx) {
                console.error('❌ لم يتم العثور على عنصر currencyChart');
                return;
            }

            // التحقق من وجود بيانات العملات
            if (!currencyData || Object.keys(currencyData).length === 0) {
                console.warn('⚠️ لا توجد بيانات عملات متاحة');
                const parentElement = currencyCtx.closest('.card-body');
                if (parentElement) {
                    parentElement.innerHTML = 
                        '<div class="text-center p-4"><p class="text-muted">لا توجد بيانات عملات متعددة</p></div>';
                }
                return;
            }

            console.log('💱 بيانات العملات:', currencyData);

            // تحضير البيانات للرسم البياني
            const currencies = Object.values(currencyData);                    // تحويل الكائن إلى مصفوفة
            const labels = currencies.map(c => `${c.currency} ${c.symbol || ''}`);  // التسميات
            const netData = currencies.map(c => Math.abs(parseFloat(c.net) || 0));  // القيم المطلقة للصافي
            
            // ألوان مختلفة لكل عملة
            const colors = [
                'rgba(40, 167, 69, 0.8)',    // أخضر
                'rgba(23, 162, 184, 0.8)',   // أزرق
                'rgba(255, 193, 7, 0.8)',    // أصفر
                'rgba(220, 53, 69, 0.8)',    // أحمر
                'rgba(108, 117, 125, 0.8)'   // رمادي
            ];

            console.log('📊 بيانات رسم العملات المحضرة:', { labels, netData });

            // إنشاء الرسم البياني الدائري
            currencyChart = new Chart(currencyCtx, {
                type: 'doughnut',  // نوع الرسم: دائري مجوف
                data: {
                    labels: labels,
                    datasets: [{
                        data: netData,
                        backgroundColor: colors.slice(0, currencies.length),           // ألوان التعبئة
                        borderColor: colors.slice(0, currencies.length).map(c => c.replace('0.8', '1')),  // ألوان الحدود
                        borderWidth: 2                                                 // سمك الحدود
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'  // موضع المفتاح في الأسفل
                        },
                        tooltip: {
                            callbacks: {
                                // تخصيص نص التلميح
                                label: function(context) {
                                    try {
                                        const currency = currencies[context.dataIndex];
                                        const value = new Intl.NumberFormat('ar-SA', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }).format(currency.net);
                                        return `${context.label}: ${value}`;
                                    } catch (e) {
                                        return `${context.label}: ${context.raw}`;
                                    }
                                }
                            }
                        }
                    }
                }
            });

            console.log('✅ تم إنشاء رسم العملات بنجاح');

        } catch (error) {
            console.error('❌ خطأ في إنشاء رسم العملات:', error);
        }
    }

    /* =====================================================
       رسم توزيع التصنيفات (Category Chart)
       ===================================================== */
    
    function initializeCategoryChart() {
        try {
            console.log('🏷️ بدء تهيئة رسم التصنيفات...');

            // العثور على عنصر Canvas
            const categoryCtx = document.getElementById('categoryChart');
            if (!categoryCtx) {
                console.error('❌ لم يتم العثور على عنصر categoryChart');
                return;
            }

            // التحقق من وجود بيانات التصنيفات
            if (!categoryData || Object.keys(categoryData).length === 0) {
                console.warn('⚠️ لا توجد بيانات تصنيفات متاحة');
                const parentElement = categoryCtx.closest('.card-body');
                if (parentElement) {
                    parentElement.innerHTML = 
                        '<div class="text-center p-4"><p class="text-muted">لا توجد بيانات تصنيفات</p></div>';
                }
                return;
            }

            console.log('📑 بيانات التصنيفات:', categoryData);

            // تحضير البيانات للرسم البياني
            const categories = Object.values(categoryData);                // تحويل الكائن إلى مصفوفة
            const labels = categories.map(c => c.category);               // أسماء التصنيفات
            const data = categories.map(c => parseFloat(c.count) || 0);   // عدد المعاملات لكل تصنيف
            
            // مجموعة ألوان متنوعة للتصنيفات
            const colors = [
                'rgba(255, 99, 132, 0.8)',   // وردي
                'rgba(54, 162, 235, 0.8)',   // أزرق
                'rgba(255, 205, 86, 0.8)',   // أصفر ذهبي
                'rgba(75, 192, 192, 0.8)',   // أخضر مائي
                'rgba(153, 102, 255, 0.8)',  // بنفسجي
                'rgba(255, 159, 64, 0.8)'    // برتقالي
            ];

            console.log('📊 بيانات رسم التصنيفات المحضرة:', { labels, data });

            // إنشاء الرسم البياني الدائري
            categoryChart = new Chart(categoryCtx, {
                type: 'pie',  // نوع الرسم: دائري مملوء
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, categories.length),           // ألوان التعبئة
                        borderColor: colors.slice(0, categories.length).map(c => c.replace('0.8', '1')),  // ألوان الحدود
                        borderWidth: 2                                                 // سمك الحدود
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'  // موضع المفتاح في الأسفل
                        },
                        tooltip: {
                            callbacks: {
                                // تخصيص نص التلميح
                                label: function(context) {
                                    try {
                                        const category = categories[context.dataIndex];
                                        return `${context.label}: ${category.count} معاملة`;
                                    } catch (e) {
                                        return `${context.label}: ${context.raw}`;
                                    }
                                }
                            }
                        }
                    }
                }
            });

            console.log('✅ تم إنشاء رسم التصنيفات بنجاح');

        } catch (error) {
            console.error('❌ خطأ في إنشاء رسم التصنيفات:', error);
        }
    }

    /* =====================================================
       الرسم البياني الأسبوعي (Weekly Chart)
       ===================================================== */
    
    function initializeWeeklyChart() {
        try {
            console.log('📅 بدء تهيئة الرسم الأسبوعي...');

            // العثور على عنصر Canvas
            const weeklyCtx = document.getElementById('weeklyChart');
            if (!weeklyCtx) {
                console.error('❌ لم يتم العثور على عنصر weeklyChart');
                return;
            }

            // التحقق من وجود البيانات الأسبوعية
            if (!weeklyData || weeklyData.length === 0) {
                console.warn('⚠️ لا توجد بيانات أسبوعية متاحة');
                const parentElement = weeklyCtx.closest('.chart-container');
                if (parentElement) {
                    parentElement.innerHTML = 
                        '<div class="text-center p-4"><p class="text-muted">لا توجد بيانات أسبوعية</p></div>';
                }
                return;
            }

            console.log('📊 البيانات الأسبوعية:', weeklyData);

            // تحضير البيانات للرسم البياني
            const labels = weeklyData.map((w, i) => `الأسبوع ${w.week_number || (i + 1)}`);  // تسميات الأسابيع
            const deposits = weeklyData.map(w => parseFloat(w.deposits) || 0);               // إيداعات كل أسبوع
            const withdrawals = weeklyData.map(w => parseFloat(w.withdrawals) || 0);         // سحوبات كل أسبوع
            const transfers = weeklyData.map(w => parseFloat(w.transfers) || 0);             // تحويلات كل أسبوع

            console.log('📋 بيانات الرسم الأسبوعي المحضرة:', { labels, deposits, withdrawals, transfers });

            // إنشاء رسم بياني عمودي
            weeklyChart = new Chart(weeklyCtx, {
                type: 'bar',  // نوع الرسم: أعمدة
                data: {
                    labels: labels,
                    datasets: [{
                        // أعمدة الإيداعات
                        label: 'إيداعات',
                        data: deposits,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',   // أخضر
                        borderColor: '#28a745',
                        borderWidth: 1
                    }, {
                        // أعمدة السحوبات
                        label: 'سحوبات',
                        data: withdrawals,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',   // أحمر
                        borderColor: '#dc3545',
                        borderWidth: 1
                    }, {
                        // أعمدة التحويلات
                        label: 'تحويلات',
                        data: transfers,
                        backgroundColor: 'rgba(255, 193, 7, 0.8)',   // أصفر
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'  // موضع المفتاح في الأعلى
                        },
                        tooltip: {
                            callbacks: {
                                // تنسيق التلميح
                                label: function(context) {
                                    try {
                                        const value = new Intl.NumberFormat('ar-SA', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }).format(context.raw);
                                        return `${context.dataset.label}: ${value}`;
                                    } catch (e) {
                                        return `${context.dataset.label}: ${context.raw}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'الأسابيع'      // عنوان المحور السيني
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'المبلغ'        // عنوان المحور الصادي
                            },
                            beginAtZero: true,        // البدء من الصفر
                            ticks: {
                                // تنسيق الأرقام على المحور الصادي
                                callback: function(value) {
                                    try {
                                        return new Intl.NumberFormat('ar-SA', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        }).format(value);
                                    } catch (e) {
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                }
            });

            console.log('✅ تم إنشاء الرسم الأسبوعي بنجاح');

        } catch (error) {
            console.error('❌ خطأ في إنشاء الرسم الأسبوعي:', error);
        }
    }

    /* =====================================================
       دوال التفاعل والتحكم في الواجهة
       ===================================================== */

    /**
     * تبديل عرض الرسوم البيانية في رسم الرصيد
     * @param {string} type - نوع العرض: 'balance' | 'transactions' | 'comparison'
     */
    window.showChart = function(type) {
        try {
            console.log(`🔄 تبديل عرض الرسم إلى: ${type}`);

            // التحقق من تهيئة الرسم البياني
            if (!balanceChart) {
                console.error('❌ رسم الرصيد غير مُهيأ');
                return;
            }

            // إزالة التفعيل من جميع الأزرار
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // تفعيل الزر المحدد
            if (event && event.target) {
                event.target.classList.add('active');
            }

            // تطبيق نوع العرض المطلوب
            switch(type) {
                case 'balance':
                    // عرض الرصيد فقط
                    balanceChart.data.datasets[0].hidden = false;  // الرصيد التراكمي
                    balanceChart.data.datasets[1].hidden = true;   // الإيداعات
                    balanceChart.data.datasets[2].hidden = true;   // السحوبات
                    break;
                    
                case 'transactions':
                    // عرض المعاملات فقط
                    balanceChart.data.datasets[0].hidden = true;   // الرصيد التراكمي
                    balanceChart.data.datasets[1].hidden = false;  // الإيداعات
                    balanceChart.data.datasets[2].hidden = false;  // السحوبات
                    break;
                    
                case 'comparison':
                    // عرض الجميع
                    balanceChart.data.datasets[0].hidden = false;  // الرصيد التراكمي
                    balanceChart.data.datasets[1].hidden = false;  // الإيداعات
                    balanceChart.data.datasets[2].hidden = false;  // السحوبات
                    break;
            }

            // تحديث الرسم البياني
            balanceChart.update();
            console.log(`✅ تم تحديث عرض الرسم إلى: ${type}`);

        } catch (error) {
            console.error('❌ خطأ في تبديل عرض الرسم:', error);
        }
    };

    /**
     * إظهار/إخفاء التفاصيل اليومية
     */
    window.toggleDayDetails = function() {
        try {
            console.log('👁️ تبديل عرض التفاصيل اليومية...');

            // العثور على عنصر التفاصيل
            const details = document.getElementById('dailyAnalysis');
            if (!details) {
                console.error('❌ لم يتم العثور على عنصر dailyAnalysis');
                return;
            }

            // العثور على الزر والأيقونة
            const button = document.querySelector('button[onclick="toggleDayDetails()"]');
            const icon = button ? button.querySelector('i') : null;

            // تبديل الحالة
            if (details.style.display === 'none') {
                // إظهار التفاصيل
                details.style.display = 'block';
                if (button) {
                    button.innerHTML = '<i class="fas fa-eye-slash me-1"></i> إخفاء التفاصيل';
                }
                console.log('✅ تم إظهار التفاصيل اليومية');
            } else {
                // إخفاء التفاصيل
                details.style.display = 'none';
                if (button) {
                    button.innerHTML = '<i class="fas fa-eye me-1"></i> إظهار التفاصيل';
                }
                console.log('✅ تم إخفاء التفاصيل اليومية');
            }

        } catch (error) {
            console.error('❌ خطأ في تبديل التفاصيل اليومية:', error);
        }
    };

    /**
     * تصدير التقرير (طباعة)
     */
    window.exportReport = function() {
        try {
            console.log('🖨️ بدء تصدير التقرير...');
            window.print();
            console.log('✅ تم تشغيل أمر الطباعة');
        } catch (error) {
            console.error('❌ خطأ في تصدير التقرير:', error);
        }
    };

    /**
     * الانتقال للشهر السابق
     */
    window.showPreviousMonth = function() {
        try {
            console.log('⬅️ الانتقال للشهر السابق...');
            const currentMonth = new Date('{{ $month }}-01');
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            const newMonth = currentMonth.toISOString().slice(0, 7);
            window.location.href = `{{ route('admin.transactions.reports.monthly') }}?month=${newMonth}`;
        } catch (error) {
            console.error('❌ خطأ في الانتقال للشهر السابق:', error);
        }
    };

    /**
     * الانتقال للشهر التالي
     */
    window.showNextMonth = function() {
        try {
            console.log('➡️ الانتقال للشهر التالي...');
            const currentMonth = new Date('{{ $month }}-01');
            currentMonth.setMonth(currentMonth.getMonth() + 1);
            const newMonth = currentMonth.toISOString().slice(0, 7);
            window.location.href = `{{ route('admin.transactions.reports.monthly') }}?month=${newMonth}`;
        } catch (error) {
            console.error('❌ خطأ في الانتقال للشهر التالي:', error);
        }
    };

    /* =====================================================
       معالج تغيير حجم النافذة
       ===================================================== */
    
    // تحديث أحجام الرسوم البيانية عند تغيير حجم النافذة
    window.addEventListener('resize', function() {
        try {
            console.log('📏 تحديث أحجام الرسوم البيانية...');
            
            // تحديث كل رسم بياني إذا كان موجوداً
            if (balanceChart) {
                balanceChart.resize();
                console.log('✅ تم تحديث حجم رسم الرصيد');
            }
            if (currencyChart) {
                currencyChart.resize();
                console.log('✅ تم تحديث حجم رسم العملات');
            }
            if (categoryChart) {
                categoryChart.resize();
                console.log('✅ تم تحديث حجم رسم التصنيفات');
            }
            if (weeklyChart) {
                weeklyChart.resize();
                console.log('✅ تم تحديث حجم الرسم الأسبوعي');
            }
            
        } catch (error) {
            console.error('❌ خطأ في تحديث أحجام الرسوم:', error);
        }
    });

    console.log('🎉 تم تعريف جميع الدوال بنجاح');

} catch (error) {
    /* =====================================================
       معالجة الأخطاء الحرجة
       ===================================================== */
    
    console.error('💥 خطأ حرج في تهيئة السكريبت:', error);
    
    // إظهار رسالة خطأ للمستخدم
    alert('⚠️ حدث خطأ في تحميل الرسوم البيانية. يرجى إعادة تحميل الصفحة أو التواصل مع الدعم الفني.');
    
    // إرسال تقرير الخطأ (اختياري)
    // يمكن إضافة كود لإرسال تفاصيل الخطأ للخادم هنا
}

/* =====================================================
   انتهاء السكريبت الرئيسي
   ===================================================== */

console.log('🏁 انتهى تحميل سكريبت التقرير الشهري');
</script>
@endpush
