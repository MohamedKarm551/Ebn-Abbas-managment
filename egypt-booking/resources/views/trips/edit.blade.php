<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">
    <h2 style="margin-bottom:24px;">✏️ تعديل الرحلة: {{ $trip->name }}</h2>

    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:6px;margin-bottom:16px;">
            @foreach($errors->all() as $error) <div>• {{ $error }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('trips.update', $trip) }}">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">اسم الرحلة *</label>
                <input type="text" name="name" value="{{ old('name', $trip->name) }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">الفنادق</label>
                <input type="text" name="hotels" value="{{ old('hotels', $trip->hotels) }}"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
            <label>الوصف</label>
            <textarea name="description" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">{{ old('description', $trip->description) }}</textarea>
            </div>
            <div>
                <label>عدد المقاعد </label>
                <input type="number" name="total_seats" value="{{ old('total_seats', $trip->total_seats) }}" min="1"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">من (التاريخ) *</label>
                <input type="date" name="from" value="{{ old('from', $trip->from) }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">إلى (التاريخ) *</label>
                <input type="date" name="to" value="{{ old('to', $trip->to) }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
            </div>
        </div>

       <h4 style="color:#2563eb;">💰 أسعار الغرف</h4>
<div id="prices-container">
    @foreach($trip->prices as $index => $price)
    <div class="price-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
        <select name="prices[{{ $index }}][room_type]" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1;">
            <option value="">اختر نوع الغرفة</option>
            <option value="فردية" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'فردية' ? 'selected' : '' }}>فردية</option>
            <option value="ثنائية" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'ثنائية' ? 'selected' : '' }}>ثنائية</option>
            <option value="ثلاثية" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'ثلاثية' ? 'selected' : '' }}>ثلاثية</option>
            <option value="رباعية" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'رباعية' ? 'selected' : '' }}>رباعية</option>
            <option value="خماسية" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'خماسية' ? 'selected' : '' }}>خماسية</option>
            <option value="سداسية" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'سداسية' ? 'selected' : '' }}>سداسية</option>
            <option value="طفل" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'طفل' ? 'selected' : '' }}>طفل</option>
            <option value="رضيع" {{ old('prices.'.$index.'.room_type', $price->room_type) == 'رضيع' ? 'selected' : '' }}>رضيع</option>
        </select>
        <input type="number" name="prices[{{ $index }}][price]" 
               value="{{ old('prices.'.$index.'.price', $price->price) }}"
               placeholder="السعر"
               style="padding:8px;border:1px solid #ddd;border-radius:6px;width:120px;">
        <button type="button" onclick="this.parentElement.remove()"
                style="background:#ef4444;color:white;border:none;border-radius:6px;
                       padding:8px 12px;cursor:pointer;">🗑️</button>
    </div>
    @endforeach
</div>

        <button type="button" onclick="addRow()"
                style="background:#10b981;color:white;border:none;border-radius:6px;
                       padding:8px 16px;cursor:pointer;margin-bottom:20px;">
            ➕ إضافة سعر
        </button>


<h4 style="color:#2563eb;">📋 البنود الأساسية</h4>
<div id="default-items-container">
    @foreach($defaultItemsWithValues as $index => $item)
    <div class="default-item-row" style="display:flex; gap:10px; margin-bottom:10px;">
        <input type="text" name="default_items[{{ $index }}][name]" 
               value="{{ $item['name'] }}" readonly
               style="padding:8px; border:1px solid #ccc; border-radius:6px; flex:1; background:#f3f4f6;">
        <input type="text" name="default_items[{{ $index }}][value]" 
               value="{{ old("default_items.$index.value", $item['value']) }}"
               style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
    </div>
    @endforeach
</div>

<h4 style="color:#2563eb;">➕ بنود إضافية</h4>
<div id="additional-items-container">
    @foreach($additionalItems as $idx => $addItem)
    <div class="additional-item-row" style="display:flex; gap:10px; margin-bottom:10px;">
        <input type="text" name="additional_items[{{ $idx }}][name]" value="{{ $addItem->name }}"
               style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
        <input type="text" name="additional_items[{{ $idx }}][value]" value="{{ $addItem->value }}"
               style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
        <button type="button" onclick="removePriceRow(this)"
                style="background:#ef4444; color:white; border:none; border-radius:6px; padding:8px 12px; cursor:pointer;">🗑️</button>
    </div>
    @endforeach
</div>

<button type="button" onclick="addAdditionalItem()"
        style="background:#10b981; color:white; border:none; border-radius:6px; padding:8px 16px; cursor:pointer; margin-bottom:20px;">
    ➕ إضافة بند إضافي
</button>
        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#f59e0b;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;font-size:16px;flex:1;">
                💾 تحديث الرحلة
            </button>
            <a href="{{ route('trips.index') }}"
               style="background:#6b7280;color:white;padding:12px 30px;
                      border-radius:6px;text-decoration:none;text-align:center;font-size:16px;flex:1;">
                ❌ إلغاء
            </a>
        </div>
    </form>
