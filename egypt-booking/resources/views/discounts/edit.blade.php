<x-app-layout>
<div style="max-width:800px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <div>
            <h2 style="margin:0;">✏️ تعديل الخصم #{{ $discount->id }}</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                المبلغ: {{ number_format($discount->amount, 2) }} ج.م
            </p>
        </div>
        <a href="javascript:history.back()"
           style="background:#6b7280;color:white;padding:8px 16px;
                  border-radius:6px;text-decoration:none;">← رجوع</a>
    </div>

    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('discounts.update', $discount) }}">
        @csrf @method('PUT')

        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">💰 المبلغ *</label>
            <input type="number" name="amount"
                   value="{{ old('amount', $discount->amount) }}"
                   step="0.01" required
                   style="width:100%;padding:10px;border:1px solid #ddd;
                          border-radius:6px;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:4px;font-weight:bold;">📝 السبب</label>
            <textarea name="description" rows="3"
                      style="width:100%;padding:10px;border:1px solid #ddd;
                             border-radius:6px;box-sizing:border-box;">{{ old('description', $discount->description) }}</textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit"
                style="background:#f59e0b;color:white;padding:12px 30px;
                       border:none;border-radius:6px;cursor:pointer;
                       font-size:16px;flex:1;">
                💾 تحديث الخصم
            </button>
            <a href="javascript:history.back()"
               style="background:#6b7280;color:white;padding:12px 30px;
                      border-radius:6px;text-decoration:none;
                      text-align:center;font-size:16px;flex:1;">
                ❌ إلغاء
            </a>
        </div>
    </form>
</div>
</x-app-layout>