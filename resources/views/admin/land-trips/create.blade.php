@extends('layouts.app')

@section('title', 'إضافة رحلة برية جديدة')
@section('head_scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
@push('styles')
    <style>
        /* تنسيق عام */
        .form-section {
            position: relative;
            border-radius: 0.75rem;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: box-shadow 0.3s;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.075);
        }

        .form-section:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: linear-gradient(135deg, #0d6efd 0%, #2a8bf9 100%);
        }

        .section-header h5 {
            margin-bottom: 0;
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .section-body {
            padding: 1.5rem;
            background-color: white;
        }

        /* تنسيق نماذج الإدخال */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: #495057;
        }

        .form-control,
        .form-select {
            padding: 0.5rem 0.75rem;
            border-color: #dee2e6;
            border-radius: 0.375rem;
            box-shadow: none;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }

        /* أنواع الغرف */
        .room-type-row {
            position: relative;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .room-type-row:hover {
            background-color: #f1f3f5;
        }

        .room-type-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-remove-room {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            padding: 0;
        }

        /* أزرار تفاعلية */
        .btn-hover-scale {
            transition: transform 0.2s;
        }

        .btn-hover-scale:hover {
            transform: scale(1.05);
        }

        .btn-add-room {
            color: white;
            background-color: #20c997;
            border-color: #20c997;
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .btn-add-room:hover {
            background-color: #1ba87e;
            border-color: #1ba87e;
        }

        /* توافق الشاشات */
        @media (max-width: 767.98px) {
            .section-body {
                padding: 1rem;
            }

            .room-type-row {
                padding: 1rem 0.5rem;
            }

            .btn-mobile-full {
                width: 100%;
                margin-top: 0.5rem;
            }
        }

        /* توافق الشاشات الصغيرة جداً */
        @media (max-width: 575.98px) {
            .section-header {
                padding: 0.75rem 1rem;
            }

            .section-body {
                padding: 1rem 0.75rem;
            }

            .btn-group {
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        .manage-types-btn {
            position: fixed;
            left: 20px;
            bottom: 20px;
            z-index: 1000;
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
        }

        /* بريدكرمب مخصص */
        .custom-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            padding: 0.75rem 0;
            margin-bottom: 1rem;
            background-color: transparent;
        }

        .custom-breadcrumb-item {
            display: flex;
            align-items: center;
            color: #6c757d;
        }

        .custom-breadcrumb-item+.custom-breadcrumb-item::before {
            display: inline-block;
            padding: 0 0.5rem;
            color: #6c757d;
            content: "/";
        }

        .custom-breadcrumb-item.active {
            color: #343a40;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <!-- بريدكرمب -->
        <nav class="custom-breadcrumb mb-3">
            <div class="custom-breadcrumb-item"><a href="{{ route('admin.land-trips.index') }}"><i class="fas fa-bus me-1"></i>
                    الرحلات البرية</a></div>
            <div class="custom-breadcrumb-item active">إضافة رحلة جديدة</div>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <h1 class="mb-0 fs-2 fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i> إضافة رحلة برية جديدة</h1>
            <div>
                <a href="{{ route('admin.trip-types.index') }}" class="btn btn-outline-info">
                    <i class="fas fa-tags me-1"></i> إدارة أنواع الرحلات
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-danger border-start border-4 mb-4">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading fs-5">يوجد أخطاء في البيانات المدخلة</h5>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.land-trips.store') }}" method="POST" id="create-trip-form">
            @csrf

            <!-- بيانات الرحلة -->
            <div class="form-section">
                <div class="section-header">
                    <h5><i class="fas fa-info-circle me-2"></i> بيانات الرحلة</h5>
                </div>
                <div class="section-body">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label for="trip_type_id" class="form-label">نوع الرحلة <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select @error('trip_type_id') is-invalid @enderror" id="trip_type_id"
                                    name="trip_type_id" required>
                                    <option value="">-- اختر نوع الرحلة --</option>
                                    @foreach ($tripTypes as $tripType)
                                        <option value="{{ $tripType->id }}"
                                            {{ old('trip_type_id') == $tripType->id ? 'selected' : '' }}>
                                            {{ $tripType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('admin.trip-types.index') }}" class="btn btn-outline-secondary"
                                    title="إضافة نوع جديد" target="_blank"><i class="fas fa-plus"></i></a>
                            </div>
                            @error('trip_type_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <label for="agent_id" class="form-label">جهة الحجز <span class="text-danger">*</span></label>
                            <select class="form-select @error('agent_id') is-invalid @enderror" id="agent_id"
                                name="agent_id" required>
                                <option value="">-- اختر جهة الحجز --</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('agent_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- حقل الفندق الجديد -->
                        <div class="col-lg-4 col-md-6">
                            <label for="hotel_id" class="form-label">الفندق <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select @error('hotel_id') is-invalid @enderror" id="hotel_id"
                                    name="hotel_id" required>
                                    <option value="">-- اختر الفندق --</option>
                                    @foreach ($hotels as $hotel)
                                        <option value="{{ $hotel->id }}"
                                            {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- <a href="{{ route('hotels.create') }}" class="btn btn-outline-secondary"
                                    title="إضافة فندق جديد" target="_blank">
                                    <i class="fas fa-plus"></i>
                                </a> --}}
                            </div>
                            @error('hotel_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label for="employee_id" class="form-label">الموظف المسؤول <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id"
                                name="employee_id" required>
                                <option value="">-- اختر الموظف --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- تاريخ المغادرة -->
                        <div class="col-lg-4 col-md-6">
                            <label for="departure_date" class="form-label">تاريخ المغادرة <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="text"
                                    class="form-control datepicker @error('departure_date') is-invalid @enderror"
                                    id="departure_date" name="departure_date"
                                    value="{{ old('departure_date', isset($landTrip) && $landTrip->departure_date ? $landTrip->departure_date->format('Y-m-d') : '') }}"
                                    placeholder="YYYY-MM-DD" required>
                            </div>
                            @error('departure_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- تاريخ العودة -->
                        <div class="col-lg-4 col-md-6">
                            <label for="return_date" class="form-label">تاريخ العودة <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="text"
                                    class="form-control datepicker @error('return_date') is-invalid @enderror"
                                    id="return_date" name="return_date"
                                    value="{{ old('return_date', isset($landTrip) && $landTrip->return_date ? $landTrip->return_date->format('Y-m-d') : '') }}"
                                    placeholder="YYYY-MM-DD" required>
                            </div>
                            @error('return_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-lg-4 col-md-6">
                            <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                        نشطة</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشطة
                                    </option>
                                </select>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label"><i class="fas fa-sticky-note me-1"></i>
                                ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                placeholder="أي ملاحظات إضافية عن الرحلة">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- أسعار الغرف -->
            <div class="form-section">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-bed me-2"></i> أسعار الغرف</h5>
                    <button type="button" id="add-room-type" class="btn btn-add-room btn-hover-scale">
                        <i class="fas fa-plus-circle me-1"></i> إضافة نوع غرفة
                    </button>
                </div>
                <div class="section-body">
                    @if ($errors->has('room_types'))
                        <div class="alert alert-danger mb-3">{{ $errors->first('room_types') }}</div>
                    @endif

                    <div id="room-types-container">
                        @if (old('room_types'))
                            @foreach (old('room_types') as $index => $roomType)
                                <div class="room-type-row shadow-sm">
                                    <div class="row g-3">
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label">نوع الغرفة <span
                                                    class="text-danger">*</span></label>
                                            <select
                                                class="form-select @error('room_types.' . $index . '.room_type_id') is-invalid @enderror"
                                                name="room_types[{{ $index }}][room_type_id]" required>
                                                <option value="">-- اختر نوع الغرفة --</option>
                                                @foreach ($roomTypes as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ $roomType['room_type_id'] == $type->id ? 'selected' : '' }}>
                                                        {{ $type->room_type_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('room_types.' . $index . '.room_type_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">سعر التكلفة <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                                <input type="number" step="0.01"
                                                    class="form-control @error('room_types.' . $index . '.cost_price') is-invalid @enderror"
                                                    name="room_types[{{ $index }}][cost_price]"
                                                    value="{{ $roomType['cost_price'] }}" required>
                                            </div>
                                            @error('room_types.' . $index . '.cost_price')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">سعر البيع <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="fas fa-money-bill-wave"></i></span>
                                                <input type="number" step="0.01"
                                                    class="form-control @error('room_types.' . $index . '.sale_price') is-invalid @enderror"
                                                    name="room_types[{{ $index }}][sale_price]"
                                                    value="{{ $roomType['sale_price'] }}" required>
                                            </div>
                                            @error('room_types.' . $index . '.sale_price')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">العملة <span class="text-danger">*</span></label>
                                            <select
                                                class="form-select @error('room_types.' . $index . '.currency') is-invalid @enderror"
                                                name="room_types[{{ $index }}][currency]" required>

                                                <option value="KWD"
                                                    {{ isset($roomType['currency']) && $roomType['currency'] == 'KWD' ? 'selected' : '' }}>
                                                    دينار كويتي</option>
                                                <option value="SAR"
                                                    {{ isset($roomType['currency']) && $roomType['currency'] == 'SAR' ? 'selected' : '' }}>
                                                    ريال سعودي</option>
                                            </select>
                                            @error('room_types.' . $index . '.currency')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">عدد الغرف المتاحة</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                                                <input type="number"
                                                    class="form-control @error('room_types.' . $index . '.allotment') is-invalid @enderror"
                                                    name="room_types[{{ $index }}][allotment]"
                                                    value="{{ $roomType['allotment'] ?? '' }}" placeholder="غير محدود">
                                            </div>
                                            @error('room_types.' . $index . '.allotment')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div
                                            class="col-lg-1 col-md-6 d-flex align-items-end justify-content-center justify-content-lg-start">
                                            <button type="button" class="btn btn-danger btn-remove-room remove-room-type"
                                                title="حذف نوع الغرفة">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="room-type-row shadow-sm">
                                <div class="row g-3">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label">نوع الغرفة <span class="text-danger">*</span></label>
                                        <select class="form-select" name="room_types[0][room_type_id]" required>
                                            <option value="">-- اختر نوع الغرفة --</option>
                                            @foreach ($roomTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->room_type_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label">سعر التكلفة <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                            <input type="number" step="0.01" class="form-control"
                                                name="room_types[0][cost_price]" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label">سعر البيع <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                            <input type="number" step="0.01" class="form-control"
                                                name="room_types[0][sale_price]" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label">العملة <span class="text-danger">*</span></label>
                                        <select class="form-select" name="room_types[0][currency]" required>
                                            <option value="SAR">ريال سعودي</option>
                                            <option value="KWD" selected>دينار كويتي</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label">عدد الغرف المتاحة</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                                            <input type="number" class="form-control" name="room_types[0][allotment]"
                                                placeholder="غير محدود">
                                        </div>
                                    </div>

                                    <div
                                        class="col-lg-1 col-md-6 d-flex align-items-end justify-content-center justify-content-lg-start">
                                        <button type="button" class="btn btn-danger btn-remove-room remove-room-type"
                                            title="حذف نوع الغرفة">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="text-center mt-3 d-block d-lg-none">
                        <button type="button" id="add-room-type-mobile" class="btn btn-success w-100">
                            <i class="fas fa-plus-circle me-1"></i> إضافة نوع غرفة آخر
                        </button>
                    </div>
                </div>
            </div>

            <!-- أزرار الإرسال -->
            <div class="d-flex flex-column flex-md-row gap-2 justify-content-center justify-content-md-start mb-5">
                <button type="submit" class="btn btn-primary px-4 btn-lg btn-hover-scale">
                    <i class="fas fa-save me-1"></i> حفظ الرحلة
                </button>
                <a href="{{ route('admin.land-trips.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-1"></i> إلغاء
                </a>
            </div>
        </form>

        <!-- زر إدارة أنواع الرحلات (للموبايل) -->
        <a href="{{ route('admin.trip-types.index') }}" class="btn btn-info manage-types-btn d-md-none shadow">
            <i class="fas fa-tags me-1"></i> أنواع الرحلات
        </a>
    </div>
@endsection
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // تعريف متغير roomTypeIndex هنا في النطاق العام
        let roomTypeIndex = 1; // نبدأ من 1 لأن الغرفة الأولى لديها مؤشر 0 بالفعل
          // تهيئة زر إضافة أنواع الغرف
            $('#add-room-type, #add-room-type-mobile').on('click', function() {

                const template = `
<div class="room-type-row shadow-sm new-room" style="opacity: 0; transform: translateY(20px); transition: all 0.3s ease;">
    <input type="hidden" name="room_types[${roomTypeIndex}][id]" value="">
    
    <div class="row g-3">
        <div class="col-lg-3 col-md-6">
            <label class="form-label">نوع الغرفة <span class="text-danger">*</span></label>
            <select class="form-select" name="room_types[${roomTypeIndex}][room_type_id]" required>
                <option value="">-- اختر نوع الغرفة --</option>
                @foreach ($roomTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->room_type_name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="col-lg-2 col-md-6">
            <label class="form-label">سعر التكلفة <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                <input type="number" step="0.01" class="form-control" name="room_types[${roomTypeIndex}][cost_price]" required>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-6">
            <label class="form-label">سعر البيع <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                <input type="number" step="0.01" class="form-control" name="room_types[${roomTypeIndex}][sale_price]" required>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-6">
            <label class="form-label">العملة <span class="text-danger">*</span></label>
            <select class="form-select" name="room_types[${roomTypeIndex}][currency]" required>
                <option value="SAR">ريال سعودي</option>
                <option value="KWD" selected>دينار كويتي</option>
            </select>
        </div>
        
        <div class="col-lg-2 col-md-6">
            <label class="form-label">عدد الغرف المتاحة</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                <input type="number" class="form-control" name="room_types[${roomTypeIndex}][allotment]" placeholder="غير محدود">
            </div>
        </div>
        
        <div class="col-lg-1 col-md-6 d-flex align-items-end justify-content-center justify-content-lg-start">
            <button type="button" class="btn btn-danger btn-remove-room remove-room-type" title="حذف نوع الغرفة">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</div>
`;

                $('#room-types-container').append(template);

                // تطبيق تأثير دخول للعنصر الجديد
                const newRoom = $('#room-types-container .new-room').last();

                // تأخير لضمان بدء التأثير بعد إضافة العنصر للصفحة
                setTimeout(function() {
                    newRoom.css({
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });
                }, 10);

                // إزالة فئة new-room بعد اكتمال التأثير
                setTimeout(function() {
                    newRoom.removeClass('new-room');
                }, 300);

                roomTypeIndex++;

                // تمرير للأسفل لرؤية الغرفة المضافة
                $('html, body').animate({
                    scrollTop: newRoom.offset().top - 100
                }, 500);
            });

            // تهيئة حدث حذف نوع غرفة
            $(document).on('click', '.remove-room-type', function() {
                // التحقق من عدد صفوف الغرف
                if ($('.room-type-row').length > 1) {
                    $(this).closest('.room-type-row').slideUp(300, function() {
                        $(this).remove();
                    });
                } else {
                    Swal.fire({
                        title: 'تنبيه',
                        text: 'يجب أن تحتوي الرحلة على نوع غرفة واحد على الأقل',
                        icon: 'warning',
                        confirmButtonText: 'حسناً'
                    });
                }
            });
        // تعريف الدوال المساعدة في النطاق العام
        function showFormError(title, message) {
            Swal.fire({
                title: title,
                text: message,
                icon: 'error',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#3f51b5',
            });
        }

        function focusField(selector) {
            $(selector).addClass('is-invalid').focus();
            setTimeout(function() {
                $(selector).removeClass('is-invalid');
            }, 3000);
        }

        function validateRequiredFields() {
            let valid = true;
            $('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    valid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            return valid;
        }

        function showLoading() {
            $('body').append(
                '<div class="loading-overlay"><div class="spinner-border text-light" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>'
            );
            $('.loading-overlay').fadeIn(300);
        }

        function isValidDateFormat(dateStr) {
            if (!dateStr || dateStr.trim() === '') {
                return false;
            }

            if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateStr)) {
                console.log('تنسيق تاريخ غير صالح:', dateStr);
                return false;
            }

            try {
                const parts = dateStr.split('/');
                const day = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10);
                const year = parseInt(parts[2], 10);

                if (day < 1 || day > 31) {
                    console.log('يوم غير صالح:', day);
                    return false;
                }

                if (month < 1 || month > 12) {
                    console.log('شهر غير صالح:', month);
                    return false;
                }

                if (year < 2000 || year > 2100) {
                    console.log('سنة غير صالحة:', year);
                    return false;
                }

                const date = new Date(year, month - 1, day);
                if (
                    date.getFullYear() !== year ||
                    date.getMonth() !== month - 1 ||
                    date.getDate() !== day
                ) {
                    console.log('تاريخ غير موجود في التقويم');
                    return false;
                }

                return true;
            } catch (e) {
                console.error('خطأ في تحليل التاريخ:', e);
                return false;
            }
        }

        function parseDateString(dateStr) {
            try {
                const parts = dateStr.split('/');
                const day = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10) - 1;
                const year = parseInt(parts[2], 10);
                const date = new Date(year, month, day, 12, 0, 0);

                if (isNaN(date.getTime())) {
                    console.error('تاريخ غير صالح بعد التحويل');
                    return new Date();
                }

                return date;
            } catch (e) {
                console.error('خطأ في تحويل التاريخ:', e);
                return new Date();
            }
        }

        // تهيئة الصفحة عند اكتمال تحميل DOM
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة حقول التاريخ
            $('.datepicker').datepicker('destroy');

            // تحديد إعدادات التقويم بشكل صحيح
            if (typeof $.fn.datepicker !== 'undefined') {
                $.fn.datepicker.defaults.format = 'yyyy-mm-dd';
                $.fn.datepicker.defaults.autoclose = true;
                $.fn.datepicker.defaults.todayHighlight = true;
                $.fn.datepicker.defaults.language = 'ar';
                $.fn.datepicker.defaults.rtl = true;
                $.fn.datepicker.defaults.orientation = "auto";
            }

            if (typeof $.fn.datepicker !== 'undefined') {
                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd', // تحديد التنسيق المطلوب للإدخال
                    autoclose: true,
                    todayHighlight: true,
                    language: 'ar',
                    rtl: true,
                    orientation: "auto",
                    clearBtn: true
                }).on('changeDate', function(e) {
                    if (e.date) {
                        // تنسيق التاريخ بشكل صريح بالصيغة المطلوبة
                        const year = e.date.getFullYear();
                        const month = String(e.date.getMonth() + 1).padStart(2, '0');
                        const day = String(e.date.getDate()).padStart(2, '0');
                        const formattedDate = `${year}-${month}-${day}`;

                        // تعيين القيمة مباشرة بالتنسيق المطلوب
                        $(this).val(formattedDate);

                        console.log(`تم اختيار التاريخ: ${formattedDate}`);
                    }
                });
            }

            // التحقق من صحة النموذج قبل الإرسال
            $('form[id="create-trip-form"]').on('submit', function(e) {
                try {
                    // التأكد من تنسيق التاريخ قبل الإرسال
                    let departureDate = $('#departure_date').val();
                    let returnDate = $('#return_date').val();

                    // طباعة للتشخيص
                    console.log('تاريخ المغادرة قبل التحويل:', departureDate);
                    console.log('تاريخ العودة قبل التحويل:', returnDate);

                    // تحويل أي تاريخ بتنسيق dd/mm/yyyy أو mm/dd/yyyy إلى yyyy-mm-dd
                    if (departureDate && departureDate.includes('/')) {
                        const parts = departureDate.split('/');
                        if (parts.length === 3) {
                            if (parts[2].length === 4) { // إذا كان الجزء الثالث هو السنة
                                const day = parts[0].padStart(2, '0');
                                const month = parts[1].padStart(2, '0');
                                const year = parts[2];
                                departureDate = `${year}-${month}-${day}`;
                                $('#departure_date').val(departureDate);
                            }
                        }
                    }

                    if (returnDate && returnDate.includes('/')) {
                        const parts = returnDate.split('/');
                        if (parts.length === 3) {
                            if (parts[2].length === 4) {
                                const day = parts[0].padStart(2, '0');
                                const month = parts[1].padStart(2, '0');
                                const year = parts[2];
                                returnDate = `${year}-${month}-${day}`;
                                $('#return_date').val(returnDate);
                            }
                        }
                    }

                    // طباعة للتشخيص بعد التحويل
                    console.log('تاريخ المغادرة بعد التحويل:', $('#departure_date').val());
                    console.log('تاريخ العودة بعد التحويل:', $('#return_date').val());

                    // التحقق من صحة تنسيق التاريخ (yyyy-mm-dd)
                    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;

                    if (!dateRegex.test($('#departure_date').val()) || !dateRegex.test($('#return_date')
                            .val())) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'خطأ في تنسيق التاريخ',
                            html: 'يجب أن تكون التواريخ بصيغة <strong>YYYY-MM-DD</strong><br>(سنة-شهر-يوم)',
                            icon: 'error',
                            confirmButtonText: 'حسناً'
                        });
                        return false;
                    }

                    // التحقق من تسلسل التواريخ
                    if ($('#return_date').val() < $('#departure_date').val()) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'خطأ في التواريخ',
                            text: 'يجب أن يكون تاريخ العودة بعد أو يساوي تاريخ المغادرة',
                            icon: 'error',
                            confirmButtonText: 'حسناً'
                        });
                        return false;
                    }

                    console.log('تم قبول النموذج وسيتم إرساله');
                    return true;

                } catch (error) {
                    console.error('خطأ في التحقق من النموذج:', error);
                    e.preventDefault();
                    Swal.fire({
                        title: 'خطأ',
                        text: 'حدث خطأ أثناء التحقق من صحة النموذج',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                    return false;
                }
            });

            // إضافة تأثير بصري عند التركيز على حقول الإدخال
            $('input, select, textarea').on('focus', function() {
                $(this).closest('.col-lg-4, .col-md-6, .col-12').addClass('focused');
            }).on('blur', function() {
                $(this).closest('.col-lg-4, .col-md-6, .col-12').removeClass('focused');
            });

            // إضافة تأثير انتقالي عند التمرير
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.custom-card');
                elements.forEach(function(element) {
                    const position = element.getBoundingClientRect();
                    if (position.top < window.innerHeight) {
                        element.classList.add('animated');
                    }
                });
            };

            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // تشغيل عند تحميل الصفحة

          

        }); // نهاية حدث DOMContentLoaded
    </script>
@endpush
