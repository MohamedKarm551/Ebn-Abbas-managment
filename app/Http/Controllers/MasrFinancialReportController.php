<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\MasrFinancialReport;
use Illuminate\Support\Facades\Auth;


class MasrFinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'date_desc');

        // جلب مع البنود
        $reports = MasrFinancialReport::with(['items', 'creator']);

        // الترتيب المباشر بالتاريخ
        if ($sort === 'date_asc') {
            $reports = $reports->orderBy('date', 'asc')->paginate(15);
        } elseif ($sort === 'date_desc') {
            $reports = $reports->orderBy('date', 'desc')->paginate(15);
        } else {
            // الترتيب بالكولكشن بعد الجلب
            $reports = $reports->get();

            // حساب الربح والتكلفة لكل تقرير
            if ($sort === 'profit_desc') {
                $reports = $reports->sortByDesc(function ($report) {
                    $total_cost = $report->items->sum('cost_amount');
                    $total_sale = $report->items->sum('sale_amount');
                    return $total_sale - $total_cost;
                })->values();
            } elseif ($sort === 'profit_asc') {
                $reports = $reports->sortBy(function ($report) {
                    $total_cost = $report->items->sum('cost_amount');
                    $total_sale = $report->items->sum('sale_amount');
                    return $total_sale - $total_cost;
                })->values();
            } elseif ($sort === 'cost_desc') {
                $reports = $reports->sortByDesc(function ($report) {
                    return $report->items->sum('cost_amount');
                })->values();
            } elseif ($sort === 'cost_asc') {
                $reports = $reports->sortBy(function ($report) {
                    return $report->items->sum('cost_amount');
                })->values();
            }
        }

        return view('masr_financial_reports.index', compact('reports', 'sort'));
    }

    public function show(MasrFinancialReport $report)
    {
        return view('masr_financial_reports.show', compact('report'));
    }

    public function create()
    {
        $currencies = ['EGP' => 'جنيه مصري', 'KWD' => 'دينار كويتي', 'SAR' => 'ريال سعودي'];
        return view('masr_financial_reports.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.cost_amount' => 'required|numeric',
            'items.*.cost_currency' => 'required|string',
            'items.*.sale_amount' => 'nullable|numeric',
            'items.*.sale_currency' => 'required|string',
        ]);

        $report = MasrFinancialReport::create([
            'date' => $request->date,
            'created_by' => Auth::id(),
            'notes' => $request->notes ?? null,
            'title' => $request->title ?? null,
        ]);

        foreach ($request->items as $item) {
            $report->items()->create($item);
        }

        return redirect()->route('admin.masr.financial-reports.index')->with('success', 'تم إضافة التقرير بنجاح');
    }
    public function edit(MasrFinancialReport $report)
    {
        $currencies = ['EGP' => 'جنيه مصري', 'KWD' => 'دينار كويتي', 'SAR' => 'ريال سعودي'];
        return view('masr_financial_reports.edit', compact('report', 'currencies'));
    }

    public function update(Request $request, MasrFinancialReport $report)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.cost_amount' => 'required|numeric',
            'items.*.cost_currency' => 'required|string',
            'items.*.sale_amount' => 'nullable|numeric',
            'items.*.sale_currency' => 'required|string',
        ]);

        $report->update([
            'date' => $request->date,
            'notes' => $request->notes ?? null,
            'title' => $request->title ?? null,
        ]);

        // حذف البنود القديمة ثم إضافة البنود الجديدة
        $report->items()->delete();
        foreach ($request->items as $item) {
            $report->items()->create($item);
        }

        return redirect()->route('admin.masr.financial-reports.index')->with('success', 'تم تعديل التقرير بنجاح');
    }


    public function destroy(MasrFinancialReport $report)
    {
        $report->delete();
        return redirect()->route('admin.masr.financial-reports.index')->with('success', 'تم حذف التقرير');
    }
    public function filter(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $reports = MasrFinancialReport::with(['items', 'creator'])->whereBetween('date', [$from, $to])->get();

        // جمع الإجماليات من البنود وليس من التقرير الرئيسي
        $total_cost = $reports->flatMap->items->sum('cost_amount');
        $total_sale = $reports->flatMap->items->sum('sale_amount');
        $net_profit = $total_sale - $total_cost;

        return response()->json([
            'reports' => $reports,
            'total_cost' => $total_cost,
            'total_sale' => $total_sale,
            'net_profit' => $net_profit,
        ]);
    }
    public function list()
    {
        $bookings = Booking::select('id', 'client_name', 'amount_due_to_hotel', 'amount_due_from_company','currency' , 'notes')->latest()->limit(10)->get();
        return response()->json($bookings);
    }
    public function info($id)
    {
        $booking = Booking::findOrFail($id);
        // هنا رجع بيانات booking فقط بصيغة json
        return response()->json([
            'client_name' => $booking->client_name,
            'cost' => $booking->amount_due_to_hotel,
            'sale' => $booking->amount_due_from_company,
            'notes' => $booking->notes,
            'currency' => $booking->currency,
        ]);
    }
}
