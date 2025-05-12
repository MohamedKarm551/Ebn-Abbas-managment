@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>حجوزات {{ $hotel->name }}</h1>
        <div class="mb-4">
            {{-- *** 1. إضافة div.table-responsive *** --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="hotelBookingsTable">
                    <thead>
                        <tr>
                            {{-- *** 2. إضافة عمود لزرار التوسيع (+/-) *** --}}
                            <th style="width: 1%;" class="d-table-cell d-md-none"></th>
                            <th style="width: 5%;">#</th>
                            <th style="width: 5%;"></th> {{-- Checkbox --}}
                            <th>العميل</th>
                            <th>الشركة</th>
                            <th>جهة الحجز</th>
                            {{-- الأعمدة اللي ممكن نخفيها في الصف الرئيسي على الشاشات الصغيرة --}}
                            <th class="d-none d-md-table-cell" style="min-width: 100px;">تاريخ الدخول</th>
                            <th class="d-none d-md-table-cell" style="min-width: 100px;">تاريخ الخروج</th>
                            <th class="d-none d-md-table-cell text-center">عدد الأيام</th>
                            <th class="d-none d-md-table-cell text-center">عدد الغرف</th>
                            <th class="d-none d-md-table-cell" style="min-width: 90px;">السعر علينا</th>
                            <th style="min-width: 110px;">الإجمالي</th> {{-- ده هنسيبه ظاهر --}}
                        </tr>
                    </thead>
                    <tbody>
                        {{-- *** 3. تعديل الـ Loop *** --}}
                        @foreach ($bookings as $key => $booking)
                            {{-- الصف الرئيسي (الأعمدة المهمة) --}}
                            <tr class="booking-main-row">
                                {{-- *** 4. إضافة زرار التوسيع (+/-) *** --}}
                                <td class="d-table-cell d-md-none text-center align-middle">
                                    <button class="btn btn-sm btn-outline-secondary toggle-details-btn"
                                        data-bs-toggle="collapse" data-bs-target="#details-{{ $booking->id }}"
                                        aria-expanded="false" aria-controls="details-{{ $booking->id }}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                                <td class="text-center align-middle">{{ $key + 1 }}</td>
                                <td class="text-center align-middle">
                                    @include('partials._booking_checkbox', [
                                        'booking' => $booking,
                                        'amountDueField' => 'amount_due_to_hotel',
                                        'amountPaidField' => 'amount_paid_to_hotel',
                                        'costPriceField' => 'sale_price', // سعر البيع للشركة هو التكلفة بالنسبة للفندق
                                    ])
                                </td>
                                <td class="align-middle">
                                    {{ $booking->client_name }}
                                    @if (!empty($booking->notes))
                                        {{-- أيقونة الملاحظات (Popover) --}}
                                        <i class="fas fa-info-circle text-primary ms-2 fs-5 p-1" data-bs-toggle="popover"
                                            data-bs-trigger="hover focus" data-bs-placement="auto" title="ملاحظات"
                                            data-bs-content="{{ e($booking->notes) }}">
                                        </i>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $booking->company->name }}</td>
                                <td class="align-middle">{{ $booking->agent->name }}</td>
                                {{-- الأعمدة المخفية في الصف الرئيسي على الشاشات الصغيرة --}}
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ $booking->check_in->format('d/m/Y') }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ $booking->check_out->format('d/m/Y') }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">{{ $booking->days }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">{{ $booking->rooms }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ number_format($booking->cost_price) }}
                                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                                <td class="text-center align-middle">
                                    {{ number_format($booking->amount_due_to_hotel) }}
                                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                            </tr>
                            {{-- *** 5. الصف المخفي للتفاصيل (يظهر فقط على الشاشات الصغيرة) *** --}}
                            <tr class="collapse booking-details-row d-md-none" id="details-{{ $booking->id }}">
                                {{-- خلية واحدة تمتد بعرض الجدول كله --}}
                                <td colspan="7"> {{-- عدد الأعمدة الظاهرة في الصف الرئيسي على الشاشة الصغيرة (زرار، #، تشيك، عميل، شركة، جهة، إجمالي) --}}
                                    <div class="p-2 bg-light border rounded">
                                        <strong>التفاصيل:</strong><br>
                                        <ul class="list-unstyled mb-0 small">
                                            <li><strong>الدخول:</strong> {{ $booking->check_in->format('d/m/Y') }}</li>
                                            <li><strong>الخروج:</strong> {{ $booking->check_out->format('d/m/Y') }}</li>
                                            <li><strong>الأيام:</strong> {{ $booking->days }}</li>
                                            <li><strong>الغرف:</strong> {{ $booking->rooms }}</li>
                                            <li><strong>السعر علينا:</strong>
                                                {{ number_format($booking->cost_price) }}
                                                {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- نهاية div.table-responsive --}}
            {{-- الأزرار --}}
            <button class="btn btn-primary" id="hotelSelectRangeBtn">تحديد النطاق</button>
            <button class="btn btn-secondary" id="hotelResetRangeBtn">إعادة تعيين النطاق</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة محدد الحجوزات
            initializeBookingSelector('hotelBookingsTable', 'hotelSelectRangeBtn', 'hotelResetRangeBtn');

            // تهيئة الـ Popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                    return new bootstrap.Popover(popoverTriggerEl, {
                        html: true
                    });
                }
                return null;
            }).filter(Boolean);

            // *** 6. إضافة JavaScript لتبديل الأيقونة (+/-) ***
            const detailCollapseElements = document.querySelectorAll('.booking-details-row');
            detailCollapseElements.forEach(el => {
                el.addEventListener('show.bs.collapse', event => {
                    const triggerButton = document.querySelector(
                        `button[data-bs-target="#${event.target.id}"] i`);
                    if (triggerButton) {
                        triggerButton.classList.remove('fa-plus');
                        triggerButton.classList.add('fa-minus');
                    }
                });
                el.addEventListener('hide.bs.collapse', event => {
                    const triggerButton = document.querySelector(
                        `button[data-bs-target="#${event.target.id}"] i`);
                    if (triggerButton) {
                        triggerButton.classList.remove('fa-minus');
                        triggerButton.classList.add('fa-plus');
                    }
                });
            });
        });
    </script>
@endpush
