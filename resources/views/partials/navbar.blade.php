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
                    @endauth
                @endif
                @auth
                    @if (auth()->user()->role != 'Company')
                        <li class="nav-item">
                            <a class="nav-link" href="/bookings/create">
                                <i class="fas fa-plus-circle me-1"></i>إضافة حجز
                            </a>
                        </li>
                    @endif
                @endauth
                @auth
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
                @endauth
                @auth
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
                                            class="fas fa-bed me-1"></i></a>
                                </li> {{-- *** أضف هذا السطر *** --}}
                                <li><a class="dropdown-item" href="{{ route('admin.availabilities.index') }}">الإتاحات
                                        <i class="fas fa-clock me-1"></i></a>
                                </li>


                                <li><a class="dropdown-item" href="{{ route('admin.archived_bookings') }}">
                                        الحجوزات المؤرشفة <i class="fas fa-archive me-1"></i> </a></li>
                                @auth
                                    @if (auth()->user()->role === 'Admin' || auth()->user()->role === 'employee')
                                        <li><a class="dropdown-item" href="{{ route('admin.land-trips.index') }}">
                                            الرحلات البرية <i class="fas fa-bus me-1"></i>
                                            </a>
                                        </li>
                                    @endif
                                @endauth <li><a class="dropdown-item" href="{{ route('admin.notifications') }}">
                                        الإشعارات<i class="fas fa-bell me-1"></i>
                                    </a></li>
                                </a>
                        </li>
                </ul>
                </li>
                {{-- *** نهاية الإضافة *** --}}
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
                        {{-- أو أي دور تستخدمه للإشعارات --}}
                        {{-- @php
                            $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();
                        @endphp --}}
                        <li class="nav-item dropdown position-relative mx-2 list-unstyled">
                            <a class="nav-link position-relative d-flex align-items-center" href="#"
                                id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg id="notification-bell-svg" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                    {{-- المسار الداخلي (جسم الجرس واللسان) - زي ما هو --}}
                                    <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm0-14.082L7.203 2.08a4.002 4.002 0 0 0-3.203 3.92c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.918z" />
                                    {{-- المسار الخارجي (الحدود اللي النقطة هتمشي عليها) - عدلناه عشان يبقى مسار واحد واديناه ID --}}
                                    <path id="bell-outline-path" fill="none" stroke="currentColor" stroke-width="0.1"
                                        {{-- خليناه خط رفيع جداً وغير مرئي تقريباً بس عشان نحدد المسار --}}
                                        d="M14.22,12 C14.443,12.447 14.701,12.801 15,13 L1,13 C1.299,12.801 1.557,12.447 1.78,12 C2.68,10.2 3,6.88 3,6 C3,3.58 4.72,1.56 7.005,1.099 A1,1 0 0 1 8,1 A1,1 0 0 1 8.995,1.099 A5.002,5.002 0 0 1 13,6 C13,6.88 13.32,10.2 14.22,12 Z" />
                                    {{-- عنصر النقطة اللي هتتحرك (هنضيفه هنا) --}}
                                    <circle id="moving-dot" r="0.5" fill="rgba(220, 53, 69, 0.8)"> {{-- نقطة حمراء نص شفافة --}}
                                        {{-- هنا هنطبق الأنيميشن بالـ CSS --}}
                                    </circle>
                                </svg>
                                @if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $unreadNotificationsCount }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow text-end mt-2 notif-dropdown"
                                aria-labelledby="notifDropdown"
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
