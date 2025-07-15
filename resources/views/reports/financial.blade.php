@extends('layouts.app')

@section('title', 'مخطط الحالة المالية')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">مخطط الحالة المالية</h1>
    
    <!-- اختبار وجود البيانات -->
    @php
        // استخدام dd() بشكل صحيح (بدون البيانات حاليًا، فقط لمعرفة ما هو متاح)
        // dd(get_defined_vars()['__data']);
    @endphp

    <!-- هنا باقي محتوى العرض -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>البيانات المالية</h5>
                </div>
                <div class="card-body">
                    <!-- هنا سيتم عرض البيانات -->
                    <p>جاري تحميل البيانات...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // سيتم استقبال البيانات من خلال طلب AJAX
    document.addEventListener('DOMContentLoaded', function() {
        console.log('جاري تحميل البيانات المالية...');
        fetchFinancialData();
    });

    // دالة لجلب البيانات المالية
    async function fetchFinancialData() {
        try {
            // إظهار رسالة تحميل
            document.querySelector('.card-body').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">جاري التحميل...</span></div><p class="mt-2">جاري تحميل البيانات...</p></div>';
            
            // طلب البيانات من الخادم
            const response = await fetch("{{ route('financial.status.data') }}");
            
            if (!response.ok) {
                throw new Error(`فشل في الحصول على البيانات: ${response.status}`);
            }
            
            // تحويل الاستجابة إلى JSON
            const data = await response.json();
            
            // عرض البيانات في وحدة التحكم للتصحيح
            console.log('البيانات المستلمة:', data);
            
            // عرض البيانات في الصفحة (مثال بسيط)
            displayFinancialSummary(data);
            
        } catch (error) {
            console.error('خطأ:', error);
            document.querySelector('.card-body').innerHTML = `
                <div class="alert alert-danger">
                    حدث خطأ أثناء تحميل البيانات: ${error.message}
                </div>
            `;
        }
    }

    // دالة لعرض ملخص البيانات المالية
    function displayFinancialSummary(data) {
        // التأكد من وجود البيانات الإحصائية
        if (!data || !data.statistics) {
            document.querySelector('.card-body').innerHTML = '<div class="alert alert-warning">لا توجد بيانات متاحة</div>';
            return;
        }
        
        const stats = data.statistics;
        
        // إنشاء محتوى HTML لعرض الإحصائيات
        let html = `
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">مدفوع بالكامل</div>
                        <div class="card-body">
                            <h5 class="card-title">${stats.fully_paid_bookings || 0}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">مدفوع جزئياً</div>
                        <div class="card-body">
                            <h5 class="card-title">${stats.partially_paid_bookings || 0}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-header">غير مدفوع</div>
                        <div class="card-body">
                            <h5 class="card-title">${stats.not_paid_bookings || 0}</h5>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // عرض المحتوى
        document.querySelector('.card-body').innerHTML = html;
    }
</script>
@endpush


