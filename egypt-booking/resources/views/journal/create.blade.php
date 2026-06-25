<x-app-layout>
<div style="max-width:900px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);
            font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;
                align-items:center;margin-bottom:24px;">
        <h2 style="margin:0;">✏️ قيد محاسبي يدوي</h2>
        <a href="{{ route('journal.pending') }}"
           style="color:#6b7280;text-decoration:none;">← لوحة المحاسب</a>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('journal.store') }}" id="journalForm">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;
                    gap:16px;margin-bottom:20px;">
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:4px;">
                    رقم المرجع *
                </label>
                <input type="text" name="reference"
                       value="{{ old('reference') }}"
                       placeholder="مثال: MAN-2026-001" required
                       style="width:100%;padding:10px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:4px;">
                    تاريخ القيد *
                </label>
                <input type="date" name="entry_date"
                       value="{{ old('entry_date', date('Y-m-d')) }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:4px;">
                    الحالة
                </label>
                <select name="status" id="statusSel"
                        style="width:100%;padding:10px;border:1px solid #ddd;
                               border-radius:6px;box-sizing:border-box;"
                        onchange="updateStatusHint()">
                    <option value="draft"  {{ old('status','draft')=='draft'  ?'selected':'' }}>
                        📝 مسودة — في انتظار الاعتماد
                    </option>
                    <option value="posted" {{ old('status')=='posted' ?'selected':'' }}>
                        ✅ معتمد مباشرة
                    </option>
                </select>
                <small id="statusHint" style="color:#92400e;font-size:11px;">
                    ⚠️ لن يؤثر على الأرصدة حتى يُعتمد
                </small>
            </div>
        </div>

        {{-- أسطر القيد --}}
        <label style="display:block;font-weight:bold;margin-bottom:8px;">
            أسطر القيد *
        </label>

        <table style="width:100%;border-collapse:collapse;margin-bottom:12px;"
               id="linesTable">
            <thead style="background:#f9fafb;">
                <tr>
                    <th style="padding:10px;text-align:right;border:1px solid #e5e7eb;
                               width:35%;">الحساب *</th>
                    <th style="padding:10px;text-align:center;border:1px solid #e5e7eb;
                               width:15%;">مدين</th>
                    <th style="padding:10px;text-align:center;border:1px solid #e5e7eb;
                               width:15%;">دائن</th>
                    <th style="padding:10px;text-align:right;border:1px solid #e5e7eb;
                               width:30%;">البيان *</th>
                    <th style="padding:10px;border:1px solid #e5e7eb;width:5%;"></th>
                </tr>
            </thead>
            <tbody id="linesBody">
                @for($i = 0; $i < 2; $i++)
                <tr class="line-row">
                    <td style="padding:6px;border:1px solid #e5e7eb;">
                        {{-- حقل البحث الديناميكي (AJAX) بدلاً من select --}}
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
                            <input type="hidden"
                                   name="lines[{{ $i }}][account_id]"
                                   class="acc-hidden"
                                   value="{{ old("lines.$i.account_id") }}"
                                   required>
                        </div>
                     </td>
                    <td style="padding:6px;border:1px solid #e5e7eb;">
                        <input type="number" step="0.01" min="0"
                               name="lines[{{ $i }}][debit]"
                               value="{{ old("lines.$i.debit") }}"
                               placeholder="0.00"
                               oninput="calcTotals()"
                               style="width:100%;padding:8px;border:1px solid #ddd;
                                      border-radius:4px;text-align:center;">
                     </td>
                    <td style="padding:6px;border:1px solid #e5e7eb;">
                        <input type="number" step="0.01" min="0"
                               name="lines[{{ $i }}][credit]"
                               value="{{ old("lines.$i.credit") }}"
                               placeholder="0.00"
                               oninput="calcTotals()"
                               style="width:100%;padding:8px;border:1px solid #ddd;
                                      border-radius:4px;text-align:center;">
                     </td>
                    <td style="padding:6px;border:1px solid #e5e7eb;">
                        <input type="text" name="lines[{{ $i }}][description]"
                               value="{{ old("lines.$i.description") }}"
                               placeholder="بيان السطر" required
                               style="width:100%;padding:8px;border:1px solid #ddd;
                                      border-radius:4px;">
                     </td>
                    <td style="padding:6px;border:1px solid #e5e7eb;text-align:center;">
                        <button type="button" onclick="removeLine(this)"
                            style="background:#fee2e2;color:#dc2626;
                                   border:none;border-radius:4px;
                                   padding:4px 8px;cursor:pointer;">✕</button>
                     </td>
                 </tr>
                @endfor
            </tbody>
         </table>

        <button type="button" onclick="addLine()"
            style="background:#f0fdf4;border:1px dashed #86efac;
                   color:#166534;padding:8px 18px;border-radius:6px;
                   cursor:pointer;margin-bottom:16px;">
            ➕ إضافة سطر
        </button>

        {{-- ملخص الأرصدة --}}
        <div style="display:flex;gap:20px;background:#f9fafb;
                    border:1px solid #e5e7eb;border-radius:8px;
                    padding:12px 16px;margin-bottom:20px;font-size:13px;">
            <div>إجمالي المدين:
                <strong id="totDebit" style="color:#059669;">0.00</strong> ج.م
            </div>
            <div>إجمالي الدائن:
                <strong id="totCredit" style="color:#dc2626;">0.00</strong> ج.م
            </div>
            <div id="balStat" style="font-weight:bold;"></div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#2563eb;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;flex:1;">
                💾 حفظ القيد
            </button>
            <a href="{{ route('journal.pending') }}"
               style="background:#6b7280;color:white;padding:12px 30px;
                      border-radius:6px;text-decoration:none;
                      text-align:center;flex:1;">
                ❌ إلغاء
            </a>
        </div>
    </form>
