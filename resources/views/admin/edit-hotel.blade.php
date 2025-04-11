@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل بيانات الفندق</h1>
    <form action="{{ route('admin.updateHotel', $hotel->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم الفندق</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $hotel->name }}" required>
        </div>
        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
        <a href="{{ route('admin.hotels') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection