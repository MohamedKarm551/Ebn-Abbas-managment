<!-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\reports\company_payments.blade.php -->
@extends('layouts.app')

{{-- *** تأكد من وجود رابط Font Awesome في layout الرئيسي layouts/app.blade.php *** --}}
{{-- مثال: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> --}}

@section('content')
    <div class="container">
        <h1>سجل المدفوعات - {{ $company->name }}</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th> {{-- *** إضافة رأس عمود الترقيم *** --}}
                    <th>التاريخ</th>
                    <th>المبلغ</th>
                    <th>الملاحظات</th>
                    <th>الإيصال</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    @php
                        // --- معالجة الملاحظات والإيصال مرة واحدة ---
                        $receiptUrl = null;
                        $isUploaded = false;
                        $displayNotes = $payment->notes; // الملاحظات الأصلية للعرض

                        // أولاً: تحقق من الملف المرفوع
                        if ($payment->receipt_path) {
                            $isUploaded = true;
                        }
                        // ثانياً: إذا لا يوجد ملف مرفوع، ابحث عن رابط في الملاحظات
                        elseif ($payment->notes) {
                            // ابحث عن أول رابط http/https
                            $pattern = '/(https?:\/\/[^\s]+)/';
                            if (preg_match($pattern, $payment->notes, $matches)) {
                                $receiptUrl = $matches[0]; // استخراج الرابط
                                // استبدل الرابط في النص المعروض
                                $displayNotes = preg_replace($pattern, '"رابط صورة الإيصال"', $payment->notes, 1); // استبدال مرة واحدة فقط
                            }
                        }
                        // --- نهاية المعالجة ---
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration }}</td> {{-- *** إضافة خلية الترقيم *** --}}
                        <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                        <td>{{ $payment->amount }} ريال</td>
                        {{-- *** عرض الملاحظات بعد استبدال الرابط (إن وجد) *** --}}
                        <td>{!! nl2br(e($displayNotes)) !!}</td> {{-- استخدم nl2br للحفاظ على الأسطر الجديدة و e للحماية --}}

                        <td> {{-- *** خلية الإيصال *** --}}
                            @if ($isUploaded)
                                {{-- عرض أيقونة تشير لوجود ملف مرفوع --}}
                                <span title="تم إرفاق إيصال (لا يمكن عرضه مباشرة)">
                                    <i class="fas fa-file-invoice text-success"></i>
                                </span>
                            @elseif ($receiptUrl)
                                {{-- عرض أيقونة كرابط للـ URL الموجود في الملاحظات --}}
                                <a href="{{ $receiptUrl }}" target="_blank" title="فتح رابط الإيصال من الملاحظات">
                                    <i class="fas fa-external-link-alt"></i> {{-- *** تغيير هنا: عرض أيقونة الرابط *** --}}
                                </a>
                            @else
                                {{-- لا يوجد إيصال أو رابط --}}
                                -
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('reports.company.payment.edit', $payment->id) }}"
                                class="btn btn-warning btn-sm">تعديل</a>
                            <form action="{{ route('reports.company.payment.destroy', $payment->id) }}" method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذه الدفعة؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
