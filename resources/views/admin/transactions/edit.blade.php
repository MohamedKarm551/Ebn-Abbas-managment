
@extends('layouts.app')

@section('title', 'تعديل المعاملة المالية')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.form-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
.form-header {
    background: linear-gradient(135deg, #fd7e14 0%, #ff6b35 100%);
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
.current-file {
    background: #e7f3ff;
    border: 1px solid #bee5eb;
    border-radius: 5px;
    padding: 10px;
    margin-top: 10px;
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
            <li class="breadcrumb-item">
                <a href="{{ route('admin.transactions.show', $transaction) }}">معاملة #{{ $transaction->id }}</a>
            </li>
            <li class="breadcrumb-item active">تعديل</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card form-card">
                <div class="card-header form-header py-4">
                    <div class="text-center">
                        <h3 class="mb-1">
                            <i class="fas fa-edit me-2"></i>
                            تعديل المعاملة المالية #{{ $transaction->id }}
                        </h3>
                        <p class="mb-0 opacity-75">تعديل تفاصيل المعاملة المالية</p>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Transaction Info -->
                    <div class="alert alert-info mb-4">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <strong>التاريخ الحالي:</strong><br>
                                {{ $transaction->transaction_date->format('Y-m-d') }}
                            </div>
                            <div class="col-md-3">
                                <strong>المبلغ الحالي:</strong><br>
                                {{ number_format($transaction->amount, 2) }} {{ $transaction->currency_symbol }}
                            </div>
                            <div class="col-md-3">
                                <strong>النوع:</strong><br>
                                <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'danger' : 'info') }}">
                                    {{ $transaction->type_arabic }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>تاريخ الإنشاء:</strong><br>
                                {{ $transaction->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </div>

                    @include('admin.transactions.partials.form', [
                        'transaction' => $transaction,
                        'action' => route('admin.transactions.update', $transaction),
                        'method' => 'PUT'
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

    // Exchange rate calculator
    $('#calculateExchange').click(function() {
        const amount = $('#amount').val();
        const fromCurrency = $('#currency').val();
        const toCurrency = $('#convertToCurrency').val();
        
        if (!amount || amount <= 0) {
            alert('يرجى إدخال مبلغ صحيح');
            return;
        }
        
        if (fromCurrency === toCurrency) {
            alert('العملة المصدر والهدف متطابقتان');
            return;
        }
        
        // Show loading
        $(this).html('<i class="fas fa-spinner fa-spin"></i> جاري الحساب...');
        
        $.get('{{ route("admin.transactions.exchange-rates") }}', {
            from: fromCurrency,
            to: toCurrency,
            amount: amount
        })
        .done(function(data) {
            if (data.success) {
                $('#convertedAmount').val(data.converted_amount + ' ' + data.to);
                $('#exchangeRate').val('1 ' + data.from + ' = ' + data.rate + ' ' + data.to);
                
                if (data.note) {
                    alert(data.note);
                }
            } else {
                alert('خطأ في جلب أسعار الصرف');
            }
        })
        .fail(function() {
            alert('خطأ في الاتصال بخدمة أسعار الصرف');
        })
        .always(function() {
            $('#calculateExchange').html('<i class="fas fa-calculator"></i> احسب التحويل');
        });
    });
});
</script>
@endpush