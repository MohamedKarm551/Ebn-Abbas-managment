@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إدارة الفنادق</h1>

    {{-- *** فورم إضافة فندق جديد *** --}}
    <form action="{{ route('admin.storeHotel') }}" method="POST" class="mb-4 p-3 border rounded">
        @csrf
        <h5>إضافة فندق جديد</h5>
        <div class="row g-3">

            {{-- ... (حقول الاسم والموقع والوصف زي ما هي) ... --}}
            <div class="col-md-6">
                <label for="name" class="form-label">اسم الفندق</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="location" class="form-label">الموقع (اختياري)</label>
                <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
    
            {{-- *** بداية التعديل: تغيير حقل الصورة إلى حقل نصي للينك *** --}}
            <div class="col-12">
                <label for="image_path" class="form-label">رابط صورة الفندق (اختياري)</label>
                <input type="text" class="form-control @error('image_path') is-invalid @enderror" id="image_path" name="image_path" placeholder="https://example.com/image.jpg" value="{{ old('image_path') }}">
                <small class="form-text text-muted">ضع رابط الصورة العام هنا.</small>
                @error('image_path')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
                <div class="col-12">
                <label for="description" class="form-label">الوصف (اختياري)</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            {{-- <div class="col-12">
                <label for="image" class="form-label">صورة الفندق (اختياري)</label>
                <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                 <small class="form-text text-muted">يفضل أن تكون الصورة مربعة أو أفقية.</small>
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div> --}}
            <div class="col-12">
                <button type="submit" class="btn btn-primary">إضافة الفندق</button>
            </div>
        </div>
    </form>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- *** جدول عرض الفنادق *** --}}
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>الصورة</th>
                <th>اسم الفندق</th>
                <th>الموقع</th>
                <th>الوصف</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($hotels as $hotel)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    @if($hotel->image_path)
                    {{-- جعل الصورة قابلة للضغط لفتح الـ Modal --}}
                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-url="{{ $hotel->image_path }}" data-hotel-name="{{ $hotel->name }}">
                        <img src="{{ $hotel->image_path }}" alt="{{ $hotel->name }}" height="50" width="50" style="object-fit: cover; cursor: pointer;" title="اضغط لعرض الصورة">
                    </a>
                @else
                    <span class="text-muted small">لا توجد صورة</span>
                @endif
                    </td>
                <td>{{ $hotel->name }}</td>
                <td>{{ $hotel->location ?? '-' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($hotel->description, 50) ?? '-' }}</td>
                <td>
                    <a href="{{ route('admin.editHotel', $hotel->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    {{-- زر الحذف معطل حالياً --}}
                    <button class="btn btn-danger btn-sm" disabled title="الحذف غير متاح حالياً">حذف</button>
                    {{-- <form action="{{ route('admin.deleteHotel', $hotel->id) }}" method="POST" style="display:inline;"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الفندق؟ سيتم حذف جميع الحجوزات المرتبطة به بشكل نهائي.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                    </form> --}}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">لا توجد فنادق مضافة حالياً.</td>
            </tr>
            @endforelse
        </tbody>
        {{-- *** بداية الإضافة: كود الـ Modal (يوضع مرة واحدة في نهاية الملف أو في layout) *** --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"> {{-- modal-lg لعرض أكبر --}}
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="imageModalLabel">صورة الفندق</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          {{-- الصورة ستوضع هنا بواسطة JavaScript --}}
          <img id="modalImage" src="" alt="صورة الفندق" class="img-fluid" style="max-height: 80vh;">
        </div>
      </div>
    </div>
  </div>
  
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
    var imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
      // الزر أو الرابط الذي ضغط عليه لفتح الـ modal
      var triggerElement = event.relatedTarget;
      // استخراج البيانات من الـ data-* attributes
      var imageUrl = triggerElement.getAttribute('data-image-url');
      var hotelName = triggerElement.getAttribute('data-hotel-name');

      // تحديث محتوى الـ modal
      var modalTitle = imageModal.querySelector('.modal-title');
      var modalImage = imageModal.querySelector('#modalImage');

      modalTitle.textContent = 'صورة فندق: ' + hotelName;
      modalImage.src = imageUrl;
      modalImage.alt = 'صورة فندق: ' + hotelName;
    });

    // اختياري: إفراغ الـ src عند إغلاق الـ modal لمنع تحميل صورة قديمة للحظة
    imageModal.addEventListener('hidden.bs.modal', function (event) {
        var modalImage = imageModal.querySelector('#modalImage');
        modalImage.src = '';
    });

</script>
@endpush {{-- أو نهاية <script> --}}


@endsection