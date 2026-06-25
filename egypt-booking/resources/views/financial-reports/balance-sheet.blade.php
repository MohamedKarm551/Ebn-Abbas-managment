<x-app-layout>
    <x-slot name="title">
    {{ 'الميزانية العمومية' }}
    </x-slot>
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
<div class="container-fluid px-4 mt-5" dir="rtl" style="width: 80%; box-sizing: border-box;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-landmark me-2 text-warning"></i> الميزانية العمومية</h2>
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
<form method="GET" action="{{ route('financial-reports.balance-sheet') }}" class="card p-3 mb-4" style="background:#1e1e1e; border-color:#444;">
    <div class="row g-3 align-items-end" style="color:white">
        <div class="col-md-4">
            <label class="form-label">من تاريخ</label>
            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}" onkeydown="return false">
        </div>
        <div class="col-md-4">
            <label class="form-label">إلى تاريخ</label>
            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" onkeydown="return false">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">
                <i class="fas fa-search me-1"></i> عرض
            </button>
        </div>
    </div>
</form>

    {{-- الميزانية العمومية --}}
    <div class="card" style="background:#1a1a1a; border-color:#ffc107; margin:0 auto;">

        {{-- رأس الميزانية --}}
        <div class="card-header text-center py-3" style="background:#1d2a00; border-color:#ffc107;">
            <h5 class="text-warning fw-bold mb-1">الميزانية العمومية</h5>
            <small class="text-light">
    {{ isset($dateFrom) ? 'من ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' ' : '' }}
    حتى {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
</small>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0" style="color:#fff; font-size:0.9rem;" dir="rtl">
                <thead>
                    <tr style="background:#252525;">
                        <th class="text-start" style="width:52%;">البيان</th>
                        <th class="text-end text-info" style="width:24%;">جزئي</th>
                        <th class="text-end text-warning" style="width:24%;">كلي</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- ══════════════════════════════════════
                         الأصول الثابتة (111x)
                    ══════════════════════════════════════ --}}
                    <tr style="background:#1d3557;">
                        <td colspan="3" class="fw-bold text-info py-2">
                            <i class="fas fa-building me-2" style="font-size:.8rem;"></i>
                            الأصول الثابتة
                        </td>
                    </tr>

                    @foreach($fixedAssets as $a)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach

                    @if($fixedAssets->isEmpty())
                    <tr><td colspan="3" class="text-center text-muted ps-4" style="font-size:.82rem;">لا توجد أصول ثابتة</td></tr>
                    @endif

                    {{-- مجمع الإهلاك (112x) --}}
                    @if($depreciation->isNotEmpty())
                    <tr style="background:#1a1a2a;">
                        <td colspan="3" class="fw-bold text-danger ps-3 py-1" style="font-size:.85rem;">
                            (-) مجمع إهلاك الأصول الثابتة
                        </td>
                    </tr>
                    @foreach($depreciation as $a)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-danger">({{ number_format($a->balance, 2) }})</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- صافي الأصول الثابتة --}}
                    <tr style="background:#162535; font-weight:bold;">
                        <td class="text-start ps-3">صافي الأصول الثابتة</td>
                        <td></td>
                        <td class="text-end text-info fw-bold">{{ number_format($netFixed, 2) }}</td>
                    </tr>

                    {{-- ══════════════════════════════════════
                         الأصول المتداولة (12x)
                    ══════════════════════════════════════ --}}
                    <tr style="background:#1d3557;">
                        <td colspan="3" class="fw-bold text-info py-2">
                            <i class="fas fa-wallet me-2" style="font-size:.8rem;"></i>
                            الأصول المتداولة
                        </td>
                    </tr>

                    {{-- الخزينة (1211x) --}}
                    @if($cashAccounts->isNotEmpty())
                    <tr style="background:#1a2a1a;">
                        <td colspan="3" class="text-success ps-3 py-1 fw-bold" style="font-size:.82rem;">رصيد الخزينة</td>
                    </tr>
                    @foreach($cashAccounts as $a)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- البنوك (1212x) --}}
                    @if($bankAccounts->isNotEmpty())
                    <tr style="background:#1a2a1a;">
                        <td colspan="3" class="text-success ps-3 py-1 fw-bold" style="font-size:.82rem;">أرصدة البنوك</td>
                    </tr>
                    @foreach($bankAccounts as $a)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- أوراق القبض (1213x) --}}
                    @if($notesReceivable->isNotEmpty())
                    @foreach($notesReceivable as $a)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- العملاء (1221x) --}}
                    @if($clientsAccounts->isNotEmpty())
                    <tr style="background:#1a2a1a;">
                        <td colspan="3" class="text-success ps-3 py-1 fw-bold" style="font-size:.82rem;">أرصدة العملاء</td>
                    </tr>
                    @foreach($clientsAccounts as $a)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- مدينون آخرون + أرصدة مدينة أخرى (1222x, 1223x) --}}
                    @if($otherDebtors->isNotEmpty() || $otherDebitBalance->isNotEmpty())
                    <tr style="background:#1a2a1a;">
                        <td colspan="3" class="text-success ps-3 py-1 fw-bold" style="font-size:.82rem;">أرصدة مدينة أخرى</td>
                    </tr>
                    @foreach($otherDebtors as $a)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @foreach($otherDebitBalance as $a)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- إجمالي الأصول المتداولة --}}
                    <tr style="background:#162535; font-weight:bold;">
                        <td class="text-start ps-3">إجمالي الأصول المتداولة</td>
                        <td></td>
                        <td class="text-end text-info fw-bold">{{ number_format($totalCurrentAssets, 2) }}</td>
                    </tr>

                    {{-- إجمالي الأصول --}}
                    <tr style="background:#0d2035; font-weight:bold; font-size:1rem; border-top:2px solid #17a2b8;">
                        <td class="text-white text-start ps-3 fw-bold">إجمالي الأصول</td>
                        <td></td>
                        <td class="text-end text-warning fw-bold">{{ number_format($totalAssets, 2) }}</td>
                    </tr>

                    {{-- ══════════════════════════════════════
                         الالتزامات قصيرة الأجل (22x)
                    ══════════════════════════════════════ --}}
                    <tr style="background:#3a1515; border-top:3px solid #dc3545;">
                        <td colspan="3" class="fw-bold text-danger py-2">
                            <i class="fas fa-hand-paper me-2" style="font-size:.8rem;"></i>
                            الالتزامات قصيرة الأجل
                        </td>
                    </tr>

                    {{-- الموردون (221x) --}}
                    @if($suppliers->isNotEmpty())
                    <tr style="background:#2b1515;">
                        <td colspan="3" class="text-danger ps-3 py-1 fw-bold" style="font-size:.82rem;">أرصدة الموردين</td>
                    </tr>
                    @foreach($suppliers as $l)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $l->code }}</code>
                            &nbsp;{{ $l->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($l->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- أوراق الدفع (222x) --}}
                    @if($notePayable->isNotEmpty())
                    @foreach($notePayable as $l)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $l->code }}</code>
                            &nbsp;{{ $l->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($l->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- التأمينات الاجتماعية (2231) --}}
                    @if($socialInsurance->isNotEmpty())
                    @foreach($socialInsurance as $l)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $l->code }}</code>
                            &nbsp;{{ $l->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($l->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ضرائب كسب العمل (2232) --}}
                    @if($taxWithholding->isNotEmpty())
                    <tr style="background:#2b1515;">
                        <td colspan="3" class="text-danger ps-3 py-1 fw-bold" style="font-size:.82rem;">مخصص ضرائب عامة وزكاه</td>
                    </tr>
                    @foreach($taxWithholding as $l)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $l->code }}</code>
                            &nbsp;{{ $l->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($l->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- أرصدة دائنة أخرى (224x) --}}
                    @if($otherCreditors->isNotEmpty())
                    <tr style="background:#2b1515;">
                        <td colspan="3" class="text-danger ps-3 py-1 fw-bold" style="font-size:.82rem;">أرصدة دائنة أخرى</td>
                    </tr>
                    @foreach($otherCreditors as $l)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $l->code }}</code>
                            &nbsp;{{ $l->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($l->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- إجمالي الالتزامات قصيرة الأجل --}}
                    <tr style="background:#1f0f0f; font-weight:bold;">
                        <td class="text-start ps-3">إجمالي الالتزامات قصيرة الأجل</td>
                        <td></td>
                        <td class="text-end text-danger fw-bold">{{ number_format($totalCurrentLiab, 2) }}</td>
                    </tr>

                    {{-- الالتزامات طويلة الأجل (21x) --}}
                    @if($longTermLoans->isNotEmpty())
                    <tr style="background:#3a1515;">
                        <td colspan="3" class="fw-bold text-danger py-2 ps-3" style="font-size:.85rem;">
                            الالتزامات طويلة الأجل
                        </td>
                    </tr>
                    @foreach($longTermLoans as $l)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $l->code }}</code>
                            &nbsp;{{ $l->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($l->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    <tr style="background:#1f0f0f; font-weight:bold;">
                        <td class="text-start ps-3">إجمالي الالتزامات طويلة الأجل</td>
                        <td></td>
                        <td class="text-end text-danger fw-bold">{{ number_format($totalLongTermLiab, 2) }}</td>
                    </tr>
                    @endif

                    {{-- رأس المال العامل --}}
                    <tr style="background:#1a1a00; font-weight:bold; border-top:2px solid #ffc107;">
                        <td class="text-start ps-3 text-white">رأس المال العامل</td>
                        <td></td>
                        <td class="text-end fw-bold" style="color:{{ $workingCapital >= 0 ? '#ffc107' : '#dc3545' }};">
                            {{ number_format($workingCapital, 2) }}
                        </td>
                    </tr>

                    {{-- إجمالي الاستثمارات --}}
                    <tr style="background:#101a10; font-weight:bold; font-size:1rem;">
                        <td class="text-start ps-3 text-white">إجمالي الاستثمارات ويتم تمويلها على النحو التالي</td>
                        <td></td>
                        <td class="text-end text-warning fw-bold">{{ number_format($netFixed + $totalCurrentAssets - $totalCurrentLiab, 2) }}</td>
                    </tr>

                    {{-- ══════════════════════════════════════
                         حقوق الملكية
                    ══════════════════════════════════════ --}}
                    <tr style="background:#163316; border-top:3px solid #28a745;">
                        <td colspan="3" class="fw-bold text-success py-2">
                            <i class="fas fa-user-shield me-2" style="font-size:.8rem;"></i>
                            حقوق الملكية
                        </td>
                    </tr>

                    {{-- حسابات رأس المال (5x) --}}
                    @foreach($capitalAccounts as $e)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $e->code }}</code>
                            &nbsp;{{ $e->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($e->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach

                    {{-- صافي الربح / الخسارة --}}
                    <tr>
                        <td class="ps-4 text-start fw-bold">
                            <i class="fas {{ $netProfit >= 0 ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger' }} me-1" style="font-size:.8rem;"></i>
                            صافي الربح
                        </td>
                        <td class="text-end {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($netProfit, 2) }}
                            @if($netProfit < 0) <small>(خسارة)</small> @endif
                        </td>
                        <td></td>
                    </tr>

                    {{-- إجمالي حقوق الملكية --}}
                    <tr style="background:#0a2a0a; font-weight:bold;">
                        <td class="text-start ps-3">إجمالي حقوق الملكية</td>
                        <td></td>
                        <td class="text-end text-success fw-bold">{{ number_format($totalEquity, 2) }}</td>
                    </tr>

                    {{-- إجمالي مصادر التمويل --}}
                    <tr style="background:#071a07; font-weight:bold; font-size:1rem; border-top:2px solid #28a745;">
                        <td class="text-white text-start ps-3 fw-bold">إجمالي مصادر التمويل</td>
                        <td></td>
                        <td class="text-end text-warning fw-bold">{{ number_format($totalFunding, 2) }}</td>
                    </tr>

                    {{-- فرق الميزانية --}}
                    <tr style="background:{{ abs($balanceDiff) < 0.01 ? '#061806' : '#2a1a00' }}; font-weight:bold;">
                        <td class="text-start ps-3" style="color:{{ abs($balanceDiff) < 0.01 ? '#28a745' : '#ffc107' }};">
                            @if(abs($balanceDiff) < 0.01)
                                <i class="fas fa-check-circle me-1"></i> الميزانية متوازنة ✅
                            @else
                                <i class="fas fa-exclamation-triangle me-1"></i> فرق الميزانية
                            @endif
                        </td>
                        <td></td>
                        <td class="text-end fw-bold" style="color:{{ abs($balanceDiff) < 0.01 ? '#28a745' : '#ffc107' }};">
                            {{ number_format(abs($balanceDiff), 2) }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
@media print {
    .btn, form { display: none !important; }
    body { background:#fff !important; color:#000 !important; }
    .card { border:1px solid #000 !important; background:#fff !important; }
    table { color:#000 !important; }
    td, th { border:1px solid #ccc !important; }
    .text-success, .text-danger, .text-info, .text-warning, .text-white { color:#000 !important; }
    tr[style*="background"] { background:#f5f5f5 !important; }
    code { color:#444 !important; }
}
</style>
</x-app-layout>