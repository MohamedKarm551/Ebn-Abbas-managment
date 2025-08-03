{{-- 📊 الرسوم البيانية المحسنة --}}
<div class="mb-4 col-6 m-auto">
    <div class="collapse-card">
        <button class="collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNetBalanceSAR"
            aria-expanded="false">
            <div class="collapse-btn-content">
                <div class="collapse-icon-wrapper">
                    <i class="fas fa-chart-line collapse-icon"></i>
                </div>
                <div class="collapse-text-content">
                    <span class="collapse-title">اتجاه صافي الرصيد مع الوقت</span>
                    <span class="collapse-subtitle">تتبع ديناميكي لحركة الأرصدة المالية بالريال</span>
                </div>
            </div>
            <div class="collapse-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </button>

        <div class="collapse" id="collapseNetBalanceSAR">
            <div class="collapse-content">
                {{-- Chart Container محسن --}}
                <div class="chart-container-enhanced position-relative">
                    <canvas id="netBalanceChart" class="main-chart"></canvas>

                    {{-- Loading Animation --}}
                    <div class="chart-loading" id="chartLoading">
                        <div class="loading-spinner">
                            <div class="spinner-ring"></div>
                            <div class="spinner-ring"></div>
                            <div class="spinner-ring"></div>
                        </div>
                        <p class="loading-text">جاري تحميل البيانات...</p>
                    </div>

                    {{-- Chart Info Panel محسّن وتفاعلي --}}
                    <div class="chart-info-panel-new" id="chartInfoPanel">
                        <button class="info-toggle-btn" onclick="toggleChartInfo()">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        
                        <div class="info-content-wrapper" id="infoContentWrapper">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">آخر تحديث</span>
                                    <span class="info-value" id="lastUpdate">{{ now()->format('H:i d/m/Y') }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-chart-bar info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">نقاط البيانات</span>
                                    <span class="info-value" id="dataPoints">--</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-trending-up info-icon"></i>
                                <div class="info-content">
                                    <span class="info-label">الاتجاه</span>
                                    <span class="info-value trend-indicator" id="trendIndicator">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- أزرار التحكم --}}
                <div class="chart-controls d-flex gap-2 justify-content-center mt-3">
                    <button class="chart-control-btn" id="fullscreenBtn" title="شاشة كاملة">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                    <button class="chart-control-btn" id="downloadBtn" title="تحميل كصورة">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="chart-control-btn" id="refreshBtn" title="تحديث">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                {{-- مؤشرات الحالة --}}
                <div class="chart-indicators mt-3" style="
    background: aliceblue;
">
                    <div class="indicator-item">
                        <div class="indicator-dot positive"></div>
                        <span class="stat-value">رصيد موجب (لك)</span>
                    </div>
                    <div class="indicator-item">
                        <div class="indicator-dot negative"></div>
                        <span class="stat-value">رصيد سالب (عليك)</span>
                    </div>
                    <div class="indicator-item">
                        <div class="indicator-dot neutral"></div>
                        <span class="stat-value">نقطة التوازن</span>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="quick-stats mt-3">
                    <div class="stat-card positive">
                        <div class="stat-icon">
                            <i class="fas fa-arrow-trend-up"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">أعلى رصيد</span>
                            <span class="stat-value" id="maxBalance">--</span>
                        </div>
                    </div>

                    <div class="stat-card negative">
                        <div class="stat-icon">
                            <i class="fas fa-arrow-trend-down"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">أقل رصيد</span>
                            <span class="stat-value" id="minBalance">--</span>
                        </div>
                    </div>

                    <div class="stat-card neutral">
                        <div class="stat-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">المتوسط</span>
                            <span class="stat-value" id="avgBalance">--</span>
                        </div>
                    </div>

                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">الحالي</span>
                            <span class="stat-value" id="currentBalance">--</span>
                        </div>
                    </div>
                </div>

                {{-- Chart Description --}}
                <div class="chart-description mt-3 p-3 rounded" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.1);">
                    <i class="fas fa-info-circle me-2" style="color: #10b981;"></i>
                    <div class="text-success d-inline">يمثل الخط التغير في صافي الرصيد (الموجب = لك، السالب = عليك) بناءً على العمليات المسجلة</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- تحديث CSS للـ Info Panel الجديد --}}
<style>
/* Chart Info Panel الجديد - أصغر وأكثر تفاعلية */
.chart-info-panel-new {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 10;
    font-family: 'Cairo', sans-serif;
}

.info-toggle-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(37, 99, 235, 0.9));
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    backdrop-filter: blur(10px);
}

.info-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.info-content-wrapper {
    position: absolute;
    top: 50px;
    right: 0;
    min-width: 220px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.info-content-wrapper.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.info-content-wrapper::before {
    content: '';
    position: absolute;
    top: -8px;
    right: 12px;
    width: 16px;
    height: 16px;
    background: rgba(255, 255, 255, 0.95);
    transform: rotate(45deg);
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.info-item:hover {
    background: rgba(16, 185, 129, 0.05);
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-icon {
    width: 20px;
    height: 20px;
    color: #10b981;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.info-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
    line-height: 1.2;
}

.info-value {
    font-size: 13px;
    color: #1f2937;
    font-weight: 700;
    line-height: 1.2;
}

.trend-indicator.positive {
    color: #10b981;
}

.trend-indicator.negative {
    color: #ef4444;
}

/* تحديث ألوان النصوص في المؤشرات */
.indicator-text {
    font-size: 0.85rem;
    color: #1f2937; /* تغيير من rgba(255, 255, 255, 0.9) إلى أسود */
    font-weight: 500;
}

.chart-description span {
    color: #1f2937 !important; /* تأكد من اللون الأسود */
}

/* Responsive */
@media (max-width: 768px) {
    .chart-info-panel-new {
        top: 10px;
        right: 10px;
    }
    
    .info-toggle-btn {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
    
    .info-content-wrapper {
        min-width: 200px;
        right: -80px;
    }
}
</style>

{{-- JavaScript للتحكم في Info Panel --}}
<script>
function toggleChartInfo() {
    const wrapper = document.getElementById('infoContentWrapper');
    wrapper.classList.toggle('active');
}

// إخفاء المعلومات عند الضغط خارجها
document.addEventListener('click', function(event) {
    const panel = document.getElementById('chartInfoPanel');
    const wrapper = document.getElementById('infoContentWrapper');
    
    if (!panel.contains(event.target)) {
        wrapper.classList.remove('active');
    }
});
</script>