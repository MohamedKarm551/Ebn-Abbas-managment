@extends('layouts.app')

@section('title', 'تعديل المصاريف الشهرية | ' . $expense->month_year)
{{-- <link rel="stylesheet" href="{{ url('css/showRoomDetails.css') }}?v={{ rand() }}"> --}}
<link rel="stylesheet" href="{{ asset('css/Monthly-expenses/edit.css') }}">



@section('content')
    <div class="edit-expense-container">
        <!-- 🌟 عناصر التصميم العائمة -->
        <div class="floating-elements"></div>

        <div class="container-fluid">
            <!-- 🎯 هيدر الصفحة العصري -->
            <div class="modern-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1>
                            <i class="fas fa-edit me-3"></i>
                            تعديل المصاريف الشهرية
                        </h1>
                        <p class="subtitle mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ $expense->month_year }} - من {{ $expense->start_date->format('d/m/Y') }} إلى
                            {{ $expense->end_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('admin.monthly-expenses.index') }}" class="modern-btn btn-secondary-modern">
                            <i class="fas fa-arrow-right"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- 📝 نموذج التعديل الرئيسي -->
            <form action="{{ route('admin.monthly-expenses.update', $expense->id) }}" method="POST"
                id="edit-expenses-form">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- 📊 القسم الأيسر: البيانات الأساسية والمصاريف -->
                    <div class="col-lg-8">
                        <!-- 🎯 البيانات الأساسية -->
                        <div class="modern-form-section">
                            <h3 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                البيانات الأساسية
                            </h3>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-calendar-alt"></i>
                                            اسم الفترة
                                        </label>
                                        <input type="text" class="modern-input" name="month_year"
                                            value="{{ old('month_year', $expense->month_year) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-play"></i>
                                            تاريخ البداية
                                        </label>
                                        <input type="date" class="modern-input" name="start_date"
                                            value="{{ old('start_date', $expense->start_date->format('Y-m-d')) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-stop"></i>
                                            تاريخ النهاية
                                        </label>
                                        <input type="date" class="modern-input" name="end_date"
                                            value="{{ old('end_date', $expense->end_date->format('Y-m-d')) }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 💰 المصاريف الثابتة -->
                        <div class="modern-form-section">
                            <h3 class="section-title">
                                <i class="fas fa-coins"></i>
                                المصاريف الأساسية
                            </h3>

                            @php
                                $currencies = $expense->expenses_currencies ?? [];
                            @endphp

                            <div class="row">
                                <!-- الرواتب -->
                                <div class="col-md-6 mb-3">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-users"></i>
                                            إجمالي الرواتب
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="number" step="0.01" min="0"
                                                class="modern-input expense-field" name="salaries"
                                                value="{{ old('salaries', $expense->salaries) }}" required>
                                            <div class="currency-selector">
                                                <option
                                                    class="currency-option sar {{ isset($currencies['salaries']) && $currencies['salaries'] === 'SAR' ? 'active' : '' }}"
                                                    data-field="salaries" data-currency="SAR">
                                                    ريال
                                                </option>
                                                <option
                                                    class="currency-option kwd {{ isset($currencies['salaries']) && $currencies['salaries'] === 'KWD' ? 'active' : '' }}"
                                                    data-field="salaries" data-currency="KWD">
                                                    دينار
                                                </option>
                                            </div>
                                            <input type="hidden" name="salaries_currency"
                                                value="{{ $currencies['salaries'] ?? 'SAR' }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- الإعلانات -->
                                <div class="col-md-6 mb-3">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-megaphone"></i>
                                            إجمالي الإعلانات
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="number" step="0.01" min="0"
                                                class="modern-input expense-field" name="advertising"
                                                value="{{ old('advertising', $expense->advertising) }}" required>
                                            <div class="currency-selector">
                                                <option
                                                    class="currency-option sar {{ isset($currencies['advertising']) && $currencies['advertising'] === 'SAR' ? 'active' : '' }}"
                                                    data-field="advertising" data-currency="SAR">
                                                    ريال
                                                </option>
                                                <option
                                                    class="currency-option kwd {{ isset($currencies['advertising']) && $currencies['advertising'] === 'KWD' ? 'active' : '' }}"
                                                    data-field="advertising" data-currency="KWD">
                                                    دينار
                                                </option>
                                            </div>
                                            <input type="hidden" name="advertising_currency"
                                                value="{{ $currencies['advertising'] ?? 'SAR' }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- الإيجار -->
                                <div class="col-md-6 mb-3">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-home"></i>
                                            الإيجار
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="number" step="0.01" min="0"
                                                class="modern-input expense-field" name="rent"
                                                value="{{ old('rent', $expense->rent) }}" required>
                                            <div class="currency-selector">
                                                <option
                                                    class="currency-option sar {{ isset($currencies['rent']) && $currencies['rent'] === 'SAR' ? 'active' : '' }}"
                                                    data-field="rent" data-currency="SAR">
                                                    ريال
                                                </option>
                                                <option
                                                    class="currency-option kwd {{ isset($currencies['rent']) && $currencies['rent'] === 'KWD' ? 'active' : '' }}"
                                                    data-field="rent" data-currency="KWD">
                                                    دينار
                                                </option>
                                            </div>
                                            <input type="hidden" name="rent_currency"
                                                value="{{ $currencies['rent'] ?? 'SAR' }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- عمولات الموظفين -->
                                <div class="col-md-6 mb-3">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-percentage"></i>
                                            عمولات الموظفين
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="number" step="0.01" min="0"
                                                class="modern-input expense-field" name="staff_commissions"
                                                value="{{ old('staff_commissions', $expense->staff_commissions) }}"
                                                required>
                                            <div class="currency-selector">
                                                <option
                                                    class="currency-option sar {{ isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'SAR' ? 'active' : '' }}"
                                                    data-field="staff_commissions" data-currency="SAR">
                                                    ريال
                                                </option>
                                                <option
                                                    class="currency-option kwd {{ isset($currencies['staff_commissions']) && $currencies['staff_commissions'] === 'KWD' ? 'active' : '' }}"
                                                    data-field="staff_commissions" data-currency="KWD">
                                                    دينار
                                                </option>
                                            </div>
                                            <input type="hidden" name="staff_commissions_currency"
                                                value="{{ $currencies['staff_commissions'] ?? 'SAR' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ➕ المصاريف الإضافية -->
                        <div class="modern-form-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="section-title mb-0">
                                    <i class="fas fa-plus-circle"></i>
                                    مصاريف إضافية
                                </h3>
                                <button type="button" class="add-expense-btn" id="add-expense-btn">
                                    <i class="fas fa-plus"></i>
                                    إضافة مصروف جديد
                                </button>
                            </div>

                            <div id="other-expenses-container">
                                @if (!empty($expense->other_expenses))
                                    @foreach ($expense->other_expenses as $index => $otherExpense)
                                        <div class="expense-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-5">
                                                    <div class="modern-input-group">
                                                        <label class="modern-label">
                                                            <i class="fas fa-tag"></i>
                                                            اسم المصروف
                                                        </label>
                                                        <input type="text" class="modern-input"
                                                            name="other_expenses[{{ $index }}][name]"
                                                            value="{{ $otherExpense['name'] }}" placeholder="اسم المصروف"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="modern-input-group">
                                                        <label class="modern-label">
                                                            <i class="fas fa-dollar-sign"></i>
                                                            المبلغ والعملة
                                                        </label>
                                                        <div class="d-flex align-items-center">
                                                            <input type="number" step="0.01" min="0"
                                                                class="modern-input other-expense-amount"
                                                                name="other_expenses[{{ $index }}][amount]"
                                                                value="{{ $otherExpense['amount'] }}"
                                                                placeholder="المبلغ" required>
                                                            <div class="currency-selector">
                                                                <option
                                                                    class="currency-option sar {{ isset($otherExpense['currency']) && $otherExpense['currency'] === 'SAR' ? 'active' : '' }}"
                                                                    data-field="other_expenses_{{ $index }}"
                                                                    data-currency="SAR">
                                                                    ريال
                                                                </option>
                                                                <option
                                                                    class="currency-option kwd {{ isset($otherExpense['currency']) && $otherExpense['currency'] === 'KWD' ? 'active' : '' }}"
                                                                    data-field="other_expenses_{{ $index }}"
                                                                    data-currency="KWD">
                                                                    دينار
                                                                </option>
                                                            </div>
                                                            <input type="hidden"
                                                                name="other_expenses[{{ $index }}][currency]"
                                                                value="{{ $otherExpense['currency'] ?? 'SAR' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center">
                                                    <button type="button" class="remove-expense-btn"
                                                        title="حذف المصروف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 📈 القسم الأيمن: الأرباح والملخص -->
                    <div class="col-lg-4">
                        <!-- 💎 إجمالي الأرباح -->
                        <div class="modern-form-section">
                            <h3 class="section-title">
                                <i class="fas fa-chart-line"></i>
                                إجمالي الأرباح الشهرية
                            </h3>

                            <!-- الريال السعودي -->
                            <div class="modern-input-group">
                                <label class="modern-label">
                                    <i class="fas fa-money-bill-wave"></i>
                                    الربح بالريال السعودي
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="number" step="0.01" min="0" class="modern-input"
                                        id="total_monthly_profit_SAR" name="total_monthly_profit_SAR"
                                        value="{{ old('total_monthly_profit_SAR', $expense->total_monthly_profit_SAR) }}"
                                        required>
                                    <span class="currency-badge sar ms-2">ريال</span>
                                </div>
                            </div>

                            <!-- الدينار الكويتي -->
                            <div class="modern-input-group">
                                <label class="modern-label">
                                    <i class="fas fa-coins"></i>
                                    الربح بالدينار الكويتي
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="number" step="0.01" min="0" class="modern-input"
                                        id="total_monthly_profit_KWD" name="total_monthly_profit_KWD"
                                        value="{{ old('total_monthly_profit_KWD', $expense->total_monthly_profit_KWD) }}"
                                        required>
                                    <span class="currency-badge kwd ms-2">دينار</span>
                                </div>
                            </div>
                        </div>

                        <!-- 🏆 صافي الربح بالريال السعودي -->
                        <div class="profit-card sar">
                            <h4 class="mb-3">
                                <i class="fas fa-trophy me-2"></i>
                                الأرباح بالريال السعودي
                                <span class="currency-badge sar float-end">SAR</span>
                            </h4>

                            <div class="modern-input-group">
                                <label class="modern-label">
                                    <i class="fas fa-calculator"></i>
                                    صافي الربح
                                </label>
                                <input type="number" step="0.01" min="0" class="modern-input"
                                    id="net_profit_SAR" name="net_profit_SAR"
                                    value="{{ old('net_profit_SAR', $expense->net_profit_SAR) }}" readonly>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-user-tie"></i>
                                            نصيب ش. إسماعيل
                                        </label>
                                        <input type="number" step="0.01" min="0" class="modern-input"
                                            id="ismail_share_SAR" name="ismail_share_SAR"
                                            value="{{ old('ismail_share_SAR', $expense->ismail_share_SAR) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-user-tie"></i>
                                            نصيب ش. محمد حسن
                                        </label>
                                        <input type="number" step="0.01" min="0" class="modern-input"
                                            id="mohamed_share_SAR" name="mohamed_share_SAR"
                                            value="{{ old('mohamed_share_SAR', $expense->mohamed_share_SAR) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 🏆 صافي الربح بالدينار الكويتي -->
                        <div class="profit-card kwd">
                            <h4 class="mb-3">
                                <i class="fas fa-trophy me-2"></i>
                                الأرباح بالدينار الكويتي
                                <span class="currency-badge kwd float-end">KWD</span>
                            </h4>

                            <div class="modern-input-group">
                                <label class="modern-label">
                                    <i class="fas fa-calculator"></i>
                                    صافي الربح
                                </label>
                                <input type="number" step="0.01" min="0" class="modern-input"
                                    id="net_profit_KWD" name="net_profit_KWD"
                                    value="{{ old('net_profit_KWD', $expense->net_profit_KWD) }}" readonly>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-user-tie"></i>
                                            نصيب ش. إسماعيل
                                        </label>
                                        <input type="number" step="0.01" min="0" class="modern-input"
                                            id="ismail_share_KWD" name="ismail_share_KWD"
                                            value="{{ old('ismail_share_KWD', $expense->ismail_share_KWD) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="modern-input-group">
                                        <label class="modern-label">
                                            <i class="fas fa-user-tie"></i>
                                            نصيب ش. محمد حسن
                                        </label>
                                        <input type="number" step="0.01" min="0" class="modern-input"
                                            id="mohamed_share_KWD" name="mohamed_share_KWD"
                                            value="{{ old('mohamed_share_KWD', $expense->mohamed_share_KWD) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 📝 الملاحظات -->
                        <div class="modern-form-section">
                            <h3 class="section-title">
                                <i class="fas fa-sticky-note"></i>
                                ملاحظات
                            </h3>
                            <div class="modern-input-group">
                                <textarea class="modern-input" name="notes" rows="4" placeholder="أدخل أي ملاحظات إضافية هنا...">{{ old('notes', $expense->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🎯 أزرار التحكم -->
                <div class="text-center mt-4">
                    <button type="submit" class="modern-btn btn-success-modern me-3">
                        <i class="fas fa-save"></i>
                        حفظ التحديثات
                    </button>
                    <a href="{{ route('admin.monthly-expenses.show', $expense->id) }}"
                        class="modern-btn btn-primary-modern me-3">
                        <i class="fas fa-eye"></i>
                        عرض التفاصيل
                    </a>
                    <a href="{{ route('admin.monthly-expenses.index') }}" class="modern-btn btn-secondary-modern">
                        <i class="fas fa-times"></i>
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let expenseCounter = {{ count($expense->other_expenses ?? []) }};

            // 🎯 دالة حساب إجمالي المصاريف الديناميكي
            function recalculateExpenses() {
                let totalExpensesSAR = 0;
                let totalExpensesKWD = 0;

                // حساب المصاريف الثابتة
                document.querySelectorAll('.expense-field').forEach(function(input) {
                    const amount = parseFloat(input.value) || 0;
                    const currencyInput = input.parentElement.querySelector('input[type="hidden"]');
                    const currency = currencyInput ? currencyInput.value : 'SAR';

                    if (currency === 'SAR') {
                        totalExpensesSAR += amount;
                    } else if (currency === 'KWD') {
                        totalExpensesKWD += amount;
                    }
                });

                // حساب المصاريف الإضافية
                document.querySelectorAll('.other-expense-amount').forEach(function(input) {
                    const amount = parseFloat(input.value) || 0;
                    const currencyInput = input.parentElement.querySelector('input[type="hidden"]');
                    const currency = currencyInput ? currencyInput.value : 'SAR';

                    if (currency === 'SAR') {
                        totalExpensesSAR += amount;
                    } else if (currency === 'KWD') {
                        totalExpensesKWD += amount;
                    }
                });

                // تحديث متغيرات النافذة للاستخدام في حساب صافي الربح
                window.totalExpensesSAR = totalExpensesSAR;
                window.totalExpensesKWD = totalExpensesKWD;

                // إعادة حساب صافي الربح
                calculateNetProfit();
            }

            // 🧮 دالة حساب صافي الربح وتوزيع الأرباح
            function calculateNetProfit() {
                // الحصول على الأرباح الإجمالية
                const totalProfitSAR = parseFloat(document.getElementById('total_monthly_profit_SAR').value) || 0;
                const totalProfitKWD = parseFloat(document.getElementById('total_monthly_profit_KWD').value) || 0;

                // الحصول على إجمالي المصاريف
                const expensesSAR = window.totalExpensesSAR || 0;
                const expensesKWD = window.totalExpensesKWD || 0;

                // حساب صافي الربح
                const netProfitSAR = Math.max(0, totalProfitSAR - expensesSAR);
                const netProfitKWD = Math.max(0, totalProfitKWD - expensesKWD);

                // تحديث حقول صافي الربح
                document.getElementById('net_profit_SAR').value = netProfitSAR.toFixed(2);
                document.getElementById('net_profit_KWD').value = netProfitKWD.toFixed(2);

                // حساب نصيب كل شريك (50% لكل شريك)
                const ismailShareSAR = netProfitSAR * 0.5;
                const mohamedShareSAR = netProfitSAR * 0.5;
                const ismailShareKWD = netProfitKWD * 0.5;
                const mohamedShareKWD = netProfitKWD * 0.5;

                // تحديث حقول نصيب الشركاء
                document.getElementById('ismail_share_SAR').value = ismailShareSAR.toFixed(2);
                document.getElementById('mohamed_share_SAR').value = mohamedShareSAR.toFixed(2);
                document.getElementById('ismail_share_KWD').value = ismailShareKWD.toFixed(2);
                document.getElementById('mohamed_share_KWD').value = mohamedShareKWD.toFixed(2);
            }

            // 🎨 دالة تبديل العملة مع التأثيرات البصرية
            function toggleCurrency(field, currency) {
                const hiddenInput = document.querySelector(`input[name="${field}_currency"]`);
                if (hiddenInput) {
                    hiddenInput.value = currency;
                }

                // تحديث الأزرار البصرية
                const currencySelector = document.querySelector(`[data-field="${field}"]`).closest(
                    '.currency-selector');
                const options = currencySelector.querySelectorAll('.currency-option');

                options.forEach(option => {
                    option.classList.remove('active');
                    if (option.dataset.currency === currency) {
                        option.classList.add('active');
                    }
                });

                // إعادة حساب المصاريف
                recalculateExpenses();
            }

            // 🎯 مستمعي الأحداث للعملات
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('currency-option')) {
                    const field = e.target.dataset.field;
                    const currency = e.target.dataset.currency;
                    toggleCurrency(field, currency);
                }
            });

            // ➕ إضافة مصروف جديد مع تأثيرات بصرية
            document.getElementById('add-expense-btn').addEventListener('click', function() {
                const container = document.getElementById('other-expenses-container');
                const expenseRow = document.createElement('div');
                expenseRow.classList.add('expense-row');
                expenseRow.style.opacity = '0';
                expenseRow.style.transform = 'translateY(20px)';

                expenseRow.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="modern-input-group">
                        <label class="modern-label">
                            <i class="fas fa-tag"></i>
                            اسم المصروف
                        </label>
                        <input type="text" 
                               class="modern-input" 
                               name="other_expenses[${expenseCounter}][name]" 
                               placeholder="اسم المصروف" 
                               required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="modern-input-group">
                        <label class="modern-label">
                            <i class="fas fa-dollar-sign"></i>
                            المبلغ والعملة
                        </label>
                        <div class="d-flex align-items-center">
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="modern-input other-expense-amount" 
                                   name="other_expenses[${expenseCounter}][amount]" 
                                   placeholder="المبلغ" 
                                   required 
                                   value="0">
                            <div class="currency-selector">
                                <div class="currency-option sar active" 
                                     data-field="other_expenses_${expenseCounter}" 
                                     data-currency="SAR">
                                    ريال
                                </div>
                                <div class="currency-option kwd" 
                                     data-field="other_expenses_${expenseCounter}" 
                                     data-currency="KWD">
                                    دينار
                                </div>
                            </div>
                            <input type="hidden" name="other_expenses[${expenseCounter}][currency]" value="SAR">
                        </div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <button type="button" class="remove-expense-btn" title="حذف المصروف">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

                container.appendChild(expenseRow);

                // تأثير الظهور التدريجي
                setTimeout(() => {
                    expenseRow.style.opacity = '1';
                    expenseRow.style.transform = 'translateY(0)';
                    expenseRow.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                }, 100);

                expenseCounter++;
                recalculateExpenses();
            });

            // 🗑️ حذف مصروف مع تأثيرات بصرية
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-expense-btn')) {
                    const button = e.target.closest('.remove-expense-btn');
                    const row = button.closest('.expense-row');

                    // تأثير الاختفاء التدريجي
                    row.style.transform = 'translateX(-100%) scale(0.8)';
                    row.style.opacity = '0';
                    row.style.transition = 'all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55)';

                    setTimeout(() => {
                        row.remove();
                        recalculateExpenses();
                    }, 300);
                }
            });

            // 🎯 مستمعي الأحداث للمدخلات
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('expense-field') ||
                    e.target.classList.contains('other-expense-amount') ||
                    e.target.id === 'total_monthly_profit_SAR' ||
                    e.target.id === 'total_monthly_profit_KWD') {
                    recalculateExpenses();
                }
            });

            // 🚀 تحديد العمليات الحسابية الأولية
            recalculateExpenses();

            // 🎨 تأثيرات بصرية للنموذج
            const form = document.getElementById('edit-expenses-form');
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
                submitBtn.disabled = true;
            });

            // 🌟 تأثيرات التحويم للبطاقات
            const cards = document.querySelectorAll('.modern-form-section, .profit-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.08)';
                });
            });

            // 🎊 تأثيرات التركيز على الحقول
            document.querySelectorAll('.modern-input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#007bff';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(0, 123, 255, 0.25)';
                    this.parentElement.style.transform = 'scale(1.02)';
                });

                input.addEventListener('blur', function() {
                    this.style.borderColor = '#ced4da';
                    this.style.boxShadow = 'none';
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // 🚨 تنبيه قبل مغادرة الصفحة
            let hasChanges = false;

            document.querySelectorAll('input, select, textarea').forEach(element => {
                element.addEventListener('change', function() {
                    hasChanges = true;
                });
            });

            window.addEventListener('beforeunload', function(e) {
                if (hasChanges) {
                    e.preventDefault();
                    e.returnValue = 'هل أنت متأكد من مغادرة الصفحة؟ قد تفقد التغييرات غير المحفوظة.';
                }
            });

            // إزالة التنبيه عند إرسال النموذج
            form.addEventListener('submit', function() {
                hasChanges = false;
            });

            // 🎯 تحسين تجربة المستخدم للعملات
            document.querySelectorAll('.currency-option').forEach(option => {
                option.addEventListener('click', function() {
                    // تأثير بصري للنقر
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });

            // ⌨️ اختصارات لوحة المفاتيح
            document.addEventListener('keydown', function(e) {
                // Ctrl+S للحفظ
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    form.submit();
                }

                // Escape للإلغاء
                if (e.key === 'Escape') {
                    window.location.href = '{{ route('admin.monthly-expenses.index') }}';
                }
            });

            // 🔄 تحديث العداد للمصاريف الجديدة
            function updateExpenseCounters() {
                const rows = document.querySelectorAll('.expense-row');
                rows.forEach((row, index) => {
                    const inputs = row.querySelectorAll('input[name*="other_expenses"]');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                        }
                    });
                });
            }

            // 🎨 تأثيرات تحريك الأرقام عند التغيير
            function animateNumber(element, newValue) {
                const oldValue = parseFloat(element.value) || 0;
                const diff = newValue - oldValue;

                if (Math.abs(diff) > 0.01) {
                    element.style.color = diff > 0 ? '#28a745' : '#dc3545';
                    element.style.fontWeight = 'bold';

                    setTimeout(() => {
                        element.style.color = '';
                        element.style.fontWeight = '';
                    }, 1000);
                }
            }

            // 📊 تحديث دالة حساب صافي الربح مع التأثيرات البصرية
            const originalCalculateNetProfit = calculateNetProfit;
            calculateNetProfit = function() {
                const netProfitSARElement = document.getElementById('net_profit_SAR');
                const netProfitKWDElement = document.getElementById('net_profit_KWD');

                const oldSAR = parseFloat(netProfitSARElement.value) || 0;
                const oldKWD = parseFloat(netProfitKWDElement.value) || 0;

                originalCalculateNetProfit();

                const newSAR = parseFloat(netProfitSARElement.value) || 0;
                const newKWD = parseFloat(netProfitKWDElement.value) || 0;

                animateNumber(netProfitSARElement, newSAR);
                animateNumber(netProfitKWDElement, newKWD);
            };

            // 🎉 رسالة ترحيب عند تحميل الصفحة
            console.log('🎯 تم تحميل صفحة تعديل المصاريف الشهرية بنجاح!');
            console.log('💡 نصائح: استخدم Ctrl+S للحفظ السريع، Escape للخروج');

            // 🔍 تحسين الأداء - تأخير العمليات الثقيلة
            setTimeout(() => {
                // تشغيل أي عمليات إضافية بعد تحميل الصفحة
                updateExpenseCounters();
            }, 100);

            // 🌈 إضافة تأثيرات لونية للحالات المختلفة
            function updateVisualStates() {
                const netProfitSAR = parseFloat(document.getElementById('net_profit_SAR').value) || 0;
                const netProfitKWD = parseFloat(document.getElementById('net_profit_KWD').value) || 0;

                // تلوين بطاقات الأرباح حسب الحالة
                const sarCard = document.querySelector('.profit-card.sar');
                const kwdCard = document.querySelector('.profit-card.kwd');

                if (sarCard) {
                    sarCard.classList.toggle('profit-positive', netProfitSAR > 0);
                    sarCard.classList.toggle('profit-zero', netProfitSAR === 0);
                }

                if (kwdCard) {
                    kwdCard.classList.toggle('profit-positive', netProfitKWD > 0);
                    kwdCard.classList.toggle('profit-zero', netProfitKWD === 0);
                }
            }

            // ربط التحديث البصري بحساب الأرباح
            const originalRecalculate = recalculateExpenses;
            recalculateExpenses = function() {
                originalRecalculate();
                updateVisualStates();
            };

            // 🎯 تشغيل التحديث الأولي
            updateVisualStates();
        });
    </script>
@endpush

