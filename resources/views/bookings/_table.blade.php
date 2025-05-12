<div class="mb-3 text-start">
    {{-- تم تغيير الرابط هنا --}}
    <a href="{{ route('bookings.export', request()->query()) }}" class="btn btn-success">
        <i class="fas fa-file-excel me-1"></i> الجدول المعروض فقط إلى Excel
    </a>
    {{-- الزر الجديد (يصدر كل الحجوزات النشطة) --}}
    <a href="{{ route('bookings.export.all') }}" class="btn btn-info"> {{-- لون مختلف للتمييز --}}
        <i class="fas fa-file-download me-1"></i> تصدير كل الحجوزات
    </a>
</div>
<table class="table table-bordered table-hover table-striped">
    <thead class="table-dark"> {{-- Use a dark header for better contrast striped يعني مخطط عشان العين تلاحظ كل صف لوحده بلون --}}
        <tr>
            <th class="text-center" style="width: 3%;">م</th>
            <th>العميل</th>
            <th>الشركة</th>
            {{-- *** بداية التعديل: إخفاء جهة الحجز للشركة *** --}}
            @if (auth()->user()->role !== 'Company')
                <th>جهة حجز</th>
            @endif
            {{-- *** نهاية التعديل *** --}}
            <th>الفندق</th>
            <th style="min-width: 100px;"> الدخول</th> {{-- تحديد عرض أدنى للتواريخ --}}
            <th style="min-width: 100px;"> الخروج</th>
            {{-- <th>عدد الأيام</th> --}}
            <th class="text-center">غرف</th> {{-- اختصار "عدد الغرف" --}}
            {{-- *** بداية التعديل: إخفاء المستحق للفندق للشركة *** --}}
            @if (auth()->user()->role !== 'Company')
                {{-- الشرط القديم بتاع company_id مش محتاجينه هنا لأن الشركة مش هتشوف العمود ده أصلاً --}}
                {{-- @if (!request('company_id')) --}}
                <th style="min-width: 100px;"> المستحق للفندق</th>
                {{-- @endif --}}
            @endif
            {{-- *** نهاية التعديل *** --}}
            <th style="min-width: 100px;"> مطلوب من الشركة</th>
            {{-- <th class="text-center">العملة</th> إضافة عمود العملة --}}

            {{-- <th>السداد من الشركة</th> --}}
            <th>الموظف المسؤول</th>
            {{-- *** بداية التعديل: إخفاء الملاحظات والإجراءات للشركة *** --}}
            @if (auth()->user()->role !== 'Company')
                <th class="text-center">الملاحظات</th>
                <th class="text-center" style="min-width: 130px;">الإجراءات</th>
            @endif
            {{-- *** نهاية التعديل *** --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($bookings as $booking)
            <tr>
                <td class="text-center align-middle">{{ $loop->iteration }}</td> <!-- رقم الصف -->
                <td class="text-center align-middle">
                    {{-- *** تعديل: الشركة لا ترى رابط التفاصيل *** --}}
                    @if (auth()->user()->role !== 'Company')
                        <a href="{{ route('bookings.show', $booking->id) }}"
                            class="text-primary text-decoration-none fw-bold">
                            {{ $booking->client_name }}
                        </a>
                    @else
                        {{ $booking->client_name }}
                    @endif
                </td>
                <td class="text-center align-middle">
                    {{-- *** تعديل: الشركة لا ترى رابط فلترة الشركة *** --}}
                    @if (auth()->user()->role !== 'Company')
                        <a href="{{ route('bookings.index', ['company_id' => $booking->company->id]) }}"
                            class="text-primary text-decoration-none">
                            {{ $booking->company->name }}
                        </a>
                    @else
                        {{ $booking->company->name }}
                    @endif
                </td>
                {{-- *** بداية التعديل: إخفاء جهة الحجز للشركة *** --}}
                @if (auth()->user()->role !== 'Company')
                    <td class="text-center align-middle">
                        <a href="{{ route('bookings.index', ['agent_id' => $booking->agent->id]) }}"
                            class="text-primary text-decoration-none">
                            {{ $booking->agent->name }}
                        </a>
                    </td>
                @endif
                {{-- *** نهاية التعديل *** --}}
                <td class="text-center align-middle">
                    {{-- *** تعديل: الشركة لا ترى رابط فلترة الفندق *** --}}
                    @if (auth()->user()->role !== 'Company')
                        <a href="{{ route('bookings.index', ['hotel_id' => $booking->hotel->id]) }}"
                            class="text-primary text-decoration-none">
                            {{ $booking->hotel->name }}
                        </a>
                    @else
                        {{ $booking->hotel->name }}
                    @endif
                </td>
                <td class="text-center align-middle">{{ $booking->check_in->format('d/m/Y') }}</td>
                <td class="text-center align-middle">{{ $booking->check_out->format('d/m/Y') }}</td>
                {{-- <td class="text-center align-middle">{{ $booking->days }}</td> --}}
                <td class="text-center align-middle">{{ $booking->rooms }}</td>
                {{-- *** بداية التعديل: إخفاء المستحق للفندق للشركة *** --}}
                @if (auth()->user()->role !== 'Company')
                    {{-- الشرط القديم بتاع company_id مش محتاجينه هنا --}}
                    {{-- @if (!request('company_id')) --}}
                    <td class="text-center align-middle"
                        title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->cost_price }} سعر الفندق)">
                        {{ $booking->amount_due_to_hotel }}
                        {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                    </td>
                    {{-- @endif --}}
                @endif
                {{-- *** نهاية التعديل *** --}}
                <td class="text-center align-middle"
                    title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->sale_price }} سعر الليلة)">
                    {{ $booking->amount_due_from_company }}
                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                </td>
                {{-- <td class="text-center align-middle">
                    {{ $booking->currency == 'SAR' ? 'ريال' : 'دينار' }}
                </td> --}}
                <td class="text-center align-middle">
                    {{-- *** تعديل: الشركة لا ترى رابط فلترة الموظف *** --}}
                    @if (auth()->user()->role !== 'Company')
                        <a href="{{ route('bookings.index', ['employee_id' => $booking->employee->id]) }}"
                            class="text-primary text-decoration-none">
                            {{ $booking->employee->name }}
                        </a>
                    @else
                        {{ $booking->employee->name }}
                    @endif
                </td>
                {{-- *** بداية التعديل: إخفاء الملاحظات والإجراءات للشركة *** --}}
                @if (auth()->user()->role !== 'Company')
                    <td class="text-center align-middle">
                        {{-- Notes Popover Implementation --}}
                        @if (!empty($booking->notes))
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="popover"
                                data-bs-trigger="hover focus" {{-- Show on hover or focus --}} data-bs-placement="left"
                                data-bs-custom-class="notes-popover" title="الملاحظات"
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
                        @auth
                            @if (auth()->user()->role === 'Admin')
                                {{-- زر الحذف للأدمن فقط --}}
                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                    style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحجز؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        @endauth
                    </td>
                @endif
                {{-- *** نهاية التعديل *** --}}
            </tr>
        @endforeach
        @php
            // $pageDueToHotels = $bookings->sum('amount_due_to_hotel');
            // $pageDueFromCompany = $bookings->sum('amount_due_from_company');
            // تجميع المبالغ حسب العملة مباشرة من الـ Collection
            $pageDueToHotelsByCurrency = $bookings
                ->groupBy('currency')
                ->map(function ($group) {
                    return [
                        'currency' => $group->first()->currency,
                        'amount' => $group->sum('amount_due_to_hotel'),
                    ];
                })
                ->values()
                ->toArray();

            $pageDueFromCompanyByCurrency = $bookings
                ->groupBy('currency')
                ->map(function ($group) {
                    return [
                        'currency' => $group->first()->currency,
                        'amount' => $group->sum('amount_due_from_company'),
                    ];
                })
                ->values()
                ->toArray();

            // حساب عدد الأعمدة المخفية
            $hiddenCols = 0;
            if (auth()->user()->role === 'Company') {
                $hiddenCols += 4; // جهة الحجز + المستحق للفندق + الملاحظات + الإجراءات
            }
            // حساب الـ colspan الأساسي (بدون الأعمدة المخفية)
            $baseColspan = 8; // م + العميل +جهة الحجز +  الشركة + الفندق + الدخول + الخروج + غرف
            if (auth()->user()->role === 'Company') {
                $baseColspan = 7; // جهة الحجز + المستحق للفندق + الملاحظات + الإجراءات
            }
            $finalColspan = $baseColspan;
            $totalColsAfterAmount = 1; // الموظف المسؤول
        @endphp
        <tr style="background: #f8f9fa; font-weight: bold;">
            {{-- الـ colspan الأول يشمل الأعمدة حتى قبل عمود "مطلوب من الشركة" --}}
            <td colspan="{{ $finalColspan }}" class="text-end">المجموع في الصفحة:</td>
            {{-- عمود مجموع المستحق للفندق (يظهر فقط لغير الشركة) --}}
            @if (auth()->user()->role !== 'Company')
                <td class="text-center">
                    @foreach ($pageDueToHotelsByCurrency as $currencyGroup)
                        {{ number_format($currencyGroup['amount'], 2) }}
                        {{ $currencyGroup['currency'] == 'SAR' ? 'ريال' : 'دينار' }}<br>
                    @endforeach
                </td>
            @endif
            {{-- عمود مجموع مطلوب من الشركة (يظهر للكل) --}}
            <td class="text-center">
                @foreach ($pageDueFromCompanyByCurrency as $currencyGroup)
                    {{ number_format($currencyGroup['amount'], 2) }}
                    {{ $currencyGroup['currency'] == 'SAR' ? 'ريال' : 'دينار' }}<br>
                @endforeach
            </td>
            {{-- الـ colspan الأخير يشمل الأعمدة بعد "مطلوب من الشركة" (مع الأخذ في الاعتبار الأعمدة المخفية) --}}
            <td colspan="{{ $totalColsAfterAmount }}"></td>
        </tr>
    </tbody>
