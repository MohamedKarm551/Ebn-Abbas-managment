<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"><title>شجرة الحسابات</title>
<style>
    body { font-family: 'Cairo', 'DejaVu Sans', sans-serif; padding: 20px; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #aaa; padding: 8px; text-align: right; vertical-align: top; }
    th { background: #f0f0f0; }
    @media print { body { padding: 0; } }
</style>
</head>
<body>
<div class="header"><h1>شجرة الحسابات</h1><div>تاريخ الطباعة: {{ now()->format('d/m/Y H:i') }}</div></div>
<table><thead><tr><th>الكود</th><th>اسم الحساب</th><th>النوع</th><th>الفئة</th><th>الرصيد</th></tr></thead>
<tbody>@include('accounts._print_tree_recursive', ['accounts' => $accounts, 'level' => 0])</tbody>
</table>
</body>
</html>