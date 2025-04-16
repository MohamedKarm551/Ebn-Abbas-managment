@extends('layouts.app')
{{-- دي الصفحة الرئيسية للحجوزات، بتورث التصميم من صفحة app.blade.php --}}
@section('title', 'كل الحجوزات : ' )
@section('favicon')
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection
@section('content')
    <div class="container-fluid">
        <h1>كل الحجوزات</h1>

        <!-- الأزرار بتاعة الإدارة - كل زر بيوديك لصفحة إدارة حاجة معينة -->
        <div class="mb-4">
            <a href="{{ route('admin.employees') }}" class="btn btn-secondary">إدارة الموظفين</a>
            <a href="{{ route('admin.companies') }}" class="btn btn-secondary">إدارة الشركات</a>
            <a href="{{ route('admin.agents') }}" class="btn btn-secondary">إدارة جهات الحجز</a>
            <a href="{{ route('admin.hotels') }}" class="btn btn-secondary">إدارة الفنادق</a>
            {{-- <a href="{{ route('admin.archived_bookings') }}" class="btn btn-secondary">أرشيف الحجوزات</a> --}}
        </div>

        <!-- البحث والفلترة - هنا بتقدر تدور على أي حجز أو تفلتر بالتاريخ -->
        <div class="p-4 mb-4" style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #ddd;">
            <h3 class="mb-3">عملية البحث والفلترة</h3>
            <form id="filterForm" method="GET" action="{{ route('bookings.index') }}">
                <div class="row align-items-center text-center">
                    <div class="col-md-4 mb-2">
                        <label for="search" class="form-label">بحث باسم العميل، الموظف، الشركة، جهة الحجز، أو
                            الفندق</label>
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
                    <button type="submit" class="btn btn-primary">فلترة</button>
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
                    <button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#bookingDetails"
                        id="toggleDetails">
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
                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('تحذير! هل أنت متأكد من أرشفة هذا الحجز؟');">
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

        <a href="{{ route('bookings.create') }}" class="btn btn-primary mb-3">+ إضافة حجز جديد</a>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

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
            <script>
                // ==========================================================
                // دالة تهيئة مكونات Bootstrap (زي الـ Popovers)
                // ==========================================================
                function initBootstrapComponents() {
                    console.log('بنهيئ مكونات Bootstrap...');
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
                    const totalDueFromCompany = document.getElementById('hidden-total-due-from-company')?.value ?? 0;
                    const totalPaidByCompany = document.getElementById('hidden-total-paid-by-company')?.value ?? 0;
                    const remainingFromCompany = document.getElementById('hidden-total-remaining-from-company')?.value ?? 0;

                    copyText += `عدد الحجوزات: ${totalCount} حجز\n`;
                    copyText += `إجمالي المستحق من الشركة: ${totalDueFromCompany} ريال\n`;
                    copyText += `إجمالي المدفوع من الشركة: ${totalPaidByCompany} ريال\n`;
                    copyText += `إجمالي المتبقي على الشركة: ${remainingFromCompany} ريال\n`;

                    // بنجيب عناصر الفنادق المخفية
                    const dueHotelsEl = document.getElementById('hidden-total-due-to-hotels');
                    const paidHotelsEl = document.getElementById('hidden-total-paid-to-hotels');
                    const remainingHotelsEl = document.getElementById('hidden-total-remaining-to-hotels');

                    // بنضيف إجماليات الفنادق بس لو الحقول بتاعتها موجودة (يعني مش بنفلتر بشركة)
                    if (dueHotelsEl && paidHotelsEl && remainingHotelsEl) {
                        const totalDueToHotels = dueHotelsEl.value ?? 0;
                        const totalPaidToHotels = paidHotelsEl.value ?? 0;
                        const remainingToHotels = remainingHotelsEl.value ?? 0;
                        copyText += `إجمالي المستحق للفنادق: ${totalDueToHotels} ريال\n`;
                        copyText += `إجمالي المدفوع للفنادق: ${totalPaidToHotels} ريال\n`;
                        copyText += `إجمالي المتبقي للفنادق: ${remainingToHotels} ريال\n`;
                    }
                    copyText += "\n";

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
                        })
                        .catch(function(error) {
                            console.error('خطأ في جلب البيانات:', error.response || error.message || error);
                            alert('حصل مشكلة واحنا بنجيب البيانات. حاول تاني أو شوف الكونسول.');
                        });
                }

                // ==========================================================
                // الكود الأساسي اللي بيشتغل لما الصفحة تحمل (DOMContentLoaded)
                // ==========================================================
                document.addEventListener('DOMContentLoaded', function() {

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

                }); // نهاية الـ DOMContentLoaded
            </script>
        @endpush

    </div>

@endsection
