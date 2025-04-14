<!-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\reports\company_payments.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>سجل المدفوعات - {{ $company->name }}</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>المبلغ</th>
                <th>الملاحظات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
            <tr>
                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                <td>{{ $payment->amount }} ريال</td>
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