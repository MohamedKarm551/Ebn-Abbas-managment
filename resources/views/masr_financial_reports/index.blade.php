{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\masr_financial_reports\index.blade.php --}}
@extends('layouts.app')
@section('content')
    <div class="container">
        <h2>تقارير أرباح ومصروفات شركة مصر</h2>
        <a href="{{ route('admin.masr.financial-reports.create') }}" class="btn btn-success mb-3">إضافة تقرير جديد</a>
        <form id="filterForm" class="mb-3">
            <label>من: <input type="date" name="from"></label>
            <label>إلى: <input type="date" name="to"></label>
            <button type="submit" class="btn btn-primary">فلترة</button>
        </form>
        <div class="mb-3 d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="date_desc">الأحدث</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="date_asc">الأقدم</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="profit_desc">الأعلى ربحاً</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="profit_asc">الأقل ربحاً</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="cost_asc">الأقل تكلفة</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="cost_desc">الأعلى تكلفة</button>
        </div>
        @php
            // إذا كان $reports هو Paginator (عند أول تحميل الصفحة)
            $allItems = collect(method_exists($reports, 'items') ? $reports->items() : $reports)->flatMap(function (
                $report,
            ) {
                return $report->items;
            });
            $total_cost = $allItems->sum('cost_amount');
            $total_sale = $allItems->sum('sale_amount');
            $net_profit = $total_sale - $total_cost;
        @endphp
        <div id="defaultTotals" class="alert alert-success mb-3">
            <strong>إجمالي التكلفة:</strong> {{ $total_cost }} &nbsp; |
            <strong>إجمالي البيع:</strong> {{ $total_sale }} &nbsp; |
            <strong>إجمالي الربح:</strong> {{ $net_profit }}
        </div>
        <div class="table-responsive-sm">
            <table class="table table-bordered table-sm align-middle text-center mb-0">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-date">تاريخ التقرير</th>
                        <th class="col-count">العنوان </th>
                        <th class="col-cost">التكلفة</th>
                        <th class="col-sale">البيع</th>
                        <th class="col-profit">الربح</th>
                        <th class="col-actions">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        @php
                            $total_cost = $report->items->sum('cost_amount');
                            $total_sale = $report->items->sum('sale_amount');
                            $net_profit = $total_sale - $total_cost;
                        @endphp
                        <tr>
                            <td class="col-num">{{ $report->id }}</td>
                            <td class="col-date" style="width: 110px">{{ $report->date }}</td>
                            <td class="col-created_by" style="width:130px;">{{ $report->title }} <div class="mb-3">
                                    <small class="text-muted"> {{ $report->creator->name }}</small>
                                </div>
                            </td>
                            <td class="col-cost">{{ $total_cost }}</td>
                            <td class="col-sale">{{ $total_sale }}</td>
                            <td class="col-profit">{{ $net_profit }}</td>
                            <td class="col-actions p-1" style="width: 25%">
                                <a href="{{ route('admin.masr.financial-reports.show', $report->id) }}"
                                    class="btn btn-sm btn-info mb-1">عرض</a>
                                <a href="{{ route('admin.masr.financial-reports.edit', $report->id) }}"
                                    class="btn btn-sm btn-warning mb-1">تعديل</a>
                                @if (Auth::user()->role === 'Admin')
                                    <form action="{{ route('admin.masr.financial-reports.destroy', $report->id) }}"
                                        method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mb-1">حذف</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div id="filterTotals" class="alert alert-info mt-3" style="display:none"></div>

        </div>
        @if (method_exists($reports, 'hasPages') && $reports->hasPages())
            <div class="pagination-container my-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-2 gap-2">
                    <!-- معلومات الترقيم -->
                    <div class="pagination-info order-2 order-md-1 text-center text-md-start">
                        <p class="mb-0">
                            عرض
                            <strong>{{ $reports->firstItem() }}</strong>
                            إلى
                            <strong>{{ $reports->lastItem() }}</strong>
                            من
                            <strong>{{ $reports->total() }}</strong>
                            تقرير
                        </p>
                    </div>
                    <!-- الترقيم نفسه -->
                    <nav class="order-1 order-md-2">
                        {{ $reports->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @endif

    </div>
    <script>
        document.getElementById('filterForm').onsubmit = function(e) {
            e.preventDefault();
            let from = this.from.value;
            let to = this.to.value;
            fetch('{{ route('admin.masr.financial-reports.filter') }}?from=' + from + '&to=' + to)
                .then(res => res.json())
                .then(data => {
                    let tbody = document.querySelector('table tbody');
                    tbody.innerHTML = '';
                    data.reports.forEach(function(report) {
                        let total_cost = 0,
                            total_sale = 0;
                        report.items.forEach(function(item) {
                            total_cost += parseFloat(item.cost_amount);
                            total_sale += parseFloat(item.sale_amount || 0);
                        });
                        let net_profit = total_sale - total_cost;
                        let tr = document.createElement('tr');
                        tr.innerHTML = `
                    <td>${report.id}</td>
                    <td>${report.date}</td>
                    <td>${report.creator.name}</td>
                    <td>${total_cost}</td>
                    <td>${total_sale}</td>
                    <td>${net_profit}</td>
                    <td>
                        <a href="/admin/masr-financial-reports/${report.id}" class="btn btn-sm btn-info">عرض التفاصيل</a>
                        <a href="/admin/masr-financial-reports/${report.id}/edit" class="btn btn-sm btn-warning">تعديل</a>
                    </td>
                `;
                        tbody.appendChild(tr);
                    });

                    // عرض الإجماليات في div
                    let totalsDiv = document.getElementById('filterTotals');
                    totalsDiv.style.display = 'block';
                    totalsDiv.innerHTML = `
                <strong>إجمالي التكلفة:</strong> ${data.total_cost} &nbsp; | 
                <strong>إجمالي البيع:</strong> ${data.total_sale} &nbsp; | 
                <strong>إجمالي الربح:</strong> ${data.net_profit}
            `;
                });
        };
    </script>
    <script>
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                let sort = this.dataset.sort;
                // أضف هنا متغيرات الفلترة لو كانت موجودة (from/to)
                let url = `{{ route('admin.masr.financial-reports.index') }}?sort=${sort}`;
                window.location.href = url; // Reload عادي
            });
        });
    </script>
@endsection
