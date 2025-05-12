const currencyData = {
    SAR: {
        label: "ريال سعودي",
        color: "rgba(75, 192, 192, 0.7)",
        borderColor: "rgb(75, 192, 192)",
    },
    KWD: {
        label: "دينار كويتي",
        color: "rgba(153, 102, 255, 0.7)",
        borderColor: "rgb(153, 102, 255)",
    },
};

// دالة لعرض الرسم البياني للمقارنة حسب العملة
function renderCurrencyChart(ctx, title, sarData, kwdData) {
    const datasets = [];

    if (sarData && sarData.length > 0) {
        datasets.push({
            label: currencyData.SAR.label,
            data: sarData,
            backgroundColor: currencyData.SAR.color,
            borderColor: currencyData.SAR.borderColor,
            borderWidth: 1,
        });
    }

    if (kwdData && kwdData.length > 0) {
        datasets.push({
            label: currencyData.KWD.label,
            data: kwdData,
            backgroundColor: currencyData.KWD.color,
            borderColor: currencyData.KWD.borderColor,
            borderWidth: 1,
        });
    }

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["المستحق", "المدفوع", "المتبقي"],
            datasets: datasets,
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "top",
                },
                title: {
                    display: true,
                    text: title,
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat("ar-SA").format(
                                    context.parsed.y
                                );
                            }
                            return label;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return new Intl.NumberFormat("ar-SA").format(value);
                        },
                    },
                },
            },
        },
    });
}

