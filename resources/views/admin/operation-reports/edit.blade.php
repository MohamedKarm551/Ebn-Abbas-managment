@extends('layouts.app')

@section('title', 'تعديل تقرير العملية')

@push('styles')
    <style>
        .profit-display {
            font-weight: bold;
            color: #21a179;
            margin-top: 8px;
        }

        .dynamic-section {
            background: #fff7ef;
            border-radius: 8px;
            padding: 18px 10px 10px 10px;
            margin-bottom: 18px;
            border: 1px solid #e0dfdf;
            position: relative;
        }

        .btn-remove {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #ffdedf !important;
            color: #a13d3d !important;
        }

        .input-group-3 {
            display: flex;
            gap: 12px;
        }

        @media (max-width: 768px) {
            .input-group-3 {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
@endpush


@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">تعديل تقرير العملية</h1>
                <p class="text-muted mb-0">التقرير رقم: #{{ $operationReport->id }}</p>
            </div>
            <div>
                <a href="{{ route('admin.operation-reports.show', $operationReport) }}" class="btn btn-secondary btn-sm me-2">
                    <i class="fas fa-eye"></i> عرض التقرير
                </a>
                <a href="{{ route('admin.operation-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> العودة للقائمة
                </a>
            </div>
        </div>

        <!-- عرض الأخطاء -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6 class="alert-heading">يرجى تصحيح الأخطاء التالية:</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.operation-reports.update', $operationReport) }}" method="POST"
            enctype="multipart/form-data" id="operationReportForm">
            @csrf
            @method('PUT')

            <!-- المعلومات الأساسية -->
            <div class="form-section">
                <h3 class="form-section-header">
                    <i class="fas fa-info-circle me-2"></i>المعلومات الأساسية
                </h3>
                <div class="form-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                {{-- employee_id only Admin can edit --}}
                                @if (Auth::user()->role === 'Admin')
                                    <label for="employee_id" class="form-label">الموظف المسؤول *</label>
                                    <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id"
                                        name="employee_id" required>
                                        <option value="">اختر موظف</option>
                                        @if (isset($employees) && !empty($employees))
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}"
                                                    {{ old('employee_id', $operationReport->employee_id) == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                @endif
                                <label for="report_date" class="form-label">تاريخ التقرير *</label>
                                <input type="date" class="form-control @error('report_date') is-invalid @enderror"
                                    id="report_date" name="report_date"
                                    value="{{ old('report_date', $operationReport->report_date->format('Y-m-d')) }}"
                                    required>
                                @error('report_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="client_name" class="form-label">اسم العميل *</label>
                                <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                    id="client_name" name="client_name"
                                    value="{{ old('client_name', $operationReport->client_name) }}" required>
                                @error('client_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="client_phone" class="form-label">هاتف العميل</label>
                                <input type="text" class="form-control @error('client_phone') is-invalid @enderror"
                                    id="client_phone" name="client_phone"
                                    value="{{ old('client_phone', $operationReport->client_phone) }}">
                                @error('client_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                          
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company_name" class="form-label">اسم الشركة</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                    id="company_name" name="company_name"
                                    value="{{ old('company_name', $operationReport->company_name) }}">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="booking_type" class="form-label">نوع الحجز</label>
                                <select class="form-select @error('booking_type') is-invalid @enderror" id="booking_type"
                                    name="booking_type">
                                    <option value="">اختر نوع الحجز</option>
                                    <option value="فردي"
                                        {{ old('booking_type', $operationReport->booking_type) == 'فردي' ? 'selected' : '' }}>
                                        فردي</option>
                                    <option value="جماعي"
                                        {{ old('booking_type', $operationReport->booking_type) == 'جماعي' ? 'selected' : '' }}>
                                        جماعي</option>
                                    <option value="شركات"
                                        {{ old('booking_type', $operationReport->booking_type) == 'شركات' ? 'selected' : '' }}>
                                        شركات</option>
                                </select>
                                @error('booking_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="booking_reference" class="form-label">مرجع الحجز</label>
                                <input type="text"
                                    class="form-control @error('booking_reference') is-invalid @enderror"
                                    id="booking_reference" name="booking_reference"
                                    value="{{ old('booking_reference', $operationReport->booking_reference) }}">
                                @error('booking_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">ملاحظات عامة</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $operationReport->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- قسم التأشيرات -->
            <div class="form-section">
                <h3 class="form-section-header">
                    <i class="fas fa-passport me-2"></i>بيانات التأشيرات
                    <button type="button" class="btn btn-light btn-sm float-end" onclick="addVisaSection()">
                        <i class="fas fa-plus"></i> إضافة تأشيرة
                    </button>
                </h3>
                <div class="form-section-body" id="visasContainer">
                    @if ($operationReport->visas->count() > 0)
                        @foreach ($operationReport->visas as $index => $visa)
                            <div class="dynamic-section visa-section" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <span class="section-counter">{{ $index + 1 }}</span>
                                        تأشيرة رقم {{ $index + 1 }}
                                    </h6>
                                    <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">نوع التأشيرة</label>
                                        <select class="form-select" name="visas[{{ $index }}][visa_type]">
                                            <option value="سياحية" {{ $visa->visa_type == 'سياحية' ? 'selected' : '' }}>
                                                سياحية</option>
                                            <option value="عمل" {{ $visa->visa_type == 'عمل' ? 'selected' : '' }}>عمل
                                            </option>
                                            <option value="زيارة" {{ $visa->visa_type == 'زيارة' ? 'selected' : '' }}>
                                                زيارة</option>
                                            <option value="عمرة" {{ $visa->visa_type == 'عمرة' ? 'selected' : '' }}>عمرة
                                            </option>
                                            <option value="حج" {{ $visa->visa_type == 'حج' ? 'selected' : '' }}>حج
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">الكمية</label>
                                        <input type="number" class="form-control"
                                            name="visas[{{ $index }}][quantity]" value="{{ $visa->quantity }}"
                                            min="1" onchange="calculateVisaProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">التكلفة</label>
                                        <input type="number" class="form-control"
                                            name="visas[{{ $index }}][cost]" value="{{ $visa->cost }}"
                                            step="0.01" onchange="calculateVisaProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">سعر البيع</label>
                                        <input type="number" class="form-control"
                                            name="visas[{{ $index }}][selling_price]"
                                            value="{{ $visa->selling_price }}" step="0.01"
                                            onchange="calculateVisaProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                                        <select class="form-select" name="visas[{{ $index }}][currency]"
                                            onchange="calculateVisaProfit({{ $index }})">
                                            <option value="KWD" {{ $visa->currency == 'KWD' ? 'selected' : '' }}>دينار
                                                كويتي</option>
                                            <option value="SAR" {{ $visa->currency == 'SAR' ? 'selected' : '' }}>ريال
                                                سعودي</option>
                                            <option value="USD" {{ $visa->currency == 'USD' ? 'selected' : '' }}>دولار
                                                أمريكي</option>
                                            <option value="EUR" {{ $visa->currency == 'EUR' ? 'selected' : '' }}>يورو
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">الربح</label>
                                        <div class="profit-display" id="visaProfit{{ $index }}">
                                            {{ number_format($visa->profit, 2) }}</div>
                                        <input type="hidden" name="visas[{{ $index }}][profit]"
                                            value="{{ $visa->profit }}" id="visaProfitInput{{ $index }}">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea class="form-control" name="visas[{{ $index }}][notes]" rows="2">{{ $visa->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4" id="visasEmptyState">
                            <i class="fas fa-passport fa-3x mb-3"></i>
                            <p>لا توجد تأشيرات مضافة. اضغط "إضافة تأشيرة" لبدء الإضافة.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- قسم الطيران -->
            <div class="form-section">
                <h3 class="form-section-header">
                    <i class="fas fa-plane me-2"></i>بيانات الطيران
                    <button type="button" class="btn btn-light btn-sm float-end" onclick="addFlightSection()">
                        <i class="fas fa-plus"></i> إضافة رحلة
                    </button>
                </h3>
                <div class="form-section-body" id="flightsContainer">
                    @if ($operationReport->flights->count() > 0)
                        @foreach ($operationReport->flights as $index => $flight)
                            <div class="dynamic-section flight-section" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <span class="section-counter">{{ $index + 1 }}</span>
                                        رحلة رقم {{ $index + 1 }}
                                    </h6>
                                    <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">تاريخ الرحلة</label>
                                        <input type="date" class="form-control"
                                            name="flights[{{ $index }}][flight_date]"
                                            value="{{ $flight->flight_date ? $flight->flight_date->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">رقم الرحلة</label>
                                        <input type="text" class="form-control"
                                            name="flights[{{ $index }}][flight_number]"
                                            value="{{ $flight->flight_number }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">شركة الطيران</label>
                                        <input type="text" class="form-control"
                                            name="flights[{{ $index }}][airline]" value="{{ $flight->airline }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">المسار</label>
                                        <input type="text" class="form-control"
                                            name="flights[{{ $index }}][route]" value="{{ $flight->route }}">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">عدد المسافرين</label>
                                        <input type="number" class="form-control"
                                            name="flights[{{ $index }}][passengers]"
                                            value="{{ $flight->passengers }}" min="1">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">نوع الرحلة</label>
                                        <select class="form-select" name="flights[{{ $index }}][trip_type]">
                                            <option value="ذهاب فقط"
                                                {{ $flight->trip_type == 'ذهاب فقط' ? 'selected' : '' }}>ذهاب فقط</option>
                                            <option value="ذهاب وعودة"
                                                {{ $flight->trip_type == 'ذهاب وعودة' ? 'selected' : '' }}>ذهاب وعودة
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">التكلفة</label>
                                        <input type="number" class="form-control"
                                            name="flights[{{ $index }}][cost]" value="{{ $flight->cost }}"
                                            step="0.01" onchange="calculateFlightProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">سعر البيع</label>
                                        <input type="number" class="form-control"
                                            name="flights[{{ $index }}][selling_price]"
                                            value="{{ $flight->selling_price }}" step="0.01"
                                            onchange="calculateFlightProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                                        <select class="form-select" name="flights[{{ $index }}][currency]"
                                            onchange="calculateFlightProfit({{ $index }})">
                                            <option value="KWD" {{ $flight->currency == 'KWD' ? 'selected' : '' }}>
                                                دينار كويتي</option>
                                            <option value="SAR" {{ $flight->currency == 'SAR' ? 'selected' : '' }}>ريال
                                                سعودي</option>
                                            <option value="USD" {{ $flight->currency == 'USD' ? 'selected' : '' }}>
                                                دولار أمريكي</option>
                                            <option value="EUR" {{ $flight->currency == 'EUR' ? 'selected' : '' }}>يورو
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">الربح</label>
                                        <div class="profit-display" id="flightProfit{{ $index }}">
                                            {{ number_format($flight->profit, 2) }}</div>
                                        <input type="hidden" name="flights[{{ $index }}][profit]"
                                            value="{{ $flight->profit }}" id="flightProfitInput{{ $index }}">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea class="form-control" name="flights[{{ $index }}][notes]" rows="2">{{ $flight->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4" id="flightsEmptyState">
                            <i class="fas fa-plane fa-3x mb-3"></i>
                            <p>لا توجد رحلات مضافة. اضغط "إضافة رحلة" لبدء الإضافة.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- قسم النقل -->
            <div class="form-section">
                <h3 class="form-section-header">
                    <i class="fas fa-bus me-2"></i>بيانات النقل
                    <button type="button" class="btn btn-light btn-sm float-end" onclick="addTransportSection()">
                        <i class="fas fa-plus"></i> إضافة نقل
                    </button>
                </h3>
                <div class="form-section-body" id="transportsContainer">
                    @if ($operationReport->transports->count() > 0)
                        @foreach ($operationReport->transports as $index => $transport)
                            <div class="dynamic-section transport-section" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <span class="section-counter">{{ $index + 1 }}</span>
                                        نقل رقم {{ $index + 1 }}
                                    </h6>
                                    <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">نوع النقل</label>
                                        <select class="form-select"
                                            name="transports[{{ $index }}][transport_type]">
                                            <option value="سيارة خاصة"
                                                {{ $transport->transport_type == 'سيارة خاصة' ? 'selected' : '' }}>سيارة
                                                خاصة</option>
                                            <option value="حافلة"
                                                {{ $transport->transport_type == 'حافلة' ? 'selected' : '' }}>حافلة
                                            </option>
                                            <option value="فان"
                                                {{ $transport->transport_type == 'فان' ? 'selected' : '' }}>فان</option>
                                            <option value="ليموزين"
                                                {{ $transport->transport_type == 'ليموزين' ? 'selected' : '' }}>ليموزين
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">اسم السائق</label>
                                        <input type="text" class="form-control"
                                            name="transports[{{ $index }}][driver_name]"
                                            value="{{ $transport->driver_name }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">هاتف السائق</label>
                                        <input type="text" class="form-control"
                                            name="transports[{{ $index }}][driver_phone]"
                                            value="{{ $transport->driver_phone }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">معلومات المركبة</label>
                                        <input type="text" class="form-control"
                                            name="transports[{{ $index }}][vehicle_info]"
                                            value="{{ $transport->vehicle_info }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">معلومات المركبة</label>
                                        <input type="text" class="form-control"
                                            name="transports[{{ $index }}][vehicle_info]"
                                            value="{{ $transport->vehicle_info }}">
                                    </div>

                                    <!-- إضافة حقول مواعيد الانطلاق والوصول -->
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">موعد الانطلاق</label>
                                        <input type="datetime-local" class="form-control"
                                            name="transports[{{ $index }}][departure_time]"
                                            value="{{ isset($transport->departure_time) ? \Carbon\Carbon::parse($transport->departure_time)->format('Y-m-d\TH:i') : '' }}">
                                    </div>

                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">موعد الوصول</label>
                                        <input type="datetime-local" class="form-control"
                                            name="transports[{{ $index }}][arrival_time]"
                                            value="{{ isset($transport->arrival_time) ? \Carbon\Carbon::parse($transport->arrival_time)->format('Y-m-d\TH:i') : '' }}">
                                    </div>

                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">التكلفة</label>
                                        <input type="number" class="form-control"
                                            name="transports[{{ $index }}][cost]"
                                            value="{{ $transport->cost }}" step="0.01"
                                            onchange="calculateTransportProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">سعر البيع</label>
                                        <input type="number" class="form-control"
                                            name="transports[{{ $index }}][selling_price]"
                                            value="{{ $transport->selling_price }}" step="0.01"
                                            onchange="calculateTransportProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                                        <select class="form-select" name="transports[{{ $index }}][currency]"
                                            onchange="calculateTransportProfit({{ $index }})">
                                            <option value="KWD" {{ $transport->currency == 'KWD' ? 'selected' : '' }}>
                                                دينار كويتي</option>
                                            <option value="SAR" {{ $transport->currency == 'SAR' ? 'selected' : '' }}>
                                                ريال سعودي</option>
                                            <option value="USD" {{ $transport->currency == 'USD' ? 'selected' : '' }}>
                                                دولار أمريكي</option>
                                            <option value="EUR" {{ $transport->currency == 'EUR' ? 'selected' : '' }}>
                                                يورو</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">الربح</label>
                                        <div class="profit-display" id="transportProfit{{ $index }}">
                                            {{ number_format($transport->profit, 2) }}</div>
                                        <input type="hidden" name="transports[{{ $index }}][profit]"
                                            value="{{ $transport->profit }}"
                                            id="transportProfitInput{{ $index }}">
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <label class="form-label">تذكرة النقل</label>
                                        @if ($transport->ticket_file_path)
                                            <div class="mb-2">
                                                <a href="{{ asset('storage/' . $transport->ticket_file_path) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-file-alt"></i> عرض التذكرة الحالية
                                                </a>
                                                <input type="hidden"
                                                    name="transports[{{ $index }}][existing_ticket_file]"
                                                    value="{{ $transport->ticket_file_path }}">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control"
                                            name="transports[{{ $index }}][ticket_file]"
                                            accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                                        <small class="text-muted">PDF, JPG, PNG (أقصى 5 ميجا)</small>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea class="form-control" name="transports[{{ $index }}][notes]" rows="2">{{ $transport->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4" id="transportsEmptyState">
                            <i class="fas fa-bus fa-3x mb-3"></i>
                            <p>لا توجد وسائل نقل مضافة. اضغط "إضافة نقل" لبدء الإضافة.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- قسم الفنادق -->
            <div class="form-section">
                <h3 class="form-section-header">
                    <i class="fas fa-hotel me-2"></i>بيانات الفنادق
                    <button type="button" class="btn btn-light btn-sm float-end" onclick="addHotelSection()">
                        <i class="fas fa-plus"></i> إضافة فندق
                    </button>
                </h3>
                <div class="form-section-body" id="hotelsContainer">
                    @if ($operationReport->hotels->count() > 0)
                        @foreach ($operationReport->hotels as $index => $hotel)
                            <div class="dynamic-section hotel-section" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <span class="section-counter">{{ $index + 1 }}</span>
                                        فندق رقم {{ $index + 1 }}
                                    </h6>
                                    <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">اسم الفندق</label>
                                        <input type="text" class="form-control"
                                            name="hotels[{{ $index }}][hotel_name]"
                                            value="{{ $hotel->hotel_name }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">المدينة</label>
                                        <input type="text" class="form-control"
                                            name="hotels[{{ $index }}][city]" value="{{ $hotel->city }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">نوع الغرفة</label>
                                        <input type="text" class="form-control"
                                            name="hotels[{{ $index }}][room_type]"
                                            value="{{ $hotel->room_type }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">عدد الغرف</label>
                                        <input type="number" class="form-control"
                                            name="hotels[{{ $index }}][rooms]" value="{{ $hotel->rooms }}"
                                            min="1">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">تاريخ الدخول</label>
                                        <input type="date" class="form-control"
                                            name="hotels[{{ $index }}][check_in]"
                                            value="{{ $hotel->check_in ? $hotel->check_in->format('Y-m-d') : '' }}"
                                            onchange="calculateHotelNights({{ $index }})">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">تاريخ الخروج</label>
                                        <input type="date" class="form-control"
                                            name="hotels[{{ $index }}][check_out]"
                                            value="{{ $hotel->check_out ? $hotel->check_out->format('Y-m-d') : '' }}"
                                            onchange="calculateHotelNights({{ $index }})">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">عدد الليالي</label>
                                        <input type="number" class="form-control"
                                            name="hotels[{{ $index }}][nights]" value="{{ $hotel->nights }}"
                                            min="1" readonly id="hotelNights{{ $index }}">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">عدد الضيوف</label>
                                        <input type="number" class="form-control"
                                            name="hotels[{{ $index }}][guests]" value="{{ $hotel->guests }}"
                                            min="1">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">تكلفة الليلة</label>
                                        <input type="number" class="form-control"
                                            name="hotels[{{ $index }}][night_cost]"
                                            value="{{ $hotel->night_cost }}" step="0.01"
                                            onchange="calculateHotelTotal({{ $index }})">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">سعر بيع الليلة</label>
                                        <input type="number" class="form-control"
                                            name="hotels[{{ $index }}][night_selling_price]"
                                            value="{{ $hotel->night_selling_price }}" step="0.01"
                                            onchange="calculateHotelTotal({{ $index }})">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">إجمالي التكلفة</label>
                                        <input type="number" class="form-control"
                                            name="hotels[{{ $index }}][total_cost]"
                                            value="{{ $hotel->total_cost }}" step="0.01" readonly
                                            id="hotelTotalCost{{ $index }}">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">إجمالي البيع</label>
                                        <input type="number" class="form-control"
                                            name="hotels[{{ $index }}][total_selling_price]"
                                            value="{{ $hotel->total_selling_price }}" step="0.01" readonly
                                            id="hotelTotalSelling{{ $index }}">
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                                        <select class="form-select" name="hotels[{{ $index }}][currency]"
                                            onchange="calculateHotelTotal({{ $index }})">
                                            <option value="KWD" {{ $hotel->currency == 'KWD' ? 'selected' : '' }}>
                                                دينار كويتي</option>
                                            <option value="SAR" {{ $hotel->currency == 'SAR' ? 'selected' : '' }}>ريال
                                                سعودي</option>
                                            <option value="USD" {{ $hotel->currency == 'USD' ? 'selected' : '' }}>
                                                دولار أمريكي</option>
                                            <option value="EUR" {{ $hotel->currency == 'EUR' ? 'selected' : '' }}>يورو
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <label class="form-label">الربح</label>
                                        <div class="profit-display" id="hotelProfit{{ $index }}">
                                            {{ number_format($hotel->profit, 2) }}</div>
                                        <input type="hidden" name="hotels[{{ $index }}][profit]"
                                            value="{{ $hotel->profit }}" id="hotelProfitInput{{ $index }}">
                                    </div>
                                    <div class="col-md-4 mt-3">
                                        <label class="form-label">فاوتشر الفندق</label>
                                        @if ($hotel->voucher_file_path)
                                            <div class="mb-2">
                                                <a href="{{ asset('storage/' . $hotel->voucher_file_path) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-file-alt"></i> عرض الفاوتشر الحالي
                                                </a>
                                                <input type="hidden"
                                                    name="hotels[{{ $index }}][existing_voucher_file]"
                                                    value="{{ $hotel->voucher_file_path }}">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control"
                                            name="hotels[{{ $index }}][voucher_file]"
                                            accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                                        <small class="text-muted">PDF, JPG, PNG (أقصى 5 ميجا)</small>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea class="form-control" name="hotels[{{ $index }}][notes]" rows="2">{{ $hotel->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4" id="hotelsEmptyState">
                            <i class="fas fa-hotel fa-3x mb-3"></i>
                            <p>لا توجد فنادق مضافة. اضغط "إضافة فندق" لبدء الإضافة.</p>
                        </div>
                    @endif
                </div>
            </div>
            {{-- قسم الرحلات البرية --}}
            <!-- قسم الرحلات البرية -->
            <div class="form-section">
                <h3 class="form-section-header">
                    <i class="fas fa-mountain me-2"></i>بيانات الرحلات البرية
                    <button type="button" class="btn btn-light btn-sm float-end" onclick="addLandTripSection()">
                        <i class="fas fa-plus"></i> إضافة رحلة برية
                    </button>
                </h3>
                <div class="form-section-body" id="landTripsContainer">
                    @if ($operationReport->landTrips->count() > 0)
                        @foreach ($operationReport->landTrips as $index => $landTrip)
                            <div class="dynamic-section land-trip-section" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <span class="section-counter">{{ $index + 1 }}</span>
                                        رحلة برية رقم {{ $index + 1 }}
                                    </h6>
                                    <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">نوع الرحلة</label>
                                        <input type="text" class="form-control"
                                            name="land_trips[{{ $index }}][trip_type]"
                                            value="{{ $landTrip->trip_type }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">تاريخ المغادرة</label>
                                        <input type="date" class="form-control"
                                            name="land_trips[{{ $index }}][departure_date]"
                                            value="{{ $landTrip->departure_date ? $landTrip->departure_date->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">تاريخ العودة</label>
                                        <input type="date" class="form-control"
                                            name="land_trips[{{ $index }}][return_date]"
                                            value="{{ $landTrip->return_date ? $landTrip->return_date->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">عدد الأيام</label>
                                        <input type="number" class="form-control"
                                            name="land_trips[{{ $index }}][days]" value="{{ $landTrip->days }}"
                                            min="1">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">تكلفة النقل</label>
                                        <input type="number" class="form-control"
                                            name="land_trips[{{ $index }}][transport_cost]"
                                            value="{{ $landTrip->transport_cost }}" step="0.01"
                                            onchange="calculateLandTripTotal({{ $index }})">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">تكلفة فندق مكة</label>
                                        <input type="number" class="form-control"
                                            name="land_trips[{{ $index }}][mecca_hotel_cost]"
                                            value="{{ $landTrip->mecca_hotel_cost }}" step="0.01"
                                            onchange="calculateLandTripTotal({{ $index }})">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">تكلفة فندق المدينة</label>
                                        <input type="number" class="form-control"
                                            name="land_trips[{{ $index }}][medina_hotel_cost]"
                                            value="{{ $landTrip->medina_hotel_cost }}" step="0.01"
                                            onchange="calculateLandTripTotal({{ $index }})">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">تكاليف إضافية</label>
                                        <input type="number" class="form-control"
                                            name="land_trips[{{ $index }}][extra_costs]"
                                            value="{{ $landTrip->extra_costs }}" step="0.01"
                                            onchange="calculateLandTripTotal({{ $index }})">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">إجمالي التكلفة</label>
                                        <input type="number" class="form-control"
                                            name="land_trips[{{ $index }}][total_cost]"
                                            value="{{ $landTrip->total_cost }}" step="0.01" readonly
                                            id="landTripTotalCost{{ $index }}">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">سعر البيع</label>
                                        <input type="number" class="form-control"
                                            name="land_trips[{{ $index }}][selling_price]"
                                            value="{{ $landTrip->selling_price }}" step="0.01"
                                            onchange="calculateLandTripProfit({{ $index }})">
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                                        <select class="form-select" name="land_trips[{{ $index }}][currency]"
                                            onchange="calculateLandTripProfit({{ $index }})">
                                            <option value="KWD" {{ $landTrip->currency == 'KWD' ? 'selected' : '' }}>
                                                دينار كويتي</option>
                                            <option value="SAR" {{ $landTrip->currency == 'SAR' ? 'selected' : '' }}>
                                                ريال
                                                سعودي</option>
                                            <option value="USD" {{ $landTrip->currency == 'USD' ? 'selected' : '' }}>
                                                دولار أمريكي</option>
                                            <option value="EUR" {{ $landTrip->currency == 'EUR' ? 'selected' : '' }}>
                                                يورو
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mt-3">
                                        <label class="form-label">الربح</label>
                                        <div class="profit-display" id="landTripProfit{{ $index }}">
                                            {{ number_format($landTrip->profit, 2) }}</div>
                                        <input type="hidden" name="land_trips[{{ $index }}][profit]"
                                            value="{{ $landTrip->profit }}"
                                            id="landTripProfitInput{{ $index }}">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea class="form-control" name="land_trips[{{ $index }}][notes]" rows="2">{{ $landTrip->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4" id="landTripsEmptyState">
                            <i class="fas fa-mountain fa-3x mb-3"></i>
                            <p>لا توجد رحلات برية مضافة. اضغط "إضافة رحلة برية" لبدء الإضافة.</p>
                        </div>
                    @endif
                </div>
            </div>
            {{-- الربح الكلي يظهر وانا بكتب وبعدل --}}
            <div class="form-section mb-4">
                <div class="profit-display bg-light p-3 rounded shadow-sm" style="font-size: 1.2rem; text-align: center;">
                    <span>إجمالي الربح الحالي: </span>
                    <span id="total-profit-display" style="color: #21a179; font-weight: bold;">0.00</span>
                    <span id="total-profit-currency" class="text-muted"></span>
                    <div class="mt-2" id="profit-breakdown" style="font-size: 0.95em;"></div>
                </div>
            </div>


            <!-- أزرار الإجراءات -->
            <div class="form-section">
                <div class="form-section-body text-center">
                    <button type="submit" class="btn btn-success btn-lg me-3">
                        <i class="fas fa-save"></i> حفظ التحديثات
                    </button>
                    <a href="{{ route('admin.operation-reports.show', $operationReport) }}"
                        class="btn btn-secondary btn-lg me-2">
                        <i class="fas fa-eye"></i> عرض التقرير
                    </a>
                    <a href="{{ route('admin.operation-reports.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection



@push('scripts')
    <script>
        // متغيرات العداد للأقسام الجديدة
        let visaIndex = {{ $operationReport->visas->count() }};
        let flightIndex = {{ $operationReport->flights->count() }};
        let transportIndex = {{ $operationReport->transports->count() }};
        let hotelIndex = {{ $operationReport->hotels->count() }};
        let landTripIndex = {{ $operationReport->landTrips->count() }};

        // دالة إضافة قسم التأشيرات
        function addVisaSection() {
            const container = document.getElementById('visasContainer');
            const emptyState = container.querySelector('.text-center.text-muted');
            if (emptyState) emptyState.remove();

            const section = `
        <div class="dynamic-section visa-section" data-index="${visaIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <span class="section-counter">${visaIndex + 1}</span>
                    تأشيرة رقم ${visaIndex + 1}
                </h6>
                <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">نوع التأشيرة</label>
                    <select class="form-select" name="visas[${visaIndex}][visa_type]">
                        <option value="سياحية">سياحية</option>
                        <option value="عمل">عمل</option>
                        <option value="زيارة">زيارة</option>
                        <option value="عمرة">عمرة</option>
                        <option value="حج">حج</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الكمية</label>
                    <input type="number" class="form-control" name="visas[${visaIndex}][quantity]" value="1" min="1" onchange="calculateVisaProfit(${visaIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">التكلفة</label>
                    <input type="number" class="form-control" name="visas[${visaIndex}][cost]" value="0" step="0.01" onchange="calculateVisaProfit(${visaIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">سعر البيع</label>
                    <input type="number" class="form-control" name="visas[${visaIndex}][selling_price]" value="0" step="0.01" onchange="calculateVisaProfit(${visaIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                    <select class="form-select" name="visas[${visaIndex}][currency]" onchange="calculateVisaProfit(${visaIndex})">
                        <option value="KWD">دينار كويتي</option>
                        <option value="SAR">ريال سعودي</option>
                        <option value="USD">دولار أمريكي</option>
                        <option value="EUR">يورو</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">الربح</label>
                    <div class="profit-display" id="visaProfit${visaIndex}">0.00</div>
                    <input type="hidden" name="visas[${visaIndex}][profit]" value="0" id="visaProfitInput${visaIndex}">
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" name="visas[${visaIndex}][notes]" rows="2"></textarea>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', section);
            visaIndex++;
        }

        // دالة إضافة قسم الطيران
        function addFlightSection() {
            const container = document.getElementById('flightsContainer');
            const emptyState = container.querySelector('.text-center.text-muted');
            if (emptyState) emptyState.remove();

            const section = `
        <div class="dynamic-section flight-section" data-index="${flightIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <span class="section-counter">${flightIndex + 1}</span>
                    رحلة رقم ${flightIndex + 1}
                </h6>
                <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">تاريخ الرحلة</label>
                    <input type="date" class="form-control" name="flights[${flightIndex}][flight_date]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">رقم الرحلة</label>
                    <input type="text" class="form-control" name="flights[${flightIndex}][flight_number]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">شركة الطيران</label>
                    <input type="text" class="form-control" name="flights[${flightIndex}][airline]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المسار</label>
                    <input type="text" class="form-control" name="flights[${flightIndex}][route]">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">عدد المسافرين</label>
                    <input type="number" class="form-control" name="flights[${flightIndex}][passengers]" value="1" min="1">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">نوع الرحلة</label>
                    <select class="form-select" name="flights[${flightIndex}][trip_type]">
                        <option value="ذهاب فقط">ذهاب فقط</option>
                        <option value="ذهاب وعودة">ذهاب وعودة</option>
                    </select>
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">التكلفة</label>
                    <input type="number" class="form-control" name="flights[${flightIndex}][cost]" value="0" step="0.01" onchange="calculateFlightProfit(${flightIndex})">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">سعر البيع</label>
                    <input type="number" class="form-control" name="flights[${flightIndex}][selling_price]" value="0" step="0.01" onchange="calculateFlightProfit(${flightIndex})">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                    <select class="form-select" name="flights[${flightIndex}][currency]" onchange="calculateFlightProfit(${flightIndex})">
                        <option value="KWD">دينار كويتي</option>
                        <option value="SAR">ريال سعودي</option>
                        <option value="USD">دولار أمريكي</option>
                        <option value="EUR">يورو</option>
                    </select>
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">الربح</label>
                    <div class="profit-display" id="flightProfit${flightIndex}">0.00</div>
                    <input type="hidden" name="flights[${flightIndex}][profit]" value="0" id="flightProfitInput${flightIndex}">
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" name="flights[${flightIndex}][notes]" rows="2"></textarea>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', section);
            flightIndex++;
        }

        // دالة إضافة قسم النقل
        function addTransportSection() {
            const container = document.getElementById('transportsContainer');
            const emptyState = container.querySelector('.text-center.text-muted');
            if (emptyState) emptyState.remove();

            const section = `
<div class="dynamic-section transport-section" data-index="${transportIndex}">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            <span class="section-counter">${transportIndex + 1}</span>
            نقل رقم ${transportIndex + 1}
        </h6>
        <button type="button" class="btn-remove-section" onclick="removeSection(this)">
            <i class="fas fa-trash"></i> حذف
        </button>
    </div>
    <div class="row">
        <div class="col-md-3">
            <label class="form-label">نوع النقل</label>
            <select class="form-select" name="transports[${transportIndex}][transport_type]">
                <option value="سيارة خاصة">سيارة خاصة</option>
                <option value="حافلة">حافلة</option>
                <option value="فان">فان</option>
                <option value="ليموزين">ليموزين</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">اسم السائق</label>
            <input type="text" class="form-control" name="transports[${transportIndex}][driver_name]">
        </div>
        <div class="col-md-3">
            <label class="form-label">هاتف السائق</label>
            <input type="text" class="form-control" name="transports[${transportIndex}][driver_phone]">
        </div>
        <div class="col-md-3">
            <label class="form-label">معلومات المركبة</label>
            <input type="text" class="form-control" name="transports[${transportIndex}][vehicle_info]">
        </div>
        
        <!-- إضافة حقول مواعيد الانطلاق والوصول -->
        <div class="col-md-3 mt-3">
            <label class="form-label">موعد الانطلاق</label>
            <input type="datetime-local" class="form-control" name="transports[${transportIndex}][departure_time]">
        </div>
        <div class="col-md-3 mt-3">
            <label class="form-label">موعد الوصول</label>
            <input type="datetime-local" class="form-control" name="transports[${transportIndex}][arrival_time]">
        </div>
        
        <div class="col-md-2 mt-3">
            <label class="form-label">التكلفة</label>
            <input type="number" class="form-control" name="transports[${transportIndex}][cost]" value="0" step="0.01" onchange="calculateTransportProfit(${transportIndex})">
        </div>
        <div class="col-md-2 mt-3">
            <label class="form-label">سعر البيع</label>
            <input type="number" class="form-control" name="transports[${transportIndex}][selling_price]" value="0" step="0.01" onchange="calculateTransportProfit(${transportIndex})">
        </div>
        <div class="col-md-2 mt-3">
            <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
            <select class="form-select" name="transports[${transportIndex}][currency]" onchange="calculateTransportProfit(${transportIndex})">
                <option value="KWD">دينار كويتي</option>
                <option value="SAR">ريال سعودي</option>
                <option value="USD">دولار أمريكي</option>
                <option value="EUR">يورو</option>
            </select>
        </div>
        <div class="col-md-2 mt-3">
            <label class="form-label">الربح</label>
            <div class="profit-display" id="transportProfit${transportIndex}">0.00</div>
            <input type="hidden" name="transports[${transportIndex}][profit]" value="0" id="transportProfitInput${transportIndex}">
        </div>
        <div class="col-md-4 mt-3">
            <label class="form-label">تذكرة النقل</label>
            <input type="file" class="form-control" name="transports[${transportIndex}][ticket_file]" 
                   accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
            <small class="text-muted">PDF, JPG, PNG (أقصى 5 ميجا)</small>
        </div>
        <div class="col-md-12 mt-2">
            <label class="form-label">ملاحظات</label>
            <textarea class="form-control" name="transports[${transportIndex}][notes]" rows="2"></textarea>
        </div>
    </div>
</div>`;
            container.insertAdjacentHTML('beforeend', section);
            transportIndex++;
        }
        // دالة إضافة قسم الفنادق
        function addHotelSection() {
            const container = document.getElementById('hotelsContainer');
            const emptyState = container.querySelector('.text-center.text-muted');
            if (emptyState) emptyState.remove();

            const section = `
        <div class="dynamic-section hotel-section" data-index="${hotelIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <span class="section-counter">${hotelIndex + 1}</span>
                    فندق رقم ${hotelIndex + 1}
                </h6>
                <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">اسم الفندق</label>
                    <input type="text" class="form-control" name="hotels[${hotelIndex}][hotel_name]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المدينة</label>
                    <input type="text" class="form-control" name="hotels[${hotelIndex}][city]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">نوع الغرفة</label>
                    <input type="text" class="form-control" name="hotels[${hotelIndex}][room_type]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">عدد الغرف</label>
                    <input type="number" class="form-control" name="hotels[${hotelIndex}][rooms]" value="1" min="1">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">تاريخ الدخول</label>
                    <input type="date" class="form-control" name="hotels[${hotelIndex}][check_in]" 
                           onchange="calculateHotelNights(${hotelIndex})">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">تاريخ الخروج</label>
                    <input type="date" class="form-control" name="hotels[${hotelIndex}][check_out]" 
                           onchange="calculateHotelNights(${hotelIndex})">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">عدد الليالي</label>
                    <input type="number" class="form-control" name="hotels[${hotelIndex}][nights]" 
                           value="1" min="1" readonly id="hotelNights${hotelIndex}">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">عدد الضيوف</label>
                    <input type="number" class="form-control" name="hotels[${hotelIndex}][guests]" value="1" min="1">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">تكلفة الليلة</label>
                    <input type="number" class="form-control" name="hotels[${hotelIndex}][night_cost]" 
                           value="0" step="0.01" onchange="calculateHotelTotal(${hotelIndex})">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">سعر بيع الليلة</label>
                    <input type="number" class="form-control" name="hotels[${hotelIndex}][night_selling_price]" 
                           value="0" step="0.01" onchange="calculateHotelTotal(${hotelIndex})">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">إجمالي التكلفة</label>
                    <input type="number" class="form-control" name="hotels[${hotelIndex}][total_cost]" 
                           value="0" step="0.01" readonly id="hotelTotalCost${hotelIndex}">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">إجمالي البيع</label>
                    <input type="number" class="form-control" name="hotels[${hotelIndex}][total_selling_price]" 
                           value="0" step="0.01" readonly id="hotelTotalSelling${hotelIndex}">
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                    <select class="form-select" name="hotels[${hotelIndex}][currency]" onchange="calculateHotelTotal(${hotelIndex})">
                        <option value="KWD">دينار كويتي</option>
                        <option value="SAR">ريال سعودي</option>
                        <option value="USD">دولار أمريكي</option>
                        <option value="EUR">يورو</option>
                    </select>
                </div>
                <div class="col-md-2 mt-3">
                    <label class="form-label">الربح</label>
                    <div class="profit-display" id="hotelProfit${hotelIndex}">0.00</div>
                    <input type="hidden" name="hotels[${hotelIndex}][profit]" value="0" id="hotelProfitInput${hotelIndex}">
                </div>
                <div class="col-md-4 mt-3">
                    <label class="form-label">فاوتشر الفندق</label>
                    <input type="file" class="form-control" name="hotels[${hotelIndex}][voucher_file]" 
                           accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                    <small class="text-muted">PDF, JPG, PNG (أقصى 5 ميجا)</small>
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" name="hotels[${hotelIndex}][notes]" rows="2"></textarea>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', section);
            hotelIndex++;
        }

        // دالة حذف قسم
        function removeSection(button) {
            if (confirm('هل أنت متأكد من حذف هذا القسم؟')) {
                const section = button.closest('.dynamic-section');
                const sectionType = section.classList.contains('visa-section') ? 'visas' :
                    section.classList.contains('flight-section') ? 'flights' :
                    section.classList.contains('transport-section') ? 'transports' :
                    section.classList.contains('hotel-section') ? 'hotels' :
                    section.classList.contains('land-trip-section') ? 'land_trips' : '';

                section.remove();
                updateSectionCounters();

                // إظهار empty state إذا لم تعد هناك أقسام
                const containerMap = {
                    'visas': 'visasContainer',
                    'flights': 'flightsContainer',
                    'transports': 'transportsContainer',
                    'hotels': 'hotelsContainer',
                    'land_trips': 'landTripsContainer'
                };

                if (sectionType && containerMap[sectionType]) {
                    const container = document.getElementById(containerMap[sectionType]);
                    const sections = container.querySelectorAll('.dynamic-section');

                    if (sections.length === 0) {
                        const emptyStateMessages = {
                            'visas': {
                                icon: 'fas fa-passport',
                                text: 'لا توجد تأشيرات مضافة. اضغط "إضافة تأشيرة" لبدء الإضافة.'
                            },
                            'flights': {
                                icon: 'fas fa-plane',
                                text: 'لا توجد رحلات مضافة. اضغط "إضافة رحلة" لبدء الإضافة.'
                            },
                            'transports': {
                                icon: 'fas fa-bus',
                                text: 'لا توجد وسائل نقل مضافة. اضغط "إضافة نقل" لبدء الإضافة.'
                            },
                            'hotels': {
                                icon: 'fas fa-hotel',
                                text: 'لا توجد فنادق مضافة. اضغط "إضافة فندق" لبدء الإضافة.'
                            },
                            'land_trips': {
                                icon: 'fas fa-mountain',
                                text: 'لا توجد رحلات برية مضافة. اضغط "إضافة رحلة برية" لبدء الإضافة.'
                            }
                        };

                        const message = emptyStateMessages[sectionType];
                        const emptyState = `
                    <div class="text-center text-muted py-4">
                        <i class="${message.icon} fa-3x mb-3"></i>
                        <p>${message.text}</p>
                    </div>
                `;
                        container.insertAdjacentHTML('beforeend', emptyState);
                    }
                }
            }
        }

        // دالة تحديث العدادات
        function updateSectionCounters() {
            // تحديث عدادات التأشيرات
            document.querySelectorAll('.visa-section').forEach((section, index) => {
                const counter = section.querySelector('.section-counter');
                if (counter) counter.textContent = index + 1;

                const title = section.querySelector('h6');
                if (title) title.innerHTML =
                    `<span class="section-counter">${index + 1}</span>تأشيرة رقم ${index + 1}`;
            });

            // تحديث عدادات الرحلات
            document.querySelectorAll('.flight-section').forEach((section, index) => {
                const counter = section.querySelector('.section-counter');
                if (counter) counter.textContent = index + 1;

                const title = section.querySelector('h6');
                if (title) title.innerHTML =
                    `<span class="section-counter">${index + 1}</span>رحلة رقم ${index + 1}`;
            });

            // تحديث عدادات النقل
            document.querySelectorAll('.transport-section').forEach((section, index) => {
                const counter = section.querySelector('.section-counter');
                if (counter) counter.textContent = index + 1;

                const title = section.querySelector('h6');
                if (title) title.innerHTML =
                    `<span class="section-counter">${index + 1}</span>نقل رقم ${index + 1}`;
            });

            // تحديث عدادات الفنادق
            document.querySelectorAll('.hotel-section').forEach((section, index) => {
                const counter = section.querySelector('.section-counter');
                if (counter) counter.textContent = index + 1;

                const title = section.querySelector('h6');
                if (title) title.innerHTML =
                    `<span class="section-counter">${index + 1}</span>فندق رقم ${index + 1}`;
            });
            // تحديث عدادات الرحلات البرية
            document.querySelectorAll('.land-trip-section').forEach((section, index) => {
                const counter = section.querySelector('.section-counter');
                if (counter) counter.textContent = index + 1;

                const title = section.querySelector('h6');
                if (title) title.innerHTML =
                    `<span class="section-counter">${index + 1}</span>رحلة برية رقم ${index + 1}`;
            });
        }

        // دوال حساب الأرباح
        function calculateVisaProfit(index) {
            const quantity = parseInt(document.querySelector(`input[name="visas[${index}][quantity]"]`)?.value) || 1;
            const cost = parseFloat(document.querySelector(`input[name="visas[${index}][cost]"]`)?.value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`input[name="visas[${index}][selling_price]"]`)
                ?.value) || 0;

            // ✅ حساب الربح لكل تأشيرة
            const profitPerVisa = sellingPrice - cost;

            // ✅ حساب إجمالي الربح مع مراعاة العدد
            const totalProfit = profitPerVisa * quantity;

            // عرض الربح وتخزينه - تصحيح معرفات العناصر
            const profitDisplay = document.getElementById(`visaProfit${index}`);
            const profitInput = document.getElementById(`visaProfitInput${index}`);

            if (profitDisplay) profitDisplay.textContent = totalProfit.toFixed(2);
            if (profitInput) profitInput.value = totalProfit.toFixed(2);

            // إعادة حساب الإجمالي
            // calculateTotals(); // أضف هذا السطر إذا كنت تريد تحديث المجموع الكلي
        }

        // دوال حساب الأرباح للطيران    
        function calculateFlightProfit(index) {
            const passengers = parseInt(document.querySelector(`input[name="flights[${index}][passengers]"]`)?.value) || 1;
            const cost = parseFloat(document.querySelector(`input[name="flights[${index}][cost]"]`)?.value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`input[name="flights[${index}][selling_price]"]`)
                ?.value) || 0;

            // ✅ حساب الربح لكل مسافر
            const profitPerPassenger = sellingPrice - cost;

            // ✅ حساب إجمالي الربح مع مراعاة عدد المسافرين
            const totalProfit = profitPerPassenger * passengers;

            // تصحيح معرفات العناصر
            const profitDisplay = document.getElementById(`flightProfit${index}`);
            const profitInput = document.getElementById(`flightProfitInput${index}`);

            if (profitDisplay) profitDisplay.textContent = totalProfit.toFixed(2);
            if (profitInput) profitInput.value = totalProfit.toFixed(2);
        }

        function calculateTransportProfit(index) {
            const cost = parseFloat(document.querySelector(`input[name="transports[${index}][cost]"]`)?.value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`input[name="transports[${index}][selling_price]"]`)
                ?.value) || 0;

            const profit = sellingPrice - cost;

            // تصحيح معرفات العناصر
            const profitDisplay = document.getElementById(`transportProfit${index}`);
            const profitInput = document.getElementById(`transportProfitInput${index}`);

            if (profitDisplay) profitDisplay.textContent = profit.toFixed(2);
            if (profitInput) profitInput.value = profit.toFixed(2);
        }

        function calculateHotelNights(index) {
            const checkInInput = document.querySelector(`input[name="hotels[${index}][check_in]"]`);
            const checkOutInput = document.querySelector(`input[name="hotels[${index}][check_out]"]`);
            const nightsInput = document.getElementById(`hotelNights${index}`);

            if (checkInInput && checkOutInput && nightsInput) {
                const checkIn = new Date(checkInInput.value);
                const checkOut = new Date(checkOutInput.value);

                if (checkIn && checkOut && checkOut > checkIn) {
                    const diffTime = Math.abs(checkOut - checkIn);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    nightsInput.value = diffDays;

                    // إعادة حساب المجموع
                    calculateHotelTotal(index);
                }
            }
        }

        function calculateHotelTotal(index) {
            const nights = parseFloat(document.getElementById(`hotelNights${index}`)?.value) || 0;
            const rooms = parseFloat(document.querySelector(`input[name="hotels[${index}][rooms]"]`)?.value) || 0;
            const nightCost = parseFloat(document.querySelector(`input[name="hotels[${index}][night_cost]"]`)?.value) || 0;
            const nightSellingPrice = parseFloat(document.querySelector(
                `input[name="hotels[${index}][night_selling_price]"]`)?.value) || 0;

            const totalCost = nights * rooms * nightCost;
            const totalSelling = nights * rooms * nightSellingPrice;
            const profit = totalSelling - totalCost;

            const totalCostInput = document.getElementById(`hotelTotalCost${index}`);
            const totalSellingInput = document.getElementById(`hotelTotalSelling${index}`);
            const profitDisplay = document.getElementById(`hotelProfit${index}`);
            const profitInput = document.getElementById(`hotelProfitInput${index}`);

            if (totalCostInput) totalCostInput.value = totalCost.toFixed(2);
            if (totalSellingInput) totalSellingInput.value = totalSelling.toFixed(2);
            if (profitDisplay) profitDisplay.textContent = profit.toFixed(2);
            if (profitInput) profitInput.value = profit.toFixed(2);
        }

        // التحقق من صحة النموذج قبل الإرسال
        document.getElementById('operationReportForm').addEventListener('submit', function(e) {
            // التحقق من البيانات الأساسية
            const clientName = document.getElementById('client_name').value.trim();
            const reportDate = document.getElementById('report_date').value;

            if (!clientName) {
                e.preventDefault();
                alert('يرجى إدخال اسم العميل');
                document.getElementById('client_name').focus();
                return;
            }

            if (!reportDate) {
                e.preventDefault();
                alert('يرجى إدخال تاريخ التقرير');
                document.getElementById('report_date').focus();
                return;
            }

            // التحقق من وجود قسم واحد على الأقل
            const visaSections = document.querySelectorAll('.visa-section').length;
            const flightSections = document.querySelectorAll('.flight-section').length;
            const transportSections = document.querySelectorAll('.transport-section').length;
            const hotelSections = document.querySelectorAll('.hotel-section').length;
            const landTripSections = document.querySelectorAll('.land-trip-section').length;

            const totalSections = visaSections + flightSections + transportSections + hotelSections +
                landTripSections;

            if (totalSections === 0) {
                e.preventDefault();
                alert('يجب إضافة قسم واحد على الأقل (تأشيرة، رحلة، نقل، فندق، أو رحلة برية)');
                return;
            }

            // إظهار مؤشر التحميل
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
            submitBtn.disabled = true;

            // إذا فشل الإرسال، إرجاع الزر إلى حالته الأصلية
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000); // إرجاع الزر بعد 10 ثواني كحد أقصى
        });

        // تشغيل العمليات الحسابية عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            // حساب الأرباح للأقسام الموجودة
            document.querySelectorAll('.visa-section').forEach((section, index) => {
                calculateVisaProfit(index);
            });

            document.querySelectorAll('.flight-section').forEach((section, index) => {
                calculateFlightProfit(index);
            });

            document.querySelectorAll('.transport-section').forEach((section, index) => {
                calculateTransportProfit(index);
            });

            document.querySelectorAll('.hotel-section').forEach((section, index) => {
                calculateHotelTotal(index);
            });
            // حساب الأرباح للرحلات البرية
            document.querySelectorAll('.land-trip-section').forEach((section, index) => {
                calculateLandTripTotal(index);
            });

            // تحديث العدادات
            updateSectionCounters();
        });

        // دالة تأكيد المغادرة في حالة وجود تغييرات غير محفوظة
        let formChanged = false;

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('operationReportForm');
            const inputs = form.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    formChanged = true;
                });
            });

            // التحقق عند مغادرة الصفحة
            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = 'هناك تغييرات غير محفوظة. هل أنت متأكد من المغادرة؟';
                }
            });

            // إزالة التحقق عند إرسال النموذج
            form.addEventListener('submit', () => {
                formChanged = false;
            });
        });

        // دالة معاينة الملفات المرفوعة
        function previewFile(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // يمكن إضافة منطق معاينة الملف هنا
                    console.log('تم تحميل الملف:', file.name);
                };
                reader.readAsDataURL(file);
            }
        }

        // إضافة معاينة للملفات
        document.addEventListener('change', function(e) {
            if (e.target.type === 'file') {
                previewFile(e.target);
            }
        });
    </script>
    <script>
        // إضافة المتغير العام للعدّاد
        let landTripIndex = {{ $operationReport->landTrips->count() }};

        // الدالة الأساسية لتحميل بيانات الحجز المرتبط
        function loadLinkedBookingInfo() {
            console.log("بدء تحميل بيانات الحجز المرتبط");

            @if ($operationReport->booking_id && $operationReport->booking_type)
                console.log("معلومات الحجز:", {
                    id: {{ $operationReport->id }},
                    booking_id: {{ $operationReport->booking_id }},
                    booking_type: "{{ $operationReport->booking_type }}"
                });

                const reportId = {{ $operationReport->id }};
                const url = `/admin/operation-reports/${reportId}/linked-booking-data`;

                console.log("عنوان طلب البيانات:", url);

                // التأكد من وجود عنصر المحتوى
                const bookingInfoContent = document.getElementById('bookingInfoContent');
                if (!bookingInfoContent) {
                    console.error("لم يتم العثور على عنصر bookingInfoContent");
                    return;
                }

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        console.log("استجابة الخادم:", response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log("بيانات الحجز المستلمة:", data);

                        if (data.success) {
                            currentBookingData = data.data;
                            displayBookingInfo(data.data, data.type);
                        } else {
                            bookingInfoContent.innerHTML =
                                '<p class="text-danger">خطأ في تحميل البيانات: ' + data.message + '</p>';
                        }
                    })
                    .catch(error => {
                        console.error("خطأ في تحميل بيانات الحجز:", error);
                        bookingInfoContent.innerHTML =
                            '<p class="text-danger">حدث خطأ في تحميل البيانات: ' + error + '</p>';
                    });
            @else
                console.log("لا يوجد حجز مرتبط بهذا التقرير");
                const bookingInfoContent = document.getElementById('bookingInfoContent');
                if (bookingInfoContent) {
                    bookingInfoContent.innerHTML = '<p class="text-warning">لا يوجد حجز مرتبط بهذا التقرير</p>';
                }
            @endif
        }

        // دالة عرض معلومات الحجز
        function displayBookingInfo(data, type) {
            const bookingInfoContent = document.getElementById('bookingInfoContent');
            if (!bookingInfoContent) return;

            let content = '';

            if (type === 'hotel') {
                // محتوى حجز الفندق (موجود بالفعل)
            } else if (type === 'land_trip') {
                content = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>اسم العميل:</strong> ${data.client_name}<br>
                            <strong>الرحلة:</strong> ${data.trip_title}<br>
                            <strong>نوع الرحلة:</strong> ${data.trip_type || 'غير محدد'}<br>
                            <strong>عدد الغرف:</strong> ${data.rooms}
                        </div>
                        <div class="col-md-6">
                            <strong>تاريخ المغادرة:</strong> ${data.departure_date}<br>
                            <strong>تاريخ العودة:</strong> ${data.return_date}<br>
                            <strong>عدد الأيام:</strong> ${data.days_count}
                        </div>
                        <div class="col-md-12 mt-2">
                            <strong>تكلفة الرحلة:</strong> ${data.cost_price} ${data.currency}<br>
                            <strong>سعر البيع:</strong> ${data.sale_price} ${data.currency}
                        </div>
                    </div>
                `;
            }

            bookingInfoContent.innerHTML = content;
        }

        // دالة فتح نافذة تعديل الحجز
        function editLinkedBooking() {
            if (!currentBookingData) {
                alert('لم يتم تحميل بيانات الحجز بعد');
                return;
            }

            const bookingEditContent = document.getElementById('bookingEditContent');
            if (!bookingEditContent) {
                console.error("لم يتم العثور على عنصر bookingEditContent");
                return;
            }

            const bookingType = '{{ $operationReport->booking_type }}';
            let formContent = '';

            if (bookingType.toLowerCase() === 'land_trip') {
                formContent = `
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">اسم العميل *</label>
                            <input type="text" class="form-control" name="client_name" value="${currentBookingData.client_name}" required>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label">عدد الغرف *</label>
                            <input type="number" class="form-control" name="rooms" value="${currentBookingData.rooms}" min="1" required>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label">العملة *</label>
                            <select class="form-select" name="currency" required>
                                <option value="KWD" ${currentBookingData.currency === 'KWD' ? 'selected' : ''}>دينار كويتي</option>
                                <option value="SAR" ${currentBookingData.currency === 'SAR' ? 'selected' : ''}>ريال سعودي</option>
                                <option value="USD" ${currentBookingData.currency === 'USD' ? 'selected' : ''}>دولار أمريكي</option>
                                <option value="EUR" ${currentBookingData.currency === 'EUR' ? 'selected' : ''}>يورو</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">تكلفة الرحلة *</label>
                            <input type="number" class="form-control" name="cost_price" value="${currentBookingData.cost_price}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">سعر البيع *</label>
                            <input type="number" class="form-control" name="sale_price" value="${currentBookingData.sale_price}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="alert alert-info">
                                <strong>معلومات الرحلة:</strong><br>
                                ${currentBookingData.trip_title}<br>
                                من ${currentBookingData.departure_date} إلى ${currentBookingData.return_date} (${currentBookingData.days_count} أيام)
                            </div>
                        </div>
                    </div>
                `;
            }

            bookingEditContent.innerHTML = formContent;

            // فتح النافذة
            const modalElement = document.getElementById('editBookingModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        // دالة إضافة قسم رحلة برية جديدة
        function addLandTripSection() {
            const container = document.getElementById('landTripsContainer');
            const emptyState = container.querySelector('.text-center.text-muted');
            if (emptyState) emptyState.remove();

            const section = `
        <div class="dynamic-section land-trip-section" data-index="${landTripIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <span class="section-counter">${landTripIndex + 1}</span>
                    رحلة برية رقم ${landTripIndex + 1}
                </h6>
                <button type="button" class="btn-remove-section" onclick="removeSection(this)">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">نوع الرحلة</label>
                    <input type="text" class="form-control" name="land_trips[${landTripIndex}][trip_type]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ المغادرة</label>
                    <input type="date" class="form-control" name="land_trips[${landTripIndex}][departure_date]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ العودة</label>
                    <input type="date" class="form-control" name="land_trips[${landTripIndex}][return_date]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">عدد الأيام</label>
                    <input type="number" class="form-control" name="land_trips[${landTripIndex}][days]" value="1" min="1">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">تكلفة النقل</label>
                    <input type="number" class="form-control" name="land_trips[${landTripIndex}][transport_cost]" value="0" 
                           step="0.01" onchange="calculateLandTripTotal(${landTripIndex})">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">تكلفة فندق مكة</label>
                    <input type="number" class="form-control" name="land_trips[${landTripIndex}][mecca_hotel_cost]" value="0" 
                           step="0.01" onchange="calculateLandTripTotal(${landTripIndex})">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">تكلفة فندق المدينة</label>
                    <input type="number" class="form-control" name="land_trips[${landTripIndex}][medina_hotel_cost]" value="0" 
                           step="0.01" onchange="calculateLandTripTotal(${landTripIndex})">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">تكاليف إضافية</label>
                    <input type="number" class="form-control" name="land_trips[${landTripIndex}][extra_costs]" value="0" 
                           step="0.01" onchange="calculateLandTripTotal(${landTripIndex})">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">إجمالي التكلفة</label>
                    <input type="number" class="form-control" name="land_trips[${landTripIndex}][total_cost]" value="0" 
                           step="0.01" readonly id="landTripTotalCost${landTripIndex}">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">سعر البيع</label>
                    <input type="number" class="form-control" name="land_trips[${landTripIndex}][selling_price]" value="0" 
                           step="0.01" onchange="calculateLandTripProfit(${landTripIndex})">
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">العملة <span class="currency-badge">مهم</span></label>
                    <select class="form-select" name="land_trips[${landTripIndex}][currency]" 
                            onchange="calculateLandTripProfit(${landTripIndex})">
                        <option value="KWD">دينار كويتي</option>
                        <option value="SAR">ريال سعودي</option>
                        <option value="USD">دولار أمريكي</option>
                        <option value="EUR">يورو</option>
                    </select>
                </div>
                <div class="col-md-3 mt-3">
                    <label class="form-label">الربح</label>
                    <div class="profit-display" id="landTripProfit${landTripIndex}">0.00</div>
                    <input type="hidden" name="land_trips[${landTripIndex}][profit]" value="0" 
                           id="landTripProfitInput${landTripIndex}">
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" name="land_trips[${landTripIndex}][notes]" rows="2"></textarea>
                </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', section);
            landTripIndex++;
        }

        // دالة حساب إجمالي تكلفة الرحلة البرية
        function calculateLandTripTotal(index) {
            const transportCost = parseFloat(document.querySelector(`input[name="land_trips[${index}][transport_cost]"]`)
                ?.value) || 0;
            const meccaHotelCost = parseFloat(document.querySelector(`input[name="land_trips[${index}][mecca_hotel_cost]"]`)
                ?.value) || 0;
            const medinaHotelCost = parseFloat(document.querySelector(
                `input[name="land_trips[${index}][medina_hotel_cost]"]`)?.value) || 0;
            const extraCosts = parseFloat(document.querySelector(`input[name="land_trips[${index}][extra_costs]"]`)
                ?.value) || 0;

            // حساب التكلفة الإجمالية
            const totalCost = transportCost + meccaHotelCost + medinaHotelCost + extraCosts;

            // عرض وتخزين التكلفة الإجمالية
            const totalCostInput = document.getElementById(`landTripTotalCost${index}`);
            if (totalCostInput) totalCostInput.value = totalCost.toFixed(2);

            // إعادة حساب الربح
            calculateLandTripProfit(index);
        }

        // دالة حساب ربح الرحلة البرية
        function calculateLandTripProfit(index) {
            const totalCost = parseFloat(document.getElementById(`landTripTotalCost${index}`)?.value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`input[name="land_trips[${index}][selling_price]"]`)
                ?.value) || 0;

            // حساب الربح
            const profit = sellingPrice - totalCost;

            // عرض وتخزين الربح
            const profitDisplay = document.getElementById(`landTripProfit${index}`);
            const profitInput = document.getElementById(`landTripProfitInput${index}`);

            if (profitDisplay) profitDisplay.textContent = profit.toFixed(2);
            if (profitInput) profitInput.value = profit.toFixed(2);
        }

        // تعديل دالة updateSectionCounters لتشمل الرحلات البرية
        function updateSectionCounters() {
            // كود الأقسام الأخرى هنا (موجود بالفعل)

            // تحديث عدادات الرحلات البرية
            document.querySelectorAll('.land-trip-section').forEach((section, index) => {
                const counter = section.querySelector('.section-counter');
                if (counter) counter.textContent = index + 1;

                const title = section.querySelector('h6');
                if (title) title.innerHTML =
                    `<span class="section-counter">${index + 1}</span>رحلة برية رقم ${index + 1}`;
            });
        }

        // إضافة حدث لمعالجة نموذج تعديل الحجز المرتبط
        document.addEventListener('DOMContentLoaded', function() {
            // تحميل بيانات الحجز المرتبط (إذا وُجد)
            if (document.getElementById('bookingInfoContent')) {
                loadLinkedBookingInfo();
            }

            // معالجة نموذج تعديل الحجز
            const editBookingForm = document.getElementById('editBookingForm');
            if (editBookingForm) {
                editBookingForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const reportId = {{ $operationReport->id }};
                    const url = `/admin/operation-reports/${reportId}/update-linked-booking`;

                    // إظهار مؤشر التحميل
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الحفظ...';
                    submitBtn.disabled = true;

                    fetch(url, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(response => {
                            return response.json();
                        })
                        .then(data => {
                            console.log("نتيجة التحديث:", data);

                            if (data.success) {
                                // إغلاق النافذة
                                const modal = bootstrap.Modal.getInstance(document.getElementById(
                                    'editBookingModal'));
                                if (modal) modal.hide();

                                // إعادة تحميل معلومات الحجز
                                loadLinkedBookingInfo();

                                // إظهار رسالة نجاح
                                alert('تم تحديث بيانات الحجز بنجاح');
                            } else {
                                alert('خطأ: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error("خطأ في حفظ التعديلات:", error);
                            alert('حدث خطأ أثناء حفظ التعديلات: ' + error);
                        })
                        .finally(() => {
                            // إرجاع الزر لحالته الأصلية
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        });
                });
            }
        });
    </script>
    <script>
        function showTotalProfit() {
            let total = 0;
            let breakdown = [];

            // أرباح التأشيرات
            document.querySelectorAll('input[id^="visaProfitInput"]').forEach(input => {
                const profit = parseFloat(input.value) || 0;
                if (profit !== 0) breakdown.push(`تأشيرات: <b>${profit.toFixed(2)}</b>`);
                total += profit;
            });
            // أرباح الطيران
            document.querySelectorAll('input[id^="flightProfitInput"]').forEach(input => {
                const profit = parseFloat(input.value) || 0;
                if (profit !== 0) breakdown.push(`طيران: <b>${profit.toFixed(2)}</b>`);
                total += profit;
            });
            // أرباح النقل
            document.querySelectorAll('input[id^="transportProfitInput"]').forEach(input => {
                const profit = parseFloat(input.value) || 0;
                if (profit !== 0) breakdown.push(`نقل: <b>${profit.toFixed(2)}</b>`);
                total += profit;
            });
            // أرباح الفنادق
            document.querySelectorAll('input[id^="hotelProfitInput"]').forEach(input => {
                const profit = parseFloat(input.value) || 0;
                if (profit !== 0) breakdown.push(`فنادق: <b>${profit.toFixed(2)}</b>`);
                total += profit;
            });
            // أرباح الرحلات البرية
            document.querySelectorAll('input[id^="landTripProfitInput"]').forEach(input => {
                const profit = parseFloat(input.value) || 0;
                if (profit !== 0) breakdown.push(`رحلات برية: <b>${profit.toFixed(2)}</b>`);
                total += profit;
            });

            document.getElementById('total-profit-display').textContent = total.toFixed(2);
            document.getElementById('total-profit-currency').textContent = ' دينار كويتي / ريال / ...';
            document.getElementById('profit-breakdown').innerHTML = breakdown.length ? breakdown.join('<br>') :
                '<small class="text-muted">لا يوجد أرباح بعد</small>';
        }

        // عندما يفتح الصفحة أو المستخدم يعدل أي قيمة
        document.addEventListener('DOMContentLoaded', function() {
            showTotalProfit();
            document.body.addEventListener('input', showTotalProfit);
            document.body.addEventListener('change', showTotalProfit);
        });
    </script>

    <script src="{{ asset('js/preventClick.js') }}"></script>
@endpush