</div>

<style>
    /* ========== حقل البحث الديناميكي (acc-wrap) ========== */
    .acc-wrap {
        position: relative;
        width: 100%;
        font-family: Arial, 'Tajawal', sans-serif;
    }
    .acc-trigger {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
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
    .acc-trigger.has-value {
        color: #111827;
        font-weight: 600;
    }
    .acc-trigger:hover {
        border-color: #f59e0b;
    }
    .acc-trigger .arr {
        font-size: 10px;
        color: #9ca3af;
        flex-shrink: 0;
        margin-right: 4px;
    }
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
        min-width: 240px;
    }
    .acc-popup.open {
        display: block;
    }
    .acc-search-box {
        padding: 8px;
        border-bottom: 1px solid #f3f4f6;
    }
    .acc-search-box input {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 12px;
        font-family: inherit;
        direction: rtl;
        box-sizing: border-box;
    }
    .acc-search-box input:focus {
        outline: none;
        border-color: #f59e0b;
        box-shadow: 0 0 0 2px #fef3c755;
    }
    .acc-results {
        max-height: 220px;
        overflow-y: auto;
        padding: 4px 0;
    }
    .acc-item {
        padding: 7px 12px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        gap: 8px;
        align-items: baseline;
        direction: rtl;
        transition: background .12s;
    }
    .acc-item:hover, .acc-item.focused {
        background: #fef9ec;
    }
    .acc-item .acc-code {
        font-family: monospace;
        font-size: 11px;
        color: #6b7280;
        background: #f3f4f6;
        padding: 1px 5px;
        border-radius: 3px;
        flex-shrink: 0;
    }
    .acc-item .acc-name {
        color: #111827;
    }
    .acc-item .acc-name mark {
        background: #fef3c7;
        color: #92400e;
        padding: 0;
    }
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

<script>
// ========== متغيرات البحث الديناميكي ==========
const SEARCH_URL = '{{ route("journal.search") }}'; // يجب أن يكون هذا الـ route موجوداً
let debounceTimer = null;

