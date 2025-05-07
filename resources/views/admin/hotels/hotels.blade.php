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
                    <input type="text" name="name" id="name"
                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="location" class="form-label">الموقع (اختياري)</label>
                    <input type="text" name="location" id="location"
                        class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- *** بداية التعديل: تغيير حقل الصورة إلى حقل نصي للينك *** --}}
                {{-- *** بداية التعديل: حقل لإدخال روابط صور متعددة *** --}}
                <div class="col-12">
                    <label class="form-label">روابط صور الفندق (اختياري)</label>
                    <div id="image_urls_container">
                        <div class="input-group mb-2 image-url-input-group">
                            <input type="url" name="image_urls[]"
                                class="form-control @error('image_urls.*') is-invalid @enderror"
                                placeholder="https://example.com/image.jpg">
                            <button type="button" class="btn btn-danger remove-image-url-btn"
                                style="display: none;">-</button>
                        </div>
                        {{-- سيتم إضافة المزيد من حقول الإدخال هنا بواسطة JavaScript --}}
                    </div>
                    <button type="button" id="add_image_url_btn" class="btn btn-success btn-sm mt-2">+ إضافة رابط صورة
                        أخرى</button>
                    <small class="form-text text-muted d-block mt-1">أدخل روابط URL كاملة للصور.</small>
                    @error('image_urls.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div> {{-- تأكد من عرض الخطأ بشكل صحيح --}}
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">الوصف (اختياري)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                        rows="3">{{ old('description') }}</textarea>
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
                            @if ($hotel->first_image_url)
                                {{-- <--- استخدام Accessor الجديد --}}
                                {{-- جعل الصورة قابلة للضغط لفتح الـ Modal --}}
                                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal-{{ $hotel->id }}"
                                    data-hotel-name="{{ $hotel->name }}"> {{-- لا نحتاج data-image-url هنا لأن الـ modal سيعرض كل الصور --}}
                                    <img src="{{ $hotel->first_image_url }}" alt="{{ $hotel->name }}" height="50"
                                        width="50" style="object-fit: cover; cursor: pointer;" title="اضغط لعرض الصور">
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
            @foreach ($hotels as $hotel)
                <div class="modal fade" id="imageModal-{{ $hotel->id }}" tabindex="-1"
                    aria-labelledby="imageModalLabel-{{ $hotel->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="imageModalLabel-{{ $hotel->id }}">صور فندق:
                                    {{ $hotel->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                @if ($hotel->images->count() > 0)
                                    <div id="carouselHotelImages-{{ $hotel->id }}" class="carousel slide"
                                        data-bs-ride="carousel">
                                        <div class="carousel-indicators">
                                            @foreach ($hotel->images as $index => $image)
                                                <button type="button"
                                                    data-bs-target="#carouselHotelImages-{{ $hotel->id }}"
                                                    data-bs-slide-to="{{ $index }}"
                                                    class="{{ $index == 0 ? 'active' : '' }}"
                                                    aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                                    aria-label="Slide {{ $index + 1 }}"></button>
                                            @endforeach
                                        </div>
                                        <div class="carousel-inner">
                                            @foreach ($hotel->images as $index => $image)
                                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                    <img src="{{ $image->image_path }}" class="d-block w-100"
                                                        alt="صورة {{ $index + 1 }} لفندق {{ $hotel->name }}"
                                                        style="max-height: 70vh; object-fit: contain;">
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($hotel->images->count() > 1)
                                            <button class="carousel-control-prev" type="button"
                                                data-bs-target="#carouselHotelImages-{{ $hotel->id }}"
                                                data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button"
                                                data-bs-target="#carouselHotelImages-{{ $hotel->id }}"
                                                data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <p>لا توجد صور لعرضها لهذا الفندق.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach


        </table>
    </div>
    @push('scripts')
        {{-- أو يمكنك وضعه مباشرة قبل @endsection --}}


        <script src="{{ asset('js/preventClick.js') }}"></script>
        <script>
            var imageModal = document.getElementById('imageModal');
            imageModal.addEventListener('show.bs.modal', function(event) {
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
            imageModal.addEventListener('hidden.bs.modal', function(event) {
                var modalImage = imageModal.querySelector('#modalImage');
                modalImage.src = '';
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('image_urls_container');
                const addButton = document.getElementById('add_image_url_btn');
            
                function updateRemoveButtonVisibility() {
                    const inputGroups = container.querySelectorAll('.image-url-input-group');
                    inputGroups.forEach((group, index) => {
                        const removeButton = group.querySelector('.remove-image-url-btn');
                        if (inputGroups.length > 1) {
                            removeButton.style.display = 'inline-block';
                        } else {
                            // إذا كان هناك حقل واحد فقط، لا تظهر زر الحذف إلا إذا كان الحقل يحتوي على قيمة
                            const inputField = group.querySelector('input[name="image_urls[]"]');
                            if (inputField && inputField.value.trim() !== '') {
                                 removeButton.style.display = 'inline-block';
                            } else {
                                removeButton.style.display = 'none';
                            }
                        }
                    });
                }
            
                if (addButton && container) {
                    addButton.addEventListener('click', function () {
                        const newGroup = document.createElement('div');
                        newGroup.classList.add('input-group', 'mb-2', 'image-url-input-group');
                        newGroup.innerHTML = `
                            <input type="url" name="image_urls[]" class="form-control" placeholder="https://example.com/image.jpg">
                            <button type="button" class="btn btn-danger remove-image-url-btn">-</button>
                        `;
                        container.appendChild(newGroup);
                        updateRemoveButtonVisibility();
                    });
            
                    container.addEventListener('click', function (e) {
                        if (e.target && e.target.classList.contains('remove-image-url-btn')) {
                            const groupToRemove = e.target.closest('.image-url-input-group');
                            const inputGroups = container.querySelectorAll('.image-url-input-group');
            
                            if (inputGroups.length > 1) {
                                groupToRemove.remove();
                            } else if (inputGroups.length === 1) {
                                // إذا كان آخر حقل، قم فقط بمسح قيمته بدلاً من حذفه بالكامل
                                // واجعل زر الحذف غير مرئي
                                const inputField = groupToRemove.querySelector('input[name="image_urls[]"]');
                                if (inputField) {
                                    inputField.value = '';
                                }
                                e.target.style.display = 'none'; // إخفاء زر الحذف
                            }
                            updateRemoveButtonVisibility();
                        }
                    });
            
                    // تحديث رؤية أزرار الحذف عند تحميل الصفحة لأول مرة (خاصة لصفحة التعديل)
                    updateRemoveButtonVisibility();
            
                    // تحديث رؤية زر الحذف عند تغيير قيمة الحقل (للحالة التي يكون فيها حقل واحد)
                    container.addEventListener('input', function(e) {
                        if (e.target && e.target.matches('input[name="image_urls[]"]')) {
                            updateRemoveButtonVisibility();
                        }
                    });
                }
            });
            </script>
    @endpush {{-- أو نهاية <script> --}}
@endsection
