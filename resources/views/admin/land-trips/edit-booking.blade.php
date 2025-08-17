{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\land-trips\edit-booking.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل حجز الرحلة البرية')

@push('styles')
    <style>
        .edit-booking-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .booking-header {
            background: linear-gradient(120deg, #10b981 60%, #2563eb 100%);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            margin-bottom: 0;
            background: linear-gradient(120deg, #10b981 60%, #2563eb 100%);



            position: relative;
            overflow: hidden;
        }

        .booking-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: headerFloat 6s ease-in-out infinite;
        }

        @keyframes headerFloat {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(-20px, -20px) rotate(180deg);
            }
        }

        .booking-form {
            background: white;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border: 1px solid #e1e5e9;
            border-top: none;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .section-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .cost-display {
            background: #e8f4f8;
            border: 2px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .cost-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .total-cost {
            border-top: 2px solid #17a2b8;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 700;
            color: #17a2b8;
            font-size: 1.1em;
        }

        .btn-update {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-cancel {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }

        .availability-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 10px;
            margin-top: 5px;
            font-size: 0.9em;
        }

        .trip-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="edit-booking-container">
        {{-- رأس الصفحة --}}
        <div class="booking-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-1">
                        <i class="fas fa-edit me-2"></i>
                        تعديل حجز الرحلة البرية
                    </h2>
                    <p class="mb-0 opacity-75">
                        حجز رقم: #{{ $booking->id }} | العميل: {{ $booking->client_name }}
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.land-trips.bookings', $landTrip->id) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        العودة للحجوزات
                    </a>
                </div>
            </div>
        </div>

        {{-- معلومات الرحلة --}}
        <div class="trip-info">
            <h6 class="mb-2"><i class="fas fa-route me-1"></i> معلومات الرحلة:</h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>النوع:</strong> {{ $landTrip->tripType->name }}
                </div>
                <div class="col-md-3">
                    <strong>الفندق:</strong> {{ $landTrip->hotel->name ?? 'غير محدد' }}
                </div>
                <div class="col-md-3">
                    <strong>المغادرة:</strong> {{ $landTrip->departure_date->format('d/m/Y') }}
                </div>
                <div class="col-md-3">
                    <strong>العودة:</strong> {{ $landTrip->return_date->format('d/m/Y') }}
                </div>
            </div>
        </div>
        {{-- قسم تغيير الرحلة --}}
        <div class="trip-info mb-3" style="background: #fff3cd; border: 1px solid #ffeaa7;">
            <h6 class="mb-3"><i class="fas fa-exchange-alt me-1"></i> تغيير الرحلة:</h6>

            <form action="{{ route('admin.land-trips.change-booking-trip', $booking->id) }}" method="POST"
                id="changeTripForm">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="new_land_trip_id" class="form-label">اختر رحلة جديدة</label>
                        <select name="new_land_trip_id" id="new_land_trip_id" class="form-select">
                            <option value="">-- اختر رحلة جديدة --</option>
                            @foreach ($activeLandTrips as $trip)
                                <option value="{{ $trip->id }}" {{ $trip->id == $landTrip->id ? 'selected' : '' }}>
                                    {{ $trip->tripType->name }} - {{ $trip->hotel->name ?? 'غير محدد' }}
                                    ({{ $trip->departure_date->format('d/m/Y') }} -
                                    {{ $trip->return_date->format('d/m/Y') }})
                                    {{$trip->agent->name}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="new_room_type" class="form-label">نوع الغرفة</label>
                        <select name="land_trip_room_price_id" id="new_room_type" class="form-select" disabled>
                            <option value="">-- اختر نوع الغرفة --</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="new_rooms" class="form-label">عدد الغرف</label>
                        <input type="number" name="rooms" id="new_rooms" class="form-control"
                            value="{{ $booking->rooms }}" min="1" disabled>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning me-2" id="changeTripBtn" disabled>
                            <i class="fas fa-exchange-alt me-1"></i>
                            تغيير الرحلة
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelChangeBtn" style="display: none;">
                            إلغاء
                        </button>
                    </div>
                </div>

                <div id="newTripCostDisplay" class="mt-3" style="display: none;">
                    <div class="alert alert-info">
                        <h6>تكلفة الرحلة الجديدة:</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>سعر التكلفة:</strong> <span id="newCostPrice">0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>سعر البيع:</strong> <span id="newSalePrice">0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>إجمالي التكلفة:</strong> <span id="newTotalCost">0.00</span>
                            </div>
                            <div class="col-md-3">
                                <strong>إجمالي البيع:</strong> <span id="newTotalSale">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- نموذج التعديل --}}
        <form action="{{ route('admin.land-trips.update-booking', $booking->id) }}" method="POST" id="editBookingForm"
            class="booking-form">
            @csrf
            @method('PUT')

            {{-- معلومات الحجز الأساسية --}}
            <div class="form-section">
                <h5 class="section-title">
                    <i class="fas fa-user-tie text-primary"></i>
                    معلومات الحجز الأساسية
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_id" class="form-label">
                                <i class="fas fa-building me-1"></i>
                                الشركة <span class="text-danger">*</span>
                            </label>
                            <select name="company_id" id="company_id"
                                class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- اختر الشركة --</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id', $booking->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="client_name" class="form-label">
                                <i class="fas fa-user me-1"></i>
                                اسم العميل <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="client_name" id="client_name"
                                class="form-control @error('client_name') is-invalid @enderror"
                                value="{{ old('client_name', $booking->client_name) }}" required
                                placeholder="أدخل اسم العميل">
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- تفاصيل الغرف والأسعار --}}
            <div class="form-section">
                <h5 class="section-title">
                    <i class="fas fa-bed text-success"></i>
                    تفاصيل الغرف والأسعار
                </h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="land_trip_room_price_id" class="form-label">
                                <i class="fas fa-home me-1"></i>
                                نوع الغرفة <span class="text-danger">*</span>
                            </label>
                            <select name="land_trip_room_price_id" id="land_trip_room_price_id"
                                class="form-select @error('land_trip_room_price_id') is-invalid @enderror" required>
                                <option value="">-- اختر نوع الغرفة --</option>
                                @foreach ($landTrip->roomPrices as $roomPrice)
                                    <option value="{{ $roomPrice->id }}" data-cost-price="{{ $roomPrice->cost_price }}"
                                        data-sale-price="{{ $roomPrice->sale_price }}"
                                        data-currency="{{ $roomPrice->currency }}"
                                        data-allotment="{{ $roomPrice->allotment }}"
                                        data-available="{{ $roomAvailability[$roomPrice->id]['available'] ?? 'غير محدود' }}"
                                        {{ old('land_trip_room_price_id', $booking->land_trip_room_price_id) == $roomPrice->id ? 'selected' : '' }}>
                                        {{ $roomPrice->roomType->room_type_name }} -
                                        {{ number_format($roomPrice->sale_price, 2) }} {{ $roomPrice->currency }}
                                        @if ($roomPrice->allotment)
                                            (متاح: {{ $roomAvailability[$roomPrice->id]['available'] ?? 0 }} غرفة)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('land_trip_room_price_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="availability-info" class="availability-info" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rooms" class="form-label">
                                <i class="fas fa-door-open me-1"></i>
                                عدد الغرف <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="rooms" id="rooms"
                                class="form-control @error('rooms') is-invalid @enderror"
                                value="{{ old('rooms', $booking->rooms) }}" min="1" max="10" required
                                placeholder="عدد الغرف المطلوبة">
                            @error('rooms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- إضافة قسم تخصيص السعر الجديد -->
<div class="row mt-3">
    <div class="col-md-6">
        <div class="form-group">
            <label for="custom_sale_price" class="form-label">
                <i class="fas fa-tags me-1"></i>
                تخصيص سعر البيع
                <small class="text-muted">(اترك فارغاً لاستخدام السعر الافتراضي)</small>
            </label>
            <div class="input-group">
                <input type="number" step="0.01" name="custom_sale_price" id="custom_sale_price"
                    class="form-control @error('custom_sale_price') is-invalid @enderror"
                    value="{{ old('custom_sale_price', $booking->sale_price != $booking->roomPrice->sale_price ? $booking->sale_price : '') }}"
                    placeholder="أدخل سعر البيع المخصص">
                <span class="input-group-text" id="currency-addon">{{ $booking->currency }}</span>
            </div>
            <small class="form-text text-muted">السعر الافتراضي: <span id="default-sale-price">{{ $booking->roomPrice->sale_price }}</span> {{ $booking->currency }}</small>
            @error('custom_sale_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

                {{-- حساب التكاليف --}}
                <div class="cost-display" id="cost-display" style="display: none;">
                    <h6 class="mb-3"><i class="fas fa-calculator me-1"></i> تفاصيل التكلفة:</h6>
                    <div class="cost-item">
                        <span>سعر التكلفة للغرفة الواحدة:</span>
                        <span id="cost-price-display">0.00</span>
                    </div>
                    <div class="cost-item">
                        <span>سعر البيع للغرفة الواحدة:</span>
                        <span id="sale-price-display">0.00</span>
                    </div>
                    <div class="cost-item">
                        <span>عدد الغرف:</span>
                        <span id="rooms-display">0</span>
                    </div>
                    <div class="cost-item">
                        <span>إجمالي المستحق للوكيل:</span>
                        <span id="total-cost-display">0.00</span>
                    </div>
                    <div class="cost-item total-cost">
                        <span>إجمالي المستحق من الشركة:</span>
                        <span id="total-sale-display">0.00</span>
                    </div>
                </div>
            </div>

            {{-- الملاحظات --}}
            <div class="form-section">
                <h5 class="section-title">
                    <i class="fas fa-sticky-note text-warning"></i>
                    ملاحظات إضافية
                </h5>

                <div class="form-group">
                    <label for="notes" class="form-label">
                        <i class="fas fa-comment me-1"></i>
                        الملاحظات
                    </label>
                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="4"
                        placeholder="أضف أي ملاحظات خاصة بالحجز...">{{ old('notes', $booking->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- أزرار التحكم --}}
            <div class="d-flex justify-content-end gap-3">
                <a href="{{ route('admin.land-trips.bookings', $landTrip->id) }}" class="btn btn-cancel">
                    <i class="fas fa-times me-1"></i>
                    إلغاء
                </a>
                <button type="submit" class="btn btn-update">
                    <i class="fas fa-save me-1"></i>
                    تحديث الحجز
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomSelect = document.getElementById('land_trip_room_price_id');
            const roomsInput = document.getElementById('rooms');
            const costDisplay = document.getElementById('cost-display');
            const availabilityInfo = document.getElementById('availability-info');

            // تحديث المعلومات عند تغيير نوع الغرفة أو العدد
            function updateCostAndAvailability() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const rooms = parseInt(roomsInput.value) || 0;

                if (selectedOption.value && rooms > 0) {
                    const costPrice = parseFloat(selectedOption.dataset.costPrice);
                    const salePrice = parseFloat(selectedOption.dataset.salePrice);
                    const currency = selectedOption.dataset.currency;
                    const available = selectedOption.dataset.available;

                    // عرض تفاصيل التكلفة
                    document.getElementById('cost-price-display').textContent =
                        `${costPrice.toFixed(2)} ${currency}`;
                    document.getElementById('sale-price-display').textContent =
                        `${salePrice.toFixed(2)} ${currency}`;
                    document.getElementById('rooms-display').textContent = rooms;
                    document.getElementById('total-cost-display').textContent =
                        `${(costPrice * rooms).toFixed(2)} ${currency}`;
                    document.getElementById('total-sale-display').textContent =
                        `${(salePrice * rooms).toFixed(2)} ${currency}`;

                    costDisplay.style.display = 'block';

                    // عرض معلومات التوفر
                    if (available !== 'غير محدود') {
                        const availableCount = parseInt(available);
                        if (availableCount === 0) {
                            availabilityInfo.innerHTML =
                                '<i class="fas fa-exclamation-triangle text-danger"></i> لا توجد غرف متاحة من هذا النوع';
                            availabilityInfo.className = 'availability-info text-danger bg-danger bg-opacity-10';
                        } else if (rooms > availableCount) {
                            availabilityInfo.innerHTML =
                                `<i class="fas fa-exclamation-triangle text-warning"></i> العدد المطلوب (${rooms}) أكبر من المتاح (${availableCount})`;
                            availabilityInfo.className = 'availability-info text-warning bg-warning bg-opacity-10';
                        } else {
                            availabilityInfo.innerHTML =
                                `<i class="fas fa-check-circle text-success"></i> متاح: ${availableCount} غرفة`;
                            availabilityInfo.className = 'availability-info text-success bg-success bg-opacity-10';
                        }
                        availabilityInfo.style.display = 'block';
                    } else {
                        availabilityInfo.style.display = 'none';
                    }
                } else {
                    costDisplay.style.display = 'none';
                    availabilityInfo.style.display = 'none';
                }
            }

            // تحديث عند تغيير القيم
            roomSelect.addEventListener('change', updateCostAndAvailability);
            roomsInput.addEventListener('input', updateCostAndAvailability);

            // تحديث أولي عند تحميل الصفحة
            updateCostAndAvailability();

            // التحقق من النموذج قبل الإرسال
            document.getElementById('editBookingForm').addEventListener('submit', function(e) {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const rooms = parseInt(roomsInput.value) || 0;
                const available = selectedOption.dataset.available;

                if (available !== 'غير محدود') {
                    const availableCount = parseInt(available);
                    if (rooms > availableCount) {
                        e.preventDefault();
                        alert(`عذراً، العدد المطلوب (${rooms}) أكبر من المتاح (${availableCount}) غرفة.`);
                        return false;
                    }
                }

                if (rooms <= 0) {
                    e.preventDefault();
                    alert('يرجى إدخال عدد غرف صحيح (أكبر من صفر).');
                    roomsInput.focus();
                    return false;
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newTripSelect = document.getElementById('new_land_trip_id');
            const newRoomSelect = document.getElementById('new_room_type');
            const newRoomsInput = document.getElementById('new_rooms');
            const changeTripBtn = document.getElementById('changeTripBtn');
            const newTripCostDisplay = document.getElementById('newTripCostDisplay');

            // عند تغيير الرحلة الجديدة
            newTripSelect.addEventListener('change', function() {
                const tripId = this.value;

                if (tripId && tripId != '{{ $landTrip->id }}') {
                    // تصحيح المسار - استخدام route helper مع معامل landTrip
                    fetch(`{{ route('admin.land-trips.room-types', ':tripId') }}`.replace(':tripId',
                            tripId))
                        .then(response => response.json())
                        .then(data => {
                            newRoomSelect.innerHTML = '<option value="">-- اختر نوع الغرفة --</option>';
                            data.forEach(room => {
                                const option = document.createElement('option');
                                option.value = room.id;
                                option.textContent =
                                    `${room.room_type_name} - ${room.sale_price} ${room.currency}`;
                                option.dataset.costPrice = room.cost_price;
                                option.dataset.salePrice = room.sale_price;
                                option.dataset.currency = room.currency;
                                option.dataset.available = room.available || 'غير محدود';
                                newRoomSelect.appendChild(option);
                            });

                            newRoomSelect.disabled = false;
                            newRoomsInput.disabled = false;
                        })
                        .catch(error => {
                            console.error('خطأ في جلب أنواع الغرف:', error);
                            alert('حدث خطأ في جلب أنواع الغرف');
                        });
                } else {
                    newRoomSelect.innerHTML = '<option value="">-- اختر نوع الغرفة --</option>';
                    newRoomSelect.disabled = true;
                    newRoomsInput.disabled = true;
                    changeTripBtn.disabled = true;
                    newTripCostDisplay.style.display = 'none';
                }
            });

            // باقي الكود كما هو...
            function updateNewTripCost() {
                const selectedOption = newRoomSelect.options[newRoomSelect.selectedIndex];
                const rooms = parseInt(newRoomsInput.value) || 0;

                if (selectedOption.value && rooms > 0) {
                    const costPrice = parseFloat(selectedOption.dataset.costPrice);
                    const salePrice = parseFloat(selectedOption.dataset.salePrice);
                    const currency = selectedOption.dataset.currency;

                    document.getElementById('newCostPrice').textContent = `${costPrice.toFixed(2)} ${currency}`;
                    document.getElementById('newSalePrice').textContent = `${salePrice.toFixed(2)} ${currency}`;
                    document.getElementById('newTotalCost').textContent =
                        `${(costPrice * rooms).toFixed(2)} ${currency}`;
                    document.getElementById('newTotalSale').textContent =
                        `${(salePrice * rooms).toFixed(2)} ${currency}`;

                    newTripCostDisplay.style.display = 'block';
                    changeTripBtn.disabled = false;
                } else {
                    newTripCostDisplay.style.display = 'none';
                    changeTripBtn.disabled = true;
                }
            }

            newRoomSelect.addEventListener('change', updateNewTripCost);
            newRoomsInput.addEventListener('input', updateNewTripCost);
        });
    </script>
    {{-- تحديث معلومات في الحجز السعر --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const roomSelect = document.getElementById('land_trip_room_price_id');
        const roomsInput = document.getElementById('rooms');
        const customSalePriceInput = document.getElementById('custom_sale_price');
        const costDisplay = document.getElementById('cost-display');
        const availabilityInfo = document.getElementById('availability-info');
        const currencyAddon = document.getElementById('currency-addon');
        const defaultSalePriceSpan = document.getElementById('default-sale-price');

        // تحديث المعلومات عند تغيير نوع الغرفة أو العدد أو السعر المخصص
        function updateCostAndAvailability() {
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const rooms = parseInt(roomsInput.value) || 0;

            if (selectedOption.value && rooms > 0) {
                const costPrice = parseFloat(selectedOption.dataset.costPrice);
                let salePrice = parseFloat(selectedOption.dataset.salePrice);
                const currency = selectedOption.dataset.currency;
                const available = selectedOption.dataset.available;
                
                // تحديث العملة في واجهة المستخدم
                currencyAddon.textContent = currency;
                defaultSalePriceSpan.textContent = salePrice.toFixed(2);
                
                // التحقق من وجود سعر بيع مخصص
                const customPrice = customSalePriceInput.value.trim();
                if (customPrice !== '') {
                    salePrice = parseFloat(customPrice);
                }

                // عرض تفاصيل التكلفة
                document.getElementById('cost-price-display').textContent = `${costPrice.toFixed(2)} ${currency}`;
                document.getElementById('sale-price-display').textContent = `${salePrice.toFixed(2)} ${currency}`;
                document.getElementById('rooms-display').textContent = rooms;
                document.getElementById('total-cost-display').textContent = `${(costPrice * rooms).toFixed(2)} ${currency}`;
                document.getElementById('total-sale-display').textContent = `${(salePrice * rooms).toFixed(2)} ${currency}`;

                // تحديد ما إذا كان هناك سعر مخصص
                const usingCustomPrice = customPrice !== '' && parseFloat(customPrice) !== parseFloat(selectedOption.dataset.salePrice);
                if (usingCustomPrice) {
                    document.getElementById('sale-price-display').classList.add('text-warning');
                    document.getElementById('total-sale-display').classList.add('text-warning');
                } else {
                    document.getElementById('sale-price-display').classList.remove('text-warning');
                    document.getElementById('total-sale-display').classList.remove('text-warning');
                }

                costDisplay.style.display = 'block';
                
                // بقية الكود للتحقق من التوفر...
            } else {
                costDisplay.style.display = 'none';
                availabilityInfo.style.display = 'none';
            }
        }

        // تحديث عند تغيير القيم
        roomSelect.addEventListener('change', function() {
            // عند تغيير الغرفة، نحدث السعر الافتراضي
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                defaultSalePriceSpan.textContent = parseFloat(selectedOption.dataset.salePrice).toFixed(2);
                currencyAddon.textContent = selectedOption.dataset.currency;
                
                // إفراغ حقل السعر المخصص عند تغيير الغرفة (اختياري)
                // customSalePriceInput.value = '';
            }
            updateCostAndAvailability();
        });
        
        roomsInput.addEventListener('input', updateCostAndAvailability);
        customSalePriceInput.addEventListener('input', updateCostAndAvailability);

        // تحديث أولي عند تحميل الصفحة
        updateCostAndAvailability();

        // التحقق من النموذج قبل الإرسال...
    });
</script>
@endpush
