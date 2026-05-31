{{-- resources/views/vouchers/payment.blade.php --}}
@extends('layouts.app')
@section('title', isset($isEdit) ? 'تعديل ايصال صرف' : 'ايصال صرف نقدية')

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
    position: relative; width: 100%; max-width: 900px; margin: 0 auto;
    background-image: url('{{ asset("images/cash_exchange.jpg") }}');
    background-size: contain; background-repeat: no-repeat;
    background-position: top center; aspect-ratio: 900 / 700; overflow: visible;
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
    display: none; position: absolute; top: calc(100% + 2px); right: 0;
    z-index: 9999; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.13);
    min-width: 260px; max-width: 360px; overflow: hidden;
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
.payoption {
    position: absolute; display: inline-flex; align-items: center;
    gap: 6px; cursor: pointer; user-select: none;
}
.payoption .check-box {
    display: inline-block; width: 18px; height: 18px; background: #fff;
    border: 1px solid #6b7280; border-radius: 3px; position: relative;
}
.payoption.selected .check-box::after {
    content: "✓"; position: absolute; top: -2px; right: 2px;
    font-size: 16px; font-weight: bold; color: #110596;
}
.cheque-field-solo {
    position: absolute; background: transparent; border: none;
    border-bottom: 1.5px dashed #9ca3af; outline: none;
    font-family: 'Noto Naskh Arabic', serif; font-weight: 700;
    padding: 2px 4px; width: auto; font-size: 1.1vw;
}
.cheque-field-solo:focus { border-bottom-color: #2563eb; }
@media print {
    .no-print { display: none !important; }
    .receipt-canvas {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        background-size: contain !important;
    }
}
</style>
@endpush

@section('content')
<div class="voucher-page">
  <div class="voucher-wrapper">

    <div class="voucher-title-bar no-print">
      <h4>💰 {{ isset($isEdit) ? 'تعديل ايصال صرف' : 'ايصال صرف نقدية' }}</h4>
      <div class="btn-toolbar">
        <a href="{{ url()->previous() }}" class="btn-tool btn-back">← رجوع</a>
        <button class="btn-tool btn-print" onclick="window.print()">🖨️ طباعة</button>
        <button class="btn-tool btn-dl"   id="btnDl">⬇️ تحميل PNG</button>
        <button class="btn-tool btn-save" id="btnSave">
            {{ isset($isEdit) ? '💾 حفظ التعديلات' : '💾 حفظ القيد' }}
        </button>
      </div>
    </div>

    <div class="receipt-canvas" id="paymentCanvas">

      {{-- رقم المرجع --}}
      <input type="text" class="v-field" id="refNo"
             style="top:27.5%; right:73%; width:18%; font-size:1.5vw;"
             value="{{ isset($detail) ? $entry->reference : $nextRef }}"
             {{ isset($isEdit) ? 'readonly' : '' }}>

      {{-- تاريخ القيد --}}
      <input type="date" class="v-field" id="entryDate"
             style="top:35.5%; right:70%; width:20%; font-size:1.3vw; direction:ltr; text-align:center;"
             value="{{ isset($detail) ? $entry->entry_date->format('Y-m-d') : date('Y-m-d') }}"
             onkeydown="return false">

      {{-- التاريخ الهجري --}}
      <input type="text" class="v-field" id="hijriDate"
             style="top:35.5%; right:12%; width:23%; font-size:1.3vw;" readonly>

      {{-- صرفنا إلى (المدين) --}}
      <div class="acc-wrap-inline" id="debitWrap"
           style="top:42.5%; right:33%; width:42%; font-size:1.35vw;">
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
             style="position:absolute; top:28%; right:2%; width:20%; font-size:1.6vw;"
             step="0.01" min="0"
             value="{{ isset($detail) ? $detail->amount : '' }}"
             oninput="updateAmountArabic()">

      {{-- المبلغ كتابةً --}}
      <input type="text" class="v-field" id="amountText"
             style="top:48%; right:15%; width:75%; font-size:1.3vw;" readonly>

      {{-- وذلك عن --}}
      <input type="text" class="v-field" id="subjectField"
             style="top:55.5%; right:12%; width:70%; font-size:1.3vw;"
             value="{{ isset($detail) ? $detail->subject : '' }}">

      {{-- نقد --}}
      <div class="payoption" id="optionCash" data-method="cash"
           style="top:63.6%; right:7.7%;">
        <span class="check-box"></span>
      </div>

      {{-- شيك --}}
      <div class="payoption" id="optionCheque" data-method="cheque"
           style="top:63.6%; right:17.8%;">
        <span class="check-box"></span>
      </div>

      {{-- قائمة البنوك للشيك (الدائن في الصرف) --}}
      <div class="acc-wrap-inline" id="bankWrap"
           style="top:62%; right:55%; width:30%; font-size:1.1vw; display:none;">
        <div class="acc-trigger-inline" id="creditTrigger" onclick="toggleDropdown('credit')">
          <span class="acc-label" id="creditLabel">— اختر البنك/الحساب —</span>
          <span class="arr">▼</span>
        </div>
        <div class="acc-popup-inline" id="creditPopup">
          <div class="acc-search-box-inline">
            <input type="text" placeholder="ابحث..."
                   oninput="searchAcc(this,'creditResults')"
                   onkeydown="accKey(event,this,'creditResults','credit')">
          </div>
          <div class="acc-results-inline" id="creditResults">
            <div class="acc-msg-inline">اكتب للبحث...</div>
          </div>
        </div>
        <input type="hidden" id="creditAccountId" value="">
      </div>

      {{-- رقم الشيك --}}
      <input type="text" class="cheque-field-solo" id="chequeNumber"
             style="top:62%; right:21%; width:12%; display:none;"
             value="{{ isset($detail) ? $detail->cheque_number : '' }}">

      {{-- تاريخ الشيك --}}
      <input type="date" class="cheque-field-solo" id="chequeDate"
             style="top:62.2%; right:72.8%; width:18%; display:none; direction:ltr;"
             value="{{ isset($detail) && $detail->cheque_date ? $detail->cheque_date->format('Y-m-d') : date('Y-m-d') }}"
             onkeydown="return false">

      {{-- الصندوق الافتراضي للنقد (الدائن في الصرف) --}}
      <input type="hidden" id="defaultCashAccountId"
             value="{{ isset($detail) && $detail->payment_method === 'cash' ? $detail->credit_account_id : $creditAccounts->first()?->id }}">
      <input type="hidden" id="defaultCashAccountName"
             value="{{ isset($detail) && $detail->payment_method === 'cash' ? $detail->creditAccount->name : $creditAccounts->first()?->name }}">

      {{-- التوقيعات --}}
      <input type="text" class="v-field" id="sigReceiver"
             style="top:74%; right:8%; width:15%; font-size:1.15vw; text-align:center;"
             value="{{ isset($detail) ? $detail->sig_receiver : '' }}">
      <input type="text" class="v-field" id="sigAccountant"
             style="top:74%; right:44%; width:15%; font-size:1.15vw; text-align:center;"
             value="{{ isset($detail) ? $detail->sig_accountant : '' }}">
      <input type="text" class="v-field" id="sigManager"
             style="top:74%; right:75%; width:16%; font-size:1.15vw; text-align:center;"
             value="{{ isset($detail) ? $detail->sig_manager : '' }}">

    </div>{{-- /payment-canvas --}}
  </div>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
// ══ إعدادات ══
@isset($isEdit)
    const SAVE_URL     = '{{ route("vouchers.update", $entry->id) }}';
    const VOUCHER_TYPE = '{{ $entry->source_type }}';
    const IS_EDIT      = true;
@else
    const SAVE_URL     = '{{ route("vouchers.save") }}';
    const VOUCHER_TYPE = 'payment';
    const IS_EDIT      = false;
@endisset

const SEARCH_URL = '{{ route("accounts.search") }}';
const CSRF       = '{{ csrf_token() }}';
let debounceT    = null;

// ══ التاريخ الهجري ══
document.getElementById('entryDate').addEventListener('change', function() {
    try {
        const h = new Intl.DateTimeFormat('ar-SA-u-ca-islamic', {
            day:'numeric', month:'long', year:'numeric'
        }).format(new Date(this.value));
        document.getElementById('hijriDate').value = h;
    } catch(e) {}
});

// ══ تحويل المبلغ لكلمات ══
function numberToArabicWords(n) {
    if (!n||isNaN(n)) return '';
    n=parseFloat(n);
    const ones=['','واحد','اثنان','ثلاثة','أربعة','خمسة','ستة','سبعة','ثمانية','تسعة','عشرة','أحد عشر','اثنا عشر','ثلاثة عشر','أربعة عشر','خمسة عشر','ستة عشر','سبعة عشر','ثمانية عشر','تسعة عشر'];
    const tens=['','','عشرون','ثلاثون','أربعون','خمسون','ستون','سبعون','ثمانون','تسعون'];
    const hunds=['','مئة','مئتان','ثلاثمئة','أربعمئة','خمسمئة','ستمئة','سبعمئة','ثمانئة','تسعمئة'];
    function below1000(num){
        if(num===0)return '';
        if(num<20)return ones[num];
        if(num<100){const t=Math.floor(num/10),o=num%10;return o>0?ones[o]+' و'+tens[t]:tens[t];}
        const h=Math.floor(num/100),rest=num%100;
        return rest>0?hunds[h]+' و'+below1000(rest):hunds[h];
    }
    const intPart=Math.floor(n),decPart=Math.round((n-intPart)*100);
    let words=intPart>=1000?below1000(Math.floor(intPart/1000))+' ألف'+(intPart%1000>0?' و'+below1000(intPart%1000):''):below1000(intPart);
    words+=' ريال سعودي';
    if(decPart>0)words+=' و'+below1000(decPart)+' هللة';
    words+=' فقط لا غير';
    return words;
}
function updateAmountArabic(){
    document.getElementById('amountText').value=
        numberToArabicWords(document.getElementById('amountNum').value);
}

// ══ طريقة الدفع ══
let currentPayMethod='cash';
function setPaymentMethod(method){
    currentPayMethod=method;
    document.getElementById('optionCash').classList.toggle('selected',method==='cash');
    document.getElementById('optionCheque').classList.toggle('selected',method==='cheque');
    const bankWrap=document.getElementById('bankWrap');
    const chequeNumberField=document.getElementById('chequeNumber');
    const chequeDateField=document.getElementById('chequeDate');
    if(method==='cash'){
        bankWrap.style.display='none';
        chequeNumberField.style.display='none';
        chequeDateField.style.display='none';
        document.getElementById('creditAccountId').value=document.getElementById('defaultCashAccountId').value;
        document.getElementById('creditLabel').innerText=document.getElementById('defaultCashAccountName').value;
    }else{
        bankWrap.style.display='block';
        chequeNumberField.style.display='block';
        chequeDateField.style.display='block';
        document.getElementById('creditAccountId').value='';
        document.getElementById('creditLabel').innerText='— اختر البنك/الحساب —';
    }
}
document.getElementById('optionCash').addEventListener('click',()=>setPaymentMethod('cash'));
document.getElementById('optionCheque').addEventListener('click',()=>setPaymentMethod('cheque'));

// ══ Dropdown الحسابات ══
function toggleDropdown(which){
    const popup=document.getElementById(which+'Popup');
    const trigger=document.getElementById(which+'Trigger');
    const isOpen=popup.classList.contains('open');
    closeAllDropdowns();
    if(!isOpen){
        popup.classList.add('open');trigger.classList.add('open');
        const inp=popup.querySelector('input[type=text]');
        if(inp)inp.focus();
        fetchAccInline('',document.getElementById(which+'Results'),which);
    }
}
function closeAllDropdowns(){
    ['debit','credit'].forEach(w=>{
        document.getElementById(w+'Popup')?.classList.remove('open');
        document.getElementById(w+'Trigger')?.classList.remove('open');
    });
}
document.addEventListener('click',e=>{if(!e.target.closest('.acc-wrap-inline'))closeAllDropdowns();});
function searchAcc(input,resultsId){
    clearTimeout(debounceT);
    const resultsBox=document.getElementById(resultsId);
    resultsBox.innerHTML='<div class="acc-msg-inline">⏳ جاري البحث...</div>';
    const which=resultsId.replace('Results','');
    debounceT=setTimeout(()=>fetchAccInline(input.value.trim(),resultsBox,which),280);
}
function fetchAccInline(q,resultsBox,which){
    fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`)
        .then(r=>r.json())
        .then(data=>renderAccItems(data,q,resultsBox,which))
        .catch(()=>{resultsBox.innerHTML='<div class="acc-msg-inline">⚠️ خطأ في البحث</div>';});
}
function renderAccItems(accounts,q,resultsBox,which){
    resultsBox.innerHTML='';
    if(!accounts.length){resultsBox.innerHTML='<div class="acc-msg-inline">🔍 لا توجد نتائج</div>';return;}
    accounts.forEach((acc,i)=>{
        const item=document.createElement('div');
        item.className='acc-item-inline'+(i===0?' focused':'');
        item.dataset.id=acc.id;item.dataset.code=acc.code;item.dataset.name=acc.name;
        const hl=q?acc.name.replace(new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`,'gi'),'<mark>$1</mark>'):acc.name;
        item.innerHTML=`<span class="acc-code-i">${acc.code}</span><span class="acc-name-i">${hl}</span>`;
        item.addEventListener('mousedown',e=>e.preventDefault());
        item.addEventListener('click',()=>selectAcc(item,which));
        resultsBox.appendChild(item);
    });
}
function selectAcc(item,which){
    document.getElementById(which+'Label').textContent=item.dataset.name;
    document.getElementById(which+'AccountId').value=item.dataset.id;
    closeAllDropdowns();
}
function accKey(e,input,resultsId,which){
    const resultsBox=document.getElementById(resultsId);
    const items=[...resultsBox.querySelectorAll('.acc-item-inline')];
    const focused=resultsBox.querySelector('.acc-item-inline.focused');
    const idx=items.indexOf(focused);
    if(e.key==='ArrowDown'){e.preventDefault();const n=items[Math.min(idx+1,items.length-1)];if(n){focused?.classList.remove('focused');n.classList.add('focused');n.scrollIntoView({block:'nearest'});}}
    else if(e.key==='ArrowUp'){e.preventDefault();const p=items[Math.max(idx-1,0)];if(p){focused?.classList.remove('focused');p.classList.add('focused');p.scrollIntoView({block:'nearest'});}}
    else if(e.key==='Enter'){e.preventDefault();if(focused)selectAcc(focused,which);}
    else if(e.key==='Escape'){closeAllDropdowns();}
}

