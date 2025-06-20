
@extends('layouts.app')

@section('title', 'إضافة معاملة مالية جديدة')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.form-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
.form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
}
.currency-converter {
    background: #f8f9fc;
    border: 2px dashed #e3e6f0;
    border-radius: 10px;
    transition: all 0.3s ease;
}
.currency-converter:hover {
    border-color: #5a5c69;
    background: #ffffff;
}
.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
}
.step {
    flex: 1;
    text-align: center;
    position: relative;
}
.step.active .step-number {
    background: #5a5c69;
    color: white;
}
.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e3e6f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.transactions.index') }}">المعاملات المالية</a>
            </li>
            <li class="breadcrumb-item active">إضافة معاملة جديدة</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card form-card">
                <div class="card-header form-header py-4">
                    <div class="text-center">
                        <h3 class="mb-1">
                            <i class="fas fa-plus-circle me-2"></i>
                            إضافة معاملة مالية جديدة
                        </h3>
                        <p class="mb-0 opacity-75">أدخل تفاصيل المعاملة المالية الجديدة</p>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step active">
                            <div class="step-number">1</div>
                            <div class="step-title">البيانات الأساسية</div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-title">التفاصيل الإضافية</div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-title">المرفقات والملاحظات</div>
                        </div>
                    </div>

                    @include('admin.transactions.partials.form', [
                        'transaction' => null,
                        'action' => route('admin.transactions.store'),
                        'method' => 'POST'
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: 'اختر...',
        allowClear: true
    });

    // Form validation
    $('#transactionForm').on('submit', function(e) {
        const amount = $('#amount').val();
        const type = $('#type').val();
        
        if (!amount && !type) {
            e.preventDefault();
            alert('يرجى إدخال القيمة أو تحديد نوع العملية على الأقل');
            return false;
        }
    });
});
</script>
@endpush