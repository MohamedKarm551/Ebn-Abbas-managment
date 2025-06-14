{{-- filepath: resources/views/admin/company-payments/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل دفعة - ' . $company->name)

@push('styles')
<style>
    .payment-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
    }
    
    .form-floating .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .form-floating .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .currency-preview {
        font-size: 2rem;
        font-weight: bold;
        opacity: 0.7;
        transition: all 0.3s ease;
    }
    
    .amount-display {
        font-size: 1.5rem;
        font-weight: bold;
        color: #198754;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-weight: bold;
        color: #6c757d;
    }
    
    .preview-image {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        object-fit: cover;
        border: 3px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .preview-image:hover {
        border-color: #0d6efd;
        transform: scale(1.05);
    }
    
    .btn-save {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        padding: 12px 30px;
        font-weight: bold;
        border-radius: 25px;
        transition: all 0.3s ease;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    .alert-edit {
        border-left: 4px solid #ffc107;
        background: rgba(255, 193, 7, 0.1);
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Breadcrumb -->
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.company-payments.index') }}" class="text-decoration-none">
                            <i class="fas fa-building me-1"></i>مدفوعات الشركات
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.company-payments.show', $company) }}" class="text-decoration-none">
                            {{ $company->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">تعديل دفعة</li>
                </ol>
            </nav>
            
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                    <i class="fas fa-edit fa-2x text-warning"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1">تعديل دفعة</h1>
                    <p class="text-muted mb-0">{{ $company->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- النموذج الرئيسي -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header payment-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            تفاصيل الدفعة
                        </h5>
                        <div class="currency-preview" id="currencyPreview">
                            {{ $payment->currency }}
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- تنبيه التعديل -->
                    <div class="alert alert-edit mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
                            <div>
                                <h6 class="mb-1">تعديل دفعة موجودة</h6>
                                <small class="text-muted">
                                    الدفعة الحالية: <strong>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</strong>
                                    | تاريخ: <strong>{{ $payment->payment_date->format('d/m/Y') }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.company-payments.update', [$company, $payment]) }}" method="POST" id="editPaymentForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- المبلغ -->
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="number" 
                                           step="0.01" 
                                           min="0.01" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="amount" 
                                           value="{{ old('amount', $payment->amount) }}" 
                                           placeholder="المبلغ"
                                           required>
                                    <label for="amount">
                                        <i class="fas fa-coins me-1"></i>المبلغ المدفوع <span class="text-danger">*</span>
                                    </label>
                                    @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- العملة -->
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <select class="form-select @error('currency') is-invalid @enderror" 
                                            id="currency" 
                                            name="currency" 
                                            required>
                                        <option value="">اختر العملة</option>
                                        <option value="SAR" {{ old('currency', $payment->currency) == 'SAR' ? 'selected' : '' }}>
                                            ريال سعودي (SAR)
                                        </option>
                                        <option value="KWD" {{ old('currency', $payment->currency) == 'KWD' ? 'selected' : '' }}>
                                            دينار كويتي (KWD)
                                        </option>
                                    </select>
                                    <label for="currency">
                                        <i class="fas fa-dollar-sign me-1"></i>العملة <span class="text-danger">*</span>
                                    </label>
                                    @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- عرض المبلغ الحالي -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="bg-light rounded p-3 text-center">
                                    <small class="text-muted d-block">المبلغ المحدث</small>
                                    <div class="amount-display" id="amountDisplay">
                                        {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- تاريخ الدفع -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="date" 
                                           class="form-control @error('payment_date') is-invalid @enderror" 
                                           id="payment_date" 
                                           name="payment_date" 
                                           value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" 
                                           max="{{ date('Y-m-d') }}" 
                                           required>
                                    <label for="payment_date">
                                        <i class="fas fa-calendar me-1"></i>تاريخ الدفع <span class="text-danger">*</span>
                                    </label>
                                    @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- رابط صورة الإيصال -->
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="url" 
                                           class="form-control @error('receipt_image_url') is-invalid @enderror" 
                                           id="receipt_image_url" 
                                           name="receipt_image_url" 
                                           value="{{ old('receipt_image_url', $payment->receipt_image_url) }}"
                                           placeholder="https://example.com/receipt.jpg">
                                    <label for="receipt_image_url">
                                        <i class="fas fa-image me-1"></i>رابط صورة الإيصال
                                    </label>
                                    <div class="form-text">رابط صورة الإيصال (اختياري)</div>
                                    @error('receipt_image_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- ملاحظات -->
                        <div class="mb-4">
                            <div class="form-floating">
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          style="height: 120px"
                                          placeholder="ملاحظات إضافية...">{{ old('notes', $payment->notes) }}</textarea>
                                <label for="notes">
                                    <i class="fas fa-sticky-note me-1"></i>ملاحظات
                                </label>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- أزرار التحكم -->
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning btn-save">
                                    <i class="fas fa-save me-2"></i>حفظ التعديلات
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo me-1"></i>إعادة تعيين
                                </button>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.company-payments.show', $company) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-1"></i>العودة للشركة
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash me-1"></i>حذف الدفعة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- الشريط الجانبي -->
        <div class="col-lg-4">
            <!-- معلومات الشركة -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-building me-1"></i>معلومات الشركة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                            <i class="fas fa-building text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $company->name }}</h6>
                            <small class="text-muted">اسم الشركة</small>
                        </div>
                    </div>
                    
                    @if($company->email)
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-envelope text-muted me-2" style="width: 20px;"></i>
                        <span>{{ $company->email }}</span>
                    </div>
                    @endif
                    
                    @if($company->phone)
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone text-muted me-2" style="width: 20px;"></i>
                        <span>{{ $company->phone }}</span>
                    </div>
                    @endif
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar text-muted me-2" style="width: 20px;"></i>
                        <span>{{ $company->landTripBookings()->count() }} حجز</span>
                    </div>
                </div>
            </div>

            <!-- معلومات الدفعة الحالية -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i>الدفعة الحالية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <h4 class="text-primary mb-1">{{ number_format($payment->amount, 2) }}</h4>
                            <small class="text-muted">المبلغ الحالي</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1">{{ $payment->currency }}</h4>
                            <small class="text-muted">العملة</small>
                        </div>
                    </div>
                    
                    <div class="text-center border-top pt-3">
                        <small class="text-muted d-block">تاريخ الدفعة الأصلي</small>
                        <strong>{{ $payment->payment_date->format('d/m/Y') }}</strong>
                    </div>
                    
                    @if($payment->employee)
                    <div class="text-center border-top pt-3 mt-3">
                        <small class="text-muted d-block">سجلت بواسطة</small>
                        <strong>{{ $payment->employee->name }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- معاينة صورة الإيصال -->
            <div class="card shadow-sm border-0" id="imagePreviewCard" style="display: none;">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-image me-1"></i>معاينة الإيصال
                    </h6>
                </div>
                <div class="card-body text-center">
                    <img id="receiptPreview" src="" alt="معاينة الإيصال" class="preview-image">
                    <div class="mt-2">
                        <a id="receiptLink" href="" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>فتح في نافذة جديدة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تأكيد الحذف -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>تأكيد حذف الدفعة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h5>هل أنت متأكد من حذف هذه الدفعة؟</h5>
                        <p class="text-muted">
                            المبلغ: <strong>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</strong><br>
                            التاريخ: <strong>{{ $payment->payment_date->format('d/m/Y') }}</strong>
                        </p>
                        <div class="alert alert-warning">
                            <i class="fas fa-warning me-1"></i>
                            لا يمكن التراجع عن هذا الإجراء
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>إلغاء
                    </button>
                    <form action="{{ route('admin.company-payments.destroy', [$company, $payment]) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>حذف نهائياً
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
// تحديث عرض المبلغ والعملة في الوقت الفعلي
function updateAmountDisplay() {
    const amount = document.getElementById('amount').value || 0;
    const currency = document.getElementById('currency').value || '';
    const display = document.getElementById('amountDisplay');
    const preview = document.getElementById('currencyPreview');
    
    if (amount && currency) {
        display.textContent = parseFloat(amount).toLocaleString('ar-SA', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' ' + currency;
        preview.textContent = currency;
    } else {
        display.textContent = '0.00';
        preview.textContent = '';
    }
}

// معاينة صورة الإيصال
function updateImagePreview() {
    const url = document.getElementById('receipt_image_url').value;
    const card = document.getElementById('imagePreviewCard');
    const img = document.getElementById('receiptPreview');
    const link = document.getElementById('receiptLink');
    
    if (url && isValidImageUrl(url)) {
        img.src = url;
        link.href = url;
        card.style.display = 'block';
    } else {
        card.style.display = 'none';
    }
}

function isValidImageUrl(url) {
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(url) || url.includes('drive.google.com') || url.includes('dropbox.com');
}

// إعادة تعيين النموذج
function resetForm() {
    if (confirm('هل تريد إعادة تعيين جميع التغييرات؟')) {
        document.getElementById('editPaymentForm').reset();
        updateAmountDisplay();
        updateImagePreview();
    }
}

// تأكيد الحذف
function confirmDelete() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// إعداد المراقبين
document.addEventListener('DOMContentLoaded', function() {
    // مراقبة تغيير المبلغ والعملة
    document.getElementById('amount').addEventListener('input', updateAmountDisplay);
    document.getElementById('currency').addEventListener('change', updateAmountDisplay);
    
    // مراقبة تغيير رابط الصورة
    document.getElementById('receipt_image_url').addEventListener('input', updateImagePreview);
    
    // تحديث العرض الأولي
    updateAmountDisplay();
    updateImagePreview();
    
    // التحقق من صحة النموذج قبل الإرسال
    document.getElementById('editPaymentForm').addEventListener('submit', function(e) {
        const amount = parseFloat(document.getElementById('amount').value);
        const currency = document.getElementById('currency').value;
        
        if (!amount || amount <= 0) {
            e.preventDefault();
            alert('يرجى إدخال مبلغ صحيح أكبر من صفر');
            return false;
        }
        
        if (!currency) {
            e.preventDefault();
            alert('يرجى اختيار العملة');
            return false;
        }
        
        // تأكيد التعديل
        const originalAmount = {{ $payment->amount }};
        const originalCurrency = '{{ $payment->currency }}';
        
        if (amount !== originalAmount || currency !== originalCurrency) {
            if (!confirm(`هل تريد تعديل الدفعة من ${originalAmount} ${originalCurrency} إلى ${amount} ${currency}؟`)) {
                e.preventDefault();
                return false;
            }
        }
    });
});

// اختصارات لوحة المفاتيح
document.addEventListener('keydown', function(e) {
    // Ctrl+S للحفظ
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('editPaymentForm').submit();
    }
    
    // Escape للإلغاء
    if (e.key === 'Escape') {
        window.location.href = '{{ route("admin.company-payments.show", $company) }}';
    }
});
</script>
@endpush