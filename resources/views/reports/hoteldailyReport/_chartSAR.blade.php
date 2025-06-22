
        {{-- 📊 الرسوم البيانية المحسنة --}}
        <div class="row mt-5">
            <div class="col-md-12 mb-4">
                <div class="chart-card position-relative overflow-hidden">
                    {{-- خلفية متدرجة متحركة --}}
                    <div class="chart-bg-gradient"></div>

                    {{-- Header محسن --}}
                    <div class="chart-header position-relative">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="chart-title-section">
                                <div class="chart-icon-wrapper">
                                    <i class="fas fa-chart-line chart-main-icon"></i>
                                </div>
                                <div class="chart-title-content">
                                    <h5 class="chart-title mb-1">اتجاه صافي الرصيد مع الوقت</h5>
                                    <p class="chart-subtitle mb-0">تتبع ديناميكي لحركة الأرصدة المالية</p>
                                </div>
                            </div>

                            {{-- أزرار التحكم --}}
                            <div class="chart-controls d-flex gap-2">
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
                        </div>

                        {{-- مؤشرات الحالة --}}
                        <div class="chart-indicators mt-3">
                            <div class="indicator-item">
                                <div class="indicator-dot positive"></div>
                                <span class="indicator-text">رصيد موجب (لك)</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-dot negative"></div>
                                <span class="indicator-text">رصيد سالب (عليك)</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-dot neutral"></div>
                                <span class="indicator-text">نقطة التوازن</span>
                            </div>
                        </div>
                    </div>

                    {{-- Chart Container محسن --}}
                    <div class="chart-body position-relative">
                        <div class="chart-container-enhanced">
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
                        </div>

                        {{-- Chart Info Panel --}}
                        <div class="chart-info-panel">
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

                    {{-- Chart Footer --}}
                    <div class="chart-footer">
                        <div class="chart-description">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>يمثل الخط التغير في صافي الرصيد (الموجب = لك، السالب = عليك) بناءً على العمليات
                                المسجلة</span>
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
                    </div>
                </div>
            </div>
        </div>