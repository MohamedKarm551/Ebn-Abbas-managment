{{-- 📋 جدول أعلى الشركات والجهات --}}
<div class="row justify-content-center mb-4">
    <div class="col-lg-8 col-md-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" 
                 style="background: linear-gradient(135deg, #dc3545, #fd7e14);">
                <h6 class="mb-0 text-black">
                    <i class="fas fa-chart-bar me-2"></i>
                    أعلى الشركات والجهات
                </h6>
                <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseTopDetails" aria-expanded="false" aria-controls="collapseTopDetails">
                    <i class="fas fa-eye text-black"></i>
                </button>
            </div>
            
            <div class="collapse" id="collapseTopDetails">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="10%">#</th>
                                    <th width="45%">الاسم</th>
                                    <th class="text-center" width="25%">المبلغ</th>
                                    <th class="text-center" width="20%">النوع</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- الشركات --}}
                                @php
                                    $topCompanies = $companiesReport->take(5);
                                    $counter = 1;
                                @endphp
                                @forelse ($topCompanies as $company)
                                    @php
                                        $remainingByCurrency = $company->remaining_bookings_by_currency ?? [];
                                        $totalRemaining = collect($remainingByCurrency)->sum();
                                    @endphp
                                    @if ($totalRemaining > 0)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-danger rounded-pill">{{ $counter++ }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-danger">{{ $company->name }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @foreach ($remainingByCurrency as $currency => $amount)
                                                    @if ($amount > 0)
                                                        <span class="badge bg-danger me-1 top-details">
                                                            {{ number_format($amount, 0) }}
                                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-outline-danger text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>شركة
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                @endforelse

                                {{-- الجهات --}}
                                @php
                                    $topAgents = $agentsReport->take(5);
                                    $counter = 1;
                                @endphp
                                @forelse ($topAgents as $agent)
                                    @php
                                        $remainingByCurrency = $agent->remaining_by_currency ?? [];
                                        $totalRemaining = collect($remainingByCurrency)->sum();
                                    @endphp
                                    @if ($totalRemaining > 0)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-warning rounded-pill">{{ $counter++ }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-warning">{{ $agent->name }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @foreach ($remainingByCurrency as $currency => $amount)
                                                    @if ($amount > 0)
                                                        <span class="badge bg-warning me-1">
                                                            {{ number_format($amount, 0) }}
                                                            {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-outline-warning text-warning">
                                                    <i class="fas fa-money-check-alt me-1"></i>جهة
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                @endforelse

                                {{-- رسالة في حالة عدم وجود بيانات --}}
                                @if($topCompanies->where(function($company) {
                                    return collect($company->remaining_bookings_by_currency ?? [])->sum() > 0;
                                })->isEmpty() && $topAgents->where(function($agent) {
                                    return collect($agent->remaining_by_currency ?? [])->sum() > 0;
                                })->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            لا توجد مبالغ متبقية حاليًا
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

