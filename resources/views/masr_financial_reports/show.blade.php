@extends('layouts.app')
@section('title', 'تفاصيل التقرير المالي')
@section('content')
    <div class="container">
        <h3>تفاصيل تقرير ( {{ $report->title }} )</h3>
        {{-- الكاتب --}}
        <p><strong>الكاتب:</strong> {{ $report->creator->name }}</p>
        {{-- التاريخ --}}
        <p><strong>التاريخ:</strong> {{ $report->date }}</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>التكلفة</th>

                    <th>سعر البيع</th>

                    <th>الربح/الخسارة</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report->items as $item)
                    <tr>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->cost_amount }} <small>
                                {{ $item->cost_currency }}</small></td>
                        <td>{{ $item->sale_amount }} <small>{{ $item->sale_currency }}</small></td>
                        <td>{{ $item->sale_amount - $item->cost_amount }}</td>

                    </tr>
                @endforeach
            </tbody>

        </table>
        {{-- مجموع التكاليف والربح --}}
        @php
            $total_cost = $report->items->sum('cost_amount');
            $total_sale = $report->items->sum('sale_amount');
            $net_profit = $total_sale - $total_cost;
        @endphp
        <div class="alert alert-success">
            <strong>إجمالي التكلفة:</strong> {{ $total_cost }} &nbsp; |
            <strong>إجمالي البيع:</strong> {{ $total_sale }} &nbsp; |
            <strong>إجمالي الربح:</strong> {{ $net_profit }}
        </div>
        @if ($report->notes)
            <div class="alert alert-info mt-3">
                <strong>ملاحظات التقرير:</strong>
                {{-- <div>{{ $report->notes }}</div> --}}
                @php
                    // وظيفة لتحويل أي رابط في النص لأيقونة قابلة للضغط
                    function convertLinksToIcons($text)
                    {
                        // نمط الرابط
                        $pattern = '/(https?:\/\/[^\s<]+)/i';
                        // الاستبدال: أيقونة FontAwesome
                        $replace = '<a href="$1" target="_blank" rel="noopener" style="text-decoration:none; margin:0 3px;">
                        <i class="fas fa-link"></i>
                    </a>';
                        return preg_replace($pattern, $replace, e($text));
                    }
                @endphp

                <div>{!! convertLinksToIcons($report->notes) !!}</div>

            </div>
        @endif
        <a href="{{ route('admin.masr.financial-reports.index') }}" class="btn btn-secondary">رجوع</a>
        <a href="{{ route('admin.masr.financial-reports.edit', $report->id) }}" class="btn btn-primary">تعديل التقرير</a>
    </div>
@endsection
