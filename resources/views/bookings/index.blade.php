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
            <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> {{-- إضافة Axios --}}
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const filterForm = document.getElementById('filterForm');
                    const bookingsTable = document.getElementById('bookingsTable'); // Assuming your table has this ID
                    const paginationContainer = document.querySelector(
                    '.d-flex.justify-content-center'); // Pagination container

                    filterForm.addEventListener('submit', function(event) {
                        event.preventDefault(); // منع الإرسال التقليدي

                        const formData = new FormData(filterForm);
                        const params = new URLSearchParams(formData)
                    .toString(); // تحويل بيانات الفورم إلى query string

                        axios.get('{{ route('bookings.index') }}?' + params) // إرسال طلب AJAX
                            .then(function(response) {
                                // تحديث الجدول بالمحتوى الجديد
                                bookingsTable.innerHTML = response.data
                                .table; // Assuming the response contains the table HTML
                                paginationContainer.innerHTML = response.data
                                .pagination; // Update pagination links
                            })
                            .catch(function(error) {
                                console.error('Error fetching data:', error);
                                alert('حدث خطأ أثناء جلب البيانات.');
                            });
                    });
                });
            </script>
        @endpush
    </div>

@endsection
