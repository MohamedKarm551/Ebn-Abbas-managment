<x-app-layout>
<div style="max-width:600px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);
            font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;
                align-items:center;margin-bottom:24px;">
        <h2 style="margin:0;">➕ إضافة حساب جديد</h2>
        <a href="{{ route('accounts.index') }}"
           style="color:#6b7280;text-decoration:none;">← رجوع</a>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('accounts.store') }}">
        @csrf

        <div style="margin-bottom:16px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">
                📂 الحساب الأب
            </label>
            <select name="parent_id" id="parentSelect"
                    style="width:100%;padding:10px;border:1px solid #ddd;
                           border-radius:6px;box-sizing:border-box;"
                    onchange="suggestType()">
                <option value="">-- بدون أب (حساب رئيسي) --</option>
                @foreach($parents as $p)
                <option value="{{ $p->id }}"
                        data-type="{{ $p->type }}"
                        {{ old('parent_id') == $p->id ? 'selected':'' }}>
                    {{ $p->code }} — {{ $p->name }}
                </option>
                @endforeach
            </select>
            <small style="color:#6b7280;">
                💡 الكود سيُولَّد تلقائياً بناءً على الأب
            </small>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">
                اسم الحساب *
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   style="width:100%;padding:10px;border:1px solid #ddd;
                          border-radius:6px;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">
                نوع الحساب *
            </label>
            <select name="type" id="typeSelect"
                    style="width:100%;padding:10px;border:1px solid #ddd;
                           border-radius:6px;box-sizing:border-box;">
                <option value="asset"     {{ old('type')=='asset'     ?'selected':'' }}>أصول</option>
                <option value="liability" {{ old('type')=='liability' ?'selected':'' }}>خصوم</option>
                <option value="equity"    {{ old('type')=='equity'    ?'selected':'' }}>حقوق ملكية</option>
                <option value="revenue"   {{ old('type')=='revenue'   ?'selected':'' }}>إيرادات</option>
                <option value="expense"   {{ old('type')=='expense'   ?'selected':'' }}>مصروفات</option>
            </select>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-weight:bold;margin-bottom:8px;">
                نوع الحساب في الشجرة *
            </label>
            <div style="display:flex;gap:16px;">
                <label style="display:flex;align-items:center;gap:8px;
                               background:#f0fdf4;border:2px solid #86efac;
                               border-radius:8px;padding:12px 20px;cursor:pointer;flex:1;">
                    <input type="radio" name="account_kind" value="leaf"
                           {{ old('account_kind','leaf')=='leaf'?'checked':'' }}>
                    <div>
                        <div style="font-weight:bold;">🍃 فرعي (نهائي)</div>
                        <div style="font-size:12px;color:#6b7280;">
                            يقبل قيود مباشرة
                        </div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:8px;
                               background:#eff6ff;border:2px solid #93c5fd;
                               border-radius:8px;padding:12px 20px;cursor:pointer;flex:1;">
                    <input type="radio" name="account_kind" value="parent"
                           {{ old('account_kind')=='parent'?'checked':'' }}>
                    <div>
                        <div style="font-weight:bold;">📁 رئيسي (مجمّع)</div>
                        <div style="font-size:12px;color:#6b7280;">
                            يجمع أرصدة الفروع
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">
                ملاحظات
            </label>
            <textarea name="description" rows="2"
                      style="width:100%;padding:10px;border:1px solid #ddd;
                             border-radius:6px;box-sizing:border-box;">{{ old('description') }}</textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#2563eb;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;flex:1;">
                💾 إنشاء الحساب
            </button>
            <a href="{{ route('accounts.index') }}"
               style="background:#6b7280;color:white;padding:12px 30px;
                      border-radius:6px;text-decoration:none;
                      text-align:center;flex:1;">
                ❌ إلغاء
            </a>
        </div>
    </form>
</div>

<script>
function suggestType() {
    const sel = document.getElementById('parentSelect');
    const opt = sel.options[sel.selectedIndex];
    const type = opt.dataset.type;
    if (type) {
        document.getElementById('typeSelect').value = type;
    }
}
</script>
</x-app-layout>