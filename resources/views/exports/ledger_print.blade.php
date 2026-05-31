<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
     <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>كشف حساب {{ $account->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, table, th, td, div, span {
            direction: rtl;
            unicode-bidi: embed;
            text-align: right;
            font-family: 'DejaVu Sans', 'Cairo', 'Tajawal', sans-serif;
        }
        .print-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            border-radius: 16px;
            overflow: hidden;
        }
        .toolbar {
            background: #1f2937;
            padding: 12px 20px;
            display: flex;
            gap: 12px;
            justify-content: flex-start;
            border-bottom: 1px solid #374151;
        }
        .toolbar button, .toolbar a {
            background: #374151;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
        }
        .toolbar button:hover, .toolbar a:hover { background: #4f5b6b; }
        .toolbar .btn-print { background: #059669; }
        .toolbar .btn-print:hover { background: #047857; }
        .toolbar .btn-pdf { background: #dc2626; }
        .toolbar .btn-pdf:hover { background: #b91c1c; }
        .report-header {
            padding: 20px 24px;
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }
        .report-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }
        .report-header .info {
            display: flex;
            gap: 24px;
            margin-top: 12px;
            font-size: 13px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th {
            background: #f3f4f6;
            padding: 12px 10px;
            border-bottom: 1px solid #d1d5db;
            font-weight: 600;
            text-align: right;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .debit {  font-weight: 600; }
        .credit {  font-weight: 600; }
        .opening-row { background: #fefce8; }
        .footer {
            padding: 16px 24px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        @media print {
            .toolbar { display: none; }
            body { background: white; padding: 0; }
            .print-container { box-shadow: none; border-radius: 0; }
            .report-header { background: white; }
        }
    </style>
</head>
<body>
<div class="print-container">
    {{-- شريط الأدوات يظهر فقط في المعاينة وليس في ملف PDF --}}
    @if(!isset($isPdfExport) || !$isPdfExport)
    <div class="toolbar">
        <button onclick="window.print()" class="btn-print">🖨️ طباعة التقرير</button>
        <a href="{{ route('accounts.ledger.download-pdf', $account) . '?' . http_build_query(request()->query()) }}" class="btn-pdf">📄 تحميل PDF (حفظ)</a>
        <a href="{{ route('accounts.ledger', $account) . '?' . http_build_query(request()->except('page')) }}" style="background:#6b7280;">↩️ العودة</a>
    </div>
    @endif

    <div class="report-header">
        <h2>
            @if(isset($isPdfExport) && $isPdfExport)
                كشف حساب:
            @else
                📊 كشف حساب:
            @endif
            {{ $account->name }} ({{ $account->code }})
        </h2>
        <div class="info">
            <span>
                @if(!(isset($isPdfExport) && $isPdfExport))
                    🏷️ نوع الحساب:
                
                @if($account->type == 'asset') أصل
                @elseif($account->type == 'liability') خصم
                @elseif($account->type == 'equity') حقوق ملكية
                @elseif($account->type == 'revenue') إيراد
                @else مصروف
                @endif
                @endif
            </span>
            <span style="margin-right: 100px;">
                 @if(!(isset($isPdfExport) && $isPdfExport))
                   ⚖️ طبيعة الرصيد:
               
                 {{ $account->normal_balance == 'debit' ? 'مدين' : 'دائن' }}</span>
                  @endif
            @if(request('date_from') || request('date_to'))
                <span>
                    @if(!(isset($isPdfExport) && $isPdfExport))
                    📅 
                    @endif
                    الفترة: {{ request('date_from', 'الكل') }} → {{ request('date_to', 'الكل') }}</span>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>رقم القيد</th>
                <th>التاريخ</th>
                <th>البيان</th>
                <th>مدين</th>
                <th>دائن</th>
                <th>الرصيد</th>
            </tr>
        </thead>
       <tbody>
    @php $counter = 1; $balance = $openingBalance; @endphp
    <tr class="opening-row">
        <td>{{ $counter++ }}</td><td>—</td><td>—</td>
        <td><strong>الرصيد الافتتاحي</strong></td>
        <td class="debit">{{ $balance > 0 ? number_format($balance,2) : '-' }}</td>
        <td class="credit">{{ $balance < 0 ? number_format(abs($balance),2) : '-' }}</td>
        <td>{{ number_format(abs($balance),2) }} {{ $balance >= 0 ? 'مدين' : 'دائن' }}</td>
    </tr>

    @foreach($transactions as $trans)
        @php
            $balance += $trans->debit - $trans->credit;
            $entry = $trans->journalEntry;

            // ✅ نفس المنطق الموجود في ledger.blade.php
            $detailedDescription = null;

            if ($entry->source_type === 'App\Models\Booking' && $entry->source_id) {
                $booking = $bookings[$entry->source_id] ?? null;
                if ($booking) {
                    $checkIn  = $booking->check_in  ? \Carbon\Carbon::parse($booking->check_in)->format('d-m-y')  : '—';
                    $checkOut = $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d-m-y') : '—';
                    $detailedDescription =
                        "{$booking->id} {$booking->client_name} - " . ($booking->hotel->name ?? '—') . "\n" .
                        "{$booking->rooms} غرفة : {$checkIn} → {$checkOut}\n" .
                        number_format($booking->sale_price, 2) . " " . ($booking->currency === 'KWD' ? 'د.ك' : 'ر.س');
                }
            } elseif ($entry->source_type === 'App\Models\Availability' && $entry->source_id) {
                $availability = $availabilities[$entry->source_id] ?? null;
                if ($availability) {
                    $startDate = $availability->start_date ? \Carbon\Carbon::parse($availability->start_date)->format('d-m-y') : '—';
                    $endDate   = $availability->end_date   ? \Carbon\Carbon::parse($availability->end_date)->format('d-m-y')   : '—';
                    $roomsSummary = $availability->availabilityRoomTypes->map(function($rt) {
                        return ($rt->roomType->room_type_name ?? '—') . ': ' . $rt->allotment . ' غرفة بـ ' . number_format($rt->cost_price, 2);
                    })->implode(' | ');
                    $detailedDescription =
                        "{$availability->id} - " . ($availability->hotel->name ?? '—') . "\n" .
                        "{$startDate} → {$endDate}\n" .
                        $roomsSummary;
                }
            }

            $descriptionText = $detailedDescription ?: ($trans->description ?: '—');
        @endphp
        <tr>
            <td>{{ $counter++ }}</td>
            <td>{{ $entry->id ?? '—' }}</td>
            <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
            <td style="white-space: pre-line;">{{ $descriptionText }}</td>
            <td class="debit">{{ $trans->debit > 0 ? number_format($trans->debit,2) : '-' }}</td>
            <td class="credit">{{ $trans->credit > 0 ? number_format($trans->credit,2) : '-' }}</td>
            <td>{{ number_format(abs($balance),2) }} {{ $balance >= 0 ? 'مدين' : 'دائن' }}</td>
        </tr>
    @endforeach

    <tr style="background:#eef2ff; font-weight:bold;">
        <td colspan="6">الرصيد النهائي</td>
        <td>{{ number_format(abs($balance),2) }} {{ $balance >= 0 ? 'مدين' : 'دائن' }}</td>
    </tr>
</tbody>
    </table>
    <div class="footer">
        تم الإنشاء بواسطة النظام – {{ now()->format('Y-m-d H:i') }}
    </div>
</div>
</body>
</html>