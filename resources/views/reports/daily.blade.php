@extends('layouts.app')
@section('title', 'التقارير اليومية')
@section('favicon')
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/daily_reports.css') }}">
    <style>
        .action-buttons-grid {
            display: grid;
            grid-template-columns: 1fr;
            /* عمود واحد فقط */
            gap: 6px;
            justify-items: center;
            align-items: center;
            width: 100%;
        }

        /* تحسين مظهر الأزرار */
        .action-buttons-grid .btn {
            width: 100%;
            white-space: nowrap;
            font-size: 0.8rem;
            padding: 0.35rem 0.6rem;
            min-height: 32px;
            /* ارتفاع ثابت للأزرار */
        }

        /* للشاشات الكبيرة - الأزرار تبقى فوق بعض */
        @media (min-width: 992px) {
            .action-buttons-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }

        /* للشاشات المتوسطة - الأزرار تبقى فوق بعض */
        @media (min-width: 768px) and (max-width: 991px) {
            .action-buttons-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }
        }

        /* للشاشات الصغيرة - الأزرار تبقى فوق بعض */
        @media (max-width: 767px) {
            .action-buttons-grid {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .action-buttons-grid .btn {
                font-size: 0.75rem;
                padding: 0.3rem 0.5rem;
                min-height: 28px;
            }
        }

        /* تحسين عرض الجدول على الشاشات الصغيرة */
        @media (max-width: 576px) {
            .action-buttons-grid .btn {
                font-size: 0.7rem;
                padding: 0.25rem 0.4rem;
                min-height: 26px;
            }
        }
    </style>
@endpush




@section('content')
    <div class="container-fluid">
        <div class="row g-3">
            <div class="col-12">
                {{-- variables --}}
                @include('reports.hoteldailyReport._variabels')
            </div>
            <div class="card col-12">
                {{-- Header Section --}}
                <div class="overflow-hidden">
                    @include('reports.hoteldailyReport._summary_section')
                </div>
            </div>

            {{-- خلي العنوان جمبه الصورة تظهر بشكل مناسب وريسبونسف --}}
            <div class=" card col-12">
                <div class="overflow-hidden">
                    @include('reports.hoteldailyReport._moneyDetails', [
                        'currencyDetails' => $currencyDetails ?? [],
                        'totalDueToCompaniesByCurrency' => $totalDueToCompaniesByCurrency ?? [],
                        'agentPaymentsByCurrency' => $agentPaymentsByCurrency ?? [],
                    ])
                </div>
            </div>

            {{-- *** بداية قسم لوحة المعلومات المصغرة *** --}}
            <div class=" card col-12 p-2">
                <div class="charts-wrapper overflow-hidden">
                    @include('reports.hoteldailyReport._chartSAR')
                </div>
            </div>
            <div class="card col-12 p-2">
                <div class="charts-wrapper overflow-hidden">
                    @include('reports.hoteldailyReport._chartKWD')
                </div>
            </div>
            <div class="col-12">
                <div class="overflow-hidden">
                    @include('reports.hoteldailyReport._topdetails')
                </div>
            </div>
            {{-- *** نهاية الرسم البياني الجديد *** --}}

            {{-- *** نهاية قسم لوحة المعلومات المصغرة *** --}}



            {{-- <div class=" mb-4">
            <div class="card-header">
                <h3>ملخص اليوم</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled" style="padding: 1%;margin: 1%;
">
                            <li>
                                <a href="{{ route('bookings.index', ['start_date' => now()->format('d/m/Y')]) }}"
                                    class="fw-bold text-decoration-none text-primary">
                                    عدد الحجوزات اليوم: {{ $todayBookings->count() }}
                                </a>
                            </li>

                            <li class="fw-bold">إجمالي المتبقي من الشركات:
                                {{ number_format($totalRemainingFromCompanies) }}
                                ريال</li>
                            <li class="fw-bold">إجمالي المتبقي للفنادق (جهات الحجز):
                                {{ number_format($totalRemainingToHotels) }} ريال</li>
                            <li class="fw-bold">صافي الربح: {{ number_format($netProfit) }} ريال</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
 --}}
            <!-- جدول الشركات -->
            <div class="col-12">
                <div class="card mb-4 overflow-hidden">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h3 class="mb-0 text-dark">حساب المطلوب من الشركات</h3>
                        <button class="btn btn-success btn-sm " id="export-btn" onclick="exportTableOfCompanies()">تحميل
                            الجدول   <i class="fas fa-download"></i></button>
                        <div class="btn-group" role="group">
                            <button class="btn btn-secondary btn-sm" onclick="copyTable('companiesTableContent')">نسخ
                                الجدول</button>
                            <button class="btn btn-info btn-sm" onclick="loadCompaniesTable(1)">تحديث</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" id="companiesTableContainer">
                            <!-- Loading Spinner للشركات -->
                            <div id="companiesTableLoader" class="text-center p-3" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">جاري تحميل بيانات الشركات...</span>
                                </div>
                            </div>

                            <!-- الجدول الأصلي للشركات -->
                            <div id="companiesTableWrapper">
                                @include('reports.hoteldailyReport.companies-table', [
                                    'companiesReport' => $companiesReport,
                                    'totalDueByCurrency' => $totalDueByCurrency ?? [],
                                    'totalPaidByCurrency' => $totalPaidByCurrency ?? [],
                                    'totalRemainingByCurrency' => $totalRemainingByCurrency ?? [],
                                ])
                            </div>
                        </div>

                        <!-- Pagination Container للشركات -->
                        <div id="companiesPaginationContainer" class="d-flex justify-content-center mt-3">
                            {{ $companiesReport->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول جهات الحجز -->
            <!-- جدول جهات الحجز مع AJAX Pagination -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="text-dark">حساب المستحق إلى جهات الحجز</h3>
                    <div class="btn-group" role="group">
                        <button class="btn btn-secondary btn-sm" onclick="copyTable('agentsTableContent')">نسخ
                            الجدول</button>
                        <button class="btn btn-info btn-sm" onclick="loadAgentsTable(1)">تحديث</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="agentsTableContainer">
                        <!-- Loading Spinner للوكلاء -->
                        <div id="agentsTableLoader" class="text-center p-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري تحميل بيانات الوكلاء...</span>
                            </div>
                        </div>

                        <!-- الجدول الأصلي للوكلاء -->
                        <div id="agentsTableWrapper">
                            @include('reports.hoteldailyReport.agents-table', [
                                'agentsReport' => $agentsReport,
                                'agentsTotalCalculations' => $agentsTotalCalculations ?? [], // ✅ تمرير الحسابات
                            ])
                        </div>
                    </div>

                    <!-- Pagination Container للوكلاء -->
                    <div id="agentsPaginationContainer" class="d-flex justify-content-center mt-3">
                        {{ $agentsReport->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>


            <!-- نماذج تسجيل الدفعات لجهات الحجز -->
            <div class="row">
                @foreach ($agentsReport as $agent)
                    <!-- نموذج الدفعة العادية -->
                    <div class="modal fade" id="agentPaymentModal{{ $agent->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('reports.agent.payment') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="agent_id" value="{{ $agent->id }}">

                                    <div class="modal-header">
                                        <h5 class="modal-title">تسجيل دفعة - {{ $agent->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">المبلغ المدفوع والعملة</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" class="form-control" name="amount"
                                                    required>
                                                <select class="form-select" name="currency" style="max-width: 120px;">
                                                    <option value="SAR" selected>ريال سعودي</option>
                                                    <option value="KWD">دينار كويتي</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">ملاحظات</label>
                                            <textarea class="form-control" name="notes"></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">إغلاق</button>
                                        <button type="submit" class="btn btn-primary">تسجيل الدفعة</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- نموذج الخصم المنفصل -->
                    <div class="modal fade" id="agentDiscountModal{{ $agent->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('reports.agent.discount', $agent->id) }}" method="POST">
                                    @csrf

                                    <div class="modal-header">
                                        <h5 class="modal-title">تطبيق خصم - {{ $agent->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">مبلغ الخصم والعملة</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" class="form-control"
                                                    name="discount_amount" required>
                                                <select class="form-select" name="currency" style="max-width: 120px;">
                                                    <option value="SAR" selected>ريال سعودي</option>
                                                    <option value="KWD">دينار كويتي</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">سبب الخصم</label>
                                            <textarea class="form-control" name="reason" placeholder="اختياري - سبب تطبيق الخصم"></textarea>
                                        </div>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            تأكد من مبلغ الخصم قبل المتابعة. هذا الإجراء سيؤثر على الحساب النهائي
                                            للوكيل.
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">إغلاق</button>
                                        <button type="submit" class="btn btn-warning">تطبيق الخصم</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- إضافة سكريبت  النسخ والخصم -->
            @push('scripts')
                <script>
                    function copyTable(tableId) {
                        const table = document.getElementById(tableId);
                        const range = document.createRange();
                        range.selectNode(table);
                        window.getSelection().removeAllRanges();
                        window.getSelection().addRange(range);
                        document.execCommand('copy');
                        window.getSelection().removeAllRanges();
                        alert('تم نسخ الجدول');
                    }

                    function toggleAgentDiscountMode(agentId) {
                        const isDiscountField = document.getElementById('is-discount-' + agentId);
                        const submitBtn = document.getElementById('agentSubmitBtn-' + agentId);
                        const toggleBtn = document.getElementById('toggleAgentDiscountBtn-' + agentId);
                        const modalTitle = document.querySelector('#agentPaymentModalTitle' + agentId);
                        const agentName = modalTitle.textContent.split('-')[1].trim();

                        if (isDiscountField.value === "0") {
                            // تحويل إلى وضع الخصم
                            isDiscountField.value = "1";
                            submitBtn.textContent = "تطبيق الخصم";
                            submitBtn.classList.remove('btn-primary');
                            submitBtn.classList.add('btn-warning');
                            toggleBtn.textContent = "تسجيل دفعة";
                            modalTitle.textContent = "تسجيل خصم - " + agentName;
                        } else {
                            // العودة إلى وضع الدفع
                            isDiscountField.value = "0";
                            submitBtn.textContent = "تسجيل الدفعة";
                            submitBtn.classList.remove('btn-warning');
                            submitBtn.classList.add('btn-primary');
                            toggleBtn.textContent = "تسجيل خصم";
                            modalTitle.textContent = "تسجيل دفعة - " + agentName;
                        }
                    }
                </script>
            @endpush



            <!-- جدول الفنادق -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="text-dark">حسابات الفنادق</h3>
                    <div class="btn-group" role="group">
                        <button class="btn btn-secondary btn-sm" onclick="copyTable('hotelsTableContent')">نسخ
                            الجدول</button>
                        <button class="btn btn-info btn-sm" onclick="loadHotelsTable(1)">تحديث</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="hotelsTableContainer">
                        <!-- Loading Spinner -->
                        <div id="hotelsTableLoader" class="text-center p-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                        </div>

                        <!-- الجدول الأصلي -->
                        <div id="hotelsTableWrapper">
                            @include('reports.hoteldailyReport.hotels-table', [
                                'hotelsReport' => $hotelsReport,
                            ])
                        </div>
                    </div>

                    <!-- Pagination Container -->
                    <div id="hotelsPaginationContainer" class="d-flex justify-content-center mt-3">
                        {{ $hotelsReport->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>

        </div>

        <!-- إضافة تنسيقات CSS في القسم الخاص بالستيلات -->

        @push('scripts')
            {{-- 1. تضمين Chart.js (إذا لم يكن مضمنًا في app.blade.php) --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            {{-- حفظ الصفحة صورة أو بي دي اف  --}}
            <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

            {{-- 2. تمرير البيانات من PHP إلى JavaScript --}}
            {{-- reports\hoteldailyReport\_variabels --}}


            {{-- 3. استدعاء ملف JavaScript الخارجي --}}
            <script src="{{ asset('js/daily.js') }}"></script>

            {{-- 4. تعريف دالة النسخ --}}
            <script>
                function copyTable(tableId) {
                    const table = document.getElementById(tableId);
                    if (!table) return; // تأكد من وجود الجدول
                    const range = document.createRange();
                    range.selectNode(table);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    try {
                        document.execCommand('copy');
                        alert('تم نسخ الجدول');
                    } catch (err) {
                        alert('فشل نسخ الجدول. حاول مرة أخرى.');
                    }
                    window.getSelection().removeAllRanges();
                }
            </script>
            <script>
                function saveDailyScreenshotIfNeeded() {
                    var today = new Date().toISOString().slice(0, 10); // yyyy-mm-dd
                    var lastSaved = localStorage.getItem('dailyScreenshotDate');
                    if (lastSaved === today) {
                        // الصورة محفوظة النهاردة بالفعل
                        return;
                    }
                    html2canvas(document.body).then(function(canvas) {
                        var imageData = canvas.toDataURL('image/png');
                        fetch('/save-screenshot', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    image: imageData
                                })
                            }).then(res => res.json())
                            .then(data => {
                                console.log('تم حفظ صورة اليوم:', data.path);
                                localStorage.setItem('dailyScreenshotDate', today);
                            }).catch(err => {
                                console.error('خطأ في رفع الصورة:', err);
                            });
                    });
                }


                window.addEventListener('load', function() {
                    setTimeout(saveDailyScreenshotIfNeeded, 3000); // انتظر 3 ثواني بعد تحميل الصفحة
                });
                // دالة التبديل وضع الخصم
                function toggleDiscountMode(companyId) {
                    const isDiscountField = document.getElementById('is-discount-' + companyId);
                    const submitBtn = document.getElementById('submitBtn-' + companyId);
                    const toggleBtn = document.getElementById('toggleDiscountBtn-' + companyId);
                    const modalTitle = document.querySelector('#paymentModal' + companyId + ' .modal-title');
                    const companyName = modalTitle.textContent.split('-')[1].trim();

                    if (isDiscountField.value === "0") {
                        // تحويل إلى وضع الخصم
                        isDiscountField.value = "1";
                        submitBtn.textContent = "تطبيق الخصم";
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-warning');
                        toggleBtn.textContent = "تسجيل دفعة";
                        modalTitle.textContent = "تسجيل خصم - " + companyName;
                    } else {
                        // العودة إلى وضع الدفع
                        isDiscountField.value = "0";
                        submitBtn.textContent = "تسجيل الدفعة";
                        submitBtn.classList.remove('btn-warning');
                        submitBtn.classList.add('btn-primary');
                        toggleBtn.textContent = "تسجيل خصم";
                        modalTitle.textContent = "تسجيل دفعة - " + companyName;
                    }
                }

                // ==========================================
                // 🏨 دوال AJAX للفنادق (منفصلة تماماً)
                // ==========================================

                function loadHotelsTable(page = 1) {
                    // إظهار loading 
                    $('#hotelsTableLoader').fadeIn(200);
                    // $('#hotelsTableWrapper').fadeTo(200, 0.3);
                    // ✅ تقليل الشفافية مؤقتاً فقط
                    $('#hotelsTableWrapper').css('pointer-events', 'none').animate({
                        opacity: 0.5
                    }, 200);

                    $.ajax({
                        url: '{{ route('reports.hotels.ajax') }}',
                        type: 'GET',
                        data: {
                            hotels_page: page, // ✅ parameter منفصل للفنادق
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(response) {

                            // ✅ تحديث المحتوى مباشرة
                            $('#hotelsTableWrapper').html(response.html);

                            // ✅ إرجاع الشفافية والتفاعل للطبيعي
                            $('#hotelsTableWrapper').css('pointer-events', 'auto').animate({
                                opacity: 1
                            }, 300);

                            // تحديث Pagination
                            $('#hotelsPaginationContainer').html(response.pagination);
                            bindHotelsPagination();

                            // إخفاء Loading
                            $('#hotelsTableLoader').fadeOut(200);
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ خطأ في تحميل الفنادق:', error);
                            $('#hotelsTableLoader').fadeOut(200);
                            $('#hotelsTableWrapper').fadeTo(200, 1);
                            alert('حدث خطأ في تحميل بيانات الفنادق');
                        }
                    });
                }

                function bindHotelsPagination() {
                    $('#hotelsPaginationContainer a').off('click').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var url = $(this).attr('href');
                        var $this = $(this);

                        if (url && url !== '#' && !$this.parent().hasClass('disabled')) {
                            var page = new URL(url).searchParams.get('hotels_page') || 1; // ✅ parameter منفصل


                            $this.addClass('clicked');
                            setTimeout(() => $this.removeClass('clicked'), 200);

                            loadHotelsTable(parseInt(page));
                        }

                        return false;
                    });
                }

                // ==========================================
                // 🤝 دوال AJAX للوكلاء (منفصلة تماماً)
                // ==========================================

                function loadAgentsTable(page = 1) {

                    $('#agentsTableLoader').fadeIn(200);
                    // $('#agentsTableWrapper').fadeTo(200, 0.3);
                    $('#agentsTableWrapper').css('pointer-events', 'none').animate({
                        opacity: 0.5
                    }, 200);

                    $.ajax({
                        url: '{{ route('reports.agents.ajax') }}',
                        type: 'GET',
                        data: {
                            agents_page: page, // ✅ parameter منفصل للوكلاء
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(response) {

                            // ✅ تحديث المحتوى مباشرة بدون fade effects معقدة
                            $('#agentsTableWrapper').html(response.html);

                            // ✅ إرجاع الشفافية والتفاعل للطبيعي
                            $('#agentsTableWrapper').css('pointer-events', 'auto').animate({
                                opacity: 1
                            }, 300);

                            // تحديث Pagination
                            $('#agentsPaginationContainer').html(response.pagination);
                            bindAgentsPagination();

                            // إخفاء Loading
                            $('#agentsTableLoader').fadeOut(200);
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ خطأ في تحميل الوكلاء:', error);
                            $('#agentsTableLoader').fadeOut(200);
                            $('#agentsTableWrapper').fadeTo(200, 1);
                            alert('حدث خطأ في تحميل بيانات الوكلاء');
                        }
                    });
                }

                function bindAgentsPagination() {
                    $('#agentsPaginationContainer a').off('click').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var url = $(this).attr('href');
                        var $this = $(this);

                        if (url && url !== '#' && !$this.parent().hasClass('disabled')) {
                            var page = new URL(url).searchParams.get('agents_page') || 1; // ✅ parameter منفصل


                            $this.addClass('clicked');
                            setTimeout(() => $this.removeClass('clicked'), 200);

                            loadAgentsTable(parseInt(page));
                        }

                        return false;
                    });
                }

                // ==========================================
                // 🔗 تهيئة عند تحميل الصفحة
                // ==========================================

                $(document).ready(function() {

                    // ربط أحداث كل جدول بشكل منفصل
                    bindHotelsPagination();
                    bindAgentsPagination();

                });

                // ==========================================
                // 🔄 دوال أخرى (النسخ والخصم)
                // ==========================================

                function copyTable(tableId) {
                    const table = document.getElementById(tableId);
                    if (!table) return;
                    const range = document.createRange();
                    range.selectNode(table);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    try {
                        document.execCommand('copy');
                        alert('تم نسخ الجدول');
                    } catch (err) {
                        alert('فشل نسخ الجدول. حاول مرة أخرى.');
                    }
                    window.getSelection().removeAllRanges();
                }

                function toggleDiscountMode(companyId) {
                    const isDiscountField = document.getElementById('is-discount-' + companyId);
                    const submitBtn = document.getElementById('submitBtn-' + companyId);
                    const toggleBtn = document.getElementById('toggleDiscountBtn-' + companyId);
                    const modalTitle = document.querySelector('#paymentModal' + companyId + ' .modal-title');
                    const companyName = modalTitle.textContent.split('-')[1].trim();

                    if (isDiscountField.value === "0") {
                        isDiscountField.value = "1";
                        submitBtn.textContent = "تطبيق الخصم";
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-warning');
                        toggleBtn.textContent = "تسجيل دفعة";
                        modalTitle.textContent = "تسجيل خصم - " + companyName;
                    } else {
                        isDiscountField.value = "0";
                        submitBtn.textContent = "تسجيل الدفعة";
                        submitBtn.classList.remove('btn-warning');
                        submitBtn.classList.add('btn-primary');
                        toggleBtn.textContent = "تسجيل خصم";
                        modalTitle.textContent = "تسجيل دفعة - " + companyName;
                    }
                }


                // ==========================================
                // 🔄 دوال أخرى (النسخ والخصم)
                // ==========================================

                function copyTable(tableId) {
                    const table = document.getElementById(tableId);
                    if (!table) return;
                    const range = document.createRange();
                    range.selectNode(table);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    try {
                        document.execCommand('copy');
                        alert('تم نسخ الجدول');
                    } catch (err) {
                        alert('فشل نسخ الجدول. حاول مرة أخرى.');
                    }
                    window.getSelection().removeAllRanges();
                }
            </script>
            <script>
                // ==========================================
                // 🏢 دوال AJAX للشركات (منفصلة تماماً)
                // ==========================================

                function loadCompaniesTable(page = 1) {
                    // إظهار loading 
                    $('#companiesTableLoader').fadeIn(200);
                    $('#companiesTableWrapper').css('pointer-events', 'none').animate({
                        opacity: 0.5
                    }, 200);

                    $.ajax({
                        url: '{{ route('reports.companies.ajax') }}',
                        type: 'GET',
                        data: {
                            companies_page: page, // parameter منفصل للشركات
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(response) {
                            // تحديث المحتوى مباشرة
                            $('#companiesTableWrapper').html(response.html);

                            // إرجاع الشفافية والتفاعل للطبيعي
                            $('#companiesTableWrapper').css('pointer-events', 'auto').animate({
                                opacity: 1
                            }, 300);

                            // تحديث Pagination
                            $('#companiesPaginationContainer').html(response.pagination);
                            bindCompaniesPagination();

                            // تحديث الإجماليات إذا كانت متاحة
                            if (response.totals) {
                                updateCompaniesTotals(response.totals);
                            }

                            // إخفاء Loading
                            $('#companiesTableLoader').fadeOut(200);
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ خطأ في تحميل الشركات:', error);
                            $('#companiesTableLoader').fadeOut(200);
                            $('#companiesTableWrapper').fadeTo(200, 1);
                            alert('حدث خطأ في تحميل بيانات الشركات');
                        }
                    });
                }

                function bindCompaniesPagination() {
                    $('#companiesPaginationContainer a').off('click').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var url = $(this).attr('href');
                        var $this = $(this);

                        if (url && url !== '#' && !$this.parent().hasClass('disabled')) {
                            var page = new URL(url).searchParams.get('companies_page') || 1;

                            $this.addClass('clicked');
                            setTimeout(() => $this.removeClass('clicked'), 200);

                            loadCompaniesTable(parseInt(page));
                        }

                        return false;
                    });
                }

                function updateCompaniesTotals(totals) {
                    // يمكنك تنفيذ تحديث للإجماليات هنا إذا كنت بحاجة لذلك
                    // console.log('تم تحديث إجماليات الشركات:', totals);
                }

                // إضافة تهيئة الباجيناشن عند تحميل الصفحة
                $(document).ready(function() {
                    // ربط أحداث الباجيناشن للشركات
                    bindCompaniesPagination();

                    // البقية من كود التهيئة الموجود حالياً...
                });
            </script>
        @endpush


    @endsection
