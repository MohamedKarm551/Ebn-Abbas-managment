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
                    <a class="nav-link" href="/bookings">الحجوزات</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/bookings/create">إضافة حجز</a>
                </li>
                @auth
                    @if (auth()->user()->role === 'Admin')
                        <li class="nav-item">
                            <a class="nav-link" href="/reports/daily">التقارير اليومية</a>
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
                    @if (auth()->user()->role === 'Admin')
                        @php
                            $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();
                        @endphp
                        <li class="nav-item dropdown position-relative mx-2 list-unstyled">
                            <a class="nav-link position-relative d-flex align-items-center" href="#"
                                id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-4"></i>
                                @if ($unreadNotificationsCount > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $unreadNotificationsCount }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow text-end mt-2" aria-labelledby="notifDropdown"
                                style="min-width: 320px; max-width: 90vw; direction: rtl;">
                                <li class="dropdown-header fw-bold">آخر 5 إشعارات</li>
                                @forelse($lastNotifications as $notification)
                                    <li>
                                        <div class="dropdown-item small {{ $notification->is_read ? 'opacity-50' : '' }} text-wrap"
                                            style="white-space: normal;">
                                            <span class="fw-bold">{{ $notification->type }}</span> -
                                            {{ \Illuminate\Support\Str::limit($notification->message, 50) }}
                                            <br>
                                            <span
                                                class="text-muted small">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item text-muted small">لا توجد إشعارات</span></li>
                                @endforelse
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-center text-primary"
                                        href="{{ route('admin.notifications') }}">
                                        عرض كل الإشعارات
                                    </a>
                                </li>
                            </ul>
                        </li>
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
