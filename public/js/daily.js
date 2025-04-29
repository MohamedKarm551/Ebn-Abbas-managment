

    document.addEventListener('DOMContentLoaded', function() {

        // --- الرسم البياني: الحجوزات اليومية (Line Chart) ---
        // >>>>> تأكد إن الكود ده كله موجود هنا <<<<<
        const dailyCtx = document.getElementById(
            'dailyBookingsChart'); // <-- لازم الـ ID ده يكون نفس الـ ID بتاع الـ canvas فوق
            const dailyLabels = window.chartData.dailyLabels; // <-- بياخد التواريخ من Controller
        const dailyData =  window.chartData.dailyData; // <-- بياخد الأرقام من Controller

        if (dailyCtx && dailyLabels.length > 0) { // بيتأكد إن فيه canvas وبيانات
            new Chart(dailyCtx, {
                type: 'line', // نوع الرسم: خطي
                data: {
                    labels: dailyLabels, // التواريخ اللي تحت
                    datasets: [{
                        label: 'عدد الحجوزات', // اسم الخط
                        data: dailyData, // الأرقام اللي هيرسمها
                        fill: true, // يلون تحت الخط
                        borderColor: 'rgb(75, 192, 192)', // لون الخط
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', // لون التعبئة
                        tension: 0.1 // يخلي الخط منحني شوية
                    }]
                },
                options: { // خيارات إضافية للرسم
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }, // يخلي المحور Y يبدأ من صفر وأرقامه صحيحة
                    plugins: {
                        legend: {
                            display: false
                        }, // يخفي اسم الخط لو هو خط واحد
                        tooltip: { // لما تقف بالماوس على نقطة
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return 'تاريخ: ' + tooltipItems[0].label;
                                }, // يكتب التاريخ فوق
                                label: function(context) { // يكتب عدد الحجوزات
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: true
                    }
                }
            });
        } else if (dailyCtx) { // لو مفيش بيانات يعرض رسالة
            dailyCtx.parentNode.innerHTML =
                '<p class="text-center text-muted">لا توجد بيانات لعرض الرسم البياني للحجوزات اليومية.</p>';
        }
        // >>>>> نهاية كود الرسم البياني اليومي <<<<<

        // --- بيانات الرسم البياني للشركات (Bar Chart) ---
        // ... (باقي أكواد الرسوم البيانية التانية) ...

    


    // --- كود تحديث وقت الساعة ---
    const timeDisplayElement = document.getElementById('watch-time-display');
    const dateDisplayElement = document.getElementById('watch-date-display'); // <-- جبنا عنصر التاريخ
    
// متغيرات لتخزين الألوان الحالية
let currentTimeColor = 'white';
let currentDateColor = '#8b22d8'; // اللون البنفسجي المبدئي

    function updateWatchTime() {
        if (timeDisplayElement) { // نتأكد إن العنصر موجود
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0'); // نجيب الساعات ونضيف صفر لو أقل من 10
            const minutes = String(now.getMinutes()).padStart(2,
                '0'); // نجيب الدقايق ونضيف صفر لو أقل من 10
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
    const topCompaniesDataPoints = window.chartData.topCompaniesRemaining ; // <-- بياخد بيانات الشركات من Controller

    const ctxCompanies = document.getElementById('topCompaniesChart');
    if (ctxCompanies && topCompaniesLabels.length > 0) { // التأكد من وجود العنصر والبيانات
        new Chart(ctxCompanies, {
            type: 'bar', // نوع الرسم: أعمدة
            data: {
                labels: topCompaniesLabels,
                datasets: [{
                    label: 'المتبقي (ريال)',
                    data: topCompaniesDataPoints,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)', // لون أحمر شفاف
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // مهم للحفاظ على الحجم المحدد في CSS
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { // تنسيق الأرقام على المحور Y (اختياري)
                            callback: function(value, index, values) {
                                return value.toLocaleString('ar-SA') +
                                    ' ريال'; // تنسيق الأرقام بالعربية السعودية
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }, // إخفاء مفتاح الرسم (label)
                    tooltip: { // تنسيق التلميح عند المرور (اختياري)
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('ar-SA') + ' ريال';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- بيانات الرسم البياني لجهات الحجز ---
    const topAgentsLabels = window.chartData.topAgentsLabels; // <-- بياخد أسماء جهات الحجز من Controller
    const topAgentsDataPoints = window.chartData.topAgentsRemaining; // <-- بياخد بيانات جهات الحجز من Controller

    const ctxAgents = document.getElementById('topAgentsChart');
    if (ctxAgents && topAgentsLabels.length > 0) { // التأكد من وجود العنصر والبيانات
        new Chart(ctxAgents, {
            type: 'bar',
            data: {
                labels: topAgentsLabels,
                datasets: [{
                    label: 'المتبقي (ريال)',
                    data: topAgentsDataPoints,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)', // لون أصفر/برتقالي شفاف
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }]
            },
            options: { // نفس الخيارات السابقة للاتساق
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return value.toLocaleString('ar-SA') + ' ريال';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('ar-SA') + ' ريال';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- *** الرسم البياني الجديد: مقارنة إجمالي المتبقي (Doughnut) *** ---
    const totalRemainingFromCompanies = window.chartData.totalRemainingFromCompanies; // <-- بياخد إجمالي المتبقي من الشركات
    const totalRemainingToHotels = window.chartData.totalRemainingToHotels; // <-- بياخد إجمالي المتبقي لجهات الحجز
    const ctxRemainingComparison = document.getElementById('remainingComparisonChart');

    if (ctxRemainingComparison && (totalRemainingFromCompanies > 0 || totalRemainingToHotels > 0)) {
        new Chart(ctxRemainingComparison, {
            type: 'doughnut', // نوع الرسم: دائري مجوف
            data: {
                labels: ['متبقي من الشركات', 'متبقي لجهات الحجز'],
                datasets: [{
                    label: 'المبلغ (ريال)',
                    data: [totalRemainingFromCompanies, totalRemainingToHotels],
                    backgroundColor: [
                        'rgba(220, 53, 69, 0.7)', // أحمر للشركات
                        'rgba(255, 193, 7, 0.7)' // أصفر/برتقالي للجهات
                    ],
                    borderColor: [
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top', // مكان ظهور مفتاح الرسم
                    },
                    tooltip: { // تنسيق التلميح
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed.toLocaleString('ar-SA') + ' ريال';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
// --- *** بداية كود الرسم البياني لصافي الرصيد *** ---
const ctxNetBalance = document.getElementById('netBalanceChart');
// !!! هام: يجب أن يتم تمرير هذه المتغيرات من الـ Controller
const netBalanceDates = window.chartData.netBalanceDates; // <-- بياخد التواريخ من Controller
const netBalanceData =  window.chartData.netBalances; // <-- بياخد بيانات صافي الرصيد من Controller

if (ctxNetBalance && netBalanceDates.length > 0 && netBalanceData.length > 0) {
    new Chart(ctxNetBalance, {
        type: 'line', // نوع الرسم: خطي
        data: {
            labels: netBalanceDates, // التواريخ على المحور الأفقي
            datasets: [{
                label: 'صافي الرصيد (ريال)', // اسم الخط
                data: netBalanceData,      // بيانات الرصيد على المحور العمودي
                fill: true,               // تلوين المنطقة تحت الخط
                borderColor: 'rgb(54, 162, 235)', // لون الخط (أزرق)
                backgroundColor: 'rgba(54, 162, 235, 0.2)', // لون التعبئة شفاف
                tension: 0.1,             // انحناء بسيط للخط
                // (اختياري متقدم) تغيير اللون حسب القيمة
                segment: {
                    borderColor: ctx => {
                        // لو القيمة قبل أو بعد النقطة الحالية < 0 خلي اللون أحمر
                        if (ctx.p0.parsed.y < 0 || ctx.p1.parsed.y < 0) {
                            return 'rgb(255, 99, 132)'; // أحمر
                        }
                        // وإلا خليه أزرق
                        return 'rgb(54, 162, 235)'; // أزرق
                    },
                    backgroundColor: ctx => {
                         if (ctx.p0.parsed.y < 0 || ctx.p1.parsed.y < 0) {
                            return 'rgba(255, 99, 132, 0.2)'; // أحمر شفاف
                        }
                        return 'rgba(54, 162, 235, 0.2)'; // أزرق شفاف
                    }
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    // beginAtZero: false, // مهم عشان يظهر القيم السالبة صح
                    ticks: {
                        callback: function(value, index, values) {
                            return value.toLocaleString('ar-SA') + ' ريال'; // تنسيق الأرقام
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true // ممكن نظهر اسم الخط هنا
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toLocaleString('ar-SA') + ' ريال';
                            }
                            return label;
                        }
                    }
                }
            },
            hover: {
                mode: 'nearest',
                intersect: true
            }
        }
    });
} else if (ctxNetBalance) { // لو مفيش بيانات
     ctxNetBalance.parentNode.innerHTML = '<p class="text-center text-muted">لا توجد بيانات كافية لعرض اتجاه صافي الرصيد.</p>';
}
// --- *** نهاية كود الرسم البياني لصافي الرصيد *** ---


    // --- *** الرسم البياني الجديد: توزيع حجوزات الشركات (Pie) *** ---
    const topCompaniesBookingLabels = window.chartData.topCompaniesLabels; // <-- بياخد أسماء الشركات من Controller
    const topCompaniesBookingCounts = window.chartData.topCompaniesBookingCounts; // <-- بياخد بيانات حجوزات الشركات من Controller
    const totalCompanyBookings = window.chartData.totalCompanyBookings; // <-- بياخد إجمالي حجوزات الشركات من Controller
    const top5CompanyBookingsSum = topCompaniesBookingCounts.reduce((a, b) => a + b, 0);
    const otherCompanyBookings = totalCompanyBookings - top5CompanyBookingsSum;

    const ctxCompanyBookingDist = document.getElementById('companyBookingDistributionChart');

    // التأكد من وجود بيانات وأن مجموع حجوزات الشركات أكبر من صفر
    if (ctxCompanyBookingDist && totalCompanyBookings > 0) {
        let bookingDistLabels = [...topCompaniesBookingLabels];
        let bookingDistData = [...topCompaniesBookingCounts];

        // إضافة "أخرى" إذا كان هناك شركات أخرى
        if (otherCompanyBookings > 0) {
            bookingDistLabels.push('شركات أخرى');
            bookingDistData.push(otherCompanyBookings);
        }

        new Chart(ctxCompanyBookingDist, {
            type: 'pie', // نوع الرسم: دائري
            data: {
                labels: bookingDistLabels,
                datasets: [{
                    label: 'عدد الحجوزات',
                    data: bookingDistData,
                    // يمكنك تحديد ألوان مختلفة لكل شريحة
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(160, 160, 160, 0.7)' // لون لـ "أخرى"
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                let value = context.parsed || 0;
                                let percentage = totalCompanyBookings > 0 ? ((value /
                                    totalCompanyBookings) * 100).toFixed(1) : 0;
                                label += value + ' (' + percentage +
                                    '%)'; // عرض العدد والنسبة المئوية
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }


});