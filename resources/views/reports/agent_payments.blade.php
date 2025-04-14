@extends('layouts.app')

@section('content')
<div class="container">
    <h1>سجل المدفوعات - {{ $agent->name }}</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>المبلغ</th>
                <th>تاريخ الدفع</th>
                <th>ملاحظات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ number_format($payment->amount, 2) }} ريال</td>
                <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                <td>{{ $payment->notes }}</td>
                <td>
                    <a href="{{ route('reports.payment.edit', $payment->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection