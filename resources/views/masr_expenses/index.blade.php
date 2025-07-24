@extends('layouts.app')
@section('title', 'تقارير المصاريف')
@section('content')
    <div class="container">
        <h3>كل تقارير المصاريف</h3>
        <a href="{{ route('admin.masr_expenses.create') }}" class="btn btn-success mb-3">إضافة تقرير مصاريف</a>
        <form id="filterForm" class="mb-3">
            <label>من: <input type="date" name="from"></label>
            <label>إلى: <input type="date" name="to"></label>
            <button type="submit" class="btn btn-primary">فلترة</button>
        </form>
        <div id="filterTotals" class="alert alert-info mt-3" style="display:none"></div>

        <div class="mb-3 d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="date_desc">الأحدث</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="date_asc">الأقدم</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="cost_desc">الأعلى تكلفة</button>
            <button type="button" class="btn btn-outline-secondary sort-btn" data-sort="cost_asc">الأقل تكلفة</button>
        </div>
        {{-- تصدير اكسيل --}}
        <button type="button" class="btn btn-success mb-3" onclick="exportMasrExpensesToExcel()">تصدير كل التقارير إلى
            Excel</button>

        <table class="table table-bordered" id="masr-expenses-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العنوان</th>
                    <th>التاريخ</th>
                    <th>إجمالي المصاريف</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                    @php
                        $total = $expense->items->sum('amount');
                    @endphp
                    <tr>
                        <td style="width: 50px;">{{ $loop->iteration }}</td>
                        <td>
                            {{ $expense->title }}
                            <div class="mb-1">
                                <small class="text-muted">{{ $expense->creator->name ?? '-' }}</small>
                            </div>
                        </td>
                        <td>{{ $expense->date }}</td>
                        <td>{{ $total }}</td>
                        <td>
                            <a href="{{ route('admin.masr_expenses.show', $expense->id) }}"
                                class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.masr_expenses.edit', $expense->id) }}"
                                class="btn btn-warning btn-sm">تعديل</a>
                            <form action="{{ route('admin.masr_expenses.destroy', $expense->id) }}" method="POST"
                                style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- إجمالي المصاريف --}}
        @php
            $allItems = collect(method_exists($expenses, 'items') ? $expenses->items() : $expenses)->flatMap(function (
                $expense,
            ) {
                return $expense->items;
            });
            $total_expenses = $allItems->sum('amount');
        @endphp
        <div class="alert alert-info">
            <strong>إجمالي المصاريف:</strong> {{ $total_expenses }}
        </div>
        {{-- Pagination links --}}
        @if (method_exists($expenses, 'hasPages') && $expenses->hasPages())
            <div class="pagination-container my-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-2 gap-2">
                    <!-- معلومات الترقيم -->
                    <div class="pagination-info order-2 order-md-1 text-center text-md-start">
                        <p class="mb-0">
                            عرض
                            <strong>{{ $expenses->firstItem() }}</strong>
                            إلى
                            <strong>{{ $expenses->lastItem() }}</strong>
                            من
                            <strong>{{ $expenses->total() }}</strong>
                            تقرير
                        </p>
                    </div>
                    <!-- الترقيم نفسه -->
                    <nav class="order-1 order-md-2">
                        {{ $expenses->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @endif

    </div>
    {{--  --}}
    <script>
        document.getElementById('filterForm').onsubmit = function(e) {
            e.preventDefault();
            let from = this.from.value;
            let to = this.to.value;
            fetch('{{ route('admin.masr_expenses.filter') }}?from=' + from + '&to=' + to)
                .then(res => res.json())
                .then(data => {
                    let tbody = document.querySelector('table tbody');
                    tbody.innerHTML = '';
                    data.expenses.forEach(function(expense) {
                        let total = expense.items.reduce((sum, item) => sum + parseFloat(item.amount), 0);
                        let tr = document.createElement('tr');
                        tr.innerHTML = `
                    <td>${expense.id}</td>
                    <td>${expense.title}<div class="mb-1"><small class="text-muted">${expense.creator ? expense.creator.name : '-'}</small></div></td>
                    <td>${expense.date}</td>
                    <td>${total}</td>
                    <td>
                        <a href="/admin/masr_expenses/${expense.id}" class="btn btn-info btn-sm">عرض</a>
                        <a href="/admin/masr_expenses/${expense.id}/edit" class="btn btn-warning btn-sm">تعديل</a>
                    </td>
                `;
                        tbody.appendChild(tr);
                    });
                    let totalsDiv = document.getElementById('filterTotals');
                    totalsDiv.style.display = 'block';
                    totalsDiv.innerHTML = `<strong>إجمالي المصاريف المفلترة:</strong> ${data.total_expenses}`;
                });
        };

        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                let sort = this.dataset.sort;
                let url = `{{ route('admin.masr_expenses.index') }}?sort=${sort}`;
                window.location.href = url;
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        function exportMasrExpensesToExcel() {
            const table = document.getElementById('masr-expenses-table');
            if (!window.XLSX) {
                alert('لم يتم تحميل مكتبة XLSX. تأكد من تضمين المكتبة في الصفحة.');
                return;
            }

            // استنساخ الجدول فقط
            let cloneTable = table.cloneNode(true);

            // موقع عمود الإجراءات (الأخير)
            const actionsColIdx = cloneTable.rows[0].cells.length - 1;

            // أضف رأس عمود "الموظف" بعد العنوان
            let th = document.createElement('th');
            th.textContent = 'الموظف';
            cloneTable.rows[0].insertBefore(th, cloneTable.rows[0].cells[2]); // بعد العنوان

            // احذف خلية الإجراءات من thead
            cloneTable.rows[0].deleteCell(actionsColIdx + 1);

            // عدّل كل صف: أخرج اسم الموظف من داخل خلية العنوان وضعه كعمود منفصل
            for (let i = 1; i < cloneTable.rows.length; i++) {
                let row = cloneTable.rows[i];
                if (row.cells.length < actionsColIdx) continue;

                let titleCell = row.cells[1];
                // ابحث عن العنصر <small class="text-muted"> (اسم الموظف)
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = titleCell.innerHTML;
                let empElem = tempDiv.querySelector('small.text-muted');
                let employee = '';
                if (empElem) {
                    employee = empElem.textContent.trim();
                    empElem.remove(); // حذف اسم الموظف من العنوان
                }
                // عدل نص العنوان ليكون فقط العنوان (بدون الموظف)
                titleCell.innerHTML = tempDiv.textContent.trim();

                // أنشئ خلية الموظف وضعها بعد العنوان
                let empTd = document.createElement('td');
                empTd.textContent = employee;
                row.insertBefore(empTd, row.cells[2]);

                // احذف خلية الإجراءات (زود 1 لأننا أضفنا عمود)
                if (row.cells.length > actionsColIdx + 1) {
                    row.deleteCell(actionsColIdx + 1);
                }
            }

            // التصدير
            let wb = XLSX.utils.table_to_book(cloneTable, {
                sheet: "تقارير المصاريف"
            });
            const fileName = `تقارير المصاريف_${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }
    </script>




@endsection
