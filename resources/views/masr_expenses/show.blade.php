@extends('layouts.app')
@section('title', 'عرض مصاريف مكتب مصر')
@section('content')
<div class="container">
    <h3>{{ $masr_expense->title }}</h3>
    <div class="mb-2">
        <small class="text-muted">أنشأ بواسطة: {{ $masr_expense->creator->name ?? '-' }}</small>
    </div>
    <div class="mb-2">
        <strong>التاريخ:</strong> {{ $masr_expense->date }}
    </div>
    <div class="mb-2">
        <strong>ملاحظات:</strong> {{ $masr_expense->notes }}
    </div>
    <hr>
    <h5>بنود المصاريف</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>العنوان</th>
                <th>المبلغ</th>
                <th>العملة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($masr_expense->items as $item)
            <tr>
                <td>{{ $item->title }}</td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->currency }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{-- المجموع --}}
    <div class="mb-2">
        <strong>إجمالي المصاريف:</strong> {{ $masr_expense->items->sum('amount') }}
    </div>
    {{-- روابط الإجراءات --}}
    <a href="{{ route('admin.masr_expenses.index') }}" class="btn btn-primary">عودة إلى قائمة المصاريف</a>
</div>
@endsection