@extends('layouts.app')
<title>@yield('title', 'الرحلات البرية المتاحة')</title>

@section('content')
    <div class="container">
        <h1 class="mb-4">الرحلات البرية المتاحة</h1>
        <a href="{{ route('company.land-trips.my-bookings') }}" class="btn btn-primary mb-3">
            <i class="fas fa-list-alt me-1"></i> حجوزاتي
        </a>
        {{-- نموذج البحث والفلترة --}}
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('company.land-trips.index') }}" method="GET" class="row g-3">
                    {{-- فلتر التاريخ --}}
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">من تاريخ</label>
                        <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                            value="{{ request('start_date') }}" placeholder="DD/MM/YYYY">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">إلى تاريخ</label>
                        <input type="text" class="form-control datepicker" id="end_date" name="end_date"
                            value="{{ request('end_date') }}" placeholder="DD/MM/YYYY">
                    </div>

                    {{-- فلتر نوع الرحلة --}}
                    <div class="col-md-4">
                        <label for="trip_type_id" class="form-label">نوع الرحلة</label>
                        <select class="form-select" id="trip_type_id" name="trip_type_id">
                            <option value="">الكل</option>
                            @foreach ($tripTypes as $tripType)
                                <option value="{{ $tripType->id }}"
                                    {{ request('trip_type_id') == $tripType->id ? 'selected' : '' }}>
                                    {{ $tripType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">بحث</button>
                        <a href="{{ route('company.land-trips.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- رسائل النجاح والخطأ --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- قائمة الرحلات --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse($landTrips as $trip)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">رحلة #{{ $trip->id }}</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><strong>نوع الرحلة:</strong> {{ $trip->tripType->name }}</li>

                                <li><strong>تاريخ المغادرة:</strong>
                                    {{ Carbon\Carbon::parse($trip->departure_date)->format('d/m/Y') }}</li>
                                <li><strong>تاريخ العودة:</strong>
                                    {{ Carbon\Carbon::parse($trip->return_date)->format('d/m/Y') }}</li>
                                <li><strong>عدد الأيام:</strong> {{ $trip->days_count }}</li>
                                {{-- <li><strong>جهة الرحلة:</strong> {{ $trip->agent->name ?? 'غير محدد' }}</li> --}}
                                <li><strong>الفندق:</strong> {{ $trip->hotel->name ?? 'غير محدد' }}</li>
                                <li><strong>الموظف المسؤول:</strong> {{ $trip->employee->name ?? 'غير محدد' }}</li>
                            </ul>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('company.land-trips.show', $trip->id) }}" class="btn btn-primary w-100">
                                <i class="fas fa-info-circle me-1"></i> عرض التفاصيل والحجز
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> لا توجد رحلات متاحة حاليًا تطابق معايير البحث.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- ترقيم الصفحات --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $landTrips->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/preventClick.js') }}"></script>

    <script>
        // تهيئة حقول التاريخ
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'ar'
            });
        });
        
    </script>
@endpush
