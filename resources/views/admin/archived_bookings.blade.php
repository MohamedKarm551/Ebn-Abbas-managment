{{--   هخلي الصفحة دي تعرض بيانات الحجوزات المؤرشفة  واللي هتكون عبارة عن  --}}
@extends('layouts.app')
@section('content')
    <div class="container" dir="rtl">
        <h1 class="mb-4"><i class="fas fa-archive"></i> أرشيف الحجوزات</h1>
        @if ($archivedBookings->isEmpty())
            <div class="alert alert-info text-center">لا توجد حجوزات مؤرشفة.</div>
        @else
            <!-- البحث والفلترة - هنا بتقدر تدور على أي حجز أو تفلتر بالتاريخ -->
            <div class="filter-box p-4 mb-4">
                <h3 class="mb-3">عملية البحث والفلترة</h3>
                <form id="archiveFilterForm" method="GET" action="{{ route('admin.archived_bookings') }}">
                    <div class="row align-items-center text-center">
                        <div class="col-md-4 mb-2">
                            <label for="search" class="form-label">بحث عن العميل، الموظف، الشركة، جهة حجز، فندق</label>
                            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="start_date" class="form-label">من تاريخ</label>
                            <input type="text" name="start_date" id="start_date" class="form-control datepicker" value="{{ request('start_date') }}" placeholder="يوم/شهر/سنة">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="end_date" class="form-label">إلى تاريخ</label>
                            <input type="text" name="end_date" id="end_date" class="form-control datepicker" value="{{ request('end_date') }}" placeholder="يوم/شهر/سنة">
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary">فلترة</button>
                        {{-- زر إعادة التعيين لإلغاء الفلاتر --}}

                        <a href="{{ route('admin.archived_bookings') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                    </div>
                </form>

                {{-- حقول مخفية لتخزين الإجماليات عشان الجافاسكريبت يقراها --}}
                <input type="hidden" id="hidden-total-count" value="{{ $totalBookingsCount ?? 0 }}">
                <input type="hidden" id="hidden-total-due-from-company" value="{{ $totalDueFromCompany ?? 0 }}">
                <input type="hidden" id="hidden-total-paid-by-company" value="{{ $totalPaidByCompany ?? 0 }}">
                <input type="hidden" id="hidden-total-remaining-from-company" value="{{ $remainingFromCompany ?? 0 }}">
                @if (!request('company_id'))
                    <input type="hidden" id="hidden-total-due-to-hotels" value="{{ $totalDueToHotels ?? 0 }}">
                    <input type="hidden" id="hidden-total-paid-to-hotels" value="{{ $totalPaidToHotels ?? 0 }}">
                    <input type="hidden" id="hidden-total-remaining-to-hotels" value="{{ $remainingToHotels ?? 0 }}">
                @endif
            </div>
            <div class="alert alert-info text-center mb-3">
                تم جلب: <strong>{{ $totalArchivedBookingsCount }}</strong> أرشيف
                
            </div>
            <div class="table-responsive" id="archivedBookingsTable">
                @include('admin._archived_table', ['archivedBookings' => $archivedBookings])
            </div>
            {{-- روابط الـ Pagination --}}
            <div class="d-flex justify-content-center mt-4" id="archivedPagination">
                {{ $archivedBookings->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
<script>
// ==========================================================
// دالة جلب البيانات بالـ AJAX (بتحدث الجدول والصفحات والإجماليات)
// ==========================================================
function fetchData(url) {
    axios.get(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        // تحديث الجدول
        if (archivedBookingsTableContainer && response.data.table !== undefined) {
            archivedBookingsTableContainer.innerHTML = response.data.table;
        }
        // تحديث الباجيناشن
        if (archivedPaginationContainer && response.data.pagination !== undefined) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = response.data.pagination.trim();
            const newPagination = tempDiv.querySelector('ul.pagination');
            if (newPagination) {
                const currentPaginationUl = archivedPaginationContainer.querySelector('ul.pagination');
                if (currentPaginationUl) {
                    currentPaginationUl.replaceWith(newPagination);
                } else {
                    archivedPaginationContainer.innerHTML = '';
                    archivedPaginationContainer.appendChild(newPagination);
                }
            } else {
                archivedPaginationContainer.innerHTML = '';
            }
        }
        // تحديث الإجماليات
        if (response.data.totals) {
            updateHiddenTotals(response.data.totals);
        }
        // إعادة تهيئة Bootstrap
        initBootstrapComponents();
        updateDateAlert();
    })
    .catch(function(error) {
        alert('حصل مشكلة واحنا بنجيب البيانات. حاول تاني أو شوف الكونسول.');
    });
}

// ==========================================================
// helper لتحديث رسالة الفلترة بالتواريخ
// ==========================================================
function updateDateAlert() {
    const params = new URLSearchParams(window.location.search);
    const start = params.get('start_date'), end = params.get('end_date');
    const container = document.getElementById('filterAlert');
    if (start && end) {
        container.innerHTML = `
          <div class="alert alert-info">
            هذه الحجوزات التي تمت "دخلت أو خرجت" بين 
            <strong>${start}</strong> و <strong>${end}</strong>
          </div>`;
    } else {
        container.innerHTML = '';
    }
}
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. تعريف المتغيرات الأساسية ---
    

    const archiveFilterForm = document.getElementById('archiveFilterForm');
    const archivedBookingsTableContainer = document.getElementById('archivedBookingsTable');
    const archivedPaginationContainer = document.getElementById('archivedPagination');
    // مش محتاجين نعرف copyBtn هنا طالما بنستخدم onclick في الـ HTML

    // --- 2. تهيئة Bootstrap أول مرة ---
    initBootstrapComponents();

    // --- 3. إضافة حدث لزرار التصوير ---
    if (captureBtn) {
        captureBtn.addEventListener('click', captureTableImage);
    }

    // --- 4. إضافة حدث لنموذج الفلترة ---
    if (archiveFilterForm) {
        archiveFilterForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(archiveFilterForm);
            const params = new URLSearchParams();
            formData.forEach((value, key) => {
                if (value) params.append(key, value);
            });
            const queryString = params.toString();
            const filterUrl = '{{ route('admin.archived_bookings') }}' + (queryString ? '?' + queryString : '');
            window.history.pushState({ path: filterUrl }, '', filterUrl);
            fetchData(filterUrl);
        });
    }

    // --- 5. إضافة حدث للنقر على أزرار الـ Pagination (باستخدام Event Delegation) ---
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
            e.preventDefault();
            const url = paginationLink.href;
            window.history.pushState({ path: url }, '', url);
            fetchData(url);
        }
    });

    // --- 6. التعامل مع زرار الـ Back/Forward ---
    window.addEventListener('popstate', function(event) {
        const url = event.state ? event.state.path : location.href;
        fetchData(url);
    });

    // تحديث الرسالة عند أول تحميل (قبل أي Ajax)
    updateDateAlert();

}); // نهاية الـ DOMContentLoaded
</script>
