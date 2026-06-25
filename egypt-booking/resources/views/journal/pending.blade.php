<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:20px;
            font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;
                align-items:center;margin-bottom:20px;">
        <div>
            <h2 style="margin:0;">⏳ القيود في انتظار الاعتماد</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                لوحة المحاسب — راجع واعتمد أو ألغِ كل قيد
            </p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('journal.index') }}"
               style="background:#0f172a;color:white;padding:8px 16px;
                      border-radius:30px;text-decoration:none;
                      box-shadow:0 2px 4px rgba(0,0,0,0.05);
                      font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                📋 قائمة القيود
            </a>
            <a href="{{ route('journal.create') }}"
               style="background:#2563eb;color:white;padding:8px 16px;
                      border-radius:30px;text-decoration:none;
                      box-shadow:0 2px 4px rgba(0,0,0,0.05);font-weight:600;">
                ✏️ قيد يدوي جديد
            </a>
            <a href="{{ route('accounts.index') }}"
               style="background:#6b7280;color:white;padding:8px 16px;
                      border-radius:30px;text-decoration:none;
                      box-shadow:0 2px 4px rgba(0,0,0,0.05);font-weight:600;">
                📊 شجرة الحسابات
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        {{ session('error') }}
    </div>
    @endif

    @if($entries->isEmpty())
    <div style="background:white;border-radius:10px;padding:60px;
                text-align:center;color:#999;
                box-shadow:0 2px 8px rgba(0,0,0,.1);">
        🎉 لا توجد قيود في انتظار الاعتماد
    </div>
    @else

    @foreach($entries as $entry)
    @php
        $sourceLabel = match($entry->source_type) {
            \App\Models\Booking::class  => '🎫 حجز',
            \App\Models\Payment::class  => '💳 دفعة',
            \App\Models\Discount::class => '🏷️ خصم',
            null                        => '✏️ يدوي',
            default                     => '📋 أخرى',
        };
        $totalDebit  = $entry->lines->sum('debit');
        $totalCredit = $entry->lines->sum('credit');
    @endphp

    <div style="background:white;border-radius:10px;
                box-shadow:0 2px 8px rgba(0,0,0,.1);
                margin-bottom:16px;overflow:hidden;
                border-right:4px solid #f59e0b;">

        {{-- Header القيد --}}
        <div style="padding:16px 20px;
                    display:flex;justify-content:space-between;
                    align-items:center;background:#fffbeb;
                    border-bottom:1px solid #fde68a;">
            <div style="display:flex;gap:16px;align-items:center;">
                <div>
                    <span style="font-size:18px;font-weight:bold;">
                        #{{ $entry->id }}
                    </span>
                    <span style="background:#fde68a;color:#92400e;
                                 padding:3px 10px;border-radius:20px;
                                 font-size:12px;font-weight:bold;
                                 margin-right:8px;">
                        {{ $entry->reference }}
                    </span>
                </div>
                <span style="background:#e0e7ff;color:#3730a3;
                             padding:3px 10px;border-radius:20px;
                             font-size:12px;">
                    {{ $sourceLabel }}
                </span>
                <span style="color:#6b7280;font-size:13px;">
                    📅 {{ $entry->entry_date->format('d/m/Y') }}
                </span>
                <span style="color:#6b7280;font-size:13px;">
                    👤 {{ $entry->creator->name ?? '-' }}
                </span>
            </div>

            {{-- أزرار الاعتماد والإلغاء --}}
            <div style="display:flex;gap:8px;">
                {{-- اعتماد --}}
                <form method="POST"
                      action="{{ route('journal.approve', $entry) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        style="background:#059669;color:white;
                               padding:8px 16px;border:none;
                               border-radius:6px;cursor:pointer;
                               font-weight:bold;">
                        ✅ اعتماد
                    </button>
                </form>

                {{-- إلغاء --}}
                <form method="POST"
                      action="{{ route('journal.cancel', $entry) }}"
                      onsubmit="return confirm('{{ $entry->source_type === \App\Models\Booking::class ? '⚠️ سيتم حذف الحجز المرتبط! هل أنت متأكد؟' : 'إلغاء القيد؟' }}')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        style="background:#ef4444;color:white;
                               padding:8px 16px;border:none;
                               border-radius:6px;cursor:pointer;
                               font-weight:bold;">
                        ❌ إلغاء
                        @if($entry->source_type === \App\Models\Booking::class)
                            <span style="font-size:11px;">(+ حذف الحجز)</span>
                        @endif
                    </button>
                </form>
            </div>
        </div>

        {{-- تفاصيل المصدر --}}
        @if($entry->source_type === \App\Models\Booking::class && $entry->source_id)
        @php $booking = \App\Models\Booking::withTrashed()->find($entry->source_id); @endphp
        @if($booking)
        <div style="padding:10px 20px;background:#f0f9ff;
                    border-bottom:1px solid #bae6fd;font-size:13px;">
            <strong>📋 تفاصيل الحجز:</strong>
            {{ $booking->client_name }} |
            رحلة: {{ $booking->trip->name ?? '—' }} |
            التسكين: {{ $booking->accommodation_type }} |
            السعر: {{ number_format($booking->base_price, 2) }} ج.م
        </div>
        @endif
        @endif

        {{-- أسطر القيد --}}
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="padding:8px 16px;text-align:right;color:#6b7280;">
                            الحساب
                        </th>
                        <th style="padding:8px;text-align:center;color:#059669;">
                            مدين
                        </th>
                        <th style="padding:8px;text-align:center;color:#dc2626;">
                            دائن
                        </th>
                        <th style="padding:8px 16px;text-align:right;color:#6b7280;">
                            البيان
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->lines as $line)
                    <tr style="border-top:1px solid #f3f4f6;">
                        <td style="padding:8px 16px;">
                            <span style="color:#6b7280;font-size:11px;">
                                {{ $line->account->code ?? '' }}
                            </span>
                            {{ $line->account->name ?? '—' }}
                        </td>
                        <td style="padding:8px;text-align:center;
                                   color:#059669;font-weight:bold;">
                            {{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}
                        </td>
                        <td style="padding:8px;text-align:center;
                                   color:#dc2626;font-weight:bold;">
                            {{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}
                        </td>
                        <td style="padding:8px 16px;color:#6b7280;font-size:12px;">
                            {{ $line->description }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="background:#f9fafb;">
                    <tr>
                        <td style="padding:8px 16px;font-weight:bold;">الإجمالي</td>
                        <td style="padding:8px;text-align:center;
                                   font-weight:bold;color:#059669;">
                            {{ number_format($totalDebit, 2) }}
                        </td>
                        <td style="padding:8px;text-align:center;
                                   font-weight:bold;color:#dc2626;">
                            {{ number_format($totalCredit, 2) }}
                        </td>
                        <td style="padding:8px 16px;">
                            @if(abs($totalDebit - $totalCredit) < 0.01)
                                <span style="color:#059669;">✅ متوازن</span>
                            @else
                                <span style="color:#dc2626;">
                                    ❌ فرق: {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endforeach

    <div style="margin-top:16px;">{{ $entries->links() }}</div>
    @endif
</div>
</x-app-layout>