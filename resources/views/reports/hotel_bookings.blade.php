@extends('layouts.app')

@section('content')
<div class="container">
    <h1>حجوزات {{ $hotel->name }}</h1>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>جهة الحجز</th>
                        <th>تاريخ الدخول</th>
                        <th>تاريخ الخروج</th>
                        <th>عدد الأيام</th>
                        <th>عدد الغرف</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr>
                        <td>{{ $booking->client_name }}</td>
                        <td>{{ $booking->company->name }}</td>
                        <td>{{ $booking->agent->name }}</td>
                        <td>{{ $booking->check_in->format('d/m/Y') }}</td>
                        <td>{{ $booking->check_out->format('d/m/Y') }}</td>
                        <td>{{ $booking->days }}</td>
                        <td>{{ $booking->rooms }}</td>
                        <td>{{ number_format($booking->cost_price) }}</td>
                        <td>{{ number_format($booking->cost_price * $booking->rooms * $booking->days) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection