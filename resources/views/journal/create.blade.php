{{-- resources/views/journal/create.blade.php --}}
@extends('layouts.app')
@section('title', 'قيد محاسبي يدوي')
@section('content')
<style>
    .journal-wrap { direction: rtl; font-family: 'Tajawal','Cairo',sans-serif; max-width: 950px; margin: 30px auto; padding: 0 16px; }
    .journal-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 28px; }
    .journal-card h5 { font-size: 16px; font-weight: 700; margin-bottom: 22px; color: #111827; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .form-control { width: 100%; padding: 8px 11px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; font-family: inherit; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #f59e0b; box-shadow: 0 0 0 3px #fef3c733; }
    .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .lines-table th { background: #f9fafb; padding: 8px 10px; font-size: 12px; font-weight: 600; color: #374151; border: 1px solid #e5e7eb; text-align: right; }
    .lines-table td { padding: 6px 8px; border: 1px solid #e5e7eb; vertical-align: middle; }
    .lines-table input[type="number"],
    .lines-table input[type="text"] { width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 5px; font-size: 12.5px; font-family: inherit; box-sizing: border-box; }
    .btn-add-line { background: #f0fdf4; border: 1px dashed #86efac; color: #166534; padding: 7px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; margin-bottom: 16px; }
    .btn-remove { background: #fee2e2; border: none; color: #dc2626; padding: 4px 10px; border-radius: 5px; cursor: pointer; font-size: 12px; }
    .btn-primary { background: #f59e0b; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; font-size: 13.5px; font-weight: 600; cursor: pointer; }
    .btn-secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; padding: 10px 24px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; }
    .totals-bar { display: flex; gap: 20px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; }
    .totals-bar span { font-weight: 600; }
    .balance-ok  { color: #059669; }
    .balance-err { color: #dc2626; }
    .alert-danger { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; }
    .serial-hint { font-size: 11px; color: #6b7280; margin-top: 4px; }
    .required-star { color: red; }

    /* ══════════════════════════════════════
       Account Searchable Dropdown
    ══════════════════════════════════════ */
    .acc-wrap {
        position: relative;
        width: 100%;
        font-family: 'Tajawal','Cairo',sans-serif;
    }

    /* الزر الذي يظهر الاختيار الحالي */
    .acc-trigger {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 12.5px;
        text-align: right;
        background: #fff;
        cursor: pointer;
        color: #374151;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-sizing: border-box;
        user-select: none;
        white-space: nowrap;
        overflow: hidden;
    }
    .acc-trigger.has-value { color: #111827; font-weight: 600; }
    .acc-trigger:hover  { border-color: #f59e0b; }
    .acc-trigger .arr   { font-size: 10px; color: #9ca3af; flex-shrink: 0; margin-right: 4px; /* لأن rtl */ }

    /* الـ popup الذي يظهر عند الضغط */
    .acc-popup {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        left: 0;
        z-index: 9999;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
        min-width: 280px;
        max-width: 400px;
        overflow: hidden;
    }
    .acc-popup.open { display: block; }

    /* حقل البحث */
    .acc-search-box {
        padding: 8px;
        border-bottom: 1px solid #f3f4f6;
    }
    .acc-search-box input {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 12.5px;
        font-family: inherit;
        direction: rtl;
        box-sizing: border-box;
    }
    .acc-search-box input:focus {
        outline: none;
        border-color: #f59e0b;
        box-shadow: 0 0 0 2px #fef3c755;
    }

    /* قائمة النتائج */
    .acc-results {
        max-height: 220px;
        overflow-y: auto;
        padding: 4px 0;
    }
    .acc-item {
        padding: 7px 12px;
        font-size: 12.5px;
        cursor: pointer;
        display: flex;
        gap: 8px;
        align-items: baseline;
        direction: rtl;
        transition: background .12s;
    }
    .acc-item:hover, .acc-item.focused { background: #fef9ec; }
    .acc-item .acc-code {
        font-family: monospace;
        font-size: 11px;
        color: #6b7280;
        background: #f3f4f6;
        padding: 1px 5px;
        border-radius: 3px;
        flex-shrink: 0;
    }
    .acc-item .acc-name { color: #111827; }
    .acc-item .acc-name mark {
        background: #fef3c7;
        color: #92400e;
        padding: 0;
        font-style: normal;
    }

    /* رسائل حالة */
    .acc-msg {
        padding: 12px;
        font-size: 12px;
        color: #6b7280;
        text-align: center;
    }
    .acc-msg.loading::before { content: '⏳ '; }
    .acc-msg.empty::before   { content: '🔍 '; }
    .acc-msg.error::before   { content: '⚠️ '; }
</style>

<div class="journal-wrap">
    <div class="journal-card">
        <h5>📝 إنشاء قيد محاسبي يدوي</h5>

        @if($errors->any())
            <div class="alert-danger">
                @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
            </div>
        @endif

        <form action="{{ route('journal.store') }}" method="POST" id="journalForm">
            @csrf

            <div class="grid-3">
                <div class="form-group">
                    <label>رقم المرجع <span class="required-star">*</span></label>
                    <input type="text" name="reference" class="form-control"
                           value="{{ old('reference') }}"
                           placeholder="مثال: JE-2026-001" required>
                    <div class="serial-hint">رقم مرجعى للقيد المحاسبي</div>
                </div>

                <div class="form-group">
                    <label>تاريخ القيد <span class="required-star">*</span></label>
                    <input type="date" name="entry_date" class="form-control"
                           value="{{ old('entry_date', date('Y-m-d')) }}" required>
                    <div class="serial-hint">تاريخ العملية المالية</div>
                </div>

                <div class="form-group">
                    <label>حالة القيد</label>
                    <select name="status" class="form-control" id="statusSelect">
                        <option value="draft"  {{ old('status','draft') == 'draft'  ? 'selected' : '' }}>📝 مسودة (غير معتمد)</option>
                        <option value="posted" {{ old('status') == 'posted' ? 'selected' : '' }}>✅ معتمد (مرحّل)</option>
                    </select>
                    <div class="serial-hint" id="statusHint" style="color:#92400e;">
                        ⚠️ القيود غير المعتمدة لا تؤثر على الأرصدة
                    </div>
                </div>
            </div>

            <label style="font-size:13px; font-weight:600; color:#374151; margin-bottom:8px; display:block;">
                أسطر القيد <span class="required-star">*</span>
            </label>

            <table class="lines-table" id="linesTable">
                <thead>
                    <tr>
                        <th style="width:32%">الحساب <span class="required-star">*</span></th>
                        <th style="width:14%">مدين</th>
                        <th style="width:14%">دائن</th>
                        <th style="width:34%">بيان السطر <span class="required-star">*</span></th>
                        <th style="width:6%"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">

                    @if(old('lines'))
                        @foreach(old('lines') as $index => $line)
                        <tr class="line-row">
                            <td>
                                {{-- Searchable Account Dropdown --}}
                                <div class="acc-wrap">
                                    <div class="acc-trigger {{ old("lines.{$index}.account_id") ? 'has-value' : '' }}"
                                         onclick="toggleAccDropdown(this)">
                                        <span class="acc-label">
                                            @php
                                                $selAcc = old("lines.{$index}.account_id")
                                                    ? $accounts->firstWhere('id', old("lines.{$index}.account_id"))
                                                    : null;
                                            @endphp
                                            {{ $selAcc ? $selAcc->code . ' - ' . $selAcc->name : '-- اختر الحساب --' }}
                                        </span>
                                        <span class="arr">▼</span>
                                    </div>
                                    <div class="acc-popup">
                                        <div class="acc-search-box">
                                            <input type="text"
                                                   placeholder="ابحث باسم الحساب أو الكود..."
                                                   oninput="handleAccSearch(this)"
                                                   onkeydown="handleAccKey(event, this)">
                                        </div>
                                        <div class="acc-results">
                                            <div class="acc-msg">اكتب للبحث...</div>
                                        </div>
                                    </div>
                                    <input type="hidden"
                                           name="lines[{{ $index }}][account_id]"
                                           value="{{ old("lines.{$index}.account_id") }}"
                                           class="acc-hidden"
                                           required>
                                </div>
                            </td>
                            <td><input type="number" step="0.01" min="0" name="lines[{{ $index }}][debit]"  value="{{ old("lines.{$index}.debit")  }}" placeholder="0.00" oninput="updateTotals()"></td>
                            <td><input type="number" step="0.01" min="0" name="lines[{{ $index }}][credit]" value="{{ old("lines.{$index}.credit") }}" placeholder="0.00" oninput="updateTotals()"></td>
                            <td><input type="text" name="lines[{{ $index }}][description]" value="{{ old("lines.{$index}.description") }}" placeholder="بيان السطر" required></td>
                            <td>@if($index >= 2)<button type="button" class="btn-remove" onclick="removeLine(this)">✕</button>@endif</td>
                        </tr>
                        @endforeach
                    @else
                        {{-- صفان افتراضيان --}}
                        @foreach([0,1] as $idx)
                        <tr class="line-row">
                            <td>
                                <div class="acc-wrap">
                                    <div class="acc-trigger" onclick="toggleAccDropdown(this)">
                                        <span class="acc-label">-- اختر الحساب --</span>
                                        <span class="arr">▼</span>
                                    </div>
                                    <div class="acc-popup">
                                        <div class="acc-search-box">
                                            <input type="text"
                                                   placeholder="ابحث باسم الحساب أو الكود..."
                                                   oninput="handleAccSearch(this)"
                                                   onkeydown="handleAccKey(event, this)">
                                        </div>
                                        <div class="acc-results">
                                            <div class="acc-msg">اكتب للبحث...</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lines[{{ $idx }}][account_id]" class="acc-hidden" required>
                                </div>
                            </td>
                            <td><input type="number" step="0.01" min="0" name="lines[{{ $idx }}][debit]"  placeholder="0.00" oninput="updateTotals()"></td>
                            <td><input type="number" step="0.01" min="0" name="lines[{{ $idx }}][credit]" placeholder="0.00" oninput="updateTotals()"></td>
                            <td><input type="text" name="lines[{{ $idx }}][description]" placeholder="بيان السطر" required></td>
                            <td><button type="button" class="btn-remove" onclick="removeLine(this)">✕</button></td>
                        </tr>
                        @endforeach
                    @endif

                </tbody>
            </table>

            <button type="button" class="btn-add-line" onclick="addLine()">+ إضافة سطر</button>

            <div class="totals-bar">
                <div>إجمالي المدين:  <span id="totalDebit"  class="balance-ok">0.00</span> ر.س</div>
                <div>إجمالي الدائن: <span id="totalCredit" class="balance-ok">0.00</span> ر.س</div>
                <div id="balanceStatus" style="font-weight:600"></div>
            </div>

            <div style="display:flex; gap:10px; margin-top:8px;">
                <button type="submit" class="btn-primary">حفظ القيد</button>
                <a href="{{ route('journal.index') }}" class="btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<script>
// ══════════════════════════════════════════════════════════════════
// إعدادات
// ══════════════════════════════════════════════════════════════════
const SEARCH_URL  = '{{ route("accounts.search") }}';
const DEBOUNCE_MS = 280;   // تأخير البحث عند الكتابة

let lineIndex = {{ old('lines') ? count(old('lines')) : 2 }};
let debounceTimer = null;

// ══════════════════════════════════════════════════════════════════
// فتح / إغلاق الـ Dropdown
// ══════════════════════════════════════════════════════════════════
function toggleAccDropdown(trigger) {
    const wrap   = trigger.closest('.acc-wrap');
    const popup  = wrap.querySelector('.acc-popup');
    const isOpen = popup.classList.contains('open');

    // أغلق كل الـ dropdowns المفتوحة
    closeAllDropdowns();

    if (!isOpen) {
        popup.classList.add('open');
        trigger.querySelector('.arr').textContent = '▲';
        // ركّز على حقل البحث
        const searchInput = popup.querySelector('input[type="text"]');
        searchInput.focus();
        // اعرض أول 20 حساب عند الفتح
        fetchAccounts('', popup);
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('.acc-popup.open').forEach(p => {
        p.classList.remove('open');
        const trigger = p.closest('.acc-wrap').querySelector('.acc-trigger');
        if (trigger) trigger.querySelector('.arr').textContent = '▼';
    });
}

// إغلاق عند الضغط خارج الـ dropdown
document.addEventListener('click', function(e) {
    if (!e.target.closest('.acc-wrap')) closeAllDropdowns();
});

// ══════════════════════════════════════════════════════════════════
// البحث بـ AJAX مع Debounce
// ══════════════════════════════════════════════════════════════════
function handleAccSearch(input) {
    clearTimeout(debounceTimer);
    const popup = input.closest('.acc-popup');
    showMsg(popup, 'loading', 'جاري البحث...');
    debounceTimer = setTimeout(() => {
        fetchAccounts(input.value.trim(), popup);
    }, DEBOUNCE_MS);
}

function fetchAccounts(q, popup) {
    const resultsBox = popup.querySelector('.acc-results');

    fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`)
        .then(r => {
            if (!r.ok) throw new Error('network error');
            return r.json();
        })
        .then(data => {
            renderResults(data, q, resultsBox);
        })
        .catch(() => {
            showMsg(popup, 'error', 'حدث خطأ في البحث، حاول مرة أخرى');
        });
}

// ══════════════════════════════════════════════════════════════════
// عرض النتائج
// ══════════════════════════════════════════════════════════════════
function renderResults(accounts, q, resultsBox) {
    resultsBox.innerHTML = '';

    if (!accounts.length) {
        resultsBox.innerHTML = '<div class="acc-msg empty">لا توجد نتائج</div>';
        return;
    }

    accounts.forEach((acc, i) => {
        const item = document.createElement('div');
        item.className = 'acc-item' + (i === 0 ? ' focused' : '');
        item.dataset.id   = acc.id;
        item.dataset.code = acc.code;
        item.dataset.name = acc.name;
        item.innerHTML = `
            <span class="acc-code">${acc.code}</span>
            <span class="acc-name">${highlight(acc.name, q)}</span>
        `;
        item.addEventListener('mousedown', function(e) {
            // منع إغلاق الـ popup قبل الاختيار
            e.preventDefault();
        });
        item.addEventListener('click', function() {
            selectAccount(this);
        });
        resultsBox.appendChild(item);
    });
}

function highlight(text, q) {
    if (!q) return text;
    const regex = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

// ══════════════════════════════════════════════════════════════════
// اختيار الحساب
// ══════════════════════════════════════════════════════════════════
function selectAccount(item) {
    const wrap    = item.closest('.acc-wrap');
    const trigger = wrap.querySelector('.acc-trigger');
    const hidden  = wrap.querySelector('.acc-hidden');
    const label   = trigger.querySelector('.acc-label');

    label.textContent  = `${item.dataset.code} - ${item.dataset.name}`;
    hidden.value       = item.dataset.id;
    trigger.classList.add('has-value');

    closeAllDropdowns();
}

// ══════════════════════════════════════════════════════════════════
// التنقل بلوحة المفاتيح (↑ ↓ Enter Escape)
// ══════════════════════════════════════════════════════════════════
function handleAccKey(e, input) {
    const popup      = input.closest('.acc-popup');
    const items      = [...popup.querySelectorAll('.acc-item')];
    const focused    = popup.querySelector('.acc-item.focused');
    let   focusedIdx = items.indexOf(focused);

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        const next = items[Math.min(focusedIdx + 1, items.length - 1)];
        if (next) { focused?.classList.remove('focused'); next.classList.add('focused'); next.scrollIntoView({ block: 'nearest' }); }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const prev = items[Math.max(focusedIdx - 1, 0)];
        if (prev) { focused?.classList.remove('focused'); prev.classList.add('focused'); prev.scrollIntoView({ block: 'nearest' }); }
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (focused) selectAccount(focused);
    } else if (e.key === 'Escape') {
        closeAllDropdowns();
    }
}

// ══════════════════════════════════════════════════════════════════
// دوال مساعدة
// ══════════════════════════════════════════════════════════════════
function showMsg(popup, type, text) {
    popup.querySelector('.acc-results').innerHTML =
        `<div class="acc-msg ${type}">${text}</div>`;
}

// ══════════════════════════════════════════════════════════════════
// إضافة سطر جديد
// ══════════════════════════════════════════════════════════════════
function addLine() {
    const tbody = document.getElementById('linesBody');
    const tr    = document.createElement('tr');
    tr.className = 'line-row';
    tr.innerHTML = `
        <td>
            <div class="acc-wrap">
                <div class="acc-trigger" onclick="toggleAccDropdown(this)">
                    <span class="acc-label">-- اختر الحساب --</span>
                    <span class="arr">▼</span>
                </div>
                <div class="acc-popup">
                    <div class="acc-search-box">
                        <input type="text"
                               placeholder="ابحث باسم الحساب أو الكود..."
                               oninput="handleAccSearch(this)"
                               onkeydown="handleAccKey(event, this)">
                    </div>
                    <div class="acc-results">
                        <div class="acc-msg">اكتب للبحث...</div>
                    </div>
                </div>
                <input type="hidden" name="lines[${lineIndex}][account_id]" class="acc-hidden" required>
            </div>
        </td>
        <td><input type="number" step="0.01" min="0" name="lines[${lineIndex}][debit]"  placeholder="0.00" oninput="updateTotals()"></td>
        <td><input type="number" step="0.01" min="0" name="lines[${lineIndex}][credit]" placeholder="0.00" oninput="updateTotals()"></td>
        <td><input type="text" name="lines[${lineIndex}][description]" placeholder="بيان السطر" required></td>
        <td><button type="button" class="btn-remove" onclick="removeLine(this)">✕</button></td>
    `;
    tbody.appendChild(tr);
    lineIndex++;
}

// ══════════════════════════════════════════════════════════════════
// حذف سطر
// ══════════════════════════════════════════════════════════════════
function removeLine(btn) {
    const rows = document.querySelectorAll('.line-row');
    if (rows.length <= 2) { alert('القيد يحتاج سطرين على الأقل'); return; }
    btn.closest('tr').remove();
    updateTotals();
}

// ══════════════════════════════════════════════════════════════════
// حساب الإجماليات
// ══════════════════════════════════════════════════════════════════
function updateTotals() {
    let debit = 0, credit = 0;
    document.querySelectorAll('.line-row').forEach(row => {
        debit  += parseFloat(row.querySelector('input[name*="[debit]"]').value)  || 0;
        credit += parseFloat(row.querySelector('input[name*="[credit]"]').value) || 0;
    });
    document.getElementById('totalDebit').textContent  = debit.toFixed(2);
    document.getElementById('totalCredit').textContent = credit.toFixed(2);

    const status = document.getElementById('balanceStatus');
    if (Math.abs(debit - credit) < 0.01) {
        status.innerHTML  = '✅ القيد متوازن';
        status.className  = 'balance-ok';
    } else {
        status.innerHTML  = `❌ فرق: ${Math.abs(debit - credit).toFixed(2)}`;
        status.className  = 'balance-err';
    }
}

// ══════════════════════════════════════════════════════════════════
// حالة القيد
// ══════════════════════════════════════════════════════════════════
document.getElementById('statusSelect').addEventListener('change', function() {
    const hint = document.getElementById('statusHint');
    if (this.value === 'draft') {
        hint.innerHTML   = '⚠️ القيود غير المعتمدة لا تؤثر على الأرصدة ولا تظهر في التقارير النهائية';
        hint.style.color = '#92400e';
    } else {
        hint.innerHTML   = '✅ القيد المعتمد سيتم ترحيله فوراً ويؤثر على الأرصدة';
        hint.style.color = '#065f46';
    }
});
</script>
@endsection