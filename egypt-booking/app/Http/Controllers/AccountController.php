<?php
namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class AccountController extends Controller
{
   public function index(Request $request)
    {
    $search = $request->input('search');

    if ($search) {
        $matchedAccounts = Account::where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%")
            ->get();

        $allRelevantIds = collect();
        foreach ($matchedAccounts as $account) {
            $allRelevantIds->push($account->id);
            // إضافة الآباء
            $parent = $account->parent;
            while ($parent) {
                $allRelevantIds->push($parent->id);
                $parent = $parent->parent;
            }
            // إضافة الأبناء (جميع الأحفاد)
            $this->addAllDescendantsIds($account->id, $allRelevantIds);
        }
        $allRelevantIds = $allRelevantIds->unique();

        $allAccounts = Account::with('children')
            ->whereIn('id', $allRelevantIds)
            ->get()
            ->keyBy('id');

        $roots = $allAccounts->filter(function ($account) use ($allAccounts) {
            return !$account->parent_id || !$allAccounts->has($account->parent_id);
        })->sortBy('code');

        foreach ($allAccounts as $account) {
            $children = $allAccounts->where('parent_id', $account->id)->sortBy('code');
            $account->setRelation('filteredChildren', $children);
        }

        $accounts = $roots;
        $isSearching = true;
    } else {
        $accounts = Account::with('allChildren.allChildren.allChildren.allChildren')
            ->roots()
            ->orderBy('code')
            ->get();
        $this->sortAccountsRecursively($accounts);
        $isSearching = false;
    }

    return view('accounts.index', compact('accounts', 'search', 'isSearching'));
    }

    // أضف الدالتين المساعدتين في نفس الكونترولر
    private function addAllDescendantsIds($accountId, &$ids)
    {
    $childrenIds = Account::where('parent_id', $accountId)->pluck('id');
    foreach ($childrenIds as $childId) {
        $ids->push($childId);
        $this->addAllDescendantsIds($childId, $ids);
    }
    }

    private function sortAccountsRecursively($accounts)
    {
        foreach ($accounts as $account) {
            if ($account->allChildren) {
                $account->allChildren = $account->allChildren->sortBy('code');
                $this->sortAccountsRecursively($account->allChildren);
            }
        }
    }

    // فورم إضافة حساب
    public function create()
    {
        // الآباء المحتملين = الحسابات غير الـ leaf
        $parents = Account::where('is_active', true)
            ->where('is_leaf', false)
            ->orderBy('code')
            ->get();
        return view('accounts.create', compact('parents'));
    }

    // حفظ حساب جديد
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id'    => 'nullable|exists:accounts,id',
            'account_kind' => 'required|in:parent,leaf',
            'description'  => 'nullable|string',
        ]);

        // تحقق من أن الأب غير leaf
        if ($request->parent_id) {
            $parent = Account::find($request->parent_id);
            if ($parent && $parent->is_leaf) {
                return back()->withInput()
                    ->withErrors(['parent_id' => 'لا يمكن إضافة حساب تحت حساب نهائي (leaf)']);
            }
        }

        $code = $this->generateCode($request->parent_id);

        Account::create([
            'code'           => $code,
            'name'           => $request->name,
            'type'           => $request->type,
            'normal_balance' => in_array($request->type, ['asset','expense'])
                                    ? 'debit' : 'credit',
            'parent_id'      => $request->parent_id,
            'level'          => $request->parent_id
                                    ? (Account::find($request->parent_id)->level + 1)
                                    : 1,
            'is_leaf'        => $request->account_kind === 'leaf',
            'is_active'      => true,
            'description'    => $request->description,
        ]);

        return redirect()->route('accounts.index')
            ->with('success', "✅ تم إنشاء الحساب [{$code}] {$request->name}");
    }

    // فورم تعديل حساب
    public function edit(Account $account)
    {
        $parents = Account::where('is_active', true)
            ->where('id', '!=', $account->id)
            ->where('is_leaf', false)
            ->orderBy('code')
            ->get();
        return view('accounts.edit', compact('account', 'parents'));
    }

    // حفظ التعديل
    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // منع تحويل حساب له أبناء إلى leaf
        if ($request->account_kind === 'leaf' && $account->children()->exists()) {
            return back()->withErrors([
                'account_kind' => 'لا يمكن تحويل حساب له فروع إلى حساب نهائي'
            ]);
        }

        $account->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_leaf'     => $request->account_kind === 'leaf',
        ]);

        return redirect()->route('accounts.index')
            ->with('success', '✅ تم تحديث الحساب');
    }

    // تجميد / تفعيل الحساب
    public function toggleFreeze(Account $account)
    {
        // منع تجميد حساب له قيود غير معتمدة
        if (!$account->is_active === false) {
            $hasDraftEntries = $account->journalLines()
                ->whereHas('journalEntry', fn($q) => $q->where('status','draft'))
                ->exists();
            if ($hasDraftEntries) {
                return back()->with('error',
                    '⚠️ لا يمكن تجميد حساب له قيود غير معتمدة');
            }
        }

        $account->update(['is_active' => !$account->is_active]);

        $msg = $account->is_active
            ? '✅ تم تفعيل الحساب'
            : '🔒 تم تجميد الحساب';

        return back()->with('success', $msg);
    }

    // توليد كود تلقائي
    private function generateCode(?int $parentId): string
    {
        if (!$parentId) {
            $max = Account::whereNull('parent_id')
                ->get()
                ->map(fn($a) => (int)$a->code)
                ->max() ?? 0;
            return (string)($max + 1);
        }

        $parent   = Account::findOrFail($parentId);
        $children = Account::where('parent_id', $parentId)
            ->withTrashed()->get();

        if ($children->isEmpty()) {
            return $parent->code . '.1';
        }

        $maxSuffix = $children->map(function ($child) use ($parent) {
            $suffix = str_replace($parent->code . '.', '', $child->code);
            return (int)(explode('.', $suffix)[0] ?? 0);
        })->max();

        return $parent->code . '.' . ($maxSuffix + 1);
    }


public function ledger(Account $account, Request $request)
{
    $baseQuery = $account->ledger()
        ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
        ->with(['journalEntry', 'journalEntry.creator', 'journalEntry.lines.account']);

    // فلاتر البحث
    $searchBy    = $request->search_by;
    $searchValue = $request->search_value;

    if ($searchBy && $searchValue) {
        match($searchBy) {
            'id'         => $baseQuery->whereHas('journalEntry', fn($q) => $q->where('id', $searchValue)),
            'reference'  => $baseQuery->whereHas('journalEntry', fn($q) => $q->where('reference', 'like', "%{$searchValue}%")),
            'status'     => $baseQuery->whereHas('journalEntry', fn($q) => $q->where('status', $searchValue === 'معتمد' ? 'posted' : 'draft')),
            'created_by' => $baseQuery->whereHas('journalEntry.creator', fn($q) => $q->where('name', 'like', "%{$searchValue}%")),
            'created_at' => $baseQuery->whereHas('journalEntry', fn($q) => $q->whereDate('created_at', $searchValue)),
            default      => null,
        };
    }

    if ($request->filled('date_from'))
        $baseQuery->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '>=', $request->date_from));

    if ($request->filled('date_to'))
        $baseQuery->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '<=', $request->date_to));

    // الرصيد الافتتاحي
    $firstTransaction = (clone $baseQuery)->orderBy('created_at', 'asc')->first();
    $openingBalance   = 0;
    if ($firstTransaction) {
        $openingBalance = $account->ledger()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->where('created_at', '<', $firstTransaction->created_at)
            ->sum(DB::raw('debit - credit'));
    }

    $transactions = $baseQuery->orderBy('created_at', 'asc')->paginate(20)->withQueryString();

    // حجوزات فقط
    $bookingIds = $transactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Booking::class
    )->pluck('journalEntry.source_id')->filter()->unique();

     $bookings = \App\Models\Booking::with('trip') 
        ->whereIn('id', $bookingIds)
        ->get()
        ->keyBy('id');

    return view('accounts.ledger', compact(
        'account', 'transactions', 'openingBalance', 'bookings'
    ));
}

