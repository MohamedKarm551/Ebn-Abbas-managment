@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>إدارة الموظفين</h1>
        <form action="{{ route('admin.storeEmployee') }}" method="POST" class="mb-3">
            @csrf
            <div class="input-group">
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    placeholder="اسم الموظف" value="{{ old('name') }}" required>
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
                    <th>#</th>
                    <th>اسم الموظف</th>
                    <th>حالة الحساب</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if (auth()->user()->role === 'Admin')
                                <span class="editable" data-id="{{ $employee->id }}">{{ $employee->name }}</span>
                            @else
                                <span>{{ $employee->name }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($employee->user_id)
                                <span class="badge bg-success">مرتبط بحساب</span>
                                @if ($employee->user && Auth::user()->role === 'Admin')
                                    ({{ $employee->user->email }})
                                @endif
                            @else
                                <span class="badge bg-warning">بدون حساب</span>
                            @endif
                        </td>
                        <td>
                            @auth
                                @if (auth()->user()->role === 'Admin')
                                    <div class="btn-group">
                                        @if (!$employee->user_id)
                                            <!-- زر إنشاء حساب جديد -->
                                            <button type="button" class="btn btn-success btn-sm create-user-btn"
                                                data-employee-id="{{ $employee->id }}"
                                                data-employee-name="{{ $employee->name }}">
                                                <i class="fas fa-user-plus"></i> إنشاء حساب
                                            </button>
                                            <!-- زر ربط بحساب موجود -->
                                            <button type="button" class="btn btn-info btn-sm link-user-btn"
                                                data-employee-id="{{ $employee->id }}"
                                                data-employee-name="{{ $employee->name }}">
                                                <i class="fas fa-link"></i> ربط بحساب
                                            </button>
                                        @else
                                            <!-- زر إلغاء ربط الحساب -->
                                            <form action="{{ route('admin.unlinkEmployeeUser', $employee->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-unlink"></i> إلغاء ربط الحساب
                                                </button>
                                            </form>
                                        @endif
                                        <!-- زر حذف الموظف -->
                                        <form action="{{ route('admin.deleteEmployee', $employee->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- نافذة منبثقة لإنشاء حساب جديد -->
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">إنشاء حساب مستخدم جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createUserForm" action="{{ route('admin.createEmployeeUser') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="employee_id" name="employee_id">
                        <div class="mb-3">
                            <label for="name" class="form-label">الاسم</label>
                            <input type="text" class="form-control" id="name" name="name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">الدور</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="employee">موظف</option>
                                <option value="Admin">مدير</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إنشاء الحساب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- نافذة منبثقة لربط بحساب موجود -->
    <div class="modal fade" id="linkUserModal" tabindex="-1" role="dialog" aria-labelledby="linkUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="linkUserModalLabel">ربط الموظف بحساب موجود</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="linkUserForm" action="{{ route('admin.linkEmployeeUser') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="employee_id_link" name="employee_id">
                        <div class="mb-3">
                            <label for="employee_name" class="form-label">اسم الموظف</label>
                            <input type="text" class="form-control" id="employee_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="user_id" class="form-label">حساب المستخدم</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">-- اختر حساب مستخدم --</option>
                                @foreach (\App\Models\User::whereDoesntHave('employee')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">ربط الحساب</button>
                    </div>
                </form>
            </div>
        </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const editableElements = document.querySelectorAll('.editable');

            editableElements.forEach(element => {
                element.addEventListener('click', function() {
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

                    input.addEventListener('blur', function() {
                        const newValue = this.value.trim();

                        if (newValue !== currentValue && newValue !== '') {
                            fetch(`/admin/employees/${id}`, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        name: newValue
                                    })
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
                                        throw new Error(data.message ||
                                            'حدث خطأ غير محدد من الخادم.');
                                    }
                                })
                                .catch(errorData => {
                                    console.error('Error:', errorData);
                                    let errorMessage = 'حدث خطأ أثناء التعديل.';
                                    if (errorData && errorData.errors && errorData
                                        .errors.name) {
                                        errorMessage = errorData.errors.name[0];
                                    } else if (errorData && errorData.message) {
                                        errorMessage = errorData.message;
                                    }

                                    const errorDiv = document.createElement('div');
                                    errorDiv.className =
                                        'text-danger ajax-error-message';
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
    <script>
            // سكريبت تحرير الاسم الحالي...

    document.addEventListener('DOMContentLoaded', function () {
        // إعداد مشغلات الأزرار للنوافذ المنبثقة
        const createUserBtns = document.querySelectorAll('.create-user-btn');
        createUserBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-employee-id');
                const employeeName = this.getAttribute('data-employee-name');
                document.getElementById('employee_id').value = employeeId;
                document.getElementById('name').value = employeeName;
                
                // إنشاء بريد إلكتروني مقترح
                const suggestedEmail = employeeName.toLowerCase().replace(/\s+/g, '.') + '@ebnabbas.com';
                document.getElementById('email').value = suggestedEmail;
                
                // عرض النافذة المنبثقة
                const createUserModal = new bootstrap.Modal(document.getElementById('createUserModal'));
                createUserModal.show();
            });
        });
        
        const linkUserBtns = document.querySelectorAll('.link-user-btn');
        linkUserBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-employee-id');
                const employeeName = this.getAttribute('data-employee-name');
                document.getElementById('employee_id_link').value = employeeId;
                document.getElementById('employee_name').value = employeeName;
                
                // عرض النافذة المنبثقة
                const linkUserModal = new bootstrap.Modal(document.getElementById('linkUserModal'));
                linkUserModal.show();
            });
        });
    });

    </script>
    @push('scripts')
        <script src="{{ asset('js/preventClick.js') }}"></script>
    @endpush
@endsection
