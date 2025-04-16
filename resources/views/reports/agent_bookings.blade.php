@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>حجوزات {{ $agent->name }}</h1>
        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-bordered" id="agentBookingsTable"> {{-- ID للجدول --}}
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th> {{-- عمود الترقيم --}}
                            <th style="width: 5%;"></th> {{-- عمود Checkbox --}}
                            <th>العميل</th>
                            <th>الشركة</th>
                            <th>الفندق</th>
                            <th style="min-width: 100px;">تاريخ الدخول</th>
                            <th style="min-width: 100px;">تاريخ الخروج</th>
                            <th class="text-center">عدد الغرف</th>
                            <th style="min-width: 110px;">المبلغ</th> {{-- المبلغ المستحق من الشركة (كمثال) --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $key => $booking)
                            <tr style="cursor: pointer;">
                                <td class="text-center align-middle">{{ $key + 1 }}</td>
                                <td class="text-center align-middle">
                                    {{-- استخدم الـ Partial مع الحقول الصحيحة للوكيل --}}
                                    {{-- المبلغ هنا هو المستحق من الشركة كمثال، قد تحتاج لتعديله إذا كان المقصود عمولة الوكيل --}}
                                    @include('partials._booking_checkbox', [
                                        'booking' => $booking,
                                        'amountDueField' => 'amount_due_from_company', // المبلغ المستحق من الشركة
                                        'amountPaidField' => 'amount_paid_by_company', // المدفوع من الشركة
                                        'costPriceField' => 'sale_price', // سعر البيع للعميل
                                    ])
                                </td>
                                <td class="align-middle">{{ $booking->client_name }}
                                    @if (!empty($booking->notes))
                                        <i class="fas fa-info-circle text-primary ms-2" data-bs-toggle="popover"
                                            data-bs-trigger="hover focus" data-bs-placement="top" title="ملاحظات"
                                            data-bs-content="{{ e($booking->notes) }}">
                                        </i>
                                    @endif

                                </td>
                                <td class="align-middle">{{ $booking->company->name }}</td>
                                <td class="align-middle">{{ $booking->hotel->name }}</td>
                                <td class="text-center align-middle">{{ $booking->check_in->format('d/m/Y') }}</td>
                                <td class="text-center align-middle">{{ $booking->check_out->format('d/m/Y') }}</td>
                                <td class="text-center align-middle">{{ $booking->rooms }}</td>
                                <td class="text-center align-middle">{{ number_format($booking->amount_due_to_hotel) }}</td>
                                {{-- تأكد من أن هذا هو الحقل الصحيح --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- الأزرار --}}
                <button class="btn btn-primary" id="agentSelectRangeBtn">تحديد النطاق</button>
                <button class="btn btn-secondary" id="agentResetRangeBtn">إعادة تعيين النطاق</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // استدعاء دالة التهيئة بالـ IDs الصحيحة لهذه الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingSelector('agentBookingsTable', 'agentSelectRangeBtn', 'agentResetRangeBtn');
        });
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                return new bootstrap.Popover(popoverTriggerEl, { html: true });
            }
            return null;
        }).filter(Boolean);
        // console.log('Agent Popovers initialized.');
    </script>
@endpush
