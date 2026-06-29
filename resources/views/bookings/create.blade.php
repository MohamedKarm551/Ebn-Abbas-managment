
@extends('layouts.app')
@section('title', 'حجز جديد - حجز إتاحة  ')
<style>
.ui-datepicker .checkout-only a {
    color: #aaa !important;
    font-style: italic;
}

</style>
@section('content')
    <div class="container">
        <h1>{{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'إتمام الحجز من الإتاحة' : 'إضافة حجز جديد' }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
            @csrf

            <input type="hidden" name="availability_room_type_id" id="availability_room_type_id"
                value="{{ $bookingData['availability_room_type_id'] ?? '' }}">

            <div class="row g-3">
                 {{-- رقم الفاوتشر --}}
                <div class="col-md-6">
                    <label for="invoice_number" class="form-label">رقم الفاوتشر <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number"
                        name="invoice_number" value="{{ old('invoice_number', $bookingData['invoice_number'] ?? '') }}" required>
                    @error('invoice_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                 {{-- اسم العميل --}}
                <div class="col-md-6">
                    <label for="client_name" class="form-label">اسم العميل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" title="سيتم دمج اسم العميل مع رقم الفاوتشر عند الحفظ  لابد من كتابة رقم الفاوتشر أولا"
                        name="client_name" value="{{ old('client_name', $bookingData['client_name'] ?? '') }}" required readonly>
                    @error('client_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- الشركة --}}
                <div class="col-md-6">
                    <label for="company_id" class="form-label">اسم الشركة <span class="text-danger">*</span></label>
                     <a href="{{ route('accounts.create', ['preset' => 'company']) }}" 
                        class="btn btn-success btn-sm" title="إضافة شركة جديدة">+</a>
                    <select class="form-select select2 @error('company_id') is-invalid @enderror" id="company_id"
                        name="company_id" required {{ auth()->user()->role == 'Company' ? 'disabled' : '' }}>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ (auth()->user()->role == 'Company' && auth()->user()->company_id == $company->id) || old('company_id', $bookingData['company_id'] ?? '') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if (auth()->user()->role == 'Company')
                        <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                    @endif
                </div>

                {{-- جهة الحجز --}}
                @if (auth()->user()->role != 'Company')
                    <div class="col-md-6">
                        <label for="agent_id" class="form-label">جهة الحجز <span class="text-danger">*</span></label>
                        <a href="{{ route('accounts.create', ['preset' => 'agent']) }}" 
                            class="btn btn-success btn-sm" title="إضافة جهة حجز جديدة">+</a>
                        <select class="form-select select2 @error('agent_id') is-invalid @enderror" id="agent_id"
                            name="agent_id" required
                            {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'disabled' : '' }}>
                            <option value="" disabled selected>اختر جهة الحجز</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}"
                                    {{ old('agent_id', $bookingData['agent_id'] ?? '') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('agent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['agent_id']))
                            <input type="hidden" name="agent_id" value="{{ $bookingData['agent_id'] }}">
                        @endif
                    </div>
                @else
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['agent_id']))
                        <input type="hidden" name="agent_id" value="{{ $bookingData['agent_id'] }}">
                    @endif
                @endif

                {{-- الفندق --}}
                <div class="col-md-6">
                    <label for="hotel_id" class="form-label">اسم الفندق <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('hotel_id') is-invalid @enderror" id="hotel_id"
                        name="hotel_id" required
                        {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'disabled' : '' }}>
                        <option value="" disabled selected>اختر الفندق</option>
                        @foreach ($hotels as $hotel)
                            <option value="{{ $hotel->id }}"
                                {{ old('hotel_id', $bookingData['hotel_id'] ?? '') == $hotel->id ? 'selected' : '' }}>
                                {{ $hotel->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('hotel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['hotel_id']))
                        <input type="hidden" name="hotel_id" value="{{ $bookingData['hotel_id'] }}">
                    @endif
                </div>

                {{-- نوع الغرفة --}}
                <div class="col-md-4">
                    <label for="room_type" class="form-label">نوع الغرفة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('room_type') is-invalid @enderror" id="room_type"
                        name="room_type" value="{{ old('room_type', $bookingData['room_type'] ?? '') }}" required
                        {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'readonly' : '' }}>
                    @error('room_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- عدد الغرف --}}
                <div class="col-md-2">
                    <label for="rooms" class="form-label">عدد الغرف <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('rooms') is-invalid @enderror" id="rooms"
                        name="rooms" value="{{ old('rooms', $bookingData['rooms'] ?? 1) }}" min="1"
                        @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['max_rooms'])) max="{{ $bookingData['max_rooms'] }}" @endif required>
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['max_rooms']))
                        <small class="form-text text-muted">الحد الأقصى: {{ $bookingData['max_rooms'] }}</small>
                    @endif
                    @error('rooms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

               {{-- سعر البيع --}}
                <div class="col-md-3">
                    <label for="sale_price" class="form-label">سعر الليلة(للشركة) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror"
                        id="sale_price" name="sale_price" value="{{ old('sale_price', $bookingData['sale_price'] ?? '') }}"
                        required 
                        {{ (isset($isBookingFromAvailability) && $isBookingFromAvailability && !(Auth::user()->role == 'admin')) ? 'readonly' : '' }}
                        >
                    @error('sale_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- سعر التكلفة --}}
                @if (auth()->user()->role != 'Company')
                    <div class="col-md-3">
                        <label for="cost_price" class="form-label">السعر من الفندق <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror"
                            id="cost_price" name="cost_price"
                            value="{{ old('cost_price', $bookingData['cost_price'] ?? '') }}" required
                            {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'readonly' : '' }}>
                        @error('cost_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <input type="hidden" id="cost_price" name="cost_price"
                        value="{{ old('cost_price', $bookingData['cost_price'] ?? '') }}">
                @endif

                {{-- العملة --}}
                <div class="col-md-3">
                    <label for="currency" class="form-label">العملة <span class="text-danger">*</span></label>
                    <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency"
                        required
                        {{ auth()->user()->role == 'Company' || (isset($isBookingFromAvailability) && $isBookingFromAvailability) ? 'disabled' : '' }}>
                        <option value="SAR" {{ old('currency', $bookingData['currency'] ?? 'SAR') == 'SAR' ? 'selected' : '' }}>ريال سعودي</option>
                        <option value="KWD" {{ old('currency', $bookingData['currency'] ?? '') == 'KWD' ? 'selected' : '' }}>دينار كويتي</option>
                    </select>
                    @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if (auth()->user()->role == 'Company' || (isset($isBookingFromAvailability) && $isBookingFromAvailability))
                        <input type="hidden" name="currency" value="{{ old('currency', $bookingData['currency'] ?? 'SAR') }}">
                    @endif
                </div>

                {{-- تاريخ الدخول --}}
                <div class="col-md-4">
                    <label for="check_in" class="form-label">تاريخ الدخول <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker @error('check_in') is-invalid @enderror"
                        id="check_in" name="check_in" 
                        value="{{ old('check_in', $bookingData['check_in'] ?? '') }}"
                        min="{{ $bookingData['first_available_date'] ?? $bookingData['availability_start_date'] ?? '' }}"
                        max="{{ $bookingData['last_available_date'] ?? $bookingData['availability_end_date'] ?? '' }}"
                        @if (!auth()->user() || strtolower(auth()->user()->role) !== 'admin') 
                            onkeydown="return false;" 
                            style="background-color: #e9ecef; cursor: pointer;" 
                        @endif
                        required placeholder="YYYY-MM-DD">
                    
                    @if(isset($bookingData['first_available_date']) && isset($bookingData['availability_start_date']) && $bookingData['first_available_date'] != $bookingData['availability_start_date'])
                        <small class="form-text text-info">
                            <i class="fas fa-info-circle"></i>
                            أول تاريخ متاح: {{ \Carbon\Carbon::parse($bookingData['first_available_date'])->format('d/m/Y') }}
                            (تم حجز الفترة السابقة)
                        </small>
                    @endif
                    
                    @error('check_in')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- تاريخ الخروج --}}
                <div class="col-md-4">
                    <label for="check_out" class="form-label">تاريخ الخروج <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker @error('check_out') is-invalid @enderror"
                        id="check_out" name="check_out" 
                        value="{{ old('check_out', $bookingData['check_out'] ?? '') }}"
                        min="{{ $bookingData['first_available_date'] ?? $bookingData['availability_start_date'] ?? '' }}"
                        max="{{ $bookingData['last_available_date'] ?? $bookingData['availability_end_date'] ?? '' }}"
                        @if (!auth()->user() || strtolower(auth()->user()->role) !== 'admin') 
                            onkeydown="return false;" 
                            style="background-color: #e9ecef; cursor: pointer;" 
                        @endif
                        required placeholder="YYYY-MM-DD">
                    
                    @if(isset($bookingData['last_available_date']) && isset($bookingData['availability_end_date']) && $bookingData['last_available_date'] != $bookingData['availability_end_date'])
                        <small class="form-text text-info">
                            <i class="fas fa-info-circle"></i>
                            آخر تاريخ متاح: {{ \Carbon\Carbon::parse($bookingData['last_available_date'])->format('d/m/Y') }}
                        </small>
                    @endif
                    
                    @error('check_out')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- الموظف المسؤول --}}
                <div class="col-md-4">
                    <label for="employee_id" class="form-label">الموظف المسؤول <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('employee_id') is-invalid @enderror" id="employee_id"
                        name="employee_id" required
                        {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'disabled' : '' }}>
                        <option value="" disabled {{ !isset($bookingData['employee_id']) && !old('employee_id') ? 'selected' : '' }}>اختر الموظف</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ old('employee_id', $bookingData['employee_id'] ?? '') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['employee_id']))
                        <input type="hidden" name="employee_id" value="{{ $bookingData['employee_id'] }}">
                    @endif
                </div>

                {{-- ملاحظات --}}
                <div class="col-12">
                    <label for="notes" class="form-label">الملاحظات (اختياري)</label>
                    <textarea class="form-control" id="notes" name="notes">{{ old('notes', $bookingData['notes'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">حفظ الحجز</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/preventClick.js') }}"></script>
<script>   
const authUserRole = @json(auth()->user()->role ?? 'guest');
    // إدارة حقل رقم الفاوتشر واسم العميل
    const invoiceNumberInput = document.getElementById('invoice_number');
    const clientNameInput = document.getElementById('client_name');
    let originalClientName = '';

    // تفعيل حقل اسم العميل عند الكتابة في رقم الفاوتشر
    invoiceNumberInput.addEventListener('input', function() {
        if (this.value.trim() !== '') {
            clientNameInput.removeAttribute('readonly');
            clientNameInput.style.backgroundColor = '#fff';
            clientNameInput.style.cursor = 'auto';
        } else {
            clientNameInput.setAttribute('readonly', 'readonly');
            clientNameInput.style.backgroundColor = '#e9ecef';
            clientNameInput.style.cursor = 'not-allowed';
            clientNameInput.value = '';
        }
    });

    // دمج اسم العميل ورقم الفاوتشر عند الإرسال
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const invoiceNumber = invoiceNumberInput.value.trim();
        const clientName = clientNameInput.value.trim();
        
        if (invoiceNumber && clientName) {
            // دمج القيمتين: اسم العميل + رقم الفاوتشر
            clientNameInput.value = clientName + ' ' + invoiceNumber;
        }
    });
document.addEventListener('DOMContentLoaded', function() {
    $('.select2').select2({ theme: 'bootstrap-5' });

    const bookingData = @json($bookingData ?? null);
    const isBookingFromAvailability = @json($isBookingFromAvailability ?? false);
    let dailyAvailability = null;

    function updateAvailabilityInfo() {
        const checkIn = $('#check_in').val();
        const checkOut = $('#check_out').val();
        const rooms = parseInt($('#rooms').val()) || 1;
        if (checkIn && checkOut && dailyAvailability && dailyAvailability.daily_status) {
            const start = new Date(checkIn);
            const end = new Date(checkOut);
            let minAvailable = Infinity;
            let hasUnavailable = false;
            for (let d = new Date(start); d < end; d.setDate(d.getDate() + 1)) {
                const dateStr = d.toISOString().split('T')[0];
                const dayStatus = dailyAvailability.daily_status.find(s => s.date === dateStr);
                if (dayStatus) {
                    const remaining = dayStatus.available_rooms - dayStatus.booked_rooms;
                    if (remaining < rooms) hasUnavailable = true;
                    minAvailable = Math.min(minAvailable, remaining);
                }
            }
            let helpElement = $('#check_in_help');
            if (helpElement.length === 0) {
                helpElement = $('<small id="check_in_help" class="form-text"></small>');
                $('#check_in').after(helpElement);
            }
            if (hasUnavailable) {
                helpElement.text('⚠️ تحذير: بعض الأيام المختارة لا تحتوي على غرف كافية').removeClass('text-success').addClass('text-danger');
            } else if (minAvailable !== Infinity) {
                helpElement.text(`✅ الغرف المتاحة في الفترة: ${minAvailable} غرفة`).removeClass('text-danger').addClass('text-success');
            } else {
                helpElement.text('');
            }
        }
    }

    function getMaxDate() {
        const lastDate = new Date(bookingData.last_available_date || bookingData.availability_end_date);
        lastDate.setDate(lastDate.getDate() + 1);
        return lastDate;
    }

    // beforeShowDay للـ check_in فقط
    function beforeShowDayCheckIn(date) {
        const dateStr = $.datepicker.formatDate('yy-mm-dd', date);
        if (dailyAvailability && dailyAvailability.daily_status) {
            const dayStatus = dailyAvailability.daily_status.find(s => s.date === dateStr);
            if (dayStatus) {
                const remaining = dayStatus.available_rooms - dayStatus.booked_rooms;
                if (remaining <= 0) return [false, 'booked', 'محجوز بالكامل'];
                if (remaining < (bookingData.max_rooms || 1)) return [true, 'partial', 'متاح جزئياً (' + remaining + ' غرفة)'];
            } else {
                return [false, 'disabled', 'غير متاح'];
            }
        }
        return [true, 'available', 'متاح'];
    }

    // beforeShowDay للـ check_out - يسمح بيوم زيادة
    function beforeShowDayCheckOut(date) {
        const dateStr = $.datepicker.formatDate('yy-mm-dd', date);
        if (dailyAvailability && dailyAvailability.daily_status) {
            const dayStatus = dailyAvailability.daily_status.find(s => s.date === dateStr);
            if (!dayStatus) {
                // يوم بعد نهاية الإتاحة - اسمح بيه كيوم خروج فقط ✅
                return [true, 'checkout-only', 'يوم خروج فقط'];
            }
            const remaining = dayStatus.available_rooms - dayStatus.booked_rooms;
            if (remaining <= 0) {
                // يوم محجوز - اسمح بيه كيوم خروج فقط ✅
                return [true, 'checkout-only', 'يوم خروج فقط'];
            }
            if (remaining < (bookingData.max_rooms || 1)) {
                return [true, 'partial', 'متاح جزئياً (' + remaining + ' غرفة)'];
            }
        }
        return [true, 'available', 'متاح'];
    }

    function initAvailabilityDatePicker() {
        const commonOptions = {
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: bookingData.first_available_date || bookingData.availability_start_date,
            maxDate: getMaxDate(),
        };

        // تهيئة check_in منفصلة
        $('#check_in').datepicker({
            ...commonOptions,
            beforeShowDay: beforeShowDayCheckIn,
            onSelect: function(selectedDate) {
                const minCheckout = new Date(selectedDate);
                $('#check_out').datepicker('option', 'minDate', minCheckout);

                if (dailyAvailability && dailyAvailability.daily_status) {
                    const selected = new Date(selectedDate);
                    const maxEnd = new Date(bookingData.last_available_date || bookingData.availability_end_date);
                    let firstUnavailable = null;

                    for (let d = new Date(selected); d <= maxEnd; d.setDate(d.getDate() + 1)) {
                        const dateStr = d.toISOString().split('T')[0];
                        const dayStatus = dailyAvailability.daily_status.find(s => s.date === dateStr);
                        if (!dayStatus || (dayStatus.available_rooms - dayStatus.booked_rooms) <= 0) {
                            firstUnavailable = new Date(d);
                            break;
                        }
                    }

                    let maxAllowedDate;
                    if (firstUnavailable) {
                        // أول يوم غير متاح نفسه = maxDate للخروج (لأن الخروج مش بيتحجز)
                        maxAllowedDate = new Date(firstUnavailable);
                    } else {
                        // مفيش يوم غير متاح = نضيف يوم زيادة على آخر يوم متاح
                        maxAllowedDate = new Date(maxEnd);
                        maxAllowedDate.setDate(maxAllowedDate.getDate() + 1);
                    }

                    $('#check_out').datepicker('option', 'maxDate', maxAllowedDate);

                    const currentCheckOut = $('#check_out').val();
                    if (currentCheckOut && new Date(currentCheckOut) > maxAllowedDate) {
                        const y = maxAllowedDate.getFullYear();
                        const m = String(maxAllowedDate.getMonth() + 1).padStart(2, '0');
                        const d = String(maxAllowedDate.getDate()).padStart(2, '0');
                        $('#check_out').val(`${y}-${m}-${d}`);
                    }
                }
                updateAvailabilityInfo();
            }
        });

        // تهيئة check_out منفصلة مع beforeShowDay مختلف
        $('#check_out').datepicker({
            ...commonOptions,
            beforeShowDay: beforeShowDayCheckOut,
            onSelect: function() {
                updateAvailabilityInfo();
            }
        });
    }

    function fillFormData() {
        if (bookingData.company_id) $('#company_id').val(bookingData.company_id).trigger('change');
        if (bookingData.agent_id) $('#agent_id').val(bookingData.agent_id).trigger('change');
        if (bookingData.hotel_id) $('#hotel_id').val(bookingData.hotel_id).trigger('change');
        if (bookingData.employee_id) $('#employee_id').val(bookingData.employee_id).trigger('change');
        if (bookingData.room_type) document.getElementById('room_type').value = bookingData.room_type;
        if (bookingData.sale_price) document.getElementById('sale_price').value = bookingData.sale_price;
        if (bookingData.cost_price) document.getElementById('cost_price').value = bookingData.cost_price;
        if (bookingData.currency) {
            document.getElementById('currency').value = bookingData.currency;
            const hiddenCurrencyInput = document.querySelector('input[type="hidden"][name="currency"]');
            if (hiddenCurrencyInput) hiddenCurrencyInput.value = bookingData.currency;
        }
        if (bookingData.check_in) document.getElementById('check_in').value = bookingData.check_in;
        if (bookingData.check_out) document.getElementById('check_out').value = bookingData.check_out;

        const isAdmin = (typeof authUserRole !== 'undefined' && authUserRole.toLowerCase() === 'admin');
        document.getElementById('agent_id').disabled = true;
        document.getElementById('hotel_id').disabled = true;
        document.getElementById('room_type').readOnly = true;
        document.getElementById('cost_price').readOnly = true;
        document.getElementById('sale_price').readOnly = !isAdmin;
    }

    if (isBookingFromAvailability && bookingData && bookingData.availability_room_type_id) {
        fetch(`/api/availability-daily-status/${bookingData.availability_room_type_id}`)
            .then(response => response.json())
            .then(data => {
                dailyAvailability = data;
                $('#check_in').datepicker('destroy');
                $('#check_out').datepicker('destroy');
                initAvailabilityDatePicker();
                fillFormData();
                setTimeout(updateAvailabilityInfo, 100);
            })
            .catch(error => {
                console.error('Error:', error);
                $('#check_in').datepicker('destroy');
                $('#check_out').datepicker('destroy');
                initAvailabilityDatePicker();
                fillFormData();
            });
    }

    $('#rooms').on('change', updateAvailabilityInfo);

    $('#bookingForm').on('submit', function(e) {
        const checkIn = $('#check_in').val();
        const checkOut = $('#check_out').val();
        const rooms = parseInt($('#rooms').val()) || 1;
        if (isBookingFromAvailability && checkIn && checkOut && dailyAvailability && dailyAvailability.daily_status) {
            const start = new Date(checkIn);
            const end = new Date(checkOut);
            // التحقق من d < end (مش <=) عشان يوم الخروج مش بيتحجز
            for (let d = new Date(start); d < end; d.setDate(d.getDate() + 1)) {
                const dateStr = d.toISOString().split('T')[0];
                const dayStatus = dailyAvailability.daily_status.find(s => s.date === dateStr);
                if (!dayStatus || (dayStatus.available_rooms - dayStatus.booked_rooms) < rooms) {
                    e.preventDefault();
                    alert(`❌ لا يوجد غرف كافية متاحة في تاريخ ${dateStr}\nالرجاء اختيار تواريخ أخرى.`);
                    return false;
                }
            }
        }
        return true;
    });
});
</script>
@endpush