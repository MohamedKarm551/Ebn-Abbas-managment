@extends('layouts.app')

@section('title', 'تعديل الألوتمنت')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">تعديل الألوتمنت</h6>
                    <div>
                        <a href="{{ route('allotments.show', $allotment->id) }}" class="btn btn-sm btn-info mx-1">
                            <i class="fas fa-eye ml-1"></i> عرض التفاصيل
                        </a>
                        <a href="{{ route('allotments.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('allotments.update', $allotment->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hotel_id">الفندق <span class="text-danger">*</span></label>
                                <select name="hotel_id" id="hotel_id" class="form-control @error('hotel_id') is-invalid @enderror" required>
                                    <option value="">اختر الفندق</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ old('hotel_id', $allotment->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }} ({{ $hotel->location ?: 'غير محدد' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('hotel_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="start_date">تاريخ البداية <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" 
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date', $allotment->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="end_date">تاريخ النهاية <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" 
                                    class="form-control @error('end_date') is-invalid @enderror"
                                    value="{{ old('end_date', $allotment->end_date->format('Y-m-d')) }}" required>
                                @error('end_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="rooms_count">عدد الغرف <span class="text-danger">*</span></label>
                                <input type="number" name="rooms_count" id="rooms_count" 
                                    class="form-control @error('rooms_count') is-invalid @enderror"
                                    value="{{ old('rooms_count', $allotment->rooms_count) }}" min="1" required>
                                @error('rooms_count')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="rate_per_room">سعر الغرفة</label>
                                <input type="number" name="rate_per_room" id="rate_per_room" 
                                    class="form-control @error('rate_per_room') is-invalid @enderror"
                                    value="{{ old('rate_per_room', $allotment->rate_per_room) }}" min="0" step="0.01">
                                @error('rate_per_room')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="currency">العملة <span class="text-danger">*</span></label>
                                <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                    <option value="SAR" {{ old('currency', $allotment->currency) == 'SAR' ? 'selected' : '' }}>ريال سعودي (SAR)</option>
                                    <option value="USD" {{ old('currency', $allotment->currency) == 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                                    <option value="EUR" {{ old('currency', $allotment->currency) == 'EUR' ? 'selected' : '' }}>يورو (EUR)</option>
                                    <option value="EGP" {{ old('currency', $allotment->currency) == 'EGP' ? 'selected' : '' }}>جنيه مصري (EGP)</option>
                                </select>
                                @error('currency')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="status">الحالة <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $allotment->status) == 'active' ? 'selected' : '' }}>نشط</option>
                                    <option value="cancelled" {{ old('status', $allotment->status) == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes">ملاحظات</label>
                                <textarea name="notes" id="notes" rows="3" 
                                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $allotment->notes) }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div id="calculation" class="alert alert-info">
                                    <h6 class="fw-bold">ملخص الألوتمنت:</h6>
                                    <div id="summary"></div>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save ml-1"></i> تحديث الألوتمنت
                                </button>
                                <a href="{{ route('allotments.index') }}" class="btn btn-light">إلغاء</a>
                            </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        // الدوال لحساب مدة الألوتمنت وعرض ملخص
        function calculateSummary() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const roomsCount = document.getElementById('rooms_count').value;
            const ratePerRoom = document.getElementById('rate_per_room').value;
            const currency = document.getElementById('currency').value;
            
            if (startDate && endDate && roomsCount) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                // التحقق من صحة التواريخ
                if (end >= start) {
                    const daysCount = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    const totalRoomsNights = daysCount * roomsCount;
                    
                    let summary = `
                        <div>عدد الأيام: <strong>${daysCount} يوم</strong></div>
                        <div>إجمالي الغرف/ليالي: <strong>${totalRoomsNights} غرفة/ليلة</strong></div>
                    `;
                    
                    // إضافة قيمة الألوتمنت إذا تم إدخال السعر
                    if (ratePerRoom && ratePerRoom > 0) {
                        const totalValue = totalRoomsNights * ratePerRoom;
                        summary += `<div>إجمالي قيمة الألوتمنت: <strong>${totalValue.toLocaleString()} ${currency}</strong></div>`;
                    }
                    
                    document.getElementById('summary').innerHTML = summary;
                    document.getElementById('calculation').classList.remove('d-none');
                } else {
                    document.getElementById('calculation').classList.add('d-none');
                }
            } else {
                document.getElementById('calculation').classList.add('d-none');
            }
        }
        
        // أضف مستمعات الأحداث للحقول
        document.getElementById('start_date').addEventListener('change', calculateSummary);
        document.getElementById('end_date').addEventListener('change', calculateSummary);
        document.getElementById('rooms_count').addEventListener('input', calculateSummary);
        document.getElementById('rate_per_room').addEventListener('input', calculateSummary);
        document.getElementById('currency').addEventListener('change', calculateSummary);
        
        // حساب أولي
        calculateSummary();
    });
</script>
@endpush