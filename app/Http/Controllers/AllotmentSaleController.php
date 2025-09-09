<?php

namespace App\Http\Controllers;

use App\Models\Allotment;
use App\Models\AllotmentSale;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AllotmentSaleController extends Controller
{
    public function create()
    {
        $hotels = Hotel::orderBy('name')->get();
        // استرجاع الألوتمنت مع علاقة الفندق
        $allotments = Allotment::with(['hotel', 'sales'])
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();
        // dd($allotments);
        // حساب الغرف المتبقية لكل ألوتمنت
        foreach ($allotments as $allotment) {
            // إذا كانت خاصية remaining_rooms غير موجودة في النموذج، نحسبها هنا
            if (!isset($allotment->remaining_rooms)) {
                $soldRooms = $allotment->sales->sum('rooms_sold');
                $allotment->remaining_rooms = $allotment->rooms_count - $soldRooms;
            }
        }
        // dd($allotments);
        return view('allotment_sales.create', compact('hotels', 'allotments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'allotment_id' => 'required|exists:allotments,id',
            'hotel_id' => 'required|exists:hotels,id',
            'company_name' => 'required|string|max:255',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'rooms_sold' => 'required|integer|min:1',
            'sale_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
            'notes' => 'nullable|string',
        ]);

        $allotment = Allotment::findOrFail($request->allotment_id);

        // التحقق من توفر الغرف في فترة البيع
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        // التحقق من أن تاريخ الدخول والخروج ضمن تواريخ الألوتمنت
        if (
            $checkIn < Carbon::parse($allotment->start_date) ||
            $checkOut > Carbon::parse($allotment->end_date)
        ) {
            return back()->withErrors([
                'check_in' => 'تواريخ الحجز يجب أن تكون ضمن فترة الألوتمنت (' .
                    $allotment->start_date->format('Y-m-d') . ' - ' .
                    $allotment->end_date->format('Y-m-d') . ')'
            ])->withInput();
        }

        // التأكد من توفر الغرف
        $existingSales = AllotmentSale::where('allotment_id', $allotment->id)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })->sum('rooms_sold');

        $availableRooms = $allotment->rooms_count - $existingSales;

        if ($request->rooms_sold > $availableRooms) {
            return back()->withErrors([
                'rooms_sold' => 'لا يوجد عدد كافي من الغرف المتاحة. الغرف المتاحة: ' . $availableRooms
            ])->withInput();
        }

        AllotmentSale::create($validated);

        return redirect()->route('allotments.show', $allotment)
            ->with('success', 'تم إضافة عملية البيع بنجاح.');
    }

    public function edit(AllotmentSale $sale)
    {
        $hotels = Hotel::orderBy('name')->get();
        $allotments = Allotment::where('status', 'active')->get();
        return view('allotment_sales.edit', compact('sale', 'hotels', 'allotments'));
    }

    public function update(Request $request, AllotmentSale $sale)
    {
        $validated = $request->validate([
            'allotment_id' => 'required|exists:allotments,id',
            'hotel_id' => 'required|exists:hotels,id',
            'company_name' => 'required|string|max:255',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'rooms_sold' => 'required|integer|min:1',
            'sale_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
            'notes' => 'nullable|string',
        ]);

        $allotment = Allotment::findOrFail($request->allotment_id);

        // نفس التحققات كما في دالة الإضافة، مع استبعاد العملية الحالية
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        if (
            $checkIn < Carbon::parse($allotment->start_date) ||
            $checkOut > Carbon::parse($allotment->end_date)
        ) {
            return back()->withErrors([
                'check_in' => 'تواريخ الحجز يجب أن تكون ضمن فترة الألوتمنت'
            ])->withInput();
        }

        $existingSales = AllotmentSale::where('allotment_id', $allotment->id)
            ->where('id', '!=', $sale->id)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })->sum('rooms_sold');

        $availableRooms = $allotment->rooms_count - $existingSales;

        if ($request->rooms_sold > $availableRooms) {
            return back()->withErrors([
                'rooms_sold' => 'لا يوجد عدد كافي من الغرف المتاحة'
            ])->withInput();
        }

        $sale->update($validated);

        return redirect()->route('allotments.show', $allotment)
            ->with('success', 'تم تحديث عملية البيع بنجاح.');
    }

    public function destroy(AllotmentSale $sale)
    {
        $allotmentId = $sale->allotment_id;
        $sale->delete();

        return redirect()->route('allotments.show', $allotmentId)
            ->with('success', 'تم حذف عملية البيع بنجاح.');
    }
}
