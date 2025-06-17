@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>تعديل بيانات الفندق: {{ $hotel->name }}</h1>

        <form action="{{ route('admin.updateHotel', $hotel->id) }}" method="POST" enctype="multipart/form-data">
            {{-- *** إضافة enctype *** --}}
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">اسم الفندق</label>
                    <input type="text" name="name" id="name"
                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $hotel->name) }}"
                        required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="location" class="form-label">الموقع (اختياري)</label>
                    <input type="text" name="location" id="location"
                        class="form-control @error('location') is-invalid @enderror"
                        value="{{ old('location', $hotel->location) }}">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="purchased_rooms_count" class="form-label">عدد الغرف المشتراة (الافتراضي 30)</label>
                    <input type="number" name="purchased_rooms_count" id="purchased_rooms_count"
                        class="form-control @error('purchased_rooms_count') is-invalid @enderror"
                        value="{{ old('purchased_rooms_count', $hotel->purchased_rooms_count ?? 30) }}" min="0" readonly>
                    @error('purchased_rooms_count')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">الوصف (اختياري)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                        rows="3">{{ old('description', $hotel->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">روابط صور الفندق (اختياري)</label>
                    <div id="image_urls_container">
                        @php $imageCounter = 1; @endphp {{-- ده المتغير اللي هيعد الصور --}}
                        @if (old('image_urls', $hotel->images->pluck('image_path')->all()))
                            @foreach (old('image_urls', $hotel->images->pluck('image_path')->all()) as $index => $imageUrl)
                                @if (!empty($imageUrl))
                                    {{-- لا تعرض حقلًا فارغًا إذا كان old يحتوي على قيمة فارغة --}}
                                    <div class="input-group mb-2 image-url-input-group">
                                        <span class="input-group-text"
                                            style="font-weight: bold; min-width: 40px; text-align: center;">{{ $imageCounter++ }}.</span>
                                        {{-- ده الكاونتر هنا --}}
                                        <input type="url" name="image_urls[]"
                                            class="form-control @error('image_urls.' . $index) is-invalid @enderror"
                                            placeholder="https://example.com/image.jpg" value="{{ $imageUrl }}">
                                        <button type="button" class="btn btn-danger remove-image-url-btn"
                                            @if (
                                                $loop->first &&
                                                    count(old('image_urls', $hotel->images->pluck('image_path')->all())) == 1 &&
                                                    empty(old('image_urls', $hotel->images->pluck('image_path')->all())[0])) style="display: none;" @elseif(count(old('image_urls', $hotel->images->pluck('image_path')->all())) <= 1 && empty($imageUrl) && $loop->first) style="display: none;" @endif>-</button>
                                    </div>
                                @endif
                            @endforeach
                            {{-- إذا كانت المصفوفة فارغة تمامًا أو تحتوي على عنصر فارغ واحد فقط، أضف حقلًا فارغًا واحدًا --}}
                            @if (empty(old('image_urls', $hotel->images->pluck('image_path')->all())) ||
                                    (count(old('image_urls', $hotel->images->pluck('image_path')->all())) == 1 &&
                                        empty(old('image_urls', $hotel->images->pluck('image_path')->all())[0])))
                                <div class="input-group mb-2 image-url-input-group">
                                    <span class="input-group-text"
                                        style="font-weight: bold; min-width: 40px; text-align: center;">{{ $imageCounter++ }}.</span>
                                    {{-- ده الكاونتر هنا --}}
                                    <input type="url" name="image_urls[]"
                                        class="form-control @error('image_urls.*') is-invalid @enderror"
                                        placeholder="https://example.com/image.jpg">
                                    <button type="button" class="btn btn-danger remove-image-url-btn"
                                        style="display: none;">-</button>
                                </div>
                            @endif
                        @else
                            {{-- إذا لم يكن هناك old ولا صور محفوظة، أضف حقلًا فارغًا واحدًا --}}
                            <div class="input-group mb-2 image-url-input-group">
                                <span class="input-group-text"
                                    style="font-weight: bold; min-width: 40px; text-align: center;">{{ $imageCounter++ }}.</span>
                                {{-- ده الكاونتر هنا --}}
                                <input type="url" name="image_urls[]"
                                    class="form-control @error('image_urls.*') is-invalid @enderror"
                                    placeholder="https://example.com/image.jpg">
                                <button type="button" class="btn btn-danger remove-image-url-btn"
                                    style="display: none;">-</button>
                            </div>
                        @endif
                    </div> <button type="button" id="add_image_url_btn" class="btn btn-success btn-sm mt-2">+ إضافة رابط
                        صورة أخرى</button>
                    <small class="form-text text-muted d-block mt-1">أدخل روابط URL كاملة للصور. سيتم استبدال جميع الصور
                        الحالية بالروابط الجديدة. اترك جميع الحقول فارغة لحذف كل الصور.</small>
                    @error('image_urls.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    {{-- عرض الصور الحالية (يمكنك إبقاؤه أو إزالته حسب الرغبة) --}}
                    @if ($hotel->images->count() > 0 && !old('image_urls'))
                        {{-- لا تعرض إذا كان هناك old data --}}
                        <div class="mt-3">
                            <p>الصور الحالية (للمعاينة فقط):</p>
                            <div class="row">
                                @foreach ($hotel->images as $index => $image)
                                    <div class="col-md-3 col-sm-4 col-6 mb-2" style="position: relative;">
                                        {{-- هنا بنضيف الرقم فوق الصورة --}}
                                        <span
                                            style="position: absolute; top: 0; left: 0; background-color: rgba(0,0,0,0.7); color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.9em; z-index: 1;">{{ $index + 1 }}.</span>
                                        <img src="{{ $image->image_path }}" alt="صورة فندق" class="img-thumbnail"
                                            style="height: 100px; width: 100%; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تعريف العناصر الأساسية
    const container = document.getElementById('image_urls_container');      // العنصر الذي يحتوي على حقول الروابط
    const addButton = document.getElementById('add_image_url_btn');         // زر إضافة رابط جديد

    // دالة: تحديث ترقيم الحقول وإظهار أرقامها بشكل تسلسلي
    function updateImageCounters() {
        const inputGroups = container.querySelectorAll('.image-url-input-group');
        inputGroups.forEach((group, index) => {
            const counterSpan = group.querySelector('.input-group-text');
            if (counterSpan) {
                counterSpan.textContent = `${index + 1}.`; // 1. 2. 3. إلخ
            }
        });
    }

    // دالة: إظهار أو إخفاء زر الحذف حسب عدد الحقول أو محتوى الحقل الوحيد
    function updateRemoveButtonVisibility() {
        const inputGroups = container.querySelectorAll('.image-url-input-group');
        inputGroups.forEach((group) => {
            const removeButton = group.querySelector('.remove-image-url-btn');
            const inputField = group.querySelector('input[name="image_urls[]"]');
            if (inputGroups.length > 1) {
                // لو فيه أكثر من حقل، زر الحذف يظهر للجميع
                removeButton.style.display = 'inline-block';
            } else {
                // لو فيه حقل واحد فقط، الزر يظهر فقط لو فيه قيمة بالحقل
                if (inputField && inputField.value.trim() !== '') {
                    removeButton.style.display = 'inline-block';
                } else {
                    removeButton.style.display = 'none';
                }
            }
        });
        updateImageCounters();
    }

    // دالة: إنشاء مجموعة حقل جديدة (input + رقم + زر حذف)
    function createImageInputGroup() {
        const group = document.createElement('div');
        group.classList.add('input-group', 'mb-2', 'image-url-input-group');
        group.innerHTML = `
            <span class="input-group-text" style="font-weight: bold; min-width: 40px; text-align: center;"></span>
            <input type="url" name="image_urls[]" class="form-control" placeholder="https://example.com/image.jpg">
            <button type="button" class="btn btn-danger remove-image-url-btn">-</button>
        `;
        return group;
    }

    // حدث: عند الضغط على زر "إضافة"
    addButton.addEventListener('click', function() {
        const newGroup = createImageInputGroup();
        container.appendChild(newGroup);
        updateRemoveButtonVisibility();
    });

    // حدث: عند الضغط على زر "حذف" لأي حقل
    container.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-image-url-btn')) {
            const groupToRemove = e.target.closest('.image-url-input-group');
            const inputGroups = container.querySelectorAll('.image-url-input-group');
            if (inputGroups.length > 1) {
                // لو فيه أكثر من حقل، إحذف الحقل تمامًا
                groupToRemove.remove();
            } else {
                // لو هو آخر حقل، فقط امسح القيمة وخفي الزر
                const inputField = groupToRemove.querySelector('input[name="image_urls[]"]');
                if (inputField) inputField.value = '';
                e.target.style.display = 'none';
            }
            updateRemoveButtonVisibility();
        }
    });

    // حدث: عند تغيير قيمة أي حقل، راقب إذا لازم يظهر/يختفي زر الحذف
    container.addEventListener('input', function(e) {
        if (e.target && e.target.matches('input[name="image_urls[]"]')) {
            updateRemoveButtonVisibility();
        }
    });

    // عند تحميل الصفحة: تأكد أن كل شيء مضبوط (مهم لصفحة التعديل)
    updateRemoveButtonVisibility();
});
</script>
@endpush

