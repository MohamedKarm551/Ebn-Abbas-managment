{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\company\availabilities\index.blade.php --}}
@extends('layouts.app')

@section('title', 'الإتاحات المتاحة للحجز')

@push('styles')
    <style>
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
            margin-bottom: 1rem;
            /* مسافة تحت الصورة */
            text-align: center;
            /* توسيط الصورة لو أصغر من الكونتينر */
        }

        .availability-image {
            max-height: 200px;
            /* تحديد أقصى ارتفاع للصورة */
            width: auto;
            /* السماح للعرض بالتغير للحفاظ على النسبة */
            max-width: 100%;
            /* ضمان عدم تجاوز عرض الكونتينر */
            border-radius: 0.25rem;
            /* حواف دائرية بسيطة */
            cursor: pointer;
            /* تغيير شكل المؤشر عند المرور فوق الصورة */
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            /* ظل خفيف */
        }

        .room-type-item {
            border-bottom: 1px dashed #eee;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            /* Allow wrapping on small screens */
        }

        .room-type-item:last-child {
            border-bottom: none;
        }

        .room-details span {
            margin-right: 15px;
            /* Spacing between details */
            font-size: 0.9em;
        }

        .book-button-container {
            margin-top: 5px;
            /* Space above button on wrap */
            margin-left: auto;
            /* Push button to the right */
            padding-left: 10px;
            /* Space before button */
        }

        /* Style for hotel filter */
        .filter-form .form-select,
        .filter-form .btn {
            min-width: 150px;
            /* Ensure dropdowns/buttons have decent width */
        }

        .availability-card.striped {
            background-color: #f8f9fa;
            /* لون رمادي فاتح جداً (ممكن تغيره) */
        }

        /* Availability Card Enhancements */
        .availability-card {
            border: 1px solid #e0e0e0;
            /* Lighter border */
            transition: box-shadow 0.3s ease-in-out, transform 0.2s ease-out;
            border-radius: 0.5rem;
            /* Slightly more rounded corners */
        }

        .availability-card:hover {
            /* box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important; */
            /* transform: translateY(-3px); */
        }

        /* Use Bootstrap's bg-light-subtle or bg-white for striping via Blade conditional */
        /* .availability-card.striped { background-color: #f8f9fa; } */


        .availability-header .hotel-name {
            font-size: 1.15rem;
            /* Adjust hotel name size */
            color: #0056b3;
            /* Darker primary color */
        }

        .availability-header .date-range {
            font-size: 0.875rem;
        }

        .availability-header .badge {
            min-width: 28px;
            /* Ensure badge has some width for single digits */
            min-height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .availability-image-container .image-popup-link {
            display: inline-block;
            overflow: hidden;
            /* To contain the image scaling effect */
            border-radius: 0.25rem;
            /* Match image radius */
        }

        .availability-image {
            max-height: 200px;
            width: 100%;
            /* Make image take full width of its container */
            object-fit: cover;
            transition: transform 0.35s ease;
            border: 1px solid #eee;
        }

        .availability-image-container .image-popup-link:hover .availability-image {
            transform: scale(1.05);
        }

        .availability-notes strong {
            color: #664d03;
            /* Match Bootstrap warning text color */
        }

        .availability-notes {
            background-color: #fff3cdb3;
            border-color: #ffeeba;
        }


        .room-types-heading {
            color: #333;
            font-weight: 600;
        }

        .room-types-list .list-group-item.room-type-item {
            transition: background-color 0.2s ease-in-out;
            border-bottom: 1px dashed #e0e0e0 !important;
            /* Ensure consistent border */
        }

        .room-types-list .list-group-item.room-type-item:last-child {
            border-bottom: 0 !important;
        }

        .room-types-list .list-group-item.room-type-item:hover {
            background-color: #f8f9fa80;
            /* Subtle hover for room type item */
        }

        .room-type-item .room-details .room-type-name {
            font-weight: 600;
            color: #212529;
        }

        .room-type-item .room-details .price {
            font-weight: 700;
            /* Bolder price */
        }

        .room-type-item .room-details .allotment {
            font-weight: 600;
        }

        .room-pricing-info .price-info,
        .room-pricing-info .allotment-info {
            white-space: nowrap;
            /* Prevent wrapping of price/allotment text */
        }

        .book-now-btn {
            min-width: 110px;
            font-size: 0.875rem;
            padding: 0.375rem 0.85rem;
            transition: all 0.2s ease-in-out;
        }

        .book-now-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3) !important;
            /* Softer, color-matched shadow */
        }

        .generic-booking-btn {
            font-size: 0.875rem;
        }

        .no-rooms-alert,
        .availability-notes {
            font-size: 0.9rem;
        }


        /* Responsive adjustments */
        @media (max-width: 767.98px) {

            /* Medium screens and down */
            .availability-header .hotel-name {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 575.98px) {

            /* Extra small screens */
            .availability-header {
                text-align: center;
            }

            .availability-header .header-main-info,
            .availability-header .header-date-info {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .availability-header .header-date-info {
                margin-top: 0.5rem;
            }

            .room-type-item .room-details {
                width: 100%;
                text-align: center;
                margin-bottom: 0.75rem;
            }

            .room-type-item .book-button-container {
                width: 100%;
                text-align: center;
            }

            .room-pricing-info .price-info,
            .room-pricing-info .allotment-info {
                display: block;
                /* Stack price and allotment */
                margin-right: 0;
                margin-bottom: 0.25rem;
            }

            .room-pricing-info .allotment-info {
                margin-bottom: 0;
            }

            .availability-image {
                max-height: 160px;
            }
        }

        .carousel-inner {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .carousel-item img {
            max-height: 300px;
            object-fit: cover;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 10px;
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
        <form method="GET" action="{{ route('company.availabilities.index') }}"
            class="row g-3 align-items-end bg-light p-3 rounded mb-4 shadow-sm filter-form">
            <div class="col-md-4">
                <label for="hotel_id" class="form-label">فلترة حسب الفندق</label>
                <select name="hotel_id" id="hotel_id" class="form-select form-select-sm">
                    <option value="">كل الفنادق</option>
                    @foreach ($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>
                            {{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- *** بداية إضافة فلتر التاريخ *** --}}
            {{-- تاريخ البدء --}}
            <div class="col-md-3"> {{-- تعديل حجم العمود --}}
                <label for="filter_start_date" class="form-label">من تاريخ</label>
                <input type="date" name="filter_start_date" id="filter_start_date" class="form-control form-control-sm"
                    value="{{ request('filter_start_date') }}">
            </div>

            {{-- تاريخ الانتهاء --}}
            <div class="col-md-3"> {{-- تعديل حجم العمود --}}
                <label for="filter_end_date" class="form-label">إلى تاريخ</label>
                <input type="date" name="filter_end_date" id="filter_end_date" class="form-control form-control-sm"
                    value="{{ request('filter_end_date') }}">
            </div>
            {{-- *** نهاية إضافة فلتر التاريخ *** --}}


            {{-- Add more filters here (e.g., date range) --}}
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> تطبيق
                    الفلتر</button>
                <a href="{{ route('company.availabilities.index') }}" class="btn btn-secondary btn-sm"><i
                        class="fas fa-times me-1"></i> مسح الفلتر</a>
                <a href="{{ route('company.availabilities.index', array_merge(request()->query(), ['sort_price' => 'asc'])) }}"
                    class="btn btn-outline-success btn-sm ms-2 {{ request('sort_price') == 'asc' ? 'active' : '' }}"
                    title="ترتيب حسب السعر الأقل">
                    <i class="fas fa-sort-amount-up-alt"></i> الأقل سعراً
                </a>
                {{-- زرار الترتيب التنازلي (الأعلى سعراً) --}}
                <a href="{{ route('company.availabilities.index', array_merge(request()->query(), ['sort_price' => 'desc'])) }}"
                    class="btn btn-outline-danger btn-sm {{ request('sort_price') == 'desc' ? 'active' : '' }}"
                    title="ترتيب حسب السعر الأعلى">
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
            {{-- Card Start --}}
            <div class="availability-card  shadow-sm mb-4 {{ $loop->odd ? 'bg-light-subtle' : 'bg-white' }}">
                {{-- Card Header --}}
                <div
                    class="card-header availability-header p-3 border-bottom d-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
                    <div class="header-main-info d-flex align-items-center mb-2 mb-md-0">
                        <span class="badge bg-primary me-2 rounded-pill fs-6 lh-1 p-2">{{ $counter++ }}</span>
                        <h5 class="mb-0 fw-bold text-primary hotel-name">{{ $availability->hotel->name }}</h5>
                    </div>
                    <div class="header-date-info text-md-end mt-2 mt-md-0">
                        <small class="text-danger date-range">
                            <i class="fas fa-calendar-alt me-1"></i>
                            من: <span class="fw-medium">{{ $availability->start_date->format('d/m/Y') }}</span>
                            إلى: <span class="fw-medium">{{ $availability->end_date->format('d/m/Y') }}</span>
                            <span class="d-block d-sm-inline">(<span
                                    class="fw-medium">{{ $availability->end_date->diffInDays($availability->start_date) + 1 }}</span>
                                أيام)</span>
                        </small>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="card-body availability-body p-3">
                    <div class="availability-image-container mb-3 text-center">
                        @if ($availability->hotel && $availability->hotel->images->count() > 0)
                            <div id="carouselHotelImages-{{ $availability->id }}" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($availability->hotel->images as $index => $image)
                                        <button type="button" data-bs-target="#carouselHotelImages-{{ $availability->id }}"
                                            data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"
                                            aria-current="{{ $index == 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach ($availability->hotel->images as $index => $image)
                                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                            <img src="{{ $image->image_path }}" class="d-block w-100 rounded shadow-sm"
                                                alt="صورة {{ $index + 1 }} لفندق {{ $availability->hotel->name }}"
                                                style="max-height: 300px; object-fit: cover;">
                                        </div>
                                    @endforeach
                                </div>
                                @if ($availability->hotel->images->count() > 1)
                                    <button class="carousel-control-prev" type="button"
                                        data-bs-target="#carouselHotelImages-{{ $availability->id }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button"
                                        data-bs-target="#carouselHotelImages-{{ $availability->id }}" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <p class="text-muted">لا توجد صور متاحة لهذا الفندق.</p>
                        @endif
                    </div>

                    @if ($availability->notes)
                        <div class="alert alert-warning d-flex align-items-start small p-2 mb-3 availability-notes">
                            <i class="fas fa-info-circle me-2 fs-5 mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">ملاحظات:</strong>
                                <span>{{ $availability->notes }}</span>
                            </div>
                        </div>
                    @endif

                    @php
                        $availableRoomTypes = $availability->availabilityRoomTypes->filter(
                            fn($rt) => $rt->allotment > 0,
                        );
                    @endphp

                    @if ($availableRoomTypes->count() > 0)
                        <h6 class="mb-2 room-types-heading"><i class="fas fa-door-open me-1"></i> أنواع الغرف والأسعار
                            المتاحة:</h6>
                        <ul class="list-group list-group-flush room-types-list">
                            @foreach ($availableRoomTypes as $roomType)
                                <li
                                    class="list-group-item px-0 py-3 room-type-item d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                                    <div class="room-details mb-2 mb-sm-0 text-center text-sm-start">
                                        <div class="mb-1">
                                            <strong class="room-type-name fs-6">{{ $roomType->room_type_name }}</strong>
                                        </div>
                                        <small class="text-muted room-pricing-info">
                                            <span class="me-3 price-info">السعر: <strong
                                                    class="text-success price">{{ number_format($roomType->sale_price, 2) }}
                                                    ر.س</strong></span>
                                            <span class="allotment-info">المتاح: <strong
                                                    class="text-info allotment">{{ $roomType->allotment }}</strong>
                                                غرف</span>
                                        </small>
                                    </div>
                                    <div class="book-button-container ms-sm-auto mt-2 mt-sm-0 text-center text-sm-end">
                                        <a href="{{ route('bookings.create', ['availability_room_type_id' => $roomType->id]) }}"
                                            class="btn btn-success btn-sm book-now-btn shadow-sm">
                                            <i class="fas fa-calendar-check me-1"></i> احجز الآن
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info d-flex align-items-start p-2 no-rooms-alert">
                            <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                            <div>
                                <p class="mb-1 fw-medium">لا توجد أنواع غرف متاحة حالياً لهذه الإتاحة.</p>
                                {{-- عرض زر الحجز العام فقط للمستخدمين غير الشركات (مثلاً الأدمن والموظفين) --}}
                                @if (Auth::check() && Auth::user()->role !== 'Company')
                                    <small class="d-block mb-2">يمكنك إنشاء حجز عام إذا كنت متأكداً من التفاصيل.</small>
                                    <a href="{{ route('bookings.create', [
                                        'hotel_id' => $availability->hotel_id,
                                        'agent_id' => $availability->agent_id,
                                        'check_in' => $availability->start_date->format('Y-m-d'),
                                        'check_out' => $availability->end_date->format('Y-m-d'),
                                    ]) }}"
                                        class="btn btn-sm btn-outline-secondary generic-booking-btn">
                                        <i class="fas fa-plus-circle me-1"></i> إنشاء حجز (عام)
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            {{-- Card End --}}
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
                        <img id="modalImage" src="" alt="صورة الفندق" class="img-fluid"
                            style="max-height: 80vh;">
                    </div>
                </div>
            </div>
        </div>
        {{-- *** نهاية إضافة كود الـ Modal *** --}}


    </div>
@endsection

@push('scripts')
  
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const carousels = document.querySelectorAll('.carousel');
            
            carousels.forEach(carousel => {
               
            });
        });
    </script>
@endpush
