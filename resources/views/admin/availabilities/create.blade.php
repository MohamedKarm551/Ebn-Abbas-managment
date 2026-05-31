@extends('layouts.app')

@section('title', 'إضافة إتاحة جديدة')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">إضافة إتاحة جديدة</h1>
    {{-- قسم عرض الإتاحات النشطة للفندق --}}
    <div class="mt-5" id="availabilities-section" style="display: none;">
    <hr>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5><i class="fas fa-calendar-alt"></i> الإتاحات النشطة لهذا الفندق</h5>
        <button type="button" id="toggle-availabilities-btn" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-eye-slash"></i> إخفاء
        </button>
    </div>
    <div id="active-availabilities"></div>
</div>
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">حدث خطأ!</h5>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.availabilities.store') }}" method="POST" id="availability-form">
        @csrf

        @include('admin.availabilities._form', [
            'hotels' => $hotels,
            'agents' => $agents,
            'employees' => $employees,
            'roomTypes' => $roomTypes,
            'availability' => null
        ])

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> حفظ الإتاحة
            </button>
            <a href="{{ route('admin.availabilities.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> إلغاء
            </a>
        </div>
    </form>  
</div>
@endsection

{{-- ضع الجافاسكريبت مباشرة هنا (بدون @push لضمان العمل) --}}
<script>
    function fetchActiveAvailabilities(hotelId) {
        console.log('🔍 يتم جلب الإتاحات للفندق:', hotelId);
        
        const section = document.getElementById('availabilities-section');
        const container = document.getElementById('active-availabilities');
        const toggleBtn = document.getElementById('toggle-availabilities-btn');
        
        if (!hotelId) {
            section.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        const url = `{{ route('availabilities.hotel-active') }}?hotel_id=${hotelId}&_=${Date.now()}`;
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            console.log('✅ البيانات المستلمة:', data);
            
            const noData = !data.availabilities || data.availabilities.length === 0;
            if (noData) {
                section.style.display = 'none';
                container.innerHTML = '';
                return;
            }
            
            section.style.display = 'block';
            
            let html = '<div class="row">';
            data.availabilities.forEach(av => {
                html += `
                <div class="col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <strong>الإتاحة #${av.id}</strong> – ${av.agent_name}
                            <span class="badge bg-light text-dark float-end">${av.start_formatted} → ${av.end_formatted}</span>
                        </div>
                        <div class="card-body">
                            ${av.room_types.length ? `
                                <ul class="list-unstyled mb-0">
                                    ${av.room_types.map(rt => `
                                        <li><i class="fas fa-bed"></i> ${rt.room_type_name} : ${rt.allotment || 0} غرفة – سعر التكلفة ${rt.cost_price}</li>
                                    `).join('')}
                                </ul>
                            ` : '<p class="text-muted">لا توجد أنواع غرفة مسجلة</p>'}
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
            
            // التحكم في إظهار المحتوى (وليس القسم كله)
            const isHidden = localStorage.getItem('availabilitiesContentHidden') === 'true';
            if (!isHidden) {
                container.style.display = 'block';
                if (toggleBtn) toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> إخفاء المحتوى';
            } else {
                container.style.display = 'none';
                if (toggleBtn) toggleBtn.innerHTML = '<i class="fas fa-eye"></i> عرض المحتوى';
            }
        })
        .catch(err => {
            console.error('❌ خطأ في الجلب:', err);
            document.getElementById('availabilities-section').style.display = 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const hotelSelect = document.getElementById('hotel_id');
        if (!hotelSelect) {
            console.error('عنصر hotel_id غير موجود!');
            return;
        }

        const section = document.getElementById('availabilities-section');
        const container = document.getElementById('active-availabilities');
        const toggleBtn = document.getElementById('toggle-availabilities-btn');
        
        if (toggleBtn && section && container) {
            // استعادة الحالة المخزنة للمحتوى
            const isContentHidden = localStorage.getItem('availabilitiesContentHidden') === 'true';
            container.style.display = isContentHidden ? 'none' : 'block';
            toggleBtn.innerHTML = isContentHidden ? '<i class="fas fa-eye"></i> عرض المحتوى' : '<i class="fas fa-eye-slash"></i> إخفاء المحتوى';
            
            toggleBtn.addEventListener('click', function() {
                if (container.style.display === 'none') {
                    container.style.display = 'block';
                    toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> إخفاء المحتوى';
                    localStorage.setItem('availabilitiesContentHidden', 'false');
                } else {
                    container.style.display = 'none';
                    toggleBtn.innerHTML = '<i class="fas fa-eye"></i> عرض المحتوى';
                    localStorage.setItem('availabilitiesContentHidden', 'true');
                }
            });
        }

        hotelSelect.addEventListener('change', function() {
            fetchActiveAvailabilities(this.value);
        });

        if (typeof $ !== 'undefined' && $(hotelSelect).hasClass('select2-hidden-accessible')) {
            $(hotelSelect).on('select2:change', function(e) {
                fetchActiveAvailabilities(e.target.value);
            });
        }

        let lastValue = hotelSelect.value;
        setInterval(function() {
            if (hotelSelect.value !== lastValue) {
                lastValue = hotelSelect.value;
                fetchActiveAvailabilities(lastValue);
            }
        }, 300);

        if (hotelSelect.value) {
            setTimeout(() => {
                fetchActiveAvailabilities(hotelSelect.value);
            }, 500);
        }
    });
</script>