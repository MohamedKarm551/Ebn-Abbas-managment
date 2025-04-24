@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>حجوزات {{ $company->name }}</h1>
        {{-- @php
        $count = $bookings->count();
        $totalDue = $bookings->sum('due_to_agent');
    @endphp --}}
       <div class="alert alert-info">
        <strong>ملخص الحساب:</strong><br>
        عدد الحجوزات: {{ $dueCount }}<br>
        إجمالي المستحق: {{ number_format($totalDue) }} ر.س<br>
        <div style="font-weight: bold;text-decoration: underline;"> المدفوع: {{ number_format($totalPaid) }} ر.س<br></div>
        المتبقي: {{ number_format($totalRemaining) }} ر.س<br>
        <small>المعادلة: (عدد الليالي الكلي × عدد الغرف × سعر البيع) ∑  لكل الحجوزات</small>
    </div>

        <div class=" mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="companyBookingsTable" data-type="company">
                        <thead>
                            <tr>
                                <th style="width: 1%;" class="d-table-cell d-md-none"></th>
                                <th>#</th>
                                <th></th>
                                <th>العميل</th>
                                <th class="d-none d-md-table-cell">جهة الحجز</th>
                                <th class="d-none d-md-table-cell">الفندق</th>
                                <th class="d-none d-md-table-cell">تاريخ الدخول</th>
                                <th class="d-none d-md-table-cell">تاريخ الخروج</th>
                                <th class="d-none d-md-table-cell">عدد الغرف</th>
                                <th class="d-none d-md-table-cell">السعر</th>
                                <th>الكلي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $key => $booking)
                                <tr class="booking-main-row">
                                    <td class="d-table-cell d-md-none text-center">
                                        <button class="btn btn-sm btn-outline-secondary toggle-details-btn"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#details-{{ $booking->id }}"
                                                aria-expanded="false"
                                                aria-controls="details-{{ $booking->id }}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @include('partials._booking_checkbox', [
                                            'booking' => $booking,
                                            'amountDueField' => 'due_to_company',
                                            'amountPaidField' => 'company_paid',
                                            'costPriceField' => 'sale_price',
                                        ])
                                    </td>
                                    <td>
                                        {{ $booking->client_name }}
                                        @if (!empty($booking->notes))
                                            <i class="fas fa-info-circle text-primary ms-2 fs-5 p-1"
                                               data-bs-toggle="popover"
                                               data-bs-trigger="hover focus"
                                               data-bs-placement="auto"
                                               title="ملاحظات"
                                               data-bs-content="{{ e($booking->notes) }}">
                                            </i>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $booking->agent->name ?? 'غير محدد' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $booking->hotel->name }}</td>
                                    <td class="d-none d-md-table-cell">{{ $booking->check_in->format('d/m/Y') }}</td>
                                    <td class="d-none d-md-table-cell">{{ $booking->check_out->format('d/m/Y') }}</td>
                                    <td class="d-none d-md-table-cell">{{ $booking->rooms }}</td>
                                    <td class="d-none d-md-table-cell">{{ number_format($booking->sale_price, 2) }} ر.س</td>
                                    <td>{{ number_format($booking->total_company_due, 2) }} ر.س</td>
                                </tr>
                                <tr class="collapse booking-details-row d-md-none" id="details-{{ $booking->id }}">
                                    <td colspan="5">
                                        <div class="p-2 bg-light border rounded">
                                            <strong>التفاصيل:</strong><br>
                                            <ul class="list-unstyled mb-0 small">
                                                <li><strong>جهة الحجز:</strong> {{ $booking->agent->name ?? 'غير محدد' }}</li>
                                                <li><strong>الفندق:</strong> {{ $booking->hotel->name }}</li>
                                                <li><strong>الدخول:</strong> {{ $booking->check_in->format('d/m/Y') }}</li>
                                                <li><strong>الخروج:</strong> {{ $booking->check_out->format('d/m/Y') }}</li>
                                                <li><strong>الغرف:</strong> {{ $booking->rooms }}</li>
                                                <li><strong>السعر:</strong> {{ number_format($booking->sale_price, 2) }} ر.س</li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-primary" id="companySelectRangeBtn">تحديد النطاق</button>
                <button class="btn btn-secondary" id="companyResetRangeBtn">إعادة تعيين النطاق</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingSelector('companyBookingsTable', 'companySelectRangeBtn', 'companyResetRangeBtn');

            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                    return new bootstrap.Popover(popoverTriggerEl, { html: true });
                }
                return null;
            }).filter(Boolean);

            const detailCollapseElements = document.querySelectorAll('.booking-details-row');
            detailCollapseElements.forEach(el => {
                el.addEventListener('show.bs.collapse', event => {
                    const triggerButton = document.querySelector(`button[data-bs-target="#${event.target.id}"] i`);
                    if (triggerButton) {
                        triggerButton.classList.remove('fa-plus');
                        triggerButton.classList.add('fa-minus');
                    }
                });

                el.addEventListener('hide.bs.collapse', event => {
                    const triggerButton = document.querySelector(`button[data-bs-target="#${event.target.id}"] i`);
                    if (triggerButton) {
                        triggerButton.classList.remove('fa-minus');
                        triggerButton.classList.add('fa-plus');
                    }
                });
            });
        });
    </script>
@endpush
