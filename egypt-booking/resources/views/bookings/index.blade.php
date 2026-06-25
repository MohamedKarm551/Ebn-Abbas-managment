<x-app-layout>
<div style="width:95%;max-width:1400px;margin:40px auto;padding:20px;font-family:Arial;" dir="rtl">

    {{-- Header --}}
        <div style="display:flex;
        justify-content:space-between;
        align-items:center;
        gap:15px;
        flex-wrap:wrap;
        margin-bottom:20px;">
        <div>
            <h2 style="margin:0;">🎫 حجوزات رحلة: {{ $trip->name }}</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                {{ $trip->from }} ← {{ $trip->to }}
                @if(auth()->user()->hasRole('admin'))
                    | إجمالي الحجوزات: <strong>{{ $bookings->total() }}</strong>
                @else
                    | حجوزاتى : <strong>{{ $bookings->total() }}</strong>
                @endif
            </p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('bookings.create', $trip) }}"
               style="background:#2563eb;color:white;padding:8px 16px;
                      border-radius:6px;text-decoration:none;">
                ➕ حجز جديد
            </a>
            <a href="{{ route('bookings.trashed', $trip) }}"
               style="background:#7c3aed;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;">
                🗑️ الحجوزات المحذوفة
            </a>
            <a href="{{ route('trips.show', $trip) }}"
               style="background:#6b7280;color:white;padding:8px 16px;
                      border-radius:6px;text-decoration:none;">
                ← رجوع للرحلة
            </a>
        </div>
    </div>

    {{-- فورم الفلترة --}}
<div style="background:white;padding:16px;border-radius:8px;
            box-shadow:0 2px 8px rgba(0,0,0,.1);margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        
        {{-- البحث باسم العميل أو رقم الحجز --}}
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="🔍 اسم العميل أو رقم الحجز..."
                   style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;
              font-family:Arial;font-size:14px;min-width:200px;">

         @if(auth()->user()->hasRole('admin'))
        {{-- المندوب --}}
        <select name="representative_id"
                style="padding:8px 12px;border:1px solid #d1d5db;
                       border-radius:6px;font-family:Arial;font-size:14px;color:#374151;">
            <option value="">👤 كل المندوبين</option>
            @foreach($representatives as $rep)
                <option value="{{ $rep->id }}" 
                    {{ request('representative_id') == $rep->id ? 'selected' : '' }}>
                    {{ $rep->name }}
                </option>
            @endforeach
        </select>
        @endif

        {{-- النوع --}}
        <select name="gender"
                style="padding:8px 12px;border:1px solid #d1d5db;
                       border-radius:6px;font-family:Arial;font-size:14px;color:#374151;">
            <option value="">👥 كل الأنواع</option>
            <option value="male"   {{ request('gender')=='male'   ? 'selected':'' }}>👨 ذكر</option>
            <option value="female" {{ request('gender')=='female' ? 'selected':'' }}>👩 أنثى</option>
            <option value="child"  {{ request('gender')=='child'  ? 'selected':'' }}>👦 طفل</option>
            <option value="infant" {{ request('gender')=='infant' ? 'selected':'' }}>👶 رضيع</option>
        </select>

        {{-- التسكين --}}
        <select name="room_status"
                style="padding:8px 12px;border:1px solid #d1d5db;
                       border-radius:6px;font-family:Arial;font-size:14px;color:#374151;">
            <option value="">🛏️ كل التسكين</option>
            <option value="assigned"   {{ request('room_status')=='assigned'   ? 'selected':'' }}>✅ مُسكَّن</option>
            <option value="unassigned" {{ request('room_status')=='unassigned' ? 'selected':'' }}>⚠️ غير مُسكَّن</option>
        </select>

         @if(auth()->user()->hasRole('admin'))
        {{-- المتبقي --}}
        <select name="has_remaining"
                style="padding:8px 12px;border:1px solid #d1d5db;
                       border-radius:6px;font-family:Arial;font-size:14px;color:#374151;">
            <option value="">💳 كل الحجوزات</option>
            <option value="1" {{ request('has_remaining')=='1' ? 'selected':'' }}>💰 لم يسددوا بالكامل</option>
        </select>
         @endif
        {{-- أزرار --}}
        <button type="submit"
                style="background:#2563eb;color:white;padding:8px 18px;
                       border:none;border-radius:6px;cursor:pointer;
                       font-family:Arial;font-size:14px;">
            🔍 بحث
        </button>
        <a href="{{ route('trips.bookings', $trip) }}"
           style="background:#6b7280;color:white;padding:8px 18px;
                  border-radius:6px;text-decoration:none;font-size:14px;">
            ✖ إلغاء
        </a>
    </form>
</div>


@if(session('success'))
    <div style="background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2; color:#dc2626; padding:12px; border-radius:6px; margin-bottom:16px;">
        {{ session('error') }}
    </div>
@endif

@if(session('warning'))
    <div style="background:#fef3c7; color:#92400e; padding:12px; border-radius:6px; margin-bottom:16px;">
        {{ session('warning') }}
    </div>
@endif

