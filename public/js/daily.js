// ========================================
// 🚀 بداية تحميل الصفحة وتهيئة العناصر
// ========================================
document.addEventListener("DOMContentLoaded", function () {
    
    // ========================================
    // ⏰ إدارة الساعة والتاريخ المباشر
    // ========================================
    
    // 🔍 الحصول على عناصر الساعة والتاريخ من DOM
    const timeDisplayElement = document.getElementById("watch-time-display");  // عنصر عرض الوقت
    const dateDisplayElement = document.getElementById("watch-date-display");  // عنصر عرض التاريخ

    // 🎨 تحديد الألوان الأولية للساعة والتاريخ
    let currentTimeColor = "white";        // لون الوقت الحالي
    let currentDateColor = "#8b22d8";      // لون التاريخ الحالي (بنفسجي)

    // ⏰ دالة تحديث الوقت المعروض
    function updateWatchTime() {
        if (timeDisplayElement) {                                    // التأكد من وجود عنصر الوقت
            const now = new Date();                                  // إنشاء كائن التاريخ الحالي
            const hours = String(now.getHours()).padStart(2, "0");   // استخراج الساعات وإضافة صفر إذا لزم
            const minutes = String(now.getMinutes()).padStart(2, "0"); // استخراج الدقائق وإضافة صفر إذا لزم
            timeDisplayElement.textContent = hours + ":" + minutes;    // عرض الوقت بتنسيق HH:MM
        }
    }

    // 🎨 دالة تبديل ألوان الساعة والتاريخ
    function swapWatchColors() {
        if (timeDisplayElement && dateDisplayElement) {  // التأكد من وجود كلا العنصرين
            const tempColor = currentTimeColor;          // حفظ لون الوقت الحالي مؤقتاً
            currentTimeColor = currentDateColor;         // تعيين لون التاريخ للوقت
            currentDateColor = tempColor;                // تعيين اللون المؤقت للتاريخ

            timeDisplayElement.style.color = currentTimeColor;  // تطبيق اللون الجديد على الوقت
            dateDisplayElement.style.color = currentDateColor;  // تطبيق اللون الجديد على التاريخ
        }
    }

    // ⚡ تشغيل دالة تحديث الوقت فوراً
    updateWatchTime();
    
    // ⏱️ تشغيل تحديث الوقت كل دقيقة (60000 مللي ثانية)
    setInterval(updateWatchTime, 60000);
    
    // 🌈 تشغيل تبديل الألوان كل 30 ثانية (30000 مللي ثانية)
    setInterval(swapWatchColors, 30000);

    // ========================================
    // 📊 رسم الحجوزات اليومية (خط بياني)
    // ========================================
    
    // 🔍 البحث عن عنصر canvas الخاص برسم الحجوزات اليومية
    const dailyCtx = document.getElementById("dailyBookingsChart");
    
    // ✅ التحقق من وجود العنصر وبيانات الرسم البياني
    if (dailyCtx && window.chartData && window.chartData.dailyLabels) {
        const dailyLabels = window.chartData.dailyLabels;  // تسميات المحور السيني (التواريخ)
        const dailyData = window.chartData.dailyData;      // بيانات المحور الصادي (عدد الحجوزات)

        // ✅ التأكد من وجود بيانات للعرض
        if (dailyLabels.length > 0) {
            
            // 📈 إنشاء رسم بياني خطي للحجوزات اليومية
            new Chart(dailyCtx, {
                type: "line",  // نوع الرسم البياني (خطي)
                data: {
                    labels: dailyLabels,  // تسميات المحور السيني
                    datasets: [
                        {
                            label: "عدد الحجوزات",                    // تسمية البيانات
                            data: dailyData,                         // البيانات الفعلية
                            fill: true,                              // ملء المنطقة تحت الخط
                            borderColor: "rgb(75, 192, 192)",        // لون الخط
                            backgroundColor: "rgba(75, 192, 192, 0.2)", // لون التعبئة
                            tension: 0.1,                            // درجة انحناء الخط
                        },
                    ],
                },
                options: {
                    responsive: true,              // الاستجابة لتغيير حجم الشاشة
                    maintainAspectRatio: false,    // عدم الحفاظ على نسبة العرض للارتفاع
                    scales: {
                        y: {
                            beginAtZero: true,     // بدء المحور الصادي من الصفر
                            ticks: { precision: 0 }, // عرض الأرقام الصحيحة فقط
                        },
                    },
                    plugins: {
                        legend: { display: false }, // إخفاء مفتاح الرسم البياني
                    },
                },
            });
        }
    }

    // ========================================
    // 📊 الرسم البياني الرئيسي: اتجاه صافي الرصيد مع الوقت
    // ========================================
    
    // 🔍 الحصول على عنصر canvas الخاص برسم صافي الرصيد
    const ctxNetBalance = document.getElementById("netBalanceChart");
    
    // 📥 استخراج البيانات من المتغير العام مع توفير قيم افتراضية
    const netBalanceDates = (window.chartData && window.chartData.netBalanceDates) ? window.chartData.netBalanceDates : [];     // تواريخ الرصيد
    const netBalances = (window.chartData && window.chartData.netBalances) ? window.chartData.netBalances : [];               // قيم الرصيد
    const dailyEventDetails = (window.chartData && window.chartData.dailyEventDetails) ? window.chartData.dailyEventDetails : {}; // تفاصيل الأحداث اليومية

    // ✅ التحقق من وجود العنصر والبيانات
    if (ctxNetBalance && netBalanceDates.length > 0) {
        
        // 📈 إنشاء الرسم البياني الرئيسي لصافي الرصيد
        const netBalanceChart = new Chart(ctxNetBalance, {
            type: "line",  // نوع الرسم البياني (خطي)
            data: {
                labels: netBalanceDates,  // تسميات المحور السيني (التواريخ)
                datasets: [
                    {
                        label: "صافي الرصيد (ريال سعودي)",         // تسمية البيانات
                        data: netBalances,                          // البيانات الفعلية للرصيد
                        borderColor: "rgb(102, 126, 234)",          // لون الخط (أزرق)
                        backgroundColor: "rgba(102, 126, 234, 0.1)", // لون التعبئة (أزرق شفاف)
                        borderWidth: 3,                             // سماكة الخط
                        fill: true,                                 // ملء المنطقة تحت الخط
                        tension: 0.4,                               // درجة انحناء الخط
                        pointBackgroundColor: "rgb(102, 126, 234)", // لون النقاط
                        pointBorderColor: "#fff",                   // لون حدود النقاط
                        pointBorderWidth: 2,                        // سماكة حدود النقاط
                        pointRadius: 6,                             // حجم النقاط
                        pointHoverRadius: 8,                        // حجم النقاط عند التمرير
                    },
                ],
            },
            options: {
                responsive: true,              // الاستجابة لتغيير حجم الشاشة
                maintainAspectRatio: false,    // عدم الحفاظ على نسبة العرض للارتفاع
                plugins: {
                    legend: {
                        display: true,         // عرض مفتاح الرسم البياني
                        position: "top",       // موضع المفتاح (أعلى)
                        labels: {
                            usePointStyle: true,  // استخدام نمط النقاط في المفتاح
                            padding: 20,          // المسافة حول تسميات المفتاح
                            font: {
                                family: "Cairo, sans-serif",  // نوع الخط
                                size: 12,                      // حجم الخط
                                weight: "600",                 // وزن الخط (غامق)
                            },
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(0, 0, 0, 0.8)",     // لون خلفية التلميح
                        titleColor: "#fff",                        // لون عنوان التلميح
                        bodyColor: "#fff",                         // لون نص التلميح
                        borderColor: "rgba(102, 126, 234, 0.8)",   // لون حدود التلميح
                        borderWidth: 2,                            // سماكة حدود التلميح
                        cornerRadius: 12,                          // درجة استدارة زوايا التلميح
                        titleFont: {
                            family: "Cairo, sans-serif",  // نوع خط العنوان
                            size: 14,                      // حجم خط العنوان
                            weight: "bold",                // وزن خط العنوان
                        },
                        bodyFont: { family: "Cairo, sans-serif", size: 12 }, // خصائص خط النص
                        callbacks: {
                            // 🏷️ دالة تخصيص عنوان التلميح
                            title: function (tooltipItems) {
                                return "📅 التاريخ: " + tooltipItems[0].label;  // عرض التاريخ مع أيقونة
                            },
                            // 📊 دالة تخصيص محتوى التلميح
                            label: function (context) {
                                var label = context.dataset.label || "";  // استخراج تسمية البيانات
                                if (label) label += ": ";                  // إضافة نقطتين بعد التسمية

                                if (context.parsed.y !== null) {           // التأكد من وجود قيمة
                                    const value = context.parsed.y;        // استخراج القيمة
                                    const formattedValue = value.toLocaleString("ar-SA");  // تنسيق الرقم بالعربية
                                    // 📈📉 تحديد حالة الرصيد (موجب/سالب/متوازن)
                                    const status = value > 0 ? "لك 📈" : value < 0 ? "عليك 📉" : "متوازن ⚖️";
                                    label += formattedValue + " ريال (" + status + ")";  // تجميع النص النهائي
                                }
                                return label;
                            },
                            // 📝 دالة إضافة تفاصيل إضافية في التلميح
                            afterBody: function (tooltipItems) {
                                const dateLabel = tooltipItems[0].label;                    // استخراج تسمية التاريخ
                                const eventDetailsForDay = dailyEventDetails[dateLabel] || []; // استخراج أحداث اليوم

                                if (eventDetailsForDay.length > 0) {  // التحقق من وجود أحداث
                                    var lines = ["", "🎯 أحداث اليوم:"];  // بداية قائمة الأحداث
                                    eventDetailsForDay.forEach(function(detail, index) {  // تكرار على كل حدث
                                        lines.push((index + 1) + ". " + detail);  // إضافة الحدث مع ترقيم
                                    });
                                    return lines;  // إرجاع قائمة الأحداث
                                }
                                return [];  // إرجاع قائمة فارغة إذا لم توجد أحداث
                            },
                        },
                    },
                },
                scales: {
                    x: {  // إعدادات المحور السيني (التواريخ)
                        grid: {
                            color: "rgba(0, 0, 0, 0.1)",  // لون خطوط الشبكة
                            drawBorder: false,             // عدم رسم حدود المحور
                        },
                        ticks: {
                            font: { family: "Cairo, sans-serif", size: 11 }, // خصائص خط التسميات
                        },
                    },
                    y: {  // إعدادات المحور الصادي (القيم)
                        grid: {
                            color: "rgba(0, 0, 0, 0.1)",  // لون خطوط الشبكة
                            drawBorder: false,             // عدم رسم حدود المحور
                        },
                        ticks: {
                            font: { family: "Cairo, sans-serif", size: 11 }, // خصائص خط التسميات
                            // 💰 دالة تنسيق القيم المعروضة على المحور
                            callback: function (value) {
                                return new Intl.NumberFormat("ar-SA").format(value) + " ريال"; // تنسيق الرقم + عملة
                            },
                        },
                        // 📊 دالة تحديد حدود المحور الصادي
                        afterDataLimits: function (scale) {
                            scale.min = Math.min(scale.min, 0);  // ضمان وجود الصفر في النطاق
                            scale.max = Math.max(scale.max, 0);  // ضمان وجود الصفر في النطاق
                        },
                    },
                },
                animation: { duration: 2000, easing: "easeOutCubic" }, // إعدادات الحركة
                interaction: { intersect: false, mode: "index" },       // إعدادات التفاعل
            },
        });

        // 📊 تحديث الإحصائيات الجانبية بناءً على بيانات الرصيد
        updateChartStats(netBalances);
        
        // 💾 حفظ مرجع الرسم البياني في المتغير العام للوصول إليه لاحقاً
        window.mainNetBalanceChart = netBalanceChart;

        // ✅ طباعة رسالة نجاح في وحدة التحكم
        console.log("✅ تم إنشاء رسم صافي الرصيد بنجاح");
        
    } else if (ctxNetBalance) {
        // 🚫 عرض رسالة عدم وجود بيانات إذا لم توجد بيانات كافية
        ctxNetBalance.parentNode.innerHTML = 
            '<div class="text-center p-5">' +
                '<i class="fas fa-chart-line fa-3x text-muted mb-3"></i>' +
                '<h5 class="text-muted">لا توجد بيانات كافية لعرض الرسم البياني</h5>' +
                '<p class="text-muted">سيتم عرض البيانات عند توفرها</p>' +
            '</div>';
    }

    // ========================================
    // 💱 رسم الدينار الكويتي (إضافي)
    // ========================================
    
    // 🔍 الحصول على عنصر canvas الخاص برسم الدينار الكويتي
    const ctxNetBalanceKWD = document.getElementById("netBalanceKWDChart");
    
    // 📥 استخراج بيانات الدينار الكويتي
    const netBalancesKWD = (window.chartData && window.chartData.netBalancesKWD) ? window.chartData.netBalancesKWD : [];

    // 🗑️ التحقق من وجود رسم بياني سابق وحذفه لتجنب التضارب
    const existingKWDChart = Chart.getChart(ctxNetBalanceKWD);
    if (existingKWDChart) {
        existingKWDChart.destroy();  // حذف الرسم البياني السابق
    }

    // ✅ التحقق من وجود العنصر والبيانات الكافية
  if (netBalancesKWD && netBalancesKWD.length > 0) {
    const ctxKWD = document.getElementById("netBalanceKWDChart");
    if (ctxKWD) {
        console.log("🎨 إنشاء رسم الدينار الكويتي...");

        const kwdChart = new Chart(ctxKWD, {
            type: "line",
            data: {
                labels: netBalanceDates,
                datasets: [
                    {
                        label: "صافي الرصيد (دينار كويتي)",
                        data: netBalancesKWD,
                        borderColor: "#ff6b35",              // ✅ برتقالي واضح
                        backgroundColor: "rgba(255, 107, 53, 0.3)", // ✅ برتقالي شفاف لكن واضح
                        borderWidth: 3,                      // ✅ خط سميك
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#ff6b35",     // ✅ نقاط برتقالية
                        pointBorderColor: "#fff",
                        pointBorderWidth: 2,
                        pointRadius: 5,                      // ✅ نقاط أكبر
                        pointHoverRadius: 8,                 // ✅ تأثير hover أكبر
                        pointHoverBackgroundColor: "#ff4500", // ✅ لون hover مختلف
                        pointHoverBorderColor: "#fff",
                        pointHoverBorderWidth: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: { family: "Cairo, sans-serif", size: 12, weight: "bold" },
                            color: "#2d3748",
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        backgroundColor: "rgba(45, 55, 72, 0.9)",
                        titleColor: "#fff",
                        bodyColor: "#fff",
                        titleFont: { family: "Cairo, sans-serif", size: 14, weight: "bold" },
                        bodyFont: { family: "Cairo, sans-serif", size: 12 },
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) label += ": ";
                                if (context.parsed.y !== null) {
                                    const value = context.parsed.y;
                                    const formattedValue = value.toLocaleString("ar-SA");
                                    const status = value > 0 ? "لك 📈" : value < 0 ? "عليك 📉" : "متوازن ⚖️";
                                    label += formattedValue + " د.ك (" + status + ")";
                                }
                                return label;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: true, color: "rgba(0, 0, 0, 0.05)" },
                        ticks: {
                            font: { family: "Cairo, sans-serif", size: 11 },
                            color: "#4a5568",
                        },
                    },
                    y: {
                        grid: { display: true, color: "rgba(0, 0, 0, 0.1)" },
                        ticks: {
                            font: { family: "Cairo, sans-serif", size: 11 },
                            color: "#4a5568",
                            callback: function (value) {
                                return new Intl.NumberFormat("ar-SA").format(value) + " د.ك";
                            },
                        },
                        afterDataLimits: function (scale) {
                            scale.min = Math.min(scale.min, 0);
                            scale.max = Math.max(scale.max, 0);
                        },
                    },
                },
                elements: {
                    point: {
                        hoverRadius: 8,
                    },
                    line: {
                        borderJoinStyle: 'round',
                    },
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            },
        });

        // 📊 تحديث إحصائيات الدينار الكويتي
        updateKWDStats(netBalancesKWD);
        
        // 💾 حفظ مرجع الرسم البياني للدينار
        window.kwdNetBalanceChart = kwdChart;

        console.log("✅ تم إنشاء رسم الدينار الكويتي بنجاح");
    }
} else {
    // 🚫 عرض رسالة عدم وجود بيانات
    const ctxKWD = document.getElementById("netBalanceKWDChart");
    if (ctxKWD) {
        ctxKWD.parentNode.innerHTML = 
            '<div class="text-center p-4">' +
                '<i class="fas fa-coins fa-3x text-muted mb-3"></i>' +
                '<h6 class="text-muted">لا توجد بيانات للدينار الكويتي</h6>' +
                '<p class="text-muted small">سيتم عرض البيانات عند توفرها</p>' +
            '</div>';
    }
}

    // ========================================
    // 🎮 ربط أزرار التحكم بالرسم البياني
    // ========================================
    
    // 🔍 الحصول على أزرار التحكم من DOM
    const fullscreenBtn = document.getElementById("fullscreenBtn");  // زر الشاشة الكاملة
    const downloadBtn = document.getElementById("downloadBtn");      // زر التحميل
    const refreshBtn = document.getElementById("refreshBtn");        // زر التحديث

    // 🖱️ ربط زر الشاشة الكاملة بالدالة المناسبة
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener("click", function() {
            toggleFullscreen("netBalanceChart");  // استدعاء دالة التبديل للشاشة الكاملة
        });
    }

    // 📥 ربط زر التحميل بالدالة المناسبة
    if (downloadBtn) {
        downloadBtn.addEventListener("click", function() {
            downloadChart("netBalanceChart");  // استدعاء دالة تحميل الرسم البياني
        });
    }

    // 🔄 ربط زر التحديث بالدالة المناسبة
    if (refreshBtn) {
        refreshBtn.addEventListener("click", function() {
            refreshChart("netBalanceChart");  // استدعاء دالة تحديث الرسم البياني
        });
    }

    // ========================================
    // 🚀 التهيئة النهائية والإعدادات الأخيرة
    // ========================================
    
    // ⏳ إخفاء شاشة التحميل بعد ثانيتين وتحديث الوقت
    setTimeout(function() {
        showChartLoading("netBalanceChart", false);  // إخفاء Loading animation
        updateLastUpdateTime();                      // تحديث وقت آخر تحديث
    }, 2000);

    // 📱 إضافة مستمع لتغيير حالة الشاشة الكاملة
    document.addEventListener("fullscreenchange", function () {
        setTimeout(function() {
            window.dispatchEvent(new Event("resize"));  // إرسال حدث تغيير الحجم لتحديث الرسوم البيانية
        }, 100);
    });

    // ✅ طباعة رسالة نجاح التحميل النهائي
    console.log("✅ تم تحميل Chart functions بنجاح");
});

// ========================================
// 📊 دوال إدارة الإحصائيات والمساعدة
// ========================================

// 📈 دالة تحديث إحصائيات الرسم البياني الرئيسي
function updateChartStats(netBalances) {
    if (!netBalances || netBalances.length === 0) return;  // التحقق من وجود بيانات

    try {
        // 📊 حساب القيم الإحصائية الأساسية
        const maxBalance = Math.max.apply(Math, netBalances);           // أعلى قيمة رصيد
        const minBalance = Math.min.apply(Math, netBalances);           // أقل قيمة رصيد
        const sum = netBalances.reduce(function(a, b) { return a + b; }, 0); // مجموع القيم
        const avgBalance = sum / netBalances.length;                    // متوسط الرصيد
        const currentBalance = netBalances[netBalances.length - 1];     // الرصيد الحالي (آخر قيمة)

        // 🔄 تحديث عناصر الإحصائيات في الواجهة
        updateStatElement("maxBalance", formatCurrency(maxBalance));       // تحديث أعلى رصيد
        updateStatElement("minBalance", formatCurrency(minBalance));       // تحديث أقل رصيد
        updateStatElement("avgBalance", formatCurrency(avgBalance));       // تحديث متوسط الرصيد
        updateStatElement("currentBalance", formatCurrency(currentBalance)); // تحديث الرصيد الحالي
        updateStatElement("dataPoints", netBalances.length);               // تحديث عدد نقاط البيانات

        // 📈 حساب وتحديث اتجاه الترند
        const trend = calculateTrend(netBalances);
        updateTrendIndicator(trend);
        
    } catch (error) {
        // 🚫 معالجة الأخطاء وطباعتها في وحدة التحكم
        console.error("خطأ في تحديث الإحصائيات:", error);
    }
}

