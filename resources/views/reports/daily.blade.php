@extends('layouts.app')
@section('title', 'التقارير اليومية')
@section('favicon')
    <link rel="icon" type="image/jpeg" href="{{ asset('images/cover.jpg') }}">
@endsection
@section('content')
    <div class="container">
        <h1>التقرير اليومي - {{ \Carbon\Carbon::now()->format('d/m/Y') }}</h1>

        <div class=" mb-4">
            <div class="card-header">
                <h3>ملخص اليوم</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li>
                                <a href="{{ route('bookings.index', ['start_date' => now()->format('d/m/Y') ]) }}"
                                   class="fw-bold text-decoration-none text-primary">
                                    عدد الحجوزات اليوم: {{ $todayBookings->count() }}
                                </a>
                            </li>
                            <li>إجمالي المستحق من الشركات: {{ number_format($totalDueFromCompanies) }} ريال</li>
                            <li>إجمالي المدفوع للفنادق: {{ number_format($totalPaidToHotels) }} ريال</li>
                            <li>صافي الربح: {{ number_format($totalDueFromCompanies - $totalPaidToHotels) }} ريال</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

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
                                <td>{{ $company->name }}</td>
                                <td>{{ $company->bookings_count }}</td>
                                <td>{{ number_format($company->total_due) }} ريال</td>
                                <td>{{ number_format($company->total_paid) }} ريال</td>
                                <td>{{ number_format($company->remaining) }} ريال</td>
                                <td>
                                        <!-- بنستخدم div بتقسيم مرن علشان الأزرار تجي مترتبة ومتباعدة -->
    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
        <!-- زر عرض الحجوزات مع مسافة margin-end -->

                                    <a href="{{ route('reports.company.bookings', $company->id) }}" class="btn btn-info btn-sm">عرض الحجوزات</a>
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $company->id }}">
                                        تسجيل دفعة
                                    </button>
                                    <a href="{{ route('reports.company.payments', $company->id) }}" class="btn btn-primary btn-sm">عرض السجل</a>
    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        <!-- جدول جهات الحجز -->
        <div class="  mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>حسابات  اللي علينا إلى جهات الحجز</h3>
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
                        @foreach($agentsReport as $agent)
                        <tr>
                            <td>{{ $agent->name }}</td>
                            <td>{{ $agent->bookings_count }}</td>
                            <td>{{ number_format($agent->total_due) }}</td>
                            <td>{{ number_format($agent->total_paid) }}</td>
                            <td>{{ number_format($agent->remaining) }}</td>
                            <td>
                                    <!-- بنستخدم div بتقسيم مرن علشان الأزرار تجي مترتبة ومتباعدة -->
    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
        <!-- زر عرض الحجوزات مع مسافة margin-end -->

                                <a href="{{ route('reports.agent.bookings', $agent->id) }}" class="btn btn-info btn-sm">عرض الحجوزات</a>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#agentPaymentModal{{ $agent->id }}">
                                    تسجيل دفعة
                                </button>
                                <a href="{{ route('reports.agent.payments', $agent->id) }}" class="btn btn-primary btn-sm">عرض السجل</a>
    </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

        <!-- نماذج تسجيل الدفعات لجهات الحجز -->
        @foreach($agentsReport as $agent)
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
                                <label class="form-label">المبلغ المدفوع</label>
                                <input type="number" step="0.01" class="form-control" name="amount" required>
                            </div>
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
                        <form action="{{ route('reports.company.payment') }}" method="POST">
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
                        @foreach($hotelsReport as $hotel)
                        <tr>
                            <td>{{ $hotel->name }}</td>
                            <td>{{ $hotel->bookings_count }}</td>
                            <td>{{ number_format($hotel->total_due) }}</td>
                            <td>
                                <a href="{{ route('reports.hotel.bookings', $hotel->id) }}" class="btn btn-info btn-sm">عرض الحجوزات</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- إضافة تنسيقات CSS في القسم الخاص بالستيلات -->
<style>
  /* تنسيق الجدول على الشاشات الصغيرة */
  @media (max-width: 768px) {
      /* نعدل حجم الخط داخل الجدول عشان يكون أصغر ومناسب */
      .table-responsive table {
          font-size: 14px;
      }
      /* لو محتاجين نحدد عرض أدنى للجدول علشان تظهر الأعمدة كلها */
      .table-responsive table {
          min-width: 600px;
      }
      /* تقليل الحشوة في خلايا الجدول */
      .table-responsive th,
      .table-responsive td {
          padding: 8px 5px;
          text-align: center; /* أو اضبط حسب المطلوب */
      }
      /* ممكن تحاول تخلي عناوين الجدول تظهر بخط واضح وتكون محاذاة مركز */
      .table-responsive thead th {
          background: #f8f9fa;
          font-weight: bold;
      }
  }
</style>
@endsection
