{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\reports\hoteldailyReport\_variabels.blade.php --}}

{{-- 
=======================================================
📊 ملف المتغيرات المركزية للتقارير اليومية 
=======================================================
هذا الملف يحتوي على جميع المتغيرات والحسابات المطلوبة 
لعرض التقارير اليومية في جميع الأقسام والأجزاء
======================================================= 
--}}

@php
    // =======================================================
    // 🏢 بيانات الشركات والجهات الأساسية
    // =======================================================
    
    // ✅ التأكد من وجود بيانات الشركات الأولى (أفضل 5 شركات)
    if (!isset($topCompanies)) {
        $topCompanies = $companiesReport->take(5);
    }
    
    // ✅ التأكد من وجود بيانات الجهات الأولى (أفضل 5 جهات)
    if (!isset($topAgents)) {
        $topAgents = $agentsReport->take(5);
    }

    // =======================================================
    // 💰 المتغيرات المالية الأساسية - الشركات
    // =======================================================
    
    // ✅ إجمالي المستحق من الشركات حسب العملة
    if (!isset($totalDueFromCompaniesByCurrency)) {
        $totalDueFromCompaniesByCurrency = [
            'SAR' => $companiesReport->sum(function($company) {
                return collect($company->total_due_by_currency ?? [])->get('SAR', 0);
            }),
            'KWD' => $companiesReport->sum(function($company) {
                return collect($company->total_due_by_currency ?? [])->get('KWD', 0);
            })
        ];
    }
    
    // ✅ ضمان وجود المفاتيح مع قيم افتراضية
    $totalDueFromCompaniesByCurrency = array_merge([
        'SAR' => 0,
        'KWD' => 0
    ], $totalDueFromCompaniesByCurrency);

    // ✅ إجمالي المدفوعات والخصومات من الشركات حسب العملة
    if (!isset($companyPaymentsByCurrency)) {
        $companyPaymentsByCurrency = [
            'SAR' => [
                'paid' => $companiesReport->sum(function($company) {
                    return collect($company->payments_by_currency ?? [])->get('SAR.paid', 0);
                }),
                'discounts' => $companiesReport->sum(function($company) {
                    return collect($company->payments_by_currency ?? [])->get('SAR.discounts', 0);
                })
            ],
            'KWD' => [
                'paid' => $companiesReport->sum(function($company) {
                    return collect($company->payments_by_currency ?? [])->get('KWD.paid', 0);
                }),
                'discounts' => $companiesReport->sum(function($company) {
                    return collect($company->payments_by_currency ?? [])->get('KWD.discounts', 0);
                })
            ]
        ];
    }
    
    // ✅ ضمان هيكل البيانات الصحيح للمدفوعات
    $companyPaymentsByCurrency = array_merge([
        'SAR' => ['paid' => 0, 'discounts' => 0],
        'KWD' => ['paid' => 0, 'discounts' => 0]
    ], $companyPaymentsByCurrency);

    // ✅ إجمالي المتبقي من الشركات حسب العملة
    if (!isset($totalRemainingByCurrency)) {
        $totalRemainingByCurrency = [
            'SAR' => $companiesReport->sum(function($company) {
                return collect($company->remaining_bookings_by_currency ?? [])->get('SAR', 0);
            }),
            'KWD' => $companiesReport->sum(function($company) {
                return collect($company->remaining_bookings_by_currency ?? [])->get('KWD', 0);
            })
        ];
    }
    
    // ✅ ضمان وجود المفاتيح للمتبقي من الشركات
    $totalRemainingByCurrency = array_merge([
        'SAR' => 0,
        'KWD' => 0
    ], $totalRemainingByCurrency);

    // =======================================================
    // 🤝 المتغيرات المالية الأساسية - الجهات
    // =======================================================
    
    // ✅ إجمالي المستحق للجهات حسب العملة
    if (!isset($totalDueToAgentsByCurrency)) {
        $totalDueToAgentsByCurrency = [
            'SAR' => $agentsReport->sum(function($agent) {
                return collect($agent->total_due_by_currency ?? [])->get('SAR', 0);
            }),
            'KWD' => $agentsReport->sum(function($agent) {
                return collect($agent->total_due_by_currency ?? [])->get('KWD', 0);
            })
        ];
    }
    
    // ✅ ضمان وجود المفاتيح مع قيم افتراضية
    $totalDueToAgentsByCurrency = array_merge([
        'SAR' => 0,
        'KWD' => 0
    ], $totalDueToAgentsByCurrency);

    // ✅ إجمالي المدفوعات والخصومات للجهات حسب العملة
    if (!isset($agentPaymentsByCurrency)) {
        $agentPaymentsByCurrency = [
            'SAR' => [
                'paid' => $agentsReport->sum(function($agent) {
                    return collect($agent->payments_by_currency ?? [])->get('SAR.paid', 0);
                }),
                'discounts' => $agentsReport->sum(function($agent) {
                    return collect($agent->payments_by_currency ?? [])->get('SAR.discounts', 0);
                })
            ],
            'KWD' => [
                'paid' => $agentsReport->sum(function($agent) {
                    return collect($agent->payments_by_currency ?? [])->get('KWD.paid', 0);
                }),
                'discounts' => $agentsReport->sum(function($agent) {
                    return collect($agent->payments_by_currency ?? [])->get('KWD.discounts', 0);
                })
            ]
        ];
    }
    
    // ✅ ضمان هيكل البيانات الصحيح للجهات
    $agentPaymentsByCurrency = array_merge([
        'SAR' => ['paid' => 0, 'discounts' => 0],
        'KWD' => ['paid' => 0, 'discounts' => 0]
    ], $agentPaymentsByCurrency);

    // ✅ إجمالي المتبقي للجهات حسب العملة
    if (!isset($totalRemainingToAgentsByCurrency)) {
        $totalRemainingToAgentsByCurrency = [
            'SAR' => $agentsReport->sum(function($agent) {
                return collect($agent->remaining_by_currency ?? [])->get('SAR', 0);
            }),
            'KWD' => $agentsReport->sum(function($agent) {
                return collect($agent->remaining_by_currency ?? [])->get('KWD', 0);
            })
        ];
    }
    
    // ✅ ضمان وجود المفاتيح للمتبقي للجهات
    $totalRemainingToAgentsByCurrency = array_merge([
        'SAR' => 0,
        'KWD' => 0
    ], $totalRemainingToAgentsByCurrency);

    // =======================================================
    // 📊 تفاصيل العملات المنظمة (currencyDetails)
    // =======================================================
    
    // ✅ إنشاء مصفوفة شاملة لتفاصيل العملات - تستخدم في جميع الأجزاء
    $currencyDetails = [];
    
    foreach(['SAR', 'KWD'] as $currency) {
        
        // 🏢 بيانات الشركات لهذه العملة
        $dueCompany = $totalDueFromCompaniesByCurrency[$currency] ?? 0;
        $paidAmountCompany = ($companyPaymentsByCurrency[$currency] ?? [])['paid'] ?? 0;
        $discountsCompany = ($companyPaymentsByCurrency[$currency] ?? [])['discounts'] ?? 0;
        $netPaidCompany = $paidAmountCompany + $discountsCompany;
        $remainingCompany = $dueCompany - $netPaidCompany;
        $percentageCompany = $dueCompany > 0 ? round(($netPaidCompany / $dueCompany) * 100, 1) : 0;
        
        // 🤝 بيانات الجهات لهذه العملة
        $dueAgent = $totalDueToAgentsByCurrency[$currency] ?? 0;
        $paidAmountAgent = ($agentPaymentsByCurrency[$currency] ?? [])['paid'] ?? 0;
        $discountsAgent = ($agentPaymentsByCurrency[$currency] ?? [])['discounts'] ?? 0;
        $netPaidAgent = $paidAmountAgent + $discountsAgent;
        $remainingAgent = $dueAgent - $netPaidAgent;
        $percentageAgent = $dueAgent > 0 ? round(($netPaidAgent / $dueAgent) * 100, 1) : 0;
        
        // 📝 تجميع البيانات في مصفوفة منظمة
        $currencyDetails[$currency] = [
            'company' => [
                'due' => $dueCompany,                    // إجمالي المستحق
                'paid' => $paidAmountCompany,            // المدفوع
                'discounts' => $discountsCompany,        // الخصومات
                'netPaid' => $netPaidCompany,            // صافي المدفوع
                'remaining' => $remainingCompany,        // المتبقي
                'percentage' => $percentageCompany       // نسبة التحصيل
            ],
            'agent' => [
                'due' => $dueAgent,                      // إجمالي المستحق
                'paid' => $paidAmountAgent,              // المدفوع
                'discounts' => $discountsAgent,          // الخصومات
                'netPaid' => $netPaidAgent,              // صافي المدفوع
                'remaining' => $remainingAgent,          // المتبقي
                'percentage' => $percentageAgent         // نسبة التحصيل
            ],
            'names' => [
                'currencyName' => $currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي',
                'symbol' => $currency === 'SAR' ? 'ر.س' : 'د.ك'
            ]
        ];
    }

    // =======================================================
    // 💹 حسابات الربح والخسارة المتقدمة
    // =======================================================
    
    // ✅ تفاصيل صافي الربح وتحليل الأداء المالي
    $netProfitDetails = [];
    
    foreach(['SAR', 'KWD'] as $currency) {
        // 📈 الأرباح الحالية
        $companyRemaining = $totalRemainingByCurrency[$currency] ?? 0;
        $agentRemaining = $totalRemainingToAgentsByCurrency[$currency] ?? 0;
        $currentNetProfit = $companyRemaining - $agentRemaining;
        
        // 📊 الأرباح المتوقعة
        $totalDueFromCompanies = $totalDueFromCompaniesByCurrency[$currency] ?? 0;
        $totalDueToAgents = $totalDueToAgentsByCurrency[$currency] ?? 0;
        $expectedNetProfit = $totalDueFromCompanies - $totalDueToAgents;
        
        // 📋 معدل التحصيل العام
        $totalDue = $totalDueFromCompanies + $totalDueToAgents;
        $totalPaid = (($companyPaymentsByCurrency[$currency] ?? [])['paid'] ?? 0) + 
                     (($agentPaymentsByCurrency[$currency] ?? [])['paid'] ?? 0);
        $collectionRate = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 1) : 0;
        
        // 💰 تأثير الخصومات
        $totalDiscounts = (($companyPaymentsByCurrency[$currency] ?? [])['discounts'] ?? 0) + 
                         (($agentPaymentsByCurrency[$currency] ?? [])['discounts'] ?? 0);
        
        // 📊 تجميع تحليل الأداء المالي
        $netProfitDetails[$currency] = [
            'currentProfit' => $currentNetProfit,       // صافي الربح الحالي
            'expectedProfit' => $expectedNetProfit,     // صافي الربح المتوقع
            'collectionRate' => $collectionRate,        // معدل التحصيل
            'totalDiscounts' => $totalDiscounts,        // إجمالي الخصومات
            'symbol' => $currency === 'SAR' ? 'ر.س' : 'د.ك'
        ];
    }

    // =======================================================
    // 📈 بيانات الرسوم البيانية والإحصائيات
    // =======================================================
    
    // ✅ إجمالي عدد الحجوزات
    $totalBookingsCount = $companiesReport->sum('bookings_count') ?? 0;
    
    // ✅ إجمالي عدد الشركات النشطة
    $activeCompaniesCount = $companiesReport->where('bookings_count', '>', 0)->count();
    
    // ✅ إجمالي عدد الجهات النشطة
    $activeAgentsCount = $agentsReport->where('bookings_count', '>', 0)->count();
    
    // ✅ متوسط قيمة الحجز
    $averageBookingValue = [];
    foreach(['SAR', 'KWD'] as $currency) {
        $totalValue = $totalDueFromCompaniesByCurrency[$currency] ?? 0;
        $bookingsCount = $totalBookingsCount > 0 ? $totalBookingsCount : 1;
        $averageBookingValue[$currency] = round($totalValue / $bookingsCount, 2);
    }

    // =======================================================
    // 🎯 مؤشرات الأداء الرئيسية (KPIs)
    // =======================================================
    
    // ✅ مؤشرات الأداء المالي
    $kpiMetrics = [];
    
    foreach(['SAR', 'KWD'] as $currency) {
        $totalRevenue = $totalDueFromCompaniesByCurrency[$currency] ?? 0;
        $totalCosts = $totalDueToAgentsByCurrency[$currency] ?? 0;
        $profitMargin = $totalRevenue > 0 ? round((($totalRevenue - $totalCosts) / $totalRevenue) * 100, 1) : 0;
        
        $kpiMetrics[$currency] = [
            'revenue' => $totalRevenue,                 // إجمالي الإيرادات
            'costs' => $totalCosts,                     // إجمالي التكاليف
            'profitMargin' => $profitMargin,            // هامش الربح
            'roi' => $totalCosts > 0 ? round((($totalRevenue - $totalCosts) / $totalCosts) * 100, 1) : 0 // العائد على الاستثمار
        ];
    }

    // =======================================================
    // 📊 بيانات إضافية للعرض المتقدم
    // =======================================================
    
    // ✅ توزيع الحجوزات حسب الحالة
    $bookingStatusDistribution = [
        'completed' => 0,      // مكتملة
        'pending' => 0,        // في الانتظار
        'cancelled' => 0       // ملغية
    ];
    
    // ✅ ترتيب الشركات حسب الأداء
    $companiesPerformance = $companiesReport->map(function($company) {
        $totalDue = collect($company->total_due_by_currency ?? [])->sum();
        $totalRemaining = collect($company->remaining_bookings_by_currency ?? [])->sum();
        $completionRate = $totalDue > 0 ? round((($totalDue - $totalRemaining) / $totalDue) * 100, 1) : 0;
        
        return [
            'id' => $company->id,
            'name' => $company->name,
            'bookings_count' => $company->bookings_count ?? 0,
            'total_due' => $totalDue,
            'completion_rate' => $completionRate,
            'performance_score' => $completionRate * 0.7 + ($company->bookings_count ?? 0) * 0.3
        ];
    })->sortByDesc('performance_score');

    // ✅ ترتيب الجهات حسب الأداء
    $agentsPerformance = $agentsReport->map(function($agent) {
        $totalDue = collect($agent->total_due_by_currency ?? [])->sum();
        $totalRemaining = collect($agent->remaining_by_currency ?? [])->sum();
        $completionRate = $totalDue > 0 ? round((($totalDue - $totalRemaining) / $totalDue) * 100, 1) : 0;
        
        return [
            'id' => $agent->id,
            'name' => $agent->name,
            'bookings_count' => $agent->bookings_count ?? 0,
            'total_due' => $totalDue,
            'completion_rate' => $completionRate,
            'performance_score' => $completionRate * 0.7 + ($agent->bookings_count ?? 0) * 0.3
        ];
    })->sortByDesc('performance_score');

    // =======================================================
    // 🎨 بيانات التصميم والألوان
    // =======================================================
    
    // ✅ ألوان الحالات المختلفة
    $statusColors = [
        'excellent' => '#10b981',    // أخضر - ممتاز (أكثر من 80%)
        'good' => '#f59e0b',        // أصفر - جيد (50-80%)
        'poor' => '#ef4444',        // أحمر - ضعيف (أقل من 50%)
        'neutral' => '#6b7280'      // رمادي - محايد
    ];
    
    // ✅ دالة مساعدة لتحديد لون الحالة
    function getStatusColor($percentage) {
        global $statusColors;
        if ($percentage >= 80) return $statusColors['excellent'];
        if ($percentage >= 50) return $statusColors['good'];
        return $statusColors['poor'];
    }
