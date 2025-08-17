 <table class="table table-bordered table-striped" id="companiesTableContent">
     <thead>
         <tr>
             <th>الشركة</th>
             <th>عدد الحجوزات</th>
             <th>إجمالي المستحق</th>
             <th>المدفوع</th>
             <th>المتبقي</th>
             <th>العمليات</th>
         </tr>
     </thead>
     <tbody>
         @foreach ($companiesReport as $company)
             <tr>
                 <td>{{ $loop->iteration }}. {{ $company->name }}
                     @php
                         $hasEdit =
                             $recentCompanyEdits
                                 ->filter(function ($n) use ($company) {
                                     return str_contains($n->first()->message, $company->name);
                                 })
                                 ->count() > 0;
                     @endphp
                     @if ($hasEdit)
                         <span class="badge bg-success" style="font-size: 0.7em;">edit</span>
                     @endif
                 </td>
                 <td>{{ $company->total_bookings_count }}</td>
                 <td>
                     @php
                         // 1. مستحقات الفنادق حسب العملة
                         $hotelDueByCurrency = $company->total_due_bookings_by_currency ?? [
                             'SAR' => 0,
                             'KWD' => 0,
                         ];
                         // 2. مستحقات الرحلات البرية حسب العملة
                         $tripDueByCurrency = $company->landTripBookings
                             ->groupBy('currency')
                             ->map->sum('amount_due_from_company')
                             ->toArray();
                         // 3. مدفوعات الرحلات البرية حسب العملة
                         // 3. مدفوعات الرحلات البرية حسب العملة - استخدام الطريقة الصحيحة
                         $tripPayments = $company
                             ->companyPayments()
                             ->select(
                                 'currency',
                                 DB::raw('SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) as paid'),
                                 DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as discounts'),
                             )
                             ->groupBy('currency')
                             ->get()
                             ->keyBy('currency');
                         // 4. المتبقي من الرحلات البرية
                         $tripRemainingByCurrency = [];
                         foreach (['SAR', 'KWD'] as $cur) {
                             $due = $tripDueByCurrency[$cur] ?? 0;
                             $paid = (float) ($tripPayments[$cur]->paid ?? 0);
                             $discounts = (float) ($tripPayments[$cur]->discounts ?? 0);
                             $tripRemainingByCurrency[$cur] = $due - $paid - $discounts;
                         }
                         // 5. إجمالي المستحق لكل عملة
                         $totalDueByCurrency = [];
                         foreach (['SAR', 'KWD'] as $cur) {
                             $totalDueByCurrency[$cur] =
                                 ($hotelDueByCurrency[$cur] ?? 0) + ($tripDueByCurrency[$cur] ?? 0);
                         }
                     @endphp

                     <div class="d-grid gap-2"
                         style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));">
                         {{-- إجمالي المستحق --}}
                         @foreach ($totalDueByCurrency as $cur => $amt)
                             @if ($amt > 0)
                                 <div>
                                     <strong>{{ number_format($amt, 2) }}</strong>
                                     {{ $cur === 'SAR' ? 'ريال' : 'دينار' }}
                                 </div>
                             @endif
                         @endforeach

                         {{-- تفاصيل الفنادق --}}
                         @foreach ($hotelDueByCurrency as $cur => $amt)
                             @if ($amt > 0)
                                 <div>
                                     <span class="badge bg-success text-white">
                                         <i class="fas fa-hotel me-1"></i>
                                         {{ number_format($amt, 2) }}
                                         {{ $cur === 'SAR' ? 'ريال' : 'دينار' }}
                                     </span>
                                 </div>
                             @endif
                         @endforeach

                         {{-- المتبقي من الرحلات البرية --}}
                         @foreach ($tripRemainingByCurrency as $cur => $rem)
                             @if ($rem != 0)
                                 <div>
                                     <span class="badge bg-info text-dark">
                                         <i class="fas fa-bus me-1"></i>
                                         {{ $rem > 0 ? number_format($rem, 2) : '-' . number_format(abs($rem), 2) }}
                                         {{ $cur === 'SAR' ? 'ريال' : 'دينار' }}
                                     </span>
                                 </div>
                             @endif
                         @endforeach
                     </div>
                 </td>
                 <td>
                     @php
                         // استخدام القيم المحسوبة للمدفوعات
                         $paidByCurrency = $company->computed_total_paid_by_currency ?? [];
                         $discountsByCurrency = $company->computed_total_discounts_by_currency ?? [];

                         // إذا لم تكن محسوبة، استخدم الطريقة القديمة كـ fallback
                         if (empty($paidByCurrency)) {
                             $paymentsByCurrency = $company->payments
                                 ? $company->payments->groupBy('currency')
                                 : collect();
                         }
                     @endphp

                     {{-- عرض المدفوعات المحسوبة --}}
                     @if (!empty($paidByCurrency))
                         @foreach ($paidByCurrency as $currency => $paidAmount)
                             @if ($paidAmount > 0)
                                 <div class="mb-1">
                                     <strong class="text-success">{{ number_format($paidAmount, 2) }}</strong>
                                     {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}

                                     {{-- عرض الخصومات إذا وجدت --}}
                                     @if (($discountsByCurrency[$currency] ?? 0) > 0)
                                         <br><small class="text-warning">
                                             <i class="fas fa-minus-circle me-1"></i>
                                             خصومات:
                                             {{ number_format($discountsByCurrency[$currency], 2) }}
                                             {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                         </small>
                                     @endif
                                 </div>
                             @endif
                         @endforeach
                     @else
                         {{-- الطريقة القديمة كـ fallback --}}
                         @forelse ($paymentsByCurrency as $currency => $payments)
                             @php
                                 $positivePaid = $payments->where('amount', '>=', 0)->sum('amount');
                                 $discounts = $payments->where('amount', '<', 0)->sum('amount');
                                 $discountsAbsolute = abs($discounts);
                             @endphp
                             <div class="mb-1">
                                 <strong class="text-success">{{ number_format($positivePaid, 2) }}</strong>
                                 {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                 @if ($discountsAbsolute > 0)
                                     <br><small class="text-warning">
                                         <i class="fas fa-minus-circle me-1"></i>
                                         خصومات: {{ number_format($discountsAbsolute, 2) }}
                                         {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                     </small>
                                 @endif
                             </div>
                         @empty
                             0 ريال
                         @endforelse
                     @endif
                 </td>
                 <td>
                     {{-- المتبقي --}}
                     @php
                         // حساب المتبقي بشكل صحيح (المستحق - المدفوع الصافي)
                         $remainingByCurrency = [];
                         foreach (['SAR', 'KWD'] as $curr) {
                             // 1. المستحق حسب العملة
                             $due = $company->total_due_bookings_by_currency[$curr] ?? 0;

                             // 2. المدفوع الفعلي (المدفوعات الموجبة)
                             $paid = $company->computed_total_paid_by_currency[$curr] ?? 0;

                             // 3. الخصومات (تعامل كجزء من المدفوع)
                             $discounts = $company->computed_total_discounts_by_currency[$curr] ?? 0;

                             // 4. المتبقي = المستحق - (المدفوع - الخصم)
                             // حيث يعتبر الخصم جزءًا من المبلغ المدفوع
                             $remainingByCurrency[$curr] = $due - ($paid + $discounts);
                         }
                     @endphp

                     @php $hasAny = collect($remainingByCurrency)->filter(fn($v) => $v != 0)->isNotEmpty(); @endphp
                     @if ($hasAny)
                         <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                             @foreach ($remainingByCurrency as $currency => $amount)
                                 @continue($amount == 0)

                                 <span
                                     class="d-inline-flex align-items-center bg-{{ $amount > 0 ? 'danger' : 'success' }} text-white rounded-pill px-2 py-1 small lh-sm text-nowrap">
                                     {{-- اختياري: علامة + للموجب --}}
                                     @if ($amount > 0)
                                         <span class="me-1">+</span>
                                     @endif

                                     <strong dir="ltr"
                                         class="mx-1">{{ number_format(abs($amount), 2) }}</strong>
                                     <span>{{ $currency === 'SAR' ? 'ر. سعودي' : 'دينار' }}</span>

                                     @if ($amount < 0)
                                         <span class="ms-1 opacity-75">(دفعوا زيادة)</span>
                                     @endif
                                 </span>
                             @endforeach
                         </div>
                     @endif


                     @php
                         $cb = $company->current_balance ?? [];
                         $bal = $cb['balance'] ?? 0;
                         $enteredDue = $cb['entered_due'] ?? 0;
                         $effectivePaid = $cb['effective_paid'] ?? 0;
                         $accColor = $bal > 0 ? 'danger' : ($bal < 0 ? 'success' : 'secondary');
                         $statusTxt = $bal > 0 ? 'مستحق' : ($bal < 0 ? 'دفع زائد' : 'مغلق');
                     @endphp

                     <div class="mt-2 company-balance-card border rounded-3 p-2 small bg-light-subtle">
                         <div class="d-flex justify-content-between align-items-center mb-1">
                             <span class="fw-semibold">
                                 <i class="fas fa-scale-balanced me-1"></i>رصيد اليوم
                             </span>
                             <span class="badge bg-{{ $accColor }}">{{ $statusTxt }}</span>
                         </div>
                         <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                             <span class="text-muted">دخلت</span>
                             <span class="fw-semibold text-primary" dir="ltr">{{ number_format($enteredDue, 2) }}
                                 ر.</span>
                         </div>
                         <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                             <span class="text-muted">مدفوع + خصومات</span>
                             <span class="fw-semibold text-info" dir="ltr">{{ number_format($effectivePaid, 2) }}
                                 ر.</span>
                         </div>
                         <div class="d-flex justify-content-between">
                             <span
                                 class="text-muted">{{ $bal > 0 ? 'مستحق' : ($bal < 0 ? 'دفع زائد' : 'الصافي') }}</span>
                             <span class="fw-bold text-{{ $accColor }}" dir="ltr">
                                 {{ number_format(abs($bal), 2) }} ر.
                             </span>
                         </div>
                     </div>
                 </td>
                 <td>
                     <div class="action-buttons-grid">
                         <a href="{{ route('reports.company.bookings', $company->id) }}"
                             class="btn btn-info btn-sm">عرض الحجوزات</a>
                         <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                             data-bs-target="#paymentModal{{ $company->id }}">
                             تسجيل دفعة
                         </button>
                         <a href="{{ route('reports.company.payments', $company->id) }}"
                             class="btn btn-primary btn-sm">كشف حساب</a>
                     </div>
                 </td>
             </tr>
         @endforeach
     </tbody>
     <tfoot>
         <tr class="table-secondary fw-bold">
             <td class="text-center">الإجمالي</td>
             <td class="text-center">
                 {{ $companiesReport->sum('total_bookings_count') }}
             </td>
             <td>
                 @php
                     // ✅ حساب الإجمالي من القيم المحسوبة للشركات (مشابه لجدول الوكلاء)
                     $totalCompanyDueByCurrency = ['SAR' => 0, 'KWD' => 0];
                     foreach ($companiesReport as $company) {
                         // 1. مستحقات الفنادق حسب العملة
                         $hotelDueByCurrency = $company->total_due_bookings_by_currency ?? ['SAR' => 0, 'KWD' => 0];

                         // 2. مستحقات الرحلات البرية حسب العملة
                         $tripDueByCurrency = $company->landTripBookings
                             ->groupBy('currency')
                             ->map->sum('amount_due_from_company')
                             ->toArray();

                         // 3. إضافة المستحقات لإجمالي الشركة
                         foreach (['SAR', 'KWD'] as $cur) {
                             $totalCompanyDueByCurrency[$cur] +=
                                 ($hotelDueByCurrency[$cur] ?? 0) + ($tripDueByCurrency[$cur] ?? 0);
                         }
                     }
                 @endphp
                 @foreach ($totalCompanyDueByCurrency as $currency => $amount)
                     @if ($amount > 0)
                         {{ number_format((float) $amount, 2) }}
                         {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                     @endif
                 @endforeach
             </td>
             <td>
                 @php
                     // ✅ حساب إجمالي المدفوعات من كل شركة
                     $totalCompanyPaidByCurrency = ['SAR' => 0, 'KWD' => 0];
                     $totalCompanyDiscountsByCurrency = ['SAR' => 0, 'KWD' => 0];

                     foreach ($companiesReport as $company) {
                         // استخدام القيم المحسوبة للمدفوعات مع وجود fallback
                         $paidByCurrency = $company->computed_total_paid_by_currency ?? [];
                         $discountsByCurrency = $company->computed_total_discounts_by_currency ?? [];

                         // جمع المدفوعات لكل عملة
                         foreach ($paidByCurrency as $currency => $amount) {
                             $totalCompanyPaidByCurrency[$currency] += (float) $amount;
                         }

                         // جمع الخصومات لكل عملة
                         foreach ($discountsByCurrency as $currency => $amount) {
                             $totalCompanyDiscountsByCurrency[$currency] += (float) $amount;
                         }
                     }
                 @endphp

                 @foreach (['SAR', 'KWD'] as $currency)
                     @if (($totalCompanyPaidByCurrency[$currency] ?? 0) > 0 || ($totalCompanyDiscountsByCurrency[$currency] ?? 0) > 0)
                         <div class="mb-1">
                             <strong
                                 class="text-success">{{ number_format((float) ($totalCompanyPaidByCurrency[$currency] ?? 0), 2) }}</strong>
                             {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}

                             @if (($totalCompanyDiscountsByCurrency[$currency] ?? 0) > 0)
                                 <br><small class="text-warning">
                                     <i class="fas fa-minus-circle me-1"></i>
                                     خصومات:
                                     {{ number_format((float) ($totalCompanyDiscountsByCurrency[$currency] ?? 0), 2) }}
                                     {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}
                                 </small>
                             @endif
                         </div>
                     @endif
                 @endforeach
             </td>
             <td>
                 @php
                     // ✅ حساب إجمالي المتبقي بناءً على الإجماليات المحسوبة أعلاه
                     $totalCompanyRemainingByCurrency = ['SAR' => 0, 'KWD' => 0];

                     foreach (['SAR', 'KWD'] as $currency) {
                         // 1. إجمالي المستحق
                         $totalDue = $totalCompanyDueByCurrency[$currency] ?? 0;

                         // 2. إجمالي المدفوع + الخصومات (صافي المدفوع)
                         $totalPaid = $totalCompanyPaidByCurrency[$currency] ?? 0;
                         $totalDiscounts = $totalCompanyDiscountsByCurrency[$currency] ?? 0;

                         // 3. المتبقي = المستحق - (المدفوع + الخصومات)
                         $remaining = $totalDue - ($totalPaid + $totalDiscounts);

                         $totalCompanyRemainingByCurrency[$currency] = $remaining;
                     }
                 @endphp

                 @foreach ($totalCompanyRemainingByCurrency as $currency => $amount)
                     @if ($amount != 0)
                         <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                             {{ $amount > 0 ? '+' : '' }}{{ number_format((float) $amount, 2) }}
                         </span>
                         {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                         @if ($amount < 0)
                             <small class="text-muted">(دفعوا زيادة)</small>
                         @endif
                     @endif
                 @endforeach

                 {{-- عرض 0 إذا كان المجموع صفر في كل العملات --}}
                 @if (!array_sum(array_map('abs', $totalCompanyRemainingByCurrency)))
                     <span class="text-success">0.00 ريال</span><br>
                     <small class="text-muted">(متوازن)</small>
                 @endif
             </td>
             <td></td>
         </tr>
     </tfoot>
 </table>

 <!-- للشركات نموذج تسجيل الدفعات -->
 @foreach ($companiesReport as $company)
     <div class="modal fade" id="paymentModal{{ $company->id }}" tabindex="-1">
         <div class="modal-dialog">
             <div class="modal-content">
                 <form action="{{ route('reports.company.payment') }}" method="POST" enctype="multipart/form-data">
                     @csrf
                     <input type="hidden" name="company_id" value="{{ $company->id }}">
                     <input type="hidden" name="is_discount" id="is-discount-{{ $company->id }}" value="0">

                     <div class="modal-header">
                         <h5 class="modal-title">تسجيل دفعة - {{ $company->name }}</h5>
                         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                     </div>

                     <div class="modal-body">
                         <div class="mb-3">
                             <label class="form-label">المبلغ المدفوع والعملة</label>
                             <div> قم بعمل سند قبض لهذه العملية : <a href="{{ route('admin.receipt.voucher') }}"
                                     target="_blank">إنشاء سند
                                     قبض</a></div>
                             <div class="input-group">
                                 <input type="number" step="0.01" class="form-control" name="amount" required>
                                 <select class="form-select" name="currency" style="max-width: 120px;">
                                     <option value="SAR" selected>ريال سعودي</option>
                                     <option value="KWD">دينار كويتي</option>
                                 </select>
                             </div>
                         </div>
                         {{-- *** أضف حقل رفع الملف مشكلة مع جوجل درايف لسه هتتحل  *** --}}
                         {{-- <div class="mb-3">
                                    <label for="receipt_file_company_{{ $company->id }}" class="form-label">إرفاق إيصال
                                        (اختياري)
                                    </label>
                                    <input class="form-control" type="file"
                                        id="receipt_file_company_{{ $company->id }}" name="receipt_file">
                                  
                                <small class="form-text text-muted">الملفات المسموحة: JPG, PNG, PDF (بحد أقصى
                                    5MB)</small>
                            </div> --}}
                         {{-- *** نهاية حقل رفع الملف *** --}}
                         <div class="mb-3">
                             <label class="form-label">ملاحظات <br>
                                 (إن كانت معك صورة من التحويل ارفعها على درايف وضع الرابط هنا)
                             </label>
                             <textarea class="form-control" name="notes"></textarea>
                         </div>
                     </div>

                     <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                         <button type="button" class="btn btn-warning" id="toggleDiscountBtn-{{ $company->id }}"
                             onclick="toggleDiscountMode({{ $company->id }})">تسجيل خصم</button>
                         <button type="submit" class="btn btn-primary" id="submitBtn-{{ $company->id }}">تسجيل
                             الدفعة</button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 @endforeach
 <script>
     function toggleDiscountMode(companyId) {
         const isDiscountField = document.getElementById('is-discount-' + companyId);
         const submitBtn = document.getElementById('submitBtn-' + companyId);
         const toggleBtn = document.getElementById('toggleDiscountBtn-' + companyId);
         const modalTitle = document.querySelector('#paymentModal' + companyId + ' .modal-title');
         const companyName = modalTitle.textContent.split('-')[1].trim();

         if (isDiscountField.value === "0") {
             isDiscountField.value = "1";
             submitBtn.textContent = "تطبيق الخصم";
             submitBtn.classList.remove('btn-primary');
             submitBtn.classList.add('btn-warning');
             toggleBtn.textContent = "تسجيل دفعة";
             modalTitle.textContent = "تسجيل خصم - " + companyName;
         } else {
             isDiscountField.value = "0";
             submitBtn.textContent = "تسجيل الدفعة";
             submitBtn.classList.remove('btn-warning');
             submitBtn.classList.add('btn-primary');
             toggleBtn.textContent = "تسجيل خصم";
             modalTitle.textContent = "تسجيل دفعة - " + companyName;
         }
     }
 </script>
 {{-- ✅ إضافة مكتبة XLSX --}}
 <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
 <script>
     function exportTableOfCompanies() {
         const btn = document.getElementById('export-btn');
         const originalHtml = btn.innerHTML;

         // حالة التحميل
         btn.disabled = true;
         btn.innerHTML = `جاري التحميل... <i class="fas fa-spinner fa-spin"></i>`;

         (async () => {
             try {
                 const tableSelector = '#companiesTableContent';
                 const paginationSelector = 'ul.pagination';
                 const colSelectors = {
                     company: 'td:nth-child(1)',
                     bookingsCount: 'td:nth-child(2)',
                     totalDue: 'td:nth-child(3)',
                     paid: 'td:nth-child(4)',
                     remaining: 'td:nth-child(5)',
                 };

                 // ===== Helpers =====
                 const normText = (t) => (t || '').replace(/\s+/g, ' ').trim();
                 const normalizeDigits = (s) => (s || '').replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));

                 const parseAmountSmart = (raw) => {
                     if (!raw) return null;
                     let s = normalizeDigits(raw)
                         .replace(/\s|\u00A0|\u200F|\u200E/g, '')
                         .replace(/٫/g, '.')
                         .replace(/٬/g, ',');

                     const token = (s.match(/[-+0-9.,]+/g) || [])[0];
                     if (!token) return null;

                     s = token;
                     const dots = (s.match(/\./g) || []).length;
                     const commas = (s.match(/,/g) || []).length;
                     const seps = dots + commas;

                     if (seps === 0) return Number(s);

                     if (seps >= 2) {
                         const lastSepIdx = Math.max(s.lastIndexOf(','), s.lastIndexOf('.'));
                         const intPart = s.slice(0, lastSepIdx).replace(/[.,]/g, '');
                         const fracPart = s.slice(lastSepIdx + 1).replace(/[^\d]/g, '');
                         return Number(`${intPart}.${fracPart || '0'}`);
                     }

                     const sep = s.includes('.') ? '.' : ',';
                     const sepIdx = s.lastIndexOf(sep);
                     const before = s.slice(0, sepIdx);
                     const after = s.slice(sepIdx + 1);

                     if (/^\d{3}$/.test(after)) {
                         return Number((before + after).replace(/[^\d\-+]/g, ''));
                     }

                     const normalized = (sep === ',') ?
                         s.replace(/\./g, '').replace(',', '.') :
                         s.replace(/,/g, '');
                     return Number(normalized);
                 };

                 const firstAmountIn = (txt) => {
                     if (!txt) return null;
                     const s = normalizeDigits(txt).replace(/٫/g, '.').replace(/٬/g, ',');
                     const tokens = s.match(/[-+0-9.,]+/g);
                     if (!tokens) return null;
                     for (const tok of tokens) {
                         const n = parseAmountSmart(tok);
                         if (Number.isFinite(n)) return n;
                     }
                     return null;
                 };

                 const getPageUrls = (rootDoc) => {
                     const urls = new Set([location.href]);
                     const pag = rootDoc.querySelector(paginationSelector);
                     if (pag) {
                         pag.querySelectorAll('a.page-link[href]').forEach(a => {
                             try {
                                 urls.add(new URL(a.href, location.href).href);
                             } catch {}
                         });
                     }
                     return urls;
                 };

                 const fetchDoc = async (url) => {
                     const res = await fetch(url, {
                         credentials: 'same-origin'
                     });
                     const html = await res.text();
                     return new DOMParser().parseFromString(html, 'text/html');
                 };

                 const extractDailyBalance = (tdRemaining) => {
                     const out = {
                         "دخلت": null,
                         "مدفوع": null,
                         "الصافي": null
                     };
                     if (!tdRemaining) return out;
                     const card = tdRemaining.querySelector('.company-balance-card');
                     if (!card) return out;

                     card.querySelectorAll('.d-flex.justify-content-between').forEach(row => {
                         const key = normText(row.querySelector('span:first-child')?.textContent ||
                             '');
                         const val = normText(row.querySelector('span:last-child')?.textContent ||
                             '');
                         if (/دخلت/.test(key)) out["دخلت"] = val || null;
                         else if (/مدفوع/.test(key)) out["مدفوع"] = val || null;
                         else if (/الصافي/.test(key)) out["الصافي"] = val || null;
                     });
                     return out;
                 };

                 const extractRemainingBadge = (tdRemaining) => {
                     if (!tdRemaining) return {
                         num: null,
                         raw: null
                     };
                     const pill = tdRemaining.querySelector('.rounded-pill');
                     let num = null,
                         raw = null;
                     if (pill) {
                         const amtEl = pill.querySelector('strong');
                         raw = normText(pill.textContent || '');
                         if (amtEl) num = firstAmountIn(amtEl.textContent);
                     }
                     return {
                         num,
                         raw
                     };
                 };

                 const extractDiscount = (tdPaid) => {
                     if (!tdPaid) return {
                         discount: null,
                         discount_raw: null
                     };
                     let discount = null,
                         discount_raw = null;
                     tdPaid.querySelectorAll('small, span, div').forEach(el => {
                         const t = normText(el.textContent || '');
                         if (/خصومات/.test(t)) {
                             discount_raw = t;
                             const n = firstAmountIn(t);
                             if (Number.isFinite(n)) discount = n;
                         }
                     });
                     return {
                         discount,
                         discount_raw
                     };
                 };

                 const extractRow = (tr) => {
                     const td1 = tr.querySelector(colSelectors.company);
                     const td2 = tr.querySelector(colSelectors.bookingsCount);
                     const td3 = tr.querySelector(colSelectors.totalDue);
                     const td4 = tr.querySelector(colSelectors.paid);
                     const td5 = tr.querySelector(colSelectors.remaining);

                     const companyRaw = normText(td1?.textContent);
                     const company = companyRaw.replace(/^\d+\.\s*/, '');

                     const total_due = firstAmountIn(normText(td3?.textContent));
                     const paid_main = firstAmountIn(normText(td4?.textContent));

                     const {
                         discount,
                         discount_raw
                     } = extractDiscount(td4);
                     const daily = extractDailyBalance(td5);
                     const rem = extractRemainingBadge(td5);

                     const remaining = (typeof rem.num === 'number') ? rem.num : null;

                     const displayRow = {
                         "اسم الشركة": company,
                         "عدد الحجوزات المسجلة": td2 ? Number(firstAmountIn(td2.textContent) ?? 0) : 0,
                         "إجمالي المستحق": (typeof total_due === 'number') ? total_due : null,
                         "المدفوع": (typeof paid_main === 'number') ? paid_main : null,
                         "الخصومات": (typeof discount === 'number') ? discount : null,
                         "المتبقي": (typeof remaining === 'number') ? remaining : null,
                         "رصيد اليوم - دخلت": daily["دخلت"],
                         "رصيد اليوم - مدفوع": daily["مدفوع"],
                         "رصيد اليوم - الصافي": daily["الصافي"]
                     };

                     const numericRow = {
                         ...displayRow,
                         "رصيد اليوم - دخلت (num)": firstAmountIn(daily["دخلت"]),
                         "رصيد اليوم - مدفوع (num)": firstAmountIn(daily["مدفوع"]),
                         "رصيد اليوم - الصافي (num)": firstAmountIn(daily["الصافي"]),
                         "الخصومات (raw)": discount_raw ?? null
                     };

                     return {
                         displayRow,
                         numericRow
                     };
                 };

                 const extractRowsFromDoc = (doc) => {
                     const view = [];
                     const numeric = [];
                     const table = doc.querySelector(tableSelector);
                     if (!table) return {
                         view,
                         numeric
                     };
                     table.querySelectorAll('tbody tr').forEach(tr => {
                         const tds = tr.querySelectorAll('td');
                         if (tds.length < 5) return;
                         const {
                             displayRow,
                             numericRow
                         } = extractRow(tr);
                         view.push(displayRow);
                         numeric.push(numericRow);
                     });
                     return {
                         view,
                         numeric
                     };
                 };

                 // ===== التنفيذ =====
                 const allUrls = Array.from(getPageUrls(document)).sort((a, b) => {
                     const getN = (u) => {
                         const url = new URL(u, location.href);
                         return Number(url.searchParams.get('companies_page') || (url.href ===
                             location.href ? 1 : 1e9));
                     };
                     return getN(a) - getN(b);
                 });

                 const allRowsView = [];
                 const allRowsNumeric = [];
                 const currentHref = location.href;

                 {
                     const {
                         view,
                         numeric
                     } = extractRowsFromDoc(document);
                     allRowsView.push(...view);
                     allRowsNumeric.push(...numeric);
                 }

                 for (const url of allUrls) {
                     if (url === currentHref) continue;
                     try {
                         const doc = await fetchDoc(url);
                         const {
                             view,
                             numeric
                         } = extractRowsFromDoc(doc);
                         allRowsView.push(...view);
                         allRowsNumeric.push(...numeric);
                         console.log('✅ Extracted:', url);
                     } catch (e) {
                         console.warn('⚠️ Failed:', url, e);
                     }
                 }

                 console.log('=== النتائج (عرض) ===');
                 console.log(JSON.stringify(allRowsView, null, 2));
                 console.log('=== النتائج (رقمية) ===');
                 console.log(JSON.stringify(allRowsNumeric, null, 2));
                 console.log(`🎉 تم — عدد الصفوف: ${allRowsView.length}`);

                 if (window.XLSX) {
                     const ws1 = XLSX.utils.json_to_sheet(allRowsView, {
                         skipHeader: false
                     });
                     const ws2 = XLSX.utils.json_to_sheet(allRowsNumeric, {
                         skipHeader: false
                     });
                     const wb = XLSX.utils.book_new();
                     XLSX.utils.book_append_sheet(wb, ws1, 'تقرير (عرض)');
                     XLSX.utils.book_append_sheet(wb, ws2, 'تقرير (رقمي)');
                     const fileName = `حساب-المطلوب-من-الشركات-${new Date().toISOString().split('T')[0]}.xlsx`;
                     XLSX.writeFile(wb, fileName);
                 } else {
                     console.warn('XLSX library not found. Skipping Excel export.');
                 }
             } catch (err) {
                 console.error('Export failed:', err);
                 // اختياري: بلغ المستخدم
                 // alert('حصل خطأ أثناء التصدير. راجع الـ Console.');
             } finally {
                 // ✅ يرجع الزر لحالته الطبيعية مهما حصل
                 btn.disabled = false;
                 btn.innerHTML = originalHtml;
             }
         })();
     }
 </script>
