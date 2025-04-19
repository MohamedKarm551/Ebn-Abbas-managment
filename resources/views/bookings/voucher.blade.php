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

        table th, table td {
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
        <div class="d-flex justify-content-end">
            <button id="downloadVoucher" class="btn btn-success d-flex align-items-center gap-2">
                <i class="bi bi-download fs-5"></i>
                تحميل صورة الفاتورة
            </button>
        </div>
    </div>
    <div class="voucher-container">
        <img src="{{ asset('images/cover.jpg') }}" alt="bg" class="voucher-bg">
        <div class="header">
            <img src="{{ asset('images/cover.jpg') }}" alt="Hotel Logo">
            <h1> شركة ابن عباس </h1>
            <h2>لخدمات العمرة والحج    </h2>
        </div>

        <hr>

        <table>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Hotel Name:</span> التيسير
                </td>
                <td>
                    <span class="fw-bold">Voucher No:</span> 105401
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Hotel Rsrv:</span> <!-- هنا قيمة الحجز لو فيه -->
                </td>
                <td>
                    <span class="fw-bold">Client Req:</span> <!-- هنا قيمة الطلب لو فيه -->
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Check In:</span> 18/04/2025
                </td>
                <td>
                    <span class="fw-bold">Check Out:</span> 20/04/2025
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Check In Time:</span> 04:00 PM
                </td>
                <td>
                    <span class="fw-bold">Check Out Time:</span> 12:00 PM
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Guest Name:</span> يسري السيد محمد فلا
                </td>
                <td>
                    <span class="fw-bold">Guest Number:</span> 01000000000
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="fw-bold">Issue Date:</span> 16/04/2025
                </td>
                <td>
                    <span class="fw-bold">Stamp And Signature:</span>
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
                <td>1</td>
                <td>Quad</td>
                <td>City View</td>
                <td>RO</td>
            </tr>
        </table>

        <div class="remarks">Transportation:</div>
        <div class="footer">Any Amount in Excess of the value of this order to be collected directly from the guest</div>
    </div>
    <script>
        document.getElementById('downloadVoucher').addEventListener('click', function () {
            html2canvas(document.querySelector('.voucher-container')).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'voucher.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        });
    </script>
</body>
</html>