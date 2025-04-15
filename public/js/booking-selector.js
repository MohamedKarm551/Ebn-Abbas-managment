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
function showAlert(message, count, detailsTextArray, total) {
    // لو كان عندنا Alert موجود قبل كده، نحذفه
    if (globalAlertDiv) globalAlertDiv.remove();
    
    // إنشاء عنصر div جديد للـ Alert
    globalAlertDiv = document.createElement('div');
    // بنحدد الكلاس علشان نستخدم الأنماط من Bootstrap والستايل المخصص
    globalAlertDiv.className = 'alert alert-danger fixed-top shadow-lg';
    // بنحدد الستايل مباشرة باستخدام cssText
    globalAlertDiv.style.cssText = `
        position: fixed;    /* تثبيت العنصر في مكانه */
        top: 10px;          /* مسافة 10px من فوق */
        left: 50%;          /* في نص الصفحة أفقياً */
        transform: translateX(-50%);  /* علشان العنصر يتتمركز تماماً */
        width: 90%;         /* العرض 90% من الشاشة */
        max-width: 700px;   /* أقصى عرض 700px */
        padding: 25px;      /* حشوة داخلية 25px */
        font-size: 16px;    /* حجم الخط */
        z-index: 1050;      /* ترتيب ظهور العنصر فوق باقي العناصر */
        background-color: rgba(220, 53, 69, 0.97); /* خلفية حمراء شفافة */
        color: white;       /* لون النص أبيض */
        direction: rtl;     /* اتجاه النص من اليمين لليسار */
        border-radius: 12px; /* حواف دائرية */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.35); /* ظل حول العنصر */
        text-align: right;  /* محاذاة النص لليمين */
    `;
    // بنحط الرسالة HTML جوا العنصر
    globalAlertDiv.innerHTML = message;
    // بنضيف العنصر للـ body علشان يظهر في الصفحة
    document.body.appendChild(globalAlertDiv);

    // -------------------------------------------
    // التعامل مع زر إغلاق الـ Alert:
    // بنستخدم class selector علشان نلاقي الزر اللي جوا الـ Alert
    const closeAlertBtn = globalAlertDiv.querySelector('.closeAlertBtn'); 
    if(closeAlertBtn) {
        // بنضيف حدث click على زر الإغلاق علشان يشيل الـ Alert
        closeAlertBtn.addEventListener('click', function() {
            if(globalAlertDiv) globalAlertDiv.remove();
            globalAlertDiv = null; // بنمسح المتغير بعد الحذف
        });
    }

    // -------------------------------------------
    // التعامل مع زر النسخ (Copy) للـ Alert:
    // بنستخدم كمان class selector للزر اللي له class اسمه copyAlertBtn
    const copyAlertBtn = globalAlertDiv.querySelector('.copyAlertBtn'); 
    if(copyAlertBtn) {
        // بنضيف حدث click علشان ينفذ عملية النسخ
        copyAlertBtn.addEventListener('click', function() {
            let alertText = `تقرير الحجوزات (${count} حجز محدد)\n------------------------------------\n`;
            // بنستخدم forEach علشان نمر على كل تفاصيل الحجوزات ونجمعها في النص
            detailsTextArray.forEach((detail, index) => { 
                alertText += `${index + 1}. ${detail}\n`; 
            });
            alertText += `------------------------------------\nالإجمالي: ${total.toFixed(2)} ريال`;
            // بنستخدم الـ Clipboard API لنسخ النص
            navigator.clipboard.writeText(alertText).then(() => {
                // بعد النسخ بنغير نص الزر ووضع تأثير بسيط للتأكيد
                copyAlertBtn.textContent = 'تم النسخ!';
                copyAlertBtn.classList.remove('btn-light');
                copyAlertBtn.classList.add('btn-success');
                // بنرجع النص بعد 2 ثانية
                setTimeout(() => {
                    // نتأكد لو الزر لسه موجود قبل التعديل
                    if (copyAlertBtn) {
                        copyAlertBtn.textContent = 'نسخ';
                        copyAlertBtn.classList.remove('btn-success');
                        copyAlertBtn.classList.add('btn-light');
                    }
                }, 2000);
            }).catch(err => { 
                console.error('Failed to copy text: ', err); // لو فيه خطأ في النسخ
                /* هنا ممكن تحط كود للتعامل مع الخطأ */
            });
        });
    }
}