// 💱 دالة تحديث إحصائيات الدينار الكويتي
function updateKWDStats(netBalancesKWD) {
    if (!netBalancesKWD || netBalancesKWD.length === 0) return;  // التحقق من وجود بيانات

    try {
        // 📊 حساب الإحصائيات الخاصة بالدينار الكويتي
        const total = netBalancesKWD[netBalancesKWD.length - 1] || 0;    // المجموع الحالي
        const previous = netBalancesKWD[netBalancesKWD.length - 2] || 0; // القيمة السابقة
        const change = total - previous;                                 // التغيير
        const changePercent = previous !== 0 ? ((change / Math.abs(previous)) * 100).toFixed(1) : 0; // نسبة التغيير

        // 🔄 تحديث عناصر إحصائيات الدينار في الواجهة
        updateStatElement("kwdTotal", formatCurrency(total, "KWD"));      // تحديث المجموع
        updateStatElement("kwdChange", changePercent + "%");              // تحديث نسبة التغيير
        
    } catch (error) {
        // 🚫 معالجة الأخطاء
        console.error("خطأ في تحديث إحصائيات الدينار:", error);
    }
}

// 🔄 دالة تحديث عنصر إحصائي واحد في الواجهة
function updateStatElement(id, value) {
    const element = document.getElementById(id);  // البحث عن العنصر باستخدام ID
    if (element) element.textContent = value;     // تحديث النص إذا وُجد العنصر
}

// 💰 دالة تنسيق العملة للعرض
function formatCurrency(value, currency) {
    if (typeof currency === 'undefined') currency = "SAR";  // تعيين قيمة افتراضية للعملة
    
    const formatted = new Intl.NumberFormat("ar-SA").format(Math.abs(value)); // تنسيق الرقم بالعربية
    const sign = value >= 0 ? "+" : "-";                                      // تحديد إشارة الرقم
    const currencySymbol = currency === "KWD" ? "د.ك" : "ريال";               // تحديد رمز العملة
    return sign + formatted + " " + currencySymbol;                           // إرجاع النص المنسق
}

// 📈 دالة حساب اتجاه الترند (صاعد/هابط/مستقر)
function calculateTrend(data) {
    if (data.length < 2) return "مستقر";  // إذا كان لدينا أقل من نقطتين

    const recent = data.slice(-5);                          // أخذ آخر 5 نقاط
    const trend = recent[recent.length - 1] - recent[0];    // حساب الفرق بين الأول والأخير

    // 📊 تحديد الاتجاه بناءً على الفرق
    if (trend > 0) return "صاعد";
    if (trend < 0) return "هابط";
    return "مستقر";
}

// 🎯 دالة تحديث مؤشر الاتجاه في الواجهة
function updateTrendIndicator(trend) {
    const element = document.getElementById("trendIndicator");  // البحث عن عنصر المؤشر
    if (element) {
        element.textContent = trend;                            // تحديث النص
        element.className = "info-value trend-indicator";       // تعيين الفئة الأساسية

        // 🎨 إضافة فئة اللون المناسب حسب الاتجاه
        if (trend === "صاعد") {
            element.classList.add("positive");   // لون أخضر للاتجاه الصاعد
        } else if (trend === "هابط") {
            element.classList.add("negative");   // لون أحمر للاتجاه الهابط
        }
        // الاتجاه المستقر يبقى بدون لون إضافي
    }
}

