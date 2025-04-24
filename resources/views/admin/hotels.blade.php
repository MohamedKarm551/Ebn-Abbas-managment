@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إدارة الفنادق</h1>
    <form action="{{ route('admin.storeHotel') }}" method="POST" class="mb-3">
        @csrf
        <div class="input-group">
            <input type="text" name="name" class="form-control" placeholder="اسم الفندق" required>
            <button type="submit" class="btn btn-primary">إضافة</button>
        </div>
    </form>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th> <!-- عمود الترقيم -->
                <th>اسم الفندق</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hotels as $hotel)
            <tr>
                <td>{{ $loop->iteration }}</td> <!-- عرض رقم الصف -->
                <td>{{ $hotel->name }}</td>
                <td>
                    <a href="{{ route('admin.editHotel', $hotel->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <small class="text-muted ms-2">(الحذف غير مسموح)</small>

                    {{-- <form action="{{ route('admin.deleteHotel', $hotel->id) }}" method="POST" style="display:inline;"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الفندق؟ سيتم حذف جميع الحجوزات المرتبطة به بشكل نهائي.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                    </form> --}}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@push('scripts') {{-- أو يمكنك وضعه مباشرة قبل @endsection --}}
<script>
    // منع النقر بزر الماوس الأيمن
    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    
    });

    // منع فتح أدوات المطور باستخدام F12 وبعض الاختصارات الأخرى
    document.addEventListener('keydown', function(event) {
        // F12
        if (event.keyCode === 123) {
            event.preventDefault();
        }
        // Ctrl+Shift+I (Chrome, Edge, Firefox)
        if (event.ctrlKey && event.shiftKey && event.keyCode === 73) {
            event.preventDefault();
        }
        // Ctrl+Shift+J (Chrome, Edge)
        if (event.ctrlKey && event.shiftKey && event.keyCode === 74) {
            event.preventDefault();
        }
        // Ctrl+U (View Source)
        if (event.ctrlKey && event.keyCode === 85) {
            event.preventDefault();
        }
    });
</script>
@endpush {{-- أو نهاية <script> --}}


@endsection