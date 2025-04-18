<table class="table table-bordered table-hover align-middle text-center">
    {{-- رؤوس الأعمدة --}}
    <thead class="table-light">
        <tr>
            <th>م</th>
            <th>العميل</th>
            <th>الشركة</th>
            <th>جهة حجز</th>
            <th>الفندق</th>
            <th>الدخول</th>
            <th>الخروج</th>
            <th>غرف</th>
            <th>المستحق للفندق</th>
            <th>مطلوب من الشركة</th>
            <th>الموظف المسؤول</th>
            <th>الملاحظات</th>
            <th>آخر تحديث</th>

            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($archivedBookings as $key => $booking)
            <tr>
                <td>{{ $archivedBookings->firstItem() + $key }}</td>
                <td>{{ $booking->client_name }}</td>
                <td>{{ $booking->company->name ?? '-' }}</td>
                <td>{{ $booking->agent->name ?? '-' }}</td>
                <td>{{ $booking->hotel->name ?? '-' }}</td>
                <td>{{ $booking->check_in ?? '-' }}</td>
                <td>{{ $booking->check_out ?? '-' }}</td>
                <td>{{ $booking->rooms ?? '-' }}</td>
                <td>{{ $booking->amount_due_to_hotel ?? '-' }}</td> {{-- أو total_agent_due أو due_to_agent --}}
                <td>{{ $booking->amount_due_from_company ?? '-' }}</td> {{-- أو total_company_due أو due_to_company --}}
                <td>{{ $booking->employee->name ?? '-' }}</td>
                <td>{{ $booking->notes }}</td>
                <td>{{ $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i') : '-' }}</td>

                <td>
                    <a href="{{ route('bookings.edit', $booking->id) }}"
                        class="btn btn-sm btn-warning mb-1">تعديل</a>
                    <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>