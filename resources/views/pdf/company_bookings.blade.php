{{-- صفحة كشف حساب الحجوزات PDF فقط --}}
@extends('layouts.app')
@section('title', 'كشف حساب الحجوزات - ' . $company->name)

@section('content')
    <div class="container py-3">
        <!-- زر تحميل PDF -->
        <button class="btn btn-danger mb-3" id="downloadPdfBtn">
            <i class="fas fa-file-pdf"></i> تحميل كشف الحساب PDF
        </button>

        <!-- كل ما سيتم تصديره للـ PDF هنا -->
        <div id="reportContent" class="bg-white p-4 rounded shadow-sm">

            <!-- رأس الصفحة: لوجو + بيانات الحساب البنكي -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    {{-- ضع هنا صورة اللوجو لو عندك، أو احذف الـ img لو مش محتاج --}}
                    <img src="{{ asset('images/cover.jpg') }}" height="100" alt="Logo" style="max-width:120px;">
                </div>
                <div class="bank-accounts"
                    style="text-align:right; font-size:14px; border: 1px solid #eee; padding: 10px; border-radius: 5px; background-color: #f9f9f9;">
                    <h4 style="margin-top:0; margin-bottom:10px; color:#333;">بيانات الحساب البنكي للتحويل</h4>

                    <div class="bank-account" style="margin-bottom:8px; padding-bottom:8px; border-bottom:1px dashed #ddd;">
                        <div style="font-weight:bold; color:#0066cc;">
                            <i class="fas fa-university" style="margin-left:5px;"></i>بنك الجزيرة
                        </div>
                        <div
                            style="font-family:monospace; padding:3px 15px; letter-spacing:1px; margin-top:3px; direction:ltr; text-align:left;">
                            SA96 6010 0010 4950 2512 6001
                        </div>
                    </div>

                    <div class="bank-account" style="margin-bottom:2px;">
                        <div style="font-weight:bold; color:#28a745;">
                            <i class="fas fa-university" style="margin-left:5px;"></i>مصرف الراجحي
                        </div>
                        <div
                            style="font-family:monospace; padding:3px 15px; letter-spacing:1px; margin-top:3px; direction:ltr; text-align:left;">
                            SA55 8090 0000 2169 9102 5042
                        </div>
                    </div>
                </div>
            </div>

            <!-- العنوان الرئيسي -->
            <h3 class="text-center mb-3" style="font-family: 'Tajawal', Arial, sans-serif;">
                كشف حساب حجوزات شركة {{ $company->name }}
            </h3>

            <!-- ملخص الحساب -->
            <div class="mb-3">
                <span style="font-weight:bold;">عدد الحجوزات:</span> {{ $bookings->count() }}<br>
                @foreach ($totalDueByCurrency as $currency => $amount)
                    <span style="font-weight:bold;">إجمالي المستحق ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}):</span>
                    {{ number_format($amount, 2) }}<br>
                @endforeach
                @foreach ($totalPaidByCurrency as $currency => $amount)
                    <span style="font-weight:bold;">المدفوع ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}):</span>
                    {{ number_format($amount, 2) }}<br>
                @endforeach
                @foreach ($totalRemainingByCurrency as $currency => $amount)
                    <span style="font-weight:bold;">المتبقي ({{ $currency === 'SAR' ? 'ريال' : 'دينار' }}):</span>
                    {{ number_format($amount, 2) }}<br>
                @endforeach
                {{-- ✅ الرصيد الحالي حتى تاريخ اليوم --}}
                @if (isset($currentBalance))
                    <hr>
                    <strong>الرصيد حتى اليوم (الحجوزات التي دخلت فعلياً):</strong><br>
                    المستحق حتى اليوم: {{ number_format($currentBalance['entered_due'], 2) }} ريال<br>
                    المدفوع: {{ number_format($currentBalance['paid'], 2) }} ريال<br>
                    الخصومات: {{ number_format($currentBalance['discounts'], 2) }} ريال<br>
                    الصافي المحتسب (مدفوع + خصومات): {{ number_format($currentBalance['effective_paid'], 2) }} ريال<br>
                    @php
                        $bal = $currentBalance['balance'];
                    @endphp
                    الرصيد:
                    @if ($bal > 0)
                        <span class="text-danger">متبقي على الشركة {{ number_format($bal, 2) }} ريال</span>
                    @elseif($bal < 0)
                        <span class="text-success">للشركة رصيد مدفوع زائد {{ number_format(abs($bal), 2) }} ريال</span>
                    @else
                        <span class="text-primary">مغلق (لا يوجد رصيد)</span>
                    @endif
                @endif

                <small>المعادلة: رصيد اليوم = (مجموع مستحق الحجوزات المدخلة) - (المدفوع + الخصومات)</small>


            </div>

            <!-- الجدول بدون عمود جهة الحجز -->
            <!-- جدول الحجوزات المحدث للـ PDF -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" style="font-size: 14px;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>الفندق</th>
                            <th>تاريخ الدخول</th>
                            <th>تاريخ الخروج</th>
                            <th>عدد الغرف</th>
                            <th>سعر الليلة</th>
                            <th>عدد الليالي</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>حالة الدفع</th>
                            {{-- احذف جهة الحجز زي ما طلبت --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $key => $booking)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $booking->client_name }}</td>
                                <td>{{ $booking->hotel->name ?? '-' }}</td>
                                <td>{{ $booking->check_in->format('d/m/Y') }}
                                    <small class="d-block text-muted hijri-date"
                                        data-date="{{ $booking->check_in->format('Y-m-d') }}"></small>
                                </td>
                                <td>{{ $booking->check_out->format('d/m/Y') }}
                                    <small class="d-block text-muted hijri-date"
                                        data-date="{{ $booking->check_out->format('Y-m-d') }}"></small>
                                </td>
                                <td>{{ $booking->rooms }}</td>
                                <td>
                                    {{ number_format($booking->sale_price, 2) }}
                                    {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                                <td>
                                    {{ $booking->total_nights }}
                                </td>
                                <td>
                                    {{ number_format($booking->total_company_due, 2) }}
                                    {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                                <td>
                                    {{ number_format($booking->company_payment_amount, 2) }}
                                    {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                                <td>
                                    {{-- المتبقي = الإجمالي - المدفوع --}}
                                    {{ number_format($booking->total_company_due - $booking->company_payment_amount, 2) }}
                                    {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                </td>
                                <td>
                                    @php
                                        // تحديد اللون المناسب حسب الحالة
                                        $status = $booking->company_payment_status;
                                        $statusColor = match ($status) {
                                            'مدفوع بالكامل',
                                            'fully_paid'
                                                => 'background: #28a745; color: #fff;', // أخضر
                                            'مدفوع جزئياً',
                                            'partially_paid'
                                                => 'background: #ffc107; color: #000;', // أصفر
                                            'غير مدفوع',
                                            'not_paid',
                                            'unpaid'
                                                => 'background: #dc3545; color: #fff;', // أحمر
                                            default => 'background: #6c757d; color: #fff;', // رمادي افتراضي
                                        };
                                    @endphp
                                    <span style="padding: 2px 10px; border-radius: 6px; {{ $statusColor }}">
                                        {{ $booking->company_payment_status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            <div class="mt-4" style="text-align: center; font-size: 13px;">
                <span>تاريخ توليد الكشف: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body,
        .table,
        h3,
        th,
        td {
            font-family: 'Tajawal', Arial, sans-serif !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var downloadBtn = document.getElementById('downloadPdfBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', async function() {
                    downloadBtn.disabled = true;
                    downloadBtn.textContent = "جاري التحميل ...";
                    try {
                        const element = document.getElementById('reportContent');
                        const canvas = await html2canvas(element, {
                            scale: 2,
                            useCORS: true,
                        });
                        const imgData = canvas.toDataURL('image/jpeg', 0.95);
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF('p', 'mm', 'a4');
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = pdf.internal.pageSize.getHeight();
                        const imgProps = pdf.getImageProperties(imgData);
                        const imgWidth = pdfWidth;
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        let heightLeft = imgHeight;
                        let position = 0;

                        pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                        heightLeft -= pdfHeight;

                        while (heightLeft > 0) {
                            position -= pdfHeight;
                            pdf.addPage();
                            pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                            heightLeft -= pdfHeight;
                        }

                        pdf.save('كشف-حساب-{{ $company->name }}.pdf');
                    } catch (e) {
                        alert("حدث خطأ أثناء توليد الـ PDF");
                    }
                    downloadBtn.disabled = false;
                    downloadBtn.textContent = "تحميل كشف الحساب PDF";
                });
            }
        });
    </script>

    <script>
        // Converts Gregorian dates to Hijri
        function convertToHijri() {
            document.querySelectorAll('.hijri-date').forEach(element => {
                const gregorianDate = element.getAttribute('data-date');
                if (gregorianDate) {
                    try {
                        // Use Intl.DateTimeFormat with 'islamic' calendar
                        const hijriDate = new Intl.DateTimeFormat('ar-SA-islamic', {
                            day: 'numeric',
                            month: 'long',
                            calendar: 'islamic'
                        }).format(new Date(gregorianDate));

                        element.textContent = hijriDate;
                    } catch (e) {
                        console.error("Error converting date:", e);
                        element.textContent = ""; // Clear if error
                    }
                }
            });
        }

        // Convert dates when page loads
        document.addEventListener("DOMContentLoaded", function() {
            convertToHijri();

            // Also convert when table is updated via AJAX
            document.addEventListener('ajaxTableUpdated', convertToHijri);
        });
    </script>
    <script>
            document.addEventListener('DOMContentLoaded', function() {
        // إعداد تحرير بيانات البنك عند النقر المزدوج
        setupBankAccountsEditing();

        // وظيفة لإعداد تحرير الحسابات البنكية
        function setupBankAccountsEditing() {
            // تحديد العناصر القابلة للتحرير
            const bankNameElements = document.querySelectorAll('.bank-account div:first-child');
            const bankIbanElements = document.querySelectorAll('.bank-account div:last-child');

            // إضافة الخاصية data-editable لإظهار أنها قابلة للتحرير
            [...bankNameElements, ...bankIbanElements].forEach(element => {
                element.dataset.editable = 'true';
                element.title = 'انقر نقرًا مزدوجًا للتحرير';
                element.style.cursor = 'pointer';
            });

            // استرجاع البيانات المحفوظة سابقًا
            loadBankData();

            // إضافة حدث النقر المزدوج لجميع العناصر القابلة للتحرير
            document.querySelector('.bank-accounts').addEventListener('dblclick', function(e) {
                const target = e.target.closest('[data-editable="true"]');
                if (!target) return;

                // لا نسمح بالتحرير عند توليد PDF
                if (document.body.classList.contains('generating-pdf')) return;

                // عنصر الإدخال لتحرير النص
                const isIban = target.classList.contains('iban-value') || 
                               target.style.direction === 'ltr';
                
                const input = document.createElement(isIban ? 'input' : 'input');
                
                // احتفظ بقيمة النص الأصلية
                const originalText = target.innerText.trim();
                const originalHTML = target.innerHTML;
                
                input.value = originalText.replace(/<i.*?<\/i>/g, '').trim();
                
                // نسخ التنسيق من العنصر الأصلي
                copyStyles(target, input);
                
                if (isIban) {
                    input.style.fontFamily = 'monospace';
                    input.style.textAlign = 'left';
                    input.style.direction = 'ltr';
                    input.style.width = '100%';
                    input.className = 'iban-edit-input';
                }

                // حفظ المعرف الفريد للعنصر للاستخدام عند الحفظ
                const bankIndex = Array.from(target.closest('.bank-account').parentNode.children)
                                       .indexOf(target.closest('.bank-account'));
                const isName = target.querySelector('i') !== null || 
                              !target.classList.contains('iban-value');
                
                input.dataset.bankIndex = bankIndex;
                input.dataset.fieldType = isName ? 'name' : 'iban';

                // استبدال النص بعنصر الإدخال
                const originalIcon = target.querySelector('i')?.outerHTML || '';
                target.innerHTML = '';
                target.appendChild(input);
                input.focus();

                // عند اكتمال التحرير
                function finishEditing() {
                    let newValue = input.value.trim();
                    
                    if (newValue && newValue !== originalText) {
                        if (isName) {
                            target.innerHTML = originalIcon + ' ' + newValue;
                        } else {
                            target.innerText = newValue;
                        }
                        
                        // حفظ البيانات الجديدة محليًا
                        saveBankData(bankIndex, isName ? 'name' : 'iban', newValue);
                    } else {
                        // استعادة النص الأصلي إذا كان الإدخال فارغًا
                        target.innerHTML = originalHTML;
                    }
                }

                // مستمعي الأحداث لإنهاء التحرير
                input.addEventListener('blur', finishEditing);
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        finishEditing();
                        e.preventDefault();
                    } else if (e.key === 'Escape') {
                        target.innerHTML = originalHTML; // إلغاء التحرير
                        e.preventDefault();
                    }
                });
            });
        }

        // نسخ التنسيق من عنصر إلى آخر
        function copyStyles(source, destination) {
            const computedStyle = window.getComputedStyle(source);
            for (const prop of ['color', 'fontSize', 'fontWeight', 'padding']) {
                destination.style[prop] = computedStyle[prop];
            }
            destination.style.border = 'none';
            destination.style.background = 'rgba(255,255,255,0.8)';
            destination.style.outline = '1px solid #007bff';
            destination.style.width = `${source.clientWidth}px`;
        }

        // حفظ بيانات البنك في التخزين المحلي
        function saveBankData(index, field, value) {
            try {
                let bankAccounts = JSON.parse(localStorage.getItem('bankAccounts')) || [];
                
                // إنشاء أو تحديث حساب البنك
                if (!bankAccounts[index]) {
                    bankAccounts[index] = { name: '', iban: '' };
                }
                
                bankAccounts[index][field] = value;
                localStorage.setItem('bankAccounts', JSON.stringify(bankAccounts));
                
                console.log('تم حفظ بيانات البنك:', bankAccounts);
            } catch (error) {
                console.error('خطأ في حفظ بيانات البنك:', error);
            }
        }

        // استرجاع بيانات البنك من التخزين المحلي
        function loadBankData() {
            try {
                const bankAccounts = JSON.parse(localStorage.getItem('bankAccounts')) || [];
                if (!bankAccounts.length) return;

                const bankElements = document.querySelectorAll('.bank-account');
                
                bankAccounts.forEach((account, index) => {
                    if (!bankElements[index]) return;
                    
                    // تحديث اسم البنك إذا كان متوفرًا
                    if (account.name) {
                        const nameElement = bankElements[index].querySelector('div:first-child');
                        const icon = nameElement.querySelector('i')?.outerHTML || '';
                        nameElement.innerHTML = icon + ' ' + account.name;
                    }
                    
                    // تحديث رقم الآيبان إذا كان متوفرًا
                    if (account.iban) {
                        const ibanElement = bankElements[index].querySelector('div:last-child');
                        ibanElement.innerText = account.iban;
                    }
                });
            } catch (error) {
                console.error('خطأ في استرجاع بيانات البنك:', error);
            }
        }
        
        // حدث قبل تحميل PDF لمنع التحرير أثناء التوليد
        document.getElementById('downloadPdfBtn')?.addEventListener('click', function() {
            document.body.classList.add('generating-pdf');
            setTimeout(() => {
                document.body.classList.remove('generating-pdf');
            }, 5000); // إزالة الفئة بعد 5 ثوانٍ
        });
    });

    </script>
@endpush
