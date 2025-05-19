@extends('layouts.app')

@section('title', 'تفاصيل الرحلة البرية')

@push('styles')
    <style>
        /* تنسيقات عامة */
        .trip-header {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .trip-info-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .trip-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 15px 20px;
            font-weight: 600;
        }

        .info-item {
            padding: 0.7rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.8rem;
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }

        .info-item:hover {
            background-color: #f0f2f5;
        }

        .info-label {
            color: #495057;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            color: #212529;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .badge-currency {
            padding: 0.3em 0.6em;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: 500;
        }

        .badge-kwd {
            background-color: #17a2b8;
            color: white;
        }

        .badge-sar {
            background-color: #6c757d;
            color: white;
        }

        .price-table th {
            font-weight: 600;
            background-color: #f8f9fa;
            color: #495057;
        }

        .price-table td {
            vertical-align: middle;
        }

        .price-cell {
            display: flex;
            align-items: center;
        }

        .price-value {
            font-weight: 600;
            margin-right: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.3s;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 6px;
        }
    </style>
@endpush

@php
    function getCurrencySymbol($currency)
    {
        return $currency == 'KWD' ? 'د.ك' : 'ر.س';
    }

    function getCurrencyClass($currency)
    {
        return $currency == 'KWD' ? 'badge-kwd' : 'badge-sar';
    }
@endphp

