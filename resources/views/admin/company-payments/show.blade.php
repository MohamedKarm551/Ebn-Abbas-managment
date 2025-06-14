{{-- filepath: resources/views/admin/company-payments/show.blade.php --}}
@extends('layouts.app')

@section('title', 'تفاصيل مدفوعات ' . $company->name)

@push('styles')
<style>
/* نظام الألوان والمتغيرات */
:root {
    --primary-blue: #3b82f6;
    --success-green: #10b981;
    --warning-amber: #f59e0b;
    --danger-red: #ef4444;
    --info-cyan: #06b6d4;
    
    /* خلفيات */
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --bg-hover: #e2e8f0;
    
    /* حدود */
    --border-light: #e2e8f0;
    --border-medium: #cbd5e1;
    --border-dark: #94a3b8;
    
    /* نصوص موحدة */
    --text-primary: #000000;
    --text-secondary: #666666;
    --text-muted: #999999;
    
    /* ظلال */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    
    /* انتقالات */
    --transition: all 0.2s ease-in-out;
    --radius: 12px;
    --radius-lg: 16px;
}

/* إعادة تعيين أساسية */
* {
    box-sizing: border-box;
}

/* الحاوي الرئيسي */
.payments-container {
    background: var(--bg-secondary);
    min-height: 100vh;
    padding: 1.5rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* البطاقات الأساسية */
.modern-card {
    background: var(--bg-primary);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: var(--transition);
}

.modern-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

/* رأس الصفحة */
.page-header-section {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
}

.page-title-main {
    color: var(--text-primary);
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-title-icon {
    color: var(--primary-blue);
    font-size: 1.8rem;
}

.breadcrumb-nav {
    background: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-nav .breadcrumb-item {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.breadcrumb-nav .breadcrumb-item a {
    color: var(--primary-blue);
    text-decoration: none;
    transition: var(--transition);
}

.breadcrumb-nav .breadcrumb-item a:hover {
    color: var(--info-cyan);
}

.breadcrumb-nav .breadcrumb-item.active {
    color: var(--text-muted);
}

.breadcrumb-nav .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--text-muted);
    font-weight: 500;
}

/* أزرار الإجراءات */
.action-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-modern {
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius);
    font-weight: 500;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    line-height: 1;
}

.btn-modern:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-success {
    background: var(--success-green);
    color: white;
}

.btn-success:hover {
    background: #059669;
    color: white;
    transform: translateY(-1px);
}

.btn-outline-primary {
    background: transparent;
    border: 2px solid var(--border-medium);
    color: var(--text-primary);
}

.btn-outline-primary:hover {
    background: var(--bg-hover);
    border-color: var(--primary-blue);
    color: var(--primary-blue);
}

/* شبكة الإحصائيات */
.stats-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .stats-section {
        grid-template-columns: repeat(2, 1fr);
    }
}

.stat-card {
    background: var(--bg-primary);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    position: relative;
    overflow: hidden;
    transition: var(--transition);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-blue);
    transition: var(--transition);
}

.stat-card.currency-sar::before {
    background: var(--success-green);
}

