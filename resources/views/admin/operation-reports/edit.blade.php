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
                            <div class="mb-3">
                                <label for="client_email" class="form-label">بريد العميل الإلكتروني</label>
                                <input type="email" class="form-control @error('client_email') is-invalid @enderror"
                                    id="client_email" name="client_email"
                                    value="{{ old('client_email', $operationReport->client_email) }}">
                                @error('client_email')
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
                                <label for="company_phone" class="form-label">هاتف الشركة</label>
                                <input type="text" class="form-control @error('company_phone') is-invalid @enderror"
                                    id="company_phone" name="company_phone"
                                    value="{{ old('company_phone', $operationReport->company_phone) }}">
                                @error('company_phone')
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
                                <input type="text" class="form-control @error('booking_reference') is-invalid @enderror"
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
        </div>
    `;
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
                    section.classList.contains('hotel-section') ? 'hotels' : '';

                section.remove();
                updateSectionCounters();

                // إظهار empty state إذا لم تعد هناك أقسام
                const containerMap = {
                    'visas': 'visasContainer',
                    'flights': 'flightsContainer',
                    'transports': 'transportsContainer',
                    'hotels': 'hotelsContainer'
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

            const totalSections = visaSections + flightSections + transportSections + hotelSections;

            if (totalSections === 0) {
                e.preventDefault();
                alert('يجب إضافة قسم واحد على الأقل (تأشيرة، رحلة، نقل، أو فندق)');
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
    <script src="{{ asset('js/preventClick.js') }}"></script>
@endpush