@endphp

{{-- 
=======================================================
🎯 تمرير البيانات إلى JavaScript للرسوم البيانية
=======================================================
جميع البيانات المحسوبة أعلاه يتم تمريرها هنا إلى JavaScript
لاستخدامها في الرسوم البيانية والتفاعلات
======================================================= 
--}}

@push('scripts')
    <script>
        // ✅ تمرير جميع البيانات المحسوبة إلى JavaScript
        window.chartData = {
            
            // 📊 بيانات الرسوم البيانية الأساسية
            dailyLabels: @json($chartDates ?? []),
            dailyData: @json($bookingCounts ?? []),
            receivableBalances: @json($receivableBalances ?? []),
            payableBalances: @json($payableBalances ?? []),
            dailyEventDetails: @json($dailyEventDetails ?? []),
            
            // 📈 بيانات صافي الرصيد
            netBalanceDates: @json($netBalanceDates ?? []),
            netBalances: @json($netBalances ?? []),
            netBalancesKWD: @json($netBalancesKWD ?? []),

            // 🏢 بيانات أفضل الشركات
            topCompaniesLabels: @json($topCompanies->pluck('name') ?? []),
            topCompaniesRemaining: @json($topCompanies->map(function($company) {
                return collect($company->remaining_bookings_by_currency ?? [])->sum();
            }) ?? []),
            topCompaniesBookingCounts: @json($topCompanies->pluck('bookings_count') ?? []),
            
            // 🤝 بيانات أفضل الجهات
            topAgentsLabels: @json($topAgents->pluck('name') ?? []),
            topAgentsRemaining: @json($topAgents->map(function($agent) {
                return collect($agent->remaining_by_currency ?? [])->sum();
            }) ?? []),

            // 📊 الإحصائيات العامة
            totalCompanyBookings: {{ $totalBookingsCount }},
            activeCompaniesCount: {{ $activeCompaniesCount }},
            activeAgentsCount: {{ $activeAgentsCount }},

            // 💰 البيانات المالية الأساسية
            totalDueFromCompaniesByCurrency: @json($totalDueFromCompaniesByCurrency),
            totalPaidByCompaniesByCurrency: @json($companyPaymentsByCurrency),
            totalRemainingFromCompaniesByCurrency: @json($totalRemainingByCurrency),
            totalDueToAgentsByCurrency: @json($totalDueToAgentsByCurrency),
            totalPaidToAgentsByCurrency: @json($agentPaymentsByCurrency),
            totalRemainingToAgentsByCurrency: @json($totalRemainingToAgentsByCurrency),
            
            // 📈 تفاصيل الربح والخسارة
            netBalanceByCurrency: @json($netProfitByCurrency ?? ['SAR' => 0, 'KWD' => 0]),
            
            // 🎯 البيانات المنظمة (الأهم!)
            currencyDetails: @json($currencyDetails ?? []),
            netProfitDetails: @json($netProfitDetails ?? []),
            kpiMetrics: @json($kpiMetrics ?? []),
            averageBookingValue: @json($averageBookingValue ?? []),
            
            // 📊 بيانات الأداء
            companiesPerformance: @json($companiesPerformance ?? []),
            agentsPerformance: @json($agentsPerformance ?? []),
            bookingStatusDistribution: @json($bookingStatusDistribution ?? []),

            // 🎨 إعدادات التصميم والألوان
            chartTheme: {
                primaryGradient: ['#667eea', '#764ba2'],
                secondaryGradient: ['#f093fb', '#f5576c'],
                positiveColor: '#10b981',
                negativeColor: '#ef4444',
                neutralColor: '#6b7280',
                warningColor: '#f59e0b',
                infoColor: '#06b6d4'
            },
            
            // 🎯 ألوان الحالات
            statusColors: @json($statusColors ?? []),
            
            // 🔧 إعدادات عامة
            settings: {
                currency: {
                    SAR: { name: 'ريال سعودي', symbol: 'ر.س' },
                    KWD: { name: 'دينار كويتي', symbol: 'د.ك' }
                },
                dateFormat: 'YYYY-MM-DD',
                decimalPlaces: 2
            }
        };

        // ✅ دوال مساعدة JavaScript
        
        // 💱 تنسيق العملة
        window.formatCurrency = function(amount, currency = 'SAR') {
            const symbol = currency === 'SAR' ? 'ر.س' : 'د.ك';
            return parseFloat(amount).toLocaleString('ar-SA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ' + symbol;
        };
        
        // 📊 تحديد لون الحالة
        window.getStatusColor = function(percentage) {
            if (percentage >= 80) return window.chartData.statusColors.excellent;
            if (percentage >= 50) return window.chartData.statusColors.good;
            return window.chartData.statusColors.poor;
        };
        
        // 📈 حساب نسبة التغيير
        window.calculateChangePercentage = function(current, previous) {
            if (previous === 0) return 0;
            return ((current - previous) / Math.abs(previous)) * 100;
        };

        // ✅ رسالة تأكيد تحميل البيانات
        // console.log('📊 تم تحميل جميع بيانات التقارير اليومية بنجاح!');
        // console.log('💰 البيانات المالية:', window.chartData.currencyDetails);
        // console.log('📈 مؤشرات الأداء:', window.chartData.kpiMetrics);
    </script>
@endpush

{{-- 
=======================================================
✅ انتهى ملف المتغيرات المركزية
=======================================================
جميع المتغيرات والحسابات محفوظة ومتاحة الآن لجميع 
أجزاء وأقسام صفحة التقارير اليومية
======================================================= 
--}}