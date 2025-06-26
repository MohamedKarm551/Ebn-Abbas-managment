<table class="table table-bordered table-striped" id="hotelsTableContent">
    <thead>
        <tr>
            <th>الفندق</th>
            <th>عدد الحجوزات</th>
            <th>إجمالي المستحق</th>
            <th>العمليات</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($hotelsReport as $hotel)
            <tr>
                <td>{{ $loop->iteration }}. {{ $hotel->name }}</td>
                <td>{{ $hotel->bookings_count }}</td>
                <td>
                    @php
                        // ✅ استخدام القيم المحسوبة للفندق مع fallback للقيم القديمة
                        $dueByCurrency = $hotel->total_due_by_currency ?? [
                            'SAR' => $hotel->total_due ?? 0,
                            'KWD' => 0
                        ];
                    @endphp

                    {{-- 🔄 عرض المستحق لكل عملة --}}
                    @foreach ($dueByCurrency as $currency => $amount)
                        @if ($amount > 0)
                            <div class="mb-1">
                                <strong>{{ number_format($amount, 2) }}</strong>
                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                            </div>
                        @endif
                    @endforeach

                    {{-- إذا لم توجد مستحقات --}}
                    @if (empty($dueByCurrency) || 
                        (($dueByCurrency['SAR'] ?? 0) == 0 && ($dueByCurrency['KWD'] ?? 0) == 0))
                        <span class="text-muted">0.00 ريال</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('reports.hotel.bookings', $hotel->id) }}"
                        class="btn btn-info btn-sm">عرض الحجوزات</a>
                </td>
            </tr>
        @endforeach
    </tbody>
    
    {{-- 🧮 صف الإجمالي مع دعم العملات --}}
    @if($hotelsReport->count() > 0)
    <tfoot>
        <tr class="table-secondary fw-bold">
            <td class="text-center">الإجمالي</td>
            <td class="text-center">
                {{ $hotelsReport->sum('bookings_count') }}
            </td>
            <td>
                @php
                    // ✅ حساب الإجمالي من القيم المحسوبة للفنادق
                    $totalHotelDueByCurrency = ['SAR' => 0, 'KWD' => 0];
                    
                    foreach ($hotelsReport as $hotel) {
                        $dueByCurrency = $hotel->total_due_by_currency ?? [
                            'SAR' => $hotel->total_due ?? 0,
                            'KWD' => 0
                        ];

                        foreach ($dueByCurrency as $currency => $amount) {
                            $totalHotelDueByCurrency[$currency] += $amount;
                        }
                    }
                @endphp

                {{-- عرض الإجمالي لكل عملة --}}
                @foreach ($totalHotelDueByCurrency as $currency => $amount)
                    @if ($amount > 0)
                        <div class="mb-1">
                            <strong>{{ number_format($amount, 2) }}</strong>
                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                        </div>
                    @endif
                @endforeach

                {{-- إذا لم توجد مستحقات --}}
                @if ($totalHotelDueByCurrency['SAR'] == 0 && $totalHotelDueByCurrency['KWD'] == 0)
                    <span class="text-muted">0.00 ريال</span>
                @endif
            </td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>