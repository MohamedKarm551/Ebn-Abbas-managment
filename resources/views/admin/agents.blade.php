@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إدارة جهات الحجز</h1>
    <form action="{{ route('admin.storeAgent') }}" method="POST" class="mb-3">
        @csrf
        <div class="input-group">
            <input type="text" name="name" class="form-control" placeholder="اسم جهة الحجز" required>
            <button type="submit" class="btn btn-primary">إضافة</button>
        </div>
    </form>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>اسم جهة الحجز</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($agents as $index => $agent)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $agent->name }}</td>
                <td>
                    <a href="{{ route('admin.editAgent', $agent->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <small class="text-muted ms-2">(الحذف غير مسموح)</small>

                    {{-- زر الحذف ممنوع  --}}
                    {{-- @auth
                        @if(auth()->user()->role === 'Admin') --}}
                            {{-- *** تم تعطيل الحذف مؤقتًا - غير مسموح حتى للمشرف بالحذف حاليًا *** --}}
                            {{-- 
                            <form action="{{ route('admin.deleteAgent', $agent->id) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('هل أنت متأكد من حذف جهة الحجز هذه؟ سيتم حذف جميع الحجوزات والدفعات المرتبطة بها بشكل نهائي.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                            </form>
                            --}}
                        {{-- @endif
                    @endauth --}}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection