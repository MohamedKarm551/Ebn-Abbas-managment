<?php 

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Booking;
use App\Models\ArchivedBooking; // <--- 1. نضيف ArchivedBooking
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <--- 2. نضيف DB
use Illuminate\Support\Facades\Log; // <--- 3. نضيف Log
use Illuminate\Support\Facades\Storage; // *** تأكد من وجوده ***


class HotelController extends Controller
{
    public function index()
    {
        $hotels = Hotel::all();
        return view('admin.hotels', compact('hotels'));
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
            'description' => 'nullable|string',
            'image_path' => 'nullable|url|max:1024', // <-- توحيد الاسم هنا
        ]);

        // 2. تنقية الاسم
        $validatedData['name'] = strip_tags($validatedData['name']);
        // يمكنك إضافة تنقية لباقي الحقول النصية لو أردت
        // $validatedData['location'] = strip_tags($validatedData['location'] ?? '');
        // $validatedData['description'] = strip_tags($validatedData['description'] ?? '');

        // 3. إنشاء الفندق بكل البيانات
        $hotel = Hotel::create($validatedData);

        // 4. إنشاء الإشعار
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة فندق جديد : {$hotel->name}", // إزالة الفاصلة الزائدة
            'type' => 'جديد',
        ]);

        // 5. إعادة التوجيه
        return redirect()->route('admin.hotels')->with('success', 'تم إضافة الفندق بنجاح!');
    }

    public function edit($id)
    {
        $hotel = Hotel::findOrFail($id);
        return view('admin.edit-hotel', compact('hotel'));
    }

    public function update(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $oldName = $hotel->name;

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
            'description' => 'nullable|string',
            'image_path' => 'nullable|url|max:1024', // <-- توحيد الاسم هنا
        ]);

        // 2. تنقية الاسم
        $validatedData['name'] = strip_tags($validatedData['name']);
        // يمكنك إضافة تنقية لباقي الحقول النصية لو أردت

        // 3. تحديث الفندق بكل البيانات الجديدة
        $hotel->update($validatedData);

        // 4. إنشاء الإشعار
        Notification::create([
            'user_id' => Auth::user()->id,
            // استخدام الاسم المحدث من $hotel->name
            'message' => "تعديل فندق: {$oldName} إلى: {$hotel->name}", // إزالة الفاصلة الزائدة والأقواس
            'type' => 'تحديث', // تغيير النوع ليكون أوضح
        ]);

        // 5. إعادة التوجيه
        return redirect()->route('admin.hotels')->with('success', 'تم تعديل الفندق بنجاح!');
    }

    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->delete();
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "حذف فندق   : {$hotel->name} ,",
            'type' => 'عملية حذف',
        ]);
        return redirect()->route('admin.hotels')->with('success', 'تم حذف الفندق بنجاح!');
    }
}