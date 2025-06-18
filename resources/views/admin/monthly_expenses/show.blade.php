@extends('layouts.app')

@section('title', 'تفاصيل المصاريف الشهرية | ' . $expense->month_year)

@push('styles')
    <style>
        /* ========================================
                           متغيرات CSS العامة
                        ======================================== */
        :root {
            --primary-gradient: linear-gradient(120deg, #10b981 60%, #2563eb 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            --primary-gradient-reverse: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            --gray-gradient: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
            --border-radius-modern: 16px;
            --border-radius-lg: 20px;
            --shadow-modern: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ========================================
                           تصميم الحاوي الرئيسي
                        ======================================== */
        .modern-container {
            padding: 1rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        /* ========================================
                           هيدر الصفحة العصري
                        ======================================== */
        .modern-header {
            background: var(--primary-gradient);
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-modern);
        }

        .modern-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: headerFloat 8s ease-in-out infinite;
        }

        .modern-header::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -50%;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 60%);
            animation: headerFloat 10s ease-in-out infinite reverse;
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

        .modern-header .content {
            position: relative;
            z-index: 2;
        }

        .modern-header h1 {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            letter-spacing: -0.02em;
        }

        .modern-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .modern-header .action-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            transition: var(--transition-smooth);
            backdrop-filter: blur(10px);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .modern-header .action-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }

        /* ========================================
                           بطاقات عصرية
                        ======================================== */
        .modern-card {
            background: white;
            border-radius: var(--border-radius-modern);
            box-shadow: var(--shadow-modern);
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: var(--transition-smooth);
            margin-bottom: 2rem;
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .modern-card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .modern-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }

        .modern-card-header .content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .modern-card-header h6 {
            margin: 0;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modern-card-body {
            padding: 2rem;
        }

        /* ========================================
                           بطاقات العملات المميزة
                        ======================================== */
        .currency-card {
            border-radius: var(--border-radius-modern);
            overflow: hidden;
            box-shadow: var(--shadow-modern);
            transition: var(--transition-smooth);
            margin-bottom: 1.5rem;
        }

        .currency-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .currency-card-sar {
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .currency-card-kwd {
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .currency-header {
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .currency-header-sar {
            background: var(--success-gradient);
            color: white;
        }

        .currency-header-kwd {
            background: var(--primary-gradient-reverse);
            color: white;
        }

        .currency-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .currency-body {
            padding: 1.5rem;
            background: white;
        }

        /* ========================================
                           شارات العملة
                        ======================================== */
        .currency-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            backdrop-filter: blur(10px);
        }

        .currency-badge-sar {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
            border: 2px solid rgba(16, 185, 129, 0.3);
        }

        .currency-badge-kwd {
            background: rgba(37, 99, 235, 0.15);
            color: #1d4ed8;
            border: 2px solid rgba(37, 99, 235, 0.3);
        }

        /* ========================================
                           بطاقات الإحصائيات
                        ======================================== */
        .stats-card {
            background: white;
            border-radius: var(--border-radius-modern);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-modern);
            transition: var(--transition-smooth);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stats-card.revenue::before {
            background: var(--success-gradient);
        }

        .stats-card.expense::before {
            background: var(--danger-gradient);
        }

        .stats-card.profit::before {
            background: var(--primary-gradient);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 1rem 0;
            line-height: 1;
        }

        .stats-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ========================================
                           جدول عصري
                        ======================================== */
        .modern-table {
            background: white;
            border-radius: var(--border-radius-modern);
            overflow: hidden;
            box-shadow: var(--shadow-modern);
            border: none;
        }

        .modern-table thead {
            background: var(--primary-gradient);
            color: white;
        }

        .modern-table thead th {
            font-weight: 700;
            padding: 1.5rem 1rem;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.875rem;
        }

        .modern-table tbody td {
            padding: 1.25rem 1rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .modern-table tbody tr {
            transition: var(--transition-smooth);
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        /* ========================================
                           بطاقات توزيع الأرباح
                        ======================================== */
        .profit-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .partner-card {
            background: white;
            border-radius: var(--border-radius-modern);
            overflow: hidden;
            box-shadow: var(--shadow-modern);
            transition: var(--transition-smooth);
        }

        .partner-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .partner-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .partner-header::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        .partner-content {
            padding: 2rem;
            text-align: center;
        }

        .partner-amount {
            font-size: 2rem;
            font-weight: 800;
            margin: 1rem 0;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ========================================
                           تنبيه معلومات العملة
                        ======================================== */
        .currency-info-alert {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: var(--border-radius-modern);
            padding: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .currency-info-alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--warning-gradient);
        }

        /* ========================================
                           أزرار التحكم العصرية
                        ======================================== */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .modern-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition-smooth);
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }

        .btn-edit {
            background: var(--warning-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-edit:hover {
            color: white;
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        .btn-back {
            background: var(--gray-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }

        .btn-back:hover {
            color: white;
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }

        /* ========================================
                           أيقونات متحركة
                        ======================================== */
        .icon-animated {
            display: inline-block;
            transition: var(--transition-smooth);
        }

        .icon-animated:hover {
            transform: scale(1.2) rotate(5deg);
        }

        /* ========================================
                           تأثيرات التحميل
                        ======================================== */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .slide-up {
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========================================
                           تحسينات للشاشات الصغيرة
                        ======================================== */
        @media (max-width: 768px) {
            .modern-header {
                padding: 2rem 1.5rem;
                text-align: center;
            }

            .modern-header h1 {
                font-size: 2rem;
            }

            .modern-card-body {
                padding: 1.5rem;
            }

            .profit-distribution {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .modern-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* ========================================
                           تحسينات إضافية
                        ======================================== */
        .text-gradient {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }

        .bg-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.15) 1px, transparent 0);
            background-size: 20px 20px;
        }

        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-elements::before,
        .floating-elements::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, rgba(16, 185, 129, 0.1), rgba(37, 99, 235, 0.1));
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .floating-elements::before {
            top: 10%;
            left: -5%;
            animation-delay: 0s;
        }

        .floating-elements::after {
            bottom: 10%;
            right: -5%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        .logs-filters {
            background: linear-gradient(120deg, #f8fafc 0%, #edf2f7 100%);
        }

        .log-row {
            transition: all 0.3s ease;
        }

        .log-row:hover {
            background-color: #f8f9fa !important;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .datetime-display,
        .user-info,
        .field-info,
        .value-display {
            transition: all 0.2s ease;
        }

        .value-content {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            border-left: 3px solid currentColor;
            position: relative;
            overflow: hidden;
        }

        .old-value .value-content {
            border-left-color: #dc3545;
        }

        .new-value .value-content {
            border-left-color: #198754;
        }

        .stat-item {
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-3px);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
        }

        /* تحسين عرض الجدول على الشاشات الصغيرة */
        @media (max-width: 768px) {
            .logs-filters .row>div {
                margin-bottom: 0.5rem;
            }

            .modern-table {
                font-size: 0.8rem;
            }

            .value-content {
                font-size: 0.75rem;
                padding: 0.5rem !important;
            }
        }

        /* تثبيت الـ modal في مكان صحيح */
        .modal {
            z-index: 9999 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: rgba(0, 0, 0, 0.5) !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
        }

        .modal-dialog {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            margin: 0 !important;
            max-width: 600px !important;
            width: 90% !important;
            z-index: 10000 !important;

        }

        .modal-content {
            position: relative !important;
            z-index: 10001 !important;
            border: none !important;
            border-radius: 15px !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4) !important;
            overflow: hidden !important;
            max-height: 90vh !important;
        }

        /* منع اختفاء الـ modal عند الـ hover */
        .modal.fade.show {
            display: block !important;
            opacity: 1 !important;
            pointer-events: all !important;
        }

   


        .modal-backdrop.show {
            opacity: 0.5 !important;
            pointer-events: all !important;
        }

        /* تحسين تصميم header الـ modal */
        .modal-header {
            padding: 1.5rem !important;
            border-bottom: none !important;
            position: relative !important;
        }

        .modal-header.bg-success {
            background: var(--success-gradient) !important;
        }

        .modal-header.bg-warning {
            background: var(--warning-gradient) !important;
        }

        .modal-header.bg-danger {
            background: var(--danger-gradient) !important;
        }

        .modal-body {
            padding: 2rem !important;
            max-height: 70vh !important;
            overflow-y: auto !important;
        }

        /* تحسين أزرار الـ modal */
        .btn-close-white {
            filter: brightness(0) invert(1) !important;
            opacity: 0.8 !important;
        }

        .btn-close-white:hover {
            opacity: 1 !important;
        }

        /* إصلاح مشكلة الـ hover على الجدول */
        .log-row {
            transition: all 0.3s ease !important;
            position: relative !important;
        }

        .log-row:hover {
            background-color: #f8f9fa !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            z-index: 1 !important;
        }

        /* منع التداخل مع العناصر الأخرى */
        .table-responsive {
            position: relative !important;
            z-index: 1 !important;
        }

        /* تحسين زر عرض التفاصيل */
        .btn-outline-info {
            border: 2px solid #17a2b8 !important;
            color: #17a2b8 !important;
            background: white !important;
            transition: all 0.3s ease !important;
            z-index: 10 !important;
            position: relative !important;
        }

        .btn-outline-info:hover {
            background: #17a2b8 !important;
            color: white !important;
            border-color: #17a2b8 !important;
            transform: scale(1.1) !important;
        }

        /* إصلاح مشكلة الـ tooltip interference */
        .btn-outline-info:focus,
        .btn-outline-info:active {
            outline: none !important;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25) !important;
        }
    </style>
@endpush

@section('content')
    <div class="modern-container">
        <!-- عناصر عائمة للتصميم -->
        <div class="floating-elements"></div>

        <!-- هيدر الصفحة العصري -->
        <div class="modern-header fade-in">
            <div class="content">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1>
                            <i class="fas fa-chart-line icon-animated me-3"></i>
                            تفاصيل المصاريف الشهرية
                        </h1>
                        <p class="subtitle mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            فترة {{ $expense->month_year }} | من {{ $expense->start_date->format('d/m/Y') }} إلى
                            {{ $expense->end_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('admin.monthly-expenses.edit', $expense->id) }}" class="action-btn me-2 mb-2">
                            <i class="fas fa-edit"></i>
                            تعديل التقرير
                        </a>
                        <a href="{{ route('admin.monthly-expenses.index') }}" class="action-btn me-2 mb-2">
                            <i class="fas fa-arrow-right"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- تنبيه معلومات العملة -->
        @if ($expense->unified_currency)
            <div class="currency-info-alert slide-up">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exchange-alt text-warning me-3 fs-3"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">معلومات تحويل العملة</h6>
                        <p class="mb-1">تم توحيد الحسابات بعملة:
                            <strong
                                class="text-gradient">{{ $expense->unified_currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي' }}</strong>
                        </p>
                        @if ($expense->exchange_rate)
                            <p class="mb-0 small">سعر الصرف المستخدم: <strong>1 دينار =
                                    {{ number_format($expense->exchange_rate, 2) }} ريال</strong></p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- بطاقة البيانات الأساسية -->
        <div class="modern-card slide-up">
            <div class="modern-card-header">
                <div class="content">
                    <h6>
                        <i class="fas fa-info-circle icon-animated"></i>
                        البيانات الأساسية والإحصائيات
                    </h6>
                    <span class="badge bg-light text-dark">{{ $expense->created_at->format('Y-m-d') }}</span>
                </div>
            </div>
            <div class="modern-card-body">
                @php
                    $totalExpensesSAR = 0;
                    $totalExpensesKWD = 0;
                    $currencies = $expense->expenses_currencies ?? [];

                    // حساب المصاريف بالريال
                    if (isset($currencies['salaries']) && $currencies['salaries'] === 'SAR') {
                        $totalExpensesSAR += $expense->salaries;
                    }
                    if (isset($currencies['advertising']) && $currencies['advertising'] === 'SAR') {
                        $totalExpensesSAR += $expense->advertising;
                    }
                    if (isset($currencies['rent']) && $currencies['rent'] === 'SAR') {
                        $totalExpensesSAR += $expense->rent;
                    }
                    if (isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'SAR') {
                        $totalExpensesSAR += $expense->staff_commissions;
                    }

                    // حساب المصاريف بالدينار
                    if (isset($currencies['salaries']) && $currencies['salaries'] === 'KWD') {
                        $totalExpensesKWD += $expense->salaries;
                    }
                    if (isset($currencies['advertising']) && $currencies['advertising'] === 'KWD') {
                        $totalExpensesKWD += $expense->advertising;
                    }
                    if (isset($currencies['rent']) && $currencies['rent'] === 'KWD') {
                        $totalExpensesKWD += $expense->rent;
                    }
                    if (isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'KWD') {
                        $totalExpensesKWD += $expense->staff_commissions;
                    }

                    // إضافة المصاريف الأخرى
                    if (!empty($expense->other_expenses)) {
                        foreach ($expense->other_expenses as $otherExpense) {
                            if (isset($otherExpense['currency'])) {
                                if ($otherExpense['currency'] === 'SAR') {
                                    $totalExpensesSAR += $otherExpense['amount'];
                                } elseif ($otherExpense['currency'] === 'KWD') {
                                    $totalExpensesKWD += $otherExpense['amount'];
                                }
                            }
                        }
                    }
                @endphp

                <!-- بطاقات الإحصائيات الرئيسية -->
                <div class="row mb-5">
                    @if ($expense->total_monthly_profit_SAR > 0)
                        <div class="col-md-4 mb-3">
                            <div class="stats-card revenue">
                                <i class="fas fa-arrow-trend-up text-success fs-2 mb-2"></i>
                                <div class="stats-number text-success">
                                    {{ number_format($expense->total_monthly_profit_SAR, 0) }}</div>
                                <div class="stats-label">إجمالي الإيرادات (ريال)</div>
                            </div>
                        </div>
                    @endif

                    @if ($totalExpensesSAR > 0)
                        <div class="col-md-4 mb-3">
                            <div class="stats-card expense">
                                <i class="fas fa-arrow-trend-down text-danger fs-2 mb-2"></i>
                                <div class="stats-number text-danger">{{ number_format($totalExpensesSAR, 0) }}</div>
                                <div class="stats-label">إجمالي المصاريف (ريال)</div>
                            </div>
                        </div>
                    @endif

                    @if ($expense->net_profit_SAR > 0)
                        <div class="col-md-4 mb-3">
                            <div class="stats-card profit">
                                <i class="fas fa-trophy text-primary fs-2 mb-2"></i>
                                <div class="stats-number text-gradient">{{ number_format($expense->net_profit_SAR, 0) }}
                                </div>
                                <div class="stats-label">صافي الربح (ريال)</div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- بطاقات الإحصائيات للدينار -->
                @if ($expense->total_monthly_profit_KWD > 0 || $totalExpensesKWD > 0)
                    <div class="row mb-5">
                        @if ($expense->total_monthly_profit_KWD > 0)
                            <div class="col-md-4 mb-3">
                                <div class="stats-card revenue">
                                    <i class="fas fa-arrow-trend-up text-primary fs-2 mb-2"></i>
                                    <div class="stats-number text-primary">
                                        {{ number_format($expense->total_monthly_profit_KWD, 0) }}</div>
                                    <div class="stats-label">إجمالي الإيرادات (دينار)</div>
                                </div>
                            </div>
                        @endif

                        @if ($totalExpensesKWD > 0)
                            <div class="col-md-4 mb-3">
                                <div class="stats-card expense">
                                    <i class="fas fa-arrow-trend-down text-warning fs-2 mb-2"></i>
                                    <div class="stats-number text-warning">{{ number_format($totalExpensesKWD, 0) }}</div>
                                    <div class="stats-label">إجمالي المصاريف (دينار)</div>
                                </div>
                            </div>
                        @endif

                        @if ($expense->net_profit_KWD > 0)
                            <div class="col-md-4 mb-3">
                                <div class="stats-card profit">
                                    <i class="fas fa-trophy text-primary fs-2 mb-2"></i>
                                    <div class="stats-number text-gradient">
                                        {{ number_format($expense->net_profit_KWD, 0) }}</div>
                                    <div class="stats-label">صافي الربح (دينار)</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- جدول تفاصيل المصاريف -->
        <div class="modern-card slide-up">
            <div class="modern-card-header">
                <div class="content">
                    <h6>
                        <i class="fas fa-list-alt icon-animated"></i>
                        تفاصيل المصاريف
                    </h6>
                </div>
            </div>
            <div class="modern-card-body p-0">
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-tag me-2"></i>البند</th>
                                <th><i class="fas fa-money-bill-wave me-2"></i>المبلغ</th>
                                <th><i class="fas fa-coins me-2"></i>العملة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fas fa-users text-primary me-2"></i>إجمالي الرواتب</td>
                                <td class="fw-bold">{{ number_format($expense->salaries, 2) }}</td>
                                <td>
                                    <span
                                        class="currency-badge {{ isset($currencies['salaries']) && $currencies['salaries'] === 'SAR' ? 'currency-badge-sar' : 'currency-badge-kwd' }}">
                                        <i
                                            class="fas fa-{{ isset($currencies['salaries']) && $currencies['salaries'] === 'SAR' ? 'money-bill' : 'coins' }}"></i>
                                        {{ isset($currencies['salaries']) && $currencies['salaries'] === 'SAR' ? 'ريال' : 'دينار' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-megaphone text-warning me-2"></i>إجمالي الإعلانات</td>
                                <td class="fw-bold">{{ number_format($expense->advertising, 2) }}</td>
                                <td>
                                    <span
                                        class="currency-badge {{ isset($currencies['advertising']) && $currencies['advertising'] === 'SAR' ? 'currency-badge-sar' : 'currency-badge-kwd' }}">
                                        <i
                                            class="fas fa-{{ isset($currencies['advertising']) && $currencies['advertising'] === 'SAR' ? 'money-bill' : 'coins' }}"></i>
                                        {{ isset($currencies['advertising']) && $currencies['advertising'] === 'SAR' ? 'ريال' : 'دينار' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-home text-info me-2"></i>الإيجار</td>
                                <td class="fw-bold">{{ number_format($expense->rent, 2) }}</td>
                                <td>
                                    <span
                                        class="currency-badge {{ isset($currencies['rent']) && $currencies['rent'] === 'SAR' ? 'currency-badge-sar' : 'currency-badge-kwd' }}">
                                        <i
                                            class="fas fa-{{ isset($currencies['rent']) && $currencies['rent'] === 'SAR' ? 'money-bill' : 'coins' }}"></i>
                                        {{ isset($currencies['rent']) && $currencies['rent'] === 'SAR' ? 'ريال' : 'دينار' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-percentage text-success me-2"></i>عمولات الموظفين</td>
                                <td class="fw-bold">{{ number_format($expense->staff_commissions, 2) }}</td>
                                <td>
                                    <span
                                        class="currency-badge {{ isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'SAR' ? 'currency-badge-sar' : 'currency-badge-kwd' }}">
                                        <i
                                            class="fas fa-{{ isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'SAR' ? 'money-bill' : 'coins' }}"></i>
                                        {{ isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'SAR' ? 'ريال' : 'دينار' }}
                                    </span>
                                </td>
                            </tr>

                            @if (!empty($expense->other_expenses))
                                @foreach ($expense->other_expenses as $otherExpense)
                                    <tr>
                                        <td><i
                                                class="fas fa-plus-circle text-secondary me-2"></i>{{ $otherExpense['name'] }}
                                        </td>
                                        <td class="fw-bold">{{ number_format($otherExpense['amount'], 2) }}</td>
                                        <td>
                                            <span
                                                class="currency-badge {{ isset($otherExpense['currency']) && $otherExpense['currency'] === 'SAR' ? 'currency-badge-sar' : 'currency-badge-kwd' }}">
                                                <i
                                                    class="fas fa-{{ isset($otherExpense['currency']) && $otherExpense['currency'] === 'SAR' ? 'money-bill' : 'coins' }}"></i>
                                                {{ isset($otherExpense['currency']) && $otherExpense['currency'] === 'SAR' ? 'ريال' : 'دينار' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- توزيع الأرباح -->
        <div class="modern-card slide-up">
            <div class="modern-card-header">
                <div class="content">
                    <h6>
                        <i class="fas fa-handshake icon-animated"></i>
                        توزيع الأرباح على الشركاء
                    </h6>
                </div>
            </div>
            <div class="modern-card-body">
                <div class="profit-distribution">
                    @if ($expense->total_monthly_profit_SAR > 0)
                        <!-- الأرباح بالريال السعودي -->
                        <div class="currency-card currency-card-sar">
                            <div class="currency-header currency-header-sar">
                                <h6 class="m-0 fw-bold d-flex align-items-center justify-content-between">
                                    <span><i class="fas fa-money-bill-wave me-2"></i>الأرباح بالريال السعودي</span>
                                    <span class="badge bg-light text-success">SAR</span>
                                </h6>
                            </div>
                            <div class="currency-body">
                                <!-- ملخص الأرباح -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div
                                            class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                            <span class="fw-bold"><i class="fas fa-arrow-up text-success me-1"></i>إجمالي
                                                الربح:</span>
                                            <span
                                                class="text-success fw-bold">{{ number_format($expense->total_monthly_profit_SAR, 2) }}
                                                ريال</span>
                                        </div>
                                        <div
                                            class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                            <span class="fw-bold"><i class="fas fa-arrow-down text-danger me-1"></i>إجمالي
                                                المصاريف:</span>
                                            <span class="text-danger fw-bold">{{ number_format($totalExpensesSAR, 2) }}
                                                ريال</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-5"><i class="fas fa-equals text-primary me-1"></i>صافي
                                                الربح:</span>
                                            <span
                                                class="text-gradient fw-bold fs-4">{{ number_format($expense->net_profit_SAR, 2) }}
                                                ريال</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- توزيع الأرباح -->
                                <div class="row">
                                    <div class="col-6">
                                        <div class="partner-card">
                                            <div class="partner-header">
                                                <i class="fas fa-user-tie fs-3 mb-2"></i>
                                                <h6 class="mb-0">ش. إسماعيل</h6>
                                            </div>
                                            <div class="partner-content">
                                                <div class="partner-amount">
                                                    {{ number_format($expense->ismail_share_SAR, 2) }}</div>
                                                <small class="text-muted">ريال سعودي</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="partner-card">
                                            <div class="partner-header">
                                                <i class="fas fa-user-tie fs-3 mb-2"></i>
                                                <h6 class="mb-0">ش. محمد حسن</h6>
                                            </div>
                                            <div class="partner-content">
                                                <div class="partner-amount">
                                                    {{ number_format($expense->mohamed_share_SAR, 2) }}</div>
                                                <small class="text-muted">ريال سعودي</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($expense->total_monthly_profit_KWD > 0)
                        <!-- الأرباح بالدينار الكويتي -->
                        <div class="currency-card currency-card-kwd">
                            <div class="currency-header currency-header-kwd">
                                <h6 class="m-0 fw-bold d-flex align-items-center justify-content-between">
                                    <span><i class="fas fa-coins me-2"></i>الأرباح بالدينار الكويتي</span>
                                    <span class="badge bg-light text-primary">KWD</span>
                                </h6>
                            </div>
                            <div class="currency-body">
                                <!-- ملخص الأرباح -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div
                                            class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                            <span class="fw-bold"><i class="fas fa-arrow-up text-primary me-1"></i>إجمالي
                                                الربح:</span>
                                            <span
                                                class="text-primary fw-bold">{{ number_format($expense->total_monthly_profit_KWD, 2) }}
                                                دينار</span>
                                        </div>
                                        <div
                                            class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                            <span class="fw-bold"><i
                                                    class="fas fa-arrow-down text-warning me-1"></i>إجمالي المصاريف:</span>
                                            <span class="text-warning fw-bold">{{ number_format($totalExpensesKWD, 2) }}
                                                دينار</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-5"><i class="fas fa-equals text-primary me-1"></i>صافي
                                                الربح:</span>
                                            <span
                                                class="text-gradient fw-bold fs-4">{{ number_format($expense->net_profit_KWD, 2) }}
                                                دينار</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- توزيع الأرباح -->
                                <div class="row">
                                    <div class="col-6">
                                        <div class="partner-card">
                                            <div class="partner-header">
                                                <i class="fas fa-user-tie fs-3 mb-2"></i>
                                                <h6 class="mb-0">ش. إسماعيل</h6>
                                            </div>
                                            <div class="partner-content">
                                                <div class="partner-amount">
                                                    {{ number_format($expense->ismail_share_KWD, 2) }}</div>
                                                <small class="text-muted">دينار كويتي</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="partner-card">
                                            <div class="partner-header">
                                                <i class="fas fa-user-tie fs-3 mb-2"></i>
                                                <h6 class="mb-0">ش. محمد حسن</h6>
                                            </div>
                                            <div class="partner-content">
                                                <div class="partner-amount">
                                                    {{ number_format($expense->mohamed_share_KWD, 2) }}</div>
                                                <small class="text-muted">دينار كويتي</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- الملاحظات -->
        @if ($expense->notes)
            <div class="modern-card slide-up">
                <div class="modern-card-header">
                    <div class="content">
                        <h6>
                            <i class="fas fa-sticky-note icon-animated"></i>
                            الملاحظات
                        </h6>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="alert alert-info border-0"
                        style="background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ $expense->notes }}
                    </div>
                </div>
            </div>
        @endif

        <!-- أزرار التحكم -->
        <div class="action-buttons">
            <a href="{{ route('admin.monthly-expenses.edit', $expense->id) }}" class="modern-btn btn-edit">
                <i class="fas fa-edit"></i>
                تعديل التقرير
            </a>
            <a href="{{ route('admin.monthly-expenses.index') }}" class="modern-btn btn-back">
                <i class="fas fa-arrow-right"></i>
                العودة للقائمة
            </a>
        </div>
    </div>
    <!-- 📊 قسم سجل التعديلات الجديد -->
    @if ($expense->logs()->count() > 0)
        <div class="modern-card slide-up">
            <div class="modern-card-header">
                <div class="content">
                    <h6>
                        <i class="fas fa-history icon-animated"></i>
                        سجل التعديلات والتغييرات
                        <span class="badge bg-light text-dark ms-2">{{ $expense->logs()->count() }} تعديل</span>
                    </h6>
                </div>
            </div>
            <div class="modern-card-body p-0">
                <!-- فلاتر سجل التعديلات -->
                <div class="logs-filters p-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="actionTypeFilter">
                                <option value="">جميع العمليات</option>
                                <option value="created">إنشاء</option>
                                <option value="updated">تعديل</option>
                                <option value="deleted">حذف</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="userFilter">
                                <option value="">جميع المستخدمين</option>
                                @foreach ($expense->logs()->with('user')->get()->unique('user_id') as $log)
                                    <option value="{{ $log->user_id }}">{{ $log->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="date" class="form-control form-control-sm" id="dateFilter"
                                placeholder="تاريخ التعديل">
                        </div>
                    </div>
                </div>

                <!-- جدول سجل التعديلات -->
                <div class="table-responsive">
                    <table class="table modern-table mb-0" id="logsTable">
                        <thead>
                            <tr>
                                <th width="8%">
                                    <i class="fas fa-hashtag me-1"></i>
                                    الرقم
                                </th>
                                <th width="12%">
                                    <i class="fas fa-clock me-1"></i>
                                    التاريخ والوقت
                                </th>
                                <th width="12%">
                                    <i class="fas fa-user me-1"></i>
                                    المستخدم
                                </th>
                                <th width="10%">
                                    <i class="fas fa-cog me-1"></i>
                                    نوع العملية
                                </th>
                                <th width="15%">
                                    <i class="fas fa-tag me-1"></i>
                                    الحقل المُعدَّل
                                </th>
                                <th width="20%">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    القيمة القديمة
                                </th>
                                <th width="20%">
                                    <i class="fas fa-arrow-right me-1"></i>
                                    القيمة الجديدة
                                </th>
                                <th width="3%">
                                    <i class="fas fa-info-circle me-1"></i>
                                    تفاصيل
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expense->logs()->with('user')->latest()->get() as $index => $log)
                                <tr class="log-row" data-action="{{ $log->action_type }}"
                                    data-user="{{ $log->user_id }}" data-date="{{ $log->created_at->format('Y-m-d') }}">

                                    <!-- الرقم التسلسلي -->
                                    <td>
                                        <span class="badge bg-{{ $log->change_color }} rounded-pill">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>

                                    <!-- التاريخ والوقت -->
                                    <td>
                                        <div class="datetime-display">
                                            <div class="fw-bold text-dark">
                                                <i class="fas fa-calendar-day me-1 text-primary"></i>
                                                {{ $log->created_at->format('Y/m/d') }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $log->created_at->format('h:i A') }}
                                                <span class="ms-1">
                                                    ({{ $log->created_at->diffForHumans() }})
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- المستخدم -->
                                    <td>
                                        <div class="user-info">
                                            <div class="fw-bold text-dark">
                                                <i class="fas fa-user-circle me-1 text-success"></i>
                                                {{ $log->user->name }}
                                            </div>
                                            @if ($log->ip_address)
                                                <div class="small text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $log->ip_address }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- نوع العملية -->
                                    <td>
                                        <span class="badge bg-{{ $log->change_color }} px-3 py-2">
                                            <i class="{{ $log->change_icon }} me-1"></i>
                                            {{ $log->action_type_display }}
                                        </span>
                                    </td>

                                    <!-- الحقل المُعدَّل -->
                                    <td>
                                        <div class="field-info">
                                            <div class="fw-bold text-dark">{{ $log->field_label }}</div>
                                            <div class="small text-muted">
                                                <code>{{ $log->field_name }}</code>
                                            </div>
                                            @if ($log->currency)
                                                <span class="badge bg-info bg-opacity-10 text-info mt-1">
                                                    <i class="fas fa-coins me-1"></i>
                                                    {{ $log->currency }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- القيمة القديمة -->
                                    <td>
                                        <div class="value-display old-value">
                                            @if ($log->old_value)
                                                <div class="value-content bg-danger bg-opacity-10 text-danger rounded p-2">
                                                    <i class="fas fa-arrow-left me-1"></i>
                                                    {{ $log->formatted_old_value }}
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus me-1"></i>
                                                    لا توجد قيمة سابقة
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- القيمة الجديدة -->
                                    <td>
                                        <div class="value-display new-value">
                                            @if ($log->new_value)
                                                <div
                                                    class="value-content bg-success bg-opacity-10 text-success rounded p-2">
                                                    <i class="fas fa-arrow-right me-1"></i>
                                                    {{ $log->formatted_new_value }}
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus me-1"></i>
                                                    تم الحذف
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- تفاصيل إضافية -->
                                    <td>
                                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#logDetailsModal{{ $log->id }}" title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Modal تفاصيل السجل -->
                                        <div class="modal fade" id="logDetailsModal{{ $log->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-{{ $log->change_color }} text-white">
                                                        <h5 class="modal-title">
                                                            <i class="{{ $log->change_icon }} me-2"></i>
                                                            تفاصيل {{ $log->action_type_display }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>الحقل:</strong><br>
                                                                {{ $log->field_label }}
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>التاريخ:</strong><br>
                                                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        @if ($log->old_value)
                                                            <div class="mb-3">
                                                                <strong>القيمة القديمة:</strong><br>
                                                                <div
                                                                    class="bg-danger bg-opacity-10 text-danger rounded p-2">
                                                                    {{ $log->formatted_old_value }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if ($log->new_value)
                                                            <div class="mb-3">
                                                                <strong>القيمة الجديدة:</strong><br>
                                                                <div
                                                                    class="bg-success bg-opacity-10 text-success rounded p-2">
                                                                    {{ $log->formatted_new_value }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if ($log->notes)
                                                            <div class="mb-3">
                                                                <strong>ملاحظات:</strong><br>
                                                                {{ $log->notes }}
                                                            </div>
                                                        @endif
                                                        @if ($log->user_agent)
                                                            <div class="small text-muted">
                                                                <strong>معلومات الجهاز:</strong><br>
                                                                {{ $log->user_agent }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- إحصائيات سجل التعديلات -->
                <div class="logs-stats p-3 border-top bg-light">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-primary">{{ $expense->logs()->count() }}</div>
                                <div class="stat-label small">إجمالي التعديلات</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-success">
                                    {{ $expense->logs()->where('action_type', 'created')->count() }}</div>
                                <div class="stat-label small">عمليات إنشاء</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-warning">
                                    {{ $expense->logs()->where('action_type', 'updated')->count() }}</div>
                                <div class="stat-label small">عمليات تعديل</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value text-danger">
                                    {{ $expense->logs()->where('action_type', 'deleted')->count() }}</div>
                                <div class="stat-label small">عمليات حذف</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 🔍 تصفية سجل التعديلات
            const actionTypeFilter = document.getElementById('actionTypeFilter');
            const userFilter = document.getElementById('userFilter');
            const dateFilter = document.getElementById('dateFilter');
            const logRows = document.querySelectorAll('.log-row');

            function filterLogs() {
                const selectedAction = actionTypeFilter.value;
                const selectedUser = userFilter.value;
                const selectedDate = dateFilter.value;

                logRows.forEach(row => {
                    let showRow = true;

                    // فلترة حسب نوع العملية
                    if (selectedAction && row.dataset.action !== selectedAction) {
                        showRow = false;
                    }

                    // فلترة حسب المستخدم
                    if (selectedUser && row.dataset.user !== selectedUser) {
                        showRow = false;
                    }

                    // فلترة حسب التاريخ
                    if (selectedDate && row.dataset.date !== selectedDate) {
                        showRow = false;
                    }

                    // إظهار أو إخفاء الصف
                    row.style.display = showRow ? '' : 'none';
                });

                // تحديث عدد النتائج المعروضة
                const visibleRows = Array.from(logRows).filter(row => row.style.display !== 'none').length;
                console.log(`عرض ${visibleRows} من أصل ${logRows.length} سجل`);
            }

            // ربط أحداث التصفية
            actionTypeFilter.addEventListener('change', filterLogs);
            userFilter.addEventListener('change', filterLogs);
            dateFilter.addEventListener('change', filterLogs);

            // 🎨 تأثيرات بصرية لسجل التعديلات
            logRows.forEach((row, index) => {
                // إضافة تأثير الظهور التدريجي
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';

                setTimeout(() => {
                    row.style.transition = 'all 0.4s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, index * 100);

                // تأثير التحويم
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.transform = 'scale(1.02)';
                });

                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                    this.style.transform = 'scale(1)';
                });
            });

            // 📊 رسم بياني لإحصائيات التعديلات (اختياري)
            const statsValues = document.querySelectorAll('.stat-value');
            statsValues.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = finalValue / 50;

                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(counter);
                    }
                    stat.textContent = Math.floor(currentValue);
                }, 30);
            });

            console.log('🔧 تم تحميل نظام سجل التعديلات بنجاح!');
        });

        // إضافة تأثيرات التحميل التدريجي
        const cards = document.querySelectorAll('.modern-card, .stats-card, .partner-card');

        // إظهار البطاقات تدريجياً
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';

            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // تأثيرات تفاعلية للأرقام
        const numbers = document.querySelectorAll('.stats-number, .partner-amount');
        numbers.forEach(number => {
            const finalValue = parseFloat(number.textContent.replace(/[^\d.-]/g, ''));
            let currentValue = 0;
            const increment = finalValue / 100;

            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    currentValue = finalValue;
                    clearInterval(timer);
                }

                if (number.classList.contains('partner-amount')) {
                    number.textContent = currentValue.toFixed(2);
                } else {
                    number.textContent = Math.floor(currentValue).toLocaleString();
                }
            }, 20);
        });

        // تأثيرات تحويم للبطاقات
        document.querySelectorAll('.stats-card, .partner-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        console.log('🎉 تم تحميل صفحة تفاصيل المصاريف بنجاح!');
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 🔧 إصلاح مشاكل الـ Modal

            // منع إغلاق الـ modal عند النقر داخله
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        // فقط عند النقر على الخلفية
                        const modalInstance = bootstrap.Modal.getInstance(this);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }
                });

                // منع الإغلاق عند النقر على محتوى الـ modal
                modal.querySelector('.modal-content').addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            // 🔧 تحسين تأثيرات الـ hover للجدول
            const logRows = document.querySelectorAll('.log-row');

            logRows.forEach((row, index) => {
                // إزالة التأثيرات القديمة وإضافة جديدة محسنة
                row.addEventListener('mouseenter', function(e) {
                    // منع التداخل مع العناصر الأخرى
                    this.style.zIndex = '10';
                    this.style.position = 'relative';
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.transform = 'translateY(-1px)';
                    this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';

                    // إضافة تأثير للخلايا
                    const cells = this.querySelectorAll('td');
                    cells.forEach(cell => {
                        cell.style.borderColor = '#dee2e6';
                    });
                });

                row.addEventListener('mouseleave', function(e) {
                    // إعادة تعيين الستايل
                    this.style.zIndex = '1';
                    this.style.backgroundColor = '';
                    this.style.transform = '';
                    this.style.boxShadow = '';

                    const cells = this.querySelectorAll('td');
                    cells.forEach(cell => {
                        cell.style.borderColor = '';
                    });
                });
            });

            // 🔧 تحسين أزرار عرض التفاصيل
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const targetModal = document.querySelector(this.getAttribute('data-bs-target'));
                    if (targetModal) {
                        // إنشاء instance جديد للـ modal
                        const modal = new bootstrap.Modal(targetModal, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });

                        // عرض الـ modal
                        modal.show();

                        // تأكيد أن الـ modal في المقدمة
                        setTimeout(() => {
                            targetModal.style.zIndex = '1055';
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.style.zIndex = '1050';
                            }
                        }, 100);
                    }
                });
            });

            // 🔧 إصلاح مشكلة اختفاء الـ modal
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    // تثبيت الـ modal في المقدمة
                    this.style.display = 'block';
                    this.style.zIndex = '1055';
                    this.style.opacity = '1';

                    // تثبيت الـ backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.zIndex = '1050';
                        backdrop.style.opacity = '0.5';
                    }

                    // منع scroll على الـ body
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '15px';
                });

                modal.addEventListener('hidden.bs.modal', function() {
                    // إعادة تعيين الـ body
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                });
            });

            // 🔍 باقي كود التصفية...
            // (الكود الموجود بالفعل)

            console.log('🔧 تم إصلاح مشاكل الـ Modal والـ positioning بنجاح!');
        });
    </script>
@endpush