// ========================================
// 🎯 دوال التحكم في الرسم البياني
// ========================================

// 🖥️ دالة التبديل بين الشاشة العادية والشاشة الكاملة
function toggleFullscreen(chartId) {
    try {
        // 🔍 البحث عن container الرسم البياني
        const chartContainer = document.getElementById(chartId).parentElement;

        // ✅ التحقق من الحالة الحالية للشاشة الكاملة
        if (!document.fullscreenElement) {
            // 📺 الدخول في وضع الشاشة الكاملة
            chartContainer.requestFullscreen().then(function() {
                // ⏳ تأخير قصير لضمان اكتمال التحويل
                setTimeout(function() {
                    window.dispatchEvent(new Event("resize"));  // تحديث حجم الرسم البياني
                }, 100);

                // 🔄 تغيير أيقونة الزر إلى "خروج من الشاشة الكاملة"
                const btn = document.getElementById("fullscreenBtn");
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-compress-alt"></i>';
                    btn.title = "خروج من الشاشة الكاملة";
                }

                // 📢 عرض رسالة نجاح
                showNotification("تم التبديل إلى الشاشة الكاملة", "success");
                
            }).catch(function(err) {
                // 🚫 معالجة أخطاء الشاشة الكاملة
                console.error("خطأ في الشاشة الكاملة:", err);
                showNotification("فشل في التبديل إلى الشاشة الكاملة", "error");
            });
        } else {
            // 🚪 الخروج من وضع الشاشة الكاملة
            document.exitFullscreen().then(function() {
                // 🔄 إعادة تعيين أيقونة الزر إلى "دخول الشاشة الكاملة"
                const btn = document.getElementById("fullscreenBtn");
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-expand-alt"></i>';
                    btn.title = "شاشة كاملة";
                }

                // 📢 عرض رسالة المعلومات
                showNotification("تم الخروج من الشاشة الكاملة", "info");
            });
        }
    } catch (error) {
        // 🚫 معالجة الأخطاء العامة
        console.error("خطأ في toggleFullscreen:", error);
        showNotification("حدث خطأ في التبديل للشاشة الكاملة", "error");
    }
}

