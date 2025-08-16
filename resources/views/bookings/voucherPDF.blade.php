<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>فاتورة حجز فندق - {{ $booking->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #fff;
            font-family: 'Tajawal', Arial, sans-serif;
            direction: rtl;
            padding: 20px;
        }

        #reportContent {
            max-width: 800px;
            margin: auto;
            border: 2px solid #000;
            padding: 30px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .header h2 {
            font-size: 16px;
        }

        .info-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-card {
            flex: 1;
            border: 1px solid #000;
            padding: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .remark {
            margin-top: 20px;
            padding: 10px;
            border: 2px dashed #000;
            text-align: center;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .actions {
            text-align: center;
            margin-top: 20px;
        }

        @media print {
            .actions {
                display: none;
            }
        }

        @font-face {
            font-family: 'Tajawal';
            src: url('{{ storage_path('fonts/Tajawal-Regular.ttf') }}') format("truetype");
        }

        #reportContent,
        body {
            font-family: 'Tajawal', sans-serif;
        }
    </style>
</head>

<body>

    <div class="actions">
        <button id="downloadPdfBtn" class="btn btn-dark">تحميل PDF</button>
    </div>

    <div id="reportContent">
        <div class="header">
            <h1 id="ourCompanyName">شركة ابن عباس</h1>
            <h2>فاتورة حجز فندق - رقم: {{ $booking->id }}</h2>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-row"><span>اسم الفندق:</span><span>{{ $booking->hotel->name }}</span></div>
                <div class="info-row"><span>اسم العميل:</span><span>{{ $booking->client_name }}</span></div>
                <div class="info-row"><span>نوع الغرفة:</span><span>{{ $booking->room_type }}</span></div>
                <div class="info-row"><span>عدد الأيام:</span><span>{{ $booking->days }}</span></div>
                <div class="info-row"><span>عدد الغرف:</span><span>{{ $booking->rooms }}</span></div>

            </div>
            <div class="info-card">
                <div class="info-row"><span>تاريخ الدخول:</span><span>{{ $booking->check_in->format('Y-m-d') }}</span>
                </div>

                <div class="info-row"><span>تاريخ الخروج:</span><span>{{ $booking->check_out->format('Y-m-d') }}</span>
                </div>

                <div class="info-row"><span>الشركة:</span><span>{{ $booking->company->name ?? '-' }}</span></div>
                <div class="info-row"><span>هاتف التسكين:</span><span style="direction:ltr"> +966 53 882 6016 </span>
                </div>
                <div class="info-row"><span>رقم
                        الفاتورة:</span><span>{{ $booking->id }}-{{ $booking->agent_id }}-{{ $booking->hotel->id }}</span>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>العدد</th>
                    <th>نوع الغرفة</th>
                    <th>الإطلالة</th>
                    <th>الوجبة</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $booking->rooms }}</td>
                    <td>{{ $booking->room_type }}</td>
                    <td>City View</td>
                    <td>RO</td>
                </tr>
            </tbody>
        </table>

        <div class="remark">
            ملاحظة: الدخول يبدأ من الساعة الثالثة مساءً
        </div>

        <div class="footer">
            تمت الطباعة بواسطة: {{ $booking->employee->name ?? 'النظام' }} - بتاريخ {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- مكتبات PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('downloadPdfBtn');
            btn?.addEventListener('click', async () => {
                btn.disabled = true;
                const old = btn.textContent;
                btn.textContent = 'جاري التحميل...';
                try {
                    if (document.fonts && document.fonts.ready) await document.fonts.ready;

                    const content = document.getElementById('reportContent');
                    const canvas = await html2canvas(content, {
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#ffffff'
                    });

                    const imgData = canvas.toDataURL('image/jpeg', 0.95);
                    const {
                        jsPDF
                    } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');

                    const pdfW = pdf.internal.pageSize.getWidth();
                    const pdfH = pdf.internal.pageSize.getHeight();
                    const imgW = pdfW;
                    const imgH = (canvas.height * imgW) / canvas.width;

                    let heightLeft = imgH;
                    let position = 0;

                    pdf.addImage(imgData, 'JPEG', 0, position, imgW, imgH);
                    heightLeft -= pdfH;

                    while (heightLeft > 0) {
                        position -= pdfH;
                        pdf.addPage();
                        pdf.addImage(imgData, 'JPEG', 0, position, imgW, imgH);
                        heightLeft -= pdfH;
                    }
                    //         $fileName = 'hotel-voucher-' . $booking->id .'-'. $booking->client_name.'.pdf';

                    pdf.save('hotel-voucher-{{ $booking->id }}-{{ $booking->client_name }}.pdf');
                } catch (e) {
                    alert('حدث خطأ أثناء توليد الـ PDF');
                    console.error(e);
                } finally {
                    btn.disabled = false;
                    btn.textContent = old;
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const companies = [
                "شركة ابن عباس",
                "شركة صرح وصال المشاعر",
                "شركة إبتاح"
            ];

            let currentIndex = 0;
            const nameElement = document.getElementById('ourCompanyName');

            nameElement.addEventListener('click', function() {
                currentIndex = (currentIndex + 1) % companies.length;
                nameElement.textContent = companies[currentIndex];
            });
        });
    </script>

</body>

</html>
