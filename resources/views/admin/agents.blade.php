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
                    <form action="{{ route('admin.deleteAgent', $agent->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection