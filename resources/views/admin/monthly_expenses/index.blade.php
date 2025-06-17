@extends('layouts.app')

@section('title', 'إدارة المصاريف الشهرية')

@section('content')
    <div class="container-fluid py-3">
        <h1 class="h3 mb-4 text-gray-800">إدارة المصاريف الشهرية</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <!-- حاسبة الربح -->
                    <div class="col-md-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">حساب الربح في فترة محددة</h6>
                            </div>
                            <div class="card-body">
                                <form id="profit-calculator-form">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label">من تاريخ</label>
                                            <input type="text" class="form-control datepicker" id="start_date"
                                                name="start_date" placeholder="يوم - شهر - سنة " required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label">إلى تاريخ</label>
                                            <input type="text" class="form-control datepicker" id="end_date"
                                                name="end_date" placeholder="يوم - شهر - سنة " required>
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-calculator me-1"></i> احسب الربح في الفترة
                                        </button>
                                    </div>
                                </form>

                                <div id="profit-result" class="mt-3 d-none">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title">نتيجة الحساب</h5>
                                            <p class="mb-1">الشهر: <span id="month-year-display" class="fw-bold"></span>
                                            </p>
                                            <p class="mb-1">عدد الحجوزات: <span id="bookings-count"
                                                    class="fw-bold"></span></p>

                                            <div class="mb-1">إجمالي الربح في الفترة:</div>
                                            <div id="profit-display" class="fw-bold text-success mb-2">
                                                <!-- ستتم إضافة عناصر الأرباح بالعملات المختلفة هنا -->
                                            </div>

                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-success" id="use-result-btn">استخدم هذه
                                                    النتيجة</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- نموذج تسجيل المصاريف -->
                    <div class="col-md-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">تسجيل المصاريف الشهرية</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.monthly-expenses.store') }}" method="POST"
                                    id="expenses-form">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="month_year" class="form-label">الشهر</label>
                                            <input type="text" class="form-control" id="month_year" name="month_year"
                                                required readonly>
                                        </div>
                                    </div>

                                    <!-- حقول التواريخ المخفية -->
                                    <input type="hidden" id="start_date_hidden" name="start_date">
                                    <input type="hidden" id="end_date_hidden" name="end_date">

                                    <!-- المصاريف الثابتة -->
                                    <h5 class="mt-4 mb-3">المصاريف الشهرية</h5>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label for="salaries" class="form-label">إجمالي الرواتب</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0"
                                                    class="form-control expense-field" id="salaries" name="salaries"
                                                    required value="0">
                                                <select class="form-select currency-select" name="salaries_currency"
                                                    style="max-width: 100px">
                                                    <option value="SAR" selected>ريال</option>
                                                    <option value="KWD">دينار</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="advertising" class="form-label">إجمالي الإعلانات</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0"
                                                    class="form-control expense-field" id="advertising"
                                                    name="advertising" required value="0">
                                                <select class="form-select currency-select" name="advertising_currency"
                                                    style="max-width: 100px">
                                                    <option value="SAR" selected>ريال</option>
                                                    <option value="KWD">دينار</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label for="rent" class="form-label">الإيجار</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0"
                                                    class="form-control expense-field" id="rent" name="rent"
                                                    required value="0">
                                                <select class="form-select currency-select" name="rent_currency"
                                                    style="max-width: 100px">
                                                    <option value="SAR" selected>ريال</option>
                                                    <option value="KWD">دينار</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="staff_commissions" class="form-label">عمولات الموظفين</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0"
                                                    class="form-control expense-field" id="staff_commissions"
                                                    name="staff_commissions" required value="0">
                                                <select class="form-select currency-select"
                                                    name="staff_commissions_currency" style="max-width: 100px">
                                                    <option value="SAR" selected>ريال</option>
                                                    <option value="KWD">دينار</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- مصاريف إضافية ديناميكية -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-3">مصاريف إضافية</h5>
                                            <button type="button" id="add-expense-btn"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-plus"></i> إضافة مصروف
                                            </button>
                                        </div>
                                        <div id="other-expenses-container">
                                            <!-- ستضاف حقول المصاريف الإضافية هنا بالجافاسكريبت -->
                                        </div>
                                    </div>
                                    <!-- قسم تحويل العملات -->
                                    <div class="card border-warning mb-4 mt-4">
                                        <div class="card-header bg-warning bg-opacity-10 text-warning">
                                            <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> توحيد العملات
                                                وتحويل الأرباح</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold">توحيد الحسابات بعملة:</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="unified_currency" id="currency_sar" value="SAR"
                                                            checked>
                                                        <label class="form-check-label" for="currency_sar">ريال سعودي
                                                            (SAR)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="unified_currency" id="currency_kwd" value="KWD">
                                                        <label class="form-check-label" for="currency_kwd">دينار كويتي
                                                            (KWD)</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="exchange_rate" class="form-label fw-bold">سعر
                                                        الصرف:</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">1 دينار =</span>
                                                        <input type="number" step="0.01" min="1"
                                                            class="form-control" id="exchange_rate" value="12.27">
                                                        <span class="input-group-text">ريال</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 pt-4">
                                                    <button type="button" id="convert_currency_btn"
                                                        class="btn btn-warning w-100">
                                                        <i class="fas fa-sync-alt me-1"></i> تحويل وتوحيد العملات
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="conversion_result" class="alert alert-info d-none">
                                                <div class="fw-bold">نتيجة التحويل:</div>
                                                <div id="conversion_details"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- حساب الربح -->
                                    <h5 class="mt-4 mb-3">حساب الربح</h5>
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">إجمالي الربح الشهري</h6>

                                                    <!-- ريال سعودي -->
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text bg-success text-white"><i
                                                                class="fas fa-money-bill-wave"></i> ريال</span>
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="total_monthly_profit_SAR"
                                                            name="total_monthly_profit_SAR" required value="0"
                                                            readonly>
                                                    </div>

                                                    <!-- دينار كويتي -->
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-primary text-white"><i
                                                                class="fas fa-money-bill-wave"></i> دينار</span>
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="total_monthly_profit_KWD"
                                                            name="total_monthly_profit_KWD" required value="0"
                                                            readonly>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">إجمالي المصاريف</h6>

                                                    <!-- ريال سعودي -->
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text bg-success text-white"><i
                                                                class="fas fa-money-bill-wave"></i> ريال</span>
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="total_expenses_display_SAR" readonly
                                                            value="0">
                                                    </div>

                                                    <!-- دينار كويتي -->
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-primary text-white"><i
                                                                class="fas fa-money-bill-wave"></i> دينار</span>
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="total_expenses_display_KWD" readonly
                                                            value="0">
                                                    </div>

                                                    <!-- إجمالي المصاريف الإجمالي (للتوافق مع الكود القديم) -->
                                                    <input type="hidden" id="total_expenses" name="total_expenses"
                                                        value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- إضافة الأقسام الجديدة بدلاً من الأقسام القديمة -->
                                    <h5 class="mt-4 mb-3">صافي الربح وتوزيع الأرباح</h5>
                                    <!-- بطاقة الريال السعودي -->
                                    <div class="card mb-3 border-success">
                                        <div class="card-header bg-success bg-opacity-10 text-success">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-1"></i> الأرباح
                                                    بالريال السعودي</h6>
                                                <span class="badge bg-success">SAR</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">صافي الربح</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="net_profit_SAR"
                                                            name="net_profit_SAR" readonly value="0">
                                                        <span class="input-group-text">ريال</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">نصيب ش. إسماعيل (50%)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="ismail_share_SAR"
                                                            name="ismail_share_SAR" readonly value="0">
                                                        <span class="input-group-text">ريال</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">نصيب ش . محمد حسن (50%)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="mohamed_share_SAR"
                                                            name="mohamed_share_SAR" readonly value="0">
                                                        <span class="input-group-text">ريال</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- بطاقة الدينار الكويتي -->
                                    <div class="card mb-3 border-primary">
                                        <div class="card-header bg-primary bg-opacity-10 text-primary">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-1"></i> الأرباح
                                                    بالدينار الكويتي</h6>
                                                <span class="badge bg-primary">KWD</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">صافي الربح</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="net_profit_KWD"
                                                            name="net_profit_KWD" readonly value="0">
                                                        <span class="input-group-text">دينار</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">نصيب ش. إسماعيل (50%)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="ismail_share_KWD"
                                                            name="ismail_share_KWD" readonly value="0">
                                                        <span class="input-group-text">دينار</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">نصيب ش . محمد حسن (50%)</label>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0"
                                                            class="form-control" id="mohamed_share_KWD"
                                                            name="mohamed_share_KWD" readonly value="0">
                                                        <span class="input-group-text">دينار</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ملاحظات -->
                                    <div class="row mb-3 mt-4">
                                        <div class="col-12">
                                            <label for="notes" class="form-label">ملاحظات</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-save me-1"></i> حفظ البيانات
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدول السجلات السابقة -->
                <div class="table-responsive">
                    <table class="table align-middle shadow-sm border-0 rounded-3 custom-expenses-table">
                        <thead class="table-light">
                            <tr class="text-center align-middle">
                                <th>#</th>
                                <th>الشهر</th>
                                <th>الفترة</th>
                                <th>إجمالي المصاريف</th>
                                <th>الأرباح والعملات</th>
                                <th>صافي الربح</th>
                                <th>تاريخ الإضافة</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $index => $expense)
                                <tr class="text-center">
                                    <td class="fw-bold">{{ $expenses->firstItem() + $index }}</td>
                                    <td class="p-0 align-middle text-center">
                                        <div class="bg-light-subtle rounded-4 shadow-sm d-inline-block p-2 mb-1 w-100"
                                            style="min-width: 120px; max-width: 170px;">
                                            <span class="fw-bold text-dark d-block" style="font-size: 1.1rem;">
                                                {{ $expense->month_year }}
                                            </span>
                                            <span class="d-block text-muted small mt-1" style="line-height:1.4;">
                                                من<br>
                                                <span class="fw-bold">{{ $expense->start_date->format('d-m-Y') }}</span>
                                                <br>
                                                إلى<br>
                                                <span class="fw-bold">{{ $expense->end_date->format('d-m-Y') }}</span>
                                            </span>
                                        </div>
                                    </td>


                                    <td>
                                        <span
                                            class="badge bg-secondary-subtle text-dark px-3 py-2 rounded-pill shadow-sm d-inline-flex align-items-center"
                                            style="font-size:15px;">
                                            <i class="fa-solid fa-calendar-days me-1 text-info"></i>
                                            {{ $expense->start_date->format('Y-m-d') }}
                                            <span class="mx-1 text-muted" style="font-size:18px;">→</span>
                                            {{ $expense->end_date->format('Y-m-d') }}
                                        </span>
                                    </td>

<td>
    @php
        $totalSAR = 0;
        $totalKWD = 0;
        
        // جلب معلومات العملات من expenses_currencies
        $currencies = $expense->expenses_currencies ?? [];
        
        // حساب المصاريف بالريال
        if(isset($currencies['salaries']) && $currencies['salaries'] === 'SAR') {
            $totalSAR += $expense->salaries;
        }
        if(isset($currencies['advertising']) && $currencies['advertising'] === 'SAR') {
            $totalSAR += $expense->advertising;
        }
        if(isset($currencies['rent']) && $currencies['rent'] === 'SAR') {
            $totalSAR += $expense->rent;
        }
        if(isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'SAR') {
            $totalSAR += $expense->staff_commissions;
        }
        
        // حساب المصاريف بالدينار
        if(isset($currencies['salaries']) && $currencies['salaries'] === 'KWD') {
            $totalKWD += $expense->salaries;
        }
        if(isset($currencies['advertising']) && $currencies['advertising'] === 'KWD') {
            $totalKWD += $expense->advertising;
        }
        if(isset($currencies['rent']) && $currencies['rent'] === 'KWD') {
            $totalKWD += $expense->rent;
        }
        if(isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'KWD') {
            $totalKWD += $expense->staff_commissions;
        }
        
        // إضافة المصاريف الأخرى
        if(!empty($expense->other_expenses)) {
            foreach($expense->other_expenses as $otherExpense) {
                if(isset($otherExpense['currency'])) {
                    if($otherExpense['currency'] === 'SAR') {
                        $totalSAR += $otherExpense['amount'];
                    } elseif($otherExpense['currency'] === 'KWD') {
                        $totalKWD += $otherExpense['amount'];
                    }
                }
            }
        }
    @endphp
    
    @if($totalSAR > 0)
        <span class="badge bg-success fs-6 mb-1 rounded-pill shadow-sm d-block">
            <i class="fa-solid fa-receipt me-1"></i>
            {{ number_format($totalSAR, 2) }} ريال
        </span>
    @endif
    @if($totalKWD > 0)
        <span class="badge bg-primary fs-6 mb-1 rounded-pill shadow-sm d-block">
            <i class="fa-solid fa-receipt me-1"></i>
            {{ number_format($totalKWD, 2) }} دينار
        </span>
    @endif
    
    @if($totalSAR == 0 && $totalKWD == 0)
        <span class="badge bg-secondary fs-6 rounded-pill">لا توجد مصاريف</span>
    @endif
</td>

                                    <td>
                                        @if ($expense->total_monthly_profit_SAR > 0)
                                            <span class="badge bg-success fs-6   mb-1 rounded-pill shadow-sm">
                                                <i class="fa-solid fa-money-bill-wave me-1"></i>
                                                {{ number_format($expense->total_monthly_profit_SAR, 2) }} ريال
                                            </span><br>
                                        @endif
                                        @if ($expense->total_monthly_profit_KWD > 0)
                                            <span class="badge bg-primary fs-6   mb-1 rounded-pill shadow-sm">
                                                <i class="fa-solid fa-coins me-1"></i>
                                                {{ number_format($expense->total_monthly_profit_KWD, 2) }} دينار
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($expense->net_profit_SAR > 0)
                                            <span class="badge bg-gradient bg-success fs-6   mb-1 rounded-pill shadow">
                                                <i class="fa-solid fa-check-circle me-1"></i>
                                                {{ number_format($expense->net_profit_SAR, 2) }} ريال
                                            </span><br>
                                        @endif
                                        @if ($expense->net_profit_KWD > 0)
                                            <span class="badge bg-gradient bg-primary fs-6   mb-1 rounded-pill shadow">
                                                <i class="fa-solid fa-check-circle me-1"></i>
                                                {{ number_format($expense->net_profit_KWD, 2) }} دينار
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-light text-dark  rounded-pill shadow-sm d-inline-flex align-items-center"
                                            style="font-size:15px;">
                                            <i class="bi bi-clock-history me-1 text-primary"></i>
                                            {{ $expense->created_at->format('Y-m-d') }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.monthly-expenses.show', $expense->id) }}"
                                                class="btn btn-outline-info" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger delete-expense-btn"
                                                data-expense-id="{{ $expense->id }}"
                                                data-month="{{ $expense->month_year }}" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $expense->id }}"
                                            action="{{ route('admin.monthly-expenses.destroy', $expense->id) }}"
                                            method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    </div>

    <!-- Modal لتأكيد الحذف -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    هل أنت متأكد من حذف سجل المصاريف لشهر <span id="delete-month-name" class="fw-bold"></span>؟
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn">حذف</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let expenseCounter = 0;
            let calculatedProfit = 0;
            let startDateValue = '';
            let endDateValue = '';

            // إضافة مصروف جديد
            document.getElementById('add-expense-btn').addEventListener('click', function() {
                const container = document.getElementById('other-expenses-container');
                const expenseRow = document.createElement('div');
                expenseRow.classList.add('row', 'mb-2', 'expense-row');
                expenseRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control" name="other_expenses[${expenseCounter}][name]" placeholder="اسم المصروف" required>
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    <input type="number" step="0.01" min="0" class="form-control expense-field other-expense-amount" name="other_expenses[${expenseCounter}][amount]" placeholder="المبلغ" required value="0">
                    <select class="form-select currency-select" name="other_expenses[${expenseCounter}][currency]" style="max-width: 100px">
                        <option value="SAR" selected>ريال</option>
                        <option value="KWD">دينار</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-expense-btn w-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
                container.appendChild(expenseRow);
                expenseCounter++;

                // تحديث العملة المختارة عند إضافة مصروف جديد
                const newCurrencySelect = expenseRow.querySelector('.currency-select');
                newCurrencySelect.addEventListener('change', recalculateExpenses);

                // إعادة حساب إجمالي المصاريف
                recalculateExpenses();
            });

            // حذف مصروف إضافي
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-expense-btn') || e.target.closest(
                        '.remove-expense-btn')) {
                    const button = e.target.classList.contains('remove-expense-btn') ? e.target : e.target
                        .closest('.remove-expense-btn');
                    button.closest('.expense-row').remove();
                    recalculateExpenses();
                }
            });

            // حساب إجمالي المصاريف حسب العملة - الدالة المصححة
            function recalculateExpenses() {
                let totalExpensesSAR = 0;
                let totalExpensesKWD = 0;

                // المصاريف الثابتة
                document.querySelectorAll('.expense-field').forEach(function(input) {
                    if (!input.classList.contains('other-expense-amount')) {
                        const amount = parseFloat(input.value) || 0;
                        const currencySelect = input.nextElementSibling;
                        const currency = currencySelect ? currencySelect.value : 'SAR';

                        if (currency === 'SAR') {
                            totalExpensesSAR += amount;
                        } else if (currency === 'KWD') {
                            totalExpensesKWD += amount;
                        }
                    }
                });

                // المصاريف الإضافية
                document.querySelectorAll('.other-expense-amount').forEach(function(input) {
                    const amount = parseFloat(input.value) || 0;
                    const currencySelect = input.nextElementSibling;
                    const currency = currencySelect ? currencySelect.value : 'SAR';

                    if (currency === 'SAR') {
                        totalExpensesSAR += amount;
                    } else if (currency === 'KWD') {
                        totalExpensesKWD += amount;
                    }
                });

                // عرض إجمالي المصاريف حسب العملة
                document.getElementById('total_expenses_display_SAR').value = totalExpensesSAR.toFixed(2);
                document.getElementById('total_expenses_display_KWD').value = totalExpensesKWD.toFixed(2);

                // تعيين إجمالي المصاريف بالريال للتوافق مع الكود القديم
                document.getElementById('total_expenses').value = totalExpensesSAR.toFixed(2);

                // تخزين القيم للاستخدام في التحويل - إضافة قيم التحكم
                console.log("تحديث المصاريف:", {
                    SAR: totalExpensesSAR,
                    KWD: totalExpensesKWD
                });
                window.totalExpensesSAR = totalExpensesSAR;
                window.totalExpensesKWD = totalExpensesKWD;

                // إعادة حساب صافي الربح
                calculateNetProfit();
            }

            // حساب صافي الربح وتوزيع الأرباح
            function calculateNetProfit() {
                console.log("حساب صافي الربح - المصاريف:", window.totalExpensesSAR, window.totalExpensesKWD);

                // التأكد من أن قيم المصاريف مُعرَّفة
                window.totalExpensesSAR = window.totalExpensesSAR || 0;
                window.totalExpensesKWD = window.totalExpensesKWD || 0;

                // تحقق من وجود العملة الموحدة
                const isUnifiedSAR = window.totalExpensesKWD === 0 && window.totalExpensesSAR > 0;
                const isUnifiedKWD = window.totalExpensesSAR === 0 && window.totalExpensesKWD > 0;

                // ريال سعودي
                const totalProfitSAR = parseFloat(document.getElementById('total_monthly_profit_SAR').value) || 0;
                const expensesSAR = parseFloat(window.totalExpensesSAR) || 0;
                const netProfitSAR = Math.max(0, totalProfitSAR - expensesSAR);

                // دينار كويتي
                const totalProfitKWD = parseFloat(document.getElementById('total_monthly_profit_KWD').value) || 0;
                const expensesKWD = parseFloat(window.totalExpensesKWD) || 0;
                const netProfitKWD = Math.max(0, totalProfitKWD - expensesKWD);

                // ✅ عرض النتائج بالعملة الصحيحة
                if (isUnifiedSAR) {
                    // إذا تم توحيد العملة بالريال السعودي
                    document.getElementById('net_profit_SAR').value = netProfitSAR.toFixed(2);
                    document.getElementById('net_profit_KWD').value = "0.00";

                    // توزيع الأرباح بالريال (50% لكل شريك)
                    const ismailShareSAR = netProfitSAR * 0.5;
                    const mohamedShareSAR = netProfitSAR * 0.5;
                    document.getElementById('ismail_share_SAR').value = ismailShareSAR.toFixed(2);
                    document.getElementById('mohamed_share_SAR').value = mohamedShareSAR.toFixed(2);

                    // تصفير الأرباح بالدينار
                    document.getElementById('ismail_share_KWD').value = "0.00";
                    document.getElementById('mohamed_share_KWD').value = "0.00";

                    // تطبيق التنسيق المناسب
                    document.getElementById('net_profit_SAR').classList.add('fw-bold', 'bg-success',
                        'bg-opacity-10');
                    document.getElementById('net_profit_KWD').classList.add('text-muted');

                } else if (isUnifiedKWD) {
                    // إذا تم توحيد العملة بالدينار الكويتي
                    document.getElementById('net_profit_SAR').value = "0.00";
                    document.getElementById('net_profit_KWD').value = netProfitKWD.toFixed(2);

                    // تصفير الأرباح بالريال
                    document.getElementById('ismail_share_SAR').value = "0.00";
                    document.getElementById('mohamed_share_SAR').value = "0.00";

                    // توزيع الأرباح بالدينار (50% لكل شريك)
                    const ismailShareKWD = netProfitKWD * 0.5;
                    const mohamedShareKWD = netProfitKWD * 0.5;
                    document.getElementById('ismail_share_KWD').value = ismailShareKWD.toFixed(2);
                    document.getElementById('mohamed_share_KWD').value = mohamedShareKWD.toFixed(2);

                    // تطبيق التنسيق المناسب
                    document.getElementById('net_profit_KWD').classList.add('fw-bold', 'bg-primary',
                        'bg-opacity-10');
                    document.getElementById('net_profit_SAR').classList.add('text-muted');

                } else {
                    // ✅ الحالة العادية - عرض العملات بناءً على البيانات الفعلية
                    const profitsByCurrency = window.calculatedProfitsByCurrency || {};

                    // إذا كان هناك أرباح بالدينار فقط
                    if (profitsByCurrency.KWD > 0 && (!profitsByCurrency.SAR || profitsByCurrency.SAR === 0)) {
                        document.getElementById('net_profit_KWD').value = netProfitKWD.toFixed(2);
                        document.getElementById('net_profit_SAR').value = "0.00";

                        const ismailShareKWD = netProfitKWD * 0.5;
                        const mohamedShareKWD = netProfitKWD * 0.5;
                        document.getElementById('ismail_share_KWD').value = ismailShareKWD.toFixed(2);
                        document.getElementById('mohamed_share_KWD').value = mohamedShareKWD.toFixed(2);
                        document.getElementById('ismail_share_SAR').value = "0.00";
                        document.getElementById('mohamed_share_SAR').value = "0.00";

                    } else if (profitsByCurrency.SAR > 0 && (!profitsByCurrency.KWD || profitsByCurrency.KWD ===
                            0)) {
                        // إذا كان هناك أرباح بالريال فقط
                        document.getElementById('net_profit_SAR').value = netProfitSAR.toFixed(2);
                        document.getElementById('net_profit_KWD').value = "0.00";

                        const ismailShareSAR = netProfitSAR * 0.5;
                        const mohamedShareSAR = netProfitSAR * 0.5;
                        document.getElementById('ismail_share_SAR').value = ismailShareSAR.toFixed(2);
                        document.getElementById('mohamed_share_SAR').value = mohamedShareSAR.toFixed(2);
                        document.getElementById('ismail_share_KWD').value = "0.00";
                        document.getElementById('mohamed_share_KWD').value = "0.00";

                    } else {
                        // إذا كان هناك أرباح بالعملتين أو لا يوجد أرباح
                        document.getElementById('net_profit_SAR').value = netProfitSAR.toFixed(2);
                        document.getElementById('net_profit_KWD').value = netProfitKWD.toFixed(2);

                        // توزيع الأرباح بالريال (50% لكل شريك)
                        const ismailShareSAR = netProfitSAR * 0.5;
                        const mohamedShareSAR = netProfitSAR * 0.5;
                        document.getElementById('ismail_share_SAR').value = ismailShareSAR.toFixed(2);
                        document.getElementById('mohamed_share_SAR').value = mohamedShareSAR.toFixed(2);

                        // توزيع الأرباح بالدينار (50% لكل شريك)
                        const ismailShareKWD = netProfitKWD * 0.5;
                        const mohamedShareKWD = netProfitKWD * 0.5;
                        document.getElementById('ismail_share_KWD').value = ismailShareKWD.toFixed(2);
                        document.getElementById('mohamed_share_KWD').value = mohamedShareKWD.toFixed(2);
                    }

                    // إزالة التنسيق الخاص
                    document.getElementById('net_profit_SAR').classList.remove('fw-bold', 'bg-success',
                        'bg-opacity-10', 'text-muted');
                    document.getElementById('net_profit_KWD').classList.remove('fw-bold', 'bg-primary',
                        'bg-opacity-10', 'text-muted');
                }

                console.log("صافي الربح:", {
                    isUnifiedSAR,
                    isUnifiedKWD,
                    SAR: netProfitSAR,
                    KWD: netProfitKWD
                });
            }

            // تحديث الحسابات عند تغيير أي من حقول المصاريف
            document.querySelectorAll('.expense-field').forEach(function(input) {
                input.addEventListener('input', recalculateExpenses);
                // إعادة حساب صافي الربح
                calculateNetProfit();
            });

            // نموذج حساب الربح
            document.getElementById('profit-calculator-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;

                if (!startDate || !endDate) {
                    alert('يرجى تحديد تاريخ البداية والنهاية');
                    return;
                }

                // إرسال طلب AJAX لحساب الربح
                fetch('{{ route('admin.calculate-profit') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            start_date: startDate,
                            end_date: endDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // عرض النتيجة
                        document.getElementById('profit-result').classList.remove('d-none');
                        document.getElementById('month-year-display').textContent = data.month_year;
                        document.getElementById('bookings-count').textContent = data.bookings_count;

                        // تفريغ محتوى عرض الأرباح
                        const profitDisplay = document.getElementById('profit-display');
                        profitDisplay.innerHTML = '';

                        // عرض الأرباح حسب العملة
                        let hasData = false;
                        let displayText = '';
                        for (const [currency, profit] of Object.entries(data.profits_by_currency)) {
                            if (profit > 0) {
                                hasData = true;
                                const currencyLabel = currency === 'SAR' ? 'ريال' : 'دينار';
                                displayText +=
                                    `<div class="mb-1 text-${currency === 'SAR' ? 'success' : 'primary'}">${profit.toFixed(2)} ${currencyLabel}</div>`;
                            }
                        }

                        // إذا لم تكن هناك أرباح، استخدم العملة الأساسية من الاستجابة
                        if (!hasData) {
                            const defaultCurrency = data.primary_currency || 'SAR';
                            const defaultLabel = defaultCurrency === 'SAR' ? 'ريال' : 'دينار';
                            profitDisplay.innerHTML = `<div>0.00 ${defaultLabel}</div>`;
                        } else {
                            profitDisplay.innerHTML = displayText;
                        }

                        // حفظ قيم الأرباح حسب العملة للاستخدام لاحقاً
                        window.calculatedProfitsByCurrency = data.profits_by_currency;
                        window.calculatedTotalProfit = data.total_profit;
                        window.startDateValue = data.start_date;
                        window.endDateValue = data.end_date;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ أثناء حساب الربح');
                    });
            });

            // استخدام نتيجة الحساب
            document.getElementById('use-result-btn').addEventListener('click', function() {
                document.getElementById('month_year').value = document.getElementById('month-year-display')
                    .textContent +
                    '(من ' + window.startDateValue + ' إلى ' + window.endDateValue + ')';

                // ✅ تحديث الحقول بناءً على العملة الفعلية
                const profitsByCurrency = window.calculatedProfitsByCurrency;
                const primaryCurrency = Object.keys(profitsByCurrency).reduce((a, b) =>
                    profitsByCurrency[a] > profitsByCurrency[b] ? a : b, 'SAR');

                // تصفير جميع الحقول أولاً
                document.getElementById('total_monthly_profit_SAR').value = '0.00';
                document.getElementById('total_monthly_profit_KWD').value = '0.00';

                // ملء الحقول بناءً على البيانات الفعلية
                for (const [currency, amount] of Object.entries(profitsByCurrency)) {
                    if (amount > 0) {
                        document.getElementById(`total_monthly_profit_${currency}`).value = amount.toFixed(
                            2);
                    }
                }

                document.getElementById('start_date_hidden').value = window.startDateValue;
                document.getElementById('end_date_hidden').value = window.endDateValue;

                // ✅ تحديث تفاصيل الأرباح مع العملات الصحيحة
                let profitDetailsDiv = document.getElementById('profit-details');
                if (!profitDetailsDiv) {
                    profitDetailsDiv = document.createElement('div');
                    profitDetailsDiv.id = 'profit-details';
                    profitDetailsDiv.className = 'alert alert-info mt-2';
                    const profitCardBody = document.querySelector('.card-body h6.card-title').closest(
                        '.card-body');
                    if (profitCardBody) {
                        profitCardBody.appendChild(profitDetailsDiv);
                    }
                }

                let detailsHTML = '<strong>تفاصيل الأرباح حسب العملة:</strong><br>';
                for (const [currency, amount] of Object.entries(profitsByCurrency)) {
                    if (amount > 0) {
                        const currencyLabel = currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي';
                        const badgeClass = currency === 'SAR' ? 'text-currency-sar' : 'text-currency-kwd';
                        detailsHTML +=
                            `<span class="currency-badge ${badgeClass}">${amount.toFixed(2)} ${currencyLabel}</span> `;
                    }
                }

                profitDetailsDiv.innerHTML = detailsHTML;

                // إضافة حقول مخفية لقيم كل عملة (للتوافق)
                for (const [currency, profit] of Object.entries(window.calculatedProfitsByCurrency)) {
                    let hiddenInput = document.getElementById(`profit_${currency}`);
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.id = `profit_${currency}`;
                        hiddenInput.name = `profit_${currency}`;
                        document.getElementById('expenses-form').appendChild(hiddenInput);
                    }
                    hiddenInput.value = profit.toFixed(2);
                }

                // إعادة حساب صافي الربح
                recalculateExpenses();
            });

            // تأكيد حذف السجل
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            let expenseIdToDelete = null;

            document.querySelectorAll('.delete-expense-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    expenseIdToDelete = this.dataset.expenseId;
                    document.getElementById('delete-month-name').textContent = this.dataset
                        .month;
                    deleteModal.show();
                });
            });

            document.getElementById('confirm-delete-btn').addEventListener('click', function() {
                if (expenseIdToDelete) {
                    document.getElementById(`delete-form-${expenseIdToDelete}`).submit();
                }
                deleteModal.hide();
            });

            // تحويل العملات - الكود المحدث
            document.getElementById('convert_currency_btn').addEventListener('click', function() {
                const unifiedCurrency = document.querySelector('input[name="unified_currency"]:checked')
                    .value;
                const exchangeRate = parseFloat(document.getElementById('exchange_rate').value) ||
                    12.27;

                // حفظ القيم الأصلية
                const originalValues = {
                    profit_SAR: parseFloat(document.getElementById('total_monthly_profit_SAR')
                            .value) ||
                        0,
                    profit_KWD: parseFloat(document.getElementById('total_monthly_profit_KWD')
                            .value) ||
                        0,
                    expenses_SAR: window.totalExpensesSAR || 0,
                    expenses_KWD: window.totalExpensesKWD || 0
                };

                // تخزين القيم الأصلية للرجوع لها عند الحاجة
                if (!window.originalValues) {
                    window.originalValues = originalValues;
                }

                // المتغيرات للقيم المحولة
                let unifiedProfit = 0;
                let unifiedExpenses = 0;
                let conversionDetails = '';

                if (unifiedCurrency === 'SAR') {
                    // تحويل الدينار إلى ريال
                    const convertedProfit = originalValues.profit_KWD * exchangeRate;
                    const convertedExpenses = originalValues.expenses_KWD * exchangeRate;

                    unifiedProfit = originalValues.profit_SAR + convertedProfit;
                    unifiedExpenses = originalValues.expenses_SAR + convertedExpenses;

                    conversionDetails = `
                <div class="alert alert-info mb-3">
                    <div class="fw-bold mb-2">تم توحيد جميع الحسابات بعملة الريال السعودي</div>
                    <div class="mb-2"><strong>الأرباح:</strong></div>
                    <div class="ms-3">- بالريال (أصلي): ${originalValues.profit_SAR.toFixed(2)} ريال</div>
                    <div class="ms-3">- بالدينار (أصلي): ${originalValues.profit_KWD.toFixed(2)} دينار</div>
                    <div class="ms-3">- تحويل الدينار: ${originalValues.profit_KWD.toFixed(2)} دينار = ${convertedProfit.toFixed(2)} ريال</div>
                    <div class="ms-3 fw-bold text-success">- الإجمالي بعد التحويل: ${unifiedProfit.toFixed(2)} ريال</div>
                    <hr>
                    <div class="mb-2"><strong>المصاريف:</strong></div>
                    <div class="ms-3">- بالريال (أصلي): ${originalValues.expenses_SAR.toFixed(2)} ريال</div>
                    <div class="ms-3">- بالدينار (أصلي): ${originalValues.expenses_KWD.toFixed(2)} دينار</div>
                    <div class="ms-3">- تحويل الدينار: ${originalValues.expenses_KWD.toFixed(2)} دينار = ${convertedExpenses.toFixed(2)} ريال</div>
                    <div class="ms-3 fw-bold text-danger">- الإجمالي بعد التحويل: ${unifiedExpenses.toFixed(2)} ريال</div>
                    <hr>
                    <div class="fw-bold text-success">صافي الربح الموحد: ${Math.max(0, unifiedProfit - unifiedExpenses).toFixed(2)} ريال</div>
                </div>
            `;

                    // تعليم الحقل النشط وتعطيل الحقل الآخر
                    markActiveField('SAR', unifiedProfit, originalValues.profit_KWD);

                    // تحديث قيم حقول المصاريف
                    document.getElementById('total_expenses_display_SAR').value = unifiedExpenses
                        .toFixed(
                            2);
                    document.getElementById('total_expenses_display_SAR').classList.add('fw-bold',
                        'bg-success', 'bg-opacity-10');
                    document.getElementById('total_expenses_display_KWD').classList.add('text-muted');

                    // تحديث المتغيرات العامة
                    window.totalExpensesSAR = unifiedExpenses;
                    window.totalExpensesKWD = 0; // صفر لحساب صافي الربح بشكل صحيح

                    // للتوافق مع الكود القديم
                    document.getElementById('total_expenses').value = unifiedExpenses.toFixed(2);
                } else {
                    // تحويل الريال إلى دينار
                    const convertedProfit = originalValues.profit_SAR / exchangeRate;
                    const convertedExpenses = originalValues.expenses_SAR / exchangeRate;

                    unifiedProfit = originalValues.profit_KWD + convertedProfit;
                    unifiedExpenses = originalValues.expenses_KWD + convertedExpenses;

                    conversionDetails = `
                <div class="alert alert-info mb-3">
                    <div class="fw-bold mb-2">تم توحيد جميع الحسابات بعملة الدينار الكويتي</div>
                    <div class="mb-2"><strong>الأرباح:</strong></div>
                    <div class="ms-3">- بالدينار (أصلي): ${originalValues.profit_KWD.toFixed(2)} دينار</div>
                    <div class="ms-3">- بالريال (أصلي): ${originalValues.profit_SAR.toFixed(2)} ريال</div>
                    <div class="ms-3">- تحويل الريال: ${originalValues.profit_SAR.toFixed(2)} ريال = ${convertedProfit.toFixed(2)} دينار</div>
                    <div class="ms-3 fw-bold text-success">- الإجمالي بعد التحويل: ${unifiedProfit.toFixed(2)} دينار</div>
                    <hr>
                    <div class="mb-2"><strong>المصاريف:</strong></div>
                    <div class="ms-3">- بالدينار (أصلي): ${originalValues.expenses_KWD.toFixed(2)} دينار</div>
                    <div class="ms-3">- بالريال (أصلي): ${originalValues.expenses_SAR.toFixed(2)} ريال</div>
                    <div class="ms-3">- تحويل الريال: ${originalValues.expenses_SAR.toFixed(2)} ريال = ${convertedExpenses.toFixed(2)} دينار</div>
                    <div class="ms-3 fw-bold text-danger">- الإجمالي بعد التحويل: ${unifiedExpenses.toFixed(2)} دينار</div>
                    <hr>
                    <div class="fw-bold text-success">صافي الربح الموحد: ${Math.max(0, unifiedProfit - unifiedExpenses).toFixed(2)} دينار</div>
                </div>
            `;

                    // تعليم الحقل النشط وتعطيل الحقل الآخر
                    markActiveField('KWD', originalValues.profit_SAR, unifiedProfit);

                    // تحديث قيم حقول المصاريف
                    document.getElementById('total_expenses_display_KWD').value = unifiedExpenses
                        .toFixed(
                            2);
                    document.getElementById('total_expenses_display_KWD').classList.add('fw-bold',
                        'bg-primary', 'bg-opacity-10');
                    document.getElementById('total_expenses_display_SAR').classList.add('text-muted');

                    // تحديث المتغيرات العامة
                    window.totalExpensesKWD = unifiedExpenses;
                    window.totalExpensesSAR = 0; // صفر لحساب صافي الربح بشكل صحيح

                    // // للتوافق مع الكود القديم
                    // document.getElementById('total_expenses').value = "0.00";
                }

                // إضافة زر للرجوع للقيم الأصلية
                conversionDetails += `
            <div class="text-center mt-2">
                <button type="button" class="btn btn-outline-secondary" id="restore_original_values">
                    <i class="fas fa-undo me-1"></i> العودة للقيم الأصلية
                </button>
            </div>
        `;

                // عرض نتيجة التحويل
                document.getElementById('conversion_result').classList.remove('d-none');
                document.getElementById('conversion_details').innerHTML = conversionDetails;

                // إضافة مستمع حدث لزر استعادة القيم الأصلية
                document.getElementById('restore_original_values').addEventListener('click',
                    function() {
                        restoreOriginalValues();
                    });

                // إعادة حساب صافي الربح بعد التحويل
                calculateNetProfit();
            });

            // دالة لتمييز حقول العملة النشطة وغير النشطة
            function markActiveField(activeCurrency, sarValue, kwdValue) {
                // إعادة تعيين التنسيقات أولاً
                const fields = [
                    'total_monthly_profit_SAR', 'total_monthly_profit_KWD',
                    'total_expenses_display_SAR', 'total_expenses_display_KWD'
                ];

                fields.forEach(field => {
                    const element = document.getElementById(field);
                    element.classList.remove('fw-bold', 'bg-success', 'bg-primary', 'bg-opacity-10',
                        'text-muted');
                });

                // تعيين قيم الحقول وتمييزها
                if (activeCurrency === 'SAR') {
                    document.getElementById('total_monthly_profit_SAR').value = sarValue.toFixed(2);
                    document.getElementById('total_monthly_profit_SAR').classList.add('fw-bold', 'bg-success',
                        'bg-opacity-10');
                    document.getElementById('total_monthly_profit_KWD').value = kwdValue.toFixed(2);
                    document.getElementById('total_monthly_profit_KWD').classList.add('text-muted');
                } else {
                    document.getElementById('total_monthly_profit_KWD').value = kwdValue.toFixed(2);
                    document.getElementById('total_monthly_profit_KWD').classList.add('fw-bold', 'bg-primary',
                        'bg-opacity-10');
                    document.getElementById('total_monthly_profit_SAR').value = sarValue.toFixed(2);
                    document.getElementById('total_monthly_profit_SAR').classList.add('text-muted');
                }
            }

            // استعادة القيم الأصلية
            function restoreOriginalValues() {
                if (window.originalValues) {
                    // استعادة قيم الربح
                    document.getElementById('total_monthly_profit_SAR').value = window.originalValues.profit_SAR
                        .toFixed(2);
                    document.getElementById('total_monthly_profit_KWD').value = window.originalValues.profit_KWD
                        .toFixed(2);

                    // استعادة قيم المصاريف
                    document.getElementById('total_expenses_display_SAR').value = window.originalValues
                        .expenses_SAR
                        .toFixed(2);
                    document.getElementById('total_expenses_display_KWD').value = window.originalValues
                        .expenses_KWD
                        .toFixed(2);

                    // استعادة المتغيرات العامة
                    window.totalExpensesSAR = window.originalValues.expenses_SAR;
                    window.totalExpensesKWD = window.originalValues.expenses_KWD;

                    // إزالة التنسيقات
                    const elements = [
                        'total_monthly_profit_SAR', 'total_monthly_profit_KWD',
                        'total_expenses_display_SAR', 'total_expenses_display_KWD'
                    ];

                    elements.forEach(id => {
                        const element = document.getElementById(id);
                        element.classList.remove('fw-bold', 'bg-success', 'bg-primary', 'bg-opacity-10',
                            'text-muted');
                    });

                    // إخفاء نتيجة التحويل
                    document.getElementById('conversion_result').classList.add('d-none');

                    // إعادة حساب صافي الربح
                    calculateNetProfit();
                }
            }

            // تحديث حساب المصاريف عند تغيير العملة
            document.querySelectorAll('.currency-select').forEach(function(select) {
                select.addEventListener('change', recalculateExpenses);
            });
        });
    </script>
    <script src="{{ asset('js/preventClick.js') }}"></script>
