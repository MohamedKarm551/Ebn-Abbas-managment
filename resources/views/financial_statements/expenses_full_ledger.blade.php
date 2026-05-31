@extends('layouts.app')
@section('title', 'ملخص حسابات المصروفات')

@section('content')
<style>
@media print {
    /* إخفاء عناصر الفلترة والأزرار */
    .no-print,
    form,
    .btn,
    .d-flex.gap-2.mb-3,
    .card-header .badge,
    .alert,
    .btn-sm {
        display: none !important;
    }

    /* تحسين مظهر الجدول للطباعة */
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000 !important;
        padding: 6px;
    }
    
    .table-dark th {
        background-color: #f0f0f0 !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* منع انقطاع الصفوف بين الصفحات */
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    thead {
        display: table-header-group;
    }
    
    /* إخفاء الخلفيات الملونة في الطباعة */
    .bg-primary, 
    .bg-success, 
    .bg-danger,
    .bg-info,
    .bg-warning {
        background-color: white !important;
        color: black !important;
    }
    
    /* إزالة الهوامش الزائدة */
    body {
        margin: 0;
        padding: 0;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: white !important;
        border-bottom: 2px solid #000 !important;
        color: black !important;
    }
}
</style>
<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i> ملخص حسابات المصروفات</h4>
            <span class="badge bg-light text-danger px-3 py-2">
                <i class="fas fa-calendar-alt me-1"></i> 
                {{ now()->format('d/m/Y') }}
            </span>


            <div class="d-flex gap-2 mb-3 no-print">
                <button onclick="window.print();" class="btn btn-secondary btn-sm">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
                <a href="{{ route('accounts.statements.expenses', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
                <a href="{{ route('accounts.statements.expenses', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
            </div>

        </div>
        <div class="card-body">
            
            {{-- فلتر بنطاق تاريخي --}}
            <form method="GET" action="{{ route('accounts.statements.expenses') }}" class="row g-3 align-items-end mb-4 no-print">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">🔍 اسم حساب المصروف</label>
                    <input type="text" name="expense_name" class="form-control" 
                           placeholder="أدخل اسم المصروف..." 
                           value="{{ request('expense_name') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">📅 من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" 
                           value="{{ request('from_date') }}" onkeydown="return false">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">📅 إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" 
                           value="{{ request('to_date') }}" onkeydown="return false">
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                    <a href="{{ route('accounts.statements.expenses') }}" class="btn btn-secondary">
                        <i class="fas fa-sync-alt me-1"></i> مسح
                    </a>
                </div>
                <div class="col-md">
                    <div class="alert alert-info mb-0 text-center py-2">
                        <i class="fas fa-info-circle me-1"></i>
                        @if(request('from_date') && request('to_date'))
                            الحركات من <strong>{{ \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') }}</strong>
                            إلى <strong>{{ \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') }}</strong>
                        @elseif(request('from_date'))
                            الحركات من <strong>{{ \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') }}</strong> حتى الآن
                        @elseif(request('to_date'))
                            الحركات حتى <strong>{{ \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') }}</strong>
                        @else
                            <strong>جميع الحركات (بدون فلترة تاريخية)</strong>
                        @endif
                    </div>
                </div>
            </form>

            {{-- الجدول --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم حساب المصروف</th>
                            <th>كود الحساب</th>
                            <th>إجمالي المدين (ر.س)</th>
                            <th>إجمالي الدائن (ر.س)</th>
                            <th>الرصيد النهائي (ر.س)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $index => $account)
                            <tr onclick="window.location='{{ route('accounts.ledger', $account) }}'" style="cursor: pointer;">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $account->name }}</td>
                                <td>{{ $account->code }}</td>
                                <td class="text-success">{{ number_format($account->total_debit, 2) }}</td>
                                <td class="text-danger">{{ number_format($account->total_credit, 2) }}</td>
                                <td class="fw-bold {{ $account->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $account->abs_balance }} {{ $account->balance_type }}
                                </td>
                            <tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">لا توجد حسابات مصروفات تطابق معايير البحث</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($accounts->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3">الإجمالي</th>
                            <th class="text-success">{{ number_format($totalDebitAll, 2) }}</th>
                            <th class="text-danger">{{ number_format($totalCreditAll, 2) }}</th>
                            <th>{{ number_format(abs($totalBalanceAll), 2) }} ر.س</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection