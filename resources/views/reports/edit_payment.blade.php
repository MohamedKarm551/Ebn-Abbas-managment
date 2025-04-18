@extends('layouts.app')

@section('content')
<div class="container">
<h1>تعديل دفعة جهة حجز : </h1>
    <form action="{{ route('reports.agent.payment.update', $payment->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount"
                   class="form-control" value="{{ old('amount', $payment->amount) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">تاريخ الدفع</label>
            <input type="date" name="payment_date"
                   class="form-control" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">الملاحظات</label>
            <textarea name="notes" class="form-control">{{ old('notes', $payment->notes) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </form>
</div>
@endsection