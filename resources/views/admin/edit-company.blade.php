@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل اسم الشركة</h1>
    <form action="{{ route('admin.updateCompany', $company->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم الشركة</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $company->name }}" required>
        </div>
        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
        <a href="{{ route('admin.companies') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection