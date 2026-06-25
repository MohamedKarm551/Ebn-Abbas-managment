{{-- resources/views/vouchers/payment.blade.php --}}
@extends('layouts.app')
@section('title', isset($isEdit) ? 'تعديل ايصال صرف' : 'ايصال صرف')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;700&display=swap');
* { box-sizing: border-box; }
.voucher-page {
    direction: rtl; font-family: 'Noto Naskh Arabic', serif;
    min-height: 100vh; background: #f3f4f6; padding: 24px 16px;
}
.voucher-wrapper { max-width: 980px; margin: 0 auto; }
.voucher-title-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
}
.voucher-title-bar h4 { font-size: 18px; font-weight: 700; color: #b91c1c; margin: 0; }
.btn-toolbar { display: flex; gap: 8px; flex-wrap: wrap; }
.btn-tool {
    padding: 8px 18px; border-radius: 7px; font-size: 13px;
    font-family: 'Noto Naskh Arabic', serif; cursor: pointer;
    border: none; font-weight: 600; transition: .18s;
}
.btn-save  { background: #059669; color: #fff; }
.btn-save:hover  { background: #047857; }
.btn-print { background: #2563eb; color: #fff; }
.btn-print:hover { background: #1d4ed8; }
.btn-dl    { background: #7c3aed; color: #fff; }
.btn-dl:hover    { background: #6d28d9; }
.btn-back  { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.btn-back:hover  { background: #f9fafb; }
 .receipt-canvas {
        position: relative;
        width: 900px;         
        max-width: 100%;
        margin: 0 auto;
        background-image: url('{{ asset('images/cash_receipt.jpg') }}');
        background-size: 100% 100%;  
        background-repeat: no-repeat;
        background-position: top center;
        aspect-ratio: 900 / 700;
        overflow: hidden;
    }
.v-field {
    position: absolute; font-family: 'Noto Naskh Arabic', serif; font-weight: 700;
    color: #000; background: transparent; border: none; outline: none;
    padding: 0 3px; text-align: right; direction: rtl; white-space: nowrap;
    cursor: text; border-bottom: 1.5px dashed transparent; transition: border-color .15s;
}
.v-field:focus, .v-field:hover { border-bottom-color: #2563eb; background: rgba(37,99,235,.04); }
.acc-wrap-inline { position: absolute; font-family: 'Noto Naskh Arabic', serif; }
.acc-trigger-inline {
    width: 100%; padding: 1px 6px; border: none;
    border-bottom: 1.5px dashed #9ca3af; border-radius: 0;
    font-size: inherit; font-family: inherit; font-weight: 700;
    text-align: right; background: transparent; cursor: pointer; color: #111827;
    display: flex; justify-content: space-between; align-items: center;
    box-sizing: border-box; white-space: nowrap; overflow: hidden; transition: border-color .15s;
}
.acc-trigger-inline:hover, .acc-trigger-inline.open { border-bottom-color: #2563eb; }
.acc-trigger-inline .arr { font-size: 9px; color: #9ca3af; flex-shrink: 0; margin-left: 4px; }
.acc-trigger-inline .acc-label { flex: 1; overflow: hidden; text-overflow: ellipsis; }
 .acc-popup-inline {
        display: none; position: absolute; bottom: calc(100% + 4px);top: auto;right: 0;
        z-index: 9999; background: #fff; border: 1px solid #e5e7eb;
        border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.13);
        min-width: 260px; max-width: 360px; max-height: 250px; overflow: hidden;
    }
.acc-popup-inline.open { display: block; }
.acc-search-box-inline { padding: 8px; border-bottom: 1px solid #f3f4f6; }
.acc-search-box-inline input {
    width: 100%; padding: 6px 10px; border: 1px solid #e5e7eb;
    border-radius: 6px; font-size: 12.5px; font-family: 'Noto Naskh Arabic', serif;
    direction: rtl; box-sizing: border-box;
}
.acc-search-box-inline input:focus { outline: none; border-color: #2563eb; }
.acc-results-inline { max-height: 200px; overflow-y: auto; padding: 4px 0; }
.acc-item-inline {
    padding: 6px 12px; font-size: 12.5px; cursor: pointer;
    display: flex; gap: 8px; align-items: baseline; direction: rtl; transition: background .1s;
}
.acc-item-inline:hover, .acc-item-inline.focused { background: #eff6ff; }
.acc-item-inline .acc-code-i {
    font-family: monospace; font-size: 11px; color: #6b7280;
    background: #f3f4f6; padding: 1px 5px; border-radius: 3px; flex-shrink: 0;
}
.acc-item-inline .acc-name-i mark { background: #fef9c3; color: #92400e; padding: 0; }
.acc-msg-inline { padding: 12px; font-size: 12px; color: #6b7280; text-align: center; }
.amount-input {
    background: transparent; border: none; border-bottom: 1.5px dashed #9ca3af;
    outline: none; font-family: 'Noto Naskh Arabic', serif; font-weight: 700;
    color: #000; text-align: center; direction: ltr; width: 100%; padding: 0 3px;
}
.amount-input:focus { border-bottom-color: #2563eb; }
.cheque-field-solo {
    position: absolute; background: transparent; border: none;
    border-bottom: 1.5px dashed #9ca3af; outline: none;
    font-family: 'Noto Naskh Arabic', serif; font-weight: 700;
    padding: 2px 4px; width: auto; font-size: 1.1vw;
}
.cheque-field-solo:focus { border-bottom-color: #2563eb; }
 @media print {
    .no-print { display: none !important; }
    .linkToSave { display: none !important; }
    
    .voucher-page {
        background: none !important;
        padding: 0 !important;
        min-height: unset !important;
    }
    .voucher-wrapper {
        max-width: 100% !important;
        margin: 0 !important;
    }
    .receipt-canvas {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        background-size: 100% 100% !important;
        page-break-inside: avoid !important;
        aspect-ratio: 900 / 700 !important;
    }

    @page {
        size: A4 landscape;
        margin: 5mm;
    }
}
</style>
@endpush

@section('content')
<div class="voucher-page">
    <div class="voucher-wrapper">
        <div class="voucher-title-bar no-print">
            <h4>💰 {{ isset($isEdit) ? 'تعديل ايصال صرف' : 'ايصال صرف' }}</h4>
            <div class="btn-toolbar">
                <a href="{{ url()->previous() }}" class="btn-tool btn-back">← رجوع</a>
                <button class="btn-tool btn-print" onclick="window.print()">🖨️ طباعة</button>
                <button class="btn-tool btn-dl" id="btnDl">⬇️ تحميل PNG</button>
                <button class="btn-tool btn-save" id="btnSave">
                    {{ isset($isEdit) ? '💾 حفظ التعديلات' : '💾 حفظ القيد' }}
                </button>
            </div>
        </div>

        <div class="receipt-canvas" id="paymentCanvas">
            {{-- رقم المرجع --}}
            <input type="text" class="v-field" id="refNo"
                style="top:34%; right:73%; width:18%; font-size:1.5vw;"
                value="{{ isset($detail) ? $entry->reference : $nextRef }}"
                {{ isset($isEdit) ? 'readonly' : '' }}>

            {{-- تاريخ القيد --}}
            <input type="date" class="v-field" id="entryDate"
                style="top:43.5%; right:70%; width:20%; font-size:1.3vw; direction:ltr; text-align:center;"
                value="{{ isset($detail) ? $entry->entry_date->format('Y-m-d') : date('Y-m-d') }}"
                onkeydown="return false">

            {{-- التاريخ الهجري --}}
            <input type="text" class="v-field" id="hijriDate"
                style="top:43.5%; right:12%; width:23%; font-size:1.3vw;" readonly>

            {{-- صرفنا إلى (المدين) - الجهة --}}
            <div class="acc-wrap-inline" id="debitWrap"
                style="top:51.5%; right:33%; width:42%; font-size:1.35vw;">
                <div class="acc-trigger-inline" id="debitTrigger" onclick="toggleDropdown('debit')">
                    <span class="acc-label" id="debitLabel">
                        {{ isset($detail) ? $detail->debitAccount->name : '— اختر الجهة —' }}
                    </span>
                    <span class="arr">▼</span>
                </div>
                <div class="acc-popup-inline" id="debitPopup">
                    <div class="acc-search-box-inline">
                        <input type="text" placeholder="ابحث باسم الحساب أو الكود..."
                            oninput="searchAcc(this,'debitResults')"
                            onkeydown="accKey(event,this,'debitResults','debit')">
                    </div>
                    <div class="acc-results-inline" id="debitResults">
                        <div class="acc-msg-inline">اكتب للبحث...</div>
                    </div>
                </div>
                <input type="hidden" id="debitAccountId"
                    value="{{ isset($detail) ? $detail->debit_account_id : '' }}">
            </div>

            {{-- المبلغ رقماً --}}
            <input type="number" class="amount-input" id="amountNum"
                style="position:absolute; top:34%; right:2%; width:20%; font-size:1.6vw;"
                step="0.01" min="0"
                value="{{ isset($detail) ? $detail->amount : '' }}"
                oninput="updateAmountArabic()">

            {{-- المبلغ كتابةً --}}
            <input type="text" class="v-field" id="amountText"
                style="top:60%; right:15%; width:75%; font-size:1.3vw;">

            {{-- وذلك عن --}}
            <input type="text" class="v-field" id="subjectField"
                style="top:69%; right:12%; width:80%; font-size:1.3vw;"
                value="{{ isset($detail) ? $detail->subject : '' }}">

            {{-- زر إظهار/إخفاء قائمة الحجوزات --}}
            <div style="position:absolute; top:69%; left:2%; width:5%; font-size:1.1vw; cursor:pointer; color:#2563eb; z-index:10;"
                 id="toggleBookingList" title="إظهار/إخفاء قائمة الحجوزات">
                <i class="fas fa-chevron-down"></i>
            </div>

           {{-- حقل اختيار الحجز للصرف (مرتبط بالحساب المدين/الجهة) --}}
            <div class="booking-select-wrapper" id="bookingSelectWrapper" style="display:none; position:absolute; top:64%; right:32%; width:50%; font-size:1.1vw;">
                <select class="v-field" id="bookingSelect" style="width:100%; padding:4px 6px; border-bottom:1.5px dashed #9ca3af; background:transparent; font-family:inherit; font-weight:700; font-size:1.1vw; color:#000; direction:rtl;">
                    <option value="">— بدون حجز —</option>
                </select>
            </div>


            {{-- الحساب المقابل (الدائن) – الخزينة أو البنك – ظاهر دائماً --}}
            <div class="acc-wrap-inline" id="bankWrap"
                style="top:77%; right:30%; width:30%; font-size:1.1vw; display:block;">
                <div class="acc-trigger-inline no-print" id="creditTrigger" onclick="toggleDropdown('credit')">
                    <span class="acc-label" id="creditLabel">
                        {{ isset($detail) ? $detail->creditAccount->name : '— اختر الحساب المقابل —' }}
                    </span>
                    <span class="arr">▼</span>
                </div>
                <div class="acc-popup-inline" id="creditPopup">
                    <div class="acc-search-box-inline">
                        <input type="text" placeholder="ابحث..." oninput="searchAcc(this,'creditResults')"
                            onkeydown="accKey(event,this,'creditResults','credit')">
                    </div>
                    <div class="acc-results-inline" id="creditResults">
                        <div class="acc-msg-inline">اكتب للبحث...</div>
                    </div>
                </div>
                <input type="hidden" id="creditAccountId"
                    value="{{ isset($detail) ? $detail->credit_account_id : '' }}">
            </div>

            {{-- تاريخ الشيك (اختياري) --}}
            <input type="date" class="cheque-field-solo no-print" id="chequeDate"
                style="top:77%; right:72.8%; width:18%; display:block; direction:ltr;"
                value="{{ isset($detail) && $detail->cheque_date ? $detail->cheque_date->format('Y-m-d') : '' }}"
                onkeydown="return false">

            {{-- التوقيعات --}}
            <input type="text" class="v-field" id="sigReceiver"
                style="top:92%; right:8%; width:15%; font-size:1.15vw; text-align:center;"
                value="{{ isset($detail) ? $detail->sig_receiver : '' }}">
            <input type="text" class="v-field" id="sigAccountant"
                style="top:92%; right:44%; width:15%; font-size:1.15vw; text-align:center;"
                value="{{ isset($detail) ? $detail->sig_accountant : '' }}">
            <input type="text" class="v-field" id="sigManager"
                style="top:92%; right:75%; width:16%; font-size:1.15vw; text-align:center;"
                value="{{ isset($detail) ? $detail->sig_manager : '' }}">
        </div>{{-- /payment-canvas --}}
    </div>

    <div class="linkToSave no-print" style="margin-top:20px; text-align:center;">
        <label for="link" style="font-size: larger;font-weight: bold;color: red;">لينك صورة الدفع (اختياري):</label><br>
        <input type="text" name="link" id="link" style="width:50%; padding:6px;">
    </div>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
    @isset($isEdit)
        const SAVE_URL = '{{ route('vouchers.update', $entry->id) }}';
        const VOUCHER_TYPE = '{{ $entry->source_type }}';
        const IS_EDIT = true;
    @else
        const SAVE_URL = '{{ route('vouchers.save') }}';
        const VOUCHER_TYPE = 'payment';
        const IS_EDIT = false;
    @endisset

    const SEARCH_URL = '{{ route('accounts.search') }}';
    const CSRF = '{{ csrf_token() }}';
    let debounceT = null;

    // ══ التاريخ الهجري ══
    document.getElementById('entryDate').addEventListener('change', function() {
        try {
            const h = new Intl.DateTimeFormat('ar-SA-u-ca-islamic', {
                day: 'numeric', month: 'long', year: 'numeric'
            }).format(new Date(this.value));
            document.getElementById('hijriDate').value = h;
        } catch(e) {}
    });

    // ══ تحويل المبلغ لكلمات ══
    function numberToArabicWords(n) {
        if (!n || isNaN(n)) return '';
        n = parseFloat(n);
        const ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة', 'أحد عشر',
            'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'
        ];
        const tens = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
        const hunds = ['', 'مئة', 'مئتان', 'ثلاثمئة', 'أربعمئة', 'خمسمئة', 'ستمئة', 'سبعمئة', 'ثمانئة', 'تسعمئة'];
        function below1000(num) {
            if (num === 0) return '';
            if (num < 20) return ones[num];
            if (num < 100) { const t = Math.floor(num/10), o = num%10; return o>0 ? ones[o]+' و'+tens[t] : tens[t]; }
            const h = Math.floor(num/100), rest = num%100;
            return rest>0 ? hunds[h]+' و'+below1000(rest) : hunds[h];
        }
        const intPart = Math.floor(n), decPart = Math.round((n - intPart)*100);
        let words = intPart>=1000 ? below1000(Math.floor(intPart/1000))+' ألف'+(intPart%1000>0?' و'+below1000(intPart%1000):'') : below1000(intPart);
        words += ' ريال سعودي';
        if (decPart>0) words += ' و'+below1000(decPart)+' هللة';
        words += ' فقط لا غير';
        return words;
    }
    function updateAmountArabic() {
        document.getElementById('amountText').value = numberToArabicWords(document.getElementById('amountNum').value);
    }

    // ══ Dropdown الحسابات ══
    function toggleDropdown(which) {
        const popup = document.getElementById(which + 'Popup');
        const trigger = document.getElementById(which + 'Trigger');
        const isOpen = popup.classList.contains('open');
        closeAllDropdowns();
        if (!isOpen) {
            popup.classList.add('open');
            trigger.classList.add('open');
            const inp = popup.querySelector('input[type=text]');
            if (inp) inp.focus();
            fetchAccInline('', document.getElementById(which + 'Results'), which);
        }
    }
    function closeAllDropdowns() {
        ['debit', 'credit'].forEach(w => {
            document.getElementById(w + 'Popup')?.classList.remove('open');
            document.getElementById(w + 'Trigger')?.classList.remove('open');
        });
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('.acc-wrap-inline')) closeAllDropdowns();
    });

    function searchAcc(input, resultsId) {
        clearTimeout(debounceT);
        const resultsBox = document.getElementById(resultsId);
        resultsBox.innerHTML = '<div class="acc-msg-inline">⏳ جاري البحث...</div>';
        const which = resultsId.replace('Results', '');
        debounceT = setTimeout(() => fetchAccInline(input.value.trim(), resultsBox, which), 280);
    }

    function fetchAccInline(q, resultsBox, which) {
        fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => renderAccItems(data, q, resultsBox, which))
            .catch(() => { resultsBox.innerHTML = '<div class="acc-msg-inline">⚠️ خطأ في البحث</div>'; });
    }

    function renderAccItems(accounts, q, resultsBox, which) {
        resultsBox.innerHTML = '';
        if (!accounts.length) {
            resultsBox.innerHTML = '<div class="acc-msg-inline">🔍 لا توجد نتائج</div>';
            return;
        }
        accounts.forEach((acc, i) => {
            const item = document.createElement('div');
            item.className = 'acc-item-inline' + (i === 0 ? ' focused' : '');
            item.dataset.id = acc.id;
            item.dataset.code = acc.code;
            item.dataset.name = acc.name;
            const hl = q ? acc.name.replace(new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'), '<mark>$1</mark>') : acc.name;
            item.innerHTML = `<span class="acc-code-i">${acc.code}</span><span class="acc-name-i">${hl}</span>`;
            item.addEventListener('mousedown', e => e.preventDefault());
            item.addEventListener('click', () => selectAcc(item, which));
            resultsBox.appendChild(item);
        });
    }

    // دالة selectAcc الأصلية (سيتم تعديلها لاحقاً)
    function selectAcc(item, which) {
        document.getElementById(which + 'Label').textContent = item.dataset.name;
        document.getElementById(which + 'AccountId').value = item.dataset.id;
        closeAllDropdowns();
    }

    function accKey(e, input, resultsId, which) {
        const resultsBox = document.getElementById(resultsId);
        const items = [...resultsBox.querySelectorAll('.acc-item-inline')];
        const focused = resultsBox.querySelector('.acc-item-inline.focused');
        const idx = items.indexOf(focused);
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const n = items[Math.min(idx + 1, items.length - 1)];
            if (n) { focused?.classList.remove('focused'); n.classList.add('focused'); n.scrollIntoView({block:'nearest'}); }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const p = items[Math.max(idx - 1, 0)];
            if (p) { focused?.classList.remove('focused'); p.classList.add('focused'); p.scrollIntoView({block:'nearest'}); }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (focused) selectAcc(focused, which);
        } else if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    }

    // ══ التحقق من تاريخ الشيك (اختياري) ══
    function validateChequeDate() {
        const entryDate = document.getElementById('entryDate').value;
        const chequeDateInput = document.getElementById('chequeDate');
        if (!entryDate || !chequeDateInput.value) return;
        if (chequeDateInput.value > entryDate) {
            alert('⚠️ تاريخ الشيك لا يمكن أن يكون بعد تاريخ القيد.');
            chequeDateInput.value = entryDate;
        }
    }
    document.getElementById('entryDate').addEventListener('change', function() {
        try {
            const h = new Intl.DateTimeFormat('ar-SA-u-ca-islamic', {
                day: 'numeric', month: 'long', year: 'numeric'
            }).format(new Date(this.value));
            document.getElementById('hijriDate').value = h;
        } catch(e) {}
        validateChequeDate();
    });
    document.getElementById('chequeDate').addEventListener('change', validateChequeDate);

    // ══ جلب الحجوزات المفتوحة عند تغيير الجهة (المدين في الصرف) ══
    let selectedBookingId = null;
    let selectedBookingData = null;

    // دالة تحميل الحجوزات
    function loadOpenBookings(voucherType) {
        // في الصرف، الجهة هي المدين (debitAccountId)
        const accountId = document.getElementById('debitAccountId').value;
        
        const wrapper = document.getElementById('bookingSelectWrapper');
        const select = document.getElementById('bookingSelect');
        
        // إخفاء القائمة إذا لم يكن هناك حساب محدد
        if (!accountId) {
            wrapper.style.display = 'none';
            return;
        }
        
        // إظهار مؤشر تحميل
        select.innerHTML = '<option value="">⏳ جاري التحميل...</option>';
        if (!IS_EDIT) {
        wrapper.style.display = 'block';
        }
        
        fetch(`/vouchers/open-bookings?account_id=${accountId}&voucher_type=${voucherType}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.has_bookings) {
                    // تعبئة الـ select
                    select.innerHTML = '<option value="">— بدون حجز —</option>';
                    data.bookings.forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b.id;
                        opt.textContent = b.label;
                        opt.dataset.remaining = b.remaining; // المتبقي الأصلي
                        opt.dataset.originalRemaining = b.remaining; // حفظ النسخة الأصلية
                        opt.dataset.clientName = b.client_name;
                        select.appendChild(opt);
                    });
                    if (!IS_EDIT) {
                        wrapper.style.display = 'block';
                        document.getElementById('toggleBookingList').innerHTML = '<i class="fas fa-chevron-up"></i>';
                    }
                    // ✅ إذا كان هناك حجز محدد مسبقاً (في حالة التعديل)، نختاره
                    if (selectedBookingId) {
                        select.value = selectedBookingId;
                        updateBookingDataAndSubject(); // تحديث البيانات فوراً
                    }
                } else {
                    // لا توجد حجوزات مفتوحة
                    select.innerHTML = '<option value="">— لا توجد حجوزات مفتوحة —</option>';
                    wrapper.style.display = 'none';
                    if (!IS_EDIT) {
                    document.getElementById('toggleBookingList').innerHTML = '<i class="fas fa-chevron-down"></i>';
                }
                }
            })
            .catch(() => {
                select.innerHTML = '<option value="">⚠️ خطأ في التحميل</option>';
                wrapper.style.display = 'block';
            });
    }

    // ══ دالة تحديث بيانات الحجز وحقل subject عند تغيير المبلغ ══
    function updateBookingDataAndSubject() {
        const select = document.getElementById('bookingSelect');
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            // لا يوجد حجز مختار
            selectedBookingData = null;
            return;
        }

        const originalRemaining = parseFloat(selectedOption.dataset.originalRemaining || 0);
        const clientName = selectedOption.dataset.clientName || 'عميل';
        const amountInput = document.getElementById('amountNum');
        const enteredAmount = parseFloat(amountInput.value) || 0;
        const newRemaining = Math.max(0, originalRemaining - enteredAmount);

        // تحديث نص الـ option لعرض المتبقي الجديد
        // (نحتفظ بالـ label الأساسي ولكن نغير المبلغ)
        const label = `${clientName} - ${newRemaining.toFixed(2)} متبقي (دخول: ... )`;
        selectedOption.textContent = label;

        // تحديث حقل subject (وذلك عن)
        const subjectField = document.getElementById('subjectField');
        const autoNote = `دفعة للحجز: ${clientName} - المبلغ المدفوع: ${enteredAmount.toFixed(2)} - المتبقي: ${newRemaining.toFixed(2)}`;
        
        // إذا كان الحقل فارغاً أو يحتوي على نص تلقائي سابق، استبدله
        // وإلا أضف النص التلقائي في نهاية ما كتبه المستخدم
        const currentSubject = subjectField.value.trim();
        if (!currentSubject || currentSubject.startsWith('دفعة للحجز:')) {
            subjectField.value = autoNote;
        } else {
            // نضيف النص التلقائي في نهاية ما كتبه المستخدم (مع فاصل)
            subjectField.value = currentSubject + ' | ' + autoNote;
        }

        // تخزين بيانات الحجز
        selectedBookingData = {
            id: selectedOption.value,
            clientName: clientName,
            originalRemaining: originalRemaining,
            enteredAmount: enteredAmount,
            newRemaining: newRemaining
        };
    }

    // ══ مستمع لتغيير المبلغ (تم إضافته في oninput أعلاه) ══
    // يتم استدعاء updateBookingDataAndSubject() مباشرة من oninput في حقل amountNum

    // ══ مستمع لتغيير المبلغ ══
   document.getElementById('amountNum').addEventListener('input', function() {
    updateAmountArabic();
    
    @isset($isEdit)
    const bookingId = '{{ $detail->booking_id ?? "" }}';
    if (bookingId && bookingId !== '' && bookingId !== 'null') {
        const amount = parseFloat(this.value) || 0;
        const subjectField = document.getElementById('subjectField');
        const clientName = '{{ optional($detail->booking)->client_name ?? "" }}';
        const originalDue = {{ optional($detail->booking)->amount_due_to_hotel ?? 0 }};
        const alreadyPaid = {{ optional($detail->booking)->amount_paid_to_hotel ?? 0 }};
        const newRemaining = Math.max(0, originalDue - alreadyPaid - amount + {{ $detail->amount ?? 0 }});
        subjectField.value = `دفعة للحجز: ${clientName} - المبلغ المدفوع: ${amount.toFixed(2)} - المتبقي: ${newRemaining.toFixed(2)}`;
    }
    @endisset
    
    if (selectedBookingId) {
        updateBookingDataAndSubject();
    }
});

    // ══ تعديل دالة selectAcc لاستدعاء loadOpenBookings عند اختيار الجهة (المدين) ══
    const originalSelectAcc = selectAcc;
    selectAcc = function(item, which) {
        // استدعاء الدالة الأصلية
        originalSelectAcc(item, which);
        
        // ✅ تحميل الحجوزات فقط عند اختيار الحساب المدين (الجهة) في الصرف
        if (which === 'debit') {
            loadOpenBookings(VOUCHER_TYPE);
        }
    };

    // ══ حفظ / تحديث ══
    document.getElementById('btnSave').addEventListener('click', async function() {
        const ref = document.getElementById('refNo').value.trim();
        const date = document.getElementById('entryDate').value;
        const amount = document.getElementById('amountNum').value;
        const debitId = document.getElementById('debitAccountId').value;  // الجهة (المدين)
        const creditId = document.getElementById('creditAccountId').value; // الحساب المقابل (الدائن)

        // تحقق اختياري من تاريخ الشيك
        const chequeDate = document.getElementById('chequeDate').value;
        if (chequeDate && chequeDate > date) {
            alert('❌ تاريخ الشيك لا يمكن أن يكون بعد تاريخ القيد');
            return;
        }

        if (!ref || !date || !amount || !debitId || !creditId) {
            alert('يرجى تعبئة جميع الحقول المطلوبة');
            return;
        }

        // subject تم تحديثه تلقائياً بواسطة updateBookingDataAndSubject
        // نأخذ القيمة الحالية من الحقل
        const finalSubject = document.getElementById('subjectField').value.trim();

        const body = new FormData();
        body.append('_token', CSRF);
        body.append('voucher_type', VOUCHER_TYPE);
        body.append('reference', ref);
        body.append('entry_date', date);
        body.append('amount', amount);
        body.append('debit_account_id', debitId);
        body.append('credit_account_id', creditId);
        body.append('subject', finalSubject);
        body.append('cheque_date', document.getElementById('chequeDate').value);
        body.append('sig_receiver', document.getElementById('sigReceiver').value);
        body.append('sig_accountant', document.getElementById('sigAccountant').value);
        body.append('sig_manager', document.getElementById('sigManager').value);
        body.append('booking_id', selectedBookingId || '');
        if (IS_EDIT) body.append('_method', 'PUT');

        this.disabled = true;
        this.textContent = '⏳ جاري الحفظ...';
        try {
            const res = await fetch(SAVE_URL, { method: 'POST', body });
            const json = await res.json();
            if (json.success) {
                alert(IS_EDIT ? '✅ تم حفظ التعديلات بنجاح' : '✅ تم حفظ القيد بنجاح');
                if (IS_EDIT) window.location.href = '{{ route('journal.index') }}';
            } else {
                alert('❌ فشل: ' + (json.message || 'خطأ غير معروف'));
            }
        } catch(e) {
            alert('⚠️ خطأ في الاتصال');
        }
        this.disabled = false;
        this.textContent = IS_EDIT ? '💾 حفظ التعديلات' : '💾 حفظ القيد';
    });

    // ══ تحميل PNG ══
    document.getElementById('btnDl').addEventListener('click', function() {
        closeAllDropdowns();

        // إخفاء العناصر المراد حذفها من الـ PNG
        const bankWrap = document.getElementById('bankWrap');
        const chequeDate = document.getElementById('chequeDate');
        const toggleBtn = document.getElementById('toggleBookingList');
        const bookingWrapper = document.getElementById('bookingSelectWrapper');

        bankWrap.style.visibility = 'hidden';
        chequeDate.style.visibility = 'hidden';
        toggleBtn.style.visibility = 'hidden';
        bookingWrapper.style.visibility = 'hidden';

        html2canvas(document.getElementById('paymentCanvas'), {
            scale: 3, useCORS: true, backgroundColor: null
        }).then(c => {
             // إعادة الإظهار
            bankWrap.style.visibility = '';
            chequeDate.style.visibility = '';
            toggleBtn.style.visibility = '';
            bookingWrapper.style.visibility = '';
            const a = document.createElement('a');
            a.download = `ايصال_صرف_${document.getElementById('refNo').value||'new'}.png`;
            a.href = c.toDataURL('image/png', 1.0);
            a.click();
        });
    });
       
    // ══ تهيئة أولية ══
    document.getElementById('entryDate').dispatchEvent(new Event('change'));
    updateAmountArabic();

    // ══ وضع التعديل: ملء الحسابات مباشرة ══
@isset($isEdit)
    // الجهة (المدين)
    document.getElementById('debitAccountId').value = '{{ $detail->debit_account_id }}';
    document.getElementById('debitLabel').textContent = '{{ $detail->debitAccount->name }}';
    // الحساب المقابل (الدائن)
    document.getElementById('creditAccountId').value = '{{ $detail->credit_account_id }}';
    document.getElementById('creditLabel').textContent = '{{ $detail->creditAccount->name }}';
    document.getElementById('chequeDate').value = '{{ $detail->cheque_date ? $detail->cheque_date->format('Y-m-d') : '' }}';
    
    // ✅ تعيين selectedBookingId إذا كان هناك حجز مرتبط
    @if($detail->booking_id)
        selectedBookingId = '{{ $detail->booking_id }}';
    @endif
    
    // ✅ لا نحمّل الحجوزات تلقائياً
@endisset

document.getElementById('bookingSelect').addEventListener('change', function() {
    selectedBookingId = this.value || null;
    const wrapper = document.getElementById('bookingSelectWrapper');
    if (selectedBookingId) {
        updateBookingDataAndSubject();
        // ✅ إخفاء القائمة بعد الاختيار
        wrapper.style.display = 'none';
    } else {
        selectedBookingData = null;
        // ✅ إذا اختار "بدون حجز"، تظهر القائمة مرة أخرى (في حال كانت مخفية)
        wrapper.style.display = 'none';
    }
});


// ══ التحكم اليدوي في إظهار/إخفاء قائمة الحجوزات ══
document.getElementById('toggleBookingList').addEventListener('click', function() {
    const wrapper = document.getElementById('bookingSelectWrapper');
    const select = document.getElementById('bookingSelect');
    
    // إذا كانت القائمة مخفية أو ليس لها عرض، نظهرها
    if (wrapper.style.display === 'none' || wrapper.style.display === '') {
        // إذا كان الـ select فارغاً (لا توجد خيارات) نحمّل الحجوزات
        if (select.options.length <= 1) { // الخيار الافتراضي فقط
            wrapper.style.display = 'block';
            loadOpenBookings(VOUCHER_TYPE);
        } else {
            // إذا كانت هناك خيارات بالفعل، نظهر القائمة فقط
            wrapper.style.display = 'block';
        }
        this.innerHTML = '<i class="fas fa-chevron-up"></i>'; // تغيير الأيقونة
    } else {
        // إخفاء القائمة
        wrapper.style.display = 'none';
        this.innerHTML = '<i class="fas fa-chevron-down"></i>';
    }
});

</script>
@endsection