@extends('layouts.app')

@section('title', 'مراقبة الألوتمنت')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">متابعة حالة الألوتمنت</h6>
            </div>
            <div class="card-body">
                <!-- فلترة البيانات -->
                <form method="GET" action="{{ route('allotments.monitor') }}" class="row mb-4 align-items-end">
                    <div class="col-md-3">
                        <label for="start_date">من تاريخ</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">إلى تاريخ</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                            value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label for="hotel_id">الفندق</label>
                        <select name="hotel_id" id="hotel_id" class="form-control">
                            <option value="">كل الفنادق</option>
                            @foreach (App\Models\Hotel::all() as $hotel)
                                <option value="{{ $hotel->id }}" {{ $selectedHotelId == $hotel->id ? 'selected' : '' }}>
                                    {{ $hotel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
                    </div>
                </form>

                <!-- القسم الأول: الرسم البياني -->
                <div class="mb-5">
                    <h5>الرسم البياني للألوتمنت</h5>
                    <canvas id="allotmentChart" height="200"></canvas>
                </div>

                <!-- القسم الثاني: جدول التفاصيل -->
                <div class="table-responsive">
                    <table id="allotmentTable" class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>الفندق</th>
                                <!-- سيتم إضافة أعمدة التواريخ عبر JavaScript -->
                            </tr>
                        </thead>
                        <tbody>
                            <!-- سيتم إضافة بيانات الجدول عبر JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>الفندق</th>
                                <th>من</th>
                                <th>إلى</th>
                                <th>عدد الغرف</th>
                                <th>المتاح</th>
                                <th>المباع</th>
                                <th>السعر</th>
                                <th>العملة</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allotments as $allotment)
                                <tr>
                                    <td>{{ $allotment->hotel->name }}</td>
                                    <td>{{ $allotment->start_date->format('Y-m-d') }}</td>
                                    <td>{{ $allotment->end_date->format('Y-m-d') }}</td>
                                    <td>{{ $allotment->rooms_count }}</td>
                                    <td><span
                                            class="badge {{ $allotment->remaining_rooms > 0 ? 'bg-success' : 'bg-danger' }}">{{ $allotment->remaining_rooms }}</span>
                                    </td>
                                    <td>{{ $allotment->sold_rooms }}</td>
                                    <td>{{ $allotment->rate_per_room }}</td>
                                    <td>{{ $allotment->currency }}</td>
                                    <td><span
                                            class="badge {{ $allotment->status == 'active' ? 'bg-success' : 'bg-danger' }}">{{ $allotment->status == 'active' ? 'نشط' : 'ملغي' }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('allotments.show', $allotment->id) }}"
                                                class="btn btn-sm btn-info" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('allotments.edit', $allotment->id) }}"
                                                class="btn btn-sm btn-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('allotments.destroy', $allotment->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الألوتمنت؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* تنسيقات لتجميل الجدول والرسم البياني */
        .table-hover-cell:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .allotment-cell {
            min-width: 120px;
            text-align: center;
        }

        .hotel-cell {
            font-weight: bold;
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
        }

        .date-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            height: 120px;
            white-space: nowrap;
            text-align: left;
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .badge-available {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: black;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge {
            padding: 0.25em 0.5em;
            font-size: 0.9em;
            border-radius: 0.25rem;
            display: inline-block;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // بيانات من Laravel
        const allotmentData = @json($allotments);
        const salesData = @json($sales);
        const startDate = "{{ $startDate }}";
        const endDate = "{{ $endDate }}";

        // تجهيز مصفوفة التواريخ
        const dateRange = [];
        let currentDate = new Date(startDate);
        const lastDate = new Date(endDate);

        while (currentDate <= lastDate) {
            dateRange.push(currentDate.toISOString().split('T')[0]);
            currentDate.setDate(currentDate.getDate() + 1);
        }

        // تجهيز البيانات لكل فندق
        const hotels = {};

        // تجهيز بيانات الألوتمنت
        allotmentData.forEach(allotment => {
            const hotelId = allotment.hotel_id;
            const hotelName = allotment.hotel.name;

            if (!hotels[hotelId]) {
                hotels[hotelId] = {
                    id: hotelId,
                    name: hotelName,
                    dates: {}
                };

                // تهيئة كل التواريخ بقيم صفرية
                dateRange.forEach(date => {
                    hotels[hotelId].dates[date] = {
                        allotted: 0,
                        sold: 0,
                        available: 0
                    };
                });
            }

            // حساب عدد الغرف المتاحة لكل يوم
            const allotmentStart = new Date(allotment.start_date);
            const allotmentEnd = new Date(allotment.end_date);

            dateRange.forEach(date => {
                const currentDate = new Date(date);
                if (currentDate >= allotmentStart && currentDate <= allotmentEnd) {
                    hotels[hotelId].dates[date].allotted += parseInt(allotment.rooms_count);
                }
            });
        });

        // تجهيز بيانات المبيعات
        salesData.forEach(sale => {
            const hotelId = sale.hotel_id;
            if (!hotels[hotelId]) return; // تخطي إذا لم يكن الفندق موجودًا

            const checkIn = new Date(sale.check_in);
            const checkOut = new Date(sale.check_out);

            dateRange.forEach(date => {
                const currentDate = new Date(date);
                if (currentDate >= checkIn && currentDate < checkOut) {
                    hotels[hotelId].dates[date].sold += parseInt(sale.rooms_sold);
                }
            });
        });

        // حساب الغرف المتاحة
        Object.values(hotels).forEach(hotel => {
            dateRange.forEach(date => {
                hotel.dates[date].available = hotel.dates[date].allotted - hotel.dates[date].sold;
            });
        });

        // بناء جدول العرض
        function buildAllotmentTable() {
            const table = document.getElementById('allotmentTable');
            const headerRow = table.querySelector('thead tr');

            // إضافة أعمدة التواريخ للـ header
            // إضافة أعمدة التواريخ للـ header (هجري + ميلادي)
            dateRange.forEach(date => {
                const d = new Date(date);

                // التاريخ بالهجري (العربي - تقويم سعودي)
                const hijri = d.toLocaleDateString('ar-SA', {
                    month: 'short',
                    day: 'numeric'
                });

                // التاريخ الميلادي (Gregorian)
                const gregorian = d.toLocaleDateString('en-GB', {
                    month: 'short',
                    day: 'numeric'
                });

                const th = document.createElement('th');
                th.className = 'date-header';
                th.innerHTML = `
        <div>${hijri}</div>
        <div class="text-muted small">${gregorian}</div>
    `;
                headerRow.appendChild(th);
            });


            // إضافة صفوف الفنادق وبيانات الغرف
            const tableBody = table.querySelector('tbody');

            Object.values(hotels).forEach(hotel => {
                const row = document.createElement('tr');

                // إضافة خلية اسم الفندق
                const hotelCell = document.createElement('td');
                hotelCell.className = 'hotel-cell';
                hotelCell.textContent = hotel.name;
                row.appendChild(hotelCell);

                // إضافة خلية لكل تاريخ
                dateRange.forEach(date => {
                    const cell = document.createElement('td');
                    cell.className = 'allotment-cell table-hover-cell';

                    const allotted = hotel.dates[date].allotted;
                    const sold = hotel.dates[date].sold;
                    const available = hotel.dates[date].available;

                    // تحديد حالة الخلية
                    let badgeClass = 'badge-available';
                    if (available <= 0) {
                        badgeClass = 'badge-danger';
                    } else if (available <= allotted * 0.2) {
                        badgeClass = 'badge-warning';
                    }

                    cell.innerHTML = `
                    <div class="badge ${badgeClass}" title="المتاح">${available}</div>
                    <div class="small mt-1">
                        <div>الألوتمنت: ${allotted}</div>
                        <div>المباع: ${sold}</div>
                    </div>
                `;

                    row.appendChild(cell);
                });

                tableBody.appendChild(row);
            });
        }

        // بناء الرسم البياني
        // بناء الرسم البياني (Cells) — 3 صفوف: المتاح / المباع / الألوتمنت
        // ✅ كل قيمة في سيل مستقل وبداخلها الرقم
        // ✅ بدون Scroll لا نهائي: نلزّم الرسم بعدد أيام أقصى + حجم ثابت للحاوية
        function buildAllotmentChart() {

            /* =========================================================
               0) إعدادات “إلزام” حتى لا يرسم كتير (مهم جدًا)
               ========================================================= */

            // أقصى عدد أيام نعرضهم في الرسم (غيّره حسب احتياجك)
            // مثال: لو الفلتر شهرين، هنعرض آخر 35 يوم فقط لتفادي الرسم الضخم
            const MAX_DAYS = 35;

            // حجم السيل (لو الأيام كتير صغّره)
            const CELL_SIZE = 26;

            // مسافة بين الصفوف (بسيطة)
            const ROW_GAP = 6;

            /* =========================================================
               1) جلب الـ canvas والتحقق
               ========================================================= */
            const canvas = document.getElementById('allotmentChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            // Destroy old chart (ضروري لمنع تكرار الرسم)
            if (window._allotmentChart) window._allotmentChart.destroy();

            /* =========================================================
               2) Helpers
               ========================================================= */
            const n = (v) => (Number.isFinite(Number(v)) ? Number(v) : 0);

            // "YYYY-MM-DD" → "D-M" بدون سنة
            const formatDM = (iso) => {
                const d = new Date(iso);
                return `${d.getUTCDate()}-${d.getUTCMonth() + 1}`;
            };

            /* =========================================================
               3) تجميع يومي لكل الفنادق (Totals)
               ========================================================= */
            const daily = {};
            Object.values(hotels).forEach((hotel) => {
                Object.entries(hotel.dates).forEach(([date, data]) => {
                    if (!daily[date]) daily[date] = {
                        allotted: 0,
                        sold: 0
                    };
                    daily[date].allotted += n(data.allotted);
                    daily[date].sold += n(data.sold);
                });
            });

            let isoDates = Object.keys(daily).sort();
            if (!isoDates.length) return;

            /* =========================================================
               4) إلزام الرسم بعدد أيام محدد (منع التمدد والرسم الكثير)
               ========================================================= */

            // لو الأيام أكثر من MAX_DAYS، نأخذ آخر MAX_DAYS فقط
            if (isoDates.length > MAX_DAYS) {
                isoDates = isoDates.slice(-MAX_DAYS);
            }

            // labels للأيام (D-M)
            const labels = isoDates.map(formatDM);

            /* =========================================================
               5) تجهيز نقاط المصفوفة (Matrix) — 3 صفوف
                  y = 0 → المتاح
                  y = 1 → المباع
                  y = 2 → الألوتمنت
               ========================================================= */

            // نستخدم 3 datasets بدل dataset واحد
            // علشان نقدر:
            // - نخلي لكل صف لون مختلف
            // - نخلي كل سيل فيها رقم مستقل
            // - نسيطر على tooltip بسهولة

            const availablePoints = [];
            const soldPoints = [];
            const allottedPoints = [];

            isoDates.forEach((iso, x) => {
                const allotted = n(daily[iso].allotted);
                const sold = n(daily[iso].sold);
                const available = allotted - sold;

                // صف الألوتمنت (y=0) - الأول
                allottedPoints.push({
                    x,
                    y: 0,
                    iso,
                    value: allotted,
                    allotted,
                    sold,
                    available
                });

                // صف المباع (y=1) - الثاني  
                soldPoints.push({
                    x,
                    y: 1,
                    iso,
                    value: sold,
                    allotted,
                    sold,
                    available
                });

                // صف المتاح (y=2) - الثالث
                availablePoints.push({
                    x,
                    y: 2,
                    iso,
                    value: available,
                    allotted,
                    sold,
                    available
                });
            });

            /* =========================================================
               6) قواعد ألوان بسيطة (قريبة من الشيت)
               ========================================================= */

            // لون المتاح حسب الحالة
            function availableColor(p) {
                // مفيش ألوتمنت ومفيش بيع
                if (p.allotted === 0 && p.sold === 0) return 'rgba(200,200,200,0.35)';

                // عجز أو صفر
                if (p.available <= 0) return 'rgba(255,99,132,0.75)';

                // تحذير: متاح قليل (<= 20%)
                if (p.allotted > 0 && p.available <= p.allotted * 0.2) return 'rgba(255,206,86,0.85)';

                // طبيعي
                return 'rgba(75,192,192,0.75)';
            }

            // لون المباع (ثابت)
            function soldColor(p) {
                // لو مفيش بيع خليه خفيف
                if (p.value === 0) return 'rgba(255,99,132,0.15)';
                return 'rgba(255,99,132,0.65)';
            }

            // لون الألوتمنت (ثابت)
            function allottedColor(p) {
                if (p.value === 0) return 'rgba(54,162,235,0.15)';
                return 'rgba(54,162,235,0.65)';
            }

            /* =========================================================
               7) Plugin لكتابة الرقم داخل كل Cell
               ========================================================= */
            const drawCellNumbers = {
                id: 'drawCellNumbers',
                afterDatasetsDraw(chart, args, pluginOptions) {
                    const {
                        ctx
                    } = chart;
                    ctx.save();

                    // إعداد الخط المحسن
                    ctx.font = 'bold 10px Arial';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        const meta = chart.getDatasetMeta(datasetIndex);

                        meta.data.forEach((element, index) => {
                            const dataPoint = dataset.data[index];
                            if (!dataPoint || dataPoint.value === undefined) return;

                            // الحصول على إحداثيات المنتصف من العنصر
                            const {
                                x,
                                y
                            } = element.getCenterPoint();

                            // تحديد لون النص بناء على لون الخلفية
                            const bgColor = dataset.backgroundColor;
                            ctx.fillStyle = 'rgba(255,255,255,0.9)'; // أبيض شفاف
                            ctx.strokeStyle = 'rgba(0,0,0,0.7)'; // حدود سوداء
                            ctx.lineWidth = 2;

                            // رسم النص مع حدود للوضوح
                            const text = String(dataPoint.value);
                            ctx.strokeText(text, x, y);
                            ctx.fillText(text, x, y);
                        });
                    });

                    ctx.restore();
                }
            };

            /* =========================================================
               8) إنشاء الرسم (Matrix) — 3 صفوف
               ========================================================= */
            window._allotmentChart = new Chart(ctx, {
                type: 'matrix',
                data: {
                    datasets: [{
                            // صف الألوتمنت
                            label: 'الألوتمنت',
                            data: allottedPoints,
                            width: () => CELL_SIZE,
                            height: () => CELL_SIZE,
                            backgroundColor: (c) => allottedColor(c.raw),
                            borderColor: 'rgba(0,0,0,0.10)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            // صف المباع
                            label: 'المباع',
                            data: soldPoints,
                            width: () => CELL_SIZE,
                            height: () => CELL_SIZE,
                            backgroundColor: (c) => soldColor(c.raw),
                            borderColor: 'rgba(0,0,0,0.10)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            // صف المتاح
                            label: 'المتاح',
                            data: availablePoints,
                            width: () => CELL_SIZE,
                            height: () => CELL_SIZE,
                            backgroundColor: (c) => availableColor(c.raw),
                            borderColor: 'rgba(0,0,0,0.10)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }

                    ]
                },
                options: {
                    responsive: true,

                    // مهم جدًا لمنع التمدد/الـ scroll الغريب
                    maintainAspectRatio: true,

                    // كل ما تزودها يقل ارتفاع الرسم (جرب 3.2 / 4 / 5)
                    aspectRatio: 4,

                    plugins: {
                        title: {
                            display: true,
                            text: `عرض Cells (آخر ${isoDates.length} يوم) — المتاح / المباع / الألوتمنت`
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                // عنوان التولتيب = التاريخ
                                title: (items) => {
                                    const p = items[0].raw;
                                    return `${formatDM(p.iso)} (${p.iso})`;
                                },
                                // تفاصيل حسب الصف
                                label: (item) => {
                                    const p = item.raw;
                                    return [
                                        `المتاح: ${p.available}`,
                                        `المباع: ${p.sold}`,
                                        `الألوتمنت: ${p.allotted}`
                                    ];
                                }
                            }
                        }
                    },

                    scales: {
                        // محور X: الأيام
                        x: {
                            position: 'top',
                            type: 'linear',
                            ticks: {
                                stepSize: 1,
                                // نعرض label D-M
                                callback: (v) => (isoDates[v] ? labels[v] : ''),
                                autoSkip: true,
                                maxRotation: 0
                            },
                            grid: {
                                display: false
                            }
                        },

                        // محور Y: 3 صفوف فقط
                        y: {
                            type: 'linear',
                            // نحدد مدى ثابت للـ 3 صفوف (0,1,2)
                            min: -0.6,
                            max: 2.6,
                            ticks: {
                                stepSize: 1,
                                // أسماء الصفوف بدل الأرقام
                                callback: (v) => {
                                    if (v === 0) return 'الألوتمنت';
                                    if (v === 1) return 'المباع';
                                    if (v === 2) return 'المتاح الباقي';
                                    return '';
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                },
                plugins: [drawCellNumbers] // كتابة الأرقام داخل cells
            });

            /* =========================================================
               9) ملخص تحت الرسم (اختياري)
               ========================================================= */
            const totalAllotted = allottedPoints.reduce((s, p) => s + p.value, 0);
            const totalSold = soldPoints.reduce((s, p) => s + p.value, 0);
            const totalAvailable = availablePoints.reduce((s, p) => s + p.value, 0);

            const summary = document.getElementById('allotmentSummary');
            if (summary) {
                summary.innerHTML = `
            <div style="
                margin-top:10px;
                padding:10px 12px;
                border:1px solid #eee;
                border-radius:10px;
                display:flex;
                gap:14px;
                flex-wrap:wrap;
                font-size:14px
            ">
                <div><strong>إجمالي الألوتمنت:</strong> ${totalAllotted}</div>
                <div><strong>إجمالي المباع:</strong> ${totalSold}</div>
                <div><strong>إجمالي المتاح:</strong> ${totalAvailable}</div>
                <div style="opacity:.7">* الرسم مُلزم بآخر ${isoDates.length} يوم لتفادي رسم ضخم</div>
            </div>
        `;
            }
        }




        // تنفيذ العمليات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            buildAllotmentTable();
            buildAllotmentChart();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@2"></script>
@endpush