.stat-card.currency-kwd::before {
    background: var(--warning-amber);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-card:hover::before {
    width: 8px;
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.stat-title {
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.currency-indicator {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.currency-indicator.sar {
    background: #dcfce7;
    color: #166534;
}

.currency-indicator.kwd {
    background: #fef3c7;
    color: #92400e;
}

.stat-rows {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.stat-value {
    color: var(--text-primary);
    font-size: 1.3rem;
    font-weight: 700;
}

.stat-value.success {
    color: var(--success-green);
}

.stat-value.warning {
    color: var(--warning-amber);
}

.progress-container {
    margin-top: 1rem;
}

.progress-bar-modern {
    width: 100%;
    height: 8px;
    background: var(--bg-tertiary);
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--success-green), #34d399);
    border-radius: 4px;
    transition: width 1s ease-in-out;
    position: relative;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-text {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

/* قسم المدفوعات */
.payments-section {
    background: var(--bg-primary);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    overflow: hidden;
}

.payments-header {
    background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-secondary));
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.payments-title {
    color: var(--text-primary);
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.payments-count {
    background: var(--primary-blue);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    box-shadow: var(--shadow-sm);
}

/* عنصر الدفعة */
.payment-entry {
    padding: 2rem;
    border-bottom: 1px solid var(--border-light);
    transition: var(--transition);
    position: relative;
}

.payment-entry:hover {
    background: var(--bg-secondary);
}

.payment-entry:last-child {
    border-bottom: none;
}

.payment-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    align-items: start;
}

@media (min-width: 768px) {
    .payment-layout {
        grid-template-columns: auto 1fr auto;
        align-items: center;
    }
}

.payment-amount-section {
    text-align: center;
    padding: 1rem;
    background: var(--bg-tertiary);
    border-radius: var(--radius);
    border: 2px solid var(--border-light);
    transition: var(--transition);
}

.payment-amount-section:hover {
    border-color: var(--primary-blue);
    transform: scale(1.02);
}

.amount-number {
    color: var(--text-primary);
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.amount-currency {
    color: var(--text-secondary);
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 0.25rem;
}

.payment-details-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 576px) {
    .payment-details-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 992px) {
    .payment-details-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--bg-secondary);
    border-radius: var(--radius);
    border: 1px solid var(--border-light);
    transition: var(--transition);
}

.detail-item:hover {
    background: var(--bg-hover);
    border-color: var(--border-medium);
}

.detail-icon {
    color: var(--text-secondary);
    font-size: 1rem;
    width: 1.2rem;
    text-align: center;
}

.detail-content {
    flex: 1;
    min-width: 0;
}

.detail-label {
    color: var(--text-secondary);
    font-size: 0.8rem;
    font-weight: 500;
    margin: 0 0 0.25rem 0;
}

.detail-value {
    color: var(--text-primary);
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
}

.receipt-image-container {
    display: flex;
    align-items: center;
    justify-content: center;
}

.receipt-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    border: 2px solid var(--border-light);
    box-shadow: var(--shadow-sm);
}

.receipt-image:hover {
    transform: scale(1.1);
    border-color: var(--primary-blue);
    box-shadow: var(--shadow-md);
}

.payment-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: stretch;
}

@media (min-width: 768px) {
    .payment-actions {
        flex-direction: row;
        align-items: center;
    }
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
}

.btn-outline-secondary {
    background: transparent;
    border: 1px solid var(--border-medium);
    color: var(--text-secondary);
}

.btn-outline-secondary:hover {
    background: var(--bg-hover);
    border-color: var(--border-dark);
    color: var(--text-primary);
}

.btn-outline-danger {
    background: transparent;
    border: 1px solid #fecaca;
    color: var(--danger-red);
}

.btn-outline-danger:hover {
    background: #fef2f2;
    border-color: var(--danger-red);
    color: #dc2626;
}

.btn-disabled {
    background: var(--bg-tertiary);
    color: var(--text-muted);
    cursor: not-allowed;
    border: 1px solid var(--border-light);
}

/* قسم الملاحظات */
.notes-section {
    margin-top: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border-radius: var(--radius);
    border-left: 4px solid var(--info-cyan);
    border: 1px solid var(--border-light);
}

.notes-label {
    color: var(--text-secondary);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 0.75rem 0;
}

.notes-content {
    color: var(--text-primary);
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
}

/* الحالة الفارغة */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    color: var(--text-muted);
    font-size: 4rem;
    margin-bottom: 1.5rem;
    opacity: 0.7;
}

.empty-title {
    color: var(--text-primary);
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
}

.empty-description {
    color: var(--text-secondary);
    font-size: 1rem;
    margin: 0 0 2rem 0;
    line-height: 1.5;
}

/* الشريط الجانبي */
.sidebar-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.sidebar-card {
    background: var(--bg-primary);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    overflow: hidden;
}

.sidebar-header {
    background: linear-gradient(135deg, var(--primary-blue), #2563eb);
    color: white;
    padding: 1.25rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
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
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-light);
}

.info-item:last-child {
    border-bottom: none;
}

.info-icon {
    color: var(--text-secondary);
    font-size: 1rem;
    width: 1.25rem;
    text-align: center;
}

.info-text {
    color: var(--text-primary);
    font-size: 0.9rem;
    font-weight: 500;
    flex: 1;
}

.booking-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-light);
}

.booking-item:last-child {
    border-bottom: none;
}

.booking-info {
    flex: 1;
}

.booking-client {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 0.9rem;
    margin: 0 0 0.25rem 0;
}

.booking-date {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin: 0;
}

