@extends('layouts.app')
{{-- *** بداية الكود الجديد: تحديد عنوان الصفحة *** --}}
@section('title', 'تفاصيل حجز : ' . $booking->client_name)
{{-- *** نهاية الكود الجديد *** --}}

<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/booking-details.svg') }}">
</head>

@section('content')
    @php
        $total_nights = \Carbon\Carbon::parse($booking->check_in)->diffInDays(
            \Carbon\Carbon::parse($booking->check_out),
        );
        $editLogs = \App\Models\EditLog::where('booking_id', $id)->orderBy('created_at', 'desc')->get();
    @endphp
    <div class="container">
        <div class="row align-items-center mb-3">
            <div class="col-12 col-lg-7 mb-2 mb-lg-0">
                <h1 class="h4 mb-0 text-center text-lg-start">تفاصيل الحجز للعميل: {{ $booking->client_name }}
                    <br>
                    <a href="{{ route('bookings.voucher', $booking->id) }}" class="btn btn-warning btn-sm" target="_blank">
                        عرض الفاوتشر
                    </a>
                </h1>
            </div>
            <div class="col-12 col-lg-5 d-flex justify-content-center justify-content-lg-end gap-2">
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary">رجوع ➡</a>
                <button id="copyBookingDetails" class="btn btn-primary">📄 نسخ بيانات الحجز 📋</button>
                <button id="calculate-total" class="btn btn-info">📝 الاجمالي 📜</button>

            </div>
        </div>
        <table class="table  table-hover table-bordered text-center">
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
                            // نضبط وقت كلا التاريخين لبداية اليوم (00:00:00)
                            $today = \Carbon\Carbon::now()->startOfDay();
                            $checkoutDate = \Carbon\Carbon::parse($booking->check_out)->startOfDay();
                            // نحسب الفرق بالأيام الصحيحة (مع تجاهل الإشارة السالبة لو التاريخ فات)
                        $remaining_days = $today->diffInDays($checkoutDate, false); @endphp
                        {{ $remaining_days > 0 ? intval($remaining_days) . ' يوم' : 'انتهى الحجز' }}
                    </td>

                </tr>
                <tr>
                    <td>10</td>
                    <td> السعر من الفندق <i class="fas fa-money-bill-wave text-success"></i></td>
                    <td>{{ $booking->cost_price }} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <!-- صف المستحق للفندق المحسوب ديناميكياً -->
                <tr id="hotel-due-row">
                    <td>11</td>
                    <td>المستحق للفندق <i class="fas fa-hand-holding-usd text-info"></i></td>
                    <td id="hotel-due-value">{{ $total_nights * $booking->rooms * $booking->cost_price }} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>12</td>
                    <td> المبلغ المدفوع للفندق <i class="fas fa-money-check-alt text-primary"></i></td>
                    <td>{{ $booking->amount_paid_to_hotel }} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>13</td>
                    <td> الباقي للفندق <i class="fas fa-money-check text-danger"></i></td>
                    <td>{{ $booking->amount_due_to_hotel - $booking->amount_paid_to_hotel }} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>14</td>
                    <td> سعر البيع للشركة <i class="fas fa-tag text-warning"></i> </td>
                    <td>{{ $booking->sale_price }}  {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>15</td>
                    <td>المبلغ المستحق من الشركة <i class="fas fa-hand-holding-usd text-success"></i> </td>
                    <td>{{ number_format($booking->amount_due_from_company, 2) }}
                    {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
                    </td>
                </tr>
                <tr>
                    <td>16</td>
                    <td> المبلغ المدفوع من الشركة<i class="fas fa-wallet text-info"></i> </td>
                    <td>{{ number_format($booking->amount_paid_by_company, 2) }}
                        {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }} </td>
                </tr>
                <tr>
                    <td>17</td>
                    <td>الباقي من الشركة <i class="fas fa-balance-scale text-danger"></i> </td>
                    <td>{{ number_format($booking->amount_due_from_company - $booking->amount_paid_by_company, 2) }}
                        {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }} </td>
                </tr>
                <tr>
                    <td>18</td>
                    <td> الموظف المسؤول <i class="fas fa-user text-primary"></i> </td>
                    <td>{{ $booking->employee->name ?? 'غير محدد' }}</td>
                </tr>
                <tr>
                    <td>19</td>
                    <td> الملاحظات <i class="fas fa-sticky-note text-warning"></i> </td>
                    <td>{{ $booking->notes }}</td>
                </tr>
            </tbody>
        </table>

        <h3>سجل التعديلات</h3>
        @if ($editLogs->isEmpty())
            <p>لا توجد تعديلات مسجلة لهذا الحجز.</p>
        @else
            @php
                $fieldNames = [
                    'id' => '#',
                    'client_name' => 'اسم العميل',
                    'company_id' => 'الشركة',
                    'agent_id' => 'جهة الحجز',
                    'hotel_id' => 'الفندق',
                    'room_type' => 'نوع الغرفة',
                    'check_in' => 'تاريخ الدخول',
                    'check_out' => 'تاريخ الخروج',
                    'days' => 'عدد الأيام',
                    'rooms' => 'عدد الغرف',
                    'cost_price' => 'سعر الفندق',
                    'amount_due_to_hotel' => 'المبلغ المستحق للفندق',
                    'amount_paid_to_hotel' => 'المدفوع للفندق',
                    'sale_price' => 'سعر البيع',
                    'employee_id' => 'الموظف المسؤول',
                    'amount_due_from_company' => 'المبلغ المستحق من الشركة',
                    'amount_paid_by_company' => 'المدفوع من الشركة',
                    'payment_status' => 'حالة السداد',
                    'notes' => 'الملاحظات',
                    'created_at' => 'تاريخ الإنشاء',
                    'updated_at' => 'آخر تعديل',
                ];
            @endphp
            <table class="table  table-hover table-bordered text-center ">
                <thead>
                    <tr>
                        <th>الحقل المعدل</th>
                        <th>القيمة القديمة</th>
                        <th>القيمة الجديدة</th>
                        <th>تاريخ التعديل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($editLogs as $log)
                        @if (trim($log->old_value) !== trim($log->new_value))
                            <!-- تجاهل الحقول غير المعدلة -->
                            <tr>
                                <!-- عرض اسم الحقل المعدل -->
                                <td>{{ $fieldNames[$log->field] ?? $log->field }}</td>

                                <!-- عرض القيمة القديمة -->
                                <td>
                                    @if ($log->field === 'employee_id')
                                        <!-- إذا كان الحقل هو الموظف المسؤول، جلب اسم الموظف بدلاً من الـ ID -->
                                        {{ \App\Models\Employee::find($log->old_value)?->name ?? $log->old_value }}
                                    @elseif ($log->field === 'company_id')
                                        <!-- إذا كان الحقل هو الشركة، جلب اسم الشركة بدلاً من الـ ID -->
                                        {{ \App\Models\Company::find($log->old_value)?->name ?? $log->old_value }}
                                    @elseif ($log->field === 'hotel_id')
                                        <!-- إذا كان الحقل هو الفندق، جلب اسم الفندق بدلاً من الـ ID -->
                                        {{ \App\Models\Hotel::find($log->old_value)?->name ?? $log->old_value }}
                                    @elseif ($log->field === 'agent_id')
                                        <!-- إذا كان الحقل هو جهة الحجز، جلب اسم جهة الحجز بدلاً من الـ ID -->
                                        {{ \App\Models\Agent::find($log->old_value)?->name ?? $log->old_value }}
                                    @elseif (in_array($log->field, ['check_in', 'check_out']))
                                        <!-- إذا كان الحقل هو تاريخ، تنسيق التاريخ لعرضه بشكل مناسب -->
                                        {{ $log->old_value ? \Carbon\Carbon::parse($log->old_value)->format('d/m/Y') : 'غير محدد' }}
                                    @else
                                        <!-- عرض القيمة القديمة كما هي إذا لم تكن من الحقول الخاصة -->
                                        {{ $log->old_value ?: 'غير محدد' }}
                                    @endif
                                </td>

                                <!-- عرض القيمة الجديدة -->
                                <td>
                                    @if ($log->field === 'employee_id')
                                        <!-- إذا كان الحقل هو الموظف المسؤول، جلب اسم الموظف الجديد بدلاً من الـ ID -->
                                        {{ \App\Models\Employee::find($log->new_value)?->name ?? $log->new_value }}
                                    @elseif ($log->field === 'company_id')
                                        <!-- إذا كان الحقل هو الشركة، جلب اسم الشركة الجديد بدلاً من الـ ID -->
                                        {{ \App\Models\Company::find($log->new_value)?->name ?? $log->new_value }}
                                    @elseif ($log->field === 'hotel_id')
                                        <!-- إذا كان الحقل هو الفندق، جلب اسم الفندق الجديد بدلاً من الـ ID -->
                                        {{ \App\Models\Hotel::find($log->new_value)?->name ?? $log->new_value }}
                                    @elseif ($log->field === 'agent_id')
                                        <!-- إذا كان الحقل هو جهة الحجز، جلب اسم جهة الحجز الجديد بدلاً من الـ ID -->
                                        {{ \App\Models\Agent::find($log->new_value)?->name ?? $log->new_value }}
                                    @elseif (in_array($log->field, ['check_in', 'check_out']))
                                        <!-- إذا كان الحقل هو تاريخ، تنسيق التاريخ الجديد لعرضه بشكل مناسب -->
                                        {{ $log->new_value ? \Carbon\Carbon::parse($log->new_value)->format('d/m/Y') : 'غير محدد' }}
                                    @else
                                        <!-- عرض القيمة الجديدة كما هي إذا لم تكن من الحقول الخاصة -->
                                        {{ $log->new_value !== null && $log->new_value !== '' ? $log->new_value : 'غير محدد' }}
                                    @endif
                                </td>

                                <!-- عرض تاريخ التعديل -->
                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif
        {{-- <ul id="editLog"></ul>
    <pre>{{ print_r($editLogs->toArray()) }}</pre> --}}
    </div>



    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }

        .table {
            border-color: #444;
        }

        .table th,
        .table td {
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
            z-index: 9999;
            /* لضمان ظهوره فوق جميع العناصر */
            padding: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            width: 90%;
            /* عرض التنبيه */
            max-width: 500px;
            /* الحد الأقصى للعرض */
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



    <script src="{{ asset('js/preventClick.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyButton = document.getElementById('copyBookingDetails');
            if (copyButton) {
                copyButton.addEventListener('click', function() {
                    try {
                        const bookingDetails =
                            `📋 *تفاصيل الحجز للعميل:* {{ $booking->client_name }}\n\n` +
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
                                else if (title.includes('تاريخ الدخول') || title.includes(
                                        'تاريخ الخروج')) emoji = '📅';
                                else if (title.includes('عدد الليالي')) emoji = '🌙';
                                else if (title.includes('الأيام المتبقية حتى الخروج')) emoji = '⏳';
                                else if (title.includes('السعر من الفندق')) emoji = '💵';
                                else if (title.includes('المستحق للفندق')) emoji =
                                    '💶'; // إضافة إيموجي للمستحق للفندق
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

            // عند الضغط على زر "calculate-total"
            document.getElementById('calculate-total').addEventListener('click', function() {
                let totalDueFromCompany = 0;
                let totalDueToHotel = 0;
                let profitPerNight = 0;
                let profitSoFar = 0;
                let totalProfit = 0;

                // حساب عدد الليالي التي قضاها العميل حتى الآن
                let checkInDate = new Date("{{ $booking->check_in }}");
                let checkOutDate = new Date("{{ $booking->check_out }}");
                let today = new Date();

                let nightsStayed = Math.min(
                    Math.max(0, Math.ceil((today - checkInDate) / (1000 * 60 * 60 * 24))),
                    {{ $booking->days }}
                );

                // حساب عدد الليالي الإجمالية
                let totalNights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

                // حساب الإجمالي من الشركة والفندق
                totalDueFromCompany = nightsStayed * {{ $booking->rooms }} * {{ $booking->sale_price }};
                totalDueToHotel = nightsStayed * {{ $booking->rooms }} * {{ $booking->cost_price }};

                // تحديث صف "المستحق للفندق" بالقيمة المحسوبة
                document.getElementById('hotel-due-value').innerText = totalDueToHotel + ' ريال';

                // حساب المكسب
                profitPerNight = ({{ $booking->sale_price }} - {{ $booking->cost_price }}) *
                    {{ $booking->rooms }};
                profitSoFar = profitPerNight * nightsStayed;
                totalProfit = profitPerNight * totalNights;

                // المبالغ المدفوعة
                let amountPaidByCompany = {{ $booking->amount_paid_by_company }};
                let amountPaidToHotel = {{ $booking->amount_paid_to_hotel }};

                // حساب المبالغ المتبقية
                let remainingFromCompany = totalDueFromCompany - amountPaidByCompany;
                let remainingToHotel = totalDueToHotel - amountPaidToHotel;

                // بناء رسالة التنبيه بالتفاصيل بما في ذلك المستحق للفندق
                let alertMessage = `💲 الإجمالي حتى الآن: 💲
            
ما لك من الشركة: ${nightsStayed} ليلة * {{ $booking->rooms }} غرفة * {{ $booking->sale_price }} سعر الليلة = ${totalDueFromCompany} ريال
ما عليك للفندق: ${nightsStayed} ليلة * {{ $booking->rooms }} غرفة * {{ $booking->cost_price }} سعر الفندق = ${totalDueToHotel} ريال

💰 المكسب:
- المكسب لكل ليلة: ${profitPerNight} ريال
- المكسب حتى الآن: ${profitSoFar} ريال
- المكسب الإجمالي: ${totalProfit} ريال

💳 المبالغ المدفوعة:
- المدفوع من الشركة: ${amountPaidByCompany} ريال
- المدفوع للفندق: ${amountPaidToHotel} ريال

⚖️ المبالغ المتبقية:
- المتبقي من الشركة: ${remainingFromCompany} ريال
- المتبقي للفندق: ${remainingToHotel} ريال`;

                showAlert(alertMessage, 'info');
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
