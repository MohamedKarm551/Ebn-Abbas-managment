@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إدارة الموظفين</h1>
    <form action="{{ route('admin.storeEmployee') }}" method="POST" class="mb-3">
        @csrf
        <div class="input-group">
            <input type="text" name="name" class="form-control" placeholder="اسم الموظف" required>
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
                <th>اسم الموظف</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
            <tr>
                <td>{{ $loop->iteration }}</td> <!-- عرض رقم الصف -->
                <td>
                    <span class="editable" data-id="{{ $employee->id }}">{{ $employee->name }}</span>
                </td>
                <td>
                    <form action="{{ route('admin.deleteEmployee', $employee->id) }}" method="POST" style="display:inline;">
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

                // تحويل النص إلى حقل إدخال
                const input = document.createElement('input');
                input.type = 'text';
                input.value = currentValue;
                input.className = 'editable-input';
                input.addEventListener('blur', function () {
                    const newValue = this.value.trim();

                    if (newValue !== currentValue) {
                        // إرسال التعديل إلى السيرفر باستخدام AJAX
                        fetch(`/admin/employees/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ name: newValue })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                element.textContent = newValue;
                                alert('تم تعديل اسم الموظف بنجاح!');
                            } else {
                                alert('حدث خطأ أثناء التعديل.');
                                element.textContent = currentValue;
                            }
                        })
                        .catch(() => {
                            alert('حدث خطأ أثناء التعديل.');
                            element.textContent = currentValue;
                        });
                    } else {
                        element.textContent = currentValue;
                    }

                    element.style.display = 'inline';
                    this.remove();
                });

                this.style.display = 'none';
                this.parentNode.appendChild(input);
                input.focus();
            });
        });
    });
</script>
@endsection