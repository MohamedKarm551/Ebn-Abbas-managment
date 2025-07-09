@extends('layouts.app')

@section('title', 'متابعة مدفوعات وكلاء الرحلات البرية')

@push('styles')
<style>
    .agent-card {
        transition: all 0.3s ease;
        border-radius: 12px;
    }

    .agent-card:hover {
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
                    <i class="fas fa-handshake text-primary me-2"></i>
                    متابعة مدفوعات وكلاء الرحلات البرية
                </h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-1"></i>
                        تصدير Excel
                    </button>
                </div>
            </div>
            <p class="text-muted mt-2">تتبع المبالغ المستحقة لوكلاء الحجز من الرحلات البرية فقط</p>
        </div>
    </div>

    <!-- الإحصائيات العامة -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-handshake fa-2x mb-2 text-primary"></i>
                    <h4 class="mb-1">{{ $totalStats['agents_count'] }}</h4>
                    <small>وكلاء نشطين</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-bus fa-2x mb-2"></i>
                    <h5 class="mb-1">{{ number_format($totalStats['total_bookings']) }}</h5>
                    <small>حجوزات رحلات برية</small>
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
                    <label class="form-label">البحث عن وكيل</label>
                    <input type="text" name="search" class="form-control search-box" placeholder="اسم جهة الحجز..."
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
                        <a href="{{ route('admin.land-trips-agent-payments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- قائمة الوكلاء -->
    <div class="row">
        @forelse($agents as $index => $agent)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card agent-card shadow-sm border-0 h-100 position-relative">
                    <!-- رقم الترتيب -->
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-primary fs-6" style="border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                            {{ $index + 1 }}
                        </span>
                    </div>
                    
                    <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">{{ $agent['name'] }}</h6>
                        <span class="badge bg-info text-white px-3 py-2">
                            <i class="fas fa-bus me-1"></i>
                            {{ $agent['bookings_count'] }} حجز
                        </span>
                    </div>

                    <div class="card-body">
                        <!-- معلومات الاتصال -->
                        <div class="mb-3">
                            @if ($agent['email'])
                                <small class="text-muted d-block">
                                    <i class="fas fa-envelope me-1"></i>{{ $agent['email'] }}
                                </small>
                            @endif
                            @if ($agent['phone'])
                                <small class="text-muted d-block">
                                    <i class="fas fa-phone me-1"></i>{{ $agent['phone'] }}
                                </small>
                            @endif
                        </div>

                        <!-- الإحصائيات المالية -->
                        <div class="row text-center mb-3">
                            @foreach(['SAR' => 'success', 'KWD' => 'primary'] as $currency => $colorClass)
                                @if($agent['totals_by_currency'][$currency]['due'] > 0)
                                    <div class="col-12 mb-2">
                                        <div class="bg-light rounded p-2">
                                            <div class="currency-badge bg-{{ $colorClass }} text-white mb-1">
                                                {{ $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                                            </div>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المستحق</small>
                                                    <strong>{{ number_format($agent['totals_by_currency'][$currency]['due'], 0) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المدفوع</small>
                                                    <strong class="text-success">{{ number_format($agent['totals_by_currency'][$currency]['paid'], 0) }}</strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">المتبقي</small>
                                                    <strong class="text-danger">{{ number_format($agent['totals_by_currency'][$currency]['remaining'], 0) }}</strong>
                                                </div>
                                            </div>
                                            
                                            <!-- شريط تقدم -->
                                            @php
                                                $percentage = $agent['totals_by_currency'][$currency]['due'] > 0 
                                                    ? ($agent['totals_by_currency'][$currency]['paid'] / $agent['totals_by_currency'][$currency]['due']) * 100 
                                                    : 0;
                                            @endphp
                                            <div class="mt-2">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar bg-{{ $colorClass }}" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ number_format($percentage, 1) }}% مدفوع</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- آخر دفعة -->
                        @if($agent['last_payment'])
                            <div class="border-top pt-2">
                                <small class="text-muted">آخر دفعة:</small>
                                <small class="d-block">
                                    {{ number_format($agent['last_payment']->amount, 2) }}
                                    {{ $agent['last_payment']->currency }}
                                    <span class="text-muted">- {{ $agent['last_payment']->payment_date->format('d/m/Y') }}</span>
                                </small>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.land-trips-agent-payments.show', $agent['id']) }}" 
                               class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i> التفاصيل
                            </a>
                            <a href="{{ route('admin.land-trips-agent-payments.create', $agent['id']) }}" 
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
                        <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                        <h5>لا توجد جهات حجز نشطة</h5>
                        <p class="text-muted">لم يتم العثور على وكلاء لديهم حجوزات رحلات برية نشطة</p>
                        <a href="{{ route('admin.land-trips.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>إضافة رحلة برية جديدة
                        </a>
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
        const data = @json($agents);

        const exportData = data.map((agent, index) => ({
            '#': index + 1,
            'اسم الوكيل': agent.name,
            'عدد الحجوزات': agent.bookings_count,
            'المستحق ريال': agent.totals_by_currency.SAR.due,
            'المدفوع ريال': agent.totals_by_currency.SAR.paid,
            'المتبقي ريال': agent.totals_by_currency.SAR.remaining,
            'المستحق دينار': agent.totals_by_currency.KWD.due,
            'المدفوع دينار': agent.totals_by_currency.KWD.paid,
            'المتبقي دينار': agent.totals_by_currency.KWD.remaining,
        }));

        const ws = XLSX.utils.json_to_sheet(exportData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'مدفوعات وكلاء الرحلات البرية');

        const fileName = `مدفوعات-وكلاء-الرحلات-البرية-${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);
    }
</script>
@endpush