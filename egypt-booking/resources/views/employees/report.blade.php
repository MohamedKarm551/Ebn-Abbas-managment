<x-app-layout>
<div style="max-width:1000px;margin:40px auto;padding:20px;
            font-family:Arial;" dir="rtl">

    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;
                align-items:center;margin-bottom:24px;">
        <div>
            <h2 style="margin:0;">📊 تقرير المندوب</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                👔 <strong>{{ $employee->name }}</strong>
                | {{ $employee->email }}
            </p>
        </div>

           <a href="{{ route('employees.index') }}"
           style="background:#6b7280;color:white;padding:8px 16px;
                  border-radius:6px;text-decoration:none;">← رجوع</a>
    </div>

    {{-- فلتر الشهر --}}
    <form method="GET"
          action="{{ route('employees.report', $employee) }}"
          style="background:white;border-radius:10px;
                 padding:16px;margin-bottom:20px;
                 box-shadow:0 2px 8px rgba(0,0,0,.1);
                 display:flex;gap:12px;align-items:end;">
        <div>
            <label style="display:block;font-size:13px;
                          margin-bottom:4px;font-weight:bold;">
                📅 فلتر بالشهر
            </label>
            <input type="month" name="month"
                   value="{{ $month }}"
                   style="padding:8px;border:1px solid #ddd;
                          border-radius:6px;">
        </div>
        <button type="submit"
            style="background:#7c3aed;color:white;padding:9px 20px;
                   border:none;border-radius:6px;cursor:pointer;">
            🔍 عرض
        </button>
        @if($month)
        <a href="{{ route('employees.report', $employee) }}"
           style="background:#6b7280;color:white;padding:9px 16px;
                  border-radius:6px;text-decoration:none;">
            ✕ إلغاء الفلتر
        </a>
        @endif
    </form>

    {{-- إجمالي سريع --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;
                gap:16px;margin-bottom:24px;">
        <div style="background:#eff6ff;border-radius:10px;
                    padding:20px;text-align:center;">
            <div style="color:#6b7280;font-size:13px;">
                إجمالي الحجوزات
                {{ $month ? '('.date('F Y', strtotime($month.'-01')).')' : '' }}
            </div>
            <div style="font-size:32px;font-weight:bold;color:#2563eb;">
                {{ $bookings->count() }}
            </div>
        </div>
        <div style="background:#f0fdf4;border-radius:10px;
                    padding:20px;text-align:center;">
            <div style="color:#6b7280;font-size:13px;">عدد الرحلات</div>
            <div style="font-size:32px;font-weight:bold;color:#059669;">
                {{ $byTrip->count() }}
            </div>
        </div>
    </div>

    {{-- حجوزاته حسب الرحلة --}}
    <div style="background:white;border-radius:10px;
                box-shadow:0 2px 8px rgba(0,0,0,.1);
                margin-bottom:24px;overflow:hidden;">
        <div style="background:#7c3aed;color:white;
                    padding:12px 16px;font-weight:bold;">
            🗺️ الحجوزات حسب الرحلة
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:#f3f4f6;">
                <tr>
                    <th style="padding:10px;text-align:right;">#</th>
                    <th style="padding:10px;text-align:right;">اسم الرحلة</th>
                    <th style="padding:10px;text-align:right;">من — إلى</th>
                    <th style="padding:10px;text-align:center;">عدد الحجوزات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byTrip as $item)
@php
    $bookingsUrl = route('trips.bookings', $item['trip']);
    if ($month) {
        $bookingsUrl .= '?month=' . $month;
    }
@endphp
<tr style="border-bottom:1px solid #f0f0f0; cursor:pointer; transition:background 0.2s;"
    onclick="window.location='{{ $bookingsUrl }}'"
    onmouseover="this.style.background='#eff6ff'"
    onmouseout="this.style.background='white'">
    <td style="padding:10px;">{{ $loop->iteration }}</td>
    <td style="padding:10px;font-weight:bold;">
        {{ $item['trip']->name ?? '—' }}
    </td>
    <td style="padding:10px;color:#6b7280;">
        {{ $item['trip']->from ?? '' }} ← {{ $item['trip']->to ?? '' }}
    </td>
    <td style="padding:10px;text-align:center;">
        <span style="background:#ddd9fe;color:#4c1d95;padding:4px 14px;border-radius:20px;font-weight:bold;">
            {{ $item['count'] }} حجز
        </span>
        <span style="color:#2563eb;font-size:12px;margin-right:6px;">← اضغط للتفاصيل</span>
    </td>
</tr>
@empty
                <tr>
                    <td colspan="4"
                        style="padding:20px;text-align:center;color:#999;">
                        لا توجد بيانات
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- إجمالي شهري --}}
    <div style="background:white;border-radius:10px;
                box-shadow:0 2px 8px rgba(0,0,0,.1);overflow:hidden;">
        <div style="background:#2563eb;color:white;
                    padding:12px 16px;font-weight:bold;">
            📅 إجمالي الحجوزات شهرياً (كل الوقت)
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:#f3f4f6;">
                <tr>
                    <th style="padding:10px;text-align:right;">الشهر</th>
                    <th style="padding:10px;text-align:center;">
                        عدد الحجوزات
                    </th>
                </tr>
            </thead>
            <tbody>
                @php $maxMonth = $byMonth->max('count'); @endphp
                @forelse($byMonth as $row)
               <tr style="border-bottom:1px solid #f0f0f0;cursor:pointer;
                           transition:background 0.2s;"
                    onclick="window.location='{{ route('employees.report', $employee) }}?month={{ $row->month }}'"
                    onmouseover="this.style.background='#eff6ff'"
                    onmouseout="this.style.background='white'">
                    <td style="padding:10px;font-weight:bold;">
                        📅 {{ date('F Y', strtotime($row->month.'-01')) }}
                    </td>
                    <td style="padding:10px;text-align:center;">
                        <strong>{{ $row->count }}</strong>
                         <span style="color:#2563eb;font-size:12px;margin-right:6px;">← اضغط للتفاصيل</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3"
                        style="padding:20px;text-align:center;color:#999;">
                        لا توجد بيانات
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>