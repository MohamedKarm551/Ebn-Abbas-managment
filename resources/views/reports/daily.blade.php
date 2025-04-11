@extends('layouts.app')

@section('content')
<div class="container">
    <h1>التقارير اليومية</h1>
    <ul>
        <li>عدد العملاء الذين دخلوا اليوم: {{ $checkInsToday }}</li>
        <li>الإيرادات المتوقعة: {{ $expectedRevenue }} جنيه</li>
        <li>التكاليف الإجمالية: {{ $totalCost }} جنيه</li>
        <li>صافي الأرباح: {{ $netProfit }} جنيه</li>
    </ul>
</div>
@endsection