@extends('layouts.app')
{{-- دي الصفحة الرئيسية للحجوزات، بتورث التصميم من صفحة app.blade.php --}}
@section('title', 'كل الحجوزات : ')
@section('favicon')
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection
@section('content')
    <div class="container-fluid">
        @auth
            @if (auth()->user()->role != 'Company')
                <h1>كل الحجوزات</h1>
            @else
                <h1>حجوزات شركة : {{ auth()->user()->name }}</h1>
            @endif
        @endauth
        {{-- لو في رسالة نجاح --}}
        <!-- الأزرار بتاعة الإدارة - كل زر بيوديك لصفحة إدارة حاجة معينة -->
        <!-- قائمة الإدارة الدائرية التفاعلية -->
        @auth
            @if (auth()->user()->role != 'Company')
                <div class="admin-menu-container mb-5" style="direction: rtl;"> {{-- Added direction: rtl --}}
                    <div class="admin-circle">
                        <span>إدارة</span>
                    </div>
                    <div class="admin-menu-items">
                        <a href="{{ route('admin.employees') }}" class="admin-menu-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                            </svg>
                            <span>إدارة الموظفين</span>
                        </a>
                        <a href="{{ route('admin.companies') }}" class="admin-menu-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z" />
                            </svg>
                            <span>إدارة الشركات</span>
                        </a>
                        <a href="{{ route('admin.agents') }}" class="admin-menu-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58s1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41s-.23-1.06-.59-1.42zM13 20.99l-9-9V4h7l9 9-7 7.01z" />
                                <circle cx="6.5" cy="6.5" r="1.5" />
                            </svg>
                            <span>إدارة جهات الحجز</span>
                        </a>
                        <a href="{{ route('admin.hotels') }}" class="admin-menu-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path d="M19 7h-8v6h8V7zm2-2H3v14h18V5zm-4 6h-4v-4h4v4z" />
                            </svg>
                            <span>إدارة الفنادق</span>
                        </a>
                        <a href="{{ route('admin.archived_bookings') }}" class="admin-menu-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z" />
                            </svg>
                            <span>أرشيف الحجوزات</span>
                        </a>
                    </div>
                </div>
            @endif
        @endauth

        <!-- البحث والفلترة - هنا بتقدر تدور على أي حجز أو تفلتر بالتاريخ -->
        <div class="filter-box p-4 mb-4">
            <h3 class="mb-3">عملية البحث والفلترة</h3>
            <form id="filterForm" method="GET" action="{{ route('bookings.index') }}">
                <div class="row align-items-center text-center">
                    <div class="col-md-4 mb-2">
                        <label for="search" class="form-label">بحث عن العميل، الموظف، الشركة، جهة حجز،
                            فندق</label>
                        <input type="text" name="search" id="search" class="form-control"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="start_date" class="form-label">من تاريخ</label>
                        <input type="text" name="start_date" id="start_date" class="form-control datepicker"
                            value="{{ request('start_date') }}" placeholder="يوم/شهر/سنة">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="end_date" class="form-label">إلى تاريخ</label>
                        <input type="text" name="end_date" id="end_date" class="form-control datepicker"
                            value="{{ request('end_date') }}" placeholder="يوم/شهر/سنة">
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary glow-hover">فلترة</button>
                    {{-- زر إعادة التعيين لإلغاء الفلاتر --}}
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                </div>
            </form>

            {{-- حقول مخفية لتخزين الإجماليات عشان الجافاسكريبت يقراها --}}
            <input type="hidden" id="hidden-total-count" value="{{ $totalBookingsCount ?? 0 }}">
            <input type="hidden" id="hidden-total-due-from-company" value="{{ $totalDueFromCompany ?? 0 }}">
            <input type="hidden" id="hidden-total-paid-by-company" value="{{ $totalPaidByCompany ?? 0 }}">
            <input type="hidden" id="hidden-total-remaining-from-company" value="{{ $remainingFromCompany ?? 0 }}">
            {{-- بنضيف حقول الفنادق بس لو مش بنفلتر بشركة --}}
            @if (!request('company_id'))
                <input type="hidden" id="hidden-total-due-to-hotels" value="{{ $totalDueToHotels ?? 0 }}">
                <input type="hidden" id="hidden-total-paid-to-hotels" value="{{ $totalPaidToHotels ?? 0 }}">
                <input type="hidden" id="hidden-total-remaining-to-hotels" value="{{ $remainingToHotels ?? 0 }}">
            @endif



        </div>

        <!-- لو في فلترة شغالة (يعني اختار شركة أو فندق أو جهة حجز) هنظهر التفاصيل دي -->
        @if (request('company_id') || request('agent_id') || request('hotel_id'))
            <div class="p-4 mb-4" style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #ddd;">
                <h3 class="mb-3">إجماليات الفلترة</h3>
                <p><strong>عدد الحجوزات:</strong> {{ $bookings->count() }} حجز</p>

                <p><strong>إجمالي المستحق من الشركة:</strong> {{ $totalDueFromCompany }} ريال</p>
                <p><strong>إجمالي المدفوع من الشركة:</strong> {{ $totalPaidByCompany }} ريال</p>
                <p><strong>إجمالي المتبقي على الشركة:</strong> {{ $remainingFromCompany }} ريال</p>

                @if (!request('company_id'))
                    <p><strong>إجمالي المستحق للفنادق:</strong> {{ $totalDueToHotels }} ريال</p>
                    <p><strong>إجمالي المدفوع للفنادق:</strong> {{ $totalPaidToHotels }} ريال</p>
                    <p><strong>إجمالي المتبقي للفنادق:</strong> {{ $remainingToHotels }} ريال</p>
                @endif

                <!-- أزرار التصدير -->
                <!-- نشيل السكشن الأول ونخلي سكشن واحد في الآخر -->

                <!-- في div الفلترة، نخلي الكود كده -->
                <div class="mt-3">
                    <button class="btn btn-success" id="captureBtn">أخذ صورة من بيانات الحجوزات</button>
                    <button class="btn btn-info" id="copyBtn" onclick="copyFilteredData()">نسخ بيانات الفلترة</button>
                    <button class="btn btn-info" type="button" data-bs-toggle="collapse"
                        data-bs-target="#bookingDetails" id="toggleDetails">
                        عرض تفاصيل الحجوزات
                    </button>
                </div>

                <!-- تفاصيل الحجوزات -->
                <div class="collapse mt-3" id="bookingDetails">
                    <div class="card card-body">
                        <h4>تفاصيل الحجوزات</h4>
                        @foreach ($bookings as $booking)
                            <div class="border-bottom py-3">
                                <p><strong>العميل:</strong> {{ $booking->client_name }}</p>
                                <p><strong>تاريخ الدخول:</strong> {{ $booking->check_in->format('d/m/Y') }}</p>
                                <p><strong>تاريخ الخروج:</strong> {{ $booking->check_out->format('d/m/Y') }}</p>
                                <p><strong>عدد الأيام:</strong> {{ $booking->days }}</p>
                                <p><strong>عدد الغرف:</strong> {{ $booking->rooms }}</p>
                                <p><strong>المبلغ المستحق من الشركة:</strong> {{ $booking->amount_due_from_company }} ريال
                                </p>
                                <p><strong>المبلغ المدفوع من الشركة:</strong> {{ $booking->amount_paid_by_company }} ريال
                                </p>
                                {{-- جوه ملف bookings/_table.blade.php غالبًا --}}
                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                    onsubmit="return confirm('تحذير! هل أنت متأكد من أرشفة هذا الحجز؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i> حذف
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- نشيل كل الـ scripts القديمة ونحط سكشن واحد في آخر الصفحة -->

                @section('scripts')
                @endsection
        @endif

        {{-- مكان لعرض رسالة الفلترة بالتواريخ --}}
        <div id="filterAlert"></div>
        @auth
            @if (auth()->user()->role != 'Company')
                <a href="{{ route('bookings.create') }}" class="btn btn-primary mb-3 glow-hover">+ إضافة حجز جديد</a>
            @endif
        @endauth

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @auth
            @if (auth()->user()->role != 'Company')
                <div class="alert alert-info text-center mb-3">
                    تم جلب: <strong>{{ $totalActiveBookingsCount }}</strong> حجز نشط
                    ||
                    <strong>{{ $totalArchivedBookingsCount }}</strong> أرشيف

                </div>
            @endif
        @endauth
        @auth
            @if (auth()->user()->role === 'Admin')
                <div class="alert alert-warning text-center mb-3">
                    <strong>ملخص المجاميع حسب العملة:</strong><br>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mt-2">
                            <thead class="table-light">
                                <tr>
                                    <th>العملة</th>
                                    <th>مجموع المستحق للفنادق</th>
                                    <th>مجموع المطلوب من الشركات</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- عرض الريال السعودي --}}
                                <tr>
                                    <th>ريال سعودي</th>
                                    <td>
                                        {{ number_format(collect($totalDueToHotelsByCurrency)->where('currency', 'SAR')->first()['amount'] ?? 0, 2) }}
                                    </td>
                                    <td>
                                        {{ number_format(collect($totalDueFromCompanyByCurrency)->where('currency', 'SAR')->first()['amount'] ?? 0, 2) }}
                                    </td>
                                </tr>
                                {{-- عرض الدينار الكويتي --}}
                                <tr>
                                    <th>دينار كويتي</th>
                                    <td>
                                        {{ number_format(collect($totalDueToHotelsByCurrency)->where('currency', 'KWD')->first()['amount'] ?? 0, 2) }}
                                    </td>
                                    <td>
                                        {{ number_format(collect($totalDueFromCompanyByCurrency)->where('currency', 'KWD')->first()['amount'] ?? 0, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endauth
        <div class="table-responsive" id="bookingsTable">
            @include('bookings._table')
        </div>

        <!-- عرض أزرار Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $bookings->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
        </div>



        @push('scripts')
            {{-- 1. بنستدعي مكتبة Axios --}}
            <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

            {{-- 2. بنستدعي مكتبة html2canvas --}}
            <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

            {{-- 3. الكود الأساسي بتاعنا --}}


            <script src="{{ asset('js/preventClick.js') }}"></script>
            {{-- <script src="{{ asset('js/hide-cols-mobile.js') }}"></script> --}}
            {{-- سكريبت لجعل الجدوال مناسبا لحجم شاشات الهاتف --}}
            <script>
                function adjustBookingsTableForMobile() {
                    // ابحث عن كل الجداول التي تحتوي على العملاء
                    document.querySelectorAll('.table').forEach(table => {
                        const headerCells = table.querySelectorAll('thead tr th');
                        let roomColIndex = -1,
                            currencyColIndex = -1,
                            clientColIndex = -1;

                        headerCells.forEach((th, idx) => {
                            if (th.textContent.trim().includes('غرف')) roomColIndex = idx;
                            if (th.textContent.trim().includes('العملة')) currencyColIndex = idx;
                            if (th.textContent.trim().includes('العميل')) clientColIndex = idx;
                        });

                        if (window.innerWidth < 768) {
                            if (roomColIndex !== -1) headerCells[roomColIndex].style.display = 'none';
                            if (currencyColIndex !== -1) headerCells[currencyColIndex].style.display = 'none';

                            table.querySelectorAll('tbody tr').forEach(row => {
                                const cells = row.querySelectorAll('td');
                                // دمج عدد الغرف مع اسم العميل (مرة واحدة فقط)
                                if (clientColIndex !== -1 && roomColIndex !== -1 && cells[clientColIndex] && cells[
                                        roomColIndex]) {
                                    const rooms = cells[roomColIndex].textContent.trim();
                                    if (rooms && !cells[clientColIndex].innerHTML.includes('غرفة')) {
                                        cells[clientColIndex].innerHTML +=
                                            `<span class="d-block text-muted small">(${rooms} غرفة)</span>`;
                                    }
                                }
                                if (roomColIndex !== -1 && cells[roomColIndex]) cells[roomColIndex].style.display =
                                    'none';
                                if (currencyColIndex !== -1 && cells[currencyColIndex]) cells[currencyColIndex]
                                    .style.display = 'none';
                            });
                        } else {
                            if (roomColIndex !== -1) headerCells[roomColIndex].style.display = '';
                            if (currencyColIndex !== -1) headerCells[currencyColIndex].style.display = '';
                            table.querySelectorAll('tbody tr').forEach(row => {
                                const cells = row.querySelectorAll('td');
                                if (roomColIndex !== -1 && cells[roomColIndex]) cells[roomColIndex].style.display =
                                    '';
                                if (currencyColIndex !== -1 && cells[currencyColIndex]) cells[currencyColIndex]
                                    .style.display = '';
                                // إزالة النص المضاف بجانب اسم العميل فقط إذا كان موجود
                                if (clientColIndex !== -1 && cells[clientColIndex]) {
                                    cells[clientColIndex].innerHTML = cells[clientColIndex].innerHTML.replace(
                                        /\(<span.*غرفة<\/span>\)/, '');
                                    cells[clientColIndex].innerHTML = cells[clientColIndex].innerHTML.replace(
                                        /<span class="d-block text-muted small">\(\d+\sغرفة\)<\/span>/, '');
                                }
                            });
                        }
                    });
                }

                // شغل الدالة عند التحميل وعند تغيير حجم الشاشة
                document.addEventListener("DOMContentLoaded", adjustBookingsTableForMobile);
                window.addEventListener('resize', adjustBookingsTableForMobile);

                // لو الجدول بيتحدث بالـ AJAX أو أي طريقة ديناميكية، شغل الدالة بعد كل تحديث:
                document.addEventListener('ajaxTableUpdated', adjustBookingsTableForMobile);
                // بعد تحديث الجدول بالـ AJAX
                document.dispatchEvent(new Event('ajaxTableUpdated'));
            </script>

            <script>
                // ==========================================================
                // دالة تهيئة مكونات Bootstrap (زي الـ Popovers)
                // ==========================================================
                function initBootstrapComponents() {
                    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                        // بنتأكد إن الـ popover مش متفعل قبل كده
                        if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                            return new bootstrap.Popover(popoverTriggerEl, {
                                html: true
                            });
                        }
                        return null;
                    }).filter(Boolean); // بنشيل الـ nulls
                }

                // ==========================================================
                // دالة تصوير الجدول باستخدام html2canvas
                // ==========================================================
                function captureTableImage() {
                    const element = document.getElementById('bookingsTable'); // الـ div اللي جواه الجدول
                    const captureBtn = document.getElementById('captureBtn');
                    if (!element || !captureBtn) {
                        alert('مش لاقي الجدول أو زرار التصوير!');
                        return;
                    }
                    const originalBtnText = captureBtn.innerText;
                    captureBtn.innerText = 'جاري التجهيز...';
                    captureBtn.disabled = true;
                    console.log('جاري تجهيز الصورة...');

                    html2canvas(element, {
                        scale: 2,
                        useCORS: true
                    }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = 'تقرير-الحجوزات-' + new Date().toISOString().slice(0, 10) + '.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        captureBtn.innerText = originalBtnText;
                        captureBtn.disabled = false;
                        console.log('تم تحميل الصورة بنجاح.');
                    }).catch(err => {
                        console.error('خطأ أثناء تصوير الجدول:', err);
                        alert('حصل خطأ أثناء تجهيز الصورة.');
                        captureBtn.innerText = originalBtnText;
                        captureBtn.disabled = false;
                    });
                }

                // ==========================================================
                // دالة تحديث قيم الحقول المخفية بتاعة الإجماليات
                // ==========================================================
                function updateHiddenTotals(totals) {
                    const countEl = document.getElementById('hidden-total-count');
                    const dueCompanyEl = document.getElementById('hidden-total-due-from-company');
                    const paidCompanyEl = document.getElementById('hidden-total-paid-by-company');
                    const remainingCompanyEl = document.getElementById('hidden-total-remaining-from-company');
                    const dueHotelsEl = document.getElementById('hidden-total-due-to-hotels');
                    const paidHotelsEl = document.getElementById('hidden-total-paid-to-hotels');
                    const remainingHotelsEl = document.getElementById('hidden-total-remaining-to-hotels');

                    // بنحدث القيم لو العناصر موجودة والبيانات موجودة
                    if (countEl && totals.count !== undefined) countEl.value = totals.count;
                    if (dueCompanyEl && totals.due_from_company !== undefined) dueCompanyEl.value = totals.due_from_company;
                    if (paidCompanyEl && totals.paid_by_company !== undefined) paidCompanyEl.value = totals.paid_by_company;
                    if (remainingCompanyEl && totals.remaining_from_company !== undefined) remainingCompanyEl.value = totals
                        .remaining_from_company;

                    // بنحدث قيم الفنادق بس لو العناصر موجودة والبيانات موجودة (مش null)
                    if (dueHotelsEl && totals.due_to_hotels !== null && totals.due_to_hotels !== undefined) dueHotelsEl.value =
                        totals.due_to_hotels;
                    if (paidHotelsEl && totals.paid_to_hotels !== null && totals.paid_to_hotels !== undefined) paidHotelsEl.value =
                        totals.paid_to_hotels;
                    if (remainingHotelsEl && totals.remaining_to_hotels !== null && totals.remaining_to_hotels !== undefined)
                        remainingHotelsEl.value = totals.remaining_to_hotels;

                    console.log('تم تحديث الإجماليات المخفية.');
                }

                // ==========================================================
                // دالة نسخ بيانات الفلترة (بتقرا من الحقول المخفية)
                // ==========================================================
                function copyFilteredData() {
                    let copyText = "تقرير الحجوزات (فلترة)\n\n";
                    copyText += "إجماليات:\n";

                    // *** بنقرا القيم من الحقول المخفية ***
                    const totalCount = document.getElementById('hidden-total-count')?.value ?? 0;
                    copyText += `عدد الحجوزات: ${totalCount} حجز\n\n`;

                    // إضافة المستحق والمدفوع من الشركات حسب العملة
                    copyText += "بالريال السعودي:\n";
                    const totalDueFromCompanySAR =
                        {{ collect($totalDueFromCompanyByCurrency)->where('currency', 'SAR')->first()['amount'] ?? 0 }};
                    const totalPaidByCompanySAR =
                        {{ collect($totalPaidByCompanyByCurrency)->where('currency', 'SAR')->first()['amount'] ?? 0 }};
                    const remainingFromCompanySAR = totalDueFromCompanySAR - totalPaidByCompanySAR;

                    copyText += `إجمالي المستحق من الشركة: ${totalDueFromCompanySAR.toFixed(2)} ريال\n`;
                    copyText += `إجمالي المدفوع من الشركة: ${totalPaidByCompanySAR.toFixed(2)} ريال\n`;
                    copyText += `إجمالي المتبقي على الشركة: ${remainingFromCompanySAR.toFixed(2)} ريال\n\n`;

                    copyText += "بالدينار الكويتي:\n";
                    const totalDueFromCompanyKWD =
                        {{ collect($totalDueFromCompanyByCurrency)->where('currency', 'KWD')->first()['amount'] ?? 0 }};
                    const totalPaidByCompanyKWD =
                        {{ collect($totalPaidByCompanyByCurrency)->where('currency', 'KWD')->first()['amount'] ?? 0 }};
                    const remainingFromCompanyKWD = totalDueFromCompanyKWD - totalPaidByCompanyKWD;

                    copyText += `إجمالي المستحق من الشركة: ${totalDueFromCompanyKWD.toFixed(2)} دينار\n`;
                    copyText += `إجمالي المدفوع من الشركة: ${totalPaidByCompanyKWD.toFixed(2)} دينار\n`;
                    copyText += `إجمالي المتبقي على الشركة: ${remainingFromCompanyKWD.toFixed(2)} دينار\n\n`;

                    // بنضيف إجماليات الفنادق بس لو الحقول بتاعتها موجودة (يعني مش بنفلتر بشركة)
                    const dueHotelsEl = document.getElementById('hidden-total-due-to-hotels');
                    if (dueHotelsEl) {
                        copyText += "إجماليات الفنادق:\n";

                        copyText += "بالريال السعودي:\n";
                        const totalDueToHotelsSAR =
                            {{ collect($totalDueToHotelsByCurrency)->where('currency', 'SAR')->first()['amount'] ?? 0 }};
                        const totalPaidToHotelsSAR =
                            {{ collect($totalPaidToHotelsByCurrency)->where('currency', 'SAR')->first()['amount'] ?? 0 }};
                        const remainingToHotelsSAR = totalDueToHotelsSAR - totalPaidToHotelsSAR;

                        copyText += `إجمالي المستحق للفنادق: ${totalDueToHotelsSAR.toFixed(2)} ريال\n`;
                        copyText += `إجمالي المدفوع للفنادق: ${totalPaidToHotelsSAR.toFixed(2)} ريال\n`;
                        copyText += `إجمالي المتبقي للفنادق: ${remainingToHotelsSAR.toFixed(2)} ريال\n\n`;

                        copyText += "بالدينار الكويتي:\n";
                        const totalDueToHotelsKWD =
                            {{ collect($totalDueToHotelsByCurrency)->where('currency', 'KWD')->first()['amount'] ?? 0 }};
                        const totalPaidToHotelsKWD =
                            {{ collect($totalPaidToHotelsByCurrency)->where('currency', 'KWD')->first()['amount'] ?? 0 }};
                        const remainingToHotelsKWD = totalDueToHotelsKWD - totalPaidToHotelsKWD;

                        copyText += `إجمالي المستحق للفنادق: ${totalDueToHotelsKWD.toFixed(2)} دينار\n`;
                        copyText += `إجمالي المدفوع للفنادق: ${totalPaidToHotelsKWD.toFixed(2)} دينار\n`;
                        copyText += `إجمالي المتبقي للفنادق: ${remainingToHotelsKWD.toFixed(2)} دينار\n\n`;
                    }

                    copyText += "تفاصيل الحجوزات (غير مدعومة حالياً في النسخ بعد الفلترة)\n";

                    // بنستخدم الـ Clipboard API عشان ننسخ النص
                    navigator.clipboard.writeText(copyText).then(() => {
                        alert('تم نسخ إجماليات الفلترة بنجاح!');
                    }).catch(err => {
                        console.error('خطأ أثناء نسخ البيانات:', err);
                        alert('فشل نسخ البيانات.');
                    });
                }

                // ==========================================================
                // دالة جلب البيانات بالـ AJAX (بتحدث الجدول والصفحات والإجماليات)
                // ==========================================================
                function fetchData(url) {
                    console.log('بنجيب بيانات من:', url);
                    // بنعرف المتغيرات دي جوه الدالة عشان نضمن إنها بتجيب العناصر الحالية بعد التحديث
                    const bookingsTableContainer = document.getElementById('bookingsTable');
                    const paginationContainer = document.querySelector('.d-flex.justify-content-center.mt-4');

                    axios.get(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function(response) {
                            console.log('البيانات وصلت:', response.data);

                            // أ. تحديث محتوى الجدول
                            if (bookingsTableContainer && response.data.table !== undefined) {
                                bookingsTableContainer.innerHTML = response.data.table;
                            } else {
                                console.warn('مش لاقي حاوية الجدول أو بيانات الجدول مرجعتش.');
                            }
                            
                            
                            // ب. تحديث أزرار التنقل بين الصفحات
                            if (paginationContainer && response.data.pagination !== undefined) {
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = response.data.pagination.trim();
                                const newPagination = tempDiv.querySelector('ul.pagination');
                                if (newPagination) {
                                    const currentPaginationUl = paginationContainer.querySelector('ul.pagination');
                                    if (currentPaginationUl) {
                                        currentPaginationUl.replaceWith(newPagination);
                                    } else {
                                        paginationContainer.innerHTML = '';
                                        paginationContainer.appendChild(newPagination);
                                    }
                                    console.log('تم تحديث أزرار الصفحات.');
                                } else {
                                    paginationContainer.innerHTML = ''; // مفيش صفحات جديدة، بنفضي الحاوية
                                    console.log('مفيش أزرار صفحات، الحاوية فضيت.');
                                }
                            } else {
                                console.warn('مش لاقي حاوية أزرار الصفحات أو بيانات الصفحات مرجعتش.');
                                if (paginationContainer) paginationContainer.innerHTML = ''; // بنفضيها احتياطي
                            }

                            // *** ج. تحديث الإجماليات المخفية ***
                            if (response.data.totals) { // بنتأكد إن الـ Controller بعت الإجماليات
                                updateHiddenTotals(response.data.totals);
                            } else {
                                console.warn('الإجماليات لم يتم إرجاعها في استجابة الـ AJAX.');
                            }

                            // د. إعادة تهيئة مكونات Bootstrap (زي الـ Popovers) بعد التحديث
                            initBootstrapComponents();

                            // 3) **تحديث الرسالة بناءً على URL الحالي**
                            updateDateAlert();
                        })
                        .catch(function(error) {
                            console.error('خطأ في جلب البيانات:', error.response || error.message || error);
                            alert('حصل مشكلة واحنا بنجيب البيانات. حاول تاني أو شوف الكونسول.');
                        });
                }

                // ==========================================================
                // helper لتحديث رسالة الفلترة بالتواريخ
                // ==========================================================
                function updateDateAlert() {
                    const params = new URLSearchParams(window.location.search);
                    const start = params.get('start_date'),
                        end = params.get('end_date');
                    const container = document.getElementById('filterAlert');
                    if (start && end) {
                        container.innerHTML = `
                          <div class="alert alert-info">
                            هذه الحجوزات التي تمت "دخلت أو خرجت" بين 
                            <strong>${start}</strong> و <strong>${end}</strong>
                          </div>`;
                    } else {
                        container.innerHTML = '';
                    }
                }

                // ==========================================================
                // الكود الأساسي اللي بيشتغل لما الصفحة تحمل (DOMContentLoaded)
                // ==========================================================
                document.addEventListener('DOMContentLoaded', function() {

                    // --- كود الإكمال التلقائي (Autocomplete) ---
                    const searchInput = document.getElementById('search');
                    let suggestionsContainer = null;
                    let debounceTimer;

                    if (searchInput) {
                        suggestionsContainer = document.createElement('div');
                        suggestionsContainer.setAttribute('id', 'suggestions-list');
                        suggestionsContainer.style.position = 'absolute';
                        suggestionsContainer.style.border = '1px solid #ddd';
                        suggestionsContainer.style.borderTop = 'none';
                        suggestionsContainer.style.zIndex = '99';
                        suggestionsContainer.style.backgroundColor = '#fff';
                        suggestionsContainer.style.width = searchInput.offsetWidth + 'px';
                        suggestionsContainer.style.maxHeight = '200px';
                        suggestionsContainer.style.overflowY = 'auto';
                        suggestionsContainer.style.display = 'none';

                        const searchInputWrapper = searchInput.closest('.col-md-4');
                        if (searchInputWrapper) {
                            searchInputWrapper.style.position = 'relative';
                            searchInputWrapper.appendChild(suggestionsContainer);
                        }

                        searchInput.addEventListener('input', function() {
                            const term = this.value;
                            clearTimeout(debounceTimer);

                            if (suggestionsContainer) {
                                suggestionsContainer.style.width = searchInput.offsetWidth + 'px';
                            }

                            if (term.length < 2) {
                                if (suggestionsContainer) {
                                    suggestionsContainer.innerHTML = '';
                                    suggestionsContainer.style.display = 'none';
                                }
                                return;
                            }

                            debounceTimer = setTimeout(() => {
                                axios.get('{{ route('bookings.autocomplete') }}', {
                                        params: {
                                            term: term
                                        },
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(response => {
                                        const suggestions = response.data;
                                        if (suggestionsContainer) {
                                            suggestionsContainer.innerHTML = '';
                                            if (suggestions && suggestions.length > 0) {
                                                suggestions.forEach(suggestion => {
                                                    const item = document.createElement('div');
                                                    item.textContent = suggestion;
                                                    item.style.padding = '8px 12px';
                                                    item.style.cursor = 'pointer';
                                                    item.addEventListener('mouseenter', () =>
                                                        item.style.backgroundColor =
                                                        '#f0f0f0');
                                                    item.addEventListener('mouseleave', () =>
                                                        item.style.backgroundColor = '#fff');
                                                    item.addEventListener('click', () => {
                                                        searchInput.value = suggestion;
                                                        suggestionsContainer.innerHTML =
                                                            '';
                                                        suggestionsContainer.style
                                                            .display = 'none';
                                                    });
                                                    suggestionsContainer.appendChild(item);
                                                });
                                                suggestionsContainer.style.display = 'block';
                                            } else {
                                                suggestionsContainer.style.display = 'none';
                                            }
                                        }
                                    })
                                    .catch(error => {
                                        if (suggestionsContainer) {
                                            suggestionsContainer.innerHTML = '';
                                            suggestionsContainer.style.display = 'none';
                                        }
                                    });
                            }, 300);
                        });

                        document.addEventListener('click', function(event) {
                            if (suggestionsContainer && !searchInput.contains(event.target) && !suggestionsContainer
                                .contains(event.target)) {
                                suggestionsContainer.style.display = 'none';
                            }
                        });

                        searchInput.addEventListener('keydown', function(event) {
                            if (event.key === 'Escape') {
                                if (suggestionsContainer) {
                                    suggestionsContainer.style.display = 'none';
                                }
                            }
                        });
                    }

                    // --- 1. تعريف المتغيرات الأساسية ---
                    const filterForm = document.getElementById('filterForm');
                    const captureBtn = document.getElementById('captureBtn');
                    // مش محتاجين نعرف copyBtn هنا طالما بنستخدم onclick في الـ HTML

                    // --- 2. تهيئة Bootstrap أول مرة ---
                    initBootstrapComponents();

                    // --- 3. إضافة حدث لزرار التصوير ---
                    if (captureBtn) {
                        captureBtn.addEventListener('click', captureTableImage);
                    }

                    // --- 4. إضافة حدث لنموذج الفلترة ---
                    if (filterForm) {
                        filterForm.addEventListener('submit', function(event) {
                            event.preventDefault(); // بنمنع تحميل الصفحة
                            const formData = new FormData(filterForm);
                            const params = new URLSearchParams();
                            // بنشيل القيم الفاضية
                            formData.forEach((value, key) => {
                                if (value) {
                                    params.append(key, value);
                                }
                            });
                            const queryString = params.toString();
                            const filterUrl = '{{ route('bookings.index') }}' + (queryString ? '?' + queryString :
                                '');
                            // بنحدث الـ URL في المتصفح
                            window.history.pushState({
                                path: filterUrl
                            }, '', filterUrl);
                            // بنجيب البيانات الجديدة بالـ AJAX
                            fetchData(filterUrl);
                        });
                    }

                    // --- 5. إضافة حدث للنقر على أزرار الـ Pagination (باستخدام Event Delegation) ---
                    document.addEventListener('click', function(e) {
                        const paginationLink = e.target.closest('.pagination a');
                        if (paginationLink) {
                            e.preventDefault(); // بنمنع تحميل الصفحة
                            const url = paginationLink.href;
                            // بنحدث الـ URL في المتصفح
                            window.history.pushState({
                                path: url
                            }, '', url);
                            // بنجيب بيانات الصفحة الجديدة بالـ AJAX
                            fetchData(url);
                        }
                    });

                    // --- 6. التعامل مع زرار الـ Back/Forward ---
                    window.addEventListener('popstate', function(event) {
                        // بنجيب الـ URL من الـ state أو الـ location الحالي
                        const url = event.state ? event.state.path : location.href;
                        console.log('زرار الـ Back/Forward اتداس، بنجيب بيانات:', url);
                        // بنجيب البيانات بالـ AJAX
                        fetchData(url);
                    });

                    // تحديث الرسالة عند أول تحميل (قبل أي Ajax)
                    updateDateAlert();
                    // --- 7. كود قائمة الإدارة الدائرية (التثبيت عند الضغط) ---
                    const adminMenuContainer = document.querySelector('.admin-menu-container');
                    const adminCircle = document.querySelector('.admin-circle');

                    if (adminMenuContainer && adminCircle) {
                        adminCircle.addEventListener('click', function(event) {
                            event.stopPropagation(); // منع انتشار الحدث للعناصر الأعلى
                            adminMenuContainer.classList.toggle('is-active');
                        });

                        // اختياري: إغلاق القائمة عند الضغط في أي مكان آخر في الصفحة
                        document.addEventListener('click', function(event) {
                            // نتأكد أن الضغطة لم تكن على القائمة نفسها أو الدائرة
                            if (!adminMenuContainer.contains(event.target) && adminMenuContainer.classList.contains(
                                    'is-active')) {
                                adminMenuContainer.classList.remove('is-active');
                            }
                        });
                    }
                    // --- نهاية كود قائمة الإدارة ---



                }); // نهاية الـ DOMContentLoaded
            </script>

            {{-- كود تفعيل الـ Accordion لجدول الحجوزات --}}
           
        @endpush

    </div>

@endsection

@push('styles')
    <style>
        .glow-hover {
            position: relative;
            display: inline-block;
            padding: 10px 22px;
            font-size: 1.1rem;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(90deg, #007bff, #0056b3);
            border: none;
            border-radius: 8px;
            text-decoration: none;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
            z-index: 1;
        }

        .glow-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(0, 123, 255, 0.6), transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
            transform: scale(0.8);
            z-index: -1;
        }

        .glow-hover::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(0, 123, 255, 0.5), rgba(0, 123, 255, 0.2));
            filter: blur(15px);
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
            z-index: -2;
        }

        .glow-hover:hover {
            color: #fff;
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.6), 0 0 50px rgba(0, 123, 255, 0.4);
            transform: translateY(-2px);
        }

        .glow-hover:hover::before {
            opacity: 1;
            transform: scale(1.2);
        }

        .glow-hover:hover::after {
            opacity: 1;
        }

        .glow-hover:active {
            transform: translateY(1px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
        }

        .filter-box {
            position: relative;
            background-color: var(--filter-bg) !important;
            border-radius: 16px;
            padding: 1.5rem 1rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
            z-index: 1;
            box-shadow: inset 0px 0px 0px 1.5px rgba(26, 26, 0, 0.16), 0 2px 8px 0 rgba(0, 0, 0, 0.04);
            border: none;
        }

        .filter-box::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            border: 2px solid transparent;
            background: linear-gradient(90deg, #f53003, #ff4433, #1b1b18, #f53003);
            background-size: 300% 300%;
            animation: border-spin 7s linear infinite;
            z-index: 2;
            pointer-events: none;
            mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            mask-composite: exclude;
            -webkit-mask-composite: xor;
            padding: 2px;
            opacity: 0.7;
        }

        @keyframes border-spin {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        html[data-theme="dark"] .filter-box {
            box-shadow: inset 0px 0px 0px 1.5px #fffaed2d, 0 2px 8px 0 rgba(0, 0, 0, 0.16);
        }

        html[data-theme="dark"] .filter-box::before {
            background: linear-gradient(90deg, #fffaed2d, #f53003, #ff4433, #1b1b18, #f53003);
        }

        /* --- Admin Circle Menu Styles --- */
        .admin-menu-container {
            position: relative;
            /* Needed for absolute positioning of items */
            display: inline-flex;
            /* Align circle and items container */
            align-items: center;
            margin-top: 1rem;
            /* Add some top margin */
        }

        .admin-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(90deg, #E81C2E, #a11422, #202C45);
            /* Gradient */
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            /* transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55); */
            /* Replaced by hover animation */
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            /* Needed for the pseudo-element border */
            overflow: hidden;
            /* Hide overflowing border parts */
            /* Add pulse animation on hover */
            transition: transform 0.3s ease;
            /* Smooth transition for manual scale */
        }

        .admin-circle::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            /* Position outside initially */
            width: 200%;
            height: 200%;
            /* Make it large */
            background: conic-gradient(transparent,
                    transparent,
                    transparent,
                    #ffffff
                    /* White part of the gradient */
                );
            animation: circle-border-spin 4s linear infinite;
            /* Apply rotation animation */
            z-index: -1;
            /* Place behind the circle content */
            opacity: 0.6;
            /* Make it slightly transparent */
        }

        /* Hover effects for circle */
        .admin-menu-container:hover .admin-circle {
            /* Combine rotation and pulse */
            animation: circle-pulse 1.5s infinite ease-in-out;
            /* Apply pulse animation */
            transform: scale(1.1);
            /* Keep the slight enlarge effect */
        }

        /* Remove the rotation from the main hover effect if pulse is applied */
        /* .admin-menu-container:hover .admin-circle {
                                                                transform: rotate(360deg) scale(1.1);
                                                            } */




        .admin-menu-items {
            position: absolute;
            /* Position items to the left for RTL */
            right: 100%;
            /* Start from the left edge of the circle */
            top: 50%;
            transform: translateY(-50%) scale(0.8);
            /* Center vertically, slightly smaller */
            display: flex;
            flex-direction: column;
            /* Stack items vertically */
            gap: 8px;
            /* Space between items */
            opacity: 0;
            pointer-events: none;
            /* Prevent interaction when hidden */
            transition: transform 0.4s ease-out, opacity 0.3s ease-out, z-index 0s 0.4s;
            /* Delay z-index change */
            transform-origin: right center;
            /* Scale origin for RTL */
            margin-right: 15px;
            /* Space between circle and items */
            z-index: 5;
            /* Initially behind */
            direction: ltr;
            /* Keep item content LTR */
        }

        .admin-menu-item {
            display: flex;
            align-items: center;
            background-color: rgba(32, 44, 69, 0.9);
            /* Dark background */
            color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            /* Rounded corners */
            text-decoration: none;
            white-space: nowrap;
            /* Prevent text wrapping */
            opacity: 0;
            /* Hidden initially */
            transform: translateX(20px);
            /* Start slightly to the right for RTL animation */
            transition: opacity 0.3s ease-out, transform 0.4s ease-out, background-color 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .admin-menu-item svg {
            margin-left: 8px;
            /* Space between icon and text */
            flex-shrink: 0;
            /* Prevent icon from shrinking */
        }

        /* Hover effects */
        .admin-menu-container:hover .admin-circle {
            transform: rotate(360deg) scale(1.1);
            /* Rotate and slightly enlarge */
        }

        .admin-menu-container:hover .admin-menu-items {
            opacity: 1;
            transform: translateY(-50%) scale(1);
            /* Scale up to full size */
            pointer-events: auto;
            /* Allow interaction */
            z-index: 15;
            /* Bring items above circle */
        }

        .admin-menu-container:hover .admin-menu-item {
            opacity: 1;
            transform: translateX(0);
            /* Move item to final position */
        }

        /* Staggered animation delays */
        .admin-menu-container:hover .admin-menu-item:nth-child(1) {
            transition-delay: 0.1s;
        }

        .admin-menu-container:hover .admin-menu-item:nth-child(2) {
            transition-delay: 0.18s;
        }

        .admin-menu-container:hover .admin-menu-item:nth-child(3) {
            transition-delay: 0.26s;
        }

        .admin-menu-container:hover .admin-menu-item:nth-child(4) {
            transition-delay: 0.34s;
        }

        .admin-menu-container:hover .admin-menu-item:nth-child(5) {
            transition-delay: 0.42s;
        }

        .admin-menu-item:hover {
            background-color: rgba(232, 28, 46, 0.9);
            /* Highlight color on item hover */
        }

        @keyframes circle-border-spin {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes circle-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }


        /* --- Styles when the menu is ACTIVE (clicked) --- */
        .admin-menu-container.is-active .admin-menu-items {
            opacity: 1;
            transform: translateY(-50%) scale(1);
            /* Scale up to full size */
            pointer-events: auto;
            /* Allow interaction */
            z-index: 15;
            /* Bring items above circle */
            transition: transform 0.4s ease-out, opacity 0.3s ease-out, z-index 0s 0s;
            /* Apply z-index immediately */
        }

        .admin-menu-container.is-active .admin-menu-item {
            opacity: 1;
            transform: translateX(0);
            /* Move item to final position */
        }

        /* Staggered animation delays when ACTIVE */
        .admin-menu-container.is-active .admin-menu-item:nth-child(1) {
            transition-delay: 0.1s;
        }

        .admin-menu-container.is-active .admin-menu-item:nth-child(2) {
            transition-delay: 0.18s;
        }

        .admin-menu-container.is-active .admin-menu-item:nth-child(3) {
            transition-delay: 0.26s;
        }

        .admin-menu-container.is-active .admin-menu-item:nth-child(4) {
            transition-delay: 0.34s;
        }

        .admin-menu-container.is-active .admin-menu-item:nth-child(5) {
            transition-delay: 0.42s;
        }

        /* --- End Active Styles --- */
    </style>
@endpush
