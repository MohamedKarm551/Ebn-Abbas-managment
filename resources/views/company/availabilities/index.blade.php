@extends('layouts.app')

@section('title', 'الإتاحات المتاحة للحجز')

@push('styles')
    <style>
        .availability-card {
            border: 1px solid #e0e0e0;
            transition: box-shadow 0.3s ease-in-out;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
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

        .room-type-item {
            border-bottom: 1px dashed #eee;
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .room-type-item:last-child {
            border-bottom: none;
        }

        .book-button-container {
            margin-top: 5px;
            margin-left: auto;
            padding-left: 10px;
        }

        .availability-image {
            max-height: 200px;
            width: 100%;
            object-fit: cover;
            border-radius: 0.25rem;
        }

        /* حاوية التقويم داخل البطاقة */
        .availability-body {
            overflow-x: auto;  /* منع الخروج خارج الـ div */
        }

        /* التقويم نفسه */
       .availability-calendar{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
    width: 100%;
    gap: 4px;
}

        /* تنسيق أيام التقويم */
        .calendar-day {
            padding: 4px 2px;
            text-align: center;
            border-radius: 4px;
            min-height: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px solid transparent;
        }

        .calendar-day.available {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .calendar-day.booked {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .calendar-day.partial {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }

        .calendar-legend {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            font-size: 0.8rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }

        .room-status-badge {
            font-size: 0.75rem;
            padding: 0.25em 0.5em;
        }

        .no-rooms-alert {
            font-size: 0.9rem;
        }


        .calendar-day .date-text {
    font-size: 0.8rem;
    font-weight: bold;
    margin-bottom: 4px;
}

.calendar-day .remaining-text {
    font-size: 0.7rem;
    padding: 2px 5px;
    border-radius: 12px;
}


.room-type-item{
    display: flex !important;
    flex-direction: column !important;
    width: 100%;
}

.room-details{
    width: 100%;
}

.book-button-container{
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-top: 25px;
    text-align: center;
}

.book-button-container .btn{
    min-width: 200px;
    padding: 12px 20px;
    font-size: 1rem;
    font-weight: bold;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.book-button-container .small{
    margin-top: 8px;
    font-size: 0.9rem;
}

.calendar-day.overbooked {
    background-color: #b71c1c !important;  /* أحمر غامق من Material Design */
    color: #fff !important;
    border-color: #8b0000 !important;
}
.calendar-day.overbooked .remaining-text {
    font-weight: bold;
    text-shadow: 0 0 2px rgba(0,0,0,0.5);
}


        @media (max-width: 575.98px) {
            .room-type-item .room-details {
                width: 100%;
                margin-bottom: 0.75rem;
            }

            .room-type-item .book-button-container {
                width: 100%;
                text-align: center;
            }

            .availability-calendar {
                font-size: 0.65rem;
                max-width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-calendar-check me-2"></i> الإتاحات المتاحة للحجز</h1>
        </div>

        {{-- Filter Form --}}
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
            
            <div class="col-md-3">
                <label for="filter_start_date" class="form-label">من تاريخ</label>
                <input type="date" name="filter_start_date" id="filter_start_date" class="form-control form-control-sm"
                    value="{{ request('filter_start_date') }}">
            </div>

            <div class="col-md-3">
                <label for="filter_end_date" class="form-label">إلى تاريخ</label>
                <input type="date" name="filter_end_date" id="filter_end_date" class="form-control form-control-sm"
                    value="{{ request('filter_end_date') }}">
            </div>

            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> تطبيق الفلتر</button>
                <a href="{{ route('company.availabilities.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times me-1"></i> مسح الفلتر</a>
                <a href="{{ route('company.availabilities.index', array_merge(request()->query(), ['sort_price' => 'asc'])) }}"
                    class="btn btn-outline-success btn-sm ms-2 {{ request('sort_price') == 'asc' ? 'active' : '' }}"
                    title="ترتيب حسب السعر الأقل">
                    <i class="fas fa-sort-amount-up-alt"></i> الأقل سعراً
                </a>
                <a href="{{ route('company.availabilities.index', array_merge(request()->query(), ['sort_price' => 'desc'])) }}"
                    class="btn btn-outline-danger btn-sm {{ request('sort_price') == 'desc' ? 'active' : '' }}"
                    title="ترتيب حسب السعر الأعلى">
                    <i class="fas fa-sort-amount-down-alt"></i> الأعلى سعراً
                </a>
            </div>
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif


        <div class="mb-4">
          <button class="btn btn-info btn-sm mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#hotelsOverview">
            <i class="fas fa-hotel me-1"></i> عرض ملخص الإتاحات النشطة حسب الفندق
          </button>
          <div class="collapse" id="hotelsOverview">
            <div class="card card-body bg-light">
              @foreach($hotelsWithActiveAvailabilities as $hotel)
                <div class="mb-3">
                  <h6 class="fw-bold text-primary">
                    <i class="fas fa-building me-1"></i>
                    {{ $hotel->name }}
                    <span class="badge bg-success ms-2">{{ $hotel->availabilities->count() }} إتاحة نشطة</span>
                  </h6>
                  <ul class="list-group list-group-flush ms-3">
                  @foreach($hotel->mergedAvailabilities as $key => $data)
                      @php $meta = $data['meta']; @endphp
                      <div class="ms-2 mt-2 border rounded p-2">
                        <span class="fw-bold">{{ $meta['room_type'] }}</span>
                        — السعر: <span class="text-success fw-bold">
                          {{ number_format($meta['price'], 2) }}
                          {{ $meta['currency'] == 'KWD' ? 'د.ك' : 'ر.س' }}
                        </span>
                        <div class="mt-1">
                         @foreach($data['ranges'] as $range)
                          <span class="badge bg-{{ $range['rooms'] > 0 ? 'success' : 'danger' }} me-1">
                            {{ \Carbon\Carbon::parse($range['from'])->format('d/m') }}
                            @if($range['from'] !== $range['to'])
                              ← {{ \Carbon\Carbon::parse($range['to'])->format('d/m') }}
                            @endif
                            : {{ $range['rooms'] }} غرفة
                          </span>
                        @endforeach
                        </div>
                      </div>
                    @endforeach
                  </ul>
                </div>
                <hr>
              @endforeach
            </div>
          </div>
        </div>
        
        @php $counter = ($availabilities->currentPage() - 1) * $availabilities->perPage() + 1; @endphp

        @forelse ($availabilities as $availability)
            <div class="availability-card shadow-sm mb-4 {{ $loop->odd ? 'bg-light-subtle' : 'bg-white' }}">
                {{-- Card Header --}}
                <div class="card-header availability-header p-3 border-bottom d-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
                    <div class="header-main-info d-flex align-items-center mb-2 mb-md-0">
                        <span class="badge bg-primary me-2 rounded-pill fs-6 lh-1 p-2">{{ $counter++ }}</span>
                        <h5 class="mb-0 fw-bold text-primary">{{ $availability->hotel->name }}</h5>
                    </div>
                    <div class="header-date-info text-md-end mt-2 mt-md-0">
                        <small class="text-danger">
                            <i class="fas fa-calendar-alt me-1"></i>
                            من: <span class="fw-medium">{{ $availability->start_date->format('d/m/Y') }}</span>
                            إلى: <span class="fw-medium">{{ $availability->end_date->format('d/m/Y') }}</span>
                            <span class="d-block d-sm-inline">(<span class="fw-medium">{{ $availability->start_date->diffInDays($availability->end_date, true) + 1 }}</span> أيام)</span>
                        </small>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="card-body availability-body p-3">
                    {{-- Hotel Images --}}
                    <div class="availability-image-container mb-3 text-center">
                        @if ($availability->hotel && $availability->hotel->images->count() > 0)
                            <div id="carouselHotelImages-{{ $availability->id }}" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @foreach ($availability->hotel->images as $index => $image)
                                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                            <img src="{{ $image->image_path }}" class="d-block w-100 rounded shadow-sm availability-image"
                                                alt="صورة {{ $index + 1 }} لفندق {{ $availability->hotel->name }}">
                                        </div>
                                    @endforeach
                                </div>
                                @if ($availability->hotel->images->count() > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselHotelImages-{{ $availability->id }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselHotelImages-{{ $availability->id }}" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <p class="text-muted">لا توجد صور متاحة لهذا الفندق.</p>
                        @endif
                    </div>

                    @if ($availability->notes)
                        <div class="alert alert-warning d-flex align-items-start small p-2 mb-3">
                            <i class="fas fa-info-circle me-2 fs-5 mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">ملاحظات:</strong>
                                <span>{{ $availability->notes }}</span>
                            </div>
                        </div>
                    @endif

                    @php
                        $availableRoomTypes = $availability->availabilityRoomTypes;
                    @endphp

                    @if ($availableRoomTypes->count() > 0)
                        <h6 class="mb-2 fw-bold"><i class="fas fa-door-open me-1"></i> أنواع الغرف والأسعار المتاحة:</h6>
                        <ul class="list-group list-group-flush">
                            @foreach ($availableRoomTypes as $roomType)
                                @php
                                    // *** التعديل الرئيسي: حساب التوفر الفعلي من dailyStatus ***
                                    $dailyStatuses = $roomType->dailyStatus ?? collect();
                                    
                                    // حساب عدد الأيام المتاحة (available_rooms > booked_rooms)
                                    $availableDays = $dailyStatuses->filter(function($status) {
                                        return ($status->available_rooms - $status->booked_rooms) > 0;
                                    });
                                    
                                    // عدد الأيام المتاحة
                                    $totalAvailableDays = $availableDays->count();
                                    
                                    // أول يوم متاح
                                    $firstAvailableDate = $availableDays->min('date');
                                    
                                    // آخر يوم متاح
                                    $lastAvailableDate = $availableDays->max('date');
                                    
                                    // هل كل الأيام محجوزة؟
                                    $isFullyBooked = $totalAvailableDays == 0;
                                    
                                    // الحد الأقصى للغرف المتاحة في أي يوم
                                    $maxAvailableRooms = $dailyStatuses->max(function($status) {
                                        return $status->available_rooms - $status->booked_rooms;
                                    }) ?? 0;
                                @endphp
                                
                                <li class="list-group-item px-0 py-3 room-type-item d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
                                    <div class="room-details mb-2 mb-sm-0">
                                        <div class="mb-1 d-flex align-items-center flex-wrap gap-2">
                                            <strong class="fs-6">
                                                {{ $roomType->roomType ? $roomType->roomType->room_type_name : 'نوع غرفة غير محدد' }}
                                            </strong>
                                            @if($isFullyBooked)
                                                <span class="badge bg-danger room-status-badge">محجوز بالكامل</span>
                                            @else
                                                <span class="badge bg-success room-status-badge">متاح</span>
                                                <small class="text-muted">({{ $totalAvailableDays }} يوم متاح)</small>
                                            @endif
                                        </div>
                                        
                                        <small class="text-muted d-block mb-2">
                                            <span class="me-3">السعر: <strong class="text-success">{{ number_format($roomType->sale_price, 2) }} {{ $roomType->currency == 'KWD' ? 'د.ك' : 'ر.س' }}</strong></span>
                                            @if(!$isFullyBooked)
                                                <span class="me-3">أول تاريخ متاح: <strong class="text-info">{{ $firstAvailableDate ? \Carbon\Carbon::parse($firstAvailableDate)->format('d/m/Y') : 'غير محدد' }}</strong></span>
                                                <span>الغرف المتاحة: <strong class="text-info">{{ $maxAvailableRooms }}</strong></span>
                                            @endif
                                        </small>
                                        
                                        {{-- Calendar Display - *** التعديل الرئيسي *** --}}
                                        @if($dailyStatuses->count() > 0)
                                            <div class="mt-2">
                                                <small class="text-muted d-block mb-1">تقويم التوفر اليومي:</small>
                                                <div class="availability-calendar">
                                                    @foreach ($dailyStatuses as $status)
                                                       @php
    $remaining = $status->available_rooms - $status->booked_rooms;
    $allotment = $roomType->allotment ?? 1;
    
    if ($remaining < 0) {
        $dayClass = 'overbooked';
        $dayTitle = $status->date->format('d/m/Y') . ' - جرد خطأ: محجوز أكثر من المتاح (' . $status->booked_rooms . '/' . $status->available_rooms . ')';
    } elseif ($remaining == 0) {
        $dayClass = 'booked';
        $dayTitle = $status->date->format('d/m/Y') . ' - محجوز بالكامل (' . $status->booked_rooms . '/' . $status->available_rooms . ')';
    } elseif ($remaining < $allotment) {
        $dayClass = 'partial';
        $dayTitle = $status->date->format('d/m/Y') . ' - متاح جزئياً (' . $remaining . ' من ' . $allotment . ' غرفة)';
    } else {
        $dayClass = 'available';
        $dayTitle = $status->date->format('d/m/Y') . ' - متاح بالكامل (' . $remaining . ' غرفة)';
    }
@endphp
<div class="calendar-day {{ $dayClass }}" title="{{ $dayTitle }}">
    <div class="date-text">{{ $status->date->format('y/m/d') }}</div>
    <div class="remaining-text">{{ $remaining < 0 ? $remaining : $remaining }}</div>
</div>
                                                    @endforeach
                                                </div>
                                                <div class="calendar-legend">
                                                    <div class="legend-item">
                                                        <div class="legend-color available"></div>
                                                        <span>متاح ({{ $dailyStatuses->filter(function($s) use ($roomType) { return ($s->available_rooms - $s->booked_rooms) >= ($roomType->allotment ?? 1); })->count() }} يوم)</span>
                                                    </div>
                                                    <div class="legend-item">
                                                        <div class="legend-color partial"></div>
                                                        <span>متاح جزئياً ({{ $dailyStatuses->filter(function($s) use ($roomType) { $rem = $s->available_rooms - $s->booked_rooms; return $rem > 0 && $rem < ($roomType->allotment ?? 1); })->count() }} يوم)</span>
                                                    </div>
                                                    <div class="legend-item">
                                                        <div class="legend-color booked"></div>
                                                        <span>محجوز ({{ $dailyStatuses->filter(function($s) { return ($s->available_rooms - $s->booked_rooms) <= 0; })->count() }} يوم)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- لو مفيش dailyStatus (إتاحة قديمة) --}}
                                            <div class="alert alert-warning small mt-2 py-1 px-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                لم يتم تحديث بيانات التوفر اليومي لهذه الإتاحة. المتاح: {{ $roomType->allotment ?? 'غير محدد' }} غرفة.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="book-button-container ms-sm-auto mt-2 mt-sm-0 text-center text-sm-end">
                                        @if(!$isFullyBooked)
                                            <a href="{{ route('bookings.create', ['availability_room_type_id' => $roomType->id]) }}"
                                                class="btn btn-success btn-sm shadow-sm">
                                                <i class="fas fa-calendar-check me-1"></i> احجز الآن
                                            </a>
                                            <div class="small text-muted mt-1">
                                                متاح من {{ $firstAvailableDate ? \Carbon\Carbon::parse($firstAvailableDate)->format('d/m') : '?' }}
                                                @if($lastAvailableDate && $firstAvailableDate != $lastAvailableDate)
                                                    إلى {{ \Carbon\Carbon::parse($lastAvailableDate)->format('d/m') }}
                                                @endif
                                            </div>
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-ban me-1"></i> غير متاح
                                            </button>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info d-flex align-items-start p-2 no-rooms-alert">
                            <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                            <div>
                                <p class="mb-1 fw-medium">لا توجد أنواع غرف متاحة حالياً لهذه الإتاحة.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i> لا توجد إتاحات متاحة حالياً تطابق معايير البحث.
            </div>
        @endforelse

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $availabilities->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

<script src="{{ asset('js/preventClick.js') }}"></script>