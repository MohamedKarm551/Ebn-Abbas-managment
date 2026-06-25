<?php
namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;
use App\Models\JournalEntry;     
use App\Models\AccountLedger;    
class TripController extends Controller
{
    public function index(Request $request) {
        $query = Trip::with([
            'prices',
            'bookings.payments',      
            'bookings.discounts'      
        ])->latest();

        if ($request->filled('name')) {
            $name = $request->name;

            if (is_numeric($name)) {
                $query->where('id', $name);
            } else {
                $query->where('name', 'LIKE', '%' . $name . '%');
            }
        }
        
        if ($request->has_remaining == '1') {
            $query->hasRemaining();
        }

        if ($request->filled('from_date')) {
            $query->whereDate('from', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('to', '<=', $request->to_date);
        }

        // فلتر الأماكن المتاحة
        if ($request->filter === 'total_seats') {
            $query->whereRaw('total_seats > (SELECT COUNT(*) FROM bookings WHERE bookings.trip_id = trips.id)');
        }

        //  حساب الإجماليات 
        $queryForTotals = clone $query;
        $allTrips = $queryForTotals->get(); // جلب كل الرحلات 

        $totalRemainingSeats = $allTrips->sum(function ($trip) {
            return $trip->total_seats - $trip->bookings->count();
        });

        $totalRemainingMoney = $allTrips->sum(function ($trip) {
            return $trip->bookings->sum(fn($b) => $b->remaining());
        });

        // فلتر رحلات فيها فلوس متبقية
        if ($request->filter === 'remaining_money') {
            $trips = $query->get()->filter(function($trip) {
                return $trip->bookings->sum(fn($b) => $b->remaining()) > 0;
            });
        }

        $trips = $query->paginate(10);
        return view('trips.index', compact('trips', 'totalRemainingMoney', 'totalRemainingSeats'));
    }

    public function create() {
        $defaultItems = $this->getDefaultItems();
        return view('trips.create', compact('defaultItems'));
    }

    public function store(Request $request)
    {
    $request->validate([
        'name' => 'required',
        'from' => 'required',
        'to'   => 'required',
        'description' => 'nullable|string',
        'total_seats' => 'nullable|integer|min:1',
        'prices.*.room_type' => 'nullable|string',
        'prices.*.price'     => 'nullable|numeric',
    ]);

    $trip = Trip::create(array_merge(
        $request->only(['name', 'from', 'to', 'hotels', 'description']),
        [
            'available_seats' => $request->total_seats,
            'total_seats'     => $request->total_seats,
        ]
    ));

    // الأسعار
    $roomTypes = [];
    foreach ($request->prices ?? [] as $index => $row) {
        if (!empty($row['room_type']) && !empty($row['price'])) {
            if (in_array($row['room_type'], $roomTypes)) {
                return back()->withErrors(['error' => '⚠️ لا يمكن إضافة نفس نوع الغرفة ("' . $row['room_type'] . '") أكثر من مرة.'])->withInput();
            }
            $roomTypes[] = $row['room_type'];
        }
    }

    foreach ($request->prices ?? [] as $row) {
        if (!empty($row['room_type']) && !empty($row['price'])) {
            $trip->prices()->create(['room_type' => $row['room_type'], 'price' => $row['price']]);
        }
    }

    // البنود الأساسية
    $defaultItems = $this->getDefaultItems();
    foreach ($defaultItems as $index => $itemName) {
        $value = $request->input("default_items.$index.value");
        if ($value !== null) {
            $trip->items()->create([
                'name'  => $itemName,
                'value' => $value,
            ]);
        }
    }

    // البنود الإضافية
    foreach ($request->additional_items ?? [] as $row) {
        if (!empty($row['name']) && !empty($row['value'])) {
            $trip->items()->create([
                'name'  => $row['name'],
                'value' => $row['value'],
            ]);
        }
    }

    return redirect()->route('trips.index')->with('success', '✅ تم إضافة الرحلة');
    }

    public function show(Trip $trip) {
        $trip->load('prices');
        return view('trips.show', compact('trip'));
    }


    public function edit(Trip $trip)
    {
        $trip->load('items', 'prices');

        // استخدم نفس قائمة البنود الأساسية من الدالة المساعدة
        $defaultItems = $this->getDefaultItems();

        $defaultItemsWithValues = [];
        foreach ($defaultItems as $index => $itemName) {
            $existingItem = $trip->items->firstWhere('name', $itemName);
            $defaultItemsWithValues[$index] = [
                'name'  => $itemName,
                'value' => $existingItem ? $existingItem->value : '',
            ];
        }

        // البنود الإضافية (التي ليست ضمن defaultItems)
        $additionalItems = $trip->items->filter(function($item) use ($defaultItems) {
            return !in_array($item->name, $defaultItems);
        })->values();

        return view('trips.edit', compact('trip', 'defaultItemsWithValues', 'additionalItems'));
    }


    public function update(Request $request, Trip $trip)
    {
    $trip->update($request->only('name','from','to','hotels','description'));

    // معالجة total_seats
    if ($request->has('total_seats')) {
        $trip->total_seats = $request->total_seats;

        // حساب المقاعد المتبقية = السعة الكلية - عدد الحجوزات الموجودة
        $bookingsCount = $trip->bookings()->count();
        $trip->available_seats = $trip->total_seats - $bookingsCount;

        // منع أن تصبح available_seats سالبة (لو زادت الحجوزات عن السعة الجديدة)
        if ($trip->available_seats < 0) {
            $trip->available_seats = 0;
        }

        $trip->save();
    }

    $roomTypes = [];
    $newPrices = [];
    foreach ($request->prices ?? [] as $row) {
        if (!empty($row['room_type']) && !empty($row['price'])) {
            if (in_array($row['room_type'], $roomTypes)) {
                return back()->withErrors(['error' => '⚠️ لا يمكن إضافة نفس نوع الغرفة ("' . $row['room_type'] . '") أكثر من مرة.'])->withInput();
            }
            $roomTypes[] = $row['room_type'];
            $newPrices[] = $row;
        }
    }

    // حذف القديم وإنشاء الجديد
    $trip->prices()->delete();
    foreach ($newPrices as $priceData) {
        $trip->prices()->create($priceData);
    }

    $trip->load('prices');
    foreach ($trip->bookings as $booking) {
        $newPrice = $trip->prices()
            ->where('room_type', $booking->accommodation_type)
            ->first();

        if ($newPrice) {
            $booking->update(['base_price' => $newPrice->price]);
        }
    }


    $trip->items()->delete();
    $defaultItems = $this->getDefaultItems();
    foreach ($defaultItems as $index => $itemName) {
        $value = $request->input("default_items.$index.value");
        if ($value !== null) {
            $trip->items()->create([
                'name'  => $itemName,
                'value' => $value,
            ]);
        }
    }

    foreach ($request->additional_items ?? [] as $row) {
        if (!empty($row['name']) && !empty($row['value'])) {
            $trip->items()->create([
                'name'  => $row['name'],
                'value' => $row['value'],
            ]);
        }
    }

    return redirect()->route('trips.index')->with('success', '✅ تم تعديل الرحلة');
}

    public function destroy(Trip $trip) {
        DB::transaction(function () use ($trip) {
            $trip->prices()->delete();
            $trip->items()->delete();
            $trip->roomAssignments()->delete();
            foreach ($trip->bookings as $booking) {
                AccountingService::onBookingDeleted($booking); 
                $booking->discounts()->delete(); 
                $booking->delete(); 
            }
        $trip->delete();
        });
        return redirect()->route('trips.index')->with('success', '🗑️ تم حذف الرحلة');
    }

    // helper
    private function getDefaultItems()
    {
        return [
            'الطيران للكبار',
            'الطيران اللأطفال',
            'الطيران للرضيع',
            'التاشيرة',
            'الباركود',
            'النقل للفرد',
            'فندق مكة',
            'فندق المدينة',
            'الإشراف',
            'الهدايا',
            'الربح',
        ];
    }


    public function representativesReport(Trip $trip)
    {
        $groups = $trip->bookings()
            ->whereNotNull('representative_id')
            ->select('representative_id')
            ->selectRaw('COUNT(id) as bookings_count')
            ->groupBy('representative_id')
            ->orderByDesc('bookings_count')
            ->get();

        $representativeIds = $groups->pluck('representative_id');
        $users = User::whereIn('id', $representativeIds)->get()->keyBy('id');

        $report = $groups->map(function ($group) use ($users) {
            $user = $users->get($group->representative_id);
            $name = $user->name ?? 
                    $user->full_name ?? 
                    $user->username ?? 
                    $user->email ?? 
                    'مندوب #' . $user->id;

            return (object) [
                'representative_name' => $name,
                'bookings_count' => $group->bookings_count,
            ];
        });

        $totalBookings = $report->sum('bookings_count');
        $totalSeats = $trip->total_seats;

        return view('trips.representatives-report', compact('trip', 'report', 'totalBookings', 'totalSeats'));
    }

    public function trashed() {
        $trips = Trip::onlyTrashed()
            ->with(['bookings' => fn($q) => $q->withTrashed()])
            ->latest('deleted_at')
            ->paginate(20);
        return view('trips.trashed', compact('trips'));
    }


    public function restore($id) {
        DB::transaction(function () use ($id) {
            $trip = Trip::withTrashed()->findOrFail($id);
            $trip->restore();
            $trip->prices()->withTrashed()->restore();
            $trip->items()->withTrashed()->restore();
            $trip->roomAssignments()->withTrashed()->restore();

            $trip->bookings()->withTrashed()->each(function ($booking) {
                $booking->discounts()->withTrashed()->each(function ($discount) {
                    JournalEntry::withTrashed()
                        ->where('source_type', 'App\Models\Discount')
                        ->where('source_id', $discount->id)
                        ->each(fn($e) => AccountingService::restoreEntry($e));
                    $discount->restore();
                });

                JournalEntry::withTrashed()
                    ->where('source_type', Booking::class)
                    ->where('source_id', $booking->id)
                    ->each(fn($e) => AccountingService::restoreEntry($e));

                $booking->restore();
            });
        });
          return redirect()->route('trips.index')->with('success', '✅ تم استعادة الرحلة بحجوزاتها وقيودها ومرتبطاتها');
    }

}