@extends('layouts.app')

@section('title', 'تفاصيل المصاريف الشهرية | ' . $expense->month_year)

@push('styles')
<style>
    .currency-badge-sar {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.2);
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 0.25rem;
    }
    
    .currency-badge-kwd {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border: 1px solid rgba(13, 110, 253, 0.2);
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 0.25rem;
    }
    
    .card-currency {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card-currency-sar {
        border-left: 4px solid #198754;
    }
    
    .card-currency-kwd {
        border-left: 4px solid #0d6efd;
    }
    
    .currency-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }
    
    .currency-header-sar {
        background-color: rgba(25, 135, 84, 0.1);
    }
    
    .currency-header-kwd {
        background-color: rgba(13, 110, 253, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">تفاصيل المصاريف الشهرية - {{ $expense->month_year }}</h1>
        <a href="{{ route('admin.monthly-expenses.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
    </div>
    @if($expense->unified_currency)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning bg-opacity-10">
                <h6 class="mb-0"><i class="fas fa-exchange-alt me-1"></i> معلومات تحويل العملة</h6>
            </div>
            <div class="card-body">
                <p>تم توحيد الحسابات بعملة: 
                    <strong>{{ $expense->unified_currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي' }}</strong>
                </p>
                @if($expense->exchange_rate)
                <p>سعر الصرف المستخدم: <strong>1 دينار = {{ number_format($expense->exchange_rate, 2) }} ريال</strong></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">البيانات الأساسية</h6>
                    <span class="badge bg-primary">{{ $expense->created_at->format('Y-m-d') }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th class="bg-light">الشهر</th>
                                    <td>{{ $expense->month_year }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">الفترة</th>
                                    <td>{{ $expense->start_date->format('Y-m-d') }} إلى {{ $expense->end_date->format('Y-m-d') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">ملخص الإيرادات</h5>
                                    
                                    <!-- الربح بالريال السعودي -->
                                    @if($expense->total_monthly_profit_SAR > 0)
                                    <div class="mb-3">
                                        <h6 class="text-success mb-2">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            إجمالي الربح بالريال السعودي:
                                        </h6>
                                        <div class="h4 text-success">
                                            {{ number_format($expense->total_monthly_profit_SAR, 2) }}
                                            <small>ريال</small>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- الربح بالدينار الكويتي -->
                                    @if($expense->total_monthly_profit_KWD > 0)
                                    <div>
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            إجمالي الربح بالدينار الكويتي:
                                        </h6>
                                        <div class="h4 text-primary">
                                            {{ number_format($expense->total_monthly_profit_KWD, 2) }}
                                            <small>دينار</small>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>تفاصيل المصاريف</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>البند</th>
                                            <th>المبلغ (ريال)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>إجمالي الرواتب</td>
                                            <td>{{ number_format($expense->salaries, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>إجمالي الإعلانات</td>
                                            <td>{{ number_format($expense->advertising, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>الإيجار</td>
                                            <td>{{ number_format($expense->rent, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>عمولات الموظفين</td>
                                            <td>{{ number_format($expense->staff_commissions, 2) }}</td>
                                        </tr>
                                        
                                        @if(!empty($expense->other_expenses))
                                            @foreach($expense->other_expenses as $otherExpense)
                                                <tr>
                                                    <td>{{ $otherExpense['name'] }}</td>
                                                    <td>{{ number_format($otherExpense['amount'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>إجمالي المصاريف</th>
                                            <th>{{ number_format($expense->total_expenses, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>صافي الربح وتوزيع الأرباح</h5>
                            
                            <div class="row">
                                <!-- الربح بالريال السعودي -->
                                @if($expense->total_monthly_profit_SAR > 0)
                                <div class="col-md-6 mb-4">
                                    <div class="card card-currency card-currency-sar">
                                        <div class="currency-header currency-header-sar d-flex justify-content-between align-items-center">
                                            <h6 class="m-0 text-success fw-bold">الأرباح بالريال السعودي</h6>
                                            <span class="currency-badge-sar">ريال</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                                        <span class="fw-bold">إجمالي الربح:</span>
                                                        <span>{{ number_format($expense->total_monthly_profit_SAR, 2) }} ريال</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                                        <span class="fw-bold">إجمالي المصاريف:</span>
                                                        <span>{{ number_format($expense->total_expenses, 2) }} ريال</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fw-bold">صافي الربح:</span>
                                                        <span class="text-success fw-bold">{{ number_format($expense->net_profit_SAR, 2) }} ريال</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="card bg-light mb-0">
                                                        <div class="card-body text-center py-3">
                                                            <h6>نصيب ش. إسماعيل</h6>
                                                            <h4 class="text-success mb-0">{{ number_format($expense->ismail_share_SAR, 2) }}</h4>
                                                            <small>ريال</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="card bg-light mb-0">
                                                        <div class="card-body text-center py-3">
                                                            <h6>نصيب محمد حسن</h6>
                                                            <h4 class="text-success mb-0">{{ number_format($expense->mohamed_share_SAR, 2) }}</h4>
                                                            <small>ريال</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- الربح بالدينار الكويتي -->
                                @if($expense->total_monthly_profit_KWD > 0)
                                <div class="col-md-6 mb-4">
                                    <div class="card card-currency card-currency-kwd">
                                        <div class="currency-header currency-header-kwd d-flex justify-content-between align-items-center">
                                            <h6 class="m-0 text-primary fw-bold">الأرباح بالدينار الكويتي</h6>
                                            <span class="currency-badge-kwd">دينار</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                                        <span class="fw-bold">إجمالي الربح:</span>
                                                        <span>{{ number_format($expense->total_monthly_profit_KWD, 2) }} دينار</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                                        <span class="fw-bold">المصاريف (على الدينار):</span>
                                                        <span>0.00 دينار</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fw-bold">صافي الربح:</span>
                                                        <span class="text-primary fw-bold">{{ number_format($expense->net_profit_KWD, 2) }} دينار</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="card bg-light mb-0">
                                                        <div class="card-body text-center py-3">
                                                            <h6>نصيب ش. إسماعيل</h6>
                                                            <h4 class="text-primary mb-0">{{ number_format($expense->ismail_share_KWD, 2) }}</h4>
                                                            <small>دينار</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="card bg-light mb-0">
                                                        <div class="card-body text-center py-3">
                                                            <h6>نصيب محمد حسن</h6>
                                                            <h4 class="text-primary mb-0">{{ number_format($expense->mohamed_share_KWD, 2) }}</h4>
                                                            <small>دينار</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-12 mt-3">
                            @if($expense->notes)
                                <div class="card">
                                    <div class="card-header">ملاحظات</div>
                                    <div class="card-body">
                                        <p>{{ $expense->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
            <script src="{{ asset('js/preventClick.js') }}"></script>
@endpush