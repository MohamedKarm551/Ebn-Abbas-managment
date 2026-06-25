<x-app-layout>
<div style="max-width:1100px;margin:40px auto;padding:20px;font-family:Arial;" dir="rtl">

    <h2 style="margin-bottom:20px;">⏳ الخصومات في انتظار الموافقة</h2>

    @if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:12px;
                border-radius:6px;margin-bottom:16px;">
        {{ session('success') }}
    </div>
    @endif

    @if($discounts->isEmpty())
    <div style="background:white;border-radius:10px;padding:40px;
                text-align:center;color:#999;
                box-shadow:0 2px 8px rgba(0,0,0,.1);">
        🎉 لا توجد خصومات في الانتظار
    </div>
    @else
    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;background:white;
                  border-radius:8px;overflow:hidden;
                  box-shadow:0 2px 8px rgba(0,0,0,.1);">
        <thead style="background:#f59e0b;color:white;">
            <tr>
                <th style="padding:12px;">العميل</th>
                <th style="padding:12px;">الرحلة</th>
                <th style="padding:12px;">مبلغ الخصم</th>
                <th style="padding:12px;">السبب</th>
                <th style="padding:12px;">المندوب</th>
                <th style="padding:12px;">التاريخ</th>
                <th style="padding:12px;">إجراء</th>
            </tr>
        </thead>
        <tbody>
            @foreach($discounts as $discount)
            <tr style="border-bottom:1px solid #f0f0f0;text-align:center;
                       background:{{ $loop->even ? '#fffbeb':'white' }}">
                <td style="padding:12px;font-weight:bold;">
                    {{ $discount->booking->client_name }}
                </td>
                <td style="padding:12px;">
                    {{ $discount->booking->trip->name }}
                </td>
                <td style="padding:12px;color:#dc2626;font-weight:bold;">
                     {{ number_format($discount->amount,2) }} ج.م
                </td>
                <td style="padding:12px;">{{ $discount->description }}</td>
                <td style="padding:12px;color:#6b7280;">
                    {{ $discount->createdBy->name }}
                </td>
                <td style="padding:12px;color:#6b7280;font-size:13px;">
                    {{ $discount->created_at->format('Y/m/d') }}
                </td>
                <td style="padding:12px;">
                    <form method="POST"
                          action="{{ route('discounts.approve', $discount) }}"
                          style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit"
                            style="background:#059669;color:white;
                                   padding:6px 12px;border:none;
                                   border-radius:4px;cursor:pointer;
                                   margin-left:4px;">
                            ✅ اعتماد
                        </button>
                    </form>
                    <form method="POST"
                          action="{{ route('discounts.reject', $discount) }}"
                          style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit"
                            style="background:#ef4444;color:white;
                                   padding:6px 12px;border:none;
                                   border-radius:4px;cursor:pointer;">
                            ❌ رفض
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:16px;">{{ $discounts->links() }}</div>
    @endif
</div>
</x-app-layout>