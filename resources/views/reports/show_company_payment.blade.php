<!-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\reports\show_company_payment.blade.php -->
@extends('layouts.app')
@section('content')
<div class="container">
  <h1>تفاصيل دفعة شركة #{{ $payment->id }} - {{ $payment->company->name }}</h1>
  <p>المبلغ: {{ number_format($payment->amount,2) }} ر.س</p>
  <p>التاريخ: {{ $payment->payment_date->format('Y-m-d') }}</p>
  <p>ملاحظات: {{ $payment->notes }}</p>
  @php
    $covered = is_array($payment->bookings_covered)
               ? $payment->bookings_covered
               : (json_decode($payment->bookings_covered, true) ?? []);
  @endphp
  <p>عدد الحجوزات المغطاة: {{ count($covered) }}</p>
  <ul>
    @foreach($covered as $bid)
      <li>حجز #{{ $bid }}</li>
    @endforeach
  </ul>
  <a href="{{ route('reports.company.payments', $payment->company_id) }}" class="btn btn-secondary">رجوع لسجل الدفعات</a>
</div>
@endsection