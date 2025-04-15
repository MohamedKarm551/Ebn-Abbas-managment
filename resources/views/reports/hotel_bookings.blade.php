@extends('layouts.app')

@section('content')
<div class="container">
    <h1>حجوزات {{ $hotel->name }}</h1>
    <div class="card mb-4">
        <div class="card-body">
            <table class="table table-bordered" id="hotelBookingsTable"> {{-- ID للجدول --}}
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th> {{-- عمود الترقيم --}}
                        <th style="width: 5%;"></th> {{-- عمود Checkbox --}}
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>جهة الحجز</th>
                        <th style="min-width: 100px;">تاريخ الدخول</th>
                        <th style="min-width: 100px;">تاريخ الخروج</th>
                        <th class="text-center">عدد الأيام</th>
                        <th class="text-center">عدد الغرف</th>
                        <th style="min-width: 90px;">السعر</th> {{-- سعر التكلفة للفندق --}}
                        <th style="min-width: 110px;">الإجمالي</th> {{-- المستحق للفندق --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $key => $booking)
                        <tr style="cursor: pointer;">
                            <td class="text-center align-middle">{{ $key + 1 }}</td>
                            <td class="text-center align-middle">
                                {{-- استخدام الـ Partial مع الحقول الصحيحة للفندق --}}
                                @include('partials._booking_checkbox', [
                                    'booking' => $booking,
                                    'amountDueField' => 'amount_due_to_hotel', // الإجمالي المستحق للفندق
                                    'amountPaidField' => 'amount_paid_to_hotel', // المدفوع للفندق
                                    'costPriceField' => 'cost_price' // سعر التكلفة (السعر)
                                ])
                            </td>
                            <td class="align-middle">{{ $booking->client_name }}</td>
                            <td class="align-middle">{{ $booking->company->name }}</td>
                            <td class="align-middle">{{ $booking->agent->name }}</td>
                            <td class="text-center align-middle">{{ $booking->check_in->format('d/m/Y') }}</td>
                            <td class="text-center align-middle">{{ $booking->check_out->format('d/m/Y') }}</td>
                            <td class="text-center align-middle">{{ $booking->days }}</td>
                            <td class="text-center align-middle">{{ $booking->rooms }}</td>
                            <td class="text-center align-middle">{{ number_format($booking->cost_price) }}</td>
                            <td class="text-center align-middle">{{ number_format($booking->amount_due_to_hotel) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- الأزرار --}}
            <button class="btn btn-primary" id="hotelSelectRangeBtn">تحديد النطاق</button>
            <button class="btn btn-secondary" id="hotelResetRangeBtn">إعادة تعيين النطاق</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // استدعاء دالة التهيئة بالـ IDs الصحيحة لهذه الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        initializeBookingSelector('hotelBookingsTable', 'hotelSelectRangeBtn', 'hotelResetRangeBtn');
    });
</script>
@endpush