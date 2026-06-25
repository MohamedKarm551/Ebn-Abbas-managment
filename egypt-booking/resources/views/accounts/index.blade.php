<x-app-layout>
<div style="max-width:80%;margin:40px auto;padding:20px;
            font-family:Arial;" dir="rtl">

    <h2 style="margin-bottom:20px;">📊 شجرة الحسابات</h2>
<div style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
    {{-- أزرار الطي والتوسع --}}
    <div style="display:flex; gap:10px;">
        <button onclick="expandAll()" style="background:#f3f4f6; border:1px solid #d1d5db; padding:5px 12px; border-radius:6px; cursor:pointer;">📂 توسيع الكل</button>
        <button onclick="collapseAll()" style="background:#f3f4f6; border:1px solid #d1d5db; padding:5px 12px; border-radius:6px; cursor:pointer;">📁 طي الكل</button>
    </div>

    <div style="display:flex; gap:8px; align-items:center;">

        {{-- PDF --}}
        <a href="{{ route('accounts.export', ['type'=>'pdf']) }}" target="_blank"
           style="background:#dc2626;color:white;padding:6px 14px;border-radius:6px;text-decoration:none;font-size:13px;">
            📄 PDF
        </a>

        {{-- Excel --}}
        <a href="{{ route('accounts.export', ['type'=>'excel']) }}"
           style="background:#059669;color:white;padding:6px 14px;border-radius:6px;text-decoration:none;font-size:13px;">
            📊 Excel
        </a>
    </div>
    <div>
        {{-- إضافة حساب --}}
        <a href="{{ route('accounts.create') }}"
           style="background:#2563eb;color:white;padding:6px 16px;border-radius:6px;text-decoration:none;font-weight:600;font-size:13px;">
            ➕ إضافة حساب
        </a>
    </div>
</div>

    


{{-- Search Bar --}}
<div style="
    background:white;
    padding:16px;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,.1);
    margin-bottom:20px;
">
    <form method="GET"
          action="{{ route('accounts.index') }}"
          style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="🔍 بحث باسم الحساب أو رقم الكود..."
               style="
                    flex:1;
                    min-width:250px;
                    padding:10px 12px;
                    border:1px solid #d1d5db;
                    border-radius:6px;
                    font-family:Arial;
               ">

        <button type="submit"
                style="
                    background:#2563eb;
                    color:white;
                    border:none;
                    padding:10px 18px;
                    border-radius:6px;
                    cursor:pointer;
                ">
            🔍 بحث
        </button>

        @if(request('search'))
            <a href="{{ route('accounts.index') }}"
               style="
                    background:#6b7280;
                    color:white;
                    padding:10px 18px;
                    border-radius:6px;
                    text-decoration:none;
               ">
                ✖ إلغاء
            </a>
        @endif

    </form>
</div>

    <table style="width:100%;border-collapse:collapse;background:white;
                  border-radius:8px;overflow:hidden;
                  box-shadow:0 2px 8px rgba(0,0,0,.1);">
        <thead style="background:#1d4ed8;color:white;">
            <tr>
                <th style="padding:12px;">الكود</th>
                <th style="padding:12px;">اسم الحساب</th>
                <th style="padding:12px;">النوع</th>
                <th style="padding:12px;">الرصيد</th>
                <th style="padding:12px;">الاجراءت</th>
            </tr>
        </thead>
        <tbody id="accountsTree">
            @foreach($accounts as $account)
                @include('accounts._node', ['account'=>$account,'level'=>0,'isSearching' => $isSearching ?? false])
            @endforeach
        </tbody>
    </table>
</div>
<script>
function toggleChildren(code) {
    const btn = document.getElementById('toggle-' + code);
    if (!btn) return;
    const isOpen = btn.textContent.trim() === '▼';

    if (isOpen) {
        hideAllDescendants(code);
        btn.textContent = '▶';
    } else {
        // إظهار الأبناء المباشرين فقط
        const directChildren = document.querySelectorAll('#accountsTree tr.children-of-' + code);
        directChildren.forEach(row => row.style.display = '');
        btn.textContent = '▼';
    }
}

function hideAllDescendants(code) {
    const directChildren = document.querySelectorAll('#accountsTree tr.children-of-' + code);
    directChildren.forEach(row => {
        row.style.display = 'none';
        const childCode = row.getAttribute('data-code');
        if (childCode) {
            hideAllDescendants(childCode);
            const childBtn = document.getElementById('toggle-' + childCode);
            if (childBtn) childBtn.textContent = '▶';
        }
    });
}

function expandAll() {
    document.querySelectorAll('#accountsTree tr').forEach(row => row.style.display = '');
    document.querySelectorAll('.toggle-btn').forEach(btn => btn.textContent = '▼');
}

function collapseAll() {
    document.querySelectorAll('#accountsTree tr[class*="children-of-"]').forEach(row => row.style.display = 'none');
    document.querySelectorAll('.toggle-btn').forEach(btn => btn.textContent = '▶');
}
</script>
</x-app-layout>