@extends('layouts.app')

@section('title', 'تفاصيل الرحلة البرية')

@push('styles')
<style>
    /* تنسيقات عامة */
    .trip-header {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .trip-info-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .trip-info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        padding: 15px 20px;
        font-weight: 600;
    }
    
    .info-item {
        padding: 0.7rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.8rem;
        background-color: #f8f9fa;
        transition: all 0.2s ease;
    }
    
    .info-item:hover {
        background-color: #f0f2f5;
    }
    
    .info-label {
        color: #495057;
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .info-value {
        color: #212529;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .badge-currency {
        padding: 0.3em 0.6em;
        border-radius: 4px;
        font-size: 0.75em;
        font-weight: 500;
    }
    
    .badge-kwd {
        background-color: #17a2b8;
        color: white;
    }
    
    .badge-sar {
        background-color: #6c757d;
        color: white;
    }
    
    .price-table th {
        font-weight: 600;
        background-color: #f8f9fa;
        color: #495057;
    }
    
    .price-table td {
        vertical-align: middle;
    }
    
    .price-cell {
        display: flex;
        align-items: center;
    }
    
    .price-value {
        font-weight: 600;
        margin-right: 5px;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn-custom {
        border-radius: 8px;
        font-weight: 500;
        padding: 8px 16px;
        transition: all 0.3s;
    }
    
    .btn-custom:hover {
        transform: translateY(-2px);
    }
    
    .status-badge {
        padding: 6px 12px;
        font-weight: 500;
        border-radius: 6px;
    }
</style>
@endpush

@php
function getCurrencySymbol($currency) {
    return $currency == 'KWD' ? 'د.ك' : 'ر.س';
}

function getCurrencyClass($currency) {
    return $currency == 'KWD' ? 'badge-kwd' : 'badge-sar';
}
@endphp

@section('content')
    <div class="container mt-4">
        <!-- بطاقة الرأس مع معلومات أساسية -->
        <div class="card trip-header mb-4">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-9">
                        <div class="p-4">
                            <div class="d-flex align-items-center mb-3">
                                <h2 class="mb-0 fw-bold">{{ $landTrip->tripType->name ?? 'رحلة برية' }}</h2>
                                <div class="ms-auto">
                                    @if ($landTrip->status == 'active')
                                        <span class="badge status-badge bg-success">نشطة</span>
                                    @elseif($landTrip->status == 'inactive')
                                        <span class="badge status-badge bg-warning">غير نشطة</span>
                                    @elseif($landTrip->status == 'expired')
                                        <span class="badge status-badge bg-secondary">منتهية</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-hotel me-2 text-primary"></i>
                                    <span class="fw-medium">{{ $landTrip->hotel->name ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap">
                                <div class="me-4 mb-2">
                                    <div class="text-muted small">تاريخ المغادرة</div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plane-departure me-2 text-success"></i>
                                        <span>{{ $landTrip->departure_date->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                                <div class="me-4 mb-2">
                                    <div class="text-muted small">تاريخ العودة</div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plane-arrival me-2 text-danger"></i>
                                        <span>{{ $landTrip->return_date->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">مدة الرحلة</div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-day me-2 text-info"></i>
                                        <span>{{ $landTrip->days_count }} يوم</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 bg-light">
                        <div class="p-4 h-100 d-flex flex-column justify-content-center">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.land-trips.edit', $landTrip->id) }}" class="btn btn-warning btn-custom">
                                    <i class="fas fa-edit me-1"></i> تعديل الرحلة
                                </a>
                                  <!-- زر إضافة حجز جديد -->
            <a href="{{ route('admin.land-trips.create-booking', $landTrip->id) }}" class="btn btn-success btn-custom">
                <i class="fas fa-plus-circle me-1"></i> إنشاء حجز
            </a>
                                <a href="{{ route('admin.land-trips.bookings', $landTrip->id) }}" class="btn btn-primary btn-custom">
                                    <i class="fas fa-calendar-check me-1"></i> عرض الحجوزات
                                </a>
                                <a href="{{ route('admin.land-trips.index') }}" class="btn btn-secondary btn-custom">
                                    <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- القسم الأيمن - تفاصيل الرحلة -->
            <div class="col-lg-4 mb-4">
                <div class="card trip-info-card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-info-circle me-2"></i> معلومات الرحلة
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <div class="info-label">جهة الحجز</div>
                            <div class="info-value">
                                <i class="fas fa-building me-1 text-primary"></i>
                                {{ $landTrip->agent->name ?? 'غير معروف' }}
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">الموظف المسؤول</div>
                            <div class="info-value">
                                <i class="fas fa-user-tie me-1 text-primary"></i>
                                {{ $landTrip->employee->name ?? 'غير معروف' }}
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">رقم الرحلة</div>
                            <div class="info-value">
                                <i class="fas fa-hashtag me-1 text-primary"></i>
                                #{{ $landTrip->id }}
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">
                                <i class="fas fa-calendar-plus me-1 text-primary"></i>
                                {{ $landTrip->created_at->format('Y-m-d') }}
                            </div>
                        </div>
                        
                        @if($landTrip->notes)
                        <div class="info-item">
                            <div class="info-label">ملاحظات</div>
                            <div class="info-value">
                                <i class="fas fa-comment me-1 text-primary"></i>
                                {{ $landTrip->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- القسم الأيسر - أسعار الغرف -->
            <div class="col-lg-8 mb-4">
                <div class="card trip-info-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-bed me-2"></i> أسعار الغرف
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table price-table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>نوع الغرفة</th>
                                        <th>سعر التكلفة</th>
                                        <th>سعر البيع</th>
                                        <th>المتاح</th>
                                        <th>المحجوز</th>
                                        <th>إجمالي المبيعات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($landTrip->roomPrices as $index => $roomPrice)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-medium">{{ $roomPrice->roomType->room_type_name ?? 'غير معروف' }}</div>
                                            </td>
                                            <td>
                                                <div class="price-cell">
                                                    <span class="price-value">{{ number_format($roomPrice->cost_price, 2) }}</span>
                                                    <span class="badge badge-currency {{ getCurrencyClass($roomPrice->currency) }}">
                                                        {{ getCurrencySymbol($roomPrice->currency) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="price-cell">
                                                    <span class="price-value">{{ number_format($roomPrice->sale_price, 2) }}</span>
                                                    <span class="badge badge-currency {{ getCurrencyClass($roomPrice->currency) }}">
                                                        {{ getCurrencySymbol($roomPrice->currency) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if (isset($bookingSummary[$roomPrice->id]['available']))
                                                    @if ($bookingSummary[$roomPrice->id]['available'] === null)
                                                        <span class="badge bg-info">غير محدود</span>
                                                    @else
                                                        <span class="fw-medium">{{ $bookingSummary[$roomPrice->id]['available'] }}</span>
                                                    @endif
                                                @else
                                                    @if ($roomPrice->allotment)
                                                        <span class="fw-medium">{{ $roomPrice->allotment }}</span>
                                                    @else
                                                        <span class="badge bg-info">غير محدود</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ $bookingSummary[$roomPrice->id]['booked'] ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <div class="price-cell">
                                                    <span class="price-value">{{ number_format($bookingSummary[$roomPrice->id]['total_amount'] ?? 0, 2) }}</span>
                                                    <span class="badge badge-currency {{ getCurrencyClass($roomPrice->currency) }}">
                                                        {{ getCurrencySymbol($roomPrice->currency) }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    لا توجد أسعار غرف لعرضها
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
