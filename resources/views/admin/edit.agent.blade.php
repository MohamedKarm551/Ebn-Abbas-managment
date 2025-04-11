@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل جهة الحجز</h1>
    <form action="{{ route('admin.updateAgent', $agent->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم جهة الحجز</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $agent->name }}" required>
        </div>
        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
        <a href="{{ route('admin.agents') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection