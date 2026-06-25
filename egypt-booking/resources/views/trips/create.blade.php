<x-app-layout >
<div  style="max-width:1100px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">
    <h2 style="margin-bottom:24px;">🗺️ إضافة رحلة جديدة</h2>

    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:6px;margin-bottom:16px;">
            @foreach($errors->all() as $error) <div>• {{ $error }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('trips.store') }}">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">اسم الرحلة *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">الفنادق</label>
                <input type="text" name="hotels" value="{{ old('hotels') }}"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">الوصف</label>
                <textarea name="description" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">{{ old('description') }}</textarea>
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">عدد المقاعد </label>
                <input type="number" name="total_seats" value="{{ old('total_seats', 45) }}" min="1"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
            <div>
    <label style="display:block;margin-bottom:4px;font-weight:bold;">من *</label>
    <div style="display:flex;gap:10px;align-items:center;">
        <input type="date" name="from" id="from_date" value="{{ old('from') }}" required
            onkeydown="return false"
               style="width:70%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f3f4f6;">
        <input type="text" id="from_hijri" readonly
               placeholder="التاريخ الهجري"
               style="width:50%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f9fafb;">
    </div>
</div>
           <div>
    <label style="display:block;margin-bottom:4px;font-weight:bold;">إلى *</label>
    <div style="display:flex;gap:10px;align-items:center;">
        <input type="date" name="to" id="to_date" value="{{ old('to') }}" required
               onkeydown="return false"
               style="width:70%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f3f4f6;">
        <input type="text" id="to_hijri" readonly
               placeholder="التاريخ الهجري"
               style="width:50%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f9fafb;">
    </div>
</div>
        </div>

        <h4 style="color:#2563eb;">💰 سعر الرحلة</h4>
        <div id="prices-container">
            <div class="price-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
                <select name="prices[0][room_type]" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1;">
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
                <input type="number" name="prices[0][price]" placeholder="السعر"
                       style="padding:8px;border:1px solid #ddd;border-radius:6px;width:120px;">
                <button type="button" onclick="removePriceRow(this)"
                        style="background:#ef4444;color:white;border:none;border-radius:6px;
                               padding:8px 12px;cursor:pointer;">🗑️</button>
            </div>
        </div>

        <button type="button" onclick="addRow()"
                style="background:#10b981;color:white;border:none;border-radius:6px;
                       padding:8px 16px;cursor:pointer;margin-bottom:20px;">
            ➕ إضافة سعر
        </button>


       <h3 style="margin:24px 0 12px 0; font-size:1.25rem; font-weight:600;">
     البنود
</h3>
        <h4 style="color:#2563eb;">📋 البنود الأساسية (إلزامية)</h4>
        <div id="default-items-container">
            @foreach($defaultItems as $index => $itemName)
            <div class="default-item-row" style="display:flex; gap:10px; margin-bottom:10px;">
                <input type="text" name="default_items[{{ $index }}][name]" 
                       value="{{ $itemName }}" readonly
                       style="padding:8px; border:1px solid #ccc; border-radius:6px; flex:1; background:#f3f4f6;">
                <input type="text" name="default_items[{{ $index }}][value]" 
                       placeholder="القيمة (مثال: 50 جنية أو متضمن)"
                       value="{{ old("default_items.$index.value") }}"
                       style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
                <!-- لا يوجد زر حذف هنا، هذه البنود ثابتة -->
            </div>
            @endforeach
        </div>

        <h4 style="color:#2563eb; margin-top:24px;">➕ بنود إضافية (اختيارية)</h4>
        <div id="additional-items-container">
            <div class="additional-item-row" style="display:flex; gap:10px; margin-bottom:10px;">
                <input type="text" name="additional_items[0][name]" placeholder="اسم البند الإضافي"
                       style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
                <input type="text" name="additional_items[0][value]" placeholder="القيمة"
                       style="padding:8px; border:1px solid #ddd; border-radius:6px; flex:1;">
                <button type="button" onclick="this.parentElement.remove()"
                        style="background:#ef4444; color:white; border:none; border-radius:6px; padding:8px 12px; cursor:pointer;">🗑️</button>
            </div>
        </div>

        <button type="button" onclick="addAdditionalItem()"
                style="background:#10b981; color:white; border:none; border-radius:6px; padding:8px 16px; cursor:pointer; margin-bottom:20px;">
            ➕ إضافة بند إضافي
        </button>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#2563eb;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;font-size:16px;flex:1;">
                💾 حفظ الرحلة
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

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.1.0/moment-hijri.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/locale/ar-sa.js"></script>
<script>
let rowIndex = 1;
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

let additionalIndex = 1;
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

    moment.locale('ar-sa');
     function convertToHijri(dateInputId, outputId) {
        const dateInput = document.getElementById(dateInputId);
        const output = document.getElementById(outputId);
        if (!dateInput || !output) return;
        
        function updateHijri() {
            if (dateInput.value && moment(dateInput.value, 'YYYY-MM-DD', true).isValid()) {
                // استخدم التنسيق الذي تفضله:
                // الأرقام فقط: 'iD/iM/iYYYY'
                // بأسماء الأشهر العربية: 'iD iMMMM iYYYY'
                const hijri = moment(dateInput.value).format('iD iMMMM iYYYY');
                output.value = hijri;
            } else {
                output.value = '';
            }
        }
        
        dateInput.addEventListener('change', updateHijri);
        updateHijri(); // للتشغيل الأولي
    }
    
    convertToHijri('from_date', 'from_hijri');
    convertToHijri('to_date', 'to_hijri');


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