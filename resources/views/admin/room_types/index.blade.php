{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\room_types\index.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة أنواع الغرف')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">إدارة أنواع الغرف</h1>

    {{-- عرض رسائل النجاح أو الخطأ --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
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

    {{-- فورم إضافة نوع غرفة جديد --}}
    <div class=" mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-plus-circle-fill me-2"></i>إضافة نوع غرفة جديد
        </div>
        <div class="card-body">
            {{-- تأكد إن الروت admin.room_types.store موجود وبيشاور على دالة store في RoomTypeController --}}
            <form action="{{ route('admin.room_types.store') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="room_type_name" class="form-label">اسم نوع الغرفة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('room_type_name') is-invalid @enderror" id="room_type_name" name="room_type_name" value="{{ old('room_type_name') }}" required placeholder="مثال: غرفة ثنائية، جناح عائلي">
                        @error('room_type_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-lg"></i> إضافة النوع
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول عرض أنواع الغرف الموجودة --}}
    <div class=" shadow-sm">
        <div class="card-header">
           <i class="bi bi-list-ul me-2"></i> قائمة أنواع الغرف
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">اسم نوع الغرفة</th>
                            <th scope="col">تاريخ الإضافة</th>
                            <th scope="col">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- الـ Controller لازم يبعت متغير اسمه $roomTypes للـ view ده --}}
                        @forelse ($roomTypes ?? [] as $roomType)
                            <tr>
                                <td>{{ $loop->iteration + ($roomTypes->currentPage() - 1) * $roomTypes->perPage() }}</td> {{-- ترقيم صحيح مع pagination --}}
                                <td>{{ $roomType->room_type_name }}</td>
                                <td>{{ $roomType->created_at->translatedFormat('Y/m/d') }}</td> {{-- تنسيق أفضل للتاريخ --}}
                                <td>
                                    {{-- زر التعديل (يفترض وجود روت ودالة edit/update) --}}
                                    <a href="{{ route('admin.room_types.edit', $roomType->id) }}" class="btn btn-warning btn-sm" title="تعديل">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    {{-- زر الحذف (يفترض وجود روت ودالة destroy) --}}
                                    <form action="{{ route('admin.room_types.destroy', $roomType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا النوع؟ قد يؤثر هذا على الإتاحات أو الحجوزات المرتبطة به.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="bi bi-exclamation-circle fs-4 me-2"></i> لا توجد أنواع غرف مضافة حالياً. استخدم الفورم أعلاه للإضافة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         {{-- روابط الـ Pagination (لو فيه عدد كبير من الأنواع) --}}
         @if(isset($roomTypes) && $roomTypes instanceof \Illuminate\Pagination\LengthAwarePaginator && $roomTypes->hasPages())
            <div class="card-footer bg-light border-top">
                {{ $roomTypes->links() }}
            </div>
         @endif
    </div>

</div>
@endsection