// 📥 دالة تحميل الرسم البياني كصورة PNG
function downloadChart(chartId) {
    try {
        // 🔍 البحث عن الرسم البياني باستخدام Chart.js
        const chart = Chart.getChart(chartId);
        if (!chart) throw new Error("الرسم البياني غير موجود");  // رمي خطأ إذا لم يوجد

        // 🔗 إنشاء رابط تحميل
        const link = document.createElement("a");
        // 📝 تحديد اسم الملف مع التاريخ الحالي
        link.download = "chart-" + chartId + "-" + new Date().toISOString().split("T")[0] + ".png";
        // 🖼️ تحويل الرسم البياني إلى صورة base64
        link.href = chart.toBase64Image("image/png", 1.0);

        // 📎 إضافة الرابط للصفحة وتشغيل التحميل
        document.body.appendChild(link);
        link.click();                    // تشغيل التحميل
        document.body.removeChild(link); // إزالة الرابط بعد التحميل

        // 📢 عرض رسالة نجاح
        showNotification("تم تحميل الرسم البياني بنجاح", "success");
        
        // 📊 تحديث إحصائيات التحميل
        updateDownloadStats();
        
    } catch (error) {
        // 🚫 معالجة أخطاء التحميل
        console.error("خطأ في downloadChart:", error);
        showNotification("فشل في تحميل الرسم البياني", "error");
    }
}

