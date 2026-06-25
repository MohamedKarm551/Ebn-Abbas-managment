<x-app-layout>
<div style="max-width:1200px;margin:40px auto;padding:20px;font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h2 style="margin:0;">🗺️ الرحلات</h2>
    <div style="display:flex;gap:10px;">       
        <a href="{{ route('trips.trashed') }}"
           style="background:#7c3aed;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;">
            🗑️ الرحلات المحذوفة
        </a> 
        <a href="{{ route('trips.create') }}"
           style="background:#2563eb;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;">
            ➕ إضافة رحلة
        </a>              
    </div>
</div>

    @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:12px;border-radius:6px;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif


<form method="GET" action="{{ route('trips.index') }}" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="text" name="name" value="{{ request('name') }}"
        placeholder="🔍 اسم الرحلة او رقمها"
        style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1;">

    <input type="date" name="from_date" value="{{ request('from_date') }}"
        style="padding:8px;border:1px solid #ddd;border-radius:6px;">

    <input type="date" name="to_date" value="{{ request('to_date') }}"
        style="padding:8px;border:1px solid #ddd;border-radius:6px;">

    <button type="submit"
        style="background:#2563eb;color:#fff;padding:8px 16px;border:none;border-radius:6px;cursor:pointer;">
        🔍 بحث
    </button>

    <a href="{{ route('trips.index') }}"
        style="background:#6b7280;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;">
        ✖ إلغاء
    </a>
</form>
 @if(auth()->user()->hasRole('admin'))
<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
    <a href="{{ route('trips.index', array_merge(request()->query(), ['filter'=>'total_seats'])) }}"
       style="background:#16a34a;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;">
        🪑 رحلات فيها أماكن
    </a>
    <a href="{{ route('trips.index', array_merge(request()->query(), ['filter'=>'remaining_money'])) }}"
       style="background:#dc2626;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;">
        💰 رحلات فيها فلوس متبقية
    </a>
    <a href="{{ route('trips.index') }}"
       style="background:#6b7280;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;">
        ✖ إلغاء الفلتر
    </a>
</div>
@endif
{{-- إجماليات عامة --}}
<div style="display: flex; gap: 20px; background: #f3f4f6; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;">
    <div style="font-size: 18px;">
        🪑 إجمالي المقاعد المتبقية :
        <strong>{{ $totalRemainingSeats }}</strong>
    </div>
    @if(auth()->user()->hasRole('admin'))
    <div style="font-size: 18px;">
        💰 إجمالي المبالغ المتبقي :
        <strong>{{ number_format($totalRemainingMoney, 2) }}</strong>
    </div>
    @endif
</div>
<div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;background:white;border-radius:8px;
                  overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);">
        <thead style="background:#2563eb;color:white;">
            <tr>
                <th style="padding:12px;">#</th>
                <th style="padding:12px;">رقم الرحلة</th>
                <th style="padding:12px;">اسم الرحلة</th>
                <th style="padding:12px;">من</th>
                <th style="padding:12px;">إلى</th>
                <th style="padding:12px;">المقاعد المتبقية</th>
                @if(auth()->user()->hasRole('admin'))
                    <th style="padding:12px;">إجمالي الرحلة</th>
                    <th style="padding:12px;">إجمالي المدفوعات</th>
                    <th style="padding:12px;">إجمالي المتبقي</th>
                @endif
                <th style="padding:12px;text-align: center;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trips as $trip)
            @php
                $totalAmount = $trip->bookings->sum(fn($b) => $b->finalPrice());
                $paidAmount  = $trip->bookings->sum(fn($b) => $b->payments->sum('amount'));
                $remainingAmount = $trip->bookings->sum(fn($b) => $b->finalPrice() - $b->payments->sum('amount'));
            @endphp
            <tr style="border-bottom:1px solid #f0f0f0;text-align:center;">
                <td style="padding:12px;">{{ $loop->iteration }}</td>
                <td style="padding:12px;">{{ $trip->id }}</td>
                <td style="padding:12px;font-weight:bold;">{{ $trip->name }}</td>
                <td style="padding:12px;">{{ $trip->from }}</td>
                <td style="padding:12px;">{{ $trip->to }}</td>
                <td style="padding:12px;">
                    {{ $trip->total_seats - $trip->bookings->count() }}
                </td>
                @if(auth()->user()->hasRole('admin'))
                    <td style="padding:12px;">{{ number_format($totalAmount, 2) }}</td>
                    <td style="padding:12px;">{{ number_format($paidAmount, 2) }}</td>
                    <td style="padding:12px;">{{ number_format($remainingAmount, 2) }}</td>
                @endif
                <td style="padding:12px; min-width: 300px;">
                    <a href="{{ route('trips.show', $trip) }}"
                       style="background:#0ea5e9;color:white;padding:6px 12px;
                              border-radius:4px;text-decoration:none;margin-left:6px;">
                        👁️ تفاصيل
                    </a>
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('trips.edit', $trip) }}"
                           style="background:#f59e0b;color:white;padding:6px 12px;
                                  border-radius:4px;text-decoration:none;margin-left:6px;">
                            ✏️ تعديل
                        </a>
                        <form method="POST" action="{{ route('trips.destroy', $trip) }}"
                              style="display:inline;"
                              onsubmit="return confirm('هل أنت متأكد من حذف الرحلة؟')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                style="background:#ef4444;color:white;padding:6px 12px;
                                       border:none;border-radius:4px;cursor:pointer;">
                                🗑️ حذف
                            </button>
                        </form> 
                    @else
                        <a href="{{ route('bookings.create', $trip) }}"
                           style="background:#059669; color:white; padding:8px 16px; border-radius:6px; text-decoration:none;">
                            ➕ احجز الآن
                        </a>
                    @endif                   
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="padding:20px;text-align:center;color:#999;">لا توجد رحلات بعد</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
    <div style="margin-top:16px;">{{ $trips->appends(request()->query())->links() }}</div>
</div>
</x-app-layout>