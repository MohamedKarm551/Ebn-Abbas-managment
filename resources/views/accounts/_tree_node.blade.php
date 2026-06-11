{{-- resources/views/accounts/_tree_node.blade.php --}}
@php
    $childrenCollection = isset($isSearching) && $isSearching 
        ? ($account->filteredChildren ?? collect()) 
        : ($account->allChildren ?? collect());
    
    $hasChildren = $childrenCollection->isNotEmpty();
    
    $balance     = $account->getTotalBalance();
    $safeCode    = str_replace('.', '_', $account->code);
    $isFrozen    = !$account->is_active;

    $colors = [
        'asset'     => '#f59e0b',
        'liability' => '#ef4444',
        'equity'    => '#8b5cf6',
        'revenue'   => '#10b981',
        'expense'   => '#3b82f6',
    ];
    $color = $colors[$account->type] ?? '#6b7280';

    $typeNames = [
        'asset'     => 'أصول',
        'liability' => 'خصوم',
        'equity'    => 'حقوق ملكية',
        'revenue'   => 'إيرادات',
        'expense'   => 'مصروفات',
    ];

    $paddingRight = ($level * 22) + 10;
@endphp

<tr
    class="row-level-{{ $level }} {{ $level > 0 ? 'children-of-' . str_replace('.', '_', optional($account->parent)->code ?? '') : '' }} {{ $isFrozen ? 'row-frozen' : '' }}"
    data-code="{{ $safeCode }}"
    data-parent="{{ str_replace('.', '_', optional($account->parent)->code ?? '') }}"
    style="{{ $isFrozen ? 'opacity:0.6;' : '' }}"
>
    {{-- كود الحساب --}}
    <td style="color:#9ca3af; font-size:12px; text-align:center; padding-right:8px;">
        {{ $account->code }}
    </td>

    {{-- اسم الحساب --}}
    <td style="padding-right: {{ $paddingRight }}px;">
        <div style="display:flex; align-items:center; gap:4px;">

            @if($hasChildren)
                <button
                    class="toggle-btn"
                    id="toggle-{{ $safeCode }}"
                    onclick="toggleChildren('{{ $safeCode }}')"
                >▼</button>
            @else
                <span style="display:inline-block; width:24px;"></span>
            @endif

            <span class="account-dot" style="background:{{ $isFrozen ? '#9ca3af' : $color }};"></span>

            <span style="font-weight: {{ $level === 0 ? '700' : ($level === 1 ? '600' : '400') }}; {{ $isFrozen ? 'text-decoration:line-through; color:#9ca3af;' : '' }}">
                {{ $account->name }}
            </span>

            {{-- علامة التجميد --}}
            @if($isFrozen)
                <span title="هذا الحساب مجمد"
                      style="background:#fee2e2; color:#dc2626; font-size:10px; padding:1px 7px; border-radius:10px; font-weight:600;">
                    🔒 مجمد
                </span>
            @endif

            {{-- علامة الحساب التلقائي (شركة أو جهة حجز) --}}
            @if($account->description && str_contains($account->description, 'حساب الشركة:'))
                <span title="حساب عميل — مرتبط بالشركة"
                     >
                </span>
            @elseif($account->description && str_contains($account->description, 'حساب جهة الحجز:'))
                <span title="حساب مورد — مرتبط بجهة الحجز"
                      >
                </span>
            @endif
        </div>
    </td>

    {{-- النوع --}}
    <td>
        @if($level === 0)
            <span class="type-badge" style="background:{{ $color }}22; color:{{ $color }};">
                {{ $typeNames[$account->type] ?? $account->type }}
            </span>
        @endif
    </td>

    {{-- الفئة --}}
    <td style="color:#6b7280; font-size:12px;">
        @if($account->parent && $level > 0)
            {{ $account->parent->code }} - {{ $account->parent->name }}
        @else
            —
        @endif
    </td>

    {{-- الرصيد --}}
  {{-- الرصيد --}}
<td class="balance-cell">
    @php
        $absBalance = abs($balance);
        // قاعدة الألوان حسب طبيعة الحساب والإشارة
        if ($balance == 0) {
            $balanceClass = 'balance-zero';
        } else {
            if ($account->normal_balance === 'debit') {
                // الحسابات المدينة: الموجب أخضر، السالب أحمر
                $balanceClass = $balance > 0 ? 'balance-positive' : 'balance-negative';
            } else {
                // الحسابات الدائنة: الموجب أحمر، السالب أخضر
                $balanceClass = $balance > 0 ? 'balance-negative' : 'balance-positive';
            }
        }
    @endphp
    <span class="{{ $balanceClass }}">
        {{ number_format($absBalance, 0) }}
        <span class="currency-label">ر.س</span>
    </span>
    {{-- اختياري: إضافة توجيه نصي للدائن/المدين (يمكنك حذفه إذا لم ترده) --}}
    @if($balance != 0)
        <span class="balance-direction" style="font-size:10px; color:inherit;">
            ({{ ($balance > 0 && $account->normal_balance === 'debit') || ($balance < 0 && $account->normal_balance === 'credit') ? 'مدين' : 'دائن' }})
        </span>
    @endif
</td>

    {{-- الإجراءات --}}
    <td style="white-space:nowrap; text-align:center;" class="no-print">

        {{-- كشف حساب (فقط للحسابات النهائية) --}}
        @if($account->is_leaf)
            <a href="{{ route('accounts.ledger', $account) }}"
               style="background:#fef3c7; padding:3px 9px; border-radius:5px; font-size:11px;
                      text-decoration:none; color:#92400e; display:inline-flex; align-items:center; gap:3px; margin-left:4px;">
                📄 كشف حساب
            </a>
        @endif
    </td>
</tr>

{{-- الأبناء (مرتبين رقمياً) --}}
@if($hasChildren)
    @php
        // ترتيب الأبناء حسب الكود عدديًا (مثال: 1.1.3.1.1 ثم 1.1.3.1.2 ... ثم 1.1.3.1.10)
        $sortedChildren = $childrenCollection->sortBy(function($child) {
            return array_map('intval', explode('.', $child->code));
        });
    @endphp
    @foreach($sortedChildren as $child)
        @include('accounts._tree_node', [
            'account' => $child,
            'level'   => $level + 1,
            'isSearching' => $isSearching ?? false,
        ])
    @endforeach
@endif