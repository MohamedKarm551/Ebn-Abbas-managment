@extends('layouts.app')
@section('title', 'التقارير اليومية')
@section('favicon')
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/daily_reports.css') }}">
@endpush
{{-- *** الخطوة 1: تضمين Chart.js (يفضل وضعه في layout/app.blade.php قبل نهاية </body>) *** --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- الرسم البياني: الحجوزات اليومية (Line Chart) ---
            // >>>>> تأكد إن الكود ده كله موجود هنا <<<<<
            const dailyCtx = document.getElementById(
            'dailyBookingsChart'); // <-- لازم الـ ID ده يكون نفس الـ ID بتاع الـ canvas فوق
            const dailyLabels = @json($chartDates ?? []); // <-- بياخد التواريخ من Controller
            const dailyData = @json($bookingCounts ?? []); // <-- بياخد عدد الحجوزات من Controller

            if (dailyCtx && dailyLabels.length > 0) { // بيتأكد إن فيه canvas وبيانات
                new Chart(dailyCtx, {
                    type: 'line', // نوع الرسم: خطي
                    data: {
                        labels: dailyLabels, // التواريخ اللي تحت
                        datasets: [{
                            label: 'عدد الحجوزات', // اسم الخط
                            data: dailyData, // الأرقام اللي هيرسمها
                            fill: true, // يلون تحت الخط
                            borderColor: 'rgb(75, 192, 192)', // لون الخط
                            backgroundColor: 'rgba(75, 192, 192, 0.2)', // لون التعبئة
                            tension: 0.1 // يخلي الخط منحني شوية
                        }]
                    },
                    options: { // خيارات إضافية للرسم
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }, // يخلي المحور Y يبدأ من صفر وأرقامه صحيحة
                        plugins: {
                            legend: {
                                display: false
                            }, // يخفي اسم الخط لو هو خط واحد
                            tooltip: { // لما تقف بالماوس على نقطة
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    title: function(tooltipItems) {
                                        return 'تاريخ: ' + tooltipItems[0].label;
                                    }, // يكتب التاريخ فوق
                                    label: function(context) { // يكتب عدد الحجوزات
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y;
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: true
                        }
                    }
                });
            } else if (dailyCtx) { // لو مفيش بيانات يعرض رسالة
                dailyCtx.parentNode.innerHTML =
                    '<p class="text-center text-muted">لا توجد بيانات لعرض الرسم البياني للحجوزات اليومية.</p>';
            }
            // >>>>> نهاية كود الرسم البياني اليومي <<<<<

            // --- بيانات الرسم البياني للشركات (Bar Chart) ---
            // ... (باقي أكواد الرسوم البيانية التانية) ...

        }); // نهاية DOMContentLoaded
    </script>
@endpush

@section('content')
    <div class="container">
        <h1>التقرير اليومي - {{ \Carbon\Carbon::now()->format('d/m/Y') }}</h1>
        {{-- *** الخطوة 2: قسم لوحة المعلومات المصغرة *** --}}
        <div class=" mb-4 shadow-sm">
            <div class="card-header">
                <h3 class="mb-0">نظرة عامة سريعة</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- الملخصات الحالية --}}
                    <div class="col-md-4 mb-3">
                        <div class=" h-100">
                            <div class="card-body">
                                <h5 class="card-title text-primary">ملخص اليوم</h5>
                                <ul class="list-unstyled mb-0">
                                    <li>
                                        <a href="{{ route('bookings.index', ['start_date' => now()->format('d/m/Y')]) }}"
                                            class="fw-bold text-decoration-none text-primary">
                                            <i class="fas fa-calendar-day me-1"></i> حجوزات اليوم:
                                            {{ $todayBookings->count() }}
                                        </a>
                                    </li>
                                    <li class="fw-bold text-danger"><i class="fas fa-file-invoice-dollar me-1"></i> متبقي من
                                        الشركات: {{ number_format($totalRemainingFromCompanies) }} ريال</li>
                                    <li class="fw-bold text-warning"><i class="fas fa-hand-holding-usd me-1"></i> متبقي
                                        للفنادق/الجهات: {{ number_format($totalRemainingToHotels) }} ريال</li>
                                    <li class="fw-bold text-success"><i class="fas fa-chart-line me-1"></i> صافي الربح:
                                        {{ number_format($netProfit) }} ريال</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- *** الخطوة 3: قائمة أعلى 5 شركات عليها مبالغ *** --}}
                    <div class="col-md-4 mb-3">
                        <div class=" h-100">
                            <div class="card-body">
                                <h5 class="card-title text-danger"><i class="fas fa-exclamation-triangle me-1"></i> أعلى 5
                                    شركات عليها مبالغ</h5>
                                @php
                                    // فرز الشركات حسب المتبقي (الأعلى أولاً) في Blade (الأفضل عمله في Controller)
                                    $topCompanies = $companiesReport->sortByDesc('remaining')->take(5);
                                @endphp
                                <ul class="list-unstyled mb-0 small">
                                    @forelse ($topCompanies as $company)
                                        @if ($company->remaining > 0)
                                            {{-- عرض فقط إذا كان المتبقي أكبر من صفر --}}
                                            <li>{{ $company->name }}: <span
                                                    class="fw-bold">{{ number_format($company->remaining) }} ريال</span>
                                            </li>
                                        @endif
                                    @empty
                                        <li>لا توجد شركات عليها مبالغ متبقية حاليًا.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    {{-- *** الخطوة 3: قائمة أعلى 5 جهات لها مبالغ *** --}}
                    <div class="col-md-4 mb-3">
                        <div class=" h-100">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><i class="fas fa-money-check-alt me-1"></i> أعلى 5 جهات
                                    لها مبالغ</h5>
                                @php
                                    // فرز الجهات حسب المتبقي (الأعلى أولاً) في Blade (الأفضل عمله في Controller)
                                    $topAgents = $agentsReport->sortByDesc('remaining')->take(5);
                                @endphp
                                <ul class="list-unstyled mb-0 small">
                                    @forelse ($topAgents as $agent)
                                        @if ($agent->remaining > 0)
                                            {{-- عرض فقط إذا كان المتبقي أكبر من صفر --}}
                                            <li>{{ $agent->name }}: <span
                                                    class="fw-bold">{{ number_format($agent->remaining) }}</span></li>
                                        @endif
                                    @empty
                                        <li>لا توجد جهات لها مبالغ متبقية حاليًا.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- *** الخطوة 4: إضافة Canvas للرسوم البيانية *** --}}
                <div class="row mt-3">
                    <div class="col-md-6 mb-3"> {{-- إضافة mb-3 --}}
                        <h5 class="text-center text-danger">المتبقي على الشركات (أعلى 5)</h5>
                        <div class="chart-container" style="position: relative; height:250px; width:100%">
                            <canvas id="topCompaniesChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3"> {{-- إضافة mb-3 --}}
                        <h5 class="text-center text-warning">المتبقي لجهات الحجز (أعلى 5)</h5>
                        <div class="chart-container" style="position: relative; height:250px; width:100%">
                            <canvas id="topAgentsChart"></canvas>
                        </div>
                    </div>

                    {{-- *** إضافة Canvas للرسوم الجديدة *** --}}
                    <div class="col-md-6 mb-3">
                        <h5 class="text-center text-info">مقارنة إجمالي المتبقي</h5>
                        <div class="chart-container" style="position: relative; height:250px; width:100%">
                            <canvas id="remainingComparisonChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h5 class="text-center text-success">توزيع الحجوزات (أعلى 5 شركات)</h5>
                        <div class="chart-container" style="position: relative; height:250px; width:100%">
                            <canvas id="companyBookingDistributionChart"></canvas>
                        </div>
                    </div>
                    {{-- *** نهاية إضافة Canvas *** --}}
                </div>
            </div>
        </div>
        {{-- *** الرسم البياني الجديد: الحجوزات اليومية *** --}}
        <div class="col-md-12 mb-4"> {{-- ممكن تخليه col-md-6 لو عايزه جنب رسم تاني --}}
            <div class=" shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0 text-primary">الحجوزات خلال آخر {{ count($chartDates ?? []) }} يومًا</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:350px; width:100%">
                        {{-- >>>>> السطر ده هو المهم <<<<< --}}
                        <canvas id="dailyBookingsChart"></canvas>
                        {{-- >>>>> تأكد إن السطر ده موجود <<< --}}
                    </div>
                </div>
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
        <div class="  mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>حساب المطلوب من الشركات</h3>
                <button class="btn btn-secondary btn-sm" onclick="copyTable('companiesTable')">نسخ الجدول</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-bordered table-striped" id="companiesTable">
                        <thead>
                            <tr>
                                <th>الشركة</th>
                                <th>عدد الحجوزات</th>
                                <th>إجمالي المستحق</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($companiesReport as $company)
                                <tr>
                                    <td>{{ $loop->iteration }}. {{ $company->name }}
                                        @php
                                            $hasEdit =
                                                $recentCompanyEdits
                                                    ->filter(function ($n) use ($company) {
                                                        return str_contains($n->first()->message, $company->name);
                                                    })
                                                    ->count() > 0;
                                        @endphp
                                        @if ($hasEdit)
                                            <span class="badge bg-success" style="font-size: 0.7em;">edit</span>
                                        @endif
                                    </td>
                                    <td>{{ $company->bookings_count }}</td>
                                    <td>{{ number_format($company->total_due) }} ريال</td>
                                    <td
                                        @if ($company->total_paid > $company->total_due) style="color: red !important; font-weight: bold;"
                                            title="المبلغ المدفوع أكثر من المستحق" @endif>
                                        {{ number_format($company->total_paid) }} ريال
                                    </td>
                                    <td>{{ number_format($company->remaining) }} ريال</td>
                                    <td>
                                        <!-- بنستخدم div بتقسيم مرن علشان الأزرار تجي مترتبة ومتباعدة -->
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                            <!-- زر عرض الحجوزات مع مسافة margin-end -->

                                            <a href="{{ route('reports.company.bookings', $company->id) }}"
                                                class="btn btn-info btn-sm">عرض الحجوزات</a>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#paymentModal{{ $company->id }}">
                                                تسجيل دفعة
                                            </button>
                                            <a href="{{ route('reports.company.payments', $company->id) }}"
                                                class="btn btn-primary btn-sm">عرض السجل</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="2" class="text-center">الإجمالي</td>
                                <td>{{ number_format($companiesReport->sum('total_due')) }} ريال</td>
                                <td>{{ number_format($companiesReport->sum('total_paid')) }} ريال</td>
                                <td>{{ number_format($companiesReport->sum('remaining')) }} ريال</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- جدول جهات الحجز -->
        <div class="  mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>حسابات اللي علينا إلى جهات الحجز</h3>
                <button class="btn btn-secondary btn-sm" onclick="copyTable('agentsTable')">نسخ الجدول</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="agentsTable">
                        <thead>
                            <tr>
                                <th>جهة الحجز</th>
                                <th>عدد الحجوزات</th>
                                <th>إجمالي المبالغ</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agentsReport as $agent)
                                <tr>
                                    <td>{{ $loop->iteration }}.{{ $agent->name }}
                                        @php
                                            $hasEdit =
                                                $resentAgentEdits
                                                    ->filter(function ($n) use ($agent) {
                                                        return str_contains($n->first()->message, $agent->name);
                                                    })
                                                    ->count() > 0;
                                        @endphp
                                        @if ($hasEdit)
                                            <span class="badge bg-success" style="font-size: 0.7em;">edit</span>
                                        @endif
                                    </td>
                                    <td>{{ $agent->bookings_count }}</td>
                                    <td>{{ number_format($agent->total_due) }}</td>
                                    <td
                                        @if ($agent->total_paid > $agent->total_due) style="color: red !important; font-weight: bold;"
                                            title="المبلغ المدفوع أكثر من المستحق" @endif>
                                        {{ number_format($agent->total_paid) }}
                                    </td>
                                    <td>{{ number_format($agent->remaining) }}</td>
                                    <td>
                                        <!-- بنستخدم div بتقسيم مرن علشان الأزرار تجي مترتبة ومتباعدة -->
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                            <!-- زر عرض الحجوزات مع مسافة margin-end -->

                                            <a href="{{ route('reports.agent.bookings', $agent->id) }}"
                                                class="btn btn-info btn-sm">عرض الحجوزات</a>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#agentPaymentModal{{ $agent->id }}">
                                                تسجيل دفعة
                                            </button>
                                            <a href="{{ route('reports.agent.payments', $agent->id) }}"
                                                class="btn btn-primary btn-sm">عرض السجل</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="2" class="text-center">الإجمالي</td>
                                <td>{{ number_format($agentsReport->sum('total_due')) }}</td>
                                <td>{{ number_format($agentsReport->sum('total_paid')) }}</td>
                                <td>{{ number_format($agentsReport->sum('remaining')) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- نماذج تسجيل الدفعات لجهات الحجز -->
        @foreach ($agentsReport as $agent)
            <div class="modal fade" id="agentPaymentModal{{ $agent->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('reports.agent.payment') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="agent_id" value="{{ $agent->id }}">

                            <div class="modal-header">
                                <h5 class="modal-title">تسجيل دفعة - {{ $agent->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">المبلغ المدفوع</label>
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
                                </div>
                                {{-- ***   حقل رفع الملف في مشكلة مع الكومبوسر وجوجل درايف  *** --}}
                                {{-- <div class="mb-3">
                                    <label for="receipt_file_agent_{{ $agent->id }}" class="form-label">إرفاق إيصال
                                        (اختياري)</label>
                                    <input class="form-control" type="file" id="receipt_file_agent_{{ $agent->id }}"
                                        name="receipt_file">
                                    <small class="form-text text-muted">الملفات المسموحة: JPG, PNG, PDF (بحد أقصى
                                        5MB)</small>
                                </div> --}}
                                {{-- *** نهاية حقل رفع الملف *** --}}
                                <div class="mb-3">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea class="form-control" name="notes"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                <button type="submit" class="btn btn-primary">تسجيل الدفعة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- إضافة سكريبت النسخ -->
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
            </script>
        @endpush

        <!-- نموذج تسجيل الدفعات -->
        @foreach ($companiesReport as $company)
            <div class="modal fade" id="paymentModal{{ $company->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('reports.company.payment') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $company->id }}">

                            <div class="modal-header">
                                <h5 class="modal-title">تسجيل دفعة - {{ $company->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">المبلغ المدفوع</label>
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
                                </div>
                                {{-- *** أضف حقل رفع الملف مشكلة مع جوجل درايف لسه هتتحل  *** --}}
                                {{-- <div class="mb-3">
                                    <label for="receipt_file_company_{{ $company->id }}" class="form-label">إرفاق إيصال
                                        (اختياري)
                                    </label>
                                    <input class="form-control" type="file"
                                        id="receipt_file_company_{{ $company->id }}" name="receipt_file">
                                  
                                <small class="form-text text-muted">الملفات المسموحة: JPG, PNG, PDF (بحد أقصى
                                    5MB)</small>
                            </div> --}}
                                {{-- *** نهاية حقل رفع الملف *** --}}
                                <div class="mb-3">
                                    <label class="form-label">ملاحظات <br>
                                        (إن كانت معك صورة من التحويل ارفعها على درايف وضع الرابط هنا)
                                    </label>
                                    <textarea class="form-control" name="notes"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                <button type="submit" class="btn btn-primary">تسجيل الدفعة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- جدول الفنادق -->
        <div class="  mb-4">
            <div class="card-header">
                <h3>حسابات الفنادق</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>الفندق</th>
                                <th>عدد الحجوزات</th>
                                <th>إجمالي المستحق</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hotelsReport as $hotel)
                                <tr>
                                    <td>{{ $loop->iteration }}. {{ $hotel->name }}</td>
                                    <td>{{ $hotel->bookings_count }}</td>
                                    <td>{{ number_format($hotel->total_due) }}</td>
                                    <td>
                                        <a href="{{ route('reports.hotel.bookings', $hotel->id) }}"
                                            class="btn btn-info btn-sm">عرض الحجوزات</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="2" class="text-center">الإجمالي</td>
                                <td>{{ number_format($hotelsReport->sum('total_due')) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- إضافة تنسيقات CSS في القسم الخاص بالستيلات -->
   
    {{-- *** الخطوة 5: JavaScript لإنشاء الرسوم البيانية *** --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // --- بيانات الرسم البياني للشركات ---
                const topCompaniesLabels = @json($topCompanies->pluck('name'));
                const topCompaniesDataPoints = @json($topCompanies->pluck('remaining'));

                const ctxCompanies = document.getElementById('topCompaniesChart');
                if (ctxCompanies && topCompaniesLabels.length > 0) { // التأكد من وجود العنصر والبيانات
                    new Chart(ctxCompanies, {
                        type: 'bar', // نوع الرسم: أعمدة
                        data: {
                            labels: topCompaniesLabels,
                            datasets: [{
                                label: 'المتبقي (ريال)',
                                data: topCompaniesDataPoints,
                                backgroundColor: 'rgba(220, 53, 69, 0.7)', // لون أحمر شفاف
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // مهم للحفاظ على الحجم المحدد في CSS
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { // تنسيق الأرقام على المحور Y (اختياري)
                                        callback: function(value, index, values) {
                                            return value.toLocaleString('ar-SA') +
                                                ' ريال'; // تنسيق الأرقام بالعربية السعودية
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }, // إخفاء مفتاح الرسم (label)
                                tooltip: { // تنسيق التلميح عند المرور (اختياري)
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y.toLocaleString('ar-SA') + ' ريال';
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // --- بيانات الرسم البياني لجهات الحجز ---
                const topAgentsLabels = @json($topAgents->pluck('name'));
                const topAgentsDataPoints = @json($topAgents->pluck('remaining'));

                const ctxAgents = document.getElementById('topAgentsChart');
                if (ctxAgents && topAgentsLabels.length > 0) { // التأكد من وجود العنصر والبيانات
                    new Chart(ctxAgents, {
                        type: 'bar',
                        data: {
                            labels: topAgentsLabels,
                            datasets: [{
                                label: 'المتبقي (ريال)',
                                data: topAgentsDataPoints,
                                backgroundColor: 'rgba(255, 193, 7, 0.7)', // لون أصفر/برتقالي شفاف
                                borderColor: 'rgba(255, 193, 7, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: { // نفس الخيارات السابقة للاتساق
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return value.toLocaleString('ar-SA') + ' ريال';
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y.toLocaleString('ar-SA') + ' ريال';
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // --- *** الرسم البياني الجديد: مقارنة إجمالي المتبقي (Doughnut) *** ---
                const totalRemainingFromCompanies = {{ $totalRemainingFromCompanies ?? 0 }};
                const totalRemainingToHotels = {{ $totalRemainingToHotels ?? 0 }};
                const ctxRemainingComparison = document.getElementById('remainingComparisonChart');

                if (ctxRemainingComparison && (totalRemainingFromCompanies > 0 || totalRemainingToHotels > 0)) {
                    new Chart(ctxRemainingComparison, {
                        type: 'doughnut', // نوع الرسم: دائري مجوف
                        data: {
                            labels: ['متبقي من الشركات', 'متبقي لجهات الحجز'],
                            datasets: [{
                                label: 'المبلغ (ريال)',
                                data: [totalRemainingFromCompanies, totalRemainingToHotels],
                                backgroundColor: [
                                    'rgba(220, 53, 69, 0.7)', // أحمر للشركات
                                    'rgba(255, 193, 7, 0.7)' // أصفر/برتقالي للجهات
                                ],
                                borderColor: [
                                    'rgba(220, 53, 69, 1)',
                                    'rgba(255, 193, 7, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top', // مكان ظهور مفتاح الرسم
                                },
                                tooltip: { // تنسيق التلميح
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed !== null) {
                                                label += context.parsed.toLocaleString('ar-SA') + ' ريال';
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // --- *** الرسم البياني الجديد: توزيع حجوزات الشركات (Pie) *** ---
                const topCompaniesBookingLabels = @json($topCompanies->pluck('name'));
                const topCompaniesBookingCounts = @json($topCompanies->pluck('bookings_count'));
                const totalCompanyBookings = {{ $companiesReport->sum('bookings_count') ?? 0 }};
                const top5CompanyBookingsSum = topCompaniesBookingCounts.reduce((a, b) => a + b, 0);
                const otherCompanyBookings = totalCompanyBookings - top5CompanyBookingsSum;

                const ctxCompanyBookingDist = document.getElementById('companyBookingDistributionChart');

                // التأكد من وجود بيانات وأن مجموع حجوزات الشركات أكبر من صفر
                if (ctxCompanyBookingDist && totalCompanyBookings > 0) {
                    let bookingDistLabels = [...topCompaniesBookingLabels];
                    let bookingDistData = [...topCompaniesBookingCounts];

                    // إضافة "أخرى" إذا كان هناك شركات أخرى
                    if (otherCompanyBookings > 0) {
                        bookingDistLabels.push('شركات أخرى');
                        bookingDistData.push(otherCompanyBookings);
                    }

                    new Chart(ctxCompanyBookingDist, {
                        type: 'pie', // نوع الرسم: دائري
                        data: {
                            labels: bookingDistLabels,
                            datasets: [{
                                label: 'عدد الحجوزات',
                                data: bookingDistData,
                                // يمكنك تحديد ألوان مختلفة لكل شريحة
                                backgroundColor: [
                                    'rgba(0, 123, 255, 0.7)',
                                    'rgba(40, 167, 69, 0.7)',
                                    'rgba(255, 193, 7, 0.7)',
                                    'rgba(23, 162, 184, 0.7)',
                                    'rgba(108, 117, 125, 0.7)',
                                    'rgba(160, 160, 160, 0.7)' // لون لـ "أخرى"
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            let value = context.parsed || 0;
                                            let percentage = totalCompanyBookings > 0 ? ((value /
                                                totalCompanyBookings) * 100).toFixed(1) : 0;
                                            label += value + ' (' + percentage +
                                                '%)'; // عرض العدد والنسبة المئوية
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

            });
        </script>
    @endpush


@endsection
