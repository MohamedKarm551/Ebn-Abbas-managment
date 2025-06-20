@extends('layouts.app')

@section('title', 'إدارة المعاملات المالية الشخصية')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .stats-card {
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .currency-badge {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }

        .amount-positive {
            color: #28a745 !important;
            font-weight: 600;
        }

        .amount-negative {
            color: #dc3545 !important;
            font-weight: 600;
        }

        .transaction-card {
            border-left: 4px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .transaction-card.deposit {
            border-left-color: #28a745;
        }

        .transaction-card.withdrawal {
            border-left-color: #dc3545;
        }

        .transaction-card.transfer {
            border-left-color: #17a2b8;
        }

        .action-buttons .btn {
            margin: 0 2px;
            padding: 0.25rem 0.5rem;
        }

        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .quick-stats {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 1rem;
        }

        .export-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: white;
        }

        .export-section .btn {
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .export-section .btn:hover {
            border-color: white;
            transform: translateY(-2px);
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            min-width: 300px;
        }

        .export-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">
                    <i class="fas fa-wallet text-primary me-2"></i>
                    معاملاتي المالية الشخصية
                </h1>
                <p class="text-muted mb-0">إدارة وتتبع جميع المعاملات المالية الشخصية</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> إضافة معاملة جديدة
                </a>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown">
                    <span class="sr-only">تبديل القائمة المنسدلة</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.transactions.reports.monthly') }}">
                            <i class="fas fa-chart-line me-1"></i> تقرير شهري
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.transactions.reports.yearly') }}">
                            <i class="fas fa-chart-bar me-1"></i> تقرير سنوي
                        </a></li>
                </ul>
            </div>
        </div>

        <!-- Export Section -->
        <div class="export-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-1">
                        <i class="fas fa-download me-2"></i>
                        تصدير البيانات
                    </h6>
                    <p class="mb-0 small opacity-75">
                        يمكنك تصدير البيانات المعروضة حالياً أو جميع المعاملات إلى ملف Excel
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-light" onclick="exportCurrentTransactions()">
                            <i class="fas fa-file-excel me-1 text-success"></i>
                            تصدير العرض الحالي
                        </button>
                        <button type="button" class="btn btn-outline-light" onclick="exportAllTransactions()">
                            <i class="fas fa-database me-1"></i>
                            تصدير الكل
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        @include('admin.transactions.partials.stats', ['totals' => $totals])

        <!-- Quick Stats Summary -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-stats">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="h4 mb-1 text-success">{{ $summary['total_deposits'] ?? 0 }}</div>
                            <small class="text-muted">إجمالي الإيداعات</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 mb-1 text-danger">{{ $summary['total_withdrawals'] ?? 0 }}</div>
                            <small class="text-muted">إجمالي السحوبات</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 mb-1 text-info">{{ $summary['this_month'] ?? 0 }}</div>
                            <small class="text-muted">معاملات هذا الشهر</small>
                        </div>
                        <div class="col-md-3">
                            <div class="h4 mb-1 text-primary">{{ $summary['avg_transaction'] ?? 0 }}</div>
                            <small class="text-muted">متوسط المعاملة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        @include('admin.transactions.partials.filters', [
            'currencies' => $currencies,
            'categories' => $categories,
        ])

        <!-- Transactions Table -->
        <div id="transactions-container">
            @include('admin.transactions.partials.table', ['transactions' => $transactions])
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <h5 id="loadingTitle">جاري تصدير البيانات</h5>
            <p class="text-muted mb-0" id="loadingText">يرجى الانتظار...</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">نجح</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- إضافة مكتبة XLSX -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'اختر...',
                allowClear: true
            });

            // Auto-hide toasts
            $('.toast').each(function() {
                new bootstrap.Toast(this, {
                    autohide: true,
                    delay: 5000
                });
            });

            // تحقق من وجود مكتبة XLSX
            if (!window.XLSX) {
                console.warn('مكتبة XLSX غير محملة - تأكد من الاتصال بالإنترنت');
            }
        });

        // تصدير المعاملات المعروضة حالياً
        function exportCurrentTransactions() {
            if (!window.XLSX) {
                showErrorToast('لم يتم تحميل مكتبة XLSX. تأكد من الاتصال بالإنترنت.');
                return;
            }

            showLoading('تصدير العرض الحالي', 'جاري تحضير البيانات للتصدير...');

            setTimeout(() => {
                try {
                    // البحث عن الجدول في صفحة المعاملات
                    const table = document.querySelector('#transactions-container table');

                    if (!table) {
                        hideLoading();
                        showErrorToast('لا يوجد جدول للتصدير');
                        return;
                    }

                    // إنشاء نسخة من الجدول وتنظيفها
                    const clonedTable = table.cloneNode(true);
                    cleanTableForExport(clonedTable);

                    // تحويل الجدول إلى Excel
                    const wb = XLSX.utils.table_to_book(clonedTable, {
                        sheet: "المعاملات المالية"
                    });

                    // إضافة ورقة الملخص
                    addSummarySheet(wb);

                    // تسمية الملف
                    const currentDate = new Date().toISOString().split('T')[0];
                    const fileName = `معاملات-مالية-عرض-حالي-${currentDate}.xlsx`;

                    // تحديث نص التحميل
                    updateLoading('جاري حفظ الملف...');

                    setTimeout(() => {
                        // تصدير الملف
                        XLSX.writeFile(wb, fileName);

                        hideLoading();
                        showSuccessToast('تم تصدير العرض الحالي بنجاح!');
                    }, 500);

                } catch (error) {
                    hideLoading();
                    console.error('خطأ في التصدير:', error);
                    showErrorToast('حدث خطأ أثناء التصدير: ' + error.message);
                }
            }, 1000);
        }

        // تصدير جميع المعاملات
        function exportAllTransactions() {
            if (!window.XLSX) {
                showErrorToast('لم يتم تحميل مكتبة XLSX. تأكد من الاتصال بالإنترنت.');
                return;
            }

            showLoading('تصدير جميع المعاملات', 'جاري جلب جميع البيانات من الخادم...');

            // طلب AJAX لجلب جميع البيانات
            $.ajax({
                url: '{{ route('admin.transactions.export') }}',
                method: 'GET',
                data: {
                    export_type: 'all',
                    format: 'json',
                    // إضافة الفلاتر الحالية إذا وجدت
                    start_date: $('input[name="start_date"]').val(),
                    end_date: $('input[name="end_date"]').val(),
                    currency: $('select[name="currency"]').val(),
                    type: $('select[name="type"]').val(),
                    category: $('input[name="category"]').val(),
                    from_to: $('input[name="from_to"]').val(),
                    min_amount: $('input[name="min_amount"]').val(),
                    max_amount: $('input[name="max_amount"]').val()
                },
                timeout: 60000, // زيادة المهلة إلى 60 ثانية
                success: function(response) {
                    console.log('Server response:', response); // للتشخيص

                    updateLoading('جاري إنشاء ملف Excel...');

                    setTimeout(() => {
                        try {
                            // التحقق من نجاح الاستجابة
                            if (!response || typeof response !== 'object') {
                                throw new Error('استجابة الخادم غير صحيحة');
                            }

                            if (!response.success) {
                                throw new Error(response.message || 'فشل في جلب البيانات من الخادم');
                            }

                            if (!response.transactions || !Array.isArray(response.transactions)) {
                                throw new Error('بيانات المعاملات غير صحيحة');
                            }

                            if (response.transactions.length === 0) {
                                hideLoading();
                                showErrorToast('لا توجد معاملات للتصدير');
                                return;
                            }

                            console.log(
                            `Found ${response.transactions.length} transactions`); // للتشخيص

                            // إنشاء ملف Excel من البيانات JSON
                            const wb = XLSX.utils.book_new();

                            // تحضير البيانات
                            const worksheetData = prepareAllDataForExport(response.transactions);

                            // إنشاء ورقة العمل الرئيسية
                            const ws = XLSX.utils.aoa_to_sheet(worksheetData);

                            // تنسيق العرض
                            formatWorksheet(ws);

                            // إضافة ورقة العمل
                            XLSX.utils.book_append_sheet(wb, ws, "جميع المعاملات");

                            // إضافة ورقة الإحصائيات
                            if (response.summary) {
                                addDetailedStatisticsSheet(wb, response.summary);
                            }

                            // تسمية الملف
                            const currentDate = new Date().toISOString().split('T')[0];
                            const fileName = `جميع-المعاملات-المالية-${currentDate}.xlsx`;

                            updateLoading('جاري حفظ الملف...');

                            setTimeout(() => {
                                // تصدير الملف
                                XLSX.writeFile(wb, fileName);

                                hideLoading();
                                showSuccessToast(
                                    `تم تصدير جميع المعاملات بنجاح!<br>` +
                                    `<small>إجمالي ${response.transactions.length} معاملة</small>`
                                );
                            }, 500);

                        } catch (error) {
                            hideLoading();
                            console.error('Error processing data:', error);
                            showErrorToast('حدث خطأ أثناء معالجة البيانات: ' + error.message);
                        }
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });

                    let errorMessage = 'حدث خطأ أثناء جلب البيانات';

                    if (status === 'timeout') {
                        errorMessage = 'انتهت مهلة الانتظار - البيانات كثيرة جداً، حاول تطبيق فلاتر أولاً';
                    } else if (xhr.status === 500) {
                        errorMessage = 'خطأ في الخادم - يرجى المحاولة لاحقاً';
                    } else if (xhr.status === 404) {
                        errorMessage = 'خدمة التصدير غير متوفرة';
                    } else if (xhr.status === 403) {
                        errorMessage = 'غير مصرح لك بهذه العملية';
                    } else if (xhr.status === 0) {
                        errorMessage = 'مشكلة في الاتصال - تحقق من الإنترنت';
                    }

                    // محاولة استخراج رسالة خطأ من الاستجابة
                    try {
                        const responseData = JSON.parse(xhr.responseText);
                        if (responseData.message) {
                            errorMessage += ': ' + responseData.message;
                        }
                    } catch (e) {
                        // تجاهل أخطاء parsing
                    }

                    showErrorToast(errorMessage);
                }
            });
        } // وظائف مساعدة للتنظيف والتنسيق
        function cleanTableForExport(table) {
            // إزالة عمود الإجراءات
            const actionHeaders = table.querySelectorAll('th');
            actionHeaders.forEach((header, index) => {
                if (header.textContent.includes('إجراءات')) {
                    header.remove();
                    // إزالة خلايا العمود المقابل
                    const rows = table.querySelectorAll('tr');
                    rows.forEach(row => {
                        const cell = row.children[index];
                        if (cell) cell.remove();
                    });
                }
            });

            // تنظيف المحتوى من HTML
            const allCells = table.querySelectorAll('td, th');
            allCells.forEach(cell => {
                // استبدال الأيقونات والشارات بالنص فقط
                const badges = cell.querySelectorAll('.badge');
                badges.forEach(badge => {
                    badge.outerHTML = badge.textContent.trim();
                });

                const icons = cell.querySelectorAll('i');
                icons.forEach(icon => icon.remove());

                // تنظيف النص
                cell.innerHTML = cell.textContent.trim();
            });
        }

        function prepareAllDataForExport(transactions) {
            // رؤوس الأعمدة
            const headers = [
                'رقم المعاملة',
                'التاريخ',
                'من/إلى',
                'المبلغ',
                'العملة',
                'نوع العملية',
                'التصنيف',
                'يوجد مرفق',
                'ملاحظات',
                'تاريخ الإنشاء'
            ];

            // تحضير البيانات
            const rows = transactions.map(transaction => [
                `#${transaction.id}`,
                transaction.transaction_date,
                transaction.from_to || 'غير محدد',
                parseFloat(transaction.amount).toLocaleString('ar-SA', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }),
                transaction.currency,
                transaction.type_arabic,
                transaction.category || 'غير محدد',
                transaction.has_attachment ? 'نعم' : 'لا',
                transaction.notes || '',
                transaction.created_at
            ]);

            return [headers, ...rows];
        }

        function formatWorksheet(worksheet) {
            // تحديد العرض للأعمدة
            const colWidths = [{
                    wch: 15
                }, // رقم المعاملة
                {
                    wch: 12
                }, // التاريخ
                {
                    wch: 20
                }, // من/إلى
                {
                    wch: 15
                }, // المبلغ
                {
                    wch: 8
                }, // العملة
                {
                    wch: 15
                }, // نوع العملية
                {
                    wch: 15
                }, // التصنيف
                {
                    wch: 10
                }, // مرفق
                {
                    wch: 30
                }, // ملاحظات
                {
                    wch: 18
                } // تاريخ الإنشاء
            ];

            worksheet['!cols'] = colWidths;
        }

        function addSummarySheet(workbook) {
            const summaryData = [
                ['ملخص المعاملات المالية'],
                ['تم إنشاؤه في: ' + new Date().toLocaleString('ar-SA')],
                [''],
                ['الإحصائيات العامة'],
                ['إجمالي الإيداعات', '{{ $summary['total_deposits'] ?? 0 }}'],
                ['إجمالي السحوبات', '{{ $summary['total_withdrawals'] ?? 0 }}'],
                ['معاملات هذا الشهر', '{{ $summary['this_month'] ?? 0 }}'],
                ['متوسط المعاملة', '{{ $summary['avg_transaction'] ?? 0 }}']
            ];

            const summaryWS = XLSX.utils.aoa_to_sheet(summaryData);
            summaryWS['!cols'] = [{
                wch: 25
            }, {
                wch: 20
            }];

            XLSX.utils.book_append_sheet(workbook, summaryWS, "الملخص");
        }

        function addDetailedStatisticsSheet(workbook, summary) {
            const statsData = [
                ['الإحصائيات التفصيلية'],
                ['تم إنشاؤه في: ' + new Date().toLocaleString('ar-SA')],
                [''],
                ['البيان', 'القيمة'],
                ['إجمالي المعاملات', summary.total_transactions || 0],
                ['إجمالي الإيداعات', summary.total_deposits || 0],
                ['إجمالي السحوبات', summary.total_withdrawals || 0],
                ['صافي الرصيد', summary.net_balance || 0],
                ['معاملات هذا الشهر', summary.this_month || 0],
                ['متوسط المعاملة', summary.avg_transaction || 0],
                [''],
                ['العملات المستخدمة'],
                ...(summary.currencies_used || []).map(currency => ['', currency])
            ];

            const statsWS = XLSX.utils.aoa_to_sheet(statsData);
            statsWS['!cols'] = [{
                wch: 25
            }, {
                wch: 20
            }];

            XLSX.utils.book_append_sheet(workbook, statsWS, "الإحصائيات التفصيلية");
        }

        // وظائف واجهة المستخدم
        function showLoading(title, text) {
            document.getElementById('loadingTitle').textContent = title;
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function updateLoading(text) {
            document.getElementById('loadingText').textContent = text;
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function showSuccessToast(message) {
            showToast(message, 'success', 'fas fa-check-circle', 'نجح التصدير');
        }

        function showErrorToast(message) {
            showToast(message, 'danger', 'fas fa-exclamation-triangle', 'خطأ');
        }

        function showToast(message, type, icon, title) {
            const toastHTML = `
        <div class="export-toast">
            <div class="toast show" role="alert">
                <div class="toast-header bg-${type} text-white">
                    <i class="${icon} me-2"></i>
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        </div>
    `;

            document.body.insertAdjacentHTML('beforeend', toastHTML);

            // إخفاء Toast بعد 5 ثوان
            setTimeout(() => {
                const toasts = document.querySelectorAll('.export-toast');
                toasts.forEach(toast => toast.remove());
            }, 5000);
        }

        // الدوال القديمة للتوافق
        function exportTransactions() {
            exportCurrentTransactions();
        }

        // Delete confirmation
        function confirmDelete(id) {
            if (confirm('هل أنت متأكد من حذف هذه المعاملة؟\nلا يمكن التراجع عن هذا الإجراء.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endpush
