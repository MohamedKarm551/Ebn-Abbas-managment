<x-app-layout>
    <x-slot name="title">
    {{ 'قائمة الدخل' }}
    </x-slot>
<style>
    @media print {
    .no-print, form, .btn, .d-flex.gap-2.mb-3, .card-header .badge, .alert, .btn-sm { display: none !important; }
    .table { width: 100%; border-collapse: collapse; }
    .table-bordered th, .table-bordered td { border: 1px solid #000 !important; padding: 6px; }
    .table-dark th { background-color: #f0f0f0 !important; color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    thead { display: table-header-group; }
    .bg-primary, .bg-success, .bg-danger, .bg-info, .bg-warning { background-color: white !important; color: black !important; }
    body { margin: 0; padding: 0; }
    .container-fluid { padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { background-color: white !important; border-bottom: 2px solid #000 !important; color: black !important; }
    }
</style>

<div class="container-fluid px-4 mt-5" dir="rtl" style="width: 80%; box-sizing: border-box;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i> قائمة الدخل</h2>
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
    <form method="GET" action="{{ route('financial-reports.income-statement') }}" class="card p-3 mb-4" style="background:#1e1e1e; border-color:#444;">
        <div class="row g-3 align-items-end" style="color:white">
            <div class="col-md-4">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" onkeydown="return false">
            </div>
            <div class="col-md-4">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" onkeydown="return false">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100 fw-bold">
                    <i class="fas fa-search me-1"></i> عرض
                </button>
            </div>
        </div>
    </form>

    {{-- قائمة الدخل --}}
    <div class="card" style="background:#1a1a1a; border-color:#28a745; margin:0 auto;">
        <div class="card-header bg-success text-white text-center py-3">
            <h5 class="mb-1 fw-bold">قائمة الدخل</h5>
            <small>عن الفترة من
                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                حتى
                {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </small>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0" style="color:#fff; font-size:0.9rem;" dir="rtl">
                <thead>
                    <tr style="background:#1d3a2a;">
                        <th class="text-start" style="width:55%;">البيان</th>
                        <th class="text-end text-info" style="width:22%;">جزئي</th>
                        <th class="text-end text-warning" style="width:23%;">كلي</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- ══════════════════════════════════════
                         أولاً: إيرادات المبيعات
                    ══════════════════════════════════════ --}}
                    <tr style="background:#1a2e1a;">
                        <td colspan="3" class="fw-bold text-success py-2">
                            <i class="fas fa-plus-circle me-1" style="font-size:.8rem;"></i>
                            أولاً: إيرادات المبيعات
                        </td>
                    </tr>
                    @foreach($salesRevenues as $a)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-success">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @if($salesRevenues->isEmpty())
                    <tr><td colspan="3" class="text-center text-muted ps-4" style="font-size:.82rem;">لا توجد إيرادات مبيعات في هذه الفترة</td></tr>
                    @endif
                    <tr style="background:#162b16; font-weight:600;">
                        <td class="text-start ps-3">إجمالي إيرادات المبيعات</td>
                        <td></td>
                        <td class="text-end text-success fw-bold">{{ number_format($totalSalesRevenue, 2) }}</td>
                    </tr>

                    {{-- إيرادات أخرى --}}
                    @if($otherRevenues->isNotEmpty())
                    <tr style="background:#1a2e1a;">
                        <td colspan="3" class="fw-bold text-success py-2 ps-3" style="font-size:.85rem;">إيرادات أخرى</td>
                    </tr>
                    @foreach($otherRevenues as $a)
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

                    {{-- إجمالي الإيرادات --}}
                    <tr style="background:#0d2010; font-weight:bold; font-size:.95rem;">
                        <td class="text-start ps-3 text-white">إجمالي الإيرادات</td>
                        <td></td>
                        <td class="text-end text-success fw-bold">{{ number_format($totalRevenue, 2) }}</td>
                    </tr>

                    {{-- ══════════════════════════════════════
                         ★ ثانياً: تكلفة النشاط + مصروفات التشغيل
                    ══════════════════════════════════════ --}}
                    <tr style="background:#2b1a1a;">
                        <td colspan="3" class="fw-bold text-danger py-2">
                            <i class="fas fa-minus-circle me-1" style="font-size:.8rem;"></i>
                            ثانياً: (-) مصاريف تكلفة النشاط
                        </td>
                    </tr>

                    {{-- تكلفة النشاط (5.3) --}}
                    @foreach($costOfSales as $a)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @if($costOfSales->isEmpty())
                    <tr><td colspan="3" class="text-center text-muted ps-4" style="font-size:.82rem;">لا توجد تكاليف نشاط في هذه الفترة</td></tr>
                    @endif

                    {{-- ★ مصروفات التشغيل (5.2) — تحت تكلفة النشاط --}}
                    @if($opExpenses->isNotEmpty())
                    <tr style="background:#2b1a1a;">
                        <td colspan="3" class="fw-bold text-danger py-1 ps-4" style="font-size:.85rem;">
                            مصروفات التشغيل
                        </td>
                    </tr>
                    @foreach($opExpenses as $a)
                    <tr>
                        <td class="ps-5 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ★ إجمالي تكلفة الحصول على الإيراد = 5.3 + 5.2 --}}
                    <tr style="background:#200d0d; font-weight:600;">
                        <td class="text-start ps-3">إجمالي تكلفة الحصول على الإيراد</td>
                        <td></td>
                        <td class="text-end text-danger fw-bold">{{ number_format($totalCostOfSales, 2) }}</td>
                    </tr>

                    {{-- مجمل الربح --}}
                    <tr style="background:#0d2a30; font-weight:bold; font-size:1rem; border-top:2px solid #17a2b8;">
                        <td class="text-start ps-3 text-white">مجمل الربح</td>
                        <td></td>
                        <td class="text-end fw-bold" style="color:{{ $grossProfit >= 0 ? '#17a2b8' : '#dc3545' }};">
                            {{ number_format($grossProfit, 2) }}
                        </td>
                    </tr>

                    {{-- ══════════════════════════════════════
                         ★ ثالثاً: المصروفات العمومية والإدارية (5.1) فقط
                    ══════════════════════════════════════ --}}
                    <tr style="background:#2b1a1a;">
                        <td colspan="3" class="fw-bold text-danger py-2">
                            <i class="fas fa-minus-circle me-1" style="font-size:.8rem;"></i>
                            ثالثاً: (-) المصروفات العمومية والإدارية
                        </td>
                    </tr>
                    @foreach($adminExpenses as $a)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @if($adminExpenses->isEmpty())
                    <tr><td colspan="3" class="text-center text-muted ps-4" style="font-size:.82rem;">لا توجد مصروفات عمومية في هذه الفترة</td></tr>
                    @endif

                    {{-- ★ الإجمالي = عمومية فقط --}}
                    <tr style="background:#200d0d; font-weight:600;">
                        <td class="text-start ps-3">إجمالي المصروفات العمومية والإدارية</td>
                        <td></td>
                        <td class="text-end text-danger fw-bold">{{ number_format($totalAdminExpenses, 2) }}</td>
                    </tr>

                    {{-- صافي الربح قبل الضرائب --}}
                    <tr style="background:#0d2030; font-weight:bold; font-size:.95rem; border-top:2px solid #ffc107;">
                        <td class="text-start ps-3 text-white">صافي الربح المحاسبي قبل م.ضرائب عامة</td>
                        <td></td>
                        <td class="text-end fw-bold" style="color:{{ $netProfitBeforeTax >= 0 ? '#ffc107' : '#dc3545' }};">
                            {{ number_format($netProfitBeforeTax, 2) }}
                        </td>
                    </tr>

                    {{-- الضرائب --}}
                    <tr style="background:#2b1a1a;">
                        <td colspan="3" class="fw-bold text-danger py-2">
                            <i class="fas fa-minus-circle me-1" style="font-size:.8rem;"></i>
                            (-) مخصص الضرائب
                        </td>
                    </tr>
                    @foreach($taxAccounts as $a)
                    <tr>
                        <td class="ps-4 text-start">
                            <code class="text-secondary" style="font-size:.75rem;">{{ $a->code }}</code>
                            &nbsp;{{ $a->name }}
                        </td>
                        <td class="text-end text-danger">{{ number_format($a->balance, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    <tr style="background:#200d0d; font-weight:600;">
                        <td class="text-start ps-3">إجمالي مصروف الضرائب + المخصص</td>
                        <td></td>
                        <td class="text-end text-danger fw-bold">{{ number_format($totalTax, 2) }}</td>
                    </tr>

                    {{-- صافي الربح بعد الضرائب --}}
                    <tr style="font-weight:bold; font-size:1.05rem; border-top:3px solid {{ $netProfitAfterTax >= 0 ? '#28a745' : '#dc3545' }};">
                        <td class="text-start ps-3 text-white" style="background:#071a07;">صافي الربح بعد الضرائب</td>
                        <td style="background:#071a07;"></td>
                        <td class="text-end fw-bold py-3" style="background:#071a07; color:{{ $netProfitAfterTax >= 0 ? '#28a745' : '#dc3545' }}; font-size:1.1rem;">
                            {{ number_format($netProfitAfterTax, 2) }}
                            @if($netProfitAfterTax < 0)
                                <small class="d-block text-danger" style="font-size:.75rem;">(خسارة)</small>
                            @endif
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

</x-app-layout>