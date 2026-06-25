<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <div>
            <h2 style="margin:0;">✏️ تعديل الحجز #{{ $booking->id }}</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                {{ $booking->client_name }} — {{ $booking->trip->name }}
            </p>
        </div>
        
            <a href="{{ route('trips.bookings', $booking->trip) }}"
           style="background:#6b7280;color:white;padding:8px 16px;
                  border-radius:6px;text-decoration:none;">← رجوع</a>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST"
          action="{{ route('bookings.update', $booking->id) }}"
          enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- بيانات العميل --}}
        <h4 style="color:#2563eb;border-bottom:2px solid #e5e7eb;padding-bottom:8px;">
            👤 بيانات العميل
        </h4>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">الاسم *</label>
                <input type="text" name="client_name"
                       value="{{ old('client_name', $booking->client_name) }}" required
                       style="width:100%;padding:10px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">النوع *</label>
                <select name="gender" required
                        style="width:100%;padding:10px;border:1px solid #ddd;
                               border-radius:6px;box-sizing:border-box;">
                    @foreach(['male'=>'👨 ذكر','female'=>'👩 أنثى','child'=>'👦 طفل','infant'=>'👶 رضيع'] as $val=>$label)
                    <option value="{{ $val }}"
                        {{ old('gender',$booking->gender)==$val ? 'selected':'' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- نوع التسكين --}}
        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">
                نوع التسكين *
            </label>
            <select name="accommodation_type" required
                    style="width:100%;padding:10px;border:1px solid #ddd;
                           border-radius:6px;box-sizing:border-box;">
                @foreach($booking->trip->prices as $price)
                <option value="{{ $price->room_type }}"
                    {{ old('accommodation_type',$booking->accommodation_type)==$price->room_type ? 'selected':'' }}>
                    🛏️ {{ $price->room_type }} — {{ number_format($price->price,2) }} ج.م
                </option>
                @endforeach
            </select>
        </div>

        {{-- الصور --}}
        <h4 style="color:#2563eb;border-bottom:2px solid #e5e7eb;
                   padding-bottom:8px;margin-top:24px;">📷 الصور</h4>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">
                    صورة الجواز
                </label>
                @if($booking->passport_image)
                <div style="margin-bottom:8px;">
                    <img src="{{ asset('storage/'.$booking->passport_image) }}"
                         style="height:60px;border-radius:4px;border:1px solid #ddd;">
                    <div style="font-size:12px;color:#6b7280;">الصورة الحالية</div>
                </div>
                @endif
                <input type="file" name="passport_image" accept="image/*"
                       style="width:100%;padding:8px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;font-weight:bold;">
                    صورة شخصية
                </label>
                @if($booking->personal_photo)
                <div style="margin-bottom:8px;">
                    <img src="{{ asset('storage/'.$booking->personal_photo) }}"
                         style="height:60px;border-radius:4px;border:1px solid #ddd;">
                    <div style="font-size:12px;color:#6b7280;">الصورة الحالية</div>
                </div>
                @endif
                <input type="file" name="personal_photo" accept="image/*"
                       style="width:100%;padding:8px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
        </div>

        {{-- المندوب والملاحظات --}}
        <h4 style="color:#2563eb;border-bottom:2px solid #e5e7eb;
                   padding-bottom:8px;margin-top:24px;">📝 معلومات إضافية</h4>

       <div style="margin-bottom:16px;">
    <label style="display:block;margin-bottom:4px;font-weight:bold;">
        👔 اسم المندوب / موظف المبيعات
    </label>
    <select name="representative_id" required
            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;">
        <option value="" disabled {{ old('representative_id', $booking->representative_id) ? '' : 'selected' }}>
            -- اختر المندوب --
        </option>
        @foreach($representatives as $rep)
            <option value="{{ $rep->id }}"
                {{ old('representative_id', $booking->representative_id) == $rep->id ? 'selected' : '' }}>
                {{ $rep->name }}
            </option>
        @endforeach
    </select>
</div>

        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">ملاحظات</label>
            <textarea name="notes" rows="3"
                      style="width:100%;padding:10px;border:1px solid #ddd;
                             border-radius:6px;box-sizing:border-box;">
                {{ old('notes', $booking->notes) }}
            </textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#f59e0b;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;
                       font-size:16px;flex:1;">
                💾 تحديث الحجز
            </button>
            <a href="{{ route('trips.bookings', $booking->trip) }}"
               style="background:#6b7280;color:white;padding:12px 30px;
                      border-radius:6px;text-decoration:none;
                      text-align:center;font-size:16px;flex:1;">
                ❌ إلغاء
            </a>
        </div>
    </form>
</div>
</x-app-layout>