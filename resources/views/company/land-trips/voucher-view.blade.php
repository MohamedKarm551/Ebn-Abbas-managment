<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحميل فاتورة - {{ $booking->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
            text-align: right;
        }
        
        #voucher {
            background-color: white;
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #007bff;
        }
        
        .subtitle {
            font-size: 16px;
            color: #6c757d;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .row-item {
            margin-bottom: 10px;
        }
        
        .label {
            font-weight: bold;
            color: #495057;
            width: 150px;
            display: inline-block;
        }
        
        .value {
            color: #212529;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
            z-index: 9999;
            transition: opacity 0.3s;
        }
        
        .button-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .button-container button {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .button-container button:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div id="voucher">
        <div class="header">
            <div class="title">فاتورة حجز رحلة برية</div>
            <div class="subtitle">رقم الحجز: {{ $booking->id }}</div>
        </div>

        <div class="section">
            <div class="section-title">معلومات الرحلة</div>
            <div class="row-item">
                <span class="label">رقم الرحلة:</span>
                <span class="value">{{ $booking->landTrip->id }}</span>
            </div>
            <div class="row-item">
                <span class="label">نوع الرحلة:</span>
                <span class="value">{{ $booking->landTrip->tripType->name }}</span>
            </div>
            <div class="row-item">
                <span class="label">تاريخ المغادرة:</span>
                <span class="value">{{ \Carbon\Carbon::parse($booking->landTrip->departure_date)->format('Y-m-d') }}</span>
            </div>
            <div class="row-item">
                <span class="label">تاريخ العودة:</span>
                <span class="value">{{ \Carbon\Carbon::parse($booking->landTrip->return_date)->format('Y-m-d') }}</span>
            </div>
            <div class="row-item">
                <span class="label">عدد الأيام:</span>
                <span class="value">{{ $booking->landTrip->days_count }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">معلومات الحجز</div>
            <div class="row-item">
                <span class="label">اسم العميل:</span>
                <span class="value">{{ $booking->client_name }}</span>
            </div>
            <div class="row-item">
                <span class="label">الفندق:</span>
                <span class="value">{{ $booking->landTrip->hotel->name ?? 'غير محدد' }}</span>
            </div>
            <div class="row-item">
                <span class="label">نوع الغرفة:</span>
                <span class="value">{{ $booking->roomPrice->roomType->room_type_name }}</span>
            </div>
            <div class="row-item">
                <span class="label">عدد الغرف:</span>
                <span class="value">{{ $booking->rooms }}</span>
            </div>
            <div class="row-item">
                <span class="label">السعر الإجمالي:</span>
                <span class="value">{{ number_format($booking->sale_price, 2) }} {{ $booking->currency == 'KWD' ? 'د.ك' : 'ر.س' }}</span>
            </div>
            <div class="row-item">
                <span class="label">المبلغ الإجمالي:</span>
                <span class="value">{{ number_format($booking->amount_due_from_company, 2) }} {{ $booking->currency == 'KWD' ? 'د.ك' : 'ر.س' }}</span>
            </div>
        </div>

        @if($booking->notes)
        <div class="section">
            <div class="section-title">ملاحظات</div>
            <div>{{ $booking->notes }}</div>
        </div>
        @endif

        <div class="footer">
            تم الحجز بواسطة: {{ $booking->company->name ?? 'غير محدد' }} - بتاريخ: {{ $booking->created_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <div class="button-container">
        <button id="downloadPdf" class="btn btn-primary">تحميل PDF</button>
        <button id="backButton" class="btn btn-secondary" onclick="window.location='{{ route('company.land-trips.voucher', $booking->id) }}'">رجوع</button>
    </div>

    <div id="loading" class="loading" style="display: none;">
        جاري تحميل الملف...
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="{{ asset('js/preventClick.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تأكد من تحميل المكتبات
            if (typeof html2canvas === 'undefined' || typeof window.jspdf === 'undefined') {
                alert('خطأ في تحميل المكتبات المطلوبة. يرجى تحديث الصفحة.');
                return;
            }

            const downloadBtn = document.getElementById('downloadPdf');
            const loading = document.getElementById('loading');
            
            downloadBtn.addEventListener('click', generatePDF);

            async function generatePDF() {
                try {
                    // إظهار شاشة التحميل
                    loading.style.display = 'flex';
                    
                    // انتظار لحظة للتأكد من إظهار شاشة التحميل
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                    // الحصول على العنصر المراد تصويره
                    const voucherElement = document.getElementById('voucher');
                    
                    // تحويل العنصر إلى صورة
                    const canvas = await html2canvas(voucherElement, {
                        scale: 2, // جودة أفضل
                        useCORS: true,
                        logging: false
                    });
                    
                    // إنشاء ملف PDF
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    
                    // احسب الأبعاد للصفحة
                    const imgWidth = 210; // A4 width in mm
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    
                    // إضافة الصورة إلى PDF
                    const imgData = canvas.toDataURL('image/jpeg', 0.95);
                    pdf.addImage(imgData, 'JPEG', 0, 0, imgWidth, imgHeight);
                    const clientName = '{{ $booking->client_name }}'.replace(/[^\u0600-\u06FFa-zA-Z0-9\s]/g, '');

                    // تحميل الملف
                    pdf.save('فاتورة-حجز-رحلة-' + clientName + '.pdf');

                    // إخفاء شاشة التحميل
                    loading.style.display = 'none';
                } catch (error) {
                    // في حالة حدوث خطأ
                    console.error('حدث خطأ أثناء إنشاء ملف PDF:', error);
                    alert('حدث خطأ أثناء إنشاء ملف PDF. يرجى المحاولة مرة أخرى.');
                    loading.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
