<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الحجوزات</title>
    {{-- تنسيق خاص لصفحة كل الحجوزات أنا رابطه بالصفحة هنا --}}
    <link rel="stylesheet" href="{{ asset('css/bookingsStyle.css') }}">
    <!-- إضافة Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- إضافة أيقونات Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- Font Awesome CDN Link --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/">نظام إدارة الحجوزات</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/bookings">الحجوزات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bookings/create">إضافة حجز</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/reports/daily">التقارير اليومية</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        @yield('content')
    </div>
    <!-- نشيل الـ scripts القديمة ونحط الترتيب الصحيح -->
    </div>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI JS -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".datepicker").datepicker({
                dateFormat: "dd/mm/yy",
                changeMonth: true,
                changeYear: true
            });
        });
    </script>
    @yield('scripts')
    {{-- ده لازم يكون موجود عشان كود تحميل الصور يكون شغال --}}
       {{-- كود الاسكريبت الخاص بحساب الأسعار لكل من الفنادق والشركات وجهات الحجز --}}
       <script src="{{ asset('js/booking-selector.js') }}"></script>
    
    @stack('scripts')
    {{--  --}}
    @include('partials.footer')
 


</body>

</html>
