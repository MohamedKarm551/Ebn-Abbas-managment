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
                        $dueByCurrency = $agent->computed_total_due_by_currency ?? 
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
                                $discountsByCurrency[$currency] = abs($payments->where('amount', '<', 0)->sum('amount'));
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
                <td>
                    @php
                        // ✅ استخدام القيم المحسوبة للمتبقي مع fallback
                        $remainingAgentByCurrency = $agent->computed_remaining_by_currency ?? 
                                                   ($agent->remaining_by_currency ?? [
                                                       'SAR' => $agent->remaining_amount ?? 0,
                                                   ]);
                    @endphp

                    {{-- 🔄 عرض المتبقي لكل عملة --}}
                    @foreach ($remainingAgentByCurrency as $currency => $amount)
                        @if ($amount != 0)
                            {{-- 🎨 تلوين المتبقي: أحمر للموجب (مدين لنا)، أبيض مع خلفية حمراء للسالب (دفعنا زيادة) --}}
                            <span class="{{ $amount > 0 ? '' : 'badge bg-danger text-white' }}">
                                {{ number_format($amount, 2) }}
                            </span>
                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>

                            {{-- 📝 ملاحظة إذا كان المبلغ سالب (دفعنا زيادة) --}}
                            @if ($amount < 0)
                                <small class="text-muted">(دفعنا زيادة)</small>
                            @endif
                        @endif
                    @endforeach
                </td>

                {{-- ⚙️ عمود العمليات --}}
                <td>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; max-width: 280px;">
                        <a href="{{ route('reports.agent.bookings', $agent->id) }}"
                            class="btn btn-info btn-sm">عرض الحجوزات</a>

                        <button type="button" class="btn btn-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#agentPaymentModal{{ $agent->id }}">
                            تسجيل دفعة
                        </button>

                        <button type="button" class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#agentDiscountModal{{ $agent->id }}">
                            تطبيق خصم
                        </button>

                        <a href="{{ route('reports.agent.payments', $agent->id) }}"
                            class="btn btn-primary btn-sm">كشف حساب</a>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
    
    {{-- 🧮 صف الإجمالي --}}
    @if($agentsReport->count() > 0)
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
                        $dueByCurrency = $agent->computed_total_due_by_currency ?? 
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
                            <strong class="text-success">{{ number_format($totalAgentPaidByCurrency[$currency] ?? 0, 2) }}</strong>
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
                        $remainingByCurrency = $agent->computed_remaining_by_currency ?? 
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