@extends('layouts.app')

@section('title', 'تعديل دفعة شركة')

@section('content')
<div class="container">
  <h1 class="mb-4">تعديل دفعة - {{ $payment->company->name }}</h1>
  <form action="{{ route('reports.company.payment.update', $payment->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
      <label class="form-label">المبلغ (ريال)</label>
      <input type="number" step="0.01" name="amount" class="form-control" 
             value="{{ old('amount', $payment->amount) }}" required>
      @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
      <label class="form-label">تاريخ الدفع</label>
      <input type="date" name="payment_date" class="form-control" 
             value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
      @error('payment_date')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
      <label class="form-label">الملاحظات</label>
      <textarea name="notes" class="form-control" rows="3">{{ old('notes', $payment->notes) }}</textarea>
      @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="d-flex justify-content-between">
      <a href="{{ route('reports.company.payments', $payment->company_id) }}" class="btn btn-secondary">
        إلغاء
      </a>
      <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
  </form>
</div>
@endsection