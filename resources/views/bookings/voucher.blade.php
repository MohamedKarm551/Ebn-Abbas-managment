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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
            border: 3px solid rgba(255, 255, 255, 0.3);
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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

        /* تنظيم شريط الأدوات ليكون ريسبونسف ومرتب */
        .tools-bar {
            gap: 10px !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            margin-bottom: 10px;
        }

        @media (max-width: 600px) {
            .tools-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

            .tools-bar .btn,
            .tools-bar .dropdown {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>

<body>
    <div class="container my-4">
        <div class="d-flex flex-wrap justify-content-between flex-row-reverse align-items-center">
            @auth
                @if (auth()->user()->role === 'Company')
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

            <div class="mx-auto d-flex flex-wrap gap-2 justify-content-center align-items-center tools-bar">
                <button id="changeLogo" class="btn btn-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-image fs-5"></i>
                    تغيير اللوجو
                </button>
                <button id="changeGradient" class="btn btn-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-palette fs-5"></i>
                    تغيير التدرج
                </button>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-secondary d-flex align-items-center gap-2 dropdown-toggle" type="button"
                        id="showGradientsBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        كل التدرجات
                    </button>
                    <ul class="dropdown-menu p-3" aria-labelledby="showGradientsBtn"
                        style="min-width: 350px; max-width: 500px;">
                        <div id="gradientsPalette" class="d-flex flex-wrap gap-2"></div>
                    </ul>
                </div>
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
            <img id="headerLogo" src="{{ asset('images/cover.jpg') }}" alt="Hotel Logo">
            <h1 id="companyName">شركة ابن عباس</h1>
            <h2 id="companySlogan">لخدمات الحج والعمرة</h2>
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
                        <span
                            class="voucher-number">{{ $booking->id }}-{{ $booking->agent_id }}-{{ $booking->hotel->id }}-{{ $booking->employee_id }}</span>
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
                                $pattern =
                                    '/(?:\+?(?:(?:966|971)\s?(?:5\d{8}|5\d\s?\d{3}\s?\d{4})|(?:965)\s?(?:[569]\d{7}|[569]\d{3}\s?\d{4})|(?:974)\s?(?:[3567]\d{7}|[3567]\d{3}\s?\d{4})|(?:973)\s?(?:[369]\d{7}|[369]\d{3}\s?\d{4})|(?:968)\s?(?:9\d{7}|9\d{3}\s?\d{4})|(?:20)\s?(?:1[0125]\d{8}|1[0125]\d\s?\d{3}\s?\d{4})))|(?:\b05\d{8}\b|\b01[0125]\d{8}\b)/';
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
                document.getElementById('downloadVoucher').innerHTML =
                    '<i class="bi bi-download fs-5"></i> تحميل صورة الفاتورة';
                document.getElementById('downloadVoucher').disabled = false;
            });
        });
    </script>
    <script>
        // Array للوجوهات والأسماء المختلفة
        const companies = [{
                name: "شركة ابن عباس",
                logo: "{{ asset('images/cover.jpg') }}"
            },
            {
                name: "شركة صرح وصال المشاعر",
                logo: "{{ asset('images/sarhWesal.png') }}"
            },
            {
                name: "شركة إبتاح",
                logo: "{{ asset('images/EptahLogo.png') }}"
            }
        ];

        let currentCompanyIndex = 0;

        // تغيير اللوجو واسم الشركة
        document.getElementById('changeLogo').addEventListener('click', function() {
            currentCompanyIndex = (currentCompanyIndex + 1) % companies.length;

            // تغيير لوجو الهيدر
            document.getElementById('headerLogo').src = companies[currentCompanyIndex].logo;

            // تغيير اسم الشركة
            document.getElementById('companyName').textContent = companies[currentCompanyIndex].name;

            // تغيير العلامة المائية
            document.querySelector('.voucher-bg').style.backgroundImage =
                `url('${companies[currentCompanyIndex].logo}')`;
        });

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
                document.getElementById('downloadVoucher').innerHTML =
                    '<i class="bi bi-download fs-5"></i> تحميل صورة الفاتورة';
                document.getElementById('downloadVoucher').disabled = false;
            });
        });
    </script>
    <script>
        // ====================
        // تعريف المتغيرات الرئيسية في الأعلى حتى يمكن استخدامها في أي مكان
        // ====================

        // المتغير الذي يحتفظ برقم التدرج الحالي (صفر يعني أول تدرج)
        let currentGradientIndex = 0;

        // مصفوفة التدرجات مع ألوان العناصر المصاحبة (الضيوف، التاريخ، الغرفة، التواصل)
        const defaultGradients = [{
                bg: "linear-gradient(120deg, #10b981 60%, #2563eb 100%)",
                guest: "linear-gradient(120deg, #10b981 60%, #2563eb 100%)",
                date: "#17a2b8",
                room: "#6f42c1",
                contact: "#e83e8c"
            },
            {
                bg: "linear-gradient(120deg, #272A35 0%, #A1B1CC 50%, #848B9B 100%)",
                guest: "#A1B1CC",
                date: "#272A35",
                room: "#848B9B",
                contact: "#272A35"
            },
            {
                bg: "linear-gradient(120deg, #C58746 0%, #573620 50%, #2A180D 80%, #FBCF4C 90%, #C4B79B 100%)",
                guest: "#FBCF4C",
                date: "#C58746",
                room: "#573620",
                contact: "#C4B79B"
            },
            {
                bg: "linear-gradient(120deg, #F2F2F3 0%, #E89A37 40%, #D7711F 70%, #A33D0B 90%, #8A4F25 100%)",
                guest: "#E89A37",
                date: "#A33D0B",
                room: "#D7711F",
                contact: "#8A4F25"
            },
            {
                bg: "linear-gradient(135deg, #ffb88c 0%, #de6262 100%)",
                guest: "#de6262",
                date: "#ffb88c",
                room: "#de6262",
                contact: "#ffb88c"
            },
            {
                bg: "linear-gradient(120deg, #a2d4fa 0%, #076585 100%)",
                guest: "#076585",
                date: "#a2d4fa",
                room: "#076585",
                contact: "#a2d4fa"
            },
            {
                bg: "linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%)",
                guest: "#a6c1ee",
                date: "#fbc2eb",
                room: "#a6c1ee",
                contact: "#fbc2eb"
            },
            {
                bg: "linear-gradient(120deg, #96fbc4 0%, #f9f586 100%)",
                guest: "#96fbc4",
                date: "#f9f586",
                room: "#96fbc4",
                contact: "#f9f586"
            },
            {
                bg: "linear-gradient(120deg, #434343 0%, #262626 100%)",
                guest: "#434343",
                date: "#262626",
                room: "#434343",
                contact: "#262626"
            },
            {
                bg: "linear-gradient(120deg, #a8ff78 0%, #78ffd6 100%)",
                guest: "#78ffd6",
                date: "#a8ff78",
                room: "#78ffd6",
                contact: "#a8ff78"
            },
            {
                bg: "linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%)",
                guest: "#ffd1ff",
                date: "#fad0c4",
                room: "#ffd1ff",
                contact: "#fad0c4"
            },
            {
                bg: "linear-gradient(135deg, #43cea2 0%, #185a9d 100%)",
                guest: "#185a9d",
                date: "#43cea2",
                room: "#185a9d",
                contact: "#43cea2"
            },
            {
                bg: "linear-gradient(135deg, #ffe259 0%, #ffa751 100%)",
                guest: "#ffa751",
                date: "#ffe259",
                room: "#ffa751",
                contact: "#ffe259"
            },
            {
                bg: "linear-gradient(120deg, #c471f5 0%, #fa71cd 100%)",
                guest: "#fa71cd",
                date: "#c471f5",
                room: "#fa71cd",
                contact: "#c471f5"
            },
            {
                bg: "linear-gradient(120deg, #11998e 0%, #38ef7d 100%)",
                guest: "#38ef7d",
                date: "#11998e",
                room: "#11998e",
                contact: "#38ef7d"
            },
            {
                bg: "linear-gradient(135deg, #f7971e 0%, #ffd200 100%)",
                guest: "#ffd200",
                date: "#f7971e",
                room: "#ffd200",
                contact: "#f7971e"
            },
            {
                bg: "linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)",
                guest: "#43e97b",
                date: "#38f9d7",
                room: "#43e97b",
                contact: "#38f9d7"
            },
            {
                bg: "linear-gradient(120deg, #fc5c7d 0%, #6a82fb 100%)",
                guest: "#fc5c7d",
                date: "#6a82fb",
                room: "#fc5c7d",
                contact: "#6a82fb"
            },
            {
                bg: "linear-gradient(135deg, #7f53ac 0%, #647dee 100%)",
                guest: "#647dee",
                date: "#7f53ac",
                room: "#647dee",
                contact: "#7f53ac"
            },
            {
                bg: "linear-gradient(135deg, #232526 0%, #414345 100%)",
                guest: "linear-gradient(135deg, #fd746c 0%, #ff9068 100%)",
                date: "#1fa2ff",
                room: "#232526",
                contact: "#ff9068"
            }
        ];
        // إذا لم توجد التدرجات في الكاش، احفظها أول مرة
        if (!localStorage.getItem('voucher_gradients')) {
            localStorage.setItem('voucher_gradients', JSON.stringify(defaultGradients));
        }

        // في كل مرة: استرجع التدرجات من الكاش
        const gradients = JSON.parse(localStorage.getItem('voucher_gradients'));

        // ابدأ دائمًا بأول تدرج (دون استرجاع آخر تدرج مختار)
        // let currentGradientIndex = 0;


        // ====================
        // بداية الأحداث بعد تحميل الصفحة (تأكد أن العناصر كلها ظهرت)
        // ====================
        document.addEventListener('DOMContentLoaded', function() {
            const palette = document.getElementById('gradientsPalette');
            if (palette) {
                palette.innerHTML = '';
                gradients.forEach((g, idx) => {
                    const swatch = document.createElement('div');
                    swatch.style.width = "48px";
                    swatch.style.height = "48px";
                    swatch.style.borderRadius = "10px";
                    swatch.style.cursor = "pointer";
                    swatch.style.border = "2px solid #eee";
                    swatch.style.background = g.bg;
                    swatch.title = `تدرج رقم ${idx + 1}`;
                    swatch.addEventListener('click', function() {
                        applyGradient(idx);
                        currentGradientIndex = idx;

                        // إغلاق القائمة المنسدلة برمجياً (Bootstrap 5)
                        const dropdown = bootstrap.Dropdown.getOrCreateInstance(document
                            .getElementById('showGradientsBtn'));
                        dropdown.hide();
                    });
                    palette.appendChild(swatch);
                });
            }

            document.getElementById('changeGradient').addEventListener('click', function() {
                currentGradientIndex = (currentGradientIndex + 1) % gradients.length;
                applyGradient(currentGradientIndex);
            });

            applyGradient(currentGradientIndex);
        });
        // ====================
        // دالة موحدة لتطبيق ألوان التدرج على كل عناصر الصفحة حسب رقم التدرج
        // ====================
        function applyGradient(idx) {
            const g = gradients[idx];
            if (!g) return; // إذا لم يوجد هذا التدرج توقف

            // 1. تغيير تدرج خلفية الهيدر (المربع العلوي)
            document.querySelector('.header').style.background = g.bg;

            // 2. تغيير لون جميع عناصر الضيوف
            document.querySelectorAll('.guest-info').forEach(el => {
                el.style.background = g.guest;
                el.style.color = "#fff"; // اجعل النص أبيض لظهور أوضح
            });

            // 3. تغيير لون جميع عناصر التاريخ
            document.querySelectorAll('.date-info').forEach(el => {
                el.style.background = g.date;
                el.style.color = "#fff";
            });

            // 4. تغيير لون جميع عناصر الغرف
            document.querySelectorAll('.room-info').forEach(el => {
                el.style.background = g.room;
                el.style.color = "#fff";
            });

            // 5. تغيير لون جميع عناصر التواصل
            document.querySelectorAll('.contact-info').forEach(el => {
                el.style.background = g.contact;
                el.style.color = "#fff";
            });
        }
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- <script src="{{ asset('js/preventClick.js') }}"></script> --}}
</body>

</html>
