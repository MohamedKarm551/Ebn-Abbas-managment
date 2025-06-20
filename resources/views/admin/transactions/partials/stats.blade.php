
        <!-- Quick Stats Summary -->
        <div class="row mb-4">
            @foreach ($totals as $total)
                <div class="col-xl-3 col-md-6 col-12 mb-4">
                    <div
                        class="card stats-card border-start-{{ $total->currency == 'SAR' ? 'primary' : ($total->currency == 'KWD' ? 'success' : ($total->currency == 'EGP' ? 'warning' : 'info')) }} shadow-sm h-100">
                        <div class="card-body p-3">
                            <!-- Header with Currency -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div
                                    class="text-xs font-weight-bold text-{{ $total->currency == 'SAR' ? 'primary' : ($total->currency == 'KWD' ? 'success' : ($total->currency == 'EGP' ? 'warning' : 'info')) }} text-uppercase">
                                    صافي الرصيد - {{ $total->currency }}
                                </div>
                                <div class="currency-icon">
                                    <i
                                        class="fas fa-coins fa-lg text-{{ $total->currency == 'SAR' ? 'primary' : ($total->currency == 'KWD' ? 'success' : ($total->currency == 'EGP' ? 'warning' : 'info')) }}"></i>
                                </div>
                            </div>

                            <!-- Main Balance -->
                            <div class="balance-display mb-3">
                                @php
                                    $isPositive = $total->net_balance >= 0;
                                    $symbols = [
                                        'SAR' => 'ر.س',
                                        'KWD' => 'د.ك',
                                        'EGP' => 'ج.م',
                                        'USD' => '$',
                                        'EUR' => '€',
                                    ];
                                @endphp
                                <div class="h4 mb-0 font-weight-bold">
                                    <span class="balance-amount {{ $isPositive ? 'text-success' : 'text-danger' }}">
                                        {{ $isPositive ? '+' : '' }}{{ number_format($total->net_balance, 2) }}
                                    </span>
                                    <small class="text-muted ms-1 currency-symbol">
                                        {{ $symbols[$total->currency] ?? $total->currency }}
                                    </small>
                                </div>
                            </div>

                            <!-- Transaction Breakdown -->
                            <div class="transaction-breakdown">
                                <!-- Deposits -->
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="transaction-type">
                                        <i class="fas fa-arrow-up text-success me-2"></i>
                                        <span class="text-xs text-muted">إيداعات</span>
                                    </div>
                                    <div class="transaction-amount text-success font-weight-bold text-xs">
                                        {{ number_format($total->total_deposits, 0) }}
                                    </div>
                                </div>

                                <!-- Withdrawals -->
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="transaction-type">
                                        <i class="fas fa-arrow-down text-danger me-2"></i>
                                        <span class="text-xs text-muted">سحوبات</span>
                                    </div>
                                    <div class="transaction-amount text-danger font-weight-bold text-xs">
                                        {{ number_format($total->total_withdrawals, 0) }}
                                    </div>
                                </div>

                                <!-- Transfers -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="transaction-type">
                                        <i class="fas fa-exchange-alt text-warning me-2"></i>
                                        <span class="text-xs text-muted">تحويلات</span>
                                    </div>
                                    <div class="transaction-amount text-warning font-weight-bold text-xs">
                                        {{ number_format($total->total_transfers ?? 0, 0) }}
                                    </div>
                                </div>

                                <!-- Calculation Formula -->
                                <div class="calculation-formula">
                                    <hr class="my-2 border-top">
                                    <div class="text-center">
                                        <small class="text-muted calculation-text">
                                            <i class="fas fa-calculator me-1"></i>
                                            <span
                                                class="text-success">{{ number_format($total->total_deposits, 0) }}</span>
                                            <span class="mx-1">-</span>
                                            <span
                                                class="text-danger">{{ number_format($total->total_withdrawals, 0) }}</span>
                                            <span class="mx-1">-</span>
                                            <span
                                                class="text-warning">{{ number_format($total->total_transfers ?? 0, 0) }}</span>
                                            <span class="mx-1">=</span>
                                            <strong
                                                class="result-amount {{ $isPositive ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($total->net_balance, 0) }}
                                            </strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($totals->isEmpty())
                <div class="col-12">
                    <div class="alert alert-info text-center border-0 shadow-sm">
                        <div class="empty-state">
                            <i class="fas fa-info-circle fa-2x text-info mb-3"></i>
                            <h5 class="text-info mb-2">لا توجد معاملات مالية</h5>
                            <p class="text-muted mb-0">ابدأ بإضافة معاملتك الأولى لرؤية الإحصائيات هنا</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

