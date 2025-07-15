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
                    <br> <br>
                    <a href="{{ route('bookings.voucher', $booking->id) }}" class="btn btn-warning btn-sm" target="_blank">
                        عرض الفاوتشر
                    </a>
                    @if (Auth::user()->role === 'Admin')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#registerPaymentModal">
                            💸 تسجيل دفعة
                        </button>
                    @endif
                    <button type="button" class="btn btn-info ms-2" data-bs-toggle="modal"
                        data-bs-target="#financialTrackingModal" onclick="loadFinancialTracking({{ $booking->id }})"
                        title="إدارة المتابعة المالية للحجز">
                        <i class="fas fa-chart-line me-1"></i>
                        حالة التحصيل والسداد
                    </button>
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
                    <td>{{ $booking->check_in->format('d/m/Y') }} <small class="d-block text-muted hijri-date"
                            data-date="{{ $booking->check_in->format('Y-m-d') }}"></small>
                    </td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>تاريخ الخروج <i class="fas fa-calendar-times text-danger"></i></td>
                    <td>{{ $booking->check_out->format('d/m/Y') }} <small class="d-block text-muted hijri-date"
                            data-date="{{ $booking->check_out->format('Y-m-d') }}"></small>
                    </td>
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
                    <td id="hotel-due-value">{{ $total_nights * $booking->rooms * $booking->cost_price }}
                        {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>12</td>
                    <td> المبلغ المدفوع للفندق <i class="fas fa-money-check-alt text-primary"></i></td>
                    <td>{{ $booking->amount_paid_to_hotel }}
                        {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>13</td>
                    <td> الباقي للفندق <i class="fas fa-money-check text-danger"></i></td>
                    <td>{{ $booking->amount_due_to_hotel - $booking->amount_paid_to_hotel }}
                        {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>14</td>
                    <td> سعر البيع للشركة <i class="fas fa-tag text-warning"></i> </td>
                    <td>{{ $booking->sale_price }} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}</td>
                </tr>
                <tr>
                    <td>15</td>
                    <td>المبلغ الكلي المستحق من الشركة <i class="fas fa-hand-holding-usd text-success"></i> </td>
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
                    <td>الباقي على الشركة <i class="fas fa-balance-scale text-danger"></i> </td>
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
                    <td>الملاحظات <i class="fas fa-sticky-note text-warning"></i></td>
                    <td class="notes-cell">
                        @php
                            $notes = $booking->notes ?? '';

                            if (!empty($notes)) {
                                // نمط للتعرف على الروابط
                                $pattern =
                                    '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/i';

                                // تقسيم النص عند الروابط للتعامل معها بشكل منفصل
                                $parts = preg_split($pattern, $notes, -1, PREG_SPLIT_DELIM_CAPTURE);

                                $formatted = '';
                                $wordCount = 0;

                                foreach ($parts as $index => $part) {
                                    // تحديد ما إذا كان هذا الجزء رابطاً
                                    if ($index % 2 === 1 || preg_match($pattern, $part)) {
                                        // هذا رابط - نحوله إلى زر
                                        $url = $part;
                                        if (!str_starts_with($url, 'http')) {
                                            $url = 'https://' . $url;
                                        }

                                        // تحديد نوع الزر بناء على الرابط
                                        $btnClass = 'btn-primary';
                                        $btnText = 'فتح الرابط';
                                        $btnIcon = 'link';

                                        if (strpos($url, 'drive.google.com') !== false) {
                                            $btnClass = 'btn-success';
                                            $btnText = 'فتح الملف';
                                            $btnIcon = 'file';
                                        }

                                        // إضافة الزر بتنسيق Bootstrap
                                        $formatted .=
                                            ' <a href="' .
                                            e($url) .
                                            '" target="_blank" class="btn btn-sm ' .
                                            $btnClass .
                                            '" style="white-space: nowrap; margin: 2px;"><i class="fas fa-' .
                                            $btnIcon .
                                            '"></i> ' .
                                            $btnText .
                                            '</a> ';

                                        // إضافة سطر جديد بعد الزر
                                        $formatted .= '<br>';
                                        $wordCount = 0;
                                    } else {
                                        // هذا نص عادي - نقسمه إلى كلمات
                                        $words = preg_split('/\s+/', $part);
                                        foreach ($words as $word) {
                                            if (!empty($word)) {
                                                $formatted .= $word . ' ';
                                                $wordCount++;

                                                // إضافة سطر جديد بعد كل 7 كلمات
                                                if ($wordCount >= 7) {
                                                    $formatted .= '<br>';
                                                    $wordCount = 0;
                                                }
                                            }
                                        }
                                    }
                                }

                                // إزالة أي <br> زائد في النهاية
                                $formatted = rtrim($formatted, '<br>');
                                $notes = $formatted;
                            }
                        @endphp
                        {!! $notes !!}
                    </td>
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
                document.getElementById('hotel-due-value').innerText = totalDueToHotel +
                    ' {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}';

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

ما لك من الشركة: ${nightsStayed} ليلة * {{ $booking->rooms }} غرفة * {{ $booking->sale_price }} سعر الليلة = ${totalDueFromCompany} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}  
ما عليك للفندق: ${nightsStayed} ليلة * {{ $booking->rooms }} غرفة * {{ $booking->cost_price }} سعر الفندق = ${totalDueToHotel} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}  

💰 المكسب:
- المكسب لكل ليلة: ${profitPerNight} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
- المكسب حتى الآن: ${profitSoFar} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
- المكسب الإجمالي: ${totalProfit} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}

💳 المبالغ المدفوعة:
- المدفوع من الشركة: ${amountPaidByCompany} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
- المدفوع للفندق: ${amountPaidToHotel} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}