// -----------------------------------------------
// دالة التنبيه (Notification) العامة
// تعرض رسالة تنبيهية مؤقتة في أعلى الصفحة
// البارامترات:
//   message: نص الرسالة
//   type: نوع التنبيه (مثلاً 'info', 'danger'، إلخ)
function showNotification(message, type = 'info') {
    // بنشوف إذا كان في تنبيه موجود، لو لقاوه بنمسحه
    const existingNotification = document.getElementById('temp-notification');
    if (existingNotification) existingNotification.remove();

    // إنشاء عنصر div للتنبيه
    const notificationDiv = document.createElement('div');
    notificationDiv.id = 'temp-notification'; // بنحدد ID علشان نقدر نلاقيه بعدين
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
        const currentNotification = document.getElementById('temp-notification');
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
        console.error(`Booking Selector Error: Missing elements for table ID "${tableId}". Check IDs.`);
        return; // نخرج من الدالة
    }

    // بنجيب كل الـ checkboxes اللي جوا الجدول واللي ليها كلاس booking-checkbox
    const checkboxes = table.querySelectorAll('.booking-checkbox');
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
            checkbox.closest('tr').classList.add('range-start');
            // بنعرض تنبيه مخصص بنستخدم الدالة showNotification
            showNotification('تم تحديد نقطة البداية. الرجاء تحديد نقطة النهاية.', 'info');
        } else if (endCheckbox === null && checkbox !== startCheckbox) {
            // لو نقطة البداية موجودة ومفيش نقطة نهاية، ونحن اخترنا خانة مختلفة، يبقى دي نقطة النهاية
            endCheckbox = checkbox;
            checkbox.closest('tr').classList.add('range-end');
            showNotification('تم تحديد نقطة النهاية. اضغط على زر "تحديد النطاق".', 'success');
        } else {
            // لو تم تحديد نقطتين من قبل، يبدأ اختيار جديد:
            clearRangeSelectionVisuals();
            startCheckbox = checkbox; // بتبدأ اختيار جديد من هنا
            endCheckbox = null;
            checkbox.closest('tr').classList.add('range-start');
            showNotification('تم تحديد نقطة بداية جديدة. الرجاء تحديد نقطة النهاية.', 'warning');
        }
        // بعد كل اختيار، بنحدث الصفوف والعرض
        updateSelectedRows();
    }

    // دالة clearRangeSelectionVisuals:
    // بتشيل كل الإشارات البصرية لنقاط البداية والنهاية (زي الخلفيات والألوان) من الصفوف
    function clearRangeSelectionVisuals() {
        table.querySelectorAll('tr.range-start, tr.range-end').forEach(row => {
            row.classList.remove('range-start', 'range-end');
            // لو الصف مش متحدد كلياً، بنرجعله الستايل الأساسي 
            if (!row.classList.contains('selected-row')) {
                row.style.backgroundColor = '';
                row.style.color = '';
                row.style.fontWeight = '';
            } else {
                // لو الصف متحدد، نخليه بالمظهر الخاص بالتحديد
                row.style.backgroundColor = 'rgba(220, 53, 69, 0.3)';
                row.style.color = '#fff';
                row.style.fontWeight = 'bold';
            }
        });
    }

    // دالة formatCombinedDate:
    // بتنسق التاريخ بحيث تعرض التاريخ الميلادي والتاريخ الهجري مع بعض
    function formatCombinedDate(dateString) {
        try {
            const date = new Date(dateString);
            // لو التاريخ مش صحيح، نبعت رسالة خطأ
            if (isNaN(date.getTime())) {
                return "تاريخ غير صالح";
            }
            // تنسيق التاريخ الميلادي مع اسم الشهر بالعربي
            const gregorianOptions = { day: 'numeric', month: 'long' };
            const gregorianFormatted = date.toLocaleDateString('ar-EG', gregorianOptions);

            // تنسيق التاريخ الهجري بخيارات طويلة
            const hijriOptions = { day: 'numeric', month: 'long', year: 'numeric', calendar: 'islamic' };
            const hijriFormatted = date.toLocaleDateString('ar-SA-u-ca-islamic', hijriOptions);
            
            // بنرجع التاريخين مع بعض داخل نص واحد 
            return `${gregorianFormatted} (${hijriFormatted})`;
        } catch (e) {
            console.error("Error formatting combined date:", dateString, e);
            return "خطأ في التاريخ";
        }
    }

    // دالة updateSelectedRows:
    // بتعمل تحديث للصفوف المختارة وتحسب الإجمالي وتعرض تفاصيل الحجوزات في تنبيه
    function updateSelectedRows() {
        let totalAmount = 0; // متغير لتجميع المبلغ الإجمالي
        let bookingDetailsHTML = []; // مصفوفة لتخزين تفاصيل الحجوزات (HTML)
        let bookingDetailsText = []; // مصفوفة لتخزين تفاصيل الحجوزات (نص)
        let selectedCount = 0; // عدد الصفوف/الحجوزات المختارة

        // بنعدي على كل checkbox موجود في الجدول
        checkboxes.forEach((checkbox, index) => {
            const row = checkbox.closest('tr'); // بنجيب الصف الفى الذي فيه الـ checkbox
            if (checkbox.checked) { // لو الـ checkbox متحدد
                selectedCount++; // زيادة عدد المحددين
                row.classList.add('selected-row'); // بنضيف كلاس علشان نغير مظهر الصف
                // بقرا المبلغ المستحق من data-amount-due
                const amountDue = parseFloat(checkbox.dataset.amountDue) || 0;
                totalAmount += amountDue; // بنجمع المبالغ
                const clientName = checkbox.dataset.clientName;
                const hotelName = checkbox.dataset.hotelName || 'فندق غير محدد';
                const checkInString = checkbox.dataset.checkIn;
                const checkOutString = checkbox.dataset.checkOut;
                const rooms = checkbox.dataset.rooms;
                const days = checkbox.dataset.days;
                const costPrice = checkbox.dataset.costPrice || 0;
                // بننسق التاريخين باستخدام دالتنا السابقة
                const checkInFormatted = formatCombinedDate(checkInString);
                const checkOutFormatted = formatCombinedDate(checkOutString);

                // بنبني عناصر HTML للتفاصيل علشان ننزلها في التنبيه
                bookingDetailsHTML.push(
                     `<li class="list-group-item d-flex justify-content-between align-items-start bg-transparent text-white border-secondary">
                        <span class="badge bg-light text-dark rounded-pill me-3">${index + 1}</span>
                        <div class="ms-0 me-auto text-start">
                            <div class="fw-bold">${clientName} - ${hotelName}</div>
                            <small>${rooms} غرف | ${checkInFormatted} إلى ${checkOutFormatted} (${days} ليالي) | ${costPrice} ريال</small>
                        </div>
                        <span class="badge bg-light text-dark rounded-pill ms-3">${amountDue.toFixed(2)}</span>
                    </li>`
                );
                // بنبني تفاصيل نصية برضه
                bookingDetailsText.push(
                    `${clientName} - ${hotelName} | ${rooms} غرف | ${checkInFormatted} إلى ${checkOutFormatted} (${days} ليالي) | ${costPrice} ريال | المستحق: ${amountDue.toFixed(2)}`
                );
            } else {
                // لو مش متحدد، نتأكد إن الصف مش محمل بنمط التحديد
                row.classList.remove('selected-row');
            }
        });

        // تطبيق الأنماط على الصفوف بناءً على التحديد
        table.querySelectorAll('tbody tr').forEach(row => {
            if (row.classList.contains('selected-row')) {
                row.style.backgroundColor = 'rgba(220, 53, 69, 0.3)';
                row.style.color = '#fff';
                row.style.fontWeight = 'bold';
            } else {
                if (!row.classList.contains('range-start') && !row.classList.contains('range-end')) {
                    row.style.backgroundColor = '';
                    row.style.color = '';
                    row.style.fontWeight = '';
                }
            }
        });

        // بنضبط مظهر الصف اللي اتحدد كنقطة بداية أو نهاية
        const startRow = table.querySelector('tr.range-start');
        if (startRow) {
            startRow.style.backgroundColor = 'rgba(255, 193, 7, 0.4)';
            startRow.style.color = '#000';
            startRow.style.fontWeight = 'bold';
        }
        const endRow = table.querySelector('tr.range-end');
        if (endRow) {
            endRow.style.backgroundColor = 'rgba(23, 162, 184, 0.4)';
            endRow.style.color = '#000';
            endRow.style.fontWeight = 'bold';
        }

        // منطق عرض أو إخفاء التنبيه بناءً على عدد المحددين
        if (selectedCount > 0) {
            let calculationDetails = bookingDetailsHTML.join('');
            // بناء رسالة التنبيه باستخدام HTML وتحديد أزرار النسخ والإغلاق باستخدام كلاس
            let alertMessage = `
                <div class="d-flex flex-column align-items-center" style="direction: rtl;">
                    <h5 class="mb-3">تم تحديد ${selectedCount} حجوزات</h5>
                    <ul class="list-group list-group-flush w-100 mb-3" style="text-align: right;">
                        ${calculationDetails}
                    </ul>
                    <h4 class="mb-3">الإجمالي: ${totalAmount.toFixed(2)} ريال</h4>
                    <div class="d-flex justify-content-center mt-2">
                        <button type="button" class="btn btn-light btn-sm mx-2 copyAlertBtn">نسخ</button>
                        <button type="button" class="btn btn-outline-light btn-sm closeAlertBtn">إغلاق</button>
                    </div>
                </div>
            `;
            // بنستدعي دالة التنبيه العامة لعرض الرسالة
            showAlert(alertMessage, selectedCount, bookingDetailsText, totalAmount);
        } else {
            // لو مفيش حاجة محددة، نتأكد إن التنبيه بيتشال
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
    table.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            const checkbox = row.querySelector('.booking-checkbox');
            if (checkbox) { // نتأكد إن الـ checkbox موجود
                handleCheckboxSelection(checkbox);
            }
        });
    });

    // بنضيف مستمعين أحداث لكل checkbox: click وchange
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(event) {
            event.stopPropagation(); // منع انتشار الحدث علشان الصف مايتأثرش
            handleCheckboxSelection(this);
        });
        checkbox.addEventListener('change', function() {
            updateSelectedRows();
        });
    });

    // مستمع حدث زر إعادة التعيين:
    resetRangeBtn.addEventListener('click', function() {
        // بنمسح إشارات النطاق من الصفوف
        clearRangeSelectionVisuals();
        // بنضبط المتغيرات لنقاط البداية والنهاية
        startCheckbox = null;
        endCheckbox = null;
        // بنشيل تحديد جميع checkboxes
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedRows();
    });

    // مستمع حدث زر تحديد النطاق:
    selectRangeBtn.addEventListener('click', function() {
        // لو مش محددين نقطة بداية ونهاية، بنعرض تنبيه
        if (!startCheckbox || !endCheckbox) {
            showNotification('الرجاء تحديد نقطة البداية ونقطة النهاية أولاً.', 'danger');
            return;
        }
        // بنحول مجموعة checkboxes لمصفوفة علشان نقدر نشتغل عليها
        const checkboxesArray = Array.from(checkboxes);
        const startIndex = checkboxesArray.indexOf(startCheckbox);
        const endIndex = checkboxesArray.indexOf(endCheckbox);
        if (startIndex === -1 || endIndex === -1) {
            showNotification('حدث خطأ في تحديد النطاق.', 'danger');
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



