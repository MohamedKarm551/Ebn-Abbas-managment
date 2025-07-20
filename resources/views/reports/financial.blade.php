@extends('layouts.app')

@section('title', 'بيانات المتابعة المالية')

@section('content')
    <div class="container-fluid mt-4">
        <h1 class="mb-4">بيانات المتابعة المالية للحجوزات الفندقية</h1>

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
                        <div class="col-md-3">
                            <label for="client_name" class="form-label">اسم العميل</label>
                            <input type="text" name="client_name" id="client_name" class="form-control"
                                placeholder="ابحث بالاسم">
                        </div>
                        <div class="col-md-3">
                            <label for="company_name" class="form-label">اسم الشركة</label>
                            <input type="text" name="company_name" id="company_name" class="form-control"
                                placeholder="ابحث بالاسم">
                        </div>
                        <div class="col-md-3">
                            <label for="agent_name" class="form-label">اسم الوكيل (جهة الحجز) </label>
                            <input type="text" name="agent_name" id="agent_name" class="form-control"
                                placeholder="ابحث بالاسم">
                        </div>
                        <div class="col-md-3">
                            <label for="hotel_name" class="form-label">اسم الفندق</label>
                            <input type="text" name="hotel_name" id="hotel_name" class="form-control"
                                placeholder="ابحث بالاسم">
                        </div>
                        <div class="col-md-2">
                            <label for="currency" class="form-label">العملة</label>
                            <select name="currency" id="currency" class="form-select">
                                <option value="">الكل</option>
                                <option value="SAR">SAR</option>
                                <option value="KWD">KWD</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priority_level" class="form-label">مستوى الأولوية</label>
                            <select name="priority_level" id="priority_level" class="form-select">
                                <option value="">الكل</option>
                                <option value="high">عالية</option>
                                <option value="medium">متوسطة</option>
                                <option value="low">منخفضة</option>
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
                <div id="trackingPagination"></div>
                <div class="loading-overlay d-none" id="loading">
                    <div class="spinner-border text-primary"></div>
                    <span class="ms-2">جاري التحميل...</span>
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



    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            function loadTrackingData(page = 1) {
                const formData = new FormData(filterForm);
                formData.append('page', page);
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

                        updateTable(data.financial_tracking, data.pagination);

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
            let lastPagination = null;

            function updateTable(data, pagination = null) {
                const trackingBody = document.getElementById('trackingBody');
                const paginationContainer = document.getElementById('trackingPagination');

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
                    const rowClass = item.combined_status === 'fully_paid' ? 'table-success' :
                        item.combined_status === 'partially_paid' ? 'table-warning' : 'table-danger';

                    const companyStatusBadge = getStatusBadge(item.company_payment_status);
                    const agentStatusBadge = getStatusBadge(item.agent_payment_status);
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
                if (pagination && pagination.last_page > 1) {
                    lastPagination = pagination;
                    paginationContainer.innerHTML = renderPaginationLinks(pagination);
                    document.querySelectorAll('.pagination-link').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            if (!this.classList.contains('active') && !this.classList.contains(
                                    'disabled')) {
                                loadTrackingData(this.dataset.page);
                            }
                        });
                    });
                } else if (paginationContainer) {
                    paginationContainer.innerHTML = '';
                }
            }

            // دالة رسم روابط الترقيم
            function renderPaginationLinks(pagination) {
                let html = `
                    <div class="pagination-container">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-2 gap-2">
                            <div class="pagination-info order-2 order-md-1 text-center text-md-start">
                                <p class="mb-0">
                                    عرض
                                    <strong>${pagination.from}</strong>
                                    إلى
                                    <strong>${pagination.to}</strong>
                                    من
                                    <strong>${pagination.total}</strong>
                                    سجل
                                </p>
                            </div>
                            <nav class="order-1 order-md-2">
                                <ul class="pagination mb-0">
                `;
                for (let i = 1; i <= pagination.last_page; i++) {
                    html += `<li class="page-item${pagination.current_page === i ? ' active' : ''}">
                        <a href="#" class="page-link pagination-link" data-page="${i}">${i}</a>
                    </li>`;
                }
                html += `
                                </ul>
                            </nav>
                        </div>
                    </div>
                `;
                return html;
            }

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

            // رسم الرسومات البيانية
            function drawCharts(data) {
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
                container.selectAll("*").remove();

                const svg = container.append("svg")
                    .attr("width", width)
                    .attr("height", height);
                const g = svg.append("g");

                const zoom = d3.zoom()
                    .scaleExtent([0.1, 10])
                    .on("zoom", (event) => {
                        g.attr("transform", event.transform);
                    });
                svg.call(zoom);

                if (!data.financial_tracking || data.financial_tracking.length === 0) {
                    g.append("text")
                        .attr("class", "text-center text-muted")
                        .attr("x", width / 2)
                        .attr("y", height / 2)
                        .attr("text-anchor", "middle")
                        .text("لا توجد بيانات لعرض الشبكة المالية.");
                    return;
                }

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
                        status: item.company_payment_status
                    });
                    links.push({
                        source: 'booking_' + item.id,
                        target: 'agent_' + item.agent_name,
                        status: item.agent_payment_status
                    });
                });

                const color = d3.scaleOrdinal()
                    .domain(['booking', 'company', 'agent'])
                    .range(['#0d6efd', '#198754', '#fd7e14']);
                const linkColor = status => {
                    if (status === 'fully_paid') return '#198754';
                    if (status === 'partially_paid') return '#ffc107';
                    return '#dc3545';
                };

                const simulation = d3.forceSimulation(nodes)
                    .force("link", d3.forceLink(links).id(d => d.id).distance(90).strength(1))
                    .force("charge", d3.forceManyBody().strength(-250))
                    .force("center", d3.forceCenter(width / 2, height / 2));

                const link = g.append("g")
                    .attr("stroke", "#999")
                    .selectAll("line")
                    .data(links)
                    .join("line")
                    .attr("stroke-width", 2)
                    .attr("stroke", d => linkColor(d.status));
                const node = g.append("g")
                    .selectAll("circle")
                    .data(nodes)
                    .join("circle")
                    .attr("r", d => d.type === 'booking' ? 18 : 14)
                    .attr("fill", d => color(d.type))
                    .call(drag(simulation));
                const label = g.append("g")
                    .selectAll("text")
                    .data(nodes)
                    .join("text")
                    .attr("text-anchor", "middle")
                    .attr("dy", 4)
                    .attr("font-size", 12)
                    .attr("font-family", "Cairo, sans-serif")
                    .text(d => d.name);

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

                document.getElementById('zoomInBtn').addEventListener('click', () => {
                    svg.transition().duration(750).call(zoom.scaleBy, 1.2);
                });
                document.getElementById('zoomOutBtn').addEventListener('click', () => {
                    svg.transition().duration(750).call(zoom.scaleBy, 0.8);
                });
                document.getElementById('panBtn').addEventListener('click', () => {
                    svg.call(zoom);
                });

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

            function drawFinancialNetwork(data, options = {}) {
                // الفلاتر - القيم الافتراضية أو المرسلة
                const selectedStatus = options.selectedStatus || "all";
                const selectedPriority = options.selectedPriority || "all";

                // تحديد الكونتينر وحساب المساحة
                const container = d3.select("#financialNetwork");
                const width = container.node().offsetWidth;
                const height = 580;
                container.selectAll("*").remove();

                // رسم الفلاتر أعلى الرسم الشبكي
                let filtersDiv = d3.select("#financialNetworkFilters");
                if (filtersDiv.empty()) {
                    filtersDiv = container.insert("div", ":first-child")
                        .attr("id", "financialNetworkFilters")
                        .attr("class", "mb-3 d-flex gap-2 align-items-center");
                } else {
                    filtersDiv.html("");
                }
                // فلاتر الحالة
                filtersDiv.append("span").attr("class", "fw-bold ms-2").text("تصفية:");
                filtersDiv.append("select")
                    .attr("id", "fn-status-filter")
                    .attr("class", "form-select form-select-sm mx-2").style("width", "150px")
                    .html(`
            <option value="all">كل الحالات</option>
            <option value="fully_paid">مدفوع بالكامل</option>
            <option value="partially_paid">مدفوع جزئياً</option>
            <option value="not_paid">غير مدفوع</option>
        `)
                    .property("value", selectedStatus);
                // فلاتر الأولوية
                filtersDiv.append("select")
                    .attr("id", "fn-priority-filter")
                    .attr("class", "form-select form-select-sm mx-2").style("width", "120px")
                    .html(`
            <option value="all">كل الأولويات</option>
            <option value="high">عالية</option>
            <option value="medium">متوسطة</option>
            <option value="low">منخفضة</option>
        `)
                    .property("value", selectedPriority);

                // عند تغيير الفلاتر، أعد رسم الرسم الشبكي بفلاتر جديدة
                filtersDiv.select("#fn-status-filter").on("change", function() {
                    drawFinancialNetwork(data, {
                        selectedStatus: this.value,
                        selectedPriority
                    });
                });
                filtersDiv.select("#fn-priority-filter").on("change", function() {
                    drawFinancialNetwork(data, {
                        selectedStatus,
                        selectedPriority: this.value
                    });
                });

                // الفلاتر البرمجية
                let bookingsRaw = data.financial_tracking || [];
                if (selectedStatus !== "all") {
                    bookingsRaw = bookingsRaw.filter(item => (item.combined_status || 'not_paid') ===
                        selectedStatus);
                }
                if (selectedPriority !== "all") {
                    bookingsRaw = bookingsRaw.filter(item => (item.priority_level || 'medium') ===
                    selectedPriority);
                }
                if (!bookingsRaw.length) {
                    container.selectAll("svg").remove();
                    container.append("div").attr("class", "text-center text-muted py-5").style("font-size", "22px")
                        .text("لا توجد بيانات تطابق معايير التصفية.");
                    return;
                }

                // رسم الـ svg الأساسي
                const svg = container.append("svg")
                    .attr("width", "100%")
                    .attr("height", height)
                    .attr("viewBox", `0 0 ${width} ${height}`)
                    .style("background", "#f7f9fb")
                    .style("border-radius", "20px");
                const g = svg.append("g");

                // Zoom مع إعادة الضبط
                const zoom = d3.zoom()
                    .scaleExtent([0.5, 6])
                    .on("zoom", (event) => g.attr("transform", event.transform));
                svg.call(zoom);

                d3.select("#newZoomInBtn").on("click", () => {
                    svg.transition().duration(300).call(zoom.scaleBy, 1.18);
                });
                d3.select("#newZoomOutBtn").on("click", () => {
                    svg.transition().duration(300).call(zoom.scaleBy, 0.85);
                });
                d3.select("#newPanBtn").on("click", () => {
                    svg.transition().duration(300).call(zoom.transform, d3.zoomIdentity);
                });

                // تقسيم كل خمسة بجانب بعضهم في سطر (صفوف)
                const perRow = 5;
                const spacingX = 270;
                const spacingY = 200;
                const n = bookingsRaw.length;
                const numRows = Math.ceil(n / perRow);

                // دعم الألوان للأولوية والحالة
                const typeColor = {
                    booking: "#3b82f6",
                    company: "#22c55e",
                    agent: "#f59e42"
                };
                const statusColor = {
                    fully_paid: "#22c55e",
                    partially_paid: "#facc15",
                    not_paid: "#ef4444"
                };
                const priorityColor = {
                    high: "#ef4444",
                    medium: "#facc15",
                    low: "#3b82f6"
                };

                // إعداد العقد (Bookings مع بيانات إضافية)
                const bookings = bookingsRaw.map((item, i) => {
                    const row = Math.floor(i / perRow);
                    const col = i % perRow;
                    const itemsInRow = (row === numRows - 1) ? n - perRow * row : perRow;
                    const totalRowWidth = (itemsInRow - 1) * spacingX;
                    const xOffset = (width - totalRowWidth) / 2;
                    return {
                        id: `booking_${item.id ?? i}`,
                        type: "booking",
                        name: item.client_name ?? "غير معروف",
                        hotel: item.hotel_name ?? "",
                        check_in: item.check_in ?? "",
                        check_out: item.check_out ?? "",
                        booking_number: item.booking_number || item.id || (i + 1),
                        status: item.combined_status ?? "not_paid",
                        priority: item.priority_level ?? "medium",
                        x: xOffset + col * spacingX,
                        y: (row * spacingY) + 140,
                        raw: item // كل بيانات الحجز نفسها لاستخدامها لاحقًا
                    }
                });

                const companies = bookings.map((b, i) => ({
                    id: `company_${b.id}`,
                    type: "company",
                    name: bookingsRaw[i]?.company_name ?? "غير محدد",
                    x: b.x - 70,
                    y: b.y - 100,
                    status: bookingsRaw[i]?.company_payment_status ?? "not_paid"
                }));

                const agents = bookings.map((b, i) => ({
                    id: `agent_${b.id}`,
                    type: "agent",
                    name: bookingsRaw[i]?.agent_name ?? "غير محدد",
                    x: b.x + 70,
                    y: b.y - 100,
                    status: bookingsRaw[i]?.agent_payment_status ?? "not_paid"
                }));

                const nodes = [...bookings, ...companies, ...agents];
                const links = [
                    ...bookings.map((b, i) => ({
                        source: b.id,
                        target: `company_${b.id}`,
                        status: companies[i]?.status
                    })),
                    ...bookings.map((b, i) => ({
                        source: b.id,
                        target: `agent_${b.id}`,
                        status: agents[i]?.status
                    }))
                ];

                const getStatusColor = s => statusColor[s] ?? "#9ca3af";
                const getPriorityColor = p => priorityColor[p] ?? "#aaa";

                // خطوط الشبكة
                g.append("g").selectAll("path")
                    .data(links)
                    .join("path")
                    .attr("d", d => {
                        const s = nodes.find(n => n.id === d.source);
                        const t = nodes.find(n => n.id === d.target);
                        return `M${s.x},${s.y} Q${(s.x + t.x) / 2},${s.y - 60} ${t.x},${t.y}`;
                    })
                    .attr("stroke", d => getStatusColor(d.status))
                    .attr("stroke-width", 3)
                    .attr("fill", "none")
                    .attr("opacity", 0.8);

                // Tooltip مطور
                const tooltip = container.append("div")
                    .style("position", "absolute")
                    .style("background", "#fff")
                    .style("padding", "14px 18px")
                    .style("border-radius", "13px")
                    .style("box-shadow", "0 4px 24px #0002")
                    .style("font-family", "Cairo, sans-serif")
                    .style("font-size", "15px")
                    .style("color", "#222")
                    .style("pointer-events", "none")
                    .style("opacity", 0);

                // رسم الدوائر (الحجوزات) مع تلوين حسب الأولوية أو الحالة
                g.append("g").selectAll("circle")
                    .data(bookings)
                    .join("circle")
                    .attr("cx", d => d.x)
                    .attr("cy", d => d.y)
                    .attr("r", 28)
                    .attr("fill", d => getPriorityColor(d.priority))
                    .attr("stroke", d => getStatusColor(d.status))
                    .attr("stroke-width", 3)
                    .attr("filter", "drop-shadow(0px 2px 10px #0002)")
                    .style("cursor", "pointer")
                    .on("mouseover", (e, d) => {
                        d3.select(e.currentTarget).attr("stroke", "#333").attr("stroke-width", 4);
                        tooltip.style("opacity", 1).html(
                            `
                <div style="font-size:16px;font-weight:bold;color:#2563eb;white-space:normal;">${d.name}</div>
                <div style="margin:2px 0 3px 0">
                    <span style="color:#777">من:</span> <span style="color:#198754">${d.check_in || '-'}</span>
                    <span style="color:#888">إلى</span> <span style="color:#dc3545">${d.check_out || '-'}</span>
                </div>
                ${d.hotel ? `<div style="color:#666;font-size:14px">الفندق: ${d.hotel}</div>` : ''}
                <div style="margin-top:4px">
                    <span style="padding:2px 8px;border-radius:7px;background:${getStatusColor(d.status)}20;color:${getStatusColor(d.status)};font-weight:600;font-size:14px">${statusText(d.status)}</span>
                </div>
                <div style="margin-top:3px">
                    <span style="padding:2px 8px;border-radius:7px;background:${getPriorityColor(d.priority)}20;color:${getPriorityColor(d.priority)};font-weight:600;font-size:13px">${priorityText(d.priority)}</span>
                </div>
                `
                        );
                    })
                    .on("mousemove", e => {
                        tooltip.style("left", (e.pageX + 18) + "px").style("top", (e.pageY - 14) + "px");
                    })
                    .on("mouseleave", e => {
                        d3.select(e.currentTarget).attr("stroke", getStatusColor(d3.select(e.currentTarget)
                            .datum().status)).attr("stroke-width", 3);
                        tooltip.style("opacity", 0);
                    })
                    .on("click", (e, d) => {
                        showBookingDetailsSidebar(d.raw); // سيعرض سلايدر التفاصيل الجانبي
                    });

                // رقم الحجز أو كود الحجز داخل الدائرة
                g.append("g").selectAll("text.booking_number")
                    .data(bookings)
                    .join("text")
                    .attr("class", "booking_number")
                    .attr("x", d => d.x)
                    .attr("y", d => d.y + 7)
                    .attr("text-anchor", "middle")
                    .attr("font-family", "Cairo, sans-serif")
                    .attr("font-size", "15px")
                    .attr("fill", "#fff")
                    .attr("font-weight", 800)
                    .text(d => d.booking_number);

                // شارة حالة الدفع أعلى الدائرة مباشرة
                g.append("g").selectAll("rect.status_badge")
                    .data(bookings)
                    .join("rect")
                    .attr("class", "status_badge")
                    .attr("x", d => d.x - 38)
                    .attr("y", d => d.y - 48)
                    .attr("width", 76)
                    .attr("height", 19)
                    .attr("rx", 9)
                    .attr("fill", d => getStatusColor(d.status))
                    .attr("opacity", 0.94);
                g.append("g").selectAll("text.status_badge")
                    .data(bookings)
                    .join("text")
                    .attr("x", d => d.x)
                    .attr("y", d => d.y - 34)
                    .attr("text-anchor", "middle")
                    .attr("font-family", "Cairo, sans-serif")
                    .attr("font-size", "12px")
                    .attr("fill", "#fff")
                    .attr("font-weight", 700)
                    .text(d => statusText(d.status));

                // اسم العميل أعلى الدائرة
                g.append("g").selectAll("text.booking_name")
                    .data(bookings)
                    .join("text")
                    .attr("class", "booking_name")
                    .attr("x", d => d.x)
                    .attr("y", d => d.y - 64)
                    .attr("text-anchor", "middle")
                    .attr("font-family", "Cairo, sans-serif")
                    .attr("font-size", "15px")
                    .attr("fill", "#334155")
                    .attr("font-weight", 700)
                    .style("pointer-events", "none")
                    .style("white-space", "pre-line")
                    .text(d => d.name.length > 22 ? d.name.slice(0, 20) + "..." : d.name);

                // التواريخ أسفل الدائرة
                g.append("g").selectAll("text.dates")
                    .data(bookings)
                    .join("text")
                    .attr("class", "dates")
                    .attr("x", d => d.x)
                    .attr("y", d => d.y + 48)
                    .attr("text-anchor", "middle")
                    .attr("font-family", "Cairo, sans-serif")
                    .attr("font-size", "12px")
                    .attr("fill", "#999")
                    .attr("font-weight", 400)
                    .text(d => d.check_in && d.check_out ? `(${d.check_in} — ${d.check_out})` : '');

                // مستطيلات الشركة/الوكيل
                g.append("g").selectAll("rect")
                    .data([...companies, ...agents])
                    .join("rect")
                    .attr("x", d => d.x - 55)
                    .attr("y", d => d.y - 28)
                    .attr("width", 110)
                    .attr("height", 36)
                    .attr("rx", 11)
                    .attr("fill", d => typeColor[d.type])
                    .attr("filter", "drop-shadow(0px 2px 8px #0001)")
                    .style("cursor", "pointer")
                    .on("mouseover", (e, d) => {
                        d3.select(e.currentTarget).attr("stroke", "#fff").attr("stroke-width", 3);
                        tooltip.style("opacity", 1).html(
                            `<b>${d.type === "company" ? "شركة" : "وكيل"}:</b> ${d.name}<br>
                <span style="padding:2px 7px;border-radius:7px;background:${getStatusColor(d.status)}20;color:${getStatusColor(d.status)};font-size:14px">${statusText(d.status)}</span>`
                        );
                    })
                    .on("mousemove", e => {
                        tooltip.style("left", (e.pageX + 18) + "px").style("top", (e.pageY - 14) + "px");
                    })
                    .on("mouseleave", e => {
                        d3.select(e.currentTarget).attr("stroke", null);
                        tooltip.style("opacity", 0);
                    });

                // نص الشركات والوكلاء
                g.append("g").selectAll("text.company")
                    .data([...companies, ...agents])
                    .join("text")
                    .attr("class", "company")
                    .attr("x", d => d.x)
                    .attr("y", d => d.y - 38)
                    .attr("text-anchor", "middle")
                    .attr("font-family", "Cairo, sans-serif")
                    .attr("font-size", "13px")
                    .attr("fill", "#2d3748")
                    .attr("font-weight", 600)
                    .text(d => d.name.length > 22 ? d.name.slice(0, 20) + "..." : d.name);

                // إعادة الرسم عند تغيير حجم الشاشة
                window.addEventListener('resize', () => {
                    drawFinancialNetwork(data, {
                        selectedStatus,
                        selectedPriority
                    });
                }, {
                    once: true
                });

                // Helper - حالة الدفع بالعربي
                function statusText(status) {
                    switch (status) {
                        case 'fully_paid':
                            return 'مدفوع بالكامل';
                        case 'partially_paid':
                            return 'مدفوع جزئياً';
                        case 'not_paid':
                            return 'غير مدفوع';
                        default:
                            return 'غير محدد';
                    }
                }
                // Helper - أولوية بالعربي
                function priorityText(level) {
                    switch (level) {
                        case 'high':
                            return 'عالية';
                        case 'medium':
                            return 'متوسطة';
                        case 'low':
                            return 'منخفضة';
                        default:
                            return 'غير محدد';
                    }
                }

                // Sidebar - تفاصيل الحجز عند الضغط
                function showBookingDetailsSidebar(item) {
                    // احذف أي سايدبار قديم
                    d3.select("#bookingSidebar").remove();
                    // أنشئ عنصر سايدبار
                    const sidebar = d3.select("body").append("div")
                        .attr("id", "bookingSidebar")
                        .style("position", "fixed")
                        .style("top", "0").style("right", "0")
                        .style("width", "350px").style("height", "100vh")
                        .style("background", "#fff").style("box-shadow", "-2px 0 20px #0002")
                        .style("z-index", "9999").style("padding", "32px 20px 20px 20px")
                        .style("transition", "right 0.3s").style("font-family", "Cairo, sans-serif");

                    // زر الإغلاق
                    sidebar.append("button")
                        .attr("class", "btn btn-link position-absolute top-0 end-0 mt-2 me-2")
                        .html('<i class="fa fa-times fa-lg"></i>')
                        .style("font-size", "1.5rem")
                        .on("click", () => sidebar.remove());

                    // المحتوى التفصيلي
                    console.log(item);

                    sidebar.append("h4").attr("class", "mb-3 mt-2").text(item.client_name ?? "عميل");
                    sidebar.append("div").attr("class", "mb-2")
                        .html(`<b>الفندق:</b> ${item.hotel_name || "-"}<br>
                   <b>تاريخ الدخول:</b> ${item.check_in || "-"}<br>
                   <b>تاريخ الخروج:</b> ${item.check_out || "-"}<br>
                   <b>رقم الحجز:</b> ${item.booking_number || item.id || "-"}<br>
                   <b>حالة الدفع:</b> <span style="color:${getStatusColor(item.combined_status)}">${statusText(item.combined_status)}</span><br>
                   <b>مستوى الأولوية:</b> <span style="color:${getPriorityColor(item.priority_level)}">${priorityText(item.priority_level)}</span><br>
                   <b>الشركة:</b> ${item.company_name || "-"}<br>
                   <b>جهة الحجز:</b> ${item.agent_name || "-"}<br>
                   <b>ملاحظات على الحجز:</b> <span style="color:#007bff">${item.notes || "-"}</span><br>
                   <b>ملاحظات تسديد الشركة:</b> <span style="color:#22c55e">${item.company_payment_notes || "-"}</span><br>
                   <b>ملاحظات تسديد الجهة:</b> <span style="color:#fd7e14">${item.agent_payment_notes || "-"}</span>`);
                    // أي حقول إضافية
                }
            }





        });

        document.getElementById('exportExcelJsBtn').addEventListener('click', function() {
            const table = document.querySelector('#bookings-table-container table, #bookings-table-container');
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
