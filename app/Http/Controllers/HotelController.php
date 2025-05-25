<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Booking;
use App\Models\ArchivedBooking;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\HotelImage;

class HotelController extends Controller
{
    // مدة صلاحية الكاش بالثواني (هنا:    3 أيام)
    protected $cacheDuration = 259200;
    protected $allHotelsCacheKey = 'hotels_all_with_images';


    public function index()
    {
        // $hotels = Hotel::all(); // Old way
        $hotels = Cache::remember($this->allHotelsCacheKey, $this->cacheDuration, function () {
            Log::info('Caching all hotels with images.');
            return Hotel::with('images')->orderBy('name')->get(); // جلب الفنادق مع صورها
        });
        return view('admin.hotels.hotels', compact('hotels'));
    }

    public function create()
    {
        return view('hotels.create');
    }

    public function store(Request $request)
    {


        // 1. التحقق من البيانات (استخدام image_url)
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:hotels,name',
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
            'location' => 'nullable|string|max:255',
            'purchased_rooms_count' => 'nullable|integer|min:0', // التحقق من أن purchased_rooms_count هو عدد صحيح (اختياري)
            'description' => 'nullable|string',
            // التحقق من أن image_urls مصفوفة وأن كل عنصر فيها هو URL صالح (اختياري)
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|url|max:1024',
        ]);

        // 2. تنقية الاسم
        $validatedData['name'] = strip_tags($validatedData['name']);
        // يمكنك إضافة تنقية لباقي الحقول النصية لو أردت
        // $validatedData['location'] = strip_tags($validatedData['location'] ?? '');
        // $validatedData['description'] = strip_tags($validatedData['description'] ?? '');

        // 3. إنشاء الفندق بكل البيانات
        // إنشاء الفندق بالبيانات الأساسية
        $hotel = Hotel::create([
            'name' => $validatedData['name'],
            'location' => $validatedData['location'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'purchased_rooms_count' => $validatedData['purchased_rooms_count'] ?? 30, // استخدام القيمة الافتراضية 30 إذا لم يتم توفيرها
            // 'color' => $validatedData['color'] ?? '#000000', // إذا كان لديك حقل لون
        ]);
        // حفظ الصور المتعددة إذا تم توفيرها
        // قم بتصفية القيم الفارغة من مصفوفة image_urls قبل الحفظ
        $imageUrlsToSave = isset($validatedData['image_urls']) ? array_filter($validatedData['image_urls']) : [];

        if (!empty($imageUrlsToSave)) {
            foreach ($imageUrlsToSave as $imageUrl) {
                // التحقق من أن الرابط ليس فارغًا تم ضمنيًا بواسطة array_filter
                $hotel->images()->create(['image_path' => $imageUrl]);
            }
        }
        // 4. إنشاء الإشعار
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة فندق جديد : {$hotel->name}", // إزالة الفاصلة الزائدة
            'type' => 'جديد',
        ]);
        // مسح كاش قائمة جميع الفنادق لأنها تغيرت
        Cache::forget($this->allHotelsCacheKey);
        Log::info('All hotels cache cleared after new hotel creation.');

        // 5. إعادة التوجيه
        return redirect()->route('admin.hotels')->with('success', 'تم إضافة الفندق بنجاح!');
    }

    public function edit($id)
    {
        // $hotel = Hotel::findOrFail($id); // Old way
        $hotelCacheKey = "hotel_{$id}_with_images";
        $hotel = Cache::remember($hotelCacheKey, $this->cacheDuration, function () use ($id) {
            Log::info("Caching hotel {$id} with images.");
            return Hotel::with('images')->findOrFail($id); // جلب الفندق مع صوره
        });

        return view('admin.hotels.edit-hotel', compact('hotel'));
    }

    public function update(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $oldName = $hotel->name;
        $hotelCacheKey = "hotel_{$id}_with_images";

        // 1. التحقق من البيانات (استخدام image_url)
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:hotels,name,' . $hotel->id, // تجاهل الفندق الحالي عند التحقق من التفرد
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
            'location' => 'nullable|string|max:255',
            'purchased_rooms_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'nullable|url|max:1024', // التحقق من أن كل عنصر في المصفوفة هو URL صالح
        ]);

        // 2. تنقية الاسم
        $validatedData['name'] = strip_tags($validatedData['name']);
        // يمكنك إضافة تنقية لباقي الحقول النصية لو أردت

        // 3. تحديث الفندق بكل البيانات الجديدة
        $hotel->update([
            'name' => $validatedData['name'],
            'location' => $validatedData['location'] ?? null,
            'description' => $validatedData['description'] ?? null,
            // 'color' => $validatedData['color'] ?? '#000000', // إذا كان لديك حقل لون
            'purchased_rooms_count' => $validatedData['purchased_rooms_count'] ?? 30, // استخدام القيمة الافتراضية 30 إذا لم يتم توفيرها
        ]);

        // معالجة الصور
        // إذا تم إرسال مفتاح image_urls (حتى لو كان مصفوفة فارغة بسبب إفراغ جميع الحقول)، قم بتحديث الصور
        if ($request->has('image_urls')) {
            $hotel->images()->delete(); // احذف الصور القديمة

            // قم بتصفية القيم الفارغة من مصفوفة image_urls قبل الحفظ
            $imageUrlsToSave = isset($validatedData['image_urls']) ? array_filter($validatedData['image_urls']) : [];

            if (!empty($imageUrlsToSave)) {
                foreach ($imageUrlsToSave as $imageUrl) {
                    $hotel->images()->create(['image_path' => $imageUrl]);
                }
            }
        }
        // إذا لم يتم إرسال 'image_urls' في الطلب، لا تقم بأي تعديل على الصور الحالية.


        // 4. إنشاء الإشعار
        Notification::create([
            'user_id' => Auth::user()->id,
            // استخدام الاسم المحدث من $hotel->name
            'message' => "تعديل فندق: {$oldName} إلى: {$hotel->name}", // إزالة الفاصلة الزائدة والأقواس
            'type' => 'تحديث', // تغيير النوع ليكون أوضح
        ]);
        // مسح كاش هذا الفندق وكاش قائمة جميع الفنادق
        Cache::forget($hotelCacheKey);
        Cache::forget($this->allHotelsCacheKey);
        Log::info("Cache cleared for hotel {$id} and all hotels list after update.");


        // 5. إعادة التوجيه
        return redirect()->route('admin.hotels')->with('success', 'تم تعديل الفندق بنجاح!');
    }

    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotelCacheKey = "hotel_{$id}_with_images";

        $hotel->delete();
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "حذف فندق   : {$hotel->name} ,",
            'type' => 'عملية حذف',
        ]);
        // مسح كاش هذا الفندق وكاش قائمة جميع الفنادق
        Cache::forget($hotelCacheKey);
        Cache::forget($this->allHotelsCacheKey);
        Log::info("Cache cleared for hotel {$id} and all hotels list after deletion.");


        return redirect()->route('admin.hotels')->with('success', 'تم حذف الفندق بنجاح!');
    }
}
