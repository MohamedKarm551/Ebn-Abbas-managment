{{-- 📈 رسم بياني للدينار --}}
@php
    // التحقق من وجود بيانات الدينار الكويتي من المتغيرات المتوفرة في الكنترولر
    $hasKwdData = false;
    $kwdTotal = 0;
    
    // 1. فحص بيانات صافي الرصيد بالدينار الكويتي
    if (isset($netBalancesKWD) && !empty($netBalancesKWD)) {
        foreach ($netBalancesKWD as $balance) {
            if ($balance != 0) {
                $hasKwdData = true;
                $kwdTotal += $balance;
                break; // مجرد العثور على قيمة غير صفر يكفي
            }
        }
    }
    
    // 2. إذا لم نجد بيانات في الرسم البياني، نفحص الإجماليات
    if (!$hasKwdData) {
        // فحص إجماليات الشركات بالدينار
        if (isset($totalDueFromCompaniesByCurrency['KWD']) && $totalDueFromCompaniesByCurrency['KWD'] > 0) {
            $hasKwdData = true;
            $kwdTotal += $totalDueFromCompaniesByCurrency['KWD'];
        }
        
        // فحص إجماليات الوكلاء بالدينار
        if (isset($totalDueToAgentsByCurrency['KWD']) && $totalDueToAgentsByCurrency['KWD'] > 0) {
            $hasKwdData = true;
            $kwdTotal += $totalDueToAgentsByCurrency['KWD'];
        }
        
        // فحص صافي الربح بالدينار
        if (isset($netProfitByCurrency['KWD']) && $netProfitByCurrency['KWD'] != 0) {
            $hasKwdData = true;
            $kwdTotal += abs($netProfitByCurrency['KWD']);
        }
    }
@endphp
@if($hasKwdData)
{{-- 📈 رسم بياني إضافي للدينار محسن --}}
<div class="mb-4 col-6 m-auto">
    <div class="collapse-card">
        <button class="collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNetBalanceKWD"
            aria-expanded="false">
            <div class="collapse-btn-content">
                <div class="collapse-icon-wrapper">
                    <i class="fas fa-chart-area collapse-icon"></i>
                </div>
                <div class="collapse-text-content">
                    <span class="collapse-title">صافي الرصيد بالدينار الكويتي</span>
                    <span class="collapse-subtitle">عرض تفصيلي للعملة الكويتية</span>
                </div>
            </div>
            <div class="collapse-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </button>

        <div class="collapse" id="collapseNetBalanceKWD">
            <div class="collapse-content">
                <div class="chart-container-secondary">
                    <canvas id="netBalanceKWDChart" class="secondary-chart"></canvas>
                </div>

                {{-- KWD Stats --}}
                <div class="kwd-stats mt-3"
                    style="background: rgba(16, 185, 129, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.1);">
                    <div class="kwd-stat-item"
                        style="color: #1f2937; background: rgba(255, 255, 255, 0.8); padding: 8px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                        <i class="fas fa-coins" style="color: #10b981;"></i>
                        <span>إجمالي بالدينار: <strong id="kwdTotal" style="color: #059669;">0.00 د.ك</strong></span>
                    </div>
                    <div class="kwd-stat-item"
                        style="color: #1f2937; background: rgba(255, 255, 255, 0.8); padding: 8px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                        <i class="fas fa-percentage" style="color: #10b981;"></i>
                        <span>نسبة التغيير: <strong id="kwdChange" style="color: #059669;">0.0%</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
    {{-- رسالة اختيارية عندما لا توجد بيانات بالدينار --}}
    {{-- <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i>
        لا توجد معاملات بالدينار الكويتي لعرضها
    </div> --}}
@endif
