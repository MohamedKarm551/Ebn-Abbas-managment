@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>حجوزات {{ $company->name }}</h1>

        <div class="alert alert-info">
            <strong>ملخص الحساب:</strong><br>
            عدد الحجوزات المستحقة: {{ $dueCount }}<br>
            إجمالي المستحق: {{ number_format($totalDue) }} ر.س<br>
            المدفوع: {{ number_format($totalPaid) }} ر.س<br>
            المتبقي: {{ number_format($totalRemaining) }} ر.س<br>
            <small>المعادلة: ∑ (عدد الليالي المنتهية حتى اليوم × عدد الغرف × سعر البيع) للحجوزات التي دخلت ولم تُسدّد كليًا</small>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-bordered" id="companyBookingsTable">
                    <thead>
                        <tr>
                            <th>#</th> {{-- عمود الترقيم --}}
                            <th></th> {{-- عمود الـ Checkbox --}}
                            <th>العميل</th>
                            <th>جهة الحجز</th> {{-- العمود الجديد لجهة الحجز --}}
                            <th>الفندق</th>
                            <th>تاريخ الدخول</th>
                            <th>تاريخ الخروج</th>
                            <th>عدد الغرف</th>
                            <th>المبلغ المستحق</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $key => $booking)
                            <tr style="cursor: pointer;">
                                <td>{{ $key + 1 }}</td> {{-- الترقيم --}}
                                <td>
                                    <label>
                                        <input type="checkbox" class="booking-checkbox" data-booking-id="{{ $booking->id }}"
                                            data-amount-due="{{ $booking->amount_due_from_company }}"
                                            data-amount-paid="{{ $booking->amount_paid_by_company }}"
                                            data-client-name="{{ $booking->client_name }}"
                                            data-hotel-name="{{ $booking->hotel->name }}" {{-- التأكد من إضافة اسم الفندق --}}
                                            data-check-in="{{ $booking->check_in->format('Y-m-d') }}" {{-- استخدام تنسيق Y-m-d --}}
                                            data-check-out="{{ $booking->check_out->format('Y-m-d') }}"
                                            {{-- استخدام تنسيق Y-m-d --}} data-rooms="{{ $booking->rooms }}"
                                            data-days="{{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }}"
                                            data-cost-price="{{ $booking->cost_price }}"
                                            onclick="event.stopPropagation();">
                                    </label>
                                </td> {{-- الـ Checkbox --}}
                                <td>{{ $booking->client_name }}
                                    @if (!empty($booking->notes))
                                        <i class="fas fa-info-circle text-primary ms-2" data-bs-toggle="popover"
                                            data-bs-trigger="hover focus" {{-- يظهر عند الهوفر أو الفوكس --}} data-bs-placement="top"
                                            title="ملاحظات" data-bs-content="{{ e($booking->notes) }}">
                                            {{-- بنستخدم e() للأمان --}}
                                        </i>
                                    @endif
                                </td>
                                <td>{{ $booking->agent->name ?? 'غير محدد' }}</td> {{-- خلية جهة الحجز الجديدة --}}

                                <td>{{ $booking->hotel->name }}</td>
                                <td>{{ $booking->check_in->format('d/m/Y') }}</td>
                                <td>{{ $booking->check_out->format('d/m/Y') }}</td>
                                <td>{{ $booking->rooms }}</td>
                                <td>{{ number_format($booking->amount_due_from_company) }}</td>
                                <td>{{ number_format($booking->amount_paid_by_company) }}</td>
                                <td>{{ number_format($booking->amount_due_from_company - $booking->amount_paid_by_company) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- استخدم IDs متوافقة مع استدعاء JS --}}
                <button class="btn btn-primary" id="companySelectRangeBtn">تحديد النطاق</button>
                <button class="btn btn-secondary" id="companyResetRangeBtn">إعادة تعيين النطاق</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // الاستدعاء الآن متوافق مع الـ IDs في HTML
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingSelector('companyBookingsTable', 'companySelectRangeBtn', 'companyResetRangeBtn');
            // *** بداية الكود الجديد: تهيئة الـ Popovers ***
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                // بنتأكد إن الـ popover مش متفعل قبل كده عشان نتجنب التكرار
                if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                    return new bootstrap.Popover(popoverTriggerEl, {
                        html: true // اسمح بـ HTML لو محتاج، بس خلي بالك من الأمان
                    });
                }
                return null;
            }).filter(Boolean); // بنشيل الـ nulls

        });
    </script>
@endpush