// 🔄 دالة تحديث الرسم البياني
function refreshChart(chartId) {
    try {
        // 🔍 البحث عن الرسم البياني
        const chart = Chart.getChart(chartId);
        if (!chart) throw new Error("الرسم البياني غير موجود");

        // ⏳ عرض شاشة التحميل
        showChartLoading(chartId, true);

        // ⏱️ محاكاة عملية التحديث بتأخير
        setTimeout(function() {
            chart.update("active");              // تحديث الرسم البياني
            showChartLoading(chartId, false);    // إخفاء شاشة التحميل
            updateLastUpdateTime();              // تحديث وقت آخر تحديث
            showNotification("تم تحديث الرسم البياني", "success"); // عرض رسالة نجاح
        }, 1500);
        
    } catch (error) {
        // 🚫 معالجة أخطاء التحديث
        console.error("خطأ في refreshChart:", error);
        showChartLoading(chartId, false);    // إخفاء شاشة التحميل
        showNotification("فشل في تحديث الرسم البياني", "error");
    }
}

// ⏳ دالة إظهار/إخفاء شاشة التحميل
function showChartLoading(chartId, show) {
    if (typeof show === 'undefined') show = true;  // قيمة افتراضية
    
    const loadingEl = document.getElementById("chartLoading");  // البحث عن عنصر التحميل
    if (loadingEl) {
        loadingEl.style.display = show ? "flex" : "none";  // إظهار أو إخفاء العنصر
    }
}

