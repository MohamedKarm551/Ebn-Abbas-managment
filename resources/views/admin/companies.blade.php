@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>إدارة الشركات</h1>
        <form action="{{ route('admin.storeCompany') }}" method="POST" class="mb-3">
            @csrf
            <div class="input-group">
                <input type="text" name="name" class="form-control" placeholder="اسم الشركة" required>
                <button type="submit" class="btn btn-primary">إضافة</button>
            </div>
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th> <!-- رقم الصف -->
                    <th>اسم الشركة</th>
                    <th>
                        الإجراءات
                        @auth
                            @if (auth()->user()->role != 'Admin')
                                - يمكنك تعديل الإسم فقط والحذف يظهر للأدمن -
                            @endif
                            @endauth
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($companies as $index => $company)
                        <tr>
                            <td>{{ $index + 1 }}</td> <!-- عرض رقم الصف -->
                            <td>{{ $company->name }}</td>
                            <td>
                                <a href="{{ route('admin.editCompany', $company->id) }}"
                                    class="btn btn-warning btn-sm">تعديل</a>
                                    <small class="text-muted ms-2">(الحذف غير مسموح)</small>

                                {{-- @auth
                                    @if (auth()->user()->role === 'Admin')
                                        <form action="{{ route('admin.deleteCompany', $company->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذه الشركة؟ سيتم حذف جميع الحجوزات والدفعات المرتبطة بها بشكل نهائي.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                        </form>
                                    @endif
                                @endauth --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endsection
