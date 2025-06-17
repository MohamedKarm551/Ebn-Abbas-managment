@extends('layouts.app')

@section('title', 'تقارير العمليات والربحية')

@push('styles')
    <style>
        :root {
            --primary-blue: #3b82f6;
            --success-green: #10b981;
            --warning-amber: #f59e0b;
            --danger-red: #ef4444;
            --info-cyan: #06b6d4;
            --bg-light: #f8fafc;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --radius: 0.75rem;
        }

        .main-container {
            background: var(--bg-light);
            min-height: 100vh;
            padding: 1.5rem;
        }

        .page-header {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .page-title {
            color: var(--text-dark);
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .reports-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .reports-header {
            background: linear-gradient(135deg, var(--primary-blue), #2563eb);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .reports-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .btn-create {
            background: var(--success-green);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-create:hover {
            background: #059669;
            color: white;
            transform: translateY(-1px);
        }

        .report-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
            display: grid;
            grid-template-columns: auto 1fr auto;
            /* تعديل هنا لإضافة عمود للرقم */
            gap: 1rem;
            align-items: start;
        }

        .report-number {
            display: flex;
            align-items: flex-start;
            padding-top: 0.25rem;
        }

        .number-circle {
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, var(--primary-blue), #2563eb);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
            transition: all 0.2s;
        }

        .report-item:hover .number-circle {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        }

        .report-item:hover {
            background: var(--bg-light);
        }

        .report-item:last-child {
            border-bottom: none;
        }

        .report-content {
            display: grid;
            gap: 0.75rem;
        }

        .report-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .report-client {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .report-company {
            color: var(--primary-blue);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .profit-display {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
            gap: 0.25rem;
        }

        .profit-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--success-green);
            margin: 0;
            line-height: 1.2;
        }

        .profit-amount:first-child {
            font-size: 1.5rem;
        }

        .profit-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        /* تخصيص عرض الإحصائيات للعملات المتعددة */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .report-item {
                grid-template-columns: auto 1fr;
                /* للشاشات الصغيرة */
                gap: 0.75rem;
            }

            .number-circle {
                width: 2rem;
                height: 2rem;
                font-size: 0.875rem;
            }

            .profit-display {
                grid-column: 1 / -1;
                /* يأخذ العرض الكامل */
                align-items: flex-start;
                text-align: left;
                margin-top: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.75rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }
        }

        .report-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-top: 1rem;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            color: white;
        }

        .btn-warning {
            background: var(--warning-amber);
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
            color: white;
        }

        .btn-danger {
            background: var(--danger-red);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-danger:hover {
            background: #dc2626;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .report-item {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .profit-display {
                align-items: flex-start;
                text-align: left;
            }

            .report-actions {
                justify-content: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-container">
        <!-- رأس الصفحة -->
        <div class="page-header">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <h1 class="page-title">
                    <i class="fas fa-chart-line"></i>
                    تقارير العمليات والربحية
                </h1>
                <div class="d-flex gap-2 mb-3">
                    <!-- ✅ إضافة زر التصدير -->
                    <button onclick="reportExporter.exportReportsToExcel()" class="btn-create"
                        style="background: var(--success-green);">
                        <i class="fas fa-file-excel"></i>
                        <span>تصدير Excel</span>
                    </button>
                    <a href="{{ route('admin.operation-reports.charts') }}" class="btn-create">
                        <i class="fas fa-chart-bar"></i>
                        <span>التحليلات الرسومية</span>
                    </a>
                    <a href="{{ route('admin.operation-reports.create') }}" class="btn-create">
                        <i class="fas fa-plus"></i>
                        <span>إنشاء تقرير جديد</span>
                    </a>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $reports->total() }}</div>
                    <div class="stat-label">إجمالي التقارير</div>
                </div>

                <!-- عرض الأرباح حسب العملة -->
                @if (!empty($profitsByCurrency))
                    @foreach ($profitsByCurrency as $currency => $profit)
                        <div class="stat-card">
                            <div class="stat-value">{{ number_format($profit, 2) }}</div>
                            <div class="stat-label">
                                إجمالي الأرباح -
                                @switch($currency)
                                    @case('KWD')
                                        دينار كويتي
                                    @break

                                    @case('SAR')
                                        ريال سعودي
                                    @break

                                    @case('USD')
                                        دولار أمريكي
                                    @break

                                    @case('EUR')
                                        يورو
                                    @break

                                    @default
                                        {{ $currency }}
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="stat-card">
                        <div class="stat-value">0.00</div>
                        <div class="stat-label">إجمالي الأرباح</div>
                    </div>
                @endif

                <div class="stat-card">
                    <div class="stat-value">{{ $reportsThisMonth ?? 0 }}</div>
                    <div class="stat-label">تقارير هذا الشهر</div>
                </div>
            </div>

            <!-- قائمة التقارير -->
            <div class="reports-card">
                <div class="reports-header">
                    <h2 class="reports-title">
                        <i class="fas fa-list me-2"></i>
                        آخر التقارير
                    </h2>
                </div>
                {{-- ✅ إضافة جدول مخفي للتصدير --}}
                <div style="display: none;">
                    <table id="reports-export-table">
                        <thead>
                            <tr>
                                <th>رقم التقرير</th>
                                <th>اسم العميل</th>
                                <th>اسم الشركة</th>
                                <th>الموظف</th>
                                <th>تاريخ التقرير</th>
                                <th>نوع الحجز</th>
                                <th>الحالة</th>
                                <th>أرباح التأشيرات (KWD)</th>
                                <th>أرباح التأشيرات (SAR)</th>
                                <th>أرباح الطيران (KWD)</th>
                                <th>أرباح الطيران (SAR)</th>
                                <th>أرباح النقل (KWD)</th>
                                <th>أرباح النقل (SAR)</th>
                                <th>أرباح الفنادق (KWD)</th>
                                <th>أرباح الفنادق (SAR)</th>
                                <th>أرباح الرحلات البرية (KWD)</th>
                                <th>أرباح الرحلات البرية (SAR)</th>
                                <th>إجمالي الربح</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                @php
                                    $reportProfits = $report->profits_by_currency_detailed ?? [];
                                @endphp
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>{{ $report->client_name }}</td>
                                    <td>{{ $report->company_name ?? 'غير محدد' }}</td>
                                    <td>{{ $report->employee->name ?? 'غير محدد' }}</td>
                                    <td>{{ $report->report_date->format('Y-m-d') }}</td>
                                    <td>{{ $report->booking_type === 'hotel' ? 'حجز فندق' : ($report->booking_type === 'land_trip' ? 'رحلة برية' : 'غير محدد') }}
                                    </td>
                                    <td>{{ $report->status === 'completed' ? 'مكتمل' : 'مسودة' }}</td>

                                    {{-- ✅ أرباح التأشيرات - KWD و SAR فقط --}}
                                    <td>{{ number_format($reportProfits['visa']['KWD'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($reportProfits['visa']['SAR'] ?? 0, 2) }}</td>

                                    {{-- ✅ أرباح الطيران - KWD و SAR فقط --}}
                                    <td>{{ number_format($reportProfits['flight']['KWD'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($reportProfits['flight']['SAR'] ?? 0, 2) }}</td>

                                    {{-- ✅ أرباح النقل - KWD و SAR فقط --}}
                                    <td>{{ number_format($reportProfits['transport']['KWD'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($reportProfits['transport']['SAR'] ?? 0, 2) }}</td>

                                    {{-- ✅ أرباح الفنادق - KWD و SAR فقط --}}
                                    <td>{{ number_format($reportProfits['hotel']['KWD'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($reportProfits['hotel']['SAR'] ?? 0, 2) }}</td>

                                    {{-- ✅ أرباح الرحلات البرية - KWD و SAR فقط --}}
                                    <td>{{ number_format($reportProfits['land_trip']['KWD'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($reportProfits['land_trip']['SAR'] ?? 0, 2) }}</td>

                                    <td>{{ number_format($report->grand_total_profit, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @forelse($reports as $report)
                    <div class="report-item">
                        <div class="report-number">
                            <span class="number-circle">{{ $loop->index + 1 }}</span>
                        </div>
                        <div class="report-content">
                            <div class="report-client">{{ $report->client_name }}</div>

                            @if ($report->company_name)
                                <div class="report-company">{{ $report->company_name }}</div>
                            @endif

                            <div class="report-meta">
                                <span>
                                    <i class="fas fa-user me-1"></i>
                                    {{ $report->employee->name ?? 'غير محدد' }}
                                </span>
                                <span>
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $report->report_date->format('d/m/Y') }}
                                </span>
                                @if ($report->booking_type)
                                    <span>
                                        <i class="fas fa-tag me-1"></i>
                                        {{ $report->booking_type === 'hotel' ? 'حجز فندق' : 'رحلة برية' }}
                                    </span>
                                @endif
                                <span class="status-badge status-{{ $report->status }}">
                                    {{ $report->status === 'completed' ? 'مكتمل' : 'مسودة' }}
                                </span>
                            </div>

                            <div class="report-actions">
                                <a href="{{ route('admin.operation-reports.show', $report) }}" class="btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                    عرض
                                </a>
                                <a href="{{ route('admin.operation-reports.edit', $report) }}" class="btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                    تعديل
                                </a>
                                <form method="POST" action="{{ route('admin.operation-reports.destroy', $report) }}"
                                    style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقرير؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="profit-display">
                            @php
                                $reportProfits = $report->profits_by_currency;
                            @endphp

                            @if (!empty($reportProfits))
                                @foreach ($reportProfits as $currency => $profit)
                                    <div class="profit-amount">
                                        {{ number_format($profit, 2) }}
                                        @switch($currency)
                                            @case('KWD')
                                                د.ك
                                            @break

                                            @case('SAR')
                                                ر.س
                                            @break

                                            @case('USD')
                                                $
                                            @break

                                            @case('EUR')
                                                €
                                            @break

                                            @default
                                                {{ $currency }}
                                        @endswitch
                                    </div>
                                @endforeach
                            @else
                                <div class="profit-amount">0.00 د.ك</div>
                            @endif

                            <div class="profit-label">إجمالي الربح</div>
                        </div>
                    </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-chart-line empty-icon"></i>
                            <h3 class="empty-title">لا توجد تقارير</h3>
                            <p class="empty-text">لم يتم إنشاء أي تقارير عمليات حتى الآن</p>
                            <a href="{{ route('admin.operation-reports.create') }}" class="btn-create">
                                <i class="fas fa-plus me-2"></i>إنشاء أول تقرير
                            </a>
                        </div>
                    @endforelse

                    @if ($reports->hasPages())
                        <div class="px-3 py-4 border-top">
                            <div class="d-flex justify-content-center">
                                {{ $reports->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endsection

        @push('scripts')
            {{-- ✅ إضافة مكتبة XLSX --}}
            <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

            <script>
                // ✅ إضافة أداة التصدير
                const reportExporter = {
                    exportReportsToExcel() {
                        const table = document.querySelector('#reports-export-table');

                        if (!window.XLSX) {
                            alert('لم يتم تحميل مكتبة XLSX. تأكد من الاتصال بالإنترنت.');
                            return;
                        }

                        if (!table) {
                            alert('لم يتم العثور على بيانات للتصدير.');
                            return;
                        }

                        try {
                            // إنشاء workbook من الجدول
                            let wb = XLSX.utils.table_to_book(table, {
                                sheet: "تقارير العمليات",
                                raw: false
                            });

                            // تخصيص عرض الأعمدة
                            const ws = wb.Sheets['تقارير العمليات'];
                            const range = XLSX.utils.decode_range(ws['!ref']);

                            // تحديد عرض الأعمدة
                            ws['!cols'] = [{
                                    wch: 12
                                }, // رقم التقرير
                                {
                                    wch: 25
                                }, // اسم العميل
                                {
                                    wch: 25
                                }, // اسم الشركة
                                {
                                    wch: 20
                                }, // الموظف
                                {
                                    wch: 15
                                }, // تاريخ التقرير
                                {
                                    wch: 15
                                }, // نوع الحجز
                                {
                                    wch: 10
                                }, // الحالة
                                {
                                    wch: 18
                                }, // أرباح التأشيرات KWD
                                {
                                    wch: 18
                                }, // أرباح التأشيرات SAR
                                {
                                    wch: 16
                                }, // أرباح الطيران KWD
                                {
                                    wch: 16
                                }, // أرباح الطيران SAR
                                {
                                    wch: 14
                                }, // أرباح النقل KWD
                                {
                                    wch: 14
                                }, // أرباح النقل SAR
                                {
                                    wch: 16
                                }, // أرباح الفنادق KWD
                                {
                                    wch: 16
                                }, // أرباح الفنادق SAR
                                {
                                    wch: 22
                                }, // أرباح الرحلات البرية KWD
                                {
                                    wch: 22
                                }, // أرباح الرحلات البرية SAR
                                {
                                    wch: 15
                                }, // إجمالي الربح
                            ];

                            // اسم الملف مع التاريخ
                            const today = new Date().toISOString().split('T')[0];
                            const fileName = `تقارير-العمليات-${today}.xlsx`;

                            // حفظ الملف
                            XLSX.writeFile(wb, fileName);

                            // رسالة نجاح
                            console.log('✅ تم تصدير التقارير بنجاح');

                        } catch (error) {
                            console.error('خطأ في التصدير:', error);
                            alert('حدث خطأ أثناء تصدير البيانات. يرجى المحاولة مرة أخرى.');
                        }
                    }
                };

                document.addEventListener('DOMContentLoaded', function() {
                    // تحديث الإحصائيات كل 30 ثانية
                    setInterval(function() {
                        fetch('{{ route('admin.operation-reports.index') }}')
                            .then(response => response.text())
                            .then(html => {
                                // يمكن تحديث جزء من الصفحة هنا
                            })
                            .catch(error => console.log('Error updating stats:', error));
                    }, 30000);
                });
            </script>
        @endpush
