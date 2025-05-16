@extends('layouts.app')

@section('title', 'إنشاء حجز جديد للرحلة البرية')

@push('styles')
<style>
    .booking-form-card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .trip-info-card {
        background-color: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 20px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .trip-info-item {
        display: flex;
        margin-bottom: 10px;
    }
    
    .trip-info-label {
        font-weight: 600;
        min-width: 120px;
    }
    
    .room-type-card {
        transition: all 0.3s;
        cursor: pointer;
        border: 2px solid #dee2e6;
        border-radius: 8px;
    }
    
    .room-type-card.selected {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
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
</style>
@endpush

@section('content')
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card booking-form-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="m-0">إنشاء حجز جديد للرحلة البرية</h4>
                </div>
                <div class="card-body">
                    <!-- معلومات الرحلة -->
                    <div class="card trip-info-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">معلومات الرحلة</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="trip-info-item">
                                        <div class="trip-info-label">نوع الرحلة:</div>
                                        <div>{{ $landTrip->tripType->name ?? 'غير محدد' }}</div>
                                    </div>
                                    <div class="trip-info-item">
                                        <div class="trip-info-label">الفندق:</div>
                                        <div>{{ $landTrip->hotel->name ?? 'غير محدد' }}</div>
                                    </div>
                                    <div class="trip-info-item">
                                        <div class="trip-info-label">جهة الحجز:</div>
                                        <div>{{ $landTrip->agent->name ?? 'غير محدد' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="trip-info-item">
                                        <div class="trip-info-label">تاريخ المغادرة:</div>
                                        <div>{{ $landTrip->departure_date->format('Y-m-d') }}</div>
                                    </div>
                                    <div class="trip-info-item">
                                        <div class="trip-info-label">تاريخ العودة:</div>
                                        <div>{{ $landTrip->return_date->format('Y-m-d') }}</div>
                                    </div>
                                    <div class="trip-info-item">
                                        <div class="trip-info-label">عدد الأيام:</div>
                                        <div>{{ $landTrip->days_count }} يوم</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- نموذج الحجز -->
                    <form action="{{ route('admin.land-trips.store-booking', $landTrip->id) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="company_id" class="form-label">الشركة <span class="text-danger">*</span></label>
                                <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                    <option value="">اختر الشركة...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="client_name" class="form-label">اسم العميل <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name') }}" required>
                                @error('client_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- اختيار نوع الغرفة -->
                        <div class="mb-4">
                            <label class="form-label">اختر نوع الغرفة <span class="text-danger">*</span></label>
                            @error('land_trip_room_price_id')
                                <div class="text-danger mb-2 small">{{ $message }}</div>
                            @enderror
                            
                            <div class="row">
                                @forelse($landTrip->roomPrices as $roomPrice)
                                    @php
                                        $isAvailable = true;
                                        $availableCount = 'غير محدود';
                                        
                                        if ($roomPrice->allotment !== null) {
                                            $booked = $roomAvailability[$roomPrice->id]['booked'] ?? 0;
                                            $available = $roomPrice->allotment - $booked;
                                            $availableCount = $available;
                                            $isAvailable = $available > 0;
                                        }
                                        
                                        $currencyLabel = $roomPrice->currency === 'KWD' ? 'د.ك' : 'ر.س';
                                        $badgeClass = $roomPrice->currency === 'KWD' ? 'badge-kwd' : 'badge-sar';
                                    @endphp
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="card room-type-card h-100 p-2 {{ old('land_trip_room_price_id') == $roomPrice->id ? 'selected' : '' }}" 
                                             data-room-price-id="{{ $roomPrice->id }}" 
                                             onclick="selectRoomType(this, {{ $roomPrice->id }})"
                                             @if(!$isAvailable) style="opacity: 0.5; cursor: not-allowed;" @endif>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h6 class="card-title">{{ $roomPrice->roomType->room_type_name ?? 'غير معروف' }}</h6>
                                                    <span class="badge {{ $badgeClass }}">{{ $currencyLabel }}</span>
                                                </div>
                                                <div class="mt-2">
                                                    <div class="d-flex justify-content-between">
                                                        <span>سعر التكلفة:</span>
                                                        <strong>{{ number_format($roomPrice->cost_price, 2) }} {{ $currencyLabel }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>سعر البيع:</span>
                                                        <strong>{{ number_format($roomPrice->sale_price, 2) }} {{ $currencyLabel }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-2">
                                                        <span>الغرف المتاحة:</span>
                                                        <strong class="{{ $isAvailable ? 'text-success' : 'text-danger' }}">
                                                            {{ $availableCount }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($isAvailable)
                                                <div class="card-footer bg-transparent text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="land_trip_room_price_id" 
                                                               id="room_type_{{ $roomPrice->id }}" value="{{ $roomPrice->id }}" 
                                                               {{ old('land_trip_room_price_id') == $roomPrice->id ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="room_type_{{ $roomPrice->id }}">
                                                            اختر هذا النوع
                                                        </label>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="card-footer bg-light text-center text-danger">
                                                    <small>غير متوفر</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            لا توجد خيارات للغرف متاحة لهذه الرحلة
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        
                        <!-- عدد الغرف والملاحظات -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="rooms" class="form-label">عدد الغرف <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('rooms') is-invalid @enderror" id="rooms" name="rooms" min="1" value="{{ old('rooms', 1) }}" required>
                                @error('rooms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">ملاحظات</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('admin.land-trips.show', $landTrip->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-1"></i> العودة
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> تأكيد الحجز
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function selectRoomType(element, roomPriceId) {
        // إزالة التحديد من كل الكروت
        document.querySelectorAll('.room-type-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // تحديد الكارت الحالي
        element.classList.add('selected');
        
        // تحديد الراديو بتن
        document.getElementById('room_type_' + roomPriceId).checked = true;
    }
    
    // تحديد الغرفة المختارة سابقاً عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        const selectedRoomPriceId = document.querySelector('input[name="land_trip_room_price_id"]:checked')?.value;
        
        if (selectedRoomPriceId) {
            document.querySelector(`.room-type-card[data-room-price-id="${selectedRoomPriceId}"]`)?.classList.add('selected');
        }
    });
</script>
@endpush