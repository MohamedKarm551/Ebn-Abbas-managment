@extends('layouts.app')
{{-- يبقا الصفحة مربوطة ب app.blade.php --}}
@section('content')
<div class="container">
    <h1>كل الحجوزات</h1>

    <!-- روابط إلى الصفحات الإدارية -->
    <div class="mb-4">
        <a href="{{ route('admin.employees') }}" class="btn btn-secondary">إدارة الموظفين</a>
        <a href="{{ route('admin.companies') }}" class="btn btn-secondary">إدارة الشركات</a>
        <a href="{{ route('admin.agents') }}" class="btn btn-secondary">إدارة جهات الحجز</a>
        <a href="{{ route('admin.hotels') }}" class="btn btn-secondary">إدارة الفنادق</a> <!-- زر إدارة الفنادق -->
    </div>

    <!-- قسم البحث والفلترة -->
    <div class="p-4 mb-4" style="background-color: #f8f9fa; border-radius: 8px; border: 1px solid #ddd;">
        <h3 class="mb-3">عملية البحث والفلترة</h3>
        <form method="GET" action="{{ route('bookings.index') }}">
            <div class="row align-items-center text-center">
                <div class="col-md-4 mb-2">
                    <label for="search" class="form-label">بحث باسم العميل، الموظف، الشركة، جهة الحجز، أو الفندق</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-2">
                    <label for="start_date" class="form-label">من تاريخ</label>
                    <input type="text" name="start_date" id="start_date" class="form-control datepicker" value="{{ request('start_date') }}" placeholder="يوم/شهر/سنة">
                </div>
                <div class="col-md-4 mb-2">
                    <label for="end_date" class="form-label">إلى تاريخ</label>
                    <input type="text" name="end_date" id="end_date" class="form-control datepicker" value="{{ request('end_date') }}" placeholder="يوم/شهر/سنة">
                </div>
            </div>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">فلترة</button>
            </div>
        </form>
    </div>

    <a href="{{ route('bookings.create') }}" class="btn btn-primary mb-3">+ إضافة حجز جديد</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th> <!-- عمود الترقيم -->
                    <th> العميل</th>
                    <th> الشركة</th>
                    <th>جهة الحجز</th>
                    <th> الفندق</th>
                    <th>تاريخ الدخول</th>
                    <th>تاريخ الخروج</th>
                    <th>عدد الأيام</th>
                    <th>عدد الغرف</th>
                    <th>المبلغ المستحق للفندق</th>
                    <th>السداد مني للفندق</th>
                    <th>المبلغ المستحق من الشركة</th>
                    <th>السداد من الشركة</th>
                    <th>الموظف المسؤول</th>
                    <th>الملاحظات</th>
                    <th>المكسب <i class="fas fa-coins"></i></th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                <tr>
                    <td class="text-center align-middle">{{ $loop->iteration }}</td> <!-- رقم الصف -->
                    <td class="text-center align-middle">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-primary">
                            {{ $booking->client_name }}
                        </a>
                    </td>
                    <td class="text-center align-middle">
                        <a href="{{ route('bookings.index', ['company_id' => $booking->company->id]) }}" class="text-primary">
                            {{ $booking->company->name }}
                        </a>
                    </td>
                    <td class="text-center align-middle">
                        <a href="{{ route('bookings.index', ['agent_id' => $booking->agent->id]) }}" class="text-primary">
                            {{ $booking->agent->name }}
                        </a>
                    </td>
                    <td class="text-center align-middle">
                        <a href="{{ route('bookings.index', ['hotel_id' => $booking->hotel->id]) }}" class="text-primary">
                            {{ $booking->hotel->name }}
                        </a>
                    </td>
                    <td class="text-center align-middle">{{ $booking->check_in->format('d/m/Y') }}</td>
                    <td class="text-center align-middle">{{ $booking->check_out->format('d/m/Y') }}</td>
                    <td class="text-center align-middle">{{ $booking->days }}</td>
                    <td class="text-center align-middle">{{ $booking->rooms }}</td>
                    <td class="text-center align-middle" title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->cost_price }} سعر الفندق)">
                        {{ $booking->amount_due_to_hotel }}
                    </td>
                    <td class="text-center align-middle">{{ $booking->amount_paid_to_hotel }}</td>
                    <td class="text-center align-middle" title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->sale_price }} سعر الليلة)">
                        {{ $booking->amount_due_from_company }}
                    </td>
                    <td class="text-center align-middle">{{ $booking->amount_paid_by_company }}</td>
                    <td class="text-center align-middle">
                        <a href="{{ route('bookings.index', ['employee_id' => $booking->employee->id]) }}" class="text-primary">
                            {{ $booking->employee->name }}
                        </a>
                    </td>
                    <td class="text-center align-middle">{{ $booking->notes }}</td>
                    <td class="text-center align-middle">
                        {{ $booking->amount_due_from_company - $booking->amount_due_to_hotel }}
                    </td>
                    <td class="text-center align-middle">
                        <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                        <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <button id="calculate-total" class="btn btn-info">حساب الإجمالي</button>
    </div>
</div>
@endsection

<script>
    document.getElementById('calculate-total').addEventListener('click', function () {
        let totalDueFromCompany = 0;
        let totalDueToHotel = 0;

        @foreach ($bookings as $booking)
            // حساب عدد الليالي التي قضاها العميل حتى الآن
            let checkInDate = new Date("{{ $booking->check_in }}");
            let today = new Date();
            let nightsStayed = Math.min(
                Math.max(0, Math.ceil((today - checkInDate) / (1000 * 60 * 60 * 24))),
                {{ $booking->days }}
            );

            // حساب الإجمالي
            totalDueFromCompany += nightsStayed * {{ $booking->rooms }} * {{ $booking->sale_price }};
            totalDueToHotel += nightsStayed * {{ $booking->rooms }} * {{ $booking->cost_price }};
        @endforeach

        alert(`الإجمالي حتى الآن:\nما لك من الشركة: ${totalDueFromCompany} جنيه\nما عليك للفندق: ${totalDueToHotel} جنيه`);
    });
</script>