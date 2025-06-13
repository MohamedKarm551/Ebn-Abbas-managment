@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">حجوزات الرحلات البرية</h1>
            <a href="{{ route('company.land-trips.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-search me-1"></i> استعراض الرحلات المتاحة
            </a>
        </div>

        <!-- بطاقات الإحصائيات -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-primary bg-gradient text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-#6c757d-50">إجمالي الحجوزات</h6>
                                <h2 class="mb-0">{{ number_format($stats['totalBookings']) }}</h2>
                            </div>
                            <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                <i class="fas fa-calendar-check fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-success bg-gradient text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-#6c757d-50">الرحلات القادمة</h6>
                                <h2 class="mb-0">{{ number_format($stats['upcomingBookings']) }}</h2>
                            </div>
                            <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                <i class="fas fa-plane-departure fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-info bg-gradient text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-#6c757d-50">حجوزات الشهر الحالي</h6>
                                <h2 class="mb-0">{{ number_format($stats['currentMonthBookings']) }}</h2>
                            </div>
                            <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                <i class="fas fa-calendar-alt fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 bg-warning bg-gradient text-white h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-#6c757d-50">إجمالي المدفوعات</h6>
                                @if (count($stats['paymentsByCurrency']) > 1)
                                    @foreach ($stats['paymentsByCurrency'] as $currency => $amount)
                                        <div class="mb-1">
                                            <span class="fs-5 fw-bold">{{ number_format($amount) }}</span>
                                            <span
                                                class="fs-6">{{ $stats['currencySymbols'][$currency] ?? $currency }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    @php
                                        $currency = key($stats['paymentsByCurrency']) ?? '';
                                        $amount = $stats['paymentsByCurrency'][$currency] ?? $stats['totalSpent'];
                                    @endphp
                                    <h2 class="mb-0">{{ number_format($amount) }} <span
                                            class="fs-6">{{ $stats['currencySymbols'][$currency] ?? $currency }}</span>
                                    </h2>
                                @endif
                            </div>
                            <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                <i class="fas fa-money-bill-wave fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- بطاقة البحث والفلترة -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i> فلترة وبحث</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('company.land-trips.my-bookings') }}" method="GET" class="row g-3">
                    <!-- بحث سريع -->
                    <div class="col-md-4">
                        <label for="search" class="form-label">بحث سريع</label>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="اسم العميل أو رقم الرحلة" value="{{ request('search') }}">
                    </div>

                    <!-- فلتر التاريخ -->
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">من تاريخ</label>
                        <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                            value="{{ request('start_date') }}" placeholder="DD/MM/YYYY">
                    </div>

                    <div class="col-md-2">
                        <label for="end_date" class="form-label">إلى تاريخ</label>
                        <input type="text" class="form-control datepicker" id="end_date" name="end_date"
                            value="{{ request('end_date') }}" placeholder="DD/MM/YYYY">
                    </div>

                    <!-- فلتر نوع الرحلة -->
                    <div class="col-md-2">
                        <label for="trip_type_id" class="form-label">نوع الرحلة</label>
                        <select class="form-select" id="trip_type_id" name="trip_type_id">
                            <option value="">الكل</option>
                            @foreach ($tripTypes as $tripType)
                                <option value="{{ $tripType->id }}"
                                    {{ request('trip_type_id') == $tripType->id ? 'selected' : '' }}>
                                    {{ $tripType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- فلتر حالة الرحلة -->
                    <div class="col-md-2">
                        <label for="status" class="form-label">حالة الرحلة</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">الكل</option>
                            <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>قادمة
                            </option>
                            <option value="current" {{ request('status') === 'current' ? 'selected' : '' }}>جارية</option>
                            <option value="past" {{ request('status') === 'past' ? 'selected' : '' }}>سابقة</option>
                        </select>
                    </div>

                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                        <a href="{{ route('company.land-trips.my-bookings') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i> إعادة تعيين
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- رسائل النجاح والخطأ -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- جدول الحجوزات -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2 text-primary"></i> قائمة الحجوزات</h5>
                <span class="badge bg-primary">{{ $bookings->total() }} حجز</span>
            </div>
            <div class="card-body p-0">
                @if ($bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>العميل</th>
                                    <th>تفاصيل الرحلة</th>
                                    <th>تفاصيل الحجز</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ الحجز</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    <tr
                                        class="{{ \Carbon\Carbon::parse($booking->landTrip->departure_date)->isPast() && \Carbon\Carbon::parse($booking->landTrip->return_date)->isFuture() ? 'table-success' : '' }}
                                    {{ \Carbon\Carbon::parse($booking->landTrip->departure_date)->isFuture() ? '' : '' }}
                                    {{ \Carbon\Carbon::parse($booking->landTrip->return_date)->isPast() ? 'table-secondary' : '' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div>{{ $booking->client_name }}</div>
                                            <span class="badge bg-dark">{{ $booking->rooms }} غرفة</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary me-2">{{ $booking->landTrip->id }}#</span>
                                                <div>
                                                    <div>{{ $booking->landTrip->tripType->name ?? 'غير معروف' }}</div>
                                                    <small class="text-#6c757d">
                                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                        {{ $booking->landTrip->hotel->name ?? 'غير معروف' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small>
                                                    <i class="fas fa-plane-departure text-primary me-1"></i>
                                                    {{ \Carbon\Carbon::parse($booking->landTrip->departure_date)->format('d/m/Y') }}
                                                </small>
                                                <small>
                                                    <i class="fas fa-plane-arrival text-success me-1"></i>
                                                    {{ \Carbon\Carbon::parse($booking->landTrip->return_date)->format('d/m/Y') }}
                                                </small>
                                                <small>
                                                    <i class="fas fa-bed text-info me-1"></i>
                                                    {{ $booking->roomPrice->roomType->room_type_name ?? 'غير معروف' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="mb-1 text-primary fw-bold">
                                                {{ number_format($booking->amount_due_from_company) }}
                                                {{ $booking->currency }}</h6>
                                            <small class="text-#6c757d">{{ number_format($booking->sale_price) }} ×
                                                {{ $booking->rooms }} </small>
                                        </td>
                                        <td>
                                            <div>{{ $booking->created_at->format('d/m/Y') }}</div>
                                            <small
                                                class="text-#6c757d">{{ $booking->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('company.land-trips.voucher', $booking->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                                <a href="{{ route('company.land-trips.downloadVoucher', $booking->id) }}"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('images/no-data.svg') }}" alt="لا توجد بيانات" class="img-fluid mb-3"
                            style="max-height: 200px">
                        <h5>لم يتم العثور على أي حجوزات</h5>
                        <p class="text-#6c757d">قم بتعديل معايير البحث أو قم بحجز رحلة جديدة</p>
                        <a href="{{ route('company.land-trips.index') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-search me-1"></i> استعراض الرحلات المتاحة
                        </a>
                    </div>
                @endif
            </div>
            @if ($bookings->hasPages())
                <!-- HTML - جزء الترقيم -->
                <div class="pagination-container">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-2 gap-2">
                        <!-- معلومات الترقيم -->
                        <div class="pagination-info order-2 order-md-1 text-center text-md-start">
                            <p class="mb-0">
                                عرض
                                <strong>{{ $bookings->firstItem() }}</strong>
                                إلى
                                <strong>{{ $bookings->lastItem() }}</strong>
                                من
                                <strong>{{ $bookings->total() }}</strong>
                                حجز
                            </p>
                        </div>

                        <!-- الترقيم نفسه -->
                        <nav class="order-1 order-md-2">
                            <ul class="pagination justify-content-center justify-content-md-end mb-0">
                                @if ($bookings->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">&laquo;</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $bookings->previousPageUrl() }}">&laquo;</a>
                                    </li>
                                @endif

                                @foreach ($bookings->getUrlRange(1, $bookings->lastPage()) as $page => $url)
                                    @if ($page == $bookings->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                @if ($bookings->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $bookings->nextPageUrl() }}">&raquo;</a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">&raquo;</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/preventClick.js') }}"></script>
    <script>
        $(document).ready(function() {
            // تهيئة أداة اختيار التاريخ
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'ar'
            });

            // تنشيط التلميحات
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // تلوين الصفوف حسب حالة الرحلة
            $('.table tbody tr').each(function() {
                // رمز التلوين موجود بالفعل في الكلاسات داخل الجدول
            });
        });
    </script>
@endpush
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.querySelector('form[method="GET"]');
        const searchInput = document.querySelector('input[name="search"]');

        if (searchForm && searchInput) {
            // منع الأحرف والأنماط الخطرة
            searchInput.addEventListener('input', function(e) {
                const value = this.value;
                const dangerousPattern =
                    /(<|>|\(|\)|{|}|\[|\]|script|alert|javascript:|onerror|onclick|eval)/i;

                if (dangerousPattern.test(value)) {
                    // تغيير لون حدود الحقل للتنبيه
                    this.style.borderColor = 'red';
                    this.dataset.hasError = 'true';

                    // إظهار رسالة تحذير
                    let errorMessage = this.parentNode.querySelector('.search-error-message');
                    if (!errorMessage) {
                        errorMessage = document.createElement('div');
                        errorMessage.className = 'search-error-message text-danger small mt-1';
                        this.parentNode.appendChild(errorMessage);
                    }
                    errorMessage.textContent = 'يحتوي البحث على محتوى غير مسموح به';
                } else {
                    // إزالة التنسيق والرسالة عند تصحيح المدخلات
                    this.style.borderColor = '';
                    this.dataset.hasError = 'false';

                    const errorMessage = this.parentNode.querySelector('.search-error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                }
            });

            // التحقق قبل إرسال النموذج
            searchForm.addEventListener('submit', function(e) {
                if (searchInput.dataset.hasError === 'true') {
                    e.preventDefault();
                    alert('الرجاء إزالة المحتوى غير المسموح به قبل البحث');
                }
            });
        }
    });
</script>
@push('styles')
    <style>
        /* تخصيص مظهر جدول البيانات */
        .table {
            --bs-table-hover-bg: rgba(13, 110, 253, 0.04);
        }

        /* تعديل حجم البيانات في الجدول للشاشات الصغيرة */
        @media (max-width: 767.98px) {
            .table {
                font-size: 0.875rem;
            }

            .card-body {
                padding: 0.75rem;
            }
        }

        /* تنسيق البطاقات الإحصائية */
        .card {
            transition: all 0.3s;
            border-radius: 10px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        /* تحسين مظهر الأزرار */
        .btn {
            border-radius: 6px;
        }

        /* تنسيق لعرض صفوف الجدول بشكل أفضل */
        .table tr:last-child td {
            border-bottom: none;
        }

        /* تحسين مظهر أدوات الفلترة */
        .form-control,
        .form-select {
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* تحسين مظهر الترقيم */
        /* دعم اتجاه RTL بشكل صحيح */
        body,
        .pagination-container {
            direction: rtl;
        }

        /* صندوق الترقيم */
        .pagination-container {
            background: #f8f9fa;
            border-radius: 0 0 10px 10px;
            padding: 0.75rem 0.5rem;
            margin-top: 1rem;
        }

        .pagination-info {
            color: #6c757d;
            font-size: 0.95rem;
            margin: 0;
            min-width: 170px;
        }

        .pagination {
            --bs-pagination-border-radius: 6px;
            margin-bottom: 0;
        }

        .pagination .page-link {
            margin: 0 2px;
            border-radius: 4px;
            min-width: 36px;
            text-align: center;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            font-weight: bold;
            color: #fff;
        }

        .page-link[aria-label] {
            font-size: 1.2rem;
            line-height: 1.2;
        }

        @media (max-width: 575.98px) {
            .pagination-container .d-flex {
                flex-direction: column !important;
                gap: 0.5rem !important;
                align-items: stretch !important;
            }

            .pagination-info {
                text-align: center;
            }

            .pagination {
                justify-content: center;
            }
        }
    </style>
@endpush
