@extends('layouts.app')
@section('title', 'إضافة تقرير مالي جديد')
@section('content')
    <div class="container">
        <h3>إضافة تقرير جديد</h3>
        <button type="button" class="btn btn-warning mb-3" id="addHotelBooking">إضافة تقرير خاص بالفنادق</button>
      
        <form method="POST" action="{{ route('admin.masr.financial-reports.store') }}">
            @csrf
              <div class="mb-3">
            <label>عنوان التقرير</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $report->title ?? '') }}" required>
        </div>
            <div id="items">
                <div class="item row mb-3">
                    <div class="col">
                        <label>العنوان</label>
                        <input type="text" name="items[0][title]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>التكلفة</label>
                        <input type="number" step="0.01" name="items[0][cost_amount]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>عملة التكلفة</label>
                        <select name="items[0][cost_currency]" class="form-control">
                            @foreach ($currencies as $key => $val)
                                <option value="{{ $key }}">{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <label>سعر البيع</label>
                        <input type="number" step="0.01" name="items[0][sale_amount]" class="form-control">
                    </div>
                    <div class="col">
                        <label>عملة البيع</label>
                        <select name="items[0][sale_currency]" class="form-control">
                            @foreach ($currencies as $key => $val)
                                <option value="{{ $key }}">{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-auto d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-item" style="display:none;">حذف</button>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label>ملاحظات التقرير</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <button type="button" id="addItem" class="btn btn-info mb-3">إضافة بند آخر</button>
            <div class="mb-3">
                <label>التاريخ</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">حفظ التقرير</button>
            <a href="{{ route('admin.masr.financial-reports.index') }}" class="btn btn-secondary">رجوع</a>
        </form>
    </div>
    <script>
        let itemIndex = 1;
        document.getElementById('addItem').onclick = function() {
            let itemsDiv = document.getElementById('items');
            let newItem = itemsDiv.firstElementChild.cloneNode(true);
            // تحديث أسماء الحقول
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
        // حذف البند
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('.item').remove();
            }
        });
    </script>
    <script>
        document.getElementById('addHotelBooking').onclick = function() {
            // جلب قائمة الحجوزات من السيرفر (AJAX)
            fetch('/admin/bookings/list')
                .then(res => res.json())
                .then(bookings => {
                    // بناء خيارات السليكت
                    let options = bookings.map(b => `<option value="${b.id}">${b.client_name}</option>`).join('');
                    let selectHtml = `<select id="bookingSelect" class="form-control mb-2">${options}</select>`;
                    let notes = document.querySelector('textarea[name="notes"]').value;

                    // بناء مودال بسيط
                    let modalHtml = `
                <div id="hotelModal" style="
                    background:#fff;
                    padding:20px;
                    border-radius:8px;
                    max-width:400px;
                    width:90vw;
                    position:fixed;
                    top:50%;
                    left:50%;
                    transform:translate(-50%,-50%);
                    z-index:9999;
                    box-shadow:0 0 20px rgba(0,0,0,0.2);
                ">
                    <h5>اختر الحجز</h5>
                    ${selectHtml}
                    <button class="btn btn-primary" id="fillHotelReport">تعبئة التقرير</button>
                </div>
                <div id="modalOverlay" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9998;"></div>
            `;
                    document.body.insertAdjacentHTML('beforeend', modalHtml);

                    // عند الضغط على تعبئة التقرير
                    document.getElementById('fillHotelReport').onclick = function() {
                        let bookingId = document.getElementById('bookingSelect').value;
                        fetch(`/admin/bookings/${bookingId}/info`)
                            .then(res => res.json())
                            .then(data => {
                                // تعبئة بيانات البند والعملات
                                let itemsDiv = document.getElementById('items');
                                itemsDiv.innerHTML = `
                            <div class="item row mb-3">
                                <div class="col">
                                    <label>العنوان</label>
                                    <input type="text" name="items[0][title]" class="form-control" value="حجز العميل: ${data.client_name}" required>
                                </div>
                                <div class="col">
                                    <label>التكلفة</label>
                                    <input type="number" step="0.01" name="items[0][cost_amount]" class="form-control" value="${data.cost}" required>
                                </div>
                                <div class="col">
                                    <label>عملة التكلفة</label>
                                    <select name="items[0][cost_currency]" class="form-control currency-select">
                                        @foreach ($currencies as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label>سعر البيع</label>
                                    <input type="number" step="0.01" name="items[0][sale_amount]" class="form-control" value="${data.sale}">
                                </div>
                                <div class="col">
                                    <label>عملة البيع</label>
                                    <select name="items[0][sale_currency]" class="form-control currency-select">
                                        @foreach ($currencies as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-item" style="display:none;">حذف</button>
                                </div>
                            </div>
                        `;

                                // تعيين العملة تلقائيًا بعد بناء العنصر (تلقائيًا من الداتا)
                                let costCurrencySelect = itemsDiv.querySelector(
                                    'select[name="items[0][cost_currency]"]');
                                let saleCurrencySelect = itemsDiv.querySelector(
                                    'select[name="items[0][sale_currency]"]');
                                if (costCurrencySelect && data.currency) {
                                    costCurrencySelect.value = data.currency;
                                }
                                if (saleCurrencySelect && data.currency) {
                                    saleCurrencySelect.value = data.currency;
                                }
                                document.querySelector('textarea[name="notes"]').value = data.notes ||
                                notes;

                                // غلق المودال
                                document.getElementById('hotelModal').remove();
                                document.getElementById('modalOverlay').remove();
                            });
                    };

                    // غلق المودال عند الضغط خارج النافذة
                    document.getElementById('modalOverlay').onclick = function() {
                        document.getElementById('hotelModal').remove();
                        document.getElementById('modalOverlay').remove();
                    };
                })
                .catch(err => {
                    alert('تعذر جلب قائمة الحجوزات!');
                    console.error(err);
                });
        };
    </script>
@endsection
