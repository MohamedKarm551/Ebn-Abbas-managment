@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>إدارة الشركات</h1>
        <form action="{{ route('admin.storeCompany') }}" method="POST" class="mb-3">
            @csrf
            <div class="input-group">
                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="اسم الشركة"
                       value="{{ old('name') }}"
                       required>
                <button type="submit" class="btn btn-primary">إضافة</button>

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th> <!-- رقم الصف -->
                    <th>اسم الشركة</th>
                    <th>
                        الإجراءات
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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('scripts')
    
          <script src="{{ asset('js/preventClick.js') }}"></script>

   
    @endpush

@endsection
