<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Company;
use App\Models\Agent;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use App\Models\Booking;
use App\Models\AccountLedger;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class AccountController extends Controller
{
    // =============================================
    // كودات الحسابات الثابتة في الشجرة
    // =============================================
    const CODE_CASH          = '1.1.1';    // الصندوق
    const CODE_RECEIVABLE    = '1.1.3';    // مدينون
    const CODE_CUSTOMERS     = '1.1.3.1';  // العملاء (الشركات)
    const CODE_PAYABLE       = '2.1.1';    // دائنون
    const CODE_SUPPLIERS     = '2.1.1.1';  // موردين (جهات الحجز)
    const CODE_REVENUE       = '4.1';      // إيرادات حجز
    const CODE_COST          = '5.3';      // مصروفات تكلفة النشاط

    // =============================================
    // CRUD الحسابات
    // =============================================

    public function index(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            // 1. البحث عن الحسابات التي يطابق اسمها أو كودها نص البحث
            $matchedAccounts = Account::where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->get();
        
            // 2. جمع جميع الآباء والأبناء لهذه الحسابات
            $allRelevantIds = collect();
            foreach ($matchedAccounts as $account) {
                $allRelevantIds->push($account->id);
                
                // إضافة الآباء (المباشرين وغير المباشرين)
                $parent = $account->parent;
                while ($parent) {
                    $allRelevantIds->push($parent->id);
                    $parent = $parent->parent;
                }
                
                // ✅ إضافة الأبناء (جميع الأحفاد)
                $this->addAllDescendantsIds($account->id, $allRelevantIds);
            }
            $allRelevantIds = $allRelevantIds->unique();
        
            // 3. جلب كل الحسابات ذات الصلة مع تحميل الأطفال المباشرين
            $allAccounts = Account::with('children')
                ->whereIn('id', $allRelevantIds)
                ->get()
                ->keyBy('id');
        
            // 4. بناء الشجرة: الحسابات التي ليس لها أب ضمن المجموعة
            $roots = $allAccounts->filter(function ($account) use ($allAccounts) {
                return !$account->parent_id || !$allAccounts->has($account->parent_id);
            })->sortBy('code');
        
            // 5. إنشاء filteredChildren لكل حساب
            foreach ($allAccounts as $account) {
                $children = $allAccounts->where('parent_id', $account->id)->sortBy('code');
                $account->setRelation('filteredChildren', $children);
            }
        
            $accounts = $roots;
            $isSearching = true;
        }
        else {
            // الوضع العادي: جلب الشجرة الكاملة
            $accounts = Account::with('allChildren.allChildren.allChildren.allChildren')
                ->roots()
                ->orderBy('code')
                ->get();
            $this->sortAccountsRecursively($accounts);
            $isSearching = false;
        }

        return view('accounts.index', compact('accounts', 'search', 'isSearching'));
    }

    private function addAllDescendantsIds($accountId, &$ids)
    {
        $childrenIds = Account::where('parent_id', $accountId)->pluck('id');
        foreach ($childrenIds as $childId) {
            $ids->push($childId);
            $this->addAllDescendantsIds($childId, $ids);
        }
    }

    public function list()
    {
        $accounts = Account::with('parent')->orderBy('code')->get();
        return view('accounts.list', compact('accounts'));
    }

    public function create()
    {
        // فقط الحسابات الرئيسية (غير المجمدة وغير الـ leaf) تظهر كآباء
        $parents = Account::where('is_active', true)
            ->orderBy('code')
            ->get();
        $types = $this->accountTypes();

        return view('accounts.create', compact('parents', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => ['required', Rule::in(array_keys($this->accountTypes()))],
            'parent_id'   => 'nullable|exists:accounts,id',
            'account_kind'=> 'required|in:parent,leaf', // رئيسي أو فرعي
            'description' => 'nullable|string',
        ]);

        // التحقق من أن الأب غير مجمد
        if (!empty($data['parent_id'])) {
            $parent = Account::find($data['parent_id']);
            if ($parent && !$parent->is_active) {
                return back()->withInput()
                    ->withErrors(['parent_id' => 'لا يمكن إضافة حساب تحت حساب مجمد']);
            }
            // التحقق من أن الأب ليس leaf (ليس نهائياً)
            // لو الأب leaf = فرعي، لا يقبل أبناء
            if ($parent && $parent->is_leaf) {
                return back()->withInput()
                    ->withErrors(['parent_id' => 'لا يمكن إضافة حساب تحت حساب نهائي (leaf). هذا الحساب لا يقبل أبناء.']);
            }
        }

        // توليد الكود التلقائي
        $data['code'] = $this->generateNextCode($data['parent_id'] ?? null);
        $data['is_leaf'] = ($data['account_kind'] === 'leaf');
        unset($data['account_kind']);

        $account = Account::create($data);

        return redirect()->route('accounts.index')
            ->with('success', "تم إنشاء الحساب [{$account->code}] {$account->name} بنجاح");
    }

    public function edit(Account $account)
    {
        $parents = Account::where('is_active', true)
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();
        $types = $this->accountTypes();

        return view('accounts.edit', compact('account', 'parents', 'types'));
    }

public function update(Request $request, Account $account)
{
    $rules = [
        'name'        => 'required|string|max:255',
        'type'        => ['required', Rule::in(array_keys($this->accountTypes()))],
        'parent_id'   => ['nullable', 'exists:accounts,id', Rule::notIn([$account->id])],
        'is_active'   => 'nullable|boolean',
        'description' => 'nullable|string',
        'account_kind'=> 'sometimes|in:parent,leaf',
    ];

    $data = $request->validate($rules);

    // منع تحويل حساب له أبناء إلى حساب فرعي
    if ($request->has('account_kind') && $request->account_kind === 'leaf' && $account->children()->exists()) {
        return back()->withErrors(['account_kind' => 'لا يمكن تحويل حساب له حسابات فرعية إلى حساب فرعي (leaf). احذف الفروع أولاً.']);
    }

    // تعيين is_leaf بناءً على account_kind إذا ورد
    if ($request->has('account_kind')) {
        $data['is_leaf'] = ($request->account_kind === 'leaf');
    }

    $data['is_active'] = $request->has('is_active');

    // إزالة account_kind من المصفوفة لأنه ليس عموداً في الجدول
    unset($data['account_kind']);

    // تحديث الحساب
    $account->update($data);

    return redirect()->route('accounts.index')
        ->with('success', 'تم تحديث الحساب بنجاح');
}

    public function destroy(Account $account)
    {
        if ($account->children()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب له حسابات فرعية');
        }
        if ($account->journalLines()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب له قيود محاسبية');
        }
        $account->delete();
        return redirect()->route('accounts.index')
            ->with('success', 'تم حذف الحساب بنجاح');
    }

    // =============================================
    // توليد الكود التلقائي
    // =============================================

    /**
     * يولد الكود التالي بناءً على الأب
     * مثال: لو الأب كوده 1.1.3.1 والأبناء الموجودين 1.1.3.1.1 و 1.1.3.1.2
     * سيولد: 1.1.3.1.3
     */
    public static function generateNextCode(?int $parentId): string
    {
        if (!$parentId) {
            // حساب رئيسي بدون أب → جد أكبر رقم رئيسي
            $maxCode = Account::whereNull('parent_id')
                ->get()
                ->map(fn($a) => (int) $a->code)
                ->max() ?? 0;
            return (string)($maxCode + 1);
        }

        $parent = Account::find($parentId);
        if (!$parent) return '1';

        $parentCode = $parent->code;

        // جلب آخر رقم للأبناء
        $children = Account::where('parent_id', $parentId)
            ->withTrashed()
            ->get();

        if ($children->isEmpty()) {
            return $parentCode . '.1';
        }

        // نستخرج آخر رقم بعد آخر نقطة
        $maxSuffix = $children->map(function ($child) use ($parentCode) {
            $suffix = str_replace($parentCode . '.', '', $child->code);
            $parts  = explode('.', $suffix);
            return (int)($parts[0] ?? 0);
        })->max();

        return $parentCode . '.' . ($maxSuffix + 1);
    }

    // =============================================
    // مزامنة الشركات وجهات الحجز مع الشجرة
    // =============================================

    /**
     * تضيف كل الشركات الغير موجودة تحت "العملاء" دفعة واحدة
     * وكل جهات الحجز الغير موجودة تحت "موردين" دفعة واحدة
     * يُستدعى عند إنشاء حجز جديد
     */
    public static function syncAllWithAccountTree(): void
{
    self::ensureParentAccountsNotLeaf();
    DB::transaction(function () {
        self::syncCompaniesToAccounts();
        self::syncAgentsToAccounts();
    });
}

