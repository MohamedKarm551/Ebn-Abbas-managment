@extends('layouts.app')
@section('title', 'إدارة الشركات')
@push('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(120deg, #10b981 60%, #2563eb 100%);
            --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #16a34a 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #eab308 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.18);
            --shadow-soft: 0 8px 32px rgba(16, 185, 129, 0.1);
            --shadow-hover: 0 15px 40px rgba(16, 185, 129, 0.2);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* تصميم الهيدر */
        .page-header {
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: headerFloat 8s ease-in-out infinite;
        }

        @keyframes headerFloat {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(-30px, -30px) rotate(180deg);
            }
        }

        .page-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            margin: 0.5rem 0 0 0;
            position: relative;
            z-index: 2;
        }

        /* تصميم الفلاتر */
        .filters-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
        }

        .filters-section:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .filter-input {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(16, 185, 129, 0.1);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .filter-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.95);
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label i {
            color: #10b981;
            font-size: 1.1rem;
        }

        /* تصميم الأزرار */
        .btn-modern {
            background: var(--primary-gradient);
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .btn-modern:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-modern span {
            position: relative;
            z-index: 2;
        }

        .btn-success-modern {
            background: var(--success-gradient);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-warning-modern {
            background: var(--warning-gradient);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-danger-modern {
            background: var(--danger-gradient);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        /* تصميم البطاقات */
        .companies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .company-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            padding: 2rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .company-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .company-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .company-card:hover::before {
            transform: scaleX(1);
        }

        .company-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .company-icon i {
            color: white;
            font-size: 1.5rem;
        }

        .company-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .company-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            background: rgba(16, 185, 129, 0.1);
            border-radius: 10px;
            padding: 0.8rem;
            text-align: center;
            flex: 1;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #10b981;
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.2rem;
        }

        .company-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .action-btn {
            padding: 0.6rem 1rem;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            flex: 1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }

        .action-edit {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
        }

        .action-delete {
            background: var(--danger-gradient);
            color: white;
            box-shadow: 0 3px 10px rgba(239, 68, 68, 0.3);
        }

        /* تنسيق مبسط للزر المُعطل */
        .action-disabled {
            background: #dc3545;
            color: white;
            cursor: not-allowed;
            opacity: 0.8;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .action-disabled:hover {
            background: #dc3545;
            color: white;
            transform: none;
            opacity: 0.9;
        }

        .action-disabled i {
            margin-left: 0.3rem;
        }

        /* تصميم المودال */
        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: var(--primary-gradient);
            border-bottom: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            padding: 2rem;
        }

        .modal-title {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(16, 185, 129, 0.1);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            background: rgba(255, 255, 255, 0.95);
        }

        /* تحسينات الاستجابة */
        @media (max-width: 768px) {
            .companies-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .filters-section {
                padding: 1.5rem;
            }

            .company-card {
                padding: 1.5rem;
            }

            .company-actions {
                flex-direction: column;
            }
        }

        /* حالة عدم وجود شركات */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-soft);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #e5e7eb, #d1d5db);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .empty-icon i {
            font-size: 2rem;
            color: #9ca3af;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        /* تأثيرات التحميل */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* تصميم عداد الشركات */
        .companies-counter {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .counter-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .counter-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .counter-icon i {
            color: white;
            font-size: 1.2rem;
        }

        .counter-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
        }

        .counter-number {
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- هيدر الصفحة -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-building me-3"></i>
                إدارة الشركات
            </h1>
            <p class="page-subtitle">إدارة وتنظيم جميع الشركات في النظام</p>
        </div>

        <!-- قسم الفلاتر والبحث -->
        <div class="filters-section">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label class="filter-label">
                        <i class="fas fa-search"></i>
                        البحث في الشركات
                    </label>
                    <input type="text" class="form-control filter-input" id="searchInput" placeholder="ابحث بالاسم...">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="filter-label">
                        <i class="fas fa-sort"></i>
                        ترتيب حسب
                    </label>
                    <select class="form-control filter-input" id="sortBy">
                        <option value="name_asc">الاسم (أ-ي)</option>
                        <option value="name_desc">الاسم (ي-أ)</option>
                        <option value="created_asc">الأقدم</option>
                        <option value="created_desc">الأحدث</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="filter-label">
                        <i class="fas fa-eye"></i>
                        عرض
                    </label>
                    <select class="form-control filter-input" id="itemsPerPage">
                        <option value="12">12 شركة</option>
                        <option value="24">24 شركة</option>
                        <option value="48">48 شركة</option>
                        <option value="all">الكل</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="button" class="btn btn-modern w-100" data-bs-toggle="modal"
                        data-bs-target="#addCompanyModal">
                        <span>
                            <i class="fas fa-plus me-2"></i>
                            إضافة شركة
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- عداد الشركات -->
        <div class="companies-counter">
            <div class="counter-info">
                <div class="counter-icon">
                    <i class="fas fa-building"></i>
                </div>
                <span class="counter-text">إجمالي الشركات المسجلة</span>
            </div>
            <div class="counter-number" id="companiesCount">{{ $companies->count() }}</div>
        </div>

        <!-- عرض الشركات -->
        <div id="companiesContainer">
            @if ($companies->count() > 0)
                <div class="companies-grid" id="companiesGrid">
                    @foreach ($companies as $company)
                        <div class="company-card" data-name="{{ strtolower($company->name) }}"
                            data-created="{{ $company->created_at->timestamp }}">
                            <div class="company-icon">
                                <i class="fas fa-building"></i>
                            </div>

                            <h3 class="company-name">{{ $company->name }}</h3>
                            @if (auth()->user()->role === 'Admin')
                                <div class="company-email">
                                    <i class="fas fa-envelope me-2"></i>
                                    @forelse($company->users as $user)
                                        <span class="badge bg-light text-dark mb-1">{{ $user->email }}</span>
                                    @empty
                                        <span class="text-muted">لا يوجد إيميلات مسجلة</span>
                                    @endforelse
                                </div>
                            @endif


                            <div class="company-stats">
                                <div class="stat-item">
                                    <span class="stat-number">{{ $company->bookings_count ?? 0 }}</span>
                                    <span class="stat-label">حجز</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">{{ $company->created_at->diffForHumans() }}</span>
                                    <span class="stat-label">منذ</span>
                                </div>
                            </div>

                            <div class="company-actions">
                                <button type="button" class="action-btn action-edit"
                                    onclick="editCompany({{ $company->id }}, '{{ $company->name }}')">
                                    <i class="fas fa-edit"></i>
                                    تعديل
                                </button>

                                <div class="action-btn action-disabled">
                                    <i class="fas fa-ban"></i>
                                    هذا الإجراء غير مسموح
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="empty-title">لا توجد شركات مسجلة</h3>
                    <p class="empty-description">ابدأ بإضافة أول شركة في النظام</p>
                    <button type="button" class="btn btn-modern" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                        <span>
                            <i class="fas fa-plus me-2"></i>
                            إضافة شركة جديدة
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- مودال إضافة شركة -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        إضافة شركة جديدة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.storeCompany') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="filter-label">
                                <i class="fas fa-building"></i>
                                اسم الشركة
                            </label>
                            <input type="text" class="form-control" name="name" required
                                placeholder="أدخل اسم الشركة">
                        </div>
                        @if (auth()->user()->role === 'Admin')
                            <div class="form-group">
                                <label class="filter-label">
                                    <i class="fas fa-envelope"></i>
                                    بريد الشركة (اختياري)
                                </label>
                                <input type="email" class="form-control" name="company_email"
                                    placeholder="company@email.com">
                            </div>
                            <div class="form-group">
                                <label class="filter-label">
                                    <i class="fas fa-lock"></i>
                                    كلمة المرور (اختياري)
                                </label>
                                <input type="password" class="form-control" name="company_password"
                                    placeholder="••••••••">
                            </div>
                        @else
                            <input type="hidden" name="company_email">
                            <input type="hidden" name="company_password">
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-modern">
                            <span>
                                <i class="fas fa-save me-2"></i>
                                حفظ
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال تعديل شركة -->
    <div class="modal fade" id="editCompanyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        تعديل الشركة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCompanyForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="filter-label">
                                <i class="fas fa-building"></i>
                                اسم الشركة
                            </label>
                            <input type="text" class="form-control" name="name" id="editCompanyName" required>
                        </div>
                        @if (auth()->user()->role === 'Admin')
                            <div class="form-group">
                                <label class="filter-label">
                                    <i class="fas fa-envelope"></i>
                                    بريد جديد للشركة (اختياري)
                                </label>
                                <input type="email" class="form-control" name="new_company_email"
                                    placeholder="company@email.com">
                            </div>
                            <div class="form-group">
                                <label class="filter-label">
                                    <i class="fas fa-lock"></i>
                                    كلمة مرور جديدة (اختياري)
                                </label>
                                <input type="password" class="form-control" name="new_company_password"
                                    placeholder="••••••••">
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-warning-modern">
                            <span>
                                <i class="fas fa-save me-2"></i>
                                تحديث
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- مودال تأكيد الحذف -->
    <div class="modal fade" id="deleteCompanyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">هل أنت متأكد من حذف الشركة "<span id="deleteCompanyName"></span>"؟</p>
                    <p class="text-danger mt-2 mb-0"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form id="deleteCompanyForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger-modern">
                            <span>
                                <i class="fas fa-trash me-2"></i>
                                حذف نهائياً
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const sortBy = document.getElementById('sortBy');
                const itemsPerPage = document.getElementById('itemsPerPage');
                const companiesGrid = document.getElementById('companiesGrid');
                const companiesCount = document.getElementById('companiesCount');

                let companies = Array.from(document.querySelectorAll('.company-card'));

                // دالة البحث والفلترة (محسنة)
                function filterAndSort() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const sortOption = sortBy.value;
                    const itemsLimit = itemsPerPage.value;

                    // تصفية حسب البحث
                    let filteredCompanies = companies.filter(company => {
                        const name = company.dataset.name;
                        return name.includes(searchTerm);
                    });

                    // ترتيب (مُحسن)
                    filteredCompanies.sort((a, b) => {
                        switch (sortOption) {
                            case 'name_asc':
                                return a.dataset.name.localeCompare(b.dataset.name, 'ar');
                            case 'name_desc':
                                return b.dataset.name.localeCompare(a.dataset.name, 'ar');
                            case 'created_asc':
                                return parseInt(a.dataset.created) - parseInt(b.dataset.created);
                            case 'created_desc':
                                return parseInt(b.dataset.created) - parseInt(a.dataset.created);
                            default:
                                return 0;
                        }
                    });

                    // تحديد العدد المطلوب عرضه
                    if (itemsLimit !== 'all') {
                        filteredCompanies = filteredCompanies.slice(0, parseInt(itemsLimit));
                    }

                    // إعادة ترتيب عرض الشركات في DOM
                    if (companiesGrid) {
                        // مسح الحاوية وإعادة إضافة الشركات مرتبة
                        companiesGrid.innerHTML = '';

                        filteredCompanies.forEach(company => {
                            companiesGrid.appendChild(company);
                            company.style.display = 'block';
                        });

                        // إخفاء الشركات غير المفلترة
                        companies.forEach(company => {
                            if (!filteredCompanies.includes(company)) {
                                company.style.display = 'none';
                            }
                        });
                    }

                    // تحديث العداد
                    if (companiesCount) {
                        companiesCount.textContent = filteredCompanies.length;
                    }

                    // إظهار رسالة في حالة عدم وجود نتائج
                    updateEmptyState(filteredCompanies.length === 0 && searchTerm !== '');
                }

                // دالة إظهار حالة عدم وجود نتائج
                function updateEmptyState(show) {
                    let emptyState = document.querySelector('.search-empty-state');
                    const companiesContainer = document.getElementById('companiesContainer');

                    if (show && !emptyState && companiesContainer) {
                        emptyState = document.createElement('div');
                        emptyState.className = 'search-empty-state empty-state';
                        emptyState.innerHTML = `
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="empty-title">لا توجد نتائج</h3>
                        <p class="empty-description">لم يتم العثور على شركات تطابق البحث</p>
                    `;
                        companiesContainer.appendChild(emptyState);
                    } else if (!show && emptyState) {
                        emptyState.remove();
                    }
                }

                // ربط الأحداث
                if (searchInput) searchInput.addEventListener('input', filterAndSort);
                if (sortBy) sortBy.addEventListener('change', filterAndSort);
                if (itemsPerPage) itemsPerPage.addEventListener('change', filterAndSort);

                // تأثيرات بصرية للبحث
                if (searchInput) {
                    searchInput.addEventListener('focus', function() {
                        this.parentElement.style.transform = 'scale(1.02)';
                    });

                    searchInput.addEventListener('blur', function() {
                        this.parentElement.style.transform = 'scale(1)';
                    });
                }
            });

            // دالة تعديل الشركة
            function editCompany(id, name) {
                const editNameField = document.getElementById('editCompanyName');
                const editForm = document.getElementById('editCompanyForm');

                if (editNameField && editForm) {
                    editNameField.value = name;
                    editForm.action = `/admin/companies/${id}`;
                    new bootstrap.Modal(document.getElementById('editCompanyModal')).show();
                }
            }

            // دالة تأكيد الحذف
            function confirmDelete(id, name) {
                const deleteNameSpan = document.getElementById('deleteCompanyName');
                const deleteForm = document.getElementById('deleteCompanyForm');

                if (deleteNameSpan && deleteForm) {
                    deleteNameSpan.textContent = name;
                    deleteForm.action = `/admin/companies/${id}`;
                    new bootstrap.Modal(document.getElementById('deleteCompanyModal')).show();
                }
            }

            // تأثيرات إضافية للبطاقات
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.company-card');

                cards.forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-8px) scale(1.02)';
                    });

                    card.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0) scale(1)';
                    });
                });

                // تأثير الكتابة للعداد
                const counter = document.getElementById('companiesCount');
                if (counter) {
                    const finalNumber = parseInt(counter.textContent);

                    let currentNumber = 0;
                    const increment = Math.ceil(finalNumber / 30);

                    const timer = setInterval(() => {
                        currentNumber += increment;
                        if (currentNumber >= finalNumber) {
                            currentNumber = finalNumber;
                            clearInterval(timer);
                        }
                        counter.textContent = currentNumber;
                    }, 50);
                }
            });
        </script>
        <script src="{{ asset('js/preventClick.js') }}"></script>
        <!-- استدعاء الخلفية التفاعلية -->
        <script type="module">
            import {
                initParticlesBg
            } from '/js/particles-bg.js';
            initParticlesBg(); // يمكنك تمرير خيارات مثل {points:80, colors:[...]} إذا أردت
        </script>
    @endpush
@endsection
