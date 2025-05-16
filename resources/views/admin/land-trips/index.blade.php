@extends('layouts.app')

@section('title', 'إدارة الرحلات البرية')

@push('styles')
    <style>
        /* قسم مؤشر التحميل */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
            backdrop-filter: blur(2px);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 123, 255, 0.1);
            border-left-color: #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* تحسينات الجدول عند البحث */
        .trip-row.highlight {
            background-color: rgba(13, 110, 253, 0.05);
        }

        /* تأثير لطيف للنتائج المتطابقة */
        .match-highlight {
            background-color: rgba(255, 193, 7, 0.2);
            border-radius: 2px;
            padding: 0 2px;
        }

        /* تحسينات عامة للصفحة */
        .bg-travel {
            background-color: #f8f9fa;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e9ecef' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .card-header-tabs .nav-link {
            color: #495057;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }

        .card-header-tabs .nav-link.active {
            color: #0d6efd;
            background: none;
            border-bottom: 2px solid #0d6efd;
        }

        .stats-card {
            transition: all 0.3s;
            border-radius: 0.75rem;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* تنسيقات الجدول وخياراته */
        .table-trips th {
            white-space: nowrap;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-responsive {
            max-height: calc(100vh - 300px);
            min-height: 400px;
        }

        .trip-row {
            transition: background-color 0.2s;
        }

        .trip-row:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .trip-filter-drawer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .trip-filter-drawer.show {
            max-height: 500px;
        }

        /* تحسينات للمرشحات */
        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filter-pill {
            background-color: #e9ecef;
            color: #495057;
            border-radius: 50px;
            padding: 0.35rem 0.85rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }

        .filter-pill:hover {
            background-color: #dee2e6;
        }

        .filter-pill .close {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        /* تحسين الأزرار */
        .floating-action-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 100;
            transition: transform 0.2s, background-color 0.2s;
        }

        .floating-action-button:hover {
            transform: scale(1.1);
            background-color: #0b5ed7;
            color: white;
        }

        /* تكيف الشاشة */
        @media (max-width: 768px) {
            .card-header-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }

            .stats-card {
                margin-bottom: 1rem;
            }

            .button-text {
                display: none;
            }
        }

        /* تنسيقات جدول الحجوزات */
        #bookings-table-container .table th {
            white-space: nowrap;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        #bookings-table-container .table tr {
            transition: background-color 0.2s;
        }

        #bookings-table-container .table tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .booking-badge {
            font-size: 0.85rem;
            padding: 0.25rem 0.65rem;
            border-radius: 50px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 bg-travel" x-data="tripsManager()">
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h1 class="h3 mb-3 mb-md-0">
                        <i class="fas fa-bus me-2 text-primary"></i> إدارة الرحلات البرية
                    </h1>

                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-secondary" @click="toggleFilters">
                            <i class="fas fa-filter me-1"></i>
                            <span class="button-text">فلترة</span>
                        </button>

                        <a href="{{ route('admin.trip-types.index') }}" class="btn btn-outline-info mb-2">
                            <i class="fas fa-tags me-1"></i>
                            <span class="button-text">أنواع الرحلات</span>
                        </a>

                        <a href="{{ route('admin.land-trips.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i>
                            <span class="button-text">إضافة رحلة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- الإحصائيات السريعة -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class=" card stats-card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="fas fa-bus fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 fs-sm">إجمالي الرحلات</h6>
                            <h4 class="mb-0">{{ $totalTrips ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card stats-card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 fs-sm">الرحلات النشطة</h6>
                            <h4 class="mb-0">{{ $activeTrips ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card stats-card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                            <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 fs-sm">رحلات الشهر الحالي</h6>
                            <h4 class="mb-0">{{ $currentMonthTrips ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card stats-card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 fs-sm">إجمالي الحجوزات</h6>
                            <h4 class="mb-0">{{ $totalBookings ?? 0 }}</h4>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- قسم الفلتر الذي يظهر/يختفي -->
        <div class="card shadow-sm mb-4 trip-filter-drawer" :class="{ 'show': showFilters }">
            <div class="card-body">
                <form action="{{ route('admin.land-trips.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <label for="trip_type_id" class="form-label">نوع الرحلة</label>
                        <select name="trip_type_id" id="trip_type_id" class="form-select">
                            <option value="">كل الأنواع</option>
                            @foreach ($tripTypes as $tripType)
                                <option value="{{ $tripType->id }}"
                                    {{ request('trip_type_id') == $tripType->id ? 'selected' : '' }}>
                                    {{ $tripType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="agent_id" class="form-label">جهة الحجز</label>
                        <select name="agent_id" id="agent_id" class="form-select">
                            <option value="">كل الجهات</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}"
                                    {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="status" class="form-label">الحالة</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشطة
                            </option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="employee_id" class="form-label">الموظف المسؤول</label>
                        <select name="employee_id" id="employee_id" class="form-select">
                            <option value="">كل الموظفين</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="start_date" class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="end_date" class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> تطبيق الفلتر
                            </button>
                            <a href="{{ route('admin.land-trips.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض الفلاتر المطبقة حاليًا -->
        @if (request()->anyFilled(['trip_type_id', 'agent_id', 'status', 'employee_id', 'start_date', 'end_date']))
            <div class="filter-pills mb-3">
                <span class="me-2 text-muted">الفلاتر المطبقة:</span>

                @if (request('trip_type_id'))
                    <span class="filter-pill">
                        نوع الرحلة:
                        {{ $tripTypes->where('id', request('trip_type_id'))->first()->name ?? request('trip_type_id') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('trip_type_id')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('agent_id'))
                    <span class="filter-pill">
                        جهة الحجز: {{ $agents->where('id', request('agent_id'))->first()->name ?? request('agent_id') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('agent_id')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('status'))
                    <span class="filter-pill">
                        الحالة:
                        @if (request('status') == 'active')
                            نشطة
                        @elseif(request('status') == 'inactive')
                            غير نشطة
                        @elseif(request('status') == 'expired')
                            منتهية
                        @else
                            {{ request('status') }}
                        @endif
                        <a href="{{ route('admin.land-trips.index', request()->except('status')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('employee_id'))
                    <span class="filter-pill">
                        الموظف:
                        {{ $employees->where('id', request('employee_id'))->first()->name ?? request('employee_id') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('employee_id')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('start_date'))
                    <span class="filter-pill">
                        من تاريخ: {{ request('start_date') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('start_date')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('end_date'))
                    <span class="filter-pill">
                        إلى تاريخ: {{ request('end_date') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('end_date')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
            </div>
        @endif

        <!-- القائمة الرئيسية -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('all')"
                            :class="{ 'active': currentTab === 'all' }">كل الرحلات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('active')"
                            :class="{ 'active': currentTab === 'active' }">الرحلات النشطة</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('All-Bookings')"
                            :class="{ 'active': currentTab === 'All-Bookings' }">الحجوزات على الرحلات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('bookings')"
                            :class="{ 'active': currentTab === 'bookings' }">رحلات بها حجوزات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('upcoming')"
                            :class="{ 'active': currentTab === 'upcoming' }">الرحلات القادمة</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="بحث سريع..."
                            x-model="searchTerm" @input="filterTable" @keydown.escape="searchTerm = ''; filterTable()">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-times text-muted" x-show="searchTerm"
                                @click="searchTerm = ''; filterTable();" style="cursor:pointer;"></i>
                            <i class="fas fa-search text-muted" x-show="!searchTerm"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0" style="position: relative;">
                <div class="loading-overlay" x-show="isLoading" style="display: none;">
                    <div class="spinner"></div>
                </div>
                <!-- حاوية جدول الرحلات -->
                <div id="trips-table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-trips">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>نوع الرحلة</th>
                                    <th>تاريخ المغادرة</th>
                                    <th>تاريخ العودة</th>
                                    <th>المدة</th>
                                    <th>جهة الحجز</th>
                                    <th>الموظف</th>
                                    <th>الحالة</th>
                                    <th>الحجوزات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($landTrips as $index => $trip)
                                    <tr class="trip-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-medium">{{ $trip->tripType->name ?? 'غير معروف' }}</span>
                                        </td>
                                        <td>{{ $trip->departure_date->format('Y-m-d') }}</td>
                                        <td>{{ $trip->return_date->format('Y-m-d') }}</td>
                                        <td>{{ $trip->days_count }} أيام</td>
                                        <td>{{ $trip->agent->name ?? 'غير معروف' }}</td>
                                        <td>{{ $trip->employee->name ?? 'غير معروف' }}</td>
                                        <td>
                                            @if ($trip->status == 'active')
                                                <span class="badge bg-success rounded-pill px-2 py-1">نشطة</span>
                                            @elseif($trip->status == 'inactive')
                                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">غير
                                                    نشطة</span>
                                            @elseif($trip->status == 'expired')
                                                <span class="badge bg-secondary rounded-pill px-2 py-1">منتهية</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info rounded-pill px-2 py-1">
                                                {{ $trip->bookings_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.land-trips.show', $trip->id) }}"
                                                    class="btn btn-outline-primary" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.land-trips.edit', $trip->id) }}"
                                                    class="btn btn-outline-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.land-trips.bookings', $trip->id) }}"
                                                    class="btn btn-outline-info" title="الحجوزات">
                                                    <i class="fas fa-calendar-check"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="حذف"
                                                    @click="confirmDelete({{ $trip->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center py-5">
                                                <i class="fas fa-bus fa-3x text-muted mb-3"></i>
                                                <h5>لا توجد رحلات لعرضها</h5>
                                                <p class="text-muted">يمكنك إضافة رحلة جديدة من خلال الضغط على زر "إضافة
                                                    رحلة"
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- حاوية جدول الحجوزات -->
                <div id="bookings-table-container">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="mb-0">قائمة الحجوزات</h5>
                        <button @click="exportBookingsToExcel" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i> تصدير إلى Excel
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">#</th>
                                    <th style="width: 20px;text-align: center;font-size: 10px;">رقم الحجز</th>
                                    <th>اسم العميل</th>
                                    <th>الشركة</th>
                                    <th>عدد الغرف</th>
                                    <th>تاريخ الحجز</th>
                                    <th>تاريخ الرحلة</th>
                                    <th>السعر الكلي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allBookings as $index => $booking)
                                    <tr>
                                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                                        <td style="text-align: center;">{{ $booking->id }}</td>
                                        <td>{{ $booking->client_name }}</td>
                                        <td>{{ $booking->company->name }}</td>
                                        <td>{{ $booking->rooms }}</td>
                                        <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('Y-m-d H:i') }}</td>
                                        <td style="direction: rtl; font-size: small;">
                                            {{ $booking->landTrip->departure_date->format('Y-m-d') ?? 'غير معروف' }}
                                            إلى
                                            {{ $booking->landTrip->return_date->format('Y-m-d') ?? 'غير معروف' }}
                                        </td>
                                        <td>
                                            {{ $booking->amount_due_from_company }}
                                            {{ $booking->currency }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center py-5">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <h5>لا توجد حجوزات لعرضها</h5>
                                                <p class="text-muted">لم يتم إجراء أي حجوزات على الرحلات حتى الآن</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                       
                    </div>
                      <div  class="d-flex justify-content-center">
                                   {{ $allBookings->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}

                    </div>
                </div>

            </div>

            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        إجمالي الرحلات: {{ $landTrips->total() }}
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $landTrips->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
                    </div>


                </div>
            </div>
        </div>

        <!-- زر الإضافة العائم للشاشات الصغيرة -->
        <a href="{{ route('admin.land-trips.create') }}" class="floating-action-button d-lg-none">
            <i class="fas fa-plus"></i>
        </a>

        <!-- مودال تأكيد الحذف -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">تأكيد الحذف</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>هل أنت متأكد من حذف هذه الرحلة؟ هذا الإجراء لا يمكن التراجع عنه.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <form :action="deleteUrl" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">نعم، قم بالحذف</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function tripsManager() {
            return {
                showFilters: false,
                currentTab: 'all',
                searchTerm: '',
                deleteUrl: '',
                deleteModal: null,
                isLoading: false,

                init() {
                    this.deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    // تخزين الجلسة الحالية
                    const savedTab = localStorage.getItem('currentTripsTab');
                    if (savedTab) {
                        setTimeout(() => this.setTab(savedTab), 100);
                    } else {
                        setTimeout(() => this.setTab('all'), 100);
                    }

                    // إخفاء جدول الحجوزات افتراضيًا
                    const bookingsTable = document.getElementById('bookings-table-container');
                    if (bookingsTable) {
                        bookingsTable.style.display = 'none';
                    }
                },

                toggleFilters() {
                    this.showFilters = !this.showFilters;
                },

                setTab(tab) {
                    // حفظ التبويب الحالي في التخزين المحلي للمتصفح
                    localStorage.setItem('currentTripsTab', tab);

                    // تحديث التبويب الحالي
                    this.currentTab = tab;

                    // الحصول على العناصر
                    const tripsTable = document.getElementById('trips-table-container');
                    const bookingsTable = document.getElementById('bookings-table-container');

                    if (!tripsTable || !bookingsTable) return;

                    // إظهار مؤشر التحميل
                    this.isLoading = true;

                    // تأخير بسيط لإظهار مؤشر التحميل
                    setTimeout(() => {
                        // تعيين العرض المناسب
                        if (tab === 'All-Bookings') {
                            tripsTable.style.display = 'none';
                            bookingsTable.style.display = 'block';

                            // تحديث البحث للحجوزات إذا كان هناك بحث نشط
                            if (this.searchTerm) {
                                this.filterBookingsTable();
                            }
                        } else {
                            tripsTable.style.display = 'block';
                            bookingsTable.style.display = 'none';

                            // تطبيق الفلتر على الرحلات
                            this.applyTripFilter(tab);
                        }

                        // إيقاف مؤشر التحميل
                        this.isLoading = false;
                    }, 100);
                },

                applyTripFilter(tab) {
                    // الحصول على جميع صفوف الرحلات
                    const rows = document.querySelectorAll('.trip-row');

                    // إعادة عرض جميع الصفوف بشكل افتراضي
                    rows.forEach(row => row.classList.remove('d-none'));

                    // إذا كان التبويب "كل الرحلات" وليس هناك مصطلح بحث، نعرض الكل ونتوقف
                    if (tab === 'all' && !this.searchTerm) {
                        return;
                    }

                    // تطبيق الفلتر حسب التبويب
                    rows.forEach(row => {
                        try {
                            // تطبيق البحث أولاً (إذا وجد)
                            if (this.searchTerm) {
                                const text = row.textContent.toLowerCase();
                                if (!text.includes(this.searchTerm.toLowerCase())) {
                                    row.classList.add('d-none');
                                    return; // تخطي التحقق من التبويبات إذا لم يتطابق البحث
                                }
                            }

                            // بعد تطبيق البحث، نطبق فلترة التبويبات
                            if (tab === 'all') return; // إذا كان "كل الرحلات"، نتوقف هنا

                            const statusElement = row.querySelector('td:nth-child(8) .badge');
                            const status = statusElement ? statusElement.textContent.trim() : '';

                            const departureDateText = row.querySelector('td:nth-child(3)').textContent.trim();
                            const dateParts = departureDateText.split('/');
                            // التحقق من أن التاريخ بالتنسيق الصحيح
                            const departureDate = dateParts.length === 3 ?
                                new Date(dateParts[2], dateParts[1] - 1, dateParts[0]) :
                                new Date();

                            const today = new Date();

                            const bookingsElement = row.querySelector('td:nth-child(9) .badge');
                            const bookingsCount = bookingsElement ? parseInt(bookingsElement.textContent.trim()) :
                                0;

                            if (tab === 'active' && status !== 'نشطة') {
                                row.classList.add('d-none');
                            } else if (tab === 'upcoming' && (status !== 'نشطة' || departureDate <= today)) {
                                row.classList.add('d-none');
                            } else if (tab === 'bookings' && bookingsCount === 0) {
                                row.classList.add('d-none');
                            }
                        } catch (err) {
                            console.error('خطأ في معالجة صف الرحلة', err);
                        }
                    });

                    // تحديث عدد النتائج المعروضة
                    this.updateResultsCount();
                },

                filterTable() {
                    // إذا كان التبويب الحالي هو "الحجوزات"، نستخدم دالة فلترة الحجوزات
                    if (this.currentTab === 'All-Bookings') {
                        this.filterBookingsTable();
                        return;
                    }

                    // تطبيق الفلتر على الرحلات
                    this.applyTripFilter(this.currentTab);
                },

                filterBookingsTable() {
                    const searchTerm = this.searchTerm.toLowerCase();
                    const rows = document.querySelectorAll('#bookings-table-container tbody tr');

                    if (searchTerm === '') {
                        rows.forEach(row => row.style.display = '');
                    } else {
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }

                    // تحديث عدد نتائج البحث المعروضة
                    this.updateBookingsResultsCount();
                },

                updateResultsCount() {
                    const visibleRows = document.querySelectorAll('#trips-table-container .trip-row:not(.d-none)').length;
                    const countElement = document.querySelector('.card-footer .text-muted.small');

                    if (countElement) {
                        if (this.searchTerm) {
                            countElement.textContent = `نتائج البحث: ${visibleRows} رحلة`;
                        } else if (this.currentTab === 'all') {
                            countElement.textContent = `إجمالي الرحلات: {{ $landTrips->total() }}`;
                        } else if (this.currentTab === 'active') {
                            countElement.textContent = `الرحلات النشطة: ${visibleRows}`;
                        } else if (this.currentTab === 'bookings') {
                            countElement.textContent = `رحلات بها حجوزات: ${visibleRows}`;
                        } else if (this.currentTab === 'upcoming') {
                            countElement.textContent = `الرحلات القادمة: ${visibleRows}`;
                        }
                    }
                },

                updateBookingsResultsCount() {
                    const visibleRows = Array.from(document.querySelectorAll('#bookings-table-container tbody tr'))
                        .filter(row => row.style.display !== 'none').length;
                    const countElement = document.querySelector('.card-footer .text-muted.small');

                    if (countElement) {
                        if (this.searchTerm) {
                            countElement.textContent = `نتائج البحث: ${visibleRows} حجز`;
                        } else {
                            countElement.textContent = `إجمالي الحجوزات: ${visibleRows}`;
                        }
                    }
                },

                confirmDelete(id) {
                    this.deleteUrl = `{{ url('admin/land-trips') }}/${id}`;
                    this.deleteModal.show();
                },

                exportBookingsToExcel() {
                    const table = document.querySelector('#bookings-table-container table');

                    if (!window.XLSX) {
                        alert('لم يتم تحميل مكتبة XLSX. تأكد من تضمين المكتبة في الصفحة.');
                        return;
                    }

                    let wb = XLSX.utils.table_to_book(table, {
                        sheet: "الحجوزات"
                    });

                    const fileName = `حجوزات-الرحلات-${new Date().toISOString().split('T')[0]}.xlsx`;
                    XLSX.writeFile(wb, fileName);
                }
            };
        }
    </script>

    <!-- إضافة مكتبة SheetJS لدعم تصدير Excel -->
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
@endpush