private static function ensureParentAccountsNotLeaf(): void
{
    $codes = ['1.1.3', '1.1.3.1', '2.1.1', '2.1.1.1'];
    Account::whereIn('code', $codes)->update(['is_leaf' => false]);
}
    /**
     * مزامنة كل الشركات مع حسابات العملاء
     */
private static function syncCompaniesToAccounts(): void
{
    $customersAccount = Account::where('code', self::CODE_CUSTOMERS)->first();
    if (!$customersAccount) return;

    foreach (Company::all() as $company) {
        $expectedCode = $customersAccount->code . '.' . $company->id;

        // 1. البحث عن حساب موجود بنفس الاسم تحت الأب الصحيح (حتى لو كان company_id null)
        $existing = Account::where('parent_id', $customersAccount->id)
                    ->where('company_id', $company->id)
                    ->first();

        if (!$existing) {
            $existing = Account::where('parent_id', $customersAccount->id)
                        ->where('name', $company->name)
                        ->first();
        }
        
        if ($existing) {
                if ($existing->company_id != $company->id) {
                    $existing->company_id = $company->id;
                }
                if ($existing->code != $expectedCode) {
                    $existing->code = $expectedCode;
                }
                if ($existing->name != $company->name) {
                    $existing->name = $company->name;
                }
                $existing->is_leaf = true;
                $existing->is_active = true;
                $existing->description = "حساب الشركة: {$company->name}";
                $existing->save();
            }else {
            // إنشاء حساب جديد
            Account::create([
                'code' => $expectedCode,
                'name' => $company->name,
                'type' => 'asset',
                'normal_balance' => 'debit',
                'parent_id' => $customersAccount->id,
                'company_id' => $company->id,
                'is_leaf' => true,
                'is_active' => true,
                'description' => "حساب الشركة: {$company->name}",
            ]);
        }
    }

    // حذف أي حسابات مكررة لا تحمل company_id ولا تطابق اسماً لأي شركة (تنظيف)
    $companyNames = Company::pluck('name')->toArray();
    Account::where('parent_id', $customersAccount->id)
        ->whereNull('company_id')
        ->whereNotIn('name', $companyNames)
        ->forceDelete();
}

private static function syncAgentsToAccounts(): void
{
    $suppliersAccount = Account::where('code', self::CODE_SUPPLIERS)->first();
    if (!$suppliersAccount) return;

    foreach (Agent::all() as $agent) {
        // ⭐ إضافة 'A' لتجنب التعارض مع الفنادق
        $expectedCode = $suppliersAccount->code . '.' . $agent->id;

        $existing = Account::where('parent_id', $suppliersAccount->id)
            ->where('agent_id', $agent->id)
            ->first();

        if (!$existing) {
            $existing = Account::where('parent_id', $suppliersAccount->id)
                        ->where('name', $agent->name)
                        ->first();
        }

        if ($existing) {
                // تحديث agent_id إذا كان مفقوداً
                if ($existing->agent_id != $agent->id) {
                    $existing->agent_id = $agent->id;
                }
                // تحديث الكود إلى الصيغة الجديدة مع '.A.'
                if ($existing->code != $expectedCode) {
                    $existing->code = $expectedCode;
                }
                // تحديث الاسم (في حال تغير)
                if ($existing->name != $agent->name) {
                    $existing->name = $agent->name;
                }
                $existing->is_leaf = true;
                $existing->is_active = true;
                $existing->description = "حساب جهة الحجز: {$agent->name}";
                $existing->save();
            }else {
            Account::create([
                'code' => $expectedCode,
                'name' => $agent->name,
                'type' => 'liability',
                'normal_balance' => 'credit',
                'parent_id' => $suppliersAccount->id,
                'agent_id' => $agent->id,
                'is_leaf' => true,
                'is_active' => true,
                'description' => "حساب جهة الحجز: {$agent->name}",
            ]);
        }
    }
}

  public static function getCompanyAccount(\App\Models\Company $company): ?Account
{
    $customersAccount = Account::where('code', self::CODE_CUSTOMERS)->first();
    if (!$customersAccount) return null;

    // ✅ البحث بـ company_id أولاً
    return Account::where('parent_id', $customersAccount->id)
        ->where('company_id', $company->id)
        ->first();
}

public static function getAgentAccount(\App\Models\Agent $agent): ?Account
{
    $suppliersAccount = Account::where('code', self::CODE_SUPPLIERS)->first();
    if (!$suppliersAccount) return null;

    return Account::where('parent_id', $suppliersAccount->id)
        ->where('agent_id', $agent->id)
        ->first();
}
    // =============================================
    // القيود المحاسبية للحجوزات
    // =============================================

    /**
     * تسجيل قيد محاسبي تلقائي للحجز
     * بيستخدم حساب الشركة وجهة الحجز الخاصين بدل الحسابات العامة
     */
    public static function createBookingJournalEntry(\App\Models\Booking $booking): void
    {
        \Log::info('🟢 createBookingJournalEntry تم استدعاؤها للحجز ID: ' . $booking->id);
        // 1. مزامنة كل الشركات وجهات الحجز أولاً
        self::syncAllWithAccountTree();

        // 2. جلب الحسابات الخاصة بالشركة وجهة الحجز
        $companyAccount = self::getCompanyAccount($booking->company);
        $agentAccount   = self::getAgentAccount($booking->agent);
        \Log::info('companyAccount: ' . ($companyAccount?->code ?? 'null'));
        \Log::info('agentAccount: ' . ($agentAccount?->code ?? 'null'));
        $revenueAccount = Account::where('code', self::CODE_REVENUE)->first();

        if (!$companyAccount || !$revenueAccount || !$agentAccount) {
            Log::warning("شجرة الحسابات: حسابات ناقصة للحجز ID: {$booking->id}", [
                'company_account' => $companyAccount?->code,
                'agent_account'   => $agentAccount?->code,
                'revenue'         => $revenueAccount?->code,
            ]);
            return;
        }

        // 3. التحقق من أن الحسابات غير مجمدة
        foreach ([$companyAccount, $agentAccount, $revenueAccount] as $acc) {
            if (!$acc->is_active) {
                Log::warning("الحساب {$acc->code} مجمد — لن يتم تسجيل القيد");
                return;
            }
        }

        DB::transaction(function () use ($booking, $companyAccount, $agentAccount, $revenueAccount) {
            $entry = JournalEntry::create([
                'reference'   => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'entry_date'  => $booking->created_at->toDateString(),
                'status'      => 'posted',
                'source_type' => \App\Models\Booking::class,
                'source_id'   => $booking->id,
                'created_by'  => Auth::id(),
            ]);

            $amountFromCompany = (float) $booking->amount_due_from_company;

            // مدين: حساب الشركة (العميل مدين لنا)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $companyAccount->id,
                'debit'            => $amountFromCompany,
                'credit'           => 0,
                'description'      => "مستحق من: {$booking->company->name}",
            ]);

            // دائن: إيرادات الحجز
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $revenueAccount->id,
                'debit'            => 0,
                'credit'           => $amountFromCompany,
                'description'      => "إيراد حجز: {$booking->hotel->name}",
            ]);

            $companyAccount->debit($amountFromCompany, "مستحق من: {$booking->company->name}", $entry->id);
            $revenueAccount->credit($amountFromCompany, "إيراد حجز: {$booking->hotel->name}", $entry->id);

        });

        Log::info("تم تسجيل القيد المحاسبي للحجز ID: {$booking->id}");
    }

    /**
     * تسجيل قيد دفعة من شركة
     */
public static function createPaymentJournalEntry(\App\Models\Booking $booking, float $amount, int $creditAccountId): bool
{
    $companyAccount = self::getCompanyAccount($booking->company);
    $paymentAccount = Account::find($creditAccountId); // الحساب المختار (صندوق/بنك/...)

    if (!$companyAccount || !$paymentAccount) {
        Log::warning("حسابات ناقصة للدفعة - حجز ID: {$booking->id}", [
            'company_account' => $companyAccount?->id,
            'payment_account' => $creditAccountId
        ]);
        return false;
    }

    // التحقق من التجميد
    if (!$companyAccount->is_active || !$paymentAccount->is_active) {
        Log::warning("أحد الحسابات مجمد — لن يتم تسجيل قيد الدفعة");
        return false;
    }

    DB::transaction(function () use ($booking, $amount, $companyAccount, $paymentAccount) {
        $entry = JournalEntry::create([
            'reference'   => 'PAY-BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . '-' . time(),
            'entry_date'  => now()->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\Payment::class,
            'source_id'   => $booking->id,
            'created_by'  => Auth::id(),
        ]);

        // ✅ مدين: حساب الدفع (الصندوق/البنك) - استلمنا فلوس
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $paymentAccount->id,
            'debit'            => $amount,
            'credit'           => 0,
            'description'      => "دفعة مستلمة من: {$booking->company->name}",
        ]);

        // ✅ دائن: حساب الشركة (العميل) - قل دينه علينا
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $companyAccount->id,
            'debit'            => 0,
            'credit'           => $amount,
            'description'      => "تسوية ذمم: {$booking->company->name}",
        ]);

        // تسجيل في Ledger
        $paymentAccount->debit($amount, "دفعة مستلمة من: {$booking->company->name}", $entry->id);
        $companyAccount->credit($amount, "تسوية ذمم: {$booking->company->name}", $entry->id);
    });

    Log::info("تم تسجيل قيد الدفعة للحجز ID: {$booking->id} بمبلغ {$amount} في حساب {$paymentAccount->code}");
    return true;
}

/**
 * تسجيل قيد محاسبي لدفع مستحقات فندق (سداد لجهة الحجز)
 * @param Booking $booking الحجز المرتبط
 * @param float $amount المبلغ المدفوع
 * @param int $paymentAccountId معرف حساب الدفع (صندوق، بنك، ...)
 * @return bool
 */
public static function createHotelPaymentJournalEntry(\App\Models\Booking $booking, float $amount, int $paymentAccountId): bool
{
    // حساب جهة الحجز (المورد)
    $agentAccount = self::getAgentAccount($booking->agent);
    // حساب الدفع الذي اختاره المستخدم (يخرج منه المال)
    $paymentAccount = Account::find($paymentAccountId);

    if (!$agentAccount || !$paymentAccount) {
        Log::warning("حسابات ناقصة لدفعة الفندق - حجز ID: {$booking->id}", [
            'agent_account' => $agentAccount?->id,
            'payment_account_id' => $paymentAccountId,
        ]);
        return false;
    }

    if (!$agentAccount->is_active || !$paymentAccount->is_active) {
        Log::warning("أحد الحسابات مجمد — لن يتم تسجيل قيد دفع الفندق");
        return false;
    }

    DB::transaction(function () use ($booking, $amount, $paymentAccount, $agentAccount) {
        $entry = JournalEntry::create([
            'reference'   => 'HPAY-BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT) . '-' . time(),
            'entry_date'  => now()->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\Payment::class,
            'source_id'   => $booking->id,
            'created_by'  => Auth::id(),
        ]);

        // مدين: حساب جهة الحجز (يقل الدين علينا)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $agentAccount->id,
            'debit'            => $amount,
            'credit'           => 0,
            'description'      => "تسوية مستحقات فندق: {$booking->hotel->name}",
        ]);

        // دائن: حساب الدفع المختار (صندوق، بنك، ...)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $paymentAccount->id,
            'debit'            => 0,
            'credit'           => $amount,
            'description'      => "دفع نقدي/بنكي للفندق: {$booking->hotel->name}",
        ]);

        // تحديث الـ Ledger
        $agentAccount->debit($amount, "تسوية مستحقات فندق: {$booking->hotel->name}", $entry->id);
        $paymentAccount->credit($amount, "دفع نقدي/بنكي للفندق: {$booking->hotel->name}", $entry->id);
    });

    Log::info("تم تسجيل قيد دفع الفندق للحجز ID: {$booking->id} بمبلغ {$amount} من حساب {$paymentAccount->code}");
    return true;
}


/**
 * تسجيل قيد محاسبي للإتاحة الجديدة (شراء غرف من فندق)
 */
public static function createAvailabilityJournalEntry(\App\Models\Availability $availability): void
{
    $availability->loadMissing('availabilityRoomTypes.roomType');
    self::syncAllWithAccountTree();

    \Log::info('--- createAvailabilityJournalEntry START ---', [
        'availability_id' => $availability->id,
        'room_types_count' => $availability->availabilityRoomTypes->count(),
    ]);

    // استخدام حساب جهة الحجز (الـ Agent) المرتبط بالإتاحة
    $agent = $availability->agent; // تأكد من وجود العلاقة agent في نموذج Availability
    $agentAccount = self::getAgentAccount($agent);

    // ✅ إذا لم يتم العثور على حساب جهة الحجز بعد المزامنة، قم بإنشائه يدوياً
    if (!$agentAccount) {
        $suppliersAccount = Account::where('code', self::CODE_SUPPLIERS)->first();
        if (!$suppliersAccount) {
            Log::error("لا يوجد حساب الموردين الأب (".self::CODE_SUPPLIERS.") — لن يتم تسجيل قيد الإتاحة");
            return;
        }

        // توليد الكود المتوقع (مثلما تفعل syncAgentsToAccounts)
        $expectedCode = $suppliersAccount->code . '.' . $agent->id;

        // حاول البحث مجدداً عن حساب بنفس الاسم تحت نفس الأب (قد يكون موجوداً بدون agent_id)
        $existingByName = Account::where('parent_id', $suppliersAccount->id)
                            ->where('name', $agent->name)
                            ->first();
        if ($existingByName) {
            // موجود بنفس الاسم → قم بتحديثه وربطه بـ agent_id
            $existingByName->agent_id = $agent->id;
            $existingByName->code = $expectedCode;
            $existingByName->is_leaf = true;
            $existingByName->is_active = true;
            $existingByName->description = "حساب جهة الحجز: {$agent->name}";
            $existingByName->save();
            $agentAccount = $existingByName;
            Log::info("تم تحديث حساب جهة الحجز الموجود بالاسم: {$agent->name} (code: {$expectedCode})");
        } else {
            // غير موجود إطلاقاً → أنشئ حساباً جديداً
            $agentAccount = Account::create([
                'code'           => $expectedCode,
                'name'           => $agent->name,
                'type'           => 'liability',
                'normal_balance' => 'credit',
                'parent_id'      => $suppliersAccount->id,
                'agent_id'       => $agent->id,
                'is_leaf'        => true,
                'is_active'      => true,
                'description'    => "حساب جهة الحجز: {$agent->name}",
            ]);
            Log::info("تم إنشاء حساب جهة الحجز يدوياً: {$agent->name} (code: {$expectedCode})");
        }
    }

    // تأكد من أن حساب المصروفات موجود
    $costAccount = Account::where('code', self::CODE_COST)->first();
    if (!$costAccount) {
        Log::error("حساب مصروف تكلفة النشاط (5.3) غير موجود!");
        return;
    }

    if (!$costAccount->is_active || !$agentAccount->is_active) {
        Log::warning("أحد الحسابات مجمد: costAccount active={$costAccount->is_active}, agentAccount active={$agentAccount->is_active}");
        return;
    }

    if ($availability->availabilityRoomTypes->isEmpty()) {
        \Log::warning('❌ لا توجد أنواع غرفة مرتبطة بهذه الإتاحة!');
        return;
    }

    // حساب التكلفة الإجمالية
    $totalCost = 0;
    $roomDetails = [];
    foreach ($availability->availabilityRoomTypes as $art) {
        if ($art->allotment <= 0 || $art->cost_price <= 0) {
            Log::warning("تخطي نوع غرفة لأن allotment أو cost_price غير صحيح", [
                'room_type_id' => $art->room_type_id,
                'allotment' => $art->allotment,
                'cost_price' => $art->cost_price
            ]);
            continue;
        }
        $days = max(1, $availability->start_date->diffInDays($availability->end_date));
        $roomTotalCost = $art->cost_price * $art->allotment * $days;
        $totalCost += $roomTotalCost;
        $roomDetails[] = "{$art->roomType->room_type_name}: {$art->cost_price} × {$art->allotment} غرفة × {$days} ليلة = {$roomTotalCost}";
    }

    \Log::info('💰 التكلفة الإجمالية المحسوبة', ['totalCost' => $totalCost]);
    if ($totalCost <= 0) {
        \Log::warning('⛔ التكلفة صفر، لن يتم إنشاء القيد');
        return;
    }

    DB::transaction(function () use ($availability, $agentAccount, $costAccount, $totalCost, $roomDetails) {
        $entry = JournalEntry::create([
            'reference'   => 'AV-' . str_pad($availability->id, 6, '0', STR_PAD_LEFT),
            'entry_date'  => $availability->created_at->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\Availability::class,
            'source_id'   => $availability->id,
            'created_by'  => Auth::id(),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $costAccount->id,
            'debit'            => $totalCost,
            'credit'           => 0,
            'description'      => "شراء إتاحة : من جهة حجز {$availability->agent->name} | " . implode(' / ', $roomDetails),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $agentAccount->id,
            'debit'            => 0,
            'credit'           => $totalCost,
            'description'      => "مستحق لجهة الحجز: {$availability->agent->name} عن إتاحة رقم {$availability->id}",
        ]);

        $costAccount->debit($totalCost, "شراء إتاحة من جهة حجز: {$availability->agent->name}", $entry->id);
        $agentAccount->credit($totalCost, "مستحق لجهة الحجز: {$availability->agent->name}", $entry->id);
    });

    Log::info("تم تسجيل قيد الإتاحة ID: {$availability->id} بقيمة {$totalCost} في حساب المورد الفرعي {$agentAccount->code}");
}

/**
 * حذف القيد المحاسبي المرتبط بالإتاحة (إن وجد)
 */
public static function deleteAvailabilityJournalEntry(\App\Models\Availability $availability): void
{
    // البحث عن القيد باستخدام source_type و source_id
    $journalEntry = JournalEntry::where('source_type', \App\Models\Availability::class)
        ->where('source_id', $availability->id)
        ->first();

    if ($journalEntry) {
        // 1. حذف سجلات الـ Ledger المرتبطة بالقيد
        \App\Models\AccountLedger::where('journal_entry_id', $journalEntry->id)->delete();
        // 2. حذف أسطر القيد
        $journalEntry->lines()->delete();
        // 3. حذف القيد نفسه
        $journalEntry->delete();

        Log::info("✅ تم حذف القيد المحاسبي للإتاحة ID: {$availability->id}");
    } else {
        Log::warning("⚠️ لم يتم العثور على قيد محاسبي للإتاحة ID: {$availability->id} عند محاولة الحذف.");
    }
}

public static function updateAvailabilityJournalEntry(\App\Models\Availability $availability): void
{
    // 1. البحث عن القيد القديم
    $oldEntry = JournalEntry::where('source_type', \App\Models\Availability::class)
        ->where('source_id', $availability->id)
        ->first();

    $oldEntryData = null;

    // 2. حفظ بيانات القيد القديم (إن وجد)
    if ($oldEntry) {
        $oldEntryData = [
            'reference'  => $oldEntry->reference,
            'entry_date' => $oldEntry->entry_date->toDateString(),
            'status'     => $oldEntry->status,
            'lines'      => $oldEntry->lines->map(fn($line) => [
                'account_id'  => $line->account_id,
                'debit'       => $line->debit,
                'credit'      => $line->credit,
                'description' => $line->description,
            ])->toArray(),
        ];
    }

    // 3. مزامنة الحسابات والتأكد من وجود الحسابات المطلوبة
    self::syncAllWithAccountTree();

    $agent = $availability->agent;
    $agentAccount = self::getAgentAccount($agent);
    $costAccount = Account::where('code', self::CODE_COST)->first();

    if (!$agentAccount || !$costAccount) {
        Log::error("حسابات ناقصة لتحديث قيد الإتاحة ID: {$availability->id}");
        return;
    }

    if (!$agentAccount->is_active || !$costAccount->is_active) {
        Log::warning("أحد الحسابات مجمد — لن يتم تحديث قيد الإتاحة ID: {$availability->id}");
        return;
    }

    // حساب التكلفة الإجمالية (نفس منطق createAvailabilityJournalEntry)
    $availability->loadMissing('availabilityRoomTypes.roomType');
    if ($availability->availabilityRoomTypes->isEmpty()) {
        Log::warning('لا توجد أنواع غرفة مرتبطة بهذه الإتاحة — لن يتم تحديث القيد');
        return;
    }

    $totalCost = 0;
    $roomDetails = [];
    foreach ($availability->availabilityRoomTypes as $art) {
        if ($art->allotment <= 0 || $art->cost_price <= 0) {
            continue;
        }
        $days = max(1, $availability->start_date->diffInDays($availability->end_date));
        $roomTotalCost = $art->cost_price * $art->allotment * $days;
        $totalCost += $roomTotalCost;
        $roomDetails[] = "{$art->roomType->room_type_name}: {$art->cost_price} × {$art->allotment} غرفة × {$days} ليلة = {$roomTotalCost}";
    }

    if ($totalCost <= 0) {
        Log::warning("التكلفة الإجمالية صفر — لن يتم تحديث قيد الإتاحة ID: {$availability->id}");
        return;
    }

    $newEntry = null;

    // 4. الترانزكشن الموحد: إنشاء القيد الجديد، نقل السجلات، حذف القديم
    DB::transaction(function () use ($availability, $agentAccount, $costAccount, $totalCost, $roomDetails, $oldEntry, $oldEntryData, &$newEntry) {
        // إنشاء القيد الجديد
        $newEntry = JournalEntry::create([
            'reference'   => 'AV-' . str_pad($availability->id, 6, '0', STR_PAD_LEFT),
            'entry_date'  => $availability->created_at->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\Availability::class,
            'source_id'   => $availability->id,
            'created_by'  => auth()->id(),
        ]);

        // أسطر القيد الجديد
        JournalEntryLine::create([
            'journal_entry_id' => $newEntry->id,
            'account_id'       => $costAccount->id,
            'debit'            => $totalCost,
            'credit'           => 0,
            'description'      => "شراء إتاحة : من جهة حجز {$availability->agent->name} | " . implode(' / ', $roomDetails),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $newEntry->id,
            'account_id'       => $agentAccount->id,
            'debit'            => 0,
            'credit'           => $totalCost,
            'description'      => "مستحق لجهة الحجز: {$availability->agent->name} عن إتاحة رقم {$availability->id}",
        ]);

        // تسجيل في Ledger
        $costAccount->debit($totalCost, "شراء إتاحة من جهة حجز: {$availability->agent->name}", $newEntry->id);
        $agentAccount->credit($totalCost, "مستحق لجهة الحجز: {$availability->agent->name}", $newEntry->id);

        // نقل سجلات التعديلات القديمة إلى القيد الجديد (إن وجدت)
        if ($oldEntry) {
            \App\Models\JournalEditLog::where('journal_entry_id', $oldEntry->id)
                ->update(['journal_entry_id' => $newEntry->id]);

            // حذف القيد القديم (Ledger, Lines, Entry)
            \App\Models\AccountLedger::where('journal_entry_id', $oldEntry->id)->delete();
            $oldEntry->lines()->delete();
            $oldEntry->delete();

            Log::info("تم حذف القيد القديم للإتاحة ID: {$availability->id} بعد نقل سجل التعديلات");
        }
    });

    // 5. جمع بيانات القيد الجديد لتسجيل التعديل الحالي
    $newEntry->load('lines');
    $newEntryData = [
        'reference'  => $newEntry->reference,
        'entry_date' => $newEntry->entry_date->toDateString(),
        'status'     => $newEntry->status,
        'lines'      => $newEntry->lines->map(fn($line) => [
            'account_id'  => $line->account_id,
            'debit'       => $line->debit,
            'credit'      => $line->credit,
            'description' => $line->description,
        ])->toArray(),
    ];

    // 6. تسجيل التعديل الحالي في journal_edit_logs
    \App\Models\JournalEditLog::create([
        'journal_entry_id' => $newEntry->id,
        'user_id'          => auth()->id(),
        'action'           => $oldEntryData ? 'edit' : 'create',
        'old_data'         => $oldEntryData ? json_encode($oldEntryData, JSON_UNESCAPED_UNICODE) : null,
        'new_data'         => json_encode($newEntryData, JSON_UNESCAPED_UNICODE),
        'notes'            => $oldEntryData
            ? "تعديل بيانات الإتاحة #{$availability->id} "
            : "إنشاء قيد محاسبي جديد للإتاحة #{$availability->id}",
    ]);

    Log::info("تم تحديث قيد الإتاحة ID: {$availability->id} مع الحفاظ على سجل التعديلات");
}

    // =====================================================
    // أضف في AccountController — دالة جديدة
    // =====================================================
public function ledger(Account $account, Request $request)
{
    // ========== 1. بناء الاستعلام الأساسي (مع الفلاتر) ==========
    $baseQuery = $account->ledger()
        ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
        ->with(['journalEntry', 'journalEntry.creator', 'journalEntry.lines.account']);

    // تطبيق الفلاتر (بحث وتواريخ)
    $searchBy = $request->search_by;
    $searchValue = $request->search_value;

    if ($searchBy && $searchValue) {
        switch ($searchBy) {
            case 'id':
                $baseQuery->whereHas('journalEntry', fn($q) => $q->where('id', $searchValue));
                break;
            case 'reference':
                $baseQuery->whereHas('journalEntry', fn($q) => $q->where('reference', 'like', "%{$searchValue}%"));
                break;
            case 'status':
                $statusValue = match($searchValue) {
                    'معتمد' => 'posted',
                    'غير معتمد' => 'draft',
                    default => $searchValue
                };
                $baseQuery->whereHas('journalEntry', fn($q) => $q->where('status', $statusValue));
                break;
            case 'created_by':
                $baseQuery->whereHas('journalEntry.creator', fn($q) => $q->where('name', 'like', "%{$searchValue}%"));
                break;
            case 'created_at':
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $searchValue)->format('Y-m-d');
                    $baseQuery->whereHas('journalEntry', fn($q) => $q->whereDate('created_at', $date));
                } catch (\Exception $e) {
                    $baseQuery->whereHas('journalEntry', fn($q) => $q->whereDate('created_at', $searchValue));
                }
                break;
        }
    }

    if ($request->filled('date_from')) {
        $baseQuery->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '>=', $request->date_from));
    }
    if ($request->filled('date_to')) {
        $baseQuery->whereHas('journalEntry', fn($q) => $q->whereDate('entry_date', '<=', $request->date_to));
    }

    // ========== 2. معالجة التصدير (PDF / Excel) ==========
    $exportType = $request->query('export');
    
    if ($exportType) {
        // جلب جميع الحركات (بدون pagination)
        $allTransactions = (clone $baseQuery)->orderBy('created_at', 'asc')->get();
        
        // حساب الرصيد الافتتاحي
        $firstTransaction = $allTransactions->first();
        $openingBalance = 0;
        if ($firstTransaction) {
            $openingBalance = $account->ledger()
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                ->where('created_at', '<', $firstTransaction->created_at)
                ->sum(DB::raw('debit - credit'));
        }
        
        // جلب البيانات الإضافية (لربط الحجوزات والإتاحات)
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
        
            
        $autoBookingsMap = [];
        if ($availabilities->isNotEmpty()) {
            $autoAvailabilityIds = $availabilities->where('is_auto', true)->pluck('id');
            if ($autoAvailabilityIds->isNotEmpty()) {
                $roomTypeIds = \App\Models\AvailabilityRoomType::whereIn('availability_id', $autoAvailabilityIds)
                    ->pluck('id');
                if ($roomTypeIds->isNotEmpty()) {
                    $bookingsForAuto = \App\Models\Booking::whereIn('availability_room_type_id', $roomTypeIds)
                        ->get()
                        ->keyBy('availability_room_type_id');
                    $roomTypes = \App\Models\AvailabilityRoomType::whereIn('availability_id', $autoAvailabilityIds)
                        ->get(['id', 'availability_id']);
                    foreach ($roomTypes as $rt) {
                        if (isset($bookingsForAuto[$rt->id])) {
                            $autoBookingsMap[$rt->availability_id] = $bookingsForAuto[$rt->id];
                        }
                    }
                }
            }
        }

        $data = [
            'account' => $account,
            'transactions' => $allTransactions,
            'openingBalance' => $openingBalance,
            'bookings' => $bookings,
            'availabilities' => $availabilities,
            'autoBookingsMap' => $autoBookingsMap,
            'isPdfExport'      => true,
        ];
        
        if ($exportType === 'pdf') {
            $pdf = Pdf::loadView('exports.ledger_pdf', $data, [], [
                'format' => 'A4-P', // portrait
                'default_font_size' => 12,
                'default_font' => 'cairo',
                'mode' => 'utf-8',
                'autoLangToFont' => true,
            ]);
            return $pdf->download("كشف_حساب_{$account->code}_{$account->name}.pdf");
        }
        
       if ($exportType === 'excel') {
            $spreadsheet = $this->generateLedgerSpreadsheet($data);
            $writer = new Xlsx($spreadsheet);

            $filename = "كشف_حساب_{$account->code}_{$account->name}.xlsx";

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        }
    }
    
    // ========== 3. العرض العادي مع Pagination ==========
    // حساب الرصيد الافتتاحي للصفحة (قبل أول حركة في النتائج بعد الفلاتر)
    $firstTransaction = (clone $baseQuery)->orderBy('created_at', 'asc')->first();
    $openingBalance = 0;
    if ($firstTransaction) {
        $openingBalance = $account->ledger()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->where('created_at', '<', $firstTransaction->created_at)
            ->sum(DB::raw('debit - credit'));
    }
    
    // Pagination
    $transactions = $baseQuery->orderBy('created_at', 'asc')->paginate(20)->withQueryString();
    
    // جلب الحجوزات والإتاحات للصفحة المعروضة فقط
    $bookingIds = $transactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Booking::class
    )->pluck('journalEntry.source_id')->filter()->unique();
    
    $availabilityIds = $transactions->filter(
        fn($t) => $t->journalEntry?->source_type === \App\Models\Availability::class
    )->pluck('journalEntry.source_id')->filter()->unique();
    
    $bookings = \App\Models\Booking::with(['company', 'hotel'])
        ->whereIn('id', $bookingIds)->get()->keyBy('id');
    
    $availabilities = \App\Models\Availability::with(['hotel', 'agent', 'availabilityRoomTypes.roomType'])
        ->whereIn('id', $availabilityIds)->get()->keyBy('id');

    $autoBookingsMap = [];
    if ($availabilities->isNotEmpty()) {
        $autoAvailabilityIds = $availabilities->where('is_auto', true)->pluck('id');
        if ($autoAvailabilityIds->isNotEmpty()) {
            $roomTypeIds = \App\Models\AvailabilityRoomType::whereIn('availability_id', $autoAvailabilityIds)
                ->pluck('id');
            if ($roomTypeIds->isNotEmpty()) {
                $bookingsForAuto = \App\Models\Booking::whereIn('availability_room_type_id', $roomTypeIds)
                    ->get()
                    ->keyBy('availability_room_type_id');
                    $roomTypes = \App\Models\AvailabilityRoomType::whereIn('availability_id', $autoAvailabilityIds)
                    ->get(['id', 'availability_id']);
                foreach ($roomTypes as $rt) {
                    if (isset($bookingsForAuto[$rt->id])) {
                        $autoBookingsMap[$rt->availability_id] = $bookingsForAuto[$rt->id];
                    }
                }
            }
        }
    }

    return view('accounts.ledger', compact(
        'account', 'transactions', 'openingBalance', 'bookings', 'availabilities', 'autoBookingsMap'
    ));
}

    private function generateLedgerSpreadsheet(array $data): Spreadsheet
{
    extract($data);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    $sheet->setTitle('كشف الحساب');

    // ── الخط الافتراضي ──
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

    // ── عرض الأعمدة ──
    $colWidths = ['A' => 6, 'B' => 14, 'C' => 12, 'D' => 40, 'E' => 12, 'F' => 14, 'G' => 18];
    foreach ($colWidths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    // ─────────────────────────────────────────
    // الصف 1: عنوان الحساب
    // ─────────────────────────────────────────
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'كشف حساب: ' . $account->name . ' (' . $account->code . ')');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 13],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
        'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(24);

    // ─────────────────────────────────────────
    // الصف 2: رؤوس الأعمدة
    // ─────────────────────────────────────────
    $headers = [
        'A2' => '#', 
        'B2' => 'التاريخ', 
        'C2' => 'رقم القيد', 
        'D2' => 'البيان', 
        'E2' => 'مدين', 
        'F2' => 'دائن', 
        'G2' => 'الرصيد'
    ];
    foreach ($headers as $cell => $label) {
        $sheet->setCellValue($cell, $label);
    }
    $sheet->getStyle('A2:G2')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(2)->setRowHeight(18);

    // ─────────────────────────────────────────
    // دالة مساعدة لتنسيق الرصيد
    // ─────────────────────────────────────────
    $balanceText = fn($bal) => number_format(abs($bal), 2) . ' ' . ($bal >= 0 ? 'مدين' : 'دائن');

    $row     = 3;
    $balance = $openingBalance;

    // ─────────────────────────────────────────
    // الرصيد الافتتاحي
    // ─────────────────────────────────────────
    $sheet->setCellValue("A{$row}", 1);
    $sheet->setCellValue("B{$row}", '—');
    $sheet->setCellValue("C{$row}", '—');
    $sheet->setCellValue("D{$row}", 'الرصيد الافتتاحي'); 
    $sheet->setCellValue("E{$row}", $balance > 0 ? $balance : 0);  
    $sheet->setCellValue("F{$row}", $balance < 0 ? abs($balance) : 0); 
    $sheet->setCellValue("G{$row}", $balanceText($balance));

    $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle("A{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;

    // ─────────────────────────────────────────
    // الحركات
    // ─────────────────────────────────────────
    $counter = 2;
    foreach ($transactions as $trans) {
        $balance += $trans->debit - $trans->credit;
        $entry = $trans->journalEntry;

        // البيان التفصيلي
        $detailedDescription = null;

        if ($entry->source_type === \App\Models\Booking::class && $entry->source_id) {
            $booking = $bookings[$entry->source_id] ?? null;
            if ($booking) {
                $checkIn  = $booking->check_in  ? \Carbon\Carbon::parse($booking->check_in)->format('d-m-y')  : '—';
                $checkOut = $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('d-m-y') : '—';
                $detailedDescription =
                    "{$booking->id} {$booking->client_name} - " . ($booking->hotel->name ?? '—') . "\n" .
                    "{$booking->rooms} غرفة : {$checkOut} → {$checkIn}\n" .
                    number_format($booking->sale_price, 2) . ' ' . ($booking->currency === 'KWD' ? 'د.ك' : 'ر.س');
            }
        } elseif ($entry->source_type === \App\Models\Availability::class && $entry->source_id) {
            $availability = $availabilities[$entry->source_id] ?? null;
            if ($availability) {
                $startDate = $availability->start_date ? \Carbon\Carbon::parse($availability->start_date)->format('d-m-y') : '—';
                $endDate   = $availability->end_date   ? \Carbon\Carbon::parse($availability->end_date)->format('d-m-y')   : '—';
                $roomsSummary = $availability->availabilityRoomTypes->map(
                    fn($rt) => ($rt->roomType->room_type_name ?? '—') . ': ' . $rt->allotment . ' غرفة بـ ' . number_format($rt->cost_price, 2)
                )->implode(' | ');
                $detailedDescription =
                    "{$availability->id} - " . ($availability->hotel->name ?? '—') . "\n" .
                    "{$startDate} → {$endDate}\n" .
                    $roomsSummary;
            }
        }

        $descriptionText = $detailedDescription ?: ($trans->description ?: '—');

       $sheet->setCellValue("A{$row}", $counter);
        $sheet->setCellValue("B{$row}", $entry->entry_date->format('d/m/Y')); 
        $sheet->setCellValue("C{$row}", $entry->id);                           
        $sheet->setCellValue("D{$row}", $descriptionText);                   
        $sheet->setCellValue("E{$row}", $trans->debit  > 0 ? $trans->debit  : 0); 
        $sheet->setCellValue("F{$row}", $trans->credit > 0 ? $trans->credit : 0);  
        $sheet->setCellValue("G{$row}", $balanceText($balance));               

        // تنسيق الأرقام للمدين والدائن
        $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

        // تنسيق الصف
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

        // تلوين صفوف متناوبة
        if ($counter % 2 === 0) {
            $sheet->getStyle("A{$row}:G{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F5F5F5');
        }

        $row++;
        $counter++;
    }

    // ─────────────────────────────────────────
    // الرصيد النهائي
    // ─────────────────────────────────────────
    $sheet->mergeCells("A{$row}:F{$row}");
    $sheet->setCellValue("A{$row}", 'الرصيد النهائي');
    $sheet->setCellValue("G{$row}", $balanceText($balance));
    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
        'font'      => ['bold' => true],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    // ─────────────────────────────────────────
    // حدود لكل الجدول
    // ─────────────────────────────────────────
    $lastRow = $row;
    $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['rgb' => 'CCCCCC'],
            ],
        ],
    ]);

    return $spreadsheet;
}


public function selectLedger(Request $request)
{
    $query = Account::where('is_leaf', true)
        ->where('is_active', true);

    // فلترة البحث
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%");
        });
    }

    $accounts = $query->orderBy('code')->paginate(9);

    return view('accounts.select_ledger', compact('accounts'));
}



// دفعة من شركة (بدون حجز محدد)
public static function createCompanyPaymentJournalEntry(
    \App\Models\Company $company,
    float $amount,
    int $paymentAccountId,
    $sourceId = null 
): bool {
    $companyAccount = self::getCompanyAccount($company);
    $paymentAccount = Account::find($paymentAccountId);

    if (!$companyAccount || !$paymentAccount) return false;
    if (!$companyAccount->is_active || !$paymentAccount->is_active) return false;

    DB::transaction(function () use ($company, $amount, $companyAccount, $paymentAccount, $sourceId) {
        $entry = JournalEntry::create([
            'reference'   => 'PAY-CO-' . $company->id . '-' . time(),
            'entry_date'  => now()->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\Payment::class,
            'source_id'   => $sourceId,
            'created_by'  => Auth::id(),
        ]);

        // مدين: حساب الدفع (صندوق/بنك) ← استلمنا فلوس
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $paymentAccount->id,
            'debit'            => $amount,
            'credit'           => 0,
            'description'      => "دفعة مستلمة من شركة: {$company->name}",
        ]);

        // دائن: حساب الشركة ← قل دينها علينا
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $companyAccount->id,
            'debit'            => 0,
            'credit'           => $amount,
            'description'      => "تسوية ذمم شركة: {$company->name}",
        ]);

        $paymentAccount->debit($amount, "دفعة مستلمة من شركة: {$company->name}", $entry->id);
        $companyAccount->credit($amount, "تسوية ذمم شركة: {$company->name}", $entry->id);

    });
  
    Log::info("تم تسجيل قيد دفعة الشركة: {$company->name} بمبلغ {$amount}");
    return true;
}

// ===================================================

// دفعة لجهة حجز (بدون حجز محدد)
public static function createAgentPaymentJournalEntry(
    \App\Models\Agent $agent,
    float $amount,
    int $paymentAccountId,
    $sourceId = null
): bool {
    $agentAccount   = self::getAgentAccount($agent);
    $paymentAccount = Account::find($paymentAccountId);

    if (!$agentAccount || !$paymentAccount) return false;
    if (!$agentAccount->is_active || !$paymentAccount->is_active) return false;

    DB::transaction(function () use ($agent, $amount, $agentAccount, $paymentAccount, $sourceId) {
        $entry = JournalEntry::create([
            'reference'   => 'PAY-AG-' . $agent->id . '-' . time(),
            'entry_date'  => now()->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\AgentPayment::class,
            'source_id'   => $sourceId,
            'created_by'  => Auth::id(),
        ]);

        // مدين: حساب جهة الحجز ← قل الدين اللي علينا ليها
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $agentAccount->id,
            'debit'            => $amount,
            'credit'           => 0,
            'description'      => "تسوية مستحقات جهة حجز: {$agent->name}",
        ]);

        // دائن: حساب الدفع (صندوق/بنك) ← خرجت فلوس
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $paymentAccount->id,
            'debit'            => 0,
            'credit'           => $amount,
            'description'      => "دفع نقدي/بنكي لجهة حجز: {$agent->name}",
        ]);


        $agentAccount->debit($amount, "تسوية مستحقات جهة حجز: {$agent->name}", $entry->id);
        $paymentAccount->credit($amount, "دفع نقدي/بنكي لجهة حجز: {$agent->name}", $entry->id);

    });
   
    Log::info("تم تسجيل قيد دفعة جهة الحجز: {$agent->name} بمبلغ {$amount}");
    return true;
}


/**
 * تسجيل قيد خصم لشركة مع إمكانية اختيار حساب الخصم (مدين)
 * @param Company $company
 * @param float $discountAmount (موجب)
 * @param int $discountAccountId  // الحساب الذي يختاره المستخدم (مدين)
 * @param string|null $reason
 * @return bool
 */
public static function createCompanyDiscountWithChoice(
    \App\Models\Company $company,
    float $discountAmount,
    int $discountAccountId,
    ?string $reason = null,
     $sourceId = null
): bool {
    // حساب الشركة (دائن)
    $companyAccount = self::getCompanyAccount($company);
    if (!$companyAccount || !$companyAccount->is_active) {
        Log::warning("حساب الشركة غير موجود أو مجمد: {$company->name}");
        return false;
    }

    // حساب الخصم الذي اختاره المستخدم
    $discountAccount = Account::find($discountAccountId);
    if (!$discountAccount || !$discountAccount->is_active) {
        Log::warning("حساب الخصم المختار غير موجود أو مجمد (ID: $discountAccountId)");
        return false;
    }

    DB::transaction(function () use ($company, $discountAmount, $reason, $companyAccount, $discountAccount, $sourceId) {
        $entry = JournalEntry::create([
            'reference'   => 'DISC-CO-' . $company->id . '-' . time(),
            'entry_date'  => now()->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\Payment::class,
            'source_id'   => $sourceId,
            'created_by'  => Auth::id(),
        ]);

        // مدين: حساب الخصم المختار
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $discountAccount->id,
            'debit'            => $discountAmount,
            'credit'           => 0,
            'description'      => "خصم للشركة: {$company->name}" . ($reason ? " - $reason" : ""),
        ]);

        // دائن: حساب الشركة (تقليل المديونية)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $companyAccount->id,
            'debit'            => 0,
            'credit'           => $discountAmount,
            'description'      => "تخفيض الذمة بخصم: {$company->name}",
        ]);

        // تسجيل في دفتر الأستاذ
        $discountAccount->debit($discountAmount, "خصم للشركة: {$company->name}", $entry->id);
        $companyAccount->credit($discountAmount, "خصم مسموح به", $entry->id);
    });

    Log::info("تم تسجيل قيد خصم للشركة {$company->name} بقيمة {$discountAmount} في حساب {$discountAccount->name}");
    return true;
}


/**
 * تسجيل قيد خصم لجهة الحجز (Agent) - خصم مكتسب لصالحنا
 * مدين: حساب جهة الحجز (تقليل الالتزام تجاهه)
 * دائن: حساب إيراد الخصم المختار (زيادة الإيرادات)
 */
public static function createAgentDiscountJournalEntry(
    \App\Models\Agent $agent,
    float $discountAmount,
    int $discountAccountId,
    ?string $reason = null,
    $sourceId = null 
): bool {
    // حساب جهة الحجز (دائنون) - سيتم مدينه لتقليل الرصيد المستحق له
    $agentAccount = self::getAgentAccount($agent);
    if (!$agentAccount || !$agentAccount->is_active) {
        \Log::warning("حساب جهة الحجز غير موجود أو مجمد: {$agent->name}");
        return false;
    }

    // حساب الخصم (يجب أن يكون من نوع إيراد أو خصم مكتسب)
    $discountAccount = \App\Models\Account::find($discountAccountId);
    if (!$discountAccount || !$discountAccount->is_active) {
        \Log::warning("حساب الخصم المختار غير موجود أو مجمد (ID: $discountAccountId)");
        return false;
    }

    \DB::transaction(function () use ($agent, $discountAmount, $reason, $agentAccount, $discountAccount, $sourceId) {
        $entry = \App\Models\JournalEntry::create([
            'reference'   => 'DISC-AG-' . $agent->id . '-' . time(),
            'entry_date'  => now()->toDateString(),
            'status'      => 'posted',
            'source_type' => \App\Models\AgentPayment::class,
            'source_id'   => $sourceId,
            'created_by'  => auth()->id(),
        ]);

        // مدين: حساب جهة الحجز (نقلل المطلوب دفعه له)
        \App\Models\JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $agentAccount->id,
            'debit'            => $discountAmount,
            'credit'           => 0,
            'description'      => "خصم مكتسب من جهة الحجز: {$agent->name}" . ($reason ? " - $reason" : ""),
        ]);

        // دائن: حساب إيراد الخصم المختار (يزيد الإيرادات)
        \App\Models\JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $discountAccount->id,
            'debit'            => 0,
            'credit'           => $discountAmount,
            'description'      => "إيراد خصم مكتسب من {$agent->name}",
        ]);

        // تسجيل في دفتر الأستاذ
        $agentAccount->debit($discountAmount, "خصم مكتسب من جهة الحجز: {$agent->name}", $entry->id);
        $discountAccount->credit($discountAmount, "إيراد خصم مكتسب", $entry->id);
    });

    \Log::info("تم تسجيل قيد خصم لجهة الحجز {$agent->name} بقيمة {$discountAmount} كإيراد في حساب {$discountAccount->name}");
    return true;
}


// احذف القيد المحاسبي بتاع دفعة معينة
public static function deletePaymentJournalEntry($paymentId, $type)
{
    if ($type == 'company') {
        $entry = \App\Models\JournalEntry::where('source_type', \App\Models\Payment::class)
                    ->where('source_id', $paymentId)->first();
    } else {
        $entry = \App\Models\JournalEntry::where('source_type', \App\Models\AgentPayment::class)
                    ->where('source_id', $paymentId)->first();
    }

    if ($entry) {
        $entry->lines()->delete();                    // امسح أسطر القيد
        \App\Models\AccountLedger::where('journal_entry_id', $entry->id)->delete(); // امسح أثر ledger
        $entry->delete();                             // امسح القيد نفسه
    }
}


    // =============================================
    // Helpers
    // =============================================

    private function accountTypes(): array
    {
        return [
            'asset'     => 'أصول',
            'liability' => 'خصوم',
            'equity'    => 'حقوق ملكية',
            'revenue'   => 'إيرادات',
            'expense'   => 'مصروفات',
        ];
    }

// =============================================
// تصدير شجرة الحسابات
// =============================================

public function exportTreePdf()
{
    $accounts = Account::with('allChildren')->roots()->orderBy('code')->get();
    $this->sortAccountsRecursively($accounts);

    $pdf = Pdf::loadView('accounts.print_tree', compact('accounts'), [], [
        'format' => 'A4-L', // landscape
        'default_font_size' => 12,
        'default_font' => 'cairo',
        'mode' => 'utf-8',
        'autoLangToFont' => true,
    ]);
    return $pdf->download('شجرة_الحسابات_' . now()->format('Y-m-d') . '.pdf');
}

