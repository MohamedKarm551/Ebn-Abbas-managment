<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="transactionForm">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <!-- Step 1: Basic Information -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="text-primary mb-3">
                <i class="fas fa-info-circle me-1"></i>
                البيانات الأساسية
            </h5>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">
                <i class="fas fa-calendar text-primary me-1"></i>
                تاريخ المعاملة
            </label>
            <input type="date" name="transaction_date"
                class="form-control @error('transaction_date') is-invalid @enderror"
                value="{{ old('transaction_date', $transaction?->transaction_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
            @error('transaction_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">
                <i class="fas fa-user text-primary me-1"></i>
                من/إلى
            </label>
            <input type="text" name="from_to" class="form-control @error('from_to') is-invalid @enderror"
                placeholder="اسم الشخص أو الجهة" value="{{ old('from_to', $transaction?->from_to) }}">
            @error('from_to')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">
                <i class="fas fa-tag text-primary me-1"></i>
                نوع العملية
            </label>
            <select name="type" id="transactionType"
                class="form-control select2 @error('type') is-invalid @enderror">
                <option value="">اختر نوع العملية</option>
                <option value="deposit" data-effect="positive" data-description="يضاف إلى الرصيد"
                    {{ old('type', $transaction?->type) == 'deposit' ? 'selected' : '' }}>
                    💰 إيداع <span class="text-success fw-bold">(+)</span>
                </option>
                <option value="withdrawal" data-effect="negative" data-description="يخصم من الرصيد"
                    {{ old('type', $transaction?->type) == 'withdrawal' ? 'selected' : '' }}>
                    💸 سحب <span class="text-danger fw-bold">(-)</span>
                </option>
                <option value="transfer" data-effect="negative" data-description="يخصم من الرصيد (تحويل لآخر)"
                    {{ old('type', $transaction?->type) == 'transfer' ? 'selected' : '' }}>
                    💱 تحويل <span class="text-warning fw-bold">(-)</span>
                </option>
                <option value="other" data-effect="neutral" data-description="حسب طبيعة العملية"
                    {{ old('type', $transaction?->type) == 'other' ? 'selected' : '' }}>
                    📋 أخرى <span class="text-info fw-bold">(±)</span>
                </option>
            </select>

            <!-- مؤشر تأثير العملية -->
            <div id="transaction-effect" class="mt-2" style="display: none;">
                <div class="alert alert-sm p-2 mb-0" id="effect-alert">
                    <i class="fas fa-info-circle me-1"></i>
                    <span id="effect-text">اختر نوع العملية لرؤية تأثيرها على الرصيد</span>
                </div>
            </div>

            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Step 2: Amount and Currency -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="text-primary mb-3">
                <i class="fas fa-coins me-1"></i>
                المبلغ والعملة
            </h5>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">
                <i class="fas fa-money-bill text-success me-1"></i>
                القيمة
            </label>
            <div class="input-group">
                <input type="number" name="amount" id="amount"
                    class="form-control @error('amount') is-invalid @enderror" step="0.01" min="0"
                    placeholder="0.00" value="{{ old('amount', $transaction?->amount) }}">
                <span class="input-group-text" id="currency-symbol">ر.س</span>
            </div>
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">
                <i class="fas fa-globe text-info me-1"></i>
                العملة
            </label>
            <select name="currency" id="currency" class="form-control select2 @error('currency') is-invalid @enderror">
                <option value="SAR"
                    {{ old('currency', $transaction?->currency ?? 'SAR') == 'SAR' ? 'selected' : '' }}>
                    🇸🇦 ريال سعودي (SAR)
                </option>
                <option value="KWD" {{ old('currency', $transaction?->currency) == 'KWD' ? 'selected' : '' }}>
                    🇰🇼 دينار كويتي (KWD)
                </option>
                <option value="EGP" {{ old('currency', $transaction?->currency) == 'EGP' ? 'selected' : '' }}>
                    🇪🇬 جنيه مصري (EGP)
                </option>
                <option value="USD" {{ old('currency', $transaction?->currency) == 'USD' ? 'selected' : '' }}>
                    🇺🇸 دولار أمريكي (USD)
                </option>
                <option value="EUR" {{ old('currency', $transaction?->currency) == 'EUR' ? 'selected' : '' }}>
                    🇪🇺 يورو (EUR)
                </option>
            </select>
            @error('currency')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">
                <i class="fas fa-bookmark text-warning me-1"></i>
                التصنيف
            </label>
            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                placeholder="مثال: راتب، مصروفات شخصية، إلخ..." list="categoryList"
                value="{{ old('category', $transaction?->category) }}">
            <datalist id="categoryList">
                <option value="راتب">
                <option value="مصروفات شخصية">
                <option value="تحويل للعميل">
                <option value="مصروفات العمل">
                <option value="استثمار">
                <option value="فواتير">
                <option value="طعام ومشروبات">
                <option value="نقل ومواصلات">
                <option value="تسوق">
                <option value="ترفيه">
            </datalist>
            @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Currency Converter -->
    <div class="currency-converter p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-warning mb-0">
                <i class="fas fa-exchange-alt me-1"></i>
                حاسبة التحويل المصرفي
                <small class="text-muted">(اختيارية)</small>
            </h6>
            <div>
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i> متوفرة محلياً
                </span>
            </div>
        </div>

        <!-- تعليمات الاستخدام -->
        <div class="alert alert-info alert-sm mb-3">
            <i class="fas fa-info-circle me-1"></i>
            <strong>كيفية الاستخدام:</strong> أدخل المبلغ واختر العملة أولاً، ثم اختر العملة المراد التحويل إليها واضغط
            "احسب التحويل"
        </div>

        <div class="row">
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-arrow-right text-primary me-1"></i>
                    تحويل إلى
                </label>
                <select id="convertToCurrency" class="form-control select2">
                    <option value="">اختر العملة المستهدفة</option>
                    <option value="SAR">🇸🇦 ريال سعودي (SAR)</option>
                    <option value="KWD">🇰🇼 دينار كويتي (KWD)</option>
                    <option value="EGP">🇪🇬 جنيه مصري (EGP)</option>
                    <option value="USD">🇺🇸 دولار أمريكي (USD)</option>
                    <option value="EUR">🇪🇺 يورو (EUR)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-calculator text-success me-1"></i>
                    المبلغ المحول
                </label>
                <input type="text" id="convertedAmount" class="form-control bg-light" readonly
                    placeholder="سيظهر هنا بعد الحساب">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-chart-line text-info me-1"></i>
                    سعر الصرف
                </label>
                <input type="text" id="exchangeRate" class="form-control bg-light" readonly
                    placeholder="سيظهر هنا بعد الحساب">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" id="calculateExchange" class="btn btn-warning w-100">
                    <i class="fas fa-calculator me-1"></i> احسب التحويل
                </button>
            </div>
        </div>

        <!-- معلومات إضافية -->
        <div class="row mt-3">
            <div class="col-12">
                <small class="text-muted d-block">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>العملات المدعومة:</strong> الريال السعودي، الدينار الكويتي، الجنيه المصري، الدولار الأمريكي،
                    اليورو
                </small>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-clock me-1"></i>
                    آخر تحديث للأسعار: 19 ديسمبر 2024 - الأسعار تقريبية وقد تختلف عن الأسعار الحقيقية في البنوك
                </small>
            </div>
        </div>
    </div>

    <!-- Step 3: Additional Details -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="text-primary mb-3">
                <i class="fas fa-paperclip me-1"></i>
                المرفقات والتفاصيل الإضافية
            </h5>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">
                <i class="fas fa-file-upload text-info me-1"></i>
                مرفق (صورة/ملف)
            </label>
            <input type="file" name="link_or_image"
                class="form-control @error('link_or_image') is-invalid @enderror" accept="image/*,.pdf">
            <small class="form-text text-muted">
                يمكنك رفع صورة أو ملف PDF (الحد الأقصى: 5 ميجابايت)
            </small>
            @if ($transaction?->link_or_image)
                <div class="mt-2">
                    <small class="text-success">
                        <i class="fas fa-check me-1"></i>
                        يوجد ملف مرفق حالياً
                    </small>
                </div>
            @endif
            @error('link_or_image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">
                <i class="fas fa-sticky-note text-secondary me-1"></i>
                ملاحظات
            </label>
            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4"
                placeholder="أضف أي ملاحظات إضافية هنا...">{{ old('notes', $transaction?->notes) }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>
                    العودة للقائمة
                </a>
                <div>
                    <button type="button" class="btn btn-outline-info me-2" onclick="resetForm()">
                        <i class="fas fa-redo me-1"></i>
                        إعادة تعيين
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        {{ $transaction ? 'تحديث المعاملة' : 'حفظ المعاملة' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .currency-converter {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 2px dashed #ffc107;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .currency-converter:hover {
        border-color: #ff9500;
        background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%);
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }

    .currency-converter .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9500 100%);
        border: none;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .currency-converter .btn-warning:hover {
        background: linear-gradient(135deg, #ff9500 0%, #ff6b35 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 149, 0, 0.4);
    }

    .currency-converter .btn-warning:disabled {
        background: #6c757d;
        transform: none;
        box-shadow: none;
    }

    .currency-converter .form-control.bg-light {
        background-color: rgba(255, 255, 255, 0.8) !important;
        border: 1px solid #dee2e6;
    }

    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .highlight-result {
        animation: highlightPulse 2s ease-in-out;
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    }

    @keyframes highlightPulse {
        0% {
            background-color: #d4edda;
            border-color: #28a745;
        }

        50% {
            background-color: #c3e6cb;
            border-color: #20c997;
        }

        100% {
            background-color: white;
            border-color: #dee2e6;
        }
    }

    .currency-converter .alert {
        border-radius: 8px;
        border: none;
        font-size: 0.9rem;
    }

    .currency-converter .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }

    .currency-converter .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }
</style>

@push('scripts')
    <script>
        $(document).ready(function() {
            // تحديث رمز العملة
            const currencySymbols = {
                'SAR': 'ر.س',
                'KWD': 'د.ك',
                'EGP': 'ج.م',
                'USD': '$',
                'EUR': '€'
            };

            const currencyNames = {
                'SAR': 'الريال السعودي',
                'KWD': 'الدينار الكويتي',
                'EGP': 'الجنيه المصري',
                'USD': 'الدولار الأمريكي',
                'EUR': 'اليورو'
            };

            $('#currency').change(function() {
                const selectedCurrency = $(this).val();
                $('#currency-symbol').text(currencySymbols[selectedCurrency] || selectedCurrency);

                // مسح النتائج عند تغيير العملة
                clearCalculationResults();
            });

            // تشغيل عند التحميل
            $('#currency').trigger('change');

            // حاسبة التحويل المصرفي المحسنة
            $('#calculateExchange').click(function(e) {
                e.preventDefault();

                const amount = parseFloat($('#amount').val());
                const fromCurrency = $('#currency').val();
                const toCurrency = $('#convertToCurrency').val();

                // التحقق من صحة البيانات
                if (!amount || amount <= 0 || isNaN(amount)) {
                    showErrorMessage('يرجى إدخال مبلغ صحيح أكبر من صفر');
                    return false;
                }

                if (!fromCurrency) {
                    showErrorMessage('يرجى اختيار العملة المصدر');
                    return false;
                }

                if (!toCurrency) {
                    showErrorMessage('يرجى اختيار العملة المستهدفة');
                    return false;
                }

                if (fromCurrency === toCurrency) {
                    showErrorMessage('لا يمكن التحويل من نفس العملة إلى نفسها');
                    return false;
                }

                // إظهار loading
                const $button = $(this);
                const originalText = $button.html();
                $button.html('<i class="fas fa-spinner fa-spin"></i> جاري الحساب...')
                    .prop('disabled', true);

                // مسح النتائج السابقة
                clearCalculationResults();

                // طلب AJAX محسن
                $.ajax({
                    url: '{{ route('admin.transactions.exchange-rates') }}',
                    method: 'GET',
                    data: {
                        from: fromCurrency,
                        to: toCurrency,
                        amount: amount
                    },
                    timeout: 10000, // 10 ثواني timeout
                    cache: false,
                    success: function(data) {
                        console.log('API Response:', data); // للتصحيح

                        if (data.success) {
                            const convertedAmountText = parseFloat(data.converted_amount)
                                .toLocaleString('ar-SA', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' ' + currencySymbols[data.to];

                            const exchangeRateText = '1 ' + currencySymbols[data.from] + ' = ' +
                                parseFloat(data.rate).toLocaleString('ar-SA', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 6
                                }) + ' ' + currencySymbols[data.to];

                            $('#convertedAmount').val(convertedAmountText);
                            $('#exchangeRate').val(exchangeRateText);

                            // إظهار رسالة نجاح مع التفاصيل
                            const successMessage = `
                        <strong>تم حساب التحويل بنجاح!</strong><br>
                        <small>${data.note || 'تحويل من ' + currencyNames[fromCurrency] + ' إلى ' + currencyNames[toCurrency]}</small>
                    `;
                            showSuccessMessage(successMessage);

                            // إضافة تأثيرات بصرية
                            $('#convertedAmount, #exchangeRate').addClass('highlight-result');
                            setTimeout(() => {
                                $('#convertedAmount, #exchangeRate').removeClass(
                                    'highlight-result');
                            }, 2000);

                        } else {
                            showErrorMessage(data.message || 'خطأ في حساب التحويل');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error, xhr
                        .responseText); // للتصحيح

                        let errorMessage = 'خطأ في الاتصال بحاسبة التحويل';

                        if (xhr.status === 422) {
                            // خطأ في التحقق من البيانات
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || 'بيانات غير صحيحة';
                            } catch (e) {
                                errorMessage = 'بيانات غير صحيحة';
                            }
                        } else if (xhr.status === 400) {
                            // خطأ في المعطيات
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || 'طلب غير صحيح';
                            } catch (e) {
                                errorMessage = 'طلب غير صحيح';
                            }
                        } else if (status === 'timeout') {
                            errorMessage = 'انتهت مهلة الانتظار - يرجى المحاولة مرة أخرى';
                        } else if (xhr.status === 404) {
                            errorMessage = 'خدمة التحويل غير متوفرة حالياً';
                        } else if (xhr.status === 500) {
                            errorMessage = 'خطأ في الخادم - يرجى المحاولة لاحقاً';
                        } else if (xhr.status === 0) {
                            errorMessage = 'لا يوجد اتصال بالإنترنت';
                        }

                        showErrorMessage(errorMessage);
                    },
                    complete: function() {
                        // إعادة الزر لحالته الأصلية
                        $button.html(originalText).prop('disabled', false);
                    }
                });
            });

            // تحديث التحويل عند تغيير المبلغ أو العملة
            $('#amount, #currency, #convertToCurrency').on('change input', function() {
                clearCalculationResults();
            });

            // منع إرسال النموذج إذا تم الضغط على Enter في حقل المبلغ
            $('#amount').keypress(function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    $('#calculateExchange').click();
                }
            });
        });

        // دالة مسح النتائج
        function clearCalculationResults() {
            $('#convertedAmount').val('');
            $('#exchangeRate').val('');
            $('.alert-success, .alert-danger').fadeOut(function() {
                $(this).remove();
            });
        }

        // دوال عرض الرسائل المحسنة
        function showSuccessMessage(message) {
            // إزالة الرسائل القديمة
            $('.alert-success, .alert-danger').remove();

            const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fa-lg"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

            $('.currency-converter').after(alertHtml);

            // إزالة التنبيه بعد 6 ثوان
            setTimeout(function() {
                $('.alert-success').fadeOut(function() {
                    $(this).remove();
                });
            }, 6000);
        }

        function showErrorMessage(message) {
            // إزالة الرسائل القديمة
            $('.alert-success, .alert-danger').remove();

            const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

            $('.currency-converter').after(alertHtml);

            // إزالة التنبيه بعد 10 ثوان
            setTimeout(function() {
                $('.alert-danger').fadeOut(function() {
                    $(this).remove();
                });
            }, 10000);
        }

        // إعادة تعيين النموذج
        function resetForm() {
            if (confirm('هل أنت متأكد من إعادة تعيين النموذج؟\nسيتم فقدان جميع البيانات المدخلة.')) {
                document.getElementById('transactionForm').reset();
                $('.select2').val(null).trigger('change');
                $('#currency').val('SAR').trigger('change');
                clearCalculationResults();
            }
        }
    </script>
@endpush