@section('content')
    <div class="container mt-4">
        <!-- بطاقة الرأس مع معلومات أساسية -->
        <div class="card trip-header mb-4">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-9">
                        <div class="p-4">
                            <div class="d-flex align-items-center mb-3">
                                <h2 class="mb-0 fw-bold">{{ $landTrip->tripType->name ?? 'رحلة برية' }}</h2>
                                <div class="ms-auto">
                                    @if ($landTrip->status == 'active')
                                        <span class="badge status-badge bg-success">نشطة</span>
                                    @elseif($landTrip->status == 'inactive')
                                        <span class="badge status-badge bg-warning">غير نشطة</span>
                                    @elseif($landTrip->status == 'expired')
                                        <span class="badge status-badge bg-secondary">منتهية</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-hotel me-2 text-primary"></i>
                                    <span class="fw-medium">{{ $landTrip->hotel->name ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap">
                                <div class="me-4 mb-2">
                                    <div class="text-muted small">تاريخ المغادرة</div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plane-departure me-2 text-success"></i>
                                        <span>{{ $landTrip->departure_date->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                                <div class="me-4 mb-2">
                                    <div class="text-muted small">تاريخ العودة</div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plane-arrival me-2 text-danger"></i>
                                        <span>{{ $landTrip->return_date->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">مدة الرحلة</div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-day me-2 text-info"></i>
                                        <span>{{ $landTrip->days_count }} يوم</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 bg-light">
                        <div class="p-4 h-100 d-flex flex-column justify-content-center">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.land-trips.edit', $landTrip->id) }}"
                                    class="btn btn-warning btn-custom">
                                    <i class="fas fa-edit me-1"></i> تعديل الرحلة
                                </a>
                                <!-- زر إضافة حجز جديد -->
                                <a href="{{ route('admin.land-trips.create-booking', $landTrip->id) }}"
                                    class="btn btn-success btn-custom">
                                    <i class="fas fa-plus-circle me-1"></i> إنشاء حجز
                                </a>
                                <a href="{{ route('admin.land-trips.bookings', $landTrip->id) }}"
                                    class="btn btn-primary btn-custom">
                                    <i class="fas fa-calendar-check me-1"></i> عرض الحجوزات
                                </a>
                                <a href="{{ route('admin.land-trips.index') }}" class="btn btn-secondary btn-custom">
                                    <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- القسم الأيمن - تفاصيل الرحلة -->
            <div class="col-lg-4 mb-4">
                <div class="card trip-info-card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-info-circle me-2"></i> معلومات الرحلة
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <div class="info-label">جهة الحجز</div>
                            <div class="info-value">
                                <i class="fas fa-building me-1 text-primary"></i>
                                {{ $landTrip->agent->name ?? 'غير معروف' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">الموظف المسؤول</div>
                            <div class="info-value">
                                <i class="fas fa-user-tie me-1 text-primary"></i>
                                {{ $landTrip->employee->name ?? 'غير معروف' }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">رقم الرحلة</div>
                            <div class="info-value">
                                <i class="fas fa-hashtag me-1 text-primary"></i>
                                #{{ $landTrip->id }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">
                                <i class="fas fa-calendar-plus me-1 text-primary"></i>
                                {{ $landTrip->created_at->format('Y-m-d') }}
                            </div>
                        </div>

                        @if ($landTrip->notes)
                            <div class="info-item">
                                <div class="info-label">ملاحظات</div>
                                <div class="info-value">
                                    <i class="fas fa-comment me-1 text-primary"></i>
                                    {{ $landTrip->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- القسم الأيسر - أسعار الغرف -->
            <div class="col-lg-8 mb-4">
                <div class="card trip-info-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-bed me-2"></i> أسعار الغرف
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table price-table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>نوع الغرفة</th>
                                        <th>سعر التكلفة</th>
                                        <th>سعر البيع</th>
                                        <th>المتاح</th>
                                        <th>المحجوز</th>
                                        <th>إجمالي المبيعات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($landTrip->roomPrices as $index => $roomPrice)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-medium">
                                                    {{ $roomPrice->roomType->room_type_name ?? 'غير معروف' }}</div>
                                            </td>
                                            <td>
                                                <div class="price-cell">
                                                    <span
                                                        class="price-value">{{ number_format($roomPrice->cost_price, 2) }}</span>
                                                    <span
                                                        class="badge badge-currency {{ getCurrencyClass($roomPrice->currency) }}">
                                                        {{ getCurrencySymbol($roomPrice->currency) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="price-cell">
                                                    <span
                                                        class="price-value">{{ number_format($roomPrice->sale_price, 2) }}</span>
                                                    <span
                                                        class="badge badge-currency {{ getCurrencyClass($roomPrice->currency) }}">
                                                        {{ getCurrencySymbol($roomPrice->currency) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if (isset($bookingSummary[$roomPrice->id]['available']))
                                                    @if ($bookingSummary[$roomPrice->id]['available'] === null)
                                                        <span class="badge bg-info">غير محدود</span>
                                                    @else
                                                        <span
                                                            class="fw-medium">{{ $bookingSummary[$roomPrice->id]['available'] }}</span>
                                                    @endif
                                                @else
                                                    @if ($roomPrice->allotment)
                                                        <span class="fw-medium">{{ $roomPrice->allotment }}</span>
                                                    @else
                                                        <span class="badge bg-info">غير محدود</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-medium">{{ $bookingSummary[$roomPrice->id]['booked'] ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <div class="price-cell">
                                                    <span
                                                        class="price-value">{{ number_format($bookingSummary[$roomPrice->id]['total_amount'] ?? 0, 2) }}</span>
                                                    <span
                                                        class="badge badge-currency {{ getCurrencyClass($roomPrice->currency) }}">
                                                        {{ getCurrencySymbol($roomPrice->currency) }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    لا توجد أسعار غرف لعرضها
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mb-4">
                @if (auth()->user()->role === 'Admin' || auth()->user()->role === 'employee')
                    @php
                        $fieldNames = [
                            'trip_type_id' => 'نوع الرحلة',
                            'agent_id' => 'جهة الحجز',
                            'hotel_id' => 'الفندق',
                            'employee_id' => 'الموظف المسؤول',
                            'departure_date' => 'تاريخ المغادرة',
                            'return_date' => 'تاريخ العودة',
                            'days_count' => 'مدة الرحلة',
                            'status' => 'الحالة',
                            'notes' => 'الملاحظات',
                            'room_price:currency' => 'العملة',
                            'room_price:allotment' => 'عدد الغرف المتاحة',
                            'room_price:cost_price' => 'سعر التكلفة',
                            'room_price:sale_price' => 'سعر البيع',
                            'room_price:room_type_id' => 'نوع الغرفة',
                            'room_price_created:cost_price' => 'إنشاء سعر غرفة',
                            'room_price_updated:room_type_id' => 'تعديل سعر غرفة',
                            'room_price_deleted:room_type_id' => 'حذف سعر غرفة',
                            'room_price_deleted:cost_price' => 'حذف سعر التكلفة',
                            'room_price_deleted:sale_price' => 'حذف سعر البيع',
                            'room_price_deleted:currency' => 'حذف العملة',
                            'room_price_deleted:allotment' => 'حذف عدد الغرف',
                            'room_price_type_change_old_deleted:room_type_id' => 'تغيير نوع الغرفة (القديم)',
                            'room_price_type_changed_to_new:room_type_id' => 'تغيير نوع الغرفة (الجديد)',
                            'room_price_created:room_type_id' => 'نوع الغرفة (جديد)',
                            'room_price_created:cost_price' => 'سعر التكلفة (جديد)',
                            'room_price_updated:room_type_id' => 'تعديل نوع الغرفة',
                        ];
                        // دالة للحصول على قيمة الحقل مع مراعاة بنية الحقل (field:type:id)
                        // دالة لتحويل اسم الحقل للعرض العربي
                        function getFriendlyFieldName($field, $fieldNames)
                        {
                            // نتعامل مع الحالات الخاصة أولاً
                            if (preg_match('/room_price_created:(\d+):([a-z_]+)/', $field, $matches)) {
                                // تنسيق: room_price_created:28:room_type_id
                                $recordId = $matches[1];
                                $fieldType = $matches[2];

                                // نحدد نوع الحقل المناسب
                                $baseName = "room_price_created:{$fieldType}";
                                if (isset($fieldNames[$baseName])) {
                                    return $fieldNames[$baseName];
                                } elseif ($fieldType == 'room_type_id') {
                                    return 'نوع الغرفة';
                                } elseif ($fieldType == 'cost_price') {
                                    return 'سعر التكلفة';
                                } elseif ($fieldType == 'sale_price') {
                                    return 'سعر البيع';
                                } elseif ($fieldType == 'currency') {
                                    return 'العملة';
                                } elseif ($fieldType == 'allotment') {
                                    return 'عدد الغرف المتاحة';
                                }
                            }

                            // تنسيق: room_price_type_change_old_deleted:cost_price:23
                            elseif (
                                preg_match('/room_price_type_change_old_deleted:([a-z_]+):(\d+)/', $field, $matches)
                            ) {
                                $fieldType = $matches[1];
                                $recordId = $matches[2];

                                // نحدد اسم الحقل المناسب
                                $baseName = "room_price_type_change_old_deleted:{$fieldType}";
                                if (isset($fieldNames[$baseName])) {
                                    return $fieldNames[$baseName];
                                } elseif ($fieldType == 'cost_price') {
                                    return 'حذف سعر التكلفة (القديم)';
                                } elseif ($fieldType == 'sale_price') {
                                    return 'حذف سعر البيع (القديم)';
                                } elseif ($fieldType == 'currency') {
                                    return 'حذف العملة (القديم)';
                                } elseif ($fieldType == 'allotment') {
                                    return 'حذف عدد الغرف (القديم)';
                                }
                            }

                            // تنسيق: room_price_type_changed_to_new:32_to_33:room_type_id
                            elseif (
                                preg_match('/room_price_type_changed_to_new:([^:]+):([a-z_]+)/', $field, $matches)
                            ) {
                                $transaction = $matches[1];
                                $fieldType = $matches[2];

                                $baseName = "room_price_type_changed_to_new:{$fieldType}";
                                if (isset($fieldNames[$baseName])) {
                                    return $fieldNames[$baseName];
                                } elseif ($fieldType == 'room_type_id') {
                                    return 'تغيير نوع الغرفة (الجديد)';
                                } elseif ($fieldType == 'cost_price') {
                                    return 'سعر التكلفة (الجديد)';
                                } elseif ($fieldType == 'sale_price') {
                                    return 'سعر البيع (الجديد)';
                                } elseif ($fieldType == 'currency') {
                                    return 'العملة (الجديد)';
                                } elseif ($fieldType == 'allotment') {
                                    return 'عدد الغرف (الجديد)';
                                }
                            }

                            // التعامل مع تحديث الأسعار
                            elseif (preg_match('/room_price_updated:([a-z_]+):(\d+)/', $field, $matches)) {
                                $fieldType = $matches[1];
                                $recordId = $matches[2];

                                if ($fieldType == 'room_type_id') {
                                    return 'تعديل نوع الغرفة';
                                } elseif ($fieldType == 'cost_price') {
                                    return 'تعديل سعر التكلفة';
                                } elseif ($fieldType == 'sale_price') {
                                    return 'تعديل سعر البيع';
                                } elseif ($fieldType == 'currency') {
                                    return 'تعديل العملة';
                                } elseif ($fieldType == 'allotment') {
                                    return 'تعديل عدد الغرف';
                                }
                            }
                            // التعامل مع حذف الأسعار
                            elseif (preg_match('/room_price_deleted:([a-z_]+):(\d+)/', $field, $matches)) {
                                $fieldType = $matches[1];
                                $recordId = $matches[2];

                                if ($fieldType == 'room_type_id') {
                                    return 'حذف نوع الغرفة';
                                } elseif ($fieldType == 'cost_price') {
                                    return 'حذف سعر التكلفة';
                                } elseif ($fieldType == 'sale_price') {
                                    return 'حذف سعر البيع';
                                } elseif ($fieldType == 'currency') {
                                    return 'حذف العملة';
                                } elseif ($fieldType == 'allotment') {
                                    return 'حذف عدد الغرف';
                                }
                            }

                            // نبحث في مصفوفة الحقول المعرفة
                            foreach ($fieldNames as $key => $name) {
                                // تجاهل المعرفات وتطابق النمط الأساسي فقط
                                $baseFieldPattern = explode(':', $key)[0];
                                if (strpos($field, $baseFieldPattern) === 0) {
                                    return $name;
                                }
                            }

                            // إذا لم نجد أي مطابقة، نعيد الاسم كما هو
                            return $field;
                        }

                    @endphp
                    @php

                        // دالة للحصول على قيمة الحقل بتنسيق مناسب
                        function getFormattedValue($field, $value)
                        {
                            // معالجة التواريخ (المغادرة والعودة)
                            if (strpos($field, 'departure_date') !== false || strpos($field, 'return_date') !== false) {
                                if ($value && preg_match('/^\d{4}(-\d{2}){2}/', $value)) {
                                    try {
                                        return \Carbon\Carbon::parse($value)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        return $value;
                                    }
                                }
                                return $value;
                            }

                            // معالجة معرف نوع الرحلة
                            if ($field === 'trip_type_id' || strpos($field, ':trip_type_id') !== false) {
                                return \App\Models\TripType::find($value)?->name ?? $value;
                            }

                            // معالجة معرف الوكيل
                            if ($field === 'agent_id' || strpos($field, ':agent_id') !== false) {
                                return \App\Models\Agent::find($value)?->name ?? $value;
                            }

                            // معالجة معرف الفندق
                            if ($field === 'hotel_id' || strpos($field, ':hotel_id') !== false) {
                                return \App\Models\Hotel::find($value)?->name ?? $value;
                            }

                            // معالجة معرف الموظف
                            if ($field === 'employee_id' || strpos($field, ':employee_id') !== false) {
                                return \App\Models\Employee::find($value)?->name ?? $value;
                            }

                            // معالجة معرف نوع الغرفة
                            if (strpos($field, 'room_type_id') !== false) {
                                return \App\Models\RoomType::find($value)?->room_type_name ?? $value;
                            }

                            // معالجة الحالة
                            if ($field === 'status') {
                                switch ($value) {
                                    case 'active':
                                        return 'نشطة';
                                    case 'inactive':
                                        return 'غير نشطة';
                                    case 'expired':
                                        return 'منتهية';
                                    default:
                                        return $value;
                                }
                            }

                            // القيم الأخرى تبقى كما هي
                            return $value;
                        }
                    @endphp
                     
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-history me-2"></i> سجل تعديلات الرحلة
                        </div>
                        <div class="card-body">
                            @if ($edits->isEmpty())
                                <div class="text-muted">لا يوجد تعديلات مسجلة على هذه الرحلة.</div>
                            @else
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>التاريخ</th>
                                            <th>المستخدم</th>
                                            <th>الحقل</th>
                                            <th>القيمة القديمة</th>
                                            <th>القيمة الجديدة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($edits as $edit)
                                            <tr>
                                                <td>{{ $edit->created_at->format('Y-m-d H:i:s') }}</td>
                                                <td>{{ $edit->user->name ?? 'غير معروف' }}</td>
                                                <td>{{ getFriendlyFieldName($edit->field, $fieldNames) }}</td>
                                                <td>{{ getFormattedValue($edit->field, $edit->old_value) }}</td>
                                                <td>{{ getFormattedValue($edit->field, $edit->new_value) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
