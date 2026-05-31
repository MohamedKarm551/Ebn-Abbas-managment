{{-- resources/views/journal/index.blade.php --}}
@extends('layouts.app')
@section('title', 'القيود اليدوية')
@section('content')
<style>
    .journal-wrap { direction: rtl; font-family: 'Tajawal','Cairo',sans-serif; }

    /* Toolbar */
    .page-toolbar {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 20px; background: #fff; border-bottom: 1px solid #e5e7eb;
    }
    .page-toolbar h5 { font-size: 15px; font-weight: 700; margin: 0; }
    .btn-add {
        background: #f59e0b; color: #fff; border: none;
        padding: 8px 18px; border-radius: 6px; font-size: 13px;
        font-weight: 600; text-decoration: none;
    }

    /* Search bar - تصميم بسيط */
    .search-section {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 20px;
    }
    .search-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }
    .search-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1;
        min-width: 180px;
    }
    .search-group label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }
    .search-group select,
    .search-group input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        font-family: inherit;
        background: #fff;
    }
    .search-group select:focus,
    .search-group input:focus {
        outline: none;
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px #fef3c733;
    }
    .search-actions {
         display: flex;
    gap: 8px;
    align-items: center;   /* بدل flex-end */
    white-space: nowrap;   /* يمنع النزول لسطر تاني */
    }
    .btn-search {
        background: #111827;
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-reset {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        text-decoration: none;
    }

    /* Table */
    .journal-table { width: 100%; border-collapse: collapse; font-size: 13px; background: #fff; }
    .journal-table th {
        background: #f9fafb; padding: 10px 14px; font-weight: 600;
        color: #374151; border-bottom: 2px solid #e5e7eb; text-align: right;
    }
    .journal-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .journal-table tr:hover td { background: #fafafa; }

    .toggle-icon { font-size: 11px; color: #9ca3af; cursor: pointer; display: inline-block; width: 20px; }
    .toggle-icon.open { transform: rotate(90deg); }

    .lines-detail { display: none; background: #f9fafb; }
    .lines-detail table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .lines-detail th {
        background: #f3f4f6; padding: 7px 14px; font-weight: 600;
        color: #6b7280; text-align: right; border-bottom: 1px solid #e5e7eb;
    }
    .lines-detail td { padding: 7px 14px; border-bottom: 1px solid #e5e7eb; }
    .lines-detail tfoot td { font-weight: 700; background: #f9fafb; padding: 8px 14px; }

    .debit-val  { color: #059669; font-weight: 600; }
    .credit-val { color: #dc2626; font-weight: 600; }

    .badge-posted {
        background: #ecfdf5; color: #065f46;
        padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
        display: inline-block;
    }
    .badge-draft {
        background: #fef3c7; color: #92400e;
        padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
        display: inline-block;
    }
    .ref-badge {
        background: #fef3c7; color: #92400e;
        padding: 2px 10px; border-radius: 5px; font-size: 12px; font-weight: 700;
        display: inline-block;
    }
    .alert-success {
        background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46;
        padding: 10px 16px; border-radius: 6px; margin: 12px 20px; font-size: 13px;
    }
    .pagination-wrap { padding: 12px 20px; }
    .empty-state { text-align: center; color: #9ca3af; padding: 40px; }
    
    .btn-approve {
        background: #f59e0b; color: white; border: none;
        padding: 4px 12px; border-radius: 5px; cursor: pointer;
        font-size: 12px; font-weight: 600;
    }
    .btn-approve:hover { background: #d97706; }

    .active-filter {
        background: #fef3c7;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        display: inline-block;
        margin: 12px 20px 0 20px;
    }

    /* تخصيص pagination مع RTL */
.pagination {
    gap: 6px;
}

.page-link {
    border-radius: 30px !important;
    padding: 8px 15px;
    color: #0d6efd;
    background-color: #fff;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}

.page-link:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
    transform: translateY(-2px);
}

.active > .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
}

.disabled > .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
}
</style>

<div class="journal-wrap">
   <div class="page-toolbar">
    <div style="display: flex; align-items: center; gap: 20px;">
        <h5>📋 القيود المحاسبية</h5>
        <div class="btn-group" role="group">
            <a href="{{ route('journal.index', array_merge(request()->except('page', 'type'), ['type' => 'manual'])) }}" 
               class="btn btn-sm {{ (request('type', 'all') == 'manual') ? 'btn-primary' : 'btn-outline-secondary' }}">
                ✏️ يدوية
            </a>
            <a href="{{ route('journal.index', array_merge(request()->except('page', 'type'), ['type' => 'auto'])) }}" 
               class="btn btn-sm {{ (request('type') == 'auto') ? 'btn-primary' : 'btn-outline-secondary' }}">
                🤖 غير يدوية
            </a>
            <a href="{{ route('journal.index', array_merge(request()->except('page', 'type'), ['type' => 'all'])) }}" 
               class="btn btn-sm {{ (request('type', 'all') == 'all') ? 'btn-primary' : 'btn-outline-secondary' }}">
                📋 الكل
            </a>
        </div>
    </div>
    <a href="{{ route('journal.create') }}" class="btn-add">+ قيد جديد</a>
</div>
<div class="search-section">
    <form method="GET" action="{{ route('journal.index') }}" class="search-form">
        <input type="hidden" name="type" value="{{ request('type', 'manual') }}">
        <div style="display: flex; gap: 12px; width: 100%; flex-wrap: wrap;">

            {{-- بحث بـ --}}
            <div class="search-group" style="flex: 0.8;">
                <label>🔍 بحث بـ</label>
                <select name="search_by" id="searchBy">
                    <option value="">-- اختر معيار البحث --</option>
                    <option value="id" {{ request('search_by') == 'id' ? 'selected' : '' }}>📝 رقم القيد</option>
                    <option value="reference" {{ request('search_by') == 'reference' ? 'selected' : '' }}>🔖 رقم المرجع</option>
                    <option value="status" {{ request('search_by') == 'status' ? 'selected' : '' }}>🏷️ الحالة</option>
                    <option value="created_by" {{ request('search_by') == 'created_by' ? 'selected' : '' }}>👤 بواسطة</option>
                    <option value="created_at" {{ request('search_by') == 'created_at' ? 'selected' : '' }}>📅 تاريخ الإنشاء</option>
                </select>
            </div>

            {{-- القيمة --}}
            <div class="search-group" style="flex: 1.2;">
                <label>✏️ القيمة</label>
                <input type="text" name="search_value" id="searchValue"
                       value="{{ request('search_value') }}"
                       placeholder="أدخل قيمة البحث...">
                <small id="searchHint"></small>
            </div>

            {{-- من تاريخ --}}
            <div class="search-group" style="flex: 1;">
                <label>📅 من تاريخ</label>
                <input type="date" name="date_from"
                       value="{{ request('date_from') }}" onkeydown="return false">
            </div>

            {{-- إلى تاريخ --}}
            <div class="search-group" style="flex: 1;">
                <label>📅 إلى تاريخ</label>
                <input type="date" name="date_to"
                       value="{{ request('date_to') }}" onkeydown="return false">
            </div>

            {{-- الأزرار --}}
            <div class="search-actions ">
                <button type="submit" class="btn-search">🔍بحث</button>
                <a href="{{ route('journal.index', ['type' => request('type', 'manual')]) }}" class="btn-reset">🗑️ مسح الكل</a>
            </div>

        </div>

    </form>




</div>
{{-- Show active filters --}}
@php
    $filters = [];
    if(request('search_by') && request('search_value')) {
        $searchByText = match(request('search_by')) {
            'id' => 'رقم القيد',
            'reference' => 'رقم المرجع',
            'status' => 'الحالة',
            'created_by' => 'بواسطة',
            'created_at' => 'تاريخ الانشاء',
            default => request('search_by')
        };
        $filters[] = "🔍 $searchByText = \"" . request('search_value') . "\"";
    }
    if(request('date_from')) {
        $dateFrom = \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y');
        $filters[] = "📅 من تاريخ: " . $dateFrom;
    }
    if(request('date_to')) {
        $dateTo = \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y');
        $filters[] = "📅 إلى تاريخ: " . $dateTo;
    }
    // ✅ إضافة فلتر نوع القيد فقط إذا كان موجوداً وقيمته ليست 'all'
    if(request()->has('type') && request('type') !== 'all') {
        $typeValue = request('type');
        $typeLabel = $typeValue == 'manual' ? 'يدوية' : 'غير يدوية';
        $filters[] = "🏷️ نوع القيد: $typeLabel";
    }
@endphp
@if(count($filters) > 0)
<div class="active-filter" style="margin: 12px 20px 0 20px;">
    <strong>الفلاتر النشطة:</strong> {{ implode(' | ', $filters) }}
    <a href="{{ route('journal.index') }}" style="margin-right: 10px; color: #dc2626; text-decoration: none;">✖️ إلغاء الكل</a>
</div>
@endif
    <br>
    <br>
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    {{-- Table --}}
    <table class="journal-table">
        <thead>
            <tr>
                <th style="width:40px">رقم القيد</th>
                <th style="width:150px">رقم المرجع</th>
                <th style="width:100px">تاريخ القيد</th>
                <th style="width:130px">تاريخ الإنشاء</th>
                <th style="width:80px">الحالة</th>
                <th style="width:100px">بواسطة</th>
                <th style="width:60px">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                {{-- صف رئيسي --}}
                <tr class="entry-row" onclick="toggleLines({{ $entry->id }})">
                    <td>
                        <span style="margin-right: 8px; color: #6b7280; font-weight: 600;">{{ $entry->id }}</span>
                        <span class="toggle-icon" id="icon-{{ $entry->id }}">▶</span>
                    </td>
                    <td>
                        <span class="ref-badge">{{ $entry->reference }}</span>
                    </td>
                    <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                    <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($entry->status === 'draft')
                            <form action="{{ route('journal.approve', $entry->id) }}" method="POST" style="display:inline">
                                @csrf @method('PATCH')
                                <button class="btn-approve">📝 اعتماد</button>
                            </form>
                        @elseif($entry->status === 'posted')
                            <span class="badge-posted">✅ معتمد</span>
                        @endif
                    </td>
                    <td>{{ $entry->creator->name ?? '-' }}</td>
                    <td style="white-space: nowrap;">
                        @if($entry->status === 'posted' && $entry->reversals_count == 0 && (is_null($entry->source_type) || $entry->source_type === 'manual'))                            <form action="{{ route('journal.reverse', $entry->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-secondary" style="padding: 4px 8px;"
                                        onclick="return confirm('هل أنت متأكد من عكس هذا القيد؟ سيتم إنشاء قيد عكسي وستختفي تأثيراته المالية.')">
                                    🔄 عكس القيد
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('journal.history', $entry->id) . '?' . http_build_query(request()->except('page')) }}" 
                           class="btn btn-sm btn-info" style="padding: 4px 8px;">
                            📜 سجل التعديلات
                        </a>
                        {{-- زر عرض الإيصال لو القيد من ايصال --}}
                        @if(in_array($entry->source_type, ['receipt','payment']))
                            <a href="{{ route('vouchers.show', $entry->id) }}"
                                class="btn btn-sm btn-warning"
                                style="padding: 4px 8px;">
                                🧾 عرض الإيصال
                            </a>
                        @endif
                    </td>
                </tr>
                
                {{-- صف التفاصيل (يحتوي على colspan لتغطية كل الأعمدة) --}}
                <tr id="detail-row-{{ $entry->id }}" style="display: none;">
                    <td colspan="7" style="padding: 0; background: #f9fafb;">
                        <div class="lines-detail" style="display: block;">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width:30%">الحساب</th>
                                        <th style="width:15%">مدين</th>
                                        <th style="width:15%">دائن</th>
                                        <th style="width:40%">البيان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entry->lines as $line)
                                        <tr>
                                            <td>
                                                {{ $line->account->code ?? '-' }} - {{ $line->account->name ?? '-' }}
                                            </td>
                                            <td class="debit-val">
                                                {{ $line->debit > 0 ? number_format($line->debit, 2) . ' ر.س' : '-' }}
                                            </td>
                                            <td class="credit-val">
                                                {{ $line->credit > 0 ? number_format($line->credit, 2) . ' ر.س' : '-' }}
                                            </td>
                                            <td>{{ $line->description ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td><strong>الإجمالي</strong></td>
                                        <td class="debit-val">
                                            <strong>{{ number_format($entry->lines->sum('debit'), 2) }} ر.س</strong>
                                        </td>
                                        <td class="credit-val">
                                            <strong>{{ number_format($entry->lines->sum('credit'), 2) }} ر.س</strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">📭 لا توجد قيود محاسبية مطابقة لمعايير البحث</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
<div class="pagination-wrap" style="padding: 12px 20px;">
    {{ $entries->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
</div>

<script>

function toggleLines(id) {
    const detailRow = document.getElementById('detail-row-' + id);
    const icon = document.getElementById('icon-' + id);
    
    if (detailRow.style.display === 'none' || detailRow.style.display === '') {
        detailRow.style.display = 'table-row';
        icon.textContent = '▼';
        icon.classList.add('open');
    } else {
        detailRow.style.display = 'none';
        icon.textContent = '▶';
        icon.classList.remove('open');
    }
}

document.getElementById('searchBy').addEventListener('change', function() {
    const searchValue = document.getElementById('searchValue');
    const searchHint = document.getElementById('searchHint');
    const selected = this.value;
    
    switch(selected) {
        case 'id':
            searchValue.placeholder = 'مثال: 5';
            searchHint.innerHTML = '🔢 أدخل رقم القيد';
            break;
        case 'reference':
            searchValue.placeholder = 'مثال: MAN-000001';
            searchHint.innerHTML = '🔖 أدخل رقم المرجع كاملاً أو جزء منه';
            break;
        case 'status':
            searchValue.placeholder = 'معتمد  أو  غير معتمد';
            searchHint.innerHTML = '📌 اكتب "معتمد" أو "غير معتمد" بالعربي';
            break;
        case 'created_by':
            searchValue.placeholder = 'مثال: محمد كرم';
            searchHint.innerHTML = '👤 أدخل اسم المستخدم';
            break;
        case 'created_at':  
            searchValue.placeholder = 'مثال: 24/04/2026';
            searchHint.innerHTML = '📅 أدخل تاريخ الإنشاء بالتنسيق يوم/شهر/سنة (DD/MM/YYYY)';
            break;
        default:
            searchValue.placeholder = 'أدخل قيمة البحث...';
            searchHint.innerHTML = '';
    }
});


function formatDate(input) {
    if (!input.value) return;
    
    const parts = input.value.split('-');
    if (parts.length === 3) {
        const year = parts[0];
        const month = parts[1];
        const day = parts[2];
    }
}

</script>
@endsection