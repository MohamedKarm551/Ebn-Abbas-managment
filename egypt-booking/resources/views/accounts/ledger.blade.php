{{-- resources/views/accounts/ledger.blade.php --}}
<x-app-layout>
<style>
<style>
@media print {
    .no-print { display: none !important; }
    body { direction: rtl; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 6px; font-size: 11px; }
    th { background: #1d4ed8 !important; color: white !important; -webkit-print-color-adjust: exact; }
}
</style>
</style>
<div style="max-width:1300px;margin:40px auto;padding:20px;font-family:Arial,'Tajawal',sans-serif;" dir="rtl">

    {{-- رأس الصفحة مع أزرار الإجراءات --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="margin:0;">📊 كشف حساب: {{ $account->name }} ({{ $account->code }})</h2>
            <p style="color:#6b7280;margin:4px 0 0;">
                عرض حركات الحساب مع الرصيد التراكمي
            </p>
        </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
    {{-- Excel --}}
    <a href="{{ route('accounts.ledger.export', array_merge(['account' => $account->id, 'type' => 'excel'], request()->query())) }}"
        style="background:#059669;color:white;padding:7px 16px;border-radius:6px;text-decoration:none;font-size:13px;">
        📊 Excel
    </a>

    {{-- PDF --}}
    <a href="{{ route('accounts.ledger.export', array_merge(['account' => $account->id, 'type' => 'pdf'], request()->query())) }}"  target="_blank"
        style="background:#dc2626;color:white;padding:7px 16px;border-radius:6px;text-decoration:none;font-size:13px;">
        📄 PDF
    </a>
</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('accounts.index') }}" 
               style="background:#6b7280;color:white;padding:8px 16px;
                      border-radius:30px;text-decoration:none;
                      box-shadow:0 2px 4px rgba(0,0,0,0.05);font-weight:600;">
                ← شجرة الحسابات
            </a>

        </div>
    </div>

    {{-- بطاقة معلومات الحساب --}}
    <div style="background:#f9fafb;border-radius:12px;padding:12px 20px;margin-bottom:16px;
                display:flex;gap:20px;flex-wrap:wrap;align-items:center;">
        <div>🏷️ <strong>نوع الحساب:</strong>
            @if($account->type == 'asset') أصل
            @elseif($account->type == 'liability') خصم
            @elseif($account->type == 'equity') حقوق ملكية
            @elseif($account->type == 'revenue') إيراد
            @else مصروف
            @endif
        </div>
        <div>⚖️ <strong>طبيعة الرصيد:</strong> {{ $account->normal_balance == 'debit' ? 'مدين' : 'دائن' }}</div>
        @if(!$account->is_active)
            <div style="background:#fee2e2;color:#991b1b;padding:4px 12px;border-radius:30px;font-size:13px;">
                ⚠️ حساب مجمد – لا يقبل قيوداً جديدة
            </div>
        @endif
    </div>

    {{-- فلتر البحث --}}
    <div style="background:white;border-radius:12px;padding:16px 20px;margin-bottom:20px;
                box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <form method="GET" action="{{ route('accounts.ledger', $account) }}">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 0.3; min-width: 150px;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">🔍 بحث بـ</label>
                    <select name="search_by" class="form-select" style="width:100%; padding:8px; border-radius:8px; border:1px solid #e5e7eb;">
                        <option value="">-- اختر --</option>
                        <option value="id" {{ request('search_by') == 'id' ? 'selected' : '' }}>📝 رقم القيد</option>
                        <option value="reference" {{ request('search_by') == 'reference' ? 'selected' : '' }}>🔖 رقم المرجع</option>
                        <option value="status" {{ request('search_by') == 'status' ? 'selected' : '' }}>🏷️ الحالة</option>
                        <option value="created_by" {{ request('search_by') == 'created_by' ? 'selected' : '' }}>👤 بواسطة</option>
                        <option value="created_at" {{ request('search_by') == 'created_at' ? 'selected' : '' }}>📅 تاريخ الإنشاء</option>
                    </select>
                </div>
                <div style="flex: 0.5; min-width: 180px;">
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">✏️ القيمة</label>
                    <input type="text" name="search_value" value="{{ request('search_value') }}" 
                           style="width:100%; padding:8px; border-radius:8px; border:1px solid #e5e7eb;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">📅 من تاريخ</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           style="padding:8px; border-radius:8px; border:1px solid #e5e7eb;" onkeydown="return false">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;">📅 إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           style="padding:8px; border-radius:8px; border:1px solid #e5e7eb;" onkeydown="return false">
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" style="background:#111827; color:white; border:none; padding:8px 20px; border-radius:30px; cursor:pointer;">🔍 بحث</button>
                    <a href="{{ route('accounts.ledger', $account) }}" style="background:#f3f4f6; color:#374151; padding:8px 16px; border-radius:30px; text-decoration:none; border:1px solid #e5e7eb;">🗑️ مسح</a>
                </div>
            </div>
        </form>
    </div>

    {{-- جدول كشف الحساب --}}
    <div style="background:white; border-radius:16px; overflow-x:auto; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <table style="width:100%; border-collapse:collapse; font-size:13px; min-width:800px;">
            <thead style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                <tr>
                    <th style="padding:12px 10px; text-align:right;">#</th>
                    <th style="padding:12px 10px; text-align:right;">رقم القيد</th>
                    <th style="padding:12px 10px; text-align:right;">المرجع</th>
                    <th style="padding:12px 10px; text-align:right;">التاريخ</th>
                    <th style="padding:12px 10px; text-align:right;">الحساب المقابل</th>
                    <th style="padding:12px 10px; text-align:right;">البيان</th>
                    <th style="padding:12px 10px; text-align:left;">مدين</th>
                    <th style="padding:12px 10px; text-align:left;">دائن</th>
                    <th style="padding:12px 10px; text-align:left;">الرصيد</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                    $runningBalance = $openingBalance;
                @endphp

                {{-- صف الرصيد الافتتاحي --}}
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:10px;">{{ $counter++ }}</td>
                    <td style="padding:10px;">—</td>
                    <td style="padding:10px;">الرصيد الافتتاحي</td>
                    <td style="padding:10px;">—</td>
                    <td colspan="2" style="padding:10px;">رصيد أول المدة</td>
                    <td style="padding:10px; text-align:left; color:#059669; font-weight:600;">
                        {{ $openingBalance > 0 ? number_format($openingBalance, 2) : '-' }}
                    </td>
                    <td style="padding:10px; text-align:left; color:#dc2626; font-weight:600;">
                        {{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '-' }}
                    </td>
                    <td style="padding:10px; text-align:left; font-weight:600;">
                        {{ number_format(abs($openingBalance), 2) }} {{ $openingBalance >= 0 ? 'مدين' : 'دائن' }}
                    </td>
                </tr>

                @forelse($transactions as $transaction)
                    @php
                        $runningBalance += $transaction->debit - $transaction->credit;
                        $entry = $transaction->journalEntry;
                        $oppositeAccount = $entry->lines->where('account_id', '!=', $account->id)->first();
                        $entryType = (is_null($entry->source_type) || $entry->source_type === 'manual') ? 'manual' : 'auto';

                       $detailedDescription = null;
                        if ($entry->source_type === 'App\Models\Booking' && $entry->source_id) {
                            $booking = $bookings[$entry->source_id] ?? null;
                            if ($booking) {
                                // استخدام تواريخ الرحلة (from, to) بدلاً من check_in/check_out
                                $trip = $booking->trip;
                                $dateFrom = $trip?->from ? \Carbon\Carbon::parse($trip->from)->format('d-m-y') : '—';
                                $dateTo   = $trip?->to   ? \Carbon\Carbon::parse($trip->to)->format('d-m-y')   : '—';

                                $detailedDescription = [
                                    'type'  => 'booking',
                                    'line1' => "حجز # {$trip->name} - {$booking->id} - {$booking->client_name}",
                                    'line2' => "الرحلة: {$dateFrom} → {$dateTo}",
                                    'line3' => number_format($booking->base_price, 2) . " ر.س",
                                    'url'   => route('bookings.show', $booking->id),
                                ];
                            }
                        }
                    @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px;">{{ $counter++ }}</td>
                        <td style="padding:10px;">
                            <a href="{{ route('journal.index', ['search_by' => 'id', 'search_value' => $entry->id, 'type' => $entryType]) }}" 
                               style="background:#fef3c7; color:#92400e; padding:2px 12px; border-radius:20px; text-decoration:none; font-size:12px;">
                                {{ $entry->id }}
                            </a>
                        </td>
                        <td style="padding:10px;">{{ $entry->reference }}</td>
                        <td style="padding:10px;">{{ $entry->entry_date->format('d/m/Y') }}</td>
                        <td style="padding:10px;">
                            @if($oppositeAccount)
                                <span style="font-size:12px; color:#374151;">
                                    <span style="color:#6b7280;">{{ $oppositeAccount->account->code ?? '' }}</span><br>
                                    <strong>{{ $oppositeAccount->account->name ?? '' }}</strong>
                                </span>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="padding:10px;">
                            @if($detailedDescription)
                                <a href="{{ $detailedDescription['url'] }}" style="text-decoration:none; color:inherit;">
                                    <div style="font-weight:600; color:#111827; font-size:13px;">{{ $detailedDescription['line1'] }}</div>
                                    <div style="color:#6b7280; font-size:12px; margin-top:2px;">{{ $detailedDescription['line2'] }}</div>
                                    <div style="color:#059669; font-size:12px; font-weight:600; margin-top:2px;">{{ $detailedDescription['line3'] }}</div>
                                </a>
                            @else
                                <span style="color:#374151;">{{ $transaction->description }}</span>
                            @endif
                        </td>
                        <td style="padding:10px; text-align:left; color:#059669; font-weight:600;">
                            {{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}
                        </td>
                        <td style="padding:10px; text-align:left; color:#dc2626; font-weight:600;">
                            {{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}
                        </td>
                        <td style="padding:10px; text-align:left; font-weight:600;">
                            {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'مدين' : 'دائن' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px; color:#9ca3af;">
                            📭 لا توجد حركات أخرى لهذا الحساب
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                <tr>
                    <td colspan="6" style="padding:12px 10px;"><strong>الرصيد النهائي</strong></td>
                    <td colspan="2" style="padding:12px 10px; text-align:left; font-weight:700;">
                        {{ number_format(abs($runningBalance ?? $openingBalance), 2) }} ر.س
                    </td>
                    <td style="padding:12px 10px;"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:20px;">
        {{ $transactions->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    
</div>

<script>
    function convertTextToLinks() {
        document.querySelectorAll('.ledger-table tbody tr td:nth-child(6)').forEach(cell => {
            if (cell.querySelector('a')) return;
            let html = cell.innerHTML;
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            if (urlRegex.test(html)) {
                html = html.replace(urlRegex, url => {
                    return `<a href="${url}" target="_blank" style="color:#0d6efd; text-decoration:none;">
                                <i class="fas fa-external-link-alt"></i>
                            </a>`;
                });
                cell.innerHTML = html;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        convertTextToLinks();
        document.querySelectorAll('.ledger-table tbody tr').forEach(row => {
            const descCell = row.cells[5];
            if (descCell) {
                const link = descCell.querySelector('a');
                if (link && link.href) {
                    descCell.style.cursor = 'pointer';
                    descCell.addEventListener('click', (e) => {
                        if (e.target.tagName !== 'A') {
                            window.open(link.href, '_blank');
                        }
                    });
                }
            }
        });
    });
</script>
</x-app-layout>