public function search(Request $request)
{
    $q = $request->get('q', '');

    $accounts = Account::where('is_leaf', true)
        ->where('is_active', true)
        ->where(function ($query) use ($q) {
            $query->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('code', 'LIKE', "%{$q}%");
        })
        ->orderBy('code')
        ->limit(20)
        ->get(['id', 'code', 'name']);

    return response()->json($accounts);
}

public function ledgerExport(Account $account, Request $request)
{
    $type = $request->get('type'); // pdf or excel

    $transactions = $account->ledger()
        ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
        ->with(['journalEntry'])
        ->orderBy('created_at', 'asc')
        ->get();

    $openingBalance = 0;
    $firstTx = $transactions->first();
    if ($firstTx) {
        $openingBalance = $account->ledger()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->where('created_at', '<', $firstTx->created_at)
            ->sum(\DB::raw('debit - credit'));
    }

    $bookingIds = $transactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Booking::class
    )->pluck('journalEntry.source_id')->filter()->unique();

    $bookings = \App\Models\Booking::with('trip')
        ->whereIn('id', $bookingIds)->get()->keyBy('id');

    $data = compact('account', 'transactions', 'openingBalance', 'bookings');

    if ($type === 'pdf') {
        $pdf = Pdf::loadView('exports.ledger_pdf', $data, [], [
            'format'          => 'A4-L',
            'default_font'    => 'cairo',
            'mode'            => 'utf-8',
            'autoLangToFont'  => true,
        ]);
        return $pdf->stream("كشف_حساب_{$account->name}.pdf");
    }

    if ($type === 'excel') {
        return $this->exportLedgerExcel($account, $transactions, $openingBalance, $bookings);
    }

    abort(404);
}