⚖️ المبالغ المتبقية:
- المتبقي من الشركة: ${remainingFromCompany} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}
- المتبقي للفندق: ${remainingToHotel} {{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}`;

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // المتغيرات الثابتة
            let originalAmountDue = {{ $booking->amount_due_from_company }};
            let originalAmountPaid = {{ $booking->amount_paid_by_company }};
            let originalRemaining = originalAmountDue - originalAmountPaid;
            const currency = "{{ $booking->currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي' }}";

            // نموذج الدفعة
            const paymentForm = document.getElementById('paymentForm');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    // قراءة المبلغ المدخل
                    const paymentAmount = parseFloat(document.getElementById('payment-amount').value);
                    if (isNaN(paymentAmount) || paymentAmount <= 0) {
                        showAlert('يرجى إدخال مبلغ صحيح', 'danger');
                        e.preventDefault();
                        return;
                    }

                    // التأكد من أن العملة متطابقة
                    const paymentCurrency = document.getElementById('payment-currency').value;
                    if (paymentCurrency !== "{{ $booking->currency }}") {
                        showAlert('يجب أن تكون عملة الدفع متطابقة مع عملة الحجز: {{ $booking->currency }}',
                            'warning');
                        e.preventDefault();
                        return;
                    }

                    // حساب القيم الجديدة (للعرض فقط - البيانات الفعلية ستأتي من السيرفر)
                    const newAmountPaid = originalAmountPaid + paymentAmount;
                    const newRemaining = originalAmountDue - newAmountPaid;

                    // تحديث المعلومات المعروضة مسبقاً (قبل الاستجابة من السيرفر)
                    updateDisplayedValues(newAmountPaid, newRemaining);

                    // إخفاء المودال
                    const modal = bootstrap.Modal.getInstance(document.getElementById(
                        'registerPaymentModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // عرض رسالة للمستخدم أننا نعالج الطلب
                    showAlert('جاري معالجة الدفعة...', 'info');

                    // تحديث المتغيرات المحلية للاستخدام في العمليات التالية
                    originalAmountPaid = newAmountPaid;
                    originalRemaining = newRemaining;
                });
            }

            // دالة تحديث القيم المعروضة
            function updateDisplayedValues(newPaid, newRemaining) {
                // تحديث المبلغ المدفوع من الشركة (الصف 16)
                const paidCell = document.querySelector('tr:nth-child(16) td:last-child');
                if (paidCell) {
                    paidCell.innerHTML = `
                    <span class="new-value">${number_format(newPaid, 2)} ${currency}</span>
                    <span class="original-value">(${number_format(originalAmountPaid, 2)})</span>
                `;
                }

                // تحديث المبلغ المتبقي من الشركة (الصف 17)
                const remainingCell = document.querySelector('tr:nth-child(17) td:last-child');
                if (remainingCell) {
                    const remainingClass = newRemaining <= 0 ? 'text-success fw-bold' : 'text-warning';
                    remainingCell.innerHTML = `
                    <span class="new-value ${remainingClass}">${number_format(newRemaining, 2)} ${currency}</span>
                    <span class="original-value">(${number_format(originalRemaining, 2)})</span>
                `;
                }

                // تحديث المبلغ المستحق من الشركة (الصف 15) - إظهار القيمة الأصلية مشطوبة مع الجديدة
                const dueCell = document.querySelector('tr:nth-child(15) td:last-child');
                if (dueCell) {
                    const currentDue = newPaid + newRemaining; // المبلغ المستحق الجديد بناءً على الدفعات
                    dueCell.innerHTML = `
                    <span class="new-value text-primary fw-bold">${number_format(originalAmountDue, 2)} ${currency}</span>
                    <small class="text-muted d-block">المدفوع: ${number_format(newPaid, 2)} + المتبقي: ${number_format(newRemaining, 2)}</small>
                `;
                }

                // إضافة أو تحديث CSS للتنسيق
                if (!document.getElementById('payment-styles')) {
                    const style = document.createElement('style');
                    style.id = 'payment-styles';
                    style.textContent = `
                    .original-value {
                        text-decoration: line-through;
                        color: #777;
                        font-size: 0.85em;
                        margin-right: 8px;
                        opacity: 0.7;
                    }
                    .new-value {
                        font-weight: bold;
                        color: #0d6efd;
                    }
                    .new-value.text-success {
                        color: #198754 !important;
                    }
                    .new-value.text-warning {
                        color: #ffc107 !important;
                    }
                    .payment-updated {
                        background-color: #f8f9fa;
                        border-left: 4px solid #0d6efd;
                        animation: highlightPayment 2s ease-in-out;
                    }
                    @keyframes highlightPayment {
                        0% { background-color: #e3f2fd; }
                        50% { background-color: #bbdefb; }
                        100% { background-color: #f8f9fa; }
                    }
                `;
                    document.head.appendChild(style);
                }

                // إضافة تأثير بصري للصفوف المحدثة
                setTimeout(() => {
                    if (paidCell) paidCell.closest('tr').classList.add('payment-updated');
                    if (remainingCell) remainingCell.closest('tr').classList.add('payment-updated');
                    if (dueCell) dueCell.closest('tr').classList.add('payment-updated');
                }, 500);
            }

            // دالة تنسيق الأرقام
            function number_format(number, decimals = 2) {
                return parseFloat(number).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            // دالة عرض الإشعارات
            function showAlert(message, type) {
                // إزالة أي تنبيهات موجودة
                const existingAlerts = document.querySelectorAll('.custom-alert');
                existingAlerts.forEach(alert => alert.remove());

                const alertBox = document.createElement('div');
                alertBox.className = `alert alert-${type} custom-alert`;
                alertBox.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                    <span>${message}</span>
                </div>
            `;
                alertBox.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                width: 90%;
                max-width: 500px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border: none;
                border-radius: 8px;
            `;

                document.body.appendChild(alertBox);

                setTimeout(() => {
                    alertBox.remove();
                }, 5000);
            }

            // التعامل مع رسائل النجاح أو الخطأ من الخادم بعد التحميل
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('payment_success')) {
                showAlert('تم تسجيل الدفعة بنجاح! تم تحديث المبالغ.', 'success');

                // تحديث الصفحة بعد 2 ثانية لإظهار البيانات المحدثة من قاعدة البيانات
                setTimeout(() => {
                    window.location.href = window.location.pathname;
                }, 2000);
            } else if (urlParams.has('payment_error')) {
                showAlert('حدث خطأ أثناء تسجيل الدفعة. يرجى المحاولة مرة أخرى.', 'danger');
            }

            // إضافة مستمع لإعادة تعيين النموذج عند إغلاق المودال
            const paymentModal = document.getElementById('registerPaymentModal');
            if (paymentModal) {
                paymentModal.addEventListener('hidden.bs.modal', function() {
                    // إعادة تعيين النموذج
                    const form = document.getElementById('paymentForm');
                    if (form) {
                        form.reset();
                    }
                });
            }
        });
    </script>
    <div class="modal fade" id="registerPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form id="paymentForm" action="{{ route('bookings.record-payment', $booking->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="company_id" value="{{ $booking->company->id ?? 0 }}">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-credit-card me-2"></i>
                            تسجيل دفعة - {{ $booking->company->name ?? 'غير محدد' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- حقل إدخال المبلغ والعملة -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>
                                المبلغ المدفوع والعملة
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="number" step="0.01"
                                    class="form-control form-control-lg text-center fw-bold" id="payment-amount"
                                    name="amount" placeholder="أدخل المبلغ" required>
                                <select class="form-select form-select-lg fw-bold text-center" name="currency"
                                    id="payment-currency" style="max-width: 140px;">
                                    <option value="SAR" {{ $booking->currency === 'SAR' ? 'selected' : '' }}>
                                        ريال سعودي
                                    </option>
                                    <option value="KWD" {{ $booking->currency === 'KWD' ? 'selected' : '' }}>
                                        دينار كويتي
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- حقل الملاحظات -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-sticky-note text-warning me-2"></i>
                                ملاحظات (اختياري)
                            </label>
                            <textarea class="form-control" id="payment-notes" name="notes" rows="3"
                                placeholder="أضف أي ملاحظات خاصة بالدفعة..."></textarea>
                        </div>



                        <!-- ملخص المبالغ -->
                        <div class="card border-primary shadow-sm">
                            <div class="card-header bg-primary text-white py-2">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    ملخص المبالغ الحالية
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span class="text-muted">
                                                <i class="fas fa-dollar-sign text-primary me-1"></i>
                                                المبلغ الأصلي:
                                            </span>
                                            <span class="fw-bold text-primary">
                                                {{ number_format($booking->amount_due_from_company, 2) }}
                                                {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                            </span>
                                        </div>
                                        <hr class="my-1">
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                المدفوع سابقاً:(قد يعدّله الادمن)
                                            </span>
                                            <span class="fw-bold text-success">
                                                {{ number_format($booking->amount_paid_by_company, 2) }}
                                                {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                            </span>
                                        </div>
                                        <hr class="my-1">
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span class="text-muted">
                                                <i class="fas fa-clock text-warning me-1"></i>
                                                المتبقي:
                                            </span>
                                            <span class="fw-bold text-warning">
                                                {{ number_format($booking->amount_due_from_company - $booking->amount_paid_by_company, 2) }}
                                                {{ $booking->currency === 'SAR' ? 'ريال' : 'دينار' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            إغلاق
                        </button>
                        <button type="submit" class="btn btn-primary px-4" id="submit-payment">
                            <i class="fas fa-save me-1"></i>
                            تسجيل الدفعة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{--  --}}
    <!-- ===== Modal المتابعة المالية للحجز ===== -->
    <div class="modal fade" id="financialTrackingModal" tabindex="-1" aria-labelledby="financialTrackingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!-- استخدام modal-xl للحصول على مساحة أكبر -->
            <div class="modal-content">
                <!-- Header الـ Modal -->
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="financialTrackingModalLabel">
                        <i class="fas fa-chart-line me-2"></i>
                        متابعة المعاملات المالية للحجز
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="إغلاق"></button>
                </div>

                <!-- Body الـ Modal -->
                <div class="modal-body">
                    <!-- شاشة التحميل -->
                    <div id="financialTrackingLoader" class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <p class="mt-3 text-muted">جاري تحميل بيانات المتابعة المالية...</p>
                    </div>

                    <!-- محتوى المتابعة المالية -->
                    <div id="financialTrackingContent" style="display: none;">
                        <!-- معلومات الحجز الأساسية -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            معلومات الحجز
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>رقم الفاوتشر:</strong>
                                                <span id="bookingVoucherNumber">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>اسم العميل:</strong>
                                                <span id="bookingClientName">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>تاريخ الدخول:</strong>
                                                <span id="bookingCheckIn">-</span>
                                                <div class="text-muted small" id="bookingCheckInHijri"></div>

                                            </div>
                                            <div class="col-md-3">
                                                <strong>تاريخ الخروج:</strong>
                                                <span id="bookingCheckOut">-</span>
                                                <div class="text-muted small" id="bookingCheckOutHijri"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- نموذج المتابعة المالية -->
                        <form id="financialTrackingForm">
                            @csrf
                            <div class="row">
                                <!-- النصف الأيمن: جهة الحجز -->
                                <div class="col-md-6">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-building me-2"></i>
                                                جهة الحجز: <span id="agentName">-</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- المبلغ المستحق -->
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-primary">
                                                    <i class="fas fa-dollar-sign me-1"></i>
                                                    المبلغ المستحق
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white"
                                                        id="agentCurrency">USD</span>
                                                    <input type="text" class="form-control bg-light"
                                                        id="agentAmountDue" readonly>
                                                </div>
                                            </div>

                                            <!-- حالة السداد -->
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-primary">
                                                    <i class="fas fa-clipboard-check me-1"></i>
                                                    حالة السداد
                                                </label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="agent_payment_status" id="agentNotPaid" value="not_paid"
                                                        checked>
                                                    <label class="form-check-label text-danger" for="agentNotPaid">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        لم يتم السداد
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="agent_payment_status" id="agentPartiallyPaid"
                                                        value="partially_paid">
                                                    <label class="form-check-label text-warning" for="agentPartiallyPaid">
                                                        <i class="fas fa-clock me-1"></i>
                                                        تم التحصيل جزئياً
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="agent_payment_status" id="agentFullyPaid"
                                                        value="fully_paid">
                                                    <label class="form-check-label text-success" for="agentFullyPaid">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        تم التحصيل بالكامل
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- المبلغ المدفوع -->
                                            <div class="mb-3" id="agentPaymentAmountGroup" style="display: none;">
                                                <label for="agentPaymentAmount" class="form-label fw-bold text-primary">
                                                    <i class="fas fa-money-bill-wave me-1"></i>
                                                    المبلغ المدفوع
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white"
                                                        id="agentPaymentCurrency">USD</span>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control" id="agentPaymentAmount"
                                                        name="agent_payment_amount" placeholder="0.00">
                                                </div>
                                                <div class="form-text">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    النسبة المدفوعة: <span id="agentPaymentPercentage"
                                                        class="fw-bold">0%</span>
                                                </div>
                                            </div>

                                            <!-- ملاحظات جهة الحجز -->
                                            <div class="mb-3">
                                                <label for="agentPaymentNotes" class="form-label fw-bold text-primary">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    ملاحظات
                                                </label>
                                                <textarea class="form-control" id="agentPaymentNotes" name="agent_payment_notes" rows="3"
                                                    placeholder="أضف ملاحظاتك حول السداد من جهة الحجز..."></textarea>
                                            </div>

                                            <!-- مؤشر بصري لحالة السداد -->
                                            <div class="progress mb-2">
                                                <div class="progress-bar" id="agentProgressBar" role="progressbar"
                                                    style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-chart-bar me-1"></i>
                                                مؤشر تقدم السداد لجهة الحجز
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- النصف الأيسر: الشركة -->
                                <div class="col-md-6">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-briefcase me-2"></i>
                                                الشركة: <span id="companyName">-</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- المبلغ المستحق -->
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-success">
                                                    <i class="fas fa-dollar-sign me-1"></i>
                                                    المبلغ المستحق
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white"
                                                        id="companyCurrency">USD</span>
                                                    <input type="text" class="form-control bg-light"
                                                        id="companyAmountDue" readonly>
                                                </div>
                                            </div>

                                            <!-- حالة السداد -->
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-success">
                                                    <i class="fas fa-clipboard-check me-1"></i>
                                                    حالة السداد
                                                </label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="company_payment_status" id="companyNotPaid"
                                                        value="not_paid" checked>
                                                    <label class="form-check-label text-danger" for="companyNotPaid">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        لم يتم السداد
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="company_payment_status" id="companyPartiallyPaid"
                                                        value="partially_paid">
                                                    <label class="form-check-label text-warning"
                                                        for="companyPartiallyPaid">
                                                        <i class="fas fa-clock me-1"></i>
                                                        تم التحصيل جزئياً
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="company_payment_status" id="companyFullyPaid"
                                                        value="fully_paid">
                                                    <label class="form-check-label text-success" for="companyFullyPaid">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        تم التحصيل بالكامل
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- المبلغ المدفوع -->
                                            <div class="mb-3" id="companyPaymentAmountGroup" style="display: none;">
                                                <label for="companyPaymentAmount" class="form-label fw-bold text-success">
                                                    <i class="fas fa-money-bill-wave me-1"></i>
                                                    المبلغ المدفوع
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white"
                                                        id="companyPaymentCurrency">USD</span>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control" id="companyPaymentAmount"
                                                        name="company_payment_amount" placeholder="0.00">
                                                </div>
                                                <div class="form-text">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    النسبة المدفوعة: <span id="companyPaymentPercentage"
                                                        class="fw-bold">0%</span>
                                                </div>
                                            </div>

                                            <!-- ملاحظات الشركة -->
                                            <div class="mb-3">
                                                <label for="companyPaymentNotes" class="form-label fw-bold text-success">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    ملاحظات
                                                </label>
                                                <textarea class="form-control" id="companyPaymentNotes" name="company_payment_notes" rows="3"
                                                    placeholder="أضف ملاحظاتك حول السداد من الشركة..."></textarea>
                                            </div>

                                            <!-- مؤشر بصري لحالة السداد -->
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-success" id="companyProgressBar"
                                                    role="progressbar" style="width: 0%" aria-valuenow="0"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-chart-bar me-1"></i>
                                                مؤشر تقدم السداد للشركة
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- إعدادات إضافية -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-cogs me-2"></i>
                                                إعدادات المتابعة
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- تاريخ الاستحقاق -->
                                                <div class="col-md-4">
                                                    <label for="paymentDeadline" class="form-label fw-bold">
                                                        <i class="fas fa-calendar-times me-1"></i>
                                                        تاريخ الاستحقاق
                                                    </label>
                                                    <input type="date" class="form-control" id="paymentDeadline"
                                                        name="payment_deadline">
                                                    <div class="form-text">تاريخ الاستحقاق المتوقع للسداد</div>
                                                </div>

                                                <!-- تاريخ المتابعة التالي -->
                                                <div class="col-md-4">
                                                    <label for="followUpDate" class="form-label fw-bold">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        تاريخ المتابعة التالي
                                                    </label>
                                                    <input type="date" class="form-control" id="followUpDate"
                                                        name="follow_up_date">
                                                    <div class="form-text">متى يجب متابعة هذا الحجز مرة أخرى</div>
                                                </div>

                                                <!-- مستوى الأولوية -->
                                                <div class="col-md-4">
                                                    <label for="priorityLevel" class="form-label fw-bold">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        مستوى الأولوية
                                                    </label>
                                                    <select class="form-select" id="priorityLevel" name="priority_level">
                                                        <option value="low" class="text-muted">
                                                            <i class="fas fa-arrow-down"></i> منخفضة
                                                        </option>
                                                        <option value="medium" selected class="text-primary">
                                                            <i class="fas fa-minus"></i> متوسطة
                                                        </option>
                                                        <option value="high" class="text-danger">
                                                            <i class="fas fa-arrow-up"></i> عالية
                                                        </option>
                                                    </select>
                                                    <div class="form-text">مستوى أولوية متابعة هذا الحجز</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ملخص الحالة الحالية -->
                            <div class="row mt-4" id="currentStatusSummary" style="display: none;">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-clipboard-list me-2"></i>
                                                ملخص الحالة الحالية
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-building text-primary me-2"></i>
                                                        <strong>جهة الحجز:</strong>
                                                        <span id="summaryAgentStatus" class="ms-2 badge">-</span>
                                                    </div>
                                                    <div class="text-muted small" id="summaryAgentDetails">-</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-briefcase text-success me-2"></i>
                                                        <strong>الشركة:</strong>
                                                        <span id="summaryCompanyStatus" class="ms-2 badge">-</span>
                                                    </div>
                                                    <div class="text-muted small" id="summaryCompanyDetails">-</div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user me-1"></i>
                                                        آخر تحديث بواسطة: <span id="lastUpdatedBy">-</span>
                                                    </small>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        تاريخ آخر تحديث: <span id="lastUpdatedDate">-</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- رسائل الخطأ -->
                    <div id="financialTrackingError" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="financialTrackingErrorMessage">حدث خطأ في تحميل البيانات</span>
                    </div>
                </div>

                <!-- Footer الـ Modal -->
                <div class="modal-footer bg-light">
                    <div class="d-flex justify-content-between w-100">
                        <!-- معلومات إضافية -->
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            جميع التغييرات يتم حفظها تلقائياً
                        </div>

                        <!-- أزرار الحفظ والإلغاء -->
                        <div>
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>
                                إغلاق
                            </button>
                            <button type="button" class="btn btn-success" id="saveFinancialTracking">
                                <i class="fas fa-save me-1"></i>
                                حفظ المتابعة المالية
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // ===== متغيرات عامة للمتابعة المالية =====
        let currentBookingId = null;
        let currentTrackingData = null;
        let isLoadingFinancialData = false;

        /**
         * تحميل بيانات المتابعة المالية للحجز
         * 
         * @param {number} bookingId معرف الحجز
         */
        function loadFinancialTracking(bookingId) {
            console.log('🔄 بدء تحميل المتابعة المالية للحجز:', bookingId);

            currentBookingId = bookingId;

            // إظهار شاشة التحميل
            showFinancialTrackingLoader();

            // مسح الرسائل السابقة
            hideFinancialTrackingError();

            // إرسال طلب AJAX لتحميل البيانات
            isLoadingFinancialData = true;

            fetch(`/bookings/${bookingId}/financial-tracking`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('✅ تم تحميل بيانات المتابعة المالية بنجاح:', data);

                    if (data.success) {
                        currentTrackingData = data;
                        populateFinancialTrackingForm(data);
                        showFinancialTrackingContent();
                    } else {
                        throw new Error(data.error || 'فشل في تحميل البيانات');
                    }
                })
                .catch(error => {
                    console.error('❌ خطأ في تحميل المتابعة المالية:', error);
                    showFinancialTrackingError(error.message || 'حدث خطأ في تحميل البيانات');
                })
                .finally(() => {
                    isLoadingFinancialData = false;
                    hideFinancialTrackingLoader();
                });
        }

        /**
         * ملء نموذج المتابعة المالية بالبيانات المحملة
         * 
         * @param {object} data البيانات المحملة من الخادم
         */
        function populateFinancialTrackingForm(data) {
            console.log('🔄 ملء نموذج المتابعة المالية بالبيانات:', data);

            try {
                // ===== ملء معلومات الحجز الأساسية =====
                document.getElementById('bookingVoucherNumber').textContent = data.booking.id || '-';
                document.getElementById('bookingClientName').textContent = data.booking.client_name || '-';
                // تعبئة تاريخ الدخول بالتاريخ الميلادي
                // document.getElementById('bookingCheckIn').textContent = formatDate(data.booking.check_in);
                // تعبئة تاريخ الدخول بالتاريخ الميلادي والهجري
                const checkInDate = new Date(data.booking.check_in);

                // عرض التاريخ الميلادي بصيغة dd/mm/yyyy بغض النظر عن إعدادات النظام
                document.getElementById('bookingCheckIn').textContent = checkInDate.toLocaleDateString('en-GB', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
                // تعبئة تاريخ الدخول بالتاريخ الهجري
                document.getElementById('bookingCheckInHijri').textContent = formatHijriDate(new Date(data.booking
                    .check_in));

                // تعبئة تاريخ الخروج بالتاريخ الميلادي والهجري
                const checkOutDate = new Date(data.booking.check_out);

                // عرض التاريخ الميلادي بصيغة dd/mm/yyyy بغض النظر عن إعدادات النظام
                document.getElementById('bookingCheckOut').textContent = checkOutDate.toLocaleDateString('en-GB', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });


                // تعبئة تاريخ الخروج بالتاريخ الهجري
                document.getElementById('bookingCheckOutHijri').textContent = formatHijriDate(new Date(data.booking
                    .check_out));

                // ===== ملء بيانات جهة الحجز =====
                document.getElementById('agentName').textContent = data.booking.agent.name;
                // id : hotel-due-value
                document.getElementById('agentAmountDue').value = formatNumber(data.booking.agent.amount_due);
                document.getElementById('agentCurrency').textContent = data.booking.currency;
                document.getElementById('agentPaymentCurrency').textContent = data.booking.currency;

                // تحديد حالة السداد لجهة الحجز
                const agentStatus = data.tracking.agent_payment_status;
                document.querySelector(`input[name="agent_payment_status"][value="${agentStatus}"]`).checked = true;

                // ملء المبلغ المدفوع والملاحظات لجهة الحجز
                document.getElementById('agentPaymentAmount').value = data.tracking.agent_payment_amount || '';
                document.getElementById('agentPaymentNotes').value = data.tracking.agent_payment_notes || '';

                // ===== ملء بيانات الشركة =====
                document.getElementById('companyName').textContent = data.booking.company.name;
                document.getElementById('companyAmountDue').value = formatNumber(data.booking.company.amount_due);
                document.getElementById('companyCurrency').textContent = data.booking.currency;
                document.getElementById('companyPaymentCurrency').textContent = data.booking.currency;

                // تحديد حالة السداد للشركة
                const companyStatus = data.tracking.company_payment_status;
                document.querySelector(`input[name="company_payment_status"][value="${companyStatus}"]`).checked = true;

                // ملء المبلغ المدفوع والملاحظات للشركة
                document.getElementById('companyPaymentAmount').value = data.tracking.company_payment_amount || '';
                document.getElementById('companyPaymentNotes').value = data.tracking.company_payment_notes || '';

                // ===== ملء الإعدادات الإضافية =====
                document.getElementById('paymentDeadline').value = data.tracking.payment_deadline || '';
                document.getElementById('followUpDate').value = data.tracking.follow_up_date || '';
                document.getElementById('priorityLevel').value = data.tracking.priority_level || 'medium';

                // ===== ملء ملخص الحالة الحالية =====
                if (data.tracking.id) {
                    populateStatusSummary(data);
                    document.getElementById('currentStatusSummary').style.display = 'block';
                }

                // ===== تحديث العناصر التفاعلية =====
                updatePaymentAmountVisibility();
                updateProgressBars();
                updateStatusLabels();

                console.log('✅ تم ملء النموذج بنجاح');

            } catch (error) {
                console.error('❌ خطأ في ملء النموذج:', error);
                showFinancialTrackingError('حدث خطأ في عرض البيانات');
            }
        }

        /**
         * ملء ملخص الحالة الحالية
         * 
         * @param {object} data البيانات المحملة
         */
        function populateStatusSummary(data) {
            // حالة جهة الحجز
            const agentStrategy = data.strategies.agent;
            document.getElementById('summaryAgentStatus').className = `ms-2 badge bg-${agentStrategy.bootstrap_class}`;
            document.getElementById('summaryAgentStatus').textContent = agentStrategy.label;
            document.getElementById('summaryAgentDetails').textContent =
                `${formatNumber(data.tracking.agent_payment_amount)} من ${formatNumber(data.booking.agent.amount_due)} ${data.booking.currency} (${data.calculations.agent_payment_percentage}%)`;

            // حالة الشركة
            const companyStrategy = data.strategies.company;
            document.getElementById('summaryCompanyStatus').className = `ms-2 badge bg-${companyStrategy.bootstrap_class}`;
            document.getElementById('summaryCompanyStatus').textContent = companyStrategy.label;
            document.getElementById('summaryCompanyDetails').textContent =
                `${formatNumber(data.tracking.company_payment_amount)} من ${formatNumber(data.booking.company.amount_due)} ${data.booking.currency} (${data.calculations.company_payment_percentage}%)`;

            // معلومات آخر تحديث
            document.getElementById('lastUpdatedBy').textContent = data.tracking.last_updated_by || 'غير معروف';
            document.getElementById('lastUpdatedDate').textContent = formatDateTime(data.tracking.updated_at);
        }

        function validateFinancialTrackingForm() {
            // جهة الحجز
            const agentStatus = document.querySelector('input[name="agent_payment_status"]:checked').value;
            const agentAmount = parseFloat(document.getElementById('agentPaymentAmount').value) || 0;
            const agentTotalDue = parseFloat(document.getElementById('agentAmountDue').value.replace(/,/g, '')) || 0;

            // الشركة
            const companyStatus = document.querySelector('input[name="company_payment_status"]:checked').value;
            const companyAmount = parseFloat(document.getElementById('companyPaymentAmount').value) || 0;
            const companyTotalDue = parseFloat(document.getElementById('companyAmountDue').value.replace(/,/g, '')) || 0;

            // التحقق من صحة بيانات جهة الحجز
            if (agentStatus === 'not_paid' && agentAmount > 0) {
                showFinancialTrackingError(
                    "حالة السداد لجهة الحجز 'لم يتم السداد' لكن المبلغ أكبر من صفر. المبلغ يجب أن يكون صفر.");
                return false;
            }

            if (agentStatus === 'fully_paid' && Math.abs(agentAmount - agentTotalDue) > 0.01) {
                showFinancialTrackingError(
                    `حالة السداد لجهة الحجز 'تم السداد بالكامل' لكن المبلغ (${agentAmount}) لا يساوي المستحق (${agentTotalDue}). سيتم تصحيح المبلغ تلقائياً.`
                );
                document.getElementById('agentPaymentAmount').value = agentTotalDue.toFixed(2);
                return false;
            }

            if (agentStatus === 'partially_paid' && (agentAmount <= 0 || agentAmount >= agentTotalDue)) {
                showFinancialTrackingError(
                    `حالة السداد لجهة الحجز 'سداد جزئي' لكن المبلغ غير صحيح (${agentAmount}). يجب أن يكون أكبر من صفر وأقل من ${agentTotalDue}.`
                );
                return false;
            }

            // التحقق من صحة بيانات الشركة
            if (companyStatus === 'not_paid' && companyAmount > 0) {
                showFinancialTrackingError(
                    "حالة السداد للشركة 'لم يتم السداد' لكن المبلغ أكبر من صفر. المبلغ يجب أن يكون صفر.");
                return false;
            }

            if (companyStatus === 'fully_paid' && Math.abs(companyAmount - companyTotalDue) > 0.01) {
                showFinancialTrackingError(
                    `حالة السداد للشركة 'تم السداد بالكامل' لكن المبلغ (${companyAmount}) لا يساوي المستحق (${companyTotalDue}). سيتم تصحيح المبلغ تلقائياً.`
                );
                document.getElementById('companyPaymentAmount').value = companyTotalDue.toFixed(2);
                return false;
            }

            if (companyStatus === 'partially_paid' && (companyAmount <= 0 || companyAmount >= companyTotalDue)) {
                showFinancialTrackingError(
                    `حالة السداد للشركة 'سداد جزئي' لكن المبلغ غير صحيح (${companyAmount}). يجب أن يكون أكبر من صفر وأقل من ${companyTotalDue}.`
                );
                return false;
            }

            // التحقق من التواريخ
            const paymentDeadline = document.getElementById('paymentDeadline').value;
            const followUpDate = document.getElementById('followUpDate').value;
            const today = new Date().toISOString().split('T')[0];

            if (paymentDeadline && paymentDeadline <= today) {
                showFinancialTrackingError("تاريخ الاستحقاق يجب أن يكون في المستقبل.");
                return false;
            }

            if (followUpDate && followUpDate <= today) {
                showFinancialTrackingError("تاريخ المتابعة يجب أن يكون في المستقبل.");
                return false;
            }

            return true;
        }

        /**
         * تحديث رؤية حقول المبلغ المدفوع حسب حالة السداد
         */
        function updatePaymentAmountVisibility() {
            console.log('🔄 تحديث رؤية حقول المبلغ المدفوع');

            // جهة الحجز
            const agentStatus = document.querySelector('input[name="agent_payment_status"]:checked').value;
            const agentAmountGroup = document.getElementById('agentPaymentAmountGroup');
            const agentAmountInput = document.getElementById('agentPaymentAmount');
            const totalAgentDue = parseFloat(document.getElementById('agentAmountDue').value.replace(/,/g, '')) || 0;

            if (agentStatus === 'not_paid') {
                agentAmountGroup.style.display = 'none';
                agentAmountInput.value = '0'; // تأكد من أن القيمة صفر عند "لم يتم السداد"
            } else {
                agentAmountGroup.style.display = 'block';
                if (agentStatus === 'fully_paid') {
                    agentAmountInput.value = totalAgentDue.toFixed(2); // تأكد من المساواة التامة للمبلغ المستحق
                } else if (agentStatus === 'partially_paid') {
                    // للسداد الجزئي: إذا كان المبلغ صفر أو يساوي المبلغ الكلي، اجعله نصف المبلغ كافتراضي
                    const currentAmount = parseFloat(agentAmountInput.value) || 0;
                    if (currentAmount <= 0 || currentAmount >= totalAgentDue) {
                        agentAmountInput.value = (totalAgentDue / 2).toFixed(2);
                    }
                }
            }

            // الشركة - نفس المنطق
            const companyStatus = document.querySelector('input[name="company_payment_status"]:checked').value;
            const companyAmountGroup = document.getElementById('companyPaymentAmountGroup');
            const companyAmountInput = document.getElementById('companyPaymentAmount');
            const totalCompanyDue = parseFloat(document.getElementById('companyAmountDue').value.replace(/,/g, '')) || 0;

            if (companyStatus === 'not_paid') {
                companyAmountGroup.style.display = 'none';
                companyAmountInput.value = '0'; // تأكد من أن القيمة صفر عند "لم يتم السداد"
            } else {
                companyAmountGroup.style.display = 'block';
                if (companyStatus === 'fully_paid') {
                    companyAmountInput.value = totalCompanyDue.toFixed(2); // تأكد من المساواة التامة للمبلغ المستحق
                } else if (companyStatus === 'partially_paid') {
                    // للسداد الجزئي: إذا كان المبلغ صفر أو يساوي المبلغ الكلي، اجعله نصف المبلغ كافتراضي
                    const currentAmount = parseFloat(companyAmountInput.value) || 0;
                    if (currentAmount <= 0 || currentAmount >= totalCompanyDue) {
                        companyAmountInput.value = (totalCompanyDue / 2).toFixed(2);
                    }
                }
            }

            // تحديث أشرطة التقدم
            updateProgressBars();
        }

        /**
         * تحديث أشرطة التقدم والنسب المئوية
         */
        function updateProgressBars() {
            console.log('📊 تحديث أشرطة التقدم');

            // شريط تقدم جهة الحجز
            const agentPaid = parseFloat(document.getElementById('agentPaymentAmount').value) || 0;
            const agentTotal = parseFloat(document.getElementById('agentAmountDue').value.replace(/,/g, '')) || 0;
            const agentPercentage = agentTotal > 0 ? Math.round((agentPaid / agentTotal) * 100) : 0;

            const agentProgressBar = document.getElementById('agentProgressBar');
            agentProgressBar.style.width = `${agentPercentage}%`;
            agentProgressBar.textContent = `${agentPercentage}%`;
            agentProgressBar.setAttribute('aria-valuenow', agentPercentage);

            // تغيير لون الشريط حسب النسبة
            agentProgressBar.className = 'progress-bar';
            if (agentPercentage === 100) {
                agentProgressBar.classList.add('bg-success');
            } else if (agentPercentage > 0) {
                agentProgressBar.classList.add('bg-warning');
            } else {
                agentProgressBar.classList.add('bg-danger');
            }

            document.getElementById('agentPaymentPercentage').textContent = `${agentPercentage}%`;

            // شريط تقدم الشركة
            const companyPaid = parseFloat(document.getElementById('companyPaymentAmount').value) || 0;
            const companyTotal = parseFloat(document.getElementById('companyAmountDue').value.replace(/,/g, '')) || 0;
            const companyPercentage = companyTotal > 0 ? Math.round((companyPaid / companyTotal) * 100) : 0;

            const companyProgressBar = document.getElementById('companyProgressBar');
            companyProgressBar.style.width = `${companyPercentage}%`;
            companyProgressBar.textContent = `${companyPercentage}%`;
            companyProgressBar.setAttribute('aria-valuenow', companyPercentage);

            // لون الشريط ثابت للشركة (أخضر)
            companyProgressBar.className = 'progress-bar bg-success';

            document.getElementById('companyPaymentPercentage').textContent = `${companyPercentage}%`;
        }

        /**
         * تحديث تسميات الحالة والألوان
         */
        function updateStatusLabels() {
            console.log('🎨 تحديث تسميات الحالة');

            // يمكن إضافة تحديثات إضافية للألوان والتسميات هنا
            // مثل تغيير لون البطاقات حسب الحالة
        }

        /**
         * حفظ بيانات المتابعة المالية
         */
        function saveFinancialTracking() {
            console.log('💾 بدء حفظ المتابعة المالية');

            if (!currentBookingId) {
                showFinancialTrackingError('معرف الحجز غير صحيح');
                return;
            }

            // التحقق من صحة البيانات قبل الإرسال
            if (!validateFinancialTrackingForm()) {
                return;
            }

            // جمع البيانات القديمة بشكل تفصيلي
            const oldData = {
                agent: {
                    status: document.querySelector('input[name="agent_payment_status"]:checked').value,
                    statusLabel: getStatusLabel(document.querySelector('input[name="agent_payment_status"]:checked')
                        .value),
                    amount: parseFloat(document.getElementById('agentPaymentAmount').value) || 0,
                    notes: document.getElementById('agentPaymentNotes').value,
                    amountFormatted: formatNumber(parseFloat(document.getElementById('agentPaymentAmount').value) || 0)
                },
                company: {
                    status: document.querySelector('input[name="company_payment_status"]:checked').value,
                    statusLabel: getStatusLabel(document.querySelector('input[name="company_payment_status"]:checked')
                        .value),
                    amount: parseFloat(document.getElementById('companyPaymentAmount').value) || 0,
                    notes: document.getElementById('companyPaymentNotes').value,
                    amountFormatted: formatNumber(parseFloat(document.getElementById('companyPaymentAmount').value) ||
                        0)
                },
                settings: {
                    priority: document.getElementById('priorityLevel').value,
                    priorityLabel: getPriorityLabel(document.getElementById('priorityLevel').value),
                    payment_deadline: document.getElementById('paymentDeadline').value,
                    follow_up_date: document.getElementById('followUpDate').value
                }
            };

            console.log('📊 البيانات القديمة المفصلة:', oldData);

            // تجهيز بيانات النموذج الجديدة
            const formElement = document.getElementById('financialTrackingForm');
            const formData = new FormData(formElement);

            // جمع البيانات الجديدة بشكل تفصيلي
            const newData = {
                agent: {
                    status: formData.get('agent_payment_status'),
                    statusLabel: getStatusLabel(formData.get('agent_payment_status')),
                    amount: parseFloat(formData.get('agent_payment_amount')) || 0,
                    notes: formData.get('agent_payment_notes') || '',
                    amountFormatted: formatNumber(parseFloat(formData.get('agent_payment_amount')) || 0)
                },
                company: {
                    status: formData.get('company_payment_status'),
                    statusLabel: getStatusLabel(formData.get('company_payment_status')),
                    amount: parseFloat(formData.get('company_payment_amount')) || 0,
                    notes: formData.get('company_payment_notes') || '',
                    amountFormatted: formatNumber(parseFloat(formData.get('company_payment_amount')) || 0)
                },
                settings: {
                    priority: formData.get('priority_level') || 'medium',
                    priorityLabel: getPriorityLabel(formData.get('priority_level') || 'medium'),
                    payment_deadline: formData.get('payment_deadline') || '',
                    follow_up_date: formData.get('follow_up_date') || ''
                }
            };

            console.log('📊 البيانات الجديدة المفصلة:', newData);

            // معلومات عامة للتسجيل
            const currency = document.getElementById('agentCurrency').textContent || '';
            const userName = "{{ Auth::user()->name }}";
            const now = new Date();
            const timestamp = now.toLocaleDateString('ar-SA') + ' ' + now.toLocaleTimeString('ar-SA');

            // ===== إنشاء سجل تفصيلي للتغييرات =====
            let changeLog = '';
            let hasChanges = false;

            // ----- مقارنة بيانات جهة الحجز -----
            let agentChanges = '';

            // مقارنة حالة السداد
            if (oldData.agent.status !== newData.agent.status) {
                agentChanges += `• تغيير حالة السداد: ${oldData.agent.statusLabel} ◀️ ${newData.agent.statusLabel}\n`;
                hasChanges = true;
            }

            // مقارنة المبلغ المدفوع
            if (Math.abs(oldData.agent.amount - newData.agent.amount) > 0.01) {
                const diff = newData.agent.amount - oldData.agent.amount;
                const diffSymbol = diff > 0 ? '▲' : '▼';
                const totalDue = parseFloat(document.getElementById('agentAmountDue').value.replace(/,/g, '')) || 0;
                const newPercentage = totalDue > 0 ? Math.round((newData.agent.amount / totalDue) * 100) : 0;

                agentChanges +=
                    `• تغيير المبلغ المدفوع: ${oldData.agent.amountFormatted} ${currency} ◀️ ${newData.agent.amountFormatted} ${currency}\n`;
                agentChanges +=
                    `  ${diffSymbol} ${diff > 0 ? 'زيادة' : 'نقص'} بمقدار ${Math.abs(diff).toFixed(2)} ${currency}\n`;
                agentChanges += `• النسبة المئوية الجديدة للسداد: ${newPercentage}%\n`;
                hasChanges = true;
            }

            // إضافة سجل تغييرات جهة الحجز إذا وجدت
            if (agentChanges) {
                changeLog += `\n--------------------------------------\n`;
                changeLog += `[${timestamp}] قام ${userName} بتحديث بيانات جهة الحجز:\n`;
                changeLog += agentChanges;
            }

            // ----- مقارنة بيانات الشركة -----
            let companyChanges = '';

            // مقارنة حالة السداد
            if (oldData.company.status !== newData.company.status) {
                companyChanges += `• تغيير حالة السداد: ${oldData.company.statusLabel} ◀️ ${newData.company.statusLabel}\n`;
                hasChanges = true;
            }

            // مقارنة المبلغ المدفوع
            if (Math.abs(oldData.company.amount - newData.company.amount) > 0.01) {
                const diff = newData.company.amount - oldData.company.amount;
                const diffSymbol = diff > 0 ? '▲' : '▼';
                const totalDue = parseFloat(document.getElementById('companyAmountDue').value.replace(/,/g, '')) || 0;
                const newPercentage = totalDue > 0 ? Math.round((newData.company.amount / totalDue) * 100) : 0;

                companyChanges +=
                    `• تغيير المبلغ المدفوع: ${oldData.company.amountFormatted} ${currency} ◀️ ${newData.company.amountFormatted} ${currency}\n`;
                companyChanges +=
                    `  ${diffSymbol} ${diff > 0 ? 'زيادة' : 'نقص'} بمقدار ${Math.abs(diff).toFixed(2)} ${currency}\n`;
                companyChanges += `• النسبة المئوية الجديدة للسداد: ${newPercentage}%\n`;
                hasChanges = true;
            }

            // إضافة سجل تغييرات الشركة إذا وجدت
            if (companyChanges) {
                changeLog += `\n--------------------------------------\n`;
                changeLog += `[${timestamp}] قام ${userName} بتحديث بيانات الشركة:\n`;
                changeLog += companyChanges;
            }

            // ----- مقارنة إعدادات المتابعة -----
            let settingsChanges = '';

            // مقارنة مستوى الأولوية
            if (oldData.settings.priority !== newData.settings.priority) {
                settingsChanges +=
                    `• تغيير مستوى الأولوية: ${oldData.settings.priorityLabel} ◀️ ${newData.settings.priorityLabel}\n`;
                hasChanges = true;
            }

            // مقارنة تاريخ الاستحقاق
            if (oldData.settings.payment_deadline !== newData.settings.payment_deadline) {
                const oldDate = oldData.settings.payment_deadline || 'غير محدد';
                const newDate = newData.settings.payment_deadline || 'غير محدد';
                settingsChanges += `• تغيير تاريخ الاستحقاق: ${oldDate} ◀️ ${newDate}\n`;
                hasChanges = true;
            }

            // مقارنة تاريخ المتابعة
            if (oldData.settings.follow_up_date !== newData.settings.follow_up_date) {
                const oldDate = oldData.settings.follow_up_date || 'غير محدد';
                const newDate = newData.settings.follow_up_date || 'غير محدد';
                settingsChanges += `• تغيير تاريخ المتابعة: ${oldDate} ◀️ ${newDate}\n`;
                hasChanges = true;
            }

            // إضافة سجل تغييرات الإعدادات إذا وجدت
            if (settingsChanges) {
                changeLog += `\n--------------------------------------\n`;
                changeLog += `[${timestamp}] قام ${userName} بتحديث إعدادات المتابعة:\n`;
                changeLog += settingsChanges;
            }

            console.log('📝 سجل التغييرات:', changeLog);

            // إضافة سجل التغييرات إلى الملاحظات فقط إذا كانت هناك تغييرات
            if (hasChanges && changeLog) {
                // إضافة السجل لملاحظات جهة الحجز
                const agentNotesField = document.getElementById('agentPaymentNotes');
                if (agentNotesField) {
                    // استخدام معرف فريد مبني على الطابع الزمني
                    const uniqueId = Date.now().toString();

                    // إضافة السجل للملاحظات الحالية
                    agentNotesField.value += changeLog;

                    // تحديث قيمة الحقل في formData - هذا هو المفتاح!
                    formData.set('agent_payment_notes', agentNotesField.value);

                    console.log('✅ تم إضافة سجل التغييرات إلى ملاحظات جهة الحجز');
                }

                // إضافة السجل لملاحظات الشركة
                const companyNotesField = document.getElementById('companyPaymentNotes');
                if (companyNotesField) {
                    // إضافة السجل للملاحظات الحالية
                    companyNotesField.value += changeLog;

                    // تحديث قيمة الحقل في formData - هذا هو المفتاح!
                    formData.set('company_payment_notes', companyNotesField.value);

                    console.log('✅ تم إضافة سجل التغييرات إلى ملاحظات الشركة');
                }
            } else {
                console.log('ℹ️ لم يتم اكتشاف أي تغييرات تستحق التسجيل');
            }

            // تأكد من تضمين القيم الصفرية
            if (!formData.get('agent_payment_amount')) {
                formData.set('agent_payment_amount', '0');
            }

            if (!formData.get('company_payment_amount')) {
                formData.set('company_payment_amount', '0');
            }

            // إضافة معرف الحجز
            formData.append('booking_id', currentBookingId);

            // إظهار مؤشر التحميل وتعطيل زر الحفظ
            const saveButton = document.getElementById('saveFinancialTracking');
            const originalButtonText = saveButton.innerHTML;
            saveButton.disabled = true;
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري الحفظ...';

            console.log('🚀 إرسال البيانات للحفظ، ملاحظات جهة الحجز:', formData.get('agent_payment_notes'));
            console.log('🚀 إرسال البيانات للحفظ، ملاحظات الشركة:', formData.get('company_payment_notes'));

            // إرسال البيانات إلى الخادم
            fetch(`/bookings/${currentBookingId}/financial-tracking`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (error) {
                        console.error('❌ خطأ في قراءة الاستجابة:', error);
                        if (!response.ok) {
                            throw new Error(`خطأ في الاستجابة: ${response.status}`);
                        }
                        throw new Error('تعذر قراءة استجابة الخادم');
                    }

                    if (!response.ok) {
                        throw new Error(data.error || data.message ||
                            `خطأ ${response.status}: ${response.statusText}`);
                    }

                    return data;
                })
                .then(data => {
                    console.log('✅ تم حفظ المتابعة المالية بنجاح:', data);

                    if (data.success) {
                        // إظهار رسالة نجاح
                        showSuccessMessage(data.message || 'تم حفظ المتابعة المالية بنجاح');

                        // إعادة تحميل البيانات بعد تأخير قصير
                        setTimeout(() => {
                            loadFinancialTracking(currentBookingId);
                        }, 1000);
                    } else {
                        throw new Error(data.error || 'فشل في حفظ البيانات');
                    }
                })
                .catch(error => {
                    console.error('❌ خطأ في حفظ المتابعة المالية:', error);
                    showFinancialTrackingError(error.message || 'حدث خطأ في حفظ البيانات');
                })
                .finally(() => {
                    // إعادة تفعيل زر الحفظ وإخفاء مؤشر التحميل
                    saveButton.disabled = false;
                    saveButton.innerHTML = originalButtonText;
                });
        }



        /**
         * الحصول على تسمية حالة السداد
         * @param {string} status رمز الحالة
         * @returns {string} تسمية الحالة بالعربية
         */
        function getStatusLabel(status) {
            switch (status) {
                case 'not_paid':
                    return 'لم يتم السداد';
                case 'partially_paid':
                    return 'سداد جزئي';
                case 'fully_paid':
                    return 'تم السداد بالكامل';
                default:
                    return status || 'غير محدد';
            }
        }

        /**
         * الحصول على تسمية مستوى الأولوية
         * @param {string} priority رمز الأولوية
         * @returns {string} تسمية الأولوية بالعربية
         */
        function getPriorityLabel(priority) {
            switch (priority) {
                case 'low':
                    return 'منخفضة';
                case 'medium':
                    return 'متوسطة';
                case 'high':
                    return 'عالية';
                default:
                    return priority || 'غير محددة';
            }
        }

        // ===== دوال مساعدة للواجهة =====
        /**
         * تسجيل التغييرات في الملاحظات
         * @param {Object} responseData البيانات المستلمة من الخادم
         * @param {Object} oldData البيانات القديمة قبل التعديل
         */


        /**
         * إنشاء وإضافة سجل التغييرات
         * @param {string} type النوع (agent أو company)
         * @param {Object} changes التغييرات
         * @param {string} currency العملة
         * @param {string} userName اسم المستخدم
         * @param {string} timestamp الوقت والتاريخ
         * @param {Object} booking بيانات الحجز
         */
        // function createAndAppendChangeLog(type, changes, currency, userName, timestamp, booking) {
        //     if (!changes.statusChanged && !changes.amountChanged) return;

        //     const notesField = document.getElementById(`${type}PaymentNotes`);
        //     if (!notesField) return;

        //     const entityName = type === 'agent' ? 'جهة الحجز' : 'الشركة';
        //     let log = `\n--------------------------------------\n`;
        //     log += `[${timestamp}] قام ${userName} بتحديث بيانات ${entityName}:`;

        //     // تغيير حالة السداد
        //     if (changes.statusChanged) {
        //         const oldStatusLabel = getStatusLabelText(changes.oldStatus);
        //         const newStatusLabel = getStatusLabelText(changes.newStatus);
        //         log += `\n• تغيير الحالة: ${oldStatusLabel} ◀️ ${newStatusLabel}`;
        //     }

        //     // تغيير المبلغ المدفوع
        //     if (changes.amountChanged) {
        //         const oldFormatted = formatCurrencyValue(changes.oldAmount, currency);
        //         const newFormatted = formatCurrencyValue(changes.newAmount, currency);

        //         // حساب الفرق بين المبلغين
        //         const diff = changes.newAmount - changes.oldAmount;
        //         const diffFormatted = formatCurrencyValue(Math.abs(diff), currency);
        //         const diffSymbol = diff > 0 ? '▲' : '▼';

        //         log += `\n• تغيير المبلغ: ${oldFormatted} ◀️ ${newFormatted}`;
        //         log += `\n  ${diffSymbol} ${diff > 0 ? 'زيادة' : 'نقص'} بمقدار ${diffFormatted}`;

        //         // حساب النسبة المئوية للدفع
        //         const totalAmount = type === 'agent' ?
        //             (booking.amount_due_to_hotel || 0) :
        //             (booking.amount_due_from_company || 0);

        //         if (totalAmount > 0) {
        //             const percentage = Math.round((changes.newAmount / totalAmount) * 100);
        //             log += `\n• النسبة المئوية للسداد: ${percentage}%`;
        //         }
        //     }

        //     // إضافة السجل للملاحظات
        //     if (notesField.value.includes(log)) {
        //         console.log(`تم تجاهل سجل مكرر للـ ${entityName}`);
        //         return;
        //     }

        //     notesField.value += log;
        // }

        /**
         * الحصول على نص حالة السداد بالعربية
         * @param {string} status حالة السداد
         * @returns {string} النص العربي للحالة
         */
        function getStatusLabelText(status) {
            switch (status) {
                case 'not_paid':
                    return 'لم يتم السداد';
                case 'partially_paid':
                    return 'سداد جزئي';
                case 'fully_paid':
                    return 'تم السداد بالكامل';
                default:
                    return status || 'غير محدد';
            }
        }

        /**
         * تنسيق القيمة النقدية
         * @param {number} amount المبلغ
         * @param {string} currency العملة
         * @returns {string} المبلغ منسقاً مع العملة
         */
        function formatCurrencyValue(amount, currency) {
            return parseFloat(amount || 0).toLocaleString('ar-SA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ' + currency;
        }

        /**
         * إظهار شاشة التحميل
         */
        function showFinancialTrackingLoader() {
            document.getElementById('financialTrackingLoader').style.display = 'block';
            document.getElementById('financialTrackingContent').style.display = 'none';
            document.getElementById('financialTrackingError').style.display = 'none';
        }

        /**
         * إخفاء شاشة التحميل
         */
        function hideFinancialTrackingLoader() {
            document.getElementById('financialTrackingLoader').style.display = 'none';
        }

        /**
         * إظهار محتوى المتابعة المالية
         */
        function showFinancialTrackingContent() {
            document.getElementById('financialTrackingContent').style.display = 'block';
            document.getElementById('financialTrackingError').style.display = 'none';
        }

        /**
         * إظهار رسالة خطأ
         * 
         * @param {string} message رسالة الخطأ
         */
        function showFinancialTrackingError(message) {
            document.getElementById('financialTrackingErrorMessage').textContent = message;
            document.getElementById('financialTrackingError').style.display = 'block';
            document.getElementById('financialTrackingContent').style.display = 'none';
        }

        /**
         * إخفاء رسالة الخطأ
         */
        function hideFinancialTrackingError() {
            document.getElementById('financialTrackingError').style.display = 'none';
        }

        /**
         * إظهار رسالة نجاح
         * 
         * @param {string} message رسالة النجاح
         */
        function showSuccessMessage(message) {
            // يمكن استخدام Bootstrap Toast أو Alert
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

            document.body.appendChild(alertDiv);

            // إزالة الرسالة بعد 5 ثواني
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }

        // ===== دوال مساعدة للتنسيق =====

        /**
         * تنسيق الأرقام
         * 
         * @param {number} number الرقم المراد تنسيقه
         * @returns {string} الرقم منسق
         */
        function formatNumber(number) {
            return parseFloat(number || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        /**
         * تنسيق التاريخ
         * 
         * @param {string} dateString تاريخ في صيغة نصية
         * @returns {string} التاريخ منسق
         */
        function formatDate(dateString) {
            if (!dateString) return '-';

            const date = new Date(dateString);
            return date.toLocaleDateString('ar-SA', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }
        /**
         * تحويل التاريخ الميلادي إلى هجري
         * 
         * @param {string} dateString تاريخ في صيغة نصية
         * @returns {string} التاريخ بالهجري
         */
        function formatHijriDate(date) {
            if (!date) return '-';

            if (typeof date === 'string') {
                date = new Date(date);
            }

            // عرض الشهر الهجري باسمه بدلاً من الرقم
            const hijriMonths = [
                'محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني',
                'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان',
                'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'
            ];

            // الحصول على التاريخ الهجري
            const hijri = date.toLocaleDateString('ar-SA', {
                day: 'numeric',
                month: 'long',
                calendar: 'islamic'
            }).split('/');

            // تحويل الأرقام من العربية للإنجليزية
            const day = parseInt(hijri[0].replace(/[\u0660-\u0669]/g, d => d.charCodeAt(0) - 1632));
            const month = parseInt(hijri[1].replace(/[\u0660-\u0669]/g, d => d.charCodeAt(0) - 1632));

            // تحويل الأرقام للأرقام العربية مرة أخرى
            const arabicDay = day.toLocaleString('ar-SA');

            // إرجاع التاريخ بالصيغة المطلوبة
            return `${arabicDay} ${hijriMonths[month-1]}`;
        }


        /**
         * تنسيق التاريخ والوقت
         * 
         * @param {string} dateTimeString تاريخ ووقت في صيغة نصية
         * @returns {string} التاريخ والوقت منسق
         */
        function formatDateTime(dateTimeString) {
            if (!dateTimeString) return '-';

            const date = new Date(dateTimeString);
            return date.toLocaleString('ar-SA', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // ===== أحداث DOM =====

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 تم تحميل JavaScript للمتابعة المالية');

            // ===== إضافة أحداث للتحكم في حالة السداد =====

            // أحداث تغيير حالة السداد لجهة الحجز
            document.querySelectorAll('input[name="agent_payment_status"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    console.log('🔄 تغيير حالة السداد لجهة الحجز:', this.value);
                    updatePaymentAmountVisibility();
                });
            });

            // أحداث تغيير حالة السداد للشركة
            document.querySelectorAll('input[name="company_payment_status"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    console.log('🔄 تغيير حالة السداد للشركة:', this.value);
                    updatePaymentAmountVisibility();
                });
            });

            // أحداث تغيير المبلغ المدفوع
            document.getElementById('agentPaymentAmount').addEventListener('input', function() {
                updateProgressBars();
            });

            document.getElementById('companyPaymentAmount').addEventListener('input', function() {
                updateProgressBars();
            });

            // حدث حفظ المتابعة المالية
            document.getElementById('saveFinancialTracking').addEventListener('click', function(e) {
                e.preventDefault();
                saveFinancialTracking();
            });

            // منع إغلاق Modal عند النقر خارجه أثناء التحميل
            document.getElementById('financialTrackingModal').addEventListener('hide.bs.modal', function(e) {
                if (isLoadingFinancialData) {
                    e.preventDefault();
                    console.log('⏳ منع إغلاق Modal أثناء التحميل');
                }
            });

            console.log('✅ تم تجهيز جميع أحداث المتابعة المالية');
        });
    </script>
    <script>
        /**
         * ===== ميزات دفعات المتابعة المالية المتعددة =====
         * 
         * هذا الكود يضيف:
         * 1. أزرار لإضافة دفعة جزئية جديدة إلى المبلغ الحالي
         * 2. سجل آلي للتغييرات في حقل الملاحظات
         */
        document.addEventListener('DOMContentLoaded', function() {
            // ===== إضافة أزرار الدفعة الإضافية =====

            // إضافة زر للدفعة الإضافية لجهة الحجز
            const agentPartialLabel = document.querySelector('label[for="agentPartiallyPaid"]');
            if (agentPartialLabel) {
                // إنشاء زر صغير بجانب خيار "تم التحصيل جزئياً"
                const addAgentPaymentBtn = document.createElement('button');
                addAgentPaymentBtn.type = 'button';
                addAgentPaymentBtn.className = 'btn btn-sm btn-outline-warning ms-2';
                addAgentPaymentBtn.innerHTML = '<i class="fas fa-plus-circle me-1"></i> إضافة دفعة';
                addAgentPaymentBtn.title = 'إضافة دفعة جديدة إلى المبلغ المدفوع الحالي';
                addAgentPaymentBtn.id = 'addAgentPaymentBtn';

                // إضافة الزر بعد النص
                agentPartialLabel.appendChild(addAgentPaymentBtn);

                // إضافة حدث النقر للزر
                addAgentPaymentBtn.addEventListener('click', function(e) {
                    e.preventDefault(); // منع السلوك الافتراضي
                    showAdditionalPaymentModal('agent');
                });
            }

            // إضافة زر للدفعة الإضافية للشركة
            const companyPartialLabel = document.querySelector('label[for="companyPartiallyPaid"]');
            if (companyPartialLabel) {
                // إنشاء زر صغير بجانب خيار "تم التحصيل جزئياً"
                const addCompanyPaymentBtn = document.createElement('button');
                addCompanyPaymentBtn.type = 'button';
                addCompanyPaymentBtn.className = 'btn btn-sm btn-outline-warning ms-2';
                addCompanyPaymentBtn.innerHTML = '<i class="fas fa-plus-circle me-1"></i> إضافة دفعة';
                addCompanyPaymentBtn.title = 'إضافة دفعة جديدة إلى المبلغ المدفوع الحالي';
                addCompanyPaymentBtn.id = 'addCompanyPaymentBtn';

                // إضافة الزر بعد النص
                companyPartialLabel.appendChild(addCompanyPaymentBtn);

                // إضافة حدث النقر للزر
                addCompanyPaymentBtn.addEventListener('click', function(e) {
                    e.preventDefault(); // منع السلوك الافتراضي
                    showAdditionalPaymentModal('company');
                });
            }

            /**
             * عرض نافذة إضافة دفعة جديدة
             * 
             * @param {string} type نوع الجهة ('agent' للوكيل أو 'company' للشركة)
             */
            function showAdditionalPaymentModal(type) {
                // التحقق من وجود مودال سابق وإزالته
                const existingModal = document.getElementById('additionalPaymentModal');
                if (existingModal) {
                    existingModal.remove();
                }

                // المتغيرات المستخدمة حسب نوع الجهة
                const entityName = type === 'agent' ? 'جهة الحجز' : 'الشركة';
                const entityColor = type === 'agent' ? 'primary' : 'success';
                const currentAmount = parseFloat(document.getElementById(`${type}PaymentAmount`).value) || 0;
                const totalDue = parseFloat(document.getElementById(`${type}AmountDue`).value.replace(/,/g, '')) ||
                    0;
                const remainingAmount = totalDue - currentAmount;
                const currency = document.getElementById(`${type}Currency`).textContent;

                // إنشاء المودال
                const modalHTML = `
        <div class="modal fade" id="additionalPaymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-${entityColor} text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle me-2"></i>
                            إضافة دفعة جديدة لـ ${entityName}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <!-- تنسيق جديد لمعلومات الدفعة الحالية -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white py-2">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    معلومات الدفعة الحالية
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="fw-bold text-primary mb-1">المبلغ المستحق الكلي:</div>
                                        <h5 class="mb-3">${formatNumber(totalDue)} ${currency}</h5>
                                    </div>
                                    <div class="col-6">
                                        <div class="fw-bold text-success mb-1">المبلغ المدفوع حالياً:</div>
                                        <h5 class="mb-3">${formatNumber(currentAmount)} ${currency}</h5>
                                    </div>
                                    <div class="col-12">
                                        <div class="fw-bold text-danger mb-1">المبلغ المتبقي:</div>
                                        <h5>${formatNumber(remainingAmount)} ${currency}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="additionalAmount" class="form-label fw-bold">
                                <i class="fas fa-money-bill-wave text-${entityColor} me-1"></i>
                                مبلغ الدفعة الإضافية:
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="number" step="0.01" min="0.01" max="${remainingAmount}" 
                                       class="form-control form-control-lg text-center fw-bold" 
                                       id="additionalAmount" placeholder="أدخل المبلغ" required>
                                <span class="input-group-text bg-${entityColor} text-white">${currency}</span>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                المبلغ يجب أن يكون أكبر من صفر وأقل من أو يساوي ${formatNumber(remainingAmount)} ${currency}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="additionalNoteText" class="form-label fw-bold">
                                <i class="fas fa-sticky-note text-${entityColor} me-1"></i>
                                ملاحظة خاصة بالدفعة الإضافية:
                            </label>
                            <textarea class="form-control" id="additionalNoteText" rows="3"
                                placeholder="أضف أي ملاحظات خاصة بهذه الدفعة الإضافية..."></textarea>
                        </div>
                        
                        <!-- تنسيق جديد للملاحظة في الأسفل -->
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-lightbulb fs-5 me-3"></i>
                            <div>
                                سيتم إضافة المبلغ الجديد إلى المبلغ المدفوع الحالي، وسيتم تحديث الملاحظات تلقائياً.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-${entityColor}" id="confirmAdditionalPayment">
                            <i class="fas fa-check me-1"></i> إضافة الدفعة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;


                // إضافة المودال إلى الصفحة
                document.body.insertAdjacentHTML('beforeend', modalHTML);

                // تهيئة المودال وعرضه
                const modalElement = document.getElementById('additionalPaymentModal');
                const modal = new bootstrap.Modal(modalElement);
                modal.show();

                // إضافة حدث للزر التأكيد
                document.getElementById('confirmAdditionalPayment').addEventListener('click', function() {
                    addAdditionalPayment(type, modal);
                });

                // إضافة حدث لإدخال المبلغ للتحقق من القيمة
                document.getElementById('additionalAmount').addEventListener('input', function() {
                    validateAdditionalAmount(this, remainingAmount);
                });
            }

            /**
             * التحقق من صحة المبلغ الإضافي
             * 
             * @param {HTMLInputElement} input حقل إدخال المبلغ
             * @param {number} maxAmount الحد الأقصى المسموح به
             */
            function validateAdditionalAmount(input, maxAmount) {
                const value = parseFloat(input.value) || 0;

                if (value <= 0) {
                    input.setCustomValidity('المبلغ يجب أن يكون أكبر من صفر');
                    input.classList.add('is-invalid');
                } else if (value > maxAmount) {
                    input.setCustomValidity(`المبلغ يجب أن يكون أقل من أو يساوي ${maxAmount}`);
                    input.classList.add('is-invalid');
                } else {
                    input.setCustomValidity('');
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            }

            /**
             * إضافة الدفعة الإضافية إلى المبلغ الحالي
             * 
             * @param {string} type نوع الجهة ('agent' للوكيل أو 'company' للشركة)
             * @param {bootstrap.Modal} modal كائن المودال
             */
            function addAdditionalPayment(type, modal) {
                // جلب المتغيرات والقيم
                const additionalAmountInput = document.getElementById('additionalAmount');
                const additionalNoteInput = document.getElementById('additionalNoteText');

                // التحقق من صحة المبلغ
                const additionalAmount = parseFloat(additionalAmountInput.value) || 0;
                const currentAmount = parseFloat(document.getElementById(`${type}PaymentAmount`).value) || 0;
                const totalDue = parseFloat(document.getElementById(`${type}AmountDue`).value.replace(/,/g, '')) ||
                    0;
                const remainingAmount = totalDue - currentAmount;

                if (additionalAmount <= 0 || additionalAmount > remainingAmount) {
                    additionalAmountInput.focus();
                    return;
                }

                // الملاحظات الإضافية
                const additionalNote = additionalNoteInput.value.trim();

                // تحديث المبلغ المدفوع (إضافة المبلغ الإضافي للمبلغ الحالي)
                const newTotalPaid = currentAmount + additionalAmount;
                document.getElementById(`${type}PaymentAmount`).value = newTotalPaid.toFixed(2);

                // تغيير حالة السداد إلى "جزئي" إذا لزم الأمر
                document.getElementById(`${type}PartiallyPaid`).checked = true;

                // تحديث ملاحظات الدفع بإضافة سجل للدفعة الجديدة
                const notesField = document.getElementById(`${type}PaymentNotes`);
                const currentNotes = notesField.value;
                const entityName = type === 'agent' ? 'جهة الحجز' : 'الشركة';
                const currency = document.getElementById(`${type}Currency`).textContent;
                const userName = "{{ Auth::user()->name }}"; // اسم المستخدم الحالي

                // إنشاء سجل الدفعة الجديد
                const now = new Date();
                const timestamp = now.toLocaleDateString('ar-SA') + ' ' + now.toLocaleTimeString('ar-SA');

                let paymentLog = `\n--------------------------------------\n`;
                paymentLog += `[${timestamp}] قام ${userName} بتسجيل دفعة إضافية`;
                paymentLog += `\nالمبلغ: ${additionalAmount.toFixed(2)} ${currency}`;
                paymentLog += `\nإجمالي المدفوع: ${newTotalPaid.toFixed(2)} من ${totalDue.toFixed(2)} ${currency}`;
                paymentLog += `\nالنسبة: ${Math.round((newTotalPaid / totalDue) * 100)}%`;

                if (additionalNote) {
                    paymentLog += `\nملاحظات: ${additionalNote}`;
                }

                // إضافة السجل للملاحظات الحالية
                notesField.value = currentNotes + paymentLog;

                // تحديث أشرطة التقدم والنسب المئوية
                updateProgressBars();

                // إغلاق المودال
                modal.hide();

                // عرض رسالة نجاح
                showSuccessMessage(
                    `تم إضافة دفعة جديدة بقيمة ${additionalAmount.toFixed(2)} ${currency} إلى ${entityName}`);
            }

            // ===== التعديل على وظيفة حفظ المتابعة المالية =====

            // // نحفظ الدالة الأصلية
            // const originalSaveFinancialTracking = saveFinancialTracking;

            // // استبدال الدالة بنسخة معدّلة تضيف السجل
            // saveFinancialTracking = function() {
            //     // جمع البيانات الحالية قبل الحفظ
            //     const oldData = collectCurrentFormData();

            //     // استدعاء الدالة الأصلية لحفظ البيانات
            //     const result = originalSaveFinancialTracking.apply(this, arguments);

            //     // انتهى التنفيذ وتم الحفظ بنجاح (سنعتمد على رد المتابعة الناجح لتحديث السجلات)
            //     return result;
            // };

            /**
             * جمع بيانات النموذج الحالية قبل الحفظ
             * لاستخدامها في مقارنة التغييرات
             */
            function collectCurrentFormData() {
                return {
                    agent: {
                        status: document.querySelector('input[name="agent_payment_status"]:checked').value,
                        amount: parseFloat(document.getElementById('agentPaymentAmount').value) || 0,
                        notes: document.getElementById('agentPaymentNotes').value
                    },
                    company: {
                        status: document.querySelector('input[name="company_payment_status"]:checked').value,
                        amount: parseFloat(document.getElementById('companyPaymentAmount').value) || 0,
                        notes: document.getElementById('companyPaymentNotes').value
                    },
                    priority: document.getElementById('priorityLevel').value,
                    payment_deadline: document.getElementById('paymentDeadline').value,
                    follow_up_date: document.getElementById('followUpDate').value
                };
            }



            /**
             * التحقق من وجود تغييرات في بيانات جهة الحجز
             */
            function hasAgentChanges(oldData, newData) {
                return oldData.agent.status !== newData.agent.status ||
                    oldData.agent.amount !== newData.agent.amount;
            }

            /**
             * التحقق من وجود تغييرات في بيانات الشركة
             */
            function hasCompanyChanges(oldData, newData) {
                return oldData.company.status !== newData.company.status ||
                    oldData.company.amount !== newData.company.amount;
            }



            /**
             * الحصول على تسمية الحالة باللغة العربية
             */
            function getStatusLabel(statusValue) {
                switch (statusValue) {
                    case 'not_paid':
                        return 'لم يتم السداد';
                    case 'partially_paid':
                        return 'سداد جزئي';
                    case 'fully_paid':
                        return 'تم السداد بالكامل';
                    default:
                        return statusValue;
                }
            }

            // /**
            //  * تعديل دالة استجابة الخادم لإضافة السجل
            //  */
            // const originalThenCallback = window.fetch;
            // window.fetch = function() {
            //     const fetchPromise = originalThenCallback.apply(this, arguments);

            //     // التحقق مما إذا كان هذا طلب حفظ المتابعة المالية
            //     const url = arguments[0];
            //     if (typeof url === 'string' && url.includes('financial-tracking') && arguments[1]?.method ===
            //         'POST') {
            //         // جمع البيانات الحالية قبل إرسال الطلب
            //         const oldData = collectCurrentFormData();

            //         // تعديل سلوك الـ then للاستجابة الناجحة
            //         return fetchPromise.then(response => {
            //             // التحقق مما إذا كانت الاستجابة ناجحة
            //             if (response.ok) {
            //                 // نحتاج إلى نسخة من الاستجابة لأن استهلاكها يحدث مرة واحدة فقط
            //                 const clonedResponse = response.clone();

            //                 // معالجة البيانات وإضافة السجل بعد الحصول على استجابة ناجحة
            //                 clonedResponse.json().then(data => {
            //                     if (data.success) {
            //                         // إضافة سجل بعد التحميل الناجح للبيانات المحدثة
            //                         setTimeout(() => {
            //                             const newData = collectCurrentFormData();
            //                             addChangeLogToNotes(oldData, newData);
            //                         }, 1500); // تأخير قليل للتأكد من تحديث البيانات
            //                     }
            //                 }).catch(err => console.error('خطأ في معالجة الاستجابة:', err));
            //             }
            //             return response;
            //         });
            //     }

            //     // إذا لم يكن طلب متابعة مالية، نعيد الوعد الأصلي
            //     return fetchPromise;
            // };

            /**
             * تنسيق الأرقام بطريقة جميلة
             */
            function formatNumber(number) {
                return parseFloat(number || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
    </script>
    <script>
        /**
         * التحقق من وجود تغييرات في بيانات جهة الحجز
         * 
         * @param {Object} oldData البيانات القديمة
         * @param {Object} newData البيانات الجديدة
         * @returns {boolean} هل هناك تغييرات؟
         */
        function hasAgentChanges(oldData, newData) {
            return oldData.agent.status !== newData.agent_payment_status ||
                Math.abs(parseFloat(oldData.agent.amount || 0) - parseFloat(newData.agent_payment_amount || 0)) > 0.01;
        }

        /**
         * التحقق من وجود تغييرات في بيانات الشركة
         * 
         * @param {Object} oldData البيانات القديمة
         * @param {Object} newData البيانات الجديدة
         * @returns {boolean} هل هناك تغييرات؟
         */
        function hasCompanyChanges(oldData, newData) {
            return oldData.company.status !== newData.company_payment_status ||
                Math.abs(parseFloat(oldData.company.amount || 0) - parseFloat(newData.company_payment_amount || 0)) > 0.01;
        }





        /**
         * إضافة نص إلى حقل الملاحظات
         * 
         * @param {string} type النوع (agent أو company)
         * @param {string} log النص المراد إضافته
         */
        function appendToNotesField(type, log) {
            if (!log) return;

            const fieldId = type === 'agent' ? 'agentPaymentNotes' : 'companyPaymentNotes';
            const notesField = document.getElementById(fieldId);

            if (!notesField) {
                console.warn(`حقل الملاحظات ${fieldId} غير موجود`);
                return;
            }

            // التحقق من عدم تكرار السجل
            if (notesField.value.includes(log)) {
                console.log(`تم تجاهل سجل مكرر لـ ${type === 'agent' ? 'جهة الحجز' : 'الشركة'}`);
                return;
            }

            notesField.value += log;
        }

        /**
         * تنسيق التاريخ أو إرجاع قيمة افتراضية
         * 
         * @param {string} dateString سلسلة التاريخ
         * @returns {string} التاريخ المنسق أو "غير محدد"
         */
        function formatDateOrDefault(dateString) {
            if (!dateString) return 'غير محدد';

            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('ar-SA', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
            } catch (error) {
                return dateString || 'غير محدد';
            }
        }

        /**
         * الحصول على تسمية الأولوية
         * 
         * @param {string} priority رمز الأولوية
         * @returns {string} تسمية الأولوية
         */
        function getPriorityLabel(priority) {
            switch (priority) {
                case 'low':
                    return 'منخفضة';
                case 'medium':
                    return 'متوسطة';
                case 'high':
                    return 'عالية';
                default:
                    return priority || 'غير محددة';
            }
        }

        /**
         * الحصول على تسمية حالة السداد
         * 
         * @param {string} status رمز الحالة
         * @returns {string} تسمية الحالة
         */
        function getStatusLabelText(status) {
            switch (status) {
                case 'not_paid':
                    return 'لم يتم السداد';
                case 'partially_paid':
                    return 'سداد جزئي';
                case 'fully_paid':
                    return 'تم السداد بالكامل';
                default:
                    return status || 'غير محدد';
            }
        }

        /**
         * تنسيق القيمة النقدية مع العملة
         * 
         * @param {number} amount المبلغ
         * @param {string} currency العملة
         * @returns {string} المبلغ منسقاً مع العملة
         */
        function formatCurrencyValue(amount, currency) {
            return parseFloat(amount || 0).toLocaleString('ar-SA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ' + currency;
        }
    </script>
    <script>
        // Converts Gregorian dates to Hijri
        function convertToHijri() {
            document.querySelectorAll('.hijri-date').forEach(element => {
                const gregorianDate = element.getAttribute('data-date');
                if (gregorianDate) {
                    try {
                        // Use Intl.DateTimeFormat with 'islamic' calendar - month as LONG text
                        const hijriDate = new Intl.DateTimeFormat('ar-SA-islamic', {
                            day: 'numeric',
                            month: 'long', // تم تغييرها من 'numeric' إلى 'long'
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
@endpush

@push('styles')
    <style>
        /* ===== تنسيقات Modal المتابعة المالية ===== */

        #financialTrackingModal .modal-dialog {
            max-width: 95%;
            margin: 1rem auto;
        }

        #financialTrackingModal .modal-content {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        #financialTrackingModal .modal-header {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            border-bottom: none;
        }

        #financialTrackingModal .modal-footer {
            border-top: 1px solid #e9ecef;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        /* تنسيقات البطاقات */
        #financialTrackingModal .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }

        #financialTrackingModal .card:hover {
            transform: translateY(-2px);
        }

        #financialTrackingModal .card-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: none;
            font-weight: 600;
        }

        /* تنسيقات أشرطة التقدم */
        #financialTrackingModal .progress {
            height: 20px;
            border-radius: 10px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        #financialTrackingModal .progress-bar {
            font-size: 12px;
            font-weight: 600;
            transition: width 0.3s ease-in-out;
        }

        /* تنسيقات الحقول */
        #financialTrackingModal .form-control,
        #financialTrackingModal .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        #financialTrackingModal .form-control:focus,
        #financialTrackingModal .form-select:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        }

        /* تنسيقات Radio Buttons */
        #financialTrackingModal .form-check {
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
        }

        #financialTrackingModal .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.1em;
        }

        #financialTrackingModal .form-check-label {
            font-weight: 500;
            cursor: pointer;
            transition: color 0.2s ease-in-out;
        }

        #financialTrackingModal .form-check-label:hover {
            opacity: 0.8;
        }

        /* تنسيقات الأزرار */
        #financialTrackingModal .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            transition: all 0.2s ease-in-out;
        }

        #financialTrackingModal .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* تنسيقات شاشة التحميل */
        #financialTrackingLoader {
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        #financialTrackingLoader .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* تنسيقات رسائل الخطأ */
        #financialTrackingError {
            border-radius: 10px;
            border-left: 5px solid #dc3545;
        }

        /* تنسيقات المعلومات الإضافية */
        #financialTrackingModal .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }

        #financialTrackingModal .input-group-text {
            font-weight: 600;
            min-width: 60px;
            justify-content: center;
        }

        /* تنسيقات التقسيم المرئي */
        #financialTrackingModal hr {
            margin: 1rem 0;
            border-top: 2px solid #e9ecef;
        }

        /* تنسيقات المعايير المخصصة */
        #financialTrackingModal .border-primary {
            border-color: #0d6efd !important;
        }

        #financialTrackingModal .border-success {
            border-color: #198754 !important;
        }

        #financialTrackingModal .border-warning {
            border-color: #ffc107 !important;
        }

        #financialTrackingModal .border-info {
            border-color: #0dcaf0 !important;
        }

        /* تنسيقات الشارات */
        #financialTrackingModal .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }

        /* تنسيقات النصوص الصغيرة */
        #financialTrackingModal .small,
        #financialTrackingModal small {
            font-size: 0.875rem;
        }

        /* تأثيرات التحريك */
        @keyframes slideInFromTop {
            0% {
                transform: translateY(-50px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        #financialTrackingModal.show .modal-content {
            animation: slideInFromTop 0.3s ease-out;
        }

        /* تنسيقات الاستجابة للهواتف */
        @media (max-width: 768px) {
            #financialTrackingModal .modal-dialog {
                max-width: 95%;
                margin: 0.5rem;
            }

            #financialTrackingModal .row>div {
                margin-bottom: 1rem;
            }

            #financialTrackingModal .modal-footer {
                flex-direction: column;
                align-items: stretch;
            }

            #financialTrackingModal .modal-footer>div:first-child {
                margin-bottom: 1rem;
                text-align: center;
            }

            #financialTrackingModal .modal-footer button {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        /* تنسيقات خاصة للطباعة */
        @media print {
            #financialTrackingModal {
                display: none !important;
            }
        }

        /* تحسينات إضافية للأداء */
        #financialTrackingModal * {
            box-sizing: border-box;
        }

        #financialTrackingModal .fade {
            transition: opacity 0.15s linear;
        }

        /* تنسيقات الحالة النشطة */
        #financialTrackingModal .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        #financialTrackingModal .form-check-input:checked[value="not_paid"] {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        #financialTrackingModal .form-check-input:checked[value="partially_paid"] {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        #financialTrackingModal .form-check-input:checked[value="fully_paid"] {
            background-color: #198754;
            border-color: #198754;
        }

        /* تنسيق حقول التاريخ المزدوج (ميلادي وهجري) */
        #bookingCheckInHijri,
        #bookingCheckOutHijri {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 2px;
        }

        #financialTrackingModal .date-container {
            display: flex;
            flex-direction: column;
        }

        #financialTrackingModal .gregorian-date {
            font-weight: 600;
            color: #212529;
        }

        #financialTrackingModal .hijri-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 2px;
        }

        /* إضافة أيقونة للتواريخ */
        #bookingCheckIn,
        #bookingCheckOut {
            position: relative;
            font-weight: 600;
        }

        /* تحسين تنسيق عرض التاريخ الهجري */
        #bookingCheckInHijri,
        #bookingCheckOutHijri {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-top: 3px;
        }

        /* إضافة رمز تقويم هجري قبل التاريخ الهجري */
        #bookingCheckInHijri:before,
        #bookingCheckOutHijri:before {
            content: "🌙 ";
            opacity: 0.7;
        }
    </style>
@endpush
