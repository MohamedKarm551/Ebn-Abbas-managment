<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hotel Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .voucher-container {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 40px;
            width: 900px;
            margin: 0 auto;
            background: #ffffff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* العلامة المائية في الخلفية - محسنة */
        .voucher-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            height: 400px;
            opacity: 0.25;
            transform: translate(-50%, -50%) rotate(-5deg);
            z-index: 5;
            pointer-events: none;
            user-select: none;
            background: url('{{ asset('images/cover.jpg') }}') no-repeat center center;
            background-size: contain;
            /* filter: grayscale(30%) brightness(1.1) contrast(0.7); */
        }

        /* إزالة الطبقة الإضافية لجعل الصورة أوضح */
        .voucher-bg::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(120deg, rgba(16, 185, 129, 0.95) 60%, rgba(37, 99, 235, 0.95) 100%);
            border-radius: 12px;
            color: white;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(3px);
        }

        .header img {
            width: 100px;
            height: auto;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.3);
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 28px;
            margin: 10px 0;
            font-weight: 700;
        }

        .header h2 {
            font-size: 18px;
            margin: 0;
            font-weight: 400;
            opacity: 0.9;
        }

        hr {
            border: none;
            height: 3px;
            background: linear-gradient(120deg, #10b981 60%, #2563eb 100%);
            margin: 25px 0;
            border-radius: 2px;
            position: relative;
            z-index: 2;
        }

        /* تحسين تنسيق المعلومات الأساسية */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .info-card {
            background: rgba(248, 249, 250, 0.9);
            border: 1px solid rgba(233, 236, 239, 0.8);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            backdrop-filter: blur(3px);
        }

        .info-card:hover {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            font-weight: 700;
            color: #495057;
            font-size: 14px;
            min-width: 120px;
        }

        .info-value {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            position: relative;
            z-index: 2;
            backdrop-filter: blur(3px);
        }

        table th,
        table td {
            border: 1px solid rgba(233, 236, 239, 0.8);
            padding: 15px;
            text-align: left;
            background: rgba(255, 255, 255, 0.85);
        }

        table th {
            background: linear-gradient(120deg, #10b981 60%, #2563eb 100%) !important;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
        }

        table tr:nth-child(even) td {
            background: rgba(248, 249, 250, 0.85);
        }

        table tr:hover td {
            background: rgba(16, 185, 129, 0.08);
        }

        .section-title {
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 15px;
            font-size: 18px;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 2;
        }

        .remarks {
            text-align: center;
            font-size: 20px;
            background: rgba(255, 243, 205, 0.9);
            font-weight: bold;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid rgba(255, 193, 7, 0.8);
            margin-top: 25px;
            position: relative;
            z-index: 2;
            color: #856404;
            backdrop-filter: blur(3px);
        }

        .remarks::before {
            content: '⚠️';
            position: absolute;
            top: -10px;
            left: 20px;
            background: #fff;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 16px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            text-align: left;
            color: #6c757d;
            position: relative;
            z-index: 2;
        }

        /* تنسيق البيانات المختلفة */
        .voucher-number {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            font-size: 13px;
        }

        .guest-info {
            background: linear-gradient(120deg, #10b981 60%, #2563eb 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 500;
        }

        .date-info {
            background: #17a2b8;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 500;
        }

        .room-info {
            background: #6f42c1;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 500;
        }

        .contact-info {
            background: #e83e8c;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 500;
        }

        /* تنسيق الأزرار */
        .btn {
            transition: all 0.3s ease;
            border-radius: 25px;
            padding: 12px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-success {
            background: linear-gradient(120deg, #10b981 60%, #2563eb 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        /* تحسينات الاستجابة */
        @media (max-width: 768px) {
            .voucher-container {
                width: 95%;
                padding: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            table th,
            table td {
                padding: 10px;
                font-size: 14px;
            }

            .voucher-bg {
                width: 350px;
                height: 350px;
                opacity: 0.2;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>

<body>
    <div class="container my-4">
        <div class="d-flex flex-wrap justify-content-between flex-row-reverse align-items-center">
            @auth
            @if(auth()->user()->role === 'Company')
                <a href="{{ route('bookings.index') }}"
                   class="btn btn-warning d-flex align-items-center gap-2 mb-2 mb-md-0">
                    <i class="bi bi-arrow-right"></i>
                    رجوع
                </a>
            @else
                <a href="{{ route('bookings.show', $booking->id) }}"
                   class="btn btn-warning d-flex align-items-center gap-2 mb-2 mb-md-0">
                    <i class="bi bi-arrow-right"></i>
                    رجوع
                </a>
            @endif
            @else
                <a href="{{ url('/') }}" class="btn btn-secondary d-flex align-items-center gap-2 mb-2 mb-md-0">
                    <i class="bi bi-house"></i>
                    رجوع للرئيسية
                </a>
            @endauth

            <div class="mx-auto">
                <button id="downloadVoucher" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-download fs-5"></i>
                    تحميل صورة الفاتورة
                </button>
            </div>
        </div>
    </div>

    <div class="voucher-container">
        <!-- العلامة المائية في الخلفية -->
        <div class="voucher-bg"></div>
        
        <div class="header">
            <img src="{{ asset('images/cover.jpg') }}" alt="Hotel Logo">
            <h1>شركة ابن عباس</h1>
            <h2>لخدمات الحج والعمرة</h2>
        </div>

        <hr>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Hotel Name:</span>
                    <div class="info-value">
                        <span class="guest-info">{{ $booking->hotel->name }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Guest Name:</span>
                    <div class="info-value">
                        <span class="guest-info">{{ $booking->client_name }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Check In:</span>
                    <div class="info-value">
                        <span class="date-info">{{ $booking->check_in->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Room Type:</span>
                    <div class="info-value">
                        <span class="room-info">{{ $booking->room_type }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Hotel phone:</span>
                    <div class="info-value">
                        <span class="contact-info">+966 53 882 6016</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Days:</span>
                    <div class="info-value">
                        <span class="date-info">{{ $booking->days }}</span>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Voucher No:</span>
                    <div class="info-value">
                        <span class="voucher-number">{{ $booking->id }}-{{ $booking->agent_id }}-{{ $booking->hotel->id }}-{{ $booking->employee_id }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <div class="info-value">
                        <span class="guest-info">{{ $booking->company->name ?? '' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Check Out:</span>
                    <div class="info-value">
                        <span class="date-info">{{ $booking->check_out->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Qty:</span>
                    <div class="info-value">
                        <span class="room-info">{{ $booking->rooms }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer phone:</span>
                    <div class="info-value">
                        @php
                            $customerPhone = null;
                            if ($booking->notes) {
                                $pattern = '/(?:\+?(?:(?:966|971)\s?(?:5\d{8}|5\d\s?\d{3}\s?\d{4})|(?:965)\s?(?:[569]\d{7}|[569]\d{3}\s?\d{4})|(?:974)\s?(?:[3567]\d{7}|[3567]\d{3}\s?\d{4})|(?:973)\s?(?:[369]\d{7}|[369]\d{3}\s?\d{4})|(?:968)\s?(?:9\d{7}|9\d{3}\s?\d{4})|(?:20)\s?(?:1[0125]\d{8}|1[0125]\d\s?\d{3}\s?\d{4})))|(?:\b05\d{8}\b|\b01[0125]\d{8}\b)/';
                                preg_match($pattern, $booking->notes, $matches);
                                if (!empty($matches[0])) {
                                    $customerPhone = preg_replace('/\s+/', '', $matches[0]);
                                }
                            }
                        @endphp
                        <span class="contact-info">{{ $customerPhone ?? '' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Notes:</span>
                    <div class="info-value">
                        @if ($booking->notes)
                            <span class="guest-info">has notes ..</span>
                        @else
                            <span style="opacity: 0.6;">No Notes</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">Remark:</div>
        <table>
            <tr>
                <th>Qty</th>
                <th>Room Type</th>
                <th>Room View</th>
                <th>Meal</th>
            </tr>
            <tr>
                <td>{{ $booking->rooms }}</td>
                <td>
                    <span class="room-info">{{ $booking->room_type }}</span>
                </td>
                <td>City View</td>
                <td>RO</td>
            </tr>
        </table>

        <div class="remarks text-danger">
            ملحوظة مهمة : الدخول يبدأ من الساعة الثالثة
        </div>

        <div class="footer"></div>
    </div>

    <script>
        document.getElementById('downloadVoucher').addEventListener('click', function() {
            // إضافة تأثير التحميل
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري التحميل...';
            this.disabled = true;
            
            html2canvas(document.querySelector('.voucher-container'), {
                scale: 2,
                useCORS: true,
                allowTaint: true
            }).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'voucher-' + new Date().getTime() + '.png';
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();
                
                // إعادة تعيين النص والحالة
                document.getElementById('downloadVoucher').innerHTML = '<i class="bi bi-download fs-5"></i> تحميل صورة الفاتورة';
                document.getElementById('downloadVoucher').disabled = false;
            });
        });
    </script>
   
    {{-- <script src="{{ asset('js/preventClick.js') }}"></script> --}}
</body>

</html>