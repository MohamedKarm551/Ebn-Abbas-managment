@extends('layouts.app')

@section('title', 'تفاصيل مدفوعات الوكيل ' . $agent->name . ' - الرحلات البرية')

{{-- استخدام نفس الستايل من صفحة الشركات مع تعديلات طفيفة --}}
@push('styles')
{{-- نفس CSS الموجود في admin/company-payments/show.blade.php --}}
@endpush

@section('content')
<div class="payments-container">
    <!-- رأس الصفحة -->
    <div class="page-header-section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
            <div class="flex-grow-1">
                <h1 class="page-title-main">
                    <i class="fas fa-handshake page-title-icon"></i>
                    {{ $agent->name }} - الرحلات البرية
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-nav">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.land-trips-agent-payments.index') }}">
                                <i class="fas fa-home me-1"></i>مدفوعات وكلاء الرحلات البرية
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $agent->name }}
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="action-buttons">
                <a href="{{ route('admin.land-trips-agent-payments.create', $agent) }}" class="btn-modern btn-success">
                    <i class="fas fa-plus"></i>
                    <span>إضافة دفعة جديدة</span>
                </a>
                <button class="btn-modern btn-warning" onclick="showDiscountModal()">
                    <i class="fas fa-percentage"></i>
                    <span>إضافة خصم</span>
                </button>
                <button class="btn-modern btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    <span>طباعة التقرير</span>
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- المحتوى الرئيسي -->
        <div class="col-xl-8">
            <!-- إحصائيات العملات -->
            <div class="stats-section">
                @foreach (['SAR' => 'sar', 'KWD' => 'kwd'] as $currency => $class)
                    @if (isset($totals[$currency]) && $totals[$currency]['due'] > 0)
                        <div class="stat-card currency-{{ $class }}">
                            <div class="stat-header">
                                <h3 class="stat-title">{{ $currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي' }}</h3>
                                <span class="currency-indicator {{ $class }}">{{ $currency }}</span>
                            </div>

                            <div class="stat-rows">
                                <div class="stat-row">
                                    <span class="stat-label">المبلغ المستحق</span>
                                    <span class="stat-value">{{ number_format($totals[$currency]['due'], 2) }}</span>
                                </div>

                                <div class="stat-row">
                                    <span class="stat-label">المبلغ المدفوع</span>
                                    <span class="stat-value success">{{ number_format($totals[$currency]['paid'], 2) }}</span>
                                </div>

                                @if (isset($totals[$currency]['discounts']) && $totals[$currency]['discounts'] > 0)
                                    <div class="stat-row">
                                        <span class="stat-label">الخصومات المطبقة</span>
                                        <span class="stat-value warning">{{ number_format($totals[$currency]['discounts'], 2) }}</span>
                                    </div>
                                @endif

                                <div class="stat-row">
                                    <span class="stat-label">المبلغ المتبقي</span>
                                    <span class="stat-value">{{ number_format($totals[$currency]['remaining'], 2) }}</span>
                                </div>
                            </div>

                            @php
                                $totalAdjusted = $totals[$currency]['paid'] + ($totals[$currency]['discounts'] ?? 0);
                                $percentage = $totals[$currency]['due'] > 0 ? ($totalAdjusted / $totals[$currency]['due']) * 100 : 0;
                            @endphp
                            <div class="progress-container">
                                <div class="progress-bar-modern">
                                    <div class="progress-fill" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <div class="progress-text">
                                    تم الدفع: {{ number_format($percentage, 1) }}% من إجمالي المستحق
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- قائمة المدفوعات -->
            <div class="payments-section">
                <div class="payments-header">
                    <h2 class="payments-title">
                        <i class="fas fa-history"></i>
                        سجل المدفوعات - الرحلات البرية
                    </h2>
                    <div class="payments-count">
                        {{ $payments->total() }} دفعة مسجلة
                    </div>
                </div>

                @forelse($payments as $payment)
                    <div class="payment-entry">
                        <div class="payment-layout">
                            <!-- مبلغ الدفعة -->
                            <div class="payment-amount-section {{ $payment->amount < 0 ? 'discount-payment' : '' }}">
                                <h4 class="amount-number">
                                    {{ $payment->amount < 0 ? '-' : '' }}{{ number_format(abs($payment->amount), 2) }}
                                </h4>
                                <p class="amount-currency">
                                    {{ $payment->currency }} {{ $payment->amount < 0 ? '(خصم)' : '' }}
                                </p>
                            </div>

                            <!-- تفاصيل الدفعة -->
                            <div class="payment-details-grid">
                                <div class="detail-item">
                                    <i class="fas fa-calendar detail-icon"></i>
                                    <div class="detail-content">
                                        <p class="detail-label">تاريخ الدفع</p>
                                        <p class="detail-value">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <i class="fas fa-credit-card detail-icon"></i>
                                    <div class="detail-content">
                                        <p class="detail-label">طريقة الدفع</p>
                                        <p class="detail-value">
                                            @switch($payment->payment_method)
                                                @case('cash')
                                                    نقداً
                                                    @break
                                                @case('transfer')
                                                    تحويل بنكي
                                                    @break
                                                @case('check')
                                                    شيك
                                                    @break
                                                @default
                                                    غير محدد
                                            @endswitch
                                        </p>
                                    </div>
                                </div>

                                @if($payment->reference_number)
                                    <div class="detail-item">
                                        <i class="fas fa-hashtag detail-icon"></i>
                                        <div class="detail-content">
                                            <p class="detail-label">رقم المرجع</p>
                                            <p class="detail-value">{{ $payment->reference_number }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if ($payment->employee)
                                    <div class="detail-item">
                                        <i class="fas fa-user detail-icon"></i>
                                        <div class="detail-content">
                                            <p class="detail-label">مسجل بواسطة</p>
                                            <p class="detail-value">{{ $payment->employee->name }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if ($payment->receipt_image_url)
                                    <div class="detail-item">
                                        <i class="fas fa-image detail-icon"></i>
                                        <div class="detail-content">
                                            <p class="detail-label">إيصال الدفع</p>
                                            <div class="receipt-image-container">
                                                <img src="{{ $payment->receipt_image_url }}" alt="إيصال الدفع"
                                                    class="receipt-image" onclick="showImageModal('{{ $payment->receipt_image_url }}')">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- أزرار التحكم -->
                            <div class="payment-actions">
                                <a href="{{ route('admin.land-trips-agent-payments.edit', [$agent, $payment]) }}"
                                    class="btn-modern btn-outline-secondary btn-sm">
                                    <i class="fas fa-edit"></i>
                                    <span>تعديل</span>
                                </a>
                                <button onclick="confirmDelete({{ $payment->id }})"
                                    class="btn-modern btn-outline-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>حذف</span>
                                </button>
                            </div>
                        </div>

                        @if ($payment->notes)
                            <div class="notes-section">
                                <h5 class="notes-label">ملاحظات إضافية</h5>
                                <p class="notes-content">{{ $payment->notes }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-money-bill-wave empty-icon"></i>
                        <h3 class="empty-title">لا توجد مدفوعات مسجلة</h3>
                        <p class="empty-description">
                            لم يتم تسجيل أي مدفوعات لهذا الوكيل في الرحلات البرية حتى الآن.<br>
                            يمكنك البدء بإضافة أول دفعة الآن.
                        </p>
                        <a href="{{ route('admin.land-trips-agent-payments.create', $agent) }}"
                            class="btn-modern btn-success">
                            <i class="fas fa-plus"></i>
                            <span>إضافة أول دفعة</span>
                        </a>
                    </div>
                @endforelse

                @if ($payments->hasPages())
                    <div class="px-3 py-4 border-top">
                        <div class="d-flex justify-content-center">
                            {{ $payments->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- الشريط الجانبي -->
        <div class="col-xl-4">
            <div class="sidebar-section">
                <!-- معلومات الوكيل -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-info-circle"></i>
                        <span>معلومات الوكيل</span>
                    </div>
                    <div class="sidebar-content">
                        <div class="info-item">
                            <i class="fas fa-handshake info-icon"></i>
                            <span class="info-text">{{ $agent->name }}</span>
                        </div>

                        @if ($agent->email)
                            <div class="info-item">
                                <i class="fas fa-envelope info-icon"></i>
                                <span class="info-text">{{ $agent->email }}</span>
                            </div>
                        @endif

                        @if ($agent->phone)
                            <div class="info-item">
                                <i class="fas fa-phone info-icon"></i>
                                <span class="info-text">{{ $agent->phone }}</span>
                            </div>
                        @endif

                        <div class="info-item">
                            <i class="fas fa-chart-line info-icon"></i>
                            <span class="info-text">{{ $agent->landTripBookings()->count() }} حجز رحلة برية</span>
                        </div>
                    </div>
                </div>

                <!-- أحدث الحجوزات -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-bus"></i>
                        <span>أحدث الحجوزات</span>
                    </div>
                    <div class="sidebar-content">
                        @forelse($recentBookings as $booking)
                            <div class="booking-item">
                                <div class="booking-info">
                                    <h6 class="booking-client">{{ $booking->client_name }}</h6>
                                    <p class="booking-date">{{ $booking->created_at->format('d/m/Y') }}</p>
                                    <small class="text-muted">
                                        {{ $booking->company->name ?? 'غير محدد' }}
                                    </small>
                                </div>
                                <span class="booking-amount {{ $booking->currency === 'SAR' ? 'sar' : 'kwd' }}">
                                    {{ number_format($booking->amount_due_to_agent, 0) }} {{ $booking->currency }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-bus fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">لا توجد حجوزات</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال الخصم - نفس المودال الموجود في صفحة الشركات مع تعديل الـ action --}}
    <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="discountModalLabel">
                        <i class="fas fa-percentage me-2"></i>
                        إضافة خصم للوكيل
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="discountForm" method="POST"
                    action="{{ route('admin.land-trips-agent-payments.apply-discount', $agent) }}">
                    @csrf
                    <div class="modal-body">
                        <!-- اختيار العملة -->
                        <div class="mb-3">
                            <label for="discount_currency" class="form-label">العملة <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="discount_currency" name="currency" required
                                onchange="updateRemainingDisplay()">
                                <option value="">اختر العملة</option>
                                @foreach (['SAR' => 'الريال السعودي', 'KWD' => 'الدينار الكويتي'] as $curr => $label)
                                    @if(isset($totals[$curr]) && $totals[$curr]['remaining'] > 0)
                                        <option value="{{ $curr }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- عرض المبلغ المتبقي -->
                        <div class="mb-3">
                            <div class="alert alert-info" id="remainingDisplay" style="display: none;">
                                <i class="fas fa-info-circle me-2"></i>
                                المبلغ المتبقي: <span id="remainingAmount">0</span>
                            </div>
                        </div>

                        <!-- مبلغ الخصم -->
                        <div class="mb-3">
                            <label for="discount_amount" class="form-label">مبلغ الخصم <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="discount_amount" name="discount_amount"
                                step="0.01" min="0.01" required placeholder="أدخل مبلغ الخصم">
                            <div class="form-text">سيتم خصم هذا المبلغ من إجمالي المستحق</div>
                        </div>

                        <!-- سبب الخصم -->
                        <div class="mb-3">
                            <label for="discount_reason" class="form-label">سبب الخصم</label>
                            <textarea class="form-control" id="discount_reason" name="reason" rows="3"
                                placeholder="اختياري - اذكر سبب الخصم..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                        <button type="submit" class="btn btn-warning" id="applyDiscountBtn">
                            <i class="fas fa-percentage me-1"></i>تطبيق الخصم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مودال تأكيد الحذف --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        تأكيد حذف الدفعة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من رغبتك في حذف هذه الدفعة نهائياً؟<br>
                        لا يمكن التراجع عن هذا الإجراء بعد التأكيد.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        <span>إلغاء</span>
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-modern btn-danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>حذف نهائياً</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // نفس JavaScript الموجود في صفحة الشركات مع تعديل المسارات
    function confirmDelete(paymentId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        form.action = `{{ route('admin.land-trips-agent-payments.show', $agent) }}/${paymentId}`;
        modal.show();
    }

    function showDiscountModal() {
        const modal = new bootstrap.Modal(document.getElementById('discountModal'));
        modal.show();
    }

    function updateRemainingDisplay() {
        const currency = document.getElementById('discount_currency').value;
        const remainingDisplay = document.getElementById('remainingDisplay');
        const remainingAmount = document.getElementById('remainingAmount');
        const discountAmountInput = document.getElementById('discount_amount');

        if (currency) {
            const totals = @json($totals);
            const remaining = totals[currency] ? totals[currency].remaining : 0;

            remainingAmount.textContent = `${remaining.toLocaleString()} ${currency}`;
            remainingDisplay.style.display = 'block';

            discountAmountInput.max = remaining;
            discountAmountInput.placeholder = `الحد الأقصى: ${remaining}`;
        } else {
            remainingDisplay.style.display = 'none';
            discountAmountInput.max = '';
            discountAmountInput.placeholder = 'أدخل مبلغ الخصم';
        }
    }

    function showImageModal(imageUrl) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-image me-2"></i>عرض إيصال الدفع
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="${imageUrl}" class="img-fluid" style="max-height: 70vh; border-radius: 0 0 var(--radius-lg) var(--radius-lg);" alt="إيصال الدفع">
                </div>
            </div>
        </div>
    `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        modal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(modal);
        });
    }

    // التحقق من صحة البيانات قبل الإرسال
    document.getElementById('discountForm').addEventListener('submit', function(e) {
        const currency = document.getElementById('discount_currency').value;
        const discountAmount = parseFloat(document.getElementById('discount_amount').value);

        if (!currency) {
            e.preventDefault();
            alert('يرجى اختيار العملة');
            return;
        }

        const totals = @json($totals);
        const remaining = totals[currency] ? totals[currency].remaining : 0;

        if (discountAmount > remaining) {
            e.preventDefault();
            alert(`مبلغ الخصم (${discountAmount}) أكبر من المبلغ المتبقي (${remaining})`);
            return;
        }

        if (!confirm(`هل أنت متأكد من تطبيق خصم ${discountAmount} ${currency} على الوكيل {{ $agent->name }}؟`)) {
            e.preventDefault();
        }
    });
</script>
@endpush