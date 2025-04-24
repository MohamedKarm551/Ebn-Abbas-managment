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
        $request->validate([
            // *** تعديل قاعدة التحقق هنا ***
            // يسمح بالحروف (بما في ذلك العربية)، الأرقام، المسافات، الشرطة، القوسين
            'name' => [
                'required',//مطلوب
                'string', //نص
                'max:255', //الحد الأقصى 255 حرف
                'unique:hotels,name', //اسم الفندق فريد
                // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);
         // *** تطبيق التنقية هنا قبل الحفظ ***
         $sanitizedName = strip_tags($request->input('name'));
        Hotel::create(['name' => $sanitizedName]);
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة فندق جديد : {$request->name} ,",
            'type' => 'جديد',
        ]);
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
        $oldName = $hotel->name; // حفظ الاسم القديم
        $request->validate([
            // *** تعديل قاعدة التحقق هنا ***
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:hotels,name,' . $hotel->id,
                 // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);
        
        // *** تطبيق التنقية هنا قبل التحديث ***
        $sanitizedName = strip_tags($request->input('name'));


        $hotel->update(['name' => $sanitizedName]);
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تعديل اسم فندق   :{$oldName} إلى: {  $hotel->name} ,",
            'type' => 'تحديث اسم',
        ]);
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