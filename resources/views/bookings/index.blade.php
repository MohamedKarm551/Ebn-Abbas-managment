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
            <table class="table table-bordered table-hover">
                <thead class="table-dark"> {{-- Use a dark header for better contrast --}}
                    <tr>
                        <th class="text-center" style="width: 5%;">م</th>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>جهة الحجز</th>
                        <th>الفندق</th>
                        <th style="min-width: 100px;">تاريخ الدخول</th> {{-- تحديد عرض أدنى للتواريخ --}}
                        <th style="min-width: 100px;">تاريخ الخروج</th>
                        {{-- <th>عدد الأيام</th> --}}
                        <th class="text-center">غرف</th> {{-- اختصار "عدد الغرف" --}}
                        @if (!request('company_id'))
                            <th style="120px;"> المستحق للفندق</th>
                            {{-- <th>السداد مني للفندق</th> --}}
                        @endif
                        <th style="min-width: 120px;"> المستحق من الشركة</th>
                        {{-- <th>السداد من الشركة</th> --}}
                        <th>الموظف المسؤول</th>
                        <th class="text-center">الملاحظات</th>
                        <th style="min-width: 130px;">الإجراءات</th> {{-- تحديد عرض أدنى لضمان ظهور الأزرار --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td> <!-- رقم الصف -->
                            <td class="text-center align-middle">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="text-primary">
                                    {{ $booking->client_name }}
                                </a>
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('bookings.index', ['company_id' => $booking->company->id]) }}"
                                    class="text-primary">
                                    {{ $booking->company->name }}
                                </a>
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('bookings.index', ['agent_id' => $booking->agent->id]) }}"
                                    class="text-primary">
                                    {{ $booking->agent->name }}
                                </a>
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('bookings.index', ['hotel_id' => $booking->hotel->id]) }}"
                                    class="text-primary">
                                    {{ $booking->hotel->name }}
                                </a>
                            </td>
                            <td class="text-center align-middle">{{ $booking->check_in->format('d/m/Y') }}</td>
                            <td class="text-center align-middle">{{ $booking->check_out->format('d/m/Y') }}</td>
                            {{-- <td class="text-center align-middle">{{ $booking->days }}</td> --}}
                            <td class="text-center align-middle">{{ $booking->rooms }}</td>
                            <td class="text-center align-middle"
                                title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->cost_price }} سعر الفندق)">
                                {{ $booking->amount_due_to_hotel }}
                            </td>
                            {{-- <td class="text-center align-middle">{{ $booking->amount_paid_to_hotel }}</td> --}}
                            <td class="text-center align-middle"
                                title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->sale_price }} سعر الليلة)">
                                {{ $booking->amount_due_from_company }}
                            </td>
                            {{-- <td class="text-center align-middle">{{ $booking->amount_paid_by_company }}</td> --}}
                            <td class="text-center align-middle">
                                <a href="{{ route('bookings.index', ['employee_id' => $booking->employee->id]) }}"
                                    class="text-primary">
                                    {{ $booking->employee->name }}
                                </a>
                            </td>
                            <td class="text-center align-middle">
                                {{-- Notes Popover Implementation --}}
                                @if (!empty($booking->notes))
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="popover" data-bs-trigger="hover focus" {{-- Show on hover or focus --}}
                                        data-bs-placement="left" data-bs-custom-class="notes-popover" title="الملاحظات"
                                        data-bs-content="{{ nl2br(e($booking->notes)) }}">
                                        <i class="fas fa-info-circle"></i> {{-- Font Awesome icon --}}
                                    </button>
                                @else
                                    <span class="text-muted small">--</span> {{-- Indicate no notes --}}
                                @endif
                            </td>

                            <td class="text-center align-middle">
                                {{-- Action Buttons with Icons --}}
                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-info me-1"
                                    title="التفاصيل"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-sm btn-warning me-1"
                                    title="تعديل"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                    style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحجز؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
            const paginationContainer = document.querySelector('.d-flex.justify-content-center'); // Pagination container

            filterForm.addEventListener('submit', function(event) {
                event.preventDefault(); // منع الإرسال التقليدي

                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData).toString(); // تحويل بيانات الفورم إلى query string

                axios.get('{{ route('bookings.index') }}?' + params) // إرسال طلب AJAX
                    .then(function(response) {
                        // تحديث الجدول بالمحتوى الجديد
                        bookingsTable.innerHTML = response.data.table; // Assuming the response contains the table HTML
                        paginationContainer.innerHTML = response.data.pagination; // Update pagination links
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