.booking-amount {
    padding: 0.375rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.booking-amount.sar {
    background: #dcfce7;
    color: #166534;
}

.booking-amount.kwd {
    background: #fef3c7;
    color: #92400e;
}

/* المودال */
.modal-content {
    border: none;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
}

.modal-header {
    background: linear-gradient(135deg, var(--danger-red), #dc2626);
    color: white;
    border-bottom: none;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    padding: 1.5rem;
}

.modal-title {
    color: white !important;
    font-weight: 600;
    margin: 0;
}

.modal-body {
    padding: 2rem;
    text-align: center;
}

.modal-body p {
    color: var(--text-primary) !important;
    font-size: 1.1rem;
    line-height: 1.6;
    margin: 0;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border-light);
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.btn-danger {
    background: var(--danger-red);
    color: white;
    border: none;
}

.btn-danger:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-1px);
}

/* طباعة */
@media print {
    .payments-container {
        background: white;
        padding: 0;
    }
    
    .action-buttons,
    .payment-actions,
    .btn-modern {
        display: none !important;
    }
    
    .modern-card,
    .sidebar-card {
        box-shadow: none;
        border: 1px solid #ccc;
        page-break-inside: avoid;
    }
    
    * {
        color: black !important;
        background: white !important;
    }
}
</style>
@endpush