document.addEventListener("DOMContentLoaded", function () {
    // --- الرسم البياني: الحجوزات اليومية (Line Chart) ---
    // >>>>> تأكد إن الكود ده كله موجود هنا <<<<<
    const dailyCtx = document.getElementById("dailyBookingsChart"); // <-- لازم الـ ID ده يكون نفس الـ ID بتاع الـ canvas فوق
    const dailyLabels = window.chartData.dailyLabels; // <-- بياخد التواريخ من Controller
    const dailyData = window.chartData.dailyData; // <-- بياخد الأرقام من Controller

    if (dailyCtx && dailyLabels.length > 0) {
        // بيتأكد إن فيه canvas وبيانات
        new Chart(dailyCtx, {
            type: "line", // نوع الرسم: خطي
            data: {
                labels: dailyLabels, // التواريخ اللي تحت
                datasets: [
                    {
                        label: "عدد الحجوزات", // اسم الخط
                        data: dailyData, // الأرقام اللي هيرسمها
                        fill: true, // يلون تحت الخط
                        borderColor: "rgb(75, 192, 192)", // لون الخط
                        backgroundColor: "rgba(75, 192, 192, 0.2)", // لون التعبئة
                        tension: 0.1, // يخلي الخط منحني شوية
                    },
                ],
            },
            options: {
                // خيارات إضافية للرسم
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                }, // يخلي المحور Y يبدأ من صفر وأرقامه صحيحة
                plugins: {
                    legend: {
                        display: false,
                    }, // يخفي اسم الخط لو هو خط واحد
                    tooltip: {
                        // لما تقف بالماوس على نقطة
                        mode: "index",
                        intersect: false,
                        callbacks: {
                            title: function (tooltipItems) {
                                return "تاريخ: " + tooltipItems[0].label;
                            }, // يكتب التاريخ فوق
                            label: function (context) {
                                // يكتب عدد الحجوزات
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                }
                                return label;
                            },
                        },
                    },
                },
                hover: {
                    mode: "nearest",
                    intersect: true,
                },
            },
        });
    } else if (dailyCtx) {
        // لو مفيش بيانات يعرض رسالة
        dailyCtx.parentNode.innerHTML =
            '<p class="text-center text-muted">لا توجد بيانات لعرض الرسم البياني للحجوزات اليومية.</p>';
    }
    // >>>>> نهاية كود الرسم البياني اليومي <<<<<

    // --- بيانات الرسم البياني للشركات (Bar Chart) ---
    // ... (باقي أكواد الرسوم البيانية التانية) ...

    // --- كود تحديث وقت الساعة ---
    const timeDisplayElement = document.getElementById("watch-time-display");
    const dateDisplayElement = document.getElementById("watch-date-display"); // <-- جبنا عنصر التاريخ

    // متغيرات لتخزين الألوان الحالية
    let currentTimeColor = "white";
    let currentDateColor = "#8b22d8"; // اللون البنفسجي المبدئي

    function updateWatchTime() {
        if (timeDisplayElement) {
            // نتأكد إن العنصر موجود
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, "0"); // نجيب الساعات ونضيف صفر لو أقل من 10
            const minutes = String(now.getMinutes()).padStart(2, "0"); // نجيب الدقايق ونضيف صفر لو أقل من 10
            timeDisplayElement.textContent = `${hours}:${minutes}`; // نحدث محتوى العنصر
        }
    }
    // *** دالة تبديل الألوان ***
    function swapWatchColors() {
        if (timeDisplayElement && dateDisplayElement) {
            // تبديل الألوان المخزنة
            const tempColor = currentTimeColor;
            currentTimeColor = currentDateColor;
            currentDateColor = tempColor;

            // تطبيق الألوان الجديدة على العناصر
            timeDisplayElement.style.color = currentTimeColor;
            dateDisplayElement.style.color = currentDateColor;
        }
    }

    updateWatchTime(); // نشغلها مرة أول ما الصفحة تحمل
    setInterval(updateWatchTime, 60000); // نشغلها كل 60 ثانية (دقيقة)
    // --- نهاية كود تحديث وقت الساعة ---
    setInterval(swapWatchColors, 30000); // تبديل الألوان كل 30 ثانية

    // --- بيانات الرسم البياني للشركات ---
    const topCompaniesLabels = window.chartData.topCompaniesLabels; // <-- بياخد أسماء الشركات من Controller
    const topCompaniesDataPoints = window.chartData.topCompaniesRemaining; // <-- بياخد بيانات الشركات من Controller

    const ctxCompanies = document.getElementById("topCompaniesChart");
    if (ctxCompanies && topCompaniesLabels.length > 0) {
        // التأكد من وجود العنصر والبيانات
        new Chart(ctxCompanies, {
            type: "bar", // نوع الرسم: أعمدة
            data: {
                labels: topCompaniesLabels,
                datasets: [
                    {
                        label: "المتبقي (ريال)",
                        data: topCompaniesDataPoints,
                        backgroundColor: "rgba(220, 53, 69, 0.7)", // لون أحمر شفاف
                        borderColor: "rgba(220, 53, 69, 1)",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // مهم للحفاظ على الحجم المحدد في CSS
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // تنسيق الأرقام على المحور Y (اختياري)
                            callback: function (value, index, values) {
                                return value.toLocaleString("ar-SA") + " ريال"; // تنسيق الأرقام بالعربية السعودية
                            },
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    }, // إخفاء مفتاح الرسم (label)
                    tooltip: {
                        // تنسيق التلميح عند المرور (اختياري)
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label +=
                                        context.parsed.y.toLocaleString(
                                            "ar-SA"
                                        ) + " ريال";
                                }
                                return label;
                            },
                        },
                    },
                },
            },
        });
    }

    // --- بيانات الرسم البياني لجهات الحجز ---
    const topAgentsLabels = window.chartData.topAgentsLabels; // <-- بياخد أسماء جهات الحجز من Controller
    const topAgentsDataPoints = window.chartData.topAgentsRemaining; // <-- بياخد بيانات جهات الحجز من Controller

    const ctxAgents = document.getElementById("topAgentsChart");
    if (ctxAgents && topAgentsLabels.length > 0) {
        // التأكد من وجود العنصر والبيانات
        new Chart(ctxAgents, {
            type: "bar",
            data: {
                labels: topAgentsLabels,
                datasets: [
                    {
                        label: "المتبقي (ريال)",
                        data: topAgentsDataPoints,
                        backgroundColor: "rgba(255, 193, 7, 0.7)", // لون أصفر/برتقالي شفاف
                        borderColor: "rgba(255, 193, 7, 1)",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                // نفس الخيارات السابقة للاتساق
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value, index, values) {
                                return value.toLocaleString("ar-SA") + " ريال";
                            },
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label +=
                                        context.parsed.y.toLocaleString(
                                            "ar-SA"
                                        ) + " ريال";
                                }
                                return label;
                            },
                        },
                    },
                },
            },
        });
    }

    // تعديل الرسم البياني لمقارنة المبالغ المستحقة
    const ctxRemainingComparison = document.getElementById(
        "remainingComparisonChart"
    );

    if (ctxRemainingComparison) {
        // تحضير مجموعات البيانات حسب العملة بطريقة أكثر احترافية
        const datasets = [];

        // استخدام ألوان متناسقة للتمثيل البياني
        const colors = {
            SAR: {
                company: "rgba(78, 115, 223, 0.7)",
                agent: "rgba(78, 115, 223, 0.4)",
                border: "rgba(78, 115, 223, 1)",
            },
            KWD: {
                company: "rgba(54, 185, 204, 0.7)",
                agent: "rgba(54, 185, 204, 0.4)",
                border: "rgba(54, 185, 204, 1)",
            },
        };

        // إضافة بيانات الريال السعودي - مجموعة منفصلة للشركات وأخرى للجهات
        if (
            window.chartData.companiesRemainingByCurrency?.SAR > 0 ||
            window.chartData.agentsRemainingByCurrency?.SAR > 0
        ) {
            datasets.push({
                label: "مستحق لنا من الشركات (ريال)",
                data: [
                    parseFloat(
                        window.chartData.companiesRemainingByCurrency?.SAR || 0
                    ),
                    0,
                ],
                backgroundColor: [colors.SAR.company, "rgba(0,0,0,0)"],
                borderColor: colors.SAR.border,
                borderWidth: 1,
                barPercentage: 0.8,
            });

            datasets.push({
                label: "مستحق علينا لجهات الحجز (ريال)",
                data: [
                    0,
                    parseFloat(
                        window.chartData.agentsRemainingByCurrency?.SAR || 0
                    ),
                ],
                backgroundColor: [colors.SAR.agent, colors.SAR.agent],
                borderColor: colors.SAR.border,
                borderWidth: 1,
                barPercentage: 0.8,
            });
        }

        // إضافة بيانات الدينار الكويتي - مجموعة منفصلة للشركات وأخرى للجهات
        if (
            window.chartData.companiesRemainingByCurrency?.KWD > 0 ||
            window.chartData.agentsRemainingByCurrency?.KWD > 0
        ) {
            datasets.push({
                label: "مستحق لنا من الشركات (دينار)",
                data: [
                    parseFloat(
                        window.chartData.companiesRemainingByCurrency?.KWD || 0
                    ),
                    0,
                ],
                backgroundColor: [colors.KWD.company, "rgba(0,0,0,0)"],
                borderColor: colors.KWD.border,
                borderWidth: 1,
                barPercentage: 0.8,
            });

            datasets.push({
                label: "مستحق علينا لجهات الحجز (دينار)",
                data: [
                    0,
                    parseFloat(
                        window.chartData.agentsRemainingByCurrency?.KWD || 0
                    ),
                ],
                backgroundColor: ["rgba(0,0,0,0)", colors.KWD.agent],
                borderColor: colors.KWD.border,
                borderWidth: 1,
                barPercentage: 0.8,
            });
        }

        // إنشاء الرسم البياني بتصميم محسّن
        if (datasets.length > 0) {
            new Chart(ctxRemainingComparison, {
                type: "bar",
                data: {
                    labels: ["من الشركات", "لجهات الحجز"],
                    datasets: datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: "مقارنة المبالغ المستحقة حسب العملة",
                            font: {
                                size: 16,
                                weight: "bold",
                            },
                            padding: {
                                bottom: 15,
                            },
                        },
                        legend: {
                            position: "top",
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    if (context.parsed.y === 0) return null;

                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label = label.split(" (")[0] + ": ";
                                    }

                                    const currency =
                                        context.dataset.label.includes("دينار")
                                            ? "دينار"
                                            : "ريال";
                                    return (
                                        label +
                                        context.parsed.y.toLocaleString(
                                            "ar-SA"
                                        ) +
                                        " " +
                                        currency
                                    );
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [2, 4],
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString("ar-SA");
                                },
                            },
                        },
                    },
                },
            });
        } else {
            ctxRemainingComparison.parentNode.innerHTML =
                '<p class="text-center text-muted">لا توجد بيانات كافية لعرض مقارنة المتبقي.</p>';
        }
    }
    // --- *** بداية كود الرسم البياني لصافي الرصيد *** ---
    // --- الرسم البياني للمستحقات والالتزامات (الخطين) ---
    const ctxMultiLineBalance = document.getElementById("netBalanceChart"); // *** نجيب عنصر الرسم البياني من الـ DOM ***
    const balanceDates = window.chartData.dailyLabels; // *** نجيب التواريخ من البيانات اللي مررناها ***
    const receivableData = window.chartData.receivableBalances; // *** نجيب بيانات الأرصدة المستحقة والمدفوعة ***
    const payableData = window.chartData.payableBalances; // *** نجيب بيانات الأرصدة المستحقة والمدفوعة ***
    // *** نجيب مصفوفة تفاصيل الأحداث ***
    const dailyEventDetailsData = window.chartData.dailyEventDetails;

    if (
        ctxMultiLineBalance &&
        balanceDates &&
        balanceDates.length > 0 &&
        receivableData &&
        payableData
    ) {
        new Chart(ctxMultiLineBalance, {
            type: "line",
            data: {
                labels: balanceDates,
                datasets: [
                    {
                        label: "مستحق من الشركات (ريال)", // الخط الأخضر
                        data: receivableData,
                        borderColor: "rgb(75, 192, 75)",
                        // ... (باقي خصائص الخط الأخضر) ...
                    },
                    {
                        label: "مستحق للجهات (ريال)", // الخط الأحمر
                        data: payableData,
                        borderColor: "rgb(255, 99, 132)",
                        // ... (باقي خصائص الخط الأحمر) ...
                    },
                ],
            },
            options: {
                // ... (باقي الخيارات زي responsive, interaction, scales, legend) ...
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                    },
                    tooltip: {
                        // *** تعديل الـ Tooltip هنا ***
                        callbacks: {
                            title: function (tooltipItems) {
                                // السطر الأول: التاريخ
                                return "تاريخ: " + tooltipItems[0].label; // tooltipItems[0].label هو التاريخ 'd/m'
                            },
                            label: function (context) {
                                // السطر التاني والتالت: قيمة كل خط (الأرصدة)
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                if (context.parsed.y !== null) {
                                    label +=
                                        context.parsed.y.toLocaleString(
                                            "ar-SA"
                                        ) + " ريال";
                                }
                                return label;
                            },
                            // *** بداية الإضافة: عرض تفاصيل الأحداث بعد الأرصدة ***
                            afterBody: function (tooltipItems) {
                                // tooltipItems[0].label هو التاريخ 'd/m' اللي المستخدم واقف عليه
                                const dateLabel = tooltipItems[0].label;
                                // نجيب قايمة تفاصيل الأحداث بتاعة اليوم ده من البيانات اللي مررناها
                                const eventDetailsForDay =
                                    dailyEventDetailsData[dateLabel] || []; // لو مفيش أحداث، هتبقى مصفوفة فاضية

                                // لو فيه أحداث لليوم ده
                                if (eventDetailsForDay.length > 0) {
                                    // نعمل مصفوفة سطور جديدة للـ tooltip
                                    let lines = [];
                                    // نضيف سطر فاصل وعنوان
                                    lines.push(""); // سطر فاضي كفاصل
                                    lines.push("--- أحداث اليوم ---");
                                    // نضيف كل تفصيل حدث في سطر جديد
                                    eventDetailsForDay.forEach((detail) => {
                                        lines.push(detail); // كل عنصر في المصفوفة دي هيبقى سطر في الـ tooltip
                                    });
                                    return lines; // نرجع مصفوفة السطور عشان Chart.js يعرضها
                                }
                                // لو مفيش أحداث، نرجع مصفوفة فاضية (مش هيعرض حاجة زيادة)
                                return [];
                            },
                            // *** نهاية الإضافة ***
                        },
                    },
                },
            },
        });
    } else if (ctxMultiLineBalance) {
        ctxMultiLineBalance.parentNode.innerHTML =
            '<p class="text-center text-muted">لا توجد بيانات كافية لعرض اتجاه المستحقات والالتزامات.</p>';
    }
    // --- *** نهاية كود الرسم البياني لصافي الرصيد *** ---
