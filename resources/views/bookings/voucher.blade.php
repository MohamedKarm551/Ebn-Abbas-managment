<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hotel Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .voucher-container {
            border: 2px solid black;
            padding: 20px;
            width: 800px;
            margin: 0 auto;
            background-color: #fff;
            position: relative;
            overflow: hidden;
        }

        .voucher-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            opacity: 0.07;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
            user-select: none;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 100px;
            height: auto;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
        }

        .header h2 {
            font-size: 16px;
            margin: 0;
            font-weight: normal;
        }

        hr {
            border: 1px solid black;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }

        .remarks {
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            text-align: left;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>

<body>
    <div class="container my-3">
        <div class="d-flex flex-wrap justify-content-between flex-row-reverse align-items-center">
            @auth
            @if(auth()->user()->role === 'Company')
                {{-- لو شركة، يرجع لصفحة الحجوزات الرئيسية --}}
                <a href="{{ route('bookings.index') }}"
                   class="btn btn-warning d-flex align-items-center gap-2 mb-2 mb-md-0">
                    رجوع ➡
                </a>
            @else
                {{-- لو أدمن أو موظف، يرجع لصفحة تفاصيل الحجز --}}
                <a href="{{ route('bookings.show', $booking->id) }}"
                   class="btn btn-warning d-flex align-items-center gap-2 mb-2 mb-md-0">
                    رجوع ➡
                </a>
            @endif
        @else
            {{-- حالة غير متوقعة: لو المستخدم مش عامل login أصلاً، ممكن نرجع للرئيسية أو نخفي الزرار --}}
            <a href="{{ url('/') }}" class="btn btn-secondary d-flex align-items-center gap-2 mb-2 mb-md-0">
                رجوع للرئيسية ➡
            </a>
        @endauth
        {{-- *** نهاية التعديل *** --}}


            <div class="mx-auto ">
                <button id="downloadVoucher" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-download fs-5"></i>
                    تحميل صورة الفاتورة
                </button>
            </div>
        </div>
    </div>
    <div class="voucher-container">
        <img src="{{ asset('images/cover.jpg') }}" alt="bg" class="voucher-bg">
        <div class="header">
            <img src="{{ asset('images/cover.jpg') }}" alt="Hotel Logo">
            <h1> شركة ابن عباس </h1>
            <h2>لخدمات العمرة والحج </h2>
        </div>

        <hr>

        <table>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Hotel Name:</span> {{ $booking->hotel->name }}
                </td>
                <td>
                    <span class="fw-bold">Voucher No:
                    </span>{{ $booking->id }}-{{ $booking->agent_id }}-{{ $booking->hotel->id }}-{{ $booking->employee_id }}
                    {{-- -{{$booking->agnet->name}} --}}
                    {{-- {{ $booking->hotel->name }} --}}
                    {{-- {{$booking->employee->name}} --}}

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Guest Name:</span> {{ $booking->client_name }}
                </td>
                <td>
                    <span class="fw-bold">Company:</span> {{ $booking->company->name ?? '' }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Check In:</span> {{ $booking->check_in->format('d/m/Y') }}
                </td>
                <td>
                    <span class="fw-bold">Check Out:</span> {{ $booking->check_out->format('d/m/Y') }}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Room Type:</span> {{ $booking->room_type }}
                </td>
                <td>
                    <span class="fw-bold">Qty:</span> {{ $booking->rooms }}
                </td>
            </tr>
            {{-- <tr>
                <td colspan="2">
                    <span class="fw-bold">Total Cost:</span> {{ number_format($booking->amount_due_to_hotel, 2) }}
                </td>
                <td>
                    <span class="fw-bold">Amount Paid to Hotel:</span> {{ number_format($booking->amount_paid_to_hotel, 2) }}
                </td>
            </tr> --}}
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Hotel phone: </span> +966 53 882 6016
                </td>
                <td>
                    <span class="fw-bold"> Customer phone : </span> @php
                        $customerPhone = null;
                        if ($booking->notes) {
                            // Regex Explanation:
                            // Group 1: International Formats
                            //   \+? : Optional leading +
                            //   (?:966|971)\s?(5\d{8}|5\d\s?\d{3}\s?\d{4}) : KSA/UAE Mobile (+966/971 5xxxxxxxx or spaced)
                            //   | (?:965)\s?([569]\d{7}|[569]\d{3}\s?\d{4}) : Kuwait Mobile (+965 [569]xxxxxxx or spaced)
                            //   | (?:974)\s?([3567]\d{7}|[3567]\d{3}\s?\d{4}) : Qatar Mobile (+974 [3567]xxxxxxx or spaced)
                            //   | (?:973)\s?([369]\d{7}|[369]\d{3}\s?\d{4}) : Bahrain Mobile (+973 [369]xxxxxxx or spaced)
                            //   | (?:968)\s?(9\d{7}|9\d{3}\s?\d{4}) : Oman Mobile (+968 9xxxxxxx or spaced)
                            //   | (?:20)\s?(1[0125]\d{8}|1[0125]\d\s?\d{3}\s?\d{4}) : Egypt Mobile (+20 1[0125]xxxxxxxx or spaced)
                            // Group 2: Local Formats
                            //   \b05\d{8}\b : KSA/UAE Local Mobile (05xxxxxxxx)
                            //   | \b01[0125]\d{8}\b : Egypt Local Mobile (01[0125]xxxxxxxx)
                            // تحديث النمط (Regex): تم توسيع النمط ليشمل:
                            // الأرقام الدولية:
                            // السعودية (+966) والإمارات (+971) تبدأ بـ 5 (مع أو بدون مسافات).
                            // الكويت (+965) تبدأ بـ 5 أو 6 أو 9 (مع أو بدون مسافات).
                            // قطر (+974) تبدأ بـ 3 أو 5 أو 6 أو 7 (مع أو بدون مسافات).
                            // البحرين (+973) تبدأ بـ 3 أو 6 أو 9 (مع أو بدون مسافات).
                            // عمان (+968) تبدأ بـ 9 (مع أو بدون مسافات).
                            // مصر (+20) تبدأ بـ 10 أو 11 أو 12 أو 15 (مع أو بدون مسافات).
                            // علامة + في البداية اختيارية.
                            // الأرقام المحلية الشائعة:
                            // السعودية/الإمارات تبدأ بـ 05 (10 أرقام).
                            // مصر تبدأ بـ 010, 011, 012, 015 (11 رقمًا).
                            // تنظيف الرقم: بعد العثور على تطابق في $matches[0], تم إضافة preg_replace('/\s+/', '', $matches[0]); لإزالة أي مسافات قد تكون موجودة داخل الرقم الذي تم العثور عليه لتوحيد شكله.

                            $pattern =
                                '/(?:\+?(?:(?:966|971)\s?(?:5\d{8}|5\d\s?\d{3}\s?\d{4})|(?:965)\s?(?:[569]\d{7}|[569]\d{3}\s?\d{4})|(?:974)\s?(?:[3567]\d{7}|[3567]\d{3}\s?\d{4})|(?:973)\s?(?:[369]\d{7}|[369]\d{3}\s?\d{4})|(?:968)\s?(?:9\d{7}|9\d{3}\s?\d{4})|(?:20)\s?(?:1[0125]\d{8}|1[0125]\d\s?\d{3}\s?\d{4})))|(?:\b05\d{8}\b|\b01[0125]\d{8}\b)/';

                            preg_match($pattern, $booking->notes, $matches);

                            if (!empty($matches[0])) {
                                // Clean up the matched number (remove spaces)
                                $customerPhone = preg_replace('/\s+/', '', $matches[0]);
                            }
                        }
                    @endphp
                    {{ $customerPhone ?? '' }} {{-- Display the found number or empty string --}}
                </td>

                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">days:</span> {{ $booking->days }}
                </td>
                <td>
                    <span class="fw-bold">Notes:</span>
                    @if ($booking->notes)
                        has notes ..
                    @else
                        No Notes
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-title">Remark:</div>
        <table>
            <tr>
                <th>Qty</th>
                <th>Room Type</th>
                <th>Room View</th>
                <th>Meal</th>
            </tr>
            <tr>
                {{-- عدد الغرف --}}
                <td> {{ $booking->rooms }} </td>
                <td>
                    {{-- نوع الغرفة --}}
                    <span class="fw-bold">{{ $booking->room_type }}</span>
                </td>
                <td>City View</td>
                <td>RO</td>
            </tr>
        </table>
        <div class="remarks text-danger "
            style="
    text-align: center;
    font-size: 20px;
    background: gold;
    font-weight: bold;
">ملحوظة
            مهمة : الدخول يبدأ من الساعة الثالثة</div>
        <div class="remarks"> </div>
        <div class="footer">
        </div>
    </div>
    <script>
        document.getElementById('downloadVoucher').addEventListener('click', function() {
            html2canvas(document.querySelector('.voucher-container')).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'voucher.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        });
    </script>
    <script>
        // تعطيل كليك يمين
        document.addEventListener('contextmenu', event => event.preventDefault());

        // تعطيل F12 وCtrl+Shift+I وCtrl+U
        document.onkeydown = function(e) {
            if (
                e.keyCode == 123 // F12
                ||
                (e.ctrlKey && e.shiftKey && e.keyCode == 73) // Ctrl+Shift+I
                ||
                (e.ctrlKey && e.keyCode == 85) // Ctrl+U
            ) {
                return false;
            }
        };
    </script>
</body>

</html>
