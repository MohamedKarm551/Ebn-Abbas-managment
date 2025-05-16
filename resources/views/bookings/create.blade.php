@extends('layouts.app')
{{-- عاوز تايتل الصفحة يبقا اسمه إتمام حجز الإتاحة --}}
@section('title', 'إتمام حجز الإتاحة')

{{-- إضافة كلاس خاص بالصفحة --}}
@section('content')
    <div class="container">
        {{-- *** تغيير العنوان بناءً على السياق *** --}}
        <h1>{{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'إتمام الحجز من الإتاحة' : 'إضافة حجز جديد' }}
        </h1>

        {{-- عرض رسائل الخطأ --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bookings.store') }}" method="POST">
            @csrf

            {{-- *** حقل مخفي لتمرير ID الإتاحة/الغرفة لو موجود *** --}}
            <input type="hidden" name="availability_room_type_id" id="availability_room_type_id"
                value="{{ $bookingData['availability_room_type_id'] ?? '' }}">

            {{-- *** استخدام نظام Grid لتنسيق أفضل *** --}}
            <div class="row g-3">
                {{-- اسم العميل --}}
                <div class="col-md-6">
                    <label for="client_name" class="form-label">اسم العميل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name"
                        name="client_name" value="{{ old('client_name', $bookingData['client_name'] ?? '') }}" required>
                    @error('client_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- الشركة --}}

                <div class="col-md-6">
                    <label for="company_id" class="form-label">اسم الشركة <span class="text-danger">*</span></label>
                    {{-- *** تعديل: تصحيح اسم الـ Role *** --}}
                    <select class="form-select select2 @error('company_id') is-invalid @enderror" id="company_id"
                        name="company_id" required {{ auth()->user()->role == 'Company' ? 'disabled' : '' }}>
                        {{-- Options --}}
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" {{-- تحديد الشركة الحالية لو اليوزر شركة أو لو فيه قيمة قديمة/من الإتاحة --}}
                                {{ (auth()->user()->role == 'Company' && auth()->user()->company_id == $company->id) || old('company_id', $bookingData['company_id'] ?? '') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    {{-- حقل مخفي لإرسال القيمة لو الحقل disabled للشركة --}}
                    {{-- *** تعديل: تصحيح اسم الـ Role *** --}}
                    @if (auth()->user()->role == 'Company')
                        <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                    @endif
                </div>

                {{-- جهة الحجز --}}
                @if (auth()->user()->role != 'Company')
                    <div class="col-md-6">
                        <label for="agent_id" class="form-label">جهة الحجز <span class="text-danger">*</span></label>
                        {{-- *** إضافة كلاس select2 و خاصية disabled *** --}}
                        <select class="form-select select2 @error('agent_id') is-invalid @enderror" id="agent_id"
                            name="agent_id" required
                            {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'disabled' : '' }}>
                            <option value="" disabled selected>اختر جهة الحجز</option>
                            @foreach ($agents as $agent)
                                {{-- *** تعديل بسيط لـ old() و إزالة الشرط الإضافي لـ selected *** --}}
                                <option value="{{ $agent->id }}"
                                    {{ old('agent_id', $bookingData['agent_id'] ?? '') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('agent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        {{-- *** حقل مخفي لإرسال القيمة لو الحقل select معطل (لغير الشركة، عند الحجز من إتاحة) *** --}}
                        @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['agent_id']))
                            <input type="hidden" name="agent_id" value="{{ $bookingData['agent_id'] }}">
                        @endif
                    </div>
                @else
                    {{-- إذا كان المستخدم شركة والحجز من إتاحة وبها جهة حجز، أرسل القيمة بشكل مخفي --}}
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['agent_id']))
                        <input type="hidden" name="agent_id" value="{{ $bookingData['agent_id'] }}">
                    @endif
                @endif
                {{-- الفندق --}}
                <div class="col-md-6">
                    <label for="hotel_id" class="form-label">اسم الفندق <span class="text-danger">*</span></label>
                    {{-- *** إضافة كلاس select2 و خاصية disabled *** --}}
                    <select class="form-select select2 @error('hotel_id') is-invalid @enderror" id="hotel_id"
                        name="hotel_id" required
                        {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'disabled' : '' }}>
                        <option value="" disabled selected>اختر الفندق</option>
                        @foreach ($hotels as $hotel)
                            {{-- *** تعديل بسيط لـ old() و إزالة الشرط الإضافي لـ selected *** --}}
                            <option value="{{ $hotel->id }}"
                                {{ old('hotel_id', $bookingData['hotel_id'] ?? '') == $hotel->id ? 'selected' : '' }}>
                                {{ $hotel->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('hotel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    {{-- *** حقل مخفي لإرسال القيمة لو الحقل disabled *** --}}
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['hotel_id']))
                        <input type="hidden" name="hotel_id" value="{{ $bookingData['hotel_id'] }}">
                    @endif
                </div>

                {{-- نوع الغرفة --}}
                <div class="col-md-4">
                    <label for="room_type" class="form-label">نوع الغرفة <span class="text-danger">*</span></label>
                    {{-- *** تغيير لـ input و إضافة readonly *** --}}
                    <input type="text" class="form-control @error('room_type') is-invalid @enderror" id="room_type"
                        name="room_type" value="{{ old('room_type', $bookingData['room_type'] ?? '') }}" required
                        {{-- السطر ده بيضيف readonly لو الحجز من إتاحة --}}
                        {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'readonly' : '' }}>
                    @error('room_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                {{-- عدد الغرف --}}
                <div class="col-md-2">
                    <label for="rooms" class="form-label">عدد الغرف <span class="text-danger">*</span></label>
                    {{-- *** إضافة قيمة افتراضية 1 لـ old() *** --}}
                    <input type="number" class="form-control @error('rooms') is-invalid @enderror" id="rooms"
                        name="rooms" value="{{ old('rooms', $bookingData['rooms'] ?? 1) }}" min="1"
                        {{-- إضافة max لو الحجز من إتاحة وفيه قيمة لـ max_rooms --}}
                        @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['max_rooms'])) max="{{ $bookingData['max_rooms'] }}" @endif required>
                    {{-- *** إضافة ملاحظة للمستخدم لو فيه حد أقصى *** --}}
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['max_rooms']))
                        <small class="form-text text-muted">الحد الأقصى: {{ $bookingData['max_rooms'] }}</small>
                    @endif
                    @error('rooms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                {{-- سعر البيع (للشركة) --}}
                <div class="col-md-3">
                    <label for="sale_price" class="form-label">سعر الليلة(للشركة) <span class="text-danger">*</span></label>
                    {{-- *** إضافة readonly *** --}}
                    <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror"
                        id="sale_price" name="sale_price" value="{{ old('sale_price', $bookingData['sale_price'] ?? '') }}"
                        required {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'readonly' : '' }}>
                    @error('sale_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- سعر التكلفة (للفندق) --}}
                @if (auth()->user()->role != 'Company')
                    {{-- سعر التكلفة (للفندق) - يظهر لغير الشركة --}}
                    <div class="col-md-3">
                        <label for="cost_price" class="form-label">السعر من الفندق <span
                                class="text-danger">*</span></label>
                        {{-- النوع هنا دايماً number لأن الشركة مش هتشوف الجزء ده أصلاً --}}
                        <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror"
                            id="cost_price" name="cost_price"
                            value="{{ old('cost_price', $bookingData['cost_price'] ?? '') }}" required
                            {{-- readonly لو جاي من إتاحة (لغير الشركة) --}}
                            {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'readonly' : '' }}>
                        @error('cost_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    {{-- لو اليوزر شركة، بنضيف حقل مخفي عشان نبعت القيمة (الكنترولر هيجيب الصح من الداتابيز) --}}
                    <input type="hidden" id="cost_price" name="cost_price"
                        value="{{ old('cost_price', $bookingData['cost_price'] ?? '') }}">
                    {{-- ممكن نضيف رسالة خطأ هنا لو حصل خطأ في الـ validation بتاع الحقل المخفي ده لو حبيت --}}
                    {{-- @error('cost_price') <div class="col-12"><small class="text-danger">خطأ في سعر التكلفة.</small></div> @enderror --}}
                @endif

                @error('cost_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            {{-- اختيار العملة --}}
            {{-- لو انت شركة ممنوع التعديل --}}
            <div class="col-md-3">
                <label for="currency" class="form-label">العملة <span class="text-danger">*</span></label>
                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency"
                    required
                    {{ auth()->user()->role == 'Company' || (isset($isBookingFromAvailability) && $isBookingFromAvailability) ? 'disabled' : '' }}>
                    <option value="SAR"
                        {{ old('currency', $bookingData['currency'] ?? 'SAR') == 'SAR' ? 'selected' : '' }}>ريال سعودي
                    </option>
                    <option value="KWD"
                        {{ old('currency', $bookingData['currency'] ?? '') == 'KWD' ? 'selected' : '' }}>دينار كويتي
                    </option>
                </select>
                @error('currency')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                {{-- حقل مخفي لإرسال القيمة لو الحقل disabled --}}
                @if (auth()->user()->role == 'Company' || (isset($isBookingFromAvailability) && $isBookingFromAvailability))
                    <input type="hidden" name="currency"
                        value="{{ old('currency', $bookingData['currency'] ?? 'SAR') }}">
                @endif
            </div>
            {{-- تاريخ الدخول --}}
            <div class="col-md-4">
                {{-- ... label ... --}}
                <label for="check_in" class="form-label">تاريخ الدخول <span class="text-danger">*</span></label>
                {{-- *** التأكد من type="text" وإضافة الشرط لـ onkeydown *** --}}
                <input type="text" class="form-control datepicker @error('check_in') is-invalid @enderror"
                    id="check_in" name="check_in" value="{{ old('check_in', $bookingData['check_in'] ?? '') }}"
                    {{-- min/max attributes --}} {{-- *** تحديد أقل تاريخ مسموح به *** --}}
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['availability_start_date'])) min="{{ $bookingData['availability_start_date'] }}" {{-- لو من إتاحة، استخدم تاريخ بدايتها --}}
           @elseif (!Auth::user() || strtolower(Auth::user()->role) !== 'admin')
               min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" {{-- لو مش أدمن ومش من إتاحة، استخدم تاريخ اليوم --}} @endif
                    {{-- *** تحديد أقصى تاريخ مسموح به (لو من إتاحة) *** --}}
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['availability_end_date'])) max="{{ $bookingData['availability_end_date'] }}" @endif
                    {{-- *** منع الكتابة اليدوية لغير الأدمن *** --}}
                    @if (!auth()->user() || strtolower(auth()->user()->role) !== 'admin') onkeydown="return false;" {{-- يمنع أي ضغطة زر --}}
               style="background-color: #e9ecef; cursor: pointer;" {{-- تغيير شكل الحقل ليوضح إنه للقراءة فقط --}} @endif
                    required placeholder="YYYY-MM-DD">
                @error('check_in')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- تاريخ الخروج --}}
            <div class="col-md-4">
                <label for="check_out" class="form-label">تاريخ الخروج <span class="text-danger">*</span></label>
                {{-- *** التأكد من type="text" وإضافة الشرط لـ onkeydown *** --}}
                <input type="text" class="form-control datepicker @error('check_out') is-invalid @enderror"
                    id="check_out" name="check_out" value="{{ old('check_out', $bookingData['check_out'] ?? '') }}"
                    {{-- *** تحديد أقل تاريخ مسموح به *** --}} {{-- تاريخ الخروج لازم يكون بعد تاريخ الدخول، لكن ممكن نحدد أقل تاريخ مسموح بيه بشكل عام --}}
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['availability_start_date'])) min="{{ $bookingData['availability_start_date'] }}" {{-- لو من إتاحة، استخدم تاريخ بدايتها --}}
           @elseif (!auth()->user() || strtolower(auth()->user()->role) !== 'admin')
               min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" {{-- لو مش أدمن ومش من إتاحة، استخدم تاريخ اليوم --}} @endif
                    {{-- *** تحديد أقصى تاريخ مسموح به (لو من إتاحة) *** --}}
                    @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['availability_end_date'])) max="{{ $bookingData['availability_end_date'] }}" @endif
                    {{-- *** منع الكتابة اليدوية لغير الأدمن *** --}}
                    @if (!auth()->user() || strtolower(auth()->user()->role) !== 'admin') onkeydown="return false;"
               style="background-color: #e9ecef; cursor: pointer;" @endif
                    required placeholder="YYYY-MM-DD">
                @error('check_out')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            {{-- الموظف المسؤول --}}
            <div class="col-md-4">
                <label for="employee_id" class="form-label">الموظف المسؤول <span class="text-danger">*</span></label>
                {{-- *** تعديل: إضافة disabled لو جاي من إتاحة (لكل المستخدمين) + تعديل القيمة الافتراضية *** --}}
                <select class="form-select select2 @error('employee_id') is-invalid @enderror" id="employee_id"
                    name="employee_id" required
                    {{ isset($isBookingFromAvailability) && $isBookingFromAvailability ? 'disabled' : '' }}>
                    {{-- تعطيل لو من إتاحة --}}
                    <option value="" disabled
                        {{ !isset($bookingData['employee_id']) && !old('employee_id') ? 'selected' : '' }}>اختر الموظف
                    </option> {{-- تعديل selected --}}
                    @foreach ($employees as $employee)
                        {{-- *** تعديل: استخدام employee_id من bookingData اللي جاي من الإتاحة *** --}}
                        <option value="{{ $employee->id }}"
                            {{ old('employee_id', $bookingData['employee_id'] ?? '') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                {{-- حقل مخفي لإرسال القيمة لو الحقل disabled --}}
                {{-- *** تعديل: إرسال القيمة المخفية دايماً لو الحقل disabled بسبب الإتاحة *** --}}
                @if (isset($isBookingFromAvailability) && $isBookingFromAvailability && isset($bookingData['employee_id']))
                    <input type="hidden" name="employee_id" value="{{ $bookingData['employee_id'] }}">
                @endif
            </div>

            {{-- ملاحظات --}}
            <div class="col-12">
                <label for="notes" class="form-label">الملاحظات (اختياري)</label>
                {{-- *** تعديل old() *** --}}
                <textarea class="form-control" id="notes" name="notes">{{ old('notes', $bookingData['notes'] ?? '') }}</textarea>
            </div>
    </div> {{-- *** نهاية div.row *** --}}

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">حفظ الحجز</button>
        {{-- *** تغيير رابط الإلغاء *** --}}
        <a href="{{ url()->previous() }}" class="btn btn-secondary">إلغاء</a>
    </div>
    </form>
    </div>
@endsection

@push('scripts')
    {{-- تضمين Select2 و DatePicker --}}
    {{-- <script src="..."></script> --}}


    {{-- تضمين ملف preventClick.js --}}
    <script src="{{ asset('js/preventClick.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة Select2
            // تأكد من أن الكلاس المستهدف هو .select2
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // تهيئة DatePicker (إذا كنت تستخدم مكتبة معينة)
            // *** بداية التعديل: تهيئة jQuery UI Datepicker ***
            // نتأكد إنه بيشتغل على الكلاس الصحيح
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd', // مهم عشان يطابق صيغة Y-m-d اللي بنستخدمها
                changeMonth: true, // السماح بتغيير الشهر
                changeYear: true, // السماح بتغيير السنة
                // مهم عشان نحدد تاريخ البداية والنهاية
                minDate: $('#check_in').attr('min'),
                maxDate: $('#check_in').attr('max')
                // ... أي خيارات تانية محتاجها
            });


            // ملء الحقول تلقائياً إذا كان الحجز من إتاحة
            const bookingData = @json($bookingData ?? null);
            const isBookingFromAvailability = @json($isBookingFromAvailability ?? false);

            if (isBookingFromAvailability && bookingData) {
                // قد تحتاج لتحديث قيم Select2 بعد تهيئتها
                if (bookingData.company_id) $('#company_id').val(bookingData.company_id).trigger('change');
                if (bookingData.agent_id) $('#agent_id').val(bookingData.agent_id).trigger('change');
                if (bookingData.hotel_id) $('#hotel_id').val(bookingData.hotel_id).trigger('change');
                if (bookingData.employee_id) $('#employee_id').val(bookingData.employee_id).trigger('change');

                // ملء الحقول الأخرى
                if (bookingData.room_type) document.getElementById('room_type').value = bookingData.room_type;
                if (bookingData.sale_price) document.getElementById('sale_price').value = bookingData.sale_price;
                // سعر التكلفة قد لا يكون متاحاً
                if (bookingData.cost_price) document.getElementById('cost_price').value = bookingData.cost_price;
                if (bookingData.currency) {
                    // تحديث القيمة في القائمة المنسدلة
                    document.getElementById('currency').value = bookingData.currency;

                    // تحديث القيمة في الحقل المخفي إذا كان موجوداً
                    const hiddenCurrencyInput = document.querySelector('input[type="hidden"][name="currency"]');
                    if (hiddenCurrencyInput) {
                        hiddenCurrencyInput.value = bookingData.currency;
                    }
                }
                if (bookingData.check_in) document.getElementById('check_in').value = bookingData.check_in;
                if (bookingData.check_out) document.getElementById('check_out').value = bookingData.check_out;
                // يمكنك إضافة حقل rooms إذا أردت ملئه أيضاً
                // if (bookingData.rooms) document.getElementById('rooms').value = bookingData.rooms;

                // جعل الحقول للقراءة فقط أو معطلة
                document.getElementById('agent_id').disabled = true;
                document.getElementById('hotel_id').disabled = true;
                document.getElementById('room_type').readOnly = true;
                document.getElementById('sale_price').readOnly = true;
                document.getElementById('cost_price').readOnly = true; // أو حسب الحاجة
            }
        });
    </script>
@endpush