// ══ التحقق من تاريخ الشيك ══
function validateChequeDate(){
    const entryDate=document.getElementById('entryDate').value;
    const chequeDateInput=document.getElementById('chequeDate');
    if(!entryDate||!chequeDateInput.value)return;
    if(chequeDateInput.value>entryDate){
        alert('⚠️ تاريخ الشيك لا يمكن أن يكون بعد تاريخ القيد.');
        chequeDateInput.value=entryDate;
    }
}
document.getElementById('entryDate').addEventListener('change',function(){
    try{
        const h=new Intl.DateTimeFormat('ar-SA-u-ca-islamic',{day:'numeric',month:'long',year:'numeric'}).format(new Date(this.value));
        document.getElementById('hijriDate').value=h;
    }catch(e){}
    if(currentPayMethod==='cheque')validateChequeDate();
});
document.getElementById('chequeDate').addEventListener('change',validateChequeDate);

// ══ حفظ / تحديث ══
document.getElementById('btnSave').addEventListener('click',async function(){
    const ref    =document.getElementById('refNo').value.trim();
    const date   =document.getElementById('entryDate').value;
    const amount =document.getElementById('amountNum').value;
    const debitId=document.getElementById('debitAccountId').value;
    const method =currentPayMethod;
    let creditId =method==='cash'
        ?document.getElementById('defaultCashAccountId').value
        :document.getElementById('creditAccountId').value;

    if(method==='cheque'){
        const chequeDate=document.getElementById('chequeDate').value;
        if(chequeDate>date){alert('❌ تاريخ الشيك لا يمكن أن يكون بعد تاريخ القيد');return;}
    }
    if(!ref||!date||!amount||!debitId||!creditId){
        alert('يرجى تعبئة جميع الحقول المطلوبة');return;
    }

    const body=new FormData();
    body.append('_token',           CSRF);
    body.append('voucher_type',     VOUCHER_TYPE);
    body.append('reference',        ref);
    body.append('entry_date',       date);
    body.append('amount',           amount);
    body.append('debit_account_id', debitId);
    body.append('credit_account_id',creditId);
    body.append('subject',          document.getElementById('subjectField').value);
    body.append('payment_method',   method);
    body.append('sig_receiver',     document.getElementById('sigReceiver').value);
    body.append('sig_accountant',   document.getElementById('sigAccountant').value);
    body.append('sig_manager',      document.getElementById('sigManager').value);
    if(method==='cheque'){
        body.append('cheque_number',document.getElementById('chequeNumber').value);
        body.append('cheque_date',  document.getElementById('chequeDate').value);
    }
    if(IS_EDIT)body.append('_method','PUT');

    this.disabled=true;
    this.textContent='⏳ جاري الحفظ...';
    try{
        const res=await fetch(SAVE_URL,{method:'POST',body});
        const json=await res.json();
        if(json.success){
            alert(IS_EDIT?'✅ تم حفظ التعديلات بنجاح':'✅ تم حفظ القيد بنجاح');
            if(IS_EDIT)window.location.href='{{ route("journal.index") }}';
        }else{
            alert('❌ فشل: '+(json.message||'خطأ غير معروف'));
        }
    }catch(e){alert('⚠️ خطأ في الاتصال');}
    this.disabled=false;
    this.textContent=IS_EDIT?'💾 حفظ التعديلات':'💾 حفظ القيد';
});

// ══ تحميل PNG ══
document.getElementById('btnDl').addEventListener('click',function(){
    closeAllDropdowns();
    html2canvas(document.getElementById('paymentCanvas'),{scale:3,useCORS:true,backgroundColor:'#fff'}).then(c=>{
        const a=document.createElement('a');
        a.download=`ايصال_صرف_${document.getElementById('refNo').value||'new'}.png`;
        a.href=c.toDataURL('image/png',1.0);a.click();
    });
});

// ══ تهيئة أولية ══
document.getElementById('entryDate').dispatchEvent(new Event('change'));
updateAmountArabic();

// ══ وضع التعديل: pre-fill الحسابات وطريقة الدفع ══
@isset($isEdit)
    setPaymentMethod('{{ $detail->payment_method }}');

    @if($detail->payment_method === 'cheque')
        // حساب البنك (الدائن في الصرف)
        document.getElementById('creditAccountId').value = '{{ $detail->credit_account_id }}';
        document.getElementById('creditLabel').textContent = '{{ $detail->creditAccount->name }}';
        document.getElementById('bankWrap').style.display = 'block';
        document.getElementById('chequeNumber').style.display = 'block';
        document.getElementById('chequeDate').style.display   = 'block';
    @endif
@endisset
</script>
@endsection