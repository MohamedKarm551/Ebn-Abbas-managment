{{-- resources/views/accounts/index.blade.php --}}
@extends('layouts.app')

@section('title', 'شجرة الحسابات')

@section('content')
<style>
    .accounts-tree-wrap { direction: rtl; font-family: 'Tajawal', 'Cairo', sans-serif; }

    /* ===== Header ===== */
    .page-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0;
    }
    .page-header-bar .breadcrumb-area {
        font-size: 13px;
        color: #6b7280;
    }
    .page-header-bar .breadcrumb-area a {
        color: #6b7280;
        text-decoration: none;
    }
    .page-header-bar .breadcrumb-area span {
        color: #111827;
        font-weight: 600;
    }
    .page-header-bar .hotel-name {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }

    /* ===== Toolbar ===== */
    .tree-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }
    .tree-toolbar .toolbar-title {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }
    .btn-add-account {
        background: #f59e0b;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .2s;
    }
    .btn-add-account:hover { background: #d97706; color: #fff; }

    /* ===== Nav Tabs ===== */
    .tree-nav-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #e5e7eb;
        background: #fff;
        padding: 0 20px;
    }
    .tree-nav-tabs a {
        padding: 10px 22px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-decoration: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all .2s;
    }
    .tree-nav-tabs a.active {
        color: #111827;
        border-bottom-color: #f59e0b;
    }

    /* ===== Expand/Collapse buttons ===== */
    .tree-actions {
        padding: 10px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        gap: 8px;
    }
    .btn-tree-action {
        background: #fff;
        border: 1px solid #d1d5db;
        padding: 5px 14px;
        border-radius: 5px;
        font-size: 12px;
        color: #374151;
        cursor: pointer;
        transition: all .15s;
    }
    .btn-tree-action:hover { background: #f3f4f6; border-color: #9ca3af; }

    /* ===== Tree Table ===== */
    .tree-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 13.5px;
    }
    .tree-table th {
        background: #f9fafb;
        padding: 10px 16px;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
        text-align: right;
        font-size: 13px;
        white-space: nowrap;
    }
    .tree-table td {
        padding: 9px 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #111827;
        vertical-align: middle;
    }

    /* صفوف المستويات */
    .row-level-0 td { background: #fafafa; font-weight: 700; font-size: 14px; }
    .row-level-1 td { background: #fff; font-weight: 600; }
    .row-level-2 td { background: #fff; }
    .row-level-3 td { background: #fff; }

    /* علامة expand/collapse */
    .toggle-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 2px 6px;
        color: #6b7280;
        font-size: 11px;
        border-radius: 3px;
        transition: all .15s;
        margin-left: 4px;
    }
    .toggle-btn:hover { background: #f3f4f6; color: #111827; }

    .account-code {
        color: #6b7280;
        font-size: 12px;
        margin-left: 8px;
        font-variant-numeric: tabular-nums;
    }

    .account-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-left: 8px;
        vertical-align: middle;
        flex-shrink: 0;
    }

    /* الرصيد */
    .balance-cell {
        text-align: left;
        direction: ltr;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        white-space: nowrap;
    }
    .balance-positive { color: #059669; }
    .balance-negative { color: #dc2626; }
    .balance-zero     { color: #9ca3af; }

    .currency-label {
        font-size: 11px;
        color: #9ca3af;
        margin-right: 3px;
    }

    /* type badge */
    .type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .children-rows { }
    .children-rows.collapsed { display: none; }

    /* Alerts */
    .alert-success {
        background: #ecfdf5;
        border: 1px solid #6ee7b7;
        color: #065f46;
        padding: 10px 16px;
        border-radius: 6px;
        margin: 12px 20px;
        font-size: 13px;
    }
    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 10px 16px;
        border-radius: 6px;
        margin: 12px 20px;
        font-size: 13px;
    }


    .row-frozen td { background: #fef2f2 !important; }
    .row-frozen:hover td { background: #fee2e2 !important; }
    
    .tree-table th:last-child,
    .tree-table td:last-child {
        text-align: center;
        white-space: nowrap;
    }


    /* ===== تنسيق الطباعة ===== */
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

<div class="accounts-tree-wrap no-print">

    {{-- Nav Tabs --}}
    <div class="tree-nav-tabs">
        <a href="{{ route('accounts.index') }}" class="active">شجرة الحسابات</a>
        <a href="{{ route('accounts.list') }}">قائمة العمليات</a>

        <div class="tree-toolbar">
   
</div>
    </div>

    {{-- Toolbar --}}
    <div class="tree-toolbar no-print">
        <div class="toolbar-title">الشجرة المحاسبية</div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-secondary btn-sm" ><i class="fas fa-print me-1"></i> طباعة</button>
            <a href="{{ route('accounts.tree.excel') }}" class="btn btn-success btn-sm" ><i class="fas fa-file-excel me-1"></i> Excel</a>
            <a href="{{ route('accounts.tree.pdf') }}" class="btn btn-danger btn-sm" ><i class="fas fa-file-pdf me-1"></i> PDF</a>
            <a href="{{ route('accounts.create') }}" class="btn-add-account">+ إضافة حساب</a>
            <a href="{{ route('journal.create') }}" class="btn-add-account"><i class="fas fa-pen-nib"></i> القيود اليومية</a>
        </div>
    </div>



    </div>

    {{-- Expand/Collapse --}}
    <div class="tree-actions no-print">
        <button class="btn-tree-action" onclick="expandAll()">توسيع الكل</button>
        <button class="btn-tree-action" onclick="collapseAll()">طي الكل</button>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Tree Table --}}
    <table class="tree-table">
        <thead>
            <tr>
                <th style="width:60px">#</th>
                <th>اسم الحساب</th>
                <th style="width:120px">النوع</th>
                <th style="width:100px">الفئة</th>
                <th style="width:150px; text-align:left">الرصيد</th>
                <th class="no-print" style="width:100px; text-align:center">الإجراءات</th> 
            </tr>
        </thead>
        <tbody id="accountsTree">
            @foreach($accounts as $account)
                @include('accounts._tree_node', [
                    'account' => $account,
                    'level'   => 0,
                ])
            @endforeach
        </tbody>
    </table>

</div>

<script>
function toggleChildren(code) {
    const btn = document.getElementById('toggle-' + code);
    const isCurrentlyOpen = btn.textContent.trim() === '▼';

    if (isCurrentlyOpen) {
        // ── إغلاق: أخفِ كل الأحفاد (مباشرين وغير مباشرين)
        hideAllDescendants(code);
        btn.textContent = '▶';
    } else {
        // ── فتح: أظهر الأبناء المباشرين فقط
        const allRows = document.querySelectorAll('#accountsTree tr');
        allRows.forEach(row => {
            if (row.classList.contains('children-of-' + code)) {
                row.style.display = '';
                // لا تفتح أبناءه — ابقِ زرّه على ▶ إن كان مقفولاً
            }
        });
        btn.textContent = '▼';
    }
}

// دالة مساعدة: تخفي كل الأحفاد بشكل متكرر
function hideAllDescendants(code) {
    const allRows = document.querySelectorAll('#accountsTree tr');
    allRows.forEach(row => {
        if (row.classList.contains('children-of-' + code)) {
            row.style.display = 'none';

            // أخفِ أبناءه هو كمان (لو عنده أبناء)
            const childCode = row.getAttribute('data-code');
            if (childCode) {
                hideAllDescendants(childCode);

                // رجّع زر الابن لـ ▶ عشان لما يتفتح تاني يبدأ مقفول
                const childBtn = document.getElementById('toggle-' + childCode);
                if (childBtn) childBtn.textContent = '▶';
            }
        }
    });
}

function expandAll() {
    document.querySelectorAll('#accountsTree tr').forEach(r => {
        r.style.display = '';
    });
    document.querySelectorAll('.toggle-btn').forEach(b => b.textContent = '▼');
}

function collapseAll() {
    document.querySelectorAll('#accountsTree tr[class*="children-of-"]').forEach(r => {
        r.style.display = 'none';
    });
    document.querySelectorAll('.toggle-btn').forEach(b => b.textContent = '▶');
}
</script>
@endsection
