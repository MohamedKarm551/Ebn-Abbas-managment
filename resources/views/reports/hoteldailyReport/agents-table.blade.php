<table class="table table-bordered table-striped" id="agentsTableContent">
    <thead>
        <tr>
            <th>جهة الحجز</th>
            <th>عدد الحجوزات</th>
            <th>إجمالي المستحق</th>
            <th>المدفوع</th>
            <th>المتبقي</th>
            <th>العمليات</th>
        </tr>
    </thead>
    <tbody>
        {{-- 🔄 حلقة عرض جميع جهات الحجز/الوكلاء --}}
        @foreach ($agentsReport as $agent)
            <tr>
                {{-- 📝 عمود اسم جهة الحجز مع رقم ترتيبي --}}
                <td>
                    {{ $loop->iteration }}. {{ $agent->name }}
                    {{-- يمكنك إضافة badge التعديل هنا إذا أردت --}}
                </td>

                {{-- 📊 عمود عدد الحجوزات --}}
                <td>{{ $agent->bookings_count }}</td>

                {{-- 💰 عمود إجمالي المستحق حسب العملة --}}
                <td>
                    @php
                        // ✅ استخدام القيم المحسوبة الجديدة للوكلاء مع fallback للقيم القديمة
                        $dueByCurrency =
                            $agent->computed_total_due_by_currency ??
                            ($agent->total_due_by_currency ?? [
                                'SAR' => $agent->total_due ?? 0,
                            ]);
                    @endphp

                    {{-- 🔄 عرض المستحق لكل عملة --}}
                    @foreach ($dueByCurrency as $currency => $amount)
                        @if ($amount > 0)
                            {{ number_format($amount, 2) }}
                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                        @endif
                    @endforeach
                </td>

                {{-- 💵 عمود المدفوعات والخصومات --}}
                <td>
                    @php
                        // ✅ استخدام القيم المحسوبة الخاصة بهذا الوكيل تحديدًا
                        $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
                        $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];

                        // إذا لم تكن محسوبة، استخدم الطريقة القديمة كـ fallback
                        if (empty($paidByCurrency) && $agent->payments) {
                            $agentPaymentsGrouped = $agent->payments->groupBy('currency');

                            foreach ($agentPaymentsGrouped as $currency => $payments) {
                                $paidByCurrency[$currency] = $payments->where('amount', '>=', 0)->sum('amount');
                                $discountsByCurrency[$currency] = abs(
                                    $payments->where('amount', '<', 0)->sum('amount'),
                                );
                            }
                        }
                    @endphp

                    {{-- عرض المدفوعات بالريال السعودي --}}
                    @if (isset($paidByCurrency['SAR']) && ($paidByCurrency['SAR'] > 0 || ($discountsByCurrency['SAR'] ?? 0) > 0))
                        <div class="mb-1">
                            <strong class="text-success">{{ number_format($paidByCurrency['SAR'], 2) }}</strong> ريال
                            @if (($discountsByCurrency['SAR'] ?? 0) > 0)
                                <br><small class="text-warning">
                                    <i class="fas fa-minus-circle me-1"></i>
                                    خصومات: {{ number_format($discountsByCurrency['SAR'], 2) }} ريال
                                </small>
                            @endif
                        </div>
                    @endif

                    {{-- عرض المدفوعات بالدينار الكويتي --}}
                    @if (isset($paidByCurrency['KWD']) && ($paidByCurrency['KWD'] > 0 || ($discountsByCurrency['KWD'] ?? 0) > 0))
                        <div class="mb-1">
                            <strong class="text-success">{{ number_format($paidByCurrency['KWD'], 2) }}</strong> دينار
                            @if (($discountsByCurrency['KWD'] ?? 0) > 0)
                                <br><small class="text-warning">
                                    <i class="fas fa-minus-circle me-1"></i>
                                    خصومات: {{ number_format($discountsByCurrency['KWD'], 2) }} دينار
                                </small>
                            @endif
                        </div>
                    @endif

                    {{-- إذا لم توجد مدفوعات --}}
                    @if (empty($paidByCurrency) ||
                            ((!isset($paidByCurrency['SAR']) || $paidByCurrency['SAR'] == 0) &&
                                (!isset($paidByCurrency['KWD']) || $paidByCurrency['KWD'] == 0)))
                        <span class="text-muted">0 ريال</span>
                    @endif
                </td>

                {{-- 📉 عمود المتبقي --}}
                <td class="align-top">

                    @php
                        // المتبقي لكل عملة (fallback)
                        $remainingAgentByCurrency =
                            $agent->computed_remaining_by_currency ??
                            ($agent->remaining_by_currency ?? ['SAR' => $agent->remaining_amount ?? 0]);

                        // رصيد اليوم
                        $cb = $agent->current_balance ?? [];
                        $bal = $cb['balance'] ?? 0;
                        $enteredDue = $cb['entered_due'] ?? 0;
                        $effectivePaid = $cb['effective_paid'] ?? 0;

                        // حالة الرصيد
                        $accent = $bal > 0 ? 'danger' : ($bal < 0 ? 'success' : 'secondary');
                        $statusTxt = $bal > 0 ? 'مستحق' : ($bal < 0 ? 'دفع زائد' : 'مغلق');
                        $netAbs = number_format(abs($bal), 2);
                    @endphp

                    {{-- موجز المتبقي لكل عملة (chips بدون .badge) --}}
                    @php $hasAnyCurrency = collect($remainingAgentByCurrency)->filter(fn($v) => $v != 0)->isNotEmpty(); @endphp
                    @if ($hasAnyCurrency)
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach ($remainingAgentByCurrency as $currency => $amount)
                                @continue($amount == 0)
                                <span
                                    class="d-inline-flex align-items-center bg-{{ $amount > 0 ? 'danger' : 'success' }} text-white rounded-pill px-2 py-1 small lh-sm text-nowrap m-auto d-block text-center">
                                    <strong dir="ltr" class="ms-1">{{ number_format(abs($amount), 2) }}</strong>
                                    <span>{{ $currency === 'SAR' ? 'ر. سعودي' : 'دينار' }}</span>
                                    @if ($amount < 0)
                                        <span class="ms-1 opacity-75">(دفعنا زيادة)</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- بطاقة رصيد اليوم --}}
                    <div
                        class="card bg-body-tertiary border-0 shadow-sm rounded-3 border-start border-4 border-{{ $accent }}">
                        <div class="card-body p-2">

                            {{-- العنوان + حالة مختصرة (chip بدون .badge) --}}
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-scale-balanced"></i>
                                    <span class="fw-semibold small">رصيد اليوم (balance)</span>
                                </div>
                                <span
                                    class="d-inline-flex align-items-center bg-{{ $accent }} text-white rounded-pill px-2 py-1 small lh-sm">
                                    {{ $statusTxt }}
                                </span>
                            </div>

                            {{-- قائمة Metrics (واضحة، بدون دوائر) --}}
                            <ul class="list-group list-group-flush">

                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                                    <span class="text-secondary">دخلت</span>
                                    <span class="fw-semibold text-primary text-nowrap" dir="ltr">
                                        {{ number_format($enteredDue, 2) }} ر.
                                    </span>
                                </li>

                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                                    <span class="text-secondary">مدفوع</span>
                                    <span class="fw-semibold text-info text-nowrap" dir="ltr">
                                        {{ number_format($effectivePaid, 2) }} ر.
                                    </span>
                                </li>

                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                                    <span
                                        class="text-secondary">{{ $bal > 0 ? 'مستحق' : ($bal < 0 ? 'دفع زائد' : 'الصافي') }}</span>
                                    <span class="fw-semibold text-{{ $accent }} text-nowrap" dir="ltr">
                                        {{ $bal == 0 ? '0.00' : $netAbs }} ر.
                                    </span>
                                </li>

                            </ul>
                        </div>
                    </div>

                </td>









                {{-- ⚙️ عمود العمليات --}}
                <td>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; max-width: 280px;">
                        <a href="{{ route('reports.agent.bookings', $agent->id) }}" class="btn btn-info btn-sm">عرض
                            الحجوزات</a>

                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#agentPaymentModal{{ $agent->id }}">
                            تسجيل دفعة
                        </button>

                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                            data-bs-target="#agentDiscountModal{{ $agent->id }}">
                            تطبيق خصم
                        </button>

                        <a href="{{ route('reports.agent.payments', $agent->id) }}" class="btn btn-primary btn-sm">كشف
                            حساب</a>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>

    {{-- 🧮 صف الإجمالي --}}
    @if ($agentsReport->count() > 0)
        <tfoot>
            <tr class="table-secondary fw-bold">
                <td class="text-center">الإجمالي</td>
                <td class="text-center">
                    {{ $agentsReport->sum('bookings_count') }}
                </td>
                <td>
                    @php
                        // ✅ حساب الإجمالي من القيم المحسوبة للوكلاء
                        $totalAgentDueByCurrency = ['SAR' => 0, 'KWD' => 0];
                        foreach ($agentsReport as $agent) {
                            $dueByCurrency =
                                $agent->computed_total_due_by_currency ??
                                ($agent->total_due_by_currency ?? [
                                    'SAR' => $agent->total_due ?? 0,
                                ]);

                            foreach ($dueByCurrency as $currency => $amount) {
                                $totalAgentDueByCurrency[$currency] += $amount;
                            }
                        }
                    @endphp
                    @foreach ($totalAgentDueByCurrency as $currency => $amount)
                        @if ($amount > 0)
                            {{ number_format($amount, 2) }}
                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                        @endif
                    @endforeach
                </td>
                <td>
                    @php
                        // ✅ حساب إجمالي المدفوعات من القيم المحسوبة للوكلاء
                        $totalAgentPaidByCurrency = ['SAR' => 0, 'KWD' => 0];
                        $totalAgentDiscountsByCurrency = ['SAR' => 0, 'KWD' => 0];

                        foreach ($agentsReport as $agent) {
                            $paidByCurrency = $agent->computed_total_paid_by_currency ?? [];
                            $discountsByCurrency = $agent->computed_total_discounts_by_currency ?? [];

                            foreach ($paidByCurrency as $currency => $amount) {
                                $totalAgentPaidByCurrency[$currency] += $amount;
                            }
                            foreach ($discountsByCurrency as $currency => $amount) {
                                $totalAgentDiscountsByCurrency[$currency] += $amount;
                            }
                        }
                    @endphp

                    @foreach (['SAR', 'KWD'] as $currency)
                        @if (($totalAgentPaidByCurrency[$currency] ?? 0) > 0 || ($totalAgentDiscountsByCurrency[$currency] ?? 0) > 0)
                            <div class="mb-1">
                                <strong
                                    class="text-success">{{ number_format($totalAgentPaidByCurrency[$currency] ?? 0, 2) }}</strong>
                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                @if (($totalAgentDiscountsByCurrency[$currency] ?? 0) > 0)
                                    <br><small class="text-warning">
                                        <i class="fas fa-minus-circle me-1"></i>
                                        خصومات: {{ number_format($totalAgentDiscountsByCurrency[$currency], 2) }}
                                        {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                    </small>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </td>
                <td>
                    @php
                        // ✅ حساب إجمالي المتبقي من القيم المحسوبة للوكلاء
                        $totalAgentRemainingByCurrency = ['SAR' => 0, 'KWD' => 0];

                        foreach ($agentsReport as $agent) {
                            $remainingByCurrency =
                                $agent->computed_remaining_by_currency ??
                                ($agent->remaining_by_currency ?? [
                                    'SAR' => $agent->remaining_amount ?? 0,
                                ]);

                            foreach ($remainingByCurrency as $currency => $amount) {
                                $totalAgentRemainingByCurrency[$currency] += $amount;
                            }
                        }
                    @endphp

                    @foreach ($totalAgentRemainingByCurrency as $currency => $amount)
                        @if ($amount != 0)
                            <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $amount > 0 ? '+' : '' }}{{ number_format($amount, 2) }}
                            </span>
                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                        @endif
                    @endforeach
                </td>
                <td></td>
            </tr>
        </tfoot>
    @endif
</table>
