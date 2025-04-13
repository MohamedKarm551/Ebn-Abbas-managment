@extends('layouts.app')

@section('content')
<div class="container">
    <h1>حجوزات {{ $agent->name }}</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>الفندق</th>
                        <th>تاريخ الدخول</th>
                        <th>تاريخ الخروج</th>
                        <th>عدد الغرف</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr>
                        <td>{{ $booking->client_name }}</td>
                        <td>{{ $booking->company->name }}</td>
                        <td>{{ $booking->hotel->name }}</td>
                        <td>{{ $booking->check_in->format('d/m/Y') }}</td>
                        <td>{{ $booking->check_out->format('d/m/Y') }}</td>
                        <td>{{ $booking->rooms }}</td>
                        <td>{{ number_format($booking->amount_due_from_company) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection