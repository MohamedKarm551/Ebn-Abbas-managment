@extends('layouts.app')
{{-- دي الصفحة الرئيسية للحجوزات، بتورث التصميم من صفحة app.blade.php --}}

@section('content')
    <div class="container-fluid">
        <h1>كل الحجوزات</h1>

        <!-- الأزرار بتاعة الإدارة - كل زر بيوديك لصفحة إدارة حاجة معينة -->
        <div class="mb-4">
            <a href="{{ route('admin.employees') }}" class="btn btn-secondary">إدارة الموظفين</a>
            <a href="{{ route('admin.companies') }}" class="btn btn-secondary">إدارة الشركات</a>
            <a href="{{ route('admin.agents') }}" class="btn btn-secondary">إدارة جهات الحجز</a>
            <a href="{{ route('admin.hotels') }}" class="btn btn-secondary">إدارة الفنادق</a>
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
            <!-- بنستدعي مكتبة html2canvas اللي هتساعدنا نحول الجدول لصورة -->
            <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
            <script>
                // بنستني لما الصفحة تحمل كلها
                document.addEventListener('DOMContentLoaded', function() {
                    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                        return new bootstrap.Popover(popoverTriggerEl, {
                            html: true // Allow HTML content in the popover (needed for nl2br)
                        })
                    });
                    // بنجيب زرار التصوير من الصفحة
                    const captureBtn = document.getElementById('captureBtn');

                    // لما حد يدوس على الزرار
                    captureBtn.addEventListener('click', function() {
                        // بنجيب الجدول اللي عايزين نصوره
                        const element = document.getElementById('bookingsTable');

                        // بنقول للمستخدم استنى شوية
                        alert('جاري تجهيز الصورة، من فضلك انتظر...');

                        // بنحول الجدول لصورة
                        html2canvas(element).then(canvas => {
                            // بنعمل رابط وهمي عشان نحمل بيه الصورة
                            const link = document.createElement('a');
                            // بنحط اسم للملف
                            link.download = 'تقرير-الحجوزات.png';
                            // بنحول الصورة لصيغة يقدر المتصفح يفهمها
                            link.href = canvas.toDataURL();
                            // بنضغط على الرابط تلقائي عشان يبدأ التحميل
                            link.click();
                        });
                    });
                });

                function copyFilteredData() {
                    let copyText = "تقرير الحجوزات\n\n";
                    copyText += "إجماليات:\n";
                    copyText += `عدد الحجوزات: {{ $bookings->count() }} حجز\n`;
                    copyText += `إجمالي المستحق من الشركة: {{ $totalDueFromCompany }} ريال\n`;
                    copyText += `إجمالي المدفوع من الشركة: {{ $totalPaidByCompany }} ريال\n`;
                    copyText += `إجمالي المتبقي على الشركة: {{ $remainingFromCompany }} ريال\n\n`;

                    copyText += "تفاصيل الحجوزات:\n";
                    @foreach ($bookings as $booking)
                        copyText += `------------------------------------------------\n`;
                        copyText += `العميل: {{ $booking->client_name }}\n`;
                        copyText += `تاريخ الدخول: {{ $booking->check_in->format('d/m/Y') }}\n`;
                        copyText += `تاريخ الخروج: {{ $booking->check_out->format('d/m/Y') }}\n`;
                        copyText += `عدد الأيام: {{ $booking->days }}\n`;
                        copyText += `عدد الغرف: {{ $booking->rooms }}\n`;
                        copyText += `المبلغ المستحق من الشركة: {{ $booking->amount_due_from_company }} ريال\n`;
                        copyText += `المبلغ المدفوع من الشركة: {{ $booking->amount_paid_by_company }} ريال\n`;
                    @endforeach

                    navigator.clipboard.writeText(copyText).then(() => {
                        alert('تم نسخ البيانات بنجاح!');
                    });
                }
            </script>
        @endpush
        @push('scripts')
        {{-- 1. بنستدعي مكتبة Axios عشان نبعت طلبات للـ Controller من غير ما الصفحة تحمل --}}
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
        {{-- 2. بنستدعي مكتبة html2canvas عشان ناخد صورة من الجدول --}}
        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    
        {{-- 3. الكود الأساسي بتاعنا --}}
        <script>
            // ==========================================================
            // دالة عشان نهيئ مكونات Bootstrap زي الـ Popovers
            // بنعملها هنا عشان نقدر نستدعيها أكتر من مرة
            // ==========================================================
            function initBootstrapComponents() {
                console.log('بنهيئ مكونات Bootstrap...'); // رسالة عشان نتأكد إنها شغالة
    
                // بنجيب كل العناصر اللي عليها popover
                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    
                // بنلف على كل عنصر ونفعل الـ popover بتاعه
                var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                    // بنتأكد الأول إن الـ popover ده مش متفعل قبل كده عشان ميحصلش تكرار أو مشاكل
                    if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                        // لو مش متفعل، بنفعله وبنسمح بمحتوى HTML جواه
                        return new bootstrap.Popover(popoverTriggerEl, {
                            html: true
                        });
                    }
                    return null; // لو متفعل قبل كده، بنرجع null
                }).filter(Boolean); // بنشيل أي null من النتيجة عشان يبقى عندنا قايمة بالـ popovers الجديدة بس
    
                // ممكن نضيف هنا تهيئة لأي مكونات تانية زي tooltips لو محتاجين
                // مثال: var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                // var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) });
            }
    
            // ==========================================================
            // دالة عشان ناخد صورة من الجدول ونحملها
            // ==========================================================
            function captureTableImage() {
                // بنجيب الجدول اللي عايزين نصوره
                const element = document.getElementById('bookingsTable');
                // بنجيب الزرار عشان نغير شكله مؤقتاً
                const captureBtn = document.getElementById('captureBtn');
    
                // بنتأكد إن الجدول موجود
                if (!element) {
                    alert('مش لاقي الجدول عشان أصوره!');
                    return;
                }
                // بنتأكد إن الزرار موجود
                if (!captureBtn) {
                     alert('مش لاقي زرار التصوير!');
                     return;
                }
    
                // بنغير نص الزرار ونخليه غير قابل للضغط مؤقتاً
                const originalBtnText = captureBtn.innerText;
                captureBtn.innerText = 'جاري التجهيز...';
                captureBtn.disabled = true;
    
                // بنقول للمستخدم يستنى
                console.log('جاري تجهيز الصورة...');
    
                // بنستخدم مكتبة html2canvas عشان نحول الجدول لصورة
                html2canvas(element, {
                    scale: 2, // ممكن نزود الجودة شوية
                    useCORS: true // لو فيه صور خارجية في الجدول
                }).then(canvas => {
                    // بنعمل رابط وهمي عشان نحمل بيه الصورة
                    const link = document.createElement('a');
                    // بنحط اسم للملف
                    link.download = 'تقرير-الحجوزات-' + new Date().toISOString().slice(0, 10) + '.png'; // بنضيف تاريخ اليوم للاسم
                    // بنحول الصورة لصيغة يقدر المتصفح يفهمها
                    link.href = canvas.toDataURL('image/png'); // بنحدد الصيغة png
                    // بنضغط على الرابط تلقائي عشان يبدأ التحميل
                    link.click();
    
                    // بنرجع الزرار لحالته الطبيعية بعد ما التحميل يخلص (أو يحصل خطأ)
                    captureBtn.innerText = originalBtnText;
                    captureBtn.disabled = false;
                    console.log('تم تحميل الصورة بنجاح.');
    
                }).catch(err => {
                    // لو حصل خطأ بنعرضه في الكونسول ونرجع الزرار لحالته
                    console.error('خطأ أثناء تصوير الجدول:', err);
                    alert('حصل خطأ أثناء تجهيز الصورة.');
                    captureBtn.innerText = originalBtnText;
                    captureBtn.disabled = false;
                });
            }
    
            // ==========================================================
            // دالة عشان ننسخ بيانات الفلترة (لو موجودة)
            // ==========================================================
            function copyFilteredData() {
                // بنجهز النص اللي هيتنسخ
                let copyText = "تقرير الحجوزات (فلترة)\n\n";
                copyText += "إجماليات:\n";
                // بنجيب القيم من الـ Blade (لازم تكون القيم دي موجودة في الصفحة لما الفلترة تشتغل)
                copyText += `عدد الحجوزات: {{ isset($bookings) ? $bookings->count() : 0 }} حجز\n`; // بنتأكد إن المتغير موجود
                copyText += `إجمالي المستحق من الشركة: {{ isset($totalDueFromCompany) ? $totalDueFromCompany : 0 }} ريال\n`;
                copyText += `إجمالي المدفوع من الشركة: {{ isset($totalPaidByCompany) ? $totalPaidByCompany : 0 }} ريال\n`;
                copyText += `إجمالي المتبقي على الشركة: {{ isset($remainingFromCompany) ? $remainingFromCompany : 0 }} ريال\n`;
                @if (!request('company_id')) // بنضيف الإجماليات دي لو مش بنفلتر بشركة معينة
                    copyText += `إجمالي المستحق للفنادق: {{ isset($totalDueToHotels) ? $totalDueToHotels : 0 }} ريال\n`;
                    copyText += `إجمالي المدفوع للفنادق: {{ isset($totalPaidToHotels) ? $totalPaidToHotels : 0 }} ريال\n`;
                    copyText += `إجمالي المتبقي للفنادق: {{ isset($remainingToHotels) ? $remainingToHotels : 0 }} ريال\n`;
                @endif
                copyText += "\n";
    
                copyText += "تفاصيل الحجوزات (لو معروضة):\n";
                // بنجيب تفاصيل الحجوزات من الجزء اللي بيظهر ويختفي (لو موجود)
                const bookingDetailsContainer = document.getElementById('bookingDetails');
                if (bookingDetailsContainer && bookingDetailsContainer.classList.contains('show')) { // بنتأكد إنه ظاهر
                    // بنلف على كل تفصيلة حجز جوه الـ div
                    bookingDetailsContainer.querySelectorAll('.border-bottom').forEach(bookingDiv => {
                        copyText += `------------------------------------------------\n`;
                        // بنجيب كل برجراف جواه ونضيفه للنص
                        bookingDiv.querySelectorAll('p').forEach(p => {
                            copyText += p.innerText.trim() + '\n'; // بناخد النص بس وبنشيل المسافات الزيادة
                        });
                    });
                } else {
                    copyText += "(التفاصيل غير معروضة حالياً)\n";
                }
    
    
                // بنستخدم الـ Clipboard API عشان ننسخ النص
                navigator.clipboard.writeText(copyText).then(() => {
                    alert('تم نسخ بيانات الفلترة بنجاح!');
                }).catch(err => {
                    console.error('خطأ أثناء نسخ البيانات:', err);
                    alert('فشل نسخ البيانات.');
                });
            }
    
    
            // ==========================================================
            // الكود الأساسي اللي بيشتغل لما الصفحة تحمل
            // ==========================================================
            document.addEventListener('DOMContentLoaded', function() {
    
                // --- 1. تعريف المتغيرات اللي هنستخدمها كتير ---
                const filterForm = document.getElementById('filterForm'); // الفورم بتاع الفلترة
                const bookingsTableContainer = document.getElementById('bookingsTable'); // الـ div اللي جواه الجدول
                const paginationContainer = document.querySelector('.d-flex.justify-content-center.mt-4'); // الـ div اللي جواه أزرار الصفحات
                const captureBtn = document.getElementById('captureBtn'); // زرار التصوير
                const copyBtn = document.getElementById('copyBtn'); // زرار النسخ (لو موجود)
    
                // --- 2. تهيئة مكونات Bootstrap أول ما الصفحة تفتح ---
                initBootstrapComponents();
    
                // --- 3. إضافة حدث لزرار التصوير (لو موجود) ---
                if (captureBtn) {
                    captureBtn.addEventListener('click', captureTableImage);
                }
    
                 // --- 4. إضافة حدث لزرار النسخ (لو موجود) ---
                 // لاحظ: زرار النسخ ده معمول عليه onclick في الـ HTML، فمش محتاجين نضيف event listener هنا
                 // بس لو شيلت الـ onclick من الـ HTML، ممكن تضيف الكود ده:
                 // if (copyBtn) {
                 //     copyBtn.addEventListener('click', copyFilteredData);
                 // }
    
    
                // --- 5. دالة موحدة عشان نجيب البيانات بالـ AJAX ---
                function fetchData(url) {
                    console.log('بنجيب بيانات من:', url); // رسالة عشان نعرف إنه بيطلب بيانات
    
                    // بنستخدم Axios عشان نبعت طلب GET للـ URL ده
                    axios.get(url, {
                            headers: { // بنضيف الهيدر ده عشان Laravel يعرف إنه طلب AJAX
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function(response) {
                            console.log('البيانات وصلت:', response.data); // بنشوف البيانات اللي رجعت
    
                            // أ. تحديث محتوى الجدول
                            if (bookingsTableContainer && response.data.table !== undefined) { // بنتأكد إن العنصر موجود وإن البيانات رجعت
                                bookingsTableContainer.innerHTML = response.data.table;
                            } else {
                                console.warn('مش لاقي حاوية الجدول أو بيانات الجدول مرجعتش.');
                            }
    
                            // ب. تحديث أزرار التنقل بين الصفحات
                            if (paginationContainer && response.data.pagination !== undefined) { // بنتأكد إن العنصر موجود وإن البيانات رجعت
                                // بنعمل عنصر div مؤقت عشان نحلل الـ HTML اللي راجع
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = response.data.pagination.trim(); // بنحط الـ HTML جواه
    
                                // بندور على قايمة الـ pagination جوه العنصر المؤقت
                                const newPagination = tempDiv.querySelector('ul.pagination');
    
                                if (newPagination) {
                                    // لو لقينا قايمة جديدة، بنبدل القديمة بيها أو بنضيفها لو مفيش قديمة
                                    const currentPaginationUl = paginationContainer.querySelector('ul.pagination');
                                    if (currentPaginationUl) {
                                        currentPaginationUl.replaceWith(newPagination); // بنبدل القديمة بالجديدة
                                    } else {
                                        paginationContainer.innerHTML = ''; // بنفضي الحاوية الأول
                                        paginationContainer.appendChild(newPagination); // بنضيف الجديدة
                                    }
                                    console.log('تم تحديث أزرار الصفحات.');
                                } else {
                                    // لو مرجعش pagination (يعني صفحة واحدة أو مفيش نتايج)، بنفضي الحاوية
                                    paginationContainer.innerHTML = '';
                                    console.log('مفيش أزرار صفحات، الحاوية فضيت.');
                                }
                            } else {
                                 console.warn('مش لاقي حاوية أزرار الصفحات أو بيانات الصفحات مرجعتش.');
                                 // لو الحاوية موجودة بس مفيش بيانات، نفضيها برضه احتياطي
                                 if(paginationContainer) paginationContainer.innerHTML = '';
                            }
    
                            // ج. إعادة تهيئة مكونات Bootstrap (زي الـ Popovers) بعد ما ضفنا عناصر جديدة للصفحة
                            initBootstrapComponents();
    
                        })
                        .catch(function(error) {
                            // لو حصل أي خطأ في الطلب بنعرضه في الكونسول ونقول للمستخدم
                            console.error('خطأ في جلب البيانات:', error.response || error.message || error);
                            alert('حصل مشكلة واحنا بنجيب البيانات. حاول تاني أو شوف الكونسول.');
                        });
                }
    
    
                // --- 6. إضافة حدث لنموذج الفلترة (لو موجود) ---
                if (filterForm) {
                    filterForm.addEventListener('submit', function(event) {
                        event.preventDefault(); // بنمنع الفورم إنه يعمل تحميل للصفحة
    
                        // بنجهز بيانات الفورم عشان نبعتها في الـ URL
                        const formData = new FormData(filterForm);
                        // بنشيل أي قيم فاضية عشان الـ URL ميبقاش طويل عالفاضي
                        const params = new URLSearchParams();
                        formData.forEach((value, key) => {
                            if (value) { // بنضيف بس لو فيه قيمة
                                params.append(key, value);
                            }
                        });
                        const queryString = params.toString();
    
                        // بنكون الـ URL الجديد بالفلترة
                        const filterUrl = '{{ route('bookings.index') }}' + (queryString ? '?' + queryString : ''); // بنضيف علامة الاستفهام بس لو فيه باراميترز
    
                        // بنستخدم الـ History API عشان نغير الـ URL في المتصفح من غير تحميل
                        // ده بيخلي المستخدم يقدر يعمل bookmark للفلترة أو يستخدم زرار الـ back
                        window.history.pushState({ path: filterUrl }, '', filterUrl);
    
                        // بنستدعي الدالة اللي بتجيب البيانات بالـ URL الجديد
                        fetchData(filterUrl);
                    });
                }
    
    
                // --- 7. إضافة حدث للنقر على أزرار التنقل بين الصفحات (Pagination) ---
                // بنستخدم event delegation عشان نصطاد النقرات على الأزرار حتى لو اتضافت بعد تحميل الصفحة
                document.addEventListener('click', function(e) {
                    // بنشوف هل العنصر اللي اتداس عليه (أو أبوه المباشر) هو رابط جوه الـ pagination
                    const paginationLink = e.target.closest('.pagination a');
    
                    // لو هو رابط pagination
                    if (paginationLink) {
                        e.preventDefault(); // بنمنع الرابط إنه يفتح صفحة جديدة
    
                        // بنجيب الـ URL بتاع الرابط اللي اتداس عليه
                        const url = paginationLink.href;
    
                        // بنستخدم الـ History API عشان نغير الـ URL في المتصفح
                        window.history.pushState({ path: url }, '', url);
    
                        // بنستدعي الدالة اللي بتجيب البيانات بالـ URL الجديد بتاع الصفحة المطلوبة
                        fetchData(url);
                    }
                });
    
                 // --- 8. التعامل مع زرار الـ Back/Forward في المتصفح ---
                 window.addEventListener('popstate', function(event) {
                    // لما المستخدم يدوس back أو forward، بنجيب الـ URL من الـ state أو الـ location
                    const url = event.state ? event.state.path : location.href;
                    console.log('زرار الـ Back/Forward اتداس، بنجيب بيانات:', url);
                    // بنستدعي الدالة اللي بتجيب البيانات بالـ URL ده
                    fetchData(url);
                });
    
            }); // نهاية الـ DOMContentLoaded
        </script>
    @endpush
    
      
    </div>

@endsection
