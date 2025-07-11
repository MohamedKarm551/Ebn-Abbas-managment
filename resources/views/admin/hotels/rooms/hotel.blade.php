@extends('layouts.app')

@section('title', 'غرف ' . $hotel->name)

<link rel="stylesheet" href="{{ url('css/roomDetails.css') }}?v={{ rand() }}">
@section('content')
    @php
        function getArabicRoomTypeName($type)
        {
            $namesMapping = [
                'single' => 'فردية',
                'double' => 'زوجية',
                'triple' => 'ثلاثية',
                'quad' => 'رباعية',
                'quint' => 'خماسية',
                // دعم القيم القديمة للتوافق
                'standard' => 'قياسية',
                'deluxe' => 'ديلوكس',
                'suite' => 'جناح',
                'family' => 'عائلية',
            ];
            return $namesMapping[$type] ?? $type;
        }

        function getRoomBedsCount($type)
        {
            $bedsMapping = [
                'single' => 1,
                'double' => 2,
                'triple' => 3,
                'quad' => 4,
                'quint' => 5,
                // دعم القيم القديمة للتوافق
                'standard' => 2,
                'deluxe' => 3,
                'suite' => 2,
                'family' => 4,
            ];
            return $bedsMapping[$type] ?? 2;
        }
    @endphp
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-hotel text-primary"></i>
                غرف فندق {{ $hotel->name }}
            </h1>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addRoomsModal">
                    <i class="fas fa-plus"></i> إضافة غرف جديدة
                </button>
                <a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right"></i> عودة لقائمة الفنادق
                </a>
            </div>
        </div>

        <!-- ملخص الإشغال -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4>ملخص الإشغال</h4>
                                <div class="progress mb-2" style="height: 20px">
                                    <div class="progress-bar bg-{{ $hotel->occupancy_rate > 80 ? 'danger' : ($hotel->occupancy_rate > 50 ? 'warning' : 'success') }}"
                                        role="progressbar" style="width: {{ $hotel->occupancy_rate }}%"
                                        aria-valuenow="{{ $hotel->occupancy_rate }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $hotel->occupancy_rate }}%
                                    </div>
                                </div>
                                <div class="text-muted">
                                    إجمالي عدد الغرف: {{ $hotel->total_rooms }} |
                                    مشغول: {{ $hotel->occupied_rooms }} |
                                    متاح: {{ $hotel->available_rooms }}
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary active" id="viewCards">
                                        <i class="fas fa-th"></i> عرض البطاقات
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="viewList">
                                        <i class="fas fa-list"></i> عرض القائمة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- إضافة قسم الحجوزات غير المخصصة هنا -->
        @if (isset($unassignedBookings) && $unassignedBookings->count() > 0)
            <div class="card mb-4 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        حجوزات تنتظر تخصيص غرف ({{ $unassignedBookings->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush border-top-0">
                        @foreach ($unassignedBookings as $booking)
                            @if ($booking->cost_price == 0 || $booking->sale_price == 0)
                                {{-- الحجوزات المؤرشفة - عرض مختلف --}}
                                <div class="list-group-item list-group-item-action bg-light opacity-75">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 text-decoration-line-through text-muted">
                                                {{ $booking->client_name }}</h6>
                                            <small class="text-danger d-block">(مؤرشف)</small>
                                            <div>
                                                <span
                                                    class="badge bg-primary me-1 opacity-50">{{ $booking->company->name ?? 'بدون شركة' }}</span>
                                                <span class="badge bg-info me-1 opacity-50">{{ $booking->rooms }}
                                                    غرفة</span>
                                                <span class="badge bg-secondary opacity-50">
                                                    {{ $booking->check_in->format('d/m') }} -
                                                    {{ $booking->check_out->format('d/m') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-muted small">مؤرشف</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- الحجوزات النشطة - العرض العادي --}}
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $booking->client_name }}</h6>
                                            <div>
                                                <span
                                                    class="badge bg-primary me-1">{{ $booking->company->name ?? 'بدون شركة' }}</span>
                                                <span class="badge bg-info me-1">{{ $booking->rooms }} غرفة</span>
                                                <span class="badge bg-secondary">
                                                    {{ $booking->check_in->format('d/m') }} -
                                                    {{ $booking->check_out->format('d/m') }}
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#assignRoomsModal" data-booking-id="{{ $booking->id }}"
                                            data-client-name="{{ $booking->client_name }}"
                                            data-company-name="{{ $booking->company->name ?? '' }}"
                                            data-rooms-count="{{ $booking->rooms }}"
                                            data-assigned-count="{{ $booking->roomAssignments->where('status', 'active')->count() }}">
                                            <i class="fas fa-door-open me-1"></i>
                                            تخصيص الغرف ({{ $booking->rooms }})
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- البحث والفلترة -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" id="searchRoom" class="form-control" placeholder="ابحث عن غرفة...">
                    </div>
                    <div class="col-md-3">
                        <select id="filterFloor" class="form-select">
                            <option value="">كل الطوابق</option>
                            @foreach ($roomsByFloor->keys() as $floor)
                                <option value="{{ $floor }}">{{ $floor ?: 'غير محدد' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterStatus" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="available">متاحة</option>
                            <option value="occupied">مشغولة</option>
                            <option value="maintenance">صيانة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="resetFilters" class="btn btn-secondary w-100">إعادة ضبط</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- عرض البطاقات -->

        <div id="roomCardsView">
            @foreach ($roomsByFloor as $floor => $rooms)
                <div class="card mb-4 shadow-sm floor-section" data-floor="{{ $floor }}">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-1"></i>
                            الطابق: {{ $floor ?: 'غير محدد' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row gx-2">
                            @foreach ($rooms as $room)
                                <div class="col-6 col-sm-4 col-md-3 col-xl-2 mb-4">
                                    <div class="card h-100 shadow-lg rounded-2xl hover-card room-card {{ $room->is_occupied ? 'border-danger' : 'border-success' }}"
                                        data-room-number="{{ $room->room_number }}" data-floor="{{ $room->floor }}"
                                        data-status="{{ $room->status }}">
                                        {{-- هيدر الكارد مخصّص لبطاقات الغرف --}}
                                        <div class="card-header room-card-header text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong class="fs-5">{{ $room->room_number }}</strong>
                                                <span class="fs-7">{{ getArabicRoomTypeName($room->type) }}</span>
                                            </div>
                                        </div>

                                        <div class="card-body room-card-body p-3 d-flex flex-column">
                                            {{-- الأيقونات—حجم ثابت لعرض الأسرة --}}
                                            <div class="bed-icons mb-2 text-center">
                                                @php
                                                    $bedsCount = getRoomBedsCount($room->type);
                                                    // جلب جميع النزلاء النشطين في الغرفة أولاً
                                                    $activeGuests = \App\Models\RoomAssignment::where(
                                                        'hotel_room_id',
                                                        $room->id,
                                                    )
                                                        ->where('status', 'active')
                                                        ->with(['booking.company'])
                                                        ->get();

                                                    // حساب عدد الأسرة المشغولة
                                                    $occupiedBeds = $room->activeAssignments->count();

                                                    $occupiedBeds = min($occupiedBeds, $bedsCount);
                                                    if ($occupiedBeds == 0 && $room->is_occupied) {
                                                        $occupiedBeds = 1;
                                                    }
                                                    // جلب أسماء النزلاء للـ tooltips
                                                    $guestNames = $activeGuests
                                                        ->pluck('booking.client_name')
                                                        ->toArray();
                                                @endphp

                                                @for ($i = 1; $i <= $bedsCount; $i++)
                                                    @if ($i <= $occupiedBeds)
                                                        @php
                                                            // تحديد اسم النزيل لهذا السرير (إذا كان متوفراً)
                                                            $guestIndex = $i - 1;
                                                            $guestName = isset($guestNames[$guestIndex])
                                                                ? $guestNames[$guestIndex]
                                                                : 'نزيل غير معروف';
                                                        @endphp
                                                        {{-- أيقونة سرير مشغول مع title فقط --}}
                                                        <i class="fas fa-bed text-danger mx-1"
                                                            title="مشغول: {{ $guestName }}"
                                                            style="cursor: pointer;"></i>
                                                    @else
                                                        {{-- أيقونة سرير متاح مع title فقط --}}
                                                        <i class="fas fa-bed text-success mx-1" title="سرير متاح"></i>
                                                    @endif
                                                @endfor


                                                <div class="small text-muted mt-1">
                                                    {{ $occupiedBeds }}/{{ $bedsCount }} مشغول
                                                </div>
                                            </div>

                                            @if ($room->is_occupied)
                                                <div class="fw-bold text-center mb-2">
                                                    @php
                                                        // جلب جميع النزلاء النشطين في الغرفة
                                                        $activeGuests = \App\Models\RoomAssignment::where(
                                                            'hotel_room_id',
                                                            $room->id,
                                                        )
                                                            ->where('status', 'active')
                                                            ->with(['booking.company'])
                                                            ->get();
                                                    @endphp

                                                    @if ($activeGuests->count() == 1)
                                                        {{ Str::limit($activeGuests->first()->booking->client_name, 15) }}
                                                    @else
                                                        نزلاء متعددين ({{ $activeGuests->count() }})
                                                    @endif
                                                </div>
                                                <div class="small text-center mb-2">
                                                    <span class="badge bg-info me-1">
                                                        {{ $activeGuests->count() }} ضيف

                                                    </span>
                                                    <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                                        @php
                                                            // جمع أسماء الشركات الفريدة
                                                            $companies = $activeGuests
                                                                ->pluck('booking.company.name')
                                                                ->filter()
                                                                ->unique()
                                                                ->values();
                                                        @endphp

                                                        @if ($companies->count() == 1)
                                                            {{ $companies->first() }}
                                                        @elseif($companies->count() > 1)
                                                            {{ $companies->count() }} شركات مختلفة
                                                            <div class="small">
                                                                {{ $companies->take(2)->join(', ') }}
                                                                @if ($companies->count() > 2)
                                                                    و {{ $companies->count() - 2 }} أخرى
                                                                @endif
                                                            </div>
                                                        @else
                                                            بدون شركة
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="mt-auto text-center">
                                                    @php
                                                        // أخذ أقرب تاريخ دخول وخروج
                                                        $earliestCheckIn = $activeGuests->min('check_in');
                                                        $latestCheckOut = $activeGuests->max('check_out');
                                                    @endphp

                                                    <span class="badge bg-secondary me-1">
                                                        <i class="fas fa-calendar-check"></i>
                                                        {{ \Carbon\Carbon::parse($earliestCheckIn)->format('d/m') }}
                                                    </span>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-calendar-times"></i>
                                                        {{ \Carbon\Carbon::parse($latestCheckOut)->format('d/m') }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="text-center py-3 mt-auto">
                                                    <span class="badge bg-success">متاحة</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- فوتر الكارد --}}
                                        <div
                                            class="card-footer room-card-footer bg-white d-flex justify-content-between align-items-center py-2">
                                            {{-- زرّ عرض التفاصيل --}}
                                            <a href="{{ route('hotel.rooms.show', $room->id) }}"
                                                class="btn btn-sm btn-info d-flex align-items-center fs-7">
                                                <i class="fas fa-eye me-1"></i> عرض
                                            </a>

                                            {{-- زرّ تعديل --}}
                                            <button type="button"
                                                class="btn btn-sm btn-warning d-flex align-items-center fs-7"
                                                data-bs-toggle="modal" data-bs-target="#editRoomModal"
                                                data-room-id="{{ $room->id }}"
                                                data-room-number="{{ $room->room_number }}"
                                                data-floor="{{ $room->floor }}" data-type="{{ $room->type }}"
                                                data-status="{{ $room->status }}" data-notes="{{ $room->notes ?? '' }}">
                                                <i class="fas fa-edit me-1"></i> تعديل
                                            </button>

                                            {{-- زرّ حذف --}}
                                            <button type="button"
                                                class="btn btn-sm btn-danger d-flex align-items-center fs-7"
                                                data-bs-toggle="modal" data-bs-target="#deleteRoomModal"
                                                data-room-id="{{ $room->id }}"
                                                data-room-number="{{ $room->room_number }}">
                                                <i class="fas fa-trash-alt me-1"></i> حذف
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


        <!-- عرض القائمة -->
        <div id="roomListView" style="display: none;">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>رقم الغرفة</th>
                                    <th>الطابق</th>
                                    <th>النوع</th>
                                    <th>الحالة</th>
                                    <th>النزيل الحالي</th>
                                    <th>تاريخ الدخول</th>
                                    <th>تاريخ الخروج</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hotel->rooms as $room)
                                    <tr class="room-row" data-room-number="{{ $room->room_number }}"
                                        data-floor="{{ $room->floor }}" data-status="{{ $room->status }}">
                                        <td>
                                            <span class="badge {{ $room->is_occupied ? 'bg-danger' : 'bg-success' }}">
                                                {{ $room->room_number }}
                                            </span>
                                        </td>
                                        <td>{{ $room->floor ?: 'غير محدد' }}</td>
                                        <td>{{ $room->type }}</td>
                                        <td>
                                            <span class="badge {{ $room->is_occupied ? 'bg-danger' : 'bg-success' }}">
                                                {{ $room->is_occupied ? 'مشغولة' : 'متاحة' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($room->current_guest)
                                                <a href="{{ route('bookings.show', $room->current_guest->id) }}">
                                                    {{ $room->current_guest->client_name }}
                                                </a>
                                                <div class="small text-muted">
                                                    {{ $room->current_guest->company->name ?? '' }}</div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($room->currentBooking)
                                                {{ $room->currentBooking->check_in->format('Y-m-d') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($room->currentBooking)
                                                {{ $room->currentBooking->check_out->format('Y-m-d') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="d-flex gap-1">
                                            <a href="{{ route('hotel.rooms.show', $room->id) }}"
                                                class="btn btn-sm btn-info d-flex align-items-center"
                                                style="font-size: 0.85rem;">
                                                <i class="fas fa-eye me-1"></i> تفاصيل
                                            </a>
                                            <button class="btn btn-sm btn-warning d-flex align-items-center"
                                                data-bs-toggle="modal" data-bs-target="#editRoomModal"
                                                data-room-id="{{ $room->id }}"
                                                data-room-number="{{ $room->room_number }}"
                                                data-floor="{{ $room->floor }}" data-type="{{ $room->type }}"
                                                data-status="{{ $room->status }}" data-notes="{{ $room->notes ?? '' }}"
                                                style="font-size: 0.85rem;">
                                                <i class="fas fa-edit me-1"></i> تعديل
                                            </button>
                                            <button class="btn btn-sm btn-danger d-flex align-items-center"
                                                data-bs-toggle="modal" data-bs-target="#deleteRoomModal"
                                                data-room-id="{{ $room->id }}"
                                                data-room-number="{{ $room->room_number }}" style="font-size: 0.85rem;">
                                                <i class="fas fa-trash-alt me-1"></i> حذف
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- موديل إضافة غرف -->
        <div class="modal fade" id="addRoomsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة غرف لفندق {{ $hotel->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('hotel.rooms.create') }}" method="POST" id="addRoomsForm">
                            @csrf
                            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_number" class="form-label">من رقم</label>
                                    <input type="number" class="form-control" id="start_number" name="start_number"
                                        min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_number" class="form-label">إلى رقم</label>
                                    <input type="number" class="form-control" id="end_number" name="end_number"
                                        min="1" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="floor" class="form-label">الطابق</label>
                                <input type="text" class="form-control" id="floor" name="floor">
                                <div class="form-text">يمكنك كتابة رقم الطابق أو اسم مميز له (مثال: الأرضي، VIP)</div>
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">نوع الغرفة</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="single">فردية (سرير واحد)</option>
                                    <option value="double">زوجية (سريرين)</option>
                                    <option value="triple">ثلاثية (3 أسرة)</option>
                                    <option value="quad">رباعية (4 أسرة)</option>
                                    <option value="quint">خماسية (5 أسرة)</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" form="addRoomsForm" class="btn btn-primary">إضافة الغرف</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- موديل تخصيص غرف متعددة -->
        <div class="modal fade" id="assignRoomsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تخصيص غرف للنزيل</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('hotel.rooms.assign-multiple') }}" method="POST" id="assignRoomsForm">
                            @csrf
                            <input type="hidden" name="booking_id" id="bookingId">

                            <div class="booking-info mb-4 p-3 bg-light rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-2">معلومات النزيل:</h6>
                                        <p class="mb-1"><strong>الاسم:</strong> <span id="clientName"></span></p>
                                        <p class="mb-1"><strong>الشركة:</strong> <span id="companyName"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-2">معلومات الحجز:</h6>
                                        <p class="mb-1">
                                            <strong>الغرف المطلوبة:</strong>
                                            <span id="requiredRooms" class="badge bg-primary"></span>
                                        </p>
                                        <p class="mb-1">
                                            <strong>الغرف المختارة:</strong>
                                            <span id="assignedRooms" class="badge bg-info">0</span> /
                                            <span id="totalRooms"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">اختر الغرف المناسبة</label>
                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    يمكنك اختيار عدد من الغرف يتوافق مع عدد الغرف المطلوبة في الحجز.
                                </div>

                                <div class="row" id="availableRoomsList">
                                    <!-- عرض الغرف المتاحة بالكامل -->
                                    @foreach ($hotel->rooms->where('status', 'available') as $room)
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <div class="card room-selection-card h-100">
                                                <div class="card-body p-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input room-checkbox" type="checkbox"
                                                            name="room_ids[]" value="{{ $room->id }}"
                                                            id="room{{ $room->id }}">
                                                        <label class="form-check-label w-100"
                                                            for="room{{ $room->id }}">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <span
                                                                    class="badge bg-success">{{ $room->room_number }}</span>
                                                                <span
                                                                    class="small">{{ getArabicRoomTypeName($room->type) }}</span>
                                                            </div>

                                                            <div class="bed-icons mb-2 text-center">
                                                                @php $bedsCount = getRoomBedsCount($room->type); @endphp
                                                                @for ($i = 0; $i < $bedsCount; $i++)
                                                                    <i class="fas fa-bed text-muted mx-1"></i>
                                                                @endfor
                                                            </div>

                                                            <div class="small text-muted text-center">
                                                                الطابق: {{ $room->floor ?: 'غير محدد' }}
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- عرض الغرف المشغولة جزئياً -->
                                    @foreach ($hotel->rooms->where('status', 'occupied') as $room)
                                        @php
                                            $bedsCount = getRoomBedsCount($room->type);
                                            $occupiedBeds = $room->activeAssignments->count();
                                            $remainingCapacity = $bedsCount - $occupiedBeds;
                                        @endphp

                                        @if ($remainingCapacity > 0)
                                            <div class="col-md-4 col-sm-6 mb-3">
                                                <div class="card room-selection-card h-100 border-warning">
                                                    <div class="card-header bg-warning bg-opacity-25 text-dark py-1">
                                                        <small><i class="fas fa-users me-1"></i> مشغولة جزئياً</small>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input room-checkbox" type="checkbox"
                                                                name="room_ids[]" value="{{ $room->id }}"
                                                                id="room{{ $room->id }}">
                                                            <label class="form-check-label w-100"
                                                                for="room{{ $room->id }}">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span
                                                                        class="badge bg-warning text-dark">{{ $room->room_number }}</span>
                                                                    <span
                                                                        class="small">{{ getArabicRoomTypeName($room->type) }}</span>
                                                                </div>

                                                                <div class="bed-icons mb-2 text-center">
                                                                    @for ($i = 1; $i <= $bedsCount; $i++)
                                                                        @if ($i <= $occupiedBeds)
                                                                            @php
                                                                                // جلب اسم النزيل للسرير المشغول
                                                                                $assignment = $room->activeAssignments
                                                                                    ->skip($i - 1)
                                                                                    ->first();
                                                                                $guestName = $assignment
                                                                                    ? $assignment->booking->client_name
                                                                                    : 'نزيل غير معروف';
                                                                            @endphp
                                                                            <i class="fas fa-bed text-danger mx-1 bed-occupied-tooltip"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="top"
                                                                                style="cursor: pointer;"
                                                                                data-bs-title="مشغول: {{ $guestName }}"
                                                                                title="سرير مشغول - {{ $guestName }}"></i>
                                                                        @else
                                                                            <i class="fas fa-bed text-success mx-1"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="top"
                                                                                title="سرير متاح"></i>
                                                                        @endif
                                                                    @endfor
                                                                    <div class="small mt-1 text-success">
                                                                        متاح {{ $remainingCapacity }} من
                                                                        {{ $bedsCount }} سرير
                                                                    </div>
                                                                </div>

                                                                <div class="small mb-2">
                                                                    <strong>النزيل الحالي:</strong>
                                                                    {{ Str::limit($room->current_guest->client_name ?? '-', 15) }}
                                                                </div>

                                                                <div class="small text-muted text-center">
                                                                    الطابق: {{ $room->floor ?: 'غير محدد' }}
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="alert alert-warning mt-3 d-none" id="roomSelectionWarning"></div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">ملاحظات (اختياري)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" form="assignRoomsForm" class="btn btn-primary" id="confirmAssignBtn">
                            <i class="fas fa-check me-1"></i> تخصيص الغرف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}

        <script>
            document.addEventListener('DOMContentLoaded', function() {


                // ===== 3. تبديل طريقة العرض (بطاقات / قائمة) =====
                const viewCards = document.getElementById('viewCards');
                const viewList = document.getElementById('viewList');
                const roomCardsView = document.getElementById('roomCardsView');
                const roomListView = document.getElementById('roomListView');

                if (viewCards && viewList && roomCardsView && roomListView) {
                    viewCards.addEventListener('click', function() {
                        roomCardsView.style.display = 'block';
                        roomListView.style.display = 'none';
                        viewCards.classList.add('active');
                        viewList.classList.remove('active');
                    });
                    viewList.addEventListener('click', function() {
                        roomCardsView.style.display = 'none';
                        roomListView.style.display = 'block';
                        viewCards.classList.remove('active');
                        viewList.classList.add('active');
                    });
                }

                // ===== 4. البحث والفلترة =====
                const searchRoom = document.getElementById('searchRoom');
                const filterFloor = document.getElementById('filterFloor');
                const filterStatus = document.getElementById('filterStatus');
                const resetFilters = document.getElementById('resetFilters');
                const roomCards = document.querySelectorAll('.room-card');
                const roomRows = document.querySelectorAll('.room-row');
                const floorSections = document.querySelectorAll('.floor-section');

                function applyFilters() {
                    const searchValue = searchRoom?.value.toLowerCase() || '';
                    const floorValue = filterFloor?.value;
                    const statusValue = filterStatus?.value;

                    // فلترة البطاقات
                    roomCards.forEach(card => {
                        const roomNumber = card.dataset.roomNumber.toLowerCase();
                        const roomFloor = card.dataset.floor;
                        const roomStatus = card.dataset.status;

                        const matchesSearch = roomNumber.includes(searchValue);
                        const matchesFloor = !floorValue || roomFloor === floorValue;
                        const matchesStatus = !statusValue || roomStatus === statusValue;

                        card.style.display = (matchesSearch && matchesFloor && matchesStatus) ? '' : 'none';
                    });

                    // فلترة صفوف القائمة
                    roomRows.forEach(row => {
                        const roomNumber = row.dataset.roomNumber.toLowerCase();
                        const roomFloor = row.dataset.floor;
                        const roomStatus = row.dataset.status;

                        const matchesSearch = roomNumber.includes(searchValue);
                        const matchesFloor = !floorValue || roomFloor === floorValue;
                        const matchesStatus = !statusValue || roomStatus === statusValue;

                        row.style.display = (matchesSearch && matchesFloor && matchesStatus) ? '' : 'none';
                    });

                    // عرض/إخفاء أقسام الطوابق
                    floorSections.forEach(section => {
                        const sectionFloor = section.dataset.floor;
                        if (!floorValue || sectionFloor === floorValue) {
                            const visibleCards = Array.from(
                                section.querySelectorAll('.room-card')
                            ).some(card => card.style.display !== 'none');
                            section.style.display = visibleCards ? '' : 'none';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                }

                // تطبيق الفلاتر عند التغيير
                if (searchRoom) searchRoom.addEventListener('input', applyFilters);
                if (filterFloor) filterFloor.addEventListener('change', applyFilters);
                if (filterStatus) filterStatus.addEventListener('change', applyFilters);

                // إعادة ضبط الفلاتر
                if (resetFilters) {
                    resetFilters.addEventListener('click', function() {
                        if (searchRoom) searchRoom.value = '';
                        if (filterFloor) filterFloor.value = '';
                        if (filterStatus) filterStatus.value = '';
                        applyFilters();
                    });
                }

                // ===== 5. التحقق من نطاق أرقام الغرف عند إضافة غرف جديدة =====
                const addRoomsForm = document.getElementById('addRoomsForm');
                const startNumber = document.getElementById('start_number');
                const endNumber = document.getElementById('end_number');

                if (addRoomsForm && startNumber && endNumber) {
                    addRoomsForm.addEventListener('submit', function(e) {
                        const start = parseInt(startNumber.value);
                        const end = parseInt(endNumber.value);

                        if (start > end) {
                            e.preventDefault();
                            alert('يجب أن يكون رقم البداية أقل من أو يساوي رقم النهاية');
                            return false;
                        }

                        if (end - start > 100) {
                            if (!confirm('هل أنت متأكد من إضافة أكثر من 100 غرفة مرة واحدة؟')) {
                                e.preventDefault();
                                return false;
                            }
                        }
                        return true;
                    });
                }

                // ===== 6. تخصيص غرف متعددة (المودال والعملية) =====
                const assignRoomsModal = document.getElementById('assignRoomsModal');
                const assignRoomsForm = document.getElementById('assignRoomsForm');
                const bookingIdInput = document.getElementById('bookingId');
                const clientNameSpan = document.getElementById('clientName');
                const companyNameSpan = document.getElementById('companyName');
                const requiredRoomsBadge = document.getElementById('requiredRooms');
                const assignedRoomsBadge = document.getElementById('assignedRooms');
                const totalRoomsSpan = document.getElementById('totalRooms');
                const roomSelectionWarning = document.getElementById('roomSelectionWarning');

                if (assignRoomsModal) {
                    assignRoomsModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const bookingId = button.getAttribute('data-booking-id') || '';
                        const clientName = button.getAttribute('data-client-name') || '';
                        const companyName = button.getAttribute('data-company-name') || '';
                        const requiredRoomsCount = parseInt(button.getAttribute('data-rooms-count') || '0');
                        const assignedRoomsCount = parseInt(button.getAttribute('data-assigned-count') || '0');
                        const remaining = requiredRoomsCount - assignedRoomsCount;

                        bookingIdInput.value = bookingId;
                        clientNameSpan.textContent = clientName || 'غير محدد';
                        companyNameSpan.textContent = companyName || 'غير محدد';
                        requiredRoomsBadge.textContent = requiredRoomsCount;
                        assignedRoomsBadge.textContent = '0';
                        totalRoomsSpan.textContent = remaining;

                        document.querySelectorAll('.room-checkbox').forEach(cb => cb.checked = false);
                        validateRoomSelection(remaining);
                    });

                    document.querySelectorAll('.room-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const checkedCount = document.querySelectorAll('.room-checkbox:checked')
                                .length;
                            assignedRoomsBadge.textContent = checkedCount;
                            const remaining = parseInt(totalRoomsSpan.textContent) || 0;
                            validateRoomSelection(remaining);
                        });
                    });

                    function validateRoomSelection(remaining) {
                        const checkedCount = document.querySelectorAll('.room-checkbox:checked').length;
                        const confirmBtn = document.getElementById('confirmAssignBtn');
                        const warning = document.getElementById('roomSelectionWarning');

                        if (checkedCount === 0) {
                            warning.textContent = 'يرجى اختيار غرفة واحدة على الأقل';
                            warning.classList.remove('d-none');
                            confirmBtn.disabled = true;
                        } else if (checkedCount > remaining) {
                            warning.textContent =
                                `لقد اخترت ${checkedCount} غرفة، بينما المتبقي للتخصيص ${remaining} فقط`;
                            warning.classList.remove('d-none');
                            confirmBtn.disabled = true;
                        } else {
                            warning.classList.add('d-none');
                            confirmBtn.disabled = false;
                        }
                    }
                }

                if (document.getElementById('confirmAssignBtn')) {
                    document.getElementById('confirmAssignBtn').addEventListener('click', function(e) {
                        e.preventDefault();
                        const checkedBoxes = document.querySelectorAll('.room-checkbox:checked');
                        if (checkedBoxes.length === 0) {
                            alert('يرجى اختيار على الأقل غرفة واحدة لتخصيصها.');
                            return;
                        }
                        document.getElementById('assignRoomsForm').submit();
                    });
                }

                // ===== 7. إعداد مودال تعديل الغرفة =====
                var editRoomModal = document.getElementById('editRoomModal');
                if (editRoomModal) {
                    editRoomModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget;
                        var roomId = button.getAttribute('data-room-id');
                        var roomNumber = button.getAttribute('data-room-number') || '';
                        var floor = button.getAttribute('data-floor') || '';
                        var type = button.getAttribute('data-type') || '';
                        var status = button.getAttribute('data-status') || '';
                        var notes = button.getAttribute('data-notes') || '';

                        var updateUrl = '{{ url('/hotels/rooms') }}/' + roomId;
                        document.getElementById('editRoomForm').setAttribute('action', updateUrl);

                        document.getElementById('edit_room_id').value = roomId;
                        document.getElementById('edit_room_number').value = roomNumber;
                        document.getElementById('edit_floor').value = floor;
                        document.getElementById('edit_type').value = type;
                        document.getElementById('edit_status').value = status;
                        document.getElementById('edit_notes').value = notes;
                    });
                }

                // ===== 8. إعداد مودال حذف الغرفة =====
                var deleteRoomModal = document.getElementById('deleteRoomModal');
                if (deleteRoomModal) {
                    deleteRoomModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget;
                        var roomId = button.getAttribute('data-room-id');
                        var roomNumber = button.getAttribute('data-room-number') || '';
                        var deleteUrl = '{{ url('/hotels/rooms') }}/' + roomId;

                        document.getElementById('deleteRoomForm').setAttribute('action', deleteUrl);
                        document.getElementById('delete_room_number').textContent = roomNumber;
                    });
                }

            });
        </script>
    @endpush

    {{-- ====================== --}}
    {{-- مودال تعديل الغرفة --}}
    {{-- ====================== --}}
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                {{-- النموذج سيرسل إلى route('hotel.rooms.update', {room_id}) --}}
                <form action="#" method="POST" id="editRoomForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">تعديل بيانات الغرفة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- حقل مخفي لحفظ room_id --}}
                        <input type="hidden" name="room_id" id="edit_room_id">

                        {{-- رقم الغرفة --}}
                        <div class="mb-3">
                            <label for="edit_room_number" class="form-label">رقم الغرفة</label>
                            <input type="text" class="form-control" id="edit_room_number" name="room_number"
                                maxlength="3" required>
                        </div>

                        {{-- الطابق --}}
                        <div class="mb-3">
                            <label for="edit_floor" class="form-label">الطابق</label>
                            <input type="text" class="form-control" id="edit_floor" name="floor" maxlength="10">
                        </div>

                        {{-- نوع الغرفة --}}
                        <div class="mb-3">
                            <label for="edit_type" class="form-label">نوع الغرفة</label>
                            <select class="form-select" id="edit_type" name="type" required>
                                <option value="single">فردية (1 سرير)</option>
                                <option value="double">زوجية (2 سرير)</option>
                                <option value="triple">ثلاثية (3 أسرة)</option>
                                <option value="quad">رباعية (4 أسرة)</option>
                                <option value="quint">خماسية (5 أسرة)</option>

                            </select>
                        </div>

                        {{-- الحالة --}}
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">الحالة</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="available">متاحة</option>
                                <option value="occupied">مشغولة</option>
                                <option value="maintenance">صيانة</option>
                            </select>
                        </div>

                        {{-- الملاحظات --}}
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            إلغاء
                        </button>
                        <button type="submit" class="btn btn-success">
                            حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ====================== --}}
    {{-- مودال تأكيد حذف الغرفة --}}
    {{-- ====================== --}}
    <div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                {{-- النموذج سيرسل إلى route('hotel.rooms.destroy', {room_id}) --}}
                <form action="#" method="POST" id="deleteRoomForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">تأكيد حذف الغرفة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>هل أنت متأكد من حذف الغرفة رقم <span id="delete_room_number"></span>؟</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            إلغاء
                        </button>
                        <button type="submit" class="btn btn-danger">
                            حذف نهائي
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
