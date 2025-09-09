@extends('layouts.app')

@section('title', 'إدارة غرف الفنادق')

@section('content')
    <div class="container-fluid px-2 px-sm-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-door-open fa-lg align-middle me-2"></i>
                إدارة غرف الفنادق
            </h1>
            <a href="{{ route('allotments.monitor') }}" class="nav-link">
                <i class="fas fa-chart-bar"></i> متابعة الألوتمنت
            </a>
            <!-- قائمة اختصارات الألوتمنت - Compact Allotment Menu -->
<div class="bg-light rounded p-3 mb-4">
    <h6 class="fw-bold text-primary mb-3">الألوتمنت</h6>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('allotments.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-list-ul"></i> قائمة الألوتمنت
        </a>
        <a href="{{ route('allotments.create') }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-plus-circle"></i> إضافة ألوتمنت
        </a>
        <a href="{{ route('allotments.monitor') }}" class="btn btn-sm btn-outline-info">
            <i class="fas fa-chart-bar"></i> متابعة الألوتمنت
        </a>
        <a href="{{ route('allotment-sales.create') }}" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-shopping-cart"></i> بيع ألوتمنت
        </a>
    </div>
</div>
            <div>
                <a href="{{ route('reports.advanced') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chart-bar me-1"></i> التقارير المتقدمة
                </a>
            </div>
        </div>

        @if (isset($unassignedBookings) && $unassignedBookings->count() > 0)
            <!--================================================================================
              قسم الحجوزات التي تحتاج لتخصيص غرف
            ==================================================================================-->
            <div class="card mb-4 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            حجوزات تنتظر تخصيص غرف
                            <span class="badge bg-dark ms-2">{{ $unassignedBookings->count() }}</span>
                        </h5>
                        <button class="btn btn-sm btn-dark" type="button" data-bs-toggle="collapse"
                            data-bs-target="#unassignedBookingsCollapse" aria-expanded="true"
                            aria-controls="unassignedBookingsCollapse">
                            عرض/إخفاء
                        </button>
                    </div>
                </div>
                <div class="collapse show" id="unassignedBookingsCollapse">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th>اسم العميل</th>
                                        <th>الفندق</th>
                                        <th>الشركة</th>
                                        <th class="text-center">تاريخ الدخول</th>
                                        <th class="text-center">تاريخ الخروج</th>
                                        <th class="text-center">عدد الغرف</th>
                                        <th class="text-center" style="width: 120px;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($unassignedBookings as $index => $booking)
                                        <tr
                                            class="{{ $booking->cost_price == 0 || $booking->sale_price == 0 ? 'table-secondary text-muted' : '' }}">
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td
                                                class="fw-medium {{ $booking->cost_price == 0 || $booking->sale_price == 0 ? 'text-decoration-line-through' : '' }}">
                                                {{ $booking->client_name }}
                                                @if ($booking->cost_price == 0 || $booking->sale_price == 0)
                                                    <small class="text-danger d-block">(مؤرشف)</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-info {{ $booking->cost_price == 0 || $booking->sale_price == 0 ? 'opacity-50' : '' }}">
                                                    {{ $booking->hotel->name }}
                                                </span>
                                            </td>
                                            <td
                                                class="{{ $booking->cost_price == 0 || $booking->sale_price == 0 ? 'text-decoration-line-through' : '' }}">
                                                {{ $booking->company->name ?? 'غير محدد' }}
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-secondary {{ $booking->cost_price == 0 || $booking->sale_price == 0 ? 'opacity-50' : '' }}">
                                                    {{ $booking->check_in->format('Y-m-d') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-secondary {{ $booking->cost_price == 0 || $booking->sale_price == 0 ? 'opacity-50' : '' }}">
                                                    {{ $booking->check_out->format('Y-m-d') }}
                                                </span>
                                            </td>
                                            <td
                                                class="text-center {{ $booking->cost_price == 0 || $booking->sale_price == 0 ? 'text-decoration-line-through' : '' }}">
                                                <span class="text-primary fw-bold">{{ $booking->rooms }}</span> غرفة
                                            </td>
                                            <td class="text-center">
                                                @if ($booking->cost_price != 0 && $booking->sale_price != 0)
                                                    <a href="{{ route('hotel.rooms.hotel', $booking->hotel_id) }}?assign_booking={{ $booking->id }}"
                                                        class="btn btn-sm btn-primary me-1" title="تخصيص غرفة">
                                                        <i class="fas fa-door-open"></i>
                                                    </a>
                                                    <a href="{{ route('bookings.show', $booking->id) }}"
                                                        class="btn btn-sm btn-info" title="عرض تفاصيل الحجز">
                                                        <i class="fas fa-info-circle"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">مؤرشف</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!--================================================================================
              بطاقات الفنادق
            ==================================================================================-->
        <div class="row g-3">
            @foreach ($hotels as $hotel)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm hotel-card position-relative overflow-hidden">
                        <!-- شارة تنبيه إذا كان هناك حجوزات بحاجة لتخصيص -->
                        @if (isset($unassignedBookingsByHotel[$hotel->id]) && $unassignedBookingsByHotel[$hotel->id]->count() > 0)
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger rounded-pill animation-pulse px-2 py-1">
                                    {{ $unassignedBookingsByHotel[$hotel->id]->count() }}
                                </span>
                            </div>
                        @endif

                        <div
                            class="card-header 
                    {{ $hotel->occupancy_rate > 80 ? 'bg-danger' : ($hotel->occupancy_rate > 50 ? 'bg-warning' : 'bg-success') }} 
                    text-white py-2">
                            <h5 class="mb-0 text-truncate">{{ $hotel->name }}</h5>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="display-6 fw-bold">{{ $hotel->total_rooms }}</div>
                                    <div class="small text-muted">إجمالي الغرف</div>
                                </div>
                                <div class="col-4">
                                    <div class="display-6 text-danger fw-bold">{{ $hotel->occupied_rooms }}</div>
                                    <div class="small text-muted">مشغولة</div>
                                </div>
                                <div class="col-4">
                                    <div class="display-6 text-success fw-bold">{{ $hotel->available_rooms }}</div>
                                    <div class="small text-muted">متاحة</div>
                                </div>
                            </div>

                            <div class="progress mb-3" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar 
                            bg-{{ $hotel->occupancy_rate > 80 ? 'danger' : ($hotel->occupancy_rate > 50 ? 'warning' : 'success') }}"
                                    role="progressbar" style="width: {{ $hotel->occupancy_rate }}%;"
                                    aria-valuenow="{{ $hotel->occupancy_rate }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <span
                                    class="badge 
                            {{ $hotel->occupancy_rate > 80 ? 'bg-danger' : ($hotel->occupancy_rate > 50 ? 'bg-warning' : 'bg-success') }}">
                                    معدل الإشغال: {{ $hotel->occupancy_rate }}%
                                </span>
                            </div>

                            @if (isset($unassignedBookingsByHotel[$hotel->id]) && $unassignedBookingsByHotel[$hotel->id]->count() > 0)
                                <div class="alert alert-warning d-flex align-items-center py-2 mb-3 small">
                                    <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                                    <div>يوجد
                                        <span class="fw-bold">{{ $unassignedBookingsByHotel[$hotel->id]->count() }}</span>
                                        حجز بحاجة لتخصيص غرف!
                                    </div>
                                </div>
                            @endif

                            <div class="mt-auto text-center">
                                <a href="{{ route('hotel.rooms.hotel', $hotel->id) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-door-open me-1"></i> إدارة الغرف
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /*======================================================================
              أنيميشن نبض للتنبيه
            ======================================================================*/
        .animation-pulse {
            animation: pulse 1.5s infinite ease-in-out;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                transform: scale(1);
            }
        }

        /*======================================================================
              تأثير الهوفر على بطاقة الفندق
            ======================================================================*/
        .hotel-card {
            border-radius: 0.75rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15) !important;
        }

        /*======================================================================
              ضبط الـ badge وتنسيق الأيقونات داخل الجدول
            ======================================================================*/
        .table td,
        .table th {
            vertical-align: middle;
        }

        .table td .badge {
            font-size: 0.8rem;
        }

        /* ضبط Rtl لعلامة الشرط */
        .table thead th {
            text-align: center;
        }

        /* عندما تكون خلايا الجدول نصًّا طويلًا، نمنع الانكسار */
        .table td,
        .table th {
            white-space: nowrap;
        }

        /* في العرض الضيق، تخفى بعض الأعمدة */
        @media (max-width: 576px) {

            .table thead th:nth-child(4),
            .table tbody td:nth-child(4),
            .table thead th:nth-child(7),
            .table tbody td:nth-child(7) {
                display: none;
            }
        }

        /*======================================================================
              ألوان الخلفيات الخفيفة (Soft) للـ Alerts
            ======================================================================*/
        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-warning i {
            color: #856404;
        }

        /*======================================================================
              تنعيم الزوايا لجميع الكروت والأزرار
            ======================================================================*/
        .card {
            border-radius: 0.75rem;
        }

        .btn {
            border-radius: 0.5rem;
        }

        /*======================================================================
              إخفاء زوايا العناصر عند تمرير شريط التمرير (Scrollbar)
            ======================================================================*/
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background-color: transparent;
        }
    </style>
@endpush
