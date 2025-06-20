
@extends('layouts.app')

@section('title', 'تفاصيل المعاملة المالية')

@push('styles')
<style>
.detail-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
.detail-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
}
.detail-item {
    border-bottom: 1px solid #f0f0f0;
    padding: 1rem 0;
}
.detail-item:last-child {
    border-bottom: none;
}
.status-badge {
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
}
.amount-display {
    font-size: 2rem;
    font-weight: bold;
    text-align: center;
    padding: 2rem;
    border-radius: 10px;
    margin: 1rem 0;
}
.amount-positive {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}
.amount-negative {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    color: white;
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
            <li class="breadcrumb-item active">تفاصيل المعاملة #{{ $transaction->id }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card detail-card">
                <div class="card-header detail-header py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">
                                <i class="fas fa-receipt me-2"></i>
                                تفاصيل المعاملة #{{ $transaction->id }}
                            </h3>
                            <p class="mb-0 opacity-75">
                                تاريخ الإنشاء: {{ $transaction->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="status-badge badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'danger' : 'info') }}">
                                {{ $transaction->type_arabic }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Amount Display -->
                    <div class="amount-display {{ $transaction->type == 'deposit' ? 'amount-positive' : 'amount-negative' }}">
                        <div class="d-flex justify-content-center align-items-center">
                            <i class="fas fa-{{ $transaction->type == 'deposit' ? 'plus' : 'minus' }} me-2"></i>
                            <span>{{ number_format($transaction->amount, 2) }} {{ $transaction->currency_symbol }}</span>
                        </div>
                        <small class="opacity-75">{{ $transaction->currency }}</small>
                    </div>

                    <!-- Transaction Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    تاريخ المعاملة
                                </label>
                                <div>{{ $transaction->transaction_date->format('Y-m-d') }}</div>
                            </div>

                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    من/إلى
                                </label>
                                <div>{{ $transaction->from_to ?: 'غير محدد' }}</div>
                            </div>

                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-tag text-primary me-1"></i>
                                    نوع العملية
                                </label>
                                <div>
                                    <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'danger' : 'info') }}">
                                        {{ $transaction->type_arabic }}
                                    </span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-bookmark text-primary me-1"></i>
                                    التصنيف
                                </label>
                                <div>{{ $transaction->category ?: 'غير محدد' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-money-bill text-primary me-1"></i>
                                    المبلغ والعملة
                                </label>
                                <div class="h5 mb-0">
                                    {{ number_format($transaction->amount, 2) }} {{ $transaction->currency_symbol }}
                                </div>
                            </div>

                            @if($transaction->exchange_rate)
                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-exchange-alt text-primary me-1"></i>
                                    سعر الصرف
                                </label>
                                <div>
                                    1 {{ $transaction->currency }} = {{ $transaction->exchange_rate }} {{ $transaction->base_currency }}
                                </div>
                                @if($transaction->converted_amount)
                                <small class="text-muted">
                                    المبلغ المحول: {{ number_format($transaction->converted_amount, 2) }} {{ $transaction->base_currency }}
                                </small>
                                @endif
                            </div>
                            @endif

                            @if($transaction->link_or_image)
                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-paperclip text-primary me-1"></i>
                                    المرفق
                                </label>
                                <div>
                                    <a href="{{ Storage::url($transaction->link_or_image) }}" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                        عرض المرفق
                                    </a>
                                </div>
                            </div>
                            @endif

                            <div class="detail-item">
                                <label class="fw-bold text-muted mb-1">
                                    <i class="fas fa-clock text-primary me-1"></i>
                                    آخر تحديث
                                </label>
                                <div>{{ $transaction->updated_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    @if($transaction->notes)
                    <div class="mt-4">
                        <label class="fw-bold text-muted mb-2">
                            <i class="fas fa-sticky-note text-primary me-1"></i>
                            الملاحظات
                        </label>
                        <div class="p-3 bg-light rounded">
                            {{ $transaction->notes }}
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                        <div>
                            <a href="{{ route('admin.transactions.edit', $transaction) }}" class="btn btn-warning me-2">
                                <i class="fas fa-edit me-1"></i>
                                تعديل
                            </a>
                            <form action="{{ route('admin.transactions.destroy', $transaction) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection