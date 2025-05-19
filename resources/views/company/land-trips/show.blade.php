@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-3">
            <a href="{{ route('company.land-trips.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i> عودة إلى قائمة الرحلات
            </a>
        </div>

        {{-- رسائل النجاح والخطأ --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            {{-- تفاصيل الرحلة --}}
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">تفاصيل الرحلة #{{ $landTrip->id }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>معلومات الرحلة</h5>
                                <ul class="list-unstyled">
                                    <li><strong>نوع الرحلة:</strong> {{ $landTrip->tripType->name }}</li>
                                    <li><strong>تاريخ المغادرة:</strong>
                                        {{ Carbon\Carbon::parse($landTrip->departure_date)->format('d/m/Y') }}</li>
                                    <li><strong>تاريخ العودة:</strong>
                                        {{ Carbon\Carbon::parse($landTrip->return_date)->format('d/m/Y') }}</li>
                                    <li><strong>عدد الأيام:</strong> {{ $landTrip->days_count }}</li>
                                    {{-- <li><strong>جهة الرحلة:</strong> {{ $landTrip->agent->name ?? 'غير محدد' }}</li> --}}
                                    <li><strong>الفندق:</strong> {{ $landTrip->hotel->name ?? 'غير محدد' }}</li>
                                    <li><strong>الموظف المسؤول:</strong> {{ $landTrip->employee->name ?? 'غير محدد' }}</li>
                                </ul>
                            </div>

                            <div class="col-md-6">
                                <h5>معلومات الأسعار</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>نوع الغرفة</th>
                                                <th>السعر</th>
                                                <th>متاح</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($roomInfo as $room)
                                                <tr>
                                                    <td>{{ $room['room_type'] }}</td>
                                                    <td>{{ number_format($room['price']) }}</td>
                                                    <td>
                                                        @if ($room['available'] === null)
                                                            غير محدود
                                                        @else
                                                            {{ $room['available'] }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if ($landTrip->notes)
                            <div class="alert alert-info">
                                <h5>ملاحظات:</h5>
                                <p>{{ $landTrip->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- حجوزاتك السابقة لهذه الرحلة --}}
                @if ($companyBookings->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">حجوزاتك السابقة لهذه الرحلة</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>اسم العميل</th>
                                            <th>نوع الغرفة</th>
                                            <th>عدد الغرف</th>
                                            <th>المبلغ</th>
                                            <th>تاريخ الحجز</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($companyBookings as $index => $booking)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $booking->client_name }}</td>
                                                <td>{{ $booking->roomPrice->roomType->room_type_name }}</td>
                                                <td>{{ $booking->rooms }}</td>
                                                <td>{{ number_format($booking->amount_due_from_company) }}</td>
                                                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('company.land-trips.voucher', $booking->id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        الفاتورة
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- نموذج الحجز --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">إضافة حجز جديد</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('company.land-trips.book', $landTrip->id) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="client_name" class="form-label">اسم العميل <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                    id="client_name" name="client_name" value="{{ old('client_name') }}" required>
                                @error('client_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="land_trip_room_price_id" class="form-label">نوع الغرفة <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('land_trip_room_price_id') is-invalid @enderror"
                                    id="land_trip_room_price_id" name="land_trip_room_price_id" required>
                                    <option value="">اختر نوع الغرفة</option>
                                    @foreach ($roomInfo as $room)
                                        <option value="{{ $room['id'] }}"
                                            {{ old('land_trip_room_price_id') == $room['id'] ? 'selected' : '' }}
                                            {{ $room['disabled'] ? 'disabled' : '' }}>
                                            {{ $room['room_type'] }} - {{ number_format($room['price']) }}
                                            @if ($room['available'] !== null)
                                                (متاح: {{ $room['available'] }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('land_trip_room_price_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="rooms" class="form-label">عدد الغرف <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('rooms') is-invalid @enderror"
                                    id="rooms" name="rooms_display" value="1" min="1" required disabled>
                                <input type="hidden" name="rooms" id="rooms_hidden" value="1">

                                @error('rooms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <input type="hidden" name="employee_id" value="{{ $landTrip->employee_id }}">

                            <div class="mb-3">
                                <label for="notes" class="form-label">ملاحظات</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">تأكيد الحجز</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/preventClick.js') }}"></script>
@endpush
