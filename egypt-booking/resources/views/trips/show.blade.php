<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 style="margin:0;">🗺️ {{ $trip->name }}</h2>
</div>

<div style="display:flex; gap:10px; margin-bottom:24px;">
    @if(auth()->user()->hasRole('admin'))
    <a href="{{ route('trips.room-assignments', $trip) }}"
        style="background:#7c3aed;color:white;padding:8px 16px;
               border-radius:6px;text-decoration:none;">
         🛏️ إدارة التسكين
         @php
             $unassignedCount = $trip->bookings()
                 ->whereNull('room_assignment_id')->count();
         @endphp
         @if($unassignedCount > 0)
            <span>
                ({{ $unassignedCount }})
            </span>
        @endif
    </a>
    @endif
    <a href="{{ route('trips.bookings', $trip) }}"
       style="background:#2563eb; color:white; padding:8px 16px; border-radius:6px; text-decoration:none;">
         @if(auth()->user()->hasRole('admin'))
            🎫 عرض كل الحجوزات ({{ $trip->bookings->count() }})
        @else
            @php
            $myBookingsCount = $trip->bookings()->where('representative_id', auth()->id())->count();
            @endphp
            🎫 الحجوزات الخاصة بى ({{ $myBookingsCount }})
        @endif
    </a>
    <a href="{{ route('bookings.create', $trip) }}"
       style="background:#059669; color:white; padding:8px 16px; border-radius:6px; text-decoration:none;">
        ➕ احجز الآن
    </a>
    @if(auth()->user()->hasRole('admin'))
    <a href="{{ route('trips.representatives.report', $trip) }}"
       style="background:#f59e0b; color:white; padding:8px 16px; border-radius:6px; text-decoration:none;">
        👔 تقرير المناديب
    </a>
    @endif
    <a href="{{ route('trips.index') }}"
       style="background:#6b7280; color:white; padding:8px 16px; border-radius:6px; text-decoration:none;">
        ← رجوع
    </a>
</div>

    {{-- معلومات الرحلة --}}
    <div style="background:#f0f9ff;border-radius:8px;padding:16px;margin-bottom:24px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <span style="color:#6b7280;font-size:13px;">من</span>
                <div style="font-size:18px;font-weight:bold;">📍 {{ $trip->from }}</div>
            </div>
            <div>
                <span style="color:#6b7280;font-size:13px;">إلى</span>
                <div style="font-size:18px;font-weight:bold;">📍 {{ $trip->to }}</div>
            </div>
            @if($trip->hotels)
            <div>
                <span style="color:#6b7280;font-size:13px;">الفنادق</span>
                <div style="font-size:16px;">🏨 {{ $trip->hotels }}</div>
            </div>
            @endif

             @if($trip->total_seats)
            <div >
                <div>
                    <span style="color:#6b7280;font-size:13px;">إجمالي المقاعد</span>
                    <div style="font-size:16px;">💺 {{ $trip->total_seats }}</div>
                </div>
                <div>
                    <span style="color:#6b7280;font-size:13px;">المقاعد المتاحة</span>
                    <div style="font-size:16px;">✅ {{ $trip->available_seats }}</div>
                </div>
            </div>
            @endif
        </div>
        @if($trip->description)
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid #d1e6f5;">
        <span style="color:#6b7280;font-size:13px;">الوصف</span>
        <div style="font-size:15px;margin-top:4px;line-height:1.5;">{{ $trip->description }}</div>
    </div>
    @endif
    </div>

    {{-- جدول الأسعار --}}
    <h3 style="margin-bottom:12px;color:#2563eb;">💰 الأسعار</h3>

    @if($trip->prices->isEmpty())
        <p style="color:#999;text-align:center;">لا توجد أسعار مضافة</p>
    @else
    <table style="width:100%;border-collapse:collapse;">
        <thead style="background:#2563eb;color:white;">
            <tr>
                <th style="padding:12px;text-align:right;">#</th>
                <th style="padding:12px;text-align:right;">نوع الغرفة</th>
                <th style="padding:12px;text-align:right;">السعر</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trip->prices as $price)
            <tr style="border-bottom:1px solid #f0f0f0;
                       background:{{ $loop->even ? '#f9fafb' : 'white' }}">
                <td style="padding:12px;">{{ $loop->iteration }}</td>
                <td style="padding:12px;font-weight:bold;">🛏️ {{ $price->room_type }}</td>
                <td style="padding:12px;color:#059669;font-weight:bold;">
                    {{ number_format((float) $price->price, 2) }} ج.م
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(auth()->user()->hasRole('admin'))
    {{-- بعد جدول الأسعار --}}
    <h3 style="margin:24px 0 12px;color:#2563eb;">📋 البنود</h3>
    
    @if($trip->items->isEmpty())
        <p style="color:#999;">لا توجد بنود</p>
    @else
    <table style="width:100%;border-collapse:collapse;">
        <thead style="background:#059669;color:white;">
            <tr>
                <th style="padding:10px;text-align:right;">البند</th>
                <th style="padding:10px;text-align:right;">القيمة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trip->items as $item)
            <tr style="border-bottom:1px solid #f0f0f0;
                       background:{{ $loop->even ? '#f9fafb' : 'white' }}">
                <td style="padding:10px;font-weight:bold;">{{ $item->name }}</td>
                <td style="padding:10px;">{{ $item->value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif


    {{-- أزرار التعديل والحذف --}}
    <div style="display:flex;gap:10px;margin-top:24px;">
        <a href="{{ route('trips.edit', $trip) }}"
           style="background:#f59e0b;color:white;padding:10px 24px;
                  border-radius:6px;text-decoration:none;">
            ✏️ تعديل
        </a>
        <form method="POST" action="{{ route('trips.destroy', $trip) }}"
              onsubmit="return confirm('هل أنت متأكد؟')">
            @csrf @method('DELETE')
            <button type="submit"
                style="background:#ef4444;color:white;padding:10px 24px;
                       border:none;border-radius:6px;cursor:pointer;">
                🗑️ حذف
            </button>
        </form>
    </div>
    @endif
</div>
</x-app-layout>