{{-- filepath: resources/views/admin/notifications.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">الإشعارات - آخر 20 إشعارا</h3>
        {{-- ==================== أزرار الفلترة ==================== --}}
        <div class="mb-3 btn-group" role="group" aria-label="Notification Filters">
            {{-- زرار عرض الكل --}}
            <a href="{{ route('admin.notifications') }}"
                class="btn btn-outline-secondary {{ !$currentFilter ? 'active' : '' }}">
                عرض الكل
            </a>
            {{-- زرار فلتر الحجوزات --}}
            <a href="{{ route('admin.notifications', ['filter' => 'bookings']) }}"
                class="btn btn-outline-primary {{ $currentFilter == 'bookings' ? 'active' : '' }}">
                الحجوزات
            </a>
            {{-- زرار فلتر الدفعات --}}
            <a href="{{ route('admin.notifications', ['filter' => 'payments']) }}"
                class="btn btn-outline-success {{ $currentFilter == 'payments' ? 'active' : '' }}">
                الدفعات
            </a>
            {{-- زرار فلتر الإتاحات --}}
            <a href="{{ route('admin.notifications', ['filter' => 'availabilities']) }}"
                class="btn btn-outline-info {{ $currentFilter == 'availabilities' ? 'active' : '' }}">
                الإتاحات
            </a>
            {{-- زرار تسجيلات الدخول والخروج  --}}
            <a href="{{ route('admin.notifications', ['filter' => 'logins']) }}"
                class="btn btn-outline-warning {{ $currentFilter == 'logins' ? 'active' : '' }}">
                تسجيلات دخول وخروج
            </a>
            {{-- ممكن تضيف أزرار فلاتر تانية هنا بنفس الطريقة --}}
            {{-- مثال:
                    <a href="{{ route('admin.notifications', ['filter' => 'users']) }}" class="btn btn-outline-warning {{ $currentFilter == 'users' ? 'active' : '' }}">
                        المستخدمين
                    </a>
                     --}}
        </div>
        {{-- ==================== نهاية أزرار الفلترة ==================== --}}


        <div class="list-group">
            {{-- *** بداية التعديل: إخفاء زر تحديد الكل للموظف *** --}}
            @if (auth()->user()->role === 'Admin' && $notifications->where('is_read', false)->count())
                <form method="POST" action="{{ route('admin.notifications.markAllRead') }}" class="mb-3">
                    @csrf
                    <button class="btn btn-sm btn-primary">تحديد الكل كمقروء</button>
                </form>
            @endif
            {{-- *** نهاية التعديل *** --}}
            @php $i = 1; @endphp
            @php
    $hasSecurityAlert = $notifications->contains(function($n) { return $n->type == 'تنبيه أمني'; });
@endphp

@if($hasSecurityAlert)
    <script>
        alert('تنبيه أمني: تم رصد محاولة اختراق أو فحص الصفحة!');
    </script>
@endif
            @forelse($notifications as $notification)
                <div class="list-group-item {{ $notification->is_read ? 'opacity-50' : '' }}"
                    @if ($notification->type == 'تنبيه أمني') style="background:linear-gradient(90deg,#b30000 60%,#ff4444 100%)!important; color:#fff; border:2px solid #b30000; box-shadow:0 0 10px #b30000;" @endif>
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                        <span class="badge bg-secondary">{{ $i++ }}</span>
                        <span class="text-muted small">بواسطة:</span>
                        <span
                            class="fw-bold text-primary">{{ $notification->user ? $notification->user->name : 'غير معروف' }}</span>
                        <span
                            @if ($notification->type == 'تنبيه أمني') class="fw-bold ms-2 bg-danger text-white px-3 py-2 rounded"
                    style="text-color: #fff; font-size: 0.9rem; font-weight: bold; text-align: center; width: 100px;"
    @else
        class="fw-bold ms-2
            @if (
                $notification->type == 'إضافة حجز' ||
                    $notification->type == 'تأكيد حجز' ||
                    $notification->type == 'تعديل حجز' ||
                    $notification->type == 'إلغاء حجز')
                text-primary
            @elseif($notification->type == 'دفعة جديدة')
                text-success
            @elseif($notification->type == 'إتاحة' || $notification->type == 'availability' || $notification->type == 'allotment')
                text-info
            @elseif(str_contains($notification->type, 'login') ||
                    str_contains($notification->type, 'logout') ||
                    $notification->type == 'تسجيل دخول' ||
                    $notification->type == 'تسجيل خروج')
                text-warning
            @elseif(str_contains($notification->type, 'حذف'))
                text-danger
            @else
                text-secondary @endif "
                             @endif
                            >
                            {{ $notification->type }}
                        </span>
                        <span class="mx-1">-</span>
                        <span class="text-break w-100 flex-md-grow-1"
                            style="min-width: 0; word-break: break-all;">{{ $notification->message }}</span>
                        <span class="text-muted small ms-2" title="{{ $notification->created_at->format('Y-m-d H:i:s') }}">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="mt-2">
                        @if (!$notification->is_read)
                            <form method="POST" action="{{ route('admin.notifications.markRead', $notification->id) }}"
                                class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-success">تمت القراءة</button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" disabled>تمت القراءة</button>
                        @endif
                    </div>
                </div>
                @empty
                    <div class="alert alert-info">لا توجد إشعارات حالياً.</div>
                @endforelse
            </div>
            <!-- عرض أزرار Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    @endsection
