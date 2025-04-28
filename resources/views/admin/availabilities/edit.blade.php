{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\availabilities\edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل الإتاحة للفندق: {{ $availability->hotel->name }} ({{ $availability->start_date->format('d/m') }} - {{ $availability->end_date->format('d/m/Y') }})</h1>

    <form action="{{ route('admin.availabilities.update', $availability->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- تضمين الفورم الجزئي مع تمرير بيانات الإتاحة الحالية --}}
        @include('admin.availabilities._form', [
            'availability' => $availability, // تمرير الإتاحة الحالية
            'hotels' => $hotels,
            'agents' => $agents,
            'employees' => $employees,
            'roomTypes' => $roomTypes,
        ])

        <div class="mt-4">
            <button type="submit" class="btn btn-success">حفظ التعديلات</button>
            <a href="{{ route('admin.availabilities.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection