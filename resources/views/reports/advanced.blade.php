@extends('layouts.app')

@section('title', 'التقارير المتقدمة')

@section('favicon')
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection

@push('styles')
    {{-- هنضيف ملف CSS للتقارير المتقدمة --}}
    <link rel="stylesheet" href="{{ asset('css/advanced_reports.css') }}">

    {{-- نضيف تنسيقات للاستجابية في مختلف الشاشات --}}
    <style>
        /* تنسيقات الرسوم البيانية */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        /* عند التحويم عالرسم البياني */
        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        /* تنسيق العدادات والبطاقات الصغيرة */
        .stat-card {
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border-right: 5px solid;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .stat-card h2 {
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .stat-card p {
            opacity: 0.7;
            margin-bottom: 0;
        }

        /* تنسيق جدول المعلومات */
        .info-table {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }

        .info-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* تنسيقات الطباعة */
        @media print {
            .no-print {
                display: none !important;
            }

            .container-fluid {
                width: 100%;
                max-width: 100%;
                padding: 0;
            }

            body {
                background-color: white;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                break-inside: avoid;
            }

            .chart-container {
                height: 250px !important;
                break-inside: avoid;
            }
        }

        /* مؤشر التحميل */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
            font-size: 18px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* تنسيقات للأجهزة المحمولة */
        @media (max-width: 767.98px) {
            .chart-container {
                height: 250px;
            }

            h1 {
                font-size: 1.75rem;
            }

            .stat-card h2 {
                font-size: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- شريط العنوان والأزرار --}}
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4 no-print">
            <h1 class="mb-3 mb-md-0">
                <i class="fas fa-chart-bar me-2 text-primary"></i>
                التقارير المتقدمة
            </h1>
            <div class="d-flex flex-wrap">
                {{-- نموذج اختيار التاريخ --}}
                <form action="{{ route('reports.advanced') }}" method="GET" class="d-flex me-2 mb-2 mb-md-0">
                    <div class="input-group">
                        <input type="date" name="date" id="reportDate" class="form-control"
                            value="{{ $today->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-calendar-check me-1"></i> عرض
                        </button>
                    </div>
                </form>

                {{-- زر الرجوع للتقارير العادية --}}
                <a href="{{ route('reports.daily') }}" class="btn btn-outline-secondary me-2 mb-2 mb-md-0">
                    <i class="fas fa-arrow-right me-1"></i> التقارير اليومية
                </a>

                {{-- قائمة منسدلة لخيارات الحفظ والطباعة --}}
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="exportOptionsButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> حفظ التقرير
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportOptionsButton">
                        <li><button class="dropdown-item" type="button" id="printReport">
                                <i class="fas fa-print me-1"></i> طباعة
                            </button></li>
                        <li><button class="dropdown-item" type="button" id="saveAsPdf">
                                <i class="fas fa-file-pdf me-1 text-danger"></i> حفظ كـ PDF
                            </button></li>
                        <li><button class="dropdown-item" type="button" id="saveAsImage">
                                <i class="fas fa-file-image me-1 text-primary"></i> حفظ كصورة
                            </button></li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    {{-- تاريخ التقرير --}}
    <div class="alert alert-info mb-4">
        <i class="fas fa-calendar-day me-2"></i>
        تقرير يوم: {{ $today->locale('ar')->translatedFormat('l، j F Y') }}
        @if ($today->isToday())
            <span class="badge bg-success me-2">اليوم</span>
        @else
            <span class="badge bg-secondary me-2">تاريخ سابق</span>
        @endif
    </div>
    {{-- أزرار التنقل السريع بين الأيام --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="btn-group w-100">
                <a href="{{ route('reports.advanced', ['date' => now()->format('Y-m-d')]) }}"
                    class="btn btn-sm {{ $today->isToday() ? 'btn-primary' : 'btn-outline-primary' }}">
                    اليوم
                </a>
                <a href="{{ route('reports.advanced', ['date' => now()->subDay()->format('Y-m-d')]) }}"
                    class="btn btn-sm {{ $today->isYesterday() ? 'btn-primary' : 'btn-outline-primary' }}">
                    الأمس
                </a>
                <a href="{{ route('reports.advanced', ['date' => now()->subDays(2)->format('Y-m-d')]) }}"
                    class="btn btn-sm {{ $today->format('Y-m-d') == now()->subDays(2)->format('Y-m-d') ? 'btn-primary' : 'btn-outline-primary' }}">
                    أول أمس
                </a>
                <a href="{{ route('reports.advanced', ['date' => now()->startOfWeek()->format('Y-m-d')]) }}"
                    class="btn btn-sm btn-outline-primary">
                    بداية الأسبوع
                </a>
                <a href="{{ route('reports.advanced', ['date' => now()->startOfMonth()->format('Y-m-d')]) }}"
                    class="btn btn-sm btn-outline-primary">
                    بداية الشهر
                </a>
            </div>
        </div>
    </div>
    {{-- صف العدادات العلوية --}}
    <div class="row mb-4">
        {{-- عداد الحجوزات النشطة --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card" style="border-right-color: #3498db;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">حجوزات نشطة حالياً</h6>
                        <h2 class="display-5 fw-bold">{{ $activeBookings->count() }}</h2>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <i class="fas fa-bed fa-2x text-primary"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <p>
                        {{ $activeBookings->sum('rooms') }} غرفة مشغولة الآن
                    </p>
                </div>
            </div>
        </div>

        {{-- عداد حجوزات الدخول اليوم --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card" style="border-right-color: #2ecc71;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">دخول اليوم</h6>
                        <h2 class="display-5 fw-bold">{{ $checkingInToday->count() }}</h2>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <i class="fas fa-sign-in-alt fa-2x text-success"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <p>
                        {{ $checkingInToday->sum('rooms') }} غرفة جديدة تم تسجيلها
                    </p>
                </div>
            </div>
        </div>

        {{-- عداد حجوزات الخروج غداً --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card" style="border-right-color: #e74c3c;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">خروج غداً</h6>
                        <h2 class="display-5 fw-bold">{{ $checkingOutTomorrow->count() }}</h2>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <i class="fas fa-sign-out-alt fa-2x text-danger"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <p>
                        {{ $checkingOutTomorrow->sum('rooms') }} غرفة ستكون متاحة بعد الخروج
                    </p>
                </div>
            </div>
        </div>

        {{-- عداد متوسط معدل الإشغال --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card" style="border-right-color: #f39c12;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">معدل الإشغال</h6>
                        @php
                            $totalRooms = $hotelStats->sum('total_rooms');
                            $occupiedRooms = $activeBookings->sum('rooms');
                            $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
                        @endphp
                        <h2 class="display-5 fw-bold">{{ $occupancyRate }}%</h2>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <i class="fas fa-percentage fa-2x text-warning"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $occupancyRate }}%"
                            aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- تقرير الإشغال بالفنادق --}}
    <div class="row mb-4">
        <div class="col-lg-7 mb-4">
            <div class="  h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-2">معدل الإشغال اليومي (أسبوع)</h5>
                    <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#occupancyChartControls"
                        aria-expanded="false">
                        <i class="fas fa-filter"></i>
                    </a>
                </div>

                {{-- خيارات تصفية الرسم البياني (مخفية مبدئياً) --}}
                <div class="collapse" id="occupancyChartControls">
                    <div class="card-body bg-light">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button class="btn btn-sm btn-primary" data-chart-type="stacked">رسم تراكمي</button>
                            <button class="btn btn-sm btn-outline-primary" data-chart-type="line">رسم خطي</button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="  h-100">
                <div class="card-header">
                    <h5 class="mb-2">حالة الغرف اليوم</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 260px;">
                        <canvas id="roomStatusChart"></canvas>
                    </div>
                    <div class="mt-3 shadow">
                        <table class="table table-sm table-striped table-hover  shadow">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الفندق</th>
                                    <th>مشغولة</th>
                                    <th>متاحة</th>
                                    <th>معدل الإشغال</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hotelStats as $hotel)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $hotel->name }}</td>
                                        <td>{{ $hotel->active_bookings }}</td>
                                        <td>{{ $hotel->total_rooms - $hotel->active_bookings }}
                                            <br>
                                            <div class="small text-muted">
                                                من : {{ $hotel->purchased_rooms_count }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-{{ $hotel->occupancy_rate > 80 ? 'danger' : ($hotel->occupancy_rate > 50 ? 'warning' : 'success') }}"
                                                    style="width: {{ $hotel->occupancy_rate }}%">
                                                </div>

                                            </div>
                                            <small>{{ $hotel->occupancy_rate }}%</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- تحليل الإيرادات والتحصيلات --}}
    <div class="row mb-4">
        <div class="col-lg-12 mb-4">
            <div class=" ">
                <div class="card-header">
                    <h5 class="mb-2">تحليل الإيرادات والتحصيلات</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- قسم التفاصيل: النزلاء الحاليين والمغادرين غداً --}}
    <div class="row">
        {{-- قائمة النزلاء الحاليين --}}
        <div class="col-lg-6">
            <div class="  mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-2">النزلاء الحاليين ({{ $activeBookings->count() }})</h5>
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse"
                        data-bs-target="#currentGuestsCollapse">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse show" id="currentGuestsCollapse">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped table-hover mb-2">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>النزيل</th>
                                        <th>الفندق</th>
                                        <th>الدخول</th>
                                        <th>الخروج</th>
                                        <th>الإشغال</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeBookings as $index => $booking)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if (isset($booking->is_land_trip) && $booking->is_land_trip)
                                                    <a
                                                        href="{{ route('admin.land-trips.show', str_replace('LT-', '', $booking->id)) }}">
                                                        {{ $booking->client_name }}
                                                    </a>
                                                    <div class="small">
                                                        <span class="badge bg-info">رحلة برية</span>
                                                        <span class="text-muted">{{ $booking->company->name }}</span>
                                                    </div>
                                                @else
                                                    <a href="{{ route('bookings.show', $booking->id) }}">
                                                        {{ $booking->client_name }}
                                                    </a>
                                                    <div class="small text-muted">{{ $booking->company->name }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $booking->hotel->name }}</td>
                                            <td>{{ $booking->check_in->format('d/m/Y') }}</td>
                                            <td>{{ $booking->check_out->format('d/m/Y') }}</td>
                                            <td> {{ $booking->rooms }} غرفة
                                                <div class="small text-muted">
                                                    @if (isset($booking->is_land_trip) && $booking->is_land_trip && isset($booking->landTrip))
                                                        {{ $booking->landTrip->days_count }} ليلة
                                                    @else
                                                        {{ $booking->days ?? 1 }} ليلة
                                                    @endif
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
        </div>

        {{-- قائمة النزلاء المغادرين غداً --}}
        <div class="col-lg-6">
            <div class="  mb-4">
                <div class="card-header bg-danger text-white d-flex justify-content-between">
                    <h5 class="mb-2">المغادرون غداً ({{ $checkingOutTomorrow->count() }})</h5>
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse"
                        data-bs-target="#checkoutTomorrowCollapse">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse show" id="checkoutTomorrowCollapse">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped table-hover mb-2">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>النزيل</th>
                                        <th>الفندق</th>
                                        <th>الدخول</th>
                                        <th>المدة</th>
                                        <th>الغرف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($checkingOutTomorrow as $index => $booking)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if (isset($booking->is_land_trip) && $booking->is_land_trip)
                                                    <a
                                                        href="{{ route('admin.land-trips.show', str_replace('LT-', '', $booking->id)) }}">
                                                        {{ $booking->client_name }}
                                                    </a>
                                                    <div class="small">
                                                        <span class="badge bg-info">رحلة برية</span>
                                                        <span class="text-muted">{{ $booking->company->name }}</span>
                                                    </div>
                                                @else
                                                    <a href="{{ route('bookings.show', $booking->id) }}">
                                                        {{ $booking->client_name }}
                                                    </a>
                                                    <div class="small text-muted">{{ $booking->company->name }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $booking->hotel->name }}</td>
                                            <td>{{ $booking->check_in->format('d/m/Y') }}</td>
                                            <td>
                                                @if (isset($booking->is_land_trip) && $booking->is_land_trip && isset($booking->landTrip))
                                                    {{ $booking->landTrip->days_count }} ليلة
                                                @else
                                                    {{ $booking->days ?? 1 }} ليلة
                                                @endif
                                            </td>
                                            <td>{{ $booking->rooms }} غرفة</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- استدعاء مكتبة Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- إضافات Chart.js للتفاعل --}}
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    {{-- مكتبة تحويل HTML إلى صورة --}}
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

    {{-- مكتبة إنشاء PDF مع دعم اللغة العربية --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    {{-- دعم الخطوط في PDF --}}
    <script src="https://unpkg.com/jspdf-font@1.0.7/dist/font.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/amiri-font/0.113/amiri-font.min.js"></script>

    {{-- تهيئة الرسوم البيانية --}}
    <script>
        // تحويل الأرقام للصيغة العربية
        function formatNumber(num) {
            return new Intl.NumberFormat('ar-SA').format(num);
        }

        // تعريف دالة إظهار مؤشر التحميل
        function showLoadingOverlay(message = 'جاري التحميل...') {
            let overlay = document.getElementById('loadingOverlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'loadingOverlay';
                overlay.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.7); display: flex; flex-direction: column;
                    justify-content: center; align-items: center; z-index: 9999; color: white; font-size: 18px;
                `;
                const spinner = document.createElement('div');
                spinner.style.cssText = `
                    border: 5px solid #f3f3f3; border-top: 5px solid #3498db;
                    border-radius: 50%; width: 50px; height: 50px;
                    animation: spin 1s linear infinite; margin-bottom: 15px;
                `;
                spinner.innerHTML = '&nbsp;';
                const messageEl = document.createElement('div');
                messageEl.id = 'loadingMessage';
                messageEl.textContent = message;
                overlay.appendChild(spinner);
                overlay.appendChild(messageEl);
                document.body.appendChild(overlay);
            } else {
                overlay.style.display = 'flex';
                document.getElementById('loadingMessage').textContent = message;
            }
        }

        // تعريف دالة إخفاء مؤشر التحميل
        function hideLoadingOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // تفعيل دعم devicePixelRatio العالي للرسوم البيانية
            Chart.defaults.devicePixelRatio = window.devicePixelRatio * 2;

            // إعدادات عالمية للرسوم البيانية
            Chart.defaults.font.family = "'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
            Chart.defaults.color = '#333';

            // ************** رسم بياني للإشغال اليومي **************
            const occupancyData = @json($occupancyData);
            const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');

            // بنجهز بيانات الرسم البياني
            const occupancyLabels = occupancyData.map(day => day.label + ' (' + day.day_name + ')');
            const occupancyBooked = occupancyData.map(day => day.total_booked);
            const occupancyAvailable = occupancyData.map(day => day.total_available);
            const occupancyRate = occupancyData.map(day => day.overall_rate);

            // بنعمل instance جديدة للرسم البياني
            const occupancyChart = new Chart(occupancyCtx, {
                type: 'bar', // نوع الرسم: أعمدة تراكمية
                data: {
                    labels: occupancyLabels,
                    datasets: [{
                            label: 'غرف مشغولة',
                            data: occupancyBooked,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0'
                        },
                        {
                            label: 'غرف متاحة',
                            data: occupancyAvailable,
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0'
                        },
                        {
                            label: 'معدل الإشغال (%)',
                            data: occupancyRate,
                            type: 'line',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                            pointRadius: 4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'الأيام القادمة'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'عدد الغرف'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            max: 100,
                            title: {
                                display: true,
                                text: 'نسبة الإشغال (%)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                // هنا بنخصص شكل التوليتيب اللي بيظهر عند التحويم
                                footer: function(tooltipItems) {
                                    const dataIndex = tooltipItems[0].dataIndex;
                                    const day = occupancyData[dataIndex];

                                    // بنظهر تفاصيل الفنادق في هذا اليوم
                                    let footerText = '\nتفاصيل الإشغال:';
                                    for (const [hotelId, hotel] of Object.entries(day.hotels)) {
                                        footerText +=
                                            `\n${hotel.name}: ${hotel.rate}% (${hotel.booked}/${hotel.total})`;
                                    }
                                    return footerText;
                                }
                            }
                        }
                    }
                }
            });

            // ************** رسم بياني لحالة الغرف **************
            const roomStatusCtx = document.getElementById('roomStatusChart').getContext('2d');

            // بنجهز بيانات الرسم البياني من بيانات الفنادق
            const hotelNames = @json($hotelStats->pluck('name'));
            const occupiedRooms = @json($hotelStats->pluck('active_bookings'));
            const availableRooms = @json(
                $hotelStats->map(function ($hotel) {
                    return $hotel->total_rooms - $hotel->active_bookings;
                }));

            // بنعمل instance جديدة للرسم البياني
            const roomStatusChart = new Chart(roomStatusCtx, {
                type: 'bar',
                data: {
                    labels: hotelNames,
                    datasets: [{
                            label: 'غرف مشغولة',
                            data: occupiedRooms,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'غرف متاحة',
                            data: availableRooms,
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'عدد الغرف'
                            }
                        },
                        y: {
                            stacked: true
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                // هنا بنظهر النسبة المئوية في التوليتيب
                                footer: function(tooltipItems) {
                                    const index = tooltipItems[0].dataIndex;
                                    const hotel = @json($hotelStats)[(index)];
                                    return `معدل الإشغال: ${hotel.occupancy_rate}%`;
                                }
                            }
                        }
                    }
                }
            });

            // ************** رسم بياني لتحليل الإيرادات **************
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueData = @json($revenueData);

            // بنجهز بيانات الرسم البياني من بيانات الإيرادات
            const months = revenueData.months;
            const actualRevenues = revenueData.data.map(item => item.actual);
            const actualPayments = revenueData.data.map(item => item.payments);
            const projectedRevenues = revenueData.data.map(item => item.projected);
            const collectionRates = revenueData.data.map(item => item.collection_rate);

            // بنعمل instance جديدة للرسم البياني
            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                            label: 'الإيرادات الفعلية',
                            data: actualRevenues,
                            backgroundColor: 'rgba(52, 152, 219, 0.7)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 1,
                            order: 2
                        },
                        {
                            label: 'المدفوعات المحصلة',
                            data: actualPayments,
                            backgroundColor: 'rgba(46, 204, 113, 0.7)',
                            borderColor: 'rgba(46, 204, 113, 1)',
                            borderWidth: 1,
                            order: 3
                        },
                        {
                            label: 'الإيرادات المتوقعة',
                            data: projectedRevenues,
                            type: 'line',
                            borderColor: 'rgba(243, 156, 18, 1)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(243, 156, 18, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(243, 156, 18, 1)',
                            pointRadius: 4,
                            order: 1
                        },
                        {
                            label: 'معدل التحصيل (%)',
                            data: collectionRates,
                            type: 'line',
                            borderColor: 'rgba(142, 68, 173, 1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.2,
                            pointBackgroundColor: 'rgba(142, 68, 173, 1)',
                            pointRadius: 4,
                            yAxisID: 'y1',
                            order: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'المبلغ (ريال)'
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
                            max: 100,
                            title: {
                                display: true,
                                text: 'معدل التحصيل (%)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                // تنسيق الأرقام في التوليتيب
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.yAxisID === 'y1') {
                                        return label + context.parsed.y + '%';
                                    } else {
                                        return label + formatNumber(context.parsed.y) + ' ريال';
                                    }
                                }
                            }
                        }
                    }
                }
            });

            // *** تبديل نوع الرسم البياني للإشغال ***
            document.querySelectorAll('[data-chart-type]').forEach(button => {
                button.addEventListener('click', function() {
                    const chartType = this.getAttribute('data-chart-type');

                    // بنغير الكلاسات للأزرار
                    document.querySelectorAll('[data-chart-type]').forEach(btn => {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-primary');
                    });
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');

                    if (chartType === 'line') {
                        // تحويل للرسم الخطي
                        occupancyChart.config.type = 'line';
                        occupancyChart.config.data.datasets[0].fill = true;
                        occupancyChart.config.data.datasets[1].fill = true;
                    } else {
                        // إعادة للرسم التراكمي
                        occupancyChart.config.type = 'bar';
                        occupancyChart.config.data.datasets[0].fill = false;
                        occupancyChart.config.data.datasets[1].fill = false;
                    }

                    occupancyChart.update();
                });
            });

            // ************** وظائف حفظ التقرير **************
            // طباعة التقرير
            document.getElementById('printReport')?.addEventListener('click', function() {
                window.print();
            });


            // حفظ التقرير كصورة
            document.getElementById('saveAsImage')?.addEventListener('click', async function() {
                try {
                    showLoadingOverlay('جاري تجهيز الصورة...');
                    await new Promise(resolve => setTimeout(resolve, 200));
                    // إخفاء العناصر غير المرغوبة
                    const elementsToHide = document.querySelectorAll(
                        '.no-print, .dropdown-menu, #loadingOverlay');
                    const originalDisplays = [];
                    elementsToHide.forEach((el, i) => {
                        originalDisplays[i] = el.style.display;
                        el.style.display = 'none';
                    });
                    await new Promise(resolve => setTimeout(resolve, 200));
                    // تصوير الصفحة كاملة
                    const container = document.body;
                    const options = {
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#fff',
                        width: container.scrollWidth,
                        height: container.scrollHeight,
                        scrollX: 0,
                        scrollY: 0,
                    };
                    const canvas = await html2canvas(container, options);
                    elementsToHide.forEach((el, i) => {
                        el.style.display = originalDisplays[i] || '';
                    });
                    showLoadingOverlay('جاري حفظ الصورة...');
                    await new Promise(resolve => setTimeout(resolve, 100));
                    const imgData = canvas.toDataURL('image/png');
                    const a = document.createElement('a');
                    const today = new Date();
                    const dateStr = today.toISOString().split('T')[0];
                    a.download = `تقرير_متقدم_${dateStr}.png`;
                    a.href = imgData;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    hideLoadingOverlay();
                } catch (error) {
                    hideLoadingOverlay();
                    alert('حدث خطأ أثناء حفظ الصورة: ' + error.message);
                }
            });

            // حفظ التقرير كملف PDF
            document.getElementById('saveAsPdf')?.addEventListener('click', async function() {
                try {
                    showLoadingOverlay('جاري تجهيز ملف PDF...');
                    await new Promise(resolve => setTimeout(resolve, 200));
                    const elementsToHide = document.querySelectorAll(
                        '.no-print, .dropdown-menu, #loadingOverlay');
                    const originalDisplays = [];
                    elementsToHide.forEach((el, i) => {
                        originalDisplays[i] = el.style.display;
                        el.style.display = 'none';
                    });
                    await new Promise(resolve => setTimeout(resolve, 200));
                    const container = document.body;
                    const options = {
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#fff',
                        width: container.scrollWidth,
                        height: container.scrollHeight,
                        scrollX: 0,
                        scrollY: 0,
                    };
                    const canvas = await html2canvas(container, options);
                    elementsToHide.forEach((el, i) => {
                        el.style.display = originalDisplays[i] || '';
                    });
                    showLoadingOverlay('جاري حفظ ملف PDF...');
                    await new Promise(resolve => setTimeout(resolve, 100));
                    const {
                        jsPDF
                    } = window.jspdf;
                    const imgData = canvas.toDataURL('image/jpeg', 0.95);
                    const orientation = canvas.width > canvas.height ? 'landscape' : 'portrait';
                    const pdf = new jsPDF(orientation, 'mm', 'a4');
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();
                    const imgWidth = pageWidth;
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    let heightLeft = imgHeight;
                    let position = 0;
                    pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                    while (heightLeft > 0) {
                        position -= pageHeight;
                        pdf.addPage();
                        pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pageHeight;
                    }
                    const today = new Date();
                    const dateStr = today.toISOString().split('T')[0];
                    pdf.save(`تقرير_متقدم_${dateStr}.pdf`);
                    hideLoadingOverlay();
                } catch (error) {
                    hideLoadingOverlay();
                    alert('حدث خطأ أثناء حفظ ملف PDF: ' + error.message);
                }
            });

        });
    </script>
@endpush
