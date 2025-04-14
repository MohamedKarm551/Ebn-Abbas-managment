@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل الدفعة</h1>
    <form action="{{ route('reports.payment.update', $payment->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">المبلغ</label>
            <input type="number" step="0.01" class="form-control" name="amount" value="{{ $payment->amount }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">تاريخ الدفع</label>
            <input type="date" class="form-control" name="payment_date" value="{{ $payment->payment_date->format('Y-m-d') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">الملاحظات</label>
            <textarea class="form-control" name="notes">{{ $payment->notes }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </form>
</div>
@endsection