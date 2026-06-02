@extends('layouts.app')
@section('title', 'ميزان المراجعة')
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
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-balance-scale me-2 text-info"></i> ميزان المراجعة</h2>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i class="fas fa-print me-1"></i> طباعة
            </button>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
        </div>
    </div>

    {{-- فلتر التاريخ --}}
    <form method="GET" class="card p-3 mb-4" style="background:#1e1e1e; border-color:#444;">
        <div class="row g-3 align-items-end" style="color: white;">
            <div class="col-md-4">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" onkeydown="return false">
            </div>
            <div class="col-md-4">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" onkeydown="return false">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-info w-100">
                    <i class="fas fa-search me-1"></i> عرض
                </button>
            </div>
        </div>
    </form>

    {{-- الجدول --}}
    <div class="card" style="background:#1a1a1a; border-color:#444;">
        <div class="card-header text-center" style="background:#2d2d2d;color: white;">
            <h5 class="mb-0">ميزان المراجعة</h5>
            <small >من {{ $dateFrom }} إلى {{ $dateTo }}</small>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover text-center mb-0" style="color:#fff;">
                <thead style="background:#333;">
                    <tr>
                        <th>كود الحساب</th>
                        <th>اسم الحساب</th>
                        <th class="text-success">مدين</th>
                        <th class="text-danger">دائن</th>
                        <th>الرصيد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                    <tr>
                        <td><code>{{ $account->code }}</code></td>
                        <td class="text-start">{{ $account->name }}</td>
                        <td class="text-success">{{ number_format($account->total_debit, 2) }}</td>
                        <td class="text-danger">{{ number_format($account->total_credit, 2) }}</td>
                        <td class="{{ $account->balance >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format(abs($account->balance), 2) }}
                            <small>{{ $account->balance >= 0 ? 'مدين' : 'دائن' }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="background:#2d2d2d; font-weight:bold;">
                    <tr>
                        <td colspan="2">الإجمالي</td>
                        <td class="text-success">{{ number_format($totalDebit, 2) }}</td>
                        <td class="text-danger">{{ number_format($totalCredit, 2) }}</td>
                        <td class="{{ $totalDebit == $totalCredit ? 'text-success' : 'text-warning' }}">
                            @if($totalDebit == $totalCredit)
                                <i class="fas fa-check-circle"></i> متوازن
                            @else
                                <i class="fas fa-exclamation-triangle"></i> غير متوازن
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection