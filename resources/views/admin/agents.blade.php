@extends('layouts.app')

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
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-30px, -30px) rotate(180deg); }
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

    /* تصميم قسم الإضافة */
    .add-section {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-soft);
        transition: var(--transition);
    }

    .add-section:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label i {
        color: #10b981;
        font-size: 1.1rem;
    }

    .form-control {
        background: rgba(255, 255, 255, 0.8);
        border: 2px solid rgba(16, 185, 129, 0.1);
        border-radius: 15px;
        padding: 1rem 1.5rem;
        font-size: 1rem;
        transition: var(--transition);
        backdrop-filter: blur(10px);
    }

    .form-control:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.95);
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

    .filter-input {
        background: rgba(255, 255, 255, 0.8);
        border: 2px solid rgba(16, 185, 129, 0.1);
        border-radius: 15px;
        padding: 1rem 1.5rem;
        font-size: 1rem;
        transition: var(--transition);
    }

    .filter-input:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        background: rgba(255, 255, 255, 0.95);
    }

    /* تصميم البطاقات */
    .agents-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
        gap: 1.5rem !important;
        margin-top: 1rem;
    }

    .agent-card {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 20px !important;
        padding: 2rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08) !important;
        display: block !important;
        width: 100% !important;
    }

    .agent-card::before {
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

    .agent-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.15) !important;
    }

    .agent-card:hover::before {
        transform: scaleX(1);
    }

    .agent-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }

    .agent-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
        line-height: 1.4;
    }

    .agent-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-item {
        background: rgba(16, 185, 129, 0.1);
        border-radius: 12px;
        padding: 1rem;
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
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 0.3rem;
    }

    .agent-actions {
        display: flex;
        gap: 0.8rem;
        justify-content: center;
    }

    .action-btn {
        padding: 0.7rem 1.2rem;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        text-decoration: none;
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

    .action-edit:hover {
        color: white;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .action-disabled {
        background: #dc3545;
        color: white;
        cursor: not-allowed;
        opacity: 0.8;
        font-size: 0.75rem;
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

    /* عداد جهات الحجز */
    .agents-counter {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
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
        color: white;
        font-size: 1.2rem;
    }

    .counter-number {
        background: var(--primary-gradient);
        color: white;
        padding: 0.5rem 1.2rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 1.2rem;
    }

    /* حالة عدم وجود جهات حجز */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
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
        font-size: 2rem;
        color: #9ca3af;
    }

    /* تحسينات للشاشات الصغيرة */
    @media (max-width: 768px) {
        .agents-grid {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        
        .page-title {
            font-size: 2rem;
        }
        
        .add-section,
        .filters-section {
            padding: 1.5rem;
        }
        
        .agent-card {
            padding: 1.5rem !important;
        }
        
        .agent-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- هيدر الصفحة -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-handshake me-3"></i>
            إدارة جهات الحجز
        </h1>
        <p class="page-subtitle">إدارة وتنظيم جميع جهات الحجز في النظام</p>
    </div>

    <!-- قسم إضافة جهة حجز جديدة -->
    <div class="add-section">
        <form action="{{ route('admin.storeAgent') }}" method="POST">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-8 mb-3">
                    <label class="form-label">
                        <i class="fas fa-handshake"></i>
                        اسم جهة الحجز
                    </label>
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="أدخل اسم جهة الحجز"
                           value="{{ old('name') }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn-modern w-100">
                        <span>
                            <i class="fas fa-plus me-2"></i>
                            إضافة جهة حجز
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- رسائل النجاح والخطأ -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- قسم الفلاتر والبحث -->
    <div class="filters-section">
        <div class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label class="form-label">
                    <i class="fas fa-search"></i>
                    البحث في جهات الحجز
                </label>
                <input type="text" class="filter-input" id="searchInput" 
                       placeholder="ابحث بالاسم...">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">
                    <i class="fas fa-sort"></i>
                    ترتيب حسب
                </label>
                <select class="filter-input" id="sortBy">
                    <option value="name_asc">الاسم (أ-ي)</option>
                    <option value="name_desc">الاسم (ي-أ)</option>
                    <option value="created_asc">الأقدم</option>
                    <option value="created_desc">الأحدث</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">
                    <i class="fas fa-eye"></i>
                    عرض
                </label>
                <select class="filter-input" id="itemsPerPage">
                    <option value="12">12 جهة</option>
                    <option value="24">24 جهة</option>
                    <option value="48">48 جهة</option>
                    <option value="all">الكل</option>
                </select>
            </div>
        </div>
    </div>

    <!-- عداد جهات الحجز -->
    <div class="agents-counter">
        <div class="counter-info">
            <div class="counter-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <span class="fw-bold">إجمالي جهات الحجز المسجلة</span>
        </div>
        <div class="counter-number" id="agentsCount">{{ $agents->count() }}</div>
    </div>

    <!-- عرض جهات الحجز -->
    @if($agents->count() > 0)
        <div class="agents-grid" id="agentsGrid">
            @foreach($agents as $index => $agent)
                <div class="agent-card" data-name="{{ strtolower($agent->name) }}" data-created="{{ $agent->created_at->timestamp }}">
                    <div class="agent-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    
                    <h3 class="agent-name">{{ $agent->name }}</h3>
                    
                    <div class="agent-stats">
                        <div class="stat-item">
                            <span class="stat-number">#{{ $index + 1 }}</span>
                            <span class="stat-label">الترتيب</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">{{ $agent->created_at->diffForHumans() }}</span>
                            <span class="stat-label">منذ</span>
                        </div>
                    </div>
                    
                    <div class="agent-actions">
                        <a href="{{ route('admin.editAgent', $agent->id) }}" class="action-btn action-edit">
                            <i class="fas fa-edit"></i>
                            تعديل
                        </a>
                        
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
                <i class="fas fa-handshake"></i>
            </div>
            <h3>لا توجد جهات حجز مسجلة</h3>
            <p class="text-muted mb-3">ابدأ بإضافة أول جهة حجز في النظام</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const sortBy = document.getElementById('sortBy');
    const itemsPerPage = document.getElementById('itemsPerPage');
    const agentsGrid = document.getElementById('agentsGrid');
    const agentsCount = document.getElementById('agentsCount');
    
    let agents = Array.from(document.querySelectorAll('.agent-card'));
    
    function filterAndSort() {
        const searchTerm = searchInput.value.toLowerCase();
        const sortOption = sortBy.value;
        const itemsLimit = itemsPerPage.value;
        
        let filteredAgents = agents.filter(agent => {
            const name = agent.dataset.name;
            return name.includes(searchTerm);
        });
        
        filteredAgents.sort((a, b) => {
            switch(sortOption) {
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
        
        if (itemsLimit !== 'all') {
            filteredAgents = filteredAgents.slice(0, parseInt(itemsLimit));
        }
        
        if (agentsGrid) {
            agentsGrid.innerHTML = '';
            
            filteredAgents.forEach(agent => {
                agentsGrid.appendChild(agent);
                agent.style.display = 'block';
            });
            
            agents.forEach(agent => {
                if (!filteredAgents.includes(agent)) {
                    agent.style.display = 'none';
                }
            });
        }
        
        if (agentsCount) {
            agentsCount.textContent = filteredAgents.length;
        }
        
        updateEmptyState(filteredAgents.length === 0 && searchTerm !== '');
    }
    
    function updateEmptyState(show) {
        let emptyState = document.querySelector('.search-empty-state');
        const agentsContainer = agentsGrid?.parentNode;
        
        if (show && !emptyState && agentsContainer) {
            emptyState = document.createElement('div');
            emptyState.className = 'search-empty-state empty-state';
            emptyState.innerHTML = `
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>لا توجد نتائج</h3>
                <p class="text-muted">لم يتم العثور على جهات حجز تطابق البحث</p>
            `;
            agentsContainer.appendChild(emptyState);
        } else if (!show && emptyState) {
            emptyState.remove();
        }
    }
    
    if (searchInput) searchInput.addEventListener('input', filterAndSort);
    if (sortBy) sortBy.addEventListener('change', filterAndSort);
    if (itemsPerPage) itemsPerPage.addEventListener('change', filterAndSort);
    
    // تأثيرات بصرية للبطاقات
    const cards = document.querySelectorAll('.agent-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // تأثير العداد
    const counter = document.getElementById('agentsCount');
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
@endpush
@endsection