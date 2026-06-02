<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>قائمة الدخل</title>
    <style>
        @font-face {
            font-family: 'cairo';
            src: url({{ storage_path('fonts/cairo.ttf') }}) format('truetype');
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'cairo', 'DejaVu Sans', sans-serif; font-size: 12px; background: #fff; color: #000; direction: rtl; }

        .rep-header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 14px; }
        .rep-header .co  { font-size: 11px; color: #555; }
        .rep-header .title { font-size: 20px; font-weight: bold; }
        .rep-header .meta  { font-size: 10px; color: #666; margin-top: 4px; }

        /* بطاقات الملخص — صف أفقي */
        .cards-row { display: flex; gap: 8px; margin-bottom: 16px; }
        .card-s { flex: 1; border: 1px solid #ccc; background: #fafafa; padding: 9px 12px; text-align: center; }
        .card-s .lbl { font-size: 9px; color: #555; margin-bottom: 3px; }
        .card-s .val { font-size: 13px; font-weight: bold; font-family: monospace; color: #000; }

        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; }
        thead tr { background: #f0f0f0; }
        thead th { font-weight: bold; }

        .sec-hd    { background: #f0f0f0; font-weight: bold; }
        .sub-hd    { background: #f8f8f8; font-weight: bold; font-size: 10px; color: #444; }
        .total-row { background: #f5f5f5; font-weight: bold; }
        .grand-row { font-weight: bold; font-size: 12px; border-top: 2px solid #333; }
        .txt-r  { text-align: left; font-family: monospace; }
        .ps-4   { padding-right: 24px; }
        .ps-5   { padding-right: 40px; }
        .muted  { font-size: 9.5px; color: #555; font-family: monospace; }
        .footer { margin-top: 14px; padding-top: 8px; border-top: 1px solid #ccc;
                  display: flex; justify-content: space-between; font-size: 9px; color: #666; }
    </style>
</head>
<body>

<div class="rep-header">
    <div class="co">النظام المحاسبي</div>
    <div class="title">قائمة الدخل</div>
    <div class="meta">
        عن الفترة من {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
        حتى {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        &nbsp;|&nbsp; تاريخ الطباعة: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

{{-- بطاقات الملخص --}}
<div class="cards-row">
    <div class="card-s">
        <div class="lbl">إجمالي الإيرادات</div>
        <div class="val">{{ number_format($totalRevenue, 2) }}</div>
    </div>
    <div class="card-s">
        <div class="lbl">إجمالي التكاليف</div>
        <div class="val">{{ number_format($totalCostOfSales + $totalAdminExpenses, 2) }}</div>
    </div>
    <div class="card-s">
        <div class="lbl">مجمل الربح</div>
        <div class="val">{{ number_format($grossProfit, 2) }}</div>
    </div>
    <div class="card-s">
        <div class="lbl">صافي الربح بعد الضرائب</div>
        <div class="val">{{ number_format($netProfitAfterTax, 2) }}</div>
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

        {{-- أولاً: إيرادات المبيعات --}}
        <tr class="sec-hd"><td colspan="3">أولاً: إيرادات المبيعات</td></tr>
        @forelse($salesRevenues as $a)
        <tr>
            <td class="ps-4"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center; color:#999;">لا توجد إيرادات مبيعات</td></tr>
        @endforelse
        <tr class="total-row">
            <td>إجمالي إيرادات المبيعات</td>
            <td></td>
            <td class="txt-r">{{ number_format($totalSalesRevenue, 2) }}</td>
        </tr>

        {{-- إيرادات أخرى --}}
        @if($otherRevenues->isNotEmpty())
        <tr class="sub-hd"><td colspan="3">إيرادات أخرى</td></tr>
        @foreach($otherRevenues as $a)
        <tr>
            <td class="ps-4"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        <tr class="total-row">
            <td>إجمالي الإيرادات</td>
            <td></td>
            <td class="txt-r">{{ number_format($totalRevenue, 2) }}</td>
        </tr>

        {{-- ★ ثانياً: تكلفة النشاط + مصروفات التشغيل معاً --}}
        <tr class="sec-hd"><td colspan="3">ثانياً: (-) مصاريف تكلفة النشاط</td></tr>
        @forelse($costOfSales as $a)
        <tr>
            <td class="ps-4"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center; color:#999;">لا توجد تكاليف نشاط</td></tr>
        @endforelse

        {{-- ★ مصروفات التشغيل ضمن قسم التكلفة --}}
        @if($opExpenses->isNotEmpty())
        <tr class="sub-hd"><td colspan="3" style="padding-right:28px;">مصروفات التشغيل</td></tr>
        @foreach($opExpenses as $a)
        <tr>
            <td class="ps-5"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif

        {{-- ★ الإجمالي = 5.3 + 5.2 --}}
        <tr class="total-row">
            <td>إجمالي تكلفة الحصول على الإيراد</td>
            <td></td>
            <td class="txt-r">{{ number_format($totalCostOfSales, 2) }}</td>
        </tr>

        {{-- مجمل الربح --}}
        <tr class="grand-row">
            <td>مجمل الربح</td>
            <td></td>
            <td class="txt-r">{{ number_format($grossProfit, 2) }}</td>
        </tr>

        {{-- ★ ثالثاً: المصروفات العمومية والإدارية (5.1) لوحدها --}}
        <tr class="sec-hd"><td colspan="3">ثالثاً: (-) المصروفات العمومية والإدارية</td></tr>
        @forelse($adminExpenses as $a)
        <tr>
            <td class="ps-4"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center; color:#999;">لا توجد مصروفات عمومية</td></tr>
        @endforelse

        {{-- ★ الإجمالي = عمومية فقط --}}
        <tr class="total-row">
            <td>إجمالي المصروفات العمومية والإدارية</td>
            <td></td>
            <td class="txt-r">{{ number_format($totalAdminExpenses, 2) }}</td>
        </tr>

        {{-- صافي الربح قبل الضرائب --}}
        <tr class="grand-row">
            <td>صافي الربح المحاسبي قبل م.ضرائب عامة</td>
            <td></td>
            <td class="txt-r">{{ number_format($netProfitBeforeTax, 2) }}</td>
        </tr>

        {{-- الضرائب --}}
        @if($taxAccounts->isNotEmpty())
        <tr class="sec-hd"><td colspan="3">(-) مخصص الضرائب</td></tr>
        @foreach($taxAccounts as $a)
        <tr>
            <td class="ps-4"><span class="muted">{{ $a->code }}</span>&nbsp;{{ $a->name }}</td>
            <td class="txt-r">{{ number_format($a->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td>إجمالي مصروف الضرائب + المخصص</td>
            <td></td>
            <td class="txt-r">{{ number_format($totalTax, 2) }}</td>
        </tr>
        @endif

        {{-- صافي الربح بعد الضرائب --}}
        <tr class="grand-row">
            <td style="font-weight:bold;">✦ صافي الربح بعد الضرائب</td>
            <td></td>
            <td class="txt-r" style="font-size:13px;">
                {{ number_format($netProfitAfterTax, 2) }}
                @if($netProfitAfterTax < 0)<small>(خسارة)</small>@endif
            </td>
        </tr>

    </tbody>
</table>

<div class="footer">
    <span>النظام المحاسبي — قائمة الدخل</span>
    <span>طُبع بتاريخ: {{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>