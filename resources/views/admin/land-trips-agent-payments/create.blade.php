@extends('layouts.app')

@section('title', 'إضافة دفعة جديدة للوكيل ' . $agent->name)

@push('styles')
<style>
    .create-payment-container {
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-bottom: none;
    }

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

    .form-section h5 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .is-invalid {
        border-color: #e53e3e;
    }

    .invalid-feedback {
        color: #e53e3e;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .summary-card {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .summary-title {
        color: #1e40af;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .summary-item {
        text-align: center;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 12px;
        border: 1px solid rgba(191, 219, 254, 0.5);
    }

    .summary-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .summary-value {
        color: #1e40af;
        font-size: 1.25rem;
        font-weight: 700;
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
        position: relative;
        overflow: hidden;
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
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

    .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 1px solid #93c5fd;
        color: #1e40af;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
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
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: scale(1.02);
    }

    @media (max-width: 768px) {
        .create-payment-container {
            padding: 1rem;
        }

        .form-section {
            padding: 1.5rem;
        }

        .button-group {
            flex-direction: column;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }
    }

    /* تأثيرات إضافية */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .main-card {
        animation: fadeInUp 0.6s ease-out;
    }

    .form-control:valid {
        border-color: #10b981;
    }

    .currency-indicator {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #667eea;
        color: white;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="create-payment-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="main-card">
                    <!-- رأس الصفحة -->
                    <div class="card-header-modern">
                        <h1 class="page-title">
                            <i class="fas fa-plus-circle"></i>
                            إضافة دفعة جديدة
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

                    <!-- ملخص المستحقات -->
                    <div class="form-section">
                        <div class="summary-card">
                            <h5 class="summary-title">
                                <i class="fas fa-chart-pie"></i>
                                ملخص المستحقات الحالية
                            </h5>
                            <div class="summary-grid">
                                @foreach(['SAR' => 'ريال سعودي', 'KWD' => 'دينار كويتي'] as $currency => $label)
                                    @if(isset($totals[$currency]) && $totals[$currency]['due'] > 0)
                                        <div class="summary-item">
                                            <div class="currency-indicator">{{ $currency }}</div>
                                            <div class="summary-label">{{ $label }}</div>
                                            <div class="row mt-2">
                                                <div class="col-4">
                                                    <small class="summary-label">المستحق</small>
                                                    <div class="summary-value text-primary">
                                                        {{ number_format($totals[$currency]['due'], 2) }}
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="summary-label">المدفوع</small>
                                                    <div class="summary-value text-success">
                                                        {{ number_format($totals[$currency]['paid'], 2) }}
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="summary-label">المتبقي</small>
                                                    <div class="summary-value text-danger">
                                                        {{ number_format($totals[$currency]['remaining'], 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- نموذج إضافة الدفعة -->
                        <form action="{{ route('admin.land-trips-agent-payments.store', $agent) }}" method="POST">
                            @csrf

                            <h5>
                                <i class="fas fa-money-bill-wave text-success"></i>
                                تفاصيل الدفعة الجديدة
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
                                               value="{{ old('amount') }}" 
                                               placeholder="أدخل مبلغ الدفعة"
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
                                            <option value="">اختر العملة</option>
                                            @foreach(['SAR' => 'ريال سعودي', 'KWD' => 'دينار كويتي'] as $curr => $label)
                                                @if(isset($totals[$curr]) && $totals[$curr]['remaining'] > 0)
                                                    <option value="{{ $curr }}" {{ old('currency') == $curr ? 'selected' : '' }}>
                                                        {{ $label }} (متبقي: {{ number_format($totals[$curr]['remaining'], 2) }})
                                                    </option>
                                                @endif
                                            @endforeach
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
                                               value="{{ old('payment_date', date('Y-m-d')) }}" 
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
                                                <input type="radio" id="cash" name="payment_method" value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }}>
                                                <label for="cash" class="payment-method-label">
                                                    <i class="fas fa-money-bill-wave d-block mb-1"></i>
                                                    نقداً
                                                </label>
                                            </div>
                                            <div class="payment-method">
                                                <input type="radio" id="transfer" name="payment_method" value="transfer" {{ old('payment_method') == 'transfer' ? 'checked' : '' }}>
                                                <label for="transfer" class="payment-method-label">
                                                    <i class="fas fa-university d-block mb-1"></i>
                                                    تحويل بنكي
                                                </label>
                                            </div>
                                            <div class="payment-method">
                                                <input type="radio" id="check" name="payment_method" value="check" {{ old('payment_method') == 'check' ? 'checked' : '' }}>
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
                                               value="{{ old('reference_number') }}" 
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
                                               value="{{ old('receipt_image_url') }}" 
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
                                                  placeholder="أي ملاحظات أو تفاصيل إضافية عن الدفعة">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- أزرار التحكم -->
                            <div class="button-group">
                                <button type="submit" class="btn-modern btn-success">
                                    <i class="fas fa-save"></i>
                                    حفظ الدفعة
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
    // التحقق من المبلغ المدخل مقابل المتبقي
    const amountInput = document.getElementById('amount');
    const currencySelect = document.getElementById('currency');
    const totals = @json($totals);

    function validateAmount() {
        const currency = currencySelect.value;
        const amount = parseFloat(amountInput.value);

        if (currency && amount && totals[currency]) {
            const remaining = totals[currency].remaining;
            
            if (amount > remaining) {
                amountInput.setCustomValidity(`المبلغ لا يمكن أن يتجاوز المتبقي: ${remaining.toLocaleString()}`);
                amountInput.classList.add('is-invalid');
            } else {
                amountInput.setCustomValidity('');
                amountInput.classList.remove('is-invalid');
            }
        }
    }

    amountInput.addEventListener('input', validateAmount);
    currencySelect.addEventListener('change', validateAmount);

    // تحديث الحد الأقصى عند تغيير العملة
    currencySelect.addEventListener('change', function() {
        const currency = this.value;
        if (currency && totals[currency]) {
            amountInput.max = totals[currency].remaining;
            amountInput.placeholder = `الحد الأقصى: ${totals[currency].remaining.toLocaleString()}`;
        } else {
            amountInput.removeAttribute('max');
            amountInput.placeholder = 'أدخل مبلغ الدفعة';
        }
    });

    // تأكيد إرسال النموذج
    document.querySelector('form').addEventListener('submit', function(e) {
        const currency = currencySelect.value;
        const amount = parseFloat(amountInput.value);
        
        if (!confirm(`هل أنت متأكد من تسجيل دفعة ${amount} ${currency} للوكيل {{ $agent->name }}؟`)) {
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