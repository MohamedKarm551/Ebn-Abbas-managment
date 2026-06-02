<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الميزانية العمومية</title>
    <style>
        @font-face {
            font-family: 'cairo';
            src: url({{ storage_path('fonts/cairo.ttf') }}) format('truetype');
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'cairo','DejaVu Sans',sans-serif; font-size:12px; background:#fff; color:#000; direction:rtl; }

        .rep-header { border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:14px; }
        .rep-header .co { font-size:11px; color:#555; }
        .rep-header .title { font-size:20px; font-weight:bold; }
        .rep-header .meta { font-size:10px; color:#666; margin-top:4px; }

        /* بطاقات الملخص — صف أفقي */
        .cards-row { display:flex; gap:8px; margin-bottom:16px; }
        .card-s { flex:1; border:1px solid #ccc; background:#fafafa; padding:9px 12px; text-align:center; }
        .card-s .lbl { font-size:9px; color:#555; margin-bottom:3px; }
        .card-s .val { font-size:13px; font-weight:bold; font-family:monospace; color:#000; }

        table { width:100%; border-collapse:collapse; font-size:11px; }
        th, td { border:1px solid #ccc; padding:6px 10px; }
        thead tr { background:#f0f0f0; }
        thead th { font-weight:bold; }

        .sec-hd { background:#f0f0f0; font-weight:bold; }
        .sub-hd { background:#f8f8f8; font-weight:bold; font-size:10px; color:#444; padding-right:24px; }
        .total-row { background:#f5f5f5; font-weight:bold; }
        .grand-row { font-weight:bold; font-size:12px; border-top:2px solid #333; }
        .txt-r { text-align:left; font-family:monospace; }
        .ps-4 { padding-right:24px; }
        .ps-5 { padding-right:40px; }
        .muted { font-size:9.5px; color:#555; font-family:monospace; }
        .footer { margin-top:14px; padding-top:8px; border-top:1px solid #ccc; display:flex; justify-content:space-between; font-size:9px; color:#666; }
        .sec-sep { border-top:2px solid #999; }
    </style>
</head>
<body>

<div class="rep-header">
    <div class="co">النظام المحاسبي</div>
    <div class="title">الميزانية العمومية</div>
    <div class="meta">
        {{ isset($dateFrom) ? 'من ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' ' : '' }}
        حتى {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        &nbsp;|&nbsp; تاريخ الطباعة: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

{{-- بطاقات الملخص — صف أفقي --}}
<div class="cards-row">
    <div class="card-s">
        <div class="lbl">إجمالي الأصول</div>
        <div class="val">{{ number_format($totalAssets, 2) }}</div>
    </div>
    <div class="card-s">
        <div class="lbl">إجمالي الالتزامات</div>
        <div class="val">{{ number_format($totalLiabilities, 2) }}</div>
    </div>
    <div class="card-s">
        <div class="lbl">حقوق الملكية</div>
        <div class="val">{{ number_format($totalEquity, 2) }}</div>
    </div>
    <div class="card-s">
        <div class="lbl">حالة الميزانية</div>
        <div class="val">
            @if(abs($balanceDiff) < 0.01) ✓ متوازنة @else ⚠ {{ number_format(abs($balanceDiff), 2) }} @endif
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:55%; text-align:right;">البيان</th>
            <th style="width:22%; text-align:left;">جزئي</th>
            <th style="width:23%; text-align:left;">كلي</th>
        </tr>
    </thead>
    <tbody>

        {{-- أولاً: الأصول الثابتة --}}
        <tr class="sec-hd"><td colspan="3">أولاً: الأصول الثابتة</td></tr>
        @forelse($fixedAssets as $a)
        <tr>
            <td class="ps-4"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center; color:#999;">لا توجد أصول ثابتة</td></tr>
        @endforelse

        @if($depreciation->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">(-) مجمع إهلاك الأصول الثابتة</td></tr>
        @foreach($depreciation as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">({{ number_format($a->balance, 2) }})</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        <tr class="total-row"><td>صافي الأصول الثابتة</td><td></td><td class="txt-r">{{ number_format($netFixed, 2) }}</td></tr>

        {{-- ثانياً: الأصول المتداولة --}}
        <tr class="sec-hd"><td colspan="3">ثانياً: الأصول المتداولة</td></tr>

        @if($cashAccounts->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">رصيد الخزينة</td></tr>
        @foreach($cashAccounts as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($bankAccounts->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">أرصدة البنوك</td></tr>
        @foreach($bankAccounts as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($notesReceivable->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">أوراق القبض</td></tr>
        @foreach($notesReceivable as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($clientsAccounts->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">أرصدة العملاء</td></tr>
        @foreach($clientsAccounts as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($otherDebtors->isNotEmpty() || $otherDebitBalance->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">أرصدة مدينة أخرى</td></tr>
        @foreach($otherDebtors as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @foreach($otherDebitBalance as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        <tr class="total-row"><td>إجمالي الأصول المتداولة</td><td></td><td class="txt-r">{{ number_format($totalCurrentAssets, 2) }}</td></tr>
        <tr class="grand-row"><td>إجمالي الأصول</td><td></td><td class="txt-r">{{ number_format($totalAssets, 2) }}</td></tr>

        {{-- ثالثاً: الالتزامات قصيرة الأجل --}}
        <tr class="sec-hd sec-sep"><td colspan="3">ثالثاً: الالتزامات قصيرة الأجل</td></tr>

        @if($suppliers->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">أرصدة الموردين</td></tr>
        @foreach($suppliers as $l)
        <tr>
            <td class="ps-5"><span class="muted">{{ $l->code }}</span>&nbsp;{{ $l->name }}</td>
            <td class="txt-r">{{ number_format($l->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($notePayable->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">أوراق الدفع</td></tr>
        @foreach($notePayable as $l)
        <tr>
            <td class="ps-5"><span class="muted">{{ $l->code }}</span>&nbsp;{{ $l->name }}</td>
            <td class="txt-r">{{ number_format($l->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($socialInsurance->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">التأمينات الاجتماعية</td></tr>
        @foreach($socialInsurance as $l)
        <tr>
            <td class="ps-5"><span class="muted">{{ $l->code }}</span>&nbsp;{{ $l->name }}</td>
            <td class="txt-r">{{ number_format($l->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($taxWithholding->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">مخصص ضرائب عامة وزكاة</td></tr>
        @foreach($taxWithholding as $l)
        <tr>
            <td class="ps-5"><span class="muted">{{ $l->code }}</span>&nbsp;{{ $l->name }}</td>
            <td class="txt-r">{{ number_format($l->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        @if($otherCreditors->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">أرصدة دائنة أخرى</td></tr>
        @foreach($otherCreditors as $l)
        <tr>
            <td class="ps-5"><span class="muted">{{ $l->code }}</span>&nbsp;{{ $l->name }}</td>
            <td class="txt-r">{{ number_format($l->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        <tr class="total-row"><td>إجمالي الالتزامات قصيرة الأجل</td><td></td><td class="txt-r">{{ number_format($totalCurrentLiab, 2) }}</td></tr>

        {{-- رابعاً: الالتزامات طويلة الأجل --}}
        @if($longTermLoans->isNotEmpty())
        <tr class="sec-hd"><td colspan="3">رابعاً: الالتزامات طويلة الأجل</td></tr>
        @foreach($longTermLoans as $l)
        <tr>
            <td class="ps-4"><span class="muted">{{ $l->code }}</span>&nbsp;{{ $l->name }}</td>
            <td class="txt-r">{{ number_format($l->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        <tr class="total-row"><td>إجمالي الالتزامات طويلة الأجل</td><td></td><td class="txt-r">{{ number_format($totalLongTermLiab, 2) }}</td></tr>
        @endif

        <tr class="total-row"><td>رأس المال العامل</td><td></td><td class="txt-r">{{ number_format($workingCapital, 2) }}</td></tr>
        <tr class="total-row"><td>إجمالي الاستثمارات ويتم تمويلها على النحو التالي</td><td></td><td class="txt-r">{{ number_format($netFixed + $totalCurrentAssets - $totalCurrentLiab, 2) }}</td></tr>

        {{-- خامساً: حقوق الملكية --}}
        <tr class="sec-hd sec-sep"><td colspan="3">خامساً: حقوق الملكية</td></tr>
        @foreach($capitalAccounts as $e)
        <tr>
            <td class="ps-4"><span class="muted">{{ $e->code }}</span>&nbsp;{{ $e->name }}</td>
            <td class="txt-r">{{ number_format($e->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        <tr>
            <td class="ps-4" style="font-weight:bold;">
                صافي الربح {{ $netProfit < 0 ? '(خسارة)' : '' }}
            </td>
            <td class="txt-r" style="font-weight:bold;">{{ number_format($netProfit, 2) }}</td>
            <td></td>
        </tr>
        <tr class="total-row"><td>إجمالي حقوق الملكية</td><td></td><td class="txt-r">{{ number_format($totalEquity, 2) }}</td></tr>

        <tr class="grand-row"><td>إجمالي مصادر التمويل (الخصوم + حقوق الملكية)</td><td></td><td class="txt-r">{{ number_format($totalFunding, 2) }}</td></tr>

        <tr class="total-row">
            <td>
                @if(abs($balanceDiff) < 0.01) ✓ الميزانية متوازنة @else ⚠ فرق الميزانية @endif
            </td>
            <td></td>
            <td class="txt-r">{{ number_format(abs($balanceDiff), 2) }}</td>
        </tr>

    </tbody>
</table>

<div class="footer">
    <span>النظام المحاسبي — الميزانية العمومية</span>
    <span>طُبع بتاريخ: {{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>