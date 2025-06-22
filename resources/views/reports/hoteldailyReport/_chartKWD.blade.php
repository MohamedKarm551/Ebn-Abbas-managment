
        {{-- 📈 رسم بياني للدينار --}}

        {{-- 📈 رسم بياني إضافي للدينار محسن --}}
        <div class="mb-4">
            <div class="collapse-card">
                <button class="collapse-btn" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseNetBalanceKWD" aria-expanded="false">
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
                        <div class="kwd-stats mt-3">
                            <div class="kwd-stat-item">
                                <i class="fas fa-coins"></i>
                                <span>إجمالي بالدينار: <strong id="kwdTotal">0.00 د.ك</strong></span>
                            </div>
                            <div class="kwd-stat-item">
                                <i class="fas fa-percentage"></i>
                                <span>نسبة التغيير: <strong id="kwdChange">0.0%</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>