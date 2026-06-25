<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);
            font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;
                align-items:center;margin-bottom:24px;">
        <div>
            <h2 style="margin:0;">👔 تقرير المناديب</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                رحلة: <strong>{{ $trip->name }}</strong>
                ({{ $trip->from }} ← {{ $trip->to }})
            </p>
        </div>
        <a href="{{ route('trips.show', $trip) }}"
           style="color:#6b7280;text-decoration:none;">← رجوع</a>
    </div>

    {{-- إجمالي --}}
    <div style="background:#eff6ff;border-radius:8px;padding:16px;
                margin-bottom:20px;text-align:center;">
        <div style="color:#6b7280;font-size:13px;">إجمالي حجوزات الرحلة</div>
        <div style="font-size:28px;font-weight:bold;color:#2563eb;">
            {{ $report->sum('bookings_count') }} حجز
        </div>
    </div>

    @if($report->isEmpty())
        <p style="text-align:center;color:#999;">لا توجد حجوزات بعد</p>
    @else
    <table style="width:100%;border-collapse:collapse;">
        <thead style="background:#7c3aed;color:white;">
            <tr>
                <th style="padding:12px;text-align:right;">#</th>
                <th style="padding:12px;text-align:right;">اسم المندوب</th>
                <th style="padding:12px;text-align:center;">عدد الحجوزات</th>
                <th style="padding:12px;text-align:center;">النسبة</th>
            </tr>
        </thead>
        <tbody>
            @php $total = $totalSeats > 0 ? $totalSeats : 1; @endphp
            @foreach($report as $row)
            <tr style="border-bottom:1px solid #f0f0f0;
                       background:{{ $loop->even ? '#f9fafb':'white' }}">
                <td style="padding:12px;">{{ $loop->iteration }}</td>
                <td style="padding:12px;font-weight:bold;">
                    👔 {{ $row->representative_name ?? 'غير محدد' }}
                </td>
                <td style="padding:12px;text-align:center;">
                    <span style="background:#ddd9fe;color:#4c1d95;
                                 padding:4px 14px;border-radius:20px;
                                 font-weight:bold;font-size:16px;">
                        {{ $row->bookings_count }}
                        {{ $row->bookings_count == 1 ? 'حجز' : 'حجوزات' }}
                    </span>
                </td>
                <td style="padding:12px;text-align:center;">
                    @php
                        $percent = $total > 0
                            ? round(($row->bookings_count / $total) * 100)
                            : 0;
                    @endphp
                    {{-- شريط النسبة --}}
                    <div style="background:#e5e7eb;border-radius:10px;
                                height:10px;width:100%;margin-bottom:4px;">
                        <div style="background:#7c3aed;border-radius:10px;
                                    height:10px;width:{{ $percent }}%;"></div>
                    </div>
                    <span style="font-size:13px;color:#6b7280;">{{ $percent }}%</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
</x-app-layout>