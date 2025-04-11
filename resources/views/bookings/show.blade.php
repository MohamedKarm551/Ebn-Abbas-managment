@extends('layouts.app')

<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

@section('content')
@php
    $total_nights = \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out));
@endphp

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="text-white">تفاصيل الحجز للعميل: {{ $booking->client_name }}</h1>
        <div>
            <a href="{{ route('bookings.index') }}" class="btn btn-secondary">رجوع ➡</a>
            <button id="copyBookingDetails" class="btn btn-primary"> 📄نسخ بيانات الحجز 📋</button>
            <button id="calculate-total" class="btn btn-info"> 📝 الاجمالي📜</button>
        </div>
    </div>
    <table class="table table-dark table-hover table-bordered text-center">
        <thead>
            <tr>
                <th>#</th>
                <th>العنوان</th>
                <th>القيمة</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>اسم الشركة <i class="fas fa-building text-primary"></i></td>
                <td>{{ $booking->company->name ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td>جهة الحجز <i class="fas fa-user-tie text-success"></i></td>
                <td>{{ $booking->agent->name ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td>3</td>
                <td>اسم الفندق <i class="fas fa-hotel text-info"></i></td>
                <td>{{ $booking->hotel->name ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td>4</td>
                <td>نوع الغرفة <i class="fas fa-bed text-warning"></i></td>
                <td>{{ $booking->room_type }}</td>
            </tr>
            <tr>
                <td>5</td>
                <td>عدد الغرف <i class="fas fa-door-open text-danger"></i></td>
                <td>{{ $booking->rooms }}</td>
            </tr>
            <tr>
                <td>6</td>
                <td>تاريخ الدخول <i class="fas fa-calendar-check text-primary"></i></td>
                <td>{{ $booking->check_in->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>7</td>
                <td>تاريخ الخروج <i class="fas fa-calendar-times text-danger"></i></td>
                <td>{{ $booking->check_out->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>8</td>
                <td>عدد الليالي <i class="fas fa-moon text-warning"></i></td>
                <td>{{ $total_nights }} ليلة</td>
            </tr>
            <tr>
                <td>9</td>
                <td>الأيام المتبقية حتى الخروج <i class="fas fa-clock text-info"></i></td>
                <td>
                    @php
                        $remaining_days = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($booking->check_out)->startOfDay(), false);
                    @endphp
                    {{ $remaining_days > 0 ? $remaining_days . ' يوم' : 'انتهى الحجز' }}
                </td>
            </tr>
            <tr>
                <td>10</td>
                <td> السعر من الفندق <i class="fas fa-money-bill-wave text-success"></i></td>
                <td>{{ $booking->cost_price }} ريال</td>
            </tr>
            <tr>
                <td>11</td>
                <td> المبلغ المدفوع للفندق <i class="fas fa-money-check-alt text-primary"></i></td>
                <td>{{ $booking->amount_paid_to_hotel }} ريال</td>
            </tr>
            <tr>
                <td>12</td>
                <td> الباقي للفندق <i class="fas fa-money-check text-danger"></i></td>
                <td>{{ $booking->amount_due_to_hotel - $booking->amount_paid_to_hotel }} ريال</td>
            </tr>
            <tr>
                <td>13</td>
                <td> سعر البيع للشركة <i class="fas fa-tag text-warning"></i> </td>
                <td>{{ $booking->sale_price }} ريال</td>
            </tr>
            <tr>
                <td>14</td>
                <td>المبلغ المستحق من الشركة <i class="fas fa-hand-holding-usd text-success"></i> </td>
                <td>{{ $booking->amount_due_from_company }} ريال</td>
            </tr>
            <tr>
                <td>15</td>
                <td> المبلغ المدفوع من الشركة<i class="fas fa-wallet text-info"></i>  </td>
                <td>{{ $booking->amount_paid_by_company }} ريال</td>
            </tr>
            <tr>
                <td>16</td>
                <td>الباقي من الشركة <i class="fas fa-balance-scale text-danger"></i> </td>
                <td>{{ $booking->amount_due_from_company - $booking->amount_paid_by_company }} ريال</td>
            </tr>
            <tr>
                <td>17</td>
                <td> الموظف المسؤول <i class="fas fa-user text-primary"></i> </td>
                <td>{{ $booking->employee->name ?? 'غير محدد' }}</td>
            </tr>
            <tr>
                <td>18</td>
                <td> الملاحظات <i class="fas fa-sticky-note text-warning"></i> </td>
                <td>{{ $booking->notes }}</td>
            </tr>
        </tbody>
    </table>

    <h3>سجل التعديلات</h3>
    <ul id="editLog"></ul>
</div>



<style>
    body {
        background-color: #121212;
        color: #ffffff;
    }

    .table {
        border-color: #444;
    }

    .table th, .table td {
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: #333;
    }

    .copyable {
        cursor: pointer;
        color: #00bcd4;
    }

    .copyable:hover {
        text-decoration: underline;
    }

    .alert {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999; /* لضمان ظهوره فوق جميع العناصر */
        padding: 15px;
        border: 1px solid transparent;
        border-radius: 4px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        width: 90%; /* عرض التنبيه */
        max-width: 500px; /* الحد الأقصى للعرض */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }

    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeeba;
    }

    .d-flex {
        display: flex;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .btn {
        margin-left: 5px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyButton = document.getElementById('copyBookingDetails');
        if (copyButton) {
            copyButton.addEventListener('click', function () {
                try {
                    const bookingDetails = `📋 *تفاصيل الحجز للعميل:* {{ $booking->client_name }}\n\n` +
                        Array.from(document.querySelectorAll('.table tbody tr'))
                            .map(row => {
                                const cells = row.querySelectorAll('td'); // استخراج الأعمدة
                                const number = cells[0]?.innerText.trim(); // الرقم
                                const title = cells[1]?.innerText.trim(); // العنوان
                                const value = cells[2]?.innerText.trim(); // القيمة

                                // إضافة الإيموجي المناسبة بناءً على العنوان
                                let emoji = '';
                                if (title.includes('اسم الشركة')) emoji = '🏢';
                                else if (title.includes('جهة الحجز')) emoji = '👔';
                                else if (title.includes('اسم الفندق')) emoji = '🏨';
                                else if (title.includes('نوع الغرفة')) emoji = '🛏️';
                                else if (title.includes('عدد الغرف')) emoji = '🚪';
                                else if (title.includes('تاريخ الدخول') || title.includes('تاريخ الخروج')) emoji = '📅';
                                else if (title.includes('عدد الليالي')) emoji = '🌙';
                                else if (title.includes('الأيام المتبقية حتى الخروج')) emoji = '⏳';
                                else if (title.includes('السعر من الفندق')) emoji = '💵';
                                else if (title.includes('المبلغ المدفوع للفندق')) emoji = '💳';
                                else if (title.includes('الباقي للفندق')) emoji = '💸';
                                else if (title.includes('سعر البيع للشركة')) emoji = '💵';
                                else if (title.includes('المبلغ المستحق من الشركة')) emoji = '💰';
                                else if (title.includes('المبلغ المدفوع من الشركة')) emoji = '💼';
                                else if (title.includes('الباقي من الشركة')) emoji = '⚖️';
                                else if (title.includes('الموظف المسؤول')) emoji = '👤';
                                else if (title.includes('الملاحظات')) emoji = '📝';

                                return `${emoji} ${number}. ${title}: ${value}`; // دمج النصوص مع الإيموجي
                            })
                            .join('\n'); // فصل النصوص بخط جديد

                    navigator.clipboard.writeText(bookingDetails).then(() => {
                        showAlert('تم نسخ بيانات الحجز بنجاح!', 'success');
                    }).catch(err => {
                        console.error('خطأ أثناء نسخ البيانات:', err);
                        showAlert('حدث خطأ أثناء نسخ البيانات.', 'danger');
                    });
                } catch (error) {
                    console.error('خطأ غير متوقع:', error);
                    showAlert('حدث خطأ أثناء نسخ البيانات.', 'danger');
                }
            });
        }

        document.getElementById('calculate-total').addEventListener('click', function () {
            let totalDueFromCompany = 0;
            let totalDueToHotel = 0;

            // حساب عدد الليالي التي قضاها العميل حتى الآن
            let checkInDate = new Date("{{ $booking->check_in }}");
            let today = new Date();
            let nightsStayed = Math.min(
                Math.max(0, Math.ceil((today - checkInDate) / (1000 * 60 * 60 * 24))),
                {{ $booking->days }}
            );

            // حساب الإجمالي
            totalDueFromCompany += nightsStayed * {{ $booking->rooms }} * {{ $booking->sale_price }};
            totalDueToHotel += nightsStayed * {{ $booking->rooms }} * {{ $booking->cost_price }};

            // عرض المعادلة بالتفصيل
            showAlert(`💲 الإجمالي حتى الآن: 💲
ما لك من الشركة: ${nightsStayed} ليلة * ${ {{ $booking->rooms }} } غرفة * ${ {{ $booking->sale_price }} } سعر الليلة = ${totalDueFromCompany} ريال
ما عليك للفندق: ${nightsStayed} ليلة * {{ $booking->rooms }} غرفة * {{ $booking->cost_price }} سعر الفندق = ${totalDueToHotel} ريال`, 'info');
        });

        function showAlert(message, type) {
            const alertBox = document.createElement('div');
            alertBox.className = `alert alert-${type}`;
            alertBox.innerText = message;

            // إضافة التنبيه إلى أعلى الصفحة
            document.body.appendChild(alertBox);

            // إزالة التنبيه بعد 5 ثوانٍ
            setTimeout(() => {
                alertBox.remove();
            }, 5000);
        }
    });
</script>
@endsection