// ========== دوال البحث و popup ==========
function toggleAccDropdown(trigger) {
    const wrap = trigger.closest('.acc-wrap');
    const popup = wrap.querySelector('.acc-popup');
    const isOpen = popup.classList.contains('open');
    closeAllDropdowns();
    if (!isOpen) {
        popup.classList.add('open');
        trigger.querySelector('.arr').textContent = '▲';
        const searchInput = popup.querySelector('input[type="text"]');
        if (searchInput) {
            searchInput.focus();
            fetchAccounts('', popup);
        }
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('.acc-popup.open').forEach(p => {
        p.classList.remove('open');
        const trigger = p.closest('.acc-wrap').querySelector('.acc-trigger');
        if (trigger) trigger.querySelector('.arr').textContent = '▼';
    });
}

function handleAccSearch(input) {
    clearTimeout(debounceTimer);
    const popup = input.closest('.acc-popup');
    showMsg(popup, 'loading', 'جاري البحث...');
    debounceTimer = setTimeout(() => {
        fetchAccounts(input.value.trim(), popup);
    }, 280);
}

function fetchAccounts(q, popup) {
    const resultsBox = popup.querySelector('.acc-results');
    fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`)
        .then(r => {
            if (!r.ok) throw new Error();
            return r.json();
        })
        .then(data => {
            renderResults(data, q, resultsBox);
        })
        .catch(() => {
            showMsg(popup, 'error', 'حدث خطأ في البحث');
        });
}

function renderResults(accounts, q, resultsBox) {
    resultsBox.innerHTML = '';
    if (!accounts.length) {
        resultsBox.innerHTML = '<div class="acc-msg empty">لا توجد نتائج</div>';
        return;
    }
    accounts.forEach((acc, i) => {
        const item = document.createElement('div');
        item.className = 'acc-item' + (i === 0 ? ' focused' : '');
        item.dataset.id = acc.id;
        item.dataset.code = acc.code;
        item.dataset.name = acc.name;
        item.innerHTML = `
            <span class="acc-code">${acc.code}</span>
            <span class="acc-name">${highlight(acc.name, q)}</span>
        `;
        item.addEventListener('mousedown', e => e.preventDefault());
        item.addEventListener('click', () => selectAccount(item));
        resultsBox.appendChild(item);
    });
}

function highlight(text, q) {
    if (!q) return text;
    const regex = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

function selectAccount(item) {
    const wrap = item.closest('.acc-wrap');
    const trigger = wrap.querySelector('.acc-trigger');
    const hidden = wrap.querySelector('.acc-hidden');
    const label = trigger.querySelector('.acc-label');
    label.textContent = `${item.dataset.code} - ${item.dataset.name}`;
    hidden.value = item.dataset.id;
    trigger.classList.add('has-value');
    closeAllDropdowns();
}

function handleAccKey(e, input) {
    const popup = input.closest('.acc-popup');
    const items = [...popup.querySelectorAll('.acc-item')];
    const focused = popup.querySelector('.acc-item.focused');
    let idx = items.indexOf(focused);
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        const next = items[Math.min(idx + 1, items.length - 1)];
        if (next) { focused?.classList.remove('focused'); next.classList.add('focused'); next.scrollIntoView({ block: 'nearest' }); }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const prev = items[Math.max(idx - 1, 0)];
        if (prev) { focused?.classList.remove('focused'); prev.classList.add('focused'); prev.scrollIntoView({ block: 'nearest' }); }
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (focused) selectAccount(focused);
    } else if (e.key === 'Escape') {
        closeAllDropdowns();
    }
}

function showMsg(popup, type, text) {
    popup.querySelector('.acc-results').innerHTML = `<div class="acc-msg ${type}">${text}</div>`;
}

// ========== إدارة الأسطر والإجماليات (المعدلة لدعم الحقول الجديدة) ==========
let lineIndex = {{ old('lines') ? count(old('lines')) : 2 }};

function addLine() {
    const tbody = document.getElementById('linesBody');
    const tr = document.createElement('tr');
    tr.className = 'line-row';
    tr.innerHTML = `
        <td style="padding:6px;border:1px solid #e5e7eb;">
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
        <td style="padding:6px;border:1px solid #e5e7eb;">
            <input type="number" step="0.01" min="0"
                   name="lines[${lineIndex}][debit]"
                   placeholder="0.00"
                   oninput="calcTotals()"
                   style="width:100%;padding:8px;border:1px solid #ddd;
                          border-radius:4px;text-align:center;">
        </td>
        <td style="padding:6px;border:1px solid #e5e7eb;">
            <input type="number" step="0.01" min="0"
                   name="lines[${lineIndex}][credit]"
                   placeholder="0.00"
                   oninput="calcTotals()"
                   style="width:100%;padding:8px;border:1px solid #ddd;
                          border-radius:4px;text-align:center;">
        </td>
        <td style="padding:6px;border:1px solid #e5e7eb;">
            <input type="text" name="lines[${lineIndex}][description]"
                   placeholder="بيان السطر" required
                   style="width:100%;padding:8px;border:1px solid #ddd;
                          border-radius:4px;">
        </td>
        <td style="padding:6px;border:1px solid #e5e7eb;text-align:center;">
            <button type="button" onclick="removeLine(this)"
                style="background:#fee2e2;color:#dc2626;
                       border:none;border-radius:4px;
                       padding:4px 8px;cursor:pointer;">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
    lineIndex++;
}

function removeLine(btn) {
    const rows = document.querySelectorAll('.line-row');
    if (rows.length <= 2) {
        alert('القيد يحتاج سطرين على الأقل');
        return;
    }
    btn.closest('tr').remove();
    calcTotals();
}

function calcTotals() {
    let d = 0, c = 0;
    document.querySelectorAll('.line-row').forEach(row => {
        d += parseFloat(row.querySelector('input[name*="[debit]"]').value) || 0;
        c += parseFloat(row.querySelector('input[name*="[credit]"]').value) || 0;
    });
    document.getElementById('totDebit').textContent = d.toFixed(2);
    document.getElementById('totCredit').textContent = c.toFixed(2);
    const el = document.getElementById('balStat');
    if (Math.abs(d - c) < 0.01) {
        el.innerHTML = '✅ متوازن';
        el.style.color = '#059669';
    } else {
        el.innerHTML = `❌ فرق: ${Math.abs(d - c).toFixed(2)}`;
        el.style.color = '#dc2626';
    }
}

function updateStatusHint() {
    const hint = document.getElementById('statusHint');
    const val = document.getElementById('statusSel').value;
    if (val === 'draft') {
        hint.textContent = '⚠️ لن يؤثر على الأرصدة حتى يُعتمد';
        hint.style.color = '#92400e';
    } else {
        hint.textContent = '✅ سيُرحَّل فوراً ويؤثر على الأرصدة';
        hint.style.color = '#065f46';
    }
}

// إغلاق الـ dropdown عند النقر خارجها
document.addEventListener('click', function(e) {
    if (!e.target.closest('.acc-wrap')) closeAllDropdowns();
});

// تهيئة القيم القديمة للحسابات عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    @if(old('lines'))
        // إذا كان هناك قيم قديمة، نعرضها في واجهة acc-trigger
        document.querySelectorAll('.line-row').forEach((row, idx) => {
            const hidden = row.querySelector('.acc-hidden');
            if (hidden && hidden.value) {
                const trigger = row.querySelector('.acc-trigger');
                const label = trigger.querySelector('.acc-label');
                // يمكن جلب الاسم والكود من hidden (لا يوجد، لذلك نترك "-- اختر الحساب --")
                // بدلاً من ذلك نعرض رقم المعرف فقط كحل بسيط
                label.textContent = `الحساب رقم ${hidden.value}`;
                trigger.classList.add('has-value');
            }
        });
    @endif
    calcTotals();
});
</script>
</x-app-layout>