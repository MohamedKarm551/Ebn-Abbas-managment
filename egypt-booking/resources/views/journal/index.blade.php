<x-app-layout>
<style>
    .journal-wrap {
        direction: rtl;
        font-family: 'Tajawal', 'Cairo', sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* الأدوات العلوية */
    .page-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border-radius: 16px 16px 0 0;
        padding: 16px 24px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        border-bottom: 1px solid #e9ecef;
    }

    .toolbar-title {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    .toolbar-title h5 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(135deg, #1e293b, #2d3a4f);
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
    }
    .btn-group-custom .btn {
        border-radius: 30px;
        padding: 6px 18px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.2s;
        margin-left: 5px;
    }
    .btn-primary-custom {
        background: #f59e0b;
        border: none;
        color: white;
    }
    .btn-primary-custom:hover {
        background: #d97706;
        transform: translateY(-1px);
    }
    .btn-outline-custom {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
    }
    .btn-outline-custom:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .btn-add {
        background: #0f172a;
        color: white;
        padding: 8px 24px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .btn-add:hover {
        background: #1e293b;
        color: white;
        transform: translateY(-1px);
    }

    /* شريط البحث */
    .search-section {
        background: #fff;
        padding: 20px 24px;
        border-bottom: 1px solid #edf2f7;
    }
    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
    }
    .search-group {
        flex: 1;
        min-width: 170px;
    }
    .search-group label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        display: block;
        margin-bottom: 6px;
    }
    .search-group input, .search-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        font-size: 0.85rem;
        background: #fff;
        transition: 0.2s;
    }
    .search-group input:focus, .search-group select:focus {
        border-color: #f59e0b;
        outline: none;
        box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
    }
    .search-actions {
        display: flex;
        gap: 10px;
    }
    .btn-search {
        background: #f59e0b;
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 40px;
        font-weight: 600;
    }
    .btn-reset {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 10px 20px;
        border-radius: 40px;
        color: #334155;
        text-decoration: none;
    }

    /* الفلاتر النشطة */
    .active-filters {
        background: #fffbeb;
        border-right: 4px solid #f59e0b;
        padding: 10px 20px;
        margin: 16px 24px 16px 24px;
        border-radius: 14px;
        font-size: 0.8rem;
        color: #92400e;
    }

    /* الجدول الرئيسي – بطاقات قابلة للتمدد */
    .journal-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 16px;
        border: 1px solid #edf2f7;
        transition: 0.2s;
        overflow: hidden;
    }
    .journal-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    }
    .card-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        background: #fefcf5;
        cursor: pointer;
        border-right: 6px solid #f59e0b;
        transition: background 0.1s;
    }
    .card-header-row.draft-border {
        border-right-color: #f59e0b;
    }
    .card-header-row.posted-border {
        border-right-color: #10b981;
    }
    .entry-basic-info {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    .entry-id {
        font-weight: 800;
        font-size: 1.1rem;
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 40px;
    }
    .ref-badge {
        background: #fef3c7;
        color: #b45309;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .source-badge {
        background: #e0f2fe;
        color: #0369a1;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .date-meta {
        color: #64748b;
        font-size: 0.75rem;
        display: flex;
        gap: 12px;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .status-draft {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fed7aa;
    }
    .status-posted {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .btn-sm-icon {
        padding: 6px 12px;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: white;
        border: 1px solid #e2e8f0;
        transition: 0.1s;
    }
    .btn-approve-sm {
        background: #f59e0b;
        border: none;
        color: white;
    }
    .btn-approve-sm:hover {
        background: #d97706;
    }
    .btn-cancel-sm {
        background: #fee2e2;
        border: none;
        color: #b91c1c;
    }
    .btn-cancel-sm:hover {
        background: #fecaca;
    }
    /* تفاصيل الحجز إن وجدت */
    .booking-info {
        background: #f0f9ff;
        padding: 8px 24px;
        font-size: 0.75rem;
        border-bottom: 1px solid #e0f2fe;
        color: #075985;
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
    }
    /* أسطر القيد الداخلية */
    .lines-wrapper {
        background: #fafcff;
        padding: 16px 24px;
        border-top: 1px solid #ecf3fa;
        display: none;
    }
    .lines-wrapper.show {
        display: block;
    }
    .inner-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.75rem;
    }
    .inner-table th {
        text-align: right;
        padding: 10px 8px;
        color: #475569;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
    }
    .inner-table td {
        padding: 8px;
        border-bottom: 1px solid #f1f5f9;
    }
    .debit-number {
        color: #059669;
        font-weight: 700;
    }
    .credit-number {
        color: #dc2626;
        font-weight: 700;
    }
    .total-row {
        background: #f8fafc;
        font-weight: 800;
    }
    .toggle-icon {
        font-size: 12px;
        color: #94a3b8;
        margin-left: 8px;
        transition: transform 0.2s;
        display: inline-block;
    }
    .toggle-icon.rotated {
        transform: rotate(90deg);
    }
    .alert-success-custom {
        background: #ecfdf5;
        border: 1px solid #6ee7b7;
        color: #065f46;
        padding: 12px 20px;
        border-radius: 16px;
        margin: 0 24px 16px 24px;
    }
    .pagination-wrap {
        margin-top: 24px;
        padding: 10px 0;
    }
    .empty-card {
        background: white;
        border-radius: 28px;
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
</style>

<div class="journal-wrap">
    {{-- شريط الأدوات --}}
    <div class="page-toolbar">
        <div class="toolbar-title">
            <h5><span style="color: #f59e0b; font-size: 1.3rem;">📋</span> دفتر الأستاذ / القيود المحاسبية</h5>
            <div class="btn-group-custom">
                <a href="{{ route('journal.index', array_merge(request()->except('page', 'type'), ['type' => 'manual'])) }}" 
                   class="btn btn-sm {{ request('type', 'all') == 'manual' ? 'btn-primary-custom' : 'btn-outline-custom' }}">
                    ✏️ يدوية
                </a>
                <a href="{{ route('journal.index', array_merge(request()->except('page', 'type'), ['type' => 'auto'])) }}" 
                   class="btn btn-sm {{ request('type') == 'auto' ? 'btn-primary-custom' : 'btn-outline-custom' }}">
                    🤖 تلقائية
                </a>
                <a href="{{ route('journal.index', array_merge(request()->except('page', 'type'), ['type' => 'all'])) }}" 
                   class="btn btn-sm {{ request('type', 'all') == 'all' ? 'btn-primary-custom' : 'btn-outline-custom' }}">
                    📋 الكل
                </a>
            </div>
        </div>
        <a href="{{ route('journal.create') }}" class="btn-add">+ قيد يدوي جديد</a>
    </div>

    {{-- بحث متقدم --}}
    <div class="search-section mb-4">
        <form method="GET" action="{{ route('journal.index') }}" class="search-form">
            <input type="hidden" name="type" value="{{ request('type', 'manual') }}">
            <div class="search-group">
                <label>🔍 بحث بـ</label>
                <select name="search_by" id="searchBySelect">
                    <option value="">-- اختر معيار --</option>
                    <option value="id" {{ request('search_by') == 'id' ? 'selected' : '' }}>رقم القيد</option>
                    <option value="reference" {{ request('search_by') == 'reference' ? 'selected' : '' }}>رقم المرجع</option>
                    <option value="status" {{ request('search_by') == 'status' ? 'selected' : '' }}>الحالة</option>
                    <option value="created_by" {{ request('search_by') == 'created_by' ? 'selected' : '' }}>بواسطة</option>
                </select>
            </div>
            <div class="search-group">
                <label>✏️ القيمة</label>
                <input type="text" name="search_value" value="{{ request('search_value') }}" placeholder="أدخل قيمة البحث...">
            </div>
            <div class="search-group">
                <label>📅 من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="search-group">
                <label>📅 إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="search-actions">
                <button type="submit" class="btn-search">🔍 بحث</button>
                <a href="{{ route('journal.index', ['type' => request('type', 'manual')]) }}" class="btn-reset">🗑️ مسح الكل</a>
            </div>
        </form>
    </div>

    {{-- عرض الفلاتر النشطة --}}
    @php
        $activeFilters = [];
        if(request('search_by') && request('search_value')) {
            $map = ['id'=>'رقم القيد', 'reference'=>'المرجع', 'status'=>'الحالة', 'created_by'=>'بواسطة'];
            $activeFilters[] = "🔍 " . ($map[request('search_by')] ?? request('search_by')) . " = " . request('search_value');
        }
        if(request('date_from')) $activeFilters[] = "📅 من " . \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y');
        if(request('date_to')) $activeFilters[] = "📅 إلى " . \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y');
        if(request()->has('type') && request('type') != 'all') $activeFilters[] = "🏷️ نوع القيد: " . (request('type')=='manual' ? 'يدوية' : 'غير يدوية');
    @endphp
    @if(count($activeFilters))
        <div class="active-filters">
            <strong>الفلاتر النشطة:</strong> {{ implode(' | ', $activeFilters) }}
            <a href="{{ route('journal.index') }}" style="margin-right: 12px; color: #dc2626;">✖️ إلغاء الكل</a>
        </div>
    @endif

    {{-- رسائل نجاح --}}
    @if(session('success'))
        <div class="alert-success-custom">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert-success-custom" style="background: #fee2e2; border-color: #fca5a5; color: #b91c1c;">
            {{ session('error') }}
        </div>
    @endif

    {{-- قائمة القيود --}}
    @forelse($entries as $entry)
    @php
        $totalDebit = $entry->lines->sum('debit');
        $totalCredit = $entry->lines->sum('credit');
        $sourceLabel = match($entry->source_type) {
            \App\Models\Booking::class => '🎫 حجز',
            \App\Models\Payment::class => '💳 دفعة',
            \App\Models\Discount::class => '🏷️ خصم',
            null => '✏️ يدوي',
            default => '📋 أخرى',
        };
        $bookingDetails = null;
        if($entry->source_type === \App\Models\Booking::class && $entry->source_id) {
            $booking = \App\Models\Booking::withTrashed()->find($entry->source_id);
            if($booking) $bookingDetails = $booking;
        }
    @endphp
    <div class="journal-card" data-entry-id="{{ $entry->id }}">
        {{-- رأس البطاقة (قابل للنقر) --}}
        <div class="card-header-row {{ $entry->status == 'draft' ? 'draft-border' : 'posted-border' }}" onclick="toggleLines({{ $entry->id }})">
            <div class="entry-basic-info">
                <span class="toggle-icon" id="icon-{{ $entry->id }}">▶</span>
                <span class="entry-id">#{{ $entry->id }}</span>
                <span class="ref-badge">{{ $entry->reference }}</span>
                <span class="source-badge">{{ $sourceLabel }}</span>
                <div class="date-meta">
                    <span>📅 القيد: {{ $entry->entry_date->format('d/m/Y') }}</span>
                    <span>🕒 الإنشاء: {{ $entry->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <span class="status-badge {{ $entry->status == 'draft' ? 'status-draft' : 'status-posted' }}">
                    {{ $entry->status == 'draft' ? '⏳ غير معتمد' : '✅ معتمد' }}
                </span>
            </div>
            <div class="action-buttons" onclick="event.stopPropagation()">
                @if($entry->status === 'draft')
                    <form method="POST" action="{{ route('journal.approve', $entry->id) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-sm-icon btn-approve-sm">✅ اعتماد</button>
                    </form>
                    <form method="POST" action="{{ route('journal.cancel', $entry->id) }}" 
                          onsubmit="return confirm('{{ $entry->source_type === \App\Models\Booking::class ? '⚠️ سيتم حذف الحجز المرتبط! هل أنت متأكد؟' : 'إلغاء القيد؟' }}')" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-sm-icon btn-cancel-sm">❌ إلغاء</button>
                    </form>
                @endif
                @if($entry->status === 'posted' && $entry->reversals_count == 0 && (is_null($entry->source_type) || $entry->source_type === 'manual'))
                    <form method="POST" action="{{ route('journal.reverse', $entry->id) }}" style="display:inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-sm-icon" onclick="return confirm('عكس القيد؟ سيتم إنشاء قيد عكسي.')">🔄 عكس</button>
                    </form>
                @endif
                 @if(in_array($entry->source_type, ['receipt', 'payment', \App\Models\Payment::class]))
                    <a href="{{ route('vouchers.show', $entry->id) }}" class="btn-sm-icon">🧾 الإيصال</a>
                @endif
                <a href="{{ route('journal.history', $entry->id) . '?' . http_build_query(request()->except('page')) }}" class="btn-sm-icon">📜 السجل</a>
               
            </div>
        </div>

        {{-- تفاصيل الحجز إن وجدت --}}
        @if($bookingDetails)
        <div class="booking-info">
            <span><strong>📋 الحجز:</strong> {{ $bookingDetails->client_name }}</span>
            <span><strong>🚍 الرحلة:</strong> {{ $bookingDetails->trip->name ?? '—' }}</span>
            <span><strong>🏠 التسكين:</strong> {{ $bookingDetails->accommodation_type }}</span>
            <span><strong>💰 السعر:</strong> {{ number_format($bookingDetails->base_price, 2) }} ج.م</span>
        </div>
        @endif

        {{-- أسطر القيد (يتم عرضها بالضغط) --}}
        <div class="lines-wrapper" id="lines-{{ $entry->id }}">
            <table class="inner-table">
                <thead>
                    <tr><th>الحساب</th><th>مدين</th><th>دائن</th><th>البيان</th></tr>
                </thead>
                <tbody>
                    @foreach($entry->lines as $line)
                    <tr>
                        <td>{{ $line->account->code ?? '' }} - {{ $line->account->name ?? 'بدون حساب' }}</td>
                        <td class="debit-number">{{ $line->debit > 0 ? number_format($line->debit, 2) . ' ج.م' : '—' }}</td>
                        <td class="credit-number">{{ $line->credit > 0 ? number_format($line->credit, 2) . ' ج.م' : '—' }}</td>
                        <td>{{ $line->description ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td><strong>الإجمالي</strong></td>
                        <td class="debit-number">{{ number_format($totalDebit, 2) }} ج.م</td>
                        <td class="credit-number">{{ number_format($totalCredit, 2) }} ج.م</td>
                        <td>@if(abs($totalDebit - $totalCredit) < 0.01) <span style="color:#059669;">✅ متوازن</span> @else <span style="color:#dc2626;">❌ غير متوازن</span> @endif</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @empty
        <div class="empty-card">
            🧾 لا توجد قيود محاسبية مطابقة لمعايير البحث
        </div>
    @endforelse

    {{-- التصفح --}}
    <div class="pagination-wrap">
        {{ $entries->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
    function toggleLines(entryId) {
        const linesDiv = document.getElementById('lines-' + entryId);
        const iconSpan = document.getElementById('icon-' + entryId);
        if (!linesDiv || !iconSpan) return;
        
        if (linesDiv.classList.contains('show')) {
            linesDiv.classList.remove('show');
            iconSpan.textContent = '▶';
            iconSpan.classList.remove('rotated');
        } else {
            linesDiv.classList.add('show');
            iconSpan.textContent = '▼';
            iconSpan.classList.add('rotated');
        }
    }

    // تلميحات البحث
    document.getElementById('searchBySelect')?.addEventListener('change', function() {
        const valInput = document.querySelector('input[name="search_value"]');
        const selected = this.value;
        if(selected === 'id') valInput.placeholder = 'مثال: 15';
        else if(selected === 'reference') valInput.placeholder = 'مثال: MAN-001';
        else if(selected === 'status') valInput.placeholder = 'معتمد أو غير معتمد';
        else if(selected === 'created_by') valInput.placeholder = 'اسم المستخدم';
        else valInput.placeholder = 'أدخل قيمة البحث...';
    });
    document.getElementById('searchBySelect')?.dispatchEvent(new Event('change'));
</script>
</x-app-layout>