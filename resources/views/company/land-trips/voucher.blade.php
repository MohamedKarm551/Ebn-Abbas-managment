@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>إيصال حجز رحلة برية 
                        <br>   <br>
                        <strong class="d-block mb-1 bg-warning">مطلوب للتأكيد:</strong>
      <span class="bg-danger">لتأكيد الحجز أرسل صور المدنية والتأشيرة للموظف <span class="badge bg-light text-dark p-2">ملاك</span></span>
                        </h3>
                    <a href="{{ route('company.land-trips.downloadVoucher', $booking->id) }}" class="btn btn-light">
                        <i class="fas fa-download"></i> تحميل PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>معلومات الرحلة</h5>
                        <p><strong>رقم الرحلة:</strong> {{ $booking->landTrip->id }}</p>
                        <p><strong>نوع الرحلة:</strong> {{ $booking->landTrip->tripType->name }}</p>
                        <p><strong>تاريخ المغادرة:</strong>
                            {{ \Carbon\Carbon::parse($booking->landTrip->departure_date)->format('Y-m-d') }}</p>
                        <p><strong>تاريخ العودة:</strong>
                            {{ \Carbon\Carbon::parse($booking->landTrip->return_date)->format('Y-m-d') }}</p>
                        <p><strong>عدد الأيام:</strong> {{ $booking->landTrip->days_count }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>معلومات الفندق</h5>
                        <p><strong>اسم الفندق:</strong>
                            {{ $booking->landTrip->hotel->name ?? 'غير محدد' }}</p>
                        <!-- معلومات أخرى ولكن لا تعرض جهة الحجز -->

                    </div>
                    <div class="col-md-6">
                        <h5>معلومات الحجز</h5>
                        <p><strong>اسم العميل:</strong> {{ $booking->client_name }}</p>
                        <p><strong>نوع الغرفة:</strong> {{ $booking->roomPrice->roomType->room_type_name }}</p>
                        <p><strong>عدد الغرف:</strong> {{ $booking->rooms }}</p>
                        <p><strong>السعر اليومي:</strong> {{ $booking->sale_price }}</p>
                        <p><strong>المبلغ الإجمالي:</strong> {{ $booking->amount_due_from_company }}</p>
                    </div>
                </div>

                @if ($booking->notes)
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5>ملاحظات</h5>
                            <p>{{ $booking->notes }}</p>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12 text-center mt-4">
                        <p class="small">تم الحجز بواسطة: {{ Auth::user()->name }} - بتاريخ:
                            {{ $booking->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('company.land-trips.index') }}" class="btn btn-secondary">العودة للرحلات</a>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/preventClick.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '<i class="fas fa-info-circle text-primary me-2"></i>تأكيد الحجز',
                html: `
                <div class="text-right p-3">
                    <div class="alert alert-warning p-3 mb-3">
                        <strong>هام:</strong> لتأكيد الحجز، يرجى إرسال المستندات التالية للموظف <b>ملاك</b>:
                    </div>
                    <ul class="list-group custom-list">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-id-card text-primary me-2"></i>
                            صورة الهوية المدنية
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-passport text-primary me-2"></i>
                            صورة التأشيرة
                        </li>
                    </ul>
                    <div class="alert alert-light mt-3">
                        <small>سيتم تأكيد حجزكم بعد استلام المستندات المطلوبة</small>
                    </div>
                </div>
            `,
                icon: null,
                confirmButtonText: 'تم الفهم',
                confirmButtonColor: '#3490dc',
                allowOutsideClick: false,
                focusConfirm: false,
                customClass: {
                    container: 'arabic-sweetalert',
                    title: 'fw-bold fs-5',
                    confirmButton: 'btn btn-lg btn-primary px-4'
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .arabic-sweetalert {
            font-family: 'Cairo', 'Tajawal', sans-serif !important;
        }

        .custom-list {
            text-align: right;
            direction: rtl;
            border-radius: 8px;
            overflow: hidden;
        }

        .custom-list .list-group-item {
            padding: 12px 16px;
            border-left: none;
            border-right: 3px solid #3490dc;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffecb5;
            color: #664d03;
            border-radius: 6px;
        }
    </style>
@endpush
