@extends('layouts.app')

@section('title', 'حجوزات الشركات - الرحلات البرية')

@push('styles')
    <style>
        /* استخدام نفس متغيرات التصميم المعتمدة في النظام */
        :root {
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #2653eb 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.15);
            --border-radius-lg: 24px;
            --border-radius: 20px;
        }

        .company-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-light);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .company-card:hover {
            transform: translateY(-5px);
        }

        .company-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-name {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }

        .company-icon {
            margin-left: 0.75rem;
            font-size: 1.2rem;
        }

        .company-stats {
            display: flex;
            gap: 1rem;
        }

        .stat-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .company-content {
            padding: 0;
        }

        .summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .summary-value.sar {
            color: #059669;
        }

        .summary-value.kwd {
            color: #d97706;
        }

        .bookings-list {
            padding: 0;
        }

        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .booking-item:last-child {
            border-bottom: none;
        }

        .booking-info h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .booking-date {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .booking-amount {
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
        }

        .booking-amount.sar {
            background: var(--success-gradient);
            color: white;
        }

        .booking-amount.kwd {
            background: var(--warning-gradient);
            color: white;
        }

        .expand-button {
            background: none;
            border: none;
            color: #667eea;
            padding: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            text-align: center;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .expand-button:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        .no-companies {
            text-align: center;
            padding: 4rem 2rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- رأس الصفحة -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                @if (isset($agent))
                    <h1 class="h3 mb-1">حجوزات الشركات لدى "{{ $agent->name }}" - الرحلات البرية</h1>
                    <p class="text-muted">عرض الشركات التي لديها حجوزات من خلال {{ $agent->name }} مع تفاصيل الحسابات</p>
                @else
                    <h1 class="h3 mb-1">حجوزات الشركات - الرحلات البرية</h1>
                    <p class="text-muted">عرض حجوزات كل شركة بشكل منفصل مع إحصائيات</p>
                @endif
            </div>

            <div>
                @if (isset($agent))
                    <a href="{{ route('admin.land-trips-agent-payments.show', $agent->id) }}"
                        class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة لصفحة {{ $agent->name }}
                    </a>
                @endif
                <a href="{{ route('admin.land-trips.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-1"></i>
                    الرحلات البرية
                </a>
            </div>
        </div>

        <!-- رسالة إذا كان يتم عرض بيانات وكيل محدد -->
        @if (isset($agent))
            <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-filter me-3 fa-2x"></i>
                    <div>
                        <h5 class="alert-heading mb-1">تصفية حسب الوكيل: {{ $agent->name }}</h5>
                        <p class="mb-0">يتم عرض الشركات التي لديها حجوزات فقط من خلال هذا الوكيل</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- فلتر البحث -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.companies.bookings') }}" method="GET" class="row g-3 align-items-end">
                    <!-- نحتفظ بمعرّف الوكيل إذا كان موجوداً -->
                    @if (isset($agent))
                        <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                    @endif

                    <!-- حقل البحث -->
                    <div class="col-md-3">
                        <label for="search" class="form-label">بحث</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="اسم الشركة..."
                            value="{{ request('search') }}">
                    </div>

                    <!-- فلتر التاريخ -->
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">من تاريخ</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">إلى تاريخ</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                            value="{{ request('end_date') }}">
                    </div>

                    <!-- أزرار -->
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                        <a href="{{ isset($agent) ? route('admin.companies.bookings', ['agent_id' => $agent->id]) : route('admin.companies.bookings') }}"
                            class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i> إعادة تعيين
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- إحصائيات عامة -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-building text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">
                                {{ isset($agent) ? 'الشركات المتعاملة مع ' . $agent->name : 'إجمالي الشركات' }}
                            </h6>
                            <h2 class="mb-0">{{ $companies->count() }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-success bg-opacity-10 me-3">
                            <i class="fas fa-ticket-alt text-success fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">
                                {{ isset($agent) ? 'حجوزات ' . $agent->name : 'إجمالي الحجوزات' }}
                            </h6>
                            <h2 class="mb-0">{{ $totalBookings }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-warning bg-opacity-10 me-3">
                            <i class="fas fa-money-bill-wave text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">إجمالي المستحقات (ريال)</h6>
                            <h2 class="mb-0">{{ number_format($totalAmountSAR, 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-info bg-opacity-10 me-3">
                            <i class="fas fa-money-bill-wave text-info fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-muted mb-1">إجمالي المستحقات (دينار)</h6>
                            <h2 class="mb-0">{{ number_format($totalAmountKWD, 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- قائمة الشركات وحجوزاتها -->
        @forelse($companies as $company)
            <div class="company-card">
                <div class="company-header">
                    <h3 class="company-name">
                        <i class="fas fa-building company-icon"></i>
                        {{ $company->name }}
                    </h3>
                    <div class="company-stats">
                        <span class="stat-badge">
                            <i class="fas fa-ticket-alt me-1"></i>
                            {{ $company->bookings_count }} حجز
                        </span>
                        <!-- زر إضافة دفعة -->
                        <button class="btn btn-sm btn-light ms-2" data-bs-toggle="modal"
                            data-bs-target="#companyPayModal{{ $company->id }}">
                            <i class="fas fa-hand-holding-usd me-1"></i> دفعة جديدة
                        </button>
                    </div>
                </div>
                {{-- مودال دفع للشركة --}}
                <div class="modal fade" id="companyPayModal{{ $company->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST"
                            action="{{ route('admin.companies.landtrips-payments.store') }}">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                            @if (isset($agent))
                                <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                            @endif

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-wallet me-1"></i>
                                    إضافة دفعة لشركة: {{ $company->name }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">العملة</label>
                                    <select name="currency" class="form-select" required>
                                        <option value="KWD" selected>دينار كويتي</option>
                                        <option value="SAR">ريال سعودي</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">المبلغ</label>
                                    <input type="number" name="amount" class="form-control" step="0.01"
                                        min="0.01" required>
                                    <small class="text-muted">
                                        المتبقي (دينار) الآن:
                                        {{ number_format(max($company->total_kwd - $company->paid_kwd, 0), 2) }}
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">تاريخ الدفعة</label>
                                    <input type="date" name="payment_date" class="form-control"
                                        value="{{ now()->format('Y-m-d') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i> حفظ الدفعة
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="company-content">
                    <!-- ملخص المبالغ -->
                    <div class="summary-row">
                        @if ($company->total_sar > 0)
                            <div class="summary-item">
                                <p class="summary-label">المستحق (ريال)</p>
                                <h3 class="summary-value sar">{{ number_format($company->total_sar, 2) }}</h3>
                            </div>
                            <div class="summary-item">
                                <p class="summary-label">المدفوع (ريال)</p>
                                <h3 class="summary-value sar">{{ number_format($company->paid_sar, 2) }}</h3>
                            </div>
                            <div class="summary-item">
                                <p class="summary-label">المتبقي (ريال)</p>
                                <h3 class="summary-value sar">
                                    {{ number_format($company->total_sar - $company->paid_sar, 2) }}</h3>
                            </div>
                        @endif

                        @if ($company->total_kwd > 0)
                            <div class="summary-item">
                                <p class="summary-label">المستحق (دينار)</p>
                                <h3 class="summary-value kwd">{{ number_format($company->total_kwd, 2) }}</h3>
                            </div>
                            <div class="summary-item">
                                <p class="summary-label">المدفوع (دينار)</p>
                                <h3 class="summary-value kwd">{{ number_format($company->paid_kwd, 2) }}</h3>
                            </div>
                            <div class="summary-item">
                                <p class="summary-label">المتبقي (دينار)</p>
                                <h3 class="summary-value kwd">
                                    {{ number_format($company->total_kwd - $company->paid_kwd, 2) }}</h3>
                            </div>
                        @endif
                    </div>

                    <!-- قائمة الحجوزات - نعرض أحدث 5 حجوزات فقط -->
                    <div class="bookings-list">
                        @forelse($company->recent_bookings as $booking)
                            <div class="booking-item">
                                <div class="booking-info">
                                    <h6>{{ $booking->client_name }}</h6>
                                    <p class="booking-date">{{ $booking->created_at->format('d/m/Y') }}</p>
                                    @if ($booking->landTrip)
                                        <small class="d-block text-info">
                                            <i
                                                class="fas fa-route me-1"></i>{{ $booking->landTrip->destination ?? 'رحلة برية' }}
                                        </small>
                                    @endif
                                    @if ($booking->rooms && $booking->days)
                                        <small class="d-block text-secondary">
                                            <i class="fas fa-bed me-1"></i>{{ $booking->rooms }} غرفة -
                                            {{ $booking->days }} ليلة
                                        </small>
                                    @endif
                                    @if (isset($agent))
                                        <small class="d-block text-primary">
                                            <i
                                                class="fas fa-user-tie me-1"></i>{{ $booking->agent->name ?? $agent->name }}
                                        </small>
                                    @else
                                        <small class="d-block text-primary">
                                            <i class="fas fa-user-tie me-1"></i>{{ $booking->agent->name ?? 'غير محدد' }}
                                        </small>
                                    @endif
                                </div>
                                <div class="booking-amount-info">
                                    <span class="booking-amount {{ $booking->currency === 'SAR' ? 'sar' : 'kwd' }}">
                                        {{ number_format($booking->amount_due_from_company, 0) }} {{ $booking->currency }}
                                    </span>
                                    @if ($booking->status)
                                        <small class="d-block mt-1 text-center">
                                            <span
                                                class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }} badge-sm">
                                                {{ $booking->status === 'confirmed' ? 'مؤكد' : 'في الانتظار' }}
                                            </span>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">لا توجد حجوزات لهذه الشركة</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- زر عرض المزيد من الحجوزات مع تمرير معرف الوكيل -->
                    @if ($company->bookings_count > count($company->recent_bookings))
                        <a href="{{ route('admin.company-payments.bookings', [
                            'company' => $company,
                            'agent_id' => isset($agent) ? $agent->id : null,
                        ]) }}"
                            class="expand-button">
                            <i class="fas fa-chevron-down me-1"></i>
                            عرض كافة الحجوزات ({{ $company->bookings_count }})
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="no-companies">
                <i class="fas fa-building fa-4x text-muted mb-3"></i>
                <h3>لا توجد شركات</h3>
                @if (isset($agent))
                    <p class="text-muted">لم يتم العثور على شركات لديها حجوزات مع {{ $agent->name }}</p>
                @else
                    <p class="text-muted">لم يتم العثور على أي شركات مطابقة لمعايير البحث</p>
                @endif
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تنشيط التلميحات
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
