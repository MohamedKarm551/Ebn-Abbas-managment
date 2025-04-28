{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\room_types\edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل نوع الغرفة: ' . $roomType->room_type_name)

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">تعديل نوع الغرفة: <span class="text-primary">{{ $roomType->room_type_name }}</span></h1>

    {{-- عرض رسائل الخطأ --}}
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

    {{-- فورم التعديل --}}
    <div class=" shadow-sm">
        <div class="card-header bg-warning">
            <i class="bi bi-pencil-square me-2"></i>تعديل بيانات النوع
        </div>
        <div class="card-body">
            {{-- تأكد إن الروت admin.room_types.update موجود وبيشاور على دالة update في RoomTypeController --}}
            <form action="{{ route('admin.room_types.update', $roomType->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- مهم جداً لتحديد إن ده طلب تحديث --}}

                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="room_type_name" class="form-label">اسم نوع الغرفة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('room_type_name') is-invalid @enderror" id="room_type_name" name="room_type_name" value="{{ old('room_type_name', $roomType->room_type_name) }}" required placeholder="مثال: غرفة ثنائية، جناح عائلي">
                        @error('room_type_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-lg"></i> حفظ التعديلات
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                     <a href="{{ route('admin.room_types.index') }}" class="btn btn-secondary">
                         <i class="bi bi-x-circle"></i> إلغاء
                     </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection