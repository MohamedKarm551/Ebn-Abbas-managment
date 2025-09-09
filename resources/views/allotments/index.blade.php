@extends('layouts.app')

@section('title', 'مراقبة الألوتمنت')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">متابعة حالة الألوتمنت</h6>
        </div>
        <div class="card-body">
            <!-- فلترة البيانات -->
            <form method="GET" action="{{ route('allotments.monitor') }}" class="row mb-4 align-items-end">
                <div class="col-md-3">
                    <label for="start_date">من تاريخ</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date">إلى تاريخ</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label for="hotel_id">الفندق</label>
                    <select name="hotel_id" id="hotel_id" class="form-control">
                        <option value="">كل الفنادق</option>
                        @foreach(App\Models\Hotel::all() as $hotel)
                            <option value="{{ $hotel->id }}" {{ $selectedHotelId == $hotel->id ? 'selected' : '' }}>
                                {{ $hotel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
                </div>
            </form>

            <!-- القسم الأول: الرسم البياني -->
            <div class="mb-5">
                <h5>الرسم البياني للألوتمنت</h5>
                <canvas id="allotmentChart" height="200"></canvas>
            </div>
            
            <!-- القسم الثاني: جدول التفاصيل -->
            <div class="table-responsive">
                <table id="allotmentTable" class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>الفندق</th>
                            <!-- سيتم إضافة أعمدة التواريخ عبر JavaScript -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- سيتم إضافة بيانات الجدول عبر JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الفندق</th>
                <th>من</th>
                <th>إلى</th>
                <th>عدد الغرف</th>
                <th>المتاح</th>
                <th>المباع</th>
                <th>السعر</th>
                <th>العملة</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allotments as $allotment)
            <tr>
                <td>{{ $allotment->hotel->name }}</td>
                <td>{{ $allotment->start_date->format('Y-m-d') }}</td>
                <td>{{ $allotment->end_date->format('Y-m-d') }}</td>
                <td>{{ $allotment->rooms_count }}</td>
                <td><span class="badge {{ $allotment->remaining_rooms > 0 ? 'bg-success' : 'bg-danger' }}">{{ $allotment->remaining_rooms }}</span></td>
                <td>{{ $allotment->sold_rooms }}</td>
                <td>{{ $allotment->rate_per_room }}</td>
                <td>{{ $allotment->currency }}</td>
                <td><span class="badge {{ $allotment->status == 'active' ? 'bg-success' : 'bg-danger' }}">{{ $allotment->status == 'active' ? 'نشط' : 'ملغي' }}</span></td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('allotments.show', $allotment->id) }}" class="btn btn-sm btn-info" title="عرض">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('allotments.edit', $allotment->id) }}" class="btn btn-sm btn-primary" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('allotments.destroy', $allotment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الألوتمنت؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* تنسيقات لتجميل الجدول والرسم البياني */
    .table-hover-cell:hover {
        background-color: rgba(0,123,255,0.1);
    }
    .allotment-cell {
        min-width: 120px;
        text-align: center;
    }
    .hotel-cell {
        font-weight: bold;
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 1;
    }
    .date-header {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        height: 120px;
        white-space: nowrap;
        text-align: left;
    }
    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }
    .badge-available {
        background-color: #28a745;
        color: white;
    }
    .badge-warning {
        background-color: #ffc107;
        color: black;
    }
    .badge-danger {
        background-color: #dc3545;
        color: white;
    }
    .badge {
        padding: 0.25em 0.5em;
        font-size: 0.9em;
        border-radius: 0.25rem;
        display: inline-block;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // بيانات من Laravel
    const allotmentData = @json($allotments);
    const salesData = @json($sales);
    const startDate = "{{ $startDate }}";
    const endDate = "{{ $endDate }}";
    
    // تجهيز مصفوفة التواريخ
    const dateRange = [];
    let currentDate = new Date(startDate);
    const lastDate = new Date(endDate);
    
    while (currentDate <= lastDate) {
        dateRange.push(currentDate.toISOString().split('T')[0]);
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    // تجهيز البيانات لكل فندق
    const hotels = {};
    
    // تجهيز بيانات الألوتمنت
    allotmentData.forEach(allotment => {
        const hotelId = allotment.hotel_id;
        const hotelName = allotment.hotel.name;
        
        if (!hotels[hotelId]) {
            hotels[hotelId] = {
                id: hotelId,
                name: hotelName,
                dates: {}
            };
            
            // تهيئة كل التواريخ بقيم صفرية
            dateRange.forEach(date => {
                hotels[hotelId].dates[date] = {
                    allotted: 0,
                    sold: 0,
                    available: 0
                };
            });
        }
        
        // حساب عدد الغرف المتاحة لكل يوم
        const allotmentStart = new Date(allotment.start_date);
        const allotmentEnd = new Date(allotment.end_date);
        
        dateRange.forEach(date => {
            const currentDate = new Date(date);
            if (currentDate >= allotmentStart && currentDate <= allotmentEnd) {
                hotels[hotelId].dates[date].allotted += parseInt(allotment.rooms_count);
            }
        });
    });
    
    // تجهيز بيانات المبيعات
    salesData.forEach(sale => {
        const hotelId = sale.hotel_id;
        if (!hotels[hotelId]) return; // تخطي إذا لم يكن الفندق موجودًا
        
        const checkIn = new Date(sale.check_in);
        const checkOut = new Date(sale.check_out);
        
        dateRange.forEach(date => {
            const currentDate = new Date(date);
            if (currentDate >= checkIn && currentDate < checkOut) {
                hotels[hotelId].dates[date].sold += parseInt(sale.rooms_sold);
            }
        });
    });
    
    // حساب الغرف المتاحة
    Object.values(hotels).forEach(hotel => {
        dateRange.forEach(date => {
            hotel.dates[date].available = hotel.dates[date].allotted - hotel.dates[date].sold;
        });
    });
    
    // بناء جدول العرض
    function buildAllotmentTable() {
        const table = document.getElementById('allotmentTable');
        const headerRow = table.querySelector('thead tr');
        
        // إضافة أعمدة التواريخ للـ header
      // إضافة أعمدة التواريخ للـ header (هجري + ميلادي)
dateRange.forEach(date => {
    const d = new Date(date);

    // التاريخ بالهجري (العربي - تقويم سعودي)
    const hijri = d.toLocaleDateString('ar-SA', {
        month: 'short',
        day: 'numeric'
    });

    // التاريخ الميلادي (Gregorian)
    const gregorian = d.toLocaleDateString('en-GB', {
        month: 'short',
        day: 'numeric'
    });

    const th = document.createElement('th');
    th.className = 'date-header';
    th.innerHTML = `
        <div>${hijri}</div>
        <div class="text-muted small">${gregorian}</div>
    `;
    headerRow.appendChild(th);
});

        
        // إضافة صفوف الفنادق وبيانات الغرف
        const tableBody = table.querySelector('tbody');
        
        Object.values(hotels).forEach(hotel => {
            const row = document.createElement('tr');
            
            // إضافة خلية اسم الفندق
            const hotelCell = document.createElement('td');
            hotelCell.className = 'hotel-cell';
            hotelCell.textContent = hotel.name;
            row.appendChild(hotelCell);
            
            // إضافة خلية لكل تاريخ
            dateRange.forEach(date => {
                const cell = document.createElement('td');
                cell.className = 'allotment-cell table-hover-cell';
                
                const allotted = hotel.dates[date].allotted;
                const sold = hotel.dates[date].sold;
                const available = hotel.dates[date].available;
                
                // تحديد حالة الخلية
                let badgeClass = 'badge-available';
                if (available <= 0) {
                    badgeClass = 'badge-danger';
                } else if (available <= allotted * 0.2) {
                    badgeClass = 'badge-warning';
                }
                
                cell.innerHTML = `
                    <div class="badge ${badgeClass}" title="المتاح">${available}</div>
                    <div class="small mt-1">
                        <div>الألوتمنت: ${allotted}</div>
                        <div>المباع: ${sold}</div>
                    </div>
                `;
                
                row.appendChild(cell);
            });
            
            tableBody.appendChild(row);
        });
    }
    
    // بناء الرسم البياني
    function buildAllotmentChart() {
        const ctx = document.getElementById('allotmentChart').getContext('2d');
        
        // تجهيز بيانات الرسم البياني
        const chartData = {
            labels: Object.values(hotels).map(hotel => hotel.name),
            datasets: [
                {
                    label: 'الألوتمنت الكلي',
                    data: Object.values(hotels).map(hotel => {
                        return Object.values(hotel.dates).reduce((sum, dateData) => sum + dateData.allotted, 0);
                    }),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                },
                {
                    label: 'المباع',
                    data: Object.values(hotels).map(hotel => {
                        return Object.values(hotel.dates).reduce((sum, dateData) => sum + dateData.sold, 0);
                    }),
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                },
                {
                    label: 'المتاح',
                    data: Object.values(hotels).map(hotel => {
                        return Object.values(hotel.dates).reduce((sum, dateData) => sum + dateData.available, 0);
                    }),
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }
            ]
        };
        
        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'توزيع الألوتمنت والمبيعات حسب الفندق'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    },
                },
                responsive: true,
                scales: {
                    x: {
                        stacked: false,
                    },
                    y: {
                        stacked: false
                    }
                }
            }
        });
    }
    
    // تنفيذ العمليات عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        buildAllotmentTable();
        buildAllotmentChart();
    });
</script>
@endpush