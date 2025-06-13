<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">نظام إدارة الحجوزات</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/bookings"><i class="fas fa-calendar-alt me-1"></i>الحجوزات</a>
                </li>
                @auth
                    @if (auth()->user()->role === 'Company')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('company.availabilities.index') }}"><i
                                    class="fas fa-calendar-check me-1"></i>الإتاحات المتاحة</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('company.land-trips.index') }}"><i
                                    class="fas fa-bus me-1"></i>الرحلات البرية</a>
                        </li>
                    @endif
                    @if (auth()->user()->role != 'Company')
                        <li class="nav-item">
                            <a class="nav-link" href="/bookings/create">
                                <i class="fas fa-plus-circle me-1"></i>إضافة حجز
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->role === 'employee')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.availabilities.index') }}">الإتاحات <i
                                    class="fas fa-calendar-check me-1"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.land-trips.index') }}">الرحلات البرية <i
                                    class="fas fa-bus me-1"></i></a>
                        </li>
                    @endif
                    @if (auth()->user()->role === 'Admin')
                        <li class="nav-item">
                            <a class="nav-link" href="/reports/daily">
                                <i class="fas fa-chart-bar me-1"></i>
                                التقارير اليومية
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                الإدارة <i class="fas fa-cogs me-1"></i>
                            </a>
                            <ul class="dropdown-menu text-end" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.employees') }}">الموظفين <i
                                            class="fas fa-user me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.companies') }}">الشركات <i
                                            class="fas fa-building me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.agents') }}">جهات الحجز <i
                                            class="fas fa-concierge-bell me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.hotels') }}">الفنادق <i
                                            class="fas fa-hotel me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.room_types.index') }}">أنواع الغرف <i
                                            class="fas fa-bed me-1"></i></a></li>
                                            {{-- عرض حالة الغرف  --}}
                                            <li>
                                                <a class="dropdown-item" href="{{ route('hotel.rooms.index') }}"> حالة الغرف <i
                                                        class="fas fa-door-open me-1"></i>
                                                    </a>
                                            </li>
                                <li><a class="dropdown-item" href="{{ route('admin.availabilities.index') }}">الإتاحات <i
                                            class="fas fa-clock me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.archived_bookings') }}">الحجوزات
                                        المؤرشفة <i class="fas fa-archive me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.land-trips.index') }}">الرحلات البرية <i
                                            class="fas fa-bus me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.notifications') }}">الإشعارات <i
                                            class="fas fa-bell me-1"></i></a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.monthly-expenses.index') }}">المصاريف
                                        الشهرية <i class="fas fa-money-bill-wave me-1"></i></a></li>
                            </ul>
                        </li>
                    @endif
                @endauth
            </ul>

            <div class="d-flex align-items-center gap-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkModeSwitch">
                    <label class="form-check-label" for="darkModeSwitch">دارك مود</label>
                </div>

                @auth
                    @if (auth()->user()->role === 'Admin' || auth()->user()->role === 'employee')
                        <li class="nav-item dropdown position-relative mx-2 list-unstyled">
                            <a class="nav-link position-relative d-flex align-items-center" href="#"
                                id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fa-lg"></i>
                                @if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                    <span
                                        class="position-absolute top-10 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $unreadNotificationsCount }}
                                    </span>
                                @endif
                            </a>
                           
                            <ul class="dropdown-menu dropdown-menu-end shadow text-end mt-2 notif-dropdown animate__animated animate__fadeIn"
                                aria-labelledby="notifDropdown"
                                style="min-width: 320px; max-width: 90vw; direction: rtl; border-radius: 0.5rem; border: none;">

                                <!-- رأس القائمة بتصميم محسن -->
                                <li class="dropdown-header bg-light p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-bell-exclamation text-primary me-2"></i>
                                        <span class="fw-bold">آخر الإشعارات</span>
                                    </div>
                                    @if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                        <span class="badge bg-primary rounded-pill">{{ $unreadNotificationsCount }}</span>
                                    @endif
                                </li>

                                <!-- منطقة قابلة للتمرير -->
                                <div class="notification-scroll" style="max-height: 350px; overflow-y: auto;">
                                    @forelse($lastNotifications as $notification)
                                        <li
                                            class="notification-item {{ !$notification->is_read ? 'unread-notification' : '' }}">
                                            <div class="dropdown-item small text-wrap py-2 px-3 d-flex align-items-start">

                                                <!-- أيقونة ديناميكية حسب نوع الإشعار -->
                                                <div class="notification-icon me-2">
                                                    @if (Str::contains(strtolower($notification->type), 'تعديل'))
                                                        <div class="icon-circle bg-warning">
                                                            <i class="fas fa-edit text-white"></i>
                                                        </div>
                                                    @elseif(Str::contains(strtolower($notification->type), 'إضافة'))
                                                        <div class="icon-circle bg-success">
                                                            <i class="fas fa-plus text-white"></i>
                                                        </div>
                                                    @elseif(Str::contains(strtolower($notification->type), 'حذف'))
                                                        <div class="icon-circle bg-danger">
                                                            <i class="fas fa-trash text-white"></i>
                                                        </div>
                                                    @elseif(Str::contains(strtolower($notification->type), 'دفعة'))
                                                        <div class="icon-circle bg-info">
                                                            <i class="fas fa-money-bill-wave text-white"></i>
                                                        </div>
                                                    @elseif(Str::contains(strtolower($notification->type), 'حجز'))
                                                        <div class="icon-circle bg-primary">
                                                            <i class="fas fa-calendar-check text-white"></i>
                                                        </div>
                                                    @else
                                                        <div class="icon-circle bg-secondary">
                                                            <i class="fas fa-bell text-white"></i>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- نص الإشعار بترتيب محسن -->
                                                <div
                                                    class="notification-content flex-grow-1 {{ $notification->is_read ? 'opacity-75' : '' }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="fw-bold">{{ $notification->type }}</span>
                                                        <small
                                                            class="text-muted ms-2">{{ $notification->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-0 small notification-message">
                                                        {{ \Illuminate\Support\Str::limit($notification->message, 50) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <!-- تصميم محسن للحالة الفارغة -->
                                        <li class="py-4">
                                            <div class="text-center empty-state">
                                                <i class="far fa-bell-slash text-muted mb-2"
                                                    style="font-size: 2rem; opacity: 0.5;"></i>
                                                <p class="text-muted mb-0">لا توجد إشعارات جديدة</p>
                                            </div>
                                        </li>
                                    @endforelse
                                </div>

                                <!-- الفوتر -->
                                <li>
                                    <hr class="dropdown-divider mb-0 mt-0">
                                </li>
                                <li class="p-2 bg-light">
                                    <a class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center"
                                        href="{{ route('admin.notifications') }}">
                                        <i class="fas fa-list-ul me-2"></i>
                                        عرض كل الإشعارات
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endauth
                        <!-- تنبيه للحجوزات التي تحتاج لتخصيص غرف -->
@auth
    @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'employee')
        @php
            $unassignedBookingsCount = \App\Models\Booking::whereDate('check_in', '<=', now())
                ->whereDate('check_out', '>', now())
                ->whereDoesntHave('roomAssignment', function($q) {
                    $q->where('status', 'active');
                })->count();
        @endphp

        @if($unassignedBookingsCount > 0)
        <li class="nav-item dropdown position-relative mx-2 list-unstyled">
            <a class="nav-link position-relative d-flex align-items-center text-danger" 
               href="{{ route('hotel.rooms.index') }}"
               title="حجوزات بحاجة لتخصيص غرف">
                <i class="fas fa-door-open fa-lg"></i>
                <span class="position-absolute top-10 start-100 translate-middle badge rounded-pill bg-danger animation-pulse">
                    {{ $unassignedBookingsCount }}
                </span>
            </a>
        </li>
        @endif
    @endif
@endauth
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button"
                            id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end text-end mt-2" aria-labelledby="userDropdown">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">تسجيل الخروج</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<style>
    .animation-pulse {
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        70% {
            transform: scale(1.1);
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            transform: scale(1);
        }
    }
</style>
