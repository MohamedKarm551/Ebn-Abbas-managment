<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Availability;
use App\Models\Hotel;
use Illuminate\Support\Facades\Log; // لاستخدام اللوج لو حبيت تتابع
use Carbon\Carbon; // لاستخدام Carbon للتعامل مع التواريخ
use Illuminate\Support\Facades\DB; // <-- ضيف السطر ده (تقريباً سطر 8)

class CompanyAvailabilityController extends Controller
{
    /**
     * Display a listing of the available resources for the company.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $hotels = Hotel::orderBy('name')->get();

        // Start query for active availabilities, eager load necessary relations
        $query = Availability::with(['hotel', 'availabilityRoomTypes.roomType'])
            ->where('status', 'active'); // جلب الإتاحات النشطة فقط
            

        // --- Optional Filtering (Example: By Hotel) ---
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }

        // --- بداية تعديل فلترة التاريخ ---
        $filterStartDate = $request->input('filter_start_date');
        $filterEndDate = $request->input('filter_end_date');

        try {
            // الحالة الأولى: تاريخ بداية ونهاية موجودين (البحث عن تقاطع/أوفرلاب)
            if ($filterStartDate && $filterEndDate) {
                $start = Carbon::parse($filterStartDate)->startOfDay();
                $end = Carbon::parse($filterEndDate)->endOfDay();

                // نتأكد إن تاريخ البداية مش بعد تاريخ النهاية
                if ($start->lte($end)) {
                    $query->where(function ($q) use ($start, $end) {
                        // شرط التقاطع: الإتاحة تبدأ قبل أو في يوم نهاية الفلتر
                        $q->whereDate('start_date', '<=', $end)
                            // و الإتاحة تنتهي بعد أو في يوم بداية الفلتر
                            ->whereDate('end_date', '>=', $start);
                    });
                    Log::info("فلتر التاريخ (إتاحات): تقاطع بين $start و $end");
                } else {
                    Log::info("فلتر التاريخ (إتاحات): تم تجاهله لأن البداية بعد النهاية.");
                }
            }
            // --- بداية تعديل الحالة الثانية ---
            // الحالة الثانية: تاريخ بداية فقط موجود (البحث عن الإتاحات النشطة في هذا اليوم)
            elseif ($filterStartDate && !$filterEndDate) {
                $start = Carbon::parse($filterStartDate)->startOfDay();
                // الإتاحة يجب أن تبدأ قبل أو في هذا اليوم
                $query->whereDate('start_date', '<=', $start)
                    // و يجب أن تنتهي بعد أو في هذا اليوم
                    ->whereDate('end_date', '>=', $start);
                Log::info("فلتر التاريخ (إتاحات): نشط في يوم $start");
            }
            // --- نهاية تعديل الحالة الثانية ---

            // --- بداية تعديل الحالة الثالثة ---
            // الحالة الثالثة: تاريخ نهاية فقط موجود (البحث عن الإتاحات التي تنتهي في هذا اليوم بالضبط)
            elseif (!$filterStartDate && $filterEndDate) {
                $end = Carbon::parse($filterEndDate)->startOfDay(); // نستخدم بداية اليوم للمقارنة الدقيقة
                // الإتاحة يجب أن تنتهي في هذا اليوم بالضبط
                $query->whereDate('end_date', '=', $end);
                Log::info("فلتر التاريخ (إتاحات): تنتهي في يوم $end");
            }
            // --- نهاية تعديل الحالة الثالثة ---
            // لو مفيش تواريخ، مش هيعمل حاجة وهيعرض كل الإتاحات (بعد فلتر الفندق لو موجود)

        } catch (\Exception $e) {
            // ... (الكود ده زي ما هو) ...
        }

        // --- نهاية تعديل فلترة التاريخ ---


        $sortPrice = $request->input('sort_price'); // بنجيب قيمة sort_price من الـ URL

        // هنتأكد إن القيمة هي 'asc' أو 'desc' بس
        if ($sortPrice === 'asc' || $sortPrice === 'desc') {
            // لو المستخدم عايز يرتب بالسعر:
            // 1. بنختار كل أعمدة جدول availabilities الأساسي
            $query->select('availabilities.*')
                // 2. بنضيف عمود جديد وهمي اسمه min_sale_price
                //    قيمته هي أقل سعر (MIN(sale_price)) من جدول availability_room_types
                //    للغرف المرتبطة بالإتاحة دي بس
                ->addSelect(DB::raw('(SELECT MIN(sale_price) FROM availability_room_types WHERE availability_room_types.availability_id = availabilities.id) as min_sale_price'))
                // 3. بنرتب النتايج بناءً على العمود الوهمي ده
                //    orderByRaw عشان نحط الإتاحات اللي ملهاش سعر (NULL) في الأول أو الآخر (هنا حطيناهم في الأول)
                //    وبعدين نرتب بالـ min_sale_price حسب اختيار المستخدم (asc أو desc)
                ->orderByRaw('min_sale_price IS NULL ASC, min_sale_price ' . $sortPrice);
        } else {
            // لو المستخدم مش عايز يرتب بالسعر (أو داس مسح الفلتر)
            // رتب ترتيب افتراضي حسب تاريخ البدء (الأقدم أولاً)
            $query->orderBy('start_date', 'asc'); // <-- الترتيب الافتراضي
        }

        // جلب النتائج مع الـ pagination والحفاظ على الفلاتر في اللينكات
        $availabilities = $query->paginate(10)->withQueryString();

        // Pass data to the view
        return view('company.availabilities.index', compact('availabilities', 'hotels'));
    }

    // Add other methods if needed (e.g., show details)
}
