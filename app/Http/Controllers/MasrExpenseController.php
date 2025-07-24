<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasrExpense;
use App\Models\MasrExpenseItem;
use Illuminate\Support\Facades\Auth;

class MasrExpenseController extends Controller
{
    public function index(Request $request)
{
    $sort = $request->input('sort', 'date_desc');

    $expensesQuery = MasrExpense::with(['items', 'creator']);

    if ($sort === 'date_asc') {
        $expenses = $expensesQuery->orderBy('date', 'asc')->paginate(15);
    } elseif ($sort === 'date_desc') {
        $expenses = $expensesQuery->orderBy('date', 'desc')->paginate(15);
    } else {
        // جلب كل البيانات ثم ترتيبها بالكولكشن
        $expenses = $expensesQuery->get();

        if ($sort === 'cost_desc') {
            $expenses = $expenses->sortByDesc(function ($expense) {
                return $expense->items->sum('amount');
            })->values();
        } elseif ($sort === 'cost_asc') {
            $expenses = $expenses->sortBy(function ($expense) {
                return $expense->items->sum('amount');
            })->values();
        }
    }

    return view('masr_expenses.index', compact('expenses', 'sort'));
}

    public function create()
    {
        $currencies = ['EGP' => 'جنيه مصري', 'USD' => 'دولار أمريكي', 'SAR' => 'ريال سعودي', 'KWD' => 'دينار كويتي'];
        return view('masr_expenses.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric',
            'items.*.currency' => 'required|string',
        ]);

        $expense = MasrExpense::create([
            'title' => $request->title,
            'date' => $request->date,
            'created_by' => Auth::id(),
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $expense->items()->create($item);
        }

        return redirect()->route('admin.masr_expenses.index')->with('success', 'تم إضافة المصاريف بنجاح');
    }

    public function edit(MasrExpense $masr_expense)
    {
        $currencies = ['EGP' => 'جنيه مصري', 'USD' => 'دولار أمريكي', 'SAR' => 'ريال سعودي', 'KWD' => 'دينار كويتي'];
        $masr_expense->load('items');
        return view('masr_expenses.edit', compact('masr_expense', 'currencies'));
    }

    public function update(Request $request, MasrExpense $masr_expense)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric',
            'items.*.currency' => 'required|string',
        ]);

        $masr_expense->update([
            'title' => $request->title,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);

        $masr_expense->items()->delete();
        foreach ($request->items as $item) {
            $masr_expense->items()->create($item);
        }

        return redirect()->route('admin.masr_expenses.index')->with('success', 'تم تعديل المصاريف بنجاح');
    }

    public function show(MasrExpense $masr_expense)
    {
        $masr_expense->load(['items', 'creator']);
        return view('masr_expenses.show', compact('masr_expense'));
    }

    public function destroy(MasrExpense $masr_expense)
    {
        $masr_expense->items()->delete();
        $masr_expense->delete();
        return redirect()->route('admin.masr_expenses.index')->with('success', 'تم حذف التقرير');
    }
    public function filter(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $expenses = MasrExpense::with(['items', 'creator'])->whereBetween('date', [$from, $to])->get();
        $total_expenses = $expenses->flatMap->items->sum('amount');
        return response()->json([
            'expenses' => $expenses,
            'total_expenses' => $total_expenses,
        ]);
    }
}
