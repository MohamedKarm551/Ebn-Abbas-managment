<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Cairo', 'DejaVu Sans', sans-serif; margin: 20px; }
        h2 { text-align: center; color: #333; }
        .info { margin-bottom: 20px; font-size: 12px; border-bottom: 1px solid #ccc; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: right; font-size: 12px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .total-row { background-color: #f9f9f9; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <div class="info">
        @if($fromDate && $toDate)
            <div><strong>الفترة:</strong> من {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} إلى {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</div>
        @elseif($fromDate)
            <div><strong>الفترة:</strong> من {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}</div>
        @elseif($toDate)
            <div><strong>الفترة:</strong> حتى {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</div>
        @endif
        @if($searchTerm)
            <div><strong>بحث:</strong> {{ e($searchTerm) }}</div>
        @endif
        <div><strong>تاريخ الإنشاء:</strong> {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الحساب</th>
                <th>كود الحساب</th>
                <th>إجمالي مدين</th>
                <th>إجمالي دائن</th>
                <th>الرصيد النهائي</th>
            </tr>
        </thead>
        <tbody>
            @php $index = 1; @endphp
            @foreach($accounts as $account)
                <tr>
                    <td style="text-align:center;">{{ $index++ }}</td>
                    <td>{{ e($account->name) }}</td>
                    <td>{{ e($account->code) }}</td>
                    <td>{{ number_format($account->total_debit, 2) }}</td>
                    <td>{{ number_format($account->total_credit, 2) }}</td>
                    <td><strong>{{ number_format(abs($account->balance), 2) }} {{ $account->balance_type }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td style="text-align:center;">الإجمالي</td>
                <td></td>
                <td></td>
                <td>{{ number_format($totalDebit, 2) }}</td>
                <td>{{ number_format($totalCredit, 2) }}</td>
                <td><strong>{{ number_format(abs($totalBalance), 2) }} ر.س</strong></td>
            </tr>
        </tfoot>
    </table>
    <div class="footer">تم التصدير بواسطة النظام المحاسبي</div>
</body>
</html>