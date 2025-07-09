@extends('layouts.app')

@section('title', 'تفاصيل مدفوعات الوكيل ' . $agent->name . ' - الرحلات البرية')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #2653eb 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
            --danger-gradient: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);

            --glass-effect: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.15);
            --shadow-hover: 0 15px 35px rgba(31, 38, 135, 0.25);

            --border-radius: 20px;
            --border-radius-sm: 12px;
            --border-radius-lg: 24px;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Inter', 'Cairo', sans-serif;
        }

        /* الحاوي الرئيسي */
        .payments-container {
            background: transparent;
            min-height: 100vh;
            padding: 2rem;
        }

        /* رأس الصفحة */
        .page-header-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
        }

        .page-title-main {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .page-title-icon {
            background: var(--primary-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-left: 1rem;
        }

        .breadcrumb-nav {
            background: transparent;
            margin-bottom: 0;
            padding: 0;
        }

        .breadcrumb-nav .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            color: #6c757d;
            font-weight: 600;
        }

        /* أزرار الإجراءات */
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            border: 2px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern.btn-success {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .btn-modern.btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        .btn-modern.btn-warning {
            background: var(--warning-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(252, 74, 26, 0.3);
        }

        .btn-modern.btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(252, 74, 26, 0.4);
        }

        .btn-modern.btn-outline-primary {
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            border-color: #667eea;
        }

        .btn-modern.btn-outline-primary:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateY(-2px);
        }

        /* قسم الإحصائيات */
        .stats-section {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-light);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-card.currency-sar::before {
            background: var(--success-gradient);
        }

        .stat-card.currency-kwd::before {
            background: var(--warning-gradient);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .stat-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        .currency-indicator {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .currency-indicator.sar {
            background: var(--success-gradient);
            color: white;
        }

        .currency-indicator.kwd {
            background: var(--warning-gradient);
            color: white;
        }

        .stat-rows {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .stat-label {
            color: #64748b;
            font-weight: 500;
        }

        .stat-value {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2d3748;
        }

        .stat-value.success {
            color: #059669;
        }

        .stat-value.warning {
            color: #d97706;
        }

        .stat-value.danger {
            color: #dc2626;
        }

        /* شريط التقدم */
        .progress-container {
            margin-top: 1.5rem;
        }

        .progress-bar-modern {
            width: 100%;
            height: 8px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: var(--success-gradient);
            border-radius: 10px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .progress-text {
            font-size: 0.875rem;
            color: #64748b;
            text-align: center;
        }

        /* قسم المدفوعات */
        .payments-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-light);
            overflow: hidden;
        }

        .payments-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .payments-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .payments-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* عنصر الدفعة */
        .payment-entry {
            padding: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .payment-entry:hover {
            background: rgba(102, 126, 234, 0.02);
        }

        .payment-entry:last-child {
            border-bottom: none;
        }

        .payment-entry.discount-payment {
            background: rgba(252, 74, 26, 0.03);
            border-left: 4px solid #fc4a1a;
        }

        .payment-layout {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 2rem;
            align-items: start;
        }

        .payment-amount-section {
            text-align: center;
            padding: 1rem;
            background: var(--success-gradient);
            color: white;
            border-radius: var(--border-radius);
            min-width: 120px;
        }

        .payment-amount-section.discount-payment {
            background: var(--warning-gradient);
        }

        .amount-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }

        .amount-currency {
            font-size: 0.875rem;
            margin: 0;
            opacity: 0.9;
        }

        .payment-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .detail-icon {
            color: #667eea;
            font-size: 1.1rem;
            margin-top: 0.25rem;
            min-width: 20px;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 0.25rem 0;
            font-weight: 600;
        }

        .detail-value {
            font-size: 0.9rem;
            color: #2d3748;
            font-weight: 500;
            margin: 0;
        }

        .receipt-image-container {
            margin-top: 0.5rem;
        }

        .receipt-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: transform 0.3s ease;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }

        .receipt-image:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }

        .payment-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn-modern.btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            min-width: 80px;
        }

        .btn-modern.btn-outline-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-modern.btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }

        .btn-modern.btn-outline-danger {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            border-color: #dc2626;
        }

        .btn-modern.btn-outline-danger:hover {
            background: var(--danger-gradient);
            color: white;
        }

        /* قسم الملاحظات */
        .notes-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .notes-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .notes-content {
            background: rgba(102, 126, 234, 0.05);
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            border-left: 4px solid #667eea;
            margin: 0;
            font-style: italic;
            color: #4a5568;
        }

        /* الحالة الفارغة */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2d3748;
        }

        .empty-description {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* الشريط الجانبي */
        .sidebar-section {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sidebar-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-light);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .sidebar-card:hover {
            transform: translateY(-3px);
        }

        .sidebar-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-content {
            padding: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            color: #667eea;
            min-width: 20px;
        }

        .info-text {
            color: #2d3748;
            font-weight: 500;
        }

        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .booking-item:last-child {
            border-bottom: none;
        }

        .booking-info h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #2d3748;
        }

        .booking-date {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .booking-amount {
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
            min-width: 80px;
        }

        .booking-amount.sar {
            background: var(--success-gradient);
            color: white;
        }

        .booking-amount.kwd {
            background: var(--warning-gradient);
            color: white;
        }

        /* الموديلات */
        .modal-content {
            border-radius: var(--border-radius-lg);
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(20px);
        }

        .modal-header {
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-header.bg-warning {
            background: var(--warning-gradient) !important;
        }

        .form-select,
        .form-control {
            border-radius: var(--border-radius-sm);
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* الاستجابة للشاشات الصغيرة */
        @media (max-width: 768px) {
            .payments-container {
                padding: 1rem;
            }

            .page-header-section {
                padding: 1.5rem;
            }

            .page-title-main {
                font-size: 1.8rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .payment-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .payment-details-grid {
                grid-template-columns: 1fr;
            }

            .payment-actions {
                flex-direction: row;
                justify-content: center;
            }

            .stat-card {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .btn-modern span {
                display: none;
            }

            .btn-modern {
                padding: 0.75rem;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                justify-content: center;
            }

            .action-buttons {
                flex-direction: row;
                justify-content: center;
                flex-wrap: wrap;
            }
        }

        /* تأثيرات إضافية */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-entry {
            animation: fadeInUp 0.5s ease-out;
        }

        .stat-card {
            animation: fadeInUp 0.5s ease-out;
        }

        .sidebar-card {
            animation: fadeInUp 0.5s ease-out;
        }

        /* تأثير النبضة للأرقام المهمة */
        .amount-number {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }
    </style>
@endpush

@section('content')
    <div class="payments-container">
        <!-- رأس الصفحة -->
        <div class="page-header-section">
            <div
                class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                <div class="flex-grow-1">
                    <h1 class="page-title-main">
                        <i class="fas fa-handshake page-title-icon"></i>
                        {{ $agent->name }} - الرحلات البرية
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-nav">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.land-trips-agent-payments.index') }}">
                                    <i class="fas fa-home me-1"></i>مدفوعات وكلاء الرحلات البرية
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ $agent->name }}
                            </li>
                        </ol>
                    </nav>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('admin.land-trips-agent-payments.create', $agent) }}" class="btn-modern btn-success">
                        <i class="fas fa-plus"></i>
                        <span>إضافة دفعة جديدة</span>
                    </a>
                    <button class="btn-modern btn-warning" onclick="showDiscountModal()">
                        <i class="fas fa-percentage"></i>
                        <span>إضافة خصم</span>
                    </button>
                    <button class="btn-modern btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        <span>طباعة التقرير</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- المحتوى الرئيسي -->
            <div class="col-xl-8">
                <!-- إحصائيات العملات -->
                <div class="stats-section">
                    @foreach (['SAR' => 'sar', 'KWD' => 'kwd'] as $currency => $class)
                        @if (isset($totals[$currency]) && $totals[$currency]['due'] > 0)
                            <div class="stat-card currency-{{ $class }}">
                                <div class="stat-header">
                                    <h3 class="stat-title">{{ $currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي' }}
                                    </h3>
                                    <span class="currency-indicator {{ $class }}">{{ $currency }}</span>
                                </div>

                                <div class="stat-rows">
                                    <div class="stat-row">
                                        <span class="stat-label">المبلغ المستحق</span>
                                        <span class="stat-value">{{ number_format($totals[$currency]['due'], 2) }}</span>
                                    </div>

                                    <div class="stat-row">
                                        <span class="stat-label">المبلغ المدفوع</span>
                                        <span
                                            class="stat-value success">{{ number_format($totals[$currency]['paid'], 2) }}</span>
                                    </div>

                                    @if (isset($totals[$currency]['discounts']) && $totals[$currency]['discounts'] > 0)
                                        <div class="stat-row">
                                            <span class="stat-label">الخصومات المطبقة</span>
                                            <span
                                                class="stat-value warning">{{ number_format($totals[$currency]['discounts'], 2) }}</span>
                                        </div>
                                    @endif

                                    <div class="stat-row">
                                        <span class="stat-label">المبلغ المتبقي</span>
                                        <span
                                            class="stat-value">{{ number_format($totals[$currency]['remaining'], 2) }}</span>
                                    </div>
                                </div>

                                @php
                                    $totalAdjusted =
                                        $totals[$currency]['paid'] + ($totals[$currency]['discounts'] ?? 0);
                                    $percentage =
                                        $totals[$currency]['due'] > 0
                                            ? ($totalAdjusted / $totals[$currency]['due']) * 100
                                            : 0;
                                @endphp
                                <div class="progress-container">
                                    <div class="progress-bar-modern">
                                        <div class="progress-fill" style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    <div class="progress-text">
                                        تم الدفع: {{ number_format($percentage, 1) }}% من إجمالي المستحق
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- قائمة المدفوعات -->
                <div class="payments-section">
                    <div class="payments-header">
                        <h2 class="payments-title">
                            <i class="fas fa-history"></i>
                            سجل المدفوعات - الرحلات البرية
                        </h2>
                        <div class="payments-count">
                            {{ $payments->total() }} دفعة مسجلة
                        </div>
                    </div>

                    @forelse($payments as $payment)
                        <div class="payment-entry">
                            <div class="payment-layout">
                                <!-- مبلغ الدفعة -->
                                <div class="payment-amount-section {{ $payment->amount < 0 ? 'discount-payment' : '' }}">
                                    <h4 class="amount-number">
                                        {{ $payment->amount < 0 ? '-' : '' }}{{ number_format(abs($payment->amount), 2) }}
                                    </h4>
                                    <p class="amount-currency">
                                        {{ $payment->currency }} {{ $payment->amount < 0 ? '(خصم)' : '' }}
                                    </p>
                                </div>

                                <!-- تفاصيل الدفعة -->
                                <div class="payment-details-grid">
                                    <div class="detail-item">
                                        <i class="fas fa-calendar detail-icon"></i>
                                        <div class="detail-content">
                                            <p class="detail-label">تاريخ الدفع</p>
                                            <p class="detail-value">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <i class="fas fa-credit-card detail-icon"></i>
                                        <div class="detail-content">
                                            <p class="detail-label">طريقة الدفع</p>
                                            <p class="detail-value">
                                                @switch($payment->payment_method)
                                                    @case('cash')
                                                        نقداً
                                                    @break

                                                    @case('transfer')
                                                        تحويل بنكي
                                                    @break

                                                    @case('check')
                                                        شيك
                                                    @break

                                                    @default
                                                        غير محدد
                                                @endswitch
                                            </p>
                                        </div>
                                    </div>

                                    @if ($payment->reference_number)
                                        <div class="detail-item">
                                            <i class="fas fa-hashtag detail-icon"></i>
                                            <div class="detail-content">
                                                <p class="detail-label">رقم المرجع</p>
                                                <p class="detail-value">{{ $payment->reference_number }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($payment->employee)
                                        <div class="detail-item">
                                            <i class="fas fa-user detail-icon"></i>
                                            <div class="detail-content">
                                                <p class="detail-label">مسجل بواسطة</p>
                                                <p class="detail-value">{{ $payment->employee->name }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($payment->receipt_image_url)
                                        <div class="detail-item">
                                            <i class="fas fa-image detail-icon"></i>
                                            <div class="detail-content">
                                                <p class="detail-label">إيصال الدفع</p>
                                                <div class="receipt-image-container">
                                                    @php
                                                        // تحويل رابط Google Drive للعرض المباشر
                                                        $imageUrl = $payment->receipt_image_url;
                                                        $originalUrl = $payment->receipt_image_url;

                                                        // محاولة تحويل رابط Google Drive
                                                        if (str_contains($imageUrl, 'drive.google.com')) {
                                                            preg_match(
                                                                '/\/file\/d\/([a-zA-Z0-9-_]+)/',
                                                                $imageUrl,
                                                                $matches,
                                                            );
                                                            if (isset($matches[1])) {
                                                                $fileId = $matches[1];
                                                                // استخدام صورة مصغرة بدلاً من المحتوى الأصلي
                                                                $imageUrl = "https://drive.google.com/thumbnail?id={$fileId}&sz=w200";
                                                            }
                                                        }
                                                    @endphp

                                                    {{-- زر مع أيقونة وصورة مصغرة --}}
                                                    <div class="receipt-preview"
                                                        onclick="showImageModal('{{ $originalUrl }}')">
                                                        <div class="receipt-image-wrapper">
                                                            <img src="{{ $imageUrl }}" alt="إيصال الدفع"
                                                                class="receipt-image"
                                                                onerror="handleImageError(this, '{{ $originalUrl }}')">
                                                            <div class="receipt-overlay">
                                                                <i class="fas fa-eye"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- أزرار التحكم -->
                                <div class="payment-actions">
                                    <a href="{{ route('admin.land-trips-agent-payments.edit', [$agent, $payment]) }}"
                                        class="btn-modern btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit"></i>
                                        <span>تعديل</span>
                                    </a>
                                    <button onclick="confirmDelete({{ $payment->id }})"
                                        class="btn-modern btn-outline-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                        <span>حذف</span>
                                    </button>
                                </div>
                            </div>

                            @if ($payment->notes)
                                <div class="notes-section">
                                    <h5 class="notes-label">ملاحظات إضافية</h5>
                                    <p class="notes-content">{{ $payment->notes }}</p>
                                </div>
                            @endif
                        </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-money-bill-wave empty-icon"></i>
                                <h3 class="empty-title">لا توجد مدفوعات مسجلة</h3>
                                <p class="empty-description">
                                    لم يتم تسجيل أي مدفوعات لهذا الوكيل في الرحلات البرية حتى الآن.<br>
                                    يمكنك البدء بإضافة أول دفعة الآن.
                                </p>
                                <a href="{{ route('admin.land-trips-agent-payments.create', $agent) }}"
                                    class="btn-modern btn-success">
                                    <i class="fas fa-plus"></i>
                                    <span>إضافة أول دفعة</span>
                                </a>
                            </div>
                        @endforelse

                        @if ($payments->hasPages())
                            <div class="px-3 py-4 border-top">
                                <div class="d-flex justify-content-center">
                                    {{ $payments->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- الشريط الجانبي -->
                <div class="col-xl-4">
                    <div class="sidebar-section">
                        <!-- معلومات الوكيل -->
                        <div class="sidebar-card">
                            <div class="sidebar-header">
                                <i class="fas fa-info-circle"></i>
                                <span>معلومات الوكيل</span>
                            </div>
                            <div class="sidebar-content">
                                <div class="info-item">
                                    <i class="fas fa-handshake info-icon"></i>
                                    <span class="info-text">{{ $agent->name }}</span>
                                </div>

                                @if ($agent->email)
                                    <div class="info-item">
                                        <i class="fas fa-envelope info-icon"></i>
                                        <span class="info-text">{{ $agent->email }}</span>
                                    </div>
                                @endif

                                @if ($agent->phone)
                                    <div class="info-item">
                                        <i class="fas fa-phone info-icon"></i>
                                        <span class="info-text">{{ $agent->phone }}</span>
                                    </div>
                                @endif

                                <div class="info-item">
                                    <i class="fas fa-chart-line info-icon"></i>
                                    <span class="info-text">{{ $agent->landTripBookings()->count() }} حجز رحلة برية</span>
                                </div>
                            </div>
                        </div>

                        <!-- جميع الحجوزات -->
                        <div class="sidebar-card">
                            <div class="sidebar-header">
                                <i class="fas fa-bus"></i>
                                <span>جميع حجوزات الرحلات البرية ({{ $allBookings->total() }})</span>
                            </div>
                            <div class="sidebar-content">
                                @forelse($allBookings as $booking)
                                    <div class="booking-item">
                                        <div class="booking-info">
                                            <h6 class="booking-client">{{ $booking->client_name }}</h6>
                                            <p class="booking-date">{{ $booking->created_at->format('d/m/Y') }}</p>
                                            <small class="text-muted">
                                                {{ $booking->company->name ?? 'غير محدد' }}
                                            </small>
                                            @if ($booking->landTrip)
                                                <small class="d-block text-info">
                                                    <i
                                                        class="fas fa-route me-1"></i>{{ $booking->landTrip->destination ?? 'رحلة برية' }}
                                                </small>
                                            @endif
                                            @if ($booking->rooms && $booking->days)
                                                <small class="d-block text-secondary">
                                                    <i class="fas fa-bed me-1"></i>{{ $booking->rooms }} غرفة -
                                                    {{ $booking->days }} ليلة
                                                </small>
                                            @endif
                                        </div>
                                        <div class="booking-amount-info">
                                            <span class="booking-amount {{ $booking->currency === 'SAR' ? 'sar' : 'kwd' }}">
                                                {{ number_format($booking->amount_due_to_agent, 0) }} {{ $booking->currency }}
                                            </span>
                                            @if ($booking->status)
                                                <small class="d-block mt-1">
                                                    <span
                                                        class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }} badge-sm">
                                                        {{ $booking->status === 'confirmed' ? 'مؤكد' : 'في الانتظار' }}
                                                    </span>
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-bus fa-2x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">لا توجد حجوزات رحلات برية</p>
                                    </div>
                                @endforelse

                                {{-- شريط التصفح للحجوزات --}}
                                @if ($allBookings->hasPages())
                                    <div class="mt-3 pt-3 border-top">
                                        <nav aria-label="تصفح الحجوزات">
                                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                                {{-- الصفحة السابقة --}}
                                                @if ($allBookings->onFirstPage())
                                                    <li class="page-item disabled">
                                                        <span class="page-link">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link"
                                                            href="{{ $allBookings->appends(request()->query())->previousPageUrl() }}">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                @endif

                                                {{-- أرقام الصفحات (مبسط للشريط الجانبي) --}}
                                                @if ($allBookings->lastPage() <= 5)
                                                    @for ($i = 1; $i <= $allBookings->lastPage(); $i++)
                                                        @if ($i == $allBookings->currentPage())
                                                            <li class="page-item active">
                                                                <span class="page-link">{{ $i }}</span>
                                                            </li>
                                                        @else
                                                            <li class="page-item">
                                                                <a class="page-link"
                                                                    href="{{ $allBookings->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                                            </li>
                                                        @endif
                                                    @endfor
                                                @else
                                                    {{-- للصفحات الكثيرة، عرض مبسط --}}
                                                    <li class="page-item">
                                                        <span class="page-link text-muted">
                                                            {{ $allBookings->currentPage() }} / {{ $allBookings->lastPage() }}
                                                        </span>
                                                    </li>
                                                @endif

                                                {{-- الصفحة التالية --}}
                                                @if ($allBookings->hasMorePages())
                                                    <li class="page-item">
                                                        <a class="page-link"
                                                            href="{{ $allBookings->appends(request()->query())->nextPageUrl() }}">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                @else
                                                    <li class="page-item disabled">
                                                        <span class="page-link">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </span>
                                                    </li>
                                                @endif
                                            </ul>
                                        </nav>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- مودال الخصم - نفس المودال الموجود في صفحة الشركات مع تعديل الـ action --}}
            <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="discountModalLabel">
                                <i class="fas fa-percentage me-2"></i>
                                إضافة خصم للوكيل
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="discountForm" method="POST"
                            action="{{ route('admin.land-trips-agent-payments.apply-discount', $agent) }}">
                            @csrf
                            <div class="modal-body">
                                <!-- اختيار العملة -->
                                <div class="mb-3">
                                    <label for="discount_currency" class="form-label">العملة <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="discount_currency" name="currency" required
                                        onchange="updateRemainingDisplay()">
                                        <option value="">اختر العملة</option>
                                        @foreach (['SAR' => 'الريال السعودي', 'KWD' => 'الدينار الكويتي'] as $curr => $label)
                                            @if (isset($totals[$curr]) && $totals[$curr]['remaining'] > 0)
                                                <option value="{{ $curr }}">{{ $label }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <!-- عرض المبلغ المتبقي -->
                                <div class="mb-3">
                                    <div class="alert alert-info" id="remainingDisplay" style="display: none;">
                                        <i class="fas fa-info-circle me-2"></i>
                                        المبلغ المتبقي: <span id="remainingAmount">0</span>
                                    </div>
                                </div>

                                <!-- مبلغ الخصم -->
                                <div class="mb-3">
                                    <label for="discount_amount" class="form-label">مبلغ الخصم <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="discount_amount" name="discount_amount"
                                        step="0.01" min="0.01" required placeholder="أدخل مبلغ الخصم">
                                    <div class="form-text">سيتم خصم هذا المبلغ من إجمالي المستحق</div>
                                </div>

                                <!-- سبب الخصم -->
                                <div class="mb-3">
                                    <label for="discount_reason" class="form-label">سبب الخصم</label>
                                    <textarea class="form-control" id="discount_reason" name="reason" rows="3"
                                        placeholder="اختياري - اذكر سبب الخصم..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>إلغاء
                                </button>
                                <button type="submit" class="btn btn-warning" id="applyDiscountBtn">
                                    <i class="fas fa-percentage me-1"></i>تطبيق الخصم
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- مودال تأكيد الحذف --}}
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                تأكيد حذف الدفعة
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>هل أنت متأكد من رغبتك في حذف هذه الدفعة نهائياً؟<br>
                                لا يمكن التراجع عن هذا الإجراء بعد التأكيد.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-modern btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i>
                                <span>إلغاء</span>
                            </button>
                            <form id="deleteForm" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-modern btn-danger">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>حذف نهائياً</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            // نفس JavaScript الموجود في صفحة الشركات مع تعديل المسارات
            function confirmDelete(paymentId) {
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                const form = document.getElementById('deleteForm');
                form.action = `{{ route('admin.land-trips-agent-payments.show', $agent) }}/${paymentId}`;
                modal.show();
            }

            function showDiscountModal() {
                const modal = new bootstrap.Modal(document.getElementById('discountModal'));
                modal.show();
            }

            function updateRemainingDisplay() {
                const currency = document.getElementById('discount_currency').value;
                const remainingDisplay = document.getElementById('remainingDisplay');
                const remainingAmount = document.getElementById('remainingAmount');
                const discountAmountInput = document.getElementById('discount_amount');

                if (currency) {
                    const totals = @json($totals);
                    const remaining = totals[currency] ? totals[currency].remaining : 0;

                    remainingAmount.textContent = `${remaining.toLocaleString()} ${currency}`;
                    remainingDisplay.style.display = 'block';

                    discountAmountInput.max = remaining;
                    discountAmountInput.placeholder = `الحد الأقصى: ${remaining}`;
                } else {
                    remainingDisplay.style.display = 'none';
                    discountAmountInput.max = '';
                    discountAmountInput.placeholder = 'أدخل مبلغ الخصم';
                }
            }

            function handleImageError(img, originalUrl) {
                console.log('فشل تحميل الصورة:', originalUrl);

                // محاولة تحويل رابط Google Drive بطرق مختلفة
                if (originalUrl.includes('drive.google.com')) {
                    const fileIdMatch = originalUrl.match(/\/file\/d\/([a-zA-Z0-9-_]+)/);
                    if (fileIdMatch && fileIdMatch[1]) {
                        const fileId = fileIdMatch[1];

                        // تجربة طريقة أخرى للعرض
                        if (!img.dataset.retryCount || img.dataset.retryCount < 1) {
                            img.dataset.retryCount = parseInt(img.dataset.retryCount || '0') + 1;
                            img.src = `https://drive.google.com/thumbnail?id=${fileId}&sz=w200`;
                            return;
                        }
                    }
                }

                // إذا فشلت كل المحاولات، إظهار واجهة بديلة مع رابط
                img.style.display = 'none';
                const container = img.parentElement;
                container.innerHTML = `
        <div style="width: 60px; height: 60px; border: 1px dashed #ccc; border-radius: var(--border-radius-sm);
                    display: flex; flex-direction: column; align-items: center; justify-content: center; 
                    background: #f8f9fa; padding: 5px;">
            <i class="fas fa-external-link-alt text-primary mb-1" style="font-size: 1rem;"></i>
            <a href="${originalUrl}" target="_blank" style="font-size: 0.7rem; text-decoration: none;">
                فتح الإيصال
            </a>
        </div>
    `;
            }

            function showImageModal(imageUrl) {
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.setAttribute('tabindex', '-1');
                modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-image me-2"></i>عرض إيصال الدفع
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0 position-relative">
                    <div class="image-loading-overlay position-absolute top-0 start-0 end-0 bottom-0 
                         d-flex align-items-center justify-content-center">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <img src="${imageUrl}" class="img-fluid" 
                         style="max-height: 70vh; border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);" 
                         alt="إيصال الدفع"
                         onload="this.parentElement.querySelector('.image-loading-overlay').style.display='none'"
                         onerror="handleModalImageError(this, '${imageUrl}')">
                </div>
                <div class="modal-footer">
                    <a href="${imageUrl}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> فتح في نافذة جديدة
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    `;

                document.body.appendChild(modal);
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();

                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modal);
                });
            }
            // إضافة دالة جديدة للتعامل مع أخطاء العرض في المودال
            function handleModalImageError(img, originalUrl) {
                img.style.display = 'none';
                const container = img.parentElement;

                // إزالة شاشة التحميل
                const loader = container.querySelector('.image-loading-overlay');
                if (loader) loader.style.display = 'none';

                // عرض رسالة خطأ وزر لفتح الرابط الأصلي
                container.innerHTML = `
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-exclamation-circle text-warning" style="font-size: 4rem;"></i>
            </div>
            <h4 class="mb-3">تعذر عرض الإيصال</h4>
            <p class="text-muted mb-4">
                لا يمكن عرض الإيصال مباشرةً نظرًا لقيود المتصفح.
                <br>يمكنك فتح الرابط مباشرةً.
            </p>
            <a href="${originalUrl}" target="_blank" class="btn btn-primary">
                <i class="fas fa-external-link-alt me-2"></i>
                فتح الإيصال في نافذة جديدة
            </a>
        </div>
    `;
            }


            // التحقق من صحة البيانات قبل الإرسال
            document.getElementById('discountForm').addEventListener('submit', function(e) {
                const currency = document.getElementById('discount_currency').value;
                const discountAmount = parseFloat(document.getElementById('discount_amount').value);

                if (!currency) {
                    e.preventDefault();
                    alert('يرجى اختيار العملة');
                    return;
                }

                const totals = @json($totals);
                const remaining = totals[currency] ? totals[currency].remaining : 0;

                if (discountAmount > remaining) {
                    e.preventDefault();
                    alert(`مبلغ الخصم (${discountAmount}) أكبر من المبلغ المتبقي (${remaining})`);
                    return;
                }

                if (!confirm(
                        `هل أنت متأكد من تطبيق خصم ${discountAmount} ${currency} على الوكيل {{ $agent->name }}؟`)) {
                    e.preventDefault();
                }
            });
        </script>
        <!-- استدعاء الخلفية التفاعلية -->
        <script type="module">
            import {
                initParticlesBg
            } from '/js/particles-bg.js';
            initParticlesBg(); // يمكنك تمرير خيارات مثل {points:80, colors:[...]} إذا أردت
        </script>
    @endpush
