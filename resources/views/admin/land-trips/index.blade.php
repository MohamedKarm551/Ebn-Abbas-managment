@extends('layouts.app')

@section('title', 'إدارة الرحلات البرية')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* متغيرات CSS للألوان المتكررة */
        :root {
            --primary-color: rgba(13, 110, 253, 0.15);
            --success-color: rgba(25, 135, 84, 0.15);
            --warning-color: rgba(255, 193, 7, 0.15);
            --info-color: rgba(13, 202, 240, 0.15);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.15);
            --glass-blur: blur(20px);
        }

        /* قسم مؤشر التحميل */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
            backdrop-filter: blur(2px);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 123, 255, 0.1);
            border-left-color: #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* الأنيميشن الأساسية */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes iconPulse {

            0%,
            100% {
                transform: scale(1);
                filter: brightness(1);
            }

            50% {
                transform: scale(1.05);
                filter: brightness(1.1);
            }
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0px);
                filter: brightness(1);
            }

            50% {
                transform: translateY(-5px);
                filter: brightness(1.15);
            }
        }

        @keyframes iconGlow {
            0% {
                filter: drop-shadow(0 0 8px rgba(13, 110, 253, 0.4)) brightness(1);
            }

            100% {
                filter: drop-shadow(0 0 12px rgba(13, 110, 253, 0.6)) brightness(1.1);
            }
        }

        @keyframes linkPulse {

            0%,
            100% {
                opacity: 0.7;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        /* التنسيقات الأساسية */
        .card-header-tabs .nav-link {
            color: #495057;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }

        .card-header-tabs .nav-link.active {
            color: #0d6efd;
            background: none;
            border-bottom: 2px solid #0d6efd;
        }

        /* الجداول */
        .table-trips th,
        #bookings-table-container .table th {
            white-space: nowrap;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-responsive {
            max-height: calc(100vh - 300px);
            min-height: 400px;
        }

        .trip-row,
        #bookings-table-container .table tr {
            transition: background-color 0.2s;
        }

        .trip-row:hover,
        #bookings-table-container .table tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        /* المرشحات */
        .trip-filter-drawer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .trip-filter-drawer.show {
            max-height: 500px;
        }

        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filter-pill {
            background-color: #e9ecef;
            color: #495057;
            border-radius: 50px;
            padding: 0.35rem 0.85rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }

        .filter-pill:hover {
            background-color: #dee2e6;
        }

        /* الأزرار العائمة */
        .floating-action-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 100;
            transition: transform 0.2s, background-color 0.2s;
        }

        .floating-action-button:hover {
            transform: scale(1.1);
            background-color: #0b5ed7;
            color: white;
        }

        /* Glass Effect - الخلفية الرئيسية */
        .glass-background-advanced {
            background: linear-gradient(135deg,
                    rgba(74, 144, 226, 0.08) 0%,
                    rgba(143, 148, 251, 0.06) 25%,
                    rgba(185, 147, 214, 0.08) 50%,
                    rgba(74, 144, 226, 0.04) 75%,
                    rgba(143, 148, 251, 0.06) 100%);
            position: relative;
            min-height: 100vh;
        }

        .glass-background-advanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(74, 144, 226, 0.05) 0%, transparent 30%);
            pointer-events: none;
            z-index: 0;
        }

        /* Glass Effect - البطاقات الأساسية */
        .stats-card-frosted-glass {
            background: var(--glass-bg) !important;
            backdrop-filter: var(--glass-blur) saturate(1.2);
            -webkit-backdrop-filter: var(--glass-blur) saturate(1.2);
            border: 1px solid var(--glass-border) !important;
            border-radius: 20px !important;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.08),
                0 2px 8px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.15),
                inset 0 -1px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        /* Glass Effect - البطاقات المميزة */
        .stats-card-premium-glass {
            background: rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(25px) saturate(1.5);
            -webkit-backdrop-filter: blur(25px) saturate(1.5);
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 24px !important;
            box-shadow:
                0 12px 40px rgba(0, 0, 0, 0.12),
                0 4px 16px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                inset 0 -1px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        /* تأثيرات Hover */
        .glass-card-hover:hover {
            transform: translateY(-12px) scale(1.02);
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }

        .glass-hover-3d:hover {
            transform: perspective(1000px) rotateY(8deg) rotateX(4deg) translateY(-15px) scale(1.03);
            background: rgba(255, 255, 255, 0.15) !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.2),
                0 12px 40px rgba(0, 0, 0, 0.15),
                inset 0 2px 0 rgba(255, 255, 255, 0.3);
        }

        /* تأثيرات الـ Overlay */
        .glass-card-overlay {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, 0.1),
                    transparent);
            transition: left 0.8s ease;
            z-index: 2;
        }

        .glass-card-hover:hover .glass-card-overlay {
            left: 100%;
        }

        .glass-shine-effect {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg,
                    transparent 30%,
                    rgba(255, 255, 255, 0.2) 50%,
                    transparent 70%);
            transition: left 1s ease;
            z-index: 3;
        }

        .glass-hover-3d:hover .glass-shine-effect {
            left: 100%;
        }

        .glass-premium-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 30%,
                    rgba(255, 255, 255, 0.1) 0%,
                    transparent 70%);
            pointer-events: none;
            z-index: 2;
        }

        /* الأيقونات */
        .glass-icon-wrapper {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            transition: all 0.3s ease;
        }

        .glass-icon-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, 0.4),
                    transparent);
            transition: left 0.8s ease;
            border-radius: inherit;
        }

        .glass-card-hover:hover .glass-icon-wrapper::before {
            left: 100%;
        }

        /* ألوان الأيقونات */
        .glass-icon-wrapper.bg-primary {
            background: var(--primary-color) !important;
            border-color: rgba(13, 110, 253, 0.3) !important;
        }

        .glass-icon-wrapper.bg-success {
            background: var(--success-color) !important;
            border-color: rgba(25, 135, 84, 0.3) !important;
        }

        .glass-icon-wrapper.bg-warning {
            background: var(--warning-color) !important;
            border-color: rgba(255, 193, 7, 0.3) !important;
        }

        .glass-icon-wrapper.bg-info {
            background: var(--info-color) !important;
            border-color: rgba(13, 202, 240, 0.3) !important;
        }

        /* ألوان النصوص */
        .text-primary.glass-icon-pulse {
            color: #0d6efd !important;
        }

        .text-success.glass-icon-pulse {
            color: #198754 !important;
        }

        .text-warning.glass-icon-pulse {
            color: #ffc107 !important;
        }

        .text-info.glass-icon-pulse {
            color: #0dcaf0 !important;
        }

        /* تأثيرات الأيقونات */
        .glass-icon-pulse {
            animation: iconPulse 2s ease-in-out infinite;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .glass-icon-float {
            animation: iconFloat 3s ease-in-out infinite;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.15));
        }

        .glass-icon-glow {
            filter: drop-shadow(0 0 8px rgba(13, 110, 253, 0.4));
            animation: iconGlow 2s ease-in-out infinite alternate;
        }

        .glass-icon-premium {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.25);
            position: relative;
            transition: all 0.4s ease;
        }

        /* النصوص المحسنة */
        .glass-text-main {
            color: rgba(0, 0, 0, 0.85);
            text-shadow: 0 1px 3px rgba(255, 255, 255, 0.5);
            font-weight: 700;
        }

        .glass-text-primary {
            color: rgba(13, 110, 253, 0.9);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .glass-text-success {
            color: rgba(25, 135, 84, 0.9);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .glass-text-warning {
            color: rgba(255, 193, 7, 0.9);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }

        .glass-text-info {
            color: rgba(13, 202, 240, 0.9);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .glass-text-premium {
            color: rgba(0, 0, 0, 0.8);
            text-shadow: 0 1px 3px rgba(255, 255, 255, 0.4);
            font-weight: 700;
        }

        .glass-text-secondary {
            color: rgba(0, 0, 0, 0.6);
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
        }

        .glass-number-display {
            color: rgba(0, 0, 0, 0.85);
            text-shadow: 0 2px 4px rgba(255, 255, 255, 0.4);
            font-weight: 800;
            letter-spacing: 0.5px;
            font-size: 1.5rem;
        }

        /* الأزرار بـ Glass Effect */
        .btn-glass-primary,
        .btn-glass-secondary,
        .btn-glass-success,
        .btn-glass-info {
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 12px;
        }

        .btn-glass-primary {
            background: rgba(13, 110, 253, 0.1) !important;
            border: 1px solid rgba(13, 110, 253, 0.3) !important;
            color: rgba(13, 110, 253, 0.9) !important;
        }

        .btn-glass-secondary {
            background: rgba(108, 117, 125, 0.1) !important;
            border: 1px solid rgba(108, 117, 125, 0.3) !important;
            color: rgba(108, 117, 125, 0.9) !important;
        }

        .btn-glass-success {
            background: rgba(25, 135, 84, 0.1) !important;
            border: 1px solid rgba(25, 135, 84, 0.3) !important;
            color: rgba(25, 135, 84, 0.9) !important;
        }

        .btn-glass-info {
            background: rgba(13, 202, 240, 0.1) !important;
            border: 1px solid rgba(13, 202, 240, 0.3) !important;
            color: rgba(13, 202, 240, 0.9) !important;
        }

        /* تأثيرات الأزرار */
        .glass-button-effect {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .glass-button-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .glass-button-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, 0.3),
                    transparent);
            transition: left 0.6s ease;
        }

        .glass-button-effect:hover::before {
            left: 100%;
        }

        .glass-link-indicator {
            animation: linkPulse 1.5s ease-in-out infinite;
        }

        /* تحسينات Hover للأيقونات */
        .glass-card-hover:hover .glass-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .glass-card-hover:hover .glass-icon-wrapper.bg-primary {
            background: rgba(13, 110, 253, 0.25) !important;
            border-color: rgba(13, 110, 253, 0.4) !important;
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }

        .glass-card-hover:hover .glass-icon-wrapper.bg-success {
            background: rgba(25, 135, 84, 0.25) !important;
            border-color: rgba(25, 135, 84, 0.4) !important;
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
        }

        .glass-card-hover:hover .glass-icon-wrapper.bg-warning {
            background: rgba(255, 193, 7, 0.25) !important;
            border-color: rgba(255, 193, 7, 0.4) !important;
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.3);
        }

        .glass-card-hover:hover .glass-icon-wrapper.bg-info {
            background: rgba(13, 202, 240, 0.25) !important;
            border-color: rgba(13, 202, 240, 0.4) !important;
            box-shadow: 0 8px 25px rgba(13, 202, 240, 0.3);
        }

        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 768px) {
            .card-header-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }

            .button-text {
                display: none;
            }

            .stats-card-frosted-glass,
            .stats-card-premium-glass {
                border-radius: 16px !important;
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
            }

            .glass-hover-3d:hover {
                transform: translateY(-8px) scale(1.02);
            }

            .glass-text-main {
                font-size: 1.5rem;
            }

            .glass-icon-wrapper {
                padding: 0.75rem !important;
            }

            .glass-icon-wrapper i {
                font-size: 1.5rem !important;
            }

            .glass-number-display {
                font-size: 1.25rem !important;
            }
        }
    </style>
