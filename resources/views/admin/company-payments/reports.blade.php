@extends('layouts.app')

@section('title', 'التقارير المالية - مدفوعات الشركات')

@push('styles')
    <style>
        /* 🎨 تنسيقات عامة للتقارير */
        .reports-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .report-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .report-header {
            background: linear-gradient(120deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-bottom: none;
        }

        .report-header h5 {
            margin: 0;
            font-weight: 600;
        }

        /* 📊 تنسيقات البطاقات الإحصائية */
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(-20px, -20px) rotate(180deg);
            }
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* 💹 تنسيقات قسم الأرباح */
        .profit-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .profit-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .profit-item:last-child {
            border-bottom: none;
        }

        .profit-positive {
            color: #28a745;
            font-weight: bold;
        }

        .profit-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .efficiency-bar {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .efficiency-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff6b6b, #feca57, #48dbfb, #0abde3);
            transition: width 0.3s ease;
        }

        /* 📈 تنسيقات الرسوم البيانية */
        .chart-container {
            position: relative;
            height: 400px;
            padding: 20px;
        }

        .chart-small {
            height: 300px;
        }

        /* 🎯 تنسيقات المؤشرات والأهداف */
        .target-progress {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .progress-bar-custom {
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            border-radius: 10px;
        }

        /* ⚠️ تنسيقات التنبيهات والمخاطر */
        .risk-alert {
            border-left: 4px solid;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .risk-danger {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
        }

        .risk-warning {
            border-color: #ffc107;
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
        }

        .risk-info {
            border-color: #17a2b8;
            background: rgba(23, 162, 184, 0.1);
            color: #0c5460;
        }

        /* 🔍 تنسيقات الفلاتر */
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-btn {
            margin: 5px;
            border-radius: 25px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: linear-gradient(120deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* 📱 التوافق مع الأجهزة المحمولة */
        @media (max-width: 768px) {
            .chart-container {
                height: 300px;
                padding: 10px;
            }

            .stats-number {
                font-size: 2rem;
            }

            .reports-container {
                padding: 10px;
            }
        }

        /* 🎨 تأثيرات إضافية */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }
    </style>
@endpush

@section('content')
    <div class="reports-container">
        <div class="container-fluid">

            <!-- 🔧 قسم الفلاتر والتحكم -->
            <div class="filters-section fade-in">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <h2 class="mb-3">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            التقارير المالية - مدفوعات الشركات
                        </h2>

                        <!-- أزرار الفترات السريعة -->
                        <div class="mb-3">
                            <button class="btn btn-outline-primary filter-btn active" data-period="daily">
                                <i class="fas fa-calendar-day me-1"></i> يومي
                            </button>
                            <button class="btn btn-outline-primary filter-btn" data-period="weekly">
                                <i class="fas fa-calendar-week me-1"></i> أسبوعي
                            </button>
                            <button class="btn btn-outline-primary filter-btn" data-period="monthly">
                                <i class="fas fa-calendar-alt me-1"></i> شهري
                            </button>
                            <button class="btn btn-outline-secondary filter-btn" data-period="custom">
                                <i class="fas fa-calendar-check me-1"></i> فترة مخصصة
                            </button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- فلتر العملة -->
                        <div class="mb-3">
                            <label class="form-label">العملة</label>
                            <select id="currencyFilter" class="form-select">
                                <option value="all">جميع العملات</option>
                                <option value="SAR">ريال سعودي</option>
                                <option value="KWD">دينار كويتي</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- فلاتر التاريخ المخصص -->
                <div id="customDateRange" class="row" style="display: none;">
                    <div class="col-md-6">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" id="startDate" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" id="endDate" class="form-control">
                    </div>
                </div>
            </div>

            <!-- 📊 قسم البطاقات الإحصائية الرئيسية -->
            <div id="mainStatsSection" class="row">
                <!-- سيتم ملؤها ديناميكياً -->
            </div>
            <!-- 💹 قسم الأرباح والتحليل المالي -->
            <div class="row">
                <div class="col-12">
                    <div class="report-card fade-in">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-chart-pie me-2"></i>
                                تحليل الأرباح والعوائد المالية
                            </h5>
                        </div>
                        <div class="card-body" id="profitAnalysisSection">
                            <!-- سيتم ملؤها ديناميكياً -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📈 قسم الرسوم البيانية الرئيسية -->
            <div class="row">
                <!-- الرسم البياني للمدفوعات اليومية/الأسبوعية/الشهرية -->
                <div class="col-lg-8">
                    <div class="report-card fade-in">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-chart-area me-2"></i>
                                اتجاه المدفوعات
                            </h5>
                        </div>
                        <div class="chart-container">
                            <canvas id="paymentsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- توزيع المدفوعات حسب العملة -->
                <div class="col-lg-4">
                    <div class="report-card fade-in">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-chart-pie me-2"></i>
                                توزيع العملات
                            </h5>
                        </div>
                        <div class="chart-container chart-small">
                            <canvas id="currencyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🏢 قسم أفضل الشركات دفعاً -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="report-card fade-in">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-trophy me-2"></i>
                                أفضل الشركات دفعاً
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container chart-small">
                                <canvas id="topCompaniesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 📅 قسم مقارنة الفترات -->
                <div class="col-lg-6">
                    <div class="report-card fade-in">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-balance-scale me-2"></i>
                                مقارنة بالفترة السابقة
                            </h5>
                        </div>
                        <div class="card-body" id="comparisonSection">
                            <!-- سيتم ملؤها ديناميكياً -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🎯 قسم أهداف المحصلات -->
            <div class="row">
                <div class="col-12">
                    <div class="report-card fade-in">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-bullseye me-2"></i>
                                أهداف المحصلات الشهرية
                            </h5>
                        </div>
                        <div class="card-body" id="targetsSection">
                            <!-- سيتم ملؤها ديناميكياً -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⚠️ قسم تحليل المخاطر والتنبيهات -->
            <div class="row">
                <div class="col-12">
                    <div class="report-card fade-in">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                تحليل المخاطر والتنبيهات
                            </h5>
                        </div>
                        <div class="card-body" id="riskAnalysisSection">
                            <!-- سيتم ملؤها ديناميكياً -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 🔄 شاشة التحميل -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <p class="mt-3 mb-0">جاري تحميل التقارير...</p>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- مكتبة Chart.js للرسوم البيانية -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js">
    </script>

    <script>
        // 🌐 متغيرات عامة
        let currentPeriod = 'daily';
        let currentCurrency = 'all';
        let charts = {}; // تخزين مراجع الرسوم البيانية

        // 🚀 تشغيل التطبيق عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📊 بدء تحميل التقارير المالية...');

            initializeEventListeners();
            loadReportsData();
        });

        // 🎛️ تهيئة مستمعات الأحداث
        function initializeEventListeners() {
            // أزرار الفترات
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const period = this.dataset.period;
                    currentPeriod = period;

                    if (period === 'custom') {
                        document.getElementById('customDateRange').style.display = 'block';
                    } else {
                        document.getElementById('customDateRange').style.display = 'none';
                        loadReportsData();
                    }
                });
            });

            // فلتر العملة
            document.getElementById('currencyFilter').addEventListener('change', function() {
                currentCurrency = this.value;
                loadReportsData();
            });

            // فلاتر التاريخ المخصص
            document.getElementById('startDate').addEventListener('change', loadCustomDateData);
            document.getElementById('endDate').addEventListener('change', loadCustomDateData);
        }

        // 📅 تحميل بيانات التاريخ المخصص
        function loadCustomDateData() {
            if (currentPeriod === 'custom') {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;

                if (startDate && endDate) {
                    loadReportsData(startDate, endDate);
                }
            }
        }

        // 📡 تحميل بيانات التقارير من الخادم
        function loadReportsData(startDate = null, endDate = null) {
            showLoading(true);

            const params = new URLSearchParams({
                period: currentPeriod,
                currency: currentCurrency
            });

            if (startDate && endDate) {
                params.append('start_date', startDate);
                params.append('end_date', endDate);
            }

            fetch(`{{ route('admin.company-payments.reports.data') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    console.log('📊 تم تحميل بيانات التقارير:', data);
                    renderAllReports(data);
                })
                .catch(error => {
                    console.error('❌ خطأ في تحميل البيانات:', error);
                    showError('حدث خطأ في تحميل البيانات');
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        // 🎨 عرض جميع التقارير
        function renderAllReports(data) {
            renderMainStats(data.total_payments);
            renderProfitAnalysis(data.profit_data); // ✅ إضافة عرض الأرباح
            renderPaymentsChart(data.chart_data);
            renderCurrencyChart(data.currency_distribution);
            renderTopCompaniesChart(data.top_companies);
            renderComparison(data.comparison);
            renderTargets(data.collection_targets);
            renderRiskAnalysis(data.risk_analysis);
        }

        // 💹 عرض تحليل الأرباح
        function renderProfitAnalysis(profitData) {
            const container = document.getElementById('profitAnalysisSection');
            container.innerHTML = '';

            if (!profitData || Object.keys(profitData).length === 0) {
                container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-chart-line text-muted fa-3x mb-3"></i>
                <h5 class="text-muted">لا توجد بيانات ربح متاحة</h5>
                <p class="text-muted">لا توجد حجوزات أو مدفوعات في الفترة المحددة</p>
            </div>
        `;
                return;
            }

            let html = '<div class="row">';

            // حساب الإجماليات
            let totalActualProfit = 0;
            let totalPotentialProfit = 0;
            let totalCollectionRate = 0;
            let currencyCount = 0;

            Object.entries(profitData).forEach(([currency, data]) => {
                totalActualProfit += Math.abs(data.actual_profit);
                totalPotentialProfit += Math.abs(data.potential_profit);
                totalCollectionRate += data.collection_rate;
                currencyCount++;
            });

            const avgCollectionRate = currencyCount > 0 ? totalCollectionRate / currencyCount : 0;

            // بطاقة الإجمالي العام للأرباح
            html += `
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                        <div class="card-body">
                            <i class="fas fa-coins fa-2x mb-2"></i>
                            <h4>${formatNumber(totalActualProfit)}</h4>
                            <small>إجمالي الربح الفعلي</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center" style="background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%); color: white;">
                        <div class="card-body">
                            <i class="fas fa-bullseye fa-2x mb-2"></i>
                            <h4>${formatNumber(totalPotentialProfit)}</h4>
                            <small>الربح المتوقع الكامل</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white;">
                        <div class="card-body">
                            <i class="fas fa-percentage fa-2x mb-2"></i>
                            <h4>${avgCollectionRate.toFixed(1)}%</h4>
                            <small>متوسط معدل التحصيل</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%); color: white;">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <h4>${formatNumber(totalPotentialProfit - totalActualProfit)}</h4>
                            <small>الربح المفقود</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

            // تفاصيل كل عملة
            Object.entries(profitData).forEach(([currency, data]) => {
                const currencyName = currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي';
                const currencySymbol = currency === 'SAR' ? 'ريال' : 'دينار';

                // تحديد لون شريط التقدم حسب معدل التحصيل
                let progressColor = 'danger';
                if (data.collection_rate >= 80) progressColor = 'success';
                else if (data.collection_rate >= 60) progressColor = 'warning';
                else if (data.collection_rate >= 40) progressColor = 'info';

                const profitLoss = data.potential_profit - data.actual_profit;
                const profitEfficiency = data.potential_profit > 0 ? (data.actual_profit / data.potential_profit) *
                    100 : 0;

                html += `
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-coins me-2"></i>
                            تحليل الربح - ${currencyName}
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- معدل التحصيل -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-bold">معدل التحصيل</small>
                                <small class="fw-bold">${data.collection_rate.toFixed(1)}%</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-${progressColor}" style="width: ${data.collection_rate}%"></div>
                            </div>
                        </div>

                        <!-- الأرباح -->
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <h6 class="text-success mb-0">${formatNumber(data.actual_profit)}</h6>
                                    <small class="text-muted">الربح الفعلي</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <h6 class="text-primary mb-0">${formatNumber(data.potential_profit)}</h6>
                                    <small class="text-muted">الربح المتوقع</small>
                                </div>
                            </div>
                        </div>

                        <!-- تفاصيل إضافية -->
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted d-block">نسبة الربح</small>
                                <strong class="text-info">${data.profit_percentage.toFixed(1)}%</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">كفاءة الربح</small>
                                <strong class="text-warning">${profitEfficiency.toFixed(1)}%</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">الربح المفقود</small>
                                <strong class="text-danger">${formatNumber(profitLoss)}</strong>
                            </div>
                        </div>

                        <!-- شريط المقارنة -->
                        <div class="mt-3">
                            <small class="text-muted">مقارنة الربح الفعلي بالمتوقع:</small>
                            <div class="progress mt-1" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: ${profitEfficiency}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            });

            html += '</div>';
            container.innerHTML = html;
        }

        // 📊 عرض البطاقات الإحصائية الرئيسية
        function renderMainStats(totalPayments) {
            const container = document.getElementById('mainStatsSection');
            container.innerHTML = '';

            if (!totalPayments || Object.keys(totalPayments).length === 0) {
                container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    لا توجد مدفوعات في الفترة المحددة
                </div>
            </div>
        `;
                return;
            }

            // إضافة بطاقة الإجمالي العام
            let grandTotal = 0;
            let grandCount = 0;

            Object.entries(totalPayments).forEach(([currency, data]) => {
                grandTotal += Math.abs(data.total); // استخدام القيمة المطلقة
                grandCount += data.count;
            });

            // بطاقة الإجمالي
            container.innerHTML += `
        <div class="col-md-6 col-lg-3">
            <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="stats-number">${formatNumber(grandTotal)}</div>
                <div class="stats-label">إجمالي عام</div>
                <small class="d-block mt-2">
                    <i class="fas fa-calculator me-1"></i>
                    ${grandCount} عملية
                </small>
            </div>
        </div>
    `;

            // بطاقات العملات
            Object.entries(totalPayments).forEach(([currency, data]) => {
                const currencyName = currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي';
                const currencySymbol = currency === 'SAR' ? 'ريال' : 'دينار';
                const amount = parseFloat(data.total);
                const isNegative = amount < 0;
                const displayAmount = Math.abs(amount);

                container.innerHTML += `
            <div class="col-md-6 col-lg-3">
                <div class="stats-card ${isNegative ? 'border border-warning' : ''}">
                    <div class="stats-number ${isNegative ? 'text-warning' : ''}">
                        ${isNegative ? '-' : ''}${formatNumber(displayAmount)}
                    </div>
                    <div class="stats-label">${currencySymbol}</div>
                    <small class="d-block mt-2">
                        <i class="fas fa-receipt me-1"></i>
                        ${data.count} عملية
                        ${isNegative ? '<span class="text-warning">(تتضمن خصومات)</span>' : ''}
                    </small>
                </div>
            </div>
        `;
            });
        }

        // 📈 رسم بياني للمدفوعات حسب الفترة
        function renderPaymentsChart(chartData) {
            const ctx = document.getElementById('paymentsChart').getContext('2d');

            // تنظيف الرسم السابق
            if (charts.payments) {
                charts.payments.destroy();
            }

            const labels = Object.keys(chartData);
            const sarData = labels.map(label => {
                const periodData = chartData[label];
                return periodData.find(d => d.currency === 'SAR')?.total_amount || 0;
            });
            const kwdData = labels.map(label => {
                const periodData = chartData[label];
                return periodData.find(d => d.currency === 'KWD')?.total_amount || 0;
            });

            charts.payments = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'ريال سعودي',
                        data: sarData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'دينار كويتي',
                        data: kwdData,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatNumber(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // 🥧 رسم بياني دائري لتوزيع العملات
        function renderCurrencyChart(currencyData) {
            const ctx = document.getElementById('currencyChart').getContext('2d');

            if (charts.currency) {
                charts.currency.destroy();
            }

            const data = Object.values(currencyData);
            const labels = Object.keys(currencyData).map(c => c === 'SAR' ? 'ريال سعودي' : 'دينار كويتي');
            const amounts = data.map(d => d.total_amount);

            charts.currency = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: amounts,
                        backgroundColor: ['#28a745', '#007bff'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = formatNumber(context.parsed);
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 🏆 رسم بياني لأفضل الشركات
        function renderTopCompaniesChart(topCompanies) {
            const ctx = document.getElementById('topCompaniesChart').getContext('2d');

            if (charts.topCompanies) {
                charts.topCompanies.destroy();
            }

            const labels = topCompanies.map(company => company.name);
            const data = topCompanies.map(company => company.total_paid);

            charts.topCompanies = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'إجمالي المدفوعات',
                        data: data,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: '#667eea',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatNumber(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // 📊 عرض مقارنة الفترات
        function renderComparison(comparison) {
            const container = document.getElementById('comparisonSection');
            const changeClass = comparison.change_percent >= 0 ? 'text-success' : 'text-danger';
            const changeIcon = comparison.change_percent >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

            container.innerHTML = `
        <div class="row text-center">
            <div class="col-md-4">
                <h4 class="text-primary">${formatNumber(comparison.current.total)}</h4>
                <small class="text-muted">الفترة الحالية</small>
            </div>
            <div class="col-md-4">
                <h4 class="text-secondary">${formatNumber(comparison.previous.total)}</h4>
                <small class="text-muted">الفترة السابقة</small>
            </div>
            <div class="col-md-4">
                <h4 class="${changeClass}">
                    <i class="fas ${changeIcon} me-1"></i>
                    ${Math.abs(comparison.change_percent)}%
                </h4>
                <small class="text-muted">نسبة التغيير</small>
            </div>
        </div>
    `;
        }

        // 🎯 عرض أهداف المحصلات
        function renderTargets(targets) {
            const container = document.getElementById('targetsSection');
            let html = '<div class="row">';

            Object.entries(targets).forEach(([currency, target]) => {
                const currencyName = currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي';
                const progressColor = target.percentage >= 80 ? 'success' : target.percentage >= 50 ? 'warning' :
                    'danger';

                html += `
            <div class="col-md-6">
                <div class="target-progress">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>${currencyName}</strong>
                        <span>${target.percentage}%</span>
                    </div>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-${progressColor}" style="width: ${target.percentage}%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small>محصل: ${formatNumber(target.collected)}</small>
                        <small>الهدف: ${formatNumber(target.target)}</small>
                    </div>
                </div>
            </div>
        `;
            });

            html += '</div>';
            container.innerHTML = html;
        }

        // ⚠️ عرض تحليل المخاطر
        function renderRiskAnalysis(risks) {
            const container = document.getElementById('riskAnalysisSection');

            if (risks.length === 0) {
                container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-shield-alt text-success fa-3x mb-3"></i>
                <h5 class="text-success">لا توجد مخاطر محددة</h5>
                <p class="text-muted">الوضع المالي مستقر ولا توجد تنبيهات</p>
            </div>
        `;
                return;
            }

            let html = '';
            risks.forEach(risk => {
                html += `
            <div class="risk-alert risk-${risk.level}">
                <h6>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${risk.title}
                </h6>
                <p class="mb-0">${risk.description}</p>
            </div>
        `;
            });

            container.innerHTML = html;
        }

        // 🛠️ دوال مساعدة

        // تنسيق الأرقام مع معالجة الأرقام السالبة
        function formatNumber(number) {
            const num = parseFloat(number);
            if (isNaN(num)) return '0';

            return new Intl.NumberFormat('ar-SA', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(Math.abs(num));
        }

        // إظهار/إخفاء شاشة التحميل
        function showLoading(show) {
            document.getElementById('loadingOverlay').style.display = show ? 'block' : 'none';
        }

        // إظهار رسالة خطأ
        function showError(message) {
            // يمكن تحسينها لاحقاً باستخدام toast notifications
            alert(message);
        }

        // 📱 تحسين التوافق مع الأجهزة المحمولة
        window.addEventListener('resize', function() {
            Object.values(charts).forEach(chart => {
                if (chart) {
                    chart.resize();
                }
            });
        });

        console.log('✅ تم تحميل نظام التقارير المالية بنجاح');
    </script>
@endpush
