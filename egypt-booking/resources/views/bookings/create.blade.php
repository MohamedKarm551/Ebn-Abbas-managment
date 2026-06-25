<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">

    <h2 style="margin-bottom:4px;">📋 حجز جديد</h2>
    <p style="color:#6b7280;margin-bottom:24px;">رحلة: <strong>{{ $trip->name }}</strong>
       ({{ $trip->from }} ← {{ $trip->to }})</p>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:6px;margin-bottom:16px;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST"
          action="{{ route('bookings.store', $trip) }}"
          enctype="multipart/form-data">
        @csrf

        {{-- بيانات العميل --}}
        <h4 style="color:#2563eb;border-bottom:2px solid #e5e7eb;padding-bottom:8px;">
            👤 بيانات العميل
        </h4>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">اسم العميل *</label>
                <input type="text" name="client_name" value="{{ old('client_name') }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">النوع *</label>
                <select name="gender" required
                        style="width:100%;padding:10px;border:1px solid #ddd;
                               border-radius:6px;box-sizing:border-box;">
                    <option value="">-- اختر --</option>
                    <option value="male"   {{ old('gender')=='male'   ?'selected':'' }}>👨 ذكر</option>
                    <option value="female" {{ old('gender')=='female' ?'selected':'' }}>👩 أنثى</option>
                    <option value="child"  {{ old('gender')=='child'  ?'selected':'' }}>👦 طفل</option>
                    <option value="infant" {{ old('gender')=='infant' ?'selected':'' }}>👶 رضيع</option>
                </select>
            </div>
        </div>

        {{-- نوع التسكين --}}
<div style="margin-bottom:16px;">
    <label style="display:block;margin-bottom:4px;font-weight:bold;">نوع التسكين *</label>
    <select name="accommodation_type" id="accommodation_type" required
            onchange="updatePrice(this)"
            style="width:100%;padding:10px;border:1px solid #ddd;
                   border-radius:6px;box-sizing:border-box;">
        <option value="">-- اختر نوع الغرفة --</option>
        @foreach($trip->prices as $price)
            <option value="{{ $price->room_type }}"
                    data-price="{{ $price->price }}"
                    {{ old('accommodation_type') == $price->room_type ? 'selected' : '' }}>
                🛏️ {{ $price->room_type }} — {{ number_format($price->price,2) }} ج.م
            </option>
        @endforeach
    </select>
</div>

        {{-- عرض السعر الأساسي --}}
        <div id="price-display"
             style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;
                    padding:12px;margin-bottom:16px;display:none;">
            💰 السعر الأساسي:
            <strong id="base-price-text" style="color:#059669;font-size:18px;"></strong>
        </div>

       {{-- أول دفعة --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">
                    💳 قيمة أول دفعة *
                </label>
                <input type="number" name="first_payment"
                       value="{{ old('first_payment', 0) }}"
                       min="0" step="0.01" required
                       style="width:100%;padding:10px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">
                    🧾 صورة إيصال أول دفعة
                </label>
                <input type="file" name="first_payment_receipt" accept="image/*" required
                       style="width:100%;padding:8px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
        </div>

        {{-- الصور --}}
        <h4 style="color:#2563eb;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-top:24px;">
            📷 الصور
        </h4>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">
                    صورة الجواز
                </label>
                <input type="file" name="passport_image" accept="image/*" required
                       style="width:100%;padding:8px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">
                    صورة شخصية
                </label>
                <input type="file" name="personal_photo" accept="image/*" required
                       style="width:100%;padding:8px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
        </div>

        {{-- المندوب والملاحظات --}}
        <h4 style="color:#2563eb;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-top:24px;">
            📝 معلومات إضافية
        </h4>
        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">
                👔 اسم المندوب / موظف المبيعات
            </label>
          @auth
            @if(auth()->user()->hasRole('admin'))
                <select name="representative_id" required
                        style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
                    <option value="" disabled {{ old('representative_id') ? '' : 'selected' }}>-- اختر المندوب --</option>
                    @foreach($representatives as $rep)
                        <option value="{{ $rep->id }}" {{ old('representative_id') == $rep->id ? 'selected' : '' }}>
                            {{ $rep->name }}
                        </option>
                    @endforeach
                </select>
            @else
                {{-- المندوب العادي: يظهر اسمه فقط مع حقل مخفي --}}
                <input type="hidden" name="representative_id" value="{{ auth()->user()->id }}">
                <div style="padding:10px; background:#f3f4f6; border-radius:6px; border:1px solid #ddd;">
                    {{ auth()->user()->name }}
                </div>
            @endif
        @endauth
        </div>






        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">ملاحظات</label>
            <textarea name="notes" rows="3"
                      style="width:100%;padding:10px;border:1px solid #ddd;
                             border-radius:6px;box-sizing:border-box;">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#2563eb;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;font-size:16px;flex:1;">
                💾 تأكيد الحجز
            </button>
            <a href="{{ route('trips.show', $trip) }}"
               style="background:#6b7280;color:white;padding:12px 30px;border-radius:6px;
                      text-decoration:none;text-align:center;font-size:16px;flex:1;">
                ❌ إلغاء
            </a>
        </div>
    </form>
</div>

<script>
function updatePrice(select) {
    const opt = select.options[select.selectedIndex];
    const price = opt.dataset.price;
    const display = document.getElementById('price-display');
    const text = document.getElementById('base-price-text');
    if (price) {
        text.textContent = parseFloat(price).toLocaleString('ar-EG') + ' ج.م';
        display.style.display = 'block';
    } else {
        display.style.display = 'none';
    }
}
</script>
</x-app-layout>