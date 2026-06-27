<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>ميزان المراجعة</title>
    <style>
        /* خطوط نظيفة بدون ألوان - تصميم هادئ */
        @font-face {
            font-family: 'cairo';
            src: url({{ storage_path('fonts/cairo.ttf') }}) format('truetype');
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'cairo', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            background: #ffffff;
            color: #000000;
            direction: rtl;
        }

        /* رأس التقرير - بالكامل أبيض وأسود */
        .report-header {
            background: #ffffff;
            color: #000000;
            padding: 18px 24px 14px;
            margin-bottom: 20px;
            border-bottom: 1px solid #cccccc;
        }
        .report-header .company-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 4px;
            color: #000000;
        }
        .report-header .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 6px;
        }
        .report-header .report-meta {
            font-size: 10px;
            color: #555555;
            display: flex;
            gap: 24px;
        }
        .report-header .report-meta span {
            display: inline-block;
        }

        /* الجدول - حدود رمادية فقط، بدون ألوان خلفية */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        thead tr {
            background: #f5f5f5;
            color: #000000;
            border-bottom: 1px solid #aaaaaa;
        }
        thead th {
            padding: 9px 10px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #cccccc;
            background: #f5f5f5;
            color: #000000;
        }
        /* جميع الصفوف بخلفية بيضاء بدون تناوب */
        tbody tr {
            background: #ffffff;
        }
        tbody td {
            padding: 7px 10px;
            border: 1px solid #cccccc;
            color: #000000;
        }
        /* إلغاء تأثير hover */
        tbody tr:hover {
            background: #ffffff;
        }
        /* الخلايا النصية – ألوان عادية */
        .td-code {
            font-family: monospace;
            font-size: 10px;
            color: #333333;
            text-align: center;
            width: 110px;
        }
        .td-name {
            text-align: right;
            color: #000000;
        }
        .td-num {
            text-align: left;
            font-family: monospace;
            width: 140px;
            color: #000000;
        }
        /* إلغاء تلوين المدين والدائن والرصيد */
        .td-debit,
        .td-credit,
        .td-balance-pos,
        .td-balance-neg {
            color: #000000;
            font-weight: normal;
        }
        /* تذييل الجدول – رمادي فاتح بدون ألوان زاهية */
        tfoot tr {
            background: #f5f5f5;
            color: #000000;
            font-weight: bold;
            border-top: 1px solid #aaaaaa;
        }
        tfoot td {
            padding: 9px 10px;
            border: 1px solid #cccccc;
            font-size: 11px;
            background: #f5f5f5;
            color: #000000;
        }
        tfoot .td-num {
            color: #000000;
        }
        /* شارة التوازن: بدون خلفية ملونة، مجرد نص عادي */
        .balanced-badge {
            display: inline-block;
            padding: 0;
            border-radius: 0;
            font-size: 10.5px;
            font-weight: normal;
            background: transparent;
            color: #000000;
        }
        .balanced-badge.ok,
        .balanced-badge.err {
            background: transparent;
            color: #000000;
        }
        /* تذييل الصفحة */
        .page-footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #cccccc;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #666666;
        }
    </style>
</head>
<body>

    {{-- رأس التقرير بنظام الأبيض والأسود --}}
    <div class="report-header">
        <div class="company-name">النظام المحاسبي</div>
        <div class="report-title">ميزان المراجعة</div>
        <div class="report-meta">
            <span>من: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</span>
            <span>إلى: {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- الجدول الرئيسي بتصميم هادئ وبدون ألوان --}}
    <table>
        <thead>
            <tr>
                <th style="width:110px; text-align:center;">كود الحساب</th>
                <th>اسم الحساب</th>
                <th style="width:140px; text-align:left;">مدين</th>
                <th style="width:140px; text-align:left;">دائن</th>
                <th style="width:140px; text-align:left;">الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
                @include('financial-reports._trial_balance_node', ['account' => $account, 'level' => 0])
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;">الإجمالي</td>
                <td class="td-num">{{ number_format($totalDebit, 2) }}</td>
                <td class="td-num">{{ number_format($totalCredit, 2) }}</td>
                <td>
                    @if(abs($totalDebit - $totalCredit) < 0.01)
                        <span class="balanced-badge ok">متوازن ✓</span>
                    @else
                        <span class="balanced-badge err">غير متوازن</span>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- تذييل الصفحة بنفس الطابع الهادئ --}}
    <div class="page-footer">
        <span>النظام المحاسبي — ميزان المراجعة</span>
        <span>طُبع بتاريخ: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</body>
</html>