public function exportTreeExcel()
{
    $accounts = Account::with('allChildren')->roots()->orderBy('code')->get();
    $this->sortAccountsRecursively($accounts);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    $sheet->setTitle('شجرة الحسابات');
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

    // عرض الأعمدة
    foreach (['A' => 8, 'B' => 18, 'C' => 14, 'D' => 35, 'E' => 14] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    // العنوان
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'شجرة الحسابات');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    // تاريخ التصدير
    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'تاريخ التصدير: ' . now()->format('d/m/Y'));
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // رؤوس الأعمدة
    foreach (['A3' => 'الكود', 'B3' => 'اسم الحساب', 'C3' => 'النوع', 'D3' => 'الفئة', 'E3' => 'الرصيد'] as $cell => $label) {
        $sheet->setCellValue($cell, $label);
    }
    $sheet->getStyle('A3:E3')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    // الصفوف
    $row = 4;
    $this->fillTreeSpreadsheetRows($sheet, $accounts, $row, 0);

    // حدود الجدول
    $sheet->getStyle("A1:E" . ($row - 1))->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);

    $filename = 'شجرة_الحسابات_' . now()->format('Y-m-d') . '.xlsx';
    $writer = new Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Cache-Control' => 'max-age=0',
    ]);
}

private function fillTreeSpreadsheetRows($sheet, $accounts, &$row, int $depth): void
{
    foreach ($accounts as $account) {
        $indent = str_repeat('    ', $depth);

        $sheet->setCellValue("A{$row}", $account->code);
        $sheet->setCellValue("B{$row}", $indent . $account->name);
        $sheet->setCellValue("C{$row}", $account->type ?? '—');
        $sheet->setCellValue("D{$row}", $account->category ?? '—');
        $sheet->setCellValue("E{$row}", $account->balance ?? 0);

        $sheet->getStyle("A{$row}:E{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$row}")->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // تمييز حسابات المستوى الأول
        if ($depth === 0) {
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F0FE']],
            ]);
        }

        $row++;

        if ($account->allChildren && $account->allChildren->isNotEmpty()) {
            $this->fillTreeSpreadsheetRows($sheet, $account->allChildren, $row, $depth + 1);
        }
    }
}

private function sortAccountsRecursively($accounts)
{
    foreach ($accounts as $account) {
        if ($account->allChildren->isNotEmpty()) {
            $sorted = $account->allChildren->sortBy(fn($c) => array_map('intval', explode('.', $c->code)));
            $account->setRelation('allChildren', $sorted);
            $this->sortAccountsRecursively($sorted);
        }
    }
}

    /**
 * بحث AJAX في الحسابات النهائية (is_leaf)
 * GET /accounts/search?q=كلمة_البحث
 */
    public function searchAccounts(Request $request)
    {
    $q = trim($request->input('q', ''));
 
    $accounts = Account::where('is_leaf', true)
        ->where('is_active', true)
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        })
        ->orderBy('code')
        ->limit(30)
        ->get(['id', 'code', 'name']);
 
    return response()->json($accounts);
    }

public static function updateBookingJournalEntry(Booking $booking, array $oldBookingData = null): void
{
    // 1. البحث عن القيد القديم
    $oldEntry = JournalEntry::where('source_type', Booking::class)
        ->where('source_id', $booking->id)
        ->first();

    $oldEntryData = null;

    // 2. حفظ بيانات القيد القديم للتسجيل في الـ log
    if ($oldEntry) {
        $oldEntryData = [
            'reference'  => $oldEntry->reference,
            'entry_date' => $oldEntry->entry_date->toDateString(),
            'status'     => $oldEntry->status,
            'lines'      => $oldEntry->lines->map(fn($line) => [
                'account_id'  => $line->account_id,
                'debit'       => $line->debit,
                'credit'      => $line->credit,
                'description' => $line->description,
            ])->toArray(),
        ];
    }

    // 3. إنشاء قيد جديد بنفس منطق createBookingJournalEntry
    self::syncAllWithAccountTree();

    $companyAccount = self::getCompanyAccount($booking->company);
    $agentAccount   = self::getAgentAccount($booking->agent);
    $revenueAccount = Account::where('code', self::CODE_REVENUE)->first();

    if (!$companyAccount || !$revenueAccount || !$agentAccount) {
        Log::warning("حسابات ناقصة لإنشاء القيد المحاسبي للحجز ID: {$booking->id}");
        return;
    }

    foreach ([$companyAccount, $agentAccount, $revenueAccount] as $acc) {
        if (!$acc->is_active) {
            Log::warning("الحساب {$acc->code} مجمد — لن يتم تسجيل القيد للحجز ID: {$booking->id}");
            return;
        }
    }

    $newEntry = null;
    $amountFromCompany = (float) $booking->amount_due_from_company;

    DB::transaction(function () use ($booking, $companyAccount, $revenueAccount, $amountFromCompany, &$newEntry) {
        $newEntry = JournalEntry::create([
            'reference'   => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            'entry_date'  => $booking->created_at->toDateString(),
            'status'      => 'posted',
            'source_type' => Booking::class,
            'source_id'   => $booking->id,
            'created_by'  => auth()->id(),
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $newEntry->id,
            'account_id'       => $companyAccount->id,
            'debit'            => $amountFromCompany,
            'credit'           => 0,
            'description'      => "مستحق من: {$booking->company->name}",
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $newEntry->id,
            'account_id'       => $revenueAccount->id,
            'debit'            => 0,
            'credit'           => $amountFromCompany,
            'description'      => "إيراد حجز: {$booking->hotel->name}",
        ]);

        $companyAccount->debit($amountFromCompany, "مستحق من: {$booking->company->name}", $newEntry->id);
        $revenueAccount->credit($amountFromCompany, "إيراد حجز: {$booking->hotel->name}", $newEntry->id);
    });

    // 4. نقل سجلات التعديلات القديمة إلى القيد الجديد (إن وجدت)
    if ($oldEntry) {
        \App\Models\JournalEditLog::where('journal_entry_id', $oldEntry->id)
            ->update(['journal_entry_id' => $newEntry->id]);

        // 5. حذف القيد القديم بالكامل (Ledger + Lines + Entry)
        AccountLedger::where('journal_entry_id', $oldEntry->id)->delete();
        $oldEntry->lines()->delete();
        $oldEntry->delete();

        Log::info("تم حذف القيد القديم للحجز ID: {$booking->id} بعد نقل سجل التعديلات إلى القيد الجديد");
    }

    // 6. جمع بيانات القيد الجديد لتسجيل التعديل الحالي
    $newEntry->load('lines');
    $newEntryData = [
        'reference'  => $newEntry->reference,
        'entry_date' => $newEntry->entry_date->toDateString(),
        'status'     => $newEntry->status,
        'lines'      => $newEntry->lines->map(fn($line) => [
            'account_id'  => $line->account_id,
            'debit'       => $line->debit,
            'credit'      => $line->credit,
            'description' => $line->description,
        ])->toArray(),
    ];

    // 7. تسجيل التعديل الحالي في journal_edit_logs (مرتبط بالقيد الجديد)
    \App\Models\JournalEditLog::create([
        'journal_entry_id' => $newEntry->id,
        'user_id'          => auth()->id(),
        'action'           => $oldEntryData ? 'edit' : 'create',
        'old_data'         => $oldEntryData ? json_encode($oldEntryData, JSON_UNESCAPED_UNICODE) : null,
        'new_data'         => json_encode($newEntryData, JSON_UNESCAPED_UNICODE),
        'notes'            => $oldEntryData 
            ? "تعديل بيانات الحجز #{$booking->id} " 
            : "إنشاء قيد محاسبي جديد للحجز #{$booking->id}",
    ]);

    Log::info("تم إنشاء قيد محاسبي جديد للحجز ID: {$booking->id} ونقل سجل التعديلات السابق إليه");
}


}