// --- الرسم البياني لصافي الرصيد بالريال والدينار الكويتي ---

const ctxNetBalanceKWD = document.getElementById("netBalanceKWDChart"); // أضف Canvas جديد في الـ Blade بهذا الـ ID
const netBalanceDates = window.chartData.netBalanceDates || window.chartData.dailyLabels;
const netBalancesKWD = window.chartData.netBalancesKWD || [];
// const dailyEventDetailsData = window.chartData.dailyEventDetails; // نفس الأحداث

if (ctxNetBalanceKWD && netBalanceDates && netBalanceDates.length > 0 && netBalancesKWD.length > 0) {
    new Chart(ctxNetBalanceKWD, {
        type: "line",
        data: {
            labels: netBalanceDates,
            datasets: [
                {
                    label: "صافي الرصيد (دينار كويتي)",
                    data: netBalancesKWD,
                    borderColor: "rgba(54, 162, 235, 0.9)",
                    backgroundColor: "rgba(54, 162, 235, 0.15)",
                    borderWidth: 3,
                    tension: 0.2,
                    fill: true,
                    pointRadius: 5,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                },
                tooltip: {
                    callbacks: {
                        title: function (tooltipItems) {
                            return "تاريخ: " + tooltipItems[0].label;
                        },
                        label: function (context) {
                            let label = context.dataset.label || "";
                            if (label) label += ": ";
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toLocaleString("ar-SA") + " دينار";
                            }
                            return label;
                        },
                        afterBody: function (tooltipItems) {
                            const dateLabel = tooltipItems[0].label;
                            const eventDetailsForDay = dailyEventDetailsData[dateLabel] || [];
                            if (eventDetailsForDay.length > 0) {
                                let lines = [];
                                lines.push("");
                                lines.push("--- أحداث اليوم ---");
                                eventDetailsForDay.forEach((detail) => lines.push(detail));
                                return lines;
                            }
                            return [];
                        }
                    },
                },
            },
        },
    });
}
    // --- *** الرسم البياني الجديد: توزيع حجوزات الشركات (Pie) *** ---
    const topCompaniesBookingLabels = window.chartData.topCompaniesLabels; // <-- بياخد أسماء الشركات من Controller
    const topCompaniesBookingCounts =
        window.chartData.topCompaniesBookingCounts; // <-- بياخد بيانات حجوزات الشركات من Controller
    const totalCompanyBookings = window.chartData.totalCompanyBookings; // <-- بياخد إجمالي حجوزات الشركات من Controller
    const top5CompanyBookingsSum = topCompaniesBookingCounts.reduce(
        (a, b) => a + b,
        0
    );
    const otherCompanyBookings = totalCompanyBookings - top5CompanyBookingsSum;

    const ctxCompanyBookingDist = document.getElementById(
        "companyBookingDistributionChart"
    );

    // التأكد من وجود بيانات وأن مجموع حجوزات الشركات أكبر من صفر
    if (ctxCompanyBookingDist && totalCompanyBookings > 0) {
        let bookingDistLabels = [...topCompaniesBookingLabels];
        let bookingDistData = [...topCompaniesBookingCounts];

        // إضافة "أخرى" إذا كان هناك شركات أخرى
        if (otherCompanyBookings > 0) {
            bookingDistLabels.push("شركات أخرى");
            bookingDistData.push(otherCompanyBookings);
        }

        new Chart(ctxCompanyBookingDist, {
            type: "pie", // نوع الرسم: دائري
            data: {
                labels: bookingDistLabels,
                datasets: [
                    {
                        label: "عدد الحجوزات",
                        data: bookingDistData,
                        // يمكنك تحديد ألوان مختلفة لكل شريحة
                        backgroundColor: [
                            "rgba(0, 123, 255, 0.7)",
                            "rgba(40, 167, 69, 0.7)",
                            "rgba(255, 193, 7, 0.7)",
                            "rgba(23, 162, 184, 0.7)",
                            "rgba(108, 117, 125, 0.7)",
                            "rgba(160, 160, 160, 0.7)", // لون لـ "أخرى"
                        ],
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                let value = context.parsed || 0;
                                let percentage =
                                    totalCompanyBookings > 0
                                        ? (
                                              (value / totalCompanyBookings) *
                                              100
                                          ).toFixed(1)
                                        : 0;
                                label += value + " (" + percentage + "%)"; // عرض العدد والنسبة المئوية
                                return label;
                            },
                        },
                    },
                },
            },
        });
    }

    // بفرض أن هناك عنصر Canvas بهذا الاسم
    const currencyComparisonCtx = document.getElementById(
        "currencyComparisonChart"
    );
    if (currencyComparisonCtx) {
        const sarData = [
            chartData.totalDueFromCompaniesByCurrency.SAR || 0,
            chartData.companyPaymentsByCurrency.SAR || 0,
            (chartData.totalDueFromCompaniesByCurrency.SAR || 0) -
                (chartData.companyPaymentsByCurrency.SAR || 0),
        ];

        const kwdData = [
            chartData.totalDueFromCompaniesByCurrency.KWD || 0,
            chartData.companyPaymentsByCurrency.KWD || 0,
            (chartData.totalDueFromCompaniesByCurrency.KWD || 0) -
                (chartData.companyPaymentsByCurrency.KWD || 0),
        ];

        renderCurrencyChart(
            currencyComparisonCtx,
            "مقارنة المبالغ حسب العملة - الشركات",
            sarData,
            kwdData
        );
    }
});
