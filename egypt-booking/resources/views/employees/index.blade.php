<x-app-layout>
<div style="max-width:900px;margin:40px auto;padding:20px;font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="margin:0;">👔 الموظفين (المناديب)</h2>
        <a href="{{ route('employees.create') }}"
           style="background:#2563eb;color:white;padding:8px 16px;
                  border-radius:6px;text-decoration:none;">
            ➕ إضافة موظف
        </a>
    </div>

    @if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        {{ session('success') }}
    </div>
    @endif
<div style="background:white;padding:16px;border-radius:8px;
            box-shadow:0 2px 8px rgba(0,0,0,.1);margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">

        <input type="text" name="name"
               value="{{ request('name') }}"
               placeholder="🔍 ابحث بالاسم..."
               style="padding:8px 12px;border:1px solid #d1d5db;
                      border-radius:6px;font-family:Arial;font-size:14px;">

        <input type="text" name="email"
               value="{{ request('email') }}"
               placeholder="📧 ابحث بالبريد..."
               style="padding:8px 12px;border:1px solid #d1d5db;
                      border-radius:6px;font-family:Arial;font-size:14px;">

        <select name="role"
                style="padding:8px 12px;border:1px solid #d1d5db;
                       border-radius:6px;font-family:Arial;font-size:14px;color:#374151;">
            <option value="">👤 كل الصلاحيات</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role')==$role->name ? 'selected':'' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                style="background:#2563eb;color:white;padding:8px 18px;
                       border:none;border-radius:6px;cursor:pointer;font-size:14px;">
            🔍 بحث
        </button>
        <a href="{{ route('employees.index') }}"
           style="background:#6b7280;color:white;padding:8px 18px;
                  border-radius:6px;text-decoration:none;font-size:14px;">
            ✖ إلغاء
        </a>
    </form>
</div>
<div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;background:white;
                  border-radius:8px;overflow:hidden;
                  box-shadow:0 2px 8px rgba(0,0,0,.1);">
        <thead style="background:#2563eb;color:white;">
            <tr>
                <th style="padding:12px;">#</th>
                <th style="padding:12px;">الاسم</th>
                <th style="padding:12px;">البريد الإلكتروني</th>
                <th style="padding:12px;">الصلاحية</th>
                <th style="padding:12px;">تاريخ الإضافة</th>
                <th style="padding:12px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
             @php
                $roleName = $employee->roles->first()->name ?? '';
                $isAdmin = strtolower($roleName) === 'admin';
                $reportUrl = route('employees.report', $employee);
            @endphp
           <tr style="border-bottom:1px solid #f0f0f0;text-align:center;
                       background:{{ $loop->even ? '#f9fafb':'white' }};
                       {{ !$isAdmin ? 'cursor:pointer;' : '' }}"
                @if(!$isAdmin) onclick="window.location='{{ $reportUrl }}'" @endif>
                <td style="padding:12px;">{{ $loop->iteration }}</td>
                <td style="padding:12px;font-weight:bold;">{{ $employee->name }}</td>
                <td style="padding:12px;">{{ $employee->email }}</td>
                <td style="padding:12px;">{{ $employee->roles->first()->name }} </td>
                <td style="padding:12px;color:#6b7280;">
                    {{ $employee->created_at->format('Y/m/d') }}
                </td>
                <td style="padding:12px;">
                    <a href="{{ route('employees.edit', $employee) }}"
                       style="background:#f59e0b;color:white;padding:6px 12px;
                              border-radius:4px;text-decoration:none;margin-left:6px;">
                        ✏️ تعديل
                    </a>
                    <form method="POST"
                          action="{{ route('employees.destroy', $employee) }}"
                          style="display:inline;"
                          onsubmit="return confirm('هل أنت متأكد من حذف الموظف؟')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            style="background:#ef4444;color:white;padding:6px 12px;
                                   border:none;border-radius:4px;cursor:pointer;">
                            🗑️ حذف
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6"
                    style="padding:30px;text-align:center;color:#999;">
                    لا يوجد موظفين مضافين بعد
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
    <div style="margin-top:16px;">{{ $employees->links() }}</div>
</div>
</x-app-layout>