{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\auth\register.blade.php --}}
{{-- الصفحة دي مش مسموح لحد يوصلها غير المبرمج يفعلها ويسجل الناس في الداتا بيز وبعدها يعملها كومنت تاني  --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل مستخدم جديد</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- استدعاء Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            border-radius: 16px;
            background: #fff;
            border: none;
        }
        .register-title {
            color: #dc2626;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 600;
            color: #374151;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn-register {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 32px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }
        .company-field {
            display: none;
            opacity: 0;
            transition: all 0.3s ease;
        }
        .company-field.show {
            display: block;
            opacity: 1;
        }
        .role-option {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .role-option:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        .role-option.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card register-card">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            تسجيل مستخدم جديد
                        </h3>
                    </div>
    
                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('admin.register.user') }}" id="registerForm">
                            @csrf
    
                            <!-- الاسم -->
                            <div class="row mb-4">
                                <label for="name" class="col-md-3 col-form-label">
                                    <i class="fas fa-user me-1"></i>الاسم <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <input id="name" type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           name="name" value="{{ old('name') }}" 
                                           required autocomplete="name" autofocus
                                           placeholder="أدخل الاسم الكامل">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
    
                            <!-- البريد الإلكتروني -->
                            <div class="row mb-4">
                                <label for="email" class="col-md-3 col-form-label">
                                    <i class="fas fa-envelope me-1"></i>البريد الإلكتروني <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <input id="email" type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           name="email" value="{{ old('email') }}" 
                                           required autocomplete="email"
                                           placeholder="example@domain.com">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- نوع المستخدم -->
                            <div class="row mb-4">
                                <label class="col-md-3 col-form-label">
                                    <i class="fas fa-user-tag me-1"></i>نوع المستخدم <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="role-option p-3 rounded text-center" data-role="Admin">
                                                <i class="fas fa-crown fa-2x text-warning mb-2"></i>
                                                <h6 class="mb-0">مدير</h6>
                                                <small class="text-muted">صلاحيات كاملة</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="role-option p-3 rounded text-center" data-role="employee">
                                                <i class="fas fa-user-tie fa-2x text-info mb-2"></i>
                                                <h6 class="mb-0">موظف</h6>
                                                <small class="text-muted">صلاحيات محدودة</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="role-option p-3 rounded text-center" data-role="Company">
                                                <i class="fas fa-building fa-2x text-success mb-2"></i>
                                                <h6 class="mb-0">شركة</h6>
                                                <small class="text-muted">عرض وحجز</small>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="role" id="selectedRole" 
                                           class="@error('role') is-invalid @enderror" 
                                           value="{{ old('role') }}">
                                    @error('role')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- اختيار الشركة (يظهر فقط عند اختيار نوع "شركة") -->
                            <div class="row mb-4 company-field" id="companyField">
                                <label for="company_id" class="col-md-3 col-form-label">
                                    <i class="fas fa-building me-1"></i>اختيار الشركة <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <select id="company_id" name="company_id" 
                                            class="form-control @error('company_id') is-invalid @enderror">
                                        <option value="">-- اختر الشركة --</option>
                                        @foreach(\App\Models\Company::orderBy('name')->get() as $company)
                                            <option value="{{ $company->id }}" 
                                                    {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
    
                            <!-- كلمة المرور -->
                            <div class="row mb-4">
                                <label for="password" class="col-md-3 col-form-label">
                                    <i class="fas fa-lock me-1"></i>كلمة المرور <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <input id="password" type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" required autocomplete="new-password"
                                           placeholder="أدخل كلمة مرور قوية">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
    
                            <!-- تأكيد كلمة المرور -->
                            <div class="row mb-4">
                                <label for="password-confirm" class="col-md-3 col-form-label">
                                    <i class="fas fa-lock me-1"></i>تأكيد كلمة المرور <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <input id="password-confirm" type="password" 
                                           class="form-control" name="password_confirmation" 
                                           required autocomplete="new-password"
                                           placeholder="أعد إدخال كلمة المرور">
                                </div>
                            </div>
    
                            <!-- أزرار التحكم -->
                            <div class="row">
                                <div class="col-md-9 offset-md-3">
                                    <button type="submit" class="btn btn-primary btn-register me-3">
                                        <i class="fas fa-user-plus me-2"></i>إنشاء الحساب
                                    </button>
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>العودة للدخول
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleOptions = document.querySelectorAll('.role-option');
    const selectedRoleInput = document.getElementById('selectedRole');
    const companyField = document.getElementById('companyField');
    const companySelect = document.getElementById('company_id');

    // التعامل مع اختيار نوع المستخدم
    roleOptions.forEach(option => {
        option.addEventListener('click', function() {
            // إزالة التحديد من جميع الخيارات
            roleOptions.forEach(opt => opt.classList.remove('selected'));
            
            // تحديد الخيار المختار
            this.classList.add('selected');
            
            // تحديث القيمة المخفية
            const role = this.dataset.role;
            selectedRoleInput.value = role;
            
            // إظهار/إخفاء حقل الشركة
            if (role === 'Company') {
                companyField.classList.add('show');
                companySelect.required = true;
            } else {
                companyField.classList.remove('show');
                companySelect.required = false;
                companySelect.value = '';
            }
        });
    });

    // تحديد الاختيار السابق عند إعادة التحميل
    const oldRole = selectedRoleInput.value;
    if (oldRole) {
        const selectedOption = document.querySelector(`[data-role="${oldRole}"]`);
        if (selectedOption) {
            selectedOption.classList.add('selected');
            if (oldRole === 'Company') {
                companyField.classList.add('show');
                companySelect.required = true;
            }
        }
    }

    // التحقق من النموذج قبل الإرسال
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        if (!selectedRoleInput.value) {
            e.preventDefault();
            alert('يرجى اختيار نوع المستخدم');
            return false;
        }
        
        if (selectedRoleInput.value === 'Company' && !companySelect.value) {
            e.preventDefault();
            alert('يرجى اختيار الشركة');
            companySelect.focus();
            return false;
        }
    });
});
</script>
</body>
</html>


