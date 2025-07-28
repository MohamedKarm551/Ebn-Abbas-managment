@extends('layouts.app')
@section('title', 'حجوزات ' . $company->name)
@section('content')
    <div class="container">
        <h1>حجوزات {{ $company->name }}</h1>
        <!-- زر جديد لتحميل كشف الحساب PDF يدعم العربي 100% -->
       <a href="{{ route('company.bookings.pdf', $company->id) }}" class="btn btn-danger mb-3" target="_blank">
    <i class="fas fa-file-pdf"></i> تحميل كشف الحساب PDF
</a>
        {{-- @php
        $count = $bookings->count();
        $totalDue = $bookings->sum('due_to_agent');
    @endphp --}}
        <a href="{{ route('reports.company.payments', $company->id) }}" class="w-25 p-2 mt-2 mb-2 btn btn-primary btn-sm">كشف
            الحساب</a>
        <button type="button" class="w-25 p-2 mt-2 mb-2 btn btn-success btn-sm" data-bs-toggle="modal"
            data-bs-target="#paymentModal{{ $company->id }}">
            تسجيل دفعة
        </button>
        <div class="modal fade" id="paymentModal{{ $company->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('reports.company.payment') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                        <input type="hidden" name="is_discount" id="is-discount-{{ $company->id }}" value="0">

                        <div class="modal-header">
                            <h5 class="modal-title">تسجيل دفعة - {{ $company->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">المبلغ المدفوع والعملة</label>
                                <div> قم بعمل سند قبض لهذه العملية : <a href="{{ route('admin.receipt.voucher') }}"
                                        target="_blank">إنشاء سند
                                        قبض</a></div>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
                                    <select class="form-select" name="currency" style="max-width: 120px;">
                                        <option value="SAR" selected>ريال سعودي</option>
                                        <option value="KWD">دينار كويتي</option>
                                    </select>
                                </div>
                            </div>
                            {{-- *** أضف حقل رفع الملف مشكلة مع جوجل درايف لسه هتتحل  *** --}}
                            {{-- <div class="mb-3">
                                    <label for="receipt_file_company_{{ $company->id }}" class="form-label">إرفاق إيصال
                                        (اختياري)
                                    </label>
                                    <input class="form-control" type="file"
                                        id="receipt_file_company_{{ $company->id }}" name="receipt_file">
                                  
                                <small class="form-text text-muted">الملفات المسموحة: JPG, PNG, PDF (بحد أقصى
                                    5MB)</small>
                            </div> --}}
                            {{-- *** نهاية حقل رفع الملف *** --}}
                            <div class="mb-3">
                                <label class="form-label">ملاحظات <br>
                                    (إن كانت معك صورة من التحويل ارفعها على درايف وضع الرابط هنا)
                                </label>
                                <textarea class="form-control" name="notes"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                            <button type="button" class="btn btn-warning" id="toggleDiscountBtn-{{ $company->id }}"
                                onclick="toggleDiscountMode({{ $company->id }})">تسجيل خصم</button>
                            <button type="submit" class="btn btn-primary" id="submitBtn-{{ $company->id }}">تسجيل
                                الدفعة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="alert alert-info">
            <strong>ملخص الحساب:</strong><br>
            عدد الحجوزات: {{ $dueCount }}<br>
            @foreach ($totalDueByCurrency as $currency => $amount)
                إجمالي المستحق ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
            @endforeach
            <div style="font-weight: bold;text-decoration: underline;">
                @foreach ($totalPaidByCurrency as $currency => $amount)
                    المدفوع ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
                @endforeach
            </div>
            @foreach ($totalRemainingByCurrency as $currency => $amount)
                المتبقي ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
            @endforeach
            <small>المعادلة: (عدد الليالي الكلي × عدد الغرف × سعر البيع) ∑ لكل الحجوزات</small>
        </div>

        <div class=" mb-4">
            <div class="card-body">
                <div id="reportContent">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="companyBookingsTable" data-type="company">
                            <thead>
                                <tr>
                                    <th style="width: 1%;" class="d-table-cell d-md-none"></th>
                                    <th>#</th>
                                    <th></th>
                                    <th>العميل</th>
                                    <th class="d-none d-md-table-cell">جهة الحجز</th>
                                    <th class="d-none d-md-table-cell">الفندق</th>
                                    <th class="d-none d-md-table-cell">تاريخ الدخول</th>
                                    <th class="d-none d-md-table-cell">تاريخ الخروج</th>
                                    <th class="d-none d-md-table-cell">عدد الغرف</th>
                                    <th class="d-none d-md-table-cell">السعر</th>
                                    <th>الكلي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $key => $booking)
                                    <tr class="booking-main-row">
                                        <td class="d-table-cell d-md-none text-center">
                                            <button class="btn btn-sm btn-outline-secondary toggle-details-btn"
                                                data-bs-toggle="collapse" data-bs-target="#details-{{ $booking->id }}"
                                                aria-expanded="false" aria-controls="details-{{ $booking->id }}">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @include('partials._booking_checkbox', [
                                                'booking' => $booking,
                                                'amountDueField' => 'due_to_company',
                                                'amountPaidField' => 'company_paid',
                                                'costPriceField' => 'sale_price',
                                            ])
                                        </td>
                                        <td>
                                            {{ $booking->client_name }}
                                            <br>
                                            <span class="block text-muted small">
                                                المبلغ المدفوع:
                                                {{ number_format(optional($booking->financialTracking)->company_payment_amount ?? 0, 2) }}
                                                <br>
                                                حالة الدفع:
                                                @php
                                                    $status =
                                                        optional($booking->financialTracking)->company_payment_status ??
                                                        'not_paid';
                                                    $statusText =
                                                        [
                                                            'fully_paid' => 'مدفوع بالكامل',
                                                            'partially_paid' => 'مدفوع جزئياً',
                                                            'not_paid' => 'غير مدفوع',
                                                            'paid' => 'مدفوع', // لو في عندك حالة باسم paid
                                                            'unpaid' => 'غير مدفوع',
                                                        ][$status] ?? 'غير محدد';
                                                @endphp
                                                {{ $statusText }}
                                            </span>
                                            @if (!empty($booking->notes))
                                                <i class="fas fa-info-circle text-primary ms-2 fs-5 p-1"
                                                    data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                    data-bs-placement="auto" title="ملاحظات"
                                                    data-bs-content="{{ e($booking->notes) }}">
                                                </i>
                                            @endif
                                        </td>

                                        <td class="d-none d-md-table-cell">{{ $booking->agent->name ?? 'غير محدد' }}</td>
                                        <td class="d-none d-md-table-cell">{{ $booking->hotel->name }}</td>
                                        <td class="d-none d-md-table-cell">{{ $booking->check_in->format('d/m/Y') }}</td>
                                        <td class="d-none d-md-table-cell">{{ $booking->check_out->format('d/m/Y') }}</td>
                                        <td class="d-none d-md-table-cell">{{ $booking->rooms }}</td>
                                        <td class="d-none d-md-table-cell">
                                            {{ number_format($booking->sale_price, 2) }}
                                            {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                        </td>

                                        <!-- تعديل عرض الإجمالي في عرض الكمبيوتر والموبايل -->
                                        <td>
                                            {{ number_format($booking->total_company_due, 2) }}
                                            {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                        </td>
                                    </tr>
                                    <tr class="collapse booking-details-row d-md-none" id="details-{{ $booking->id }}">
                                        <td colspan="5">
                                            <div class="p-2 bg-light border rounded">
                                                <strong>التفاصيل:</strong><br>
                                                <ul class="list-unstyled mb-0 small">
                                                    <li><strong>جهة الحجز:</strong>
                                                        {{ $booking->agent->name ?? 'غير محدد' }}
                                                    </li>
                                                    <li><strong>الفندق:</strong> {{ $booking->hotel->name }}</li>
                                                    <li><strong>الدخول:</strong> {{ $booking->check_in->format('d/m/Y') }}
                                                    </li>
                                                    <li><strong>الخروج:</strong> {{ $booking->check_out->format('d/m/Y') }}
                                                    </li>
                                                    <li><strong>الغرف:</strong> {{ $booking->rooms }}</li>
                                                    <li>
                                                        <strong>السعر:</strong>
                                                        {{ number_format($booking->sale_price, 2) }}
                                                        {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <button class="btn btn-primary" id="companySelectRangeBtn">تحديد النطاق</button>
                <button class="btn btn-secondary" id="companyResetRangeBtn">إعادة تعيين النطاق</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingSelector('companyBookingsTable', 'companySelectRangeBtn', 'companyResetRangeBtn');

            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                    return new bootstrap.Popover(popoverTriggerEl, {
                        html: true
                    });
                }
                return null;
            }).filter(Boolean);

            const detailCollapseElements = document.querySelectorAll('.booking-details-row');
            detailCollapseElements.forEach(el => {
                el.addEventListener('show.bs.collapse', event => {
                    const triggerButton = document.querySelector(
                        `button[data-bs-target="#${event.target.id}"] i`);
                    if (triggerButton) {
                        triggerButton.classList.remove('fa-plus');
                        triggerButton.classList.add('fa-minus');
                    }
                });

                el.addEventListener('hide.bs.collapse', event => {
                    const triggerButton = document.querySelector(
                        `button[data-bs-target="#${event.target.id}"] i`);
                    if (triggerButton) {
                        triggerButton.classList.remove('fa-minus');
                        triggerButton.classList.add('fa-plus');
                    }
                });
            });
        });
    </script>
    <script>
        // سكريبت الخصم : 
        function toggleAgentDiscountMode(agentId) {
            const isDiscountField = document.getElementById('is-discount-' + agentId);
            const submitBtn = document.getElementById('agentSubmitBtn-' + agentId);
            const toggleBtn = document.getElementById('toggleAgentDiscountBtn-' + agentId);
            const modalTitle = document.querySelector('#agentPaymentModalTitle' + agentId);
            const agentName = modalTitle.textContent.split('-')[1].trim();

            if (isDiscountField.value === "0") {
                // تحويل إلى وضع الخصم
                isDiscountField.value = "1";
                submitBtn.textContent = "تطبيق الخصم";
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-warning');
                toggleBtn.textContent = "تسجيل دفعة";
                modalTitle.textContent = "تسجيل خصم - " + agentName;
            } else {
                // العودة إلى وضع الدفع
                isDiscountField.value = "0";
                submitBtn.textContent = "تسجيل الدفعة";
                submitBtn.classList.remove('btn-warning');
                submitBtn.classList.add('btn-primary');
                toggleBtn.textContent = "تسجيل خصم";
                modalTitle.textContent = "تسجيل دفعة - " + agentName;
            }
        }
        // دالة التبديل وضع الخصم
        function toggleDiscountMode(companyId) {
            const isDiscountField = document.getElementById('is-discount-' + companyId);
            const submitBtn = document.getElementById('submitBtn-' + companyId);
            const toggleBtn = document.getElementById('toggleDiscountBtn-' + companyId);
            const modalTitle = document.querySelector('#paymentModal' + companyId + ' .modal-title');
            const companyName = modalTitle.textContent.split('-')[1].trim();

            if (isDiscountField.value === "0") {
                // تحويل إلى وضع الخصم
                isDiscountField.value = "1";
                submitBtn.textContent = "تطبيق الخصم";
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-warning');
                toggleBtn.textContent = "تسجيل دفعة";
                modalTitle.textContent = "تسجيل خصم - " + companyName;
            } else {
                // العودة إلى وضع الدفع
                isDiscountField.value = "0";
                submitBtn.textContent = "تسجيل الدفعة";
                submitBtn.classList.remove('btn-warning');
                submitBtn.classList.add('btn-primary');
                toggleBtn.textContent = "تسجيل خصم";
                modalTitle.textContent = "تسجيل دفعة - " + companyName;
            }
        }
    </script>
@endpush
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body,
        .table,
        h1,
        h3,
        th,
        td {
            font-family: 'Tajawal', Arial, sans-serif !important;
        }
    </style>
@endpush


