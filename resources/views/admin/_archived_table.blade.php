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
            {{-- <th>المستحق للفندق</th>
            <th>مطلوب من الشركة</th> --}}
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
                <td class="text-center align-middle">
                    <a href="{{ route('bookings.show', $booking->id) }}" class="text-primary">
                        {{ $booking->client_name }}
                    </a>
                </td>
                <td class="text-center align-middle">
                    <a href="{{ route('admin.archived_bookings', ['company_id' => $booking->company->id]) }}"
                        class="text-primary">
                        {{ $booking->company->name }}
                    </a>
                </td>
                <td class="text-center align-middle">
                    <a href="{{ route('admin.archived_bookings', ['agent_id' => $booking->agent->id]) }}"
                        class="text-primary">
                        {{ $booking->agent->name }}
                    </a>
                </td>
                <td class="text-center align-middle">
                    <a href="{{ route('admin.archived_bookings', ['hotel_id' => $booking->hotel->id]) }}"
                        class="text-primary">
                        {{ $booking->hotel->name }}
                    </a>
                </td>
                <td class="text-center align-middle">
                    {{ $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('d/m/Y') : '-' }}
                </td>
                <td class="text-center align-middle">
                    {{ $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') : '-' }}
                </td>
                <td>{{ $booking->rooms ?? '-' }}</td>
                {{-- <td>{{ $booking->amount_due_to_hotel ?? '-' }}   --}}
                {{-- <td>{{ $booking->amount_due_from_company ?? '-' }} --}}
                <td>{{ $booking->employee->name ?? '-' }}</td>
                <td class="text-center align-middle">
                    @if (!empty($booking->notes))
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="popover"
                            data-bs-trigger="hover focus" data-bs-placement="left" data-bs-custom-class="notes-popover"
                            title="الملاحظات" data-bs-content="{{ nl2br(e($booking->notes)) }}">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    @else
                        <span class="text-muted small">--</span>
                    @endif
                </td>
                <td>{{ $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i') : '-' }}</td>

                <td class="text-center align-middle">
                    <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-sm btn-warning me-1"
                        title="تعديل">
                        <i class="fas fa-edit"></i>
                    </a>
                    @auth
                        @if (auth()->user()->role === 'Admin')
                            <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST"
                                style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    @endauth
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
