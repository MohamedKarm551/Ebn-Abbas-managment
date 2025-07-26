// ================================================
// الدوال المساعدة العامة:
// ================================================

// -----------------------------------------------
// دالة العرض العامة للـ Alert (التنبيه)
// -----------------------------------------------
let globalAlertDiv = null; // متغير عام علشان نخزن العنصر الخاص بالAlert لو موجود حاليًا

// دالة showAlert: بتعرض تنبيه (alert) يحتوي على تفاصيل الحجوزات
// البارامترات:
//   message: الرسالة HTML اللي هتتعرض داخل الـ Alert
//   count: عدد الحجوزات المحددة
//   detailsTextArray: مصفوفة بتحتوي تفاصيل الحجوزات كنصوص
//   total: الإجمالي (المبلغ الكلي)
function showAlert(message, count, detailsTextArray, totalsByCurrency) {
    // لو كان عندنا Alert موجود قبل كده، نحذفه
    if (globalAlertDiv) globalAlertDiv.remove();

    // إنشاء عنصر div جديد للـ Alert
    globalAlertDiv = document.createElement("div");
    globalAlertDiv.className =
        "alert alert-danger shadow-lg d-flex flex-column align-items-center justify-content-center";
    globalAlertDiv.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 800px;
        max-height: 80vh; /* أقصى ارتفاع */
        padding: 20px;
        font-size: 14px;
        z-index: 1050;
        background-color: rgba(220, 53, 69, 0.97);
        color: white;
        direction: rtl;
        border-radius: 12px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.35);
        text-align: right;
        overflow-y: auto; /* السماح بالتمرير العمودي */
        overflow-x: hidden; /* منع التمرير الأفقي */
    `;

    // بناء الكاردات
    const cardsHTML = detailsTextArray
        .map((detail, index) => {
            const [clientName, hotelName, ...rest] = detail.split(" - ");
            const fullDetails = detail; // التفاصيل الكاملة
            return `
            <div class="card text-white bg-transparent border-secondary m-2" 
                 style="width: auto; cursor: pointer;" 
                 title="اضغط لنسخ التفاصيل"
                 data-bs-toggle="popover" 
                 data-bs-trigger="hover focus" 
                 data-bs-content="${fullDetails}">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-1">${index + 1}. ${clientName}</h6>
                    <p class="card-subtitle text-muted small">${hotelName}</p>
                    <p class="card-text d-none">${fullDetails}</p>
                </div>
            </div>
        `;
        })
        .join("");
    // بناء نص عرض الإجماليات حسب العملة
    let totalHtml = "";
    for (const currency in totalsByCurrency) {
        const currencySymbol = currency === "KWD" ? "دينار" : "ريال";
        totalHtml += `<div>${totalsByCurrency[currency].toFixed(
            2
        )} ${currencySymbol}</div>`;
    }
    // بنحط الرسالة HTML جوا العنصر
    globalAlertDiv.innerHTML = `
        <div class="d-flex flex-column align-items-center">
            <h5 class="mb-3" style="position: sticky; top: 0; background-color: rgba(220, 53, 69, 0.97); z-index: 10; padding: 10px; width: 100%;">تم تحديد ${count} حجوزات</h5>
            <div class="d-flex flex-wrap justify-content-center">
                ${cardsHTML}
            </div>
            <h4 class="mb-3"> إجمالي المطلوب على هذه الحجوزات:</h4>
            <div class="mb-3">${totalHtml}</div>
            <div class="d-flex justify-content-center mt-2">
                <button type="button" class="btn btn-light btn-sm mx-2 copyAlertBtn">نسخ بيانات الحجوزات</button>
                <button type="button" class="btn btn-outline-light btn-sm closeAlertBtn">إغلاق</button>
            </div>
        </div>
    `;

    // بنضيف العنصر للـ body علشان يظهر في الصفحة
    document.body.appendChild(globalAlertDiv);

    // -------------------------------------------
    // التعامل مع زر إغلاق الـ Alert:
    const closeAlertBtn = globalAlertDiv.querySelector(".closeAlertBtn");
    if (closeAlertBtn) {
        closeAlertBtn.addEventListener("click", function () {
            if (globalAlertDiv) globalAlertDiv.remove();
            globalAlertDiv = null;
        });
    }

    // -------------------------------------------
    // التعامل مع زر النسخ (Copy) للـ Alert:
    const copyAlertBtn = globalAlertDiv.querySelector(".copyAlertBtn");
    if (copyAlertBtn) {
        copyAlertBtn.addEventListener("click", function () {
            let alertText = `تقرير الحجوزات (${count} حجز محدد)\n------------------------------------\n`;
            detailsTextArray.forEach((detail, index) => {
                alertText += `${index + 1}. ${detail}\n`;
            });
            alertText += `------------------------------------\n`;
            alertText += `الإجماليات:\n`;
            for (const currency in totalsByCurrency) {
                const currencySymbol =
                    currency === "KWD" ? "دينار كويتي" : "ريال سعودي";
                alertText += `${totalsByCurrency[currency].toFixed(
                    2
                )} ${currencySymbol}\n`;
            }
            navigator.clipboard
                .writeText(alertText)
                .then(() => {
                    copyAlertBtn.textContent = "تم النسخ!";
                    copyAlertBtn.classList.remove("btn-light");
                    copyAlertBtn.classList.add("btn-success");
                    setTimeout(() => {
                        if (copyAlertBtn) {
                            copyAlertBtn.textContent = "نسخ";
                            copyAlertBtn.classList.remove("btn-success");
                            copyAlertBtn.classList.add("btn-light");
                        }
                    }, 2000);
                })
                .catch((err) => {
                    console.error("Failed to copy text: ", err);
                });
        });
    }

    // -------------------------------------------
    // التعامل مع الكاردات (نسخ التفاصيل عند الضغط):
    const cards = globalAlertDiv.querySelectorAll(".card");
    cards.forEach((card) => {
        card.addEventListener("click", function () {
            const detailText = this.querySelector(".card-text").textContent;
            navigator.clipboard
                .writeText(detailText)
                .then(() => {
                    showNotification("تم نسخ التفاصيل!", "success");
                })
                .catch((err) => {
                    console.error("Failed to copy text: ", err);
                });
        });
    });

    // -------------------------------------------
    // تفعيل الـ Popover باستخدام Bootstrap:
    const popoverTriggerList = [].slice.call(
        globalAlertDiv.querySelectorAll('[data-bs-toggle="popover"]')
    );
    popoverTriggerList.forEach(function (popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl, {
            html: true,
            placement: "top",
        });
    });
}

// -----------------------------------------------
// دالة التنبيه (Notification) العامة
// تعرض رسالة تنبيهية مؤقتة في أعلى الصفحة
// البارامترات:
//   message: نص الرسالة
//   type: نوع التنبيه (مثلاً 'info', 'danger'، إلخ)
function showNotification(message, type = "info") {
    // بنشوف إذا كان في تنبيه موجود، لو لقاوه بنمسحه
    const existingNotification = document.getElementById("temp-notification");
    if (existingNotification) existingNotification.remove();

    // إنشاء عنصر div للتنبيه
    const notificationDiv = document.createElement("div");
    notificationDiv.id = "temp-notification"; // بنحدد ID علشان نقدر نلاقيه بعدين
    notificationDiv.className = `alert alert-${type} fixed-top shadow`;
    notificationDiv.style.cssText = `
        position: fixed;   /* العنصر ثابت */
        top: 80px;         /* من فوق 80px */
        left: 50%;         /* في نص الشاشة أفقياً */
        transform: translateX(-50%); /* علشان يتمركز */
        width: auto;       /* العرض تلقائي حسب المحتوى */
        max-width: 90%;    /* أقصى عرض 90% */
        padding: 10px 20px; /* حشوة داخلية */
        font-size: 14px;   /* حجم الخط */
        z-index: 1055;     /* ترتيب ظهور عالي */
        direction: rtl;    /* اتجاه من اليمين لليسار */
        text-align: center; /* محاذاة النص في النص */
        border-radius: 8px; /* حواف دائرية */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* ظل خفيف */
    `;
    // نحط رسالة التنبيه
    notificationDiv.textContent = message;
    // نضيف التنبيه للـ body
    document.body.appendChild(notificationDiv);

    // نعتمد مؤقت بعد 3.5 ثانية علشان نشيل التنبيه
    setTimeout(() => {
        const currentNotification =
            document.getElementById("temp-notification");
        if (currentNotification) currentNotification.remove();
    }, 3500);
}

// ================================================
// دالة التهيئة الرئيسية لوظيفة "Booking Selector"
// ================================================

// دالة initializeBookingSelector بتستقبل 3 بارامترات:
//   tableId: الـ ID بتاع جدول الحجوزات
//   selectBtnId: الـ ID بتاع زر تحديد النطاق
//   resetBtnId: الـ ID بتاع زر إعادة تعيين النطاق
function initializeBookingSelector(tableId, selectBtnId, resetBtnId) {
    // بنستدعي العناصر من الـ DOM باستخدام الـ IDs الممررة
    const table = document.getElementById(tableId);
    const selectRangeBtn = document.getElementById(selectBtnId);
    const resetRangeBtn = document.getElementById(resetBtnId);

    // لو حاجة من العناصر مش موجودة، نطبع رسالة خطأ في الكونصول ونخرج من الدالة
    if (!table || !selectRangeBtn || !resetRangeBtn) {
        console.error(
            `Booking Selector Error: Missing elements for table ID "${tableId}". Check IDs.`
        );
        return; // نخرج من الدالة
    }

    // بنجيب كل الـ checkboxes اللي جوا الجدول واللي ليها كلاس booking-checkbox
    const checkboxes = table.querySelectorAll(".booking-checkbox");
    // متغيرين لتحديد نقطة البداية والنهاية للنطاق
    let startCheckbox = null;
    let endCheckbox = null;

    // -----------------------------------------------------
    // دوال داخلية تُستخدم ضمن عملية التهيئة - عملية الاختيار والتحديد
    // -----------------------------------------------------

    // دالة handleCheckboxSelection:
    // بتتعامل مع متى نختار نقطة بداية وأي نقطة نهاية
    function handleCheckboxSelection(checkbox) {
        // لو مش محدد نقطة بداية، يبقى ده هو البداية
        if (startCheckbox === null) {
            startCheckbox = checkbox;
            // بنضيف كلاس range-start للصف لتعليم نقطة البداية
            checkbox.closest("tr").classList.add("range-start");
            // بنعرض تنبيه مخصص بنستخدم الدالة showNotification
            showNotification(
                "تم تحديد نقطة البداية. الرجاء تحديد نقطة النهاية.",
                "info"
            );
        } else if (endCheckbox === null && checkbox !== startCheckbox) {
            // لو نقطة البداية موجودة ومفيش نقطة نهاية، ونحن اخترنا خانة مختلفة، يبقى دي نقطة النهاية
            endCheckbox = checkbox;
            checkbox.closest("tr").classList.add("range-end");
            showNotification(
                'تم تحديد نقطة النهاية. اضغط على زر "تحديد النطاق".',
                "success"
            );
        } else {
            // لو تم تحديد نقطتين من قبل، يبدأ اختيار جديد:
            clearRangeSelectionVisuals();
            startCheckbox = checkbox; // بتبدأ اختيار جديد من هنا
            endCheckbox = null;
            checkbox.closest("tr").classList.add("range-start");
            showNotification(
                "تم تحديد نقطة بداية جديدة. الرجاء تحديد نقطة النهاية.",
                "warning"
            );
        }
        // بعد كل اختيار، بنحدث الصفوف والعرض
        updateSelectedRows();
    }

    // دالة clearRangeSelectionVisuals:
    // بتشيل كل الإشارات البصرية لنقاط البداية والنهاية (زي الخلفيات والألوان) من الصفوف
    function clearRangeSelectionVisuals() {
        table
            .querySelectorAll("tr.range-start, tr.range-end")
            .forEach((row) => {
                row.classList.remove("range-start", "range-end");
                // لو الصف مش متحدد كلياً، بنرجعله الستايل الأساسي
                if (!row.classList.contains("selected-row")) {
                    row.style.backgroundColor = "";
                    row.style.color = "";
                    row.style.fontWeight = "";
                } else {
                    // لو الصف متحدد، نخليه بالمظهر الخاص بالتحديد
                    row.style.backgroundColor = "rgba(220, 53, 69, 0.3)";
                    row.style.color = "#fff";
                    row.style.fontWeight = "bold";
                }
            });
    }

    // دالة formatCombinedDate:
    // بتنسق التاريخ بحيث تعرض التاريخ الميلادي والتاريخ الهجري مع بعض
    function formatCombinedDate(dateString) {
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return "تاريخ غير صالح";
            }

            // تنسيق التاريخ الميلادي
            const gregorianOptions = { day: "numeric", month: "long" };
            const gregorianFormatted = date.toLocaleDateString(
                "ar-EG",
                gregorianOptions
            );

            // تنسيق التاريخ الهجري
            const hijriOptions = {
                day: "numeric",
                month: "long",
                calendar: "islamic",
            };
            const hijriFormatted = date.toLocaleDateString(
                "ar-SA-u-ca-islamic",
                hijriOptions
            );

            // دمج التاريخ الميلادي والهجري
            return `${gregorianFormatted} (${hijriFormatted})`;
        } catch (e) {
            console.error("Error formatting combined date:", dateString, e);
            return "خطأ في التاريخ";
        }
    }

    // دالة updateSelectedRows:
    // بتعمل تحديث للصفوف المختارة وتحسب الإجمالي وتعرض تفاصيل الحجوزات في تنبيه
    function updateSelectedRows() {
        let totalsByCurrency = {}; // الإجماليات حسب العملة
        let bookingDetailsHTML = []; // تفاصيل الحجوزات (HTML)
        let bookingDetailsText = []; // تفاصيل الحجوزات (نص)
        let selectedCount = 0; // عدد الحجوزات المحددة

        checkboxes.forEach((checkbox, index) => {
            const row = checkbox.closest("tr");
            if (checkbox.checked) {
                selectedCount++;
                row.classList.add("selected-row");

                // قراءة البيانات من الـ data-attributes
                const rooms = parseInt(checkbox.dataset.rooms, 10) || 0;
                const days = parseInt(checkbox.dataset.days, 10) || 0;
                const costPrice = parseFloat(checkbox.dataset.costPrice) || 0;
                // قراءة العملة - إذا لم تكن موجودة، نفترض أنها SAR
                const currency = checkbox.dataset.currency || "SAR";
                const currencySymbol = currency === "KWD" ? "دينار" : "ريال";

                // تنسيق التواريخ
                const checkInFormatted = formatCombinedDate(
                    checkbox.dataset.checkIn
                );
                const checkOutFormatted = formatCombinedDate(
                    checkbox.dataset.checkOut
                );

                // حساب المستحق الكلي
                const computedDue = rooms * days * costPrice;
                // قراءة بيانات الدفع من الـ data attributes (الأهم)
                const paymentAmount =
                    parseFloat(checkbox.dataset.paymentAmount) || 0;
                const paymentStatus =
                    checkbox.dataset.paymentStatus || "not_paid";
                // ترجمة حالة الدفع
                let paymentStatusLabel = "غير مدفوع";
                if (paymentStatus === "fully_paid" || paymentStatus === "paid")
                    paymentStatusLabel = "مدفوع بالكامل";
                else if (paymentStatus === "partially_paid")
                    paymentStatusLabel = "مدفوع جزئياً";

                // إضافة المبلغ إلى الإجمالي حسب العملة
                if (!totalsByCurrency[currency]) {
                    totalsByCurrency[currency] = 0;
                }
                totalsByCurrency[currency] += computedDue;

                // بناء تفاصيل الحجز بالشكل الجديد
                bookingDetailsHTML.push(`
    <li class="list-group-item d-flex justify-content-between align-items-start bg-transparent text-white border-secondary">
        <span class="badge bg-light text-dark rounded-pill me-3">${
            index + 1
        }</span>
        <div class="ms-0 me-auto text-start">
            <div class="fw-bold">(${checkbox.dataset.clientName}) - [[${
                    checkbox.dataset.hotelName
                }]]</div>
            <small>
                {دخول: ${checkInFormatted} | خروج: ${checkOutFormatted}} | 
                ${rooms} غرف | ${days} ليالي × ${costPrice.toFixed(
                    2
                )} = ${computedDue.toFixed(2)} ${currencySymbol}<br>
                <span class="text-info">المبلغ المدفوع: ${Number(
                    paymentAmount
                ).toFixed(2)}</span>
                &nbsp;|&nbsp;
                <span class="text-warning">حالة الدفع: ${paymentStatusLabel}</span>
            </small>
        </div>
    </li>
