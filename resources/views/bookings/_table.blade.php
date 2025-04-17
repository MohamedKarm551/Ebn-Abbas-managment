<table class="table table-bordered table-hover table-striped">
    <thead class="table-dark"> {{-- Use a dark header for better contrast striped يعني مخطط عشان العين تلاحظ كل صف لوحده بلون--}}
        <tr>
            <th class="text-center" style="width: 3%;">م</th>
            <th>العميل</th>
            <th>الشركة</th>
            <th>جهة حجز</th>
            <th>الفندق</th>
            <th style="min-width: 100px;"> الدخول</th> {{-- تحديد عرض أدنى للتواريخ --}}
            <th style="min-width: 100px;"> الخروج</th>
            {{-- <th>عدد الأيام</th> --}}
            <th class="text-center">غرف</th> {{-- اختصار "عدد الغرف" --}}
            @if (!request('company_id'))
                <th style="min-width: 100px;"> المستحق للفندق</th> {{-- Adjusted width --}}
                {{-- <th>السداد مني للفندق</th> --}}
            @endif
            <th style="min-width: 100px;"> مطلوب من الشركة</th>
            {{-- <th>السداد من الشركة</th> --}}
            <th>الموظف المسؤول</th>
            <th class="text-center">الملاحظات</th>
            <th class="text-center" style="min-width: 130px;">الإجراءات</th> {{-- تحديد عرض أدنى لضمان ظهور الأزرار --}}
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
                    <a href="{{ route('bookings.index', ['company_id' => $booking->company->id]) }}"
                        class="text-primary">
                        {{ $booking->company->name }}
                    </a>
                </td>
                <td class="text-center align-middle">
                    <a href="{{ route('bookings.index', ['agent_id' => $booking->agent->id]) }}"
                        class="text-primary">
                        {{ $booking->agent->name }}
                    </a>
                </td>
                <td class="text-center align-middle">
                    <a href="{{ route('bookings.index', ['hotel_id' => $booking->hotel->id]) }}"
                        class="text-primary">
                        {{ $booking->hotel->name }}
                    </a>
                </td>
                <td class="text-center align-middle">{{ $booking->check_in->format('d/m/Y') }}</td>
                <td class="text-center align-middle">{{ $booking->check_out->format('d/m/Y') }}</td>
                {{-- <td class="text-center align-middle">{{ $booking->days }}</td> --}}
                <td class="text-center align-middle">{{ $booking->rooms }}</td>
                @if (!request('company_id')) {{-- Add the same condition here --}}
                <td class="text-center align-middle"
                    title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->cost_price }} سعر الفندق)">
                    {{ $booking->amount_due_to_hotel }}
                </td>
                @endif {{-- End the condition --}}
                {{-- <td class="text-center align-middle">{{ $booking->amount_paid_to_hotel }}</td> --}}
                <td class="text-center align-middle"
                    title="({{ $booking->days }} ليالي * {{ $booking->rooms }} غرفة * {{ $booking->sale_price }} سعر الليلة)">
                    {{ $booking->amount_due_from_company }}
                </td>
                {{-- <td class="text-center align-middle">{{ $booking->amount_paid_by_company }}</td> --}}
                <td class="text-center align-middle">
                    <a href="{{ route('bookings.index', ['employee_id' => $booking->employee->id]) }}"
                        class="text-primary">
                        {{ $booking->employee->name }}
                    </a>
                </td>
                <td class="text-center align-middle">
                    {{-- Notes Popover Implementation --}}
                    @if (!empty($booking->notes))
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="popover" data-bs-trigger="hover focus" {{-- Show on hover or focus --}}
                            data-bs-placement="left" data-bs-custom-class="notes-popover" title="الملاحظات"
                            data-bs-content="{{ nl2br(e($booking->notes)) }}">
                            <i class="fas fa-info-circle"></i> {{-- Font Awesome icon --}}
                        </button>
                    @else
                        <span class="text-muted small">--</span> {{-- Indicate no notes --}}
                    @endif
                </td>

                <td class="text-center align-middle">
                    {{-- Action Buttons with Icons --}}
                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-info me-1"
                        title="التفاصيل"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-sm btn-warning me-1"
                        title="تعديل"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                        style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحجز؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="حذف"><i
                                class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>