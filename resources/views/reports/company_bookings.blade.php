@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>حجوزات {{ $company->name }}</h1>

        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-bordered" id="companyBookingsTable">
                    <thead>
                        <tr>
                            <th>#</th> {{-- عمود الترقيم --}}
                            <th></th> {{-- عمود الـ Checkbox --}}
                            <th>العميل</th>
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
                                            data-check-out="{{ $booking->check_out->format('Y-m-d') }}" {{-- استخدام تنسيق Y-m-d --}}
                                            data-rooms="{{ $booking->rooms }}"
                                            data-days="{{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }}"
                                            data-cost-price="{{ $booking->cost_price }}"
                                            onclick="event.stopPropagation();">
                                    </label> 
                                </td> {{-- الـ Checkbox --}}
                                <td>{{ $booking->client_name }}</td>
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
    });
</script>
@endpush
