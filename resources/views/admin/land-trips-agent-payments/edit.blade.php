@extends('layouts.app')

@section('title', 'تعديل دفعة الوكيل ' . $agent->name)

@push('styles')
<style>
    /* نفس الستايل من create.blade.php مع تعديلات بسيطة */
    .edit-payment-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem;
        font-family: 'Inter', 'Cairo', sans-serif;
    }

    .main-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 24px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        overflow: hidden;
    }

    .card-header-modern {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 2rem;
        border-bottom: none;
    }

    /* باقي الستايل مثل create.blade.php */
    .page-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0.5rem 0 0 0;
    }

    .form-section {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        color: #4a5568;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control, .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #ffffff;
    }

    .form-control:focus, .form-select:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        outline: none;
    }

    .btn-modern {
        padding: 0.75rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-1px);
    }

    .button-group {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        padding: 2rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .payment-method {
        position: relative;
    }

    .payment-method input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .payment-method-label {
        display: block;
        padding: 1rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .payment-method input[type="radio"]:checked + .payment-method-label {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
        transform: scale(1.02);
    }

    .current-payment-info {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #f59e0b;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .current-payment-title {
        color: #92400e;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="edit-payment-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="main-card">
                    <!-- رأس الصفحة -->
                    <div class="card-header-modern">
                        <h1 class="page-title">
                            <i class="fas fa-edit"></i>
                            تعديل الدفعة
                        </h1>
                        <p class="page-subtitle">
                            للوكيل: {{ $agent->name }} - الرحلات البرية
                        </p>
                    </div>

                    <!-- عرض الأخطاء -->
                    @if ($errors->any())
                        <div class="alert alert-danger m-3">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>يوجد أخطاء في البيانات:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-section">
                        <!-- معلومات الدفعة الحالية -->
                        <div class="current-payment-info">
                            <h5 class="current-payment-title">
                                <i class="fas fa-info-circle"></i>
                                معلومات الدفعة الحالية
                            </h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>المبلغ:</strong><br>
                                    {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
                                </div>
                                <div class="col-md-3">
                                    <strong>التاريخ:</strong><br>
                                    {{ $payment->payment_date->format('d/m/Y') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>الطريقة:</strong><br>
                                    @switch($payment->payment_method)
                                        @case('cash') نقداً @break
                                        @case('transfer') تحويل بنكي @break
                                        @case('check') شيك @break
                                        @default غير محدد
                                    @endswitch
                                </div>
                                <div class="col-md-3">
                                    <strong>المرجع:</strong><br>
                                    {{ $payment->reference_number ?: 'غير متوفر' }}
                                </div>
                            </div>
                        </div>

                        <!-- نموذج التعديل -->
                        <form action="{{ route('admin.land-trips-agent-payments.update', [$agent, $payment]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <h5>
                                <i class="fas fa-edit text-warning"></i>
                                تعديل بيانات الدفعة
                            </h5>

                            <div class="row">
                                <!-- مبلغ الدفعة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount" class="form-label">
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            مبلغ الدفعة <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" 
                                               name="amount" 
                                               step="0.01" 
                                               min="0.01" 
                                               value="{{ old('amount', $payment->amount) }}" 
                                               required>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- العملة -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currency" class="form-label">
                                            <i class="fas fa-coins me-1"></i>
                                            العملة <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('currency') is-invalid @enderror" 
                                                id="currency" 
                                                name="currency" 
                                                required>
                                            <option value="SAR" {{ old('currency', $payment->currency) == 'SAR' ? 'selected' : '' }}>
                                                ريال سعودي (SAR)
                                            </option>
                                            <option value="KWD" {{ old('currency', $payment->currency) == 'KWD' ? 'selected' : '' }}>
                                                دينار كويتي (KWD)
                                            </option>
                                        </select>
                                        @error('currency')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- تاريخ الدفع -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_date" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>
                                            تاريخ الدفع <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control @error('payment_date') is-invalid @enderror" 
                                               id="payment_date" 
                                               name="payment_date" 
                                               value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" 
                                               max="{{ date('Y-m-d') }}"
                                               required>
                                        @error('payment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- طريقة الدفع -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-credit-card me-1"></i>
                                            طريقة الدفع <span class="text-danger">*</span>
                                        </label>
                                        <div class="payment-methods">
                                            <div class="payment-method">
                                                <input type="radio" id="cash" name="payment_method" value="cash" 
                                                       {{ old('payment_method', $payment->payment_method) == 'cash' ? 'checked' : '' }}>
                                                <label for="cash" class="payment-method-label">
                                                    <i class="fas fa-money-bill-wave d-block mb-1"></i>
                                                    نقداً
                                                </label>
                                            </div>
                                            <div class="payment-method">
                                                <input type="radio" id="transfer" name="payment_method" value="transfer" 
                                                       {{ old('payment_method', $payment->payment_method) == 'transfer' ? 'checked' : '' }}>
                                                <label for="transfer" class="payment-method-label">
                                                    <i class="fas fa-university d-block mb-1"></i>
                                                    تحويل بنكي
                                                </label>
                                            </div>
                                            <div class="payment-method">
                                                <input type="radio" id="check" name="payment_method" value="check" 
                                                       {{ old('payment_method', $payment->payment_method) == 'check' ? 'checked' : '' }}>
                                                <label for="check" class="payment-method-label">
                                                    <i class="fas fa-file-invoice d-block mb-1"></i>
                                                    شيك
                                                </label>
                                            </div>
                                        </div>
                                        @error('payment_method')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- رقم المرجع -->
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="reference_number" class="form-label">
                                            <i class="fas fa-hashtag me-1"></i>
                                            رقم المرجع (اختياري)
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('reference_number') is-invalid @enderror" 
                                               id="reference_number" 
                                               name="reference_number" 
                                               value="{{ old('reference_number', $payment->reference_number) }}" 
                                               placeholder="رقم الشيك أو رقم التحويل">
                                        @error('reference_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- رابط صورة الإيصال -->
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="receipt_image_url" class="form-label">
                                            <i class="fas fa-image me-1"></i>
                                            رابط صورة الإيصال (اختياري)
                                        </label>
                                        <input type="url" 
                                               class="form-control @error('receipt_image_url') is-invalid @enderror" 
                                               id="receipt_image_url" 
                                               name="receipt_image_url" 
                                               value="{{ old('receipt_image_url', $payment->receipt_image_url) }}" 
                                               placeholder="https://example.com/receipt.jpg">
                                        @error('receipt_image_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- الملاحظات -->
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">
                                            <i class="fas fa-sticky-note me-1"></i>
                                            ملاحظات إضافية (اختياري)
                                        </label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  id="notes" 
                                                  name="notes" 
                                                  rows="3" 
                                                  placeholder="أي ملاحظات أو تفاصيل إضافية عن الدفعة">{{ old('notes', $payment->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- أزرار التحكم -->
                            <div class="button-group">
                                <button type="submit" class="btn-modern btn-warning">
                                    <i class="fas fa-save"></i>
                                    حفظ التعديلات
                                </button>
                                <a href="{{ route('admin.land-trips-agent-payments.show', $agent) }}" class="btn-modern btn-secondary">
                                    <i class="fas fa-times"></i>
                                    إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تأكيد إرسال النموذج
    document.querySelector('form').addEventListener('submit', function(e) {
        const amount = document.getElementById('amount').value;
        const currency = document.getElementById('currency').value;
        
        if (!confirm(`هل أنت متأكد من تعديل الدفعة إلى ${amount} ${currency}؟`)) {
            e.preventDefault();
        }
    });

    // تحسين تجربة المستخدم مع طرق الدفع
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const referenceField = document.getElementById('reference_number');
            const referenceLabel = referenceField.previousElementSibling;
            
            switch(this.value) {
                case 'transfer':
                    referenceField.placeholder = 'رقم التحويل البنكي';
                    referenceLabel.innerHTML = '<i class="fas fa-hashtag me-1"></i>رقم التحويل (مُوصى به)';
                    break;
                case 'check':
                    referenceField.placeholder = 'رقم الشيك';
                    referenceLabel.innerHTML = '<i class="fas fa-hashtag me-1"></i>رقم الشيك (مُوصى به)';
                    break;
                default:
                    referenceField.placeholder = 'رقم المرجع أو الملاحظة';
                    referenceLabel.innerHTML = '<i class="fas fa-hashtag me-1"></i>رقم المرجع (اختياري)';
            }
        });
    });
});
</script>
@endpush