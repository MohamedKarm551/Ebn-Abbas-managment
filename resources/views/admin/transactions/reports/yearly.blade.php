
@extends('layouts.app')

@section('title', 'التقرير السنوي للمعاملات المالية')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.report-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
.report-header {
    background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
    color: white;
    border-radius: 15px 15px 0 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.transactions.index') }}">المعاملات المالية</a>
            </li>
            <li class="breadcrumb-item active">التقرير السنوي</li>
        </ol>
    </nav>

    <div class="card report-card">
        <div class="card-header report-header py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-chart-bar me-2"></i>
                        التقرير السنوي للمعاملات المالية
                    </h3>
                    <p class="mb-0 opacity-75">تقرير مفصل للمعاملات المالية السنوية</p>
                </div>
                <div>
                    <button class="btn btn-light" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> طباعة
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Date Filter -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('admin.transactions.reports.yearly') }}">
                        <div class="input-group">
                            <select name="year" class="form-control">
                                @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                    <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> عرض التقرير
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Coming Soon Message -->
            <div class="text-center py-5">
                <i class="fas fa-chart-bar fa-4x text-primary mb-3"></i>
                <h4>التقرير السنوي قيد التطوير</h4>
                <p class="text-muted">سيتم إضافة تقارير مفصلة قريباً تتضمن:</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-1"></i> إجمالي المعاملات السنوية</li>
                    <li><i class="fas fa-check text-success me-1"></i> تحليل الأداء المالي</li>
                    <li><i class="fas fa-check text-success me-1"></i> مخططات النمو والانخفاض</li>
                    <li><i class="fas fa-check text-success me-1"></i> مقارنات سنوية</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection