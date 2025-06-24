<!-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\reports\receipt-voucher.blade.php -->
@extends('layouts.app')

@section('title', 'سند قبض')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400..700&display=swap');

.receipt-container {
    max-width: 900px;
    margin: 20px auto;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.receipt-image {
    position: relative;
    width: 100%;
    background-image: url('{{ asset("images/receipt voucher.jpg") }}');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    height: 700px;
}

.receipt-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.receipt-text {
    position: absolute;
    font-family: "Noto Naskh Arabic", serif;
    font-weight: 700;
    color: #000;
    pointer-events: auto; /* تفعيل التفاعل للسحب */
    text-align: right;
    direction: rtl;
    white-space: nowrap;
    cursor: move; /* تغيير شكل المؤشر */
    user-select: none; /* منع تحديد النص */
    border: 2px dashed transparent; /* حدود شفافة */
    padding: 2px;
    transition: all 0.2s ease;
}

.receipt-text:hover {
    border-color: #007bff; /* إظهار الحدود عند التمرير */
    background: rgba(0, 123, 255, 0.1);
}

.receipt-text.dragging {
    border-color: #ff6b6b; /* لون مختلف أثناء السحب */
    background: rgba(255, 107, 107, 0.2);
    z-index: 1000;
}

.form-container {
    padding: 30px;
    background: linear-gradient(120deg, #10b981 60%, #2563eb 100%);
    color: white;
}

.modern-form {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255,255,255,0.2);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #fff;
    font-family: "Noto Naskh Arabic", serif;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 10px;
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-family: "Noto Naskh Arabic", serif;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #fff;
    background: rgba(255,255,255,0.3);
    box-shadow: 0 0 20px rgba(255,255,255,0.3);
}

.form-control::placeholder {
    color: rgba(255,255,255,0.7);
}

.btn-generate {
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    border: none;
    padding: 15px 40px;
    border-radius: 50px;
    color: white;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(238, 90, 36, 0.4);
}

.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(238, 90, 36, 0.6);
}

.btn-download {
    background: linear-gradient(45deg, #00b894, #00cec9);
    border: none;
    padding: 15px 40px;
    border-radius: 50px;
    color: white;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 184, 148, 0.4);
    margin-left: 15px;
    display: none;
}

.btn-download:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 184, 148, 0.6);
}

.payment-method-toggle {
    display: flex;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 15px;
}

.payment-option {
    flex: 1;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.7);
}

.payment-option.active {
    background: rgba(255,255,255,0.3);
    color: #fff;
}

.check-fields {
    display: none;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.row {
    display: flex;
    gap: 15px;
    margin: 0 -7.5px;
}

.col {
    flex: 1;
    padding: 0 7.5px;
}

/* أزرار التحكم في المواضع */
.position-controls {
    position: fixed;
    top: 20px;
    left: 20px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 20px;
    border-radius: 10px;
    z-index: 1000;
    max-height: 80vh;
    overflow-y: auto;
}

.position-control {
    margin: 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.position-control label {
    min-width: 100px;
    font-size: 12px;
}

.position-control input {
    width: 60px;
    padding: 5px;
    border: none;
    border-radius: 5px;
}

/* زر تفعيل/إلغاء السحب */
.drag-toggle-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    cursor: pointer;
    z-index: 1001;
    font-weight: bold;
    transition: all 0.3s ease;
}

.drag-toggle-btn:hover {
    background: #218838;
    transform: scale(1.05);
}

.drag-toggle-btn.disabled {
    background: #dc3545;
}

.drag-toggle-btn.disabled:hover {
    background: #c82333;
}

@media (max-width: 768px) {
    .row {
        flex-direction: column;
    }
    
    .receipt-image {
        height: 500px;
    }
    
    .form-container {
        padding: 20px;
    }
    
    .modern-form {
        padding: 20px;
    }
    
    .position-controls {
        display: none;
    }
    
    .drag-toggle-btn {
        display: none;
    }
}
</style>
@endpush

@section('content')
<!-- زر تفعيل/إلغاء السحب -->
<button id="dragToggleBtn" class="drag-toggle-btn" onclick="toggleDragMode()">
    <i class="fas fa-arrows-alt me-1"></i>
    تفعيل السحب
</button>

<!-- أزرار التحكم في المواضع (للتطوير فقط) -->
<div class="position-controls" id="positionControls" style="display: none;">
    <h6>التحكم في المواضع</h6>

    <div class="position-control">
        <label>رقم السند:</label>
        <input type="number" id="numberTop" value="259" onchange="updatePosition('receiptNumber')">
        <input type="number" id="numberRight" value="760" onchange="updatePosition('receiptNumber')">
        <input type="number" id="numberSize" value="20" onchange="updatePosition('receiptNumber')">
    </div>

    <div class="position-control">
        <label>التاريخ عربي:</label>
        <input type="number" id="dateTop" value="304" onchange="updatePosition('receiptDateArabic')">
        <input type="number" id="dateRight" value="150" onchange="updatePosition('receiptDateArabic')">
        <input type="number" id="dateSize" value="20" onchange="updatePosition('receiptDateArabic')">
    </div>

    <div class="position-control">
        <label>التاريخ انجليزي:</label>
        <input type="number" id="dateEngTop" value="314" onchange="updatePosition('receiptDateEnglish')">
        <input type="number" id="dateEngRight" value="718" onchange="updatePosition('receiptDateEnglish')">
        <input type="number" id="dateEngSize" value="20" onchange="updatePosition('receiptDateEnglish')">
    </div>

    <div class="position-control">
        <label>اسم الدافع:</label>
        <input type="number" id="payerTop" value="359" onchange="updatePosition('payerName')">
        <input type="number" id="payerRight" value="285" onchange="updatePosition('payerName')">
        <input type="number" id="payerSize" value="20" onchange="updatePosition('payerName')">
    </div>

    <div class="position-control">
        <label>المبلغ:</label>
        <input type="number" id="amountTop" value="403" onchange="updatePosition('amountText')">
        <input type="number" id="amountRight" value="110" onchange="updatePosition('amountText')">
        <input type="number" id="amountSize" value="20" onchange="updatePosition('amountText')">
    </div>

    <div class="position-control">
        <label>الموضوع:</label>
        <input type="number" id="subjectTop" value="433" onchange="updatePosition('subjectText')">
        <input type="number" id="subjectRight" value="145" onchange="updatePosition('subjectText')">
        <input type="number" id="subjectSize" value="20" onchange="updatePosition('subjectText')">
    </div>

    <div class="position-control">
        <label>علامة نقد:</label>
        <input type="number" id="cashTop" value="470" onchange="updatePosition('cashCheck')">
        <input type="number" id="cashRight" value="500" onchange="updatePosition('cashCheck')">
        <input type="number" id="cashSize" value="20" onchange="updatePosition('cashCheck')">
    </div>

    <div class="position-control">
        <label>علامة شيك:</label>
        <input type="number" id="checkMarkTop" value="499" onchange="updatePosition('checkMark')">
        <input type="number" id="checkMarkRight" value="429" onchange="updatePosition('checkMark')">
        <input type="number" id="checkMarkSize" value="20" onchange="updatePosition('checkMark')">
    </div>

    <div class="position-control">
        <label>رقم الشيك:</label>
        <input type="number" id="checkTop" value="506" onchange="updatePosition('checkNumberText')">
        <input type="number" id="checkRight" value="249" onchange="updatePosition('checkNumberText')">
        <input type="number" id="checkSize" value="20" onchange="updatePosition('checkNumberText')">
    </div>

    <div class="position-control">
        <label>البنك:</label>
        <input type="number" id="bankTop" value="506" onchange="updatePosition('bankNameText')">
        <input type="number" id="bankRight" value="563" onchange="updatePosition('bankNameText')">
        <input type="number" id="bankSize" value="20" onchange="updatePosition('bankNameText')">
    </div>

    <div class="position-control">
        <label>تاريخ الشيك:</label>
        <input type="number" id="checkDateTop" value="504" onchange="updatePosition('checkDateText')">
        <input type="number" id="checkDateRight" value="740" onchange="updatePosition('checkDateText')">
        <input type="number" id="checkDateSize" value="20" onchange="updatePosition('checkDateText')">
    </div>

    <button onclick="toggleControls()" style="margin-top: 10px; padding: 5px 10px; background: #ff6b6b; color: white; border: none; border-radius: 5px;">إخفاء</button>
</div>




<div class="receipt-container">
    <!-- صورة سند القبض مع النصوص المدرجة -->
    <div class="receipt-image" id="receiptImage">
        <div class="receipt-overlay">
            <!-- رقم السند -->
            <div class="receipt-text" id="receiptNumber" style="top: 259px; right: 760px; font-size: 16px;">001</div>
            
            <!-- التاريخ العربي -->
            <div class="receipt-text" id="receiptDateArabic" style="top: 308px; right: 115px; font-size: 14px;"></div>
            
            <!-- التاريخ الإنجليزي -->
            <div class="receipt-text" id="receiptDateEnglish" style="top: 314px; right: 718px; font-size: 14px;"></div>
            
            <!-- اسم الدافع -->
            <div class="receipt-text" id="payerName" style="top: 359px; right: 285px; font-size: 14px; max-width: 600px;"></div>
            
            <!-- المبلغ -->
            <div class="receipt-text" id="amountText" style="top: 402px; right: 150px; font-size: 14px; max-width: 600px;"></div>
            
            <!-- وذلك عن -->
            <div class="receipt-text" id="subjectText" style="top: 455px; right: 128px; font-size: 14px; max-width: 650px;"></div>
            
            <!-- علامة صح للنقد -->
            <div class="receipt-text" id="cashCheck" style="top: 501px; right: 65px; font-size: 18px; display: none;">✓</div>
            
            <!-- علامة صح للشيك -->
            <div class="receipt-text" id="checkMark" style="top: 498px; right: 156px; font-size: 18px; display: none;">✓</div>
            
            <!-- رقم الشيك -->
            <div class="receipt-text" id="checkNumberText" style="top: 494px; right: 250px; font-size: 20px;"></div>
            
            <!-- البنك -->
            <div class="receipt-text" id="bankNameText" style="top: 498px; right: 564px; font-size: 20px;"></div>
            
            <!-- تاريخ الشيك -->
            <div class="receipt-text" id="checkDateText" style="top: 504px; right: 724px; font-size: 12px;"></div>
            
            <!-- التوقيعات -->
            <div class="receipt-text" id="receiverSignature" style="bottom: 87px; right: 96px; font-size: 21px;">المستلم</div>
            <div class="receipt-text" id="accountantSignature" style="bottom: 90px; right: 425px; font-size: 20px;">المحاسب</div>
            <div class="receipt-text" id="managerSignature" style="bottom: 91px; right: 692px; font-size: 19px;">محمد حسن عباس</div>
        </div>
    </div>

    <!-- نموذج الإدخال -->
    <div class="form-container">
        <div class="modern-form">
            <h2 style="text-align: center; margin-bottom: 30px; font-family: 'Noto Naskh Arabic', serif;">
                <i class="fas fa-receipt me-2"></i>
                إنشاء سند قبض
            </h2>

            <form id="receiptForm">
                @csrf
                
                <!-- القيمة والعملة -->
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-dollar-sign me-1"></i>
                                القيمة
                            </label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                   placeholder="أدخل المبلغ" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-coins me-1"></i>
                                العملة
                            </label>
                            <select class="form-control" id="currency" name="currency" required>
                                <option value="SAR">ريال سعودي</option>
                                <option value="KWD">دينار كويتي</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- الموضوع -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-edit me-1"></i>
                        وذلك عن
                    </label>
                    <input type="text" class="form-control" id="subject" name="subject" 
                           placeholder="سبب الدفع" required>
                </div>

                <!-- التاريخ -->
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                التاريخ (عربي)
                            </label>
                            <input type="text" class="form-control" id="dateArabic" name="date_arabic" 
                                   placeholder="مثال: 15 شعبان 1445" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar me-1"></i>
                                التاريخ (إنجليزي)
                            </label>
                            <input type="date" class="form-control" id="dateEnglish" name="date_english" 
                                    required>
                        </div>
                    </div>
                </div>

                <!-- اسم الدافع -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user me-1"></i>
                        اسم الدافع
                    </label>
                    <input type="text" class="form-control" id="payerNameInput" name="payer_name" 
                           placeholder="اسم الشخص أو الشركة" required>
                </div>

                <!-- طريقة الدفع -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-money-bill me-1"></i>
                        طريقة الدفع
                    </label>
                    <div class="payment-method-toggle">
                        <button type="button" class="payment-option active" data-method="cash">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            نقداً
                        </button>
                        <button type="button" class="payment-option" data-method="check">
                            <i class="fas fa-money-check me-1"></i>
                            شيك
                        </button>
                    </div>
                </div>

                <!-- تفاصيل الشيك -->
                <div class="check-fields" id="checkFields">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">رقم الشيك</label>
                                <input type="text" class="form-control" id="checkNumber" name="check_number" 
                                       placeholder="رقم الشيك">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">البنك</label>
                                <input type="text" class="form-control" id="bankName" name="bank_name" 
                                       placeholder="اسم البنك">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تاريخ الشيك</label>
                        <input type="date" class="form-control" id="checkDate" name="check_date">
                    </div>
                </div>

                <!-- التوقيعات -->
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-signature me-1"></i>
                                توقيع المستلم
                            </label>
                            <input type="text" class="form-control" id="receiverSig" name="receiver_signature" 
                                   placeholder="اسم المستلم" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-signature me-1"></i>
                                توقيع المحاسب
                            </label>
                            <input type="text" class="form-control" id="accountantSig" name="accountant_signature" 
                                   placeholder="اسم المحاسب" required>
                        </div>
                    </div>
                </div>

                <!-- أزرار العمل -->
                <div style="text-align: center; margin-top: 30px;">
                    <button type="button" onclick="toggleControls()" class="btn-generate" style="background: #6c757d; margin-right: 10px;">
                        <i class="fas fa-cogs me-1"></i>
                        تعديل المواضع
                    </button>
                    <button type="submit" class="btn-generate">
                        <i class="fas fa-magic me-1"></i>
                        إنشاء سند القبض
                    </button>
                    <button type="button" class="btn-download" id="downloadBtn">
                        <i class="fas fa-download me-1"></i>
                        تحميل السند
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
let isDragEnabled = false;
let draggedElement = null;
let startX = 0;
let startY = 0;
let startTop = 0;
let startRight = 0;

// دالة تفعيل/إلغاء وضع السحب
function toggleDragMode() {
    isDragEnabled = !isDragEnabled;
    const btn = document.getElementById('dragToggleBtn');
    const receiptTexts = document.querySelectorAll('.receipt-text');
    
    if (isDragEnabled) {
        btn.textContent = '🔓 إلغاء السحب';
        btn.classList.add('disabled');
        receiptTexts.forEach(element => {
            element.style.cursor = 'move';
            element.addEventListener('mousedown', startDrag);
        });
    } else {
        btn.innerHTML = '<i class="fas fa-arrows-alt me-1"></i>تفعيل السحب';
        btn.classList.remove('disabled');
        receiptTexts.forEach(element => {
            element.style.cursor = 'default';
            element.removeEventListener('mousedown', startDrag);
            element.classList.remove('dragging');
        });
    }
}

// بداية السحب
function startDrag(e) {
    if (!isDragEnabled) return;
    
    e.preventDefault();
    draggedElement = e.target;
    draggedElement.classList.add('dragging');
    
    // تحديد النقطة الأولى
    startX = e.clientX;
    startY = e.clientY;
    
    // الحصول على الموقع الحالي
    const rect = draggedElement.getBoundingClientRect();
    const containerRect = document.getElementById('receiptImage').getBoundingClientRect();
    
    startTop = rect.top - containerRect.top;
    startRight = containerRect.right - rect.right;
    
    // إضافة مستمعي الأحداث
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
}

// أثناء السحب
function drag(e) {
    if (!draggedElement) return;
    
    e.preventDefault();
    
    // حساب المسافة المتحركة
    const deltaX = e.clientX - startX;
    const deltaY = e.clientY - startY;
    
    // حساب الموقع الجديد
    const newTop = startTop + deltaY;
    const newRight = startRight - deltaX;
    
    // تطبيق الموقع الجديد
    draggedElement.style.top = Math.max(0, newTop) + 'px';
    draggedElement.style.right = Math.max(0, newRight) + 'px';
    
    // تحديث أزرار التحكم إذا كانت مرئية
    updateControlsFromPosition(draggedElement);
}

// إنهاء السحب
function stopDrag() {
    if (draggedElement) {
        draggedElement.classList.remove('dragging');
        draggedElement = null;
    }
    
    // إزالة مستمعي الأحداث
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', stopDrag);
}

// تحديث أزرار التحكم بناءً على الموقع الحالي
function updateControlsFromPosition(element) {
    const elementId = element.id;
    const computedStyle = window.getComputedStyle(element);
    const top = parseInt(computedStyle.top);
    const right = parseInt(computedStyle.right);
    const fontSize = parseInt(computedStyle.fontSize);
    
    // تحديث أزرار التحكم المناسبة
    switch(elementId) {
        case 'receiptNumber':
            if (document.getElementById('numberTop')) {
                document.getElementById('numberTop').value = top;
                document.getElementById('numberRight').value = right;
                document.getElementById('numberSize').value = fontSize;
            }
            break;
        case 'receiptDateArabic':
            if (document.getElementById('dateTop')) {
                document.getElementById('dateTop').value = top;
                document.getElementById('dateRight').value = right;
                document.getElementById('dateSize').value = fontSize;
            }
            break;
        case 'receiptDateEnglish':
            if (document.getElementById('dateEngTop')) {
                document.getElementById('dateEngTop').value = top;
                document.getElementById('dateEngRight').value = right;
                document.getElementById('dateEngSize').value = fontSize;
            }
            break;
        case 'payerName':
            if (document.getElementById('payerTop')) {
                document.getElementById('payerTop').value = top;
                document.getElementById('payerRight').value = right;
                document.getElementById('payerSize').value = fontSize;
            }
            break;
        case 'amountText':
            if (document.getElementById('amountTop')) {
                document.getElementById('amountTop').value = top;
                document.getElementById('amountRight').value = right;
                document.getElementById('amountSize').value = fontSize;
            }
            break;
        case 'subjectText':
            if (document.getElementById('subjectTop')) {
                document.getElementById('subjectTop').value = top;
                document.getElementById('subjectRight').value = right;
                document.getElementById('subjectSize').value = fontSize;
            }
            break;
        case 'cashCheck':
            if (document.getElementById('cashTop')) {
                document.getElementById('cashTop').value = top;
                document.getElementById('cashRight').value = right;
                document.getElementById('cashSize').value = fontSize;
            }
            break;
        case 'checkMark':
            if (document.getElementById('checkMarkTop')) {
                document.getElementById('checkMarkTop').value = top;
                document.getElementById('checkMarkRight').value = right;
                document.getElementById('checkMarkSize').value = fontSize;
            }
            break;
        case 'checkNumberText':
            if (document.getElementById('checkTop')) {
                document.getElementById('checkTop').value = top;
                document.getElementById('checkRight').value = right;
                document.getElementById('checkSize').value = fontSize;
            }
            break;
        case 'bankNameText':
            if (document.getElementById('bankTop')) {
                document.getElementById('bankTop').value = top;
                document.getElementById('bankRight').value = right;
                document.getElementById('bankSize').value = fontSize;
            }
            break;
        case 'checkDateText':
            if (document.getElementById('checkDateTop')) {
                document.getElementById('checkDateTop').value = top;
                document.getElementById('checkDateRight').value = right;
                document.getElementById('checkDateSize').value = fontSize;
            }
            break;
    }
}

// دالة التحكم في إظهار/إخفاء أزرار التحكم
function toggleControls() {
    const controls = document.getElementById('positionControls');
    controls.style.display = controls.style.display === 'none' ? 'block' : 'none';
}

// دالة تحديث موقع العنصر
function updatePosition(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let top, right, size;
    
    switch(elementId) {
        case 'receiptNumber':
            top = document.getElementById('numberTop').value;
            right = document.getElementById('numberRight').value;
            size = document.getElementById('numberSize').value;
            break;
        case 'receiptDateArabic':
            top = document.getElementById('dateTop').value;
            right = document.getElementById('dateRight').value;
            size = document.getElementById('dateSize').value;
            break;
        case 'receiptDateEnglish':
            top = document.getElementById('dateEngTop').value;
            right = document.getElementById('dateEngRight').value;
            size = document.getElementById('dateEngSize').value;
            break;
        case 'payerName':
            top = document.getElementById('payerTop').value;
            right = document.getElementById('payerRight').value;
            size = document.getElementById('payerSize').value;
            break;
        case 'amountText':
            top = document.getElementById('amountTop').value;
            right = document.getElementById('amountRight').value;
            size = document.getElementById('amountSize').value;
            break;
        case 'subjectText':
            top = document.getElementById('subjectTop').value;
            right = document.getElementById('subjectRight').value;
            size = document.getElementById('subjectSize').value;
            break;
        case 'cashCheck':
            top = document.getElementById('cashTop').value;
            right = document.getElementById('cashRight').value;
            size = document.getElementById('cashSize').value;
            break;
        case 'checkMark':
            top = document.getElementById('checkMarkTop').value;
            right = document.getElementById('checkMarkRight').value;
            size = document.getElementById('checkMarkSize').value;
            break;
        case 'checkNumberText':
            top = document.getElementById('checkTop').value;
            right = document.getElementById('checkRight').value;
            size = document.getElementById('checkSize').value;
            break;
        case 'bankNameText':
            top = document.getElementById('bankTop').value;
            right = document.getElementById('bankRight').value;
            size = document.getElementById('bankSize').value;
            break;
        case 'checkDateText':
            top = document.getElementById('checkDateTop').value;
            right = document.getElementById('checkDateRight').value;
            size = document.getElementById('checkDateSize').value;
            break;
    }
    
    element.style.top = top + 'px';
    element.style.right = right + 'px';
    element.style.fontSize = size + 'px';
}

document.addEventListener('DOMContentLoaded', function() {
    // تعيين التاريخ الحالي
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('dateEnglish').value = today;
    
    // التحكم في أذونات التاريخ حسب الدور
    @if(auth()->user()->role !== 'Admin')
    document.getElementById('dateEnglish').min = today;
    @endif

    // تبديل طريقة الدفع
    document.querySelectorAll('.payment-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            const method = this.dataset.method;
            const checkFields = document.getElementById('checkFields');
            
            if (method === 'check') {
                checkFields.style.display = 'block';
            } else {
                checkFields.style.display = 'none';
            }
            
            // تحديث علامات الصح
            updateReceiptText();
        });
    });

    // تحديث النص في الوقت الفعلي
    function updateReceiptText() {
        const amount = document.getElementById('amount').value;
        const currency = document.getElementById('currency').value;
        const subject = document.getElementById('subject').value;
        const dateArabic = document.getElementById('dateArabic').value;
        const dateEnglish = document.getElementById('dateEnglish').value;
        const payerNameInput = document.getElementById('payerNameInput').value;
        const paymentMethod = document.querySelector('.payment-option.active').dataset.method;
        const checkNumber = document.getElementById('checkNumber').value;
        const bankName = document.getElementById('bankName').value;
        const checkDate = document.getElementById('checkDate').value;
        const receiverSig = document.getElementById('receiverSig').value;
        const accountantSig = document.getElementById('accountantSig').value;

        // تحديث النصوص
        document.getElementById('receiptDateArabic').textContent = dateArabic;
        document.getElementById('receiptDateEnglish').textContent = dateEnglish;
        document.getElementById('payerName').textContent = payerNameInput;
        
        const currencyText = currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي';
        document.getElementById('amountText').textContent = amount ? `${amount} ${currencyText}` : '';
        
        document.getElementById('subjectText').textContent = subject;
        
        // إظهار علامة الصح حسب طريقة الدفع
        const cashCheck = document.getElementById('cashCheck');
        const checkMark = document.getElementById('checkMark');
        
        if (paymentMethod === 'cash') {
            cashCheck.style.display = 'block';
            checkMark.style.display = 'none';
        } else {
            cashCheck.style.display = 'none';
            checkMark.style.display = 'block';
        }
        
        if (paymentMethod === 'check') {
            document.getElementById('checkNumberText').textContent = checkNumber || '';
            document.getElementById('bankNameText').textContent = bankName || '';
            document.getElementById('checkDateText').textContent = checkDate || '';
        } else {
            document.getElementById('checkNumberText').textContent = '';
            document.getElementById('bankNameText').textContent = '';
            document.getElementById('checkDateText').textContent = '';
        }
        
        document.getElementById('receiverSignature').textContent = receiverSig || 'المستلم';
        document.getElementById('accountantSignature').textContent = accountantSig || 'المحاسب';
    }

    // ربط الأحداث
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', updateReceiptText);
        input.addEventListener('change', updateReceiptText);
    });

    // إرسال النموذج
    document.getElementById('receiptForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // تحديث النص أولاً
        updateReceiptText();
        
        // إلغاء وضع السحب
        if (isDragEnabled) {
            toggleDragMode();
        }
        
        // إخفاء أزرار التحكم عند التحميل
        document.getElementById('positionControls').style.display = 'none';
        document.getElementById('dragToggleBtn').style.display = 'none';
        
        // إظهار زر التحميل
        document.getElementById('downloadBtn').style.display = 'inline-block';
        
        // رسالة نجاح
        alert('تم إنشاء سند القبض بنجاح! يمكنك الآن تحميله.');
    });

    // تحميل السند
    document.getElementById('downloadBtn').addEventListener('click', function() {
        // إخفاء أزرار التحكم عند التحميل
        document.getElementById('positionControls').style.display = 'none';
        document.getElementById('dragToggleBtn').style.display = 'none';
        
        const receiptImage = document.getElementById('receiptImage');
        
        html2canvas(receiptImage, {
            scale: 3,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            width: receiptImage.offsetWidth,
            height: receiptImage.offsetHeight
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = `سند_قبض_${new Date().toISOString().split('T')[0]}.png`;
            link.href = canvas.toDataURL('image/png', 1.0);
            link.click();
            
            // إعادة إظهار الأزرار بعد التحميل
            document.getElementById('dragToggleBtn').style.display = 'block';
        }).catch(error => {
            console.error('خطأ في التحميل:', error);
            alert('حدث خطأ أثناء تحميل السند. يرجى المحاولة مرة أخرى.');
            
            // إعادة إظهار الأزرار في حالة الخطأ
            document.getElementById('dragToggleBtn').style.display = 'block';
        });
    });

    // تحديث أولي للنص
    updateReceiptText();
});
</script>
@endpush