@endpush

@section('content')
    <div x-data="tripsManager()" x-cloak>
        <div class="container-fluid py-4 glass-background-advanced">
            <div class="row mb-4">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h1 class="h3 mb-3 mb-md-0 glass-text-main">
                            <i class="fas fa-bus me-2 text-primary glass-icon-glow"></i> إدارة الرحلات البرية
                        </h1>

                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-glass-secondary glass-button-effect" @click="toggleFilters">
                                <i class="fas fa-filter me-1"></i>
                                <span class="button-text">فلترة</span>
                            </button>

                            <a href="{{ route('admin.trip-types.index') }}"
                                class="btn btn-glass-info glass-button-effect mb-2">
                                <i class="fas fa-tags me-1"></i>
                                <span class="button-text">أنواع الرحلات</span>
                            </a>

                            {{-- الزر الجديد لمدفوعات الشركات --}}
                            <a href="{{ route('admin.company-payments.index') }}"
                                class="btn btn-glass-success glass-button-effect mb-2">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                <span class="button-text">مدفوعات الشركات</span>
                            </a>

                            <a href="{{ route('admin.land-trips-agent-payments.index') }}"
                                class="btn btn-glass-success glass-button-effect mb-2">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                <span class="button-text">حسابات الجهات</span>
                            </a>

                            <a href="{{ route('admin.land-trips.create') }}"
                                class="btn btn-glass-primary glass-button-effect">
                                <i class="fas fa-plus-circle me-1"></i>
                                <span class="button-text">إضافة رحلة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الإحصائيات السريعة بـ Glass Effect -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="card stats-card-frosted-glass glass-card-hover h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-circle glass-icon-wrapper bg-primary bg-opacity-20 p-3 me-3">
                                <i class="fas fa-bus fa-2x text-primary glass-icon-pulse"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fs-sm glass-text-primary fw-bold">إجمالي الرحلات</h6>
                                <h4 class="mb-0 glass-number-display">{{ $totalTrips ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="glass-card-overlay"></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="card stats-card-frosted-glass glass-card-hover h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-circle glass-icon-wrapper bg-success bg-opacity-20 p-3 me-3">
                                <i class="fas fa-check-circle fa-2x text-success glass-icon-pulse"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fs-sm glass-text-success fw-bold">الرحلات النشطة</h6>
                                <h4 class="mb-0 glass-number-display">{{ $activeTrips ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="glass-card-overlay"></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="card stats-card-frosted-glass glass-card-hover h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-circle glass-icon-wrapper bg-warning bg-opacity-20 p-3 me-3">
                                <i class="fas fa-calendar-alt fa-2x text-warning glass-icon-pulse"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fs-sm glass-text-warning fw-bold">رحلات الشهر الحالي</h6>
                                <h4 class="mb-0 glass-number-display">{{ $currentMonthTrips ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="glass-card-overlay"></div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card-frosted-glass glass-card-hover h-100">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="rounded-circle glass-icon-wrapper bg-info bg-opacity-20 p-3 me-3">
                                <i class="fas fa-users fa-2x text-info glass-icon-pulse"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fs-sm glass-text-info fw-bold">إجمالي الحجوزات</h6>
                                <h4 class="mb-0 glass-number-display">{{ $totalBookings ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="glass-card-overlay"></div>
                    </div>
                </div>

                {{-- البطاقات الخاصة للمدفوعات مع Glass Effect متقدم --}}
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admin.company-payments.index') }}" class="text-decoration-none">
                        <div class="card stats-card-premium-glass glass-hover-3d h-100">
                            <div class="card-body d-flex align-items-center p-4">
                                <div class="rounded-circle glass-icon-premium p-3 me-3">
                                    <i class="fas fa-money-bill-wave fa-2x text-success glass-icon-float"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fs-sm glass-text-premium fw-bold">مدفوعات الشركات</h6>
                                    <small class="glass-text-secondary">إدارة المدفوعات</small>
                                    <div class="mt-2">
                                        <i class="fas fa-external-link-alt fa-sm text-primary glass-link-indicator"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="glass-premium-overlay"></div>
                            <div class="glass-shine-effect"></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admin.land-trips-agent-payments.index') }}" class="text-decoration-none">
                        <div class="card stats-card-premium-glass glass-hover-3d h-100">
                            <div class="card-body d-flex align-items-center p-4">
                                <div class="rounded-circle glass-icon-premium p-3 me-3">
                                    <i class="fas fa-handshake fa-2x text-success glass-icon-float"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fs-sm glass-text-premium fw-bold">جهات الحجز</h6>
                                    <small class="glass-text-secondary">إدارة التفاصيل والمدفوعات</small>
                                    <div class="mt-2">
                                        <i class="fas fa-external-link-alt fa-sm text-primary glass-link-indicator"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="glass-premium-overlay"></div>
                            <div class="glass-shine-effect"></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @auth
            @if (auth()->user()->role === 'Admin')
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-link text-primary me-2"></i>
                                        <span class="fw-bold text-dark">وصول سريع:</span>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('admin.company-payments.index') }}"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            مدفوعات الشركات
                                        </a>
                                        <a href="{{ route('admin.land-trips-agent-payments.index') }}"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            حسابات جهات الحجز
                                        </a>
                                        <a href="{{ route('admin.monthly-expenses.index') }}"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-chart-line me-1"></i>
                                            المصاريف الشهرية
                                        </a>
                                        <a href="{{ route('reports.daily') }}" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-chart-bar me-1"></i>
                                            التقارير
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endauth
        <!-- قسم الفلتر الذي يظهر/يختفي -->
        <div class="card shadow-sm mb-4 trip-filter-drawer" :class="{ 'show': showFilters }">
            <div class="card-body">
                <form action="{{ route('admin.land-trips.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <label for="trip_type_id" class="form-label">نوع الرحلة</label>
                        <select name="trip_type_id" id="trip_type_id" class="form-select">
                            <option value="">كل الأنواع</option>
                            @foreach ($tripTypes as $tripType)
                                <option value="{{ $tripType->id }}"
                                    {{ request('trip_type_id') == $tripType->id ? 'selected' : '' }}>
                                    {{ $tripType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="agent_id" class="form-label">جهة الحجز</label>
                        <select name="agent_id" id="agent_id" class="form-select">
                            <option value="">كل الجهات</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}"
                                    {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="status" class="form-label">الحالة</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشطة
                            </option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهية</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="employee_id" class="form-label">الموظف المسؤول</label>
                        <select name="employee_id" id="employee_id" class="form-select">
                            <option value="">كل الموظفين</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="start_date" class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="end_date" class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> تطبيق الفلتر
                            </button>
                            <a href="{{ route('admin.land-trips.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- عرض الفلاتر المطبقة حاليًا -->
        @if (request()->anyFilled(['trip_type_id', 'agent_id', 'status', 'employee_id', 'start_date', 'end_date']))
            <div class="filter-pills mb-3">
                <span class="me-2 ">الفلاتر المطبقة:</span>

                @if (request('trip_type_id'))
                    <span class="filter-pill">
                        نوع الرحلة:
                        {{ $tripTypes->where('id', request('trip_type_id'))->first()->name ?? request('trip_type_id') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('trip_type_id')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('agent_id'))
                    <span class="filter-pill">
                        جهة الحجز: {{ $agents->where('id', request('agent_id'))->first()->name ?? request('agent_id') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('agent_id')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('status'))
                    <span class="filter-pill">
                        الحالة:
                        @if (request('status') == 'active')
                            نشطة
                        @elseif(request('status') == 'inactive')
                            غير نشطة
                        @elseif(request('status') == 'expired')
                            منتهية
                        @else
                            {{ request('status') }}
                        @endif
                        <a href="{{ route('admin.land-trips.index', request()->except('status')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('employee_id'))
                    <span class="filter-pill">
                        الموظف:
                        {{ $employees->where('id', request('employee_id'))->first()->name ?? request('employee_id') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('employee_id')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('start_date'))
                    <span class="filter-pill">
                        من تاريخ: {{ request('start_date') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('start_date')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif

                @if (request('end_date'))
                    <span class="filter-pill">
                        إلى تاريخ: {{ request('end_date') }}
                        <a href="{{ route('admin.land-trips.index', request()->except('end_date')) }}"
                            class="text-danger ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
            </div>
        @endif

        <!-- القائمة الرئيسية -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('all')"
                            :class="{ 'active': currentTab === 'all' }">كل الرحلات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('active')"
                            :class="{ 'active': currentTab === 'active' }">الرحلات النشطة</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('All-Bookings')"
                            :class="{ 'active': currentTab === 'All-Bookings' }">الحجوزات على الرحلات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('bookings')"
                            :class="{ 'active': currentTab === 'bookings' }">رحلات بها حجوزات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="setTab('upcoming')"
                            :class="{ 'active': currentTab === 'upcoming' }">الرحلات القادمة</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.company-payments.index') }}" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>المدفوعات
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="بحث سريع..."
                            x-model="searchTerm" @input="filterTable" @keydown.escape="searchTerm = ''; filterTable()">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-times text-muted" x-show="searchTerm"
                                @click="searchTerm = ''; filterTable();" style="cursor:pointer;"></i>
                            <i class="fas fa-search text-muted" x-show="!searchTerm"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0" style="position: relative;">
                <div class="loading-overlay" x-show="isLoading" style="display: none;">
                    <div class="spinner"></div>
                </div>
                <!-- حاوية جدول الرحلات -->
                <div id="trips-table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-trips">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>نوع الرحلة</th>
                                    <th>تاريخ المغادرة</th>
                                    <th>تاريخ العودة</th>
                                    <th>المدة</th>
                                    <th>جهة الحجز</th>
                                    <th>الموظف</th>
                                    <th>الحالة</th>
                                    <th>الحجوزات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($landTrips as $index => $trip)
                                    <tr class="trip-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-medium">{{ $trip->tripType->name ?? 'غير معروف' }}</span>
                                        </td>
                                        <td>{{ $trip->departure_date->format('Y-m-d') }}</td>
                                        <td>{{ $trip->return_date->format('Y-m-d') }}</td>
                                        <td>{{ $trip->days_count }} أيام</td>
                                        <td>{{ $trip->agent->name ?? 'غير معروف' }}</td>
                                        <td>{{ $trip->employee->name ?? 'غير معروف' }}</td>
                                        <td>
                                            @if ($trip->status == 'active')
                                                <span class="badge bg-success rounded-pill px-2 py-1">نشطة</span>
                                            @elseif($trip->status == 'inactive')
                                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">غير
                                                    نشطة</span>
                                            @elseif($trip->status == 'expired')
                                                <span class="badge bg-secondary rounded-pill px-2 py-1">منتهية</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info rounded-pill px-2 py-1">
                                                {{ $trip->bookings_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.land-trips.show', $trip->id) }}"
                                                    class="btn btn-outline-primary" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.land-trips.edit', $trip->id) }}"
                                                    class="btn btn-outline-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.land-trips.bookings', $trip->id) }}"
                                                    class="btn btn-outline-info" title="الحجوزات">
                                                    <i class="fas fa-calendar-check"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="حذف"
                                                    @click="confirmDelete({{ $trip->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center py-5">
                                                <i class="fas fa-bus fa-3x text-muted mb-3"></i>
                                                <h5>لا توجد رحلات لعرضها</h5>
                                                <p class="text-muted">يمكنك إضافة رحلة جديدة من خلال الضغط على زر "إضافة
                                                    رحلة"
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- حاوية جدول الحجوزات -->
                <div id="bookings-table-container">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="mb-0">قائمة الحجوزات</h5>
                        <button @click="exportBookingsToExcel" class="btn btn-sm btn-outline-success" data-export-excel>
                            <i class="fas fa-file-excel me-1"></i> تصدير إلى Excel
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">#</th>
                                    <th style="width: 20px;text-align: center;font-size: 10px;">رقم الحجز</th>
                                    <th>اسم العميل</th>
                                    <th>الشركة</th>
                                    <th>عدد الغرف</th>
                                    <th>تاريخ الحجز</th>
                                    <th>تاريخ الرحلة</th>
                                    <th>السعر الكلي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allBookings as $index => $booking)
                                    <tr>
                                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                                        <td style="text-align: center;">{{ $booking->id }}</td>
                                        <td>{{ $booking->client_name }}</td>
                                        <td>{{ $booking->company->name }}</td>
                                        <td>{{ $booking->rooms }}</td>
                                        <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('Y-m-d H:i') }}</td>
                                        <td style="direction: rtl; font-size: small;">
                                            {{ $booking->landTrip->departure_date->format('Y-m-d') ?? 'غير معروف' }}
                                            إلى
                                            {{ $booking->landTrip->return_date->format('Y-m-d') ?? 'غير معروف' }}
                                        </td>
                                        <td>
                                            {{ $booking->amount_due_from_company }}
                                            {{ $booking->currency }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center py-5">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <h5>لا توجد حجوزات لعرضها</h5>
                                                <p class="text-muted">لم يتم إجراء أي حجوزات على الرحلات حتى الآن</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $allBookings->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}

                    </div>
                </div>

            </div>

            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        إجمالي الرحلات: {{ $landTrips->total() }}
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $landTrips->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
                    </div>


                </div>
            </div>
        </div>

        <!-- زر الإضافة العائم للشاشات الصغيرة -->
        <a href="{{ route('admin.land-trips.create') }}" class="floating-action-button d-lg-none">
            <i class="fas fa-plus"></i>
        </a>

        <!-- مودال تأكيد الحذف -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">تأكيد الحذف</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>هل أنت متأكد من حذف هذه الرحلة؟ هذا الإجراء لا يمكن التراجع عنه.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <form :action="deleteUrl" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">نعم، قم بالحذف</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // تعريف الدالة خارج Alpine.js
        function tripsManager() {
            return {
                showFilters: false,
                currentTab: 'all',
                searchTerm: '',
                deleteUrl: '',
                deleteModal: null,
                isLoading: false,

                init() {

                    // تهيئة مودال الحذف
                    const modalElement = document.getElementById('deleteModal');
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        this.deleteModal = new bootstrap.Modal(modalElement);
                    }

                    // إخفاء جدول الحجوزات افتراضيًا
                    const bookingsTable = document.getElementById('bookings-table-container');
                    if (bookingsTable) {
                        bookingsTable.style.display = 'none';
                    }

                    // استرجاع التبويب المحفوظ
                    const savedTab = localStorage.getItem('currentTripsTab') || 'all';

                    // تطبيق التبويب المحفوظ
                    this.$nextTick(() => {
                        this.setTab(savedTab);
                    });
                },

                toggleFilters() {
                    this.showFilters = !this.showFilters;
                },

                setTab(tab) {

                    // حفظ التبويب الحالي
                    localStorage.setItem('currentTripsTab', tab);
                    this.currentTab = tab;

                    // الحصول على عناصر الجداول
                    const tripsTable = document.getElementById('trips-table-container');
                    const bookingsTable = document.getElementById('bookings-table-container');

                    if (!tripsTable || !bookingsTable) {
                        console.error('لم يتم العثور على عناصر الجداول!');
                        return;
                    }

                    // إظهار مؤشر التحميل
                    this.isLoading = true;

                    // تأخير بسيط لإظهار مؤشر التحميل
                    setTimeout(() => {
                        // تبديل عرض الجداول
                        if (tab === 'All-Bookings') {
                            tripsTable.style.display = 'none';
                            bookingsTable.style.display = 'block';

                            if (this.searchTerm) {
                                this.filterBookingsTable();
                            }
                        } else {
                            tripsTable.style.display = 'block';
                            bookingsTable.style.display = 'none';

                            this.applyTripFilter(tab);
                        }

                        // إيقاف مؤشر التحميل
                        this.isLoading = false;
                    }, 100);
                },

                applyTripFilter(tab) {

                    const rows = document.querySelectorAll('.trip-row');

                    // إظهار جميع الصفوف أولاً
                    rows.forEach(row => {
                        row.style.display = '';
                    });

                    // إذا كان "كل الرحلات" ولا يوجد بحث، نعرض الكل ونتوقف
                    if (tab === 'all' && !this.searchTerm) {
                        this.updateResultsCount();
                        return;
                    }

                    // تطبيق فلتر البحث أولاً (إن وجد)
                    if (this.searchTerm) {
                        const searchTerm = this.searchTerm.toLowerCase();

                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (!text.includes(searchTerm)) {
                                row.style.display = 'none';
                            }
                        });

                        // إذا كان "كل الرحلات" مع بحث، نتوقف هنا
                        if (tab === 'all') {
                            this.updateResultsCount();
                            return;
                        }
                    }

                    // تطبيق فلتر التبويبات
                    rows.forEach(row => {
                        // تخطي الصفوف المخفية بالفعل
                        if (row.style.display === 'none') return;

                        try {
                            const statusElement = row.querySelector('td:nth-child(8) .badge');
                            const status = statusElement ? statusElement.textContent.trim() : '';

                            const departureDateText = row.querySelector('td:nth-child(3)').textContent.trim();
                            let departureDate;

                            if (departureDateText.includes('-')) {
                                const [year, month, day] = departureDateText.split('-').map(Number);
                                departureDate = new Date(year, month - 1, day);
                            } else if (departureDateText.includes('/')) {
                                const [day, month, year] = departureDateText.split('/').map(Number);
                                departureDate = new Date(year, month - 1, day);
                            } else {
                                departureDate = new Date();
                            }

                            const today = new Date();
                            today.setHours(0, 0, 0, 0);

                            const bookingsElement = row.querySelector('td:nth-child(9) .badge');
                            const bookingsCount = bookingsElement ? parseInt(bookingsElement.textContent.trim()) :
                            0;

                            // تطبيق الفلتر حسب التبويب
                            if (tab === 'active' && status !== 'نشطة') {
                                row.style.display = 'none';
                            } else if (tab === 'upcoming' && (status !== 'نشطة' || departureDate <= today)) {
                                row.style.display = 'none';
                            } else if (tab === 'bookings' && bookingsCount === 0) {
                                row.style.display = 'none';
                            }
                        } catch (err) {
                            console.error('خطأ في معالجة صف الرحلة:', err);
                        }
                    });

                    this.updateResultsCount();
                },

                filterTable() {

                    if (this.currentTab === 'All-Bookings') {
                        this.filterBookingsTable();
                    } else {
                        this.applyTripFilter(this.currentTab);
                    }
                },

                filterBookingsTable() {
                    const searchTerm = this.searchTerm.toLowerCase();
                    const rows = document.querySelectorAll('#bookings-table-container tbody tr');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (searchTerm === '' || text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    this.updateBookingsResultsCount();
                },

                updateResultsCount() {
                    const visibleRows = Array.from(document.querySelectorAll('.trip-row'))
                        .filter(row => row.style.display !== 'none').length;

                    const countElement = document.querySelector('.card-footer .text-muted.small');
                    if (countElement) {
                        if (this.searchTerm) {
                            countElement.textContent = `نتائج البحث: ${visibleRows} رحلة`;
                        } else if (this.currentTab === 'all') {
                            countElement.textContent = `إجمالي الرحلات: {{ $landTrips->total() }}`;
                        } else if (this.currentTab === 'active') {
                            countElement.textContent = `الرحلات النشطة: ${visibleRows}`;
                        } else if (this.currentTab === 'bookings') {
                            countElement.textContent = `رحلات بها حجوزات: ${visibleRows}`;
                        } else if (this.currentTab === 'upcoming') {
                            countElement.textContent = `الرحلات القادمة: ${visibleRows}`;
                        }
                    }
                },

                updateBookingsResultsCount() {
                    const visibleRows = Array.from(document.querySelectorAll('#bookings-table-container tbody tr'))
                        .filter(row => row.style.display !== 'none').length;

                    const countElement = document.querySelector('.card-footer .text-muted.small');
                    if (countElement) {
                        if (this.searchTerm) {
                            countElement.textContent = `نتائج البحث: ${visibleRows} حجز`;
                        } else {
                            countElement.textContent = `إجمالي الحجوزات: ${visibleRows}`;
                        }
                    }
                },

                confirmDelete(id) {
                    this.deleteUrl = `{{ url('admin/land-trips') }}/${id}`;
                    if (this.deleteModal) {
                        this.deleteModal.show();
                    }
                },

                exportBookingsToExcel() {

                    const table = document.querySelector('#bookings-table-container table');
                    if (!table) {
                        alert('لم يتم العثور على جدول الحجوزات!');
                        return;
                    }

                    try {
                        if (typeof XLSX === 'undefined') {
                            console.warn('مكتبة XLSX غير محملة، جاري تحميلها...');

                            const script = document.createElement('script');
                            script.src = 'https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js';
                            script.onload = () => {
                                this.doExportToExcel(table);
                            };
                            script.onerror = () => {
                                alert('فشل في تحميل مكتبة XLSX. يرجى المحاولة مرة أخرى لاحقًا.');
                            };
                            document.head.appendChild(script);
                            return;
                        }

                        this.doExportToExcel(table);
                    } catch (error) {
                        console.error('خطأ أثناء تصدير البيانات:', error);
                        alert('حدث خطأ أثناء تصدير البيانات، يرجى المحاولة مرة أخرى.');
                    }
                },

                doExportToExcel(table) {
                    try {
                        let wb = XLSX.utils.table_to_book(table, {
                            sheet: "الحجوزات"
                        });

                        const fileName = `حجوزات-الرحلات-${new Date().toISOString().split('T')[0]}.xlsx`;
                        XLSX.writeFile(wb, fileName);

                        alert(`تم تصدير البيانات بنجاح إلى الملف: ${fileName}`);
                    } catch (error) {
                        console.error('خطأ أثناء تصدير البيانات:', error);
                        alert('حدث خطأ أثناء تصدير البيانات، يرجى المحاولة مرة أخرى.');
                    }
                }
            };
        }

        // إخفاء جدول الحجوزات مباشرة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            const bookingsTable = document.getElementById('bookings-table-container');
            if (bookingsTable) {
                bookingsTable.style.display = 'none';
            }

        });
    </script>

    <!-- تأكد من تحميل مكتبة XLSX -->
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
@endpush
