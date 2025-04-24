@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إدارة الموظفين</h1>
    <form action="{{ route('admin.storeEmployee') }}" method="POST" class="mb-3">
        @csrf
        <div class="input-group">
            <input type="text"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="اسم الموظف"
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
                <th>#</th> <!-- عمود الترقيم -->
                <th>اسم الموظف</th>
                <th>الإجراءات
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
            <tr>
                <td>{{ $loop->iteration }}</td> <!-- عرض رقم الصف -->
                <td>
                    @if(auth()->user()->role === 'Admin')
                        <span class="editable" data-id="{{ $employee->id }}">{{ $employee->name }}</span>
                    @else
                        <span>{{ $employee->name }}</span>
                    @endif
                </td>
                <td>
                    @auth
                        @if(auth()->user()->role === 'Admin')
                            <form action="{{ route('admin.deleteEmployee', $employee->id) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                            </form>
                        @endif
                    @endauth
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
    .editable {
        cursor: pointer;
        color: #007bff;
    }

    .editable:hover {
        text-decoration: underline;
    }

    .editable-input {
        width: 100%;
        border: none;
        background: transparent;
        border-bottom: 1px solid #ccc;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editableElements = document.querySelectorAll('.editable');

        editableElements.forEach(element => {
            element.addEventListener('click', function () {
                const id = this.dataset.id;
                const currentValue = this.textContent.trim();

                const input = document.createElement('input');
                input.type = 'text';
                input.value = currentValue;
                input.className = 'editable-input';

                const existingError = this.parentNode.querySelector('.ajax-error-message');
                if (existingError) {
                    existingError.remove();
                }

                input.addEventListener('blur', function () {
                    const newValue = this.value.trim();

                    if (newValue !== currentValue && newValue !== '') {
                        fetch(`/admin/employees/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ name: newValue })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errorData => {
                                    throw errorData;
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                element.textContent = data.newName || newValue;
                            } else {
                                throw new Error(data.message || 'حدث خطأ غير محدد من الخادم.');
                            }
                        })
                        .catch(errorData => {
                            console.error('Error:', errorData);
                            let errorMessage = 'حدث خطأ أثناء التعديل.';
                            if (errorData && errorData.errors && errorData.errors.name) {
                                errorMessage = errorData.errors.name[0];
                            } else if (errorData && errorData.message) {
                                errorMessage = errorData.message;
                            }

                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'text-danger ajax-error-message';
                            errorDiv.style.fontSize = '0.8em';
                            errorDiv.textContent = errorMessage;
                            element.parentNode.appendChild(errorDiv);

                            element.textContent = currentValue;
                        })
                        .finally(() => {
                             element.style.display = 'inline';
                             input.remove();
                        });
                    } else {
                        element.textContent = currentValue;
                        element.style.display = 'inline';
                        input.remove();
                    }
                });

                this.style.display = 'none';
                this.parentNode.appendChild(input);
                input.focus();
            });
        });
    });
</script>
@push('scripts')
<script>
    document.addEventListener('contextmenu', function(event) {
        event.preventDefault();
    
    });

    document.addEventListener('keydown', function(event) {
        if (event.keyCode === 123) {
            event.preventDefault();
        }
        if (event.ctrlKey && event.shiftKey && event.keyCode === 73) {
            event.preventDefault();
        }
        if (event.ctrlKey && event.shiftKey && event.keyCode === 74) {
            event.preventDefault();
        }
        if (event.ctrlKey && event.keyCode === 85) {
            event.preventDefault();
        }
    });
</script>
@endpush

@endsection