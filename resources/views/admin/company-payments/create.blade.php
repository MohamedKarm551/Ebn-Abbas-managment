{{-- filepath: resources/views/admin/company-payments/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة دفعة جديدة - ' . $company->name)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        إضافة دفعة جديدة - {{ $company->name }}
                    </h5>
                </div>
                
                <div class="card-body">
                    <!-- عرض الإحصائيات الحالية -->
                    <div class="row mb-4">
                        @foreach(['SAR' => 'ريال سعودي', 'KWD' => 'دينار كويتي'] as $currency => $currencyName)
                        @if($totals[$currency]['due'] > 0)
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">{{ $currencyName }}</h6>
                                <p class="mb-1">المستحق: <strong>{{ number_format($totals[$currency]['due'], 2) }}</strong></p>
                                <p class="mb-1">المدفوع: <strong>{{ number_format($totals[$currency]['paid'], 2) }}</strong></p>
                                <p class="mb-0">المتبقي: <strong class="text-danger">{{ number_format($totals[$currency]['remaining'], 2) }}</strong></p>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>

                    <!-- نموذج إضافة الدفعة -->
                    <form action="{{ route('admin.company-payments.store', $company) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">المبلغ المدفوع <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount') }}" required>
                                @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">العملة <span class="text-danger">*</span></label>
                                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                    <option value="">اختر العملة</option>
                                    <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>ريال سعودي</option>
                                    <option value="KWD" {{ old('currency') == 'KWD' ? 'selected' : '' }}>دينار كويتي</option>
                                </select>
                                @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}" required>
                            @error('payment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="receipt_image_url" class="form-label">رابط صورة الإيصال</label>
                            <input type="url" class="form-control @error('receipt_image_url') is-invalid @enderror" 
                                   id="receipt_image_url" name="receipt_image_url" value="{{ old('receipt_image_url') }}"
                                   placeholder="https://example.com/receipt.jpg">
                            <div class="form-text">يمكنك رفع الصورة لأي موقع (مثل Google Drive, Dropbox) ونسخ الرابط هنا</div>
                            @error('receipt_image_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> حفظ الدفعة
                            </button>
                            <a href="{{ route('admin.company-payments.show', $company) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// تحديث المبلغ المقترح حسب العملة المختارة
document.getElementById('currency').addEventListener('change', function() {
    const currency = this.value;
    const amountField = document.getElementById('amount');
    
    if (currency === 'SAR') {
        amountField.placeholder = 'مثال: {{ number_format($totals["SAR"]["remaining"], 2) }}';
    } else if (currency === 'KWD') {
        amountField.placeholder = 'مثال: {{ number_format($totals["KWD"]["remaining"], 2) }}';
    } else {
        amountField.placeholder = '';
    }
});
</script>
@endpush