 <div class="mb-3 text-start">
     <a href="{{ route('admin.archived_bookings.export', request()->query()) }}" class="btn btn-success">
         <i class="fas fa-file-excel me-1"></i> تصدير إلى Excel
     </a>
 </div>

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
                     @if ($booking->check_in)
                         <small class="d-block text-muted hijri-date"
                             data-date="{{ \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d') }}"></small>
                     @endif
                 </td>
                 <td class="text-center align-middle">
                     {{ $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d/m/Y') : '-' }}
                     @if ($booking->check_out)
                         <small class="d-block text-muted hijri-date"
                             data-date="{{ \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d') }}"></small>
                     @endif
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

                 <td class="text-center align-middle d-flex justify-content-center flex-wrap gap-2 p-2">
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
 @push('scripts')
     <script>
         // Converts Gregorian dates to Hijri
         function convertToHijri() {
             document.querySelectorAll('.hijri-date').forEach(element => {
                 const gregorianDate = element.getAttribute('data-date');
                 if (gregorianDate) {
                     try {
                         // Use Intl.DateTimeFormat with 'islamic' calendar
                         const hijriDate = new Intl.DateTimeFormat('ar-SA-islamic', {
                             day: 'numeric',
                             month: 'long',
                             calendar: 'islamic'
                         }).format(new Date(gregorianDate));

                         element.textContent = hijriDate;
                     } catch (e) {
                         console.error("Error converting date:", e);
                         element.textContent = ""; // Clear if error
                     }
                 }
             });
         }

         // Convert dates when page loads
         document.addEventListener("DOMContentLoaded", function() {
             convertToHijri();

             // Also convert when table is updated via AJAX
             document.addEventListener('ajaxTableUpdated', convertToHijri);
         });
     </script>
 @endpush
