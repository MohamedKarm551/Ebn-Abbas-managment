{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\availabilities\show.blade.php --}}
@extends('layouts.app')

@section('title', 'تفاصيل الإتاحة #' . $availability->id)

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>تفاصيل الإتاحة #{{ $availability->id }}</h1>
        <div>
            <a href="{{ route('admin.availabilities.edit', $availability->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i> تعديل
            </a>
            <a href="{{ route('admin.availabilities.index') }}" class="btn btn-secondary">
                <i class="bi bi-list-ul"></i> العودة للقائمة
            </a>
        </div>
    </div>

    <div class=" shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-info-circle-fill me-2"></i> معلومات الإتاحة الأساسية
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong>الفندق:</strong> {{ $availability->hotel->name ?? 'غير محدد' }}</p>
                    <p><strong>جهة الحجز:</strong> {{ $availability->agent->name ?? 'غير محدد' }}</p>
                    <p><strong>الموظف المسؤول:</strong> {{ $availability->employee->name ?? 'غير محدد' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>تاريخ البداية:</strong> {{ $availability->start_date->format('d/m/Y') }}</p>
                    <p><strong>تاريخ النهاية:</strong> {{ $availability->end_date->format('d/m/Y') }}</p>
                    <p><strong>الحالة:</strong>
                        @if($availability->status == 'active')
                            <span class="badge bg-success">نشطة</span>
                        @elseif($availability->status == 'inactive')
                            <span class="badge bg-secondary">غير نشطة</span>
                        @elseif($availability->status == 'expired')
                            <span class="badge bg-danger">منتهية</span>
                        @else
                            <span class="badge bg-warning">{{ $availability->status }}</span>
                        @endif
                    </p>
                </div>
                @if($availability->notes)
                <div class="col-12">
                    <p><strong>ملاحظات:</strong></p>
                    <p class="text-muted" style="white-space: pre-wrap;">{{ $availability->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class=" mt-4 shadow-sm">
        <div class="card-header">
           <i class="bi bi-door-open-fill me-2"></i> أنواع الغرف والأسعار
        </div>
        <div class="card-body p-0"> {{-- Remove padding for full-width table --}}
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0"> {{-- Remove bottom margin --}}
                    <thead class="table-light">
                        <tr>
                            <th scope="col">نوع الغرفة</th>
                            <th scope="col">سعر التكلفة</th>
                            <th scope="col">سعر البيع</th>
                            <th scope="col">عدد الغرف (Allotment)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($availability->availabilityRoomTypes as $roomTypeDetail)
                            <tr>
                                <td>{{ $roomTypeDetail->roomType->room_type_name ?? 'غير محدد' }}</td>
                                <td>{{ number_format($roomTypeDetail->cost_price, 2) }}</td>
                                <td>{{ number_format($roomTypeDetail->sale_price, 2) }}</td>
                                <td>{{ $roomTypeDetail->allotment ?? 'غير محدد' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="bi bi-exclamation-circle fs-4 me-2"></i> لا توجد تفاصيل أسعار غرف لهذه الإتاحة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection