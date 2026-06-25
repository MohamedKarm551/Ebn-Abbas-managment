<x-app-layout>
<div style="max-width:1200px;margin:40px auto;padding:20px;font-family:Arial;" dir="rtl">

    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
            <h2 style="margin:0;">🗑️ الحجوزات المحذوفة</h2>
            <p style="color:#6b7280;margin:4px 0 0;">رحلة: <strong>{{ $trip->name }}</strong></p>
        </div>
        <a href="{{ route('trips.bookings', $trip) }}"
           style="background:#6b7280;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;">
            ↩️ رجوع للحجوزات
        </a>
    </div>

    @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:12px;border-radius:6px;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;background:white;
                      border-radius:8px;overflow:hidden;
                      box-shadow:0 2px 8px rgba(0,0,0,.1);white-space:nowrap;">
            <thead style="background:#7c3aed;color:white;">
                <tr>
                    <th style="padding:12px;">#</th>
                    <th style="padding:12px;">رقم الحجز</th>
                    <th style="padding:12px;">العميل</th>
                    <th style="padding:12px;">النوع</th>
                    <th style="padding:12px;">السعر</th>
                    <th style="padding:12px;">المندوب</th>
                    <th style="padding:12px;">تاريخ الحذف</th>
                     @if(auth()->user()->hasRole('admin'))
                    <th style="padding:12px;">استعادة</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr style="border-bottom:1px solid #f0f0f0;text-align:center;
                           background:{{ $loop->even ? '#f9fafb':'white' }}">
                    <td style="padding:12px;">{{ $loop->iteration }}</td>
                    <td style="padding:12px;font-weight:bold;">{{ $booking->id }}</td>
                    <td style="padding:12px;font-weight:bold;">{{ $booking->client_name }}</td>
                    <td style="padding:12px;">
                        @switch($booking->gender)
                            @case('male')   👨 ذكر   @break
                            @case('female') 👩 أنثى  @break
                            @case('child')  👦 طفل   @break
                            @case('infant') 👶 رضيع  @break
                        @endswitch
                    </td>
                    <td style="padding:12px;">{{ number_format($booking->base_price, 2) }}</td>
                    <td style="padding:12px;color:#6b7280;">
                        {{ $booking->representative?->name ?? '—' }}
                    </td>
                    <td style="padding:12px;color:#dc2626;">
                        {{ $booking->deleted_at->format('Y-m-d H:i') }}
                    </td>
                     @if(auth()->user()->hasRole('admin'))
                    <td style="padding:12px;">
                        <form method="POST" action="{{ route('bookings.restore', $booking->id) }}"
                              onsubmit="return confirm('هل تريد استعادة هذا الحجز؟')">
                            @csrf
                            <button type="submit"
                                style="background:#16a34a;color:white;padding:6px 14px;
                                       border:none;border-radius:4px;cursor:pointer;">
                                ♻️ استعادة
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:30px;text-align:center;color:#999;">
                        لا توجد حجوزات محذوفة لهذه الرحلة
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $bookings->links() }}</div>
</div>
</x-app-layout>