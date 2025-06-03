@extends('layouts.app')

@section('title', 'غرفة ' . $room->room_number . ' - ' . $room->hotel->name)
 <link rel="stylesheet" href="{{ url('css/showRoomDetails.css') }}?v={{ rand() }}">

@section('content')
    @php
        function getArabicRoomTypeName($type)
        {
            $namesMapping = [
                'single' => 'فردية',
                'double' => 'زوجية',
                'triple' => 'ثلاثية',
                'quad' => 'رباعية',
                'quint' => 'خماسية',
                // دعم القيم القديمة للتوافق
                'standard' => 'قياسية',
                'deluxe' => 'ديلوكس',
                'suite' => 'جناح',
                'family' => 'عائلية',
            ];
            return $namesMapping[$type] ?? $type;
        }
    @endphp
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-door-open text-primary"></i>
                غرفة {{ $room->room_number }} - {{ $room->hotel->name }}
            </h1>
            <div>
                <a href="{{ route('hotel.rooms.hotel', $room->hotel_id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right"></i> عودة لقائمة غرف الفندق
                </a>
            </div>
        </div>

        <!-- أضف هذا في بداية الصفحة بعد div.container-fluid مباشرة -->
        <div class="mb-3 no-print">
            <a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-pie me-1"></i> عرض حالة الغرف
            </a>
            <a href="{{ route('reports.advanced') }}?date={{ now()->format('Y-m-d') }}"
                class="btn btn-outline-secondary ms-2">
                <i class="fas fa-chart-bar me-1"></i> التقارير المتقدمة
            </a>
        </div>

        <div class="row">
            <!-- بطاقة تفاصيل الغرفة -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div
                        class="card-header py-3 {{ $room->is_occupied ? 'bg-danger text-white' : 'bg-success text-white' }}">
                        <h5 class="mb-0 d-flex justify-content-between align-items-center">
                            <span>معلومات الغرفة</span>
                            <span class="badge bg-light text-{{ $room->is_occupied ? 'danger' : 'success' }}">
                                {{ $room->is_occupied ? 'مشغولة' : 'متاحة' }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="text-center mb-3">
                                <div class="display-1">{{ $room->room_number }}</div>
                                <div class="text-muted">{{ $room->type }}</div>
                            </div>

                            <div class="bed-icons text-center my-3">
                                @php
                                    $bedsCount = $maxGuests;
                                    $occupiedBeds = $room->is_occupied ? $currentGuestsCount : 0;
                                @endphp

                                @for ($i = 1; $i <= $bedsCount; $i++)
                                    @if ($i <= $occupiedBeds)
                                        <i class="fas fa-bed text-danger fa-2x mx-1" title="سرير مشغول"></i>
                                    @else
                                        <i class="fas fa-bed text-success fa-2x mx-1" title="سرير متاح"></i>
                                    @endif
                                @endfor

                                <div class="mt-2 text-muted">
                                    {{ $occupiedBeds }} / {{ $bedsCount }} مشغول
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-6">
                                    <div class="text-muted mb-1">الفندق</div>
                                    <div class="fw-bold">{{ $room->hotel->name }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted mb-1">الطابق</div>
                                    <div class="fw-bold">{{ $room->floor ?: 'غير محدد' }}</div>
                                </div>
                            </div>

                            @if ($room->notes)
                                <div class="mt-3">
                                    <div class="text-muted mb-1">ملاحظات</div>
                                    <div>{{ $room->notes }}</div>
                                </div>
                            @endif
                        </div>

                        @if (!$room->is_occupied)
                            <div class="text-center mt-4">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignRoomModal">
                                    <i class="fas fa-user-plus me-1"></i> تخصيص الغرفة لنزيل
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- بيانات النزلاء الحاليين (إذا كانت الغرفة مشغولة) -->
            <div class="col-md-8 mb-4">
                @if ($room->is_occupied && $room->activeAssignments->isNotEmpty())
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0">
                                معلومات النزلاء الحاليين ({{ $room->activeAssignments->count() }} ضيف)
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach ($room->activeAssignments as $assignment)
                                @php
                                    // نحسب عدد الأيام المتبقية لهذا التخصيص من RoomAssignment
                                    $today = \Carbon\Carbon::now()->startOfDay();
                                    $checkoutDate = $assignment->check_out->startOfDay();
                                    $daysLeft = $today->diffInDays($checkoutDate, false);
                                @endphp

                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        {{-- اسم الضيف من الحجز المرتبط --}}
                                        <h4>{{ $assignment->booking->client_name }}</h4>

                                        {{-- اسم الشركة والوكيل (إن وجد) --}}
                                        <div class="text-muted mb-2">
                                            {{ $assignment->booking->company->name ?? 'بدون شركة' }}
                                            @if ($assignment->booking->agent)
                                                <span class="mx-2">|</span>
                                                {{ $assignment->booking->agent->name }}
                                            @endif
                                        </div>

                                        {{-- تواريخ الحجز (من حقل التخصيص نفسه RoomAssignment) --}}
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <div class="text-muted mb-1">تاريخ الدخول</div>
                                                <div class="fw-bold">{{ $assignment->booking->check_in->format('Y-m-d') }}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted mb-1">تاريخ الخروج</div>
                                                <div class="fw-bold">{{ $assignment->booking->check_out->format('Y-m-d') }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- عدد الليالي والليالي المتبقية --}}
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <div class="text-muted mb-1">عدد الليالي</div>
                                                <div class="fw-bold">
                                                    {{ $assignment->check_in->diffInDays($assignment->check_out) }}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted mb-1">الليالي المتبقية</div>
                                                <div class="fw-bold">
                                                    <span
                                                        class="badge {{ $daysLeft <= 0 ? 'bg-danger' : ($daysLeft <= 1 ? 'bg-warning' : 'bg-success') }}">
                                                        {{ $daysLeft <= 0 ? 'انتهت الإقامة' : $daysLeft . ' يوم' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ملاحظات التخصيص الحالية إن وجدت --}}
                                        @if ($assignment->notes)
                                            <div class="mt-2">
                                                <div class="text-muted mb-1">ملاحظات</div>
                                                <div>{{ $assignment->notes }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- إجراءات لكل تخصيص: تفاصيل الحجز و زر “إخلاء” --}}
                                    <div class="col-md-4 text-md-end mt-4 mt-md-0">
                                        <a href="{{ route('bookings.show', $assignment->booking->id) }}"
                                            class="btn btn-info mb-2 d-flex align-items-center"
                                            style="background: linear-gradient(135deg, #0099f7, #f11712); border: none;">
                                            <i class="fas fa-info-circle me-1"></i> تفاصيل الحجز
                                        </a>

                                        <button type="button" class="btn btn-warning d-flex align-items-center"
                                            data-bs-toggle="modal"
                                            data-bs-target="#endAssignmentModal{{ $assignment->id }}"
                                            style="background: linear-gradient(135deg, #f7971e, #ffd200); border: none;">
                                            <i class="fas fa-sign-out-alt me-1"></i> إخلاء الغرفة
                                        </button>

                                        {{-- موديل تأكيد إخلاء الغرفة لكل تخصيص --}}
                                        <div class="modal fade" id="endAssignmentModal{{ $assignment->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تأكيد إخلاء الغرفة</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <p>هل أنت متأكد من إخلاء الغرفة للنزيل
                                                            <strong>{{ $assignment->booking->client_name }}</strong>؟
                                                        </p>
                                                        @if ($daysLeft > 0)
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                تنبيه: لا يزال لدى النزيل {{ $daysLeft }} يوم متبقي في
                                                                الحجز.
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">إلغاء</button>
                                                        <form
                                                            action="{{ route('hotel.rooms.end-assignment', $assignment->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-danger">تأكيد إخلاء
                                                                الغرفة</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if (!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- عندما تكون الغرفة متاحة تمامًا --}}
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center py-5">
                            <img src="{{ asset('images/empty-room.svg') }}" alt="غرفة خالية"
                                style="max-width: 200px; opacity: 0.6;">
                            <h3 class="mt-4 text-muted">الغرفة متاحة حاليًا</h3>
                            <p>يمكنك تخصيص نزيل جديد لهذه الغرفة</p>
                            <button class="btn btn-primary mt-2" data-bs-toggle="modal"
                                data-bs-target="#assignRoomModal">
                                <i class="fas fa-user-plus me-1"></i> تخصيص الغرفة لنزيل
                            </button>
                        </div>
                    </div>
                @endif
            </div>


        </div>

        <!-- سجل تاريخ الغرفة -->
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0">
                    <i class="fas fa-history text-primary"></i>
                    سجل تاريخ الغرفة
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم النزيل</th>
                                <th>الشركة</th>
                                <th>تاريخ الدخول</th>
                                <th>تاريخ الخروج</th>
                                <th>مدة الإقامة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($room->allBookings as $index => $assignment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('bookings.show', $assignment->booking->id) }}">
                                            {{ $assignment->booking->client_name }}
                                        </a>
                                    </td>
                                    <td>{{ $assignment->booking->company->name ?? '-' }}</td>

                                    {{-- نستعمل تواريخ الحجز نفسها من جدول bookings --}}
                                    <td>{{ $assignment->booking->check_in->format('Y-m-d') }}</td>
                                    <td>{{ $assignment->booking->check_out->format('Y-m-d') }}</td>
                                    <td>
                                        {{ $assignment->booking->check_in->diffInDays($assignment->booking->check_out) }}
                                        ليلة
                                    </td>

                                    <td>
                                        <span
                                            class="badge {{ $assignment->status == 'active'
                                                ? 'bg-primary'
                                                : ($assignment->status == 'completed'
                                                    ? 'bg-danger'
                                                    : 'bg-secondary') }}">
                                            {{ $assignment->status == 'active' ? 'نشط' : ($assignment->status == 'completed' ? 'انتهى' : 'ملغي') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">لا يوجد سجل سابق لهذه الغرفة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- موديل تخصيص الغرفة -->
        <div class="modal fade" id="assignRoomModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تخصيص نزيل للغرفة {{ $room->room_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('hotel.rooms.assign') }}" method="POST" id="assignRoomForm">
                            @csrf
                            <input type="hidden" name="room_id" value="{{ $room->id }}">
         
                          
                            <div class="mb-3">
                                <label for="booking_id" class="form-label">اختر الحجز</label>
                                <select class="form-select" id="booking_id" name="booking_id" required>
                                    <option value="">-- اختر حجز لتخصيصه --</option>
                                    @foreach ($availableBookings as $booking)
                                        <option value="{{ $booking->id }}">
                                            {{ $booking->client_name }} | {{ $booking->company->name ?? 'بدون شركة' }} |
                                            {{ $booking->check_in->format('d/m/Y') }} -
                                            {{ $booking->check_out->format('d/m/Y') }}
                                            ({{ $booking->check_in->diffInDays($booking->check_out) }} ليلة)
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">اختر حجزًا من القائمة لتخصيصه لهذه الغرفة</div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">ملاحظات (اختياري)</label>
                                <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                            </div>

                            <!-- داخل الموديل، قبل قائمة الاختيار -->
                            <div class="small text-muted mb-2">
                                عدد الحجوزات المتاحة: {{ $availableBookings->count() }}
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" form="assignRoomForm" class="btn btn-primary">تخصيص الغرفة</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- موديل إضافة نزيل إضافي -->
        <div class="modal fade" id="addGuestModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة نزيل إضافي للغرفة {{ $room->room_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('hotel.rooms.add-guest') }}" method="POST" id="addGuestForm">
                            @csrf
                            <input type="hidden" name="room_id" value="{{ $room->id }}">

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>معلومات:</strong> يمكنك اختيار حجز من الحجوزات التي تنتظر تخصيص غرف لهذا الفندق.
                                <hr>
                                <strong>سعة الغرفة:</strong> {{ $maxGuests }} نزلاء |
                                <strong>النزلاء الحاليين:</strong> {{ $currentGuestsCount }} |
                                <strong>السعة المتبقية:</strong> {{ $remainingCapacity }} نزيل
                            </div>

                            <div class="mb-3">
                                <label for="booking_id" class="form-label">اختر حجز من الحجوزات المنتظرة</label>
                                <select class="form-select" id="add_booking_id" name="booking_id" required
                                    style="width: 100%;">
                                    <option value="">-- اختر حجز لإضافته كنزيل إضافي --</option>
                                    @forelse ($availableBookings as $booking)
                                        <option value="{{ $booking->id }}">
                                            {{ $booking->client_name ?? 'بدون اسم' }} |
                                            {{ $booking->company->name ?? 'بدون شركة' }} |
                                            {{ $booking->check_in ? $booking->check_in->format('d/m/Y') : 'بدون تاريخ' }} -
                                            {{ $booking->check_out ? $booking->check_out->format('d/m/Y') : 'بدون تاريخ' }}
                                            @if ($booking->check_in && $booking->check_out)
                                                ({{ $booking->check_in->diffInDays($booking->check_out) }} ليلة)
                                            @endif
                                        </option>
                                    @empty
                                        <option value="" disabled>لا توجد حجوزات متاحة للإضافة</option>
                                    @endforelse
                                </select>

                                <div class="mt-2">
                                    @if ($availableBookings->count() == 0)
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            لا توجد حجوزات متاحة للإضافة. يجب إنشاء حجز جديد أولاً.
                                        </div>
                                    @else
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            يتوفر {{ $availableBookings->count() }} حجز للإضافة
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">ملاحظات (اختياري)</label>
                                <textarea class="form-control" id="add_notes" name="notes" rows="2"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" form="addGuestForm" class="btn btn-primary">إضافة نزيل إضافي</button>
                    </div>
                </div>
            </div>
        </div>

        @php
            // حساب سعة الغرفة والسعة المتبقية
            $roomType = $room->type;
            $maxGuests =
                [
                    'single' => 1,
                    'double' => 2,
                    'triple' => 3,
                    'quad' => 4,
                    'quint' => 5,
                    'standard' => 2,
                    'deluxe' => 3,
                    'suite' => 2,
                    'family' => 4,
                ][$roomType] ?? 1;

            // $currentGuestsCount = $room->currentBooking ? ($room->currentBooking->booking->guests_count ?? 1) : 0;
            // $remainingCapacity = $maxGuests - $currentGuestsCount;

        @endphp

        @if ($room->is_occupied && $remainingCapacity > 0)
            <div class="card mt-3 border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">إضافة نزلاء إضافيين</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p>هذه غرفة {{ getArabicRoomTypeName($room->type) }} تستوعب {{ $maxGuests }} نزلاء كحد أقصى
                            </p>
                            <p class="mb-0">
                                <strong>النزلاء الحاليين:</strong> {{ $currentGuestsCount }} نزيل
                                <span class="mx-2">|</span>
                                <strong>السعة المتبقية:</strong> {{ $remainingCapacity }} نزيل
                            </p>
                        </div>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addGuestModal">
                            <i class="fas fa-user-plus me-1"></i> إضافة نزيل جديد
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تأكد من وجود jQuery و Select2
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            console.error("يرجى التأكد من تحميل jQuery و Select2 قبل هذا السكربت.");
            return;
        }

        /**
         * دالة لتهيئة Select2 على العنصر #booking_id
         * تقوم أولاً بمسح أي تهيئة سابقة (destroy) ثم تعيد التهيئة من الصفر
         */
        function initMainBookingSelect() {
            const $bookingSelect = $('#booking_id');
            if (!$bookingSelect.length) return;

            // حذف تهيئة Select2 السابقة لو كانت موجودة
            try {
                $bookingSelect.select2('destroy');
            } catch (e) {
                // إذا لم يكن مهيأ مسبقًا فلا نفعل شيئًا
            }

            // إعادة تهيئة Select2 مع الخيارات المطلوبة
            $bookingSelect.select2({
                theme: 'bootstrap-5',
                placeholder: 'ابحث عن حجز...',
                dropdownParent: $('#assignRoomModal'),
                width: '100%',
                language: {
                    noResults: function() {
                        return "لا توجد نتائج مطابقة";
                    }
                }
            });
        }

        // عندما يُعرض المودال، نقوم بتهيئة Select2
        $('#assignRoomModal').on('shown.bs.modal', function() {
            initMainBookingSelect();
        });

        // إذا رغبت بتهيئته مباشرةً عند تحميل الصفحة (اختياري)، يمكن إلغاء التعليق:
        // initMainBookingSelect();
    });
</script>
@endpush



