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

                     @foreach ($remainingByCurrency as $currency => $amount)
                         @if ($amount != 0)
                             <span class="{{ $amount > 0 ? 'text-danger' : 'text-success' }}">
                                 {{ $amount > 0 ? '+' : '' }}{{ number_format($amount, 2) }}
                             </span>
                             {{ $currency === 'SAR' ? 'ريال' : 'دينار' }}<br>
                             @if ($amount < 0)
                                 <small class="text-muted">(دفعوا زيادة)</small>
                             @endif
                         @endif
                     @endforeach
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
