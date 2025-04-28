{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\availabilities\create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة إتاحة جديدة')

{{-- @push('styles') --}}
{{-- Add page-specific styles here if needed --}}
{{-- @endpush --}}

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">إضافة إتاحة جديدة</h1>

    {{-- Display validation errors if any --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">حدث خطأ!</h5>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.availabilities.store') }}" method="POST" id="create-availability-form">
        @csrf

        {{-- Include the shared form partial --}}
        @include('admin.availabilities._form', [
            'hotels' => $hotels,
            'agents' => $agents,
            'employees' => $employees,
            'roomTypes' => $roomTypes, // Pass all possible RoomType models
            'availability' => null    // Pass null for 'availability' in create mode
        ])

        {{-- Form Actions --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> حفظ الإتاحة
            </button>
            <a href="{{ route('admin.availabilities.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> إلغاء
            </a>
        </div>
    </form>
</div>
@endsection

{{-- @push('scripts') --}}
{{-- Add page-specific scripts here if needed --}}
{{-- @endpush --}}