@endpush

@push('styles')
    <style>
        #profit-details {
            font-size: 0.9rem;
            padding: 0.75rem;
            border-radius: 0.25rem;
        }

        .currency-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 0.25rem 0;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .text-currency-sar {
            color: #198754;
            /* نفس لون النجاح */
        }

        .text-currency-kwd {
            color: #0d6efd;
            /* نفس لون الأساسي */
        }

        /* ظل وبُعد لبطاقات النتائج المالية */
        .custom-expenses-table th,
        .custom-expenses-table td {
            vertical-align: middle !important;
        }

        .custom-expenses-table .badge {
            font-size: 1rem !important;
            letter-spacing: 0.5px;
        }

        .custom-expenses-table tr {
            transition: box-shadow 0.15s;
        }

        .custom-expenses-table tr:hover {
            box-shadow: 0 2px 12px 0 #cacaca1f;
            background: #f7fafd !important;
        }

        .custom-expenses-table tbody tr:hover {
            background: #eaf7fa !important;
            box-shadow: 0 2px 16px 0 #c1e3ed70;
            transition: all .15s;
        }

        .custom-expenses-table .btn-group .btn:hover {
            transform: scale(1.12) rotate(-7deg);
            transition: 0.1s;
        }

        /* تصميم إضافي لجعل الخانات دائماً مناسبة للموبايل والديسكتوب */
        @media (max-width: 575px) {

            .custom-expenses-table td,
            .custom-expenses-table th {
                font-size: 13px !important;
                padding: 0.3rem 0.2rem !important;
                white-space: normal !important;
            }

            .custom-expenses-table .bg-light-subtle {
                max-width: 99vw !important;
                min-width: unset !important;
                padding-left: 4px !important;
                padding-right: 4px !important;
            }
        }
    </style>
@endpush
