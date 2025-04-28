<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';"> --}}
    <title>@yield('title', 'نظام إدارة الحجوزات')</title>
    @yield('favicon')
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- Font Awesome CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Dark Mode CSS -->
    <link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/nav-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bookingsStyle.css') }}">
    {{-- Stack for page specific styles --}}
    @stack('styles')

</head>

<body class="d-flex flex-column min-vh-100">

    @include('partials.navbar')

    <main class="container mt-4 flex-grow-1">
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Scripts Section - Organized --}}

    <!-- 1. jQuery (Must be first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- 2. Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 3. jQuery UI (for Datepicker) -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- 4. Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- 5. Alpine.js (defer is recommended) -->
    <script src="//unpkg.com/alpinejs" defer></script>

    {{-- Global Initializations --}}
    <script>
        $(document).ready(function() {
            // Initialize DatePicker
            try {
                if (typeof $.fn.datepicker === 'function') {
                    $(".datepicker").datepicker({
                        dateFormat: "dd/mm/yy", // Match validation format d/m/y
                        changeMonth: true,
                        changeYear: true,
                        showButtonPanel: true
                    });
                } else {
                    console.warn("jQuery UI Datepicker not loaded or initialized correctly.");
                }
            } catch (e) {
                console.error("Error initializing datepicker:", e);
            }

            // Initialize Select2
            try {
                if (typeof $.fn.select2 === 'function') {
                    $('.select2').select2({
                        theme: 'bootstrap-5',
                        placeholder: $(this).data('placeholder') || "اختر...",
                        allowClear: true,
                        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                    });
                } else {
                    console.warn("Select2 not loaded or initialized correctly.");
                }
            } catch (e) {
                console.error("Error initializing select2:", e);
            }
        });
    </script>

    {{-- Custom Global Scripts (if any) --}}
    {{-- Example: <script src="{{ asset('js/global-helpers.js') }}"></script> --}}
    <script src="{{ asset('js/booking-selector.js') }}"></script> {{-- Make sure this doesn't re-initialize libraries --}}


    {{-- Dark Mode Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            const sw = document.getElementById('darkModeSwitch');
            if (sw) {
                const theme = localStorage.getItem('theme') || 'light';
                html.setAttribute('data-theme', theme);
                sw.checked = (theme === 'dark');

                sw.addEventListener('change', () => {
                    const t = sw.checked ? 'dark' : 'light';
                    html.setAttribute('data-theme', t);
                    localStorage.setItem('theme', t);
                });
            } else {
                console.warn("Dark mode switch element not found.");
            }
        });
    </script>

    {{-- Notification Update Script --}}
    <script>
        function fetchNotifications() {
            fetch('/api/notifications/unread-count')
                .then(res => {
                    if (!res.ok) {
                        console.error('Failed to fetch notifications:', res.status, res.statusText);
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    const badge = document.querySelector('.bi-bell')?.parentElement?.querySelector('.badge');
                    if (badge) {
                        badge.innerText = data.count;
                        badge.style.display = data.count > 0 ? 'inline-block' : 'none';
                    } else {
                        // console.warn("Notification badge element not found.");
                    }
                })
                .catch(error => console.error('Error fetching or processing notifications:', error));
        }
        document.addEventListener('DOMContentLoaded', fetchNotifications);
        setInterval(fetchNotifications, 30000); // Fetch every 30 seconds
    </script>

    {{-- Stack for page specific scripts (Keep only ONE at the very end) --}}
    @stack('scripts')

</body>
</html>
