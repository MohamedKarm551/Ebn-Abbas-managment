
<div class="row mb-4">
    @foreach($totals as $total)
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card border-start-{{ $total->currency == 'SAR' ? 'primary' : ($total->currency == 'KWD' ? 'success' : ($total->currency == 'EGP' ? 'warning' : 'info')) }} shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-{{ $total->currency == 'SAR' ? 'primary' : ($total->currency == 'KWD' ? 'success' : ($total->currency == 'EGP' ? 'warning' : 'info')) }} text-uppercase mb-1">
                            صافي الرصيد - {{ $total->currency }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            @php
                                $isPositive = $total->net_balance >= 0;
                                $symbols = ['SAR' => 'ر.س', 'KWD' => 'د.ك', 'EGP' => 'ج.م', 'USD' => '$', 'EUR' => '€'];
                            @endphp
                            <span class="{{ $isPositive ? 'text-success' : 'text-danger' }}">
                                {{ $isPositive ? '+' : '' }}{{ number_format($total->net_balance, 2) }}
                            </span>
                            <small class="text-muted ms-1">{{ $symbols[$total->currency] ?? $total->currency }}</small>
                        </div>
                        <div class="row text-xs text-muted mt-2">
                            <div class="col-6 text-success">
                                <i class="fas fa-arrow-up text-success me-1"></i>
                                إيداعات: {{ number_format($total->total_deposits, 0) }}
                            </div>
                            <div class="col-6 text-danger">
                                <i class="fas fa-arrow-down text-danger me-1"></i>
                                سحوبات: {{ number_format($total->total_withdrawals, 0) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-coins fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @if($totals->isEmpty())
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            لا توجد معاملات مالية بعد. ابدأ بإضافة معاملتك الأولى!
        </div>
    </div>
    @endif
</div>