<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class FinancialStatementController extends Controller
{
    /**
     * كشف حساب كامل لكل العملاء (جميع الحركات)
     */
    public function customersFullLedger(Request $request)
    {
        $parentAccount = Account::where('code', '1.1.3.1')->first();
        if (!$parentAccount) {
            abort(404, 'حساب العملاء غير موجود');
        }

        // جلب الحسابات مع الفلاتر
        $accountsQuery = Account::where('parent_id', $parentAccount->id)
            ->where('is_leaf', true)
            ->orderBy('name');

        $searchCustomer = $request->input('customer_name'); // ✅ تعريف المتغير
        if ($searchCustomer) {
            $accountsQuery->where('name', 'like', '%' . $searchCustomer . '%');
        }
        $accounts = $accountsQuery->get();

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // حساب المجاميع
        $totalDebitAll = 0;
        $totalCreditAll = 0;
        $totalBalanceAll = 0;

        foreach ($accounts as $account) {
            $ledgerQuery = $account->ledger()
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'));

            if ($fromDate && $toDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                    $q->whereDate('entry_date', '>=', $fromDate)
                      ->whereDate('entry_date', '<=', $toDate);
                });
            } elseif ($fromDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($fromDate) {
                    $q->whereDate('entry_date', '>=', $fromDate);
                });
            } elseif ($toDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($toDate) {
                    $q->whereDate('entry_date', '<=', $toDate);
                });
            }

            $totalDebit = $ledgerQuery->sum('debit');
            $totalCredit = $ledgerQuery->sum('credit');
            $balance = $totalDebit - $totalCredit;

            $account->total_debit = $totalDebit;
            $account->total_credit = $totalCredit;
            $account->balance = $balance;
            $account->balance_type = $balance >= 0 ? 'مدين' : 'دائن';
            $account->abs_balance = number_format(abs($balance), 2);

            $totalDebitAll += $totalDebit;
            $totalCreditAll += $totalCredit;
            $totalBalanceAll += $balance;
        }

        // التصدير
        if ($request->query('export') === 'excel') {
            return $this->exportFullLedgerToExcel(
                $accounts,
                $totalDebitAll,
                $totalCreditAll,
                $totalBalanceAll,
                'ملخص حسابات العملاء',
                $fromDate,
                $toDate,
                $request->customer_name, 
                $searchCustomer);
        }

        if ($request->query('export') === 'pdf') {
            return $this->exportFullLedgerToPdf(
                $accounts,
                $totalDebitAll,
                $totalCreditAll,
                $totalBalanceAll,
                'ملخص حسابات العملاء',
                $fromDate,
                $toDate,
                $request->customer_name,
                $searchCustomer
            );
        }

        return view('financial_statements.customers_full_ledger', compact(
            'accounts', 'totalDebitAll', 'totalCreditAll', 'totalBalanceAll',
            'fromDate', 'toDate', 'searchCustomer'
        ));
    }

    /**
     * كشف حساب كامل للموردين
     */
    public function suppliersFullLedger(Request $request)
    {
        $parentAccount = Account::where('code', '2.1.1.1')->first();
        if (!$parentAccount) {
            abort(404, 'حساب الموردين غير موجود');
        }

        $accountsQuery = Account::where('parent_id', $parentAccount->id)
            ->where('is_leaf', true)
            ->orderBy('name');

       $searchSupplier = $request->input('supplier_name'); // ✅ تعريف المتغير
        if ($searchSupplier) {
            $accountsQuery->where('name', 'like', '%' . $searchSupplier . '%');
        }
        $accounts = $accountsQuery->get();

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $totalDebitAll = 0;
        $totalCreditAll = 0;
        $totalBalanceAll = 0;

        foreach ($accounts as $account) {
            $ledgerQuery = $account->ledger()
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'));

            if ($fromDate && $toDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                    $q->whereDate('entry_date', '>=', $fromDate)
                      ->whereDate('entry_date', '<=', $toDate);
                });
            } elseif ($fromDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($fromDate) {
                    $q->whereDate('entry_date', '>=', $fromDate);
                });
            } elseif ($toDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($toDate) {
                    $q->whereDate('entry_date', '<=', $toDate);
                });
            }

            $totalDebit = $ledgerQuery->sum('debit');
            $totalCredit = $ledgerQuery->sum('credit');
            $balance = $totalDebit - $totalCredit;

            $account->total_debit = $totalDebit;
            $account->total_credit = $totalCredit;
            $account->balance = $balance;
            $account->balance_type = $balance >= 0 ? 'مدين' : 'دائن';
            $account->abs_balance = number_format(abs($balance), 2);

            $totalDebitAll += $totalDebit;
            $totalCreditAll += $totalCredit;
            $totalBalanceAll += $balance;
        }

        if ($request->query('export') === 'excel') {
            return $this->exportFullLedgerToExcel(
                $accounts,
                $totalDebitAll,
                $totalCreditAll,
                $totalBalanceAll,
                'ملخص حسابات الموردين',
                $fromDate,
                $toDate,
                $request->supplier_name
            );
        }

        if ($request->query('export') === 'pdf') {
            return $this->exportFullLedgerToPdf(
                $accounts,
                $totalDebitAll,
                $totalCreditAll,
                $totalBalanceAll,
                'ملخص حسابات الموردين',
                $fromDate,
                $toDate,
                $request->supplier_name
            );
        }

        return view('financial_statements.suppliers_full_ledger', compact(
            'accounts', 'totalDebitAll', 'totalCreditAll', 'totalBalanceAll',
            'fromDate', 'toDate', 'searchSupplier'
        ));
    }

    /**
     * كشف حساب كامل للمصروفات
     */
    public function expensesFullLedger(Request $request)
    {
        $parentAccount = Account::where('code', '5')->first();
        if (!$parentAccount) {
            abort(404, 'حساب المصروفات غير موجود');
        }

        $accountsQuery = Account::where('parent_id', $parentAccount->id)
            ->where('is_leaf', true)
            ->orderBy('code');

         $searchExpense = $request->input('expense_name'); // ✅ تعريف المتغير
        if ($searchExpense) {
            $accountsQuery->where('name', 'like', '%' . $searchExpense . '%');
        }
        $accounts = $accountsQuery->get();
    
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $totalDebitAll = 0;
        $totalCreditAll = 0;
        $totalBalanceAll = 0;

        foreach ($accounts as $account) {
            $ledgerQuery = $account->ledger()
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'));

            if ($fromDate && $toDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                    $q->whereDate('entry_date', '>=', $fromDate)
                      ->whereDate('entry_date', '<=', $toDate);
                });
            } elseif ($fromDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($fromDate) {
                    $q->whereDate('entry_date', '>=', $fromDate);
                });
            } elseif ($toDate) {
                $ledgerQuery->whereHas('journalEntry', function($q) use ($toDate) {
                    $q->whereDate('entry_date', '<=', $toDate);
                });
            }

            $totalDebit = $ledgerQuery->sum('debit');
            $totalCredit = $ledgerQuery->sum('credit');
            $balance = $totalDebit - $totalCredit;

            $account->total_debit = $totalDebit;
            $account->total_credit = $totalCredit;
            $account->balance = $balance;
            $account->balance_type = $balance >= 0 ? 'مدين' : 'دائن';
            $account->abs_balance = number_format(abs($balance), 2);

            $totalDebitAll += $totalDebit;
            $totalCreditAll += $totalCredit;
            $totalBalanceAll += $balance;
        }

        if ($request->query('export') === 'excel') {
            return $this->exportFullLedgerToExcel(
                $accounts,
                $totalDebitAll,
                $totalCreditAll,
                $totalBalanceAll,
                'ملخص حسابات المصروفات',
                $fromDate,
                $toDate,
                $request->expense_name
            );
        }

        if ($request->query('export') === 'pdf') {
            return $this->exportFullLedgerToPdf(
                $accounts,
                $totalDebitAll,
                $totalCreditAll,
                $totalBalanceAll,
                'ملخص حسابات المصروفات',
                $fromDate,
                $toDate,
                $request->expense_name
            );
        }

        return view('financial_statements.expenses_full_ledger', compact(
            'accounts', 'totalDebitAll', 'totalCreditAll', 'totalBalanceAll',
            'fromDate', 'toDate', 'searchExpense'
        ));
    }

    // ======================= دوال مساعدة للتصدير =======================

    /**
     * تصدير بيانات كشف الحساب الكامل إلى ملف Excel (HTML)
     */
  private function exportFullLedgerToExcel($accounts, $totalDebit, $totalCredit, $totalBalance, $title, $fromDate, $toDate, $searchTerm)
{
    // حدد ترتيب الأعمدة الذي تريده (المفاتيح هي أسماء الأعمدة)
    $columnsOrder = [
        'الرصيد النهائي' => fn($account) => number_format(abs($account->balance), 2) . ' ' . $account->balance_type,
        'إجمالي دائن' => fn($account) => number_format($account->total_credit, 2),
        'إجمالي مدين' => fn($account) => number_format($account->total_debit, 2),
        'كود الحساب' => fn($account) => e($account->code),
        'اسم الحساب' => fn($account) => e($account->name),
        '#' => fn($account, $index) => $index,
    ];

    // إذا أردت أن يكون الرصيد هو العمود الأول، يمكنك إعادة ترتيب المصفوفة:
    // $columnsOrder = array_merge(['الرصيد النهائي' => $columnsOrder['الرصيد النهائي']], array_diff_key($columnsOrder, ['الرصيد النهائي' => null]));

    $html = '<html dir="rtl"><meta charset="UTF-8"><body>';
    $html .= '<h2 style="text-align:center;">' . $title . '</h2>';
    $html .= '<div style="margin-bottom:15px;">';
    if ($fromDate && $toDate) {
        $html .= '<p><strong>الفترة:</strong> من ' . \Carbon\Carbon::parse($fromDate)->format('d/m/Y') . ' إلى ' . \Carbon\Carbon::parse($toDate)->format('d/m/Y') . '</p>';
    } elseif ($fromDate) {
        $html .= '<p><strong>الفترة:</strong> من ' . \Carbon\Carbon::parse($fromDate)->format('d/m/Y') . '</p>';
    } elseif ($toDate) {
        $html .= '<p><strong>الفترة:</strong> حتى ' . \Carbon\Carbon::parse($toDate)->format('d/m/Y') . '</p>';
    }
    if ($searchTerm) {
        $html .= '<p><strong>بحث:</strong> ' . e($searchTerm) . '</p>';
    }
    $html .= '<p><strong>تاريخ الطباعة:</strong> ' . now()->format('d/m/Y H:i') . '</p>';
    $html .= '</div>';

    $html .= '<table border="1" cellpadding="5" style="border-collapse:collapse; width:100%;">';
    // رأس الجدول حسب الترتيب المحدد
    $html .= '<thead><tr style="background:#333;color:#fff;">';
    foreach (array_keys($columnsOrder) as $colName) {
        $html .= '<th>' . $colName . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    $index = 1;
    foreach ($accounts as $account) {
        $html .= '<tr>';
        foreach ($columnsOrder as $colKey => $callback) {
            $value = ($colKey === '#') ? $callback($account, $index) : $callback($account);
            $html .= '<td style="text-align:right;">' . $value . '</td>';
        }
        $html .= '</tr>';
        $index++;
    }

   $html .= '</tbody><tfoot><tr style="background:#f0f0f0; font-weight:bold;">';
    $html .= '<td style="text-align:left;">' . number_format(abs($totalBalance), 2) . ' ر.س</td>';
    $html .= '<td style="text-align:left;">' . number_format($totalCredit, 2) . '</td>';
    $html .= '<td style="text-align:left;">' . number_format($totalDebit, 2) . '</td>';
    $html .= '<td></td>';
    $html .= '<td></td>';
    $html .= '<td style="text-align:center;">الإجمالي</td>';
    $html .= '</tr></tfoot>';

    $filename = str_replace(' ', '_', $title) . '_' . now()->format('Ymd_His') . '.xls';
    return response($html, 200, [
        'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        'Content-Disposition' => "attachment; filename={$filename}",
    ]);
}

    /**
     * تصدير بيانات كشف الحساب الكامل إلى PDF
     */
    private function exportFullLedgerToPdf($accounts, $totalDebit, $totalCredit, $totalBalance, $title, $fromDate, $toDate, $searchTerm)
    {
        $data = [
            'title' => $title,
            'accounts' => $accounts,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'totalBalance' => $totalBalance,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'searchTerm' => $searchTerm,
            'generatedAt' => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('exports.full_ledger_pdf', $data, [], [
            'format' => 'A4-L',
            'default_font_size' => 12,
            'default_font' => 'cairo',
            'mode' => 'utf-8',
            'autoLangToFont' => true,
        ]);

        $filename = str_replace(' ', '_', $title) . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}