// 📢 دالة عرض الإشعارات للمستخدم
function showNotification(message, type) {
    if (typeof type === 'undefined') type = "info";  // نوع الإشعار الافتراضي
    
    // 🖨️ طباعة الرسالة في وحدة التحكم
    console.log(type.toUpperCase() + ": " + message);

    // 🎨 إنشاء عنصر الإشعار المرئي
    const toast = document.createElement("div");
    const alertClass = type === "success" ? "success" : type === "error" ? "danger" : "info"; // تحديد فئة التنبيه
    toast.className = "alert alert-" + alertClass + " position-fixed";  // تعيين الفئات
    toast.style.cssText = "top: 20px; right: 20px; z-index: 9999; min-width: 300px;"; // تحديد الموضع والتنسيق

    // 🎯 تحديد الأيقونة المناسبة للإشعار
    const iconClass = type === "success" ? "check" : type === "error" ? "times" : "info";
    
    // 📝 بناء محتوى الإشعار
    toast.innerHTML = '<div class="d-flex align-items-center">' +
        '<i class="fas fa-' + iconClass + '-circle me-2"></i>' +  // الأيقونة
        message +  // النص
        '<button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>' + // زر الإغلاق
        '</div>';

    // 📎 إضافة الإشعار للصفحة
    document.body.appendChild(toast);
    
    // ⏰ إزالة الإشعار تلقائياً بعد 4 ثوانِ
    setTimeout(function() {
        if (toast.parentElement) {  // التأكد من وجود العنصر
            toast.remove();         // إزالة الإشعار
        }
    }, 4000);
}

// 📊 دالة تحديث إحصائيات التحميل
function updateDownloadStats() {
    const currentDownloads = localStorage.getItem("chartDownloads");       // قراءة العدد الحالي
    const downloads = parseInt(currentDownloads || "0") + 1;               // زيادة العدد
    localStorage.setItem("chartDownloads", downloads);                     // حفظ العدد الجديد
}

// ⏰ دالة تحديث وقت آخر تحديث
function updateLastUpdateTime() {
    // 🕒 تنسيق الوقت والتاريخ بالعربية
    const timeString = new Date().toLocaleString("ar-SA", {
        hour: "2-digit",      // ساعات بخانتين
        minute: "2-digit",    // دقائق بخانتين
        day: "2-digit",       // يوم بخانتين
        month: "2-digit",     // شهر بخانتين
        year: "numeric",      // سنة كاملة
    });

    // 🔄 تحديث عنصر الوقت في الواجهة
    const lastUpdateEl = document.getElementById("lastUpdate");
    if (lastUpdateEl) lastUpdateEl.textContent = timeString;
}

// 📱 دالة التعامل مع تغيير حجم الرسوم البيانية
function handleChartResize() {
    const instances = Chart.instances || {};  // الحصول على جميع instances الرسوم البيانية
    Object.values(instances).forEach(function(chart) {  // التكرار على كل رسم بياني
        if (chart && typeof chart.resize === "function") {  // التأكد من وجود دالة resize
            chart.resize();  // تحديث حجم الرسم البياني
        }
    });
}

// 📱 إضافة مستمع لتغيير حجم النافذة
window.addEventListener("resize", handleChartResize);

// ✅ رسالة اكتمال تحميل الملف
console.log("📊 تم تحميل ملف daily.js المُحسن");