private function exportLedgerExcel($account, $transactions, $openingBalance, $bookings)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    $sheet->setTitle('كشف حساب');

    // العنوان
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'كشف حساب: ' . $account->name . ' (' . $account->code . ')');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // الهيدر
    $headers = ['#', 'التاريخ', 'المرجع', 'البيان', 'مدين', 'دائن', 'الرصيد'];
    foreach ($headers as $i => $header) {
        $col = chr(65 + $i);
        $sheet->setCellValue("{$col}2", $header);
        $sheet->getStyle("{$col}2")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563eb']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
    }

    // الرصيد الافتتاحي
    $sheet->setCellValue('A3', '-');
    $sheet->setCellValue('B3', '-');
    $sheet->setCellValue('C3', '-');
    $sheet->setCellValue('D3', 'الرصيد الافتتاحي');
    $sheet->setCellValue('E3', '');
    $sheet->setCellValue('F3', '');
    $sheet->setCellValue('G3', number_format($openingBalance, 2));
    $sheet->getStyle('A3:G3')->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fef3c7']],
        'font' => ['bold' => true],
    ]);

    // البيانات
    $row     = 4;
    $balance = $openingBalance;
    foreach ($transactions as $i => $tx) {
        $entry   = $tx->journalEntry;
        $balance += ($tx->debit - $tx->credit);

        // === منطق البيان المطابق لـ ledger ===
        $detailedDescription = null;
        if ($entry && $entry->source_type === 'App\Models\Booking' && $entry->source_id) {
            $booking = $bookings[$entry->source_id] ?? null;
            if ($booking) {
                $trip = $booking->trip;
                $dateFrom = $trip?->from ? \Carbon\Carbon::parse($trip->from)->format('d-m-y') : '—';
                $dateTo   = $trip?->to   ? \Carbon\Carbon::parse($trip->to)->format('d-m-y')   : '—';

                $detailedDescription = [
                    'line1' => "حجز # {$trip->name} - {$booking->id} - {$booking->client_name}",
                    'line2' => "الرحلة: {$dateFrom} → {$dateTo}",
                    'line3' => number_format($booking->base_price, 2) . " ر.س",
                ];
            }
        }

        if ($detailedDescription) {
            $descriptionText = $detailedDescription['line1'] . "\n" . 
                               $detailedDescription['line2'] . "\n" . 
                               $detailedDescription['line3'];
        } else {
            $descriptionText = $tx->description ?? '-';
        }

        $bg = $i % 2 === 0 ? 'f9fafb' : 'ffffff';
        $sheet->setCellValue("A{$row}", $i + 1);
        $sheet->setCellValue("B{$row}", $entry?->entry_date?->format('Y-m-d') ?? '-');
        $sheet->setCellValue("C{$row}", $entry?->reference ?? '-');
        $sheet->setCellValue("D{$row}", $descriptionText);
        $sheet->setCellValue("E{$row}", $tx->debit > 0 ? number_format($tx->debit, 2) : '');
        $sheet->setCellValue("F{$row}", $tx->credit > 0 ? number_format($tx->credit, 2) : '');
        $sheet->setCellValue("G{$row}", number_format($balance, 2));
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e5e7eb']]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true, // ← هذا مهم
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(-1);
        $row++;
    }

    // عرض الأعمدة
    foreach (['A'=>8,'B'=>14,'C'=>16,'D'=>40,'E'=>14,'F'=>14,'G'=>14] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    $writer   = new Xlsx($spreadsheet);
    $filename = "كشف_حساب_{$account->name}.xlsx";

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

public function export(Request $request)
{
    $type = $request->get('type');

    $accounts = Account::with('allChildren.allChildren.allChildren.allChildren')
        ->roots()
        ->orderBy('code')
        ->get();

    $this->sortAccountsRecursively($accounts);

    if ($type === 'pdf') {
        $pdf = Pdf::loadView('exports.accounts_pdf', compact('accounts'), [], [
            'format'         => 'A4',
            'default_font'   => 'cairo',
            'mode'           => 'utf-8',
            'autoLangToFont' => true,
        ]);
        return $pdf->stream('شجرة_الحسابات.pdf');
    }

    if ($type === 'excel') {
        return $this->exportAccountsExcel($accounts);
    }

    abort(404);
}

private function exportAccountsExcel($accounts)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    $sheet->setTitle('شجرة الحسابات');

    // العنوان
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A1', 'شجرة الحسابات');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // الهيدر
    $headers = ['الكود', 'اسم الحساب', 'النوع', 'المستوى', 'الرصيد', 'الحالة'];
    foreach ($headers as $i => $header) {
        $col = chr(65 + $i);
        $sheet->setCellValue("{$col}2", $header);
        $sheet->getStyle("{$col}2")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563eb']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    $typeNames = [
        'asset'     => 'أصول',
        'liability' => 'خصوم',
        'equity'    => 'ملكية',
        'revenue'   => 'إيرادات',
        'expense'   => 'مصروفات',
    ];

    $row = 3;
    $this->writeAccountsToExcel($sheet, $accounts, $row, $typeNames, 0);

    // عرض الأعمدة
    foreach (['A' => 14, 'B' => 35, 'C' => 12, 'D' => 10, 'E' => 16, 'F' => 10] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    $writer   = new Xlsx($spreadsheet);
    $filename = 'شجرة_الحسابات.xlsx';

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

private function writeAccountsToExcel($sheet, $accounts, &$row, $typeNames, $level)
{
    foreach ($accounts as $i => $account) {
        $balance = $account->getTotalBalance();
        $pad     = str_repeat('    ', $level); // مسافة بادئة

        $bg = $level === 0 ? 'dbeafe' : ($level === 1 ? 'eff6ff' : 'ffffff');

        $sheet->setCellValue("A{$row}", $account->code);
        $sheet->setCellValue("B{$row}", $pad . $account->name);
        $sheet->setCellValue("C{$row}", $typeNames[$account->type] ?? $account->type);
        $sheet->setCellValue("D{$row}", $level + 1);
        $sheet->setCellValue("E{$row}", number_format(abs($balance), 2) . ' ج.م');
        $sheet->setCellValue("F{$row}", $account->is_active ? 'نشط' : 'مجمد');

        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            'font'    => ['bold' => $level === 0],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'e5e7eb']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row++;

        // الأبناء
        $children = $account->allChildren ?? collect();
        if ($children->isNotEmpty()) {
            $this->writeAccountsToExcel($sheet, $children->sortBy('code'), $row, $typeNames, $level + 1);
        }
    }
}
    
}