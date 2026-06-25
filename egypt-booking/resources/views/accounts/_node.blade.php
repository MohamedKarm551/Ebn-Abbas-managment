@php
    $childrenCollection = isset($isSearching) && $isSearching 
        ? ($account->filteredChildren ?? collect()) 
        : ($account->allChildren ?? collect());
    
    $hasChildren = $childrenCollection->isNotEmpty();
    $colors=['asset'=>'#f59e0b','liability'=>'#ef4444',
             'equity'=>'#8b5cf6','revenue'=>'#10b981','expense'=>'#3b82f6'];
    $typeNames=['asset'=>'أصول','liability'=>'خصوم',
                'equity'=>'ملكية','revenue'=>'إيرادات','expense'=>'مصروفات'];
    $balance = $account->getTotalBalance();
    $pad = $level * 24;
    $hasChildren = $account->allChildren->isNotEmpty();
    $safeCode = str_replace('.', '_', $account->code);
    $safeParentCode = $account->parent ? str_replace('.', '_', $account->parent->code) : '';
@endphp
<tr style="border-bottom:1px solid #f0f0f0;"
    @if($level > 0) class="children-of-{{ $safeParentCode }}" @endif
    data-code="{{ $safeCode }}">
    <td style="padding:10px;color:#6b7280;font-size:12px;">
        {{ $account->code }}
    </td>
    <td style="padding:10px;padding-right:{{ $pad + 12 }}px;">
        @if($hasChildren)
            <button id="toggle-{{ $safeCode }}"
                    class="toggle-btn"
                    onclick="toggleChildren('{{ $safeCode }}')"
                    style="background:none; border:none; cursor:pointer; margin-left:5px; font-size:12px; color:#6b7280;">
                ▼
            </button>
        @else
            <span style="display:inline-block; width:18px;"></span>
        @endif
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                     background:{{ $colors[$account->type] }};margin-left:8px;"></span>
        <strong style="font-weight:{{ $level==0?700:($level==1?600:400) }};">
            {{ $account->name }}
        </strong>
    </td>
    <td style="padding:10px;">
        <span style="background:{{ $colors[$account->type] }}22;
                     color:{{ $colors[$account->type] }};
                     padding:3px 10px;border-radius:20px;font-size:12px;">
            {{ $typeNames[$account->type] }}
        </span>
    </td>
    <td style="padding:10px;font-weight:bold;
               color:{{ $balance>0?'#059669':($balance<0?'#dc2626':'#9ca3af') }}">
        {{ number_format(abs($balance), 2) }} ج.م
        @if($balance != 0)
            <small>({{ $balance > 0 ? 'مدين' : 'دائن' }})</small>
        @endif
    </td>
    <td style="padding:10px;text-align:center;white-space:nowrap;">
         @if($account->is_leaf)
        <a href="{{ route('accounts.ledger', $account) }}" class="btn-view"
         style="background:#2563eb;color:white;padding:4px 10px;
                  border-radius:4px;font-size:12px;text-decoration:none;
                  display:inline-block;margin-left:4px;">
            📊
        </a>
        @endif
        
        @if($account->is_leaf)
        <form method="POST"
              action="{{ route('accounts.toggle-freeze', $account) }}"
              style="display:inline;"
              onsubmit="return confirm('{{ $account->is_active ? 'تجميد' : 'تفعيل' }} الحساب؟')">
            @csrf @method('PATCH')
            <button type="submit"
                title="{{ $account->is_active ? 'تجميد الحساب' : 'تفعيل الحساب' }}"
                style="background:{{ $account->is_active ? '#fee2e2' : '#d1fae5' }};
                       color:{{ $account->is_active ? '#dc2626' : '#059669' }};
                       padding:4px 10px;border:none;border-radius:4px;
                       font-size:12px;cursor:pointer;">
                {{ $account->is_active ? '🔒' : '🔓' }}
            </button>
        </form>
        @endif
        <a href="{{ route('accounts.edit', $account) }}"
           style="background:#fef3c7;color:#92400e;padding:4px 10px;
                  border-radius:4px;font-size:12px;text-decoration:none;
                  display:inline-block;margin-left:4px;">
            ✏️
        </a>
    </td>
</tr>

@foreach($childrenCollection->sortBy('code') as $child)
    @include('accounts._node', [
        'account' => $child,
        'level' => $level + 1,
        'isSearching' => $isSearching ?? false
    ])
@endforeach