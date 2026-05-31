@extends('layouts.app')
@section('title', 'إدارة الإتاحات')
@section('content')
<style>
    /* نفس استايلات journal.index تقريباً */
    .search-section {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 20px;
    }
    .search-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }
    .search-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1;
        min-width: 180px;
    }
    .search-group label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }
    .search-group select,
    .search-group input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
    }
    .btn-search {
        background: #111827;
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
    }
    .btn-reset {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
    }
    .active-filter {
        background: #fef3c7;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        display: inline-block;
        margin: 12px 20px 0 20px;
    }
    .badge {
        font-size: 12px;
        padding: 4px 10px;
    }
    .page-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }




.search-section{
    background: #fff;
    padding: 22px;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    border: 1px solid #eee;
}

.search-form{
    width: 100%;
}

.search-row{
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    align-items: end;
}

.second-row{
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px dashed #ddd;
}

.search-group{
    flex: 1;
    min-width: 180px;
    display: flex;
    flex-direction: column;
}

.search-group.small{
    flex: .8;
}

.search-group.large{
    flex: 1.4;
}

.search-group label{
    margin-bottom: 7px;
    font-size: 14px;
    font-weight: 600;
    color: #444;
}

.search-group input,
.search-group select{
    height: 46px;
    border: 1px solid #dcdcdc;
    border-radius: 12px;
    padding: 0 14px;
    font-size: 14px;
    transition: .2s ease;
    background: #fafafa;
}

.search-group input:focus,
.search-group select:focus{
    border-color: #4f46e5;
    background: #fff;
    outline: none;
    box-shadow: 0 0 0 4px rgba(79,70,229,.12);
}

.search-actions{
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-search,
.btn-reset{
    height: 46px;
    padding: 0 18px;
    border-radius: 12px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-search{
    background: #4f46e5;
    color: #fff;
}

.btn-search:hover{
    background: #4338ca;
    transform: translateY(-1px);
}

.btn-reset{
    background: #f3f4f6;
    color: #444;
}

.btn-reset:hover{
    background: #e5e7eb;
}

@media(max-width: 768px){

    .search-row{
        flex-direction: column;
    }

    .search-group,
    .search-group.small,
    .search-group.large{
        width: 100%;
    }

    .search-actions{
        width: 100%;
    }

    .btn-search,
    .btn-reset{
        flex: 1;
    }
}

</style>

<div class="container mt-4">
    <div class="page-toolbar">
        <h5>📋 قائمة الإتاحات</h5>
        <a href="{{ route('admin.availabilities.create') }}" class="btn btn-primary">+ إضافة إتاحة جديدة</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success m-3">{{ session('success') }}</div>
    @endif

    {{-- شريط البحث والفلترة --}}
    <div class="search-section">
    <form method="GET" action="{{ route('admin.availabilities.index') }}" class="search-form">

        {{-- الصف الأول --}}
        <div class="search-row">

            <div class="search-group small">
                <label>🔍 بحث بـ</label>
                <select name="search_by">
                    <option value="">-- اختر معيار البحث --</option>
                    <option value="hotel" {{ request('search_by') == 'hotel' ? 'selected' : '' }}>🏨 الفندق</option>
                    <option value="agent" {{ request('search_by') == 'agent' ? 'selected' : '' }}>🤝 جهة الحجز</option>
                    <option value="status" {{ request('search_by') == 'status' ? 'selected' : '' }}>🏷️ الحالة</option>
                    <option value="employee" {{ request('search_by') == 'employee' ? 'selected' : '' }}>👤 الموظف</option>
                </select>
            </div>

            <div class="search-group large">
                <label>✏️ القيمة</label>
                <input type="text"
                       name="search_value"
                       value="{{ request('search_value') }}"
                       placeholder="أدخل قيمة البحث...">
            </div>

            <div class="search-group">
                <label>📅 من تاريخ الإنشاء</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="search-group">
                <label>📅 إلى تاريخ الإنشاء</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="search-actions">
                <button type="submit" class="btn-search">
                    🔍 بحث
                </button>

                <a href="{{ route('admin.availabilities.index') }}" class="btn-reset">
                    🗑️ مسح
                </a>
            </div>
        </div>

        {{-- الصف الثاني --}}
        <div class="search-row second-row">

            <div class="search-group">
                <label>📅 تاريخ البدء من</label>
                <input type="date" name="start_date_from" value="{{ request('start_date_from') }}">
            </div>

            <div class="search-group">
                <label>📅 تاريخ البدء إلى</label>
                <input type="date" name="start_date_to" value="{{ request('start_date_to') }}">
            </div>

            <div class="search-group">
                <label>📅 تاريخ الانتهاء من</label>
                <input type="date" name="end_date_from" value="{{ request('end_date_from') }}">
            </div>

            <div class="search-group">
                <label>📅 تاريخ الانتهاء إلى</label>
                <input type="date" name="end_date_to" value="{{ request('end_date_to') }}">
            </div>

        </div>

    </form>
    </div>

    {{-- عرض الفلاتر النشطة --}}
    @php
        $filters = [];
        if(request('search_by') && request('search_value')) {
            $searchByText = match(request('search_by')) {
                'hotel' => 'الفندق',
                'agent' => 'جهة الحجز',
                'status' => 'الحالة',
                'employee' => 'الموظف',
                default => request('search_by')
            };
            $filters[] = "🔍 $searchByText = \"" . request('search_value') . "\"";
        }
        if(request('date_from')) $filters[] = "📅 من تاريخ الإنشاء: " . \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y');
        if(request('date_to')) $filters[] = "📅 إلى تاريخ الإنشاء: " . \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y');
        if(request('start_date_from')) $filters[] = "📅 بدء من: " . \Carbon\Carbon::parse(request('start_date_from'))->format('d/m/Y');
        if(request('start_date_to')) $filters[] = "📅 بدء إلى: " . \Carbon\Carbon::parse(request('start_date_to'))->format('d/m/Y');
        if(request('end_date_from')) $filters[] = "📅 انتهاء من: " . \Carbon\Carbon::parse(request('end_date_from'))->format('d/m/Y');
        if(request('end_date_to')) $filters[] = "📅 انتهاء إلى: " . \Carbon\Carbon::parse(request('end_date_to'))->format('d/m/Y');
    @endphp
    @if(count($filters) > 0)
        <div class="active-filter">
            <strong>الفلاتر النشطة:</strong> {{ implode(' | ', $filters) }}
            <a href="{{ route('admin.availabilities.index') }}" style="margin-right: 10px; color: #dc2626; text-decoration: none;">✖️ إلغاء الكل</a>
        </div>
    @endif

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>الفندق</th>
                    <th>جهة الحجز</th>
                    <th>تاريخ البدء</th>
                    <th>تاريخ الانتهاء</th>
                    <th>الحالة</th>
                    <th>الموظف</th>
                    <th>تاريخ الإنشاء</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($availabilities as $availability)
                    <tr>
                        <td>{{ $availability->id }}</td>
                        <td>{{ $availability->hotel->name ?? 'N/A' }}</td>
                        <td>{{ $availability->agent->name ?? 'عام' }}</td>
                        <td>{{ $availability->start_date?->format('d/m/Y') ?? 'N/A' }}</td>
                        <td>{{ $availability->end_date?->format('d/m/Y') ?? 'N/A' }}</td>
                        <td>
                            @if ($availability->status == 'expired')
                                <span class="badge bg-danger">منتهية</span>
                            @elseif ($availability->status == 'active')
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-secondary">غير نشط</span>
                            @endif
                        </td>
                        <td>{{ $availability->employee->name ?? 'N/A' }}</td>
                        <td>{{ $availability->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('admin.availabilities.show', $availability->id) }}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.availabilities.edit', $availability->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            @if (auth()->user()->role === 'Admin')
                            <form action="{{ route('admin.availabilities.destroy', $availability->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">لا توجد إتاحات لعرضها.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $availabilities->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection