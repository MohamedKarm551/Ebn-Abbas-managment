
@extends('layouts.app')

@section('title', 'تقرير أرباح الموظفين')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">تقرير أرباح الموظفين</h6>
        </div>
        
        <div class="card-body">
            <!-- فلتر التقرير -->
            <div class="mb-4">
                <form action="{{ route('admin.operation-reports.employee-profits') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">تاريخ البداية</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">تاريخ النهاية</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">الموظف</label>
                        <select class="form-control" id="employee_id" name="employee_id">
                            <option value="">جميع الموظفين</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
                    </div>
                </form>
            </div>
            
            <!-- جدول البيانات -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>الموظف</th>
                            <th>عدد التقارير</th>
                            <th>إجمالي الأرباح (حسب العملة)</th>
                            <th>أرباح الموظف (جنيه مصري)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profitsByEmployee as $employeeId => $data)
                            <tr>
                                <td>
                                    {{ $data['employee']->name ?? 'غير معروف' }}
                                </td>
                                <td>{{ $data['reports_count'] }}</td>
                                <td>
                                    @foreach($data['profits'] as $currency => $amount)
                                        {{ number_format($amount, 2) }} {{ $currency }}<br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($data['employee_profits'] as $currency => $amount)
                                        <span class="text-success fw-bold">
                                            {{ number_format($amount, 2) }} {{ $currency }}
                                        </span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">لا توجد بيانات متاحة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- تفاصيل التقارير لكل موظف -->
    @foreach($profitsByEmployee as $employeeId => $data)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    تفاصيل تقارير {{ $data['employee']->name ?? 'غير معروف' }}
                </h6>
            </div>
            <div class="card-body">
                @php
                    // استعلام لجلب تفاصيل تقارير هذا الموظف
                    $employeeReports = \App\Models\BookingOperationReport::where('employee_id', $employeeId)
                        ->whereBetween('report_date', [$startDate, $endDate])
                        ->latest('report_date')
                        ->get();
                @endphp
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>التاريخ</th>
                                <th>العميل</th>
                                <th>الشركة</th>
                                <th>إجمالي الربح</th>
                                <th>ربح الموظف</th>
                                <th>عرض</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeeReports as $report)
                                <tr>
                                    <td>{{ $report->report_date->format('Y-m-d') }}</td>
                                    <td>{{ $report->client_name }}</td>
                                    <td>{{ $report->company_name ?? '-' }}</td>
                                    <td>{{ number_format($report->grand_total_profit, 2) }} {{ $report->currency }}</td>
                                    <td class="text-success">
                                        {{ number_format($report->employee_profit, 2) }} {{ $report->employee_profit_currency }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.operation-reports.show', $report) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد تقارير لهذا الموظف</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('styles')
<style>
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .text-primary {
        color: #4e73df !important;
    }
    
    .text-success {
        color: #1cc88a !important;
    }
</style>
@endpush