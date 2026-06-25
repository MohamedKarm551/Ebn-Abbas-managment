<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: cairo, sans-serif; direction: rtl; font-size: 11px; }
    h2 { text-align: center; color: #1d4ed8; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th { background: #1d4ed8; color: white; padding: 8px; text-align: center; }
    td { padding: 6px 8px; border: 1px solid #e5e7eb; text-align: center; vertical-align: middle; }
    tr:nth-child(even) { background: #f9fafb; }
    .opening { background: #fef3c7; font-weight: bold; }
    .booking-details { font-size: 10px; line-height: 1.4; }
    .booking-details .line1 { font-weight: 600; color: #111827; }
    .booking-details .line2 { color: #6b7280; }
    .booking-details .line3 { color: #059669; font-weight: 600; }
</style>
</head>
<body>
<h2>كشف حساب: {{ $account->name }} ({{ $account->code }})</h2>
<p style="text-align:center; color:#6b7280;">تاريخ الطباعة: {{ now()->format('Y-m-d') }}</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>التاريخ</th>
            <th>المرجع</th>
            <th>البيان</th>
            <th>مدين</th>
            <th>دائن</th>
            <th>الرصيد</th>
        </tr>
    </thead>
    <tbody>
        <tr class="opening">
            <td colspan="4">الرصيد الافتتاحي</td>
            <td>-</td>
            <td>-</td>
            <td>{{ number_format($openingBalance, 2) }}</td>
        </tr>
        @php $balance = $openingBalance; @endphp
        @foreach($transactions as $i => $tx)
            @php
                $balance += ($tx->debit - $tx->credit);
                $entry = $tx->journalEntry;

                // === نفس منطق البيان الموجود في ledger.blade.php ===
                $detailedDescription = null;
                if ($entry && $entry->source_type === 'App\Models\Booking' && $entry->source_id) {
                    $booking = $bookings[$entry->source_id] ?? null;
                    if ($booking) {
                        $trip = $booking->trip;
                        $dateFrom = $trip?->from ? \Carbon\Carbon::parse($trip->from)->format('d-m-y') : '—';
                        $dateTo   = $trip?->to   ? \Carbon\Carbon::parse($trip->to)->format('d-m-y')   : '—';

                        $detailedDescription = [
                            'line1' => "حجز # {$trip->name} - {$booking->id} - {$booking->client_name}",
                            'line2' => "الرحلة: {$dateFrom} → {$dateTo}",
                            'line3' => number_format($booking->base_price, 2) . " ر.س",
                        ];
                    }
                }

                // إذا لم تكن تفاصيل الحجز موجودة، نأخذ الوصف العادي من الحركة
                $descriptionText = $detailedDescription 
                    ? $detailedDescription['line1'] . "\n" . $detailedDescription['line2'] . "\n" . $detailedDescription['line3']
                    : ($tx->description ?? '-');
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $entry?->entry_date?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ $entry?->reference ?? '-' }}</td>
                <td style="text-align:right;">
                    @if($detailedDescription)
                        <div class="booking-details">
                            <div class="line1">{{ $detailedDescription['line1'] }}</div>
                            <div class="line2">{{ $detailedDescription['line2'] }}</div>
                            <div class="line3">{{ $detailedDescription['line3'] }}</div>
                        </div>
                    @else
                        {{ $descriptionText }}
                    @endif
                </td>
                <td>{{ $tx->debit > 0 ? number_format($tx->debit, 2) : '-' }}</td>
                <td>{{ $tx->credit > 0 ? number_format($tx->credit, 2) : '-' }}</td>
                <td style="font-weight:bold; color:{{ $balance >= 0 ? '#059669' : '#dc2626' }}">
                    {{ number_format(abs($balance), 2) }}
                    {{ $balance >= 0 ? 'مدين' : 'دائن' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>