@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إدارة الفنادق</h1>
    <form action="{{ route('admin.storeHotel') }}" method="POST" class="mb-3">
        @csrf
        <div class="input-group">
            <input type="text" name="name" class="form-control" placeholder="اسم الفندق" required>
            <button type="submit" class="btn btn-primary">إضافة</button>
        </div>
    </form>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th> <!-- عمود الترقيم -->
                <th>اسم الفندق</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hotels as $hotel)
            <tr>
                <td>{{ $loop->iteration }}</td> <!-- عرض رقم الصف -->
                <td>{{ $hotel->name }}</td>
                <td>
                    <a href="{{ route('admin.editHotel', $hotel->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('admin.deleteHotel', $hotel->id) }}" method="POST" style="display:inline;">
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