{{-- resources/views/accounts/ledger.blade.php --}}
@extends('layouts.app')
@section('title', 'كشف حساب - ' . $account->name)

@section('content')
<style>
    .ledger-wrap { direction: rtl; font-family: 'Tajawal','Cairo',sans-serif; }
    
    /* نفس CSS اللي في journal.index */
    .page-toolbar {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 20px; background: #fff; border-bottom: 1px solid #e5e7eb;
    }
    .page-toolbar h5 { font-size: 15px; font-weight: 700; margin: 0; }
    .btn-back {
        background: #6b7280; color: #fff; border: none;
        padding: 8px 18px; border-radius: 6px; font-size: 13px;
        text-decoration: none;
    }
    
    .search-section {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 20px;
    }
    .search-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1;
        min-width: 180px;
    }
    .search-group label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }
    .search-group select, .search-group input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
    }
    .btn-search {
        background: #111827;
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        cursor: pointer;
    }
    .btn-reset {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
    }
    
    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        background: #fff;
    }
    .ledger-table th {
        background: #f9fafb;
        padding: 10px 14px;
        font-weight: 600;
        border-bottom: 2px solid #e5e7eb;
        text-align: right;
    }
    .ledger-table td {
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .ledger-table tr:hover td {
        background: #fafafa;
        cursor: pointer;
    }
    
    .debit { color: #059669; font-weight: 600; }
    .credit { color: #dc2626; font-weight: 600; }
    .ref-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 700;
    }
    .badge-posted {
        background: #ecfdf5;
        color: #065f46;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 11px;
    }
    .info-card {
        background: #f9fafb;
        padding: 12px 20px;
        margin: 12px 20px;
        border-radius: 8px;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .info-card span {
        font-weight: 700;
        color: #111827;
    }
    .active-filter {
        background: #fef3c7;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        margin: 12px 20px 0 20px;
    }

    .btn-view {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 5px 12px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-view:hover {
    background: #d97706;
    color: white;
    transform: translateY(-1px);
}

.btn-view:active {
    transform: translateY(0);
}

/* تنسيق رسالة الحساب المجمد */
.frozen-banner {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-right: 6px solid #dc2626;
    border-radius: 12px;
    padding: 14px 24px;
    margin: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 14px;
    font-weight: 500;
    color: #7f1d1d;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
    transition: all 0.2s ease;
}

.frozen-banner i {
    font-size: 22px;
    color: #dc2626;
}

.frozen-banner .banner-text {
    flex: 1;
}

.frozen-banner .banner-text strong {
    font-weight: 700;
    display: inline-block;
    margin-left: 6px;
}

.frozen-banner .banner-hint {
    font-size: 12px;
    color: #b91c1c;
    margin-top: 4px;
    font-weight: normal;
}


/* تخصيص pagination مع RTL */
.pagination {
    gap: 6px;
}

.page-link {
    border-radius: 30px !important;
    padding: 8px 15px;
    color: #0d6efd;
    background-color: #fff;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}

.page-link:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
    transform: translateY(-2px);
}

.active > .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
}

.disabled > .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
}


/* تحديد عرض عمود البيان (العمود السادس) */
.ledger-table th:nth-child(6),
.ledger-table td:nth-child(6) {
    width: 20%;
    max-width: 20%;
    word-break: break-word;
    white-space: normal;
}

</style>

<div class="ledger-wrap">
    <div class="page-toolbar">
        <h5>📊 كشف حساب: {{ $account->name }} ({{ $account->code }})</h5>
        <a href="{{ route('accounts.index') }}" class="btn-back">← العودة للشجرة</a>
    </div>
    
{{-- بطاقة معلومات الحساب --}}
<div class="d-flex align-items-start gap-3 flex-wrap">
    {{--这部分 كما هو، لم يتغير --}}
    <div class="info-card flex-grow-1">
        <div>🏷️ <strong>نوع الحساب:</strong>
            @if($account->type == 'asset') أصل
            @elseif($account->type == 'liability') خصم
            @elseif($account->type == 'equity') حقوق ملكية
            @elseif($account->type == 'revenue') إيراد
            @else مصروف
            @endif
        </div>
        <div>⚖️ <strong>طبيعة الرصيد:</strong> {{ $account->normal_balance == 'debit' ? 'مدين' : 'دائن' }}</div>
    </div>

    {{-- أزرار التصدير – موضوعة في الشمال --}}
    <div class="d-flex gap-2 me-auto mt-3">
       <a href="{{ route('accounts.ledger', $account) . '?' . http_build_query(array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Excel
        </a>
       <a href="{{ route('accounts.ledger.print', $account) . '?' . http_build_query(request()->query()) }}" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
    </div>
</div>
    
    {{-- مجمد --}}
@if(!$account->is_active)
    <div class="frozen-banner">
        <i class="fas fa-lock"></i>
        <div class="banner-text">
            <strong>⚠️ هذا الحساب مجمد</strong>
            <div class="banner-hint">لا يقبل أي قيود محاسبية جديدة حتى يتم إعادة تفعيله.</div>
        </div>
    </div>
@endif

    {{-- Search --}}
    <div class="search-section">
        <form method="GET" action="{{ route('accounts.ledger', $account) }}">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div class="search-group" style="flex: 0.3;">
                    <label>🔍 بحث بـ</label>
                    <select name="search_by">
                        <option value="">-- اختر --</option>
                        <option value="id" {{ request('search_by') == 'id' ? 'selected' : '' }}>📝 رقم القيد</option>
                        <option value="reference" {{ request('search_by') == 'reference' ? 'selected' : '' }}>🔖 رقم المرجع</option>
                        <option value="status" {{ request('search_by') == 'status' ? 'selected' : '' }}>🏷️ الحالة</option>
                        <option value="created_by" {{ request('search_by') == 'created_by' ? 'selected' : '' }}>👤 بواسطة</option>
                        <option value="created_at" {{ request('search_by') == 'created_at' ? 'selected' : '' }}>📅 تاريخ الإنشاء</option>
                    </select>
                </div>
                <div class="search-group" style="flex: 0.5;">
                    <label>✏️ القيمة</label>
                    <input type="text" name="search_value" value="{{ request('search_value') }}" placeholder="أدخل القيمة...">
                </div>
                <div class="search-group">
                    <label>📅 من تاريخ</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" onkeydown="return false">
                </div>
                <div class="search-group">
                    <label>📅 إلى تاريخ</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" onkeydown="return false">
                </div>
                <div class="search-actions" style="display: flex; gap: 8px; align-items: flex-end;">
                    <button type="submit" class="btn-search">🔍 بحث</button>
                    <a href="{{ route('accounts.ledger', $account) }}" class="btn-reset">🗑️ مسح</a>
                </div>
            </div>
        </form>
    </div>
    
    {{-- الجدول --}}
    <table class="ledger-table">
        <thead>
            <tr>
                <th>#</th>
                <th>رقم القيد</th>
                <th>رقم المرجع</th>
                <th>التاريخ</th>
                <th>الحساب المقابل</th>
                <th >البيان</th>
                <th style="text-align: left;">مدين</th>
                <th style="text-align: left;">دائن</th>
                <th style="text-align: left;">الرصيد</th>
            </tr>
        </thead>
{{-- بعد الـ </thead> مباشرة --}}
<tbody>
    @php
        $counter = 1;
        $runningBalance = $openingBalance;
    @endphp

    {{-- صف الرصيد الافتتاحي --}}
    <tr>
        <td>{{ $counter++ }}</td>
        <td>—</td>
        <td>الرصيد الافتتاحي</td>
        <td>—</td>
        <td colspan="2">رصيد أول المدة</td>
        <td class="debit" style="text-align: left;">
            {{ $openingBalance > 0 ? number_format($openingBalance, 2) : '-' }}
        </td>
        <td class="credit" style="text-align: left;">
            {{ $openingBalance < 0 ? number_format(abs($openingBalance), 2) : '-' }}
        </td>
        <td style="text-align: left; font-weight: 600;">
            {{ number_format(abs($openingBalance), 2) }}
            {{ $openingBalance >= 0 ? 'مدين' : 'دائن' }}
        </td>
    </tr>

    {{-- الحركات الفعلية --}}
    @forelse($transactions as $index => $transaction)
        @php
            $runningBalance += $transaction->debit - $transaction->credit;
            $entry = $transaction->journalEntry;
            $oppositeAccount = $entry->lines->where('account_id', '!=', $account->id)->first();
            $entryType = (is_null($entry->source_type) || $entry->source_type === 'manual') ? 'manual' : 'auto';

            // ✅ تحديد البيان التفصيلي
        $detailedDescription = null;

        if ($entry->source_type === 'App\Models\Booking' && $entry->source_id) {
            $booking = $bookings[$entry->source_id] ?? null;
            if ($booking) {
                $checkIn  = $booking->check_in  ? \Carbon\Carbon::parse($booking->check_in)->format('d-m-y')  : '—';
                $checkOut = $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d-m-y') : '—';
                $detailedDescription = [
                    'type'    => 'booking',
                    'line1'   => "{$booking->id} {$booking->client_name} - " . ($booking->hotel->name ?? '—'),
                    'line2'   => "{$booking->rooms} غرفة : {$checkIn} → {$checkOut}",
                    'line3'   => number_format($booking->sale_price, 2) . " " . ($booking->currency === 'KWD' ? 'د.ك' : 'ر.س'),
                    'url'     => route('bookings.show', $booking->id),
                ];
            }
        } elseif ($entry->source_type === 'App\Models\Availability' && $entry->source_id) {
            $availability = $availabilities[$entry->source_id] ?? null;
            if ($availability) {
                $startDate = $availability->start_date ? \Carbon\Carbon::parse($availability->start_date)->format('d-m-y') : '—';
                $endDate   = $availability->end_date   ? \Carbon\Carbon::parse($availability->end_date)->format('d-m-y')   : '—';
                $roomsSummary = $availability->availabilityRoomTypes->map(function($rt) {
                    return ($rt->roomType->room_type_name ?? '—') . ': ' . $rt->allotment . ' غرفة بـ ' . number_format($rt->cost_price, 2);
                })->implode(' | ');
                $detailedDescription = [
                    'type'  => 'availability',
                    'line1' => "{$availability->id} - " . ($availability->hotel->name ?? '—'),
                    'line2' => "{$startDate} → {$endDate}",
                    'line3' => $roomsSummary,
                    'url'   => route('admin.availabilities.show', $availability->id),
                ];
            }
        }

        @endphp
        <tr>
            <td>{{ $counter++ }}</td>
             {{-- رقم القيد --}}
            <td>
                <a href="{{ route('journal.index', ['search_by' => 'id', 'search_value' => $entry->id, 'type' => $entryType]) }}" 
                   class="ref-badge" style="text-decoration: none; color: #92400e;">
                    {{ $entry->id }}
                </a>
            </td>
            {{-- رقم المرجع --}}
            <td>
                {{ $entry->reference }}
            </td>
            <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
            {{-- ✅ عمود الحساب المقابل الجديد --}}
            <td>
                @if($oppositeAccount)
                    <span style="font-size: 12px; color: #374151;">
                        <span style="color: #6b7280;">{{ $oppositeAccount->account->code ?? '' }}</span>
                        <br>
                        <strong>{{ $oppositeAccount->account->name ?? '' }}</strong>
                    </span>
                @else
                    <span style="color: #9ca3af;">—</span>
                @endif
            </td>
            
             {{-- ✅ عمود البيان التفصيلي --}}
        <td >
            @if($detailedDescription)
                <a href="{{ $detailedDescription['url'] }}" style="text-decoration: none; color: inherit;">
                    <div style="font-weight: 600; color: #111827; font-size: 13px;">
                        {{ $detailedDescription['line1'] }}
                    </div>
                    <div style="color: #6b7280; font-size: 12px; margin-top: 2px;">
                        {{ $detailedDescription['line2'] }}
                    </div>
                    <div style="color: #059669; font-size: 12px; font-weight: 600; margin-top: 2px;">
                        {{ $detailedDescription['line3'] }}
                    </div>
                </a>
            @else
                <span 
                    style="color: #374151;">{{ $transaction->description }}
                </span>
            @endif
        </td>
            <td class="debit" style="text-align: left;">{{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}</td>
            <td class="credit" style="text-align: left;">{{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}</td>
            <td style="text-align: left; font-weight: 600;">{{ number_format(abs($runningBalance), 2) }} {{ $runningBalance >= 0 ? 'مدين' : 'دائن' }}</td>
        </tr>
    @empty
        {{-- في حالة عدم وجود حركات، يبقى صف الرصيد الافتتاحي فقط --}}
        @if($transactions->isEmpty())
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px;">📭 لا توجد حركات أخرى لهذا الحساب</td>
            </tr>
        @endif
    @endforelse
</tbody>
        <tfoot>
            <tr>
                <td colspan="6"><strong>الرصيد النهائي</strong></td>
                <td colspan="2" style="text-align: left; font-weight: 700; background: #f3f4f6;">
                    {{ number_format(abs($runningBalance ?? $openingBalance), 2) }} ر.س
                </td>
                <td style="background: #f3f4f6;"></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="pagination-wrap" style="padding: 12px 20px;">
    {{ $transactions->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
   function convertTextToLinks() {
    document.querySelectorAll('.ledger-table tbody tr td:nth-child(6)').forEach(cell => {
        // ✅ إذا كانت الخلية تحتوي بالفعل على رابط <a>، تخطى معالجتها
        if (cell.querySelector('a')) {
            return;
        }
        let html = cell.innerHTML;
        // التعبير العادي يلتقط الرابط حتى أول مسافة أو نهاية النص
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        if (urlRegex.test(html)) {
            html = html.replace(urlRegex, url => {
                // استبدال الرابط النصي بأيقونة فقط
                return `<a href="${url}" target="_blank" style="color: #0d6efd; text-decoration: none;">
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
@endsection