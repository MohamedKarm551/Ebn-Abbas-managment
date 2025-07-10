@extends('layouts.app')

@section('title', 'حجوزات الرحلة البرية')
@push('styles')
    <style>

    </style>
@endpush
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>حجوزات الرحلة البرية #{{ $landTrip->id }}</h1>
            <div>
                <a href="{{ route('admin.land-trips.create-booking', $landTrip->id) }}" class="btn btn-success me-2">
                    <i class="fas fa-plus-circle me-1"></i> إنشاء حجز
                </a>
                <a href="{{ route('admin.land-trips.show', $landTrip->id) }}" class="btn btn-info">
                    <i class="fas fa-info-circle me-1"></i> تفاصيل الرحلة
                </a>
                <a href="{{ route('admin.land-trips.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                </a>
            </div>
        </div>

        <div class="card shadow mb-4" style="
    opacity: 0.8;
">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">معلومات الرحلة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <h6>نوع الرحلة:</h6>
                        <p class="lead">{{ $landTrip->tripType->name ?? 'غير معروف' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6>تاريخ المغادرة:</h6>
                        <p class="lead">{{ $landTrip->departure_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6>تاريخ العودة:</h6>
                        <p class="lead">{{ $landTrip->return_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6>عدد الأيام:</h6>
                        <p class="lead">{{ $landTrip->days_count }} يوم</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow" style="
    opacity: 0.9;
">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">الحجوزات</h5>
            </div>
            <div class="card-body">
                @if ($bookings->isEmpty())
                    <div class="alert alert-info">لا توجد حجوزات لهذه الرحلة حتى الآن.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الشركة</th>
                                    <th>اسم العميل</th>
                                    {{-- <th>رقم الهاتف</th> --}}
                                    <th>نوع الغرفة</th>
                                    <th>عدد الغرف</th>
                                    <th>سعر الغرفة</th>
                                    <th>المستحق من الشركة</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $index => $booking)
                                    @php
                                        $currencySymbol = $booking->currency == 'KWD' ? 'د.ك' : 'ر.س';
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $booking->company->name ?? 'غير معروف' }}</td>
                                        <td>{{ $booking->client_name }}</td>
                                        {{-- <td>{{ $booking->phone ?? 'غير متوفر' }}</td> --}}
                                        <td>{{ $booking->roomPrice->roomType->room_type_name ?? 'غير معروف' }}</td>
                                        <td>{{ $booking->rooms }}</td>
                                        <td>{{ number_format($booking->sale_price ?? 0, 2) }} {{ $currencySymbol }}</td>
                                        <td>{{ number_format($booking->amount_due_from_company, 2) }}
                                            {{ $currencySymbol }}</td>
                                        <td>{{ number_format($booking->amount_paid_by_company ?? 0, 2) }}
                                            {{ $currencySymbol }}</td>
                                        <td>{{ number_format($booking->amount_due_from_company - ($booking->amount_paid_by_company ?? 0), 2) }}
                                            {{ $currencySymbol }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.land-trips.bookings.voucher', $booking->id) }}"
                                                    class="btn btn-info" title="عرض الفاوتشر" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.land-trips.bookings.edit', $booking->id) }}"
                                                    class="btn btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="حذف الحجز"
                                                    onclick="confirmDeleteBooking({{ $booking->id }}, '{{ $booking->client_name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- الإجماليات مقسمة حسب العملة -->
                    @php
                        $bookingsBySAR = $bookings->where('currency', 'SAR')->all();
                        $bookingsByKWD = $bookings->where('currency', 'KWD')->all();

                        $totalRoomsSAR = collect($bookingsBySAR)->sum('rooms');
                        $totalDueFromCompanySAR = collect($bookingsBySAR)->sum('amount_due_from_company');
                        $totalPaidByCompanySAR = collect($bookingsBySAR)->sum('amount_paid_by_company');
                        $totalRemainingSAR = $totalDueFromCompanySAR - $totalPaidByCompanySAR;

                        $totalRoomsKWD = collect($bookingsByKWD)->sum('rooms');
                        $totalDueFromCompanyKWD = collect($bookingsByKWD)->sum('amount_due_from_company');
                        $totalPaidByCompanyKWD = collect($bookingsByKWD)->sum('amount_paid_by_company');
                        $totalRemainingKWD = $totalDueFromCompanyKWD - $totalPaidByCompanyKWD;

                        $hasSAR = count($bookingsBySAR) > 0;
                        $hasKWD = count($bookingsByKWD) > 0;
                    @endphp

                    <!-- الإجماليات للريال السعودي -->
                    @if ($hasSAR)
                        <div class="mt-4">
                            <h5 class="border-bottom pb-2 text-primary">إجمالي الريال السعودي (ر.س)</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>عدد الحجوزات</th>
                                            <th>عدد الغرف</th>
                                            <th>إجمالي المستحق</th>
                                            <th>إجمالي المدفوع</th>
                                            <th>إجمالي المتبقي</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-light fw-bold">
                                            <td>{{ count($bookingsBySAR) }}</td>
                                            <td>{{ $totalRoomsSAR }}</td>
                                            <td>{{ number_format($totalDueFromCompanySAR, 2) }} ر.س</td>
                                            <td>{{ number_format($totalPaidByCompanySAR, 2) }} ر.س</td>
                                            <td>{{ number_format($totalRemainingSAR, 2) }} ر.س</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- الإجماليات للدينار الكويتي -->
                    @if ($hasKWD)
                        <div class="mt-4">
                            <h5 class="border-bottom pb-2 text-primary">إجمالي الدينار الكويتي (د.ك)</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>عدد الحجوزات</th>
                                            <th>عدد الغرف</th>
                                            <th>إجمالي المستحق</th>
                                            <th>إجمالي المدفوع</th>
                                            <th>إجمالي المتبقي</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-light fw-bold">
                                            <td>{{ count($bookingsByKWD) }}</td>
                                            <td>{{ $totalRoomsKWD }}</td>
                                            <td>{{ number_format($totalDueFromCompanyKWD, 2) }} د.ك</td>
                                            <td>{{ number_format($totalPaidByCompanyKWD, 2) }} د.ك</td>
                                            <td>{{ number_format($totalRemainingKWD, 2) }} د.ك</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-center mt-4">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- استدعاء الخلفية التفاعلية -->
    <script type="module">
        import {
            initParticlesBg
        } from '/js/particles-bg.js';
        initParticlesBg({
            points: 60, // عدد النقاط (افتراضي: 60)
            dist: 140, // المسافة القصوى بين النقاط لرسم الخط (افتراضي: 140)
            mouseDist: 180, // المسافة القصوى بين النقطة والماوس لرسم الخط (افتراضي: 180)
            colors: [ // ألوان النقاط (افتراضي: مجموعة ألوان عصرية)
                '#06b6d4', '#f59e42', '#6366f1', '#f43f5e', '#22d3ee'
            ],
            zIndex: 0, // ترتيب الطبقة (z-index) للكانفاس (افتراضي: 0)
            opacity: 1, // شفافية الكانفاس (افتراضي: 1)
            dotRadius: 3.2, // نصف قطر النقطة (افتراضي: 3.2)
            lineColor: '#b6b6b6', // لون الخطوط بين النقاط (افتراضي: #b6b6b6)
            mouseLineColor: '#06b6d4', // لون الخط مع الماوس (افتراضي: #06b6d4)
            shadowBlur: 8 // شدة الظل حول النقاط (افتراضي: 8)
        });
    </script>
    <!-- مودال تأكيد حذف الحجز -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1" aria-labelledby="deleteBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteBookingModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>تأكيد حذف الحجز
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5>هل أنت متأكد من حذف هذا الحجز؟</h5>
                    <p class="text-muted">
                        العميل: <strong id="clientNameToDelete"></strong><br>
                        <span class="text-danger">هذا الإجراء لا يمكن التراجع عنه!</span>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>إلغاء
                </button>
                <form id="deleteBookingForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>نعم، احذف الحجز
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function confirmDeleteBooking(bookingId, clientName) {
        // تحديث بيانات المودال
        document.getElementById('clientNameToDelete').textContent = clientName;
        
        // تحديد action للفورم
        const form = document.getElementById('deleteBookingForm');
        form.action = `{{ route('admin.land-trips.bookings.destroy', ':booking') }}`.replace(':booking', bookingId);
        
        // إظهار المودال
        const modal = new bootstrap.Modal(document.getElementById('deleteBookingModal'));
        modal.show();
    }
</script>
@endpush
