@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center text-white mb-4">تفاصيل حجوزات الفندق: {{ $hotel->name }}</h1>
    <a href="{{ route('bookings.index') }}" class="btn btn-secondary mb-3">رجوع</a>

    <table class="table table-dark table-hover table-bordered text-center">
        <thead class="thead-light">
            <tr>
                <th>اسم العميل</th>
                <th>جهة الحجز</th>
                <th>تاريخ الدخول</th>
                <th>تاريخ الخروج</th>
                <th>عدد الأيام</th>
                <th>عدد الغرف</th>
                <th>حالة الدفع</th>
                <th>الملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
            <tr>
                <td>{{ $booking->client_name }}</td>
                <td>{{ $booking->agent->name }}</td>
                <td>{{ $booking->check_in }}</td>
                <td>{{ $booking->check_out }}</td>
                <td class="copyable">{{ $booking->days }}</td>
                <td class="copyable">{{ $booking->rooms }}</td>
                <td>{{ $booking->payment_status }}</td>
                <td>{{ $booking->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
    body {
        background-color: #121212;
        color: #ffffff;
    }

    .table {
        border-color: #444;
    }

    .table th, .table td {
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: #333;
    }

    .copyable {
        cursor: pointer;
        color: #00bcd4;
    }

    .copyable:hover {
        text-decoration: underline;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyableElements = document.querySelectorAll('.copyable');
        copyableElements.forEach(element => {
            element.addEventListener('click', function () {
                navigator.clipboard.writeText(this.textContent).then(() => {
                    alert('تم نسخ الرقم: ' + this.textContent);
                });
            });
        });
    });
</script>
@endsection