</div>
</x-app-layout>

<script>
let rowIndex = {{ $trip->prices->count() }};
function addRow() {
    const container = document.getElementById('prices-container');
    container.insertAdjacentHTML('beforeend', `
    <div class="price-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
        <select name="prices[${rowIndex}][room_type]" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1;">
            <option value="">اختر نوع الغرفة</option>
            <option value="فردية">فردية</option>
            <option value="ثنائية">ثنائية</option>
            <option value="ثلاثية">ثلاثية</option>
            <option value="رباعية">رباعية</option>
            <option value="خماسية">خماسية</option>
            <option value="سداسية">سداسية</option>
            <option value="طفل">طفل</option>
            <option value="رضيع">رضيع</option>
        </select>
        <input type="number" name="prices[${rowIndex}][price]" placeholder="السعر"
               style="padding:8px;border:1px solid #ddd;border-radius:6px;width:120px;">
        <button type="button" onclick="removePriceRow(this)"
                style="background:#ef4444;color:white;border:none;border-radius:6px;
                       padding:8px 12px;cursor:pointer;">🗑️</button>
    </div>
    `);
    rowIndex++;
}


let additionalIndex = {{ $additionalItems->count() ?? 0 }};

function addAdditionalItem() {
    const container = document.getElementById('additional-items-container');
    container.insertAdjacentHTML('beforeend', `
        <div class="additional-item-row" style="display:flex; gap:10px; margin-bottom:10px;">
            <input type="text" name="additional_items[${additionalIndex}][name]" placeholder="اسم البند الإضافي"
                   style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
            <input type="text" name="additional_items[${additionalIndex}][value]" placeholder="القيمة"
                   style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
            <button type="button" onclick="this.parentElement.remove()"
                    style="background:#ef4444; color:white; border:none; border-radius:6px; padding:8px 12px; cursor:pointer;">🗑️</button>
        </div>
    `);
    additionalIndex++;
}

// دالة لتحديث خيارات أنواع الغرف (تعطيل المكررة)
function updateRoomTypeOptions() {
    const selects = document.querySelectorAll('select[name$="[room_type]"]');
    // جمع القيم المحددة حالياً (ما عدا القيم الفارغة)
    const selectedValues = [];
    selects.forEach(select => {
        if (select.value && select.value !== '') {
            selectedValues.push(select.value);
        }
    });

    // لكل select، نمر على خياراته ونعطيل الخيارات المكررة (ما عدا القيمة الحالية لهذا الـ select)
    selects.forEach(select => {
        const currentValue = select.value;
        Array.from(select.options).forEach(option => {
            if (option.value !== '' && selectedValues.includes(option.value) && option.value !== currentValue) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });
    });
}

// دالة مخصصة لإضافة صف سعر جديد
function addPriceRow() {
    const container = document.getElementById('prices-container');
    const newRow = document.createElement('div');
    newRow.className = 'price-row';
    newRow.style.display = 'flex';
    newRow.style.gap = '10px';
    newRow.style.marginBottom = '10px';
    newRow.style.alignItems = 'center';
    newRow.innerHTML = `
        <select name="prices[${rowIndex}][room_type]" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1;">
            <option value="">اختر نوع الغرفة</option>
            <option value="فردية">فردية</option>
            <option value="ثنائية">ثنائية</option>
            <option value="ثلاثية">ثلاثية</option>
            <option value="رباعية">رباعية</option>
            <option value="خماسية">خماسية</option>
            <option value="سداسية">سداسية</option>
            <option value="طفل">طفل</option>
            <option value="رضيع">رضيع</option>
        </select>
        <input type="number" name="prices[${rowIndex}][price]" placeholder="السعر"
               style="padding:8px;border:1px solid #ddd;border-radius:6px;width:120px;">
        <button type="button" onclick="removePriceRow(this)" style="background:#ef4444;color:white;border:none;border-radius:6px;padding:8px 12px;cursor:pointer;">🗑️</button>
    `;
    container.appendChild(newRow);
    rowIndex++;
    updateRoomTypeOptions(); // تحديث القوائم المنسدلة بعد إضافة الصف
}

// دالة لإزالة صف السعر
function removePriceRow(btn) {
    btn.closest('.price-row').remove();
    updateRoomTypeOptions(); // تحديث القوائم بعد الحذف
}

// ربط المراقبة على التغييرات وأحداث التحميل
document.addEventListener('DOMContentLoaded', function() {
    updateRoomTypeOptions();
    // استماع لتغيير أي select خاص بالغرف
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches('select[name$="[room_type]"]')) {
            updateRoomTypeOptions();
        }
    });
});

// تعديل دالة addRow الموجودة سابقاً لتصبح addPriceRow (أو تعديلها لاستخدام الدالة الجديدة)
// إذا أردت الاحتفاظ باسم addRow، استبدل محتواها باستدعاء addPriceRow
function addRow() {
    addPriceRow();
}


</script>