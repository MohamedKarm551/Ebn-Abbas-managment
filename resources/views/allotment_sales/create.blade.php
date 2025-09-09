@extends('layouts.app')

@section('title', 'إضافة بيع ألوتمنت جديد')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">إضافة بيع ألوتمنت جديد</h6>
                        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-right ml-1"></i> العودة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('allotment-sales.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                {{-- الفندق --}}
                                <div class="col-md-6 mb-3">
                                    <label for="hotel_id">الفندق <span class="text-danger">*</span></label>
                                    <select name="hotel_id" id="hotel_id"
                                            class="form-control @error('hotel_id') is-invalid @enderror" required>
                                        <option value="">اختر الفندق</option>
                                        @foreach ($hotels as $hotel)
                                            <option value="{{ $hotel->id }}"
                                                {{ old('hotel_id', request('hotel_id')) == $hotel->id ? 'selected' : '' }}>
                                                {{ $hotel->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('hotel_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- الألوتمنت --}}
                                <div class="col-md-6 mb-3">
                                    <label for="allotment_id">الألوتمنت <span class="text-danger">*</span></label>
                                    <select name="allotment_id" id="allotment_id"
                                            class="form-control @error('allotment_id') is-invalid @enderror" required>
                                        <option value="">اختر الألوتمنت</option>
                                        @foreach ($allotments as $allotment)
                                            @php
                                                $remaining = $allotment->remaining_rooms
                                                    ?? ($allotment->rooms_count - ($allotment->sales->sum('rooms_sold') ?? 0));
                                            @endphp
                                            <option
                                                value="{{ $allotment->id }}"
                                                data-hotel="{{ $allotment->hotel_id }}"
                                                data-start="{{ $allotment->start_date->toDateString() }}"
                                                data-end="{{ $allotment->end_date->toDateString() }}"
                                                data-remaining="{{ $remaining }}"
                                                {{ old('allotment_id') == $allotment->id ? 'selected' : '' }}
                                            >
                                                {{ $allotment->hotel?->name ?? '—' }}
                                                — {{ $allotment->start_date->format('Y-m-d') }} →
                                                {{ $allotment->end_date->format('Y-m-d') }}
                                                — {{ $allotment->rooms_count }} غرفة
                                                (متبقي: {{ $remaining }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('allotment_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- الشركة --}}
                                <div class="col-md-6 mb-3">
                                    <label for="company_name">اسم الشركة <span class="text-danger">*</span></label>
                                    <input type="text" name="company_name" id="company_name"
                                           class="form-control @error('company_name') is-invalid @enderror"
                                           value="{{ old('company_name') }}" required>
                                    @error('company_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- التواريخ --}}
                                <div class="col-md-3 mb-3">
                                    <label for="check_in">تاريخ الدخول <span class="text-danger">*</span></label>
                                    <input type="date" name="check_in" id="check_in"
                                           class="form-control @error('check_in') is-invalid @enderror"
                                           value="{{ old('check_in') }}" required>
                                    @error('check_in')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="check_out">تاريخ الخروج <span class="text-danger">*</span></label>
                                    <input type="date" name="check_out" id="check_out"
                                           class="form-control @error('check_out') is-invalid @enderror"
                                           value="{{ old('check_out') }}" required>
                                    @error('check_out')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- عدد الغرف / السعر --}}
                                <div class="col-md-3 mb-3">
                                    <label for="rooms_sold">عدد الغرف <span class="text-danger">*</span></label>
                                    <input type="number" name="rooms_sold" id="rooms_sold"
                                           class="form-control @error('rooms_sold') is-invalid @enderror"
                                           value="{{ old('rooms_sold', 1) }}" min="1" required>
                                    <div id="rooms_warning" class="text-danger small"></div>
                                    @error('rooms_sold')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="sale_price">سعر الغرفة <span class="text-danger">*</span></label>
                                    <input type="number" name="sale_price" id="sale_price"
                                           class="form-control @error('sale_price') is-invalid @enderror"
                                           value="{{ old('sale_price') }}" min="0" step="0.01" required>
                                    @error('sale_price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- العملة --}}
                                <div class="col-md-3 mb-3">
                                    <label for="currency">العملة <span class="text-danger">*</span></label>
                                    <select name="currency" id="currency"
                                            class="form-control @error('currency') is-invalid @enderror" required>
                                        <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>ريال سعودي (SAR)</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>يورو (EUR)</option>
                                        <option value="EGP" {{ old('currency') == 'EGP' ? 'selected' : '' }}>جنيه مصري (EGP)</option>
                                    </select>
                                    @error('currency')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- ملاحظات --}}
                                <div class="col-md-12 mb-3">
                                    <label for="notes">ملاحظات</label>
                                    <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- ملخص الحساب --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div id="calculation" class="alert alert-info d-none">
                                        <h6 class="fw-bold">ملخص البيع:</h6>
                                        <div id="summary"></div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save ml-1"></i> حفظ عملية البيع
                                    </button>
                                    <a href="{{ url()->previous() }}" class="btn btn-light">إلغاء</a>
                                </div>
                            </div>
                        </form>
                    </div> {{-- card-body --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hotelSelect      = document.getElementById('hotel_id');
    const allotmentSelect  = document.getElementById('allotment_id');
    const checkInInput     = document.getElementById('check_in');
    const checkOutInput    = document.getElementById('check_out');
    const roomsSoldInput   = document.getElementById('rooms_sold');
    const salePriceInput   = document.getElementById('sale_price');
    const currencySelect   = document.getElementById('currency');
    const roomsWarningEl   = document.getElementById('rooms_warning');

    // خزّن كل خيارات الألوتمنت (عدا الـ placeholder)
    const ALL_OPTIONS = Array.from(allotmentSelect.querySelectorAll('option[data-hotel]'));

    function rebuildAllotmentOptions(hotelId) {
        // ارجع للـ placeholder
        allotmentSelect.innerHTML = '<option value="">اختر الألوتمنت</option>';

        // فلترة حسب الفندق لو محدد
        const filtered = hotelId
            ? ALL_OPTIONS.filter(opt => opt.dataset.hotel === hotelId)
            : ALL_OPTIONS;

        // ضيف النسخ (cloneNode) علشان ما نفقد النسخة الأصلية
        filtered.forEach(opt => allotmentSelect.appendChild(opt.cloneNode(true)));

        // تصفير الحقول المساعدة
        checkInInput.value  = '';
        checkOutInput.value = '';
        checkInInput.min = checkInInput.max = '';
        checkOutInput.min = checkOutInput.max = '';
        roomsWarningEl.textContent = '';
    }

    function updateDateRangeFromSelectedAllotment() {
        const opt = allotmentSelect.options[allotmentSelect.selectedIndex];
        if (!opt || !opt.value) return;

        const start = opt.dataset.start;
        const end   = opt.dataset.end;

        checkInInput.min  = start;
        checkInInput.max  = end;
        checkOutInput.min = start;
        checkOutInput.max = end;

        if (!checkInInput.value)  checkInInput.value  = start;
        if (!checkOutInput.value) checkOutInput.value = end;

        checkRoomsAvailability();
    }

    function checkRoomsAvailability() {
        const opt = allotmentSelect.options[allotmentSelect.selectedIndex];
        if (!opt || !opt.value) { roomsWarningEl.textContent=''; return; }

        const remaining = parseInt(opt.dataset.remaining || '0', 10);
        const requested = parseInt(roomsSoldInput.value || '0', 10);

        roomsWarningEl.textContent =
            requested > remaining
                ? `تنبيه: عدد الغرف المطلوب (${requested}) أكبر من المتاح (${remaining}).`
                : '';
    }

    function calculateSummary() {
        const checkIn   = checkInInput.value;
        const checkOut  = checkOutInput.value;
        const rooms     = parseInt(roomsSoldInput.value || '0', 10);
        const price     = parseFloat(salePriceInput.value || '0');
        const currency  = currencySelect.value;

        if (!checkIn || !checkOut || !rooms || !price) {
            document.getElementById('calculation').classList.add('d-none');
            return;
        }

        const start = new Date(checkIn);
        const end   = new Date(checkOut);
        if (end <= start) {
            document.getElementById('calculation').classList.add('d-none');
            return;
        }

        const days = Math.round((end - start) / (1000 * 60 * 60 * 24));
        const roomNights = days * rooms;
        const total = roomNights * price;

        document.getElementById('summary').innerHTML = `
          <div>عدد الأيام: <strong>${days} يوم</strong></div>
          <div>عدد الغرف: <strong>${rooms} غرفة</strong></div>
          <div>سعر الغرفة: <strong>${price} ${currency}</strong></div>
          <div>إجمالي الغرف/الليالي: <strong>${roomNights} غرفة/ليلة</strong></div>
          <div>إجمالي قيمة البيع: <strong>${total.toLocaleString()} ${currency}</strong></div>
        `;
        document.getElementById('calculation').classList.remove('d-none');
    }

    // Events
    hotelSelect.addEventListener('change', () => {
        rebuildAllotmentOptions(hotelSelect.value);
    });

    allotmentSelect.addEventListener('change', () => {
        updateDateRangeFromSelectedAllotment();
        calculateSummary();
    });

    [checkInInput, checkOutInput].forEach(el => el.addEventListener('change', calculateSummary));
    roomsSoldInput.addEventListener('input', () => { checkRoomsAvailability(); calculateSummary(); });
    salePriceInput.addEventListener('input', calculateSummary);
    currencySelect.addEventListener('change', calculateSummary);

    // Init on load
    rebuildAllotmentOptions(hotelSelect.value); // يبني حسب الفندق المختار إن وجد
    if (allotmentSelect.value) {
        updateDateRangeFromSelectedAllotment();
    }
});
</script>
@endpush
