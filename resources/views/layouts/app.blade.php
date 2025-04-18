<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'نظام إدارة الحجوزات')</title>
    @yield('favicon')
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
    <link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet">
    {{-- تنسيق خاص لصفحة كل الحجوزات أنا رابطه بالصفحة هنا --}}
    <link rel="stylesheet" href="{{ asset('css/bookingsStyle.css') }}">
    @stack('styles')    {{-- لازم تحطه هنا عشان يظهر اللي بتدفعه بـ @push --}}
 
</head>

<body class="d-flex flex-column min-vh-100">

  @include('partials.navbar')
    <div class="container mt-4">
        @yield('content')
    </div>
    <!-- نشيل الـ scripts القديمة ونحط الترتيب الصحيح -->
     
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
    <script>
        document.addEventListener('DOMContentLoaded',function(){
          const html = document.documentElement;
          const sw   = document.getElementById('darkModeSwitch');
          // استرجاع الثيم
          const theme = localStorage.getItem('theme') || 'light';
          html.setAttribute('data-theme', theme);
          sw.checked = (theme === 'dark');
          // حدث التبديل
          sw.addEventListener('change',()=>{
            const t = sw.checked ? 'dark' : 'light';
            html.setAttribute('data-theme', t);
            localStorage.setItem('theme', t);
          });
        });
    </script>
</body>
</html>
