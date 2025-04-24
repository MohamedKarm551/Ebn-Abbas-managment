@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل اسم الشركة</h1>

    {{-- Form for Updating --}}
    <form action="{{ route('admin.updateCompany', $company->id) }}" method="POST" class="mb-3">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم الشركة</label>
            {{-- *** تعديل هنا: إضافة كلاس is-invalid *** --}}
            <input type="text"
                   name="name"
                   id="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $company->name) }}"
                   required>
            {{-- *** تعديل هنا: تغيير كلاس div الخطأ إلى invalid-feedback *** --}}
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
        <a href="{{ route('admin.companies') }}" class="btn btn-secondary">إلغاء</a>
    </form>

</div>
@endsection