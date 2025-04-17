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
                <td class="d-flex gap-1">
                    <a href="{{ route('reports.company.payment.edit', $payment->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('reports.company.payment.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الدفعة؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection