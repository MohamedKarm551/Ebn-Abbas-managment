{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\availabilities\index.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة الإتاحات')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>قائمة الإتاحات</h1>
            <a href="{{ route('admin.availabilities.create') }}" class="btn btn-primary">إضافة إتاحة جديدة</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- يمكنك إضافة فورم للفلترة هنا --}}
        {{-- <form method="GET" action="{{ route('admin.availabilities.index') }}" class="mb-3 p-3 bg-light rounded">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="hotel_id" class="form-label">الفندق</label>
                <select name="hotel_id" id="hotel_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach ($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">الحالة</label>
                <select name="status" id="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-info">فلترة</button>
                <a href="{{ route('admin.availabilities.index') }}" class="btn btn-secondary">إعادة تعيين</a>
            </div>
        </div>
    </form> --}}


        <div class="table-responsive">
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
                                {{-- *** بداية التعديل: التحقق من تاريخ النهاية أولاً *** --}}
                                @if ($availability->status == 'expired' )
                                    {{-- لو تاريخ النهاية عدى، نعرض "منتهية" بغض النظر عن الـ status --}}
                                    <span class="badge bg-danger">منتهية</span>
                                @elseif ($availability->status == 'active')
                                    {{-- لو التاريخ لسه معداش والحالة active، نعرض "نشط" --}}
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    {{-- لو التاريخ لسه معداش والحالة مش active (يعني inactive)، نعرض "غير نشط" --}}
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                                {{-- *** نهاية التعديل *** --}}
                          
                            <td>{{ $availability->employee->name ?? 'N/A' }}</td>
                            {{-- *** بداية التعديل: إضافة تحقق قبل format *** --}}
                            <td>
                                @if ($availability->created_at)
                                    {{ $availability->created_at->format('Y-m-d H:i') }}
                                @else
                                    N/A {{-- أو أي قيمة بديلة تفضلها --}}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.availabilities.show', $availability->id) }}"
                                    class="btn btn-sm btn-info" title="عرض"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.availabilities.edit', $availability->id) }}"
                                    class="btn btn-sm btn-warning" title="تعديل"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.availabilities.destroy', $availability->id) }}"
                                    method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذه الإتاحة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف"><i
                                            class="bi bi-trash"></i></button>
                                </form>
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

        {{-- روابط الـ Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $availabilities->appends(request()->query())->links() }}
        </div>

    </div>
@endsection

@push('scripts')
    {{-- يمكنك إضافة Select2 هنا إذا استخدمت الفلاتر --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" /> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    <script>
        // $(document).ready(function() {
        //     $('#hotel_id, #status').select2({
        //         theme: 'bootstrap-5',
        //         placeholder: $(this).data('placeholder'),
        //         width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        //     });
        // });
    </script>
@endpush
