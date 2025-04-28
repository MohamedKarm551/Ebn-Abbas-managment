@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل بيانات الفندق: {{ $hotel->name }}</h1>

    <form action="{{ route('admin.updateHotel', $hotel->id) }}" method="POST" enctype="multipart/form-data"> {{-- *** إضافة enctype *** --}}
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">اسم الفندق</label>
                <input type="text"
                       name="name"
                       id="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $hotel->name) }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="location" class="form-label">الموقع (اختياري)</label>
                <input type="text"
                       name="location"
                       id="location"
                       class="form-control @error('location') is-invalid @enderror"
                       value="{{ old('location', $hotel->location) }}">
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label for="description" class="form-label">الوصف (اختياري)</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $hotel->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label for="image_path" class="form-label">رابط صورة الفندق (اختياري)</label>
                <input type="text" class="form-control @error('image_path') is-invalid @enderror" id="image_path" name="image_path" placeholder="https://example.com/image.jpg" value="{{ old('image_path', $hotel->image_url) }}">
                <small class="form-text text-muted">ضع رابط الصورة العام هنا. اتركه فارغاً لحذف الرابط الحالي.</small>
                @error('image_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                {{-- عرض الصورة الحالية لو اللينك موجود --}}
                @if($hotel->image_path)
                    <div class="mt-2">
                        <p>الصورة الحالية (من الرابط):</p>
                        <img src="{{ $hotel->image_path }}" alt="{{ $hotel->name }}" height="100" style="object-fit: cover;">
                    </div>
                @endif
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">حفظ التعديلات</button>
                <a href="{{ route('admin.hotels') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </div>
    </form>
</div>
@endsection