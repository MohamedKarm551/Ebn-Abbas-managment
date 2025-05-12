@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>حجوزات {{ $agent->name }}</h1>

        {{-- ملخص الحساب --}}
        @php
            $count = $bookings->count();
            $totalDue = $bookings->sum('due_to_agent');
        @endphp
        <div class="alert alert-info">
            <strong>ملخص الحساب:</strong><br>
            عدد الحجوزات المستحقة: {{ $dueCount }}<br>
            @foreach ($totalDueByCurrency as $currency => $amount)
                إجمالي المستحق ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
            @endforeach
            إجمالي اللي علينا: {{ number_format($totalDue) }} ر.س<br>
            <div style="font-weight: bold;text-decoration: underline;">
                @foreach ($totalPaidByCurrency as $currency => $amount)
                    المدفوع ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
                @endforeach
            </div>
            @foreach ($totalRemainingByCurrency as $currency => $amount)
                المتبقي ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
            @endforeach
            <small>المعادلة: ∑ (عدد الليالي الكلي × عدد الغرف × سعر الفندق) لكل الحجوزات</small>
        </div>
        <div class="mb-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="agentBookingsTable" data-type="agent">
                    <thead>
                        <tr>
                            <th style="width: 1%;" class="d-table-cell d-md-none"></th>
                            <th style="width: 5%;">#</th>
                            <th style="width: 5%;"></th> {{-- Checkbox --}}
                            <th>العميل</th>
                            <th>الشركة</th>
                            <th class="d-none d-md-table-cell">الفندق</th>
                            <th class="d-none d-md-table-cell" style="min-width: 100px;">تاريخ الدخول</th>
                            <th class="d-none d-md-table-cell" style="min-width: 100px;">تاريخ الخروج</th>
                            <th class="d-none d-md-table-cell text-center">عدد الغرف</th>
                            <th class="d-none d-md-table-cell" style="min-width: 110px;">سعر الفندق</th>
                            <th style="min-width: 110px;">السعر الكلي المستحق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $key => $booking)
                            <tr class="booking-main-row">
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
                                        'amountDueField' => 'due_to_agent',
                                        'amountPaidField' => 'agent_paid',
                                        'costPriceField' => 'cost_price',
                                    ])
                                </td>
                                <td class="align-middle">
                                    {{ $booking->client_name }}
                                    @if (!empty($booking->notes))
                                        <i class="fas fa-info-circle text-primary ms-2 fs-5 p-1" data-bs-toggle="popover"
                                            data-bs-trigger="hover focus" data-bs-placement="auto" title="ملاحظات"
                                            data-bs-content="{{ e($booking->notes) }}">
                                        </i>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $booking->company->name }}</td>
                                <td class="d-none d-md-table-cell align-middle">{{ $booking->hotel->name }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ $booking->check_in->format('d/m/Y') }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ $booking->check_out->format('d/m/Y') }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">{{ $booking->rooms }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ number_format($booking->cost_price, 2) }}
                                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                                 <td class="text-center align-middle">
                                    {{ number_format($booking->amount_due_to_hotel, 2) }}
                                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                            </tr>
                            <tr class="collapse booking-details-row d-md-none" id="details-{{ $booking->id }}">
                                <td colspan="6">
                                    <div class="p-2 bg-light border rounded">
                                        <strong>التفاصيل:</strong><br>
                                        <ul class="list-unstyled mb-0 small">
                                            <li><strong>الفندق:</strong> {{ $booking->hotel->name }}</li>
                                            <li><strong>الدخول:</strong> {{ $booking->check_in->format('d/m/Y') }}</li>
                                            <li><strong>الخروج:</strong> {{ $booking->check_out->format('d/m/Y') }}</li>
                                            <li><strong>الغرف:</strong> {{ $booking->rooms }}</li>
                                           <li><strong>سعر الفندق:</strong> 
                                                {{ number_format($booking->cost_price, 2) }}
                                                {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary" id="agentSelectRangeBtn">تحديد النطاق</button>
            <button class="btn btn-secondary" id="agentResetRangeBtn">إعادة تعيين النطاق</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingSelector('agentBookingsTable', 'agentSelectRangeBtn', 'agentResetRangeBtn');

            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                    return new bootstrap.Popover(popoverTriggerEl, {
                        html: true
                    });
                }
                return null;
            }).filter(Boolean);

            const detailCollapseElements = document.querySelectorAll('.booking-details-row');
            detailCollapseElements.forEach(el => {
                el.addEventListener('show.bs.collapse', event => {
                    const button = event.target.previousElementSibling.querySelector(
                        '.toggle-details-btn');
                    button.innerHTML = '<i class="fas fa-minus"></i>';
                });
                el.addEventListener('hide.bs.collapse', event => {
                    const button = event.target.previousElementSibling.querySelector(
                        '.toggle-details-btn');
                    button.innerHTML = '<i class="fas fa-plus"></i>';
                });
            });
        });
    </script>
@endpush
