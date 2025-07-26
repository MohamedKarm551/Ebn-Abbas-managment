{{-- ملف جديد: resources/views/admin/company-payments/bookings.blade.php --}}
@extends('layouts.app')

@section('title', 'حجوزات شركة ' . $company->name)

@push('styles')
<style>
    .bookings-container {
        background: #f8fafc;
        min-height: 100vh;
        padding: 1.5rem;
    }
    
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #718096;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .bookings-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .table th {
        background: #f7fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        color: #2d3748;
    }
    
    .booking-amount {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .booking-amount.sar {
        background: #dcfce7;
        color: #166534;
    }
    
    .booking-amount.kwd {
        background: #fef3c7;
        color: #92400e;
    }
</style>
@endpush

@section('content')
<div class="bookings-container">
    <!-- رأس الصفحة -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">حجوزات شركة {{ $company->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.company-payments.index') }}">مدفوعات الشركات</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.company-payments.show', $company) }}">{{ $company->name }}</a>
                    </li>
                    <li class="breadcrumb-item active">الحجوزات</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.company-payments.show', $company) }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-right me-1"></i>العودة للشركة
        </a>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_bookings'] }}</div>
            <div class="stat-label">إجمالي الحجوزات</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_amount_sar'], 0) }}</div>
            <div class="stat-label">المستحق (ريال سعودي)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_amount_kwd'], 0) }}</div>
            <div class="stat-label">المستحق (دينار كويتي)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['paid_amount_sar'], 0) }}</div>
            <div class="stat-label">المدفوع (ريال سعودي)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['paid_amount_kwd'], 0) }}</div>
            <div class="stat-label">المدفوع (دينار كويتي)</div>
        </div>
    </div>

    <!-- فلاتر البحث -->
    <div class="filter-card">
        <h5 class="mb-3">
            <i class="fas fa-filter me-2"></i>فلترة البحث
        </h5>
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">من تاريخ</label>
                <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">العملة</label>
                <select class="form-select" name="currency">
                    <option value="">جميع العملات</option>
                    <option value="SAR" {{ request('currency') == 'SAR' ? 'selected' : '' }}>ريال سعودي</option>
                    <option value="KWD" {{ request('currency') == 'KWD' ? 'selected' : '' }}>دينار كويتي</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    <option value="">جميع الحالات</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>في الانتظار</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">بحث</label>
                <input type="text" class="form-control" name="search" placeholder="اسم العميل" value="{{ request('search') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>بحث
                </button>
                <a href="{{ route('admin.company-payments.bookings', $company) }}" class="btn btn-outline-secondary">
                    إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <!-- جدول الحجوزات -->
    <div class="bookings-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>معلومات العميل</th>
                        <th>تفاصيل الرحلة</th>
                        <th>تفاصيل الحجز</th>
                        <th>المبالغ المالية</th>
                        <th>الحالة</th>
                        <th>التواريخ</th>
                        <th width="10%">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $index => $booking)
                    <tr>
                        <td>{{ $bookings->firstItem() + $index }}</td>
                        <td>
                            <div>
                                <strong>{{ $booking->client_name }}</strong>
                                @if($booking->client_phone)
                                    <br><small class="text-muted">
                                        <i class="fas fa-phone fa-xs"></i> {{ $booking->client_phone }}
                                    </small>
                                @endif
                                @if($booking->client_email)
                                    <br><small class="text-muted">
                                        <i class="fas fa-envelope fa-xs"></i> {{ $booking->client_email }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                {{-- <strong>{{ $booking->landTrip->destination ?? 'غير محدد' }}</strong> --}}
                                @if($booking->landTrip->hotel)
                                    <br><small class="text-muted">
                                        <i class="fas fa-hotel fa-xs"></i> {{ $booking->landTrip->hotel->name }}
                                    </small>
                                @endif
                                @if($booking->landTrip->departure_date)
                                    <br><small class="text-muted">
                                        <i class="fas fa-calendar fa-xs"></i> 
                                        {{ $booking->landTrip->departure_date->format('d/m/Y') }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $booking->rooms }} غرفة</strong>
                                @if($booking->roomPrice && $booking->roomPrice->roomType)
                                    <br><small class="text-muted">
                                        {{ $booking->roomPrice->roomType->room_type_name }}
                                    </small>
                                @endif
                                @if($booking->days)
                                    <br><small class="text-muted">
                                        {{ $booking->days }} ليلة
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="booking-amount {{ $booking->currency === 'SAR' ? 'sar' : 'kwd' }}">
                                    {{ number_format($booking->amount_due_from_company, 2) }} {{ $booking->currency }}
                                </span>
                                @if($booking->amount_due_to_agent > 0)
                                    <br><small class="text-muted">
                                        للوكيل: {{ number_format($booking->amount_due_to_agent, 2) }} {{ $booking->currency }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                {{ $booking->status === 'confirmed' ? 'مؤكد' : 'في الانتظار' }}
                            </span>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $booking->created_at->format('d/m/Y') }}</strong>
                                <br><small class="text-muted">
                                    {{ $booking->created_at->format('H:i') }}
                                </small>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm">
                                @if(Route::has('admin.land-trips.bookings.show'))
                                    <a href="{{ route('admin.land-trips.bookings.show', $booking) }}" 
                                       class="btn btn-outline-primary btn-sm" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                @if(Route::has('admin.land-trips.bookings.edit'))
                                    <a href="{{ route('admin.land-trips.bookings.edit', $booking) }}" 
                                       class="btn btn-outline-warning btn-sm" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5>لا توجد حجوزات</h5>
                            <p class="text-muted">لم يتم العثور على حجوزات تطابق معايير البحث</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- الباجينيشن -->
      @if($bookings->hasPages())
    <div class="pagination-container mt-2 p-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
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
            <nav class="order-1 order-md-2" aria-label="pagination">
                <ul class="pagination">
                    {{-- الصفحة الأولى --}}
                    @if ($bookings->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-angle-double-right"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $bookings->appends(request()->query())->url(1) }}" title="الصفحة الأولى">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    @endif

                    {{-- الصفحة السابقة --}}
                    @if ($bookings->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-angle-right"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $bookings->appends(request()->query())->previousPageUrl() }}" title="السابق">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    @endif

                    {{-- أرقام الصفحات --}}
                    @foreach ($bookings->appends(request()->query())->getUrlRange(1, $bookings->lastPage()) as $page => $url)
                        @if ($page == $bookings->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            {{-- عرض صفحات محددة فقط للشاشات الصغيرة --}}
                            @if ($page == 1 || $page == $bookings->lastPage() || ($page >= $bookings->currentPage() - 1 && $page <= $bookings->currentPage() + 1))
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @elseif ($page == $bookings->currentPage() - 2 || $page == $bookings->currentPage() + 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif
                    @endforeach

                    {{-- الصفحة التالية --}}
                    @if ($bookings->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $bookings->appends(request()->query())->nextPageUrl() }}" title="التالي">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-angle-left"></i>
                            </span>
                        </li>
                    @endif

                    {{-- الصفحة الأخيرة --}}
                    @if ($bookings->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $bookings->appends(request()->query())->url($bookings->lastPage()) }}" title="الصفحة الأخيرة">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-angle-double-left"></i>
                            </span>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحسين تجربة المستخدم للفلاتر
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('select, input[type="date"]');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value) {
                this.classList.add('bg-light');
            } else {
                this.classList.remove('bg-light');
            }
        });
        
        // تطبيق الستايل المبدئي
        if (input.value) {
            input.classList.add('bg-light');
        }
    });
});
</script>
@endpush