@php $padding = $level * 20; @endphp
<tr style="{{ $level === 0 ? 'background:#f0f0f0; font-weight:bold;' : ($level === 1 ? 'background:#f9f9f9; font-weight:600;' : '') }}">
    <td><code>{{ $account->code }}</code></td>
    <td style="padding-right: {{ $padding }}px">
        {{ $account->is_leaf ? '' : '▶ ' }}{{ $account->name }}
    </td>
    <td class="text-success">{{ number_format($account->total_debit, 2) }}</td>
    <td class="text-danger">{{ number_format($account->total_credit, 2) }}</td>
    <td class="{{ $account->balance >= 0 ? 'text-success' : 'text-danger' }}">
        {{ number_format(abs($account->balance), 2) }}
        <small>{{ $account->balance >= 0 ? 'مدين' : 'دائن' }}</small>
    </td>
</tr>
@if(!$account->is_leaf && $account->children_data->isNotEmpty())
    @foreach($account->children_data as $child)
        @include('financial-reports._trial_balance_node', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif