{{--   هخلي الصفحة دي تعرض بيانات الحجوزات المؤرشفة  واللي هتكون عبارة عن  --}}
@extends('layouts.app')
@section('content')
    <div class="container" dir="rtl">
        <h1 class="mb-4"><i class="fas fa-archive"></i> أرشيف الحجوزات</h1>
        @if ($archivedBookings->isEmpty())
            <div class="alert alert-info text-center">لا توجد حجوزات مؤرشفة.</div>
        @else
            <!-- البحث والفلترة - هنا بتقدر تدور على أي حجز أو تفلتر بالتاريخ -->
            <div class="filter-box pulse-border  p-4 mb-4">
               
                <h3 class="mb-3 text-muted">عملية البحث والفلترة</h3>
                <form id="archiveFilterForm" method="GET" action="{{ route('admin.archived_bookings') }}">
                    <div class="row align-items-center text-center">
                        <div class="col-md-4 mb-2">
                            <label for="search" class="form-label">بحث عن العميل، الموظف، الشركة، جهة حجز، فندق</label>
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

                        <a href="{{ route('admin.archived_bookings') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                    </div>
                </form>

                {{-- حقول مخفية لتخزين الإجماليات عشان الجافاسكريبت يقراها --}}
                <input type="hidden" id="hidden-total-count" value="{{ $totalBookingsCount ?? 0 }}">
                <input type="hidden" id="hidden-total-due-from-company" value="{{ $totalDueFromCompany ?? 0 }}">
                <input type="hidden" id="hidden-total-paid-by-company" value="{{ $totalPaidByCompany ?? 0 }}">
                <input type="hidden" id="hidden-total-remaining-from-company" value="{{ $remainingFromCompany ?? 0 }}">
                @if (!request('company_id'))
                    <input type="hidden" id="hidden-total-due-to-hotels" value="{{ $totalDueToHotels ?? 0 }}">
                    <input type="hidden" id="hidden-total-paid-to-hotels" value="{{ $totalPaidToHotels ?? 0 }}">
                    <input type="hidden" id="hidden-total-remaining-to-hotels" value="{{ $remainingToHotels ?? 0 }}">
                @endif
            </div>
            <div class="alert alert-info text-center mb-3">
                تم جلب: <strong>{{ $totalArchivedBookingsCount }}</strong> أرشيف

            </div>
            <div class="table-responsive" id="archivedBookingsTable">
                @include('admin._archived_table', ['archivedBookings' => $archivedBookings])
            </div>
            {{-- روابط الـ Pagination --}}
            <div class="d-flex justify-content-center mt-4" id="archivedPagination">
                {{ $archivedBookings->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
@push('styles')
    {{-- أو ممكن تستخدم @section('styles') لو الـ layout بتاعك فيه yield('styles') --}}
    <style>
        .autocomplete-suggestions {
            position: absolute;
            /* عشان يظهر فوق المحتوى اللي تحته */
            border: 1px solid #ddd;
            border-top: none;
            z-index: 999;
            /* عشان يظهر فوق أي حاجة تانية */
            background-color: #fff;
            width: calc(100% - 2px);
            /* ياخد نفس عرض عمود البحث تقريباً */
            max-height: 200px;
            /* أقصى ارتفاع عشان لو الاقتراحات كتير */
            overflow-y: auto;
            /* لو الاقتراحات أكتر من الارتفاع، يظهر سكرول */
            display: none;
            /* بيكون مخفي في الأول */
            text-align: right;
            /* عشان النص يبقى يمين */
        }

        .autocomplete-suggestions div {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            /* خط فاصل بين الاقتراحات */
        }

        .autocomplete-suggestions div:last-child {
            border-bottom: none;
            /* آخر اقتراح من غير خط تحته */
        }

        .autocomplete-suggestions div:hover {
            background-color: #f0f0f0;
            /* تغيير اللون لما الماوس يجي عليه */
        }

        /* تعديل بسيط عشان الـ div يظهر تحت الانبوت بالظبط */
        .col-md-4 {
            position: relative;
            /* مهم عشان الـ absolute يشتغل صح */
        }


        .pulse-border {
            border-radius: 22px;
            box-shadow: 0 0 0 0 #ffe259, 0 0 10px 4px #fff70044;
            animation: pulseGlow 2.2s infinite cubic-bezier(.66, 0, .26, 1);
        }

        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 0 0 #ffe259, 0 0 10px 4px #fff70044;
            }

            50% {
                box-shadow: 0 0 0 7px #ffe25955, 0 0 24px 10px #fff70077;
            }

            100% {
                box-shadow: 0 0 0 0 #ffe259, 0 0 10px 4px #fff70044;
            }
        }
    </style>
@endpush

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
        // console.log('جاري تجهيز الصورة...');

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
            // console.log('تم تحميل الصورة بنجاح.');
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

        // console.log('تم تحديث الإجماليات المخفية.');
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
        // console.log('بنجيب بيانات من:', url);
        // بنعرف المتغيرات دي جوه الدالة عشان نضمن إنها بتجيب العناصر الحالية بعد التحديث
        const bookingsTableContainer = document.getElementById('bookingsTable');
        const paginationContainer = document.querySelector('.d-flex.justify-content-center.mt-4');

        axios.get(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                // console.log('البيانات وصلت:', response.data);

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
                        // console.log('تم تحديث أزرار الصفحات.');
                    } else {
                        paginationContainer.innerHTML = ''; // مفيش صفحات جديدة، بنفضي الحاوية
                        // console.log('مفيش أزرار صفحات، الحاوية فضيت.');
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
                    axios.get('{{ route('admin.archived_bookings.autocomplete') }}', {
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
            // console.log('زرار الـ Back/Forward اتداس، بنجيب بيانات:', url);
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
<!-- استدعاء الخلفية التفاعلية -->
<script type="module">
    import {
        initParticlesBg
    } from '/js/particles-bg.js';
    initParticlesBg(); // يمكنك تمرير خيارات مثل {points:80, colors:[...]} إذا أردت
</script>