`);

                bookingDetailsText.push(
                    `(${checkbox.dataset.clientName}) - [[${
                        checkbox.dataset.hotelName
                    }]] | {دخول: ${checkInFormatted} | خروج: ${checkOutFormatted}} | ${rooms} غرف | ${days} ليالي | ${costPrice.toFixed(
                        2
                    )} ${currencySymbol} | الإجمالي: ${computedDue.toFixed(
                        2
                    )} ${currencySymbol} | المبلغ المدفوع: ${Number(
                        paymentAmount
                    ).toFixed(2)} | حالة الدفع: ${paymentStatusLabel}`
                );
            } else {
                row.classList.remove("selected-row");
            }
        });

        // تحديث واجهة المستخدم
        if (selectedCount > 0) {
            // إنشاء HTML للإجماليات حسب العملة
            let totalsHtml = "";
            for (const currency in totalsByCurrency) {
                const currencySymbol = currency === "KWD" ? "دينار" : "ريال";
                totalsHtml += `<div>${totalsByCurrency[currency].toFixed(
                    2
                )} ${currencySymbol}</div>`;
            }

            const alertMessage = `
            <div class="d-flex flex-column align-items-center">
                <h5 class="mb-3">تم تحديد ${selectedCount} حجوزات</h5>
                <ul class="list-group list-group-flush w-100 mb-3">
                    ${bookingDetailsHTML.join("")}
                </ul>
                <h4 class="mb-3">الإجمالي:</h4>
                <div class="mb-3">${totalsHtml}</div>
                <div class="d-flex justify-content-center mt-2">
                    <button type="button" class="btn btn-light btn-sm mx-2 copyAlertBtn">نسخ</button>
                    <button type="button" class="btn btn-outline-light btn-sm closeAlertBtn">إغلاق</button>
                </div>
            </div>
        `;
            showAlert(
                alertMessage,
                selectedCount,
                bookingDetailsText,
                totalsByCurrency
            );
        } else {
            if (globalAlertDiv) {
                globalAlertDiv.remove();
                globalAlertDiv = null;
            }
        }
    }

    // -----------------------------------------------------
    // ربط الأحداث (Event Binding) للعناصر
    // -----------------------------------------------------

    // بنضيف مستمع للحدث "click" على كل صف في الجدول
    table.querySelectorAll("tbody tr").forEach((row) => {
        row.addEventListener("click", function () {
            const checkbox = row.querySelector(".booking-checkbox");
            if (checkbox) {
                // نتأكد إن الـ checkbox موجود
                handleCheckboxSelection(checkbox);
            }
        });
    });

    // بنضيف مستمعين أحداث لكل checkbox: click وchange
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener("click", function (event) {
            event.stopPropagation(); // منع انتشار الحدث علشان الصف مايتأثرش
            handleCheckboxSelection(this);
        });
        checkbox.addEventListener("change", function () {
            updateSelectedRows();
        });
    });

    // مستمع حدث زر إعادة التعيين:
    resetRangeBtn.addEventListener("click", function () {
        // بنمسح إشارات النطاق من الصفوف
        clearRangeSelectionVisuals();
        // بنضبط المتغيرات لنقاط البداية والنهاية
        startCheckbox = null;
        endCheckbox = null;
        // بنشيل تحديد جميع checkboxes
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
        });
        updateSelectedRows();
    });

    // مستمع حدث زر تحديد النطاق:
    selectRangeBtn.addEventListener("click", function () {
        // لو مش محددين نقطة بداية ونهاية، بنعرض تنبيه
        if (!startCheckbox || !endCheckbox) {
            showNotification(
                "الرجاء تحديد نقطة البداية ونقطة النهاية أولاً.",
                "danger"
            );
            return;
        }
        // بنحول مجموعة checkboxes لمصفوفة علشان نقدر نشتغل عليها
        const checkboxesArray = Array.from(checkboxes);
        const startIndex = checkboxesArray.indexOf(startCheckbox);
        const endIndex = checkboxesArray.indexOf(endCheckbox);
        if (startIndex === -1 || endIndex === -1) {
            showNotification("حدث خطأ في تحديد النطاق.", "danger");
            return;
        }
        // بنحسب أقل وأعلى مؤشر علشان نحدد النطاق الكامل
        const minIndex = Math.min(startIndex, endIndex);
        const maxIndex = Math.max(startIndex, endIndex);
        // بنحدد كل checkboxes اللي في النطاق
        for (let i = minIndex; i <= maxIndex; i++) {
            if (checkboxesArray[i]) {
                checkboxesArray[i].checked = true;
            }
        }
        // بنمسح تأثير النطاق بعد التحديد
        clearRangeSelectionVisuals();
        startCheckbox = null;
        endCheckbox = null;
        updateSelectedRows();
    });
} // نهاية دالة initializeBookingSelector
