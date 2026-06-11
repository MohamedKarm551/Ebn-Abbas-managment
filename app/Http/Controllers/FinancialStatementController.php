<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

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
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    $sheet->setTitle(mb_substr($title, 0, 31));
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

    // عرض الأعمدة
    foreach (['A' => 6, 'B' => 30, 'C' => 16, 'D' => 18, 'E' => 18, 'F' => 22] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    // ── الصف 1: العنوان ──
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A1', $title);
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(24);

    // ── الصف 2: الفترة / البحث / تاريخ الطباعة ──
    $periodText = '';
    if ($fromDate && $toDate) {
        $periodText = 'الفترة: من ' . \Carbon\Carbon::parse($fromDate)->format('d/m/Y') . ' إلى ' . \Carbon\Carbon::parse($toDate)->format('d/m/Y');
    } elseif ($fromDate) {
        $periodText = 'الفترة: من ' . \Carbon\Carbon::parse($fromDate)->format('d/m/Y');
    } elseif ($toDate) {
        $periodText = 'الفترة: حتى ' . \Carbon\Carbon::parse($toDate)->format('d/m/Y');
    }
    if ($searchTerm) {
        $periodText .= ($periodText ? '   |   ' : '') . 'بحث: ' . $searchTerm;
    }
    $periodText .= ($periodText ? '   |   ' : '') . 'تاريخ الطباعة: ' . now()->format('d/m/Y H:i');

    $sheet->mergeCells('A2:F2');
    $sheet->setCellValue('A2', $periodText);
    $sheet->getStyle('A2')->applyFromArray([
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
    ]);

    // ── الصف 3: رؤوس الأعمدة ──
    $headers = ['A3' => '#', 'B3' => 'اسم الحساب', 'C3' => 'كود الحساب', 'D3' => 'إجمالي مدين', 'E3' => 'إجمالي دائن', 'F3' => 'الرصيد النهائي'];
    foreach ($headers as $cell => $label) {
        $sheet->setCellValue($cell, $label);
    }
    $sheet->getStyle('A3:F3')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(3)->setRowHeight(18);

    // ── الصفوف ──
    $row = 4;
    $index = 1;
    foreach ($accounts as $account) {
        $sheet->setCellValue("A{$row}", $index);
        $sheet->setCellValue("B{$row}", $account->name);
        $sheet->setCellValue("C{$row}", $account->code);
        $sheet->setCellValue("D{$row}", $account->total_debit);
        $sheet->setCellValue("E{$row}", $account->total_credit);
        $sheet->setCellValue("F{$row}", abs($account->balance) . ' ' . $account->balance_type);

        $sheet->getStyle("A{$row}:F{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$row}:E{$row}")->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // تلوين متناوب
        if ($index % 2 === 0) {
            $sheet->getStyle("A{$row}:F{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F5F5F5');
        }

        $row++;
        $index++;
    }

    // ── صف الإجمالي ──
    $sheet->setCellValue("A{$row}", 'الإجمالي');
    $sheet->setCellValue("D{$row}", $totalDebit);
    $sheet->setCellValue("E{$row}", $totalCredit);
    $sheet->setCellValue("F{$row}", number_format(abs($totalBalance), 2) . ' ر.س');
    $sheet->mergeCells("A{$row}:C{$row}");
    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
        'font'      => ['bold' => true],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getStyle("D{$row}:E{$row}")->getNumberFormat()
        ->setFormatCode('#,##0.00');

    // ── حدود الجدول ──
    $sheet->getStyle("A1:F{$row}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);

    $filename = str_replace(' ', '_', $title) . '_' . now()->format('Ymd_His') . '.xlsx';
    $writer = new Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Cache-Control' => 'max-age=0',
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