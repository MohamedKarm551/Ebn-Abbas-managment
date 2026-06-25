@php
    $typeNames = ['asset'=>'أصول','liability'=>'خصوم','equity'=>'ملكية','revenue'=>'إيرادات','expense'=>'مصروفات'];
    $balance   = $account->getTotalBalance();
    $pad       = $level * 15;
    $children  = $account->allChildren ?? collect();
@endphp
<tr class="level-{{ min($level, 2) }}">
    <td style="text-align:center;">{{ $account->code }}</td>
    <td style="padding-right:{{ $pad + 8 }}px;">{{ $account->name }}</td>
    <td style="text-align:center;">{{ $typeNames[$account->type] ?? '' }}</td>
    <td style="text-align:center;" class="{{ $balance > 0 ? 'balance-positive' : ($balance < 0 ? 'balance-negative' : '') }}">
        {{ number_format(abs($balance), 2) }} ج.م
        @if($balance != 0) ({{ $balance > 0 ? 'مدين' : 'دائن' }}) @endif
    </td>
</tr>
@foreach($children->sortBy('code') as $child)
    @include('exports._accounts_pdf_node', ['account' => $child, 'level' => $level + 1])
@endforeach