@extends('layouts.app')

@section('title', 'تعديل الرحلة البرية')

@push('styles')
    <style>
        :root {
            --primary-color: #3f51b5;
            --secondary-color: #6c757d;
            --accent-color: #ffc107;
            --danger-color: #f44336;
            --success-color: #4caf50;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
        }

        /* تحسين العنوان الرئيسي */
        .page-title {
            position: relative;
            font-size: 1.7rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            color: var(--dark-color);
        }

        .page-title:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            bottom: 0;
            right: 0;
            border-radius: 2px;
        }

        /* تصميم البطاقات */
        .custom-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .custom-card-header {
            background: var(--primary-color);
            color: white;
            padding: 1.2rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }

        .custom-card-header .btn {
            border-radius: 50px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            transition: all 0.3s;
        }

        .custom-card-header .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .custom-card-body {
            padding: 1.8rem;
        }

        /* تحسين النماذج */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .form-control,
        .form-select {
            padding: 0.65rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.2);
            border-color: var(--primary-color);
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
        }

        /* تحسين حقول التاريخ */
        .date-input-group {
            position: relative;
        }

        .date-input-group .form-control {
            padding-left: 40px;
        }

        .date-input-group .calendar-icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: var(--secondary-color);
            pointer-events: none;
            z-index: 10;
        }

        /* تحسين أزرار الإجراءات */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .btn-custom-primary {
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            padding: 0.65rem 1.5rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
        }

        .btn-custom-primary:hover {
            background: #303f9f;
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .btn-custom-secondary {
            background: var(--secondary-color);
            color: white;
            border-radius: 8px;
            padding: 0.65rem 1.5rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
        }

        .btn-custom-secondary:hover {
            background: #5a6268;
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* تحسين صفوف أنواع الغرف */
        .room-type-row {
            background: #fcfcfc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .room-type-row:hover {
            background: #f5f5f5;
        }

        .remove-room-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .remove-room-btn:hover {
            transform: rotate(90deg);
            background: #e53935;
        }

        /* رسائل الخطأ */
        .error-alert {
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            background-color: #ffebee;
            border-left: 4px solid var(--danger-color);
        }

        /* تحسينات تقويم التاريخ */
        .datepicker-dropdown {
            font-family: 'Cairo', sans-serif;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 15px;
            border: none;
        }

        .datepicker table tr td.active {
            background-color: var(--primary-color) !important;
            border-radius: 50%;
        }

        .datepicker table tr td.day:hover {
            background-color: rgba(63, 81, 181, 0.1);
            border-radius: 50%;
        }

        .datepicker table tr td,
        .datepicker table tr th {
            width: 40px;
            height: 40px;
            text-align: center;
            border-radius: 50%;
        }

        .datepicker-rtl {
            direction: rtl;
        }

        /* التوافقية مع الشاشات المختلفة */
        @media (max-width: 992px) {
            .custom-card-body {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }

            .custom-card-body {
                padding: 1.2rem;
            }

            .custom-card-header {
                padding: 1rem;
            }

            .room-type-row {
                padding: 10px;
            }
        }

        @media (max-width: 576px) {
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .page-title {
                font-size: 1.3rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
@endpush
@section('head_scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
@section('content')
    <div class="container my-4">
        <h1 class="page-title">تعديل الرحلة البرية #{{ $landTrip->id }}</h1>

        @if ($errors->any())
            <div class="error-alert">
                <h5 class="mb-2 fw-bold">يوجد أخطاء في البيانات المدخلة:</h5>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.land-trips.update', $landTrip->id) }}" method="POST" id="edit-trip-form">
            @csrf
            @method('PUT')

            <!-- بيانات الرحلة -->
            <div class="custom-card">
                <div class="custom-card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 d-flex align-items-center">
                        <i class="fas fa-plane-departure me-2"></i> بيانات الرحلة
                    </h5>
                </div>
                <div class="custom-card-body">
                    <div class="row g-4">
                        <!-- نوع الرحلة -->
                        <div class="col-lg-4 col-md-6">
                            <label for="trip_type_id" class="form-label">نوع الرحلة <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('trip_type_id') is-invalid @enderror" id="trip_type_id"
                                name="trip_type_id" required>
                                <option value="">-- اختر نوع الرحلة --</option>
                                @foreach ($tripTypes as $tripType)
                                    <option value="{{ $tripType->id }}"
                                        {{ old('trip_type_id', $landTrip->trip_type_id) == $tripType->id ? 'selected' : '' }}>
                                        {{ $tripType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('trip_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- جهة الحجز -->
                        <div class="col-lg-4 col-md-6">
                            <label for="agent_id" class="form-label">جهة الحجز <span class="text-danger">*</span></label>
                            <select class="form-select @error('agent_id') is-invalid @enderror" id="agent_id"
                                name="agent_id" required>
                                <option value="">-- اختر جهة الحجز --</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ old('agent_id', $landTrip->agent_id) == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('agent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- حقل الفندق -->
                        <div class="col-lg-4 col-md-6">
                            <label for="hotel_id" class="form-label">الفندق <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select @error('hotel_id') is-invalid @enderror" id="hotel_id"
                                    name="hotel_id" required>
                                    <option value="">-- اختر الفندق --</option>
                                    @foreach ($hotels as $hotel)
                                        <option value="{{ $hotel->id }}"
                                            {{ old('hotel_id', $landTrip->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                            {{ $hotel->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- <button class="btn btn-outline-secondary" type="button" title="إضافة فندق جديد">
                                    <i class="fas fa-plus"></i>
                                </button> --}}
                            </div>
                            @error('hotel_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- الموظف المسؤول -->
                        <div class="col-lg-4 col-md-6">
                            <label for="employee_id" class="form-label">الموظف المسؤول <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id"
                                name="employee_id" required>
                                <option value="">-- اختر الموظف --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ old('employee_id', $landTrip->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
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

                        <!-- الحالة -->
                        <div class="col-lg-2 col-md-6">
                            <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status"
                                required>
                                <option value="active"
                                    {{ old('status', $landTrip->status) == 'active' ? 'selected' : '' }}>نشطة</option>
                                <option value="inactive"
                                    {{ old('status', $landTrip->status) == 'inactive' ? 'selected' : '' }}>غير نشطة
                                </option>
                                @if ($landTrip->status == 'expired')
                                    <option value="expired" selected>منتهية</option>
                                @endif
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ملاحظات -->
                        <div class="col-12">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $landTrip->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- أسعار الغرف -->
            <div class="custom-card">
                <div class="custom-card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 d-flex align-items-center">
                        <i class="fas fa-bed me-2"></i> أسعار الغرف
                    </h5>
                    <button type="button" id="add-room-type" class="btn btn-light btn-sm">
                        <i class="fas fa-plus-circle me-1"></i> إضافة نوع غرفة
                    </button>
                </div>
                <div class="custom-card-body">
                    @if ($errors->has('room_types'))
                        <div class="alert alert-danger">{{ $errors->first('room_types') }}</div>
                    @endif

                    <div id="room-types-container">
                        @if (old('room_types'))
                            {{-- إذا كان هناك بيانات من التحقق السابق --}}
                            @foreach (old('room_types') as $index => $roomType)
                                <div class="room-type-row">
                                    <input type="hidden" name="room_types[{{ $index }}][id]"
                                        value="{{ $roomType['id'] ?? '' }}">

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
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">سعر التكلفة <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01"
                                                    class="form-control @error('room_types.' . $index . '.cost_price') is-invalid @enderror"
                                                    name="room_types[{{ $index }}][cost_price]"
                                                    value="{{ $roomType['cost_price'] }}" required>
                                            </div>
                                            @error('room_types.' . $index . '.cost_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">سعر البيع <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01"
                                                    class="form-control @error('room_types.' . $index . '.sale_price') is-invalid @enderror"
                                                    name="room_types[{{ $index }}][sale_price]"
                                                    value="{{ $roomType['sale_price'] }}" required>
                                            </div>
                                            @error('room_types.' . $index . '.sale_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">العملة <span class="text-danger">*</span></label>
                                            <select
                                                class="form-select @error('room_types.' . $index . '.currency') is-invalid @enderror"
                                                name="room_types[{{ $index }}][currency]" required>
                                                <option value="SAR"
                                                    {{ isset($roomType['currency']) && $roomType['currency'] == 'SAR' ? 'selected' : '' }}>
                                                    ريال سعودي</option>
                                                <option value="KWD"
                                                    {{ isset($roomType['currency']) && $roomType['currency'] == 'KWD' ? 'selected' : '' }}>
                                                    دينار كويتي</option>
                                            </select>
                                            @error('room_types.' . $index . '.currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">عدد الغرف المتاحة</label>
                                            <input type="number"
                                                class="form-control @error('room_types.' . $index . '.allotment') is-invalid @enderror"
                                                name="room_types[{{ $index }}][allotment]"
                                                value="{{ $roomType['allotment'] ?? '' }}">
                                            @error('room_types.' . $index . '.allotment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-1 col-md-6 d-flex align-items-end">
                                            <button type="button"
                                                class="btn btn-danger remove-room-type remove-room-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- إذا لم يكن هناك خطأ في البيانات، استخدم البيانات المخزنة --}}
                            @foreach ($landTrip->roomPrices as $index => $roomPrice)
                                <div class="room-type-row">
                                    <input type="hidden" name="room_types[{{ $index }}][id]"
                                        value="{{ $roomPrice->id }}">

                                    <div class="row g-3">
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label">نوع الغرفة <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select"
                                                name="room_types[{{ $index }}][room_type_id]" required>
                                                <option value="">-- اختر نوع الغرفة --</option>
                                                @foreach ($roomTypes as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ $roomPrice->room_type_id == $type->id ? 'selected' : '' }}>
                                                        {{ $type->room_type_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">سعر التكلفة <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" class="form-control"
                                                    name="room_types[{{ $index }}][cost_price]"
                                                    value="{{ $roomPrice->cost_price }}" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">سعر البيع <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" class="form-control"
                                                    name="room_types[{{ $index }}][sale_price]"
                                                    value="{{ $roomPrice->sale_price }}" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">العملة <span class="text-danger">*</span></label>
                                            <select class="form-select" name="room_types[{{ $index }}][currency]"
                                                required>
                                                <option value="SAR"
                                                    {{ $roomPrice->currency == 'SAR' ? 'selected' : '' }}>
                                                    ريال سعودي</option>
                                                <option value="KWD"
                                                    {{ $roomPrice->currency == 'KWD' ? 'selected' : '' }}>
                                                    دينار كويتي</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">عدد الغرف المتاحة</label>
                                            <input type="number" class="form-control"
                                                name="room_types[{{ $index }}][allotment]"
                                                value="{{ $roomPrice->allotment }}">
                                        </div>

                                        <div class="col-lg-1 col-md-6 d-flex align-items-end">
                                            <button type="button"
                                                class="btn btn-danger remove-room-type remove-room-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-custom-primary">
                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                </button>
                <a href="{{ route('admin.land-trips.index') }}" class="btn btn-custom-secondary">
                    <i class="fas fa-arrow-right me-1"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
                let roomTypeIndex = 1; // نبدأ من 1 لأن الغرفة الأولى لديها مؤشر 0 بالفعل

           // حل المشكلة هنا - تسجيل معالج الحدث click بشكل صحيح
            $('#add-room-type').on('click', function() {
                const template = `
                <div class="room-type-row new-room">
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
                                <input type="number" step="0.01" class="form-control" name="room_types[${roomTypeIndex}][cost_price]" required>
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">سعر البيع <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="room_types[${roomTypeIndex}][sale_price]" required>
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">العملة <span class="text-danger">*</span></label>
                            <select class="form-select" name="room_types[${roomTypeIndex}][currency]" required>
                                <option value="SAR" selected>ريال سعودي</option>
                                <option value="KWD">دينار كويتي</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">عدد الغرف المتاحة</label>
                            <input type="number" class="form-control" name="room_types[${roomTypeIndex}][allotment]">
                        </div>
                        
                        <div class="col-lg-1 col-md-6 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-room-type remove-room-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                `;

                $('#room-types-container').append(template);

                // تطبيق تأثير دخول للعنصر الجديد
                const newRoom = $('#room-types-container .new-room').last();
                setTimeout(function() {
                    newRoom.removeClass('new-room');
                }, 100);

                roomTypeIndex++;
            });

            // حذف نوع غرفة
            $(document).on('click', '.remove-room-type', function() {
                if ($('.room-type-row').length > 1) {
                    const row = $(this).closest('.room-type-row');
                    row.addClass('removing');
                    setTimeout(function() {
                        row.remove();
                    }, 300);
                } else {
                    Swal.fire({
                        title: 'تنبيه!',
                        text: 'يجب إضافة نوع غرفة واحد على الأقل.',
                        icon: 'warning',
                        confirmButtonText: 'حسناً',
                        confirmButtonColor: '#3f51b5',
                    });
                }
            });
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة حقول التاريخ
            $('.datepicker').datepicker('destroy');
                if (typeof $.fn.datepicker !== 'undefined') {
        $.fn.datepicker.defaults.format = 'yyyy-mm-dd';
        $.fn.datepicker.defaults.autoclose = true;
        $.fn.datepicker.defaults.todayHighlight = true;
        $.fn.datepicker.defaults.language = 'ar';
        $.fn.datepicker.defaults.rtl = true;
        $.fn.datepicker.defaults.orientation = "auto"; }
            // تهيئة حقول التاريخ بنفس الإعدادات لضمان التناسق
            if (typeof $.fn.datepicker !== 'undefined') {
                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'ar',
                    rtl: true,
                    orientation: "auto"
                }).on('changeDate', function(e) {
                    // التأكد من تنسيق التاريخ عند اختياره
                    if (e.date) {
                        const year = e.date.getFullYear();
                        const month = String(e.date.getMonth() + 1).padStart(2, '0');
                        const day = String(e.date.getDate()).padStart(2, '0');
                        const formattedDate = `${year}-${month}-${day}`;
                        $(this).val(formattedDate);
                        console.log(`تم اختيار التاريخ: ${formattedDate}`);
                    }
                });
                
                // تحويل أي تواريخ بالتنسيق القديم
                $('.datepicker').each(function() {
                    const value = $(this).val();
                    if (value && value.includes('/')) {
                        // تحويل من dd/mm/yyyy إلى yyyy-mm-dd
                        const parts = value.split('/');
                        // التأكد من أن لدينا 3 أجزاء
                        if (parts.length === 3) {
                            let day, month, year;
                            
                            // تحديد الترتيب الصحيح
                            if (parts[2].length === 4) { // إذا كان الجزء الثالث هو السنة (dd/mm/yyyy)
                                day = parts[0];
                                month = parts[1];
                                year = parts[2];
                            } else if (parts[0].length === 4) { // إذا كان الجزء الأول هو السنة (yyyy/mm/dd)
                                year = parts[0];
                                month = parts[1];
                                day = parts[2];
                            }
                            
                            // إنشاء التنسيق الجديد
                            const newValue = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
                            $(this).val(newValue);
                            console.log(`تم تحويل التاريخ من ${value} إلى ${newValue}`);
                        }
                    }
                });
                
                // تغيير النص التوضيحي في حقول التاريخ
                $('.datepicker').attr('placeholder', 'YYYY-MM-DD');
            }

            // التحقق من صحة النموذج قبل الإرسال
            $('#edit-trip-form').on('submit', function(e) {
                // التأكد من تنسيق التاريخ قبل الإرسال
                const departureDate = $('#departure_date').val();
                const returnDate = $('#return_date').val();
                
                // طباعة للتشخيص
                console.log('تاريخ المغادرة قبل الإرسال:', departureDate);
                console.log('تاريخ العودة قبل الإرسال:', returnDate);
                
                // تحويل أي تاريخ ما زال بالتنسيق القديم (dd/mm/yyyy)
                let fixedReturnDate = returnDate;
                if (returnDate.includes('/')) {
                    const parts = returnDate.split('/');
                    if (parts.length === 3) {
                        if (parts[2].length === 4) { // dd/mm/yyyy
                            fixedReturnDate = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                            $('#return_date').val(fixedReturnDate);
                        }
                    }
                }
                
                // التحقق من صحة تنسيق التاريخ (yyyy-mm-dd)
                const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                
                if (!dateRegex.test(departureDate) || !dateRegex.test($('#return_date').val())) {
                    e.preventDefault();
                    alert('خطأ في تنسيق التاريخ: يجب أن تكون التواريخ بصيغة YYYY-MM-DD (سنة-شهر-يوم)');
                    return false;
                }
                
                // التحقق من تسلسل التواريخ
                if ($('#return_date').val() < departureDate) {
                    e.preventDefault();
                    alert('خطأ في التواريخ: يجب أن يكون تاريخ العودة بعد أو يساوي تاريخ المغادرة');
                    return false;
                }
                
                // طباعة للتشخيص
                console.log('تم قبول النموذج وسيتم إرساله');
                return true;
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

            // إضافة نوع غرفة
            let roomTypeIndex = $('.room-type-row').length;

         

            // التحقق من صحة النموذج قبل الإرسال
            $('#create-trip-form, #edit-trip-form').on('submit', function(e) {
                // التأكد من تنسيق التاريخ قبل الإرسال
                const departureDate = $('#departure_date').val();
                const returnDate = $('#return_date').val();

                // طباعة للتشخيص
                console.log('تاريخ المغادرة قبل الإرسال:', departureDate);
                console.log('تاريخ العودة قبل الإرسال:', returnDate);

                // التحقق من صحة تنسيق التاريخ (yyyy-mm-dd)
                const dateRegex = /^\d{4}-\d{2}-\d{2}$/;

                if (!dateRegex.test(departureDate) || !dateRegex.test(returnDate)) {
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
                if (returnDate < departureDate) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'خطأ في التواريخ',
                        text: 'يجب أن يكون تاريخ العودة بعد أو يساوي تاريخ المغادرة',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                    return false;
                }

                // طباعة للتشخيص
                console.log('تم قبول النموذج وسيتم إرساله');
                return true;
            });
        });

        // دوال مساعدة إضافية تظل خارج معالج الحدث DOMContentLoaded
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
    </script>

    <style>
        /* تأثيرات إضافية */
        .custom-card {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }

        .custom-card.animated {
            opacity: 1;
            transform: translateY(0);
        }

        .room-type-row {
            transition: all 0.5s ease;
        }

        .new-room {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.3s ease forwards;
        }

        .removing {
            opacity: 0;
            transform: translateY(20px);
        }

        .focused {
            position: relative;
        }

        .focused:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
            opacity: 0.6;
        }

        /* مؤشر التحميل */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        /* تحريكات */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* الهواتف الصغيرة */
        @media (max-width: 576px) {
            .room-type-row {
                margin-bottom: 25px;
            }

            .room-type-row .row>div {
                margin-bottom: 10px;
            }

            .remove-room-btn {
                margin-top: 10px;
            }
        }
    </style>
@endpush
