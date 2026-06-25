<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:30px;background:white;
            border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.1);font-family:Arial;" dir="rtl">

    {{-- Header with back button only --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <div>
            <h2 style="margin:0;">🎫 تفاصيل الحجز #{{ $booking->id }}</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                رحلة: <strong>{{ $booking->trip->name }}</strong>
            </p>
        </div>
        <div>
            <a href="{{ route('bookings.edit', $booking) }}"
                           style="background:#f59e0b;color:white;padding:5px 10px;
                                  border-radius:4px;text-decoration:none;
                                  display:inline-block;margin-bottom:4px;">
                            ✏️ تعديل
                        </a>
            <a href="{{ route('trips.bookings', $booking->trip) }}"
               style="background:#6b7280;color:white;padding:8px 16px;
                      border-radius:6px;text-decoration:none;">← رجوع</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        {{ session('success') }}
    </div>
    @endif

    {{-- بيانات العميل --}}
    <div style="background:#f8fafc;border-radius:8px;padding:20px;margin-bottom:24px;">
        <h4 style="color:#2563eb;margin:0 0 16px;">👤 بيانات العميل</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div>
                <div style="color:#6b7280;font-size:12px;">الاسم</div>
                <div style="font-weight:bold;font-size:16px;">{{ $booking->client_name }}</div>
            </div>
            <div>
                <div style="color:#6b7280;font-size:12px;">النوع</div>
                <div style="font-weight:bold;">
                    @switch($booking->gender)
                        @case('male')   👨 ذكر @break
                        @case('female') 👩 أنثى @break
                        @case('child')  👦 طفل @break
                        @case('infant') 👶 رضيع @break
                    @endswitch
                </div>
            </div>
            <div>
                <div style="color:#6b7280;font-size:12px;">نوع التسكين</div>
                <div style="font-weight:bold;">🛏️ {{ $booking->accommodation_type }}</div>
            </div>
            <div>
                <div style="color:#6b7280;font-size:12px;">المندوب</div>
                <div style="font-weight:bold;"> {{ $booking->representative ? $booking->representative->name : '—' }}</div>
            </div>
            @if($booking->notes)
            <div style="grid-column:span 2;">
                <div style="color:#6b7280;font-size:12px;">ملاحظات</div>
                <div>{{ $booking->notes }}</div>
            </div>
            @endif
        </div>

        {{-- الصور --}}
        @if($booking->passport_image || $booking->personal_photo)
        <div style="display:flex;gap:16px;margin-top:16px;">
            @if($booking->passport_image)
            <div style="text-align:center;">
                <div style="color:#6b7280;font-size:12px;margin-bottom:4px;">صورة الجواز</div>
                 <a href="{{ asset('storage/'.$booking->passport_image) }}"  target="_blank">
                <img src="{{ asset('storage/'.$booking->passport_image) }}"
                     style="height:100px;border-radius:6px;border:2px solid #ddd;"></a>
            </div>
            @endif
            @if($booking->personal_photo)
            <div style="text-align:center;">
                <div style="color:#6b7280;font-size:12px;margin-bottom:4px;">صورة شخصية</div>
                <a href="{{ asset('storage/'.$booking->personal_photo) }}"  target="_blank">
                <img src="{{ asset('storage/'.$booking->personal_photo) }}"
                     style="height:100px;border-radius:6px;border:2px solid #ddd;"></a>
            </div>
            @endif
            
        </div>
        @endif
    </div>

    
    {{-- قسم الدفعات (ظاهر دائماً) --}}
    <div id="paymentsSection">
        {{-- ملخص الدفعات --}}
        <div style="background:#f8fafc;border-radius:8px;padding:16px;margin-bottom:20px;">
            <h4 style="margin:0 0 12px;color:#2563eb;">💳 الدفعات</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;
            text-align:center;margin-bottom:12px;">
    
    {{-- السعر الأساسي (قبل الخصم) --}}
    <div style="background:#eff6ff;border-radius:6px;padding:10px;">
        <div style="font-size:12px;color:#6b7280;">السعر الأساسي</div>
        <div style="font-weight:bold;color:#2563eb;">
            {{ number_format($booking->base_price, 2) }} ج.م
        </div>
    </div>

    {{-- المدفوع --}}
    <div style="background:#f0fdf4;border-radius:6px;padding:10px;">
        <div style="font-size:12px;color:#6b7280;">المدفوع</div>
        <div style="font-weight:bold;color:#059669;">
            {{ number_format($booking->totalPaid(), 2) }} ج.م
        </div>
    </div>

    {{-- الخصم --}}
    <div style="background:#fffbeb;border-radius:6px;padding:10px;">
        <div style="font-size:12px;color:#6b7280;">الخصم</div>
        <div style="font-weight:bold;color:#f59e0b;">
            {{ number_format($booking->discounts->where('status','approved')->sum('amount'), 2) }} ج.م
        </div>
    </div>

    {{-- المتبقي --}}
    <div style="background:{{ $booking->remaining()>0?'#fef2f2':'#f0fdf4' }};
                border-radius:6px;padding:10px;">
        <div style="font-size:12px;color:#6b7280;">المتبقي</div>
        <div style="font-weight:bold;
                    color:{{ $booking->remaining()>0?'#dc2626':'#059669' }};">
            {{ number_format($booking->remaining(), 2) }} ج.م
        </div>
    </div>

</div>

            {{-- جدول الدفعات --}}
            @if($booking->payments->isNotEmpty())
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead style="background:#e5e7eb;">
                    <tr>
                        <th style="padding:8px;">المبلغ</th>
                        <th style="padding:8px;">التاريخ</th>
                        <th style="padding:8px;">ملاحظات</th>
                        <th style="padding:8px;">إيصال</th>
                        <th style="padding:8px;">الاجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->payments as $payment)
                    <tr style="text-align:center;border-bottom:1px solid #f0f0f0;">
                        <td style="padding:8px;color:#059669;font-weight:bold;">
                            {{ number_format($payment->amount,2) }} ج.م
                        </td>
                        <td style="padding:8px;">{{ $payment->paid_at }}</td>
                        <td style="padding:8px;">{{ $payment->notes ?? '—' }}</td>
                        <td style="padding:8px;">
                            @if($payment->receipt_image)
                            <a href="{{ asset('storage/'.$payment->receipt_image) }}"
                               target="_blank">
                                <img src="{{ asset('storage/'.$payment->receipt_image) }}"
                                     style="height:35px;border-radius:4px;">
                            </a>
                            @else
                            —
                            @endif
                        </td>
                        @if(auth()->user()->hasRole('admin'))
                            <td style="padding:8px;">
                                <div style="display:flex; gap:6px; align-items:center; justify-content:center;">

                                    @if($payment->journal_entry_id)
                                        <a href="{{ route('vouchers.show', $payment->journal_entry_id) }}"
                                           style="background:#f59e0b;color:white;padding:6px 12px;
                                                  border-radius:6px;text-decoration:none;font-size:13px;
                                                  font-weight:600;display:inline-flex;align-items:center;">
                                            ✏️ تعديل الإيصال
                                        </a>
                                    @else
                                        <span style="color:#999;font-size:12px;">لا يوجد إيصال</span>
                                    @endif

                                    <form method="POST"
                                  action="{{ route('payments.destroy', $payment) }}"
                                  onsubmit="return confirm('حذف الدفعة؟')"
                                  style="display:inline;">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        style="
                                            background:#ef4444;
                                            color:white;
                                            padding:6px 12px;
                                            border:none;
                                            border-radius:6px;
                                            cursor:pointer;
                                            font-size:13px;
                                            font-weight:600;
                                            display:inline-flex;
                                            align-items:center;
                                            justify-content:center;
                                            min-height:36px;
                                            box-sizing:border-box;
                                        ">
                                        🗑️ حذف
                                    </button>

                                    </form>

                                </div>
                            </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <p style="color:#999;">لا توجد دفعات مسجلة</p>
            @endif

            {{-- إضافة دفعة جديدة (تظهر فقط في حالة وجود متبقي) --}}
            @if($booking->remaining() > 0)
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                <div style="font-size:13px;font-weight:bold;margin-bottom:8px;">
                    ➕ إضافة دفعة
                </div>
                <form method="POST"
                      action="{{ route('bookings.payments.add', $booking) }}"
                      enctype="multipart/form-data"
                      style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;
                             gap:8px;align-items:end;">
                    @csrf
                    <div>
                        <input type="number" name="amount" placeholder="المبلغ *"
                               min="1" step="0.01" required
                               style="width:100%;padding:8px;border:1px solid #ddd;
                                      border-radius:6px;box-sizing:border-box;font-size:13px;">
                    </div>
                    <div>
                        <input type="date" name="paid_at" value="{{ date('Y-m-d') }}"
                               style="width:100%;padding:8px;border:1px solid #ddd;
                                      border-radius:6px;box-sizing:border-box;font-size:13px;">
                    </div>
                    <div>
                        <input type="text" name="notes" placeholder="ملاحظات"
                               style="width:100%;padding:8px;border:1px solid #ddd;
                                      border-radius:6px;box-sizing:border-box;font-size:13px;">
                    </div>
                    <div>
                    <input type="file" name="receipt_image" accept="image/*" required
                               style="width:100%;padding:6px;border:1px solid #ddd;
                                      border-radius:6px;box-sizing:border-box;font-size:12px;">
                    </div>
                    <button type="submit"
                        style="background:#059669;color:white;padding:8px 14px;
                               border:none;border-radius:6px;cursor:pointer;
                               white-space:nowrap;">
                        💾 إضافة
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- ===== الخصومات ===== --}}
<div style="background:#fffbeb;border:1px solid #fde68a;
            border-radius:8px;padding:16px;margin-bottom:20px;">
    <h4 style="color:#92400e;margin:0 0 12px;">🏷️ الخصومات</h4>

    {{-- جدول الخصومات --}}
    @if($booking->discounts->isNotEmpty())
    <table style="width:100%;border-collapse:collapse;margin-bottom:12px;font-size:13px;">
        <thead style="background:#fde68a;">
            <tr>
                <th style="padding:8px;">المبلغ</th>
                <th style="padding:8px;">السبب</th>
                <th style="padding:8px;">بواسطة</th>
                <th style="padding:8px;">الحالة</th>
                @if(auth()->user()->hasRole('admin'))
                <th style="padding:8px;">إجراء</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($booking->discounts as $discount)
            <tr style="text-align:center;border-bottom:1px solid #fde68a;">
                <td style="padding:8px;font-weight:bold;color:#dc2626;">
                     {{ number_format($discount->amount,2) }} ج.م
                </td>
                <td style="padding:8px;">{{ $discount->description }}</td>
                <td style="padding:8px;color:#6b7280;">
                    {{ $discount->createdBy->name }}
                </td>
                <td style="padding:8px;">
                    @if($discount->status == 'pending')
                        <span style="background:#fef3c7;color:#92400e;
                                     padding:3px 8px;border-radius:20px;font-size:12px;">
                            ⏳ انتظار
                        </span>
                    @elseif($discount->status == 'approved')
                        <span style="background:#d1fae5;color:#065f46;
                                     padding:3px 8px;border-radius:20px;font-size:12px;">
                            ✅ معتمد
                        </span>
                    @else
                        <span style="background:#fee2e2;color:#991b1b;
                                     padding:3px 8px;border-radius:20px;font-size:12px;">
                            ❌ مرفوض
                        </span>
                    @endif
                    
                </td>
                @if(auth()->user()->hasRole('admin'))
             <td style="padding:10px;">

    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:center;">

        @if($discount->status == 'pending')

        <form method="POST"
              action="{{ route('discounts.approve', $discount) }}"
              style="display:inline;">
            @csrf
            @method('PATCH')

            <button type="submit"
                style="
                    background:#059669;
                    color:white;
                    padding:6px 12px;
                    border:none;
                    border-radius:8px;
                    cursor:pointer;
                    font-size:13px;
                    font-weight:600;
                    display:inline-flex;
                    align-items:center;
                    gap:5px;
                    box-shadow:0 2px 6px rgba(0,0,0,0.15);
                    transition:0.2s;
                "
                onmouseover="this.style.opacity='0.85'"
                onmouseout="this.style.opacity='1'">
                ✅ اعتماد
            </button>
        </form>

        <form method="POST"
              action="{{ route('discounts.reject', $discount) }}"
              style="display:inline;">
            @csrf
            @method('PATCH')

            <button type="submit"
                style="
                    background:#f97316;
                    color:white;
                    padding:6px 12px;
                    border:none;
                    border-radius:8px;
                    cursor:pointer;
                    font-size:13px;
                    font-weight:600;
                    display:inline-flex;
                    align-items:center;
                    gap:5px;
                    box-shadow:0 2px 6px rgba(0,0,0,0.15);
                    transition:0.2s;
                "
                onmouseover="this.style.opacity='0.85'"
                onmouseout="this.style.opacity='1'">
                ❌ رفض
            </button>
        </form>

        @endif

        <a href="{{ route('discounts.edit', $discount) }}"
           style="
                background:#3b82f6;
                color:white;
                padding:6px 12px;
                border-radius:8px;
                text-decoration:none;
                font-size:13px;
                font-weight:600;
                display:inline-flex;
                align-items:center;
                gap:5px;
                box-shadow:0 2px 6px rgba(0,0,0,0.15);
                transition:0.2s;
           "
           onmouseover="this.style.opacity='0.85'"
           onmouseout="this.style.opacity='1'">
            ✏️ تعديل
        </a>

        <form method="POST"
              action="{{ route('discounts.destroy', $discount) }}"
              onsubmit="return confirm('حذف الخصم؟')"
              style="display:inline;">

            @csrf
            @method('DELETE')

            <button type="submit"
                style="
                    background:#ef4444;
                    color:white;
                    padding:6px 12px;
                    border:none;
                    border-radius:8px;
                    cursor:pointer;
                    font-size:13px;
                    font-weight:600;
                    display:inline-flex;
                    align-items:center;
                    gap:5px;
                    box-shadow:0 2px 6px rgba(0,0,0,0.15);
                    transition:0.2s;
                "
                onmouseover="this.style.opacity='0.85'"
                onmouseout="this.style.opacity='1'">
                🗑️ حذف
            </button>

        </form>

    </div>

</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- فورم إضافة خصم جديد --}}
    <form method="POST" action="{{ route('discounts.store', $booking) }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 2fr auto;
                    gap:10px;align-items:end;">
            <div>
                <label style="display:block;font-size:12px;margin-bottom:4px;">
                    💰 مبلغ الخصم
                </label>
                <input type="number" name="amount"
                       placeholder="0.00" min="1" step="0.01"
                       style="width:100%;padding:8px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:12px;margin-bottom:4px;">
                    📝 سبب الخصم
                </label>
                <input type="text" name="description"
                       placeholder="مثلاً: العميل معه تأشيرة"
                       style="width:100%;padding:8px;border:1px solid #ddd;
                              border-radius:6px;box-sizing:border-box;">
            </div>
            <button type="submit"
                style="background:#f59e0b;color:white;padding:9px 14px;
                       border:none;border-radius:6px;cursor:pointer;
                       white-space:nowrap;">
                📤 طلب خصم
            </button>
        </div>
    </form>
</div>


</div>
</x-app-layout>