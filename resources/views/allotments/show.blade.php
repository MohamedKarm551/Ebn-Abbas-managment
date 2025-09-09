@extends('layouts.app')

@section('title', 'تفاصيل الألوتمنت')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">تفاصيل الألوتمنت</h6>
                    <div>
                        <a href="{{ route('allotments.edit', $allotment->id) }}" class="btn btn-primary btn-sm mx-1">
                            <i class="fas fa-edit ml-1"></i> تعديل
                        </a>
                        <form action="{{ route('allotments.destroy', $allotment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الألوتمنت؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm mx-1">
                                <i class="fas fa-trash ml-1"></i> حذف
                            </button>
                        </form>
                        <a href="{{ route('allotments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle ml-1"></i> معلومات الألوتمنت</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-striped table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 35%">الفندق</th>
                                                <td>{{ $allotment->hotel->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>تاريخ البداية</th>
                                                <td>{{ $allotment->start_date->format('Y-m-d') }}</td>
                                            </tr>
                                            <tr>
                                                <th>تاريخ النهاية</th>
                                                <td>{{ $allotment->end_date->format('Y-m-d') }}</td>
                                            </tr>
                                            <tr>
                                                <th>المدة</th>
                                                <td>{{ $allotment->start_date->diffInDays($allotment->end_date) }} يوم</td>
                                            </tr>
                                            <tr>
                                                <th>عدد الغرف</th>
                                                <td>{{ $allotment->rooms_count }}</td>
                                            </tr>
                                            <tr>
                                                <th>سعر الغرفة</th>
                                                <td>{{ $allotment->rate_per_room }} {{ $allotment->currency }}</td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
                                                <td>
                                                    <span class="badge {{ $allotment->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $allotment->status === 'active' ? 'نشط' : 'غير نشط' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie ml-1"></i> إحصائيات الألوتمنت</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-sm-4 mb-3">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body py-3">
                                                    <h3 class="mb-0">{{ $allotment->rooms_count }}</h3>
                                                    <div class="small">إجمالي الغرف</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-3">
                                            <div class="card {{ $allotment->remaining_rooms > 0 ? 'bg-success' : 'bg-danger' }} text-white">
                                                <div class="card-body py-3">
                                                    <h3 class="mb-0">{{ $allotment->remaining_rooms }}</h3>
                                                    <div class="small">الغرف المتاحة</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-3">
                                            <div class="card bg-info text-white">
                                                <div class="card-body py-3">
                                                    <h3 class="mb-0">{{ $allotment->rooms_count - $allotment->remaining_rooms }}</h3>
                                                    <div class="small">الغرف المباعة</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- الإحصائيات الإضافية والرسوم البيانية -->
                                    <div class="mt-3">
                                        <div class="progress mb-2" style="height: 25px;">
                                            @php 
                                                $soldPercentage = ($allotment->rooms_count > 0) ? 
                                                    (($allotment->rooms_count - $allotment->remaining_rooms) / $allotment->rooms_count) * 100 : 0;
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: {{ $soldPercentage }}%;" 
                                                aria-valuenow="{{ $soldPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($soldPercentage, 1) }}% تم البيع
                                            </div>
                                        </div>
                                        <div class="small text-muted text-center">
                                            نسبة الإشغال: {{ round($soldPercentage, 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ملاحظات الألوتمنت -->
                            @if($allotment->notes)
                                <div class="card bg-light mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-sticky-note ml-1"></i> الملاحظات</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $allotment->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- جدول المبيعات المرتبطة بالألوتمنت -->
                    <div class="card">
                        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart ml-1"></i> عمليات البيع المرتبطة</h5>
                            <a href="{{ route('allotment-sales.create', ['allotment_id' => $allotment->id, 'hotel_id' => $allotment->hotel_id]) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus-circle ml-1"></i> إضافة بيع جديد
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                @if($allotment->sales && $allotment->sales->count() > 0)
                                    <table class="table table-striped table-bordered mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>الشركة</th>
                                                <th>تاريخ الدخول</th>
                                                <th>تاريخ الخروج</th>
                                                <th>عدد الغرف</th>
                                                <th>سعر الغرفة</th>
                                                <th>الإجمالي</th>
                                                <th>تاريخ البيع</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allotment->sales as $index => $sale)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $sale->company_name }}</td>
                                                    <td>{{ $sale->check_in->format('Y-m-d') }}</td>
                                                    <td>{{ $sale->check_out->format('Y-m-d') }}</td>
                                                    <td>{{ $sale->rooms_sold }}</td>
                                                    <td>{{ $sale->sale_price }} {{ $sale->currency }}</td>
                                                    <td>
                                                        @php
                                                            $days = $sale->check_in->diffInDays($sale->check_out);
                                                            $total = $days * $sale->rooms_sold * $sale->sale_price;
                                                        @endphp
                                                        {{ number_format($total, 2) }} {{ $sale->currency }}
                                                    </td>
                                                    <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('allotment-sales.edit', $sale->id) }}" class="btn btn-sm btn-primary" title="تعديل">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('allotment-sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف عملية البيع؟');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th colspan="4">الإجمالي</th>
                                                <th>{{ $allotment->sales->sum('rooms_sold') }}</th>
                                                <th>-</th>
                                                <th colspan="3">-</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-muted mb-0">لا توجد عمليات بيع مرتبطة بهذا الألوتمنت</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('allotments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
                        </a>
                        <a href="{{ route('allotment-sales.create', ['allotment_id' => $allotment->id, 'hotel_id' => $allotment->hotel_id]) }}" class="btn btn-success">
                            <i class="fas fa-plus-circle ml-1"></i> إضافة بيع جديد
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header h5 {
        font-size: 1.1rem;
        font-weight: 600;
    }
    .progress {
        border-radius: 0.25rem;
    }
    .progress-bar {
        font-weight: 600;
        font-size: 1rem;
    }
</style>
@endpush