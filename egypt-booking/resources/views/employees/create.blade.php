<x-app-layout>
<div style="max-width:500px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <h2 style="margin:0;">➕ إضافة موظف جديد</h2>
        <a href="{{ route('employees.index') }}"
           style="color:#6b7280;text-decoration:none;">← رجوع</a>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('employees.store') }}">
        @csrf

        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">
                👤 الاسم *
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   style="width:100%;padding:10px;border:1px solid #ddd;
                          border-radius:6px;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">
                📧 البريد الإلكتروني *
            </label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   style="width:100%;padding:10px;border:1px solid #ddd;
                          border-radius:6px;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">
                🔒 كلمة المرور *
            </label>
            <input type="password" name="password" required
                   style="width:100%;padding:10px;border:1px solid #ddd;
                          border-radius:6px;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">
                🔒 تأكيد كلمة المرور *
            </label>
            <input type="password" name="password_confirmation" required
                   style="width:100%;padding:10px;border:1px solid #ddd;
                          border-radius:6px;box-sizing:border-box;">
        </div>

      <div style="margin-bottom:20px;">
    <label style="display:block;margin-bottom:4px;font-weight:bold;">
        🔐 الصلاحية *
    </label>
    <select name="role" required
            style="width:100%;padding:10px;border:1px solid #ddd;
                   border-radius:6px;box-sizing:border-box;">
        {{-- نزال الخيار الفارغ لأننا سنحدد قيمة افتراضية --}}
        @foreach($roles as $role)
        <option value="{{ $role->name }}"
                {{ old('role', 'representative') == $role->name ? 'selected' : '' }}>
            {{ $role->name == 'admin' ? '👑 أدمن' : '👔 مندوب' }}
        </option>
        @endforeach
    </select>
</div>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#2563eb;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;
                       font-size:16px;flex:1;">
                💾 إضافة الموظف
            </button>
            <a href="{{ route('employees.index') }}"
               style="background:#6b7280;color:white;padding:12px 30px;
                      border-radius:6px;text-decoration:none;
                      text-align:center;font-size:16px;flex:1;">
                ❌ إلغاء
            </a>
        </div>
    </form>
</div>
</x-app-layout>