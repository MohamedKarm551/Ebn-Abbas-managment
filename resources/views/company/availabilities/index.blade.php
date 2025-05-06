{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\company\availabilities\index.blade.php --}}
@extends('layouts.app')

@section('title', 'الإتاحات المتاحة للحجز')

@push('styles')
<style>

    /* *** نهاية الإضافة *** */
    .availability-card {
        border: 1px solid #dee2e6;
        margin-bottom: 1.5rem;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .availability-header {
        background-color: rgba(0, 0, 0, 0.03);
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .availability-body {
        padding: 1.25rem;
    }
        /* *** بداية إضافة ستايل للصورة *** */
        .availability-image-container {
        margin-bottom: 1rem; /* مسافة تحت الصورة */
        text-align: center; /* توسيط الصورة لو أصغر من الكونتينر */
    }
    .availability-image {
        max-height: 200px; /* تحديد أقصى ارتفاع للصورة */
        width: auto; /* السماح للعرض بالتغير للحفاظ على النسبة */
        max-width: 100%; /* ضمان عدم تجاوز عرض الكونتينر */
        border-radius: 0.25rem; /* حواف دائرية بسيطة */
        cursor: pointer; /* تغيير شكل المؤشر عند المرور فوق الصورة */
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* ظل خفيف */
    }

    .room-type-item {
        border-bottom: 1px dashed #eee;
        padding: 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; /* Allow wrapping on small screens */
    }
    .room-type-item:last-child {
        border-bottom: none;
    }
    .room-details span {
        margin-right: 15px; /* Spacing between details */
        font-size: 0.9em;
    }
    .book-button-container {
        margin-top: 5px; /* Space above button on wrap */
        margin-left: auto; /* Push button to the right */
        padding-left: 10px; /* Space before button */
    }

    /* Style for hotel filter */
    .filter-form .form-select, .filter-form .btn {
        min-width: 150px; /* Ensure dropdowns/buttons have decent width */
    }
    .availability-card.striped {
        background-color: #f8f9fa; /* لون رمادي فاتح جداً (ممكن تغيره) */
    }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-calendar-check me-2"></i> الإتاحات المتاحة للحجز</h1>
        {{-- Add filters if needed --}}
    </div>

    {{-- Optional Filter Form --}}
    <form method="GET" action="{{ route('company.availabilities.index') }}" class="row g-3 align-items-end bg-light p-3 rounded mb-4 shadow-sm filter-form">
        <div class="col-md-4">
            <label for="hotel_id" class="form-label">فلترة حسب الفندق</label>
            <select name="hotel_id" id="hotel_id" class="form-select form-select-sm">
                <option value="">كل الفنادق</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                @endforeach
            </select>
        </div>
            {{-- *** بداية إضافة فلتر التاريخ *** --}}
    {{-- تاريخ البدء --}}
    <div class="col-md-3"> {{-- تعديل حجم العمود --}}
        <label for="filter_start_date" class="form-label">من تاريخ</label>
        <input type="date" name="filter_start_date" id="filter_start_date" class="form-control form-control-sm" value="{{ request('filter_start_date') }}">
    </div>

    {{-- تاريخ الانتهاء --}}
    <div class="col-md-3"> {{-- تعديل حجم العمود --}}
        <label for="filter_end_date" class="form-label">إلى تاريخ</label>
        <input type="date" name="filter_end_date" id="filter_end_date" class="form-control form-control-sm" value="{{ request('filter_end_date') }}">
    </div>
    {{-- *** نهاية إضافة فلتر التاريخ *** --}}


        {{-- Add more filters here (e.g., date range) --}}
        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> تطبيق الفلتر</button>
            <a href="{{ route('company.availabilities.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times me-1"></i> مسح الفلتر</a>
            <a href="{{ route('company.availabilities.index', array_merge(request()->query(), ['sort_price' => 'asc'])) }}" class="btn btn-outline-success btn-sm ms-2 {{ request('sort_price') == 'asc' ? 'active' : '' }}" title="ترتيب حسب السعر الأقل">
                <i class="fas fa-sort-amount-up-alt"></i> الأقل سعراً
            </a>
            {{-- زرار الترتيب التنازلي (الأعلى سعراً) --}}
            <a href="{{ route('company.availabilities.index', array_merge(request()->query(), ['sort_price' => 'desc'])) }}" class="btn btn-outline-danger btn-sm {{ request('sort_price') == 'desc' ? 'active' : '' }}" title="ترتيب حسب السعر الأعلى">
                <i class="fas fa-sort-amount-down-alt"></i> الأعلى سعراً
            </a>
    
        </div>
    </form>


    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @php $counter = ($availabilities->currentPage() - 1) * $availabilities->perPage() + 1; @endphp

    @forelse ($availabilities as $availability)
    <div class="availability-card {{ $loop->odd ? 'striped' : '' }}">
        <div class="availability-header">
                {{-- <h5 class="mb-0 fw-bold">{{ $availability->hotel->name }}</h5> --}}
                <div class="header-content">
                    <p class="badge bg-primary me-2 fs-6">{{ $counter++ }}</p><span class="mb-0 fw-bold text-primary">{{ $availability->hotel->name }}</span>
                </div>
                {{-- *** نهاية إضافة عرض العداد *** --}}
                <span class="text-danger small">
                    <i class="fas fa-calendar-alt me-1"></i>
                    من: {{ $availability->start_date->format('d/m/Y') }} إلى: {{ $availability->end_date->format('d/m/Y') }}
                    ({{ $availability->end_date->diffInDays($availability->start_date) + 1 }} أيام)
                </span>
            </div>
            <div class="availability-body">
              

                {{-- *** بداية إضافة عرض الصورة *** --}}
                @if($availability->hotel && $availability->hotel->image_path)
                    <div class="availability-image-container">
                        {{-- رابط لفتح الـ Modal --}}
                        <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-url="{{ $availability->hotel->image_path }}" data-hotel-name="{{ $availability->hotel->name }}">
                            <img src="{{ $availability->hotel->image_path }}" alt="صورة فندق {{ $availability->hotel->name }}" class="img-fluid availability-image">
                        </a>
                    </div>
                @endif
                @if($availability->notes)
                <p class="text-warning fst-italic"><i class="fas fa-info-circle me-1"></i> ملاحظات: {{ $availability->notes }}</p>
            @endif
                @if($availability->availabilityRoomTypes->count() > 0)
                    <h6><i class="fas fa-door-open me-1"></i> أنواع الغرف والأسعار:</h6>
                    <ul class="list-unstyled mb-0">
                        @foreach ($availability->availabilityRoomTypes as $roomType)
                        {{-- *** إضافة الشرط هنا: اعرض فقط لو المتاح > 0 *** --}}
                        @if($roomType->allotment > 0)
                            <li class="room-type-item">
                                <div class="room-details">
                                    <span class="fw-bold">{{ $roomType->room_type_name }}</span>
                                    <span>| السعر : <strong class="text-success">{{ number_format($roomType->sale_price, 2) }}</strong> ر.س</span>
                                    {{-- *** عرض العدد المتاح *** --}}
                                    <span>| المتاح: <strong class="text-info">{{ $roomType->allotment }}</strong> غرف</span>
                                </div>
                                <div class="book-button-container">
                                    {{-- Button to create booking, passing necessary info --}}
                                    <a href="{{ route('bookings.create', ['availability_room_type_id' => $roomType->id]) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-calendar-check me-1"></i> احجز الآن
                                    </a>
                                </div>
                            </li>
                        @endif {{-- *** نهاية الشرط *** --}}
                    @endforeach
                    </ul>
                @else
                    <p class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> لا توجد تفاصيل غرف أو أسعار محددة لهذه الإتاحة.</p>
                    {{-- Optional: Add a generic book button if needed, but price/room type won't be prefilled --}}
                     <a href="{{ route('bookings.create', [
                        'hotel_id' => $availability->hotel_id,
                        'agent_id' => $availability->agent_id, // Pass agent if exists
                        'check_in' => $availability->start_date->format('Y-m-d'), // Pass in Y-m-d for input type=date
                        'check_out' => $availability->end_date->format('Y-m-d')   // Pass in Y-m-d for input type=date
                    ]) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-plus-circle me-1"></i> إنشاء حجز (عام)
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i> لا توجد إتاحات متاحة حالياً تطابق معايير البحث.
        </div>
    @endforelse

    {{-- Pagination Links --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $availabilities->appends(request()->query())->links() }}
    </div>
        {{-- *** بداية إضافة كود الـ Modal (تأكد من وجوده مرة واحدة في الصفحة أو في layout) *** --}}
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageModalLabel">صورة الفندق</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="modalImage" src="" alt="صورة الفندق" class="img-fluid" style="max-height: 80vh;">
                    </div>
                </div>
            </div>
        </div>
        {{-- *** نهاية إضافة كود الـ Modal *** --}}
    
    
</div>
@endsection

@push('scripts')
{{-- Add any specific JS for this page if needed --}}
<script>
    // Optional: Initialize Select2 if you use it for filters
    // $(document).ready(function() {
    //     $('#hotel_id').select2({ theme: 'bootstrap-5' });
    // });
    var imageModal = document.getElementById('imageModal');
    if (imageModal) {
        imageModal.addEventListener('show.bs.modal', function (event) {
            var triggerElement = event.relatedTarget;
            var imageUrl = triggerElement.getAttribute('data-image-url');
            var hotelName = triggerElement.getAttribute('data-hotel-name');

            var modalTitle = imageModal.querySelector('.modal-title');
            var modalImage = imageModal.querySelector('#modalImage');

            if (modalTitle) modalTitle.textContent = 'صورة فندق: ' + hotelName;
            if (modalImage) {
                modalImage.src = imageUrl;
                modalImage.alt = 'صورة فندق: ' + hotelName;
            }
        });

        imageModal.addEventListener('hidden.bs.modal', function (event) {
            var modalImage = imageModal.querySelector('#modalImage');
            if (modalImage) modalImage.src = '';
        });
    }

</script>
@endpush