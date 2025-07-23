@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>تعديل تقرير</h3>
      
        <form method="POST" action="{{ route('admin.masr.financial-reports.update', $report->id) }}">
            @csrf
            @method('PUT')
              <div class="mb-3">
            <label>عنوان التقرير</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $report->title ?? '') }}" required>
        </div>
            <div id="items">
                @foreach ($report->items as $index => $item)
                    <div class="item row mb-3">
                        <div class="col">
                            <label>العنوان</label>
                            <input type="text" name="items[{{ $index }}][title]" class="form-control"
                                value="{{ $item->title }}" required>
                        </div>
                        <div class="col">
                            <label>التكلفة</label>
                            <input type="number" step="0.01" name="items[{{ $index }}][cost_amount]"
                                class="form-control" value="{{ $item->cost_amount }}" required>
                        </div>
                        <div class="col">
                            <label>عملة التكلفة</label>
                            <select name="items[{{ $index }}][cost_currency]" class="form-control">
                                @foreach ($currencies as $key => $val)
                                    <option value="{{ $key }}" @if ($item->cost_currency == $key) selected @endif>
                                        {{ $val }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label>سعر البيع</label>
                            <input type="number" step="0.01" name="items[{{ $index }}][sale_amount]"
                                class="form-control" value="{{ $item->sale_amount }}">
                        </div>
                        <div class="col">
                            <label>عملة البيع</label>
                            <select name="items[{{ $index }}][sale_currency]" class="form-control">
                                @foreach ($currencies as $key => $val)
                                    <option value="{{ $key }}" @if ($item->sale_currency == $key) selected @endif>
                                        {{ $val }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-item"
                                @if ($loop->first) style="display:none;" @endif>حذف</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mb-3">
                <label>ملاحظات التقرير</label>
                <textarea name="notes" class="form-control" rows="3">{{ $report->notes }}</textarea>
            </div>
            <button type="button" id="addItem" class="btn btn-info mb-3">إضافة بند آخر</button>
            <div class="mb-3">
                <label>التاريخ</label>
                <input type="date" name="date" class="form-control" value="{{ $report->date }}" required>
            </div>
            <button type="submit" class="btn btn-primary">تحديث التقرير</button>
            <a href="{{ route('admin.masr.financial-reports.index') }}" class="btn btn-secondary">رجوع</a>
        </form>
    </div>
    <script>
        let itemIndex = {{ $report->items->count() }};
        document.getElementById('addItem').onclick = function() {
            let itemsDiv = document.getElementById('items');
            let newItem = itemsDiv.firstElementChild.cloneNode(true);
            newItem.querySelectorAll('input, select').forEach(function(el) {
                let name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/\d+/, itemIndex));
                    el.value = '';
                }
            });
            newItem.querySelector('.remove-item').style.display = 'inline-block';
            itemsDiv.appendChild(newItem);
            itemIndex++;
        };
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('.item').remove();
            }
        });
    </script>
@endsection
