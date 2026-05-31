@foreach($accounts as $account)
@php
    $balance = $account->getTotalBalance();
    $balanceText = $balance > 0 ? number_format($balance,2).' مدين' : ($balance < 0 ? number_format(abs($balance),2).' دائن' : '0.00');
    $typeName = ['asset'=>'أصول','liability'=>'خصوم','equity'=>'حقوق ملكية','revenue'=>'إيرادات','expense'=>'مصروفات'][$account->type] ?? $account->type;
    $parentInfo = $account->parent ? $account->parent->code.' - '.$account->parent->name : '—';
    $padding = $level * 20;
@endphp
<tr class="level-{{ $level }}">
    <td>{{ $account->code }}</td>
    <td style="padding-right: {{ $padding }}px;">{{ str_repeat('—', $level) }} {{ $account->name }} @if(!$account->is_active) (مجمد) @endif</td>
    <td>{{ $typeName }}</td>
    <td>{{ $parentInfo }}</td>
    <td >{{ $balanceText }}</td>
</tr>
@if($account->allChildren->isNotEmpty())
    @include('accounts._print_tree_recursive', ['accounts' => $account->allChildren, 'level' => $level + 1])
@endif
@endforeach