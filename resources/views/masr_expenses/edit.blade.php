@extends('layouts.app')
@section('title', 'تعديل تقرير مصاريف')
@section('content')
<div class="container">
    <h3>تعديل تقرير مصاريف</h3>
    <form method="POST" action="{{ route('admin.masr_expenses.update', $masr_expense->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>عنوان التقرير</label>
            <input type="text" name="title" class="form-control" value="{{ $masr_expense->title }}" required>
        </div>
        <div class="mb-3">
            <label>التاريخ</label>
            <input type="date" name="date" class="form-control" value="{{ $masr_expense->date }}" required>
        </div>
        <div class="mb-3">
            <label>ملاحظات التقرير</label>
            <textarea name="notes" class="form-control" rows="2">{{ $masr_expense->notes }}</textarea>
        </div>
        <hr>
        <h5>بنود المصاريف</h5>
        <div id="items">
            @foreach($masr_expense->items as $index => $item)
            <div class="item row mb-2">
                <div class="col">
                    <input type="text" name="items[{{ $index }}][title]" class="form-control" value="{{ $item->title }}" required>
                </div>
                <div class="col">
                    <input type="number" step="0.01" name="items[{{ $index }}][amount]" class="form-control" value="{{ $item->amount }}" required>
                </div>
                <div class="col">
                    <select name="items[{{ $index }}][currency]" class="form-control">
                        @foreach($currencies as $key => $val)
                            <option value="{{ $key }}" @if($item->currency == $key) selected @endif>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-item" @if($loop->first) style="display:none;" @endif>حذف</button>
                </div>
            </div>
            @endforeach
        </div>
        <button type="button" id="addItem" class="btn btn-info mb-3">إضافة بند آخر</button>
        <button type="submit" class="btn btn-success">تحديث التقرير</button>
    </form>
</div>
<script>
let itemIndex = {{ $masr_expense->items->count() }};
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