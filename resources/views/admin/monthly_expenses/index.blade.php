@extends('layouts.app')

@section('title', 'إدارة المصاريف الشهرية')
<link rel="stylesheet" href="{{ asset('css/Monthly-expenses/index.css') }}">


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
                                <h6 class="m-0 font-weight-bold ">حساب الربح في فترة محددة</h6>
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
                                <h6 class="m-0 font-weight-bold ">تسجيل المصاريف الشهرية</h6>
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
                                                    <label class="form-label">نصيب محمد حسن (50%)</label>
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
                                        <div class="card-header bg-primary bg-opacity-10 ">
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
                                                    <label class="form-label">نصيب محمد حسن (50%)</label>
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
                <div class="modern-table-container">
                    <div class="table-responsive">
                        <table class="table modern-expenses-table">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-hashtag me-2"></i>
                                            <span>الرقم</span>
                                        </div>
                                    </th>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <span>الشهر والفترة</span>
                                        </div>
                                    </th>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-clock me-2"></i>
                                            <span>المدة الزمنية</span>
                                        </div>
                                    </th>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-receipt me-2"></i>
                                            <span>إجمالي المصاريف</span>
                                        </div>
                                    </th>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-chart-line me-2"></i>
                                            <span>الأرباح والعملات</span>
                                        </div>
                                    </th>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-trophy me-2"></i>
                                            <span>صافي الربح</span>
                                        </div>
                                    </th>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-clock-history me-2"></i>
                                            <span>تاريخ الإضافة</span>
                                        </div>
                                    </th>
                                    <th scope="col">
                                        <div class="th-content">
                                            <i class="fas fa-tools me-2"></i>
                                            <span>العمليات</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expenses as $index => $expense)
                                    <tr class="table-row" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                                        <!-- الرقم -->
                                        <td>
                                            <div class="row-number">
                                                @php
                                                    // حساب رقم الصف بشكل آمن
                                                    $rowNumber =
                                                        $expenses instanceof \Illuminate\Pagination\LengthAwarePaginator
                                                            ? $expenses->firstItem() + $index
                                                            : $index + 1;
                                                @endphp
                                                <span class="number-badge">{{ $rowNumber }}</span>
                                            </div>
                                        </td>

                                        <!-- الشهر والفترة -->
                                        <td>
                                            <div class="month-card">
                                                <div class="month-header">
                                                    <i class="fas fa-calendar-check me-2"></i>
                                                    <span
                                                        class="month-title">{{ $expense->month_year ?? 'غير محدد' }}</span>
                                                </div>
                                                @if ($expense->start_date && $expense->end_date)
                                                    <div class="month-dates">
                                                        <div class="date-item">
                                                            <i class="fas fa-play text-success me-1"></i>
                                                            <span>{{ $expense->start_date->format('d/m/Y') }}</span>
                                                        </div>
                                                        <div class="date-separator">
                                                            <i class="fas fa-arrow-right"></i>
                                                        </div>
                                                        <div class="date-item">
                                                            <i class="fas fa-stop text-danger me-1"></i>
                                                            <span>{{ $expense->end_date->format('d/m/Y') }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-muted small">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        التواريخ غير محددة
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- المدة الزمنية -->
                                        <td>
                                            @if ($expense->start_date && $expense->end_date)
                                                <div class="duration-badge">
                                                    <i class="fas fa-calendar-days me-2"></i>
                                                    <div class="duration-content">
                                                        <span
                                                            class="duration-from">{{ $expense->start_date->format('Y-m-d') }}</span>
                                                        <div class="duration-arrow">
                                                            <i class="fas fa-long-arrow-alt-right"></i>
                                                        </div>
                                                        <span
                                                            class="duration-to">{{ $expense->end_date->format('Y-m-d') }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center text-muted">
                                                    <i class="fas fa-question-circle"></i>
                                                    <div class="small">غير محدد</div>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- إجمالي المصاريف -->
                                        <td>
                                            <div class="expenses-container">
                                                @php
                                                    $totalSAR = 0;
                                                    $totalKWD = 0;
                                                    $currencies = $expense->expenses_currencies ?? [];

                                                    // حساب المصاريف بالريال
                                                    if (
                                                        isset($currencies['salaries']) &&
                                                        $currencies['salaries'] === 'SAR'
                                                    ) {
                                                        $totalSAR += $expense->salaries ?? 0;
                                                    }
                                                    if (
                                                        isset($currencies['advertising']) &&
                                                        $currencies['advertising'] === 'SAR'
                                                    ) {
                                                        $totalSAR += $expense->advertising ?? 0;
                                                    }
                                                    if (isset($currencies['rent']) && $currencies['rent'] === 'SAR') {
                                                        $totalSAR += $expense->rent ?? 0;
                                                    }
                                                    if (
                                                        isset($currencies['staff_commissions']) &&
                                                        $currencies['staff_commissions'] === 'SAR'
                                                    ) {
                                                        $totalSAR += $expense->staff_commissions ?? 0;
                                                    }

                                                    // حساب المصاريف بالدينار
                                                    if (
                                                        isset($currencies['salaries']) &&
                                                        $currencies['salaries'] === 'KWD'
                                                    ) {
                                                        $totalKWD += $expense->salaries ?? 0;
                                                    }
                                                    if (
                                                        isset($currencies['advertising']) &&
                                                        $currencies['advertising'] === 'KWD'
                                                    ) {
                                                        $totalKWD += $expense->advertising ?? 0;
                                                    }
                                                    if (isset($currencies['rent']) && $currencies['rent'] === 'KWD') {
                                                        $totalKWD += $expense->rent ?? 0;
                                                    }
                                                    if (
                                                        isset($currencies['staff_commissions']) &&
                                                        $currencies['staff_commissions'] === 'KWD'
                                                    ) {
                                                        $totalKWD += $expense->staff_commissions ?? 0;
                                                    }

                                                    // إضافة المصاريف الأخرى
                                                    if (
                                                        !empty($expense->other_expenses) &&
                                                        is_array($expense->other_expenses)
                                                    ) {
                                                        foreach ($expense->other_expenses as $otherExpense) {
                                                            if (
                                                                isset($otherExpense['currency']) &&
                                                                isset($otherExpense['amount'])
                                                            ) {
                                                                if ($otherExpense['currency'] === 'SAR') {
                                                                    $totalSAR += $otherExpense['amount'] ?? 0;
                                                                } elseif ($otherExpense['currency'] === 'KWD') {
                                                                    $totalKWD += $otherExpense['amount'] ?? 0;
                                                                }
                                                            }
                                                        }
                                                    }
                                                @endphp

                                                @if ($totalSAR > 0)
                                                    <div class="expense-badge expense-sar">
                                                        <div class="expense-icon">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </div>
                                                        <div class="expense-content">
                                                            <span
                                                                class="expense-amount">{{ number_format($totalSAR, 2) }}</span>
                                                            <span class="expense-currency">ريال</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($totalKWD > 0)
                                                    <div class="expense-badge expense-kwd">
                                                        <div class="expense-icon">
                                                            <i class="fas fa-coins"></i>
                                                        </div>
                                                        <div class="expense-content">
                                                            <span
                                                                class="expense-amount">{{ number_format($totalKWD, 2) }}</span>
                                                            <span class="expense-currency">دينار</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($totalSAR == 0 && $totalKWD == 0)
                                                    <div class="no-expenses">
                                                        <i class="fas fa-ban me-2"></i>
                                                        <span>لا توجد مصاريف</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- الأرباح والعملات -->
                                        <td>
                                            <div class="profits-container">
                                                @if (($expense->total_monthly_profit_SAR ?? 0) > 0)
                                                    <div class="profit-badge profit-sar">
                                                        <div class="profit-icon">
                                                            <i class="fas fa-trending-up"></i>
                                                        </div>
                                                        <div class="profit-content">
                                                            <span
                                                                class="profit-amount">{{ number_format($expense->total_monthly_profit_SAR, 2) }}</span>
                                                            <span class="profit-currency">ريال</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (($expense->total_monthly_profit_KWD ?? 0) > 0)
                                                    <div class="profit-badge profit-kwd">
                                                        <div class="profit-icon">
                                                            <i class="fas fa-chart-line"></i>
                                                        </div>
                                                        <div class="profit-content">
                                                            <span
                                                                class="profit-amount">{{ number_format($expense->total_monthly_profit_KWD, 2) }}</span>
                                                            <span class="profit-currency">دينار</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (($expense->total_monthly_profit_SAR ?? 0) == 0 && ($expense->total_monthly_profit_KWD ?? 0) == 0)
                                                    <div class="no-expenses">
                                                        <i class="fas fa-chart-line me-2"></i>
                                                        <span>لا توجد أرباح</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- صافي الربح -->
                                        <td>
                                            <div class="net-profit-container">
                                                @if (($expense->net_profit_SAR ?? 0) > 0)
                                                    <div class="net-profit-badge net-profit-sar">
                                                        <div class="net-profit-icon">
                                                            <i class="fas fa-trophy"></i>
                                                        </div>
                                                        <div class="net-profit-content">
                                                            <span
                                                                class="net-profit-amount">{{ number_format($expense->net_profit_SAR, 2) }}</span>
                                                            <span class="net-profit-currency">ريال</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (($expense->net_profit_KWD ?? 0) > 0)
                                                    <div class="net-profit-badge net-profit-kwd">
                                                        <div class="net-profit-icon">
                                                            <i class="fas fa-award"></i>
                                                        </div>
                                                        <div class="net-profit-content">
                                                            <span
                                                                class="net-profit-amount">{{ number_format($expense->net_profit_KWD, 2) }}</span>
                                                            <span class="net-profit-currency">دينار</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (($expense->net_profit_SAR ?? 0) == 0 && ($expense->net_profit_KWD ?? 0) == 0)
                                                    <div class="no-expenses">
                                                        <i class="fas fa-trophy me-2"></i>
                                                        <span>لا يوجد ربح صافي</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- تاريخ الإضافة -->
                                        <td>
                                            <div class="created-date">
                                                @if ($expense->created_at)
                                                    <div class="date-badge">
                                                        <i class="fas fa-clock-history me-2"></i>
                                                        <span>{{ $expense->created_at->format('Y/m/d') }}</span>
                                                    </div>
                                                    <div class="date-time">
                                                        <small
                                                            class="text-muted">{{ $expense->created_at->format('H:i') }}</small>
                                                    </div>
                                                @else
                                                    <div class="text-muted text-center">
                                                        <i class="fas fa-question-circle"></i>
                                                        <div class="small">غير محدد</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- العمليات -->
                                        <td>
                                            <div class="actions-container">
                                                <div class="action-buttons">
                                                    <a href="{{ route('admin.monthly-expenses.show', $expense->id) }}"
                                                        class="action-btn action-view" title="عرض التفاصيل"
                                                        data-bs-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                        <span class="btn-text">عرض</span>
                                                    </a>

                                                    <a href="{{ route('admin.monthly-expenses.edit', $expense->id) }}"
                                                        class="action-btn action-edit" title="تعديل التقرير"
                                                        data-bs-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                        <span class="btn-text">تعديل</span>
                                                    </a>

                                                    <button type="button"
                                                        class="action-btn action-delete delete-expense-btn"
                                                        data-expense-id="{{ $expense->id }}"
                                                        data-month="{{ $expense->month_year ?? 'غير محدد' }}"
                                                        title="حذف" data-bs-toggle="tooltip">
                                                        <i class="fas fa-trash-alt"></i>
                                                        <span class="btn-text">حذف</span>
                                                    </button>
                                                </div>

                                                <form id="delete-form-{{ $expense->id }}"
                                                    action="{{ route('admin.monthly-expenses.destroy', $expense->id) }}"
                                                    method="POST" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">لا توجد سجلات مصاريف</h5>
                                                <p class="text-muted">ابدأ بإضافة أول سجل مصاريف شهرية</p>
                                                <a href="{{ route('admin.monthly-expenses.create') }}"
                                                    class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus me-1"></i> إضافة سجل جديد
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($expenses instanceof \Illuminate\Pagination\LengthAwarePaginator && $expenses->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $expenses->links() }}
                        </div>
                    @endif
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

                // ✅ تحديث العرض بشكل آمن
                const totalExpensesSARElement = document.getElementById('total_expenses_display_SAR');
                const totalExpensesKWDElement = document.getElementById('total_expenses_display_KWD');
                const totalExpensesElement = document.getElementById('total_expenses');

                if (totalExpensesSARElement) totalExpensesSARElement.value = totalExpensesSAR.toFixed(2);
                if (totalExpensesKWDElement) totalExpensesKWDElement.value = totalExpensesKWD.toFixed(2);
                if (totalExpensesElement) totalExpensesElement.value = totalExpensesSAR.toFixed(2);

                // ✅ تخزين القيم بشكل آمن
                window.totalExpensesSAR = totalExpensesSAR;
                window.totalExpensesKWD = totalExpensesKWD;

                // console.log("تحديث المصاريف:", {
                //     SAR: totalExpensesSAR,
                //     KWD: totalExpensesKWD
                // });

                // إعادة حساب صافي الربح
                calculateNetProfit();
            }

            // حساب صافي الربح وتوزيع الأرباح
            function calculateNetProfit() {
                // ✅ التأكد من وجود القيم أولاً
                const expensesSAR = window.totalExpensesSAR || 0;
                const expensesKWD = window.totalExpensesKWD || 0;

                // console.log("حساب صافي الربح - المصاريف:", expensesSAR, expensesKWD);

                // ✅ جلب الأرباح مع التحقق من وجود العناصر
                const profitSARElement = document.getElementById('total_monthly_profit_SAR');
                const profitKWDElement = document.getElementById('total_monthly_profit_KWD');

                const totalProfitSAR = profitSARElement ? (parseFloat(profitSARElement.value) || 0) : 0;
                const totalProfitKWD = profitKWDElement ? (parseFloat(profitKWDElement.value) || 0) : 0;

                // حساب صافي الربح
                const netProfitSAR = Math.max(0, totalProfitSAR - expensesSAR);
                const netProfitKWD = Math.max(0, totalProfitKWD - expensesKWD);

                // ✅ تحديث العرض مع التحقق من وجود العناصر
                const netProfitSARElement = document.getElementById('net_profit_SAR');
                const netProfitKWDElement = document.getElementById('net_profit_KWD');

                if (netProfitSARElement) netProfitSARElement.value = netProfitSAR.toFixed(2);
                if (netProfitKWDElement) netProfitKWDElement.value = netProfitKWD.toFixed(2);

                // ✅ توزيع الأرباح مع التحقق من وجود العناصر
                const ismailShareSAR = netProfitSAR * 0.5;
                const mohamedShareSAR = netProfitSAR * 0.5;
                const ismailShareKWD = netProfitKWD * 0.5;
                const mohamedShareKWD = netProfitKWD * 0.5;

                const elements = {
                    ismail_share_SAR: document.getElementById('ismail_share_SAR'),
                    mohamed_share_SAR: document.getElementById('mohamed_share_SAR'),
                    ismail_share_KWD: document.getElementById('ismail_share_KWD'),
                    mohamed_share_KWD: document.getElementById('mohamed_share_KWD')
                };

                if (elements.ismail_share_SAR) elements.ismail_share_SAR.value = ismailShareSAR.toFixed(2);
                if (elements.mohamed_share_SAR) elements.mohamed_share_SAR.value = mohamedShareSAR.toFixed(2);
                if (elements.ismail_share_KWD) elements.ismail_share_KWD.value = ismailShareKWD.toFixed(2);
                if (elements.mohamed_share_KWD) elements.mohamed_share_KWD.value = mohamedShareKWD.toFixed(2);

                // console.log("صافي الربح:", {
                //     SAR: netProfitSAR,
                //     KWD: netProfitKWD
                // });
            }

            // ✅ تحديث الحسابات عند تغيير المصاريف
            document.querySelectorAll('.expense-field').forEach(function(input) {
                input.addEventListener('input', recalculateExpenses);
            });

            // نموذج حساب الربح - الكود المُصحح
            // ✅ نموذج حساب الربح - مُصحح
            document.getElementById('profit-calculator-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;

                if (!startDate || !endDate) {
                    alert('يرجى تحديد تاريخ البداية والنهاية');
                    return;
                }

                // إضافة loading state
                const submitBtn = e.target.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الحساب...';
                submitBtn.disabled = true;

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
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // console.log('Response data:', data);

                        if (!data || typeof data !== 'object') {
                            throw new Error('Invalid response format');
                        }

                        // عرض النتيجة
                        document.getElementById('profit-result').classList.remove('d-none');

                        const monthYearDisplay = document.getElementById('month-year-display');
                        const bookingsCount = document.getElementById('bookings-count');

                        if (monthYearDisplay) monthYearDisplay.textContent = data.month_year ||
                            'غير محدد';
                        if (bookingsCount) bookingsCount.textContent = data.reports_count || '0';
                        // يمكنك أيضاً عرض تفاصيل التقارير
                        if (data.reports_details && data.reports_details.length > 0) {
                            let reportsDetailsHTML =
                                '<div class="mt-2"><small class="text-muted">التقارير المعثور عليها:</small><ul class="list-unstyled small">';
                            data.reports_details.forEach(report => {
                                reportsDetailsHTML +=
                                    `<li>• ${report.client_name} (${report.report_date}) - ربح: ${report.grand_total_profit}</li>`;
                            });
                            reportsDetailsHTML += '</ul></div>';

                            // إضافة هذا HTML إلى مكان مناسب في النتائج
                            const profitDisplay = document.getElementById('profit-display');
                            if (profitDisplay) {
                                profitDisplay.insertAdjacentHTML('afterend', reportsDetailsHTML);
                            }
                        }

                        // عرض الأرباح
                        const profitDisplay = document.getElementById('profit-display');
                        if (profitDisplay) {
                            profitDisplay.innerHTML = '';

                            const profitsByCurrency = data.profits_by_currency || {};
                            let hasData = false;
                            let displayHTML = '';
                            let actualTotal = 0; // ✅ إضافة متغير للحساب الفعلي

                            Object.entries(profitsByCurrency).forEach(([currency, profit]) => {
                                const profitValue = parseFloat(profit) || 0;
                                if (profitValue > 0) {
                                    hasData = true;
                                    actualTotal += profitValue; // ✅ جمع القيم الفعلية

                                    const currencyInfo = {
                                        'SAR': {
                                            label: 'ريال سعودي',
                                            class: 'success',
                                            icon: 'fa-money-bill-wave'
                                        },
                                        'KWD': {
                                            label: 'دينار كويتي',
                                            class: 'primary',
                                            icon: 'fa-coins'
                                        },
                                        'USD': {
                                            label: 'دولار أمريكي',
                                            class: 'info',
                                            icon: 'fa-dollar-sign'
                                        },
                                        'EUR': {
                                            label: 'يورو',
                                            class: 'warning',
                                            icon: 'fa-euro-sign'
                                        }
                                    };

                                    const info = currencyInfo[currency] || {
                                        label: currency,
                                        class: 'secondary',
                                        icon: 'fa-money-bill'
                                    };

                                    displayHTML += `
                <div class="profit-item mb-2 d-flex justify-content-between align-items-center">
                    <span class="badge bg-${info.class} fs-6 px-3 py-2">
                        <i class="fas ${info.icon} me-2"></i>
                        ${profitValue.toFixed(2)} ${info.label}
                    </span>
                    <small class="text-muted">${currency}</small>
                </div>
            `;
                                }
                            });

                            if (hasData) {
                                // ✅ التحقق من عدد العملات
                                const currencyCount = Object.keys(profitsByCurrency).length;

                                if (currencyCount === 1) {
                                    // إذا كانت عملة واحدة فقط، اعرض الإجمالي
                                    const [currency, amount] = Object.entries(profitsByCurrency)[0];
                                    const currencyLabel = currency === 'SAR' ? 'ريال سعودي' :
                                        currency === 'KWD' ? 'دينار كويتي' :
                                        currency === 'USD' ? 'دولار أمريكي' :
                                        currency === 'EUR' ? 'يورو' : currency;

                                    displayHTML += `
            <hr class="my-2">
            <div class="profit-total">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        <i class="fas fa-calculator me-1"></i>
                        الإجمالي:
                    </span>
                    <span class="badge bg-dark fs-6 px-3 py-2">
                        ${parseFloat(amount).toFixed(2)} ${currencyLabel}
                    </span>
                </div>
            </div>
        `;
                                } else {
                                    // إذا كانت عملات متعددة، اعرض تحذير
                                    displayHTML += `
            <hr class="my-2">
            <div class="alert alert-warning py-2 mb-0">
                <small><i class="fas fa-exclamation-triangle me-1"></i>
                <strong>تنبيه:</strong> لا يمكن جمع عملات مختلفة. يرجى استخدام تحويل العملات لحساب إجمالي موحد.
                </small>
            </div>
        `;
                                }
                            } else {
                                displayHTML = `
        <div class="profit-item text-center py-3">
            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
            <div class="text-muted">لا توجد أرباح في هذه الفترة</div>
        </div>
    `;
                            }

                            profitDisplay.innerHTML = displayHTML;
                        }

                        // ✅ حفظ البيانات للاستخدام اللاحق
                        window.calculatedProfitsByCurrency = data.profits_by_currency || {};
                        window
                            .calculatedTotalProfit = data.total_profit || 0;
                        window.startDateValue = data
                            .start_date || startDate;
                        window.endDateValue = data.end_date || endDate;

                    })
                    .catch(error => {
                        console.error('Error details:', error);
                        document.getElementById('profit-result').classList.add('d-none');
                        alert('حدث خطأ أثناء حساب الربح');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });

            // ✅ استخدام نتيجة الحساب - مُصحح
            document.getElementById('use-result-btn').addEventListener('click', function() {
                try {
                    if (!window.calculatedProfitsByCurrency || !window.startDateValue || !window
                        .endDateValue) {
                        alert('لا توجد بيانات محسوبة للاستخدام. يرجى حساب الربح أولاً.');
                        return;
                    }

                    // تحديث حقل الشهر
                    const monthYearInput = document.getElementById('month_year');
                    const monthYearDisplay = document.getElementById('month-year-display');

                    if (monthYearInput && monthYearDisplay) {
                        const monthText = monthYearDisplay.textContent || 'غير محدد';
                        monthYearInput.value =
                            `${monthText} (من ${window.startDateValue} إلى ${window.endDateValue})`;
                    }

                    // تصفير جميع حقول الربح أولاً
                    const profitFieldsSAR = document.getElementById('total_monthly_profit_SAR');
                    const profitFieldsKWD = document.getElementById('total_monthly_profit_KWD');

                    if (profitFieldsSAR) profitFieldsSAR.value = '0.00';
                    if (profitFieldsKWD) profitFieldsKWD.value = '0.00';

                    // ✅ ملء الحقول بناءً على البيانات الفعلية
                    const profitsByCurrency = window.calculatedProfitsByCurrency;
                    Object.entries(profitsByCurrency).forEach(([currency, amount]) => {
                        const profitValue = parseFloat(amount) || 0;
                        if (profitValue > 0) {
                            const field = document.getElementById(
                                `total_monthly_profit_${currency}`);
                            if (field) {
                                field.value = profitValue.toFixed(2);
                            }
                        }
                    });

                    // تحديث الحقول المخفية
                    const startDateHidden = document.getElementById('start_date_hidden');
                    const endDateHidden = document.getElementById('end_date_hidden');

                    if (startDateHidden) startDateHidden.value = window.startDateValue;
                    if (endDateHidden) endDateHidden.value = window.endDateValue;

                    // إعادة حساب المصاريف وصافي الربح
                    recalculateExpenses();

                    alert('تم استخدام بيانات الربح المحسوبة بنجاح!');

                } catch (error) {
                    console.error('Error using calculated results:', error);
                    alert('حدث خطأ أثناء استخدام النتائج المحسوبة.');
                }
            });

            // دالة مساعدة لتحديث تفاصيل الأرباح
            function updateProfitDetails(profitsByCurrency) {
                let profitDetailsDiv = document.getElementById('profit-details');
                if (!profitDetailsDiv) {
                    profitDetailsDiv = document.createElement('div');
                    profitDetailsDiv.id = 'profit-details';
                    profitDetailsDiv.className = 'alert alert-info mt-2';

                    // البحث عن المكان المناسب لإدراج التفاصيل
                    const profitCardTitle = document.querySelector('.card-body h6.card-title');
                    if (profitCardTitle && profitCardTitle.closest('.card-body')) {
                        profitCardTitle.closest('.card-body').appendChild(profitDetailsDiv);
                    }
                }

                let detailsHTML =
                    '<strong><i class="fas fa-chart-line me-2"></i>تفاصيل الأرباح حسب العملة:</strong><br><br>';

                let hasAnyProfit = false;
                Object.entries(profitsByCurrency).forEach(([currency, amount]) => {
                    const profitValue = parseFloat(amount) || 0;
                    if (profitValue > 0) {
                        hasAnyProfit = true;
                        const currencyLabel = currency === 'SAR' ? 'ريال سعودي' : 'دينار كويتي';
                        const badgeClass = currency === 'SAR' ? 'bg-success' : 'bg-primary';
                        const icon = currency === 'SAR' ? 'fa-money-bill-wave' : 'fa-coins';

                        detailsHTML += `
                <span class="badge ${badgeClass} me-2 mb-1" style="font-size: 0.9em;">
                    <i class="fas ${icon} me-1"></i>
                    ${profitValue.toFixed(2)} ${currencyLabel}
                </span>
            `;
                    }
                });

                if (!hasAnyProfit) {
                    detailsHTML +=
                        '<span class="badge bg-secondary"><i class="fas fa-info-circle me-1"></i>لا توجد أرباح في هذه الفترة</span>';
                }

                profitDetailsDiv.innerHTML = detailsHTML;
            }

            // دالة مساعدة لعرض رسائل النجاح
            function showSuccessMessage(message) {
                // إنشاء عنصر التنبيه
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-2';
                alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

                // إدراج التنبيه في أعلى الصفحة
                const container = document.querySelector('.container-fluid');
                if (container) {
                    container.insertBefore(alertDiv, container.firstChild);

                    // إزالة التنبيه تلقائياً بعد 5 ثواني
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                }
            }
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

                    // ✅ تصحيح: استعادة قيم المصاريف
                    document.getElementById('total_expenses_display_SAR').value = window.originalValues.expenses_SAR
                        .toFixed(2);
                    document.getElementById('total_expenses_display_KWD').value = window.originalValues.expenses_KWD
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
    {{-- <script src="{{ asset('js/preventClick.js') }}"></script> --}}
@endpush
