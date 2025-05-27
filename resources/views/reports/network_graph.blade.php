@extends('layouts.app')

@section('title', 'مخطط العلاقات')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">
        <i class="fas fa-project-diagram me-2"></i>
        مخطط العلاقات
    </h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">العلاقات بين الجهات، الفنادق والحجوزات</h6>
            <div class="filter-controls">
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary" id="zoomIn">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="zoomOut">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="resetZoom">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- فلاتر البحث -->
            <div class="row mb-4">
                <div class="col-md-3 mb-2">
                    <select id="agentFilter" class="form-select">
                        <option value="">جميع جهات الحجز</option>
                        @foreach(\App\Models\Agent::orderBy('name')->get() as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select id="hotelFilter" class="form-select">
                        <option value="">جميع الفنادق</option>
                        @foreach(\App\Models\Hotel::orderBy('name')->get() as $hotel)
                            <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select id="companyFilter" class="form-select">
                        <option value="">جميع الشركات</option>
                        @foreach(\App\Models\Company::orderBy('name')->get() as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select id="limitFilter" class="form-select">
                        <option value="25">25 حجز</option>
                        <option value="50" selected>50 حجز</option>
                        <option value="100">100 حجز</option>
                        <option value="200">200 حجز</option>
                    </select>
                </div>
                <div class="col-12 mt-2">
                    <button id="applyFilters" class="btn btn-primary">تطبيق الفلاتر</button>
                    <button id="resetFilters" class="btn btn-secondary">إعادة ضبط</button>
                </div>
            </div>
            
            <!-- مؤشر التحميل -->
            <div id="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-2">جاري تحميل البيانات...</p>
            </div>
            
            <!-- كونتينر المخطط -->
            <div id="network-graph" style="height: 700px; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;"></div>
            
            <!-- دليل الرموز -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="m-0">دليل الرموز</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-around">
                                <div class="legend-item mb-2">
                                    <span class="legend-color" style="background-color: #4e73df;"></span>
                                    <span>شركة</span>
                                </div>
                                <div class="legend-item mb-2">
                                    <span class="legend-color" style="background-color: #1cc88a;"></span>
                                    <span>جهة حجز</span>
                                </div>
                                <div class="legend-item mb-2">
                                    <span class="legend-color" style="background-color: #f6c23e;"></span>
                                    <span>فندق</span>
                                </div>
                                <div class="legend-item mb-2">
                                    <span class="legend-color" style="background-color: #e74a3b;"></span>
                                    <span>حجز/عميل</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- نافذة عرض تفاصيل الحجز -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الحجز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bookingDetailsContent">
                <!-- سيتم ملء هذا المحتوى ديناميكياً -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <a id="viewBookingLink" href="#" class="btn btn-primary">عرض الحجز</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .legend-item {
        display: flex;
        align-items: center;
        margin-right: 20px;
    }
    .legend-color {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 8px;
    }
    
    /* تنسيقات المخطط الشبكي */
    .node {
        cursor: pointer;
    }
    .node text {
        font-family: 'Tajawal', sans-serif;
        font-size: 12px;
    }
    .link {
        stroke-opacity: 0.6;
    }
    
    /* تنسيقات التوليتيب */
    .tooltip {
        position: absolute;
        text-align: right;
        padding: 10px;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        border-radius: 5px;
        pointer-events: none;
        font-size: 14px;
        max-width: 300px;
        z-index: 1000;
    }
    
    /* أنماط زيادة التكبير */
    #network-graph {
        position: relative;
    }
    
    /* زيادة الأولوية للمستطيل عند تحديد خلية */
    .selected-node circle {
        stroke: #ff4500;
        stroke-width: 3px;
    }
</style>
@endpush

@push('scripts')
<!-- تضمين مكتبة D3.js -->
<script src="https://d3js.org/d3.v7.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // متغيرات عالمية
    let svg = null;
    let simulation = null;
    let zoom = null;
    let g = null;
    let width = document.getElementById('network-graph').clientWidth;
    let height = document.getElementById('network-graph').clientHeight;
    let tooltip = null;
    let currentZoomTransform = null;
    
    // دالة تهيئة المخطط
    function initializeGraph() {
        // إنشاء التوليتيب
        tooltip = d3.select("body").append("div")
            .attr("class", "tooltip")
            .style("opacity", 0);
        
        // إنشاء عنصر SVG
        svg = d3.select("#network-graph")
            .append("svg")
            .attr("width", width)
            .attr("height", height);
        
        // إضافة دعم للتكبير/التصغير
        zoom = d3.zoom()
            .scaleExtent([0.1, 4])
            .on("zoom", function(event) {
                currentZoomTransform = event.transform;
                g.attr("transform", event.transform);
            });
        
        svg.call(zoom);
        
        // إنشاء مجموعة عامة داخل SVG لتطبيق التكبير عليها
        g = svg.append("g");
        
        // تحميل البيانات
        loadData();
    }
    
    // دالة تحميل البيانات
    function loadData() {
        // إظهار مؤشر التحميل
        document.getElementById('loading').style.display = 'block';
        
        // جمع معاملات الفلاتر
        const agentId = document.getElementById('agentFilter').value;
        const hotelId = document.getElementById('hotelFilter').value;
        const companyId = document.getElementById('companyFilter').value;
        const limit = document.getElementById('limitFilter').value;
        
        // بناء عنوان URL مع المعاملات
        let url = "{{ route('network.data') }}";
        const params = new URLSearchParams();
        
        if (agentId) params.append('agent_id', agentId);
        if (hotelId) params.append('hotel_id', hotelId);
        if (companyId) params.append('company_id', companyId);
        params.append('limit', limit);
        
        url = `${url}?${params.toString()}`;
        
        // جلب البيانات من الخادم
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // إخفاء مؤشر التحميل
                document.getElementById('loading').style.display = 'none';
                
                // رسم المخطط بالبيانات الجديدة
                renderGraph(data);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                document.getElementById('loading').style.display = 'none';
                alert('حدث خطأ أثناء تحميل البيانات');
            });
    }
    
    // دالة رسم المخطط
    function renderGraph(data) {
        // تفريغ المخطط الحالي
        g.selectAll("*").remove();
        
        // تعريف ألوان العقد حسب النوع
        const nodeColors = {
            'company': '#4e73df', // أزرق
            'agent': '#1cc88a',   // أخضر
            'hotel': '#f6c23e',   // أصفر
            'booking': '#e74a3b'  // أحمر
        };
        
        // إنشاء المحاكاة
        simulation = d3.forceSimulation(data.nodes)
            .force("link", d3.forceLink(data.links).id(d => d.id).distance(100))
            .force("charge", d3.forceManyBody().strength(-500))
            .force("center", d3.forceCenter(width / 2, height / 2))
            .force("x", d3.forceX(width / 2).strength(0.1))
            .force("y", d3.forceY(height / 2).strength(0.1));
        
        // رسم الروابط
        const link = g.append("g")
            .attr("class", "links")
            .selectAll("line")
            .data(data.links)
            .enter().append("line")
            .attr("class", "link")
            .attr("stroke", "#999")
            .attr("stroke-width", d => Math.sqrt(d.value));
        
        // رسم العقد
        const node = g.append("g")
            .attr("class", "nodes")
            .selectAll(".node")
            .data(data.nodes)
            .enter().append("g")
            .attr("class", "node")
            .on("mouseover", function(event, d) {
                // إظهار التوليتيب عند التحويم
                tooltip.transition()
                    .duration(200)
                    .style("opacity", .9);
                
                let tooltipContent = `<strong>${d.name}</strong><br/>`;
                tooltipContent += `النوع: ${getTypeName(d.type)}<br/>`;
                
                // إضافة تفاصيل إضافية حسب نوع العقدة
                if (d.type === 'booking') {
                    tooltipContent += `تاريخ الدخول: ${d.check_in || 'غير محدد'}<br/>`;
                    tooltipContent += `تاريخ الخروج: ${d.check_out || 'غير محدد'}<br/>`;
                    tooltipContent += `عدد الغرف: ${d.rooms || 0}`;
                }
                
                tooltip.html(tooltipContent)
                    .style("left", (event.pageX + 10) + "px")
                    .style("top", (event.pageY - 28) + "px");
            })
            .on("mouseout", function() {
                // إخفاء التوليتيب عند مغادرة العنصر
                tooltip.transition()
                    .duration(500)
                    .style("opacity", 0);
            })
            .on("click", function(event, d) {
                // التعامل مع النقر على العقدة
                if (d.type === 'booking') {
                    showBookingDetails(d);
                }
                
                // تمييز العقدة عند النقر عليها
                d3.selectAll(".node").classed("selected-node", false);
                d3.select(this).classed("selected-node", true);
            })
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended));
        
        // إضافة الدوائر للعقد
        node.append("circle")
            .attr("r", d => d.value)
            .attr("fill", d => nodeColors[d.type] || "#999");
        
        // إضافة النصوص للعقد
        node.append("text")
            .attr("dx", d => d.value + 3)
            .attr("dy", ".35em")
            .text(d => d.name)
            .attr("fill", "#333");
        
        // تحديث موضع العقد والروابط في كل تيك
        simulation.on("tick", () => {
            link
                .attr("x1", d => d.source.x)
                .attr("y1", d => d.source.y)
                .attr("x2", d => d.target.x)
                .attr("y2", d => d.target.y);
            
            node.attr("transform", d => `translate(${d.x}, ${d.y})`);
        });
        
        // تقريب المخطط لتناسب الشاشة بعد التحميل
        setTimeout(() => {
            fitGraphToScreen();
        }, 1000);
    }
    
    // دالة للحصول على اسم النوع بالعربية
    function getTypeName(type) {
        switch (type) {
            case 'company': return 'شركة';
            case 'agent': return 'جهة حجز';
            case 'hotel': return 'فندق';
            case 'booking': return 'حجز';
            default: return type;
        }
    }
    
    // دالة لعرض تفاصيل الحجز
    function showBookingDetails(booking) {
        const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
        const contentElement = document.getElementById('bookingDetailsContent');
        const viewBookingLink = document.getElementById('viewBookingLink');
        
        // تعيين رابط عرض الحجز
        viewBookingLink.href = `/bookings/${booking.booking_id}`;
        
        // إنشاء محتوى الموديل
        let content = `
            <div class="mb-3">
                <h5>العميل: ${booking.name}</h5>
            </div>
            <div class="row">
                <div class="col-6">
                    <p><strong>تاريخ الدخول:</strong> ${booking.check_in || 'غير محدد'}</p>
                </div>
                <div class="col-6">
                    <p><strong>تاريخ الخروج:</strong> ${booking.check_out || 'غير محدد'}</p>
                </div>
            </div>
            <p><strong>عدد الغرف:</strong> ${booking.rooms || 0}</p>
        `;
        
        contentElement.innerHTML = content;
        modal.show();
    }
    
    // دوال السحب والإفلات للمخطط
    function dragstarted(event) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        event.subject.fx = event.subject.x;
        event.subject.fy = event.subject.y;
    }
    
    function dragged(event) {
        event.subject.fx = event.x;
        event.subject.fy = event.y;
    }
    
    function dragended(event) {
        if (!event.active) simulation.alphaTarget(0);
        event.subject.fx = null;
        event.subject.fy = null;
    }
    
    // دالة تناسب المخطط مع الشاشة
    function fitGraphToScreen() {
        if (!svg || !g) return;
        
        // إعادة ضبط التكبير
        svg.transition().duration(750).call(
            zoom.transform,
            d3.zoomIdentity.scale(0.8)
        );
    }
    
    // تهيئة المخطط عند تحميل الصفحة
    initializeGraph();
    
    // التعامل مع أزرار التكبير/التصغير
    document.getElementById('zoomIn').addEventListener('click', function() {
        svg.transition().duration(750).call(
            zoom.scaleBy,
            1.2
        );
    });
    
    document.getElementById('zoomOut').addEventListener('click', function() {
        svg.transition().duration(750).call(
            zoom.scaleBy,
            0.8
        );
    });
    
    document.getElementById('resetZoom').addEventListener('click', function() {
        fitGraphToScreen();
    });
    
    // التعامل مع أزرار الفلاتر
    document.getElementById('applyFilters').addEventListener('click', function() {
        loadData();
    });
    
    document.getElementById('resetFilters').addEventListener('click', function() {
        // إعادة تعيين جميع الفلاتر
        document.getElementById('agentFilter').value = '';
        document.getElementById('hotelFilter').value = '';
        document.getElementById('companyFilter').value = '';
        document.getElementById('limitFilter').value = '50';
        
        // إعادة تحميل البيانات
        loadData();
    });
    
    // إعادة تعيين حجم المخطط عند تغيير حجم النافذة
    window.addEventListener('resize', function() {
        width = document.getElementById('network-graph').clientWidth;
        height = document.getElementById('network-graph').clientHeight;
        
        if (svg) {
            svg.attr("width", width).attr("height", height);
            if (simulation) {
                simulation.force("center", d3.forceCenter(width / 2, height / 2));
                simulation.alpha(0.3).restart();
            }
        }
    });
});
</script>
@endpush