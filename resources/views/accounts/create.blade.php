{{-- resources/views/accounts/create.blade.php --}}
@extends('layouts.app')
@section('title', 'إضافة حساب جديد')
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
        font-family: inherit; box-sizing: border-box; transition: border-color .2s;
    }
    .form-control:focus { outline: none; border-color: #f59e0b; box-shadow: 0 0 0 3px #fef3c733; }
    .is-invalid { border-color: #ef4444 !important; }
    .invalid-feedback { color: #ef4444; font-size: 12px; margin-top: 4px; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .btn-primary {
        background: #f59e0b; color: #fff; border: none;
        padding: 10px 24px; border-radius: 6px; font-size: 13.5px;
        font-weight: 600; cursor: pointer;
    }
    .btn-secondary {
        background: #fff; color: #374151; border: 1px solid #d1d5db;
        padding: 10px 24px; border-radius: 6px; font-size: 13.5px;
        font-weight: 600; text-decoration: none;
    }
    .form-actions { display: flex; gap: 10px; margin-top: 24px; }
    .required { color: #ef4444; }
    .code-preview {
        background: #f0fdf4; border: 1px solid #86efac;
        border-radius: 6px; padding: 10px 14px; font-size: 13px;
        color: #166534; font-weight: 700; margin-top: 8px;
        display: none;
    }
    .info-box {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 6px; padding: 10px 14px; font-size: 12.5px;
        color: #1e40af; margin-bottom: 16px;
    }
    .kind-options { display: flex; gap: 12px; margin-top: 6px; }
    .kind-option {
        flex: 1; border: 2px solid #e5e7eb; border-radius: 8px;
        padding: 12px; cursor: pointer; text-align: center; transition: all .2s;
    }
    .kind-option:hover { border-color: #f59e0b; background: #fffbeb; }
    .kind-option input[type=radio] { display: none; }
    .kind-option.selected { border-color: #f59e0b; background: #fffbeb; }
    .kind-option .icon { font-size: 22px; margin-bottom: 4px; }
    .kind-option .label { font-size: 13px; font-weight: 600; color: #374151; }
    .kind-option .desc { font-size: 11px; color: #6b7280; margin-top: 3px; }
</style>

<div class="form-wrap">
    <div class="form-card">
        <h5>➕ إضافة حساب جديد</h5>

        <div class="info-box">
            💡 الكود سيُولَّد تلقائياً بناءً على الحساب الأب الذي تختاره
        </div>

        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf

            <div class="grid-2">
                {{-- اسم الحساب --}}
                <div class="form-group">
                    <label>اسم الحساب <span class="required">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- نوع الحساب --}}
                <div class="form-group">
                    <label>نوع الحساب <span class="required">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="">-- اختر النوع --</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- الحساب الأب --}}
            <div class="form-group">
                <label>الحساب الأب (اختياري — اتركه فارغاً لإنشاء حساب رئيسي)</label>
                <select name="parent_id" class="form-control" id="parentSelect" onchange="previewCode(this.value)">
                    <option value="">-- بدون أب (حساب رئيسي) --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}"
                                data-leaf="{{ $parent->is_leaf ? '1' : '0' }}"
                                data-active="{{ $parent->is_active ? '1' : '0' }}"
                                {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->code }} - {{ $parent->name }}
                            @if(!$parent->is_active) (مجمد) @endif
                        </option>
                    @endforeach
                </select>
                @error('parent_id') <div class="invalid-feedback" style="display:block">{{ $message }}</div> @enderror
                <div class="code-preview" id="codePreview">
                    🔢 الكود المتوقع: <span id="previewCode">—</span>
                </div>
            </div>

            {{-- نوع الحساب: رئيسي أم فرعي --}}
            <div class="form-group">
                <label>هل الحساب رئيسي أم فرعي؟ <span class="required">*</span></label>
                <div class="kind-options">
                    <label class="kind-option {{ old('account_kind', 'leaf') === 'parent' ? 'selected' : '' }}"
                           id="kindParent" onclick="selectKind('parent')">
                        <input type="radio" name="account_kind" value="parent"
                               {{ old('account_kind') === 'parent' ? 'checked' : '' }}>
                        <div class="icon">📁</div>
                        <div class="label">رئيسي (مجمّع)</div>
                        <div class="desc">يحتوي على حسابات فرعية — لا يقبل قيوداً مباشرة</div>
                    </label>
                    <label class="kind-option {{ old('account_kind', 'leaf') !== 'parent' ? 'selected' : '' }}"
                           id="kindLeaf" onclick="selectKind('leaf')">
                        <input type="radio" name="account_kind" value="leaf"
                               {{ old('account_kind', 'leaf') !== 'parent' ? 'checked' : '' }}>
                        <div class="icon">📄</div>
                        <div class="label">فرعي (نهائي)</div>
                        <div class="desc">يقبل القيود المحاسبية مباشرةً — لا يحتوي على أبناء</div>
                    </label>
                </div>
                @error('account_kind') <div class="invalid-feedback" style="display:block">{{ $message }}</div> @enderror
            </div>

            {{-- الوصف --}}
            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" class="form-control" rows="2"
                          placeholder="وصف اختياري">{{ old('description') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">حفظ الحساب</button>
                <a href="{{ route('accounts.index') }}" class="btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<script>
// معاينة الكود التلقائي عبر AJAX
function previewCode(parentId) {
    const preview = document.getElementById('codePreview');
    const previewText = document.getElementById('previewCode');

    if (!parentId) {
        preview.style.display = 'none';
        return;
    }

    fetch(`/accounts/preview-code?parent_id=${parentId}`)
        .then(r => r.json())
        .then(data => {
            previewText.textContent = data.code;
            preview.style.display = 'block';
        })
        .catch(() => { preview.style.display = 'none'; });
}

function selectKind(kind) {
    document.getElementById('kindParent').classList.toggle('selected', kind === 'parent');
    document.getElementById('kindLeaf').classList.toggle('selected', kind === 'leaf');
    document.querySelector(`input[value="${kind}"]`).checked = true;
}

// تفعيل المعاينة لو فيه قيمة قديمة
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('parentSelect');
    if (sel.value) previewCode(sel.value);
});
</script>
@endsection