@section('content')
<div class="payments-container">
    <!-- رأس الصفحة -->
    <div class="page-header-section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
            <div class="flex-grow-1">
                <h1 class="page-title-main">
                    <i class="fas fa-building page-title-icon"></i>
                    {{ $company->name }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-nav">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.company-payments.index') }}">
                                <i class="fas fa-home me-1"></i>مدفوعات الشركات
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $company->name }}
                        </li>
                    </ol>
                </nav>
            </div>
            
            <div class="action-buttons">
                <a href="{{ route('admin.company-payments.create', $company) }}" class="btn-modern btn-success">
                    <i class="fas fa-plus"></i>
                    <span>إضافة دفعة جديدة</span>
                </a>
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
                @foreach(['SAR' => 'sar', 'KWD' => 'kwd'] as $currency => $class)
                @if(isset($totals[$currency]) && $totals[$currency]['due'] > 0)
                <div class="stat-card currency-{{ $class }}">
                    <div class="stat-header">
                        <h3 class="stat-title">
                            {{ $currency === 'SAR' ? 'الريال السعودي' : 'الدينار الكويتي' }}
                        </h3>
                        <span class="currency-indicator {{ $class }}">{{ $currency }}</span>
                    </div>
                    
                    <div class="stat-rows">
                        <div class="stat-row">
                            <span class="stat-label">إجمالي المستحق</span>
                            <span class="stat-value">{{ number_format($totals[$currency]['due'], 2) }}</span>
                        </div>
                        
                        <div class="stat-row">
                            <span class="stat-label">إجمالي المدفوع</span>
                            <span class="stat-value success">{{ number_format($totals[$currency]['paid'], 2) }}</span>
                        </div>
                        
                        <div class="stat-row">
                            <span class="stat-label">المبلغ المتبقي</span>
                            <span class="stat-value warning">{{ number_format($totals[$currency]['remaining'], 2) }}</span>
                        </div>
                    </div>
                    
                    @php
                        $percentage = $totals[$currency]['due'] > 0 ? ($totals[$currency]['paid'] / $totals[$currency]['due']) * 100 : 0;
                    @endphp
                    <div class="progress-container">
                        <div class="progress-bar-modern">
                            <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="progress-text">
                            نسبة الإنجاز: {{ number_format($percentage, 1) }}%
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
                        سجل المدفوعات
                    </h2>
                    <div class="payments-count">
                        {{ $payments->total() }} دفعة مسجلة
                    </div>
                </div>

                @forelse($payments as $payment)
                <div class="payment-entry">
                    <div class="payment-layout">
                        <!-- مبلغ الدفعة -->
                        <div class="payment-amount-section">
                            <h4 class="amount-number">{{ number_format($payment->amount, 2) }}</h4>
                            <p class="amount-currency">
                                {{ $payment->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                            </p>
                        </div>

                        <!-- تفاصيل الدفعة -->
                        <div class="payment-details-grid">
                            <div class="detail-item">
                                <i class="fas fa-calendar-alt detail-icon"></i>
                                <div class="detail-content">
                                    <p class="detail-label">تاريخ الدفع</p>
                                    <p class="detail-value">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                </div>
                            </div>

                            @if($payment->employee)
                            <div class="detail-item">
                                <i class="fas fa-user-tie detail-icon"></i>
                                <div class="detail-content">
                                    <p class="detail-label">الموظف المسجل</p>
                                    <p class="detail-value">{{ $payment->employee->name }}</p>
                                </div>
                            </div>
                            @endif

                            @if($payment->receipt_image_url)
                            <div class="detail-item">
                                <i class="fas fa-receipt detail-icon"></i>
                                <div class="detail-content">
                                    <p class="detail-label">إيصال الدفع</p>
                                    <div class="receipt-image-container">
                                        <img src="{{ $payment->receipt_image_url }}" 
                                             alt="إيصال الدفع" 
                                             class="receipt-image"
                                             onclick="showImageModal('{{ $payment->receipt_image_url }}')">
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- أزرار التحكم -->
                        <div class="payment-actions">
                            @if(($payment->source ?? 'company_payments') === 'company_payments')
                            <a href="{{ route('admin.company-payments.edit', [$company, $payment]) }}" 
                               class="btn-modern btn-outline-secondary btn-sm">
                                <i class="fas fa-edit"></i>
                                <span>تعديل</span>
                            </a>
                            <button class="btn-modern btn-outline-danger btn-sm" 
                                    onclick="confirmDelete({{ $payment->id }})">
                                <i class="fas fa-trash-alt"></i>
                                <span>حذف</span>
                            </button>
                            @else
                            <span class="btn-modern btn-disabled btn-sm">
                                <i class="fas fa-lock"></i>
                                <span>دفعة محفوظة</span>
                            </span>
                            @endif
                        </div>
                    </div>

                    @if($payment->notes)
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
                        لم يتم تسجيل أي مدفوعات لهذه الشركة حتى الآن.<br>
                        يمكنك البدء بإضافة أول دفعة الآن.
                    </p>
                    <a href="{{ route('admin.company-payments.create', $company) }}" 
                       class="btn-modern btn-success">
                        <i class="fas fa-plus"></i>
                        <span>إضافة أول دفعة</span>
                    </a>
                </div>
                @endforelse

                @if($payments->hasPages())
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
                <!-- معلومات الشركة -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-info-circle"></i>
                        <span>معلومات الشركة</span>
                    </div>
                    <div class="sidebar-content">
                        <div class="info-item">
                            <i class="fas fa-building info-icon"></i>
                            <span class="info-text">{{ $company->name }}</span>
                        </div>
                        
                        @if($company->email)
                        <div class="info-item">
                            <i class="fas fa-envelope info-icon"></i>
                            <span class="info-text">{{ $company->email }}</span>
                        </div>
                        @endif
                        
                        @if($company->phone)
                        <div class="info-item">
                            <i class="fas fa-phone info-icon"></i>
                            <span class="info-text">{{ $company->phone }}</span>
                        </div>
                        @endif
                        
                        <div class="info-item">
                            <i class="fas fa-chart-line info-icon"></i>
                            <span class="info-text">{{ $company->bookings_count ?? 0 }} حجز إجمالي</span>
                        </div>
                    </div>
                </div>

                <!-- آخر الحجوزات -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <i class="fas fa-clock"></i>
                        <span>آخر الحجوزات</span>
                    </div>
                    <div class="sidebar-content">
                        @forelse($recentBookings->take(5) as $booking)
                        <div class="booking-item">
                            <div class="booking-info">
                                <h6 class="booking-client">{{ $booking->client_name }}</h6>
                                <p class="booking-date">{{ $booking->created_at->format('d/m/Y') }}</p>
                            </div>
                            <span class="booking-amount {{ $booking->currency === 'SAR' ? 'sar' : 'kwd' }}">
                                {{ number_format($booking->amount_due_from_company, 0) }} {{ $booking->currency }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">لا توجد حجوزات حديثة</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تأكيد الحذف -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        تأكيد حذف الدفعة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
// تأكيد حذف الدفعة
function confirmDelete(paymentId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `{{ route('admin.company-payments.show', $company) }}/${paymentId}`;
    modal.show();
}

// عرض صورة الإيصال في مودال
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
                <div class="modal-body text-center p-0">
                    <img src="${imageUrl}" class="img-fluid" style="max-height: 70vh; border-radius: 0 0 var(--radius-lg) var(--radius-lg);" alt="إيصال الدفع">
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

// تحريك أشرطة التقدم عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // تأخير لتأثير أفضل
    setTimeout(() => {
        document.querySelectorAll('.progress-fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    }, 500);
    
    // تحسين تجربة النقر على الصور
    document.querySelectorAll('.receipt-image').forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// اختصارات لوحة المفاتيح
document.addEventListener('keydown', function(e) {
    // Ctrl+P للطباعة
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
    
    // Escape لإغلاق المودال
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        });
    }
});
</script>
@endpush
