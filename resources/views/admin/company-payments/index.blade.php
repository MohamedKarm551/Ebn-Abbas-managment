{{-- filepath: resources/views/admin/company-payments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'متابعة مدفوعات الشركات')

@push('styles')
    <style>
        .company-card {
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .company-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .currency-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-building text-primary me-2"></i>
                        متابعة مدفوعات الشركات
                    </h1>
                    <div class="d-flex gap-2">
                        <!-- ✅ إضافة زر التقارير المالية -->
                        <a href="{{ route('admin.company-payments.reports') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-pie me-1"></i>
                            التقارير المالية
                        </a>
                        <button class="btn btn-outline-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-1"></i>
                            تصدير Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

      <!-- الإحصائيات العامة -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-building fa-2x mb-2"></i>
                <h4 class="mb-1">{{ $totalStats['companies_count'] }}</h4>
                <small>الشركات النشطة</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calendar-check fa-2x mb-2"></i>
                <h5 class="mb-1">{{ number_format($totalStats['total_bookings']) }}</h5>
                <small>إجمالي الحجوزات</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-coins fa-2x mb-2"></i>
                <h6 class="mb-1">{{ number_format($totalStats['total_due_sar'], 0) }}</h6>
                <small>المستحق (ريال)</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-primary text-white border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-coins fa-2x mb-2"></i>
                <h6 class="mb-1">{{ number_format($totalStats['total_due_kwd'], 0) }}</h6>
                <small>المستحق (دينار)</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                <h6 class="mb-1">{{ number_format($totalStats['total_paid_sar'], 0) }}</h6>
                <small>المدفوع (ريال)</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                <h6 class="mb-1">{{ number_format($totalStats['total_paid_kwd'], 0) }}</h6>
                <small>المدفوع (دينار)</small>
            </div>
        </div>
    </div>
</div>

        <!-- البحث والفلترة -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">البحث عن شركة</label>
                        <input type="text" name="search" class="form-control search-box" placeholder="اسم الشركة..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">العملة</label>
                        <select name="currency" class="form-select">
                            <option value="">كل العملات</option>
                            <option value="SAR" {{ request('currency') == 'SAR' ? 'selected' : '' }}>ريال سعودي</option>
                            <option value="KWD" {{ request('currency') == 'KWD' ? 'selected' : '' }}>دينار كويتي</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> بحث
                            </button>
                            <a href="{{ route('admin.company-payments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- قائمة الشركات -->
        <div class="row">
            @forelse($companies as $company)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card company-card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">{{ $company['name'] }}</h6>
                            <span class="badge bg-light text-dark">{{ $company['bookings_count'] }} حجز</span>
                        </div>

                        <div class="card-body">
                            <!-- معلومات الاتصال -->
                            <div class="mb-3">
                                @if ($company['email'])
                                    <small class="text-muted d-block">
                                        <i class="fas fa-envelope me-1"></i>{{ $company['email'] }}
                                    </small>
                                @endif
                                @if ($company['phone'])
                                    <small class="text-muted d-block">
                                        <i class="fas fa-phone me-1"></i>{{ $company['phone'] }}
                                    </small>
                                @endif
                            </div>

                            <!-- الإحصائيات المالية -->
                            <div class="row text-center mb-3">
                                <!-- الريال السعودي -->
                                @if ($company['totals_by_currency']['SAR']['due'] > 0)
                                    <div class="col-12 mb-2">
                                        <div class="bg-light rounded p-2">
                                            <div class="currency-badge bg-success text-white mb-1">ريال سعودي</div>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المستحق</small>
                                                    <strong>{{ number_format($company['totals_by_currency']['SAR']['due'], 2) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المدفوع</small>
                                                    <strong
                                                        class="text-success">{{ number_format($company['totals_by_currency']['SAR']['paid'], 2) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المتبقي</small>
                                                    <strong
                                                        class="text-danger">{{ number_format($company['totals_by_currency']['SAR']['remaining'], 2) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- الدينار الكويتي -->
                                @if ($company['totals_by_currency']['KWD']['due'] > 0)
                                    <div class="col-12 mb-2">
                                        <div class="bg-light rounded p-2">
                                            <div class="currency-badge bg-primary text-white mb-1">دينار كويتي</div>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المستحق</small>
                                                    <strong>{{ number_format($company['totals_by_currency']['KWD']['due'], 2) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المدفوع</small>
                                                    <strong
                                                        class="text-success">{{ number_format($company['totals_by_currency']['KWD']['paid'], 2) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المتبقي</small>
                                                    <strong
                                                        class="text-danger">{{ number_format($company['totals_by_currency']['KWD']['remaining'], 2) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- آخر دفعة -->
                            @if ($company['last_payment'])
                                <div class="border-top pt-2">
                                    <small class="text-muted">آخر دفعة:</small>
                                    <small class="d-block">
                                        {{ number_format($company['last_payment']->amount, 2) }}
                                        {{ $company['last_payment']->currency }}
                                        <span class="text-muted">-
                                            {{ $company['last_payment']->payment_date->format('d/m/Y') }}</span>
                                    </small>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.company-payments.show', $company['id']) }}"
                                    class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-eye me-1"></i> التفاصيل
                                </a>
                                <a href="{{ route('admin.company-payments.create', $company['id']) }}"
                                    class="btn btn-success btn-sm flex-fill">
                                    <i class="fas fa-plus me-1"></i> دفعة جديدة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5>لا توجد شركات</h5>
                            <p class="text-muted">لم يتم العثور على شركات مطابقة لمعايير البحث</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script>
        function exportToExcel() {
            const data = @json($companies);

            const exportData = data.map((company, index) => ({
                '#': index + 1,
                'اسم الشركة': company.name,
                'عدد الحجوزات': company.bookings_count,
                'المستحق ريال': company.totals_by_currency.SAR.due,
                'المدفوع ريال': company.totals_by_currency.SAR.paid,
                'المتبقي ريال': company.totals_by_currency.SAR.remaining,
                'المستحق دينار': company.totals_by_currency.KWD.due,
                'المدفوع دينار': company.totals_by_currency.KWD.paid,
                'المتبقي دينار': company.totals_by_currency.KWD.remaining,
            }));

            const ws = XLSX.utils.json_to_sheet(exportData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'مدفوعات الشركات');

            const fileName = `مدفوعات-الشركات-${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }
    </script>
@endpush
