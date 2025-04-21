{{-- filepath: resources/views/admin/notifications.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">الإشعارات - آخر 20 إشعارا</h3>
        <div class="list-group">
            @if ($notifications->where('is_read', false)->count())
                <form method="POST" action="{{ route('admin.notifications.markAllRead') }}" class="mb-3">
                    @csrf
                    <button class="btn btn-sm btn-primary">تحديد الكل كمقروء</button>
                </form>
            @endif
            @php $i = 1; @endphp
            @forelse($notifications as $notification)
                <div class="list-group-item {{ $notification->is_read ? 'opacity-50' : '' }}">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                        <span class="badge bg-secondary">{{ $i++ }}</span>
                        <span class="text-muted small">بواسطة:</span>
                        <span
                            class="fw-bold text-primary">{{ $notification->user ? $notification->user->name : 'غير معروف' }}</span>
                        <span
                            class="fw-bold ms-2 {{ $notification->type == 'إضافة' ? 'text-success' : ($notification->type == 'عملية حذف' ? 'text-danger' : 'text-warning') }}">
                            {{ $notification->type }}
                        </span>
                        <span class="mx-1">-</span>
                        <span class="flex-fill">{{ $notification->message }}</span>
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
