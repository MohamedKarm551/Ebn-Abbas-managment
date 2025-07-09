@extends('layouts.app')

@section('title', 'متابعة مدفوعات وكلاء الرحلات البرية')

@push('styles')
    <style>
        .agent-card {
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .agent-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .currency-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* تحسينات البطاقات */

        .agent-card {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 16px;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .agent-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
            border-color: rgba(13, 110, 253, 0.2) !important;
        }

        /* رقم الترتيب */
        .badge-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        /* رأس البطاقة */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
            position: relative;
        }

        .bg-gradient-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        /* قسم معلومات الاتصال */
        .contact-section {
            border-left: 4px solid #0d6efd;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%) !important;
            transition: all 0.3s ease;
        }

        .contact-section:hover {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.04) 100%) !important;
            border-left-color: #0b5ed7;
        }

        .contact-item {
            transition: all 0.2s ease;
        }

        .contact-item:hover {
            transform: translateX(5px);
        }

        /* صناديق الإحصائيات */
        .stat-box {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.6s;
        }

        .stat-box:hover::before {
            left: 100%;
        }

        .stat-box:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* شريط التقدم المحسن */
        .progress {
            background-color: rgba(0, 0, 0, 0.05);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, .2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .2) 50%, rgba(255, 255, 255, .2) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
        }

        /* آخر دفعة */
        .last-payment-section {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, rgba(25, 135, 84, 0.02) 100%);
            border-radius: 12px;
            margin: -0.5rem;
            padding: 1rem !important;
            transition: all 0.3s ease;
        }

        .last-payment-section:hover {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.08) 0%, rgba(25, 135, 84, 0.04) 100%);
            transform: scale(1.02);
        }

        /* أزرار العمل */
        .card-footer .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card-footer .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .card-footer .btn:hover::before {
            left: 100%;
        }

        .card-footer .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 576px) {
            .agent-card {
                margin-bottom: 1rem;
            }

            .badge-number {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }

            .card-header .badge {
                font-size: 0.7rem;
                padding: 0.5rem 0.75rem !important;
            }

            .stat-box {
                padding: 0.75rem !important;
            }

            .stat-value {
                font-size: 0.8rem !important;
            }

            .contact-section {
                padding: 0.75rem !important;
            }

            .card-footer .btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .card-header {
                padding: 1rem !important;
            }

            .card-header .d-flex {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.75rem !important;
            }

            .card-header .badge {
                align-self: flex-start !important;
            }
        }

        /* تأثيرات التحميل */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .agent-card {
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .agent-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .agent-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .agent-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .agent-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .agent-card:nth-child(5) {
            animation-delay: 0.5s;
        }

        .agent-card:nth-child(6) {
            animation-delay: 0.6s;
        }

        /* تحسين البادجات */
        .badge {
            font-weight: 600 !important;
            letter-spacing: 0.025em;
        }

        /* الحالة الفارغة */
        .empty-state-icon {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* تحسين النصوص العربية */
        * {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .fw-bold {
            font-weight: 700 !important;
        }

        .fw-semibold {
            font-weight: 600 !important;
        }
        .ranking-badge-modern {
    background: rgba(255, 255, 255, 0.95) !important;
    color: #0d6efd !important;
    font-weight: 700;
    font-size: 0.75rem;
    padding: 0.4rem 0.6rem;
    border-radius: 12px;
    border: 2px solid #0d6efd;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.ranking-badge-modern:hover {
    background: #0d6efd !important;
    color: white !important;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

/* ألوان للمراكز الأولى */
.agent-card:nth-child(1) .ranking-badge-modern {
    border-color: #ffc107;
    color: #ffc107 !important;
}

.agent-card:nth-child(1) .ranking-badge-modern:hover {
    background: #ffc107 !important;
    color: white !important;
}

.agent-card:nth-child(2) .ranking-badge-modern {
    border-color: #6c757d;
    color: #6c757d !important;
}

.agent-card:nth-child(2) .ranking-badge-modern:hover {
    background: #6c757d !important;
}

.agent-card:nth-child(3) .ranking-badge-modern {
    border-color: #fd7e14;
    color: #fd7e14 !important;
}

.agent-card:nth-child(3) .ranking-badge-modern:hover {
    background: #fd7e14 !important;
}
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-handshake text-primary me-2"></i>
                        متابعة مدفوعات وكلاء الرحلات البرية
                    </h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-1"></i>
                            تصدير Excel
                        </button>
                    </div>
                </div>
                <p class="text-muted mt-2">تتبع المبالغ المستحقة لوكلاء الحجز من الرحلات البرية فقط</p>
            </div>
        </div>

        <!-- الإحصائيات العامة -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-handshake fa-2x mb-2 text-primary"></i>
                        <h4 class="mb-1">{{ $totalStats['agents_count'] }}</h4>
                        <small>جهات حجز نشطة  </small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-bus fa-2x mb-2"></i>
                        <h5 class="mb-1">{{ number_format($totalStats['total_bookings']) }}</h5>
                        <small>حجوزات رحلات برية</small>
                    </div>
                </div>
            </div>
          
            <div class="col-md-2">
                <div class="card bg-primary text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-coins fa-2x mb-2"></i>
                        <h6 class="mb-1">{{ number_format($totalStats['total_due_kwd'], 0) }}</h6>
                        <small>المستحق (دينار)</small>
                    </div>
                </div>
            </div>
           
            <div class="col-md-2">
                <div class="card bg-secondary text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                        <h6 class="mb-1">{{ number_format($totalStats['total_paid_kwd'], 0) }}</h6>
                        <small>المدفوع (دينار)</small>
                    </div>
                </div>
            </div>
              <div class="col-md-2">
                <div class="card bg-success text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-coins fa-2x mb-2"></i>
                        <h6 class="mb-1">{{ number_format($totalStats['total_due_sar'], 0) }}</h6>
                        <small>المستحق (ريال)</small>
                    </div>
                </div>
            </div>
             <div class="col-md-2">
                <div class="card bg-warning text-white border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                        <h6 class="mb-1">{{ number_format($totalStats['total_paid_sar'], 0) }}</h6>
                        <small>المدفوع (ريال)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- البحث والفلترة -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">البحث عن وكيل</label>
                        <input type="text" name="search" class="form-control search-box" placeholder="اسم جهة الحجز..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">العملة</label>
                        <select name="currency" class="form-select">
                            <option value="">كل العملات</option>
                            <option value="SAR" {{ request('currency') == 'SAR' ? 'selected' : '' }}>ريال سعودي</option>
                            <option value="KWD" {{ request('currency') == 'KWD' ? 'selected' : '' }}>دينار كويتي</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> بحث
                            </button>
                            <a href="{{ route('admin.land-trips-agent-payments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- قائمة الوكلاء -->
        <div class="row g-4">
            @forelse($agents as $index => $agent)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card agent-card shadow-sm border-0 h-100 position-relative overflow-hidden">
                        <!-- رقم الترتيب -->
                        <div class="position-absolute z-3" style="top: 8px; right: 8px;">
    <span class="badge ranking-badge-modern">
        #{{ $index + 1 }}
    </span>
</div>

                        <!-- رأس البطاقة -->
                        <div class="card-header bg-gradient-primary text-white border-0 position-relative">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-2">
                                <h6 class="mb-0 fw-bold text-truncate flex-grow-1 ps-5" title="{{ $agent['name'] }}">
                                    {{ $agent['name'] }}
                                </h6>
                                <span class="badge bg-light text-primary px-3 py-2 rounded-pill flex-shrink-0">
                                    <i class="fas fa-bus me-1"></i>
                                    {{ $agent['bookings_count'] }} حجز
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <!-- معلومات الاتصال -->
                            @if ($agent['email'] || $agent['phone'])
                                <div class="contact-section mb-3 p-3 bg-light rounded-3">
                                    <h6 class="text-primary mb-2 fw-semibold">
                                        <i class="fas fa-address-book me-1"></i>
                                        معلومات الاتصال
                                    </h6>
                                    @if ($agent['email'])
                                        <div class="contact-item d-flex align-items-center mb-2">
                                            <i class="fas fa-envelope text-muted me-2" style="width: 16px;"></i>
                                            <small class="text-muted text-truncate" title="{{ $agent['email'] }}">
                                                {{ $agent['email'] }}
                                            </small>
                                        </div>
                                    @endif
                                    @if ($agent['phone'])
                                        <div class="contact-item d-flex align-items-center mb-0">
                                            <i class="fas fa-phone text-muted me-2" style="width: 16px;"></i>
                                            <small class="text-muted">{{ $agent['phone'] }}</small>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- الإحصائيات المالية -->
                            <div class="financial-stats">
                                @foreach (['SAR' => ['color' => 'success', 'name' => 'ريال سعودي'], 'KWD' => ['color' => 'warning', 'name' => 'دينار كويتي']] as $currency => $config)
                                    @if ($agent['totals_by_currency'][$currency]['due'] > 0)
                                        <div class="currency-section mb-3">
                                            <!-- رأس العملة -->
                                            <div class="currency-header text-center mb-3">
                                                <span
                                                    class="badge bg-{{ $config['color'] }} text-white px-3 py-2 rounded-pill">
                                                    <i class="fas fa-coins me-1"></i>
                                                    {{ $config['name'] }}
                                                </span>
                                            </div>

                                            <!-- إحصائيات العملة -->
                                            <div class="row g-2 mb-3">
                                                <div class="col-4">
                                                    <div
                                                        class="stat-box text-center p-2 bg-primary bg-opacity-10 rounded-3">
                                                        <div class="stat-label text-primary fw-semibold mb-1"
                                                            style="font-size: 0.7rem;">المستحق</div>
                                                        <div class="stat-value fw-bold text-primary"
                                                            style="font-size: 0.85rem;">
                                                            {{ number_format($agent['totals_by_currency'][$currency]['due'], 0) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div
                                                        class="stat-box text-center p-2 bg-success bg-opacity-10 rounded-3">
                                                        <div class="stat-label text-success fw-semibold mb-1"
                                                            style="font-size: 0.7rem;">المدفوع</div>
                                                        <div class="stat-value fw-bold text-success"
                                                            style="font-size: 0.85rem;">
                                                            {{ number_format($agent['totals_by_currency'][$currency]['paid'], 0) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div
                                                        class="stat-box text-center p-2 bg-danger bg-opacity-10 rounded-3">
                                                        <div class="stat-label text-danger fw-semibold mb-1"
                                                            style="font-size: 0.7rem;">المتبقي</div>
                                                        <div class="stat-value fw-bold text-danger"
                                                            style="font-size: 0.85rem;">
                                                            {{ number_format($agent['totals_by_currency'][$currency]['remaining'], 0) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- شريط التقدم -->
                                            @php
                                                $percentage =
                                                    $agent['totals_by_currency'][$currency]['due'] > 0
                                                        ? ($agent['totals_by_currency'][$currency]['paid'] /
                                                                $agent['totals_by_currency'][$currency]['due']) *
                                                            100
                                                        : 0;
                                            @endphp
                                            <div class="progress-section">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted fw-semibold">نسبة السداد</small>
                                                    <small
                                                        class="badge bg-{{ $config['color'] }} bg-opacity-25 text-{{ $config['color'] }}">
                                                        {{ number_format($percentage, 1) }}%
                                                    </small>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $config['color'] }} progress-bar-striped progress-bar-animated rounded-pill"
                                                        style="width: {{ $percentage }}%" role="progressbar"
                                                        aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <!-- آخر دفعة -->
                            @if ($agent['last_payment'])
                                <div class="last-payment-section mt-3 pt-3 border-top">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-history text-success me-2"></i>
                                            <div>
                                                <div class="fw-semibold text-success" style="font-size: 0.8rem;">آخر دفعة
                                                </div>
                                                <small class="text-muted">
                                                    {{ $agent['last_payment']->payment_date->format('d/m/Y') }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success" style="font-size: 0.9rem;">
                                                {{ number_format($agent['last_payment']->amount, 0) }}
                                            </div>
                                            <small class="text-muted">{{ $agent['last_payment']->currency }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- أسفل البطاقة -->
                        <div class="card-footer bg-white border-0 p-3">
                            <div class="d-grid gap-2 d-md-flex">
                                <a href="{{ route('admin.land-trips-agent-payments.show', $agent['id']) }}"
                                    class="btn btn-outline-primary btn-sm flex-fill d-flex align-items-center justify-content-center">
                                    <i class="fas fa-eye me-2"></i>
                                    <span>التفاصيل</span>
                                </a>
                                <a href="{{ route('admin.land-trips-agent-payments.create', $agent['id']) }}"
                                    class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center">
                                    <i class="fas fa-plus me-2"></i>
                                    <span>دفعة جديدة</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- الحالة الفارغة -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="empty-state-icon mb-4">
                                <i class="fas fa-handshake fa-4x text-muted opacity-50"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-3">لا توجد جهات حجز نشطة</h4>
                            <p class="text-muted mb-4 lead">
                                لم يتم العثور على وكلاء لديهم حجوزات رحلات برية نشطة في الوقت الحالي
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                                <a href="{{ route('admin.land-trips.index') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus me-2"></i>
                                    إضافة رحلة برية جديدة
                                </a>
                                <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-users me-2"></i>
                                    إدارة الوكلاء
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script>
        function exportToExcel() {
            const data = @json($agents);

            const exportData = data.map((agent, index) => ({
                '#': index + 1,
                'اسم الوكيل': agent.name,
                'عدد الحجوزات': agent.bookings_count,
                'المستحق ريال': agent.totals_by_currency.SAR.due,
                'المدفوع ريال': agent.totals_by_currency.SAR.paid,
                'المتبقي ريال': agent.totals_by_currency.SAR.remaining,
                'المستحق دينار': agent.totals_by_currency.KWD.due,
                'المدفوع دينار': agent.totals_by_currency.KWD.paid,
                'المتبقي دينار': agent.totals_by_currency.KWD.remaining,
            }));

            const ws = XLSX.utils.json_to_sheet(exportData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'مدفوعات وكلاء الرحلات البرية');

            const fileName = `مدفوعات-وكلاء-الرحلات-البرية-${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }
    </script>
    <!-- استدعاء الخلفية التفاعلية -->
    <script type="module">
        import {
            initParticlesBg
        } from '/js/particles-bg.js';
        initParticlesBg(); // يمكنك تمرير خيارات مثل {points:80, colors:[...]} إذا أردت
    </script>
@endpush
