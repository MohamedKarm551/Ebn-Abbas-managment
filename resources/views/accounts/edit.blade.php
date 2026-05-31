{{-- resources/views/accounts/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'تعديل الحساب')
@section('content')
<style>
    .form-wrap { direction: rtl; font-family: 'Tajawal','Cairo',sans-serif; max-width: 700px; margin: 30px auto; padding: 0 20px; }
    .form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 28px; }
    .form-card h5 { font-size: 16px; font-weight: 700; margin-bottom: 22px; color: #111827; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-control {
        width: 100%; padding: 9px 12px; border: 1px solid #d1d5db;
        border-radius: 6px; font-size: 13.5px; color: #111827;
        font-family: inherit; box-sizing: border-box;
    }
    .form-control:focus { outline: none; border-color: #f59e0b; }
    .is-invalid { border-color: #ef4444 !important; }
    .invalid-feedback { color: #ef4444; font-size: 12px; margin-top: 4px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .btn-primary { background: #f59e0b; color: #fff; border: none; padding: 10px 24px; border-radius: 6px; font-size: 13.5px; font-weight: 600; cursor: pointer; }
    .btn-secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; padding: 10px 24px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; }
    .btn-danger { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 10px 24px; border-radius: 6px; font-size: 13.5px; font-weight: 600; cursor: pointer; }
    .form-actions { display: flex; gap: 10px; margin-top: 24px; align-items: center; }

    /* Freeze Toggle */
    .freeze-box {
        border: 2px solid #e5e7eb; border-radius: 10px; padding: 16px;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 18px;
    }
    .freeze-box.frozen { border-color: #fca5a5; background: #fef2f2; }
    .freeze-box.active { border-color: #86efac; background: #f0fdf4; }
    .freeze-info .title { font-size: 14px; font-weight: 700; }
    .freeze-info .desc  { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .freeze-toggle {
        position: relative; width: 52px; height: 28px; cursor: pointer;
    }
    .freeze-toggle input { opacity: 0; width: 0; height: 0; }
    .freeze-slider {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        border-radius: 28px; background: #d1d5db; transition: .3s;
    }
    .freeze-slider:before {
        content: ''; position: absolute; width: 22px; height: 22px;
        border-radius: 50%; background: #fff; top: 3px; left: 3px; transition: .3s;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .freeze-toggle input:checked + .freeze-slider { background: #22c55e; }
    .freeze-toggle input:checked + .freeze-slider:before { transform: translateX(24px); }

    .code-badge {
        background: #fef3c7; color: #92400e; padding: 4px 12px;
        border-radius: 20px; font-size: 13px; font-weight: 700;
        display: inline-block; margin-bottom: 8px;
    }
</style>

<div class="form-wrap">
    <div class="form-card">
        <h5>✏️ تعديل الحساب</h5>
        <div class="code-badge">{{ $account->code }}</div>

        <form action="{{ route('accounts.update', $account) }}" method="POST">
            @csrf @method('PUT')

            {{-- تجميد / تفعيل الحساب --}}
            <div class="freeze-box {{ $account->is_active ? 'active' : 'frozen' }}" id="freezeBox">
                <div class="freeze-info">
                    <div class="title" id="freezeTitle">
                        {{ $account->is_active ? '✅ الحساب نشط' : '🔒 الحساب مجمد' }}
                    </div>
                    <div class="desc">
                        {{ $account->is_active
                            ? 'يقبل القيود المحاسبية حالياً — أوقفه لتجميده'
                            : 'مجمد — لا يقبل أي قيد مدين أو دائن' }}
                    </div>
                </div>
                <label class="freeze-toggle">
                    <input type="checkbox" name="is_active" value="1"
                           {{ $account->is_active ? 'checked' : '' }}
                           onchange="updateFreezeUI(this)">
                    <span class="freeze-slider"></span>
                </label>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>اسم الحساب <span style="color:red">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $account->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>نوع الحساب <span style="color:red">*</span></label>
                    <select name="type" class="form-control">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $account->type) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>الحساب الأب</label>
                <select name="parent_id" class="form-control">
                    <option value="">-- حساب رئيسي --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}"
                                {{ old('parent_id', $account->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->code }} - {{ $parent->name }}
                            @if(!$parent->is_active) (مجمد) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
    <label>نوع الحساب</label>
    <select name="account_kind" class="form-control" {{ $account->children()->exists() ? 'disabled' : '' }}>
        <option value="parent" {{ $account->is_leaf ? '' : 'selected' }}>📂 حساب رئيسي (يحتوي على حسابات فرعية)</option>
        <option value="leaf" {{ $account->is_leaf ? 'selected' : '' }}>📄 حساب فرعي (نهائي - لا يقبل أبناء)</option>
    </select>
    @if($account->children()->exists())
        <small class="text-muted">⚠️ لا يمكن تغيير نوع هذا الحساب لأنه يحتوي على حسابات فرعية.</small>
    @endif
</div>

            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $account->description) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">حفظ التعديلات</button>
                <a href="{{ route('accounts.index') }}" class="btn-secondary">إلغاء</a>

                
            </div>
        </form>
    </div>
</div>

<script>
function updateFreezeUI(checkbox) {
    const box   = document.getElementById('freezeBox');
    const title = document.getElementById('freezeTitle');
    if (checkbox.checked) {
        box.className = 'freeze-box active';
        title.textContent = '✅ الحساب نشط';
    } else {
        box.className = 'freeze-box frozen';
        title.textContent = '🔒 الحساب مجمد';
    }
}
</script>
@endsection