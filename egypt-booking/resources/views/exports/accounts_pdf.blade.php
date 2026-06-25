<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'cairo', Arial; font-size: 11px; direction: rtl; }
    h2   { text-align: center; color: #1d4ed8; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th   { background: #1d4ed8; color: white; padding: 7px; text-align: center; }
    td   { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
    .level-0 { background: #dbeafe; font-weight: bold; }
    .level-1 { background: #eff6ff; }
    .level-2 { background: #f9fafb; }
    .balance-positive { color: #059669; }
    .balance-negative { color: #dc2626; }
</style>
</head>
<body>
<h2> شجرة الحسابات</h2>
<table>
    <thead>
        <tr>
            <th>الكود</th>
            <th>اسم الحساب</th>
            <th>النوع</th>
            <th>الرصيد</th>
        </tr>
    </thead>
    <tbody>
        @foreach($accounts as $account)
            @include('exports._accounts_pdf_node', ['account' => $account, 'level' => 0])
        @endforeach
    </tbody>
</table>
</body>
</html>