<div style="overflow-x:auto;">
    {{-- جدول الحجوزات --}}
    <table style="width:100%;border-collapse:collapse;background:white;
                  border-radius:8px;overflow:hidden;
                  box-shadow:0 2px 8px rgba(0,0,0,.1);white-space:nowrap;">
        <thead style="background:#2563eb;color:white;">
            <tr>
                <th style="padding:12px;">#</th>
                <th style="padding:12px;">رقم الحجز</th>
                <th style="padding:12px;">العميل</th>
                <th style="padding:12px;">النوع</th>
                <th style="padding:12px;">التسكين</th>
                <th style="padding:12px;">السعر</th>
                <th style="padding:12px;">المدفوع</th>
                <th style="padding:12px;">المتبقي</th>
                <th style="padding:12px;">حالة القيد</th>
                <th style="padding:12px;">المندوب</th>
                <th style="padding:12px;">التسكين</th>
                <th style="padding:12px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
            <tr style="border-bottom:1px solid #f0f0f0;text-align:center;
                       background:{{ $loop->even ? '#f9fafb':'white' }}">
                <td style="padding:12px;">{{ $loop->iteration }}</td>
                <td style="padding:12px;font-weight:bold;">
                    {{ $booking->id }}
                </td>
                <td style="padding:12px;font-weight:bold;">
                    {{ $booking->client_name }}
                </td>
                <td style="padding:12px;">
                    @switch($booking->gender)
                        @case('male')   👨 ذكر   @break
                        @case('female') 👩 أنثى   @break
                        @case('child')  👦 طفل    @break
                        @case('infant') 👶 رضيع   @break
                    @endswitch
                </td>
                <td style="padding:12px;">🛏️ {{ $booking->accommodation_type }}</td>
                <td style="padding:12px;font-weight:bold;">
                     {{ number_format($booking->base_price, 2) }} 
                </td>
                <td style="padding:12px;color:#059669;font-weight:bold;">
                    {{ number_format($booking->totalPaid(), 2) }}
                </td>
                <td style="padding:12px;
                           color:{{ $booking->remaining() > 0 ? '#dc2626':'#059669' }};
                           font-weight:bold;">
                    {{ number_format($booking->remaining(), 2) }}
                </td>

                <td style="padding:12px;">
                    @if($booking->journalEntry)
                        @if($booking->journalEntry->status == 'draft')
                            <span style="background:#fef3c7;color:#92400e;
                                            padding:4px 10px;border-radius:20px;font-size:12px;">
                                ⏳ مسودة
                            </span>
                        @elseif($booking->journalEntry->status == 'posted')
                            <span style="background:#d1fae5;color:#065f46;
                                            padding:4px 10px;border-radius:20px;font-size:12px;">
                                ✅ معتمد
                            </span>
                        @else
                            <span>{{ $booking->journalEntry->status }}</span>
                        @endif
                    @else
                        <span style="background:#fee2e2;color:#dc2626;
                                        padding:4px 10px;border-radius:20px;font-size:12px;">
                            ❌ لا يوجد
                        </span>
                    @endif
                </td>

                <td style="padding:12px;color:#6b7280;">
                    {{ $booking->representative ? $booking->representative->name : '—' }}
                </td>
                <td style="padding:12px;text-align:center;">
                    <a href="{{ route('trips.room-assignments', $trip) }}"
                        style="text-decoration:none;">
                        @if($booking->roomAssignment)
                            <span style="background:#dbeafe;color:#1d4ed8;
                                            padding:4px 10px;border-radius:20px;font-size:12px;
                                            display:inline-block;">
                                🛏️ غرفة {{ $booking->roomAssignment->room_number }}
                            </span>
                        @else
                            <span style="background:#fee2e2;color:#dc2626;
                                            padding:4px 10px;border-radius:20px;font-size:12px;
                                            display:inline-block;">
                                ⚠️ لم يُسكَّن
                            </span>
                        @endif
                    </a>
                </td>
                <td style="padding:12px;">
                <div style="display:flex;gap:5px;justify-content:center;flex-wrap:nowrap;">
                    <a href="{{ route('bookings.show', $booking) }}"
                       style="background:#2563eb;color:white;padding:5px 10px;
                              border-radius:4px;text-decoration:none;
                              white-space:nowrap;">
                        👁️ تفاصيل
                    </a>
                    <a href="{{ route('bookings.edit', $booking) }}"
                       style="background:#f59e0b;color:white;padding:5px 10px;
                              border-radius:4px;text-decoration:none;
                              white-space:nowrap;">
                        ✏️ تعديل
                    </a>
                    <form method="POST"
                          action="{{ route('bookings.destroy', $booking) }}"
                          style="white-space:nowrap;"
                          onsubmit="return confirm('هل أنت متأكد من حذف الحجز؟')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            style="background:#ef4444;color:white;padding:5px 10px;
                                   border:none;border-radius:4px;cursor:pointer;">
                            🗑️ حذف
                        </button>
                    </form>
                </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12"
                    style="padding:30px;text-align:center;color:#999;">
                    @if(auth()->user()->hasRole('admin'))
                    لا توجد حجوزات لهذه الرحلة بعد
                    @else
                    لم تقم بأى حجز بعد
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
    <div style="margin-top:16px;">{{ $bookings->links() }}</div>
</div>
</x-app-layout>