{{-- 📋 قوائم أعلى الشركات والجهات --}}
<div class="row">

    {{-- أعلى 5 شركات عليها مبالغ --}}
    <div class="col-md-6 mb-3">
        <div class="h-100">
            <div class="card-body">
                <h5 class="card-title text-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    أعلى 5 شركات عليها مبالغ
                    <button class="btn btn-sm btn-outline-danger float-end" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseCompanies" aria-expanded="false" aria-controls="collapseCompanies">
                        عرض/إخفاء
                    </button>
                </h5>
                <div class="collapse " id="collapseCompanies">
                    @php
                        $topCompanies = $companiesReport->take(5);
                    @endphp
                    <ul class="list-unstyled mb-2 small">
                        @forelse ($topCompanies as $company)
                            @php
                                $remainingByCurrency = $company->remaining_bookings_by_currency ?? [];
                                $totalRemaining = collect($remainingByCurrency)->sum();
                            @endphp
                            @if ($totalRemaining > 0)
                                <li class="mb-1">
                                    <strong>{{ $company->name }}:</strong>
                                    @foreach ($remainingByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            <span class="badge bg-danger">
                                                {{ number_format($amount, 0) }}
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                            </span>
                                        @endif
                                    @endforeach
                                </li>
                            @endif
                        @empty
                            <li class="text-muted">لا توجد شركات عليها مبالغ متبقية حاليًا.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- أعلى 5 جهات لها مبالغ --}}
    <div class="col-md-6 mb-3">
        <div class="h-100">
            <div class="card-body">
                <h5 class="card-title text-warning">
                    <i class="fas fa-money-check-alt me-1"></i>
                    أعلى 5 جهات لها مبالغ
                    <button class="btn btn-sm btn-outline-warning float-end" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseAgents" aria-expanded="false" aria-controls="collapseAgents">
                        عرض/إخفاء
                    </button>
                </h5>
                <div class="collapse " id="collapseAgents">
                    @php
                        $topAgents = $agentsReport->take(5);
                    @endphp
                    <ul class="list-unstyled mb-2 small">
                        @forelse ($topAgents as $agent)
                            @php
                                $remainingByCurrency = $agent->remaining_by_currency ?? [];
                                $totalRemaining = collect($remainingByCurrency)->sum();
                            @endphp
                            @if ($totalRemaining > 0)
                                <li class="mb-1">
                                    <strong>{{ $agent->name }}:</strong>
                                    @foreach ($remainingByCurrency as $currency => $amount)
                                        @if ($amount > 0)
                                            <span class="badge bg-warning">
                                                {{ number_format($amount, 0) }}
                                                {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                            </span>
                                        @endif
                                    @endforeach
                                </li>
                            @endif
                        @empty
                            <li class="text-muted">لا توجد جهات لها مبالغ متبقية حاليًا.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
