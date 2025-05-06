@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إدارة جهات الحجز</h1>
    <form action="{{ route('admin.storeAgent') }}" method="POST" class="mb-3">
        @csrf
        <div class="input-group">
            {{-- *** تعديل هنا: إضافة كلاس is-invalid و value="{{ old('name') }}" *** --}}
            <input type="text"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="اسم جهة الحجز"
                   value="{{ old('name') }}"
                   required>
            <button type="submit" class="btn btn-primary">إضافة</button>

            {{-- *** إضافة هذا الجزء لعرض الخطأ *** --}}
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
            {{-- *** نهاية جزء عرض الخطأ *** --}}
        </div>
    </form>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    {{-- *** إضافة عرض رسالة الخطأ العامة إذا وجدت (من try-catch) *** --}}
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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
                    {{-- إضافة ملاحظة ظاهرة هنا --}}
                    <small class="text-muted ms-2">(الحذف غير مسموح)</small>

                    {{-- زر الحذف ممنوع (الكود معطل في التعليقات) --}}
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
@push('scripts') {{-- أو يمكنك وضعه مباشرة قبل @endsection --}}

      <script src="{{ asset('js/preventClick.js') }}"></script>


@endpush {{-- أو نهاية <script> --}}

@endsection