@extends('layouts.app')
@section('title', ' الإشعارات')
@section('favicon')

    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection
@push('styles')
    <style>
        /* تحسينات الشكل */
        :root {
            --primary: #3490dc;
            --success: #38c172;
            --danger: #e3342f;
            --warning: #f6993f;
            --info: #6cb2eb;
            --dark: #343a40;
            --light: #f8f9fa;
            --card-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
            --hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.18);
            --transition: all 0.25s cubic-bezier(.4, 2, .6, 1);
            --border-radius: 1.1rem;
        }

        body {
            background: #f4f8fb;
        }

        .notifications-container {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            transition: var(--transition);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            letter-spacing: 0.5px;
        }

        .page-title small {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 400;
            margin-right: 1rem;
        }

        .page-title::before {
            content: '🔔';
            font-size: 2.1rem;
            margin-left: 0.5rem;
        }

        .filter-wrapper {
            margin-bottom: 1.5rem;
        }

        .filter-scroll {
            display: flex;
            overflow-x: auto;
            padding: 0.5rem 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .filter-scroll::-webkit-scrollbar {
            display: none;
        }

        .filter-buttons {
            display: flex;
            gap: 0.7rem;
            min-width: 100%;
        }

        .filter-btn {
            border-radius: 2rem;
            padding: 0.5rem 1.3rem;
            font-size: 1rem;
            font-weight: 700;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            color: #333;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(52, 144, 220, 0.04);
            position: relative;
            outline: none;
        }

        .filter-btn.active,
        .filter-btn:focus {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 4px 16px rgba(52, 144, 220, 0.13);
            transform: scale(1.06);
        }

        .filter-btn i {
            margin-left: 0.5rem;
            font-size: 1.1em;
        }

        .filter-indicator {
            display: none;
        }

        .notification-list {
            min-height: 120px;
        }

        .notification-item {
            margin-bottom: 1.2rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 12px rgba(52, 144, 220, 0.07);
            background: #fff;
            transition: var(--transition);
            border: 1.5px solid #f1f3f7;
            position: relative;
            animation: fadeIn 0.4s cubic-bezier(.4, 2, .6, 1);
        }

        .notification-item:hover {
            box-shadow: var(--hover-shadow);
            border-color: var(--primary);
            transform: translateY(-2px) scale(1.01);
        }

        .notification-item.read {
            opacity: 0.7;
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .notification-item .notification-content {
            padding: 1.2rem 1.1rem;
        }

        .notification-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.7rem;
        }

        .notification-author .badge {
            background: var(--primary);
            color: #fff;
            font-size: 1rem;
            margin-left: 0.7rem;
            border-radius: 50%;
            width: 2.1rem;
            height: 2.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-type {
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
            background: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-type.security-alert {
            background: var(--danger);
        }

        .notification-type.land-trip {
            background: var(--info);
        }

        .notification-type.booking {
            background: var(--primary);
        }

        .notification-type.payment {
            background: var(--success);
        }

        .notification-type.login {
            background: var(--warning);
            color: #212529;
        }

        .notification-type.delete {
            background: var(--danger);
        }

        .notification-message {
            margin: 0.8rem 0 0.5rem 0;
            font-size: 1.08rem;
            color: #222;
            line-height: 1.7;
        }

        .notification-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.7rem;
            padding-top: 0.7rem;
            border-top: 1px dashed #e2e8f0;
        }

        .notification-time {
            font-size: 0.95rem;
            color: #718096;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .action-btn {
            border-radius: 2rem;
            padding: 0.35rem 1.1rem;
            font-size: 0.98rem;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.3rem;
            border: none;
            outline: none;
            background: var(--success);
            color: #fff;
            box-shadow: 0 2px 8px rgba(56, 193, 114, 0.08);
        }

        .action-btn:hover,
        .action-btn:focus {
            background: #2fa36c;
            color: #fff;
            transform: scale(1.04);
        }

        .already-read {
            background: #e2e8f0;
            color: #64748b;
            cursor: not-allowed;
            box-shadow: none;
        }

        .loading-container {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
            border-radius: var(--border-radius);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s;
        }

        .loading-container.visible {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 44px;
            height: 44px;
            border: 5px solid #e3eaf2;
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .no-notifications {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
            border-radius: var(--border-radius);
            box-shadow: 0 0 0 1px #e2e8f0;
        }

        .no-notifications i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #a0aec0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .notifications-container {
                padding: 1rem 0.5rem;
            }

            .page-title {
                font-size: 1.3rem;
            }

            .notification-content {
                padding: 0.7rem 0.5rem;
            }

            .notification-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.3rem;
            }

            .notification-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.7rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3">
        <div class="notifications-container">
            <h3 class="page-title">الإشعارات <small>آخر 20 إشعارا</small></h3>

            <!-- أزرار الفلترة -->
            <div class="filter-wrapper">
                <div class="filter-scroll" id="filter-scroll">
                    <div class="filter-buttons">
                        <a href="javascript:void(0)" data-url="{{ route('admin.notifications') }}" data-filter="all"
                            class="filter-btn btn {{ !$currentFilter ? 'active btn-dark' : 'btn-outline-dark' }}">
                            <i class="fas fa-list-ul"></i> عرض الكل
                        </a>

                        <a href="javascript:void(0)" data-url="{{ route('admin.notifications', ['filter' => 'bookings']) }}"
                            data-filter="bookings"
                            class="filter-btn btn {{ $currentFilter == 'bookings' ? 'active btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-calendar-check"></i> الحجوزات
                        </a>

                        <a href="javascript:void(0)" data-url="{{ route('admin.notifications', ['filter' => 'payments']) }}"
                            data-filter="payments"
                            class="filter-btn btn {{ $currentFilter == 'payments' ? 'active btn-success' : 'btn-outline-success' }}">
                            <i class="fas fa-money-bill-wave"></i> الدفعات
                        </a>

                        <a href="javascript:void(0)"
                            data-url="{{ route('admin.notifications', ['filter' => 'availabilities']) }}"
                            data-filter="availabilities"
                            class="filter-btn btn {{ $currentFilter == 'availabilities' ? 'active btn-info' : 'btn-outline-info' }}">
                            <i class="fas fa-calendar-alt"></i> الإتاحات
                        </a>

                        <a href="javascript:void(0)"
                            data-url="{{ route('admin.notifications', ['filter' => 'land-trips']) }}"
                            data-filter="land-trips"
                            class="filter-btn btn {{ $currentFilter == 'land-trips' ? 'active btn-info' : 'btn-outline-info' }}">
                            <i class="fas fa-bus-alt"></i> الرحلات البرية
                        </a>

                        <a href="javascript:void(0)" data-url="{{ route('admin.notifications', ['filter' => 'logins']) }}"
                            data-filter="logins"
                            class="filter-btn btn {{ $currentFilter == 'logins' ? 'active btn-warning' : 'btn-outline-warning' }}">
                            <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                        </a>
                    </div>
                    <div class="filter-indicator"></div>
                </div>
            </div>

            <!-- زر تحديد الكل كمقروء -->
            @if (auth()->user()->role === 'Admin' && $notifications->where('is_read', false)->count())
                <div class="mb-3 text-end">
                    <button type="button" id="mark-all-read-btn" class="btn btn-primary btn-sm rounded-pill">
                        <i class="fas fa-check-double"></i> تحديد الكل كمقروء
                    </button>
                </div>
            @endif

            @php
                $landTripTypes = [
                    'إضافة رحلة',
                    'تعديل رحلة',
                    'حذف رحلة',
                    'حجز رحلة',
                    'تحديث_تلقائي',
                    'تحديث حجز رحلة',
                    'حذف حجز رحلة',
                ];
                $hasSecurityAlert = $notifications->contains(function ($n) {
                    return $n->type == 'تنبيه أمني';
                });
            @endphp
            @php
                // تعريف أنواع إشعارات تسجيل الدخول والخروج
                $loginTypes = ['login', 'logout', 'تسجيل دخول', 'تسجيل خروج'];
            @endphp
            @php
                // تعريف أنواع إشعارات الدفعات والمتابعة المالية
                $paymentTypes = [
                    // الإشعارات العربية
                    'دفعة جديدة',
                    'تعديل دفعة',
                    'حذف دفعة',
                    'خصم مطبق',
                    'متابعة مالية عالية الأهمية',
                    'اكتمال دفعة الوكيل',
                    'اكتمال دفعة الشركة',
                    'دفعة جزئية للوكيل',
                    'دفعة جزئية للشركة',
                    'دفعة معلقة للوكيل',
                    'دفعة معلقة للشركة',
                    'تغيير تاريخ المتابعة',
                    'تغيير مستوى الأولوية',
                    'إنشاء متابعة مالية جديدة',
                    'تغيير حالة الدفع',
                    'تغيير قيمة الدفعة',

                    // الإشعارات الإنجليزية
                    'high_priority_tracking',
                    'agent_payment_completed',
                    'company_payment_completed',
                    'agent_payment_partial',
                    'company_payment_partial',
                    'agent_payment_pending',
                    'company_payment_pending',
                    'follow_up_date_change',
                    'priority_level_change',
                    'financial_tracking_created',
                    'payment_status_change',
                    'payment_amount_change',
                ];
            @endphp

            @if ($hasSecurityAlert)
                <div id="security-alert-placeholder"></div>
            @endif

            <!-- قائمة الإشعارات -->
            <div class="notification-list" id="notifications-list">
                <div class="loading-container" id="loading-overlay">
                    <div class="spinner"></div>
                </div>

                <div id="notifications-content">
                    @php $i = 1; @endphp
                    @forelse($notifications as $notification)
                        @php
                            // تخطي جميع الإشعارات التي ليست للمستخدم الحالي
                            if ($notification->user_id != auth()->id()) {
                                continue;
                            }
                        @endphp

                        @if ($currentFilter == 'land-trips' && !in_array($notification->type, $landTripTypes))
                            @continue
                        @endif
                        {{-- إضافة التحقق من فلتر الدفعات --}}
                        @if ($currentFilter == 'payments')
                            {{-- طباعة معلومات تشخيصية للتأكد من أن الإشعارات تدرج ضمن فلتر الدفعات --}}
                            @php
                                $notificationTypes = [];
                                $matchedNotifications = 0;
                                $unmatchedNotifications = 0;
                            @endphp

                            @foreach ($notifications as $notification)
                                @php
                                    $isPaymentNotification = in_array($notification->type, $paymentTypes);

                                    if (!$isPaymentNotification) {
                                        $isPaymentNotification =
                                            strpos($notification->type, 'دفع') !== false ||
                                            strpos($notification->type, 'مالي') !== false ||
                                            strpos($notification->type, 'مستوى') !== false ||
                                            strpos($notification->type, 'أولوية') !== false ||
                                            strpos($notification->type, 'payment') !== false ||
                                            strpos($notification->type, 'financial') !== false ||
                                            strpos($notification->type, 'track') !== false ||
                                            strpos($notification->type, 'priority') !== false;
                                    }

                                    if ($isPaymentNotification) {
                                        $matchedNotifications++;
                                        if (!in_array($notification->type, $notificationTypes)) {
                                            $notificationTypes[] = $notification->type;
                                        }
                                    } else {
                                        $unmatchedNotifications++;
                                    }
                                @endphp
                            @endforeach

                       
                        @endif
                        @php
                            // تحديد نوع الإشعار وإضافة الصفات المناسبة
                            // تحديد نوع الإشعار وإضافة الصفات المناسبة
                            $itemClass = 'notification-item';
                            $typeClass = '';
                            $typeIcon = 'fas fa-bell';

                            if ($notification->is_read) {
                                $itemClass .= ' read';
                            }

                            // تحديد النوع بناءً على type أو على البيانات المخزنة في data
                            $isPaymentType = in_array($notification->type, $paymentTypes);

                            // تحقق إضافي للكلمات المتعلقة بالدفعات والمتابعة
                            if (!$isPaymentType) {
                                $isPaymentType =
                                    strpos($notification->type, 'دفع') !== false ||
                                    strpos($notification->type, 'مالي') !== false ||
                                    strpos($notification->type, 'مستوى') !== false ||
                                    strpos($notification->type, 'أولوية') !== false ||
                                    strpos($notification->type, 'payment') !== false ||
                                    strpos($notification->type, 'financial') !== false ||
                                    strpos($notification->type, 'track') !== false ||
                                    strpos($notification->type, 'priority') !== false;
                            }

                            if ($notification->type == 'تنبيه أمني') {
                                $itemClass .= ' security-alert';
                                $typeClass = 'security-alert';
                                $typeIcon = 'fas fa-shield-alt';
                            } elseif (in_array($notification->type, $landTripTypes)) {
                                $itemClass .= ' land-trip';
                                $typeClass = 'land-trip';
                                $typeIcon = 'fas fa-bus-alt';
                            } elseif (
                                in_array($notification->type, ['إضافة حجز', 'تأكيد حجز', 'تعديل حجز', 'إلغاء حجز'])
                            ) {
                                $itemClass .= ' booking';
                                $typeClass = 'booking';
                                $typeIcon = 'fas fa-calendar-check';
                            } elseif ($isPaymentType) {
                                $itemClass .= ' payment';
                                $typeClass = 'payment';
                                $typeIcon = 'fas fa-money-bill-wave';
                            } elseif (in_array($notification->type, ['إتاحة', 'availability', 'allotment'])) {
                                $itemClass .= ' availability';
                                $typeClass = 'booking';
                                $typeIcon = 'fas fa-calendar-alt';
                            } elseif (str_contains($notification->type, 'حذف')) {
                                $itemClass .= ' delete';
                                $typeClass = 'delete';
                                $typeIcon = 'fas fa-trash-alt';
                            }

                        @endphp
                        <div class="{{ $itemClass }}" style="animation-delay: {{ ($i - 1) * 0.05 }}s">
                            <div class="notification-content">
                                <div class="notification-header">
                                    <div class="notification-author">
                                        <span class="badge bg-secondary">{{ $i++ }}</span>
                                        <span class="text-muted small">بواسطة:</span>
                                        <strong
                                            class="text-primary">{{ $notification->user ? $notification->user->name : 'غير معروف' }}</strong>
                                    </div>

                                    <div class="notification-type {{ $typeClass }}">
                                        <i class="{{ $typeIcon }}"></i>
                                        {{ $notification->type }}
                                    </div>
                                </div>

                                <div class="notification-message">
                                    {{ $notification->message }}
                                </div>

                                <div class="notification-footer">
                                    <div class="notification-time">
                                        <i class="far fa-clock"></i>
                                        <span
                                            title="{{ $notification->created_at->format('Y-m-d H:i:s') }}">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>

                                    @if (!$notification->is_read)
                                        <button class="btn btn-sm action-btn read-btn mark-read"
                                            data-id="{{ $notification->id }}">
                                            <i class="fas fa-check"></i> تمت القراءة
                                        </button>
                                    @else
                                        <button class="btn btn-sm action-btn already-read" disabled>
                                            <i class="fas fa-check-double"></i> تمت القراءة
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="no-notifications">
                            <i class="far fa-bell-slash"></i>
                            <p>لا توجد إشعارات حالياً</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- عرض أزرار Pagination -->
            <div id="pagination-container" class="d-flex justify-content-center mt-4">
                {{ $notifications->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
            </div>

            <!-- محتوى Toast -->
            <div class="toast-container" id="toast-container"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            // SweetAlert للتنبيه الأمني
            @if ($hasSecurityAlert)
                Swal.fire({
                    title: 'تنبيه أمني',
                    text: 'تم رصد محاولة اختراق أو فحص الصفحة!',
                    icon: 'warning',
                    confirmButtonText: 'تم الفهم'
                });
            @endif

            // تفعيل زر تحديد الكل كمقروء
            $('#mark-all-read-btn').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحديث...');
                $.post('/admin/notifications/mark-all-read', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }).done(function() {
                    $('.notification-item:not(.read)').addClass('read');
                    $('.mark-read').each(function() {
                        $(this).replaceWith(`<button class="btn btn-sm action-btn already-read" disabled>
                    <i class="fas fa-check-double"></i> تمت القراءة
                </button>`);
                    });
                    $btn.closest('.mb-3').hide();
                    showToast('تمت العملية', 'تم تحديد جميع الإشعارات كمقروءة', 'success');
                }).fail(function() {
                    $btn.prop('disabled', false).html(
                        '<i class="fas fa-check-double"></i> تحديد الكل كمقروء');
                    Swal.fire('خطأ', 'حدث خطأ أثناء تحديث حالة الإشعارات، يرجى المحاولة مرة أخرى.',
                        'error');
                });
            });

            // تفعيل زر قراءة إشعار واحد
            $(document).on('click', '.mark-read', function() {
                var $btn = $(this);
                var id = $btn.data('id');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.post(`/admin/notifications/${id}/read`, { // <-- هنا التعديل
                    _token: $('meta[name="csrf-token"]').attr('content')
                }).done(function() {
                    $btn.closest('.notification-item').addClass('read');
                    $btn.replaceWith(`<button class="btn btn-sm action-btn already-read" disabled>
                <i class="fas fa-check-double"></i> تمت القراءة
            </button>`);
                    showToast('تمت العملية', 'تم تحديد الإشعار كمقروء', 'success');
                }).fail(function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-check"></i> تمت القراءة');
                    Swal.fire('خطأ', 'حدث خطأ أثناء تحديث حالة الإشعار، يرجى المحاولة مرة أخرى.',
                        'error');
                });
            });

            // تفعيل أزرار الفلترة مع تأثير متحرك
            $('.filter-btn').on('click', function(e) {
                e.preventDefault();
                if ($(this).hasClass('active')) return;
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                // ✅ إضافة تحديث URL بدون reload
                var url = $(this).data('url');
                window.history.pushState({}, '', url);
                // تحميل الإشعارات بالفلتر المطلوب
                fetchNotifications($(this).data('url'));
            });

            // جلب الإشعارات بالفلترة (AJAX)
            function fetchNotifications(url) {
                $('#loading-overlay').addClass('visible');
                $.ajax({
                    url: url,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function(html) {
                    var $html = $(html);
                    $('#notifications-content').html($html.find('#notifications-content').html());
                    $('#pagination-container').html($html.find('#pagination-container').html());
                    $('#loading-overlay').removeClass('visible');
                    showToast('تم التحديث', 'تم تطبيق الفلتر بنجاح', 'success');
                }).fail(function() {
                    $('#loading-overlay').removeClass('visible');
                    Swal.fire('خطأ', 'حدث خطأ أثناء جلب البيانات، يرجى المحاولة مرة أخرى.', 'error');
                });
            }

            // توست للتنبيهات السريعة
            function showToast(title, message, type = 'success') {
                var $toast = $(`
            <div class="toast" style="background:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.13);border-radius:0.7rem;">
                <div class="toast-header" style="font-weight:700;">
                    <i class="fas ${type === 'success' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger'} toast-icon"></i>
                    <strong>${title}</strong>
                    <button type="button" class="btn-close ms-auto" onclick="$(this).closest('.toast').remove()"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `);
                $('#toast-container').append($toast);
                setTimeout(function() {
                    $toast.fadeOut(400, function() {
                        $(this).remove();
                    });
                }, 2500);
            }
            $(document).on('click', '#pagination-container .page-link', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (!url || url === '#') return;

                $('#loading-overlay').addClass('visible');
                $.ajax({
                    url: url,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function(html) {
                    var $html = $(html);
                    $('#notifications-content').html($html.find('#notifications-content').html());
                    $('#pagination-container').html($html.find('#pagination-container').html());
                    $('#loading-overlay').removeClass('visible');
                }).fail(function() {
                    $('#loading-overlay').removeClass('visible');
                    Swal.fire('خطأ', 'حدث خطأ أثناء تحميل الصفحة، يرجى المحاولة مرة أخرى.',
                        'error');
                });
            });
        });
    </script>
@endpush
