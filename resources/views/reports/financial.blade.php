@extends('layouts.app')

@section('title', 'بيانات المتابعة المالية')

@section('content')
    <div class="container-fluid mt-4">
        <h1 class="mb-4">بيانات المتابعة المالية</h1>

        <!-- الفلاتر -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-filter me-2"></i> فلترة البيانات
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="date_filter" class="form-label">فترة التاريخ</label>
                            <select name="date_filter" id="date_filter" class="form-select">
                                <option value="week" selected>هذا الأسبوع</option>
                                <option value="month">هذا الشهر</option>
                                <option value="today">اليوم</option>
                                <option value="yesterday">الأمس</option>
                                <option value="custom">مخصص</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">تاريخ البداية</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" disabled>
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">تاريخ النهاية</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" disabled>
                        </div>
                        <div class="col-md-3">
                            <label for="payment_status" class="form-label">حالة الدفع</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="">الكل</option>
                                <option value="fully_paid">مدفوع بالكامل</option>
                                <option value="partially_paid">مدفوع جزئياً</option>
                                <option value="not_paid">غير مدفوع</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> تطبيق
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- الإحصائيات -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">مدفوع بالكامل</h5>
                        <p class="stats-value text-success" id="fully_paid_count">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">مدفوع جزئياً</h5>
                        <p class="stats-value text-warning" id="partially_paid_count">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted">غير مدفوع</h5>
                        <p class="stats-value text-danger" id="not_paid_count">0</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- الرسومات البيانية -->
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <i class="fas fa-chart-pie me-2 text-primary"></i> توزيع حالات الدفع
                    </div>
                    <div class="card-body">
                        <canvas id="paymentStatusChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <i class="fas fa-chart-bar me-2 text-primary"></i> توزيع مستويات الأولوية
                    </div>
                    <div class="card-body">
                        <canvas id="priorityLevelChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- رسم بياني شبكي للعلاقات المالية -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-project-diagram me-2 text-primary"></i> شبكة العلاقات المالية (الحجز ← الشركة ←
                    الجهة)</span>
                <div>
                    <button id="zoomInBtn" class="btn btn-sm btn-outline-secondary me-2"><i
                            class="fas fa-search-plus"></i></button>
                    <button id="zoomOutBtn" class="btn btn-sm btn-outline-secondary me-2"><i
                            class="fas fa-search-minus"></i></button>
                    <button id="panBtn" class="btn btn-sm btn-outline-secondary"><i
                            class="fas fa-arrows-alt"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div id="networkGraph" style="height: 400px;"></div>
            </div>
        </div>

        <!-- شبكة العلاقات المالية الجديدة -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-sitemap me-2 text-primary"></i> شبكة العلاقات المالية الجديدة (الحجوزات ← الشركة ←
                    الجهة)</span>
                <div>
                    <button id="newZoomInBtn" class="btn btn-sm btn-outline-secondary me-2"><i
                            class="fas fa-search-plus"></i></button>
                    <button id="newZoomOutBtn" class="btn btn-sm btn-outline-secondary me-2"><i
                            class="fas fa-search-minus"></i></button>
                    <button id="newPanBtn" class="btn btn-sm btn-outline-secondary"><i
                            class="fas fa-arrows-alt"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div id="financialNetwork" style="height: 400px;"></div>
            </div>
        </div>
        <!-- جدول البيانات -->
        <div class="d-flex justify-content-end mb-2">
            <button id="exportExcelJsBtn" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i> تصدير إلى Excel
            </button>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-table me-2 text-primary"></i> سجل المتابعة المالية</h5>
            </div>
            <div class="card-body position-relative">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="bookings-table-container">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>العميل</th>
                                <th>الشركة</th>
                                <th>الوكيل</th>
                                <th>الفندق</th>
                                <th>تاريخ الوصول</th>
                                <th>تاريخ المغادرة</th>
                                <th>حالة دفع الشركة</th>
                                <th>حالة دفع الوكيل</th>
                                <th>تاريخ الاستحقاق</th>
                                <th>تاريخ المتابعة</th>
                                <th>مستوى الأولوية</th>
                            </tr>
                        </thead>
                        <tbody id="trackingBody">
                            <tr>
                                <td colspan="12" class="text-center">اختر الفلاتر ثم اضغط "تطبيق" لعرض البيانات</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="loading-overlay d-none" id="loading">
                    <div class="spinner-border text-primary"></div>
                    <span class="ms-2">جاري التحميل...</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <!-- مكتبة Chart.js للرسومات البيانية -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- مكتبة D3.js للرسومات الشبكية -->
    <script src="https://d3js.org/d3.v7.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const dateFilterSelect = document.querySelector('[name="date_filter"]');
            const startDateInput = document.querySelector('[name="start_date"]');
            const endDateInput = document.querySelector('[name="end_date"]');

            // تهيئة التواريخ الافتراضية
            startDateInput.valueAsDate = new Date(new Date().setDate(new Date().getDate() - 7));
            endDateInput.valueAsDate = new Date();

            // معالجة تقديم النموذج
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                loadTrackingData();
            });

            // تمكين/تعطيل حقول التاريخ المخصصة
            dateFilterSelect.addEventListener('change', function() {
                const isCustom = this.value === 'custom';
                startDateInput.disabled = !isCustom;
                endDateInput.disabled = !isCustom;
                if (isCustom) {
                    startDateInput.focus();
                }
            });

            // تحميل البيانات عند بدء التشغيل
            loadTrackingData();

            // دالة لتحميل بيانات المتابعة المالية
            function loadTrackingData() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData).toString();
                const loading = document.getElementById('loading');
                const trackingBody = document.getElementById('trackingBody');

                loading.classList.remove('d-none');

                fetch(`/financial/tracking?${params}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`خطأ في الشبكة: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.error || 'حدث خطأ غير معروف');
                        }

                        // تحديث الإحصائيات
                        document.getElementById('fully_paid_count').textContent = data.statistics.fully_paid ||
                            0;
                        document.getElementById('partially_paid_count').textContent = data.statistics
                            .partially_paid || 0;
                        document.getElementById('not_paid_count').textContent = data.statistics.not_paid || 0;

                        // تحديث الجدول
                        updateTable(data.financial_tracking);

                        // رسم الرسومات فقط إذا كانت البيانات موجودة
                        if (data.statistics && data.financial_tracking) {
                            drawCharts(data);
                            drawNetworkGraph(data);
                            drawFinancialNetwork(data);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        trackingBody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        فشل تحميل البيانات: ${error.message}
                    </td>
                </tr>
            `;
                    })
                    .finally(() => {
                        loading.classList.add('d-none');
                    });
            }

            // تحديث الجدول بالبيانات
            function updateTable(data) {
                const trackingBody = document.getElementById('trackingBody');

                if (data.length === 0) {
                    trackingBody.innerHTML = `
                    <tr>
                        <td colspan="12" class="text-center">لا توجد بيانات متابعة مالية تطابق معايير البحث</td>
                    </tr>
                `;
                    return;
                }

                let html = '';

                data.forEach((item, index) => {
                    // تحديد لون الصف بناءً على حالة الدفع المدمجة
                    const rowClass = item.combined_status === 'fully_paid' ? 'table-success' :
                        item.combined_status === 'partially_paid' ? 'table-warning' : 'table-danger';

                    // تنسيق حالات الدفع
                    const companyStatusBadge = getStatusBadge(item.company_payment_status);
                    const agentStatusBadge = getStatusBadge(item.agent_payment_status);

                    // تنسيق مستوى الأولوية
                    const priorityBadge = getPriorityBadge(item.priority_level);

                    html += `
                    <tr class="${rowClass}">
                        <td>${index + 1}</td>
                        <td>${item.client_name}</td>
                        <td>${item.company_name}</td>
                        <td>${item.agent_name}</td>
                        <td>${item.hotel_name}</td>
                        <td>${item.check_in}</td>
                        <td>${item.check_out}</td>
                        <td>${companyStatusBadge}</td>
                        <td>${agentStatusBadge}</td>
                        <td>${item.payment_deadline || '-'}</td>
                        <td>${item.follow_up_date || '-'}</td>
                        <td>${priorityBadge}</td>
                    </tr>
                `;
                });

                trackingBody.innerHTML = html;
            }
            drawCharts(data);
            drawNetworkGraph(data);


            // دالة مساعدة لإنشاء شارة حالة الدفع
            function getStatusBadge(status) {
                let badgeClass = '';
                let text = '';

                switch (status) {
                    case 'fully_paid':
                        badgeClass = 'bg-success';
                        text = 'مدفوع بالكامل';
                        break;
                    case 'partially_paid':
                        badgeClass = 'bg-warning text-dark';
                        text = 'مدفوع جزئياً';
                        break;
                    case 'not_paid':
                        badgeClass = 'bg-danger';
                        text = 'غير مدفوع';
                        break;
                    default:
                        badgeClass = 'bg-secondary';
                        text = 'غير محدد';
                }

                return `<span class="badge ${badgeClass}">${text}</span>`;
            }

            // دالة مساعدة لإنشاء شارة مستوى الأولوية
            function getPriorityBadge(level) {
                let badgeClass = '';
                let text = '';

                switch (level) {
                    case 'high':
                        badgeClass = 'bg-danger';
                        text = 'عالية';
                        break;
                    case 'medium':
                        badgeClass = 'bg-warning text-dark';
                        text = 'متوسطة';
                        break;
                    case 'low':
                        badgeClass = 'bg-info';
                        text = 'منخفضة';
                        break;
                    default:
                        badgeClass = 'bg-secondary';
                        text = 'غير محدد';
                }

                return `<span class="badge ${badgeClass}">${text}</span>`;
            }
            // رسم الرسومات البيانية بعد تحميل البيانات
            function drawCharts(data) {
                // رسم حالات الدفع
                const ctx1 = document.getElementById('paymentStatusChart').getContext('2d');
                if (window.paymentChart) window.paymentChart.destroy();
                window.paymentChart = new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: ['مدفوع بالكامل', 'مدفوع جزئياً', 'غير مدفوع'],
                        datasets: [{
                            data: [data.statistics.fully_paid, data.statistics.partially_paid, data
                                .statistics.not_paid
                            ],
                            backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom',
                                rtl: true
                            },
                            title: {
                                display: true,
                                text: 'حالات الدفع',
                                font: {
                                    size: 16
                                }
                            }
                        }
                    }
                });

                // رسم مستويات الأولوية
                const priorities = data.financial_tracking.reduce((acc, item) => {
                    acc[item.priority_level] = (acc[item.priority_level] || 0) + 1;
                    return acc;
                }, {});
                const ctx2 = document.getElementById('priorityLevelChart').getContext('2d');
                if (window.priorityChart) window.priorityChart.destroy();
                window.priorityChart = new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: ['عالية', 'متوسطة', 'منخفضة'],
                        datasets: [{
                            data: [priorities.high || 0, priorities.medium || 0, priorities.low ||
                                0
                            ],
                            backgroundColor: ['#dc3545', '#ffc107', '#0dcaf0'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'مستويات الأولوية',
                                font: {
                                    size: 16
                                }
                            }
                        }
                    }
                });
            }

            // رسم الشبكة المالية (الحجز ← الشركة ← الجهة)
            function drawNetworkGraph(data) {
                const width = document.getElementById('networkGraph').offsetWidth;
                const height = 400;
                const container = d3.select("#networkGraph");
                container.selectAll("*").remove(); // تنظيف المحتوى

                // إنشاء SVG مع دعم الزووم
                const svg = container.append("svg")
                    .attr("width", width)
                    .attr("height", height);

                const g = svg.append("g"); // مجموعة لتطبيق التحويلات

                // تهيئة الزووم
                const zoom = d3.zoom()
                    .scaleExtent([0.1, 10]) // نطاق التكبير/التصغير
                    .on("zoom", (event) => {
                        g.attr("transform", event.transform);
                    });
                svg.call(zoom);

                // التحقق من البيانات
                if (!data.financial_tracking || data.financial_tracking.length === 0) {
                    g.append("text")
                        .attr("class", "text-center text-muted")
                        .attr("x", width / 2)
                        .attr("y", height / 2)
                        .attr("text-anchor", "middle")
                        .text("لا توجد بيانات لعرض الشبكة المالية.");
                    return;
                }

                // إعداد العقد و الروابط
                const nodes = [];
                const links = [];
                const nodeMap = {};

                data.financial_tracking.forEach(item => {
                    if (!nodeMap['company_' + item.company_name]) {
                        nodes.push({
                            id: 'company_' + item.company_name,
                            name: item.company_name,
                            type: 'company'
                        });
                        nodeMap['company_' + item.company_name] = true;
                    }
                    if (!nodeMap['agent_' + item.agent_name]) {
                        nodes.push({
                            id: 'agent_' + item.agent_name,
                            name: item.agent_name,
                            type: 'agent'
                        });
                        nodeMap['agent_' + item.agent_name] = true;
                    }
                    nodes.push({
                        id: 'booking_' + item.id,
                        name: item.client_name,
                        type: 'booking',
                        status: item.combined_status
                    });
                    links.push({
                        source: 'booking_' + item.id,
                        target: 'company_' + item.company_name,
                        value: 2,
                        status: item.company_payment_status
                    });
                    links.push({
                        source: 'booking_' + item.id,
                        target: 'agent_' + item.agent_name,
                        value: 2,
                        status: item.agent_payment_status
                    });
                });

                // تحديد الألوان
                const color = d3.scaleOrdinal()
                    .domain(['booking', 'company', 'agent'])
                    .range(['#0d6efd', '#198754', '#fd7e14']);

                const linkColor = status => {
                    if (status === 'fully_paid') return '#198754';
                    if (status === 'partially_paid') return '#ffc107';
                    return '#dc3545';
                };

                // تهيئة المحاكاة
                const simulation = d3.forceSimulation(nodes)
                    .force("link", d3.forceLink(links).id(d => d.id).distance(90).strength(1))
                    .force("charge", d3.forceManyBody().strength(-250))
                    .force("center", d3.forceCenter(width / 2, height / 2));

                // رسم الروابط
                const link = g.append("g")
                    .attr("stroke", "#999")
                    .selectAll("line")
                    .data(links)
                    .join("line")
                    .attr("stroke-width", d => d.value * 2)
                    .attr("stroke", d => linkColor(d.status));

                // رسم العقد
                const node = g.append("g")
                    .selectAll("circle")
                    .data(nodes)
                    .join("circle")
                    .attr("r", d => d.type === 'booking' ? 18 : 14)
                    .attr("fill", d => color(d.type))
                    .call(drag(simulation));

                // رسم التسميات
                const label = g.append("g")
                    .selectAll("text")
                    .data(nodes)
                    .join("text")
                    .attr("text-anchor", "middle")
                    .attr("dy", 4)
                    .attr("font-size", 12)
                    .attr("font-family", "Cairo, sans-serif")
                    .text(d => d.name);

                // تحديث المواقع عند التغيير
                simulation.on("tick", () => {
                    link
                        .attr("x1", d => d.source.x)
                        .attr("y1", d => d.source.y)
                        .attr("x2", d => d.target.x)
                        .attr("y2", d => d.target.y);
                    node
                        .attr("cx", d => d.x)
                        .attr("cy", d => d.y);
                    label
                        .attr("x", d => d.x)
                        .attr("y", d => d.y - (d.type === 'booking' ? 24 : 18));
                });

                // إضافة أحداث الأزرار
                document.getElementById('zoomInBtn').addEventListener('click', () => {
                    svg.transition().duration(750).call(zoom.scaleBy, 1.2);
                });

                document.getElementById('zoomOutBtn').addEventListener('click', () => {
                    svg.transition().duration(750).call(zoom.scaleBy, 0.8);
                });

                document.getElementById('panBtn').addEventListener('click', () => {
                    // تفعيل التحريك باستخدام السحب
                    svg.call(zoom);
                });

                // دالة السحب
                function drag(simulation) {
                    function dragstarted(event, d) {
                        if (!event.active) simulation.alphaTarget(0.3).restart();
                        d.fx = d.x;
                        d.fy = d.y;
                    }

                    function dragged(event, d) {
                        d.fx = event.x;
                        d.fy = event.y;
                    }

                    function dragended(event, d) {
                        if (!event.active) simulation.alphaTarget(0);
                        d.fx = null;
                        d.fy = null;
                    }
                    return d3.drag()
                        .on("start", dragstarted)
                        .on("drag", dragged)
                        .on("end", dragended);
                }
            }

            function drawFinancialNetwork(data) {
                const width = document.getElementById('financialNetwork').offsetWidth;
                const height = 400;
                const container = d3.select("#financialNetwork");
                container.selectAll("*").remove();

                // إنشاء SVG مع دعم الزووم
                const svg = container.append("svg")
                    .attr("width", width)
                    .attr("height", height);

                const g = svg.append("g"); // مجموعة لتطبيق التحويلات

                // تهيئة الزووم
                const zoom = d3.zoom()
                    .scaleExtent([0.1, 10]) // نطاق التكبير/التصغير
                    .on("zoom", (event) => {
                        g.attr("transform", event.transform);
                    });
                svg.call(zoom);

                // التحقق من البيانات
                if (!data.financial_tracking || data.financial_tracking.length === 0) {
                    g.append("text")
                        .attr("class", "text-center text-muted")
                        .attr("x", width / 2)
                        .attr("y", height / 2)
                        .attr("text-anchor", "middle")
                        .text("لا توجد بيانات لعرض الشبكة المالية.");
                    return;
                }

                // إعداد العقد (الحجوزات) ومربعات الشركات/الجهات مع تصحيح البيانات
                const bookings = data.financial_tracking.map((item, index) => ({
                    id: 'booking_' + (item.id || index),
                    name: item.client_name || 'غير معروف',
                    x: (index * 300) + 150, // زيادة المسافة إلى 300 بكسل
                    y: height / 2 + 50, // الدائرة تحتهما
                    status: item.combined_status || 'not_paid'
                }));

                const companies = bookings.map((booking, index) => {
                    const item = data.financial_tracking[index] || {};
                    return {
                        id: 'company_' + booking.id,
                        name: item.company_name || 'غير محدد', // الوصول المباشر إلى company_name
                        x: booking.x - 50, // المربع الأول يسار الزوج
                        y: booking.y - 100, // فوق الدائرة بـ 100 بكسل
                        status: item.company_payment_status || 'not_paid' // التحقق من حالة الدفع
                    };
                });

                const agents = bookings.map((booking, index) => {
                    const item = data.financial_tracking[index] || {};
                    return {
                        id: 'agent_' + booking.id,
                        name: item.agent_name || 'غير محدد', // الوصول المباشر إلى agent_name
                        x: booking.x + 50, // المربع الثاني يمين الزوج
                        y: booking.y - 100, // نفس ارتفاع الشركة
                        status: item.agent_payment_status || 'not_paid'
                    };
                });

                const nodes = [...bookings, ...companies, ...agents];
                const links = [
                    ...bookings.map(booking => ({
                        source: booking.id,
                        target: 'company_' + booking.id,
                        status: companies.find(c => c.id === 'company_' + booking.id).status
                    })),
                    ...bookings.map(booking => ({
                        source: booking.id,
                        target: 'agent_' + booking.id,
                        status: agents.find(a => a.id === 'agent_' + booking.id).status
                    }))
                ];

                // تحديد الألوان
                const color = d3.scaleOrdinal()
                    .domain(['booking', 'company', 'agent'])
                    .range(['#0d6efd', '#198754', '#fd7e14']);

                const linkColor = status => {
                    if (status === 'fully_paid') return '#198754';
                    if (status === 'partially_paid') return '#ffc107';
                    return '#dc3545';
                };

                // رسم الروابط
                const link = g.append("g")
                    .attr("stroke", "#999")
                    .selectAll("line")
                    .data(links)
                    .join("line")
                    .attr("stroke-width", 2)
                    .attr("stroke", d => linkColor(d.status))
                    .attr("x1", d => nodes.find(n => n.id === d.source).x)
                    .attr("y1", d => nodes.find(n => n.id === d.source).y)
                    .attr("x2", d => nodes.find(n => n.id === d.target).x)
                    .attr("y2", d => nodes.find(n => n.id === d.target).y);

                // رسم العقد (الأشخاص)
                const node = g.append("g")
                    .selectAll("circle")
                    .data(bookings)
                    .join("circle")
                    .attr("r", 20)
                    .attr("cx", d => d.x)
                    .attr("cy", d => d.y)
                    .attr("fill", d => color('booking'));

                // رسم المربعات (الشركات و الجهات) بجانب بعض
                const rect = g.append("g")
                    .selectAll("rect")
                    .data([...companies, ...agents])
                    .join("rect")
                    .attr("x", d => d.x - 50) // عرض المربع 100 بكسل
                    .attr("y", d => d.y - 20)
                    .attr("width", 100)
                    .attr("height", 40)
                    .attr("fill", d => color(d.id.startsWith('company_') ? 'company' : 'agent'))
                    .attr("rx", 5); // زوايا مستديرة

                // رسم التسميات
                const label = g.append("g")
                    .selectAll("text")
                    .data(nodes)
                    .join("text")
                    .attr("x", d => d.x)
                    .attr("y", d => d.y - (d.type === 'booking' ? 30 : -10))
                    .attr("text-anchor", "middle")
                    .attr("dy", 4)
                    .attr("font-size", 12)
                    .attr("font-family", "Cairo, sans-serif")
                    .text(d => d.name);

                // إضافة أحداث الأزرار
                document.getElementById('newZoomInBtn').addEventListener('click', () => {
                    svg.transition().duration(750).call(zoom.scaleBy, 1.2);
                });

                document.getElementById('newZoomOutBtn').addEventListener('click', () => {
                    svg.transition().duration(750).call(zoom.scaleBy, 0.8);
                });

                document.getElementById('newPanBtn').addEventListener('click', () => {
                    svg.call(zoom);
                });
            }
        });
    </script>
    <script>
        document.getElementById('exportExcelJsBtn').addEventListener('click', function() {
            // دالة تصدير الجدول الحالي إلى Excel باستخدام SheetJS
            const table = document.querySelector(
                '#bookings-table-container table, #bookings-table-container'); // يدعم كلا الحالتين
            if (!window.XLSX) {
                alert('لم يتم تحميل مكتبة XLSX. تأكد من تضمين المكتبة في الصفحة.');
                return;
            }
            let wb = XLSX.utils.table_to_book(table, {
                sheet: "المتابعة المالية"
            });
            const fileName = `المتابعة-المالية-${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);
        });
    </script>
@endpush