</table>
<script>
document.addEventListener("DOMContentLoaded", function () {
    function mergeTableCellsForMobile() {
        if (window.innerWidth >= 768) {
            // عند العودة للديسكتوب أظهر كل الأعمدة
            document.querySelectorAll('.table').forEach(table => {
                table.querySelectorAll('thead th, tbody td').forEach(cell => {
                    cell.style.display = '';
                });
            });
            return;
        }

        document.querySelectorAll('.table').forEach(table => {
            // استخراج رؤوس الأعمدة الفعلية
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());

            // حدد الأعمدة المطلوب إخفاؤها حسب النص
            const hideCols = ['غرف', 'الدخول', 'الخروج'];
            // إخفاء رؤوس الأعمدة المطلوبة
            headers.forEach((h, idx) => {
                if (hideCols.some(col => h.includes(col))) {
                    const th = table.querySelector(`thead th:nth-child(${idx + 1})`);
                    if (th) th.style.display = 'none';
                }
            });

            // لكل صف بيانات
            table.querySelectorAll('tbody tr').forEach(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                if (cells.length < 4) return;

                // ابحث عن الأعمدة حسب النص (وليس الترتيب)
                let clientIdx = -1, roomsIdx = -1, hotelIdx = -1, checkInIdx = -1, checkOutIdx = -1;
                headers.forEach((h, i) => {
                    if (h.includes('العميل')) clientIdx = i;
                    if (h.includes('غرف')) roomsIdx = i;
                    if (h.includes('فندق')) hotelIdx = i;
                    if (h.includes('الدخول')) checkInIdx = i;
                    if (h.includes('الخروج')) checkOutIdx = i;
                });

                // دمج اسم العميل + عدد الغرف
                if (clientIdx !== -1 && roomsIdx !== -1 && cells[clientIdx] && cells[roomsIdx]) {
                    if (!cells[clientIdx].innerHTML.includes('غرفة')) {
                        cells[clientIdx].innerHTML += `<span class="d-block text-muted small">(${cells[roomsIdx].textContent.trim()} غرفة)</span>`;
                    }
                    cells[roomsIdx].style.display = 'none';
                }

                // دمج التواريخ + الفندق
                if (hotelIdx !== -1 && checkInIdx !== -1 && checkOutIdx !== -1 &&
                    cells[hotelIdx] && cells[checkInIdx] && cells[checkOutIdx]) {
                    if (!cells[hotelIdx].innerHTML.includes('دخول:')) {
                        cells[hotelIdx].innerHTML += `<div class="text-muted small">دخول: ${cells[checkInIdx].textContent.trim()}<br>خروج: ${cells[checkOutIdx].textContent.trim()}</div>`;
                    }
                    cells[checkInIdx].style.display = 'none';
                    cells[checkOutIdx].style.display = 'none';
                }
            });
        });
    }

    mergeTableCellsForMobile();
    window.addEventListener('resize', mergeTableCellsForMobile);
    document.addEventListener('ajaxTableUpdated', mergeTableCellsForMobile);
});
</script>
