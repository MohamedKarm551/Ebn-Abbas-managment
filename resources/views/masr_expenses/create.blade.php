@extends('layouts.app')

@section('title', 'إضافة تقرير مصاريف')

@section('content')
<div class="container">
    <h3>إضافة تقرير مصاريف</h3>
    <form method="POST" action="{{ route('admin.masr_expenses.store') }}">
        @csrf
        <div class="mb-3">
            <label>عنوان التقرير</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>التاريخ</label>
            <input type="date" name="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>ملاحظات التقرير</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        <hr>
        <h5>بنود المصاريف</h5>
        <div id="items">
            <div class="item row mb-2">
                <div class="col">
                    <input type="text" name="items[0][title]" class="form-control" placeholder="عنوان البند" required>
                </div>
                <div class="col">
                    <input type="number" step="0.01" name="items[0][amount]" class="form-control" placeholder="المبلغ" required>
                </div>
                <div class="col">
                    <select name="items[0][currency]" class="form-control">
                        @foreach($currencies as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-item" style="display:none;">حذف</button>
                </div>
            </div>
        </div>
        <button type="button" id="addItem" class="btn btn-info mb-3">إضافة بند آخر</button>
        <button type="submit" class="btn btn-success">حفظ التقرير</button>
    </form>
</div>
<script>
let itemIndex = 1;
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