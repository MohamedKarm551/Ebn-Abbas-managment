<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;        
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class FinancialReportsController extends Controller
{
    // =============================
    // ميزان المراجعة
    // =============================
    public function trialBalance(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateTo   = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $rootAccounts = Account::with('allChildren')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $accounts = $this->buildTrialBalanceTree($rootAccounts, $dateFrom, $dateTo);

        $accounts = $this->filterNonZero($accounts);

        $totalDebit  = $this->sumTree($accounts, 'total_debit');
        $totalCredit = $this->sumTree($accounts, 'total_credit');

        if ($request->query('export') === 'excel') {
            return $this->exportToExcel($accounts, 'ميزان_المراجعة', $dateFrom, $dateTo); 
        }
        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('financial-reports.trial-balance-pdf', compact('accounts','totalDebit','totalCredit','dateFrom','dateTo'), [], [
                'format' => 'A4-L', 'default_font' => 'cairo', 'mode' => 'utf-8',
            ]);
            return $pdf->download('ميزان_المراجعة.pdf');
        }

        return view('financial-reports.trial-balance', compact(
            'accounts', 'totalDebit', 'totalCredit', 'dateFrom', 'dateTo'
        ));
    }

    private function buildTrialBalanceTree($accounts, $dateFrom, $dateTo): \Illuminate\Support\Collection
    {
        return $accounts->map(function ($account) use ($dateFrom, $dateTo) {
            if ($account->is_leaf) {
                $data = $this->calcBalance($account, $dateFrom, $dateTo);
                $account->total_debit  = $data['total_debit'];
                $account->total_credit = $data['total_credit'];
                $account->balance      = $data['balance'];
                $account->children_data = collect();
            } else {
                $account->children_data = $this->buildTrialBalanceTree(
                    $account->allChildren, $dateFrom, $dateTo
                );
                $account->total_debit  = $this->sumTree($account->children_data, 'total_debit');
                $account->total_credit = $this->sumTree($account->children_data, 'total_credit');
                $account->balance      = $account->total_debit - $account->total_credit;
            }
            return $account;
        });
    }

    private function sumTree($accounts, string $field): float
    {
        return $accounts->sum(fn($a) => $a->$field ?? 0);
    }

    private function filterNonZero($accounts): \Illuminate\Support\Collection
    {
        return $accounts->filter(function ($account) {
            if ($account->is_leaf) {
                return $account->total_debit > 0 || $account->total_credit > 0;
            }
            $account->children_data = $this->filterNonZero($account->children_data);
            return $account->children_data->isNotEmpty();
        })->values();
    }

    public function incomeStatement(Request $request)
    {
        // معالجة التواريخ
        $dateFromRaw = $request->input('date_from', '');
        $dateToRaw   = $request->input('date_to', '');
       
        $dateFrom = null;
        $dateTo   = null;

        if (!empty($dateFromRaw)) {
            try {
                $dateFrom = Carbon::parse($dateFromRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $dateFrom = null;
            }
        }
        if (!empty($dateToRaw)) {
            try {
                $dateTo = Carbon::parse($dateToRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $dateTo = null;
            }
        }

        // تعيين القيم الافتراضية إذا لم يتم تحديد أي تاريخ
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->format('Y-m-d');
        }

        // إيرادات المبيعات
        $salesRevenues = $this->getLeafAccountsByPrefix('4.1', $dateFrom, $dateTo);
        // إيرادات أخرى
        $otherRevenues = $this->getLeafAccountsByPrefix('4.2', $dateFrom, $dateTo);
        // تكلفة النشاط
        $costOfSales = $this->getLeafAccountsByPrefix('5.3', $dateFrom, $dateTo);
        // مصروفات تشغيل
        $opExpenses = $this->getLeafAccountsByPrefix('5.2', $dateFrom, $dateTo);

        // مصروفات عمومية
        $adminExpenses = $this->getLeafAccountsByPrefix('5.1', $dateFrom, $dateTo);
        // الضرائب
        $taxAccounts = $this->getLeafAccountsByPrefix('2.1.1.2', $dateFrom, $dateTo);
 
        // ── الحسابات ──
        $totalSalesRevenue  = $salesRevenues->sum('balance');
        $totalOtherRevenue  = $otherRevenues->sum('balance');
        $totalRevenue       = $totalSalesRevenue + $totalOtherRevenue;
 
        $totalCostOfSales   = $costOfSales->sum('balance') + $opExpenses->sum('balance');
        $grossProfit        = $totalRevenue - $totalCostOfSales;
 
        $totalAdminExpenses = $adminExpenses->sum('balance');
        $totalOpExpenses    = $opExpenses->sum('balance');
        $totalExpenses      = $totalAdminExpenses;
 
        $netProfitBeforeTax = $grossProfit - $totalExpenses;
        $totalTax           = $taxAccounts->sum('balance');
        $netProfitAfterTax  = $netProfitBeforeTax - $totalTax;
 

       if ($request->query('export') === 'excel') {
            return $this->exportIncomeStatementToExcel(
                $salesRevenues, $otherRevenues, $costOfSales,
                $adminExpenses, $opExpenses, $taxAccounts,
                $totalSalesRevenue, $totalOtherRevenue, $totalRevenue,
                $totalCostOfSales, $grossProfit,
                $totalAdminExpenses, $totalOpExpenses, $totalExpenses,
                $netProfitBeforeTax, $totalTax, $netProfitAfterTax,
                $dateFrom, $dateTo, 'قائمة_الدخل'
            );
        }

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('financial-reports.income-statement-pdf', compact(
                'salesRevenues', 'otherRevenues',
                'costOfSales', 'adminExpenses', 'opExpenses',
                'taxAccounts',
                'totalSalesRevenue', 'totalOtherRevenue', 'totalRevenue',
                'totalCostOfSales', 'grossProfit',
                'totalAdminExpenses', 'totalOpExpenses', 'totalExpenses',
                'netProfitBeforeTax', 'totalTax', 'netProfitAfterTax',
                'dateFrom', 'dateTo'
            ), [], [
                'format'           => 'A4',
                'default_font'     => 'cairo',
                'mode'             => 'utf-8',
                'autoLangToFont'   => true,
            ]);
            return $pdf->download('قائمة_الدخل_' . $dateTo . '.pdf');
        }

        return view('financial-reports.income-statement', compact(
            'salesRevenues',    'otherRevenues',
            'costOfSales',      'adminExpenses',      'opExpenses',
            'taxAccounts',
            'totalSalesRevenue','totalOtherRevenue',  'totalRevenue',
            'totalCostOfSales', 'grossProfit',
            'totalAdminExpenses','totalOpExpenses',   'totalExpenses',
            'netProfitBeforeTax','totalTax',          'netProfitAfterTax',
            'dateFrom', 'dateTo'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $dateFromRaw = $request->input('date_from', '');
        $dateToRaw   = $request->input('date_to', '');

        $dateFrom = null;
        $dateTo   = null;

        if (!empty($dateFromRaw)) {
            try {
                $dateFrom = Carbon::parse($dateFromRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $dateFrom = null;
            }
        }
        if (!empty($dateToRaw)) {
            try {
                $dateTo = Carbon::parse($dateToRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $dateTo = null;
            }
        }

        // تعيين القيم الافتراضية
        if (!$dateFrom) {
            $dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
        }
        if (!$dateTo) {
            $dateTo = Carbon::now()->format('Y-m-d');
        }

        // ── الأصول الثابتة (1.2.x) ──
        $fixedAssets  = $this->getLeafAccountsByPrefix('1.2.1', $dateFrom, $dateTo);
        $fixedAssets2 = $this->getLeafAccountsByPrefix('1.2.2', $dateFrom, $dateTo);
        $fixedAssets  = $fixedAssets->merge($fixedAssets2);
        $depreciation = $this->getLeafAccountsByPrefix('1.2.3', $dateFrom, $dateTo);
        $netFixed     = $fixedAssets->sum('balance') - $depreciation->sum('balance');

        // ── الأصول المتداولة ──
        $cashAccounts      = $this->getLeafAccountsByPrefix('1.1.1', $dateFrom, $dateTo);  // الصندوق
        $bankAccounts      = $this->getLeafAccountsByPrefix('1.1.2', $dateFrom, $dateTo);  // البنوك
        $notesReceivable   = collect();                                                 // أوراق قبض (مش موجودة في شجرتك)
        $clientsAccounts   = $this->getLeafAccountsByPrefix('1.1.3.1', $dateFrom, $dateTo); // العملاء
        $otherDebtors      = $this->getLeafAccountsByPrefix('1.1.3.2', $dateFrom, $dateTo); // المخزون
        $otherDebitBalance = collect();

        $totalCurrentAssets = $cashAccounts->sum('balance')
                            + $bankAccounts->sum('balance')
                            + $clientsAccounts->sum('balance')
                            + $otherDebtors->sum('balance');
        $totalAssets = $netFixed + $totalCurrentAssets;

        $totalCash         = $cashAccounts->sum('balance') + $bankAccounts->sum('balance');
        $totalClients      = $clientsAccounts->sum('balance');
        $totalOtherDebtors = $otherDebtors->sum('balance');

        $totalAssets = $netFixed + $totalCurrentAssets;
        
        // ── الالتزامات قصيرة الأجل ──
        $suppliers       = $this->getLeafAccountsByPrefix('2.1.1.1', $dateFrom, $dateTo);
        $notePayable     = collect();
        $socialInsurance = collect();
        $taxWithholding  = $this->getLeafAccountsByPrefix('2.1.1.2', $dateFrom, $dateTo);
        $otherCreditors  = $this->getLeafAccountsByPrefix('2.1.2',   $dateFrom, $dateTo)
                               ->merge($this->getLeafAccountsByPrefix('2.1.3', $dateFrom, $dateTo));

        $totalCurrentLiab = $suppliers->sum('balance')
                          + $taxWithholding->sum('balance')
                          + $otherCreditors->sum('balance');

        $totalSuppliers   = $suppliers->sum('balance');
        $totalNotePayable = 0;
        $totalSocialIns   = 0;
        $totalTaxWith     = $taxWithholding->sum('balance');
        $totalOtherCred   = $otherCreditors->sum('balance');

        // ── التزامات طويلة الأجل ──
        $longTermLoans     = $this->getLeafAccountsByPrefix('2.2', $dateFrom, $dateTo);
        $totalLongTermLiab = $longTermLoans->sum('balance');
        $totalLiabilities  = $totalCurrentLiab + $totalLongTermLiab;

        // ── رأس المال العامل ──
        $workingCapital = $totalCurrentAssets - $totalCurrentLiab;

        // ── صافي الربح ──
        $salesRev  = $this->getLeafAccountsByPrefix('4.1', $dateFrom, $dateTo)->sum('balance');
        $otherRev  = $this->getLeafAccountsByPrefix('4.2', $dateFrom, $dateTo)->sum('balance');
        $costRev   = $this->getLeafAccountsByPrefix('5.3', $dateFrom, $dateTo)->sum('balance');
        $adminExp  = $this->getLeafAccountsByPrefix('5.1', $dateFrom, $dateTo)->sum('balance');
        $opExp     = $this->getLeafAccountsByPrefix('5.2', $dateFrom, $dateTo)->sum('balance');
        $taxExp    = $this->getLeafAccountsByPrefix('2.1.1.2', $dateFrom, $dateTo)->sum('balance');
        $netProfit = ($salesRev + $otherRev) - $costRev - $adminExp - $opExp - $taxExp;

        // ── حقوق الملكية (3.x) ──
        $capitalAccounts = $this->getLeafAccountsByPrefix('3', $dateFrom, $dateTo);
        $totalCapital    = $capitalAccounts->sum('balance');
        $totalEquity     = $totalCapital + $netProfit;
        $totalFunding    = $totalLiabilities + $totalEquity;
        $balanceDiff     = $totalAssets - $totalFunding;

        if ($request->query('export') === 'excel') {
            return $this->exportBalanceSheetToExcel(
                $fixedAssets, $depreciation, $netFixed,
                $cashAccounts, $bankAccounts, $notesReceivable,
                $clientsAccounts, $otherDebtors, $otherDebitBalance,
                $totalCash, $totalClients, $totalOtherDebtors, $totalCurrentAssets,
                $totalAssets,
                $suppliers, $notePayable, $socialInsurance, $taxWithholding, $otherCreditors,
                $totalSuppliers, $totalNotePayable, $totalSocialIns, $totalTaxWith, $totalOtherCred,
                $totalCurrentLiab,
                $longTermLoans, $totalLongTermLiab,
                $totalLiabilities,
                $capitalAccounts, $totalCapital, $netProfit, $totalEquity,
                $workingCapital, $totalFunding, $balanceDiff,
                $dateFrom,$dateTo, 'الميزانية_العمومية'
            );
        }

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('financial-reports.balance-sheet-pdf', compact(
                'fixedAssets', 'depreciation', 'netFixed',
                'cashAccounts', 'bankAccounts', 'notesReceivable',
                'clientsAccounts', 'otherDebtors', 'otherDebitBalance',
                'totalCash', 'totalClients', 'totalOtherDebtors', 'totalCurrentAssets',
                'totalAssets',
                'suppliers', 'notePayable', 'socialInsurance', 'taxWithholding', 'otherCreditors',
                'totalSuppliers', 'totalNotePayable', 'totalSocialIns', 'totalTaxWith', 'totalOtherCred',
                'totalCurrentLiab',
                'longTermLoans', 'totalLongTermLiab',
                'totalLiabilities',
                'capitalAccounts', 'totalCapital', 'netProfit', 'totalEquity',
                'workingCapital', 'totalFunding', 'balanceDiff',
                'dateFrom','dateTo'
            ), [], [
                'format'           => 'A4-L',
                'default_font'     => 'cairo',
                'mode'             => 'utf-8',
                'autoLangToFont'   => true,
            ]);
            return $pdf->download('الميزانية_العمومية_' . $dateTo . '.pdf');
        }


        return view('financial-reports.balance-sheet', compact(
            // أصول ثابتة
            'fixedAssets', 'depreciation', 'netFixed',
            // أصول متداولة
            'cashAccounts', 'bankAccounts', 'notesReceivable',
            'clientsAccounts', 'otherDebtors', 'otherDebitBalance',
            'totalCash', 'totalClients', 'totalOtherDebtors', 'totalCurrentAssets',
            'totalAssets',
            // التزامات قصيرة
            'suppliers', 'notePayable', 'socialInsurance', 'taxWithholding', 'otherCreditors',
            'totalSuppliers', 'totalNotePayable', 'totalSocialIns', 'totalTaxWith', 'totalOtherCred',
            'totalCurrentLiab',
            // التزامات طويلة
            'longTermLoans', 'totalLongTermLiab',
            'totalLiabilities',
            // حقوق الملكية
            'capitalAccounts', 'totalCapital', 'netProfit', 'totalEquity',
            // إجماليات
            'workingCapital', 'totalFunding', 'balanceDiff',
            'dateTo', 'dateFrom'
        ));
    }

   // =============================
    // Helpers
    // =============================
 
    private function getLeafAccountsByPrefix(string $prefix, ?string $dateFrom, ?string $dateTo)
{
    $accounts = Account::where('is_leaf', true)
        ->where('is_active', true)
        ->where('code', 'like', $prefix . '%')
        ->orderBy('code')
        ->get();
    
    \Log::info('Fetching accounts with prefix', ['prefix' => $prefix, 'count' => $accounts->count()]);
    
    $result = collect();
    foreach ($accounts as $account) {
        $data = $this->calcBalance($account, $dateFrom, $dateTo);
        if (abs($data['balance']) > 0.001) {
            // إضافة البيانات ككائن Account مع الخصائص المطلوبة
            $account->total_debit = $data['total_debit'];
            $account->total_credit = $data['total_credit'];
            $account->balance = $data['balance'];
            $result->push($account);
        }
    }
    
    return $result;
}

private function calcBalance(Account $account, ?string $dateFrom, ?string $dateTo): array
{
    $query = JournalEntryLine::where('journal_entry_lines.account_id', $account->id)
        ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
        ->where('journal_entries.status', 'posted');
    
    if ($dateFrom && $dateTo) {
        $query->whereDate('journal_entries.entry_date', '>=', $dateFrom)
              ->whereDate('journal_entries.entry_date', '<=', $dateTo);
    } elseif ($dateFrom) {
        $query->whereDate('journal_entries.entry_date', '>=', $dateFrom);
    } elseif ($dateTo) {
        $query->whereDate('journal_entries.entry_date', '<=', $dateTo);
    }
    
    $result = $query->selectRaw('SUM(journal_entry_lines.debit) as total_debit, SUM(journal_entry_lines.credit) as total_credit')->first();
    
    $total_debit = (float) ($result->total_debit ?? 0);
    $total_credit = (float) ($result->total_credit ?? 0);
    
    // حساب الرصيد حسب طبيعة الحساب
    if (str_starts_with($account->code, '1') || 
        str_starts_with($account->code, '3') || 
        str_starts_with($account->code, '5')) {
        $balance = $total_debit - $total_credit;
    } else {
        $balance = $total_credit - $total_debit;
    }
    
    \Log::info('CalcBalance Result', [
        'account' => $account->code,
        'total_debit' => $total_debit,
        'total_credit' => $total_credit,
        'balance' => $balance,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
    ]);
    
    return [
        'total_debit' => $total_debit,
        'total_credit' => $total_credit,
        'balance' => $balance,
    ];
}


     private function getAccountsByCodePrefix(string $prefix, ?string $dateFrom, ?string $dateTo)
    {
        return Account::where('is_leaf', true)
            ->where('is_active', true)
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code')
            ->get()
            ->map(fn($a) => $this->calcBalance($a, $dateFrom, $dateTo))
            ->filter(fn($a) => $a->balance != 0);
    }

   private function exportToExcel($data, string $fileName, $dateFrom = null, $dateTo = null)
{
    $headers = [
        'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        'Content-Disposition' => "attachment; filename={$fileName}.xls",
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    $totalDebit  = $data->sum('total_debit');
    $totalCredit = $data->sum('total_credit');
    $title = 'ميزان المراجعة';
    
    // بناء عنوان التاريخ
    $dateRange = '';
    if ($dateFrom && $dateTo) {
        $dateRange = "من {$dateFrom} إلى {$dateTo}";
    } elseif ($dateFrom) {
        $dateRange = "من {$dateFrom}";
    } elseif ($dateTo) {
        $dateRange = "حتى {$dateTo}";
    }

    $html = '<html dir="rtl"><meta charset="UTF-8"><body style="font-family: \'Cairo\', sans-serif;">';
    
    // عنوان التقرير
    $html .= '<div style="text-align: center; margin-bottom: 20px;">';
    $html .= '<h2>' . $title . '</h2>';
    if ($dateRange) {
        $html .= '<h4 style="color: #555;">' . $dateRange . '</h4>';
    }
    $html .= '</div>';
    
    // بداية الجدول - نفس تنسيق generateLedgerExcelHtml
    $html .= '<table border="1" cellpadding="5" style="border-collapse:collapse; width:100%; direction: rtl;">';
    
    // رأس الجدول (أعمدة واضحة)
    $html .= '<thead>';
    $html .= '<tr style="background:#333;color:#fff;">';
    $html .= '<th style="text-align: center;">الرصيد</th>';
    $html .= '<th style="text-align: center;">دائن</th>';
    $html .= '<th style="text-align: center;">مدين</th>';
    $html .= '<th style="text-align: center;">اسم الحساب</th>';
    $html .= '<th style="text-align: center;">كود الحساب</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    
    $html .= '<tbody>';
    
    $html .= $this->renderTreeRows($data, 0);
    
    // صف الإجمالي
    $status = ($totalDebit == $totalCredit) ? 'متوازن' : 'غير متوازن';
    $html .= '<tr style="background:#f0f0f0; font-weight:bold;">';
    $html .= '<td style="text-align: right;">' . $status . '</td>';
    $html .= '<td style="text-align: right;">' . number_format($totalCredit, 2) . '</td>';
    $html .= '<td style="text-align: right;">' . number_format($totalDebit, 2) . '</td>';
    $html .= '<td colspan="2" style="text-align: right;"><strong>الإجمالي</strong></td>';
    $html .= '</tr>';
    
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</body></html>';
    
    return response($html, 200, $headers);
}

private function renderTreeRows($accounts, int $level): string
{
    $html = '';
    $padding = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    
    foreach ($accounts as $account) {
        $bgColor = $level === 0 ? '#e8e8e8' : ($level === 1 ? '#f5f5f5' : '#ffffff');
        $fontWeight = $level <= 1 ? 'bold' : 'normal';
        
        $balanceDir = $account->balance >= 0 ? 'مدين' : 'دائن';
        
        $html .= "<tr style='background:{$bgColor}; font-weight:{$fontWeight};'>";
        $html .= "<td style='text-align:right;'>" . number_format(abs($account->balance), 2) . " {$balanceDir}</td>";
        $html .= "<td style='text-align:right;'>" . number_format($account->total_credit, 2) . "</td>";
        $html .= "<td style='text-align:right;'>" . number_format($account->total_debit, 2) . "</td>";
        $html .= "<td style='text-align:right;'>{$padding}{$account->name}</td>";
        $html .= "<td>{$account->code}</td>";
        $html .= "</tr>";
        
        if (!$account->is_leaf && $account->children_data->isNotEmpty()) {
            $html .= $this->renderTreeRows($account->children_data, $level + 1);
        }
    }
    return $html;
}


/**
 * تصدير قائمة الدخل إلى Excel بنفس تنسيق الـ View
 */
private function exportIncomeStatementToExcel(
    $salesRevenues, $otherRevenues, $costOfSales,
    $adminExpenses, $opExpenses, $taxAccounts,
    $totalSalesRevenue, $totalOtherRevenue, $totalRevenue,
    $totalCostOfSales, $grossProfit,
    $totalAdminExpenses, $totalOpExpenses, $totalExpenses,
    $netProfitBeforeTax, $totalTax, $netProfitAfterTax,
    $dateFrom, $dateTo, string $fileName
) {
    $headers = [
        'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        'Content-Disposition' => "attachment; filename={$fileName}.xls",
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    // تنسيق التاريخ للعرض
    $from = \Carbon\Carbon::parse($dateFrom)->format('d/m/Y');
    $to   = \Carbon\Carbon::parse($dateTo)->format('d/m/Y');

    $html = '<html dir="rtl"><meta charset="UTF-8"><body style="font-family: \'Cairo\', sans-serif;">';
    
    // عنوان التقرير
    $html .= '<div style="text-align: center; margin-bottom: 20px;">';
    $html .= '<h2>قائمة الدخل</h2>';
    $html .= '<h4 style="color: #555;">عن الفترة من ' . $from . ' إلى ' . $to . '</h4>';
    $html .= '</div>';
    
    // بداية الجدول (بنفس أعمدة الـ View)
    $html .= '<table border="1" cellpadding="5" style="border-collapse:collapse; width:100%; direction: rtl;">';
    $html .= '<thead>';
    $html .= '<tr style="background:#333;color:#fff;">';
    $html .= '<th style="text-align: center; width:23%;">كلي</th>';
    $html .= '<th style="text-align: center; width:22%;">جزئي</th>';
    $html .= '<th style="text-align: center; width:55%;">البيان</th>';
    $html .= '</tr>';
    $html .= '</thead><tbody>';

    // ========== إيرادات المبيعات ==========
    $html .= '<tr ><td colspan="3" class="fw-bold text-success"><strong>أولاً: إيرادات المبيعات</strong></td></tr>';
    foreach ($salesRevenues as $a) {
        $html .= '<tr>';
        $html .= '<td></td>';
        $html .= '<td style="text-align: right;">' . number_format($a->balance, 2) . '</td>';
        $html .= '<td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>';
        $html .= '</tr>';
    }
    $html .= '<tr">
    <td style="text-align: right;"><strong>' . number_format($totalSalesRevenue, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي إيرادات المبيعات</strong></td>
    </tr>';

    // إيرادات أخرى
    if ($otherRevenues->isNotEmpty()) {
        $html .= '<tr><td colspan="3"><strong>إيرادات أخرى</strong></td></tr>';
        foreach ($otherRevenues as $a) {
            $html .= '
            <tr>
            <td></td>
            <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
            </tr>';
        }
    }
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($totalRevenue, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي الإيرادات</strong></td>
    </tr>';

    // ========== مصاريف تكلفة النشاط ==========
    $html .= '<tr><td colspan="3"><strong>ثانياً: (-) مصاريف تكلفة النشاط</strong></td></tr>';
    foreach ($costOfSales as $a) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
        </tr>';
    }

     if ($opExpenses->isNotEmpty()) {
        $html .= '<tr><td></td><td></td>
            <td style="text-align:right;"><strong>&nbsp;&nbsp;&nbsp;مصروفات التشغيل</strong></td></tr>';
        foreach ($opExpenses as $a) {
            $html .= '<tr><td></td>
                <td style="text-align:right;">' . number_format($a->balance, 2) . '</td>
                <td style="text-align:right;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td></tr>';
        }
    }
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($totalCostOfSales, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي تكلفة الحصول على الإيراد</strong></td>
    </tr>';

    // مجمل الربح
    $grossClass = ($grossProfit >= 0) ? '#17a2b8' : '#dc3545';
    $html .= '<tr>
    <td style="text-align: right; color:' . $grossClass . ';"><strong>' . number_format($grossProfit, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>مجمل الربح</strong></td>
    </tr>';

    // ========== مصروفات عمومية وإدارية ==========
    $html .= '<tr><td colspan="3"><strong>ثالثاً: (-) المصروفات العمومية والإدارية</strong></td></tr>';
    foreach ($adminExpenses as $a) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
        </tr>';
    }
    
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($totalExpenses, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي المصروفات العمومية والإدارية</strong></td>
    </tr>';

    // صافي الربح قبل الضرائب
    $beforeTaxClass = ($netProfitBeforeTax >= 0) ? '#ffc107' : '#dc3545';
    $html .= '<tr>
    <td style="text-align: right; color:' . $beforeTaxClass . ';"><strong>' . number_format($netProfitBeforeTax, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>صافي الربح المحاسبي قبل م.ضرائب عامة</strong></td>
    </tr>';

    // الضرائب
   $html .= '<tr><td colspan="3" style="text-align: right;"><strong>(-) مخصص الضرائب</strong></td></tr>';
    foreach ($taxAccounts as $a) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
        </tr>';
    }
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($totalTax, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي مصروف الضرائب + المخصص</strong></td>
    </tr>';

    // صافي الربح بعد الضرائب
    $afterTaxClass = ($netProfitAfterTax >= 0) ? '#28a745' : '#dc3545';
    $html .= '<tr>
    <td style="text-align: right; color:' . $afterTaxClass . '; font-size:1.1rem;"><strong>' . number_format($netProfitAfterTax, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>صافي الربح بعد الضرائب</strong></td>
    </tr>';

    $html .= '</tbody></table></body></html>';
    return response($html, 200, $headers);
}

/**
 * تصدير الميزانية العمومية إلى Excel بنفس تنسيق الـ View
 */
private function exportBalanceSheetToExcel(
    $fixedAssets, $depreciation, $netFixed,
    $cashAccounts, $bankAccounts, $notesReceivable,
    $clientsAccounts, $otherDebtors, $otherDebitBalance,
    $totalCash, $totalClients, $totalOtherDebtors, $totalCurrentAssets,
    $totalAssets,
    $suppliers, $notePayable, $socialInsurance, $taxWithholding, $otherCreditors,
    $totalSuppliers, $totalNotePayable, $totalSocialIns, $totalTaxWith, $totalOtherCred,
    $totalCurrentLiab,
    $longTermLoans, $totalLongTermLiab,
    $totalLiabilities,
    $capitalAccounts, $totalCapital, $netProfit, $totalEquity,
    $workingCapital, $totalFunding, $balanceDiff,
    $dateFrom,$dateTo, string $fileName
) {
    $headers = [
        'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        'Content-Disposition' => "attachment; filename={$fileName}.xls",
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    $from = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '';
    $to   = \Carbon\Carbon::parse($dateTo)->format('d/m/Y');
    $period = $from ? "من {$from} إلى {$to}" : "في {$to}";

    $html = '<html dir="rtl"><meta charset="UTF-8"><body style="font-family: \'Cairo\', sans-serif;">';
    $html .= '<div style="text-align: center; margin-bottom: 20px;">';
    $html .= '<h2>الميزانية العمومية</h2>';
    $html .= '<h4 style="color: #555;">في ' . $period . '</h4>';
    $html .= '</div>';

    $html .= '<table border="1" cellpadding="5" style="border-collapse:collapse; width:100%; direction: rtl;">';
    $html .= '<thead><tr>
    <th style="width:24%; text-align: center;">كلي</th>
    <th style="width:24%; text-align: center;">جزئي</th>
    <th style="width:52%; text-align: center;">البيان</th>
    </tr></thead><tbody>';

    // الأصول الثابتة
    $html .= '<tr><td colspan="3"><strong>الأصول الثابتة</strong></td></tr>';
    foreach ($fixedAssets as $a) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
        </tr>';
    }
    if ($depreciation->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>(-) مجمع إهلاك الأصول الثابتة</strong></td>
        </tr>';
        foreach ($depreciation as $a) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">(' . number_format($a->balance, 2) . ')</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
            </tr>';
        }
    }
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($netFixed, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>صافي الأصول الثابتة</strong></td>
    </tr>';

    // الأصول المتداولة
    $html .= '<tr><td colspan="3"><strong>الأصول المتداولة</strong></td></tr>';
    if ($cashAccounts->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>رصيد الخزينة</strong></td>
        </tr>';
        foreach ($cashAccounts as $a) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
            </tr>';
        }
    }
    if ($bankAccounts->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>أرصدة البنوك</strong></td>
        </tr>';
        foreach ($bankAccounts as $a) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
            </tr>';
        }
    }
    foreach ($notesReceivable as $a) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
        </tr>';
    }
    if ($clientsAccounts->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>أرصدة العملاء</strong></td>
        </tr>';
        foreach ($clientsAccounts as $a) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
            </tr>';
        }
    }
    if ($otherDebtors->isNotEmpty() || $otherDebitBalance->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>أرصدة مدينة أخرى</strong></td>
        </tr>';
        foreach ($otherDebtors as $a) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
            </tr>';
        }
        foreach ($otherDebitBalance as $a) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($a->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $a->code . '</code> ' . $a->name . '</td>
            </tr>';
        }
    }
    $html .= '<tr>
    <td></td><td style="text-align: right;"><strong>' . number_format($totalCurrentAssets, 2) . '</strong></td>
    <td style="text-align: right;"><strong>إجمالي الأصول المتداولة</strong></td>
    </tr>';
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($totalAssets, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي الأصول</strong></td>
    </tr>';

    // الالتزامات قصيرة الأجل
    $html .= '<tr><td colspan="3"><strong>الالتزامات قصيرة الأجل</strong></td></tr>';
    if ($suppliers->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>أرصدة الموردين</strong></td>
        </tr>';
        foreach ($suppliers as $l) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($l->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $l->code . '</code> ' . $l->name . '</td>
            </tr>';
        }
    }
    foreach ($notePayable as $l) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($l->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $l->code . '</code> ' . $l->name . '</td>
        </tr>';
    }
    foreach ($socialInsurance as $l) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($l->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $l->code . '</code> ' . $l->name . '</td>
        </tr>';
    }
    if ($taxWithholding->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>مخصص ضرائب عامة وزكاه</strong></td>
        </tr>';
        foreach ($taxWithholding as $l) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($l->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $l->code . '</code> ' . $l->name . '</td>
            </tr>';
        }
    }
    if ($otherCreditors->isNotEmpty()) {
        $html .= '<tr>
        <td></td>
        <td></td>
        <td style="text-align: right;"><strong>أرصدة دائنة أخرى</strong></td>
        </tr>';
        foreach ($otherCreditors as $l) {
            $html .= '<tr>
            <td></td> 
            <td style="text-align: right;">' . number_format($l->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $l->code . '</code> ' . $l->name . '</td>
            </tr>';
        }
    }
    $html .= '<tr>
    <td></td><td style="text-align: right;"><strong>' . number_format($totalCurrentLiab, 2) . '</strong></td>
    <td style="text-align: right;"><strong>إجمالي الالتزامات قصيرة الأجل</strong></td>
    </tr>';

    // التزامات طويلة الأجل
    if ($longTermLoans->isNotEmpty()) {
        $html .= '<tr><td colspan="3"><strong>الالتزامات طويلة الأجل</strong></td></tr>';
        foreach ($longTermLoans as $l) {
            $html .= '<tr>
            <td></td>
            <td style="text-align: right;">' . number_format($l->balance, 2) . '</td>
            <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $l->code . '</code> ' . $l->name . '</td>
            </tr>';
        }
        $html .= '<tr>
        <td style="text-align: right;"><strong>' . number_format($totalLongTermLiab, 2) . '</strong></td>
        <td></td>
        <td style="text-align: right;"><strong>إجمالي الالتزامات طويلة الأجل</strong></td></tr>';
    }

    // رأس المال العامل
    $html .= '<tr>
    <td style="text-align: right; color:' . ($workingCapital >= 0 ? '#ffc107' : '#dc3545') . ';"><strong>' . number_format($workingCapital, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>رأس المال العامل</strong></td>
    </tr>';
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($netFixed + $totalCurrentAssets - $totalCurrentLiab, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي الاستثمارات ويتم تمويلها على النحو التالي</strong></td>
    </tr>';

    // حقوق الملكية
    $html .= '<tr><td colspan="3"><strong>حقوق الملكية</strong></td></tr>';
    foreach ($capitalAccounts as $e) {
        $html .= '<tr>
        <td></td>
        <td style="text-align: right;">' . number_format($e->balance, 2) . '</td>
        <td style="text-align: right;">&nbsp;&nbsp;&nbsp;<code>' . $e->code . '</code> ' . $e->name . '</td>
        </tr>';
    }
    $html .= '<tr>
    <td></td>
    <td style="text-align: right; color:' . ($netProfit >= 0 ? 'green' : 'red') . ';">' . number_format($netProfit, 2) . '</td>
    <td style="text-align: right;"><strong>صافي الربح</strong></td>
    </tr>';
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($totalEquity, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي حقوق الملكية</strong></td>
    </tr>';
    $html .= '<tr>
    <td style="text-align: right;"><strong>' . number_format($totalFunding, 2) . '</strong></td>
    <td></td>
    <td style="text-align: right;"><strong>إجمالي مصادر التمويل</strong></td>
    </tr>';

    // فرق الميزانية
    $diffClass = (abs($balanceDiff) < 0.01) ? '#28a745' : '#ffc107';
    $html .= '<tr>
    <td style="text-align: right; color:' . $diffClass . ';"><strong>' . number_format(abs($balanceDiff), 2) . '</strong></td>
    <td></td>
    <td style="text-align: right; color:' . $diffClass . ';"><strong>' . (abs($balanceDiff) < 0.01 ? 'الميزانية متوازنة ✅' : 'فرق الميزانية') . '</strong></td>
    </tr>';

    $html .= '</tbody></table></body></html>';
    return response($html, 200, $headers);
}

    
    /**
 * عرض صفحة المعاينة والطباعة
 */
public function printLedger(Account $account, Request $request)
{
    // نفس المنطق لجلب جميع البيانات (بدون pagination) كحالة التصدير
    $baseQuery = $this->buildLedgerQuery($account, $request);
    $allTransactions = $baseQuery->orderBy('created_at', 'asc')->get();

    // حساب الرصيد الافتتاحي
    $firstTransaction = $allTransactions->first();
    $openingBalance = 0;
    if ($firstTransaction) {
        $openingBalance = $account->ledger()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->where('created_at', '<', $firstTransaction->created_at)
            ->sum(DB::raw('debit - credit'));
    }

    // جلب البيانات الإضافية (حجوزات، إتاحات) – اختياري للمعاينة
    // يمكنك حذفها إذا لم تكن ضرورية في المعاينة
    $bookingIds = $allTransactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Booking::class
    )->pluck('journalEntry.source_id')->filter()->unique();

    $availabilityIds = $allTransactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Availability::class
    )->pluck('journalEntry.source_id')->filter()->unique();

    $bookings = \App\Models\Booking::with(['company', 'hotel'])->whereIn('id', $bookingIds)->get()->keyBy('id');
    $availabilities = \App\Models\Availability::with(['hotel', 'agent', 'availabilityRoomTypes.roomType'])->whereIn('id', $availabilityIds)->get()->keyBy('id');

    $data = [
        'account' => $account,
        'transactions' => $allTransactions,
        'openingBalance' => $openingBalance,
        'bookings' => $bookings,
        'availabilities' => $availabilities,
    ];

    return view('exports.ledger_print', $data);
}

/**
 * تحميل PDF حقيقي (عند الضغط على زر التحميل داخل صفحة المعاينة)
 */
public function downloadLedgerPdf(Account $account, Request $request)
{
    // نفس بناء البيانات (يمكنك استدعاء الدالة المشتركة)
    $baseQuery = $this->buildLedgerQuery($account, $request);
    $allTransactions = $baseQuery->orderBy('created_at', 'asc')->get();

    $firstTransaction = $allTransactions->first();
    $openingBalance = 0;
    if ($firstTransaction) {
        $openingBalance = $account->ledger()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->where('created_at', '<', $firstTransaction->created_at)
            ->sum(DB::raw('debit - credit'));
    }

    $bookingIds = $allTransactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Booking::class
    )->pluck('journalEntry.source_id')->filter()->unique();

    $availabilityIds = $allTransactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Availability::class
    )->pluck('journalEntry.source_id')->filter()->unique();

    $bookings = \App\Models\Booking::with(['company', 'hotel'])
        ->whereIn('id', $bookingIds)->get()->keyBy('id');

    $availabilities = \App\Models\Availability::with(['hotel', 'agent', 'availabilityRoomTypes.roomType'])
        ->whereIn('id', $availabilityIds)->get()->keyBy('id');

    $data = [
        'account' => $account,
        'transactions' => $allTransactions,
        'openingBalance' => $openingBalance,
        'bookings' => $bookings ?? collect(),
        'availabilities' => $availabilities ?? collect(),
        'isPdfExport' => true,
    ];


    $pdf = Pdf::loadView('exports.ledger_print', $data, [], [
        'format' => 'A4-L',
        'default_font_size' => 12,
        'default_font' => 'cairo', // أو 'dejavusans'
        'mode' => 'utf-8',
        'autoLangToFont' => true,
    ]);

    return $pdf->download("كشف_حساب_{$account->code}_{$account->name}.pdf");
}

/**
 * دالة مساعدة لبناء الاستعلام مع الفلاتر (لتجنب تكرار الكود)
 */
private function buildLedgerQuery(Account $account, Request $request)
{
    $query = $account->ledger()
        ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
        ->with(['journalEntry', 'journalEntry.creator', 'journalEntry.lines.account']);

    // تطبيق الفلاتر بنفس الكود الموجود في ledger الأصلي
    $searchBy = $request->search_by;
    $searchValue = $request->search_value;

    if ($searchBy && $searchValue) {
        switch ($searchBy) {
            case 'id':
                $query->whereHas('journalEntry', fn($q) => $q->where('id', $searchValue));
                break;
            case 'reference':
                $query->whereHas('journalEntry', fn($q) => $q->where('reference', 'like', "%{$searchValue}%"));
                break;
            case 'status':
                $statusValue = match($searchValue) {
                    'معتمد' => 'posted',
                    'غير معتمد' => 'draft',
                    default => $searchValue
                };
                $query->whereHas('journalEntry', fn($q) => $q->where('status', $statusValue));
                break;
            case 'created_by':
                $query->whereHas('journalEntry.creator', fn($q) => $q->where('name', 'like', "%{$searchValue}%"));
                break;
            case 'created_at':
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $searchValue)->format('Y-m-d');
                    $query->whereHas('journalEntry', fn($q) => $q->whereDate('created_at', $date));
                } catch (\Exception $e) {
                    $query->whereHas('journalEntry', fn($q) => $q->whereDate('created_at', $searchValue));
                }
                break;
        }
    }

    if ($request->filled('date_from')) {
        $query->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '>=', $request->date_from));
    }
    if ($request->filled('date_to')) {
        $query->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '<=', $request->date_to));
    }

    return $query;
}


}