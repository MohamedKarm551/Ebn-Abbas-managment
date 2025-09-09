<?php

namespace App\Http\Controllers;

use App\Models\Allotment;
use App\Models\AllotmentSale;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AllotmentController extends Controller
{
    public function index()
    {
        // قيم افتراضية للمتغيرات المطلوبة في صفحة العرض
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $hotelId = null;

        $hotels = Hotel::all();
        $allotments = Allotment::with('hotel')->orderBy('start_date', 'desc')->get();

        // توفير بيانات المبيعات الفارغة أو الفعلية إذا كانت مطلوبة في العرض
        $sales = AllotmentSale::with('hotel')
            ->whereDate('check_out', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->get();

        return view('allotments.index', [
            'allotments' => $allotments,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hotels' => $hotels,
            'sales' => $sales,
            'selectedHotelId' => $hotelId,
        ]);
    }

    public function create()
    {
        $hotels = Hotel::orderBy('name')->get();
        $allotments = Allotment::with('hotel')->orderBy('start_date', 'desc')->get();

        // dd($allotments);
        return view(
            'allotments.create',
            compact('hotels', 'allotments')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rooms_count' => 'required|integer|min:1',
            'rate_per_room' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:5',
            'notes' => 'nullable|string',
        ]);

        $allotment = Allotment::create($validated);
        return redirect()->route('allotments.index')->with('success', 'تم إضافة الألوتمنت بنجاح.');
    }

    public function show(Allotment $allotment)
    {
        $allotment->load(['hotel', 'sales']);
        $hotels = Hotel::orderBy('name')->get(); 
        $allotments = Allotment::with('hotel')->orderBy('start_date', 'desc')->get(); 
        return view('allotments.show', compact('allotment', 'hotels', 'allotments'));
    }

    public function edit(Allotment $allotment)
    {
        $hotels = Hotel::orderBy('name')->get();
        return view('allotments.edit', compact('allotment', 'hotels'));
    }

    public function update(Request $request, Allotment $allotment)
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rooms_count' => 'required|integer|min:1',
            'rate_per_room' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:5',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $allotment->update($validated);
        return redirect()->route('allotments.index')->with('success', 'تم تحديث الألوتمنت بنجاح.');
    }

    public function destroy(Allotment $allotment)
    {
        $allotment->delete();
        return redirect()->route('allotments.index')->with('success', 'تم حذف الألوتمنت بنجاح.');
    }

    // كنترولر لعرض تقرير الألوتمنت
    public function monitor(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $hotelId = $request->input('hotel_id');

        $hotels = Hotel::when($hotelId, function ($query) use ($hotelId) {
            return $query->where('id', $hotelId);
        })->get();

        // جلب بيانات الألوتمنت للفترة المحددة
        $allotments = Allotment::with('hotel')
            ->whereDate('end_date', '>=', $startDate)
            ->whereDate('start_date', '<=', $endDate)
            ->when($hotelId, function ($query) use ($hotelId) {
                return $query->where('hotel_id', $hotelId);
            })
            ->get();

        // جلب بيانات المبيعات للفترة المحددة
        $sales = AllotmentSale::with('hotel')
            ->whereDate('check_out', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->when($hotelId, function ($query) use ($hotelId) {
                return $query->where('hotel_id', $hotelId);
            })
            ->get();

        return view('allotments.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hotels' => $hotels,
            'allotments' => $allotments,
            'sales' => $sales,
            'selectedHotelId' => $hotelId,
        ]);
    }
}
