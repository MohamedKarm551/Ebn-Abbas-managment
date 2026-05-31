{{-- resources/views/accounts/list.blade.php --}}
@extends('layouts.app')

@section('title', 'قائمة الحسابات')

@section('content')
<style>
    .accounts-list-wrap { direction: rtl; font-family: 'Tajawal', 'Cairo', sans-serif; }

    .page-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }
    .page-header-bar .breadcrumb-area { font-size: 13px; color: #6b7280; }
    .page-header-bar .breadcrumb-area span { color: #111827; font-weight: 600; }

    .tree-nav-tabs {
        display: flex;
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
    }
    .tree-nav-tabs a.active {
        color: #111827;
        border-bottom-color: #f59e0b;
    }

    .list-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 12px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        gap: 10px;
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

    /* Table */
    .accounts-flat-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
        background: #fff;
    }
    .accounts-flat-table th {
        background: #fff;
        padding: 10px 16px;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
        text-align: right;
        font-size: 13px;
    }
    .accounts-flat-table td {
        padding: 9px 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #111827;
        vertical-align: middle;
    }
    .accounts-flat-table tr:hover td { background: #fafafa; }

    .code-cell { color: #6b7280; font-size: 12px; text-align: center; }

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

    .currency-label { font-size: 11px; color: #9ca3af; margin-right: 3px; }

    .btn-edit {
        background: #fff;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 4px 14px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
    }
    .btn-edit:hover { background: #f3f4f6; border-color: #9ca3af; }

    .alert-success {
        background: #ecfdf5; border: 1px solid #6ee7b7;
        color: #065f46; padding: 10px 16px;
        border-radius: 6px; margin: 12px 20px; font-size: 13px;
    }
    .alert-danger {
        background: #fef2f2; border: 1px solid #fca5a5;
        color: #991b1b; padding: 10px 16px;
        border-radius: 6px; margin: 12px 20px; font-size: 13px;
    }
</style>

<div class="accounts-list-wrap">

    {{-- Nav Tabs --}}
    <div class="tree-nav-tabs">
        <a href="{{ route('accounts.index') }}">شجرة الحسابات</a>
        <a href="{{ route('accounts.list') }}" class="active">قائمة العمليات</a>
    </div>

    {{-- Toolbar --}}
    <div class="list-toolbar">
        <a href="{{ route('accounts.create') }}" class="btn-add-account">
            + إضافة حساب
        </a>
          <a href="{{ route('journal.create') }}" class="btn-add-account">
            <i class="fas fa-pen-nib me-1"></i> القيود اليدوية
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Table --}}
    <table class="accounts-flat-table">
        <thead>
            <tr>
                <th style="width:60px">الكود</th>
                <th>اسم الحساب</th>
                <th style="width:120px">النوع</th>
                <th style="width:150px">الفئة</th>
                <th style="width:160px; text-align:left">الرصيد</th>
                <th style="width:100px; text-align:center">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
                @php
                    $balance = $account->balance;

                    $typeNames = [
                        'asset'     => 'أصول',
                        'liability' => 'خصوم',
                        'equity'    => 'حقوق ملكية',
                        'revenue'   => 'إيرادات',
                        'expense'   => 'مصروفات',
                    ];

                    // الفئة = الحساب الأب
                    $categoryText = $account->parent
                        ? $account->parent->code . ' - ' . $account->parent->name
                        : '—';
                @endphp
                <tr>
                    <td class="code-cell">{{ $account->code }}</td>

                    <td style="font-weight: {{ !$account->parent_id ? '700' : ($account->level === 2 ? '600' : '400') }}">
                        {{ $account->name }}
                    </td>

                    <td style="color:#374151">
                        {{ $typeNames[$account->type] ?? $account->type }}
                    </td>

                    <td style="color:#6b7280; font-size:12px;">
                        {{ $categoryText }}
                    </td>

                    <td class="balance-cell">
                        @if($balance > 0)
                            <span class="balance-positive">
                                {{ number_format($balance, 0) }}
                                <span class="currency-label">ر.س</span>
                            </span>
                        @elseif($balance < 0)
                            <span class="balance-negative">
                                {{ number_format(abs($balance), 0) }}
                                <span class="currency-label">ر.س</span>
                            </span>
                        @else
                            <span class="balance-zero">* ر.س</span>
                        @endif
                    </td>

                    <td style="text-align:center">
                        {{-- تعديل --}}
                        <a href="{{ route('accounts.edit', $account) }}"
                           style="background:#f3f4f6; padding:3px 9px; border-radius:5px; font-size:11px;
                                  text-decoration:none; color:#374151; display:inline-flex; align-items:center; gap:3px;">
                            ✏️ تعديل
                        </a>
                    </td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
