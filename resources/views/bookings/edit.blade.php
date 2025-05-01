@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
            <h1 class="mb-0">تعديل الحجز</h1>
            <button type="button" id="cancel-booking-btn" class="btn btn-outline-danger btn-sm ms-2 mb-2 mb-md-0">
                <i class="fas fa-ban"></i> إلغاء الحجز مؤقتا
            </button>
        </div>
        <form action="{{ route('bookings.update', $booking->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="client_name" class="form-label">اسم العميل</label>
                <input type="text" class="form-control" id="client_name" name="client_name"
                    value="{{ $booking->client_name }}" required>
            </div>
            <div class="mb-3">
                <label for="company_id" class="form-label">اسم الشركة</label>
                <select class="form-control" id="company_id" name="company_id" required>
                    <option value="" disabled selected>اختر الشركة</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ isset($booking) && $company->id == $booking->company_id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="agent_id" class="form-label">جهة الحجز</label>
                <select class="form-control" id="agent_id" name="agent_id" required>
                    <option value="" disabled selected>اختر جهة الحجز</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}"
                            {{ isset($booking) && $agent->id == $booking->agent_id ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="hotel_id" class="form-label">اسم الفندق</label>
                <select class="form-control" id="hotel_id" name="hotel_id" required>
                    <option value="" disabled selected>اختر الفندق</option>
                    @foreach ($hotels as $hotel)
                        <option value="{{ $hotel->id }}"
                            {{ isset($booking) && $hotel->id == $booking->hotel_id ? 'selected' : '' }}>
                            {{ $hotel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="room_type" class="form-label">نوع الغرفة</label>
                <input type="text" class="form-control" id="room_type" name="room_type"
                    value="{{ $booking->room_type }}" required>
            </div>

            <div class="mb-3">
                <label for="check_in" class="form-label">تاريخ الدخول</label>
                <input type="text" class="form-control" id="check_in" name="check_in"
                    value="{{ $booking->check_in->format('d/m/Y') }}" required {{-- *** بداية الإضافة: تعطيل الحقل لغير الأدمن *** --}}
                    @if (!auth()->user() || strtolower(auth()->user()->role) !== 'admin') disabled
                style="background-color: #e9ecef; cursor: not-allowed;" {{-- تغيير شكل الحقل ليوضح إنه معطل --}} @endif
                    {{-- *** نهاية الإضافة *** --}}>
                @error('check_in')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="check_out" class="form-label">تاريخ الخروج</label>
                <input type="text" class="form-control" id="check_out" name="check_out"
                    value="{{ $booking->check_out->format('d/m/Y') }}" required>
                @error('check_out')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="rooms" class="form-label">عدد الغرف</label>
                <input type="number" class="form-control" id="rooms" name="rooms" value="{{ $booking->rooms }}"
                    required>
            </div>
            <div class="mb-3">
                <label for="cost_price" class="form-label">السعر من الفندق</label>
                <input type="number" step="0.01" class="form-control" id="cost_price" name="cost_price"
                    value="{{ $booking->cost_price }}" required>
            </div>
            <div class="mb-3">
                <label for="sale_price" class="form-label">سعر البيع للشركة</label>
                <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price"
                    value="{{ $booking->sale_price }}" required>
            </div>
            <div class="mb-3">
                <label for="employee_id" class="form-label">الموظف المسؤول</label>
                <select class="form-control" id="employee_id" name="employee_id" required>
                    <option value="" disabled selected>اختر الموظف</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}"
                            {{ isset($booking) && $employee->id == $booking->employee_id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">الملاحظات</label>
                <textarea class="form-control" id="notes" name="notes">{{ $booking->notes }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        </form>
    </div>

    <!-- Include Flatpickr CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userRole = "{{ auth()->user()->role ?? 'guest' }}"; // جلب الدور من PHP
            const isAdmin = userRole.toLowerCase() === 'admin';
            const checkInInput = document.getElementById('check_in');

            if (isAdmin && checkInInput) { // نتأكد إن المستخدم أدمن والحقل موجود
                flatpickr(checkInInput, { // نستخدم المتغير checkInInput هنا
                    dateFormat: "d/m/Y", // صيغة يوم/شهر/سنة
                    allowInput: true, // السماح للأدمن بالكتابة لو عايز
                });
            } else if (checkInInput) {
                // لو المستخدم مش أدمن، ممكن نضيف كلاس عشان نوضح إنه معطل (اختياري)
                checkInInput.classList.add('disabled-flatpickr'); // ممكن تستخدم الكلاس ده في الـ CSS
            }
            flatpickr("#check_out", {
                dateFormat: "d/m/Y", // صيغة يوم/شهر/سنة
            });

            // زرار إلغاء الحجز مؤقتا
            document.getElementById('cancel-booking-btn').onclick = function() {
                let costInput = document.getElementById('cost_price');
                let saleInput = document.getElementById('sale_price');
                costInput.value = 0;
                saleInput.value = 0;
                costInput.classList.add('border', 'border-danger', 'fw-bold');
                saleInput.classList.add('border', 'border-danger', 'fw-bold');
                // Scroll للسعر
                costInput.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
            };

            ['cost_price', 'sale_price'].forEach(function(id) {
                document.getElementById(id).addEventListener('input', function() {
                    if (this.value != 0) {
                        this.classList.remove('border-danger', 'fw-bold');
                    }
                });
            });
        });
    </script>
@endsection
