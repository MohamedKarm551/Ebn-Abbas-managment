<x-app-layout>
<div style="max-width:600px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);
            font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;
                align-items:center;margin-bottom:24px;">
        <div>
            <h2 style="margin:0;">✏️ تعديل الحساب</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                {{ $account->code }} — {{ $account->name }}
            </p>
        </div>
        <a href="{{ route('accounts.index') }}"
           style="color:#6b7280;text-decoration:none;">← رجوع</a>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('accounts.update', $account) }}">
        @csrf @method('PUT')

        {{-- الكود (للعرض فقط) --}}
        <div style="background:#f8fafc;border-radius:8px;padding:12px;
                    margin-bottom:16px;display:flex;align-items:center;gap:12px;">
            <span style="font-size:24px;">📋</span>
            <div>
                <div style="font-size:12px;color:#6b7280;">كود الحساب (غير قابل للتعديل)</div>
                <div style="font-size:20px;font-weight:bold;color:#2563eb;">
                    {{ $account->code }}
                </div>
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-weight:bold;margin-bottom:4px;">
                اسم الحساب *
            </label>
            <input type="text" name="name"
                   value="{{ old('name', $account->name) }}" required
                   style="width:100%;padding:10px;border:1px solid #ddd;
                          border-radius:6px;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-weight:bold;margin-bottom:8px;">
                نوع الحساب في الشجرة
            </label>
            <div style="display:flex;gap:16px;">
                <label style="display:flex;align-items:center;gap:8px;
                               background:#f0fdf4;border:2px solid #86efac;
                               border-radius:8px;padding:12px 20px;cursor:pointer;flex:1;">
                    <input type="radio" name="account_kind" value="leaf"
                           {{ $account->is_leaf ? 'checked':'' }}>
                    <div>
                        <div style="font-weight:bold;">🍃 فرعي</div>
                        <div style="font-size:12px;color:#6b7280;">يقبل قيود</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:8px;
                               background:#eff6ff;border:2px solid #93c5fd;
                               border-radius:8px;padding:12px 20px;cursor:pointer;flex:1;">
                    <input type="radio" name="account_kind" value="parent"
                           {{ !$account->is_leaf ? 'checked':'' }}>
                    <div>
                        <div style="font-weight:bold;">📁 رئيسي</div>
                        <div style="font-size:12px;color:#6b7280;">يجمع فروع</div>
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
                             border-radius:6px;box-sizing:border-box;">{{ old('description', $account->description) }}</textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#f59e0b;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;flex:1;">
                💾 تحديث
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
</x-app-layout>