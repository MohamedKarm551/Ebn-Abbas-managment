{{-- filepath: resources/views/admin/operation-reports/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة تقرير عمليات جديد')

@push('styles')
    <style>
        :root {
            --main-blue: #2563eb;
            --main-green: #10b981;
            --main-orange: #f59e0b;
            --main-red: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-900: #111827;
            --radius: 18px;
            --shadow: 0 2px 12px rgba(44, 62, 80, 0.08);
        }

        body,
        html {
            background: var(--gray-50) !important;
            direction: rtl;
        }

        .form-container {
            background: var(--gray-50);
            padding: 2.5rem 0;
            min-height: 100vh;
        }

        .form-card,
        .section-card,
        .totals-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            padding: 2rem 1.5rem;
            border: 1px solid var(--gray-200);
        }

        .form-header {
            border-bottom: 1px solid var(--gray-100);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--main-blue);
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-header {
            background: linear-gradient(90deg, var(--main-blue), #4f8cfb 80%);
            border-radius: var(--radius) var(--radius) 0 0;
            color: #fff;
            padding: 0.75rem 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .section-title {
            font-size: 1.2rem;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .section-content {
            padding: 0.5rem 0 0 0;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.3rem;
            display: block;
            font-size: 1rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            min-height: 45px;
            font-size: 1rem;
            background: var(--gray-100);
            transition: box-shadow .15s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--main-blue);
            box-shadow: 0 0 0 2px #2563eb22;
        }

        .btn.btn-add {
            background: var(--main-green);
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 7px;
            padding: 0.45rem 1.1rem;
            font-size: 1rem;
            transition: 0.18s;
        }

        .btn.btn-add:hover {
            background: #059669;
        }

        .totals-card {
            padding: 2.2rem 1.5rem;
            background: linear-gradient(120deg, var(--main-green) 60%, var(--main-blue) 100%);
            color: #fff;
            border: none;
            box-shadow: var(--shadow);
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.1rem 2.5rem;
            margin-bottom: 2rem;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 500;
            background: #ffffff22;
            padding: 0.7rem 1.1rem;
            border-radius: 10px;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.3rem;
            font-weight: bold;
            background: #fff;
            color: var(--main-blue);
            padding: 1rem 1.4rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px #1e293b15;
        }

        @media (max-width: 991px) {
            .totals-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {

            .form-card,
            .section-card,
            .totals-card {
                padding: 1rem 0.7rem;
            }

            .form-title {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="form-container">
        <div class="container-fluid">
            <form action="{{ route('admin.operation-reports.store') }}" method="POST" id="operationReportForm"
                enctype="multipart/form-data">
                @csrf

                <!-- Header -->
                <div class="form-card">
                    <div class="form-header">
                        <h1 class="form-title">
                            <i class="fas fa-chart-line"></i>
                            إضافة تقرير عمليات جديد
                        </h1>
                    </div>
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_name" class="form-label">الموظف المسؤول</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_date" class="form-label">تاريخ التقرير</label>
                                    <input type="date" name="report_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- بيانات العميل والحجز -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-user"></i>
                            بيانات العميل والحجز
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="recent_bookings" class="form-label">اختيار من آخر الحجوزات</label>
                                    <select id="recent_bookings" class="form-control form-select">
                                        <option value="">-- اختر حجز موجود --</option>
                                        @foreach ($recentBookings as $booking)
                                            <option value="{{ $booking['id'] }}" data-type="{{ $booking['type'] }}"
                                                data-client-name="{{ $booking['client_name'] }}"
                                                data-company-name="{{ $booking['company']['name'] ?? '' }}"
                                                data-company-phone="{{ $booking['company']['phone'] ?? '' }}">
                                                {{ $booking['display_text'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">أو ابحث عن عميل</label>
                                    <div class="position-relative">
                                        <input type="text" id="client_search" class="form-control"
                                            placeholder="ابحث باسم العميل...">
                                        <div id="client_results" class="autocomplete-results" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_name" class="form-label">اسم العميل *</label>
                                    <input type="text" name="client_name" id="client_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_phone" class="form-label">رقم هاتف العميل</label>
                                    <input type="text" name="client_phone" id="client_phone" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_email" class="form-label">البريد الإلكتروني للعميل</label>
                                    <input type="email" name="client_email" id="client_email" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <input type="text" name="notes" id="notes" class="form-control">
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_name" class="form-label">اسم الشركة</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_phone" class="form-label">رقم هاتف الشركة</label>
                                    <input type="text" name="company_phone" id="company_phone" class="form-control">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="booking_type" id="booking_type">
                        <input type="hidden" name="booking_id" id="booking_id">
                    </div>
                </div>

                <!-- حسابات التأشيرات -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-passport"></i>
                            حسابات التأشيرات
                        </h3>
                        <button type="button" class="btn btn-add" onclick="addVisaSection()">
                            <i class="fas fa-plus"></i> إضافة تأشيرة
                        </button>
                    </div>
                    <div class="section-content">
                        <div id="visas_container">
                            <!-- سيتم إضافة أقسام التأشيرات هنا -->
                        </div>
                    </div>
                </div>

                <!-- حسابات الطيران -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-plane"></i>
                            حسابات الطيران
                        </h3>
                        <button type="button" class="btn btn-add" onclick="addFlightSection()">
                            <i class="fas fa-plus"></i> إضافة رحلة طيران
                        </button>
                    </div>
                    <div class="section-content">
                        <div id="flights_container">
                            <!-- سيتم إضافة أقسام الطيران هنا -->
                        </div>
                    </div>
                </div>

                <!-- حسابات النقل -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-bus"></i>
                            حسابات النقل
                        </h3>
                        <button type="button" class="btn btn-add" onclick="addTransportSection()">
                            <i class="fas fa-plus"></i> إضافة وسيلة نقل
                        </button>
                    </div>
                    <div class="section-content">
                        <div id="transports_container">
                            <!-- سيتم إضافة أقسام النقل هنا -->
                        </div>
                    </div>
                </div>

                <!-- حسابات الفنادق -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-hotel"></i>
                            حسابات الفنادق
                        </h3>
                        <button type="button" class="btn btn-add" onclick="addHotelSection()">
                            <i class="fas fa-plus"></i> إضافة فندق
                        </button>
                    </div>
                    <div class="section-content">
                        <div id="hotels_container">
                            <!-- سيتم إضافة أقسام الفنادق هنا -->
                        </div>
                    </div>
                </div>

                <!-- حسابات الرحلات البرية -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-mountain"></i>
                            حسابات الرحلات البرية
                        </h3>
                        <button type="button" class="btn btn-add" onclick="addLandTripSection()">
                            <i class="fas fa-plus"></i> إضافة رحلة برية
                        </button>
                    </div>
                    <div class="section-content">
                        <div id="land_trips_container">
                            <!-- سيتم إضافة أقسام الرحلات البرية هنا -->
                        </div>
                    </div>
                </div>

                <!-- إجمالي الأرباح -->
                <div class="totals-card">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-calculator me-2"></i>
                        إجمالي الأرباح حسب العملة
                    </h3>

                    <div class="grand-total">
                        <div class="grand-total-label">الإجمالي الكلي</div>
                        <div class="grand-total-value" id="grand_total_profit">0.00 د.ك</div>
                    </div>
                </div>

                <!-- ملاحظات -->
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-sticky-note"></i>
                            ملاحظات إضافية
                        </h3>
                    </div>
                    <div class="section-content">
                        <div class="form-group">
                            <textarea name="notes" class="form-control" rows="4" placeholder="أضف أي ملاحظات إضافية هنا..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- أزرار التحكم -->
                <div class="d-flex gap-3 justify-content-center mb-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save"></i>
                        حفظ التقرير
                    </button>
                    <a href="{{ route('admin.operation-reports.index') }}" class="btn btn-outline btn-lg">
                        <i class="fas fa-times"></i>
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // متغيرات العدادات
        let visaIndex = 0;
        let flightIndex = 0;
        let transportIndex = 0;
        let hotelIndex = 0;
        let landTripIndex = 0;

        // وظائف إضافة الأقسام
        function addVisaSection() {
            const container = document.getElementById('visas_container');
            const html = `
        <div class="dynamic-section visa-section" data-index="${visaIndex}">
            <button type="button" class="btn btn-remove remove-section" onclick="removeSection(this)">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">نوع التأشيرة</label>
                    <select name="visas[${visaIndex}][visa_type]" class="form-control form-select">
                        <option value="">اختر النوع</option>
                        <option value="سياحية">سياحية</option>
                        <option value="عمرة">عمرة</option>
                        <option value="زيارة">زيارة</option>
                        <option value="عمل">عمل</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">عدد التأشيرات</label>
                    <input type="number" 
                           name="visas[${visaIndex}][quantity]" 
                           class="form-control" 
                           value="1" 
                           min="1"
                           onchange="calculateVisaProfit(${visaIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">تكلفة التأشيرة الواحدة</label>
                    <input type="number" 
                           name="visas[${visaIndex}][cost]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateVisaProfit(${visaIndex})">
                </div>
            </div>
            
            <div class=" ">
                <div class="form-group">
                    <label class="form-label">سعر بيع التأشيرة   الواحدة "دينار كويتي " </label>
                    <input type="number" 
                           name="visas[${visaIndex}][selling_price]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateVisaProfit(${visaIndex})">
                </div>
                <br>
                <div class="form-group">
                    <label class="form-label">الربح الإجمالي</label>
                    <div class="profit-display" id="visa_profit_${visaIndex}">0.00</div>
                    <input type="hidden" name="visas[${visaIndex}][profit]" id="visa_profit_input_${visaIndex}">
                </div>
                 <!-- إضافة حقل العملة -->
    <div class="form-group mt-2">
        <label class="form-label">العملة</label>
        <select name="visas[${visaIndex}][currency]" class="form-control form-select">
            <option value="KWD" selected>دينار كويتي (KWD)</option>
            <option value="SAR">ريال سعودي (SAR)</option>
            <option value="USD">دولار أمريكي (USD)</option>
            <option value="EUR">يورو (EUR)</option>
        </select>
    </div>
                    <!-- إضافة حقل الملاحظات -->
        <div class="form-group">
            <label class="form-label">ملاحظات التأشيرة</label>
            <textarea name="visas[${visaIndex}][notes]" class="form-control" rows="2" 
                     placeholder="أي ملاحظات إضافية عن التأشيرة..."></textarea>
        </div>
           
    `;
            container.insertAdjacentHTML('beforeend', html);
            visaIndex++;
        }

        function addFlightSection() {
            const container = document.getElementById('flights_container');
            const html = `
        <div class="dynamic-section flight-section" data-index="${flightIndex}">
            <button type="button" class="btn btn-remove remove-section" onclick="removeSection(this)">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">تاريخ الرحلة</label>
                    <input type="date" 
                           name="flights[${flightIndex}][flight_date]" 
                           class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">رقم الرحلة</label>
                    <input type="text" 
                           name="flights[${flightIndex}][flight_number]" 
                           class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">شركة الطيران</label>
                    <input type="text" 
                           name="flights[${flightIndex}][airline]" 
                           class="form-control">
                </div>
            </div>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">المسار</label>
                    <input type="text" 
                           name="flights[${flightIndex}][route]" 
                           class="form-control" 
                           placeholder="من - إلى">
                </div>
                <div class="form-group">
                    <label class="form-label">عدد المسافرين</label>
                    <input type="number" 
                           name="flights[${flightIndex}][passengers]" 
                           class="form-control" 
                           value="1" 
                           min="1"
                           onchange="calculateFlightProfit(${flightIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">نوع الرحلة</label>
                    <select name="flights[${flightIndex}][trip_type]" class="form-control form-select">
                        <option value="ذهاب وعودة">ذهاب وعودة</option>
                        <option value="ذهاب فقط">ذهاب فقط</option>
                    </select>
                </div>
            </div>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">تكلفة الطيران</label>
                    <input type="number" 
                           name="flights[${flightIndex}][cost]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateFlightProfit(${flightIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">سعر بيع الطيران</label>
                    <input type="number" 
                           name="flights[${flightIndex}][selling_price]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateFlightProfit(${flightIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">الربح</label>
                    <div class="profit-display" id="flight_profit_${flightIndex}">0.00</div>
                    <input type="hidden" name="flights[${flightIndex}][profit]" id="flight_profit_input_${flightIndex}">
                </div>
                  <!-- إضافة حقل العملة -->
    <div class="form-group mt-2">
        <label class="form-label">العملة</label>
        <select name="flights[${flightIndex}][currency]" class="form-control form-select">
            <option value="KWD" selected>دينار كويتي (KWD)</option>
            <option value="SAR">ريال سعودي (SAR)</option>
            <option value="USD">دولار أمريكي (USD)</option>
            <option value="EUR">يورو (EUR)</option>
        </select>
    </div>
                  <!-- إضافة حقل الملاحظات -->
    <div class="form-group mt-3">
        <label class="form-label">ملاحظات الرحلة</label>
        <textarea name="flights[${flightIndex}][notes]" class="form-control" rows="2" 
                 placeholder="أي ملاحظات إضافية عن الرحلة..."></textarea>
    </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            flightIndex++;
        }

        function addTransportSection() {
            const container = document.getElementById('transports_container');
            const html = `
        <div class="dynamic-section transport-section" data-index="${transportIndex}">
            <button type="button" class="btn btn-remove remove-section" onclick="removeSection(this)">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">نوع النقل</label>
                    <input type="text" 
                           name="transports[${transportIndex}][transport_type]" 
                           class="form-control" 
                           placeholder="باص، سيارة، قطار...">
                </div>
                <div class="form-group">
                    <label class="form-label">اسم السائق</label>
                    <input type="text" 
                           name="transports[${transportIndex}][driver_name]" 
                           class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">رقم السائق</label>
                    <input type="text" 
                           name="transports[${transportIndex}][driver_phone]" 
                           class="form-control">
                </div>
            </div>
                <!-- إضافة معلومات المركبة -->
    <div class="form-group">
        <label class="form-label">معلومات المركبة</label>
        <input type="text" 
               name="transports[${transportIndex}][vehicle_info]" 
               class="form-control"
               placeholder="رقم اللوحة، نوع المركبة، اللون...">
    </div>

            
            <div class="input-group">
                <div class="form-group">
                    <label class="form-label">موعد الانطلاق</label>
                    <input type="datetime-local" 
                           name="transports[${transportIndex}][departure_time]" 
                           class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">موعد الوصول</label>
                    <input type="datetime-local" 
                           name="transports[${transportIndex}][arrival_time]" 
                           class="form-control">
                </div>
            </div>
                <!-- إضافة ملاحظات المواعيد -->
    <div class="form-group">
        <label class="form-label">ملاحظات المواعيد</label>
        <textarea name="transports[${transportIndex}][schedule_notes]" 
                  class="form-control" 
                  rows="2"
                  placeholder="معلومات إضافية عن المواعيد، نقاط التوقف..."></textarea>
    </div>

            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">تكلفة النقل</label>
                    <input type="number" 
                           name="transports[${transportIndex}][cost]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateTransportProfit(${transportIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">سعر بيع النقل</label>
                    <input type="number" 
                           name="transports[${transportIndex}][selling_price]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateTransportProfit(${transportIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">الربح</label>
                    <div class="profit-display" id="transport_profit_${transportIndex}">0.00</div>
                    <input type="hidden" name="transports[${transportIndex}][profit]" id="transport_profit_input_${transportIndex}">
                </div>
                  <!-- إضافة حقل العملة -->
    <div class="form-group mt-2">
        <label class="form-label">العملة</label>
        <select name="transports[${transportIndex}][currency]" class="form-control form-select">

            <option value="KWD" selected>دينار كويتي (KWD)</option>
            <option value="SAR">ريال سعودي (SAR)</option>
            <option value="USD">دولار أمريكي (USD)</option>
            <option value="EUR">يورو (EUR)</option>
        </select>
    </div>
                    <!-- إضافة مرفق التذكرة -->
    <div class="form-group mt-3">
        <label class="form-label">مرفق التذكرة</label>
        <input type="file" 
               name="transports[${transportIndex}][ticket_file]" 
               class="form-control">
    </div>
    
    <!-- إضافة ملاحظات عامة -->
    <div class="form-group mt-3">
        <label class="form-label">ملاحظات إضافية</label>
        <textarea name="transports[${transportIndex}][notes]" 
                  class="form-control" 
                  rows="2"
                  placeholder="أي ملاحظات إضافية عن النقل..."></textarea>
    </div>

            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            transportIndex++;
        }

        function addHotelSection() {
            const container = document.getElementById('hotels_container');
            const html = `
        <div class="dynamic-section hotel-section" data-index="${hotelIndex}">
            <button type="button" class="btn btn-remove remove-section" onclick="removeSection(this)">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">اسم الفندق</label>
                    <input type="text" 
                           name="hotels[${hotelIndex}][hotel_name]" 
                           class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">تاريخ الدخول</label>
                    <input type="date" 
                           name="hotels[${hotelIndex}][check_in]" 
                           class="form-control"
                           onchange="calculateHotelNights(${hotelIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">تاريخ الخروج</label>
                    <input type="date" 
                           name="hotels[${hotelIndex}][check_out]" 
                           class="form-control"
                           onchange="calculateHotelNights(${hotelIndex})">
                </div>
            </div>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">عدد الليالي</label>
                    <input type="number" 
                           name="hotels[${hotelIndex}][nights]" 
                           class="form-control" 
                           min="1"
                           readonly
                           id="hotel_nights_${hotelIndex}">
                </div>

                <div class="form-group">
                    <label class="form-label">عدد الغرف</label>
                    <input type="number" 
                           name="hotels[${hotelIndex}][rooms]" 
                           class="form-control" 
                           value="1" 
                           min="1"
                           onchange="calculateHotelProfit(${hotelIndex})">
                </div>
                <div class="form-group">
            <label class="form-label">عدد النزلاء</label>
            <input type="number" 
                   name="hotels[${hotelIndex}][guests]" 
                   class="form-control" 
                   value="1" 
                   min="1">
        </div>
                <div class="form-group">
                    <label class="form-label">نوع الغرفة</label>
                    <input type="text" 
                           name="hotels[${hotelIndex}][room_type]" 
                           class="form-control" 
                           placeholder="مفرد، مزدوج، جناح...">
                </div>
            </div>
            
            <div class="input-group">
                <div class="form-group">
                    <label class="form-label">سعر بيع الليلة الواحدة</label>
                    <input type="number" 
                           name="hotels[${hotelIndex}][night_selling_price]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateHotelProfit(${hotelIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">تكلفة الليلة الواحدة</label>
                    <input type="number" 
                           name="hotels[${hotelIndex}][night_cost]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateHotelProfit(${hotelIndex})">
                </div>
            </div>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">إجمالي سعر البيع</label>
                    <div class="profit-display" id="hotel_total_selling_${hotelIndex}">0.00</div>
                    <input type="hidden" name="hotels[${hotelIndex}][total_selling_price]" id="hotel_total_selling_input_${hotelIndex}">
                </div>
                <div class="form-group">
                    <label class="form-label">إجمالي التكلفة</label>
                    <div class="profit-display" id="hotel_total_cost_${hotelIndex}">0.00</div>
                    <input type="hidden" name="hotels[${hotelIndex}][total_cost]" id="hotel_total_cost_input_${hotelIndex}">
                </div>
                <div class="form-group">
                    <label class="form-label">الربح من الفندق</label>
                    <div class="profit-display" id="hotel_profit_${hotelIndex}">0.00</div>
                    <input type="hidden" name="hotels[${hotelIndex}][profit]" id="hotel_profit_input_${hotelIndex}">
                </div>
                  <!-- إضافة حقل العملة -->
    <div class="form-group mt-2">
        <label class="form-label">العملة</label>
        <select name="hotels[${hotelIndex}][currency]" class="form-control form-select">

            <option value="KWD" selected>دينار كويتي (KWD)</option>
            <option value="SAR">ريال سعودي (SAR)</option>
            <option value="USD">دولار أمريكي (USD)</option>
            <option value="EUR">يورو (EUR)</option>
        </select>
    </div>
                 <!-- إضافة مرفق الفاوتشر -->
    <div class="form-group mt-3">
         <label for="hotels[${hotelIndex}][voucher_file]" class="form-label">
                        مرفق الفاوتشر
                    </label>
                    <input type="file" 
                           class="form-control" 
                           name="hotels[${hotelIndex}][voucher_file]" 
                           accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                    <small class="text-muted">يمكن رفع ملفات PDF أو صور (أقصى حجم: 5MB)</small>
    </div>
    
    <!-- إضافة حقل الملاحظات -->
    <div class="form-group mt-3">
        <label class="form-label">ملاحظات الفندق</label>
        <textarea name="hotels[${hotelIndex}][notes]" class="form-control" rows="2" 
                 placeholder="أي ملاحظات إضافية عن الفندق..."></textarea>
    </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            hotelIndex++;
        }

        function addLandTripSection() {
            const container = document.getElementById('land_trips_container');
            const html = `
        <div class="dynamic-section land-trip-section" data-index="${landTripIndex}">
            <button type="button" class="btn btn-remove remove-section" onclick="removeSection(this)">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">نوع الرحلة</label>
                    <input type="text" 
                           name="land_trips[${landTripIndex}][trip_type]" 
                           class="form-control" 
                           placeholder="عمرة، حج، سياحة...">
                </div>
                <div class="form-group">
                    <label class="form-label">تاريخ الانطلاق</label>
                    <input type="date" 
                           name="land_trips[${landTripIndex}][departure_date]" 
                           class="form-control"
                           onchange="calculateLandTripDays(${landTripIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">تاريخ العودة</label>
                    <input type="date" 
                           name="land_trips[${landTripIndex}][return_date]" 
                           class="form-control"
                           onchange="calculateLandTripDays(${landTripIndex})">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">مدة الرحلة (أيام)</label>
                <input type="number" 
                       name="land_trips[${landTripIndex}][days]" 
                       class="form-control" 
                       readonly
                       id="land_trip_days_${landTripIndex}">
            </div>
            
            <div class="input-group">
                <div class="form-group">
                    <label class="form-label">تكلفة النقل</label>
                    <input type="number" 
                           name="land_trips[${landTripIndex}][transport_cost]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateLandTripProfit(${landTripIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">تكلفة فندق مكة</label>
                    <input type="number" 
                           name="land_trips[${landTripIndex}][mecca_hotel_cost]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateLandTripProfit(${landTripIndex})">
                </div>
            </div>
            
            <div class="input-group">
                <div class="form-group">
                    <label class="form-label">تكلفة فندق المدينة</label>
                    <input type="number" 
                           name="land_trips[${landTripIndex}][medina_hotel_cost]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateLandTripProfit(${landTripIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">تكاليف إضافية</label>
                    <input type="number" 
                           name="land_trips[${landTripIndex}][extra_costs]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateLandTripProfit(${landTripIndex})">
                </div>
                  <!-- إضافة حقل العملة -->
    <div class="form-group mt-2">
        <label class="form-label">العملة</label>
        <select name="land_trips[${landTripIndex}][currency]" class="form-control form-select">

            <option value="KWD" selected>دينار كويتي (KWD)</option>
            <option value="SAR">ريال سعودي (SAR)</option>
            <option value="USD">دولار أمريكي (USD)</option>
            <option value="EUR">يورو (EUR)</option>
        </select>
    </div>
            </div>
            
            <div class="input-group-3">
                <div class="form-group">
                    <label class="form-label">سعر البيع</label>
                    <input type="number" 
                           name="land_trips[${landTripIndex}][selling_price]" 
                           class="form-control" 
                           step="0.01" 
                           min="0"
                           onchange="calculateLandTripProfit(${landTripIndex})">
                </div>
                <div class="form-group">
                    <label class="form-label">إجمالي التكلفة</label>
                    <div class="profit-display" id="land_trip_total_cost_${landTripIndex}">0.00</div>
                    <input type="hidden" name="land_trips[${landTripIndex}][total_cost]" id="land_trip_total_cost_input_${landTripIndex}">
                </div>
                <div class="form-group">
                    <label class="form-label">الربح من الرحلة</label>
                    <div class="profit-display" id="land_trip_profit_${landTripIndex}">0.00</div>
                    <input type="hidden" name="land_trips[${landTripIndex}][profit]" id="land_trip_profit_input_${landTripIndex}">
                </div>
                   <!-- إضافة حقل الملاحظات -->
    <div class="form-group mt-3">
        <label class="form-label">ملاحظات الرحلة البرية</label>
        <textarea name="land_trips[${landTripIndex}][notes]" class="form-control" rows="2" 
                 placeholder="أي ملاحظات إضافية عن الرحلة..."></textarea>
    </div>
            </div>
        </div>
    `;
            container.insertAdjacentHTML('beforeend', html);
            landTripIndex++;
        }

        // وظائف حساب الأرباح
        function calculateVisaProfit(index) {
            const cost = parseFloat(document.querySelector(`[name="visas[${index}][cost]"]`).value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`[name="visas[${index}][selling_price]"]`).value) || 0;
            const quantity = parseFloat(document.querySelector(`[name="visas[${index}][quantity]"]`).value) || 1;

            const profit = (sellingPrice - cost) * quantity;

            document.getElementById(`visa_profit_${index}`).textContent = profit.toFixed(2);
            document.getElementById(`visa_profit_input_${index}`).value = profit.toFixed(2);

            calculateTotals();
        }

        function calculateFlightProfit(index) {
            const cost = parseFloat(document.querySelector(`[name="flights[${index}][cost]"]`).value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`[name="flights[${index}][selling_price]"]`).value) || 0;

            const profit = sellingPrice - cost;

            document.getElementById(`flight_profit_${index}`).textContent = profit.toFixed(2);
            document.getElementById(`flight_profit_input_${index}`).value = profit.toFixed(2);

            calculateTotals();
        }

        function calculateTransportProfit(index) {
            const cost = parseFloat(document.querySelector(`[name="transports[${index}][cost]"]`).value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`[name="transports[${index}][selling_price]"]`).value) ||
                0;

            const profit = sellingPrice - cost;

            document.getElementById(`transport_profit_${index}`).textContent = profit.toFixed(2);
            document.getElementById(`transport_profit_input_${index}`).value = profit.toFixed(2);

            calculateTotals();
        }

        function calculateHotelNights(index) {
            const checkIn = new Date(document.querySelector(`[name="hotels[${index}][check_in]"]`).value);
            const checkOut = new Date(document.querySelector(`[name="hotels[${index}][check_out]"]`).value);

            if (checkIn && checkOut && checkOut > checkIn) {
                const timeDiff = checkOut.getTime() - checkIn.getTime();
                const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
                document.getElementById(`hotel_nights_${index}`).value = nights;
                document.querySelector(`[name="hotels[${index}][nights]"]`).value = nights;
                calculateHotelProfit(index);
            }
        }

        function calculateHotelProfit(index) {
            const nightSellingPrice = parseFloat(document.querySelector(`[name="hotels[${index}][night_selling_price]"]`)
                .value) || 0;
            const nightCost = parseFloat(document.querySelector(`[name="hotels[${index}][night_cost]"]`).value) || 0;
            const nights = parseFloat(document.querySelector(`[name="hotels[${index}][nights]"]`).value) || 0;
            const rooms = parseFloat(document.querySelector(`[name="hotels[${index}][rooms]"]`).value) || 1;

            const totalSellingPrice = nightSellingPrice * nights * rooms;
            const totalCost = nightCost * nights * rooms;
            const profit = totalSellingPrice - totalCost;

            document.getElementById(`hotel_total_selling_${index}`).textContent = totalSellingPrice.toFixed(2);
            document.getElementById(`hotel_total_selling_input_${index}`).value = totalSellingPrice.toFixed(2);

            document.getElementById(`hotel_total_cost_${index}`).textContent = totalCost.toFixed(2);
            document.getElementById(`hotel_total_cost_input_${index}`).value = totalCost.toFixed(2);

            document.getElementById(`hotel_profit_${index}`).textContent = profit.toFixed(2);
            document.getElementById(`hotel_profit_input_${index}`).value = profit.toFixed(2);

            calculateTotals();
        }

        function calculateLandTripDays(index) {
            const departureDate = new Date(document.querySelector(`[name="land_trips[${index}][departure_date]"]`).value);
            const returnDate = new Date(document.querySelector(`[name="land_trips[${index}][return_date]"]`).value);

            if (departureDate && returnDate && returnDate > departureDate) {
                const timeDiff = returnDate.getTime() - departureDate.getTime();
                const days = Math.ceil(timeDiff / (1000 * 3600 * 24));
                document.getElementById(`land_trip_days_${index}`).value = days;
                document.querySelector(`[name="land_trips[${index}][days]"]`).value = days;
            }
        }

        function calculateLandTripProfit(index) {
            const transportCost = parseFloat(document.querySelector(`[name="land_trips[${index}][transport_cost]"]`)
                .value) || 0;
            const meccaHotelCost = parseFloat(document.querySelector(`[name="land_trips[${index}][mecca_hotel_cost]"]`)
                .value) || 0;
            const medinaHotelCost = parseFloat(document.querySelector(`[name="land_trips[${index}][medina_hotel_cost]"]`)
                .value) || 0;
            const extraCosts = parseFloat(document.querySelector(`[name="land_trips[${index}][extra_costs]"]`).value) || 0;
            const sellingPrice = parseFloat(document.querySelector(`[name="land_trips[${index}][selling_price]"]`).value) ||
                0;

            const totalCost = transportCost + meccaHotelCost + medinaHotelCost + extraCosts;
            const profit = sellingPrice - totalCost;

            document.getElementById(`land_trip_total_cost_${index}`).textContent = totalCost.toFixed(2);
            document.getElementById(`land_trip_total_cost_input_${index}`).value = totalCost.toFixed(2);

            document.getElementById(`land_trip_profit_${index}`).textContent = profit.toFixed(2);
            document.getElementById(`land_trip_profit_input_${index}`).value = profit.toFixed(2);

            calculateTotals();
        }

        // تحديث دالة حساب الإجماليات لدعم العملات المختلفة
        function calculateTotals() {
            // تجميع الأرباح حسب العملة
            let profitsByCurrency = {
                'KWD': 0,
                'SAR': 0,
                'USD': 0,
                'EUR': 0
            };

            // حساب أرباح التأشيرات
            document.querySelectorAll('.visa-section').forEach((section, index) => {
                const profit = parseFloat(document.getElementById(`visa_profit_input_${index}`)?.value) || 0;
                const currency = document.querySelector(`[name="visas[${index}][currency]"]`)?.value || 'KWD';
                profitsByCurrency[currency] += profit;
            });

            // حساب أرباح الطيران
            document.querySelectorAll('.flight-section').forEach((section, index) => {
                const profit = parseFloat(document.getElementById(`flight_profit_input_${index}`)?.value) || 0;
                const currency = document.querySelector(`[name="flights[${index}][currency]"]`)?.value || 'KWD';
                profitsByCurrency[currency] += profit;
            });

            // حساب أرباح النقل
            document.querySelectorAll('.transport-section').forEach((section, index) => {
                const profit = parseFloat(document.getElementById(`transport_profit_input_${index}`)?.value) || 0;
                const currency = document.querySelector(`[name="transports[${index}][currency]"]`)?.value || 'KWD';
                profitsByCurrency[currency] += profit;
            });

            // حساب أرباح الفنادق
            document.querySelectorAll('.hotel-section').forEach((section, index) => {
                const profit = parseFloat(document.getElementById(`hotel_profit_input_${index}`)?.value) || 0;
                const currency = document.querySelector(`[name="hotels[${index}][currency]"]`)?.value || 'KWD';
                profitsByCurrency[currency] += profit;
            });

            // حساب أرباح الرحلات البرية
            document.querySelectorAll('.land-trip-section').forEach((section, index) => {
                const profit = parseFloat(document.getElementById(`land_trip_profit_input_${index}`)?.value) || 0;
                const currency = document.querySelector(`[name="land_trips[${index}][currency]"]`)?.value || 'KWD';
                profitsByCurrency[currency] += profit;
            });

            // عرض الإجماليات حسب العملة
            let totalDisplay = '';
            Object.keys(profitsByCurrency).forEach(currency => {
                if (profitsByCurrency[currency] > 0) {
                    const currencySymbol = currency === 'KWD' ? 'د.ك' :
                        currency === 'SAR' ? 'ر.س' :
                        currency === 'USD' ? '$' : '€';
                    totalDisplay += `${profitsByCurrency[currency].toFixed(2)} ${currencySymbol}<br>`;
                }
            });

            document.getElementById('grand_total_profit').innerHTML = totalDisplay || '0.00 د.ك';
        }
        // اختيار حجز فعلي : 
        document.getElementById('recent_bookings').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            // التحقق من وجود خيار محدد
            if (!selectedOption || !selectedOption.value) {
                console.log('لم يتم اختيار حجز صالح');
                return;
            }

            console.log('تم اختيار حجز:', selectedOption.value);
            console.log('نوع الحجز:', selectedOption.dataset.type);

            try {
                // ملء البيانات الأساسية مع التحقق من وجود العناصر
                const clientNameField = document.getElementById('client_name');
                const companyNameField = document.getElementById('company_name');
                const companyPhoneField = document.getElementById('company_phone');
                const bookingTypeField = document.getElementById('booking_type');
                const bookingIdField = document.getElementById('booking_id');

                if (clientNameField) clientNameField.value = selectedOption.dataset.clientName || '';
                if (companyNameField) companyNameField.value = selectedOption.dataset.companyName || '';
                if (companyPhoneField) companyPhoneField.value = selectedOption.dataset.companyPhone || '';
                if (bookingTypeField) bookingTypeField.value = selectedOption.dataset.type || '';
                if (bookingIdField) bookingIdField.value = selectedOption.value;

                // التحقق من صحة البيانات المطلوبة
                const bookingType = selectedOption.dataset.type;
                const bookingId = selectedOption.value;

                if (!bookingType || !bookingId) {
                    console.error('بيانات الحجز غير مكتملة:', {
                        bookingType,
                        bookingId
                    });
                    alert('بيانات الحجز غير مكتملة. يرجى المحاولة مرة أخرى.');
                    return;
                }

                // عرض مؤشر التحميل (إذا كان موجوداً)
                const loadingIndicator = document.getElementById('loading_indicator');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'block';
                }

                console.log('جاري استعلام بيانات الحجز...');

                // استعلام AJAX مع معالجة شاملة للأخطاء
                fetch(`{{ route('admin.operation-reports.get-booking-details') }}?type=${encodeURIComponent(bookingType)}&id=${encodeURIComponent(bookingId)}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(response => {
                        console.log('استجابة الخادم:', response.status, response.statusText);

                        // إخفاء مؤشر التحميل
                        if (loadingIndicator) {
                            loadingIndicator.style.display = 'none';
                        }

                        if (!response.ok) {
                            throw new Error(`خطأ في الخادم: ${response.status} - ${response.statusText}`);
                        }

                        return response.json();
                    })
                    .then(data => {
                        console.log('البيانات المستلمة:', data);

                        if (!data || typeof data !== 'object') {
                            throw new Error('استجابة غير صالحة من الخادم');
                        }

                        if (data.success) {
                            // معالجة البيانات حسب نوع الحجز
                            if (data.type === 'hotel' && data.hotelData) {
                                handleHotelBookingData(data.hotelData);
                            } else if (data.type === 'land_trip' && data.landTripData) {
                                handleLandTripBookingData(data.landTripData);
                            } else {
                                console.warn('نوع حجز غير مدعوم أو بيانات مفقودة:', data.type);
                                alert(`نوع الحجز "${data.type}" غير مدعوم حالياً`);
                            }
                        } else {
                            const errorMessage = data.message || 'خطأ غير محدد من الخادم';
                            console.error('فشل في الاستعلام:', errorMessage);
                            alert(`فشل في جلب بيانات الحجز: ${errorMessage}`);
                        }
                    })
                    .catch(error => {
                        console.error('خطأ في استعلام بيانات الحجز:', error);

                        // إخفاء مؤشر التحميل في حالة الخطأ
                        if (loadingIndicator) {
                            loadingIndicator.style.display = 'none';
                        }

                        // رسالة خطأ مفصلة للمستخدم
                        let userMessage = 'حدث خطأ أثناء جلب بيانات الحجز.';

                        if (error.name === 'TypeError') {
                            userMessage += ' تحقق من اتصال الإنترنت.';
                        } else if (error.message.includes('404')) {
                            userMessage += ' البيانات المطلوبة غير موجودة.';
                        } else if (error.message.includes('500')) {
                            userMessage += ' خطأ في الخادم. يرجى المحاولة لاحقاً.';
                        }

                        alert(userMessage);
                    });

            } catch (error) {
                console.error('خطأ عام في معالجة اختيار الحجز:', error);
                alert('حدث خطأ غير متوقع. يرجى إعادة تحميل الصفحة والمحاولة مرة أخرى.');
            }
        });

        // دالة معالجة بيانات حجز الفندق
        function handleHotelBookingData(hotelData) {
            try {
                console.log('معالجة بيانات الفندق...');

                // إضافة قسم فندق جديد
                addHotelSection();

                // الحصول على الفهرس الحالي
                const currentIndex = hotelIndex - 1;
                console.log('فهرس الفندق الحالي:', currentIndex);

                // انتظار قصير لضمان إنشاء العناصر في DOM
                setTimeout(() => {
                    try {
                        // مصفوفة العناصر المطلوب ملؤها
                        const fieldsToFill = [{
                                selector: `[name="hotels[${currentIndex}][hotel_name]"]`,
                                value: hotelData.hotel_name,
                                label: 'اسم الفندق'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][check_in]"]`,
                                value: hotelData.check_in,
                                label: 'تاريخ الدخول'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][check_out]"]`,
                                value: hotelData.check_out,
                                label: 'تاريخ الخروج'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][nights]"]`,
                                value: hotelData.nights || 1,
                                label: 'عدد الليالي'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][rooms]"]`,
                                value: hotelData.rooms || 1,
                                label: 'عدد الغرف'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][guests]"]`,
                                value: hotelData.guests || 1,
                                label: 'عدد النزلاء'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][room_type]"]`,
                                value: hotelData.room_type,
                                label: 'نوع الغرفة'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][night_cost]"]`,
                                value: hotelData.night_cost || 0,
                                label: 'تكلفة الليلة'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][night_selling_price]"]`,
                                value: hotelData.night_selling_price || 0,
                                label: 'سعر بيع الليلة'
                            },
                            {
                                selector: `[name="hotels[${currentIndex}][currency]"]`,
                                value: hotelData.currency || 'KWD',
                                label: 'العملة'
                            }
                        ];

                        let filledFields = 0;
                        let failedFields = [];

                        // ملء كل حقل مع التحقق من وجوده
                        fieldsToFill.forEach(field => {
                            const element = document.querySelector(field.selector);
                            if (element) {
                                element.value = field.value || '';
                                filledFields++;
                                console.log(`تم ملء ${field.label}:`, field.value);
                            } else {
                                failedFields.push(field.label);
                                console.warn(`لم يتم العثور على حقل: ${field.label} (${field.selector})`);
                            }
                        });

                        console.log(`تم ملء ${filledFields} حقل من أصل ${fieldsToFill.length}`);

                        if (failedFields.length > 0) {
                            console.warn('الحقول التي فشل ملؤها:', failedFields);
                        }

                        // حساب التكلفة الإجمالية والربح
                        if (typeof calculateHotelProfit === 'function') {
                            calculateHotelProfit(currentIndex);
                            console.log('تم حساب أرباح الفندق');
                        } else {
                            console.error('دالة calculateHotelProfit غير موجودة');
                        }

                        // إشعار المستخدم بنجاح العملية
                        console.log('تم ملء بيانات الفندق بنجاح');

                    } catch (fillError) {
                        console.error('خطأ أثناء ملء بيانات الفندق:', fillError);
                        alert('تم جلب البيانات ولكن حدث خطأ أثناء ملء النموذج. يرجى المحاولة يدوياً.');
                    }
                }, 150); // زيادة وقت الانتظار قليلاً

            } catch (error) {
                console.error('خطأ في معالجة بيانات الفندق:', error);
                alert('حدث خطأ أثناء معالجة بيانات الفندق.');
            }
        }

        // دالة معالجة بيانات الرحلة البرية
        function handleLandTripBookingData(landTripData) {
            try {
                console.log('معالجة بيانات الرحلة البرية...');

                // إضافة قسم رحلة برية جديد
                addLandTripSection();

                // الحصول على الفهرس الحالي
                const currentIndex = landTripIndex - 1;
                console.log('فهرس الرحلة البرية الحالي:', currentIndex);

                // انتظار قصير لضمان إنشاء العناصر في DOM
                setTimeout(() => {
                    try {
                        // مصفوفة العناصر المطلوب ملؤها
                        const fieldsToFill = [{
                                selector: `[name="land_trips[${currentIndex}][trip_type]"]`,
                                value: landTripData.trip_type,
                                label: 'نوع الرحلة'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][departure_date]"]`,
                                value: landTripData.departure_date,
                                label: 'تاريخ الانطلاق'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][return_date]"]`,
                                value: landTripData.return_date,
                                label: 'تاريخ العودة'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][days]"]`,
                                value: landTripData.days || 1,
                                label: 'عدد الأيام'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][selling_price]"]`,
                                value: landTripData.selling_price || 0,
                                label: 'سعر البيع'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][transport_cost]"]`,
                                value: landTripData.transport_cost || 0,
                                label: 'تكلفة النقل'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][mecca_hotel_cost]"]`,
                                value: landTripData.mecca_hotel_cost || 0,
                                label: 'تكلفة فندق مكة'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][medina_hotel_cost]"]`,
                                value: landTripData.medina_hotel_cost || 0,
                                label: 'تكلفة فندق المدينة'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][extra_costs]"]`,
                                value: landTripData.extra_costs || 0,
                                label: 'تكاليف إضافية'
                            },
                            {
                                selector: `[name="land_trips[${currentIndex}][currency]"]`,
                                value: landTripData.currency || 'KWD',
                                label: 'العملة'
                            }
                        ];

                        let filledFields = 0;
                        let failedFields = [];

                        // ملء كل حقل مع التحقق من وجوده
                        fieldsToFill.forEach(field => {
                            const element = document.querySelector(field.selector);
                            if (element) {
                                element.value = field.value || '';
                                filledFields++;
                                console.log(`تم ملء ${field.label}:`, field.value);
                            } else {
                                failedFields.push(field.label);
                                console.warn(`لم يتم العثور على حقل: ${field.label} (${field.selector})`);
                            }
                        });

                        console.log(`تم ملء ${filledFields} حقل من أصل ${fieldsToFill.length}`);

                        if (failedFields.length > 0) {
                            console.warn('الحقول التي فشل ملؤها:', failedFields);
                        }

                        // حساب التكلفة الإجمالية والربح
                        if (typeof calculateLandTripProfit === 'function') {
                            calculateLandTripProfit(currentIndex);
                            console.log('تم حساب أرباح الرحلة البرية');
                        } else {
                            console.error('دالة calculateLandTripProfit غير موجودة');
                        }

                        // إشعار المستخدم بنجاح العملية
                        console.log('تم ملء بيانات الرحلة البرية بنجاح');

                    } catch (fillError) {
                        console.error('خطأ أثناء ملء بيانات الرحلة البرية:', fillError);
                        alert('تم جلب البيانات ولكن حدث خطأ أثناء ملء النموذج. يرجى المحاولة يدوياً.');
                    }
                }, 150); // زيادة وقت الانتظار قليلاً

            } catch (error) {
                console.error('خطأ في معالجة بيانات الرحلة البرية:', error);
                alert('حدث خطأ أثناء معالجة بيانات الرحلة البرية.');
            }
        }
        // حذف قسم
        function removeSection(button) {
            const section = button.closest('.dynamic-section');
            section.remove();
            calculateTotals();
        }




        // البحث عن العملاء
        let searchTimeout;
        document.getElementById('client_search').addEventListener('input', function() {
            const query = this.value;
            const resultsDiv = document.getElementById('client_results');

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(
                        `{{ route('admin.operation-reports.api.clients.search') }}?q=${encodeURIComponent(query)}`
                    )
                    .then(response => response.json())
                    .then(data => {
                        if (data.clients && data.clients.length > 0) {
                            let html = '';
                            data.clients.forEach(client => {
                                html += `
                            <div class="autocomplete-item" onclick="selectClient('${client.name}', '${client.phone || ''}')">
                                <strong>${client.name}</strong>
                                ${client.phone ? `<br><small>${client.phone}</small>` : ''}
                            </div>
                        `;
                            });
                            resultsDiv.innerHTML = html;
                            resultsDiv.style.display = 'block';
                        } else {
                            resultsDiv.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultsDiv.style.display = 'none';
                    });
            }, 300);
        });

        function selectClient(name, phone) {
            document.getElementById('client_name').value = name;
            document.getElementById('client_phone').value = phone;
            document.getElementById('client_results').style.display = 'none';
            document.getElementById('client_search').value = name;

            // جلب آخر حجز للعميل
            fetchClientLatestBooking(name);
        }

        function fetchClientLatestBooking(clientName) {
            fetch(`{{ route('admin.operation-reports.api.client.latest-booking', ['name' => 'PLACEHOLDER']) }}`.replace(
                    'PLACEHOLDER', encodeURIComponent(clientName))).then(response => response.json())
                .then(data => {
                    if (data.latest_booking) {
                        const booking = data.latest_booking;
                        document.getElementById('company_name').value = booking.company?.name || '';
                        document.getElementById('company_phone').value = booking.company?.phone || '';
                        document.getElementById('booking_type').value = booking.type || '';
                        document.getElementById('booking_id').value = booking.booking?.id || '';
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // إخفاء نتائج البحث عند النقر خارجها
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#client_search') && !e.target.closest('#client_results')) {
                document.getElementById('client_results').style.display = 'none';
            }
        });

        // التحقق من صحة النموذج قبل الإرسال
        document.getElementById('operationReportForm').addEventListener('submit', function(e) {
            const clientName = document.getElementById('client_name').value.trim();

            if (!clientName) {
                e.preventDefault();
                alert('يجب إدخال اسم العميل');
                document.getElementById('client_name').focus();
                return;
            }

            // التأكد من وجود بيانات في أحد الأقسام على الأقل
            const hasData =
                document.querySelectorAll('.visa-section').length > 0 ||
                document.querySelectorAll('.flight-section').length > 0 ||
                document.querySelectorAll('.transport-section').length > 0 ||
                document.querySelectorAll('.hotel-section').length > 0 ||
                document.querySelectorAll('.land-trip-section').length > 0;

            if (!hasData) {
                e.preventDefault();
                alert('يجب إضافة بيانات في أحد الأقسام على الأقل');
                return;
            }
        });

        // إضافة أقسام افتراضية
        document.addEventListener('DOMContentLoaded', function() {
            // التحقق من وجود تفضيل وضع سابق
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);

                // تحديث حالة زر التبديل
                if (savedTheme === 'dark') {
                    document.getElementById('darkModeSwitch').checked = true;
                }
            }

            // تبديل الوضع عند النقر على الزر
            document.getElementById('darkModeSwitch').addEventListener('change', function() {
                if (this.checked) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                }
            });

            // إضافة قسم تأشيرة واحد افتراضياً
            addVisaSection();
        });
    </script>
    <script src="{{ asset('js/preventClick.js') }}"></script>
@endpush
