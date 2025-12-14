@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>حجوزات {{ $agent->name }}</h1>
        <a href="{{ route('reports.agent.payments', $agent->id) }}" class="w-25 p-2 mt-2 mb-2 btn btn-primary btn-sm">كشف
            الحساب</a>

        <button type="button" class="w-25 p-2 mt-2 mb-2 btn btn-success btn-sm" data-bs-toggle="modal"
            data-bs-target="#agentPaymentModal{{ $agent->id }}">
            تسجيل دفعة
        </button>

        <button type="button" class=" w-25 p-2 mt-2 mb-2 btn btn-warning btn-sm" data-bs-toggle="modal"
            data-bs-target="#agentDiscountModal{{ $agent->id }}">
            تطبيق خصم
        </button>
        <!-- نموذج الدفعة العادية -->
        <div class="modal fade" id="agentPaymentModal{{ $agent->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('reports.agent.payment') }}" method="POST">
                        @csrf
                        <input type="hidden" name="agent_id" value="{{ $agent->id }}">

                        <div class="modal-header">
                            <h5 class="modal-title">تسجيل دفعة - {{ $agent->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">المبلغ المدفوع والعملة</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
                                    <select class="form-select" name="currency" style="max-width: 120px;">
                                        <option value="SAR" selected>ريال سعودي</option>
                                        <option value="KWD">دينار كويتي</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ملاحظات</label>
                                <textarea class="form-control" name="notes"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                            <button type="submit" class="btn btn-primary">تسجيل الدفعة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- نموذج الخصم المنفصل -->
        <div class="modal fade" id="agentDiscountModal{{ $agent->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('reports.agent.discount', $agent->id) }}" method="POST">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title">تطبيق خصم - {{ $agent->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">مبلغ الخصم والعملة</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="discount_amount"
                                        required>
                                    <select class="form-select" name="currency" style="max-width: 120px;">
                                        <option value="SAR" selected>ريال سعودي</option>
                                        <option value="KWD">دينار كويتي</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">سبب الخصم</label>
                                <textarea class="form-control" name="reason" placeholder="اختياري - سبب تطبيق الخصم"></textarea>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                تأكد من مبلغ الخصم قبل المتابعة. هذا الإجراء سيؤثر على الحساب النهائي
                                للوكيل.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                            <button type="submit" class="btn btn-warning">تطبيق الخصم</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- ملخص الحساب --}}
        @php
            $count = $bookings->count();
            $totalDue = $bookings->sum('due_to_agent');
        @endphp
        <div class="alert alert-info">
            <strong>ملخص الحساب:</strong><br>
            عدد الحجوزات المستحقة: {{ $dueCount }}<br>
            @foreach ($totalDueByCurrency as $currency => $amount)
                إجمالي المستحق ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
            @endforeach
            إجمالي اللي علينا: {{ number_format($totalDue) }} ر.س<br>
            <div style="font-weight: bold;text-decoration: underline;">
                @foreach ($totalPaidByCurrency as $currency => $amount)
                    المدفوع ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
                @endforeach
            </div>
            @foreach ($totalRemainingByCurrency as $currency => $amount)
                المتبقي ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}): {{ number_format($amount, 2) }}<br>
            @endforeach
            <small>المعادلة: ∑ (عدد الليالي الكلي × عدد الغرف × سعر الفندق) لكل الحجوزات</small>
            {{--  --}}
            @if (isset($currentBalance))
                <hr>
                <strong style="color: blueviolet">الرصيد حتى اليوم (حجوزات دخلت فعلياً):</strong><br>
                المستحق حتى اليوم: {{ number_format($currentBalance['entered_due'], 2) }} ريال<br>
                المدفوع: {{ number_format($currentBalance['paid'], 2) }} ريال<br>
                الخصومات: {{ number_format($currentBalance['discounts'], 2) }} ريال<br>
                الصافي المحتسب (مدفوع + خصومات): {{ number_format($currentBalance['effective_paid'], 2) }} ريال<br>
                @php $bal = $currentBalance['balance']; @endphp
                الرصيد:
                @if ($bal > 0)
                    <span class="text-danger">متبقي مستحق للوكيل {{ number_format($bal, 2) }} ريال</span>
                @elseif($bal < 0)
                    <span class="text-success">تم دفع زيادة للوكيل {{ number_format(abs($bal), 2) }} ريال</span>
                @else
                    <span class="text-primary">مغلق</span>
                @endif
                <small class="d-block mt-1">المعادلة: المستحق حتى اليوم - (المدفوع + الخصومات)</small>
            @endif
            <small style="color: blueviolet">المعادلة: ∑ (عدد الليالي × عدد الغرف × سعر الفندق) لكل الحجوزات المدخلة</small>

        </div>
        <div class="mb-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="agentBookingsTable" data-type="agent">
                    <thead>
                        <tr>
                            <th style="width: 1%;" class="d-table-cell d-md-none"></th>
                            <th style="width: 5%;">#</th>
                            <th style="width: 5%;"></th> {{-- Checkbox --}}
                            <th>العميل</th>
                            <th>الشركة</th>
                            <th class="d-none d-md-table-cell">الفندق</th>
                            <th class="d-none d-md-table-cell" style="min-width: 100px;">تاريخ الدخول</th>
                            <th class="d-none d-md-table-cell" style="min-width: 100px;">تاريخ الخروج</th>
                            <th class="d-none d-md-table-cell text-center">عدد الغرف</th>
                            <th class="d-none d-md-table-cell" style="min-width: 110px;">سعر الفندق</th>
                            <th style="min-width: 110px;">السعر الكلي المستحق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $key => $booking)
                            <tr class="booking-main-row">
                                <td class="d-table-cell d-md-none text-center align-middle">
                                    <button class="btn btn-sm btn-outline-secondary toggle-details-btn"
                                        data-bs-toggle="collapse" data-bs-target="#details-{{ $booking->id }}"
                                        aria-expanded="false" aria-controls="details-{{ $booking->id }}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                                <td class="text-center align-middle">{{ $key + 1 }}</td>
                                <td class="text-center align-middle">
                                    @include('partials._booking_checkbox', [
                                        'booking' => $booking,
                                        'amountDueField' => 'due_to_agent',
                                        'amountPaidField' => 'agent_paid',
                                        'costPriceField' => 'cost_price',
                                    ])
                                </td>
                                <td class="align-middle">
                                    {{ $booking->client_name }}
                                    <br>
                                    <span class="block text-muted small">
                                        {{-- اسم الموظف --}}
                                        {{ optional($booking->employee)->name ?? 'غير محدد' }}
                                        <br>
                                        المبلغ المدفوع:
                                        {{ number_format(optional($booking->financialTracking)->agent_payment_amount ?? 0, 2) }}
                                        <br>
                                        حالة الدفع:
                                        @php
                                            $status =
                                                optional($booking->financialTracking)->agent_payment_status ??
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
                                        @if (!empty($booking->notes))
                                            <i class="fas fa-info-circle text-primary ms-2 fs-5 p-1"
                                                data-bs-toggle="popover" data-bs-trigger="hover focus"
                                                data-bs-placement="auto" title="ملاحظات"
                                                data-bs-content="{{ e($booking->notes) }}">
                                            </i>
                                        @endif
                                </td>
                                <td class="align-middle">{{ $booking->company->name }}</td>
                                <td class="d-none d-md-table-cell align-middle">{{ $booking->hotel->name }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ $booking->check_in->format('d/m/Y') }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ $booking->check_out->format('d/m/Y') }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">{{ $booking->rooms }}</td>
                                <td class="d-none d-md-table-cell text-center align-middle">
                                    {{ number_format($booking->cost_price, 2) }}
                                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                                <td class="text-center align-middle">
                                    {{ number_format($booking->amount_due_to_hotel, 2) }}
                                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                            </tr>
                            <tr class="collapse booking-details-row d-md-none" id="details-{{ $booking->id }}">
                                <td colspan="6">
                                    <div class="p-2 bg-light border rounded">
                                        <strong>التفاصيل:</strong><br>
                                        <ul class="list-unstyled mb-0 small">
                                            <li><strong>الفندق:</strong> {{ $booking->hotel->name }}</li>
                                            <li><strong>الدخول:</strong> {{ $booking->check_in->format('d/m/Y') }}</li>
                                            <li><strong>الخروج:</strong> {{ $booking->check_out->format('d/m/Y') }}</li>
                                            <li><strong>الغرف:</strong> {{ $booking->rooms }}</li>
                                            <li><strong>سعر الفندق:</strong>
                                                {{ number_format($booking->cost_price, 2) }}
                                                {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary" id="agentSelectRangeBtn">تحديد النطاق</button>
            <button class="btn btn-secondary" id="agentResetRangeBtn">إعادة تعيين النطاق</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingSelector('agentBookingsTable', 'agentSelectRangeBtn', 'agentResetRangeBtn');

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
                    const button = event.target.previousElementSibling.querySelector(
                        '.toggle-details-btn');
                    button.innerHTML = '<i class="fas fa-minus"></i>';
                });
                el.addEventListener('hide.bs.collapse', event => {
                    const button = event.target.previousElementSibling.querySelector(
                        '.toggle-details-btn');
                    button.innerHTML = '<i class="fas fa-plus"></i>';
                });
            });
        });
    </script>
    <script>
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
    </script>
@endpush
