@extends('layouts.app')

@section('title', 'تعديل بيع ألوتمنت')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">تعديل بيع ألوتمنت</h6>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-arrow-right ml-1"></i> العودة
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('allotment-sales.update', $sale->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hotel_id">الفندق <span class="text-danger">*</span></label>
                                <select name="hotel_id" id="hotel_id" class="form-control @error('hotel_id') is-invalid @enderror" required>
                                    <option value="">اختر الفندق</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" {{ old('hotel_id', $sale->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hotel_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="allotment_id">الألوتمنت <span class="text-danger">*</span></label>
                                <select name="allotment_id" id="allotment_id" class="form-control @error('allotment_id') is-invalid @enderror" required>
                                    <option value="">اختر الألوتمنت</option>
                                    @foreach($allotments as $allotment)
                                        @php
                                            // حساب عدد الغرف المتاحة مع استثناء الغرف في هذه العملية
                                            $availableRooms = $allotment->remaining_rooms;
                                            if ($allotment->id === $sale->allotment_id) {
                                                $availableRooms += $sale->rooms_sold;
                                            }
                                        @endphp
                                        @if(($availableRooms > 0 && $allotment->status === 'active') || $allotment->id === $sale->allotment_id)
                                            <option value="{{ $allotment->id }}" 
                                                data-hotel="{{ $allotment->hotel_id }}"
                                                data-start="{{ $allotment->start_date->format('Y-m-d') }}"
                                                data-end="{{ $allotment->end_date->format('Y-m-d') }}"
                                                data-remaining="{{ $availableRooms }}"
                                                {{ old('allotment_id', $sale->allotment_id) == $allotment->id ? 'selected' : '' }}>
                                                {{ $allotment->hotel->name }} | من {{ $allotment->start_date->format('Y-m-d') }} إلى {{ $allotment->end_date->format('Y-m-d') }} | متاح: {{ $availableRooms }} غرفة
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('allotment_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="company_name">اسم الشركة <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" id="company_name" 
                                    class="form-control @error('company_name') is-invalid @enderror"
                                    value="{{ old('company_name', $sale->company_name) }}" required>
                                @error('company_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="check_in">تاريخ الدخول <span class="text-danger">*</span></label>
                                <input type="date" name="check_in" id="check_in" 
                                    class="form-control @error('check_in') is-invalid @enderror"
                                    value="{{ old('check_in', $sale->check_in->format('Y-m-d')) }}" required>
                                @error('check_in')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="check_out">تاريخ الخروج <span class="text-danger">*</span></label>
                                <input type="date" name="check_out" id="check_out" 
                                    class="form-control @error('check_out') is-invalid @enderror"
                                    value="{{ old('check_out', $sale->check_out->format('Y-m-d')) }}" required>
                                @error('check_out')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="rooms_sold">عدد الغرف <span class="text-danger">*</span></label>
                                <input type="number" name="rooms_sold" id="rooms_sold" 
                                    class="form-control @error('rooms_sold') is-invalid @enderror"
                                    value="{{ old('rooms_sold', $sale->rooms_sold) }}" min="1" required>
                                <div id="rooms_warning" class="text-danger small"></div>
                                @error('rooms_sold')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="sale_price">سعر الغرفة <span class="text-danger">*</span></label>
                                <input type="number" name="sale_price" id="sale_price" 
                                    class="form-control @error('sale_price') is-invalid @enderror"
                                    value="{{ old('sale_price', $sale->sale_price) }}" min="0" step="0.01" required>
                                @error('sale_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="currency">العملة <span class="text-danger">*</span></label>
                                <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                    <option value="SAR" {{ old('currency', $sale->currency) == 'SAR' ? 'selected' : '' }}>ريال سعودي (SAR)</option>
                                    <option value="USD" {{ old('currency', $sale->currency) == 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                                    <option value="EUR" {{ old('currency', $sale->currency) == 'EUR' ? 'selected' : '' }}>يورو (EUR)</option>
                                    <option value="EGP" {{ old('currency', $sale->currency) == 'EGP' ? 'selected' : '' }}>جنيه مصري (EGP)</option>
                                </select>
                                @error('currency')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="notes">ملاحظات</label>
                                <textarea name="notes" id="notes" rows="3" 
                                    class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $sale->notes) }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div id="calculation" class="alert alert-info">
                                    <h6 class="fw-bold">ملخص البيع:</h6>
                                    <div id="summary"></div>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save ml-1"></i> تحديث عملية البيع
                                </button>
                                <a href="{{ url()->previous() }}" class="btn btn-light">إلغاء</a>
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
        const hotelSelect = document.getElementById('hotel_id');
        const allotmentSelect = document.getElementById('allotment_id');
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        const roomsSoldInput = document.getElementById('rooms_sold');
        const salePriceInput = document.getElementById('sale_price');
        const currencySelect = document.getElementById('currency');
        
        // احتفظ بخيارات الألوتمنت الأصلية
        const originalAllotmentOptions = Array.from(allotmentSelect.options);
        
        // تصفية خيارات الألوتمنت حسب الفندق المختار
        function filterAllotments() {
            const selectedHotelId = hotelSelect.value;
            
            // استعادة جميع الخيارات الأصلية أولاً
            allotmentSelect.innerHTML = '';
            originalAllotmentOptions.forEach(option => {
                // إضافة خيار فارغ أو الخيارات التي تطابق الفندق المحدد
                if (!option.value || option.dataset.hotel === selectedHotelId) {
                    allotmentSelect.appendChild(option.cloneNode(true));
                }
            });
        }
        
        // تحديث نطاق تواريخ الدخول والخروج بناءً على الألوتمنت المحدد
        function updateDateRange() {
            const selectedOption = allotmentSelect.options[allotmentSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const startDate = selectedOption.dataset.start;
                const endDate = selectedOption.dataset.end;
                
                // تحديث الحد الأدنى والحد الأقصى لحقول التاريخ
                checkInInput.min = startDate;
                checkInInput.max = endDate;
                checkOutInput.min = startDate;
                checkOutInput.max = endDate;
                
                // تحديث التحقق من عدد الغرف المتاحة
                checkRoomsAvailability();
            }
        }
        
        // التحقق من توفر الغرف
        function checkRoomsAvailability() {
            const selectedOption = allotmentSelect.options[allotmentSelect.selectedIndex];
            const warningElement = document.getElementById('rooms_warning');
            
            if (selectedOption && selectedOption.value) {
                const remainingRooms = parseInt(selectedOption.dataset.remaining);
                const roomsRequested = parseInt(roomsSoldInput.value) || 0;
                
                if (roomsRequested > remainingRooms) {
                    warningElement.textContent = `تنبيه: عدد الغرف المطلوب (${roomsRequested}) أكبر من الغرف المتاحة (${remainingRooms}).`;
                    warningElement.classList.add('text-danger');
                } else {
                    warningElement.textContent = '';
                }
            }
        }
        
        // حساب وعرض ملخص البيع
        function calculateSummary() {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;
            const roomsSold = parseInt(roomsSoldInput.value) || 0;
            const salePrice = parseFloat(salePriceInput.value) || 0;
            const currency = currencySelect.value;
            
            if (checkIn && checkOut && roomsSold && salePrice) {
                const start = new Date(checkIn);
                const end = new Date(checkOut);
                
                if (end > start) {
                    const daysCount = Math.round((end - start) / (1000 * 60 * 60 * 24));
                    const totalRoomsNights = daysCount * roomsSold;
                    const totalValue = totalRoomsNights * salePrice;
                    
                    const summary = `
                        <div>عدد الأيام: <strong>${daysCount} يوم</strong></div>
                        <div>عدد الغرف: <strong>${roomsSold} غرفة</strong></div>
                        <div>سعر الغرفة: <strong>${salePrice} ${currency}</strong></div>
                        <div>إجمالي الغرف/الليالي: <strong>${totalRoomsNights} غرفة/ليلة</strong></div>
                        <div>إجمالي قيمة البيع: <strong>${totalValue.toLocaleString()} ${currency}</strong></div>
                    `;
                    
                    document.getElementById('summary').innerHTML = summary;
                    document.getElementById('calculation').classList.remove('d-none');
                } else {
                    document.getElementById('calculation').classList.add('d-none');
                }
            } else {
                document.getElementById('calculation').classList.add('d-none');
            }
        }
        
        // أحداث التغيير
        hotelSelect.addEventListener('change', filterAllotments);
        allotmentSelect.addEventListener('change', function() {
            updateDateRange();
            calculateSummary();
        });
        checkInInput.addEventListener('change', calculateSummary);
        checkOutInput.addEventListener('change', calculateSummary);
        roomsSoldInput.addEventListener('input', function() {
            checkRoomsAvailability();
            calculateSummary();